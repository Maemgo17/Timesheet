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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Proyectos</title>
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

        select {
            padding: 12px; /* Más espacio para mayor comodidad */
            margin-bottom: 15px; /* Espaciado entre los campos */
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%; /* El select ocupa todo el ancho del contenedor */
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
            width: 50%; /* Ajustado para ser más estrecho */
            margin: 0 auto; /* Espaciado y centrado automático */
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
            width: 50%; /* Ajustado para ser más estrecho */
            margin: 0 auto; /* Espaciado y centrado automático */
        }

        footer button:hover {
            background: #0056b3; /* Color más oscuro al pasar el mouse */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Modificar Proyecto</h1>
        <form action="edit_project.php" method="POST">
            <select name="id" required>
                <option value="">Seleccione un proyecto</option>
                <?php foreach ($proyectos as $proyecto): ?>
                    <option value="<?= $proyecto['id'] ?>"><?= htmlspecialchars($proyecto['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Modificar Proyecto</button>
        </form>
        <footer>
            <button onclick="window.location.href='settings.php'">Volver</button>
        </footer>
    </div>
</body>
</html>
