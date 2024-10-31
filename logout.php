<?php
session_start(); // Iniciar sesión
session_unset(); // Limpiar la sesión
session_destroy(); // Destruir la sesión
header("Location: login.php"); // Redirigir a la página de inicio de sesión
exit;
?>
