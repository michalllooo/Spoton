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

// Pobierz oczekujące zaproszenia
$stmt = $conn->prepare("
    SELECT fr.id AS request_id, u.username AS sender_name
    FROM friend_requests fr
    JOIN users u ON fr.sender_id = u.id
    WHERE fr.receiver_id = ? AND fr.status = 'pending'
");
if (!$stmt) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Błąd przygotowania zapytania: ' . $conn->error]);
    exit;
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$requests = [];
while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}

header('Content-Type: application/json');
echo json_encode(['success' => true, 'requests' => $requests]);
?>