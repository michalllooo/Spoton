<?php 
include('server.php'); 

$isLoggedIn = isset($_SESSION['username']);
if (!$isLoggedIn) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

$username = $isLoggedIn ? $_SESSION['username'] : '';
$friend_ids = []; // Initialize empty array for friend IDs

if ($isLoggedIn) {
    // Get user ID
    $query = "SELECT id FROM users WHERE username='$username'";
    $result = mysqli_query($db, $query);
    $user = mysqli_fetch_assoc($result);
    $user_id = $user['id'];

    // Fetch user's default city
    $city_query = "SELECT m.latitude, m.longitude FROM user_cities uc 
                   JOIN miasta m ON uc.city_id = m.id 
                   WHERE uc.user_id = '$user_id' LIMIT 1";
    $city_result = mysqli_query($db, $city_query);
    $default_city = mysqli_fetch_assoc($city_result);

    $default_lat = $default_city ? $default_city['latitude'] : 53.4285; // Default latitude
    $default_lon = $default_city ? $default_city['longitude'] : 14.5523; // Default longitude

    // Only fetch friends if we have a valid user_id
    if (isset($user_id)) {
        $friends_query = "SELECT friend_id FROM friends WHERE user_id = '$user_id'";
        $friends_result = mysqli_query($db, $friends_query);
        if ($friends_result) {
            while ($row = mysqli_fetch_assoc($friends_result)) {
                $friend_ids[] = $row['friend_id'];
            }
        }
    }
}

$locations_query = "SELECT l.*, u.username FROM locations l JOIN users u ON l.user_id = u.id";
$locations_result = mysqli_query($db, $locations_query);
$saved_locations = [];
while ($row = mysqli_fetch_assoc($locations_result)) {
    $saved_locations[] = $row;
}

$spot_categories = [
    'jezioro',
    'basen',
    'kąpielisko',
    'plaża',
    'rzeka',
    'morze',
    'góry',
    'szlak',
    'las',
    'park narodowy',
    'widok',
    'park',
    'skatepark',
    'bar',
    'restauracja',
    'sklep',
    'zabytek',
    'miejsce do fotek',
    'galeria',
    'muzeum',
    'kino',
    'teatr',
    'klub',
    'inne',
];
// Pass categories to JavaScript
echo "<script>const spotCategories = " . json_encode($spot_categories) . ";</script>";

?>
<!DOCTYPE html>
<html>
<head>
    <title>Panel - SpotON</title>
    <link id="theme-stylesheet" rel="stylesheet" href="style_glowna_light.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
    <link rel="stylesheet" href="style_popup.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="icon" type="image/x-icon" href="img/spotOn_logo.png">
    <script>
        function updateTheme() {
            const darkTheme = localStorage.getItem("darkTheme") === "true";
            const themeStylesheet = document.getElementById("theme-stylesheet");
            themeStylesheet.href = darkTheme ? "style_glowna_dark.css" : "style_glowna_light.css";
            document.body.classList.toggle("dark-theme", darkTheme);
        }

        document.addEventListener("DOMContentLoaded", updateTheme);
        window.addEventListener("storage", updateTheme);
    </script>
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh; /* Full viewport height */
            overflow: hidden; /* Prevent scrollbars */
            display: flex;
        }
        .sidebar {
            height: 100%; /* Full height for the sidebar */
        }
        .map-container {
            flex: 1; /* Take up remaining space next to the sidebar */
            height: 100%; /* Full height */
        }
        #map {
            height: 100%; /* Fill the container */
            width: 100%; /* Fill the container */
        }
        #spot-search-container {
            display: flex;
            align-items: center;
            margin: 10px 0;
            padding: 10px;
            background-color: #2c3e50; /* Match sidebar background */
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        #filter-options {
            display: none;
            margin-top: 10px;
            background-color: #2c3e50; /* Match sidebar background */
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        #spot-search {
            flex: 1;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-right: 10px;
        }
        #filter-icon {
            cursor: pointer;
            width: 30px;
            height: 30px;
            background-color: #2c3e50; /* Match sidebar background */
            border-radius: 4px;
            padding: 5px;
        }
        #spot-results {
            display: none; /* Hide results by default */
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #fff; /* Keep results background white */
        }
        #spot-results.visible {
            display: block; /* Show results when visible */
        }
        .spot-result-item {
            padding: 12px; /* Increased padding for better visibility */
            cursor: pointer;
            border-bottom: 1px solid #ddd;
            color: #000; /* Ensure text is black */
        }
        .spot-result-item .distance {
            font-size: 0.9em;
            color: #000; /* Ensure black text for distance */
        }
        .spot-result-item:hover {
            background-color: #f0f0f0;
        }
        .no-results {
            color: #000; /* Ensure black text for "Brak wyników" */
        }
        .distance-label {
            font-size: 14px;
            color: #fff; /* White text for "Maksymalna odległość" */
            margin-bottom: 5px;
        }
        #city-filter, #type-filter {
            color: #000; /* Black text for dropdowns */
        }
        .theme-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            background-color: #2c3e50;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .theme-icon {
            width: 24px;
            height: 24px;
            fill: #fff;
        }
        .custom-popup {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 5px;
            border-radius: 0px;
            
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            font-size: 12px;
            color: #333;
            line-height: 1.5;
        }
        .custom-popup a {
            color: #007bff;
            text-decoration: none;
        }
        .custom-popup a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Menu</h2>
        <p id="login">Witaj, <?php echo $isLoggedIn ? htmlspecialchars($username) : "<a href='login.php'>Zaloguj się</a>"; ?>!</p>
        <div id="spot-search-container">
            <input type="text" id="spot-search" placeholder="Wyszukaj Spot-a.">
            <img id="filter-icon" src="img/filter.png" alt="Filtruj" title="Filtruj">
        </div>
        <div id="filter-options" style="display: none; margin-top: 10px; background-color: #2c3e50; padding: 10px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);">
            <div class="filter-item">
                <label for="city-filter">Miasto:</label>
                <select id="city-filter">
                    <option value="">Wszystkie</option>
                    <?php
                    $city_query = "SELECT id, nazwa FROM miasta ORDER BY nazwa ASC";
                    $city_result = mysqli_query($db, $city_query);
                    while ($city = mysqli_fetch_assoc($city_result)) {
                        echo "<option value='{$city['id']}'>{$city['nazwa']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="filter-item">
                <label for="type-filter">Typ miejsca:</label>
                <select id="type-filter">
                    <option value="">Wszystkie</option>
                    <?php foreach ($spot_categories as $key => $label): ?>
                        <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-item">
                <label for="distance-filter" class="distance-label">Maksymalna odległość: <span id="distance-value">10 km</span></label>
                <input type="range" id="distance-filter" min="1" max="50" value="10">
            </div>
            <div style="display: flex; gap: 10px;">
                <button id="apply-filter-btn" style="flex: 1; padding: 8px; background-color: #4caf50; color: white; border: none; border-radius: 4px; cursor: pointer;">Zastosuj filtry</button>
                <button id="clear-filter-btn" style="flex: 1; padding: 8px; background-color: #f44336; color: white; border: none; border-radius: 4px; cursor: pointer;">Usuń filtry</button>
            </div>
        </div>
        <div id="spot-results"></div>
        <ul>
            <?php if ($isLoggedIn): ?>
                <li><a href="losuj.html" id="losuj_spota">Losuj Spot-a</a></li>
                <li><a href="miasto.php">Miasto</a></li>
                <li>
                    <div class="filter-section">
                        <h3>Filtry</h3>
                        <form id="filters-form">
                            <div class="filter-item">
                                <label for="radius">Promień (km):</label>
                                <input type="range" id="radius" name="radius" min="1" max="50" value="10">
                                <span id="radius-value">10 km</span>
                            </div>
                            <div class="filter-item">
                                <label>
                                    <input type="checkbox" id="friends-spots" name="friends-spots">
                                    Tylko miejsca znajomych
                                </label>
                            </div>
                            <button type="button" id="apply-filters">Zastosuj</button>
                        </form>
                    </div>
                </li>
                <li><a href="znajomi/znajomi.html">Znajomi</a></li>
                <li><a href="logout.php" class="logout">Wyloguj się</a></li>
            <?php endif; ?>
        </ul>
        <div id="friend-request-feedback" style="display: none; color: green; font-weight: bold; margin-top: 10px;"></div>
    </div>

    <div class="map-container">
        <div id="map"></div>
        <div id="message"></div>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const defaultLat = <?php echo json_encode($default_lat); ?>;
                const defaultLon = <?php echo json_encode($default_lon); ?>;

                // Initialize the map with default coordinates
                const map = L.map("map").setView([defaultLat, defaultLon], 13);

                L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                }).addTo(map);

                const urlParams = new URLSearchParams(window.location.search);
                const lat = parseFloat(urlParams.get('lat')) || defaultLat;
                const lon = parseFloat(urlParams.get('lon')) || defaultLon;

                // Add a marker if coordinates are provided in the URL
                if (urlParams.has('lat') && urlParams.has('lon')) {
                    const marker = L.marker([lat, lon]).addTo(map).bindPopup("Wybrana Lokalizacja").openPopup();
                    map.setView([lat, lon], 13); // Center the map on the marker
                }

                var locationIcon = L.icon({
                    iconUrl: 'img/icon.png',
                    iconSize: [50, 50],
                    iconAnchor: [25, 50],
                    popupAnchor: [0, -50]
                });

                var userMarker = null;

                // Sprawdzenie, czy błąd już był wyświetlany
                var errorShown = localStorage.getItem("errorShown") === "true";

                function success(pos) {
                    const latitude = pos.coords.latitude;
                    const longitude = pos.coords.longitude;

                    sessionStorage.setItem("latitude", latitude);
                    sessionStorage.setItem("longitude", longitude);

                    if (userMarker) {
                        userMarker.setLatLng([latitude, longitude]); // Przesuwanie istniejącego markera
                    } else {
                        userMarker = L.marker([latitude, longitude], { icon: locationIcon }).addTo(map);
                    }
                }

                function error(err) {
                    if (!errorShown) {
                        if (err.code === 1) {
                            alert("Proszę zezwolić na dostęp do swojej lokalizacji. W przeciwnym razie zostaną użyte domyślne współrzędne.");
                        } else {
                            alert("Nie udało się pobrać lokalizacji, zostaną użyte domyślne współrzędne.");
                        }

                        // Zapisanie w localStorage, aby alert nie pokazywał się po odświeżeniu
                        localStorage.setItem("errorShown", "true");
                    }

                    // Ustawienie domyślnej lokalizacji, np. Warszawa
                    const defaultLatitude = 52.2298;
                    const defaultLongitude = 21.0118;

                    sessionStorage.setItem("latitude", defaultLatitude);
                    sessionStorage.setItem("longitude", defaultLongitude);

                    if (!userMarker) {
                        userMarker = L.marker([defaultLatitude, defaultLongitude], { icon: locationIcon }).addTo(map);
                    }
                }

                // Uruchomienie śledzenia lokalizacji
                navigator.geolocation.watchPosition(success, error);

                // Funkcja obsługująca losowanie spota
                function losujSpota() {
                    $.ajax({
                        url: 'losuj_spot.php', // Skrypt backendowy zwracający wylosowanego spota
                        method: 'GET',
                        success: function (response) {
                            const spotData = JSON.parse(response); // Oczekujemy {latitude: ..., longitude: ...}

                            // Ustaw nowe współrzędne jako default location
                            lat = spotData.latitude;
                            lon = spotData.longitude;

                            // Zmień URL w przeglądarce bez przeładowania strony
                            const newUrl = `${window.location.pathname}?lat=${lat}&lon=${lon}`;
                            window.history.pushState({}, '', newUrl);

                            // Zaktualizuj widok mapy na nowe współrzędne
                            map.setView([lat, lon], 13);

                            // Dodanie markera dla wylosowanego spota
                            L.marker([lat, lon]).addTo(map).bindPopup("Wylosowane miejsce").openPopup();
                        },
                        error: function () {
                            alert("Nie udało się wylosować miejsca. Spróbuj ponownie.");
                        }
                    });
                }

                // Podpięcie funkcji losowania spota do przycisku
                const losujButton = document.getElementById('losuj-spot-btn');
                if (losujButton) {
                    losujButton.addEventListener('click', function () {
                        losujSpota();
                    });
                }

                // Funkcja obsługująca zmianę widoku mapy na wybrane miasto
                function changeDefaultLocation(cityId) {
                    $.ajax({
                        url: 'miasto.php', // Skrypt backendowy, który zwróci współrzędne miasta
                        method: 'GET',
                        data: { id: cityId }, // Wysyłamy ID wybranego miasta
                        success: function (response) {
                            const cityData = JSON.parse(response); // Oczekujemy {latitude: ..., longitude: ...}

                            // Zmień widok mapy na nowe współrzędne
                            lat = cityData.latitude;
                            lon = cityData.longitude;

                            map.setView([lat, lon], 13); // Ustawienie widoku mapy na nowe współrzędne

                            // Dodanie markera dla wybranego miasta
                            L.marker([lat, lon]).addTo(map).bindPopup("Wybrane miasto").openPopup();

                            // Aktualizacja URL z nowymi współrzędnymi
                            const newUrl = `${window.location.pathname}?lat=${lat}&lon=${lon}`;
                            window.history.pushState({}, '', newUrl);
                        },
                        error: function () {
                            alert("Nie udało się załadować danych miasta. Spróbuj ponownie.");
                        }
                    });
                }

                // Obsługa kliknięcia w miasto (przykład: lista miast w menu)
                const cityLinks = document.querySelectorAll(".city-link"); // Przyjmujemy, że miasta mają klasę 'city-link'

                cityLinks.forEach(link => {
                    link.addEventListener("click", function (e) {
                        e.preventDefault();
                        const cityId = this.getAttribute("data-id"); // ID miasta przypisane do atrybutu 'data-id'
                        changeDefaultLocation(cityId); // Wywołaj funkcję zmieniającą domyślną lokalizację
                    });
                });

                const markers = {};
                const isLoggedIn = <?php echo json_encode($isLoggedIn); ?>;
                const currentUserId = <?php echo json_encode($isLoggedIn ? $user_id : null); ?>;
                const savedLocations = <?php echo json_encode($saved_locations); ?>;
                const friendIds = <?php echo json_encode($friend_ids); ?>;

                savedLocations.forEach(function(location) {
                    const latlng = [location.latitude, location.longitude];
                    const isFriendSpot = friendIds.includes(parseInt(location.user_id));
                    
                    const markerIcon = new L.Icon({
                        iconUrl: isFriendSpot ? 'img/icons/markers/marker-icon-green.png' : 'img/icons/markers/marker-icon.png',
                        shadowUrl: 'img/icons/markers/marker-shadow.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    });

                    const marker = L.marker(latlng, { icon: markerIcon }).addTo(map);
                    marker.locationId = location.id;
                    marker.userId = location.user_id;
                    marker.isFriendSpot = isFriendSpot;
                    markers[latlng] = marker;

                    marker.on("click", function() {
                        const popupContent = document.createElement('div');
                        popupContent.className = 'popup-form ' + (marker.userId === currentUserId ? 'existing-spot' : 'other-spot');
                        
                        const nameInput = document.createElement('input');
                        nameInput.className = 'popup-input';
                        nameInput.value = location.location_name;
                        nameInput.readOnly = true;
                        popupContent.appendChild(nameInput);

                        const categoryInfo = document.createElement('p');
                        categoryInfo.textContent = `Kategoria: ${location.category || 'Nie określono'}`;
                        categoryInfo.style.fontSize = '12px';
                        categoryInfo.style.color = '#555';
                        popupContent.appendChild(categoryInfo);

                        const userInfo = document.createElement('p');
                        userInfo.innerHTML = `Dodane przez: <a href="user_profile.php?user_id=${location.user_id}" target="_blank">${location.username}</a>`;
                        userInfo.style.fontSize = '12px';
                        userInfo.style.color = '#555';
                        popupContent.appendChild(userInfo);

                        const googleMapsLink = document.createElement('a');
                        googleMapsLink.href = `https://www.google.com/maps?q=${location.latitude},${location.longitude}`;
                        googleMapsLink.target = '_blank';
                        googleMapsLink.textContent = 'Otwórz w Google Maps';
                        popupContent.appendChild(googleMapsLink);

                        if (location.image_path) {
                            const image = document.createElement('img');
                            image.src = location.image_path;
                            image.className = 'location-image';
                            popupContent.appendChild(image);
                        }

                        const buttonContainer = document.createElement('div');
                        buttonContainer.className = 'button-container';

                        if (marker.userId === currentUserId) {
                            const deleteBtn = document.createElement('button');
                            deleteBtn.className = 'popup-button delete-btn';
                            deleteBtn.textContent = 'Usuń';
                            buttonContainer.appendChild(deleteBtn);

                            deleteBtn.addEventListener('click', function() {
                                $.ajax({
                                    url: 'delete_location.php',
                                    method: 'POST',
                                    data: { location_id: marker.locationId },
                                    success: function(response) {
                                        $('#message').html(response);
                                        map.removeLayer(marker);
                                        delete markers[latlng];
                                    },
                                    error: function() {
                                        $('#message').html("<p style='color:red;'>Nie udało się usunąć lokalizacji. Spróbuj ponownie.</p>");
                                    }
                                });
                            });
                        }

                        popupContent.appendChild(buttonContainer);

                        // Bind the popup content and open it
                        marker.bindPopup(popupContent).openPopup();
                    });

                    // Ensure the popup can be reopened after closing
                    marker.on("popupclose", function() {
                        marker.unbindPopup();
                    });
                });

                if (isLoggedIn) {
                    map.on('click', function(e) {
                        const lat = e.latlng.lat;
                        const lng = e.latlng.lng;
                        const latlngKey = `${lat},${lng}`;

                        if (markers[latlngKey]) {
                            map.removeLayer(markers[latlngKey]);
                            delete markers[latlngKey];
                        } else {
                            const marker = L.marker([lat, lng], {
                                icon: new L.Icon({
                                    iconUrl: 'img/icons/markers/marker-icon.png',
                                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                                    iconSize: [25, 41],
                                    iconAnchor: [12, 41],
                                    popupAnchor: [1, -34],
                                    shadowSize: [41, 41]
                                })
                            }).addTo(map);

                            const popupContent = document.createElement('div');
                            popupContent.className = 'popup-form new-spot';

                            // Name input
                            const inputField = document.createElement('input');
                            inputField.className = 'popup-input';
                            inputField.type = 'text';
                            inputField.placeholder = 'Wprowadź nazwę lokalizacji';

                            // Category select
                            const categorySelect = document.createElement('select');
                            categorySelect.className = 'category-select';
                            Object.entries(spotCategories).forEach(([value, label]) => {
                                const option = document.createElement('option');
                                option.value = value;
                                option.textContent = label;
                                categorySelect.appendChild(option);
                            });

                            // File upload
                            const fileContainer = document.createElement('div');
                            fileContainer.className = 'file-upload';
                            
                            const fileLabel = document.createElement('label');
                            fileLabel.textContent = 'Prześlij zdjęcie';
                            
                            const fileInput = document.createElement('input');
                            fileInput.type = 'file';
                            fileInput.accept = 'image/*';
                            
                            const imagePreview = document.createElement('img');
                            imagePreview.className = 'preview-image';
                            imagePreview.style.display = 'none';

                            fileLabel.appendChild(fileInput);
                            fileContainer.appendChild(fileLabel);
                            fileContainer.appendChild(imagePreview);

                            fileInput.addEventListener('change', function(e) {
                                if (e.target.files && e.target.files[0]) {
                                    const reader = new FileReader();
                                    reader.onload = function(e) {
                                        imagePreview.src = e.target.result;
                                        imagePreview.style.display = 'block';
                                    };
                                    reader.readAsDataURL(e.target.files[0]);
                                }
                            });

                            // Buttons
                            const buttonContainer = document.createElement('div');
                            buttonContainer.className = 'button-container';

                            const saveBtn = document.createElement('button');
                            saveBtn.className = 'popup-button save-btn';
                            saveBtn.textContent = 'Zapisz';

                            const deleteBtn = document.createElement('button');
                            deleteBtn.className = 'popup-button delete-btn';
                            deleteBtn.textContent = 'Anuluj';

                            buttonContainer.appendChild(saveBtn);
                            buttonContainer.appendChild(deleteBtn);

                            // Append all elements
                            popupContent.appendChild(inputField);
                            popupContent.appendChild(categorySelect);
                            popupContent.appendChild(fileContainer);
                            popupContent.appendChild(buttonContainer);

                            marker.bindPopup(popupContent).openPopup();

                            // Add this function to check for profanity
                            function containsProfanity(text) {
                                const profanityList = ['chuj','chuja', 'chujek', 'chuju', 'chujem', 'chujnia',
'chujowy', 'chujowa', 'chujowe', 'cipa', 'cipę', 'cipe', 'cipą',
'cipie', 'dojebać','dojebac', 'dojebie', 'dojebał', 'dojebal',
'dojebała', 'dojebala', 'dojebałem', 'dojebalem', 'dojebałam',
'dojebalam', 'dojebię', 'dojebie', 'dopieprzać', 'dopieprzac',
'dopierdalać', 'dopierdalac', 'dopierdala', 'dopierdalał',
'dopierdalal', 'dopierdalała', 'dopierdalala', 'dopierdoli',
'dopierdolił', 'dopierdolil', 'dopierdolę', 'dopierdole', 'dopierdoli',
'dopierdalający', 'dopierdalajacy', 'dopierdolić', 'dopierdolic',
'dupa', 'dupie', 'dupą', 'dupcia', 'dupeczka', 'dupy', 'dupe', 'huj',
'hujek', 'hujnia', 'huja', 'huje', 'hujem', 'huju', 'jebać', 'jebac',
'jebał', 'jebal', 'jebie', 'jebią', 'jebia', 'jebak', 'jebaka', 'jebal',
'jebał', 'jebany', 'jebane', 'jebanka', 'jebanko', 'jebankiem',
'jebanymi', 'jebana', 'jebanym', 'jebanej', 'jebaną', 'jebana',
'jebani', 'jebanych', 'jebanymi', 'jebcie', 'jebiący', 'jebiacy',
'jebiąca', 'jebiaca', 'jebiącego', 'jebiacego', 'jebiącej', 'jebiacej',
'jebia', 'jebią', 'jebie', 'jebię', 'jebliwy', 'jebnąć', 'jebnac',
'jebnąc', 'jebnać', 'jebnął', 'jebnal', 'jebną', 'jebna', 'jebnęła',
'jebnela', 'jebnie', 'jebnij', 'jebut', 'koorwa', 'kórwa', 'kurestwo',
'kurew', 'kurewski', 'kurewska', 'kurewskiej', 'kurewską', 'kurewska',
'kurewsko', 'kurewstwo', 'kurwa', 'kurwaa', 'kurwami', 'kurwą', 'kurwe',
'kurwę', 'kurwie', 'kurwiska', 'kurwo', 'kurwy', 'kurwach', 'kurwami',
'kurewski', 'kurwiarz', 'kurwiący', 'kurwica', 'kurwić', 'kurwic',
'kurwidołek', 'kurwik', 'kurwiki', 'kurwiszcze', 'kurwiszon',
'kurwiszona', 'kurwiszonem', 'kurwiszony', 'kutas', 'kutasa', 'kutasie',
'kutasem', 'kutasy', 'kutasów', 'kutasow', 'kutasach', 'kutasami',
'matkojebca', 'matkojebcy', 'matkojebcą', 'matkojebca', 'matkojebcami',
'matkojebcach', 'nabarłożyć', 'najebać', 'najebac', 'najebał',
'najebal', 'najebała', 'najebala', 'najebane', 'najebany', 'najebaną',
'najebana', 'najebie', 'najebią', 'najebia', 'naopierdalać',
'naopierdalac', 'naopierdalał', 'naopierdalal', 'naopierdalała',
'naopierdalala', 'naopierdalała', 'napierdalać', 'napierdalac',
'napierdalający', 'napierdalajacy', 'napierdolić', 'napierdolic',
'nawpierdalać', 'nawpierdalac', 'nawpierdalał', 'nawpierdalal',
'nawpierdalała', 'nawpierdalala', 'obsrywać', 'obsrywac', 'obsrywający',
'obsrywajacy', 'odpieprzać', 'odpieprzac', 'odpieprzy', 'odpieprzył',
'odpieprzyl', 'odpieprzyła', 'odpieprzyla', 'odpierdalać',
'odpierdalac', 'odpierdol', 'odpierdolił', 'odpierdolil',
'odpierdoliła', 'odpierdolila', 'odpierdoli', 'odpierdalający',
'odpierdalajacy', 'odpierdalająca', 'odpierdalajaca', 'odpierdolić',
'odpierdolic', 'odpierdoli', 'odpierdolił', 'opieprzający',
'opierdalać', 'opierdalac', 'opierdala', 'opierdalający',
'opierdalajacy', 'opierdol', 'opierdolić', 'opierdolic', 'opierdoli',
'opierdolą', 'opierdola', 'piczka', 'pieprznięty', 'pieprzniety',
'pieprzony', 'pierdel', 'pierdlu', 'pierdolą', 'pierdola', 'pierdolący',
'pierdolacy', 'pierdoląca', 'pierdolaca', 'pierdol', 'pierdole',
'pierdolenie', 'pierdoleniem', 'pierdoleniu', 'pierdolę', 'pierdolec',
'pierdola', 'pierdolą', 'pierdolić', 'pierdolicie', 'pierdolic',
'pierdolił', 'pierdolil', 'pierdoliła', 'pierdolila', 'pierdoli',
'pierdolnięty', 'pierdolniety', 'pierdolisz', 'pierdolnąć',
'pierdolnac', 'pierdolnął', 'pierdolnal', 'pierdolnęła', 'pierdolnela',
'pierdolnie', 'pierdolnięty', 'pierdolnij', 'pierdolnik', 'pierdolona',
'pierdolone', 'pierdolony', 'pierdołki', 'pierdzący', 'pierdzieć',
'pierdziec', 'pizda', 'pizdą', 'pizde', 'pizdę', 'piździe', 'pizdzie',
'pizdnąć', 'pizdnac', 'pizdu', 'podpierdalać', 'podpierdalac',
'podpierdala', 'podpierdalający', 'podpierdalajacy', 'podpierdolić',
'podpierdolic', 'podpierdoli', 'pojeb', 'pojeba', 'pojebami',
'pojebani', 'pojebanego', 'pojebanemu', 'pojebani', 'pojebany',
'pojebanych', 'pojebanym', 'pojebanymi', 'pojebem', 'pojebać',
'pojebac', 'pojebalo', 'popierdala', 'popierdalac', 'popierdalać',
'popierdolić', 'popierdolic', 'popierdoli', 'popierdolonego',
'popierdolonemu', 'popierdolonym', 'popierdolone', 'popierdoleni',
'popierdolony', 'porozpierdalać', 'porozpierdala', 'porozpierdalac',
'poruchac', 'poruchać', 'przejebać', 'przejebane', 'przejebac',
'przyjebali', 'przepierdalać', 'przepierdalac', 'przepierdala',
'przepierdalający', 'przepierdalajacy', 'przepierdalająca',
'przepierdalajaca', 'przepierdolić', 'przepierdolic', 'przyjebać',
'przyjebac', 'przyjebie', 'przyjebała', 'przyjebala', 'przyjebał',
'przyjebal', 'przypieprzać', 'przypieprzac', 'przypieprzający',
'przypieprzajacy', 'przypieprzająca', 'przypieprzajaca',
'przypierdalać', 'przypierdalac', 'przypierdala', 'przypierdoli',
'przypierdalający', 'przypierdalajacy', 'przypierdolić',
'przypierdolic', 'qrwa', 'rozjebać', 'rozjebac', 'rozjebie',
'rozjebała', 'rozjebią', 'rozpierdalać', 'rozpierdalac', 'rozpierdala',
'rozpierdolić', 'rozpierdolic', 'rozpierdole', 'rozpierdoli',
'rozpierducha', 'skurwić', 'skurwiel', 'skurwiela', 'skurwielem',
'skurwielu', 'skurwysyn', 'skurwysynów', 'skurwysynow', 'skurwysyna',
'skurwysynem', 'skurwysynu', 'skurwysyny', 'skurwysyński',
'skurwysynski', 'skurwysyństwo', 'skurwysynstwo', 'spieprzać',
'spieprzac', 'spieprza', 'spieprzaj', 'spieprzajcie', 'spieprzają',
'spieprzaja', 'spieprzający', 'spieprzajacy', 'spieprzająca',
'spieprzajaca', 'spierdalać', 'spierdalac', 'spierdala', 'spierdalał',
'spierdalała', 'spierdalal', 'spierdalalcie', 'spierdalala',
'spierdalający', 'spierdalajacy', 'spierdolić', 'spierdolic',
'spierdoli', 'spierdoliła', 'spierdoliło', 'spierdolą', 'spierdola',
'srać', 'srac', 'srający', 'srajacy', 'srając', 'srajac', 'sraj',
'sukinsyn', 'sukinsyny', 'sukinsynom', 'sukinsynowi', 'sukinsynów',
'sukinsynow', 'śmierdziel', 'udupić', 'ujebać', 'ujebac', 'ujebał',
'ujebal', 'ujebana', 'ujebany', 'ujebie', 'ujebała', 'ujebala',
'upierdalać', 'upierdalac', 'upierdala', 'upierdoli', 'upierdolić',
'upierdolic', 'upierdoli', 'upierdolą', 'upierdola', 'upierdoleni',
'wjebać', 'wjebac', 'wjebie', 'wjebią', 'wjebia', 'wjebiemy',
'wjebiecie', 'wkurwiać', 'wkurwiac', 'wkurwi', 'wkurwia', 'wkurwiał',
'wkurwial', 'wkurwiający', 'wkurwiajacy', 'wkurwiająca', 'wkurwiajaca',
'wkurwić', 'wkurwic', 'wkurwi', 'wkurwiacie', 'wkurwiają', 'wkurwiali',
'wkurwią', 'wkurwia', 'wkurwimy', 'wkurwicie', 'wkurwiacie', 'wkurwić',
'wkurwic', 'wkurwia', 'wpierdalać', 'wpierdalac', 'wpierdalający',
'wpierdalajacy', 'wpierdol', 'wpierdolić', 'wpierdolic', 'wpizdu',
'wyjebać', 'wyjebac', 'wyjebali', 'wyjebał', 'wyjebac', 'wyjebała',
'wyjebały', 'wyjebie', 'wyjebią', 'wyjebia', 'wyjebiesz', 'wyjebie',
'wyjebiecie', 'wyjebiemy', 'wypieprzać', 'wypieprzac', 'wypieprza',
'wypieprzał', 'wypieprzal', 'wypieprzała', 'wypieprzala', 'wypieprzy',
'wypieprzyła', 'wypieprzyla', 'wypieprzył', 'wypieprzyl', 'wypierdal',
'wypierdalać', 'wypierdalac', 'wypierdala', 'wypierdalaj',
'wypierdalał', 'wypierdalal', 'wypierdalała', 'wypierdalala',
'wypierdalać', 'wypierdolić', 'wypierdolic', 'wypierdoli',
'wypierdolimy', 'wypierdolicie', 'wypierdolą', 'wypierdola',
'wypierdolili', 'wypierdolił', 'wypierdolil', 'wypierdoliła',
'wypierdolila', 'zajebać', 'zajebac', 'zajebie', 'zajebią', 'zajebia',
'zajebiał', 'zajebial', 'zajebała', 'zajebiala', 'zajebali', 'zajebana',
'zajebani', 'zajebane', 'zajebany', 'zajebanych', 'zajebanym',
'zajebanymi', 'zajebiste', 'zajebisty', 'zajebistych', 'zajebista',
'zajebistym', 'zajebistymi', 'zajebiście', 'zajebiscie', 'zapieprzyć',
'zapieprzyc', 'zapieprzy', 'zapieprzył', 'zapieprzyl', 'zapieprzyła',
'zapieprzyla', 'zapieprzą', 'zapieprza', 'zapieprzy', 'zapieprzymy',
'zapieprzycie', 'zapieprzysz', 'zapierdala', 'zapierdalać',
'zapierdalac', 'zapierdalaja', 'zapierdalał', 'zapierdalaj',
'zapierdalajcie', 'zapierdalała', 'zapierdalala', 'zapierdalali',
'zapierdalający', 'zapierdalajacy', 'zapierdolić', 'zapierdolic',
'zapierdoli', 'zapierdolił', 'zapierdolil', 'zapierdoliła',
'zapierdolila', 'zapierdolą', 'zapierdola', 'zapierniczać',
'zapierniczający', 'zasrać', 'zasranym', 'zasrywać', 'zasrywający',
'zesrywać', 'zesrywający', 'zjebać', 'zjebac', 'zjebał', 'zjebal',
'zjebała', 'zjebala', 'zjebana', 'zjebią', 'zjebali', 'zjeby',    'Schwanz', 'Schwänze', 'Schwänzen', 'Schwanzlutscher', 'Schwanzkopf',
'Arsch', 'Arschloch', 'Arschlöcher', 'Arschlecken', 'Arschkriecher',
'Scheiße', 'Scheißkerl', 'Scheißdreck', 'Scheißhaufen', 'Scheißkopf',
'Fick', 'Ficken', 'Ficker', 'Fickerei', 'Fick dich', 'Fick mich',
'Hure', 'Hurensohn', 'Hurentochter', 'Hurenkind', 'Hurenbock',
'Miststück', 'Drecksack', 'Dreckschwein', 'Dreckskerl', 'Dreckshure',
'Bastard', 'Dummkopf', 'Vollidiot', 'Blödmann', 'Spasti', 'Penner',
'Wichser', 'Wichserei', 'Wichsgriff', 'Wichstyp', 'Wichslappen',
'Lutscher', 'Schlampe', 'Schlampen', 'Schlampig', 'Schweinehund',
'Kotzbrocken', 'Pissnelke', 'Pissen', 'Pisser', 'Pisskopf', 'Pissflitsche',
'Verpiss dich', 'Hampelmann', 'Hirni', 'Hohlkopf', 'Depp', 'Trottel',
'Krüppel', 'Versager', 'Hampel', 'Dulli', 'Dödel', 'Kackbratze',
'Kacke', 'Kackhaufen', 'Kackspaten', 'Kackvogel', 'Drecksau', 'Schweinebacke',    'fuck', 'fucking', 'fucker', 'motherfucker', 'bullshit', 'shit', 
'bitch', 'son of a bitch', 'bastard', 'asshole', 'dumbass', 'jackass', 
'prick', 'cunt', 'dick', 'dickhead', 'cock', 'cockhead', 'cockface', 
'pussy', 'twat', 'wanker', 'jerk', 'douche', 'douchebag', 'dipshit', 
'crap', 'piss', 'pissed', 'piss off', 'dumbfuck', 'shithead', 'shitface', 
'whore', 'slut', 'skank', 'hoe', 'fuckface', 'ass', 'arse', 'arsehole', 
'screw you', 'goddamn', 'damn', 'hell', 'bloody hell', 'dickweed', 'fucktard']; // Replace with actual profane words
                                const regex = new RegExp(`\\b(${profanityList.join('|')})\\b`, 'i');
                                return regex.test(text);
                            }

                            // Event handlers
                            deleteBtn.addEventListener('click', function() {
                                map.removeLayer(marker);
                                delete markers[latlngKey];
                            });

                            saveBtn.addEventListener('click', function() {
                                const spotName = inputField.value.trim();
                                const category = categorySelect.value;

                                if (!spotName) {
                                    alert("Proszę wprowadzić nazwę lokalizacji.");
                                    return;
                                }

                                // Check for profanity
                                if (containsProfanity(spotName)) {
                                    alert("Nazwa lokalizacji zawiera nieodpowiednie słowa. Proszę użyć innej nazwy.");
                                    return;
                                }

                                $('#message').html("<p>Zapisywanie lokalizacji...</p>");

                                const formData = new FormData();
                                formData.append('user_id', currentUserId);
                                formData.append('location_name', spotName);
                                formData.append('category', category);
                                formData.append('latitude', lat);
                                formData.append('longitude', lng);

                                if (fileInput.files.length > 0) {
                                    formData.append('image', fileInput.files[0]);
                                }

                                fetch('save_location.php', {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(response => response.text())
                                .then(text => {
                                    console.log('Surowa odpowiedź:', text);
                                    try {
                                        const data = JSON.parse(text);
                                        if (data.success) {
                                            // Handle success
                                            $('#message').html("<p style='color:green;'>Lokalizacja została pomyślnie zapisana!</p>");
                                            marker.closePopup();
                                        } else {
                                            $('#message').html("<p style='color:red;'>" + data.message + "</p>");
                                        }
                                    } catch (e) {
                                        console.error('Parse error:', e);
                                        console.log('Response text:', text);
                                        $('#message').html("<p style='color:red;'>Błąd podczas przetwarzania odpowiedzi</p>");
                                    }
                                })
                                .catch(error => {
                                    console.error('Fetch error:', error);
                                    $('#message').html("<p style='color:red;'>Błąd sieci</p>");
                                });
                            });
                        }
                    });
                } else {
                    $('#message').html("<p>Zaloguj się, aby dodać lokalizacje!</p>");
                }

                // Filter functionality
                let radiusCircle = null;
                let userLocation = null;

                // Function to update the circle and filters
                function updateFilters(radius) {
                    if (!userLocation) {
                        return;
                    }

                    const radiusInMeters = radius * 1000; // Convert to meters
                    const showFriendsOnly = document.getElementById('friends-spots').checked;

                    // Remove existing radius circle if any
                    if (radiusCircle) {
                        map.removeLayer(radiusCircle);
                    }

                    // Draw new radius circle centered on user location
                    radiusCircle = L.circle(userLocation, {
                        radius: radiusInMeters,
                        color: '#3388ff',
                        fillColor: '#3388ff',
                        fillOpacity: 0.1
                    }).addTo(map);

                    // Filter markers based on distance and friend status
                    Object.values(markers).forEach(marker => {
                        const markerLatLng = marker.getLatLng();
                        const distance = L.latLng(userLocation).distanceTo(markerLatLng);
                        const isInRadius = distance <= radiusInMeters;

                        if (showFriendsOnly) {
                            // Show user's own spots and friends' spots with full opacity, others with reduced opacity
                            marker.setOpacity(isInRadius && (friendIds.includes(marker.userId) || marker.userId === currentUserId) ? 1 : 0.3);
                        } else {
                            // Show all spots within radius with full opacity, others with reduced opacity
                            marker.setOpacity(isInRadius ? 1 : 0.3);
                        }
                    });
                }

                // Update radius value display and circle when slider moves
                document.getElementById('radius').addEventListener('input', function() {
                    const radius = this.value;
                    document.getElementById('radius-value').textContent = radius + ' km';
                    updateFilters(radius);
                });

                // Handle the apply filters button for the friends-only toggle
                document.getElementById('apply-filters').addEventListener('click', function() {
                    const radius = document.getElementById('radius').value;
                    updateFilters(radius);
                });

                // Initialize filters with default radius
                const initialRadius = document.getElementById('radius').value;
                updateFilters(initialRadius);

                // Update user location when geolocation is successful
                function success(pos) {
                    const latitude = pos.coords.latitude;
                    const longitude = pos.coords.longitude;

                    userLocation = [latitude, longitude];

                    sessionStorage.setItem("latitude", latitude);
                    sessionStorage.setItem("longitude", longitude);

                    if (userMarker) {
                        userMarker.setLatLng(userLocation); // Przesuwanie istniejącego markera
                    } else {
                        userMarker = L.marker(userLocation, { icon: locationIcon }).addTo(map);
                    }

                    // Update filters with the new location
                    updateFilters(document.getElementById('radius').value);
                }

                // Spot search functionality
                const spotSearchInput = document.getElementById('spot-search');
                const spotResultsContainer = document.getElementById('spot-results');
                const filterOptions = document.getElementById("filter-options");
                const filterIcon = document.getElementById("filter-icon");
                const applyFilterBtn = document.getElementById("apply-filter-btn");
                const clearFilterBtn = document.getElementById("clear-filter-btn");
                const cityFilter = document.getElementById("city-filter");
                const typeFilter = document.getElementById("type-filter");
                const distanceFilter = document.getElementById("distance-filter");
                const distanceValue = document.getElementById("distance-value");

                // Update distance value display
                distanceFilter.addEventListener("input", function () {
                    distanceValue.textContent = `${distanceFilter.value} km`;
                });

                // Toggle filter options visibility
                filterIcon.addEventListener("click", function () {
                    filterOptions.style.display = filterOptions.style.display === "none" ? "block" : "none";
                    spotResultsContainer.classList.remove("visible"); // Hide results when toggling filter options
                });

                // Apply filters and search
                applyFilterBtn.addEventListener("click", function () {
                    fetchSpots();
                });

                // Clear filters
                clearFilterBtn.addEventListener("click", function () {
                    cityFilter.value = "";
                    typeFilter.value = "";
                    distanceFilter.value = 10;
                    distanceValue.textContent = "10 km";
                    fetchSpots();
                });

                // Fetch spots based on filters or search query
                function fetchSpots() {
                    const query = spotSearchInput.value.trim();
                    const cityId = cityFilter.value;
                    const type = typeFilter.value;
                    const maxDistance = distanceFilter.value;

                    fetch(`search_spot.php?query=${encodeURIComponent(query)}&city_id=${encodeURIComponent(cityId)}&type=${encodeURIComponent(type)}&max_distance=${encodeURIComponent(maxDistance)}`)
                        .then(response => response.json())
                        .then(data => {
                            spotResultsContainer.innerHTML = '';
                            if (data.length > 0) {
                                data.forEach(spot => {
                                    if (spot.latitude && spot.longitude) { // Ensure the spot has valid coordinates
                                        const resultItem = document.createElement('div');
                                        resultItem.className = 'spot-result-item';
                                        resultItem.innerHTML = `
                                            <div>${spot.name}</div>
                                            <div class="distance">Odległość: ${spot.distance ? spot.distance.toFixed(2) + ' km' : 'N/A'}</div>
                                        `;
                                        resultItem.addEventListener('click', function () {
                                            map.setView([spot.latitude, spot.longitude], 16); // Increased zoom level to 16
                                            const marker = addInteractiveMarker(spot);
                                            marker.openPopup(); // Open the popup immediately
                                            spotResultsContainer.innerHTML = ''; // Clear results
                                            spotSearchInput.value = ''; // Clear input
                                            spotResultsContainer.classList.remove("visible"); // Hide results
                                        });
                                        spotResultsContainer.appendChild(resultItem);
                                    }
                                });
                                spotResultsContainer.classList.add("visible"); // Show results if valid spots exist
                            } else {
                                spotResultsContainer.classList.remove("visible"); // Hide results if no valid spots
                            }
                        })
                        .catch(error => console.error('Błąd podczas pobierania miejsc:', error));
                }

                // Show results only when the search input is focused
                spotSearchInput.addEventListener('focus', function () {
                    if (spotResultsContainer.innerHTML.trim() !== '') {
                        spotResultsContainer.classList.add("visible");
                    }
                });

                // Hide results when the search input loses focus
                spotSearchInput.addEventListener('blur', function () {
                    setTimeout(() => spotResultsContainer.classList.remove("visible"), 200); // Delay to allow click events
                });

                // Trigger search on input
                spotSearchInput.addEventListener('input', fetchSpots);

                // Tworzenie niestandardowej kontrolki
                const attributionControl = L.control({ position: 'bottomleft' });

                attributionControl.onAdd = function (map) {
                    const div = L.DomUtil.create('div', 'custom-popup');
                    div.innerHTML = `
                        <p>
                            <a href="https://leafletjs.com/" target="_blank">Leaflet</a> | 
                            &copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a>
                        </p>
                    `;
                    return div;
                };

                // Dodanie kontrolki do mapy
                attributionControl.addTo(map);
            });

        // Function to create a popup for a SPOT
        function createSpotPopup(spot) {
            const popupContent = document.createElement('div');
            popupContent.className = 'popup-form existing-spot';

            const nameInput = document.createElement('input');
            nameInput.className = 'popup-input';
            nameInput.value = spot.name;
            nameInput.readOnly = true;
            popupContent.appendChild(nameInput);

            const googleMapsLink = document.createElement('a');
            googleMapsLink.href = `https://www.google.com/maps?q=${spot.latitude},${spot.longitude}`;
            googleMapsLink.target = '_blank';
            googleMapsLink.textContent = 'Otwórz w Google Maps';
            popupContent.appendChild(googleMapsLink);

            if (spot.image_path) {
                const image = document.createElement('img');
                image.src = spot.image_path;
                image.className = 'location-image';
                popupContent.appendChild(image);
            }

            return popupContent;
        }

        // Function to add an interactive marker
        function addInteractiveMarker(spot) {
            const marker = L.marker([spot.latitude, spot.longitude]).addTo(map);
            const popupContent = createSpotPopup(spot);
            marker.bindPopup(popupContent).openPopup(); // Open the popup immediately
            return marker;
        }

        // Spot search functionality
        const spotSearchInput = document.getElementById('spot-search');
        const spotResultsContainer = document.getElementById('spot-results');

        spotSearchInput.addEventListener('input', function () {
            const query = spotSearchInput.value.trim();
            if (query.length > 0) {
                fetch(`search_spot.php?query=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        spotResultsContainer.innerHTML = '';
                        data.forEach(spot => {
                            const resultItem = document.createElement('div');
                            resultItem.className = 'spot-result-item';
                            resultItem.textContent = spot.name;
                            resultItem.addEventListener('click', function () {
                                map.setView([spot.latitude, spot.longitude], 16); // Increased zoom level to 16
                                const marker = addInteractiveMarker(spot);
                                marker.openPopup(); // Open the popup immediately
                                spotResultsContainer.innerHTML = ''; // Clear results
                                spotSearchInput.value = ''; // Clear input
                            });
                            spotResultsContainer.appendChild(resultItem);
                        });
                    })
                    .catch(error => console.error('Błąd podczas pobierania miejsc::', error));
            } else {
                spotResultsContainer.innerHTML = ''; // Clear results if query is empty
            }
        });

        // Function to handle random SPOT selection
        function losujSpota() {
            fetch('losuj.php')
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(`Błąd: ${data.error}`);
                        return;
                    }
                    const lat = data.latitude;
                    const lon = data.longitude;
                    const marker = L.marker([lat, lon]).addTo(map).bindPopup(data.name).openPopup();
                    map.setView([lat, lon], 13); // Center the map on the marker
                })
                .catch(error => {
                    console.error('Błąd:', error);
                    alert('Wystąpił błąd podczas losowania miejsca.');
                });
        }

        // Attach random SPOT selection to button
        const losujButton = document.getElementById('losuj-spot-btn');
        if (losujButton) {
            losujButton.addEventListener('click', losujSpota);
        }

        // Function to handle city selection
        function changeDefaultLocation(cityId) {
            fetch(`search_city.php?id=${cityId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(`: ${data.error}`);
                        return;
                    }
                    const lat = data.latitude;
                    const lon = data.longitude;
                    const marker = L.marker([lat, lon]).addTo(map).bindPopup(data.name).openPopup();
                    map.setView([lat, lon], 13); // Center the map on the marker
                })
                .catch(error => {
                    console.error('Błąd:', error);
                    alert("Nie udało się załadować danych miasta.");
                });
        }

        // Attach city selection to city links
        const cityLinks = document.querySelectorAll(".city-link");
        cityLinks.forEach(link => {
            link.addEventListener("click", function (e) {
                e.preventDefault();
                const cityId = this.getAttribute("data-id");
                changeDefaultLocation(cityId);
            });
        });

        // Handle friend request feedback
        function sendFriendRequest(userId) {
            fetch('send_friend_request.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `friend_id=${userId}`
            })
            .then(response => response.text())
            .then(message => {
                const feedback = document.getElementById('friend-request-feedback');
                feedback.textContent = message;
                feedback.style.display = 'block';
                setTimeout(() => feedback.style.display = 'none', 5000); // Hide after 5 seconds
            })
            .catch(error => console.error('Błąd:', error));
        }
    </script>
    </div>

    <div class="theme-toggle" id="themeToggle" title="Toggle theme">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="theme-icon">
            <path class="sun-icon" d="M12 2.25a.75.75 0 01.75.75v2.25a.75.75 0 01-1.5 0V3a.75.75 0 01.75-.75zM7.5 12a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM18.894 6.166a.75.75 0 00-1.06-1.06l-1.591 1.59a.75.75 0 101.06 1.061l1.591-1.59zM21.75 12a.75.75 0 01-.75.75h-2.25a.75.75 0 010-1.5H21a.75.75 0 01.75.75zM17.834 18.894a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 10-1.061 1.06l1.59 1.591zM12 18a.75.75 0 01.75.75V21a.75.75 0 01-1.5 0v-2.25A.75.75 0 0112 18zM7.758 17.303a.75.75 0 00-1.061-1.06l-1.591 1.59a.75.75 0 001.06 1.061l1.591-1.59zM6 12a.75.75 0 01-.75.75H3a.75.75 0 010-1.5h2.25A.75.75 0 016 12zM6.697 7.757a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 00-1.061 1.06l1.59 1.591z"/>
        </svg>
    </div>

    <script>
        // Theme toggle functionality
        const themeToggle = document.getElementById('themeToggle');
        const themeStylesheet = document.getElementById('theme-stylesheet');

        // Load saved theme
        document.addEventListener('DOMContentLoaded', () => {
            const darkTheme = localStorage.getItem('darkTheme') === 'true';
            updateTheme(darkTheme);
        });

        // Toggle theme on click
        themeToggle.addEventListener('click', () => {
            const isDarkTheme = themeStylesheet.href.includes('dark');
            updateTheme(!isDarkTheme);
        });

        function updateTheme(isDark) {
            themeStylesheet.href = isDark ? 'style_glowna_dark.css' : 'style_glowna_light.css';
            localStorage.setItem('darkTheme', isDark);
            
            // Update icon
            const icon = themeToggle.querySelector('svg');
            if (isDark) {
                icon.style.transform = 'rotate(40deg)';
                themeToggle.title = 'Switch to light theme';
            } else {
                icon.style.transform = 'rotate(0deg)';
                themeToggle.title = 'Switch to dark theme';
            }
        }
    </script>
</body>
</html>