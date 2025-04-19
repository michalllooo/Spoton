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

if (!isset($_POST['request_id']) || !isset($_POST['action'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Brak wymaganych danych.']);
    exit;
}

$request_id = $_POST['request_id'];
$action = $_POST['action'];

if ($action === 'accept') {
    // Akceptacja zaproszenia
    $stmt = $conn->prepare("UPDATE friend_requests SET status = 'accepted' WHERE id = ? AND receiver_id = ?");
    $stmt->bind_param("ii", $request_id, $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Dodanie do listy znajomych
        $stmt = $conn->prepare("INSERT INTO friends (user_id, friend_id) SELECT sender_id, receiver_id FROM friend_requests WHERE id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();

        $stmt = $conn->prepare("INSERT INTO friends (user_id, friend_id) SELECT receiver_id, sender_id FROM friend_requests WHERE id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Zaproszenie zaakceptowane.']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Nie udało się znaleźć zaproszenia.']);
    }
} elseif ($action === 'reject') {
    // Odrzucenie zaproszenia
    $stmt = $conn->prepare("UPDATE friend_requests SET status = 'rejected' WHERE id = ? AND receiver_id = ?");
    $stmt->bind_param("ii", $request_id, $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Zaproszenie odrzucone.']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Nie udało się znaleźć zaproszenia.']);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Nieznana akcja.']);
}
?>