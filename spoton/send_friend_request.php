<?php
include('server.php');

if (!isset($_SESSION['user_id'])) {
    echo "Musisz być zalogowany, aby wysłać zaproszenie do znajomych.s";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['friend_id']) && is_numeric($_POST['friend_id'])) {
    $sender_id = $_SESSION['user_id']; // Assuming the column for the sender is named 'sender_id'
    $receiver_id = $_POST['friend_id']; // Assuming the column for the receiver is named 'receiver_id'

    if ($sender_id == $receiver_id) {
        echo "Nie możesz wysłać zaproszenia do znajomych do samego siebie.";
        exit();
    }

    // Check if the friend request already exists
    $check_query = "SELECT * FROM friend_requests WHERE sender_id = '$sender_id' AND receiver_id = '$receiver_id'";
    $check_result = mysqli_query($db, $check_query);

    if (!$check_result) {
        die("Błąd podczas sprawdzania zaproszenia do znajomych: " . mysqli_error($db));
    }

    if (mysqli_num_rows($check_result) > 0) {
        echo "Zaproszenie do znajomych zostało już wysłane.";
        exit();
    }

    // Insert the friend request
    $insert_query = "INSERT INTO friend_requests (sender_id, receiver_id, created_at) VALUES ('$sender_id', '$receiver_id', NOW())";
    if (mysqli_query($db, $insert_query)) {
        echo "Zaproszenie do znajomych zostało wysłane pomyślnie.";
    } else {
        die("Błąd przy wysyłaniu zaproszenia: " . mysqli_error($db));
    }
} else {
    echo "Błędne żadanie.";
}
?>
