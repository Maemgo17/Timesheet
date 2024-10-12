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

// Agregar horas trabajadas
if (isset($_POST['addHours'])) {
    $empleadoId = $_POST['empleado_id'];
    $proyectoId = $_POST['proyecto_id'];
    $fecha = $_POST['fecha'];
    $horas = $_POST['horas'];

    // Verificar horas trabajadas ya registradas para el empleado y proyecto en esa fecha
    $stmt = $pdo->prepare("SELECT SUM(horas) as total_horas FROM horas_trabajadas WHERE empleado_id = :empleado_id AND proyecto_id = :proyecto_id AND fecha = :fecha");
    $stmt->execute(['empleado_id' => $empleadoId, 'proyecto_id' => $proyectoId, 'fecha' => $fecha]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_horas = $result['total_horas'] ? $result['total_horas'] : 0;

    // Verificar si la suma total de horas superaría las 8
    if ($total_horas + $horas > 8) {
        // Mensaje de error si se supera el límite de horas
        echo "<script>alert('No se pueden asignar más de 8 horas en un solo día.'); window.history.back();</script>";
        exit;
    }

    // Insertar horas trabajadas
    $stmt = $pdo->prepare("INSERT INTO horas_trabajadas (empleado_id, proyecto_id, fecha, horas) VALUES (:empleado_id, :proyecto_id, :fecha, :horas)");
    $stmt->execute(['empleado_id' => $empleadoId, 'proyecto_id' => $proyectoId, 'fecha' => $fecha, 'horas' => $horas]);

    // Redirigir para evitar duplicación al recargar la página
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Horas Trabajadas</title>
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
            height: 100vh; /* Ocupa toda la altura de la ventana */
        }

        .container {
            background: white;
            padding: 30px; /* Espaciado más amplio para mayor comodidad */
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 400px;
            display: flex;
            flex-direction: column;
            align-items: center; /* Centrar elementos dentro del contenedor */
        }

        h1 {
            color: #333;
            margin-bottom: 20px; /* Espacio debajo del título */
            text-align: center; /* Centrar el texto del título */
            width: 100%; /* Hacer que el título ocupe todo el ancho */
        }

        form {
            width: 100%;
            display: flex;
            flex-direction: column; /* Organiza los elementos verticalmente */
        }

        form input, form select {
            padding: 12px; /* Más espacio para mayor comodidad */
            margin-bottom: 15px; /* Espaciado entre los campos */
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
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
            width: 100%; /* Botón ocupa el 100% del ancho */
        }

        button:hover {
            background: #45a049; /* Color más oscuro al pasar el mouse */
        }

        footer {
            text-align: center;
            margin-top: 20px;
            color: #777;
        }

        footer button {
            background: #007BFF; /* Color azul para el botón de volver */
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
            transition: background 0.3s;
            width: 100%; /* Asegurarse de que ocupe todo el ancho */
        }

        footer button:hover {
            background: #0056b3; /* Color más oscuro al pasar el mouse */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Agregar Horas Trabajadas</h1>
        <form action="add_hours.php" method="POST">
            <select name="empleado_id" required>
                <option value="">Seleccionar Empleado</option>
                <?php foreach ($empleados as $empleado): ?>
                    <option value="<?= $empleado['id'] ?>"><?= htmlspecialchars($empleado['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="proyecto_id" required>
                <option value="">Seleccionar Proyecto</option>
                <?php foreach ($proyectos as $proyecto): ?>
                    <option value="<?= $proyecto['id'] ?>"><?= htmlspecialchars($proyecto['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="fecha" required>
            <input type="number" step="0.01" name="horas" placeholder="Horas" required>
            <button type="submit" name="addHours">Agregar Horas</button>
        </form>
        <footer>
            <button onclick="window.location.href='index.php'">Volver</button>
        </footer>
    </div>
</body>
</html>
