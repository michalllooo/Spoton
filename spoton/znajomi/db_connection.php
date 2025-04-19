<?php
$host = 'localhost';
$username = "root";
$password = '';
$database = 'spoton';

// Połączenie z bazą danych
$conn = new mysqli($host, $username, $password, $database);

// Sprawdzenie połączenia
if ($conn->connect_error) {
    die('Błąd połączenia: ' . $conn->connect_error);
}

// Ustawienie kodowania znaków
$conn->set_charset('utf8mb4');
?>