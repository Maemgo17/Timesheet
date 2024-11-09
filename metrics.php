<?php
session_start();
include 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Obtener los filtros (si existen)
$fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';
$proyectoId = isset($_GET['proyecto_id']) ? $_GET['proyecto_id'] : '';
$usuarioId = isset($_GET['usuario_id']) ? $_GET['usuario_id'] : '';

// Consultas para obtener datos según los filtros
// Primera consulta (Usuarios)
$query = "SELECT usuarios.nombre AS usuario_nombre, SUM(horas_trabajadas.horas) AS total_horas
          FROM horas_trabajadas
          JOIN usuarios ON horas_trabajadas.usuario_id = usuarios.id
          JOIN proyectos ON horas_trabajadas.proyecto_id = proyectos.id";
$params = [];

if ($fechaInicio && $fechaFin) {
    $query .= " WHERE fecha BETWEEN :fecha_inicio AND :fecha_fin";
    $params['fecha_inicio'] = $fechaInicio;
    $params['fecha_fin'] = $fechaFin;
}

if ($proyectoId) {
    $query .= isset($params['fecha_inicio']) ? " AND proyectos.id = :proyecto_id" : " WHERE proyectos.id = :proyecto_id";
    $params['proyecto_id'] = $proyectoId;
}

if ($usuarioId) {
    $query .= isset($params['fecha_inicio']) || isset($params['proyecto_id']) ? " AND usuarios.id = :usuario_id" : " WHERE usuarios.id = :usuario_id";
    $params['usuario_id'] = $usuarioId;
}

$query .= " GROUP BY usuarios.id";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$totalHorasUsuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Segunda consulta (Proyectos)
$query = "SELECT proyectos.nombre AS proyecto_nombre, SUM(horas_trabajadas.horas) AS total_horas
          FROM horas_trabajadas
          JOIN proyectos ON horas_trabajadas.proyecto_id = proyectos.id";
$params = [];

if ($fechaInicio && $fechaFin) {
    $query .= " WHERE fecha BETWEEN :fecha_inicio AND :fecha_fin";
    $params['fecha_inicio'] = $fechaInicio;
    $params['fecha_fin'] = $fechaFin;
}

if ($proyectoId) {
    $query .= isset($params['fecha_inicio']) ? " AND proyectos.id = :proyecto_id" : " WHERE proyectos.id = :proyecto_id";
    $params['proyecto_id'] = $proyectoId;
}

$query .= " GROUP BY proyectos.id";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$totalHorasProyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener la lista de proyectos y usuarios para el filtro
$stmt = $pdo->query("SELECT id, nombre FROM proyectos");
$proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT id, nombre FROM usuarios");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Métricas de Horas Trabajadas</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
        }

        body {
            background-color: #A3C1DA;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .container {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 900px;
            padding: 40px;
            text-align: center;
        }

        h1 {
            font-size: 2.5em;
            color: #333;
            margin-bottom: 30px;
        }

        .filter-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .filter-button, .back-button {
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 12px 24px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s, transform 0.3s;
            width: 180px;
        }

        .filter-button:hover, .back-button:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        .date-filters {
            display: none;
            margin-top: 20px;
            gap: 20px;
            justify-content: center;
        }

        .date-filters.active {
            display: flex;
        }

        select, input[type="date"] {
            padding: 12px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 6px;
            width: 180px;
        }

        .date-filters button {
            padding: 12px 24px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s ease;
        }

        .date-filters button:hover {
            background-color: #0056b3;
        }

        .chart-container {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
        }

        .chart-box {
            flex: 1;
            min-width: 300px;
            max-width: 480px;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        canvas {
            width: 100% !important;
            height: 300px !important;
        }

        .back-button {
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Métricas de Horas Trabajadas</h1>

        <!-- Botón para activar el filtro -->
        <div class="filter-container">
            <button class="filter-button" id="showDateFilter">Filtrar</button>
        </div>

        <!-- Formulario de filtro -->
        <div class="date-filters" id="dateFilterForm">
            <form method="GET" action="">
                <input type="date" name="fecha_inicio" value="<?= htmlspecialchars($fechaInicio) ?>">
                <span>-</span>
                <input type="date" name="fecha_fin" value="<?= htmlspecialchars($fechaFin) ?>">
                <br><br>
                <select name="proyecto_id">
                    <option value="">Seleccionar Proyecto</option>
                    <?php foreach ($proyectos as $proyecto): ?>
                        <option value="<?= $proyecto['id'] ?>" <?= $proyecto['id'] == $proyectoId ? 'selected' : '' ?>>
                            <?= htmlspecialchars($proyecto['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <br><br>
                <select name="usuario_id">
                    <option value="">Seleccionar Usuario</option>
                    <?php foreach ($usuarios as $usuario): ?>
                        <option value="<?= $usuario['id'] ?>" <?= $usuario['id'] == $usuarioId ? 'selected' : '' ?>>
                            <?= htmlspecialchars($usuario['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <br><br>
                <button type="submit">Aplicar Filtro</button>
                <br>
                <br>
            </form>
        </div>

        <div class="chart-container">
            <!-- Gráfico de barras para horas por usuario -->
            <div class="chart-box">
                <h2>Total de Horas por Usuario</h2>
                <canvas id="usuarioChart"></canvas>
            </div>
            <!-- Gráfico de torta para horas por proyecto -->
            <div class="chart-box">
                <h2>Total de Horas por Proyecto</h2>
                <canvas id="proyectoChart"></canvas>
            </div>
        </div>

        <button class="back-button" onclick="window.location.href='index.php'">Volver</button>
    </div>

    <script>
        // Mostrar el formulario de filtros
        document.getElementById('showDateFilter').addEventListener('click', function() {
            document.getElementById('dateFilterForm').classList.toggle('active');
        });

        // Datos de usuarios para el gráfico de barras
        const usuarioNombres = <?= json_encode(array_column($totalHorasUsuarios, 'usuario_nombre')) ?>;
        const usuarioHoras = <?= json_encode(array_column($totalHorasUsuarios, 'total_horas')) ?>;

        // Configuración del gráfico de barras para usuarios
        const ctxUsuario = document.getElementById('usuarioChart').getContext('2d');
        new Chart(ctxUsuario, {
            type: 'bar',
            data: {
                labels: usuarioNombres,
                datasets: [{
                    label: 'Total Horas',
                    data: usuarioHoras,
                    backgroundColor: '#007bff',
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: { 
                        title: { display: true, text: 'Usuario' }
                    },
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Horas' }
                    }
                }
            }
        });

        // Datos de proyectos para el gráfico de torta
        const proyectoNombres = <?= json_encode(array_column($totalHorasProyectos, 'proyecto_nombre')) ?>;
        const proyectoHoras = <?= json_encode(array_column($totalHorasProyectos, 'total_horas')) ?>;

        // Configuración del gráfico de torta para proyectos
        const ctxProyecto = document.getElementById('proyectoChart').getContext('2d');
        new Chart(ctxProyecto, {
            type: 'pie',
            data: {
                labels: proyectoNombres,
                datasets: [{
                    label: 'Total Horas',
                    data: proyectoHoras,
                    backgroundColor: ['#FF5733', '#33FF57', '#3357FF', '#FF33A8'],
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
            }
        });
    </script>
</body>
</html>
