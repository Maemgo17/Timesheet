<?php
session_start(); 
include 'db.php'; 

$secretKey = '6Le4G2sqAAAAAFiCVErefo2myfD2h61Y7GZ1JsKX';

if (isset($_POST['login'])) {
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];
    $recaptchaResponse = $_POST['g-recaptcha-response'];

   
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret' => $secretKey,
        'response' => $recaptchaResponse
    ];

    $options = [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_RETURNTRANSFER => true
    ];

    $curl = curl_init();
    curl_setopt_array($curl, $options);
    $recaptchaValidationResponse = curl_exec($curl);
    curl_close($curl);

    $recaptchaResult = json_decode($recaptchaValidationResponse, true);

    if ($recaptchaResult['success']) {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = :correo");
        $stmt->execute(['correo' => $correo]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($contrasena, $usuario['contrasena'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nombre_usuario'] = $usuario['nombre']; 
            $_SESSION['rol'] = $usuario['rol']; 

            if ($usuario['rol'] === 'admin') {
                header("Location: index.php");
            } elseif ($usuario['rol'] === 'usuario') {
                header("Location: index_usuario.php"); 
            } else {
                $errorLogin = "Ha habido un error interno. Notificalo a un administrador";
            }
            exit;
        } else {
            $errorLogin = "Correo o contraseña incorrectos.";
        }
    } else {
        $errorLogin = "Verificación de reCAPTCHA fallida. Inténtalo nuevamente.";
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
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #A3C1DA;
        }

        .container {
            background-color: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }

        h1 {
            margin-bottom: 1.5rem;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        input[type="email"], input[type="password"] {
            margin-bottom: 1rem;
            padding: 0.8rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }

        button {
            padding: 0.8rem;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            margin-top: 1rem;
        }

        button:hover {
            background-color: #0056b3;
        }

        .g-recaptcha {
            margin: 1rem 0;
            display: flex;
            justify-content: center;
        }

        p {
            margin-top: 1rem;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        p[style="color: red;"] {
            margin-top: 1rem;
            color: red;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Iniciar Sesión</h1>
        <?php if (isset($errorLogin)): ?>
            <p style="color: red;"><?= $errorLogin ?></p>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <input type="email" name="correo" placeholder="Correo Electrónico" required>
            <input type="password" name="contrasena" placeholder="Contraseña" required>
            <div class="g-recaptcha" data-sitekey="6Le4G2sqAAAAAHFA7Nygr5CTNZr1HTyNjvJ4tmPi"></div>
            <button type="submit" name="login">Iniciar Sesión</button>
        </form>
        <p>¿No tienes una cuenta? <a href="register.php">Regístrate aquí</a></p>
    </div>

</body>
</html>
