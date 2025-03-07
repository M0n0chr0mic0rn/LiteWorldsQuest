<?php

class cronBridge
{
    private static $_db_username = "maria";
    private static $_db_passwort = "KerkerRocks22";
    private static $_db_host = "127.0.0.1";
    private static $_db_name = "Bridge";
    private static $_db;

    private static $_minSendingAmount = 0.000054;
    private static $_BridgeAddress = "Kfq52oVxADcsZXCXi7P2N5gxFVCkRzZNKr";
    private static $_OmniAddress = "MBTCfZJKcW5M2R5BfQPtcKM2J53fkFKy7p";

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

    private static function Omni($method, $params = [], $rpc_url = "http://192.168.0.165:10000/wallet/") 
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
        //var_dump($json_data);

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

    private static function Grant($destination, $amount)
    {
        # Es wird immer die Bridgeaddresse benutzt um Token zu generieren oder zu wiederrufen
        # UTXOs der Bridgeaddresse abfragen
        $utxos = json_decode(self::Omni("listunspent", [0, 999999999, [self::$_OmniAddress]]));

        # Input & Output Array erstellen
        $input = [];
        $output = [];

        # Gesamtbetrag der UTXOs berechnen
        $balance = 0;
        foreach ($utxos as $key => $utxo)
        {
            $input[$key] = ["txid" => $utxo->txid, "vout" => $utxo->vout];
            $balance += $utxo->amount;
        }

        # amount muss als String übergeben werden
        $balance = number_format($balance, 8, ".", "");

        # payload erstellen
        $payload = str_replace("\"", "", self::Omni("omni_createpayload_grant", [2147484191, $amount, ""]));

        # Outputs definieren
        $output[self::$_OmniAddress] = number_format(($balance - self::$_minSendingAmount), 8, ".", "");
        $output[$destination] = number_format(self::$_minSendingAmount, 8, ".", "");

        # Raw Transaction erstellen
        $raw = str_replace("\"", "", self::Omni("createrawtransaction", [$input, $output]));

        # OP_RETURN hinzufügen
        $modraw = str_replace("\"", "", self::Omni("omni_createrawtx_opreturn", [$raw, $payload]));

        # Transaktion signieren
        $signed = json_decode(self::Omni("signrawtransactionwithwallet", [$modraw]));

        # Transaktion auslesen
        $tx = json_decode(self::Omni("decoderawtransaction", [$signed->hex]));

        # Transaktionsgewicht berechnen
        $weight = $tx->vsize * 3 / 100000000;

        # Netzwerkgebühr für die Transaktion von der Bridgeaddresse abziehen
        $output[self::$_OmniAddress] = number_format(((float)$output[self::$_OmniAddress] - $weight), 8, ".", "");

        # Raw Transaction erstellen
        $raw = str_replace("\"", "", self::Omni("createrawtransaction", [$input, $output]));

        # OP_RETURN hinzufügen
        $modraw = str_replace("\"", "", self::Omni("omni_createrawtx_opreturn", [$raw, $payload]));

        # Transaktion signieren
        $signed = json_decode(self::Omni("signrawtransactionwithwallet", [$modraw]));

        # Transaktion senden
        $txid = self::Omni("sendrawtransaction", [$signed->hex]);
        var_dump(["LitecoinTX"=>$txid]);
        #var_dump("Litecoin sagt", $signed);

        if ($txid) return true;
        return false;
    }

    private static function Lock($input, $output)
    {
        $raw = str_replace("\"", "", self::Node("createrawtransaction", [$input, $output]));
        $signed = json_decode(self::Node("signrawtransaction", [$raw]));
        $tx = json_decode(self::Node("decoderawtransaction", [$signed->hex]));

        $output[self::$_BridgeAddress] = number_format(((float)$output[self::$_BridgeAddress] - $tx->size / 10000000), 8, ".", "");
        $raw = str_replace("\"", "", self::Node("createrawtransaction", [$input, $output]));
        $signed = json_decode(self::Node("signrawtransaction", [$raw]));
        $txid = json_decode(self::Node("sendrawtransaction", [$signed->hex]));
        var_dump(["KotiaTX"=>$txid]);

        if ($txid) return true;
        return false;

        #var_dump("Kotia sagt", $signed);
    }

    public static function CheckSwaps()
    {
        # Zeit und progress definieren
        $time = time() + 60;
        $progress = 0;

        # Swaps abfragen
        $stmt = self::$_db->prepare("SELECT * FROM swaps WHERE progress = :progress AND time > :time");
        $stmt->bindParam(":progress", $progress);
        $stmt->bindParam(":time", $time);
        $stmt->execute();

        # Wenn keine Swaps vorhanden sind, wird die Funktion beendet
        if ($stmt->rowCount() < 1) return false;

        # Swaps zwischenspeichern
        $swaps = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($swaps as $key => $swap)
        {
            # für jedes Swap die Adresse und die UTXOs auslesen
            $address = json_decode(self::Node("getaddressesbyaccount", [$swap["label"]]))[0];
            $utxos = json_decode(self::Node("listunspent", [1, 999999999, [$address]]));

            # Input und Output definieren
            $input = [];
            $output = [];

            $balance = 0;

            foreach ($utxos as $key => $utxo)
            {
                $input[$key] = ["txid" => $utxo->txid, "vout" => $utxo->vout];
                $balance += $utxo->amount;

                if (number_format($balance, 8, ".", "") != $swap["amount"]) return false;

                $output[self::$_BridgeAddress] = number_format($balance, 8, ".", "");

                if (self::Lock($input, $output))
                {
                    if (self::Grant($swap["address"], $swap["amount"])) self::DeleteSwap($swap);
                }
            }
        }

        
        #var_dump($swaps);
        #$address = json_decode(self::Node("getaddressesbyaccount", [$swaps["label"]]))[0];
        #var_dump($address);

        
        

        #var_dump($utxos);

        
        

        #$tx = json_decode(self::Node("getrawtransaction", [$utxos[0]->txid, 1]));
        
        
        #var_dump($tx->time, $time);

        #if ($tx->confirmations == 0) return false;

        #echo "Ready";

        #$input = ["txid"=>$utxos[0]->txid, "vout"=>$utxos[0]->vout];
        #$output = ["Kfq52oVxADcsZXCXi7P2N5gxFVCkRzZNKr"=>number_format($utxos[0]->amount, 8, ".", "")];

        #$rawtx = str_replace("\"", "", self::Node("createrawtransaction", [[$input], $output]));
        #var_dump($rawtx);

        
        

        #$signtx = json_decode(self::Node("signrawtransaction", [$rawtx]));
        #$tx = json_decode(self::Node("decoderawtransaction", [$signtx->hex]));

        #$new_amount = (float)$output["Kfq52oVxADcsZXCXi7P2N5gxFVCkRzZNKr"] - $tx->size / 100000000 * 11;
        #$dif = (float)$output["Kfq52oVxADcsZXCXi7P2N5gxFVCkRzZNKr"] - $new_amount;
        #$dif = number_format($dif, 8, ".", "");
        #var_dump($dif);

        #$output["Kfq52oVxADcsZXCXi7P2N5gxFVCkRzZNKr"] = number_format($new_amount, 8, ".", "");

        #$rawtx = str_replace("\"", "", self::Node("createrawtransaction", [[$input], $output]));
        #var_dump($rawtx);

        #$signtx = json_decode(self::Node("signrawtransaction", [$rawtx]));

        #$tx = json_decode(self::Node("decoderawtransaction", [$signtx->hex]));
        #var_dump($tx);

        #$txid = json_decode(self::Node("sendrawtransaction", [$signtx->hex]));
        #var_dump($txid);

        #if (!$txid) return false;

        #$progress = 1;

        #$stmt = self::$_db->prepare("UPDATE swaps SET progress=:progress WHERE label=:label AND address=:address");
        #$stmt->bindParam(":progress", $progress);
        #$stmt->bindParam(":label", $swaps["label"]);
        #$stmt->bindParam(":address", $swaps["address"]);
        #$stmt->execute();
    }
}

# Bridge Address - Kfq52oVxADcsZXCXi7P2N5gxFVCkRzZNKr

$do = new cronBridge();
$do->CheckSwaps();