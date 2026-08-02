<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// IMPORT PHPMailer
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';


// =======================================================
// ACCETTA SOLO RICHIESTE POST
// =======================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}


// =======================================================
// HONEYPOT
// =======================================================

if (!empty($_POST['website'])) {
    http_response_code(403);
    exit;
}


// =======================================================
// CLOUDFLARE TURNSTILE
// =======================================================

$token = $_POST['cf-turnstile-response'] ?? '';

if (empty($token)) {
    http_response_code(403);
    exit("Captcha mancante.");
}

$data = http_build_query([
    'secret'   => '0x4AAAAAAEEWueMhKynopud5jAV-N-dRLOI',
    'response' => $token,
    'remoteip' => $_SERVER['REMOTE_ADDR']
]);

$ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);
$curlError = curl_error($ch);
curl_close($ch);

if ($response === false) {
    http_response_code(500);
    exit("Errore nella verifica del captcha: " . $curlError);
}

$result = json_decode($response, true);

if (empty($result['success'])) {
    http_response_code(403);
    exit("Captcha non valido.");
}



// =======================================================
// TIPO FORM
// =======================================================

$formType = $_POST['form_type'] ?? '';


// =======================================================
// CREA MAIL
// =======================================================

$mail = new PHPMailer(true);

try {

    $mail->isSMTP();
    $mail->Host = 'smtps.aruba.it';
    $mail->SMTPAuth = true;

    $mail->Username = 'info@almuseo.it';
    $mail->Password = 'Malecon1976!';

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->setFrom('info@almuseo.it', 'Sito Web');
    $mail->addAddress('info@almuseo.it');

    $mail->isHTML(true);


    // =======================================================
    // FORM CONTATTI
    // =======================================================

    if ($formType === 'contatti') {

        $nome = trim(htmlspecialchars($_POST['nome'] ?? ''));
        $cognome = trim(htmlspecialchars($_POST['cognome'] ?? ''));
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $telefono = trim(htmlspecialchars($_POST['telefono'] ?? ''));
        $indirizzo = trim(htmlspecialchars($_POST['indirizzo'] ?? ''));
        $citta = trim(htmlspecialchars($_POST['citta'] ?? ''));
        $messaggio = trim(htmlspecialchars($_POST['messaggio'] ?? ''));

        if (!$email) {
            http_response_code(400);
            exit("Email non valida.");
        }

        if (
            strlen($nome) > 50 ||
            strlen($cognome) > 50 ||
            strlen($telefono) > 25 ||
            strlen($indirizzo) > 120 ||
            strlen($citta) > 50 ||
            strlen($messaggio) > 3000
        ) {
            http_response_code(400);
            exit("Dati non validi.");
        }

        $mail->addReplyTo($email, "$nome $cognome");

        $mail->Subject = "Nuovo messaggio dal sito web";

        $mail->Body = "
            <h2>Nuovo messaggio (Contatti)</h2>

            <p><b>Nome:</b> $nome $cognome</p>
            <p><b>Email:</b> $email</p>
            <p><b>Telefono:</b> $telefono</p>
            <p><b>Indirizzo:</b> $indirizzo</p>
            <p><b>Città:</b> $citta</p>

            <hr>

            <p><b>Messaggio:</b><br>$messaggio</p>
        ";

        $mail->AltBody = "
Nome: $nome $cognome
Email: $email
Telefono: $telefono
Indirizzo: $indirizzo
Città: $citta

Messaggio:
$messaggio";

    }


    // =======================================================
    // PRENOTAZIONE LIBRI
    // =======================================================

    elseif ($formType === 'prenotazione') {

        $nome = trim(htmlspecialchars($_POST['nome'] ?? ''));
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $telefono = trim(htmlspecialchars($_POST['telefono'] ?? ''));
        $istituto = trim(htmlspecialchars($_POST['istituto'] ?? ''));
        $classe = trim(htmlspecialchars($_POST['classe'] ?? ''));
        $localita = trim(htmlspecialchars($_POST['localita'] ?? ''));
        $messaggio = trim(htmlspecialchars($_POST['messaggio'] ?? ''));
        $indirizzo = trim(htmlspecialchars($_POST['indirizzo'] ?? ''));

        $spedizione = isset($_POST['spedizione']) ? "SI" : "NO";

        if (!$email) {
            http_response_code(400);
            exit("Email non valida.");
        }

        if (
            strlen($nome) > 50 ||
            strlen($telefono) > 25 ||
            strlen($istituto) > 120 ||
            strlen($classe) > 50 ||
            strlen($localita) > 60 ||
            strlen($indirizzo) > 120 ||
            strlen($messaggio) > 3000
        ) {
            http_response_code(400);
            exit("Dati non validi.");
        }

        $mail->addReplyTo($email, $nome);

        $mail->Subject = "Prenotazione libri";

        $mail->Body = "
            <h2>Nuova prenotazione libri</h2>

            <p><b>Nome:</b> $nome</p>
            <p><b>Email:</b> $email</p>
            <p><b>Telefono:</b> $telefono</p>

            <hr>

            <p><b>Istituto:</b> $istituto</p>
            <p><b>Classe:</b> $classe</p>
            <p><b>Località:</b> $localita</p>

            <hr>

            <p><b>Messaggio:</b><br>$messaggio</p>

            <hr>

            <p><b>Spedizione:</b> $spedizione</p>
        ";

        if ($spedizione === "SI") {
            $mail->Body .= "<p><b>Indirizzo:</b> $indirizzo</p>";
        }

        $mail->AltBody = "
Nome: $nome
Email: $email
Telefono: $telefono

Istituto: $istituto
Classe: $classe
Località: $localita

Messaggio:
$messaggio

Spedizione: $spedizione
Indirizzo: $indirizzo";

    }

    else {
        http_response_code(400);
        exit("Tipo form non valido.");
    }

    $mail->send();

    echo "OK";

} catch (Exception $e) {

    http_response_code(500);
    echo "Errore invio: {$mail->ErrorInfo}";
}