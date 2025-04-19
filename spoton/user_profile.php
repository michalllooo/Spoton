<?php
include('server.php');

if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    echo "Nieprawidłowe ID użytkownika.";
    exit();
}

$user_id = $_GET['user_id'];

// Pobieranie danych użytkownika
$user_query = "SELECT u.username, 
                      (SELECT m.nazwa FROM user_cities uc 
                       JOIN miasta m ON uc.city_id = m.id 
                       WHERE uc.user_id = u.id LIMIT 1) AS city_name 
               FROM users u 
               WHERE u.id = '$user_id'";
$user_result = mysqli_query($db, $user_query);

if (!$user_result) {
    die("Błąd podczas pobierania danych użytkownika: " . mysqli_error($db));
}

$user = mysqli_fetch_assoc($user_result);

if (!$user) {
    echo "Nie znaleziono użytkownika.";
    exit();
}

// Pobieranie miejsc użytkownika
$spots_query = "SELECT location_name, latitude, longitude 
                FROM locations 
                WHERE user_id = '$user_id'";
$spots_result = mysqli_query($db, $spots_query);

if (!$spots_result) {
    die("Błąd podczas pobierania miejsc użytkownika: " . mysqli_error($db));
}

// Pobieranie znajomych użytkownika
$friends_query = "SELECT u.id, u.username 
                  FROM friends f 
                  JOIN users u ON f.friend_id = u.id 
                  WHERE f.user_id = '$user_id'";
$friends_result = mysqli_query($db, $friends_query);

if (!$friends_result) {
    die("Błąd podczas pobierania znajomych użytkownika: " . mysqli_error($db));
}

// Liczba znajomych
$friends_count_query = "SELECT COUNT(*) AS friend_count 
                        FROM friends 
                        WHERE user_id = '$user_id'";
$friends_count_result = mysqli_query($db, $friends_count_query);

if (!$friends_count_result) {
    die("Błąd podczas zliczania znajomych: " . mysqli_error($db));
}

$friends_count = mysqli_fetch_assoc($friends_count_result)['friend_count'];

// Liczba miejsc dodanych przez użytkownika
$spots_count_query = "SELECT COUNT(*) AS spot_count 
                      FROM locations 
                      WHERE user_id = '$user_id'";
$spots_count_result = mysqli_query($db, $spots_count_query);

if (!$spots_count_result) {
    die("Błąd podczas zliczania miejsc użytkownika: " . mysqli_error($db));
}

$spots_count = mysqli_fetch_assoc($spots_count_result)['spot_count'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profil użytkownika <?php echo htmlspecialchars($user['username']); ?></title>
    <link id="theme-stylesheet" rel="stylesheet" href="style_user_profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .collapsible {
            cursor: pointer;
            padding: 10px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            text-align: left;
            outline: none;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .collapsible:hover {
            background-color: #2980b9;
        }

        .content {
            display: none;
            overflow: hidden;
            margin-top: 10px;
        }

        .content ul {
            margin-top: 0;
        }

        .button-container {
            margin-top: 20px;
        }
    </style>
    <script>
        function updateTheme() {
            const darkTheme = localStorage.getItem("darkTheme") === "true";
            const themeStylesheet = document.getElementById("theme-stylesheet");
            themeStylesheet.href = darkTheme ? "style_user_profile_dark.css" : "style_user_profile.css";
            document.body.classList.toggle("dark-theme", darkTheme);
        }

        document.addEventListener("DOMContentLoaded", updateTheme);
        window.addEventListener("storage", updateTheme);
    </script>
</head>
<body>
    <div class="back-button" onclick="window.location.href='index.php'" title="Powrót do strony głównej">
        <i class="fas fa-arrow-left"></i>
    </div>

    <div class="theme-toggle" id="themeToggle" title="Zmień motyw">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="theme-icon">
            <path class="sun-icon" d="M12 2.25a.75.75 0 01.75.75v2.25a.75.75 0 01-1.5 0V3a.75.75 0 01.75-.75zM7.5 12a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM18.894 6.166a.75.75 0 00-1.06-1.06l-1.591 1.59a.75.75 0 101.06 1.061l1.591-1.59zM21.75 12a.75.75 0 01-.75.75h-2.25a.75.75 0 010-1.5H21a.75.75 0 01.75.75zM17.834 18.894a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 10-1.061 1.06l1.59 1.591zM12 18a.75.75 0 01.75.75V21a.75.75 0 01-1.5 0v-2.25A.75.75 0 0112 18zM7.758 17.303a.75.75 0 00-1.061-1.06l-1.591 1.59a.75.75 0 001.06 1.061l1.591-1.59zM6 12a.75.75 0 01-.75.75H3a.75.75 0 010-1.5h2.25A.75.75 0 016 12zM6.697 7.757a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 00-1.061 1.06l1.59 1.591z"/>
        </svg>
    </div>

    <h1>Profil użytkownika <?php echo htmlspecialchars($user['username']); ?></h1>
    <p>Miasto: <?php echo htmlspecialchars($user['city_name'] ?? 'Nie określono'); ?></p>

    <h2>Spot-y dodane przez użytkownika (<?php echo $spots_count; ?>)</h2>
    <button class="collapsible">Pokaż/Ukryj miejsca</button>
    <div class="content">
        <ul>
            <?php while ($spot = mysqli_fetch_assoc($spots_result)): ?>
                <li>
                    <?php echo htmlspecialchars($spot['location_name']); ?> 
                    (<a href="index.php?lat=<?php echo $spot['latitude']; ?>&lon=<?php echo $spot['longitude']; ?>">Zobacz na mapie</a>)
                </li>
            <?php endwhile; ?>
        </ul>
    </div>

    <h2>Znajomi (<?php echo $friends_count; ?>)</h2>
    <ul>
        <?php while ($friend = mysqli_fetch_assoc($friends_result)): ?>
            <li onclick="window.location.href='user_profile.php?user_id=<?php echo $friend['id']; ?>'" style="cursor: pointer;">
                <?php echo htmlspecialchars($friend['username']); ?>
            </li>
        <?php endwhile; ?>
    </ul>

    <div class="button-container">
        <button onclick="sendFriendRequest(<?php echo $user_id; ?>)" style="padding: 10px 15px; background-color: #4caf50; color: white; border: none; border-radius: 5px; cursor: pointer;">Wyślij zaproszenie do znajomych</button>
    </div>
    <div class="button-container" style="margin-top: 10px;">
        <button onclick="window.location.href='index.php'" style="padding: 10px 15px; background-color: #3498db; color: white; border: none; border-radius: 5px; cursor: pointer;">Powrót</button>
    </div>

    <script>
        // Wysyłanie zaproszenia do znajomych
        function sendFriendRequest(userId) {
            fetch('send_friend_request.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `friend_id=${userId}`
            })
            .then(response => response.text())
            .then(message => alert("Odpowiedź serwera: " + message))
            .catch(error => {
                console.error('Błąd:', error);
                alert("Wystąpił błąd podczas wysyłania zaproszenia do znajomych.");
            });
        }

        // Pokazywanie/ukrywanie miejsc
        document.addEventListener('DOMContentLoaded', function () {
            const collapsible = document.querySelector('.collapsible');
            const content = document.querySelector('.content');

            collapsible.addEventListener('click', function () {
                content.style.display = content.style.display === 'block' ? 'none' : 'block';
            });
        });

        // Zmiana motywu
        const themeToggle = document.getElementById('themeToggle');
        
        themeToggle.addEventListener('click', () => {
            const isDarkTheme = localStorage.getItem('darkTheme') === 'true';
            localStorage.setItem('darkTheme', !isDarkTheme);
            updateTheme();
        });
    </script>
</body>
</html>
