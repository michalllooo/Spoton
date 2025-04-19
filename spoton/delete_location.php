<?php
// Połączenie z bazą danych
include('server.php');

// Sprawdzamy, czy mamy przekazane ID lokalizacji
if (isset($_POST['location_id'])) {
    $location_id = $_POST['location_id'];

    // Upewnij się, że ID jest liczbą
    if (is_numeric($location_id)) {
        // Zapytanie SQL do usunięcia lokalizacji
        $query = "DELETE FROM locations WHERE id = '$location_id'";

        if (mysqli_query($db, $query)) {
            echo "<p>Lokalizacja została pomyślnie usunięta.</p>";
        } else {
            echo "<p style='color:red;'>Nie udało się usunąć lokalizacji. Spróbuj ponownie.</p>";
        }
    } else {
        echo "<p style='color:red;'>Nieprawidłowe ID lokalizacji.</p>";
    }
} else {
    echo "<p style='color:red;'>Nie podano ID lokalizacji.</p>";
}
?>
