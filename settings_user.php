<?php
session_start(); // Iniciar sesión
include 'db.php'; // Incluir la conexión a la base de datos

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php"); // Redirigir a la página de inicio de sesión si no está autenticado
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #A3C1DA;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
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
            position: fixed;
            top: 0;
            z-index: 1000;
        }

        .nav-bar button {
            background: white;
            color: #007BFF;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s, color 0.3s;
        }

        .nav-bar button:hover {
            background-color: #0056b3;
            color: white;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 500px;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 80px;
            position: relative;
            z-index: 1;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
            font-size: 1.8em;
        }

        .section {
            width: 100%;
            margin: 10px 0;
        }

        .section-title {
            font-size: 1.5em;
            color: #007BFF;
            margin-bottom: 10px;
            text-align: center;
            cursor: pointer;
            padding: 10px;
            background-color: #e7f3ff;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .section-title:hover {
            background-color: #d0e4ff;
        }

        .actions {
            width: 100%;
            display: none;
            flex-direction: column;
            align-items: center;
        }

        .action-button {
            background: #4CAF50;
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
            background: #45a049;
            transform: scale(1.05);
        }

        .footer {
            margin-top: auto;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="nav-bar">
        <button onclick="window.location.href='index_usuario.php'">Volver al Inicio</button>
        <button onclick="confirmLogout()">Cerrar Sesión</button>
    </div>

    <div class="container">
        <h1>Configuración</h1>
        
        <div class="section">
            <div class="section-title" onclick="toggleActions('horas')">GESTIÓN DE HORAS</div>
            <div class="actions" id="horas">
                <button class="action-button" onclick="window.location.href='add_hours_user.php'">Agregar Horas</button>
                <button class="action-button" onclick="window.location.href='metrics_user.php'">Ver Métricas</button>
            </div>
        </div>
    </div>

    <script>
        function confirmLogout() {
            if (confirm("¿Estás seguro de que deseas cerrar sesión?")) {
                window.location.href = 'logout.php';
            }
        }

        function toggleActions(section) {
            const sections = ['horas'];
            
            // Ocultar todas las acciones
            sections.forEach((sec) => {
                const actions = document.getElementById(sec);
                actions.style.display = "none"; // Oculta las demás secciones
            });

            // Alternar la sección que se está clickeando
            const actions = document.getElementById(section);
            if (actions.style.display === "flex") {
                actions.style.display = "none"; // Ocultar si ya está visible
            } else {
                actions.style.display = "flex"; // Mostrar si está oculto
            }
        }
    </script>
</body>
</html>
