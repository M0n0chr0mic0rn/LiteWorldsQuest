<?php
require_once("telegram.php");
require_once("key.php");

class Litecoin
{
    private static $Telegram;
    private static $Key;

    private static $_db_username = "maria";
    private static $_db_passwort = "KerkerRocks22";
    private static $_db_host = "127.0.0.1";
    private static $_db_name = "LiteWorldsQuest";
    private static $_db;

    private static $API = "https://liteworlds.quest/?method=";

    private static $_ServiceFeeFoundation = "MP2bKNDoDGXmG4j5V4aaTNqXhP9ZybLGnk";
    private static $_ServiceFeeFaucet = "MCtYmUDUvjCatos2whjAsPaBr2a1nwA1tG";

    private static $_minSendingAmount = 0.000054;
    private static $_ServiceFee = 0.00025;
    private static $_inputWeight = 0.0000015;

    private function _PrepareSend($RETURN)
    {
        $keyring = self::$Key->Craft2FA("ltcsend");         // einzigartigen Schlüsselbund erzeugen
        $RETURN->send["expire"] = time() + (60 * 3);        // Zeitstempel nehmen (UNIX Zeit - 3min in der Zukunft - Stempel für den Terminator)

        //prepare for sign
        $stmt = self::$_db->prepare("INSERT INTO ltcsend (name, time, copper, jade, crystal, ip, txhex) VALUES (:name, :time, :copper, :jade, :crystal, :ip, :txhex)");
        $stmt->bindParam(":name", $RETURN->user["name"]);
        $stmt->bindParam(":time", $RETURN->send["expire"]);
        $stmt->bindParam(":copper", $keyring->copper);
        $stmt->bindParam(":jade", $keyring->jade);
        $stmt->bindParam(":crystal", $keyring->crystal);
        $stmt->bindParam(":ip", $RETURN->ip);
        $stmt->bindParam(":txhex", $RETURN->send["signedtxhex"]->hex);
        $stmt->execute();

        if ($stmt->rowCount() != 1) Fail($RETURN, "Could not insert action into database");
        Response($RETURN, "action prepared");

        $RETURN->security = array();
        $RETURN->security["link"] = self::$API . "execute&action=ltcsend&name=" . $RETURN->user["name"] . "&copper=" . $keyring->copper . "&jade=" . $keyring->jade . "&crystal=" . $keyring->crystal; // Link zum signieren der Registrierung

        switch ($RETURN->user["security"])
        {
            case 'email':
                self::sendEmail($RETURN);
            break;
            
            case 'telegram':
                $RETURN->security["message"] = "LiteWorlds.Quest Network - Send Litecoin from Address"; // Nachricht des Telegam Bots
                $RETURN->security["text"] = "You are going to send Litecoin via LiteWorlds.Quest User: ".$RETURN->user["name"];    // Beschriftung des Buttons
                
                self::$Telegram->Send($RETURN);      // Nachricht vom Bot senden lassen
            break;
        }
    }

    private function sendEmail($RETURN)
    {
        $title;
        $info;

        switch ($RETURN->action)
        {
            case "ltc-send-address":
                $title = "LiteWorlds.Quest Network - Send Litecoin from Address";
                $info = "You are going to send Litecoin via LiteWorlds<br>User: ".$RETURN->user["name"];
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

        if (!mail($RETURN->user["email"], $title, $message, $headers)) Fail($RETURN, "Email send failed");
        Response($RETURN, "message to email send");
        Pretty($RETURN);
    }

    private function _ServiceFeeDestination()
    {
        $rand = random_int(1, 100);

        if ($rand <= 50) return self::$_ServiceFeeFoundation;
        else return self::$_ServiceFeeFaucet;
    }

    private function _Weight($RETURN, $hex)
    {
        $r = Node($RETURN, "decoderawtransaction", [$hex], $RETURN->user["name"]);
        $tx = json_decode($r);
        $fee = $tx->vsize * $RETURN->send["networkfee"];
        return $fee;
    }

    private function _BuildInputA($RETURN, $all = false)
    {
        if ($all)
        {
            $RETURN->send["utxo"] = json_decode(Node($RETURN, "listunspent", [0,999999999,[$RETURN->send["origin"]]], $RETURN->user["name"]));
            $RETURN->send["input"] = array();

            foreach ($RETURN->send["utxo"] as $index => $utxo)
            {
                $RETURN->send["amount"] += $utxo->amount;
                array_push($RETURN->send["input"], array("txid"=>$utxo->txid, "vout"=>$utxo->vout));
            }
        }
        else
        {
            $utxos = json_decode(Node($RETURN, "listunspent", [0,999999999,[$RETURN->send["origin"]]], $RETURN->user["name"]));

            $amount2send = 0;
            $input = array();
            $index = 0;
            $exceptedfee = 0;
            if ($RETURN->send["servicefee"])
            {
                do
                {
                    array_push($input, array("txid"=>$utxos[$index]->txid, "vout"=>$utxos[$index]->vout));
                    $amount2send += $utxos[$index]->amount;
                    $index++;
                    $exceptedfee += self::$_inputWeight;

                    if ($index == count($utxos)) break;
                }
                while($amount2send < (self::$_minSendingAmount + self::$_ServiceFee + $RETURN->send["amount"] + $exceptedfee));

                if ($amount2send < (self::$_minSendingAmount + self::$_ServiceFee + $RETURN->send["amount"] + $exceptedfee)) Fail($RETURN, "collide at dust amount");
            }
            else
            {
                do
                {
                    array_push($input, array("txid"=>$utxos[$index]->txid, "vout"=>$utxos[$index]->vout));
                    $amount2send += $utxos[$index]->amount;
                    $index++;
                    $exceptedfee += self::$_inputWeight;

                    if ($index == count($utxos)) break;
                }
                while($amount2send < (self::$_minSendingAmount + $RETURN->send["amount"] + $exceptedfee));

                if ($amount2send < (self::$_minSendingAmount + $RETURN->send["amount"] + $exceptedfee)) Fail($RETURN, "collide at dust amount");
            }
            

            $RETURN->send["liquidity"] = $amount2send;
            return $input;
        }
    }

    private function _BuildOutputA($RETURN, $all = false)
    {
        $RETURN->send["txhex"] = Node($RETURN, "createrawtransaction", [$RETURN->send["input"], $RETURN->send["output"]], $RETURN->user["name"]);
        $RETURN->send["txhex"] = str_replace("\"","", $RETURN->send["txhex"]);

        $RETURN->send["signedtxhex"] = json_decode(Node($RETURN, "signrawtransactionwithwallet", [$RETURN->send["txhex"]], $RETURN->user["name"]));
        if ($RETURN->send["signedtxhex"]->complete)
        {
            $RETURN->send["networkfee"] = self::_Weight($RETURN, $RETURN->send["signedtxhex"]->hex) / 100000000;

            if ($all)
            {
                $RETURN->send["output"][$RETURN->send["destination"]] = (float)$RETURN->send["output"][$RETURN->send["destination"]] - $RETURN->send["networkfee"];
                if ($RETURN->send["output"][$RETURN->send["destination"]] < self::$_minSendingAmount) Fail($RETURN, "dust error");

                $RETURN->send["output"][$RETURN->send["destination"]] = number_format($RETURN->send["output"][$RETURN->send["destination"]], 8, ".", "");
            }
            else
            {
                $RETURN->send["output"][$RETURN->send["origin"]] = (float)$RETURN->send["output"][$RETURN->send["origin"]] - $RETURN->send["networkfee"];
                if ($RETURN->send["output"][$RETURN->send["origin"]] < self::$_minSendingAmount) Fail($RETURN, "dust error");

                $RETURN->send["output"][$RETURN->send["origin"]] = number_format($RETURN->send["output"][$RETURN->send["origin"]], 8, ".", "");
            }

            $RETURN->send["txhex"] = Node($RETURN, "createrawtransaction", [$RETURN->send["input"], $RETURN->send["output"]], $RETURN->user["name"]);
            $RETURN->send["txhex"] = str_replace("\"","", $RETURN->send["txhex"]);

            $RETURN->send["signedtxhex"] = json_decode(Node($RETURN, "signrawtransactionwithwallet", [$RETURN->send["txhex"]], $RETURN->user["name"]));
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

    function Wallet($RETURN)
    {
        $r = Node($RETURN, "listwallets", [], $RETURN->user["name"]);
        //var_dump($r);

        $r1 = json_decode($r);
        $loaded = false;

        foreach ($r1 as $key => $value)
        {
            //var_dump($value);

            if ($value == $RETURN->user["name"])
            {
                $loaded = true;
                Response($RETURN, "Wallet loaded");
            }
        }

        if (!$loaded)
        {
            if (isset($RETURN->user["name"]))
            {
                $h1 = json_decode(Node($RETURN, "createwallet", [$RETURN->user["name"]]));
                //var_dump($h1);
                if (!isset($h1->name)) Node($RETURN, "loadwallet", [$RETURN->user["name"]]);
            }
            else Fail($RETURN, "unknown User");
        }

        $RETURN->litecoin = array();

        $labels = json_decode(Node($RETURN, "listlabels", [], $RETURN->user["name"]));
        //var_dump($labels);

        Response($RETURN, "Labels loaded");

        foreach ($labels as $key => $label)
        {
            $RETURN->litecoin[$label] = array();
            $addresses = (array)json_decode(Node($RETURN, "getaddressesbylabel", [$label], $RETURN->user["name"]));
            //var_dump($adr);

            Response($RETURN, $label . " Addresses loaded");

            foreach ($addresses as $key1 => $value1)
            {
                //var_dump($key1);
                //array_push($RETURN->litecoin[$value], $key1);
                $RETURN->litecoin[$label][$key1] = array();

                $utxo = (array)json_decode(Node($RETURN, "listunspent", [0, 999999999, [$key1]], $RETURN->user["name"]));
                //var_dump($utxo);

                foreach ($utxo as $key2 => $value2)
                {
                    array_push($RETURN->litecoin[$label][$key1], array("txid"=>$value2->txid, "vout"=>$value2->vout, "amount"=>number_format($value2->amount, 8, ".", ""), "confirmations"=>$value2->confirmations));
                }
            }
        }
    }

    function NewAddress($RETURN, $label, $type)
    {
        var_dump($label, $type);
        //$r = Node($RETURN, "getnewaddress", [$label, $type], $RETURN->user["name"]);
        //var_dump($r);

        if (Node($RETURN, "getnewaddress", [$label, $type], $RETURN->user["name"]))
        {
            Response($RETURN, "new address added");
            Pretty($RETURN);
        }
        else
        {
            Fail($RETURN, "could not add new address");
        }
    }

    function SendfromAddress($RETURN)
    {
        $stmt = self::$_db->prepare("SELECT * FROM ltcsend WHERE name=:name LIMIT 1");
        $stmt->bindParam(":name", $RETURN->user["name"]);
        $stmt->execute();

        if ($stmt->rowCount() != 0) Fail($RETURN, "prepared action already existence - confirm or wait for termination");
        Response($RETURN, "action possible");

        self::Wallet($RETURN);

        $RETURN->send["input"] = self::_BuildInputA($RETURN);
        Response($RETURN, "build input");

        $RETURN->send["output"] = array();

        $RETURN->send["change"] = $RETURN->send["liquidity"] - $RETURN->send["amount"] - self::$_ServiceFee;
        if ($RETURN->send["change"] < self::$_minSendingAmount) Fail($RETURN, "dust error");

        if ($RETURN->send["servicefee"])
        {
            $RETURN->send["output"][$RETURN->send["origin"]] = number_format($RETURN->send["change"], 8, ".", "");
            $RETURN->send["output"][self::_ServiceFeeDestination()] = number_format(self::$_ServiceFee, 8, ".", "");
        }
        else
        {
            $RETURN->send["output"][$RETURN->send["origin"]] = number_format(($RETURN->send["change"] + self::$_ServiceFee), 8, ".", "");
        }
        
        $RETURN->send["output"][$RETURN->send["destination"]] = number_format($RETURN->send["amount"], 8, ".", "");

        self::_BuildOutputA($RETURN);
        Response($RETURN, "build output");

        $keyring = self::$Key->Craft2FA("ltcsend");     // einzigartigen Schlüsselbund erzeugen
        $time = time() + (60 * 3);                      // Zeitstempel nehmen (UNIX Zeit - 3min in der Zukunft - Stempel für den Terminator)

        //prepare for sign
        $stmt = self::$_db->prepare("INSERT INTO ltcsend (name, time, copper, jade, crystal, ip, txhex) VALUES (:name, :time, :copper, :jade, :crystal, :ip, :txhex)");
        $stmt->bindParam(":name", $RETURN->user["name"]);
        $stmt->bindParam(":time", $time);
        $stmt->bindParam(":copper", $keyring->copper);
        $stmt->bindParam(":jade", $keyring->jade);
        $stmt->bindParam(":crystal", $keyring->crystal);
        $stmt->bindParam(":ip", $RETURN->ip);
        $stmt->bindParam(":txhex", $RETURN->send["signedtxhex"]->hex);
        $stmt->execute();

        if ($stmt->rowCount() != 1) Fail($RETURN, "Could not insert action into database");
        Response($RETURN, "action prepared");

        $RETURN->security = array();
        $RETURN->security["link"] = self::$API . "execute&action=ltcsend&name=" . $RETURN->user["name"] . "&copper=" . $keyring->copper . "&jade=" . $keyring->jade . "&crystal=" . $keyring->crystal; // Link zum signieren der Registrierung

        switch ($RETURN->user["security"])
        {
            case 'email':
                self::sendEmail($RETURN);
            break;
            
            case 'telegram':
                $RETURN->security["message"] = "LiteWorlds.Quest Network - Send Litecoin from Address"; // Nachricht des Telegam Bots
                $RETURN->security["text"] = "You are going to send Litecoin via LiteWorlds.Quest User: ".$RETURN->user["name"];    // Beschriftung des Buttons
                
                self::$Telegram->Send($RETURN);      // Nachricht vom Bot senden lassen
            break;
        }

        Done($RETURN);
    }

    function SendfromAddressAll($RETURN)
    {
        $stmt = self::$_db->prepare("SELECT * FROM ltcsend WHERE name=:name LIMIT 1");
        $stmt->bindParam(":name", $RETURN->user["name"]);
        $stmt->execute();

        if ($stmt->rowCount() != 0) Fail($RETURN, "prepared action already exists - confirm or wait for termination");
        Response($RETURN, "action possible");

        self::Wallet($RETURN);

        self::_BuildInputA($RETURN, true);
        Response($RETURN, "build input");

        $RETURN->send["output"] = array();
        $RETURN->send["output"][$RETURN->send["destination"]] = number_format($RETURN->send["amount"], 8, ".", "");

        self::_BuildOutputA($RETURN, true);
        Response($RETURN, "build output");

        self::_PrepareSend($RETURN);

        Done($RETURN);
    }

    function TokenList($RETURN, $origin, $token, $amount, $desire)
    {
        //self::Wallet($RETURN);

        $utxos = json_decode(Node($RETURN, "listunspent", [0,999999999,[$origin]], $RETURN->user["name"]));

        $amount2send = 0;
        $input = array();
        $index = 0;
        do
        {
            array_push($input, array("txid"=>$utxos[$index]->txid, "vout"=>$utxos[$index]->vout));
            $amount2send += $utxos[$index]->amount;
            $index++;

            if ($index == count($utxos)) break;
        }
        while($amount2send < (self::$_minSendingAmount + self::$_ServiceFee));

        //var_dump($origin);
        //var_dump($token);
        //var_dump($amount);
        //var_dump($desire);
        //var_dump($amount2send);
        //var_dump($input);
        //var_dump($RETURN);

        if ($amount2send >= (self::$_minSendingAmount + self::$_ServiceFee))
        {
            var_dump("READY TO GO");

            $output = array();

            $output[$origin] = number_format($amount2send - self::$_ServiceFee, 8, ".", "");
            $output[self::_ServiceFeeDestination()] = number_format(self::$_ServiceFee, 8, ".", "");

            var_dump($output);

            $txhex = Node($RETURN, "createrawtransaction", [$input, $output], $RETURN->user["name"]);
            $txhex = str_replace("\"","", $txhex);
            var_dump($txhex);

            $payload = Node($RETURN, "omni_createpayload_dexsell", [$token, $amount, $desire, 9, "0.000001", 1], $RETURN->user["name"]);
            $payload = str_replace("\"","", $payload);
            var_dump($payload);
            
            $txhexmod = Node($RETURN, "omni_createrawtx_opreturn", [$txhex, $payload], $RETURN->user["name"]);
            $txhexmod = str_replace("\"","", $txhexmod);

            $r = json_decode(Node($RETURN, "signrawtransactionwithwallet", [$txhexmod], $RETURN->user["name"]));
            if ($r->complete)
            {
                var_dump($r->hex);

                $networkfee = self::_Weight($RETURN, $r->hex) / 100000000;
                $output[$origin] = (float)$output[$origin] - $networkfee;

                if ($output[$origin] < self::$_minSendingAmount) Fail($RETURN, "dust error");
                $output[$origin] = number_format($output[$origin], 8, ".", "");

                var_dump("Final Output");
                var_dump($output);

                $txhex = Node($RETURN, "createrawtransaction", [$input, $output], $RETURN->user["name"]);
                $txhex = str_replace("\"","", $txhex);

                $payload = Node($RETURN, "omni_createpayload_dexsell", [$token, $amount, $desire, 9, "0.000001", 1], $RETURN->user["name"]);
                $payload = str_replace("\"","", $payload);

                $txhexmod = Node($RETURN, "omni_createrawtx_opreturn", [$txhex, $payload], $RETURN->user["name"]);
                $txhexmod = str_replace("\"","", $txhexmod);

                $r = json_decode(Node($RETURN, "signrawtransactionwithwallet", [$txhexmod], $RETURN->user["name"]));
                if ($r->complete)
                {
                    var_dump($r->hex);

                    $r = json_decode(Node($RETURN, "sendrawtransaction", [$r->hex], $RETURN->user["name"]));
                    Done($RETURN);
                }


            }
            
        }
    }
}