<?php

class Bridge
{
    private static $_db_username = "maria";
    private static $_db_passwort = "KerkerRocks22";
    private static $_db_host = "127.0.0.1";
    private static $_db_name = "Bridge";
    private static $_db;

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


    private static function GetAddress($index)
    {
        $result = [];

        $result["label"] = "BRIDGE-LITECOIN-OMNILITE-" . $index;
        $result["address"] = json_decode(self::Node("getaddressesbyaccount", [$result["label"]]));

        if ($result["address"] == null) $result["address"] = str_replace("\"", "", self::Node("getnewaddress", [$result["label"]]));
        else $result["address"] = $result["address"][0];

        return $result;
    }

    private static function CheckSlot($label)
    {
        $stmt = self::$_db->prepare("SELECT * FROM swaps WHERE label=:label LIMIT 1");
        $stmt->bindParam(":label", $label);
        $stmt->execute();

        if ($stmt->rowCount() == 0) return true;
        return false;
    }

    private static function CheckAddress($address)
    {
        $stmt = self::$_db->prepare("SELECT * FROM swaps WHERE address=:address LIMIT 1");
        $stmt->bindParam(":address", $address);
        $stmt->execute();

        if ($stmt->rowCount() == 0) return true;
        return false;
    }

    private static function CheckAddressOUT($address)
    {
        $stmt = self::$_db->prepare("SELECT * FROM swapsOUT WHERE address=:address LIMIT 1");
        $stmt->bindParam(":address", $address);
        $stmt->execute();

        if ($stmt->rowCount() == 0) return true;
        return false;
    }

    public static function CreateSwap($address, $amount, $index = 0)
    {
        $echo = [];

        if (!self::CheckAddress($address))
        {
            $echo["answer"] = "Swap with this Address already exists!";
            $echo["bool"] = false;

            echo json_encode($echo, JSON_PRETTY_PRINT);
            exit;
        }

        do {
            $response = self::GetAddress($index);
            if (self::CheckSlot($response["label"])) break;
            $index++;

            if ($index > 50)
            {
                $echo["answer"] = "No free Slot available!";
                $echo["bool"] = false;

                echo json_encode($echo, JSON_PRETTY_PRINT);
                exit;
            }
        } while (true);
        
        $time = time() + 60 * 60;

        $stmt = self::$_db->prepare("INSERT INTO swaps (label, address, amount, time) VALUES (:label, :address, :amount, :time)");
        $stmt->bindParam(":label", $response["label"]);
        $stmt->bindParam(":address", $address);
        $stmt->bindParam(":amount", $amount);
        $stmt->bindParam(":time", $time);
        $stmt->execute();

        $echo["answer"] = "Swap prepared";
        $echo["pay_address"] = $response["address"];
        $echo["receive_address"] = $address;
        $echo["amount"] = $amount;
        $echo["bool"] = true;
        echo json_encode($echo, JSON_PRETTY_PRINT);

        exit;
    }

    public static function CreateOUT($address, $amount)
    {
        $echo = [];

        if (!self::CheckAddressOUT($address))
        {
            $echo["answer"] = "Swap with this Address already exists!";
            $echo["bool"] = false;

            echo json_encode($echo, JSON_PRETTY_PRINT);
            exit;
        }
        
        $time = time() + 60 * 60;

        $stmt = self::$_db->prepare("INSERT INTO swapsOUT (address, amount, time) VALUES (:address, :amount, :time)");
        $stmt->bindParam(":address", $address);
        $stmt->bindParam(":amount", $amount);
        $stmt->bindParam(":time", $time);
        $stmt->execute();

        $echo["answer"] = "Swap prepared";
        $echo["pay_address"] = self::$_OmniAddress;
        $echo["receive"] = $address;
        $echo["amount"] = $amount;
        $echo["bool"] = true;
        echo json_encode($echo, JSON_PRETTY_PRINT);

        exit;
    }
}

# Bridge Address - Kfq52oVxADcsZXCXi7P2N5gxFVCkRzZNKr