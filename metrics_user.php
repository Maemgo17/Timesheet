<?php
session_start();
include 'db.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Obtener el ID del usuario autenticado
$usuarioId = $_SESSION['usuario_id'];

// Obtener la fecha seleccionada para el filtro, si existe
$fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';

// Obtener el filtro por proyecto
$proyectoFiltro = isset($_GET['proyecto']) ? $_GET['proyecto'] : '';

// Consultas para obtener datos según la fecha seleccionada, solo para el usuario autenticado
$query = "SELECT proyectos.nombre AS proyecto_nombre, proyectos.id AS proyecto_id, SUM(horas_trabajadas.horas) AS total_horas
          FROM horas_trabajadas
          JOIN proyectos ON horas_trabajadas.proyecto_id = proyectos.id
          WHERE horas_trabajadas.usuario_id = :usuario_id";

// Si hay un filtro por proyecto, agregamos la condición correspondiente
if ($proyectoFiltro) {
    $query .= " AND proyectos.id = :proyecto";
}

// Agregar el filtro por fecha solo si alguno de los dos campos de fecha es proporcionado
if ($fechaInicio && $fechaFin) {
    $query .= " AND horas_trabajadas.fecha BETWEEN :fecha_inicio AND :fecha_fin";
} elseif ($fechaInicio) {
    $query .= " AND horas_trabajadas.fecha >= :fecha_inicio";
} elseif ($fechaFin) {
    $query .= " AND horas_trabajadas.fecha <= :fecha_fin";
}

$query .= " GROUP BY proyectos.nombre"; // Asegurarse de agrupar por proyecto

// Ejecutar la consulta
$stmt = $pdo->prepare($query);

// Bind de los parámetros
$params = ['usuario_id' => $usuarioId];
if ($proyectoFiltro) {
    $params['proyecto'] = $proyectoFiltro; // Usar el ID del proyecto para la consulta
}
if ($fechaInicio) {
    $params['fecha_inicio'] = $fechaInicio;
}
if ($fechaFin) {
    $params['fecha_fin'] = $fechaFin;
}

$stmt->execute($params);
$totalHorasProyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consultar el total de horas trabajadas por el usuario sin filtro de fecha
$stmt = $pdo->prepare("SELECT SUM(horas) AS total_horas
                       FROM horas_trabajadas
                       WHERE usuario_id = :usuario_id");
$stmt->execute(['usuario_id' => $usuarioId]);
$totalHoras = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener la lista de proyectos disponibles
$queryProyectos = "SELECT id, nombre FROM proyectos";
$stmtProyectos = $pdo->prepare($queryProyectos);
$stmtProyectos->execute();
$proyectos = $stmtProyectos->fetchAll(PDO::FETCH_ASSOC);
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
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
        }

        body {
            background-color: #A3C1DA;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 6px 30px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 800px; /* Ancho ajustado para centrado */
            padding: 40px;
            text-align: center;
        }

        h1 {
            font-size: 2.5em;
            color: #333;
            margin-bottom: 30px;
        }

        .filter-button, .back-button {
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 14px 24px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            width: 180px;
            margin-top: 5px;
        }

        .filter-button:hover, .back-button:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        .filters {
            display: none;
            gap: 10px;
            justify-content: center;
            margin-top: 20px;
            text-align: center;
        }

        .filters.active {
            display: flex;
        }

        .filter-input {
            padding: 12px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 6px;
            width: 250px;
            margin-bottom: 20px;
        }

        .date-filters {
            margin-bottom: 20px;
        }

        .date-filters input[type="date"] {
            padding: 12px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 6px;
            margin: 0 5px;
        }

        .filters button {
            padding: 12px 24px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s ease;
        }

        .filters button:hover {
            background-color: #0056b3;
        }

        .chart-container {
            display: flex;
            justify-content: center; /* Centrado del gráfico */
            flex-wrap: wrap;
        }

        .chart-box {
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            max-width: 600px; /* Ancho del gráfico */
            width: 100%;
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
        <h2>Panel de Métricas de Horas Trabajadas</h2>

        <!-- Botón para activar el formulario de filtros -->
        <div class="filter-container">
            <button class="filter-button" id="showFilters">Filtrar</button>
        </div>

        <!-- Formulario de filtros -->
        <div class="filters" id="filtersForm">
            <form method="GET" action="">
                <!-- Filtro de proyecto como un desplegable -->
                <select name="proyecto" class="filter-input">
                    <option value="">Seleccionar Proyecto</option>
                    <?php foreach ($proyectos as $proyecto): ?>
                        <option value="<?= htmlspecialchars($proyecto['id']) ?>" <?= ($proyecto['id'] == $proyectoFiltro) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($proyecto['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- Filtro de fecha -->
                <div class="date-filters">
                    <input type="date" name="fecha_inicio" value="<?= htmlspecialchars($fechaInicio) ?>">
                    <span>-</span>
                    <input type="date" name="fecha_fin" value="<?= htmlspecialchars($fechaFin) ?>">
                </div>

                <!-- Botón de filtro -->
                <button type="submit" class="filter-button">Aplicar Filtro</button>
            </form>
        </div>

        <br>

        <!-- Contenedor del gráfico centrado -->
        <div class="chart-container">
            <div class="chart-box">
                <h2>Total de Horas por Proyecto</h2>
                <canvas id="proyectoChart"></canvas>
            </div>
        </div>

        <button class="back-button" onclick="window.location.href='index_usuario.php'">Volver</button>
    </div>

    <script>
        // Mostrar el formulario de filtros cuando se haga clic en el botón
        document.getElementById('showFilters').addEventListener('click', function() {
            document.getElementById('filtersForm').classList.toggle('active');
        });

        // Datos de proyectos para el gráfico de torta
        const proyectosData = <?= json_encode($totalHorasProyectos) ?>;
        const proyectoLabels = proyectosData.map(p => p.proyecto_nombre);
        const horasData = proyectosData.map(p => p.total_horas);

        // Configuración del gráfico de torta
        const ctxProyecto = document.getElementById('proyectoChart').getContext('2d');
        new Chart(ctxProyecto, {
            type: 'pie',
            data: {
                labels: proyectoLabels,
                datasets: [{
                    data: horasData,
                    backgroundColor: ['#FF5733', '#33FF57', '#3357FF', '#FF33A8', '#FFAA33'],
                    borderColor: ['#FF5733', '#33FF57', '#3357FF', '#FF33A8', '#FFAA33'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.label + ': ' + tooltipItem.raw + ' horas';
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
