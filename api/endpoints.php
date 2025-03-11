<?php

switch ($_GET["method"])
{
    # USER
    case "register":
        # Prüfe alle Parameter ab
        if (!isset($_GET["name"])) Fail($RETURN, "parameter \"name\" missing");
        if (!isset($_GET["pass"])) Fail($RETURN, "parameter \"pass\" missing");
        if (!isset($_GET["email"]) && !isset($_GET["telegram"])) Fail($RETURN, "2fa parameter missing - \"email\" and/or \"telegram\"");
        if (isset($_GET["email"]) && isset($_GET["telegram"])) Fail($RETURN, "too much 2fa parameter - \"email\" or \"telegram\" - we can add more later");
        if (isset($_GET["email"]) && !filter_var($_GET["email"], FILTER_VALIDATE_EMAIL)) Fail($RETURN, "No Email format - doublecheck your input");

        # fange Eingaben auf
        $RETURN->user = array();
        $RETURN->user["name"] = $_GET["name"];
        $RETURN->user["pass"] = $_GET["pass"];
        if (isset($_GET["email"])) $RETURN->user["email"] = $_GET["email"];
        if (isset($_GET["telegram"])) $RETURN->user["telegram"] = $_GET["telegram"];

        # register einleiten
        $USER->Register($RETURN);
        Done($RETURN);
    break;

    case "login":
        # Prüfe alle Parameter ab
        if (!isset($_GET["name"])) Fail($RETURN, "parameter \"name\" missing");
        if (!isset($_GET["pass"])) Fail($RETURN, "parameter \"pass\" missing");

        # fange Eingaben auf
        $RETURN->user["name"] = $_GET["name"];
        $RETURN->user["pass"] = $_GET["pass"];

        # login einleiten
        $USER->Login($RETURN);
        Done($RETURN);
    break;

    case "logout":
        # Prüfe alle Parameter ab
        if (!isset($_GET["authkey"])) Fail($RETURN, "parameter \"authkey\" missing");

        $USER->logout($RETURN, $AUTHKEY);
        Done($RETURN);
    break;

    case "execute":
        # Prüfe alle Parameter ab
        if (!isset($_GET["action"])) Fail($RETURN, "parameter \"action\" missing");
        if (!isset($_GET["name"])) Fail($RETURN, "parameter \"name\" missing");
        if (!isset($_GET["copper"])) Fail($RETURN, "copper key - parameter \"copper\" missing");
        if (!isset($_GET["jade"])) Fail($RETURN, "jade key - parameter \"jade\" missing");
        if (!isset($_GET["crystal"])) Fail($RETURN, "crystal key - parameter \"crystal\" missing");

        # fange Eingaben auf
        $RETURN->action = preg_replace( "/[^a-zA-Z0-9]/", "", $_GET["action"]);
        $RETURN->user["name"] = preg_replace( "/[^a-zA-Z0-9]/", "", $_GET["name"]);
        $RETURN->keyring["copper"] = preg_replace( "/[^a-zA-Z0-9]/", "", $_GET["copper"]);
        $RETURN->keyring["jade"] = preg_replace( "/[^a-zA-Z0-9]/", "", $_GET["jade"]);
        $RETURN->keyring["crystal"] = preg_replace( "/[^a-zA-Z0-9]/", "", $_GET["crystal"]);

        # execute einleiten
        $USER->Execute($RETURN);
        Done($RETURN);
    break;

    case "update":
        # Prüfe alle Parameter ab
        if (!isset($_GET["authkey"])) Fail($RETURN, "parameter \"authkey\" missing");
        if (!isset($_GET["key"])) Fail($RETURN, "parameter \"key\" missing");
        if (!isset($_GET["value"])) Fail($RETURN, "parameter \"value\" missing");

        # fange Eingaben auf
        $RETURN->update = array();
        $RETURN->update["key"] = preg_replace( "/[^a-zA-Z0-9]/", "", $_GET["key"]);
        $RETURN->update["value"] = preg_replace( "/[^a-zA-Z0-9]/", "", $_GET["value"]);

        # update einleiten
        $USER->update($RETURN);
        Done($RETURN);
    break;

    case "get":
        # Prüfe alle Parameter ab
        if (!isset($_GET["authkey"])) Fail($RETURN, "parameter \"authkey\" missing");

        $USER->get($RETURN);
        Done($RETURN);
    break;

    case "progress":
        # Prüfe alle Parameter ab
        if (!isset($_GET["name"])) Fail($RETURN, "parameter \"name\" missing");
        if (!isset($_GET["action"])) Fail($RETURN, "parameter \"action\" missing");

        $USER->progress($RETURN, $_GET["name"], $_GET["action"]);
        Done($RETURN);
    break;

    # LITECOIN User
    case "ltc-get":
        if (!isset($_GET["authkey"])) Fail($RETURN, "parameter \"authkey\" missing");

        $USER->get($RETURN);
        $LITECOIN->Wallet($RETURN);
        DoneLTC($RETURN);
    break;

    case "ltc-new-address":
        if (!isset($_GET["authkey"])) Fail($RETURN, "parameter \"authkey\" missing");

        if (!isset($_GET["label"])) $label = "default";
        else $label = preg_replace( "/[^a-zA-Z0-9_]/", "", $_GET["label"]);

        if ($label == "" || $label == NULL) $label = "default";

        if (isset($_GET["type"]))
        {
            if ($_GET["type"] == "legacy" || $_GET["type"] == "p2sh-segwit" || $_GET["type"] == "bech32") $type = $_GET["type"];
            else Fail($RETURN, "unknown addresstype");
        }
        else $type = "bech32";
        

        $USER->get($RETURN);
        $LITECOIN->NewAddress($RETURN, $label, $type);
        Done($RETURN);
    break;

    case "ltc-send-address":
        if (!isset($_GET["authkey"])) Fail($RETURN, "parameter \"authkey\" missing");
        if (!isset($_GET["origin"])) Fail($RETURN, "parameter \"origin\" missing");
        if (!isset($_GET["amount"])) Fail($RETURN, "parameter \"amount\" missing");
        if (!isset($_GET["destination"])) Fail($RETURN, "parameter \"destination\" missing");

        # fange Eingaben auf
        $RETURN->send = array();
        $RETURN->send["origin"] = preg_replace( "/[^a-zA-Z0-9]/", "", $_GET["origin"]);
        $RETURN->send["destination"] = preg_replace( "/[^a-zA-Z0-9]/", "", $_GET["destination"]);

        if (isset($_GET["networkfee"])) $RETURN->send["networkfee"] = floatval($_GET["networkfee"]);
        else $RETURN->send["networkfee"] = 3;
        
        if ($_GET["amount"] == "all")
        {
            $RETURN->send["amount"] = 0;
            $RETURN->send["servicefee"] = false;

            $USER->get($RETURN, true);
            $LITECOIN->SendfromAddressAll($RETURN);
        }
        else
        {
            $RETURN->send["amount"] = floatval(preg_replace( "/[^0-9.]/", "", $_GET["amount"]));

            if (isset($_GET["fee"])) $RETURN->send["servicefee"] = true;
            else $RETURN->send["servicefee"] = false;

            $USER->get($RETURN, true);
            $LITECOIN->SendfromAddress($RETURN);
        }

        
        DoneLTC($RETURN);
        
    break;

    case "ltcomni-token-send":
        if (!isset($_GET["authkey"])) Fail($RETURN, "parameter \"authkey\" missing");
        if (!isset($_GET["origin"])) Fail($RETURN, "parameter \"origin\" missing");
        if (!isset($_GET["destination"])) Fail($RETURN, "parameter \"destination\" missing");
        if (!isset($_GET["token"])) Fail($RETURN, "parameter \"token\" missing");
        if (!isset($_GET["amount"])) Fail($RETURN, "parameter \"amount\" missing");

        $RETURN->send = array();
        $RETURN->send["origin"] = preg_replace( "/[^a-zA-Z0-9]/", "", $_GET["origin"]);
        $RETURN->send["destination"] = preg_replace( "/[^a-zA-Z0-9]/", "", $_GET["destination"]);
        $RETURN->send["token"] = intval($_GET["token"]);
        $RETURN->send["amount"] = number_format(floatval($_GET["amount"]), 8, ".", "");
        $RETURN->send["networkfee"] = 3;

        $USER->get($RETURN);
        $LITECOIN->TokenSend($RETURN);
    break;

    case "ltcomni-token-list":
        if (!isset($_GET["authkey"])) Fail($RETURN, "parameter \"authkey\" missing");
        if (!isset($_GET["origin"])) Fail($RETURN, "parameter \"origin\" missing");
        if (!isset($_GET["token"])) Fail($RETURN, "parameter \"token\" missing");
        if (!isset($_GET["amount"])) Fail($RETURN, "parameter \"amount\" missing");
        if (!isset($_GET["desire"])) Fail($RETURN, "parameter \"desire\" missing");

        $RETURN->send = array();
        $RETURN->send["origin"] = preg_replace( "/[^a-zA-Z0-9]/", "", $_GET["origin"]);
        $RETURN->send["token"] = intval($_GET["token"]);
        $RETURN->send["amount"] = preg_replace( "/[^0-9.]/", "", $_GET["amount"]);
        $RETURN->send["desire"] = preg_replace( "/[^0-9.]/", "", $_GET["desire"]);

        $RETURN->send["networkfee"] = 3;

        $USER->get($RETURN);
        $LITECOIN->TokenList($RETURN);
    break;

    case "ltcomni-token-cancel":
        if (!isset($_GET["authkey"])) Fail($RETURN, "parameter \"authkey\" missing");
        if (!isset($_GET["origin"])) Fail($RETURN, "parameter \"origin\" missing");
        if (!isset($_GET["token"])) Fail($RETURN, "parameter \"token\" missing");

        $RETURN->send = array();
        $RETURN->send["origin"] = preg_replace( "/[^a-zA-Z0-9]/", "", $_GET["origin"]);
        $RETURN->send["token"] = intval($_GET["token"]);
        $RETURN->send["amount"] = 0;
        $RETURN->send["desire"] = 0;
        $RETURN->send["networkfee"] = 3;

        $USER->get($RETURN);
        $LITECOIN->TokenCancel($RETURN);
    break;

    case "ltcomni-token-request":
        if (!isset($_GET["authkey"])) Fail($RETURN, "parameter \"authkey\" missing");
        if (!isset($_GET["origin"])) Fail($RETURN, "parameter \"origin\" missing");
        if (!isset($_GET["destination"])) Fail($RETURN, "parameter \"destination\" missing");
        if (!isset($_GET["token"])) Fail($RETURN, "parameter \"token\" missing");
        if (!isset($_GET["amount"])) Fail($RETURN, "parameter \"amount\" missing");

        $RETURN->send = array();
        $RETURN->send["origin"] = preg_replace( "/[^a-zA-Z0-9]/", "", $_GET["origin"]);
        $RETURN->send["destination"] = preg_replace( "/[^a-zA-Z0-9]/", "", $_GET["destination"]);
        $RETURN->send["token"] = intval($_GET["token"]);
        $RETURN->send["amount"] = number_format(floatval($_GET["amount"]), 8, ".", "");
        $RETURN->send["networkfee"] = 3;

        $USER->get($RETURN);
        $LITECOIN->TokenRequestPurchase($RETURN);
    break;

    # LITECOIN Public
    case "ltc-help":
        header("Content-type: text/plain; charset=utf-8");
        if (isset($_GET["com"]))echo json_decode(Node($RETURN, "help", [preg_replace( "/[^a-zA-Z0-9_]/", "", $_GET["com"])]));
        else echo json_decode(Node($RETURN, "help"));
    break;

    case "ltc-dex-get":
        echo Node($RETURN, "omni_getactivedexsells");
    break;

    case "ltc-property-get":
        if (!isset($_GET["property"])) Fail($RETURN, "parameter \"property\" missing");
        $property = intval($_GET["property"]);
        echo Node($RETURN, "omni_getproperty", [$property]);
    break;

    case "ltc-nft-get":
        if (!isset($_GET["property"])) Fail($RETURN, "parameter \"property\" missing");
        if (!isset($_GET["token"])) Fail($RETURN, "parameter \"token\" missing");

        $property = intval($_GET["property"]);
        $token = intval($_GET["token"]);

        echo Node($RETURN, "omni_getnonfungibletokendata", [$property, $token]);
    break;

    case "ltcomni-get-balance-address":
        if (!isset($_GET["address"])) Fail($RETURN, "parameter \"address\" missing");

        $address = preg_replace( "/[^a-zA-Z0-9]/", "", $_GET["address"]);

        echo Node($RETURN, "omni_getallbalancesforaddress", [$address]);
    break;

    case "ltcomni-get-balance-token":
        if (!isset($_GET["token"])) Fail($RETURN, "parameter \"token\" missing");

        $token = intval($_GET["token"]);

        echo Node($RETURN, "omni_getallbalancesforid", [$token]);
    break;

    # LITECOIN LiteWorlds
    case "ltc-trader-get":
        # trader address - MGTjUjDccbaCQZEyhFHDr1x9SAGwhyxa2L
        echo Node($RETURN, "omni_getnonfungibletokens", ["MGTjUjDccbaCQZEyhFHDr1x9SAGwhyxa2L"]);
    break;

    case "ltc-bridge-kot":
        if (!isset($_GET["destination"])) Fail($RETURN, "parameter \"destination\" missing - Litecoin Omnilite receive Address");
        if (!isset($_GET["amount"])) Fail($RETURN, "parameter \"amount\" missing");

        $address = preg_replace( "/[^a-zA-Z0-9]/", "", $_GET["destination"]);
        $amount = number_format(floatval($_GET["amount"]), 8, ".", "");

        if (substr($address, 0, 1) != "K") Fail($RETURN, "only swap to Kotia K Address type");
        if ((float)$amount < 1.0) Fail($RETURN, "min amount 1.00000000 OmniKotia");
    
        $BRIDGE->CreateOut($address, $amount);
        Done($RETURN);
    break;

    # KOTIA LiteWorlds
    case "kot-bridge-ltc":
        if (!isset($_GET["destination"])) Fail($RETURN, "parameter \"destination\" missing - Litecoin Omnilite receive Address");
        if (!isset($_GET["amount"])) Fail($RETURN, "parameter \"amount\" missing");

        $address = preg_replace( "/[^a-zA-Z0-9]/", "", $_GET["destination"]);
        $amount = number_format(floatval($_GET["amount"]), 8, ".", "");

        if (substr($address, 0, 1) != "M") Fail($RETURN, "only swap to Litecoin M Address type");
        if ((float)$amount < 1.0) Fail($RETURN, "min amount 1.00000000 Kotia");
    
        $BRIDGE->CreateSwap($address, $amount);
        Done($RETURN);
    break;

    default:
        Fail($RETURN, "Method not found");
    break;
}