<?php include('server.php') ?>
<!DOCTYPE html>
<html>
<head>
  <title>Rejestracja</title>
  <link rel="stylesheet" href="style_rejestracja.css">
</head>
<body>
  <div class="register-container">
    <div class="header">
      <h2>Rejestracja</h2>
    </div>
    <div class="theme-toggle" id="themeToggle" title="Zmień motyw">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="theme-icon">
            <path class="sun-icon" d="M12 2.25a.75.75 0 01.75.75v2.25a.75.75 0 01-1.5 0V3a.75.75 0 01.75-.75zM7.5 12a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM18.894 6.166a.75.75 0 00-1.06-1.06l-1.591 1.59a.75.75 0 101.06 1.061l1.591-1.59zM21.75 12a.75.75 0 01-.75.75h-2.25a.75.75 0 010-1.5H21a.75.75 0 01.75.75zM17.834 18.894a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 10-1.061 1.06l1.59 1.591zM12 18a.75.75 0 01.75.75V21a.75.75 0 01-1.5 0v-2.25A.75.75 0 0112 18zM7.758 17.303a.75.75 0 00-1.061-1.06l-1.591 1.59a.75.75 0 001.06 1.061l1.591-1.59zM6 12a.75.75 0 01-.75.75H3a.75.75 0 010-1.5h2.25A.75.75 0 016 12zM6.697 7.757a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 00-1.061 1.06l1.59 1.591z"/>
        </svg>
    </div>
    <form method="post" action="register.php">
      <?php include('errors.php'); ?>
      <div class="input-group">
        <label>Nazwa użytkownika</label>
        <input type="text" name="username" value="">
      </div>
      <div class="input-group">
        <label>Email</label>
        <input type="email" name="email" value="">
      </div>
      <div class="input-group">
        <label>Hasło</label>
        <input type="password" name="password_1">
      </div>
      <div class="input-group">
        <label>Potwierdź hasło</label>
        <input type="password" name="password_2">
      </div>
      <div class="input-group">
        <label>Wybierz swoje miasto</label>
        <select name="city_id" required>
          <option value="">Wybierz miasto</option>
          <?php
          $cities_query = "SELECT id, nazwa FROM miasta";
          $cities_result = mysqli_query($db, $cities_query);
          while ($city = mysqli_fetch_assoc($cities_result)) {
              echo "<option value='{$city['id']}'>{$city['nazwa']}</option>";
          }
          ?>
        </select>
      </div>
      <div class="input-group">
        <button type="submit" class="btn" name="reg_user">Zarejestruj się</button>
      </div>
      <p>
        Masz już konto? <a href="login.php">Zaloguj się</a>
      </p>
    </form>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
        const themeToggle = document.getElementById('themeToggle');
        const body = document.body;

        // Wczytaj zapisany motyw
        const darkTheme = localStorage.getItem('darkTheme') === 'true';
        updateTheme(darkTheme);

        // Przełącz motyw po kliknięciu
        themeToggle.addEventListener('click', () => {
            const isDarkTheme = body.classList.contains('dark-theme');
            updateTheme(!isDarkTheme);
        });

        function updateTheme(isDark) {
            if (isDark) {
                body.classList.add('dark-theme');
                localStorage.setItem('darkTheme', 'true');
            } else {
                body.classList.remove('dark-theme');
                localStorage.setItem('darkTheme', 'false');
            }
        }
    });
  </script>
</body>
</html>