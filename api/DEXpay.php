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
        self::$_db = new mysqli(self::$_db_host, self::$_db_username, self::$_db_passwort, self::$_db_name);
        if (self::$_db->connect_error) {
            die("Connection failed: " . self::$_db->connect_error);
        }
    }

    function Node($method, $params = array(), $wallet = null, $rpc_url = "http://192.168.0.165:10000/") 
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

    function go()
    {
        $stmt = self::$_db->prepare("SELECT * FROM DEXpay");
        $stmt->execute();

        if ($stmt->rowCount() == 0) echo "{\"error\": \"No DEXpay data found\"}";

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($result as $key => $value)
        {
            $DEX = Node("omni_getactivedexsells", [$value["destination"]]);
            var_dump($DEX);
        }
    }
}