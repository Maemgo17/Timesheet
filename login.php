<?php
session_start(); // Iniciar sesión
include 'db.php'; // Incluir la conexión a la base de datos

// Inicio de sesión
if (isset($_POST['login'])) {
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];

    // Obtener el usuario de la base de datos a través del correo
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = :correo");
    $stmt->execute(['correo' => $correo]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar la contraseña
    if ($usuario && password_verify($contrasena, $usuario['contrasena'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nombre_usuario'] = $usuario['nombre']; // Guardar el nombre en la sesión
        header("Location: index.php"); // Redirigir a la página principal
        exit;
    } else {
        $errorLogin = "Correo o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <div class="container">
        <h1>Iniciar Sesión</h1>
        <?php if (isset($errorLogin)): ?>
            <p style="color: red;"><?= $errorLogin ?></p> <!-- Mensaje de error en rojo -->
        <?php endif; ?>
        <form action="login.php" method="POST">
            <input type="email" name="correo" placeholder="Correo Electrónico" required>
            <input type="password" name="contrasena" placeholder="Contraseña" required>
            <button type="submit" name="login">Iniciar Sesión</button>
        </form>
        <p>¿No tienes una cuenta? <a href="register.php">Regístrate aquí</a></p>
    </div>

</body>
</html>
