<?php
require_once("/var/www/access.php");

class Telegram
{
    private static $Access;
    private static $BotHandle;

    public function __construct(){
        self::$Access = new Access();
    }

    private static function Update(){
        $token = self::$Access->Telegram;           // privater Zugangs Token für Telegram Bot
        $url = "https://api.telegram.org/bot$token/getUpdates";    // url für den Bot
        return array_reverse(json_decode(file_get_contents($url))->result);
    }

    public function GetID($Kirby){
        # rufe den chatlog aus dem Bot ab
        $bot_log = self::Update();
        $destination = 0;

        // suche nach Telegram handle im chatlog
        for ($a = 0; $a < count($bot_log); $a++){
            $element = $bot_log[$a];

            $userID = $element->message->from->id;
            $user = $element->message->from->username;

            // wenn er gefunden wird seten wir unser Ziel aud diese ID
            if ($user == $Kirby->Star["user"]["telegram"]) $destination = $userID;
        }

        // Ausgabe
        if (!$destination) $Kirby->Fail("Could not get TelegramID - please send a message to the Telegram Bot " .self::$BotHandle);
        else{
            $Kirby->Star["user"]["telegram"] = $destination;
            $Kirby->Response("telegram id found");
        }
    }

    public function Send($Kirby)
    {
        $token = self::$Access->Telegram;
        $url = "https://api.telegram.org/bot$token/sendMessage";

        $button = [
            'text' => $Kirby->Star["security"]["message"],
            'url' => $Kirby->Star["security"]["link"]
        ];
    
        $replyMarkup = [
            'inline_keyboard' => [[
                $button
            ]]
        ];
    
        $data = [
            'chat_id' => $Kirby->Star["user"]["telegram"],
            'text' => $Kirby->Star["security"]["text"],
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
            if (!$debug) $Kirby->Fail("Telegram Error, connection failed");
            $debug = json_decode($debug);
            if (!$debug->ok) $Kirby->Fail("Telegram Error, could not send message");

            $Kirby->Response("message sent to telegram");
        } catch (\Throwable $th) {
            $Kirby->Fail("Telegram Fatal Error");
        }
    }
}