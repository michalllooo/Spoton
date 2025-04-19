<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Wybierz miasto</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link id="theme-stylesheet" rel="stylesheet" href="miasto_light.css">
    <script>
        function updateTheme() {
            const darkTheme = localStorage.getItem("darkTheme") === "true";
            const themeStylesheet = document.getElementById("theme-stylesheet");
            themeStylesheet.href = darkTheme ? "miasto_dark.css" : "miasto_light.css";
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

    <div class="container">
        <h1>Wybierz miasto</h1>
        <div class="search-container">
            <input type="text" id="search" placeholder="Wyszukaj miasto...">
            <i class="fas fa-search search-icon"></i>
        </div>
        <ul id="results"></ul>
    </div>

    <script>
        $(document).ready(function () {
            // Dodaj animację opóźnienia dla kontenerów miast
            function animateCityContainers() {
                $('.city-container').each(function(index) {
                    $(this).css('--index', index);
                });
            }

            // Obsługa wyszukiwania miasta
            $('#search').on('input', function () {
                let query = $(this).val();
                if (query.length > 0) {
                    $.getJSON("search_city.php", { query: query }, function (data) {
                        $('#results').empty();
                        data.forEach(function (city, index) {
                            const cityContainer = $('<li>').addClass('city-container');
                            const cityName = $('<div>').text(city.name).addClass('city-name');
                            
                            // Ustawienie tła kontenera miasta na obrazek
                            cityContainer.css('background-image', `url(${city.image || ''})`);
                            cityContainer.css('--index', index); // Dodaj opóźnienie animacji
                            
                            cityContainer.append(cityName);

                            // Obsługa kliknięcia
                            cityContainer.on('click', function () {
                                window.location.href = `index.php?lat=${city.latitude}&lon=${city.longitude}`;
                            });

                            $('#results').append(cityContainer);
                        });
                    });
                } else {
                    $('#results').empty();
                }
            });
        });

        // Funkcja przełączania motywu
        const themeToggle = document.getElementById('themeToggle');

        themeToggle.addEventListener('click', () => {
            const isDarkTheme = localStorage.getItem('darkTheme') === 'true';
            localStorage.setItem('darkTheme', !isDarkTheme);
            updateTheme();
        });
    </script>
</body>
</html>
