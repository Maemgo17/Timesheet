<?php
session_start(); // Iniciar sesión
include 'db.php'; // Incluir la conexión a la base de datos

// Registro de usuario
if (isset($_POST['register'])) {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];
    $confirmarContrasena = $_POST['confirmar_contrasena'];

    // Verificar que las contraseñas coincidan
    if ($contrasena !== $confirmarContrasena) {
        $errorRegistro = "Las contraseñas no coinciden."; // Mensaje de error
    } elseif (strlen($contrasena) < 8) { // Verificar la longitud de la contraseña
        $errorRegistro = "La contraseña debe tener al menos 8 caracteres."; // Mensaje de error
    } else {
        // Verificar si el correo ya está registrado
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = :correo");
        $stmt->execute(['correo' => $correo]);
        $usuarioExistente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuarioExistente) {
            $errorRegistro = "Este correo electrónico ya está registrado.";
        } else {
            // Encriptar la contraseña
            $contrasenaHash = password_hash($contrasena, PASSWORD_DEFAULT);

            // Insertar el nuevo usuario
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, correo, contrasena) VALUES (:nombre, :apellido, :correo, :contrasena)");
            $stmt->execute(['nombre' => $nombre, 'apellido' => $apellido, 'correo' => $correo, 'contrasena' => $contrasenaHash]);
            $registroExitoso = true; // Bandera para el registro exitoso
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <div class="container">
        <h1>Registro</h1>
        <?php if (isset($errorRegistro)): ?>
            <p style="color: red;"><?= $errorRegistro ?></p> <!-- Mensaje de error en rojo -->
        <?php elseif (isset($registroExitoso) && $registroExitoso): ?>
            <p>Registro exitoso. Ahora puedes iniciar sesión.</p>
        <?php endif; ?>
        <form action="register.php" method="POST">
            <input type="text" name="nombre" placeholder="Nombre" required>
            <input type="text" name="apellido" placeholder="Apellido" required>
            <input type="email" name="correo" placeholder="Correo Electrónico" required>
            <input type="password" name="contrasena" placeholder="Contraseña" required>
            <input type="password" name="confirmar_contrasena" placeholder="Confirmar Contraseña" required>
            <button type="submit" name="register">Registrar</button>
        </form>
        <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
    </div>

</body>
</html>
