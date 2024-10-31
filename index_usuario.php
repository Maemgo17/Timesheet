<?php
session_start();
include 'db.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Obtener ID del usuario actual
$usuarioId = $_SESSION['usuario_id'];

// Determinar si el usuario es administrador
$isAdmin = $_SESSION['is_admin'] ?? false;

$horasTrabajadas = [];

// Obtener proyectos
if ($isAdmin) {
    $stmt = $pdo->query("SELECT id, nombre FROM proyectos");
} else {
    // Si no es admin, solo obtener los proyectos asociados a su usuario
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

// Obtener horas trabajadas
foreach ($proyectos as $proyecto) {
    $stmt = $pdo->prepare("SELECT horas_trabajadas.* FROM horas_trabajadas
                           WHERE horas_trabajadas.proyecto_id = :proyecto_id" . 
                           (!$isAdmin ? " AND horas_trabajadas.usuario_id = :usuario_id" : ""));

    // Si no es administrador, añadir el usuario ID a la consulta
    if (!$isAdmin) {
        $stmt->execute(['proyecto_id' => $proyecto['id'], 'usuario_id' => $usuarioId]);
    } else {
        $stmt->execute(['proyecto_id' => $proyecto['id']]);
    }

    $horasTrabajadas[$proyecto['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Filtros
$filterProyecto = isset($_POST['filter_proyecto']) ? $_POST['filter_proyecto'] : '';
$filterFecha = isset($_POST['filter_fecha']) ? $_POST['filter_fecha'] : '';

$filteredHorasTrabajadas = [];
foreach ($horasTrabajadas as $proyectoId => $horas) {
    foreach ($horas as $hora) {
        if (($filterProyecto === '' || stripos($proyectosPorId[$proyectoId], $filterProyecto) !== false) &&
            ($filterFecha === '' || stripos($hora['fecha'], $filterFecha) !== false)) {
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
            text-align: center;
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

        .filter-container {
            width: 80%;
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
            font-size: 12px;
            transition: background 0.3s;
            margin-bottom: 8px;
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
    </style>
</head>
<body>
    <div class="nav-bar">
        <div class="config-dropdown">
            <button onclick="window.location.href='settings_user.php'">Configuración</button>
        </div>
        <div class="user-panel">USER PANEL</div>
        <button class="logout-button" onclick="confirmLogout()">Cerrar Sesión</button>
    </div>
    
    <div class="container">
        <h1>TimeSheet</h1>

        <!-- Botón para mostrar/ocultar filtros -->
        <div class="filter-container">
            <button class="filter-button" onclick="toggleFilters()">Mostrar Filtros</button>
            <form method="post" class="filter-form" id="filterForm">
                <!-- Eliminar filtro por usuario -->
                <input type="text" name="filter_proyecto" placeholder="Filtrar por Proyecto" class="filter-input" value="<?= htmlspecialchars($filterProyecto) ?>">
                <input type="text" name="filter_fecha" placeholder="Filtrar por Fecha" class="filter-input" value="<?= htmlspecialchars($filterFecha) ?>">
                <input type="submit" value="Filtrar" class="filter-button">
            </form>
        </div>

        <!-- Tabla de horas trabajadas -->
        <h2>Horas Trabajadas en Proyectos</h2>
        <table>
            <thead>
                <tr>
                    <th>Proyecto</th>
                    <th>Fecha</th>
                    <th>Horas</th>
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
