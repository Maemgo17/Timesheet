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

// Obtener todos los empleados
$stmt = $pdo->query("SELECT * FROM empleados");
$empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener horas trabajadas
$horasTrabajadas = [];
foreach ($proyectos as $proyecto) {
    $stmt = $pdo->prepare("SELECT empleados.nombre AS empleado_nombre, horas_trabajadas.* FROM horas_trabajadas
                           JOIN empleados ON horas_trabajadas.empleado_id = empleados.id
                           WHERE horas_trabajadas.proyecto_id = :proyecto_id");
    $stmt->execute(['proyecto_id' => $proyecto['id']]);
    $horasTrabajadas[$proyecto['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TimeSheet</title>
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
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 800px;
            display: flex;
            flex-direction: column;
            align-items: center; /* Centrar los elementos dentro del contenedor */
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .actions {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            align-items: stretch; /* Asegurarse de que los botones ocupen todo el ancho */
            width: 100%;
        }

        .add-hours-button, .metrics-button, .actions-button {
            background: #4CAF50; /* Color para el botón de agregar horas */
            color: white; /* Texto blanco */
            width: 100%; /* Asegura que el botón ocupe todo el ancho */
            border: none;
            border-radius: 5px;
            padding: 15px;
            margin: 10px 0;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }

        .add-hours-button:hover, .metrics-button:hover {
            opacity: 0.9; /* Añadir efecto de hover */
        }

        .actions-button {
            background: #D9534F; /* Color para el botón de acciones */
        }

        .actions-button:hover {
            background: #c9302c; /* Color al pasar el mouse */
        }

        .dropdown {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .dropdown-content {
            display: none;
            position: relative;
            background-color: #f9f9f9;
            min-width: 200px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            flex-direction: column;
        }

        .show {
            display: flex;
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>TimeSheet</h1>
        <div class="actions">
            <button class="add-hours-button" onclick="window.location.href='add_hours.php'">Agregar Horas</button>
            <button class="metrics-button" onclick="window.location.href='metrics.php'">Ver Métricas</button>
            <div class="dropdown">
                <button class="actions-button" onclick="toggleDropdown()">Acciones</button>
                <div id="dropdownContent" class="dropdown-content">
                    <button onclick="window.location.href='add_project.php'">Agregar Proyecto</button>
                    <button onclick="window.location.href='add_employee.php'">Agregar Empleado</button>
                    <button onclick="window.location.href='edit_employee.php'">Modificar Empleado</button>
                    <button onclick="window.location.href='edit_project.php'">Modificar Proyecto</button>
                    <button onclick="window.location.href='delete_project.php'">Eliminar Proyecto</button>
                    <button onclick="window.location.href='delete_employees.php'">Eliminar Empleado</button> <!-- Botón para eliminar empleado -->
                    <button onclick="window.location.href='logout.php'">Cerrar Sesión</button>
                </div>
            </div>
        </div>

        <!-- Tabla de horas trabajadas -->
        <h2>Horas Trabajadas por Proyecto</h2>
        <table>
            <thead>
                <tr>
                    <th>Proyecto</th>
                    <th>Empleado</th>
                    <th>Fecha</th>
                    <th>Horas</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($horasTrabajadas as $proyectoId => $horas): ?>
                    <?php foreach ($horas as $hora): ?>
                    <tr>
                        <td><?= htmlspecialchars($proyectoId) ?></td>
                        <td><?= htmlspecialchars($hora['empleado_nombre']) ?></td>
                        <td><?= htmlspecialchars($hora['fecha']) ?></td>
                        <td><?= htmlspecialchars($hora['horas']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        function toggleDropdown() {
            document.getElementById("dropdownContent").classList.toggle("show");
        }

        // Cerrar el menú desplegable si se hace clic fuera de él
        window.onclick = function(event) {
            if (!event.target.matches('.dropdown button')) {
                var dropdowns = document.getElementsByClassName("dropdown-content");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }
    </script>
</body>
</html>
