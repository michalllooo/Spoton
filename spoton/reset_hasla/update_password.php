<?php
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
    $token = $_POST['token'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password === $confirm_password) {
        // Sprawdzenie tokena
        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
        $stmt->execute([$token]);
        $resetRequest = $stmt->fetch();

        if ($resetRequest) {
            // Aktualizacja hasła w tabeli `users`
            $hashedPassword = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([$hashedPassword, $resetRequest['email']]);

            // Usunięcie tokena po zresetowaniu hasła
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
            $stmt->execute([$token]);

            echo "Twoje hasło zostało zresetowane.";
        } else {
            echo "Nieprawidłowy lub wygasły token.";
        }
    } else {
        echo "Hasła się nie zgadzają.";
    }
}
?>
