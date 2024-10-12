<?php
session_start(); // Iniciar sesión
include 'db.php'; // Incluir la conexión a la base de datos

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php"); // Redirigir a la página de inicio de sesión si no está autenticado
    exit;
}

// Obtener total de horas trabajadas por empleado
$stmt = $pdo->query("SELECT empleados.nombre AS empleado_nombre, SUM(horas_trabajadas.horas) AS total_horas
                      FROM horas_trabajadas
                      JOIN empleados ON horas_trabajadas.empleado_id = empleados.id
                      GROUP BY empleados.id");
$totalHorasEmpleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener total de horas trabajadas por proyecto
$stmt = $pdo->query("SELECT proyectos.nombre AS proyecto_nombre, SUM(horas_trabajadas.horas) AS total_horas
                      FROM horas_trabajadas
                      JOIN proyectos ON horas_trabajadas.proyecto_id = proyectos.id
                      GROUP BY proyectos.id");
$totalHorasProyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Métricas</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center; /* Centrar horizontalmente */
            align-items: center; /* Centrar verticalmente */
            height: 100vh; /* Hacer que ocupe toda la altura de la ventana */
        }

        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 800px;
            display: flex;
            flex-direction: column;
            align-items: center; /* Centrar los elementos dentro del contenedor */
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px; /* Espacio debajo del título */
        }

        h2 {
            color: #4CAF50; /* Color verde para los subtítulos */
            margin-top: 20px; /* Espacio encima del subtítulo */
            margin-bottom: 10px; /* Espacio debajo del subtítulo */
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            text-align: center; /* Centrar el texto en la tabla */
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px; /* Espacio dentro de las celdas */
            text-align: center; /* Centrar el texto en celdas */
        }

        th {
            background-color: #f4f4f4;
            color: #333; /* Color del texto del encabezado */
        }

        .back-button {
            background: #007BFF; /* Color azul */
            color: white; /* Texto en blanco */
            border: none;
            border-radius: 5px;
            padding: 12px 20px;
            cursor: pointer;
            margin-top: 20px; /* Espacio encima del botón */
            transition: background 0.3s, transform 0.3s; /* Transiciones para el hover */
        }

        .back-button:hover {
            background: #0056b3; /* Color más oscuro al pasar el mouse */
            transform: scale(1.05); /* Aumento de tamaño al pasar el mouse */
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
        <h1>Métricas de Horas Trabajadas</h1>
        
        <h2>Total de Horas por Empleado</h2>
        <table>
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th>Total Horas</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($totalHorasEmpleados as $empleado): ?>
                <tr>
                    <td><?= htmlspecialchars($empleado['empleado_nombre']) ?></td>
                    <td><?= htmlspecialchars($empleado['total_horas']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Total de Horas por Proyecto</h2>
        <table>
            <thead>
                <tr>
                    <th>Proyecto</th>
                    <th>Total Horas</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($totalHorasProyectos as $proyecto): ?>
                <tr>
                    <td><?= htmlspecialchars($proyecto['proyecto_nombre']) ?></td>
                    <td><?= htmlspecialchars($proyecto['total_horas']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <button class="back-button" onclick="window.location.href='index.php'">Volver</button>
    </div>
</body>
</html>
