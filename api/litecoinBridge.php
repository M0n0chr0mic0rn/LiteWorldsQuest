<?php

class litecoinBridge
{
    private static $_db_username = "maria";
    private static $_db_passwort = "KerkerRocks22";
    private static $_db_host = "127.0.0.1";
    private static $_db_name = "Bridge";
    private static $_db;

    private static $_minSendingAmount = 0.000054;
    private static $_BridgeAddress = "MBTCfZJKcW5M2R5BfQPtcKM2J53fkFKy7p";

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

    function Node($method, $params = [], $rpc_url = "http://192.168.0.165:10000/wallet/") 
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

    function Grant($destination, $amount)
    {
        # Es wird immer die Bridgeaddresse benutzt um Token zu generieren oder zu wiederrufen
        # UTXOs der Bridgeaddresse abfragen
        $utxos = json_decode($this->Node("listunspent", [0, 999990999, [self::$_BridgeAddress]]));

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
        $amount = number_format($amount, 8, ".", "");

        # payload erstellen
        $payload = str_replace("\"", "", $this->Node("omni_createpayload_grant", [2147484191, $amount, ""]));

        # Outputs definieren
        $output[self::$_BridgeAddress] = number_format(($balance - self::$_minSendingAmount), 8, ".", "");
        $output[$destination] = number_format(self::$_minSendingAmount, 8, ".", "");

        # Raw Transaction erstellen
        $raw = str_replace("\"", "", $this->Node("createrawtransaction", [$input, $output]));
        var_dump($raw);

        # OP_RETURN hinzufügen
        $modraw = str_replace("\"", "", $this->Node("omni_createrawtx_opreturn", [$raw, $payload]));
        var_dump($modraw);

        # Transaktion signieren
        $signed = json_decode($this->Node("signrawtransactionwithwallet", [$modraw]));

        # Transaktion auslesen
        $tx = json_decode($this->Node("decoderawtransaction", [$signed->hex]));

        # Transaktionsgewicht berechnen
        $weight = $tx->vsize * 3 / 100000000;

        # Netzwerkgebühr für die Transaktion von der Bridgeaddresse abziehen
        $output[self::$_BridgeAddress] = number_format(((float)$output[self::$_BridgeAddress] - $weight), 8, ".", "");

        # Raw Transaction erstellen
        $raw = str_replace("\"", "", $this->Node("createrawtransaction", [$input, $output]));

        # OP_RETURN hinzufügen
        $modraw = str_replace("\"", "", $this->Node("omni_createrawtx_opreturn", [$raw, $payload]));

        # Transaktion signieren
        $signed = json_decode($this->Node("signrawtransactionwithwallet", [$modraw]));
        var_dump($signed);
        # Transaktion senden
        $txid = $this->Node("sendrawtransaction", [$signed->hex]);
    }

    function Revoke($destination, $amount)
    {
        # Es wird immer die Bridgeaddresse benutzt um Token zu generieren oder zu wiederrufen
        # UTXOs der Bridgeaddresse abfragen
        $utxos = json_decode($this->Node("listunspent", [0, 999990999, [self::$_BridgeAddress]]));

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
        $amount = number_format($amount, 8, ".", "");

        # payload erstellen
        $payload = str_replace("\"", "", $this->Node("omni_createpayload_revoke", [2147484191, $amount, ""]));

        # Output definieren
        $output[self::$_BridgeAddress] = number_format($balance, 8, ".", "");

        # Raw Transaction erstellen
        $raw = str_replace("\"", "", $this->Node("createrawtransaction", [$input, $output]));

        # OP_RETURN hinzufügen
        $modraw = str_replace("\"", "", $this->Node("omni_createrawtx_opreturn", [$raw, $payload]));

        # Transaktion signieren
        $signed = json_decode($this->Node("signrawtransactionwithwallet", [$modraw]));

        # Transaktion auslesen
        $tx = json_decode($this->Node("decoderawtransaction", [$signed->hex]));

        # Transaktionsgewicht berechnen
        $weight = $tx->vsize * 3 / 100000000;

        # Netzwerkgebühr für die Transaktion von der Bridgeaddresse abziehen
        $output[self::$_BridgeAddress] = number_format(((float)$output[self::$_BridgeAddress] - $weight), 8, ".", "");

        # Raw Transaction erstellen
        $raw = str_replace("\"", "", $this->Node("createrawtransaction", [$input, $output]));

        # OP_RETURN hinzufügen
        $modraw = str_replace("\"", "", $this->Node("omni_createrawtx_opreturn", [$raw, $payload]));

        # Transaktion signieren
        $signed = json_decode($this->Node("signrawtransactionwithwallet", [$modraw]));

        # Transaktion senden
        #$txid = $this->Node("sendrawtransaction", [$signed->hex]);
        #var_dump($txid);
    }

    function test()
    {
        $utxos = json_decode($this->Node("listunspent", [0, 999990999, ["MBTCfZJKcW5M2R5BfQPtcKM2J53fkFKy7p"]]));

        $input = [];
        $amount = 0;
        foreach ($utxos as $key => $utxo)
        {
            $input[$key] = ["txid" => $utxo->txid, "vout" => $utxo->vout];
            $amount += $utxo->amount;
        }

        $output = [];
        $output["MBTCfZJKcW5M2R5BfQPtcKM2J53fkFKy7p"] = $amount;

        $payload = str_replace("\"", "", $this->Node("omni_createpayload_grant", [2147484191, "1.0", ""]));
        #var_dump($payload);

        $raw = str_replace("\"", "", $this->Node("createrawtransaction", [$input, $output]));
        $modraw = str_replace("\"", "", $this->Node("omni_createrawtx_opreturn", [$raw, $payload]));
        $signed = json_decode($this->Node("signrawtransactionwithwallet", [$modraw]));
        $tx = json_decode($this->Node("decoderawtransaction", [$signed->hex]));
        $weight = $tx->vsize * 3 / 100000000;

        $output["MBTCfZJKcW5M2R5BfQPtcKM2J53fkFKy7p"] = number_format(($amount - $weight), 8, ".", "");
        #var_dump($output);
        $raw = str_replace("\"", "", $this->Node("createrawtransaction", [$input, $output]));
        #var_dump($raw);
        $modraw = str_replace("\"", "", $this->Node("omni_createrawtx_opreturn", [$raw, $payload]));
        #var_dump($modraw);
        $signed = json_decode($this->Node("signrawtransactionwithwallet", [$modraw]));
        #var_dump($signed);
        $txid = $this->Node("sendrawtransaction", [$signed->hex]);
        var_dump($txid);
    }
}

# Bridge Address - MBTCfZJKcW5M2R5BfQPtcKM2J53fkFKy7p

$bridge = new litecoinBridge();
$bridge->Grant("M85rZY37m5m5YxKKqbEhLVWEZYz8M9rz3L", 1.0);