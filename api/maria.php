<?php

class Maria {
    private static $TimeWindow = 60 *3;     # 3 Minuten

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

    public function NameAvailable($name)
    {
        $stmt = self::$_db->prepare("SELECT * FROM user WHERE name=:name LIMIT 1");
        $stmt->bindParam(":name", $name);
        $stmt->execute();
        if ($stmt->rowCount() > 0) return false;

        $stmt = self::$_db->prepare("SELECT * FROM register WHERE name=:name LIMIT 1");
        $stmt->bindParam(":name", $name);
        $stmt->execute();
        if ($stmt->rowCount() > 0) return false;

        return true;
    }

    public function EmailAvailable($email)
    {
        $stmt = self::$_db->prepare("SELECT * FROM user WHERE email=:email LIMIT 1");
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        if ($stmt->rowCount() > 0) return false;
        
        $stmt = self::$_db->prepare("SELECT * FROM register WHERE email=:email LIMIT 1");
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        if ($stmt->rowCount() > 0) return false;

        $stmt = self::$_db->prepare("SELECT * FROM _update WHERE value=:email LIMIT 1");
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        if ($stmt->rowCount() > 0) return false;

        return true;
    }

    public function TelegramAvailable($telegram)
    {
        $stmt = self::$_db->prepare("SELECT * FROM user WHERE BINARY telegram=:telegram LIMIT 1");
        $stmt->bindParam(":telegram", $telegram);
        $stmt->execute();
        if ($stmt->rowCount() > 0) return false;
        
        $stmt = self::$_db->prepare("SELECT * FROM register WHERE BINARY telegram=:telegram LIMIT 1");
        $stmt->bindParam(":telegram", $telegram);
        $stmt->execute();
        if ($stmt->rowCount() > 0) return false;

        $stmt = self::$_db->prepare("SELECT * FROM _update WHERE BINARY value=:telegram LIMIT 1");
        $stmt->bindParam(":telegram", $telegram);
        $stmt->execute();
        if ($stmt->rowCount() > 0) return false;

        return true;
    }

    private static function Security($Kirby){
        $time = time() + self::$TimeWindow;

        switch ($Kirby->Star["action"]){
            case "login":
                $keyring = $Kirby->Key->Craft2FA($Kirby->Star["action"]);

                $Kirby->Star["security"]["link"] = $Kirby->API . "execute&action=login&name=" . $Kirby->Star["user"]["name"] . "&copper=" . $keyring->copper . "&jade=" . $keyring->jade . "&crystal=" . $keyring->crystal;
                $Kirby->Star["security"]["message"] = "LiteWorlds.Quest Network - Login";
                $Kirby->Star["security"]["text"] = "You are Login into your Account User: ".$Kirby->Star["user"]["name"];
                $Kirby->Star["authkey"] = $Kirby->Key->CraftAuth();

                # Datensatz in prepare Tabelle einfügen
                $stmt = self::$_db->prepare("INSERT INTO login (name, authkey, copper, jade, crystal, ip, time) VALUES (:name, :authkey, :copper, :jade, :crystal, :ip, :time)");
                $stmt->bindParam(":name", $Kirby->Star["user"]["name"]);
                $stmt->bindParam(":authkey", $Kirby->Star["authkey"]);
                $stmt->bindParam(":copper", $keyring->copper);
                $stmt->bindParam(":jade", $keyring->jade);
                $stmt->bindParam(":crystal", $keyring->crystal);
                $stmt->bindParam(":ip", $Kirby->Star["ip"]);
                $stmt->bindParam(":time", $time);
                $stmt->execute();

                if ($stmt->rowCount() == 0) $Kirby->Fail("login failed");
                $Kirby->Response("login prepared");
            break;

            case "update":
                $keyring = $Kirby->Key->Craft2FA("_update");

                $Kirby->Star["security"]["link"] = $Kirby->API . "execute&action=update&name=" . $Kirby->Star["user"]["name"] . "&copper=" . $keyring->copper . "&jade=" . $keyring->jade . "&crystal=" . $keyring->crystal;
                $Kirby->Star["security"]["message"] = "LiteWorlds.Quest Network - Data Update";
                $Kirby->Star["security"]["text"] = "You are updating your Account User: ".$Kirby->Star["user"]["name"];
                $Kirby->Star["authkey"] = $Kirby->Key->CraftAuth();

                $stmt = self::$_db->prepare("INSERT INTO _update VALUES (:name, :key, :value, :copper, :jade, :crystal, :ip, :time)");
                $stmt->bindParam(":name", $Kirby->Star["user"]["name"]);
                $stmt->bindParam(":key", $Kirby->Star["update"]["key"]);
                $stmt->bindParam(":value", $Kirby->Star["update"]["value"]);
                $stmt->bindParam(":copper", $keyring->copper);
                $stmt->bindParam(":jade", $keyring->jade);
                $stmt->bindParam(":crystal", $keyring->crystal);
                $stmt->bindParam(":ip", $Kirby->Star["ip"]);
                $stmt->bindParam(":time", $time);
                $stmt->execute();

                if ($stmt->rowCount() == 0) $Kirby->Fail("login failed");
                $Kirby->Response("update prepared");
            break;
            
            default:
                # code...
            break;
        }

        switch ($Kirby->Star["user"]["security"]){
            case "telegram": $Kirby->Telegram->Send($Kirby); break;
            case "email": self::EmailSend($Kirby); break;
        }
    }

    private static function EmailSend($Kirby){
        // Nachricht
        $message = "
            <html>
                <body style=\"background-color: black; color: deepskyblue;\">
                <table align=\"center\">
                <tr>
                    <td><img src=\"https://ordinalslite.com/content/4749f65fc682b103d9a221b8bf3370c97583c6c530eaee4d0f27f71bfb966fcfi0\" style=\"height:250px; margin-left:auto; margin-right:auto; display:block;\"></td>
                </tr>

                <tr>
                    <td><p align=\"center\" style=\"color:deepskyblue;\">".$Kirby->Star["security"]["text"]."</p></td>
                </tr>
                <tr>
                    <td>
                        <p align=\"center\" style=\"color:crimson;\">Please sign your Action</p>
                        <a target=\"_blank\" rel=\"noopener noreferrer\" href=".$Kirby->Star["security"]["link"].">
                            <button style=\"font-size:24px;width:100%;color:deepskyblue;background-color:transparent;cursor:crosshair;border:3px solid deepskyblue;border-radius:7px;\">SIGN</button>
                        </a>
                        <p align=\"center\" style=\"color:crimson;\">Time: ".time()."</p>
                    </td>
                </tr>
                </table>
                </body>
            </html>
        ";

        // Email Headers konfigurieren
        $headers = 
            "From: Security <security@liteworlds.quest>" . "\r\n" .
            "Reply-To: Security <security@liteworlds.quest>" . "\r\n" .
            "MIME-Version: 1.0" . "\r\n" .
            "Content-type: text/html; charset=iso-8859-1" . "\r\n" .
            "X-Mailer: PHP/" . phpversion()
        ;

        if (!mail($Kirby->Star["user"]["email"], $Kirby->Star["security"]["message"], $message, $headers)) $Kirby->Fail("Email send failed");
        $Kirby->Response("message to email send");
    }

    public static function Register($Kirby)
    {
        $time = time() + (self::$TimeWindow *2);
        $keyring = $Kirby->Key->Craft2FA("register");

        $Kirby->Star["security"]["link"] = $Kirby->API . "execute&action=register&name=" . $Kirby->Star["user"]["name"] . "&copper=" . $keyring->copper . "&jade=" . $keyring->jade . "&crystal=" . $keyring->crystal;
        $Kirby->Star["security"]["message"] = "LiteWorlds.Quest Network - Registration";
        $Kirby->Star["security"]["text"] = "You create an Account User: ".$Kirby->Star["user"]["name"];
        
        $a = "telegram";
        if (isset($Kirby->Star["user"]["email"])) $a = "email";

        $stmt = self::$_db->prepare("INSERT INTO register (name, pass, $a, copper, jade, crystal, ip, time) VALUES (:name, :pass, :security, :copper, :jade, :crystal, :ip, :time)");
        $stmt->bindParam(":name", $Kirby->Star["user"]["name"]);
        $stmt->bindParam(":pass", $Kirby->Star["user"]["pass"]);
        $stmt->bindParam(":security", $Kirby->Star["user"][$a]);
        $stmt->bindParam(":copper", $keyring->copper);
        $stmt->bindParam(":jade", $keyring->jade);
        $stmt->bindParam(":crystal", $keyring->crystal);
        $stmt->bindParam(":ip", $Kirby->Star["ip"]);
        $stmt->bindParam(":time", $time);
        $stmt->execute();

        if ($stmt->rowCount() == 0) $Kirby->Fail("Writing to database failed"); // Fehlerausgabe
        $Kirby->Response("register prepared");

        if (isset($Kirby->Star["user"]["telegram"])) $Kirby->Telegram->Send($Kirby);
        if (isset($Kirby->Star["user"]["email"])) self::EmailSend($Kirby);

        $Kirby->Pretty();
        $Kirby->Spit();
    }

    public static function Login($Kirby)
    {
        # auf vorhandenen Eintrag prüfen
        $stmt = self::$_db->prepare("SELECT * FROM login WHERE name=:name LIMIT 1");
        $stmt->bindParam(":name", $Kirby->Star["user"]["name"]);
        $stmt->execute();
        if ($stmt->rowCount() != 0) $Kirby->Fail("login already prepared");

        # userdaten abrufen
        $stmt = self::$_db->prepare("SELECT * FROM user WHERE name=:name AND pass=:pass LIMIT 1");
        $stmt->bindParam(":name", $Kirby->Star["user"]["name"]);
        $stmt->bindParam(":pass", $Kirby->Star["user"]["pass"]);
        $stmt->execute();
        if ($stmt->rowCount() == 0) $Kirby->Fail("username and/or password wrong");
        $Kirby->Star["user"] = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];

        $Kirby->Response("user found");

        self::Security($Kirby);

        $Kirby->Pretty();
        $Kirby->Spit();
    }

    public static function Get($Kirby, $full = false)
    {
        if ($full) $stmt = self::$_db->prepare("SELECT * FROM user WHERE BINARY authkey=:authkey LIMIT 1");
        else $stmt = self::$_db->prepare("SELECT name, createtime, lastaction, language, faucetkotia, faucetlitecoin, pairingomnilite, security FROM user WHERE BINARY authkey=:authkey LIMIT 1");
        $stmt->bindParam(":authkey", $Kirby->Star["authkey"]);
        $stmt->execute();
        
        if ($stmt->rowCount() == 0) $Kirby->Fail("user not found");
        $Kirby->Star["user"] = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];
        $Kirby->Response("user found");

        if (!$full) {
            $Kirby->Pretty();
            $Kirby->Spit();
        }
    }

    public static function Update($Kirby)
    {
        switch ($Kirby->Star["update"]["key"]) {
            case "pass":
                if (strlen($Kirby->Star["update"]["value"]) != 128) $Kirby->Fail("Password is not sha512 encrypted");
            break;
            
            default:
                # code...
            break;
        }
        
        self::Get($Kirby, true);
        $stmt = self::$_db->prepare("SELECT * FROM _update WHERE BINARY name=:name LIMIT 1");
        $stmt->bindParam(":name", $Kirby->Star["user"]["name"]);
        $stmt->execute();

        if ($stmt->rowCount() > 0) $Kirby->Fail("update action already prepared");

        self::Security($Kirby);
        
        $Kirby->Pretty();
        $Kirby->Spit();
    }

    public static function Litecoin($Kirby){
        switch ($action) {
            case "ltc-send":
                $keyring = $Kirby->Key->Craft2FA("ltcsend");
                $time = time() + self::$TimeWindow;

                $stmt = self::$_db->prepare("INSERT INTO ltcsend (name, time, copper, jade, crystal, ip, txhex) VALUES (:name, :time, :copper, :jade, :crystal, :ip, :txhex)");
                $stmt->bindParam(":name", $Kirby->Star->user["name"]);
                $stmt->bindParam(":time", $Kirby->Star["send"]["expire"]);
                $stmt->bindParam(":copper", $keyring->copper);
                $stmt->bindParam(":jade", $keyring->jade);
                $stmt->bindParam(":crystal", $keyring->crystal);
                $stmt->bindParam(":ip", $Kirby->ip);
                $stmt->bindParam(":txhex", $Kirby->send["signed"]->hex);
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