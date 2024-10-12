<?php
session_start(); // Iniciar sesión
include 'db.php'; // Incluir la conexión a la base de datos

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php"); // Redirigir a la página de inicio de sesión si no está autenticado
    exit;
}

// Verificar si se ha seleccionado un empleado
if (!isset($_POST['id'])) {
    header("Location: modify_employees.php"); // Redirigir si no se ha enviado el ID
    exit;
}

// Obtener el empleado seleccionado
$id = $_POST['id'];
$stmt = $pdo->prepare("SELECT * FROM empleados WHERE id = :id");
$stmt->execute(['id' => $id]);
$empleado = $stmt->fetch(PDO::FETCH_ASSOC);

// Modificar el nombre del empleado
if (isset($_POST['modifyEmployee'])) {
    $nombre = $_POST['nombre'];

    $stmt = $pdo->prepare("UPDATE empleados SET nombre = :nombre WHERE id = :id");
    $stmt->execute(['nombre' => $nombre, 'id' => $id]);

    // Redirigir para evitar duplicación al recargar la página
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Empleado</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            flex-direction: column;
        }

        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 400px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            width: 100%;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        form input {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
        }

        button {
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background: #45a049;
        }

        footer {
            text-align: center;
            margin-top: 20px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Editar Empleado</h1>
        <form action="" method="POST">
            <input type="hidden" name="id" value="<?= $empleado['id'] ?>">
            <input type="text" name="nombre" value="<?= htmlspecialchars($empleado['nombre']) ?>" required>
            <button type="submit" name="modifyEmployee">Modificar Nombre</button>
        </form>
        <footer>
            <button onclick="window.location.href='modify_employees.php'">Volver</button>
        </footer>
    </div>
</body>
</html>
