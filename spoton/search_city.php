<?php
header("Content-Type: application/json");

// Połączenie z bazą danych
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "spoton";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode([]);
    exit();
}

// Pobranie frazy wyszukiwania z żądania
$query = isset($_GET['query']) ? $_GET['query'] : '';

// Przygotowanie zapytania SQL
$sql = "SELECT id, nazwa AS name, latitude, longitude FROM miasta WHERE nazwa LIKE ?";
$stmt = $conn->prepare($sql);
$searchTerm = "%" . $query . "%";
$stmt->bind_param("s", $searchTerm);

// Wykonanie zapytania
$stmt->execute();
$result = $stmt->get_result();

// Przygotowanie wyników do formatu JSON
$cities = [];
while ($row = $result->fetch_assoc()) {
    $cities[] = [
        "id" => $row['id'], 
        "name" => $row['name'],
        "latitude" => $row['latitude'],
        "longitude" => $row['longitude']
    ];
}

// Zwrócenie wyników w formacie JSON
echo json_encode($cities);

// Zamknięcie połączenia
$stmt->close();
$conn->close();
?>
