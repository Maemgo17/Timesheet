<?php
session_start(); // Iniciar sesión
include 'db.php'; // Incluir la conexión a la base de datos

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php"); // Redirigir a la página de inicio de sesión si no está autenticado
    exit;
}

// Verificar si se ha seleccionado un usuario
if (!isset($_POST['id'])) {
    header("Location: modify_users.php"); // Redirigir si no se ha enviado el ID
    exit;
}

// Obtener el usuario seleccionado
$id = $_POST['id'];
$stmt = $pdo->prepare("SELECT id, nombre, apellido, dni, correo FROM usuarios WHERE id = :id");
$stmt->execute(['id' => $id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Modificar los datos del usuario
if (isset($_POST['modifyUser'])) {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $dni = $_POST['dni']; // Obtener el DNI del formulario
    $correo = $_POST['correo'];

    // Preparar la consulta para actualizar los datos del usuario
    $stmt = $pdo->prepare("UPDATE usuarios SET nombre = :nombre, apellido = :apellido, dni = :dni, correo = :correo WHERE id = :id");
    $stmt->execute(['nombre' => $nombre, 'apellido' => $apellido, 'dni' => $dni, 'correo' => $correo, 'id' => $id]);

    // Redirigir para evitar duplicación al recargar la página
    header("Location: settings.php");
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
            justify-content: center; /* Centrar horizontalmente */
            align-items: center; /* Centrar verticalmente */
            height: 100vh; /* Ocupa toda la altura de la ventana */
        }

        .container {
            background: white;
            padding: 30px; /* Espaciado más amplio para mayor comodidad */
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 400px; /* Ancho máximo del contenedor */
            display: flex;
            flex-direction: column;
            align-items: center; /* Centrar elementos dentro del contenedor */
            margin: 0 auto; /* Centrar el contenedor horizontalmente */
        }

        h1 {
            color: #333;
            margin-bottom: 20px; /* Espacio debajo del título */
            text-align: center; /* Centrar el texto del título */
        }

        form {
            width: 100%; /* El formulario ocupa todo el ancho del contenedor */
            display: flex;
            flex-direction: column; /* Organiza los elementos verticalmente */
        }

        form input {
            padding: 12px; /* Más espacio para mayor comodidad */
            margin-bottom: 15px; /* Espaciado entre los campos */
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%; /* El input ocupa todo el ancho del contenedor */
            font-size: 16px; /* Aumentar el tamaño de la fuente */
        }

        button {
            background: #4CAF50; /* Color verde */
            color: white; /* Texto en blanco */
            border: none;
            border-radius: 5px;
            padding: 12px; /* Mayor tamaño de botón */
            cursor: pointer;
            font-size: 16px; /* Tamaño de fuente */
            transition: background 0.3s; /* Transición suave al pasar el mouse */
            width: 50%; /* Ancho ajustado al 50% del contenedor */
            margin: 0px auto; /* Espaciado vertical entre botones y centrado automático */
        }

        button:hover {
            background: #45a049; /* Color más oscuro al pasar el mouse */
        }

        footer {
            text-align: center;
            margin-top: 20px;
            color: #777;
            width: 100%; /* Asegurar que el footer ocupe el 100% */
        }

        footer button {
            background: #007BFF; /* Color azul para el botón de volver */
            padding: 12px; /* Igualar el padding con el botón de modificar */
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
            transition: background 0.3s;
            width: 50%; /* Ancho ajustado al 50% del contenedor */
            margin: 0px auto; /* Espaciado vertical entre botones y centrado automático */
        }

        footer button:hover {
            background: #0056b3; /* Color más oscuro al pasar el mouse */
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
            <input type="text" name="dni" value="<?= htmlspecialchars($usuario['dni']) ?>" required placeholder="DNI"> <!-- Campo de DNI -->
            <input type="email" name="correo" value="<?= htmlspecialchars($usuario['correo']) ?>" required placeholder="Correo Electrónico"> <!-- Cambiar a 'correo' -->
            <button type="submit" name="modifyUser">Modificar Datos</button> <!-- Botón de modificar datos -->
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
