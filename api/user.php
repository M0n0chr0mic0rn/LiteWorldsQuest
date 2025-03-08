<?php
require_once("telegram.php");
require_once("key.php");

class User
{
    private static $Telegram;
    private static $Key;

    private static $_db_username = "maria";
    private static $_db_passwort = "KerkerRocks22";
    private static $_db_host = "127.0.0.1";
    private static $_db_name = "LiteWorldsQuest";
    private static $_db;

    private static $API = "https://liteworlds.quest/?method=";

    private function Action($RETURN, $action)
    {
        $RETURN->action = $action;
        $RETURN->response[0] = "initialize ".$action;
    }


    private function sendEmail($RETURN)
    {
        $title;
        $info;

        switch ($RETURN->action)
        {
            case "register":
                $title = "LiteWorlds.Quest Network - Registration";
                $info = "You are going to create an Account at LiteWorlds<br>User: ".$RETURN->user["name"];
            break;
            
            case "login":
                $title = "LiteWorlds.Quest Network - Login";
                $info = "You are going to login into your Account at LiteWorlds<br>User: ".$RETURN->user["name"];
            break;

            case "update":
                $title = "LiteWorlds.Quest Network - Update";
                $info = "You are going to update your Account at LiteWorlds<br>User: ".$RETURN->user["name"];
            break;

            default:
                # code...
            break;
        }
        // Nachricht
        $message = "
            <html>
                <body style=\"background-color: black; color: deepskyblue;\">
                <table align=\"center\">
                <tr>
                    <td><img src=\"https://ordinalslite.com/content/4749f65fc682b103d9a221b8bf3370c97583c6c530eaee4d0f27f71bfb966fcfi0\" style=\"height:250px; margin-left:auto; margin-right:auto; display:block;\"></td>
                </tr>

                <tr>
                    <td><p align=\"center\" style=\"color:deepskyblue;\">".$RETURN->security["text"]."</p></td>
                </tr>
                <tr>
                    <td>
                        <p align=\"center\" style=\"color:crimson;\">Please sign your Action</p>
                        <a target=\"_blank\" rel=\"noopener noreferrer\" href=".$RETURN->security["link"].">
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

        if (!mail($RETURN->user["email"], $RETURN->security["message"], $message, $headers)) Fail($RETURN, "Email send failed");
        Response($RETURN, "message to email send");
        Pretty($RETURN);
    }

    private function _update($RETURN)
    {
        $keyring = self::$Key->Craft2FA("_update");
        $time = time() + (60 * 3);

        $RETURN->security["link"] = self::$API . "execute&action=update&name=" . $RETURN->user["name"] . "&copper=" . $keyring->copper . "&jade=" . $keyring->jade . "&crystal=" . $keyring->crystal;
        $RETURN->security["message"] = "LiteWorlds.Quest Network - Update"; // Nachricht des Telegam Bots
        $RETURN->security["text"] = "You are going to Update your Account at LiteWorlds.Quest<br>User: ".$RETURN->user["name"];    // Beschriftung des Buttons
        $RETURN->authkey = self::$Key->CraftAuth();

        $stmt = self::$_db->prepare("INSERT INTO _update VALUES (:name, :key, :value, :copper, :jade, :crystal, :ip, :time)");
        $stmt->bindParam(":name", $RETURN->user["name"]);
        $stmt->bindParam(":key", $RETURN->update["key"]);
        $stmt->bindParam(":value", $RETURN->update["value"]);
        $stmt->bindParam(":copper", $keyring->copper);
        $stmt->bindParam(":jade", $keyring->jade);
        $stmt->bindParam(":crystal", $keyring->crystal);
        $stmt->bindParam(":ip", $RETURN->ip);
        $stmt->bindParam(":time", $time);
        $stmt->execute();

        if ($stmt->rowCount() == 0) Fail($RETURN, "prepare update failed");
        Response($RETURN, "update prepared");
        
        switch ($RETURN->user["security"])
        {
            case "telegram":
                self::$Telegram->Send($RETURN);
            break;

            case "email":
                self::sendEmail($RETURN);
            break;
            
            default:
                # code...
            break;
        }
    }

    function __construct()
    {
        self::$Telegram = new Telegram;
        self::$Key = new Key;

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

    function Execute($RETURN)
    {
        if ($RETURN->action == "update") $RETURN->action = "_update";
        if ($RETURN->action == "ltc-send-address") $RETURN->action = "ltcsend";

        // suche Action in der prepare tabelle
        $stmt = self::$_db->prepare("SELECT * FROM $RETURN->action WHERE name=:name AND BINARY copper=:copper AND BINARY jade=:jade AND BINARY crystal=:crystal AND ip=:ip LIMIT 1");
        $stmt->bindParam(":name", $RETURN->user["name"]);
        $stmt->bindParam(":copper", $RETURN->keyring["copper"]);
        $stmt->bindParam(":jade", $RETURN->keyring["jade"]);
        $stmt->bindParam(":crystal", $RETURN->keyring["crystal"]);
        $stmt->bindParam(":ip", $RETURN->ip);
        $stmt->execute();

        // wenn nicht gefunden abbruch
        if ($stmt->rowCount() == 0) Fail($RETURN, "Could not found action in database");
        // ausgabe hinzufügen
        Response($RETURN, "action found in database");

        // speichern des gefundenen users in der aktiven user tabelle
        $RETURN->user = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];

        switch ($RETURN->action)
        {
            case 'register':
                if ($RETURN->user["email"] !== NULL) $RETURN->security = "email";
                if ($RETURN->user["telegram"] !== NULL) $RETURN->security = "telegram";


                $stmt = self::$_db->prepare("INSERT INTO user (name, pass, email, telegram, createtime, createip, lastip, lastaction, security) VALUES (:name, :pass, :email, :telegram, :createtime, :createip, :lastip, :lastaction, :security)");
                $stmt->bindParam(":name", $RETURN->user["name"]);
                $stmt->bindParam(":pass", $RETURN->user["pass"]);
                $stmt->bindParam(":email", $RETURN->user["email"]);
                $stmt->bindParam(":telegram", $RETURN->user["telegram"]);
                $stmt->bindParam(":createtime", $RETURN->user["time"]);
                $stmt->bindParam(":createip", $RETURN->user["ip"]);
                $stmt->bindParam(":lastip", $RETURN->user["ip"]);
                $stmt->bindParam(":lastaction", $RETURN->user["time"]);
                $stmt->bindParam(":security", $RETURN->security);
                $stmt->execute();

                // abbruch bei schreibfehler
                if ($stmt->rowCount() == 0) Fail($RETURN, "Could not write user to database");
                // ausgabe hinzufügen
                Response($RETURN, "user added and activated");

                $stmt = self::$_db->prepare("DELETE FROM $RETURN->action WHERE name=:name AND BINARY copper=:copper AND BINARY jade=:jade AND BINARY crystal=:crystal AND ip=:ip LIMIT 1");
                $stmt->bindParam(":name", $RETURN->user["name"]);
                $stmt->bindParam(":copper", $RETURN->keyring["copper"]);
                $stmt->bindParam(":jade", $RETURN->keyring["jade"]);
                $stmt->bindParam(":crystal", $RETURN->keyring["crystal"]);
                $stmt->bindParam(":ip", $RETURN->ip);
                $stmt->execute();

                // abbruch bei löschung? eigentlich nicht kritisch
                if ($stmt->rowCount() == 0) Fail($RETURN, "Error deleting prepared action in database");
                // ausgabe hinzufügen
                Response($RETURN, "cleanup done");
            break;

            case "login":
                $time = time();

                $stmt = self::$_db->prepare("UPDATE user SET authkey=:authkey, lastaction=:lastaction, lastip=:lastip WHERE name=:name LIMIT 1");
                $stmt->bindParam(":authkey", $RETURN->user["authkey"]);
                $stmt->bindParam(":lastaction", $time);
                $stmt->bindParam(":lastip", $RETURN->ip);
                $stmt->bindParam(":name", $RETURN->user["name"]);
                $stmt->execute();

                if ($stmt->rowCount() == 0) Fail($RETURN, "Error accepting authkey");
                Response($RETURN, "authkey accepted");

                $stmt = self::$_db->prepare("DELETE FROM $RETURN->action WHERE name=:name AND BINARY copper=:copper AND BINARY jade=:jade AND BINARY crystal=:crystal AND ip=:ip LIMIT 1");
                $stmt->bindParam(":name", $RETURN->user["name"]);
                $stmt->bindParam(":copper", $RETURN->keyring["copper"]);
                $stmt->bindParam(":jade", $RETURN->keyring["jade"]);
                $stmt->bindParam(":crystal", $RETURN->keyring["crystal"]);
                $stmt->bindParam(":ip", $RETURN->ip);
                $stmt->execute();

                if ($stmt->rowCount() == 0) Fail($RETURN, "Error deleting prepared action in database");
                Response($RETURN, "cleanup done");
            break;

            case "_update":
                $time = time();
                $key = $RETURN->user["key"];

                $stmt = self::$_db->prepare("UPDATE user SET $key=:value, lastaction=:lastaction WHERE name=:name LIMIT 1");
                $stmt->bindParam(":value", $RETURN->user["value"]);
                $stmt->bindParam(":lastaction", $time);
                $stmt->bindParam(":name", $RETURN->user["name"]);
                $stmt->execute();

                if ($stmt->rowCount() == 0) Fail($RETURN, "failed update user");
                Response($RETURN, "user update done");

                $stmt = self::$_db->prepare("DELETE FROM $RETURN->action WHERE name=:name AND BINARY copper=:copper AND BINARY jade=:jade AND BINARY crystal=:crystal AND ip=:ip LIMIT 1");
                $stmt->bindParam(":name", $RETURN->user["name"]);
                $stmt->bindParam(":copper", $RETURN->keyring["copper"]);
                $stmt->bindParam(":jade", $RETURN->keyring["jade"]);
                $stmt->bindParam(":crystal", $RETURN->keyring["crystal"]);
                $stmt->bindParam(":ip", $RETURN->ip);
                $stmt->execute();

                $RETURN->action = "update";

                if ($stmt->rowCount() == 0) Fail($RETURN, "Error deleting prepared action in database");
                Response($RETURN, "cleanup done");
            break;

            case "ltcsend":
                $RETURN->txid = Node($RETURN, "sendrawtransaction", [$RETURN->user["txhex"]]);
                $RETURN->txid = str_replace("\"","", $RETURN->txid);

                if (!$RETURN->txid) Fail($RETURN, "could not send transaction");
                Response($RETURN, "transaction send to mempool");

                $stmt = self::$_db->prepare("DELETE FROM $RETURN->action WHERE name=:name AND BINARY copper=:copper AND BINARY jade=:jade AND BINARY crystal=:crystal AND ip=:ip LIMIT 1");
                $stmt->bindParam(":name", $RETURN->user["name"]);
                $stmt->bindParam(":copper", $RETURN->keyring["copper"]);
                $stmt->bindParam(":jade", $RETURN->keyring["jade"]);
                $stmt->bindParam(":crystal", $RETURN->keyring["crystal"]);
                $stmt->bindParam(":ip", $RETURN->ip);
                $stmt->execute();

                Response($RETURN, "cleanup done");
            break;
            
            default:
                Fail($RETURN, "undefined action");
            break;
        }

        Pretty($RETURN);
    }

    function Update($RETURN)
    {
        //self::Action($RETURN, "_update");
        self::Get($RETURN, true);

        # Sicherheitscheck
        switch ($RETURN->update["key"])
        {
            case "pass":
                if (strlen($RETURN->update["value"]) != strlen(preg_replace( "/[^a-zA-Z0-9]/", "", $RETURN->update["value"])) || strlen($RETURN->update["value"]) != 128) Fail($RETURN, "Password is not sha512 encrypted");
            break;

            case "telegram":
                $RETURN->user["telegram"] = $RETURN->update["value"];
                self::$Telegram->GetID($RETURN);
                $RETURN->update["value"] = $RETURN->user["telegram"];

                if (self::checkTelegram($RETURN->update["value"])) Fail($RETURN, "Telegram ID already linked");
            break;

            case "email":
                if (!filter_var($RETURN->update["value"], FILTER_VALIDATE_EMAIL)) Fail($RETURN, "No Email format - double check your input");
                if (self::checkEmail($RETURN->update["value"])) Fail($RETURN, "Email already linked");
            break;

            case "security":
                if (!($RETURN->update["value"] == "telegram" || $RETURN->update["value"] == "email")) Fail($RETURN, "Security must be one of the following: telegram, email");
                if ($RETURN->update["value"] == "telegram" && $RETURN->user["telegram"] == NULL) Fail($RETURN, "You cant switch to Telegram without set it up first");
                if ($RETURN->update["value"] == "email" && $RETURN->user["email"] == NULL) Fail($RETURN, "You cant switch to Email without set it up first");

                $RETURN->user["security"] = $RETURN->update["value"];
            break;
            
            default:
                Fail($RETURN, "unknown key");
            break;
        }

        $stmt = self::$_db->prepare("SELECT * FROM _update WHERE name=:name LIMIT 1");
        $stmt->bindParam(":name", $RETURN->user["name"]);
        $stmt->execute();

        if ($stmt->rowCount() > 0) Fail($RETURN, "update action already prepared");
        Response($RETURN, "update possible");

        self::_update($RETURN);
    }

    function Logout($RETURN, $authkey)
    {
        //self::Action($RETURN, "logout");

        $null = NULL;

        $stmt = self::$_db->prepare("SELECT * FROM user WHERE BINARY authkey=:authkey LIMIT 1");
        $stmt->bindParam(":authkey", $authkey);
        $stmt->execute();

        if ($stmt->rowCount() == 0) Fail($RETURN, "no user with found");
        Response($RETURN, "user with key found");

        $stmt = self::$_db->prepare("UPDATE user SET authkey=:_null WHERE BINARY authkey=:authkey LIMIT 1");
        $stmt->bindParam(":_null", $null);
        $stmt->bindParam(":authkey", $authkey);
        $stmt->execute();

        if ($stmt->rowCount() == 0) Fail($RETURN, "logout failed");
        Response($RETURN, "logout done");
    }

    function Get($RETURN, $full = false)
    {
        //self::Action($RETURN, "get");
        $stmt;

        if ($full) $stmt = self::$_db->prepare("SELECT * FROM user WHERE BINARY authkey=:authkey LIMIT 1");
        else $stmt = self::$_db->prepare("SELECT name,createtime,lastaction,language,faucetkotia,faucetlitecoin,pairingomnilite FROM user WHERE BINARY authkey=:authkey LIMIT 1");
        $stmt->bindParam(":authkey", $RETURN->authkey);
        $stmt->execute();
        
        if ($stmt->rowCount() == 0) Fail($RETURN, "user not found");
        Response($RETURN, "user found");

        $RETURN->user = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];
    }

    function Progress($RETURN, $name, $table)
    {
        //self::Action($RETURN, "progress");

        // --- username ---
        // Überprüfung des username auf spezial Zeichen
        if (strlen($name) != strlen(preg_replace( "/[^a-zA-Z0-9]/", "", $name))) Fail($RETURN, "Username contains special characters - only use a-z and 0-9 please");
        
        // länge des Namen prüfen
        if (strlen($name) < 6 || strlen($name) > 18) Fail($RETURN, "Username length missmatch, 6-18 characters");

        // Namen in Großbuchstaben umwandeln
        $name = strtoupper($name);

        $stmt = self::$_db->prepare("SELECT * FROM $table WHERE name=:name LIMIT 1");
        $stmt->bindParam(":name", $name);
        $stmt->execute();

        if ($stmt->rowCount() == 0) Fail($RETURN, "action not found");
        Response($RETURN, "action found"); 
    }
}