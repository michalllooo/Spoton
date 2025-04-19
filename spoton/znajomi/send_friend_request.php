<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Nie jesteś zalogowany.']);
    exit;
}

$sender_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'] ?? null;

if (!$receiver_id) {
    echo json_encode(['success' => false, 'error' => 'Brak odbiorcy.']);
    exit;
}

// Sprawdzenie, czy użytkownik nie zaprasza samego siebie
if ($sender_id === $receiver_id) {
    echo json_encode(['success' => false, 'error' => 'Nie możesz zaprosić samego siebie.']);
    exit;
}

require 'db_connection.php';

// Sprawdzenie, czy użytkownik już ma tego samego znajomego
$stmt = $conn->prepare("SELECT * FROM friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)");
$stmt->bind_param("iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);
$stmt->execute();
$existing_friend = $stmt->get_result();

if ($existing_friend->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Jesteście już znajomymi.']);
    exit;
}

// Sprawdzenie, czy zaproszenie już zostało wysłane
$stmt = $conn->prepare("SELECT * FROM friend_requests WHERE sender_id = ? AND receiver_id = ? AND status = 'pending'");
$stmt->bind_param("ii", $sender_id, $receiver_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Już wysłałeś zaproszenie do tej osoby.']);
    exit;
}

// Dodanie zaproszenia
$stmt = $conn->prepare("INSERT INTO friend_requests (sender_id, receiver_id, status) VALUES (?, ?, 'pending')");
$stmt->bind_param("ii", $sender_id, $receiver_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Zaproszenie zostało wysłane.']);
} else {
    echo json_encode(['success' => false, 'error' => 'Błąd przy wysyłaniu zaproszenia.']);
}
?>
