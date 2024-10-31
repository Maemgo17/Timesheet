<?php
$host = 'localhost';
$db = 'proyectos_db'; // Cambiar por el nombre de tu base de datos
$user = 'root'; // Cambiar por tu usuario
$pass = ''; // Cambiar por tu contraseña

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
