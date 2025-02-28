<?php

class DEXpay
{
    private static $_db_username = "maria";
    private static $_db_passwort = "KerkerRocks22";
    private static $_db_host = "127.0.0.1";
    private static $_db_name = "LiteWorldsQuest";
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

    function Node($RETURN, $method, $params = array(), $wallet = null, $rpc_url = "http://192.168.0.165:10000/") 
    {
        if ($wallet) $rpc_url .= "wallet/" . urlencode($wallet);  # Wallet-Namen an die URL anhängen

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

    function go($RETURN)
    {
        $stmt = self::$_db->prepare("SELECT * FROM DEXpay");
        $stmt->execute();

        var_dump($stmt);

        if ($stmt->rowCount() == 0) echo "{\"error\": \"No DEXpay data found\"}";

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($result as $key => $value)
        {
            $DEX = json_decode(self::Node($RETURN, "omni_getactivedexsells", [$value["destination"]]));
            foreach ($DEX as $key => $listing) 
            {
                foreach ($listing->accepts as $key => $accept)
                {
                    if ($accept->buyer == $value["origin"])
                    {
                        $txhex = self::Node($RETURN, "omni_senddexpay", [$value["origin"], $value["destination"], $value["property"], $accept->amounttopay], $value["name"]);

                        if ($txhex)
                        {
                            $stmt = self::$_db->prepare("DELETE FROM DEXpay WHERE name=:name AND origin=:origin AND destination=:destination AND property=:property");
                            $stmt->bindParam(":name", $value["name"]);
                            $stmt->bindParam(":origin", $value["origin"]);
                            $stmt->bindParam(":destination", $value["destination"]);
                            $stmt->bindParam(":property", $value["property"]);
                            $stmt->execute();

                            if ($stmt->rowCount() == 1) echo "{\"success\": \"DEXrequest payed and record deleted\"}";
                        }
                    }
                }
            }
        }
    }
}

$RETURN = "";
$DEXpay = new DEXpay();
$DEXpay->go($RETURN);