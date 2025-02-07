<?php
require_once("/var/www/access.php");

class Telegram
{
    private static $Access;

    function __construct()
    {
        self::$Access = new Access();
    }

    function Update()
    {
        $token = self::$Access->Telegram;           // privater Zugangs Token für Telegram Bot
        $url = "https://api.telegram.org/bot$token/getUpdates";    // url für den Bot
        return array_reverse(json_decode(file_get_contents($url))->result);
    }

    function GetID($RETURN)
    {
        $bot_log = self::Update();  // rufe den chatlog aus dem Bot ab
        $destination = 0;

        // suche nach Telegram handle im chatlog
        for ($a = 0; $a < count($bot_log); $a++)
        {
            $element = $bot_log[$a];

            $userID = $element->message->from->id;
            $user = $element->message->from->username;

            // wenn er gefunden wird seten wir unser Ziel aud diese ID
            if ($user == $RETURN->user["telegram"])
            {
                $destination = $userID;
            }
        }

        // Ausgabe
        if (!$destination) Fail($RETURN, "Could not get TelegramID - please send a message to the Telegram Bot @LWQtestBot");
        else
        {
            $RETURN->user["telegram"] = $destination;
            Response($RETURN, "telegram id found");
        }
    }

    function Send($RETURN)
    {
        $token = self::$Access->Telegram;
        $url = "https://api.telegram.org/bot$token/sendMessage";

        $button = [
            'text' => $RETURN->security["message"],
            'url' => $RETURN->security["link"]
        ];
    
        $replyMarkup = [
            'inline_keyboard' => [[
                $button
            ]]
        ];
    
        $data = [
            'chat_id' => $RETURN->user["telegram"],
            'text' => $RETURN->security["text"],
            'reply_markup' => json_encode($replyMarkup)
        ];
    
        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            ],
        ];
        

        $context  = stream_context_create($options);
        try {
            $debug = file_get_contents($url, false, $context);
            if (!$debug) Fail($RETURN, "Telegram Error, connection failed");
            $debug = json_decode($debug);
            if (!$debug->ok) Fail($RETURN, "Telegram Error, could not send message");

            Response($RETURN, "message sent to telegram");
            Pretty($RETURN);
        } catch (\Throwable $th) {
            Fail($RETURN, "Telegram Fatal Error");
        }
    }
}