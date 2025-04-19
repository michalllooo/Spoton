<?php include('server.php') ?>
<!DOCTYPE html>
<html>
<head>
  <title>Logowanie</title>
  <link rel="stylesheet" href="style_logowanie.css">
</head>
<body>
  <div class="login-container">
    <h1>Logowanie</h1>
    <form method="post" action="login.php">
      <?php include('errors.php'); ?>
      <div class="input-group">
        <label>Nazwa użytkownika</label>
        <input type="text" name="username">
      </div>
      <div class="input-group password-input">
        <label>Hasło</label>
        <div class="password-container">
          <input type="password" name="password" id="password">
          <span class="eye" onclick="togglePasswordVisibility()">👀</span>
        </div>
      </div>
      <div class="input-group">
        <button type="submit" class="btn" name="login_user">Zaloguj się</button>
      </div>
      <p>
        Nie masz jeszcze konta? <a href="register.php">Zarejestruj się</a>
      </p>
   <!-- <p>Zapomniałeś hasła? <a href="reset_hasla/brak_hasla.html">Zresetuj hasło</a></p>-->
    </form>
  </div>
  <div class="theme-toggle" id="themeToggle" title="Zmień motyw">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="theme-icon">
    <path class="sun-icon" d="M12 2.25a.75.75 0 01.75.75v2.25a.75.75 0 01-1.5 0V3a.75.75 0 01.75-.75zM7.5 12a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM18.894 6.166a.75.75 0 00-1.06-1.06l-1.591 1.59a.75.75 0 101.06 1.061l1.591-1.59zM21.75 12a.75.75 0 01-.75.75h-2.25a.75.75 0 010-1.5H21a.75.75 0 01.75.75zM17.834 18.894a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 10-1.061 1.06l1.59 1.591zM12 18a.75.75 0 01.75.75V21a.75.75 0 01-1.5 0v-2.25A.75.75 0 0112 18zM7.758 17.303a.75.75 0 00-1.061-1.06l-1.591 1.59a.75.75 0 001.06 1.061l1.591-1.59zM6 12a.75.75 0 01-.75.75H3a.75.75 0 010-1.5h2.25A.75.75 0 016 12zM6.697 7.757a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 00-1.061 1.06l1.59 1.591z"/>
    </svg>
  </div>
  <script src="script_logowanie.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
        const themeToggle = document.getElementById('themeToggle');
        const body = document.body;

        // Load saved theme
        const darkTheme = localStorage.getItem('darkTheme') === 'true';
        updateTheme(darkTheme);

        // Toggle theme on click
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