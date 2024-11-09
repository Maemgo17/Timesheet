<?php
session_start(); 
include 'db.php'; 

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php"); 
    exit;
}

if (!isset($_POST['id'])) {
    header("Location: modify_users.php"); 
    exit;
}

$id = $_POST['id'];
$stmt = $pdo->prepare("SELECT id, nombre, apellido, dni, correo FROM usuarios WHERE id = :id");
$stmt->execute(['id' => $id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (isset($_POST['modifyUser'])) {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $dni = $_POST['dni']; 
    $correo = $_POST['correo'];

   
    $stmt = $pdo->prepare("UPDATE usuarios SET nombre = :nombre, apellido = :apellido, dni = :dni, correo = :correo WHERE id = :id");
    $stmt->execute(['nombre' => $nombre, 'apellido' => $apellido, 'dni' => $dni, 'correo' => $correo, 'id' => $id]);


    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #A3C1DA;
            display: flex;
            justify-content: center; 
            align-items: center; 
            height: 100vh;
        }

        .container {
            background: white;
            padding: 30px; 
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 400px; 
            display: flex;
            flex-direction: column;
            align-items: center; 
            margin: 0 auto; 
        }

        h1 {
            color: #333;
            margin-bottom: 20px; 
            text-align: center; 
        }

        form {
            width: 100%; 
            display: flex;
            flex-direction: column; 
        }

        form input {
            padding: 12px; 
            margin-bottom: 15px; 
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%; 
            font-size: 16px; 
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
            width: 50%; 
            margin: 0px auto; 
        }

        button:hover {
            background: #45a049; 
        }

        footer {
            text-align: center;
            margin-top: 20px;
            color: #777;
            width: 100%; 
        }

        footer button {
            background: #007BFF; 
            padding: 12px; 
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
            transition: background 0.3s;
            width: 50%; 
            margin: 0px auto; 
        }

        footer button:hover {
            background: #0056b3; 
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Editar Usuario</h1>
        <form action="" method="POST" onsubmit="return confirmModification()">
            <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
            <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required placeholder="Nombre">
            <input type="text" name="apellido" value="<?= htmlspecialchars($usuario['apellido']) ?>" required placeholder="Apellido">
            <input type="text" name="dni" value="<?= htmlspecialchars($usuario['dni']) ?>" required placeholder="DNI"> 
            <input type="email" name="correo" value="<?= htmlspecialchars($usuario['correo']) ?>" required placeholder="Correo Electrónico"> 
            <button type="submit" name="modifyUser">Modificar Datos</button> 
        </form>
        <footer>
            <button onclick="window.location.href='modify_employees.php'">Volver</button>
        </footer>
    </div>

    <script>
        function confirmModification() {
            return confirm("¿Estás seguro de que deseas modificar los datos del usuario?");
        }
    </script>
</body>
</html>
