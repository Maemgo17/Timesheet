<?php
session_start();
include 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$horasTrabajadas = [];

$stmt = $pdo->query("SELECT id, nombre FROM proyectos");
$proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$proyectosPorId = [];
foreach ($proyectos as $proyecto) {
    $proyectosPorId[$proyecto['id']] = $proyecto['nombre'];
}

$stmt = $pdo->query("SELECT * FROM usuarios");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($proyectos as $proyecto) {
    $stmt = $pdo->prepare("SELECT usuarios.nombre AS usuario_nombre, horas_trabajadas.* FROM horas_trabajadas
                           JOIN usuarios ON horas_trabajadas.usuario_id = usuarios.id
                           WHERE horas_trabajadas.proyecto_id = :proyecto_id");
    $stmt->execute(['proyecto_id' => $proyecto['id']]);
    $horasTrabajadas[$proyecto['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$stmt = $pdo->prepare("SELECT nombre, apellido FROM usuarios WHERE id = :usuario_id");
$stmt->execute(['usuario_id' => $_SESSION['usuario_id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
$nombreUsuario = $usuario['nombre'];
$apellidoUsuario = $usuario['apellido'];

$filterUsuario = isset($_POST['filter_usuario']) ? $_POST['filter_usuario'] : '';
$filterProyecto = isset($_POST['filter_proyecto']) ? $_POST['filter_proyecto'] : '';
$filterFechaInicio = isset($_POST['filter_fecha_inicio']) ? $_POST['filter_fecha_inicio'] : '';
$filterFechaFin = isset($_POST['filter_fecha_fin']) ? $_POST['filter_fecha_fin'] : '';
$filterHoras = isset($_POST['filter_horas']) ? $_POST['filter_horas'] : '';

$filteredHorasTrabajadas = [];
foreach ($horasTrabajadas as $proyectoId => $horas) {
    foreach ($horas as $hora) {
        // Filtrado por Usuario
        $matchUsuario = ($filterUsuario === '' || stripos($hora['usuario_nombre'], $filterUsuario) !== false);

        // Filtrado por Proyecto
        $matchProyecto = ($filterProyecto === '' || stripos($proyectosPorId[$proyectoId], $filterProyecto) !== false);

        // Filtrado por Fecha
        $matchFecha = true;
        if ($filterFechaInicio !== '' && $filterFechaFin !== '') {
            $fecha = strtotime($hora['fecha']);
            $startDate = strtotime($filterFechaInicio);
            $endDate = strtotime($filterFechaFin);
            $matchFecha = ($fecha >= $startDate && $fecha <= $endDate);
        }

        // Filtrado por Horas
        $matchHoras = ($filterHoras === '' || $hora['horas'] == $filterHoras);

        if ($matchUsuario && $matchProyecto && $matchFecha && $matchHoras) {
            $filteredHorasTrabajadas[$proyectoId][] = $hora;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TimeSheet</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
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
            overflow: auto;
        }

        .nav-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            position: absolute;
            top: 0;
        }

        .nav-bar .admin-panel {
            font-size: 20px;
            font-weight: bold;
            flex-grow: 1;
            text-align: left;
        }

        .config-dropdown button, .logout-button {
            background: white;
            color: #007BFF;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s, color 0.3s;
        }

        .config-dropdown button:hover, .logout-button:hover {
            background-color: #0056b3;
            color: white;
        }

        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 900px;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 60px;
        }

        h1, h2 {
            text-align: center;
            color: #333;
        }

        .button-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
            margin: 20px 0;
        }

        .action-button, .stats-button {
            background-color: #007BFF;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
        }

        .action-button:hover, .stats-button:hover {
            background-color: #0056b3;
        }

        .filter-container {
            width: 50%;
            margin: 10px auto;
            text-align: center;
        }

        .filter-button {
            background-color: #007BFF;
            color: white;
            cursor: pointer;
            padding: 8px;
            border: none;
            border-radius: 5px;
            font-size: 15px;
            transition: background 0.3s;
            margin-bottom: 8px;
            width: 20%;
        }

        .filter-button:hover {
            background-color: #0056b3;
        }

        .filter-form {
            display: none;
            flex-direction: column;
            gap: 8px;
            align-items: center;
        }

        .filter-input {
            width: 50%;
            padding: 6px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 12px;
        }

        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
            text-align: center;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #007BFF;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f4f4f4;
        }

        .no-data {
            text-align: center;
            color: #666;
            font-style: italic;
        }

        .actions-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .actions-buttons button {
            background-color: transparent;
            border: none;
            cursor: pointer;
            color: #007BFF;
            font-size: 18px;
        }

        .actions-buttons button:hover {
            color: #0056b3;
        }

        .header-buttons {
            display: flex;
            align-items: center;
            gap: 10px;
            justify-content: center;
        }

        .header-buttons button {
            background-color: transparent;
            border: none;
            cursor: pointer;
            color: white;
            font-size: 18px;
        }

        .header-buttons button:hover {
            color: #0056b3;
        }

        .user-header .actions-buttons button {
            color: white;
        }

        .user-header .actions-buttons button:hover {
            color: #0056b3;
        }

        .add-hour-button {
            font-size: 18px;
            color: white;
            cursor: pointer;
        }

        .filter-button {
    background-color: #007BFF;
    color: white;
    cursor: pointer;
    padding: 12px 20px; /* Aumenta el tamaño del botón */
    border: none;
    border-radius: 8px; /* Aumenta el radio para redondear más */
    font-size: 16px; /* Aumenta el tamaño de la fuente */
    transition: background 0.3s;
    margin-bottom: 12px; /* Aumenta el margen inferior */
    width: 25%; /* Aumenta el tamaño del botón */
}

.filter-button:hover {
    background-color: #0056b3;
}

.filter-input {
    width: 60%; /* Aumenta el tamaño de los campos de entrada */
    padding: 12px; /* Aumenta el padding de los campos */
    border: 1px solid #ccc;
    border-radius: 8px; /* Aumenta el radio para redondear los campos */
    font-size: 14px; /* Aumenta el tamaño de la fuente */
    margin-bottom: 12px; /* Aumenta el margen inferior de los campos */
}

select.filter-input {
    font-size: 14px; /* Aumenta el tamaño de la fuente en los select */
    padding: 12px; /* Aumenta el padding de los select */
}


        .add-hour-button:hover {
            color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="nav-bar">
        <div class="admin-panel">ADMIN PANEL - <?= htmlspecialchars($nombreUsuario) . ' ' . htmlspecialchars($apellidoUsuario) ?></div>
        <button class="logout-button" onclick="confirmLogout()">Cerrar Sesión</button>
    </div>

    <div class="container">
        <h1>TimeSheet</h1>

        <div class="filter-container">
    <button class="filter-button" onclick="toggleFilters()">Filtrar</button>
    <form class="filter-form" id="filterForm" method="POST">
        <!-- Filtrar por Usuario: Lista desplegable -->
       
        <select name="filter_usuario" class="filter-input" style="width: 50%;">
            <option value="">Filtrar por Usuario</option>
            <?php foreach ($usuarios as $usuario): ?>
                <option value="<?= htmlspecialchars($usuario['nombre']) ?>" <?= $filterUsuario == $usuario['nombre'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($usuario['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- Filtrar por Proyecto: Lista desplegable -->
        
        <select name="filter_proyecto" class="filter-input" style="width: 50%;">
            <option value="">Filtrar por Proyecto</option>
            <?php foreach ($proyectos as $proyecto): ?>
                <option value="<?= htmlspecialchars($proyecto['nombre']) ?>" <?= $filterProyecto == $proyecto['nombre'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($proyecto['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- Filtrar por Fechas -->
        <div style="width: 100%; text-align: center;">
            <label for="filter_fecha_inicio">Filtrar por rango de Fechas:</label><br><br>
            <input type="date" name="filter_fecha_inicio" class="filter-input" value="<?= htmlspecialchars($filterFechaInicio) ?>" style="width: 48%;">
            <span> - </span>
            <input type="date" name="filter_fecha_fin" class="filter-input" value="<?= htmlspecialchars($filterFechaFin) ?>" style="width: 48%;">
        </div>
        

        <!-- Filtrar por Horas -->
        <input type="number" name="filter_horas" class="filter-input" placeholder="Filtrar por Horas" value="<?= htmlspecialchars($filterHoras) ?>">

        <!-- Botón para enviar los filtros -->
        <input type="submit" value="Filtrar" class="filter-button">
    </form>
</div>

        <h2>Horas Trabajadas</h2>

        <table>
            <thead>
                <tr>
                    <th class="user-header">Usuario
                        <div class="actions-buttons">
                            <button onclick="window.location.href='modify_employees.php'"><i class="fas fa-pencil-alt"></i></button>
                            <button onclick="window.location.href='delete_employees.php'"><i class="fas fa-times-circle"></i></button>
                            <button onclick="window.location.href='assign_projects.php'"><i class="fas fa-tasks"></i></button>
                        </div>
                    </th>
                    <th class="project-header">Proyecto
                        <div class="header-buttons">
                            <button onclick="window.location.href='modify_projects.php'"><i class="fas fa-pencil-alt"></i></button>
                            <button onclick="window.location.href='delete_project.php'"><i class="fas fa-times-circle"></i></button>
                            <button onclick="window.location.href='add_project.php'"><i class="fas fa-plus-circle"></i></button>
                        </div>
                    </th>
                    <th>Fecha</th>
                    <th>Horas
                        <br>
                        <span class="add-hour-button" onclick="window.location.href='add_hours.php'">+</span>

                        <span class="stats-icon" onclick="window.location.href='metrics.php'">
                            <i class="fas fa-chart-bar"></i>
                        </span>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty(array_filter($filteredHorasTrabajadas))): ?>
                    <tr>
                        <td colspan="4" class="no-data">Sin datos existentes</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($filteredHorasTrabajadas as $proyectoId => $horas): ?>
                        <?php foreach ($horas as $hora): ?>
                        <tr>
                            <td><?= htmlspecialchars($hora['usuario_nombre']) ?></td>
                            <td><?= htmlspecialchars($proyectosPorId[$proyectoId]) ?></td>
                            <td><?= htmlspecialchars($hora['fecha']) ?></td>
                            <td><?= htmlspecialchars($hora['horas']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        function toggleFilters() {
            var form = document.getElementById("filterForm");
            form.style.display = form.style.display === "none" ? "flex" : "none";
        }

        function confirmLogout() {
            if (confirm("¿Estás seguro de que deseas cerrar sesión?")) {
                window.location.href = 'logout.php';
            }
        }
    </script>
</body>
</html>
