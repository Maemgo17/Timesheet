<?php
session_start();
include 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuarioId = $_SESSION['usuario_id'];

$isAdmin = $_SESSION['is_admin'] ?? false;

$stmt = $pdo->prepare("SELECT nombre, apellido FROM usuarios WHERE id = :usuario_id");
$stmt->execute(['usuario_id' => $usuarioId]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
$nombreUsuario = $usuario['nombre'];
$apellidoUsuario = $usuario['apellido'];

$horasTrabajadas = [];

if ($isAdmin) {
    $stmt = $pdo->query("SELECT id, nombre FROM proyectos");
} else {
    $stmt = $pdo->prepare("SELECT p.id, p.nombre FROM proyectos p
                            JOIN horas_trabajadas h ON p.id = h.proyecto_id
                            WHERE h.usuario_id = :usuario_id
                            GROUP BY p.id, p.nombre");
    $stmt->execute(['usuario_id' => $usuarioId]);
}

$proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);
$proyectosPorId = [];
foreach ($proyectos as $proyecto) {
    $proyectosPorId[$proyecto['id']] = $proyecto['nombre'];
}

foreach ($proyectos as $proyecto) {
    $stmt = $pdo->prepare("SELECT horas_trabajadas.* FROM horas_trabajadas
                           WHERE horas_trabajadas.proyecto_id = :proyecto_id" . 
                           (!$isAdmin ? " AND horas_trabajadas.usuario_id = :usuario_id" : ""));

    if (!$isAdmin) {
        $stmt->execute(['proyecto_id' => $proyecto['id'], 'usuario_id' => $usuarioId]);
    } else {
        $stmt->execute(['proyecto_id' => $proyecto['id']]);
    }

    $horasTrabajadas[$proyecto['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$filterProyecto = isset($_POST['filter_proyecto']) ? $_POST['filter_proyecto'] : '';
$filterFechaInicio = isset($_POST['filter_fecha_inicio']) ? $_POST['filter_fecha_inicio'] : '';
$filterFechaFin = isset($_POST['filter_fecha_fin']) ? $_POST['filter_fecha_fin'] : '';
$filterHoras = isset($_POST['filter_horas']) ? $_POST['filter_horas'] : '';

$filteredHorasTrabajadas = [];
foreach ($horasTrabajadas as $proyectoId => $horas) {
    foreach ($horas as $hora) {
        $fechaValida = true;
        $horasValidas = true;

        if ($filterFechaInicio && $filterFechaFin) {
            $fechaValida = (strtotime($hora['fecha']) >= strtotime($filterFechaInicio)) && (strtotime($hora['fecha']) <= strtotime($filterFechaFin));
        }

        if ($filterHoras) {
            $horasValidas = $hora['horas'] == $filterHoras;
        }

        if (($filterProyecto === '' || $proyectoId == $filterProyecto) &&
            $fechaValida && $horasValidas) {
            $filteredHorasTrabajadas[$proyectoId][] = $hora;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
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

        .nav-bar .user-panel {
            font-size: 20px;
            font-weight: bold;
            flex-grow: 1;
            text-align: left;
        }

        .logout-button {
            background: white;
            color: #007BFF;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s, color 0.3s;
        }

        .logout-button:hover {
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

        .filter-container {
            width: 60%;
            margin: 10px auto;
            text-align: center;
        }

        .filter-button {
            background-color: #007BFF; 
            color: white;
            cursor: pointer;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            transition: background 0.3s;
            margin-bottom: 12px;
            width: 150px !important; 
            max-width: 250px;
        }

        .filter-button:hover {
            background-color: #0056b3; 
        }

        .filter-form {
            display: none;
            flex-direction: column;
            gap: 12px;
            align-items: center;
        }

        .filter-input {
            width: 50%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
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

        .stats-icon {
            cursor: pointer;
            font-size: 15px; 
            margin-left: 10px;  
        }

        .stats-icon:hover {
            transform: scale(0.8); 
        }

        .action-button {
            background: #28a745;
            color: white;
            width: 100%;
            max-width: 300px;
            border: none;
            border-radius: 5px;
            padding: 12px;
            margin: 10px 0;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s, transform 0.3s;
        }

        .action-button:hover {
            background: #218838;
            transform: scale(1.05);
        }

        .action-button, .filter-button {
            width: 100%;
            max-width: 250px;
        }
    </style>
</head>
<body>
    <div class="nav-bar">
        <div class="user-panel">USER PANEL -  <?= htmlspecialchars($nombreUsuario) . ' ' . htmlspecialchars($apellidoUsuario) ?></div>
        <button class="logout-button" onclick="confirmLogout()">Cerrar Sesión</button>
    </div>
    
    <div class="container">
        <h1>TimeSheet</h1>

        <div class="filter-container">
            <button class="filter-button" onclick="toggleFilters()">Filtrar</button>
            <form class="filter-form" id="filterForm" method="POST">
                <!-- Filtro por Proyecto -->
                <select name="filter_proyecto" class="filter-input">
                    <option value="">Seleccione un Proyecto</option>
                    <?php foreach ($proyectos as $proyecto): ?>
                        <option value="<?= $proyecto['id'] ?>" <?= $filterProyecto == $proyecto['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($proyecto['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <br>
                <!-- Filtro por Fechas -->
                <div style="width: 100%; text-align: center;">
                    <label for="filter_fecha_inicio">Filtrar por rango de Fechas:</label><br>
                    <input type="date" name="filter_fecha_inicio" class="filter-input" value="<?= htmlspecialchars($filterFechaInicio) ?>" style="width: 48%;">
                    <span> - </span>
                    <input type="date" name="filter_fecha_fin" class="filter-input" value="<?= htmlspecialchars($filterFechaFin) ?>" style="width: 48%;">
                </div>
                <br>
                <!-- Filtro por Horas -->
                <input type="number" name="filter_horas" class="filter-input" placeholder="Filtrar por Horas" value="<?= htmlspecialchars($filterHoras) ?>">

                <input type="submit" value="Filtrar" class="filter-button">
            </form>
        </div>

        <h2>Horas Trabajadas en Proyectos</h2>
        
        <table>
            <thead>
                <tr>
                    <th>Proyecto</th>
                    <th>Fecha</th>
                    <th>Horas
                    <br>
                        <span class="add-hour-button" onclick="window.location.href='add_hours_user.php'">+</span>
                        <span class="stats-icon" onclick="window.location.href='metrics_user.php'">
                            <i class="fas fa-chart-bar"></i>
                        </span>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty(array_filter($filteredHorasTrabajadas))): ?>
                    <tr>
                        <td colspan="3" class="no-data">Sin datos existentes</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($filteredHorasTrabajadas as $proyectoId => $horas): ?>
                        <?php foreach ($horas as $hora): ?>
                        <tr>
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
            const form = document.getElementById('filterForm');
            form.style.display = form.style.display === 'flex' ? 'none' : 'flex';
        }

        function confirmLogout() {
            if (confirm("¿Estás seguro de que quieres cerrar sesión?")) {
                window.location.href = "logout.php";
            }
        }
    </script>
</body>
</html>
