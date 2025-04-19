<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

session_start();
include('server.php');

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Musisz być zalogowany, aby dodać miejscówkę.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $user_id = $_SESSION['user_id'];
        $location_name = isset($_POST['location_name']) ? mysqli_real_escape_string($db, $_POST['location_name']) : '';
        $category = isset($_POST['category']) ? mysqli_real_escape_string($db, $_POST['category']) : '';
        $latitude = isset($_POST['latitude']) ? (float)$_POST['latitude'] : 0;
        $longitude = isset($_POST['longitude']) ? (float)$_POST['longitude'] : 0;
        $image = isset($_FILES['image']) ? $_FILES['image'] : null;

        // Sprawdź, czy użytkownik dodał już 5 miejscówek w ciągu ostatnich 15 minut
        $cooldown_query = "
            SELECT COUNT(*) AS spot_count 
            FROM locations 
            WHERE user_id = '$user_id' 
              AND created_at >= NOW() - INTERVAL 15 MINUTE
        ";
        $cooldown_result = mysqli_query($db, $cooldown_query);
        $cooldown_data = mysqli_fetch_assoc($cooldown_result);

        if ($cooldown_data['spot_count'] >= 5) {
            echo json_encode(['success' => false, 'message' => 'Możesz dodać maksymalnie 5 miejscówek co 15 minut. Poczekaj chwilę przed dodaniem kolejnych.']);
            exit();
        }

        // Obsługa przesyłania obrazu
        $image_path = null;
        if ($image && $image['error'] == 0) {
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($image['name']);
            if (move_uploaded_file($image['tmp_name'], $target_file)) {
                $image_path = $target_file;
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Nie udało się przesłać obrazu.'
                ]);
                exit;
            }
        }

        if (empty($location_name) || empty($user_id) || empty($latitude) || empty($longitude)) {
            echo json_encode([
                'success' => false,
                'message' => 'Brakuje wymaganych pól.'
            ]);
            exit;
        }

        $insert_query = "
            INSERT INTO locations (user_id, location_name, category, latitude, longitude, image_path, created_at) 
            VALUES ('$user_id', '$location_name', '$category', $latitude, $longitude, '$image_path', NOW())
        ";

        if (mysqli_query($db, $insert_query)) {
            $location_id = mysqli_insert_id($db);
            echo json_encode([
                'success' => true,
                'message' => 'Miejscówka została dodana pomyślnie.',
                'location' => [
                    'id' => $location_id,
                    'name' => $location_name,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'category' => $category,
                    'image_path' => $image_path
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Błąd bazy danych: ' . mysqli_error($db)
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Błąd serwera: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Nieprawidłowa metoda żądania.'
    ]);
}
?>
