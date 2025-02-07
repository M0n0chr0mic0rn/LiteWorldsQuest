<?php

class Litecoin
{
    private static $_ServiceFeeFoundation = "MP2bKNDoDGXmG4j5V4aaTNqXhP9ZybLGnk";
    private static $_ServiceFeeFaucet = "MCtYmUDUvjCatos2whjAsPaBr2a1nwA1tG";

    private static $_minSendingAmount = 0.00006;
    private static $_ServiceFee = 0.00025;

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
        $fee = $tx->vsize * 3;
        return $fee;
    }

    function __construct()
    {

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
        //$r = Node($RETURN, "getnewaddress", [$label, $type], $RETURN->user["name"]);
        //var_dump($r);

        if (Node($RETURN, "getnewaddress", [$label, $type], $RETURN->user["name"]))
        {
            Response($RETURN, "new address added");
        }
        else
        {
            Fail($RETURN, "could not add new address");
        }
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

        var_dump($origin);
        var_dump($token);
        var_dump($amount);
        var_dump($desire);
        var_dump($amount2send);
        var_dump($input);
        //var_dump($RETURN);

        if ($amount2send >= (self::$_minSendingAmount + self::$_ServiceFee))
        {
            var_dump("READY TO GO");

            $output = array();

            $output[$origin] = number_format($amount2send - self::$_ServiceFee, 8, ".", "");
            $output[self::_ServiceFeeDestination()] = self::$_ServiceFee;

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
                }


            }
            
        }
    }
}