<?php
require 'vendor/autoload.php'; // Upewnij się, że biblioteka została zainstalowana przez Composer

use ConsoleTVs\Profanity\Builder;

// Jeśli to jest endpoint API, nie wyświetlaj outputu
$is_api = false;
if (strpos($_SERVER['SCRIPT_NAME'], 'save_location.php') !== false || 
    strpos($_SERVER['SCRIPT_NAME'], 'delete_location.php') !== false) {
    $is_api = true;
    // Ukryj błędy dla endpointów API
    error_reporting(0);
    ini_set('display_errors', 0);
}

session_start();

// Dane do połączenia z bazą danych
$server = "localhost";
$username = "root";
$password = "";
$database = "spoton";

// Połączenie z bazą danych
$db = mysqli_connect($server, $username, $password, $database);

// Sprawdzenie połączenia
if (!$db) {
    if ($is_api) {
        header('Content-Type: application/json');
        die(json_encode(['success' => false, 'message' => 'Nie udało się połączyć z bazą danych']));
    } else {
        die("Połączenie nieudane: " . mysqli_connect_error());
    }
}

// Inicjalizacja tablicy błędów
$errors = [];

// REJESTRACJA UŻYTKOWNIKA
if (isset($_POST['reg_user'])) {
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $email = mysqli_real_escape_string($db, $_POST['email']);
    $password_1 = mysqli_real_escape_string($db, $_POST['password_1']);
    $password_2 = mysqli_real_escape_string($db, $_POST['password_2']);
    $city_id = mysqli_real_escape_string($db, $_POST['city_id']);

    if (empty($username)) { array_push($errors, "Nazwa użytkownika jest wymagana"); }

    // Filtrowanie wulgaryzmów w nazwie użytkownika
    $clean_username = Builder::blocker($username)->filter();
    if ($clean_username !== $username) {
        array_push($errors, "Nazwa użytkownika zawiera nieodpowiednie słowa i została zmodyfikowana.");
        $username = $clean_username; // Zastąp zmodyfikowaną wersją
    }

    if (empty($email)) { array_push($errors, "Adres e-mail jest wymagany"); }
    if (empty($password_1)) { array_push($errors, "Hasło jest wymagane"); }

    // Sprawdzenie, czy hasło spełnia wymagania
    if (strlen($password_1) < 8 || 
        !preg_match('/[A-Z]/', $password_1) || 
        !preg_match('/[a-z]/', $password_1) || 
        !preg_match('/[\W_]/', $password_1)) {
        array_push($errors, "Hasło musi mieć co najmniej 8 znaków, zawierać wielką i małą literę oraz znak specjalny.");
    }

    // Sprawdzenie zgodności haseł
    if ($password_1 !== $password_2) {
        array_push($errors, "Hasła nie są zgodne.");
    }

    $user_check_query = "SELECT * FROM users WHERE username='$username' OR email='$email' LIMIT 1";
    $result = mysqli_query($db, $user_check_query);
    $user = mysqli_fetch_assoc($result);
  
    if ($user) { 
        if ($user['username'] === $username) {
            array_push($errors, "Nazwa użytkownika już istnieje");
        }
        if ($user['email'] === $email) {
            array_push($errors, "Adres e-mail już istnieje");
        }
    }

    if (count($errors) == 0) {
        $password = md5($password_1); // Poprawka: wcześniej było błędnie $password

        $query = "INSERT INTO users (username, email, password) 
                  VALUES('$username', '$email', '$password')";
        mysqli_query($db, $query);

        // Pobierz ID nowego użytkownika
        $user_id = mysqli_insert_id($db);

        $city_query = "INSERT INTO user_cities (user_id, city_id) VALUES ('$user_id', '$city_id')";
        mysqli_query($db, $city_query);

        $_SESSION['user_id'] = $user_id; // Zapisz ID użytkownika do sesji
        $_SESSION['username'] = $username;
        $_SESSION['success'] = "Jesteś teraz zalogowany";
        header('location: index.php');
    }
}

// LOGOWANIE UŻYTKOWNIKA
if (isset($_POST['login_user'])) {
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $password = mysqli_real_escape_string($db, $_POST['password']);

    if (empty($username)) {
        array_push($errors, "Nazwa użytkownika jest wymagana");
    }
    if (empty($password)) {
        array_push($errors, "Hasło jest wymagane");
    }

    if (count($errors) == 0) {
        $password = md5($password); // Hasło zaszyfrowane tak samo jak przy rejestracji
        $query = "SELECT id, username FROM users WHERE username='$username' AND password='$password' LIMIT 1";
        $results = mysqli_query($db, $query);

        if (mysqli_num_rows($results) == 1) {
            $user = mysqli_fetch_assoc($results);

            $_SESSION['user_id'] = $user['id']; // Zapis ID użytkownika do sesji
            $_SESSION['username'] = $user['username'];
            $_SESSION['success'] = "Jesteś teraz zalogowany";

            header('location: index.php');
        } else {
            array_push($errors, "Nieprawidłowa nazwa użytkownika lub hasło");
        }
    }
}
?>