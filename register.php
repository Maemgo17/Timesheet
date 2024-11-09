<?php
session_start(); 
include 'db.php';

// Registro de usuario
if (isset($_POST['register'])) {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];
    $confirmarContrasena = $_POST['confirmar_contrasena'];
    $dni = $_POST['dni']; 

    if ($contrasena !== $confirmarContrasena) {
        $errorRegistro = "Las contraseñas no coinciden."; 
    } elseif (strlen($contrasena) < 8) { 
        $errorRegistro = "La contraseña debe tener al menos 8 caracteres."; 
    } else {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = :correo");
        $stmt->execute(['correo' => $correo]);
        $usuarioExistente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuarioExistente) {
            $errorRegistro = "Este correo electrónico ya está registrado.";
        } else {
            $contrasenaHash = password_hash($contrasena, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, correo, contrasena, rol, dni) VALUES (:nombre, :apellido, :correo, :contrasena, 'usuario', :dni)");
            $stmt->execute([
                'nombre' => $nombre,
                'apellido' => $apellido,
                'correo' => $correo,
                'contrasena' => $contrasenaHash,
                'dni' => $dni 
            ]);
            $registroExitoso = true; 
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
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #A3C1DA;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            padding: 20px;
        }

        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px; 
        }

        input {
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            width: 100%;
            transition: border 0.3s;
        }

        input:focus {
            border: 1px solid #4CAF50; 
            outline: none; 
        }

        button {
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 12px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }

        button:hover {
            background: #45a049; 
        }

        p {
            color: #777;
            margin-top: 20px;
        }

        a {
            color: #007BFF;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline; 
        }

        .error {
            color: red; 
            margin-bottom: 10px;
        }

        .success {
            color: green; 
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Registro de Usuario</h1>
        <?php if (isset($errorRegistro)): ?>
            <div class="error"><?= htmlspecialchars($errorRegistro) ?></div>
        <?php endif; ?>
        <?php if (isset($registroExitoso)): ?>
            <div class="success">Registro exitoso. Puedes iniciar sesión ahora.</div>
        <?php endif; ?>
        <form action="" method="POST">
            <input type="text" name="nombre" placeholder="Nombre" required>
            <input type="text" name="apellido" placeholder="Apellido" required>
            <input type="email" name="correo" placeholder="Correo" required>
            <input type="password" name="contrasena" placeholder="Contraseña" required>
            <input type="password" name="confirmar_contrasena" placeholder="Confirmar Contraseña" required>
            <input type="text" name="dni" placeholder="DNI" required>
            <button type="submit" name="register">Registrar</button>
        </form>
        <p>¿Ya tienes una cuenta? <a href="login.php">Iniciar sesión</a></p>
    </div>
</body>
</html>
