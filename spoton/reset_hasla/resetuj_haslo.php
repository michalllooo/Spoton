<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ustaw nowe hasło</title>
</head>
<body>
    <h2>Ustaw nowe hasło</h2>
    <form action="update_password.php" method="POST">
        <input type="hidden" name="token" value="<?php echo $_GET['token']; ?>">
        <label>Nowe hasło:</label>
        <input type="password" name="new_password" required>
        <label>Potwierdź nowe hasło:</label>
        <input type="password" name="confirm_password" required>
        <button type="submit">Zresetuj hasło</button>
    </form>
</body>
</html>
