<?php

class Maria {
    private static $_db_username = "maria";
    private static $_db_passwort = "KerkerRocks22";
    private static $_db_host = "127.0.0.1";
    private static $_db_name = "LiteWorldsQuest";

    public static $_db;

    public function __construct(){
        try{
            self::$_db = new PDO("mysql:host=" . self::$_db_host . ";dbname=" . self::$_db_name, self::$_db_username, self::$_db_passwort);
        }
        catch(PDOException $e){
            echo "<br>Maria Error, maybe drunk?<br>".$e;
            die();
        }
    }

    public static function Litecoin($Kirby, $action){
        switch ($action) {
            case "send":
                $keyring = $Kirby->Key->Craft2FA("ltcsend");

                $stmt = self::$_db->prepare("INSERT INTO ltcsend (name, time, copper, jade, crystal, ip, txhex) VALUES (:name, :time, :copper, :jade, :crystal, :ip, :txhex)");
                $stmt->bindParam(":name", $Kirby->user["name"]);
                $stmt->bindParam(":time", $Kirby->send["expire"]);
                $stmt->bindParam(":copper", $keyring->copper);
                $stmt->bindParam(":jade", $keyring->jade);
                $stmt->bindParam(":crystal", $keyring->crystal);
                $stmt->bindParam(":ip", $Kirby->ip);
                $stmt->bindParam(":txhex", $Kirby->send["signedtxhex"]->hex);
                $stmt->execute();

                if ($stmt->rowCount() != 1) $Kirby->Fail("Could not insert action into database, sorry");
                $Kirby->Response("sending Litecoin prepared, I'm out of here");
            break;
            
            default:
                # code...
            break;
        }
    }
}