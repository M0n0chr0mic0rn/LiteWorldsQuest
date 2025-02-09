<?php

class Terminator
{
    private static $_db_username = "maria";
    private static $_db_passwort = "KerkerRocks22";
    private static $_db_host = "127.0.0.1";
    private static $_db_name = "LiteWorldsQuest";
    private static $_db;

    private static $_time;


    function __construct()
    {
        self::$_time = time();

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

    function terminate($table)
    {
        $stmt = self::$_db->prepare("DELETE FROM $table WHERE time<=:time");
        $stmt->bindParam(":time", self::$_time);
        $stmt->execute();
    }
}

$t = new Terminator();

$t->terminate("register");
$t->terminate("login");
$t->terminate("_update");
$t->terminate("ltcsend");