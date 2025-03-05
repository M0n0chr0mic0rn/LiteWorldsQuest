<?php

class cronBridge
{
    private static $_db_username = "maria";
    private static $_db_passwort = "KerkerRocks22";
    private static $_db_host = "127.0.0.1";
    private static $_db_name = "Bridge";
    private static $_db;

    function __construct()
    {
        try
        {
            self::$_db = new PDO("mysql:host=" . self::$_db_host . ";dbname=" . self::$_db_name, self::$_db_username, self::$_db_passwort);
        }
        catch(PDOException $e)
        {
            echo "<br>DATABASE ERROR<br>".$e;
            die();
        }
    }

    private static function Node($method, $params = array(), $rpc_url = "http://127.0.0.1:41234/") 
    {
        # Die JSON-RPC-Daten für den Request
        $request_data = array(
            "jsonrpc" => "1.0",
            "id" => "curltest",  # Eine beliebige ID für die Anfrage
            "method" => $method,  # Die Methode, die ausgeführt werden soll (z.B. "getblock")
            "params" => $params,  # Die Parameter für die Methode
        );

        # JSON-Daten kodieren
        $json_data = json_encode($request_data);
        #var_dump($json_data);

        # HTTP-Kontext-Optionen für die Anfrage
        $options = array(
            "http" => array(
                "method"  => "POST",  # POST-Methode verwenden
                "header"  => "Content-Type: application/json\r\n" .
                "Authorization: Basic " . base64_encode("user:password") . "\r\n",  # Authentifizierung
                "content" => $json_data,  # Die JSON-Daten, die gesendet werden
            ),
        );

        # Kontext erstellen
        $context = stream_context_create($options);

        # POST-Request absenden und Antwort empfangen
        $response = file_get_contents($rpc_url, false, $context);

        # Fehlerbehandlung
        if ($response === FALSE) return false;

        # Die Antwort in ein Array dekodieren
        return json_encode(json_decode($response, true)["result"], JSON_PRETTY_PRINT);
    }

    public static function CheckSwaps()
    {
        $time = time() + 60 * 15;
        $progress = 0;

        $stmt = self::$_db->prepare("SELECT * FROM swaps WHERE progress = :progress AND time > :time");
        $stmt->bindParam(":progress", $progress);
        $stmt->bindParam(":time", $time);
        $stmt->execute();

        #var_dump($stmt->errorInfo());
        #var_dump($stmt->rowCount());

        if ($stmt->rowCount() < 1) return false;

        $swaps = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];
        var_dump($swaps);
        $address = json_decode(self::Node("getaddressesbyaccount", [$swaps["label"]]))[0];
        #var_dump($address);

        
        $utxos = json_decode(self::Node("listunspent", [0, 999999999, [$address]]));

        #var_dump($utxos);

        if (count($utxos) == 0) return false;
        if (number_format($utxos[0]->amount, 8, ".", "") != $swaps["amount"]) return false;

        $tx = json_decode(self::Node("getrawtransaction", [$utxos[0]->txid, 1]));
        
        
        #var_dump($tx->time, $time);

        if ($tx->confirmations == 0) return false;

        #echo "Ready";

        $input = ["txid"=>$utxos[0]->txid, "vout"=>$utxos[0]->vout];
        $output = ["Kfq52oVxADcsZXCXi7P2N5gxFVCkRzZNKr"=>number_format($utxos[0]->amount, 8, ".", "")];

        $rawtx = str_replace("\"", "", self::Node("createrawtransaction", [[$input], $output]));
        #var_dump($rawtx);

        
        

        $signtx = json_decode(self::Node("signrawtransaction", [$rawtx]));
        $tx = json_decode(self::Node("decoderawtransaction", [$signtx->hex]));

        $new_amount = (float)$output["Kfq52oVxADcsZXCXi7P2N5gxFVCkRzZNKr"] - $tx->size / 100000000 * 11;
        $dif = (float)$output["Kfq52oVxADcsZXCXi7P2N5gxFVCkRzZNKr"] - $new_amount;
        $dif = number_format($dif, 8, ".", "");
        var_dump($dif);

        $output["Kfq52oVxADcsZXCXi7P2N5gxFVCkRzZNKr"] = number_format($new_amount, 8, ".", "");

        $rawtx = str_replace("\"", "", self::Node("createrawtransaction", [[$input], $output]));
        var_dump($rawtx);

        $signtx = json_decode(self::Node("signrawtransaction", [$rawtx]));

        $tx = json_decode(self::Node("decoderawtransaction", [$signtx->hex]));
        var_dump($tx);

        $txid = json_decode(self::Node("sendrawtransaction", [$signtx->hex]));
        var_dump($txid);

        if (!$txid) return false;

        $progress = 1;

        $stmt = self::$_db->prepare("UPDATE swaps SET progress=:progress WHERE label=:label AND address=:address");
        $stmt->bindParam(":progress", $progress);
        $stmt->bindParam(":label", $swaps["label"]);
        $stmt->bindParam(":address", $swaps["address"]);
        $stmt->execute();
    }
}

# Bridge Address - Kfq52oVxADcsZXCXi7P2N5gxFVCkRzZNKr

$do = new cronBridge();
$do->CheckSwaps();