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
$fechaSeleccionada = isset($_GET['fecha']) ? $_GET['fecha'] : '';

// Consultas para obtener datos según la fecha seleccionada, solo para el usuario autenticado
if ($fechaSeleccionada) {
    // Total de horas trabajadas por el usuario
    $stmt = $pdo->prepare("SELECT SUM(horas) AS total_horas
                           FROM horas_trabajadas
                           WHERE usuario_id = :usuario_id AND fecha = :fecha");
    $stmt->execute(['usuario_id' => $usuarioId, 'fecha' => $fechaSeleccionada]);
    $totalHoras = $stmt->fetch(PDO::FETCH_ASSOC);

    // Total de horas trabajadas por proyecto
    $stmt = $pdo->prepare("SELECT proyectos.nombre AS proyecto_nombre, SUM(horas_trabajadas.horas) AS total_horas
                           FROM horas_trabajadas
                           JOIN proyectos ON horas_trabajadas.proyecto_id = proyectos.id
                           WHERE horas_trabajadas.fecha = :fecha
                           GROUP BY proyectos.id");
    $stmt->execute(['fecha' => $fechaSeleccionada]);
    $totalHorasProyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Total de horas trabajadas por el usuario sin filtro de fecha
    $stmt = $pdo->prepare("SELECT SUM(horas) AS total_horas
                           FROM horas_trabajadas
                           WHERE usuario_id = :usuario_id");
    $stmt->execute(['usuario_id' => $usuarioId]);
    $totalHoras = $stmt->fetch(PDO::FETCH_ASSOC);

    // Total de horas trabajadas por proyecto sin filtro de fecha
    $stmt = $pdo->query("SELECT proyectos.nombre AS proyecto_nombre, SUM(horas_trabajadas.horas) AS total_horas
                         FROM horas_trabajadas
                         JOIN proyectos ON horas_trabajadas.proyecto_id = proyectos.id
                         GROUP BY proyectos.id");
    $totalHorasProyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
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
            font-size: 2em;
            color: #333;
            margin-bottom: 20px;
        }

        .filter-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .filter-container input[type="date"] {
            padding: 12px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: auto;
        }

        .filter-button, .back-button {
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 12px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s, transform 0.3s;
            width: 150px;
        }

        .filter-button:hover, .back-button:hover {
            background-color: #45a049;
            transform: scale(1.05);
        }

        .chart-container {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            flex-wrap: wrap;
            gap: 20px; /* Espacio entre gráficos */
        }

        .chart-box {
            flex: 1; /* Permite que los gráficos ocupen el mismo espacio */
            min-width: 300px; /* Ancho mínimo para los gráficos */
            max-width: 400px; /* Ancho máximo para los gráficos */
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        canvas {
            width: 100% !important; /* Asegura que el canvas use el 100% del contenedor */
            height: 300px !important; /* Altura fija para todos los gráficos */
        }

        .back-button {
            margin-top: 40px; /* Separación del botón respecto a los gráficos */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Métricas de Horas Trabajadas</h1>

        <!-- Formulario de filtro de fecha -->
        <div class="filter-container">
            <form method="GET" action="">
                <input type="date" name="fecha" value="<?= htmlspecialchars($fechaSeleccionada) ?>">
                <button class="filter-button" type="submit">Filtrar</button>
            </form>
        </div>

        <div class="chart-container">
            <!-- Gráfico de barras para horas trabajadas por el usuario -->
            <div class="chart-box">
                <h2>Total de Horas Trabajadas</h2>
                <canvas id="usuarioChart"></canvas>
            </div>
            <!-- Gráfico de torta para horas trabajadas por proyecto -->
            <div class="chart-box">
                <h2>Total de Horas por Proyecto</h2>
                <canvas id="proyectoChart"></canvas>
            </div>
        </div>

        <button class="back-button" onclick="window.location.href='settings_user.php'">Volver</button>
    </div>

    <script>
        // Datos para el gráfico de barras
        const totalHoras = <?= json_encode($totalHoras['total_horas'] ?? 0) ?>; // Total de horas del usuario

        // Configuración del gráfico de barras
        const ctxUsuario = document.getElementById('usuarioChart').getContext('2d');
        new Chart(ctxUsuario, {
            type: 'bar',
            data: {
                labels: ['Horas Trabajadas'],
                datasets: [{
                    label: 'Total Horas',
                    data: [totalHoras],
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                responsive: true,
                plugins: {
                    legend: { display: false }
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
                    data: proyectoHoras,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(153, 102, 255, 0.6)',
                        'rgba(255, 159, 64, 0.6)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                }
            }
        });
    </script>
</body>
</html>
