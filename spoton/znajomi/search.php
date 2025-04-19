<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

require 'db_connection.php';

header('Content-Type: application/json');

// Debug: Log the search query
error_log("Search query: " . $_GET['query']);

if (!isset($_GET['query'])) {
    echo json_encode(['success' => false, 'message' => 'Brak zapytania']);
    exit;
}

$query = $_GET['query'];
$stmt = $conn->prepare("SELECT id, username FROM users WHERE username LIKE ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Błąd przygotowania zapytania: ' . $conn->error]);
    exit;
}

$searchTerm = "%$query%";
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    // Debug: Log each found user
    error_log("Found user: " . json_encode($row));
    $users[] = $row;
}

// Debug: Log the final response
$response = ['success' => true, 'users' => $users];
error_log("Final response: " . json_encode($response));

echo json_encode($response);
$stmt->close();
?>