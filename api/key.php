<?php
# Die Key Klasse - sie generiert zufällig Sicherheitsschlüssel
 
class Key
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
            echo "<br>DATABASE ERROR FROM Key Class<br>".$e;
            die();
        }
    }

    # 3 Schlüssel kombination für das signieren von Aktionen mit Schreibrecht
    function Craft2FA($table)
    {
        $key;
        $loop = true;
        do {
            # Erstellen der Schlüssel Arrays
            $key = (object)array("copper"=>"LWQ","jade"=>"LWQ","crystal"=>"LWQ");

            # Schlüssel zufällig generieren
            for ($b=0; $b < 200; $b++)
            { 
                $key->copper .= self::RandomSign();
                $key->jade .= self::RandomSign();
                $key->crystal .= self::RandomSign();
            }

            # Einzigartigkeit prüfen
            $stmt = self::$_db->prepare("SELECT * FROM $table WHERE BINARY copper=:copper OR BINARY copper=:jade OR BINARY copper=:crystal LIMIT 1");
            $stmt->bindParam(":copper", $key->copper);
            $stmt->bindParam(":jade", $key->jade);
            $stmt->bindParam(":crystal", $key->crystal);
            $stmt->execute();
            $copper = $stmt->rowCount();

            $stmt = self::$_db->prepare("SELECT * FROM $table WHERE BINARY jade=:copper OR BINARY jade=:jade OR BINARY jade=:crystal LIMIT 1");
            $stmt->bindParam(":copper", $key->copper);
            $stmt->bindParam(":jade", $key->jade);
            $stmt->bindParam(":crystal", $key->crystal);
            $stmt->execute();
            $jade = $stmt->rowCount();

            $stmt = self::$_db->prepare("SELECT * FROM $table WHERE BINARY crystal=:copper OR BINARY crystal=:jade OR BINARY crystal=:crystal LIMIT 1");
            $stmt->bindParam(":copper", $key->copper);
            $stmt->bindParam(":jade", $key->jade);
            $stmt->bindParam(":crystal", $key->crystal);
            $stmt->execute();
            $crystal = $stmt->rowCount();

            if (!($copper && $jade && $crystal)) $loop = false;
        } while ($loop);
        

        # Rückgabe des Schlüssel Arrays
        return $key;
    }

    # große Schlüssel für sessions
    function CraftAuth()
    {
        $loop = true;
        do
        {
            # Schlüssel erstellen
            $result = "LWQ";

            # Schlüssel zufällig generieren
            for ($a=0; $a < 417; $a++)
            { 
                $result .= self::RandomSign();
            }

            # Einzigartigkeit prüfen
            $stmt = self::$_db->prepare("SELECT * FROM user WHERE BINARY authkey=:authkey LIMIT 1");
            $stmt->bindParam(":authkey", $result);
            $stmt->execute();
            $user = $stmt->rowCount();

            $stmt = self::$_db->prepare("SELECT * FROM login WHERE BINARY authkey=:authkey LIMIT 1");
            $stmt->bindParam(":authkey", $result);
            $stmt->execute();
            $login = $stmt->rowCount();

            if (!($user && $login)) $loop = false;
        } while ($loop);
        

        # Rückgabe des Schlüssels
        return $result;
    }

    # gib mir eine zfällige Zahl oder Buchstabe
    private function RandomSign()
    {
        # Zufallszahl würfeln
        $rand = rand(0, 59);

        # das Ergebnis wird ab 10 einem Buchstaben zugeordnet und zurück gegeben
        if ($rand < 10)
        {
            return $rand;
        }
        else
        {
            switch ($rand)
            {
                case 10:return "a";break;
                case 11:return "b";break;
                case 12:return "c";break;
                case 13:return "d";break;
                case 14:return "e";break;
                case 15:return "f";break;
                case 16:return "g";break;
                case 17:return "h";break;
                case 18:return "i";break;
                case 19:return "j";break;
                case 20:return "k";break;
                case 21:return "l";break;
                case 22:return "m";break;
                case 23:return "n";break;
                case 24:return "o";break;
                case 25:return "p";break;
                case 26:return "q";break;
                case 27:return "r";break;
                case 28:return "s";break;
                case 29:return "t";break;
                case 30:return "u";break;
                case 31:return "v";break;
                case 32:return "w";break;
                case 33:return "x";break;
                case 34:return "y";break;
                case 35:return "A";break;
                case 36:return "B";break;
                case 37:return "C";break;
                case 38:return "D";break;
                case 39:return "E";break;
                case 40:return "F";break;
                case 41:return "G";break;
                case 42:return "H";break;
                case 43:return "I";break;
                case 44:return "J";break;
                case 45:return "K";break;
                case 46:return "L";break;
                case 47:return "M";break;
                case 48:return "N";break;
                case 49:return "O";break;
                case 50:return "P";break;
                case 51:return "Q";break;
                case 52:return "R";break;
                case 53:return "S";break;
                case 54:return "T";break;
                case 55:return "U";break;
                case 56:return "V";break;
                case 57:return "W";break;
                case 58:return "X";break;
                case 59:return "Y";break;
                default:break;
            }
        }
    }
}