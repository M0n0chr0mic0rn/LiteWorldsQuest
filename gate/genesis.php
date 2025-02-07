<?php
# zuerst schauen wir nach der "method" Variable
if (!isset($_GET["method"])) 
{
    # wenn diese nicht gesetzt ist leiten wir zur Haupt oder zu Infoseiten weiter
    if (!isset($_GET["info"]))
    {
        # Erlaube nur Scripts aus sicheren Quellen
        header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self';");
        include("genesis.html");
    }
    else
    {
        switch ($_GET["info"]) 
        {
            case "LWQW":
                include("./LWQWinfo.html");
            break;

            case "TWOS":
                include("./TWOSinfo.html");
            break;

            case "docs":
                include("./docs.html");
            break;

            case "BinaryArt":
                header("Access-Control-Allow-Origin:*");
                include("./BinaryArt.csv");
            break;
            
            default:
                echo "Page not found";
            break;
        }
    }
} 
else 
{
    # wenn die Variable "method" gesetzt ist gehen wir in die API

    # Fehler anzeigen
    ini_set("display_errors", 1);
    //error_reporting(E_ALL ^ E_WARNING);
    error_reporting(E_ALL);

    # den Inhalt setzen wir auf JSON
    header("Content-type: application/json; charset=utf-8");

    # Zugang erlauben von ? in unserem Fall alle
    header("Access-Control-Allow-Origin:*");

    # START globale Funktionen

    # Abbruch und Anzeige des Vorgangs
    function Fail($RETURN, $error)
    {
        $RETURN->error = $error;
        Pretty($RETURN);
        echo json_encode($RETURN, JSON_PRETTY_PRINT);
        exit;
    }

    # Anzeigen des erfolgreichen Vorgangs
    function Done($RETURN)
    {
        $RETURN->bool = true;
        unset($RETURN->error);
        echo json_encode($RETURN, JSON_PRETTY_PRINT);
        exit;
    }

    # Anzeigen des erfolgreichen Vorgangs
    function DoneLTC($RETURN)
    {
        Pretty($RETURN);
        unset($RETURN->error);
        unset($RETURN->bool);
        unset($RETURN->ip);
        unset($RETURN->response);
        echo json_encode($RETURN, JSON_PRETTY_PRINT);
        exit;
    }

    # Hinzufügen von Statusmeldung
    function Response($RETURN, $response)
    {
        $RETURN->response[count($RETURN->response)] = $response;
    }

    # Entfernen sensibler Daten
    function Pretty($RETURN)
    {
        if ($RETURN->action != "login")
        {
            try {
                unset($RETURN->authkey);
            } catch (\Throwable $th) {
                //throw $th;
            }
        }

        try {
            unset($RETURN->user);
        } catch (\Throwable $th) {
            //throw $th;
        }

        try {
            unset($RETURN->security);
        } catch (\Throwable $th) {
            //throw $th;
        }

        try {
            unset($RETURN->action);
        } catch (\Throwable $th) {
            //throw $th;
        }

        try {
            unset($RETURN->keyring);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    # Funktion für den Datenaustausch mit dem Litecoin Node
    function Node($RETURN, $method, $params = array(), $wallet = null, $rpc_url = "http://192.168.0.165:10000/") 
    {
        if ($wallet) $rpc_url .= "wallet/" . urlencode($wallet);  # Wallet-Namen an die URL anhängen

        # Die JSON-RPC-Daten für den Request
        $request_data = array(
            "jsonrpc" => "1.0",
            "id" => "curltest",  # Eine beliebige ID für die Anfrage
            "method" => $method,  # Die Methode, die ausgeführt werden soll (z.B. "getblock")
            "params" => $params,  # Die Parameter für die Methode
        );

        # JSON-Daten kodieren
        $json_data = json_encode($request_data);
        //var_dump($json_data);

        # HTTP-Kontext-Optionen für die Anfrage
        $options = array(
            "http" => array(
                "method"  => "POST",  # POST-Methode verwenden
                "header"  => "Content-Type: application/json\r\n" .
                "Authorization: Basic " . base64_encode("user:password") . "\r\n",  # Authentifizierung
                "content" => $json_data,  # Die JSON-Daten, die gesendet werden
            ),
        );

        # Kontext erstellen
        $context = stream_context_create($options);

        # POST-Request absenden und Antwort empfangen
        $response = file_get_contents($rpc_url, false, $context);

        # Fehlerbehandlung
        if ($response === FALSE) return false;

        # Die Antwort in ein Array dekodieren
        return json_encode(json_decode($response, true)["result"], JSON_PRETTY_PRINT);
    }

    # ENDE globale Funktionen


    # Das RETURN Objekt welches am Ende alle Prozzessdaten enthält
    $RETURN = (object)array();
    $RETURN->response = array("initialize");
    $RETURN->error = "";
    $RETURN->bool = false;
    $RETURN->ip = $_SERVER["REMOTE_ADDR"];
    $RETURN->security = array();
    $RETURN->user = array();
    $RETURN->action = $_GET["method"];
    
    # AuthKey überprüfen
    if (isset($_GET["authkey"]))
    {
        $RETURN->authkey = preg_replace( "/[^a-zA-Z0-9]/", "", $_GET["authkey"]);
        if ($RETURN->authkey == NULL) Fail($RETURN, "authkey cant be null");
        if (strlen($RETURN->authkey) != 420) Fail($RETURN, "Invalid authkey");
        Response($RETURN, "authkey valid");
    }

    # aufruf der Klassen
    #require_once("../api/telegram.php");
    require_once("../api/user.php");
    require_once("../api/litecoin.php");

    # Erstellen der Klassen und Zuweißung auf Varibale/Objekt
    $USER = new User();
    $LITECOIN = new Litecoin();

    # API Endpunkte einbinden
    include("../api/endpoints.php");
}

# Security Helfer
#filter_var(), htmlspecialchars(), oder intval()

/*
1. filter_var()

Die Funktion filter_var() wird verwendet, um eine Eingabe zu validieren oder zu bereinigen. 
Sie kann Daten sowohl auf Gültigkeit als auch auf ein bestimmtes Format prüfen und dabei eine Reihe von Filter-Optionen anwenden. 
Sie ist sehr nützlich, um Benutzereingaben zu überprüfen, bevor sie weiterverarbeitet werden, und um sicherzustellen, 
dass sie bestimmte Anforderungen erfüllen (z.B. eine gültige E-Mail-Adresse oder URL).
Beispiel:

$email = "someone@example.com";
if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Die E-Mail-Adresse ist gültig.";
} else {
    echo "Die E-Mail-Adresse ist ungültig.";
}

Häufig verwendete Filter:

    FILTER_VALIDATE_EMAIL: Prüft, ob eine E-Mail-Adresse gültig ist.
    FILTER_VALIDATE_URL: Prüft, ob eine URL gültig ist.
    FILTER_SANITIZE_STRING: Entfernt HTML-Tags und andere schadhafte Zeichen.
    FILTER_SANITIZE_EMAIL: Entfernt ungültige Zeichen aus einer E-Mail-Adresse.

2. htmlspecialchars()

Die Funktion htmlspecialchars() wird verwendet, um Zeichen, die in HTML eine spezielle Bedeutung haben, in HTML-Entities umzuwandeln. 
Dies schützt vor Cross-Site Scripting (XSS)-Angriffen, indem verhindert wird, dass schadhafter JavaScript-Code in einer Webseite ausgeführt wird.
Beispiel:

$user_input = "<script>alert("XSS Attack")</script>";
$safe_input = htmlspecialchars($user_input, ENT_QUOTES, "UTF-8");
echo $safe_input;  # Gibt &lt;script&gt;alert(&quot;XSS Attack&quot;)&lt;/script&gt; aus


3. intval()

Die Funktion intval() wird verwendet, um eine Eingabe in einen Ganzzahlwert (Integer) umzuwandeln. 
Sie kann auch als eine Art Typumwandlung genutzt werden, um sicherzustellen, dass eine Eingabe ein gültiger Integer-Wert ist. 
Wenn die Eingabe keine gültige Zahl ist, gibt intval() den Standardwert 0 zurück.
Beispiel:

$input = "123abc";
$int_value = intval($input);  # Gibt 123 zurück
echo $int_value;  # Gibt 123 aus


*/