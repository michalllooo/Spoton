<?php
// Połączenie z bazą danych
include('server.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => "Musisz być zalogowany, aby losować miejsca."]);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];
    $latitude = isset($_GET['latitude']) ? (float)$_GET['latitude'] : null;
    $longitude = isset($_GET['longitude']) ? (float)$_GET['longitude'] : null;
    $radius = isset($_GET['radius']) ? (int)$_GET['radius'] : 10; // Domyślny promień 10 km

    if ($latitude === null || $longitude === null) {
        echo json_encode(['error' => "Brak współrzędnych lokalizacji użytkownika."]);
        exit();
    }

    // Zapytanie SQL w celu wylosowania jednego spota w obrębie wybranego promienia
    $query = "
        SELECT latitude, longitude, location_name, image_path,
        (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance
        FROM locations
        WHERE user_id = ?
        HAVING distance <= ?
        ORDER BY RAND()
        LIMIT 1
    ";

    $stmt = $db->prepare($query);
    $stmt->bind_param("dddii", $latitude, $longitude, $latitude, $user_id, $radius);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $spot = $result->fetch_assoc();

        // Zwrócenie wylosowanego spota w formacie JSON
        echo json_encode([
            'latitude' => $spot['latitude'],
            'longitude' => $spot['longitude'],
            'name' => $spot['location_name'],
            'image_path' => $spot['image_path']
        ]);
    } else {
        echo json_encode(['error' => "Brak dostępnych miejsc w wybranym obszarze."]);
    }
} catch (Exception $e) {
    echo json_encode(['error' => "Błąd serwera: " . $e->getMessage()]);
}
?>
