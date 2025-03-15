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
    private static $_PropertyID = 2147484191;
    private static $_BridgeKey = -0.00025;

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

    private static function _bridgeOpen($origin)
    {
        $utxos = json_decode(self::Omni("listunspent", [0, 999999999, [self::$_OmniAddress]]));

        # Rückgabewert
        $result;

        $open = false;
        foreach ($utxos as $key => $utxo)
        {
            $tx = json_decode(self::Omni("gettransaction", [$utxo->txid]));
            $tx = json_decode(self::Omni("decoderawtransaction", [$tx->hex]));
            #var_dump("TX#".$key);
            #var_dump($tx);

            foreach ($tx->vout as $key => $output) {
                #var_dump($output);
                if (isset($output->scriptPubKey->addresses)) {
                    if ($output->scriptPubKey->addresses[0] == self::$_OmniAddress && self::$_BridgeKey <= $output->value)
                    {
                        foreach ($tx->vin as $key => $rawInput) {
                            $prevTX = json_decode(self::Omni("getrawtransaction", [$rawInput->txid, 1]));
                            #var_dump($prevTX);

                            if ($prevTX->vout[$rawInput->vout]->scriptPubKey->addresses[0] == $origin)
                            {
                                $open = true;
                                $result = ["txid" => $utxo->txid, "vout" => $utxo->vout, "amount" => $utxo->amount];
                            }
                        }
                    }
                }
            }
        }

        if ($open) return $result;
        return false;
    }

    private static function Grant($destination, $amount, $utxo)
    {
        # Es wird immer die Bridgeaddresse benutzt um Token zu generieren oder zu wiederrufen
        # UTXOs der Bridgeaddresse abfragen

        # Input & Output Array erstellen
        $input = [["txid" => $utxo["txid"], "vout" => $utxo["vout"]]];
        $output = [];
        
        $balance = number_format($utxo["amount"], 8, ".", "");
        $amount = number_format((float)$amount - 0.00025, 8, ".", "");

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

    private static function Revoke($amount)
    {
        # Es wird immer die Bridgeaddresse benutzt um Token zu generieren oder zu wiederrufen
        # UTXOs der Bridgeaddresse abfragen
        $utxos = json_decode(self::Omni("listunspent", [0, 999990999, [self::$_OmniAddress]]));

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
        $payload = str_replace("\"", "", self::Omni("omni_createpayload_revoke", [2147484191, $amount, ""]));

        # Output definieren
        $output[self::$_OmniAddress] = number_format($balance, 8, ".", "");

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
        var_dump("Litecoin sagt", $txid);

        self::_setProgress(2);

        if ($txid) return true;
        return false;
    }

    private static function Lock($input, $output)
    {
        $raw = str_replace("\"", "", self::Node("createrawtransaction", [$input, $output]));
        $signed = json_decode(self::Node("signrawtransaction", [$raw]));
        $tx = json_decode(self::Node("decoderawtransaction", [$signed->hex]));

        $output[self::$_BridgeAddress] = number_format(((float)$output[self::$_BridgeAddress] - (($tx->size +1) / 10000000)), 8, ".", "");
        $raw = str_replace("\"", "", self::Node("createrawtransaction", [$input, $output]));
        $signed = json_decode(self::Node("signrawtransaction", [$raw]));
        #var_dump("Kotia sagt", $signed);
        #return $signed->complete;
        $txid = json_decode(self::Node("sendrawtransaction", [$signed->hex]));
        var_dump(["KotiaTX"=>$txid]);
        if ($txid) return true;
        return false;
    }

    private static function _setProgress($progress) {
        $stmt = self::$_db->prepare("UPDATE swapsOUT SET progress=:progress WHERE address=:address");
        $stmt->bindParam(":progress", $progress);
        $stmt->bindParam(":address", $swap["address"]);
        $stmt->execute();
    }

    private static function DeleteSwap($swap)
    {
        $stmt = self::$_db->prepare("DELETE FROM swaps WHERE label=:label AND address=:address");
        $stmt->bindParam(":label", $swap["label"]);
        $stmt->bindParam(":address", $swap["address"]);
        $stmt->execute();
    }

    private static function DeleteSwapOUT($swap)
    {
        $stmt = self::$_db->prepare("DELETE FROM swapsOUT WHERE address=:address");
        $stmt->bindParam(":address", $swap["address"]);
        $stmt->execute();
    }

    public static function CheckSwapsIN()
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

        #var_dump($swaps);

        foreach ($swaps as $key => $swap)
        {
            # Wurde die Litecoin Fee schon bezahlt?
            $inputOmni = self::_bridgeOpen($swap["address"]);
            if ($inputOmni) {
                # für jedes Swap die Adresse und die UTXOs auf Kotia auslesen
                $address = json_decode(self::Node("getaddressesbyaccount", [$swap["label"]]))[0];
                $utxos = json_decode(self::Node("listunspent", [1, 999999999, [$address]]));

                # Input und Output für Kotia definieren
                $input = [];
                $output = [];

                $balance = 0;
                foreach ($utxos as $key => $utxo)
                {
                    $input[$key] = ["txid" => $utxo->txid, "vout" => $utxo->vout];
                    $balance += $utxo->amount;
                }
                $balance = number_format($balance, 8, ".", "");
                #var_dump($balance);

                if ($swap["amount"] <= $balance)
                {
                    $output[self::$_BridgeAddress] = $balance;

                    if (self::Lock($input, $output))
                    {
                        if (self::Grant($swap["address"], $swap["amount"], $inputOmni)); self::DeleteSwap($swap);
                    }
                } else {
                    echo "Waiting for payment";
                }
            } else {
                echo "Missing BridgeKey";
            }
        }
    }

    public static function CheckSwapsOut()
    {
        # Zeit und progress definieren
        $time = time() + 60;
        $progress = 0;

        # Swaps abfragen
        $stmt = self::$_db->prepare("SELECT * FROM swapsOUT WHERE progress = :progress AND time > :time");
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
            #$address = json_decode(self::Node("getaddressesbyaccount", [$swap["label"]]))[0];
            $utxos = json_decode(self::Node("listunspent", [0, 999999999, [self::$_BridgeAddress]]));
            $token = json_decode(self::Omni("omni_getbalance", [self::$_OmniAddress, self::$_PropertyID]));

            if ((float)$token->balance >= (float)$swap["amount"])
            {
                # Input und Output definieren
                $input = [];
                $output = [];

                $balance = 0;
                foreach ($utxos as $key => $utxo)
                {
                    $input[$key] = ["txid" => $utxo->txid, "vout" => $utxo->vout];
                    $balance += $utxo->amount;
                }

                if ($balance > ((float)$swap["amount"] +1)) {
                    $output[self::$_BridgeAddress] = number_format($balance - (float)$swap["amount"], 8, ".", "");
                    $output[$swap["address"]] = $swap["amount"];

                    if (self::Lock($input, $output))
                    {
                        if (self::Revoke($swap["amount"])); self::DeleteSwapOUT($swap);
                    }
                } else {
                    echo "Bridge Holdings Low";
                }
            } else {
                echo "Waiting for Tokens";
            }
        }
    }
}

$do = new cronBridge();
$do->CheckSwapsIN();
$do->CheckSwapsOUT();