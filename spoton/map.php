<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa Miast Polski</title>
    <style>
        #map {
            height: 600px;
            width: 100%;
        }
        #search-container {
            margin: 10px 0;
        }
        #results {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
        }
        .result-item {
            padding: 8px;
            cursor: pointer;
            border-bottom: 1px solid #ddd;
        }
        .result-item:hover {
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>
    <h1>Mapa Miast Polski</h1>

    <!-- Kontener wyszukiwania -->
    <div id="search-container">
        <input type="text" id="city-search" placeholder="Enter the city name">
        <div id="results"></div>
    </div>

    <!-- Mapa -->
    <div id="map"></div>

    <!-- Ładowanie Google Maps API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY"></script>

    <script>
        let map;
        let marker;

        // Inicjalizacja mapy z domyślną lokalizacją na Szczecin
        function initMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                center: {lat: 53.4285, lng: 14.5528}, // Domyślnie Szczecin
                zoom: 10
            });
        }

        // Funkcja do wyświetlenia miasta na mapie
        function showCityOnMap(lat, lng, name) {
            if (marker) marker.setMap(null); // Usuwanie poprzedniego markera
            map.setCenter({lat: parseFloat(lat), lng: parseFloat(lng)});
            map.setZoom(10);

            marker = new google.maps.Marker({
                position: {lat: parseFloat(lat), lng: parseFloat(lng)},
                map: map,
                title: name
            });
        }

        // Obsługa wyszukiwania miast
        document.getElementById('city-search').addEventListener('input', () => {
            const query = document.getElementById('city-search').value;

            // Wysłanie zapytania AJAX do `search_cities.php`
            fetch(`http://localhost/search_cities.php?query=${query}`)
                .then(response => response.json())
                .then(data => {
                    // Czyszczenie wyników wyszukiwania
                    const resultsContainer = document.getElementById('results');
                    resultsContainer.innerHTML = '';

                    // Wyświetlenie wyników
                    data.forEach(city => {
                        const div = document.createElement('div');
                        div.className = 'result-item';
                        div.textContent = city.nazwa;
                        div.addEventListener('click', () => {
                            showCityOnMap(city.latitude, city.longitude, city.nazwa);
                        });
                        resultsContainer.appendChild(div);
                    });
                });
        });

        // Inicjalizacja mapy po załadowaniu strony
        window.onload = initMap;
    </script>
</body>
</html>
