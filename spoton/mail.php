<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h3>Your mail</h3>
    <form method="post">
        <input type="email" name="email" placeholder="Enter your email" required>
        <input type="submit" name="button" value="Send Password">
    </form>
</body>

<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';  // Załaduj PHPMailer

$mail = new PHPMailer(true);
if (isset($_POST['button'])) {
    
    $randomNumber = mt_rand(100000, 999999);
try {
    // Konfiguracja SMTP Mailjet
    $mail->isSMTP(); 
    $mail->Host = 'in-v3.mailjet.com';  // Serwer SMTP Mailjet
    $mail->SMTPAuth = true;
    $mail->Username = '85d8718de4d0c1103d9722b65871ec39';  // Twój API Key z Mailjet
    $mail->Password = '819ee8c350d16ce6c573924c92bdc68c';  // Twój API Secret z Mailjet
    $mail->Port = 587;  // Port SMTP dla TLS

    // Ustawienia nadawcy i odbiorcy
    $mail->setFrom('WielkiSpoton@gmail.com', 'SpotON');
    $mail->addAddress($to, 'Recipient Name');  // Adres odbiorcy

    // Treść wiadomości
    $mail->isHTML(true);
    $mail->Subject = 'Spoton Code';
    $mail->Body    = 'Your new password is:'$randomNumber;
    $mail->AltBody = 'This is the plain text version of the test email.';

    // Wysyłanie e-maila
    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
}
?>
</html>
