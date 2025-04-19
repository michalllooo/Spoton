<?php
session_start();         // Rozpoczęcie sesji, aby mieć do niej dostęp
session_unset();         // Usunięcie wszystkich zmiennych sesji
session_destroy();       // Zniszczenie sesji
header('Location: index.php');  // Przekierowanie do strony głównej
exit();
