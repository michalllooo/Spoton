<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Użytkownik nie jest zalogowany.']);
    exit;
}

$user_id = $_SESSION['user_id'];

require 'db_connection.php';

// Sprawdzenie połączenia
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Błąd połączenia: ' . $conn->connect_error]);
    exit;
}

// Pobierz listę znajomych
$stmt = $conn->prepare("
    SELECT u.id AS friend_id, u.username
    FROM friends f
    JOIN users u ON f.friend_id = u.id
    WHERE f.user_id = ?
");
if (!$stmt) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Błąd przygotowania zapytania: ' . $conn->error]);
    exit;
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$friends = [];
while ($row = $result->fetch_assoc()) {
    $friends[] = $row;
}

header('Content-Type: application/json');
echo json_encode(['success' => true, 'friends' => $friends]);
?>