<?php
// Konfiguracja połączenia z bazą danych
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "project";


try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
} catch (PDOException $e) {
    die("Błąd połączenia z bazą danych: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Sprawdzenie, czy użytkownik istnieje
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Generowanie unikalnego tokena
        $token = bin2hex(random_bytes(50));
        $expires_at = date("Y-m-d H:i:s", strtotime('+1 hour'));

        // Wstawienie tokena do bazy danych
        $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$email, $token, $expires_at]);

        // Tworzenie linku resetującego hasło
        $resetLink = "http://localhost/nazwa_folderu/resetuj_haslo.php?token=" . $token;

        // Treść wiadomości
        $subject = "Reset hasła";
        $message = "Kliknij w poniższy link, aby zresetować swoje hasło:\n\n" . $resetLink;
        $headers = "From: no-reply@spoton.pl\r\n" .
                   "Reply-To: no-reply@spoton.pl\r\n" .
                   "Content-Type: text/plain; charset=UTF-8\r\n";

        // Wysyłanie e-maila
        if (mail($email, $subject, $message, $headers)) {
            echo "Wysłano link resetujący na podany adres email.";
        } else {
            echo "Wystąpił błąd podczas wysyłania wiadomości.";
        }
    } else {
        echo "Nie znaleziono użytkownika o podanym adresie email.";
    }
}
?>
