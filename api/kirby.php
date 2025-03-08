<?php
require_once("telegram.php");
require_once("key.php");

class Kirby
{
    public $Telegram;
    public $Key;

    public $Star = [];
    public $API = "https://liteworlds.quest/?method=";

    public static $_ServiceFeeFoundation = "MP2bKNDoDGXmG4j5V4aaTNqXhP9ZybLGnk";
    public static $_ServiceFeeFaucet = "MCtYmUDUvjCatos2whjAsPaBr2a1nwA1tG";

    public static $_DustAmount = 0.000054;      # minimum amount to send
    public static $_NetworkFee = 3;             # lits/vbyte
    public static $_ServiceFee = 0.00025;       # LiteWorldsQuest Service Fee
    public static $_LitecoinServiceFee = [      # Array with Outputs for Service Fee
        "MP2bKNDoDGXmG4j5V4aaTNqXhP9ZybLGnk",   # Foundation
        "MCtYmUDUvjCatos2whjAsPaBr2a1nwA1tG"    # Faucet
    ];

    public function __construct(){
        $this->Telegram = new Telegram;
        $this->Key = new Key;

        try{
            $this->Star["response"] = ["inhale"];
            $this->Star["error"] = "";
            $this->Star["bool"] = false;
            $this->Star["ip"] = $_SERVER["REMOTE_ADDR"];
            $this->Star["security"] = [];
            $this->Star["user"] = [];
            $this->Star["action"] = $_GET["method"];
            $this->Star["send"] = [];
        }catch(PDOException $e){
            echo "<br>Star Setup Error<br>".$e;
            exit;
        }
    }

    public static function Litecoin($method, $params = array(), $wallet = null){
        $rpc_url = "http://192.168.0.165:10000/";
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

    # Abbruch und Anzeige des Vorgangs
    public function Fail($error){
        $this->Star["error"] = $error;
        echo json_encode($this->Star, JSON_PRETTY_PRINT);
        exit;
    }

    # Hinzufügen von Statusmeldung
    public function Response($response){
        $this->Star["response"][count($this->Star["response"])] = $response;
    }

    public function Pretty(){
        if ($this->Star["action"] != "login") unset($this->Star["authkey"]);
        if ($this->Star["action"] != "get") unset($this->Star["user"]);
        unset($this->Star["security"]);
        unset($this->Star["error"]);
        unset($this->Star["ip"]);
    }

    public function Spit(){
        $this->Star["bool"] = true;
        echo json_encode($this->Star, JSON_PRETTY_PRINT);
        exit;
    }
}
