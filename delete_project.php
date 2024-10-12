<?php
session_start(); // Iniciar sesión
include 'db.php'; // Incluir la conexión a la base de datos

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php"); // Redirigir a la página de inicio de sesión si no está autenticado
    exit;
}

// Obtener todos los proyectos
$stmt = $pdo->query("SELECT * FROM proyectos");
$proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Eliminar un proyecto
if (isset($_POST['deleteProject'])) {
    $id = $_POST['id'];

    // Primero eliminar las horas trabajadas relacionadas
    $stmt = $pdo->prepare("DELETE FROM horas_trabajadas WHERE proyecto_id = :id");
    $stmt->execute(['id' => $id]);

    // Ahora eliminar el proyecto
    $stmt = $pdo->prepare("DELETE FROM proyectos WHERE id = :id");
    $stmt->execute(['id' => $id]);

    // Redirigir para evitar duplicación al recargar la página
    header("Location: index.php"); // Asegúrate de que la URL sea correcta
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Proyectos</title>
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
            margin-bottom: 20px; /* Espacio debajo del título */
            text-align: center; /* Centrar el título */
        }

        form {
            width: 100%;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            align-items: center; /* Centrar el contenido del formulario */
        }

        select {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
        }

        button {
            background: #D9534F;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
            width: 100%; /* Botón ocupa el 100% del ancho */
        }

        button:hover {
            background: #c9302c;
        }

        footer {
            text-align: center;
            margin-top: 20px;
            color: #777;
        }
    </style>
    <script>
        function confirmDelete() {
            return confirm("¿Estás seguro de que deseas eliminar este proyecto?");
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Eliminar Proyectos</h1>
        <form action="" method="POST" onsubmit="return confirmDelete();">
            <select name="id" required>
                <option value="">Selecciona un proyecto...</option>
                <?php foreach ($proyectos as $proyecto): ?>
                    <option value="<?= $proyecto['id'] ?>"><?= htmlspecialchars($proyecto['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="deleteProject">Eliminar Proyecto</button>
        </form>
        <footer>
            <button onclick="window.location.href='index.php'">Volver</button>
        </footer>
    </div>
</body>
</html>
