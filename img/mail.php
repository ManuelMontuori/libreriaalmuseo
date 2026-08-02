<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// IMPORT PHPMailer
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

// TIPO FORM
$formType = $_POST['form_type'] ?? '';

// CREA OGGETTO MAIL
$mail = new PHPMailer(true);

try {

    //  CONFIG SMTP ARUBA
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

    //  ===============================
    //          FORM CONTATTI 
    //  ===============================
    if ($formType === 'contatti') {

        $nome = htmlspecialchars($_POST['nome'] ?? '');
        $cognome = htmlspecialchars($_POST['cognome'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $telefono = htmlspecialchars($_POST['telefono'] ?? '');
        $indirizzo = htmlspecialchars($_POST['indirizzo'] ?? '');
        $citta = htmlspecialchars($_POST['citta'] ?? '');
        $messaggio = htmlspecialchars($_POST['messaggio'] ?? '');

        if (!$email) {
            echo "Email non valida";
            exit;
        }

        $mail->addReplyTo($email, $nome . ' ' . $cognome);

        $mail->Subject = 'Nuovo messaggio dal sito web';

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
        $messaggio
        ";

    }

    //  ===============================
    //   FORM PRENOTAZIONE LIBRI
    //  ===============================
    elseif ($formType === 'prenotazione') {

        $nome = htmlspecialchars($_POST['nome'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $telefono = htmlspecialchars($_POST['telefono'] ?? '');
        $istituto = htmlspecialchars($_POST['istituto'] ?? '');
        $classe = htmlspecialchars($_POST['classe'] ?? '');
        $localita = htmlspecialchars($_POST['localita'] ?? '');
        $messaggio = htmlspecialchars($_POST['messaggio'] ?? '');
        $indirizzo = htmlspecialchars($_POST['indirizzo'] ?? '');

        $spedizione = isset($_POST['spedizione']) ? 'SI' : 'NO';

        if (!$email) {
            echo "Email non valida";
            exit;
        }

        $mail->addReplyTo($email, $nome);

        $mail->Subject = 'Prenotazione libri';

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

        // aggiunge indirizzo solo se serve
        if ($spedizione === 'SI') {
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
        Indirizzo: $indirizzo
        ";
    }

    else {
        echo "Tipo form non valido";
        exit;
    }

    // INVIO
    $mail->send();
    echo "OK";

} catch (Exception $e) {
    echo "Errore invio: {$mail->ErrorInfo}";
}

?>
