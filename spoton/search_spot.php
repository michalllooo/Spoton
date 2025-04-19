<?php
header("Content-Type: application/json");
require 'server.php';

$query = isset($_GET['query']) ? mysqli_real_escape_string($db, $_GET['query']) : '';
$city_id = isset($_GET['city_id']) ? intval($_GET['city_id']) : null;
$type = isset($_GET['type']) ? mysqli_real_escape_string($db, $_GET['type']) : '';
$max_distance = isset($_GET['max_distance']) ? intval($_GET['max_distance']) : null;

$sql = "SELECT l.id, l.location_name AS name, l.latitude, l.longitude, l.category, l.image_path, 
        (6371 * acos(cos(radians(m.latitude)) * cos(radians(l.latitude)) * cos(radians(l.longitude) - radians(m.longitude)) + sin(radians(m.latitude)) * sin(radians(l.latitude)))) AS distance
        FROM locations l 
        LEFT JOIN miasta m ON m.id = ?";

$params = [$city_id];
$types = "i";

if (!empty($query)) {
    $sql .= " WHERE l.location_name LIKE ?";
    $params[] = "%$query%";
    $types .= "s";
}

if ($type) {
    $sql .= empty($query) ? " WHERE" : " AND";
    $sql .= " l.category = ?";
    $params[] = $type;
    $types .= "s";
}

if ($max_distance && $city_id) {
    $sql .= " HAVING distance <= ?";
    $params[] = $max_distance;
    $types .= "i";
}

$stmt = $db->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$spots = [];
while ($row = $result->fetch_assoc()) {
    $spots[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'latitude' => $row['latitude'],
        'longitude' => $row['longitude'],
        'category' => $row['category'],
        'image_path' => $row['image_path'],
        'distance' => isset($row['distance']) ? $row['distance'] : null // Include distance if calculated
    ];
}

echo json_encode($spots);
$stmt->close();
?>
