<?php
require_once("../api/maria.php");

class Executer{
    private static $Post;
    private static $Maria;

    public function __construct($Kirby){
        $Post = json_decode(file_get_contents("php://input"), true);
        $Maria = new Maria();

        # AuthKey überprüfen
        if (isset($Post["authkey"]))
        {
            $Kirby->Star["authkey"] = preg_replace( "/[^a-zA-Z0-9]/", "", $Post["authkey"]);
            if ($Kirby->Star["authkey"] == NULL) $Kirby->Fail("authkey cant be null");
            if (strlen($Kirby->Star["authkey"]) != 420) $Kirby->Fail("Invalid authkey");
        }

        switch ($Kirby->Star["action"]) {
            # USER
            case "register":
                # Prüfe alle Parameter ab
                if (!isset($Post["name"])) $Kirby->Fail("parameter \"name\" missing");
                if (!isset($Post["pass"])) $Kirby->Fail("parameter \"pass\" missing");
                if (!isset($Post["email"]) && !isset($Post["telegram"])) $Kirby->Fail("2fa parameter missing - \"email\" and/or \"telegram\"");
                if (isset($Post["email"]) && isset($Post["telegram"])) $Kirby->Fail("too much 2fa parameter - \"email\" or \"telegram\"");

                # fange Eingaben auf
                $Kirby->Star["user"]["name"] = strtoupper(preg_replace( "/[^a-zA-Z0-9]/", "", $Post["name"]));
                $Kirby->Star["user"]["pass"] = preg_replace( "/[^a-zA-Z0-9]/", "", $Post["pass"]);
                if (isset($Post["email"])){
                    if (filter_var($Post["email"], FILTER_VALIDATE_EMAIL)) $Kirby->Star["user"]["email"] = strtolower($Post["email"]);
                    else $Kirby->Fail("email format not valid");
                }
                if (isset($Post["telegram"])) $Kirby->Star["user"]["telegram"] = htmlspecialchars($Post["telegram"], ENT_QUOTES, "UTF-8");

                # länge des Namen prüfen
                if (strlen($Kirby->Star["user"]["name"]) < 6 || strlen($Kirby->Star["user"]["name"]) > 18) $Kirby->Fail("Username length missmatch, 6-18 characters");

                # prüfe Verfügbarkeit des username
                if (!$Maria->NameAvailable($Kirby->Star["user"]["name"])) $Kirby->Fail("Username already taken or reserved - please try again in some minutes or another name");
                $Kirby->Response("username ok");
                
                # password auf SHA512 Richtlinien prüfen
                if (strlen($Kirby->Star["user"]["pass"]) != 128) $Kirby->Fail("Password is not sha512 encrypted");
                $Kirby->Response("password ok");


                # --- 2fa check ---
                if (isset($Kirby->Star["user"]["telegram"])){
                    # Verbindung im TelegramBot prüfen
                    $Kirby->Telegram->GetID($Kirby);

                    # ist handle schon verbunden?
                    if (!$Maria->TelegramAvailable($Kirby->Star["user"]["telegram"])) $Kirby->Fail("Telegram handle already linked or reserved - please try again in some minutes");
                    $Kirby->Response("telegram ok");
                }elseif (isset($Kirby->Star["user"]["email"])){
                    # ist Email schon verbunden?
                    if (!$Maria->EmailAvailable($Kirby->Star["user"]["email"])) $Kirby->Fail("Email already linked or reserved - please try again in some minutes or try another");
                    $Kirby->Response("email ok");
                }

                $Kirby->Response("security check passed - all inputs are fine");
                $Maria->Register($Kirby);
            break;
            
            case "login": # Done
                if (!isset($Post["name"])) $Kirby->Fail("parameter \"name\" missing");
                if (!isset($Post["pass"])) $Kirby->Fail("parameter \"pass\" missing");

                $Kirby->Star["user"]["name"] = strtoupper(preg_replace( "/[^a-zA-Z0-9]/", "", $Post["name"]));
                $Kirby->Star["user"]["pass"] = preg_replace( "/[^a-zA-Z0-9]/", "", $Post["pass"]);

                $Kirby->Response("inputs checked");
                $Maria->Login($Kirby);
            break;

            case "get": # Done
                if (!isset($Post["authkey"])) $Kirby->Fail("parameter \"authkey\" missing");

                $Kirby->Response("inputs checked");
                $Maria->Get($Kirby);
            break;

            case "update":
                if (!isset($Post["authkey"])) $Kirby->Fail("parameter \"authkey\" missing");
                if (!isset($Post["key"])) $Kirby->Fail("parameter \"key\" missing");
                if (!isset($Post["value"])) $Kirby->Fail("parameter \"value\" missing");

                $Kirby->Star["update"]["key"] = preg_replace( "/[^a-zA-Z0-9]/", "", $Post["key"]);
                $Kirby->Star["update"]["value"] = preg_replace( "/[^a-zA-Z0-9]/", "", $Post["value"]);

                $Kirby->Response("inputs checked");
                $Maria->Update($Kirby);
            break;

            default:
                return false;
            break;
        }
    }

    public static function Execute($Kirby){
        switch ($Kirby->action) {
            case "login":
                if (!isset($_GET["name"])) $Kirby->Fail("parameter \"name\" missing");
                if (!isset($_GET["pass"])) $Kirby->Fail("parameter \"pass\" missing");

                $Kirby->user["name"] = strtoupper(preg_replace( "/[^a-zA-Z0-9]/", "", $_GET["pass"]));
                $Kirby->user["pass"] = preg_replace( "/[^a-zA-Z0-9]/", "", $_GET["pass"]);

                $USER->Login($Kirby);
            break;
            
            default:
                return false;
            break;
        }
    }
}