<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Nie jesteś zalogowany.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$friend_id = $_POST['friend_id'] ?? null;

if (!$friend_id) {
    echo json_encode(['success' => false, 'error' => 'Brak identyfikatora znajomego.']);
    exit;
}

require 'db_connection.php';

// Sprawdź, czy użytkownik jest znajomym
$stmt = $conn->prepare("SELECT * FROM friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)");
$stmt->bind_param("iiii", $user_id, $friend_id, $friend_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Nie jesteście znajomymi.']);
    exit;
}

// Usuń rekord z tabeli friends
$stmt = $conn->prepare("DELETE FROM friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)");
$stmt->bind_param("iiii", $user_id, $friend_id, $friend_id, $user_id);
$stmt->execute();

echo json_encode(['success' => true, 'message' => 'Znajomy został usunięty.']);
?>
