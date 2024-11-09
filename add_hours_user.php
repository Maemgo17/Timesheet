<?php
session_start(); 
include 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index_usuario.php"); 
    exit;
}

$usuarioId = $_SESSION['usuario_id'];

$stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = :id");
$stmt->execute(['id' => $usuarioId]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT p.* FROM proyectos p JOIN proyectos_asignados pa ON p.id = pa.proyecto_id WHERE pa.usuario_id = :usuario_id");
$stmt->execute(['usuario_id' => $usuarioId]);
$proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['addHours'])) {
    $usuarioId = $_SESSION['usuario_id'];
    $proyectoId = $_POST['proyecto_id'];
    $fecha = $_POST['fecha'];
    $horas = $_POST['horas'];

    if ($horas <= 0) {
        echo "<script>alert('Las horas deben ser mayores que 0.'); window.history.back();</script>";
        exit;
    }

    $stmt = $pdo->prepare("SELECT SUM(horas) as total_horas FROM horas_trabajadas WHERE usuario_id = :usuario_id AND proyecto_id = :proyecto_id AND fecha = :fecha");
    $stmt->execute(['usuario_id' => $usuarioId, 'proyecto_id' => $proyectoId, 'fecha' => $fecha]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_horas = $result['total_horas'] ? $result['total_horas'] : 0;

    if ($total_horas + $horas > 8) {
        echo "<script>alert('No se pueden asignar más de 8 horas en un solo día.'); window.history.back();</script>";
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO horas_trabajadas (usuario_id, proyecto_id, fecha, horas) VALUES (:usuario_id, :proyecto_id, :fecha, :horas)");
    $stmt->execute(['usuario_id' => $usuarioId, 'proyecto_id' => $proyectoId, 'fecha' => $fecha, 'horas' => $horas]);

    header("Location: index_usuario.php");
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
            background-color: #A3C1DA;
            display: flex;
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
        }

        .container {
            background: white;
            padding: 30px; 
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 400px; 
            display: flex;
            flex-direction: column;
            align-items: center; 
        }

        h1 {
            color: #333;
            margin-bottom: 20px; 
            text-align: center;
        }

        form {
            width: 100%; 
            display: flex;
            flex-direction: column;
        }

        form input, form select {
            padding: 12px; 
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%; 
            font-size: 16px; 
        }

        button {
            background: #4CAF50;
            color: white; 
            border: none;
            border-radius: 5px;
            padding: 12px; 
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s; 
            width: 50%; 
            margin: 0px auto; 
        }

        button:hover {
            background: #45a049; 
        }

        footer {
            text-align: center;
            margin-top: 20px;
            color: #777;
            width: 100%;
        }

        footer button {
            background: #007BFF; 
            padding: 12px;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
            transition: background 0.3s;
            width: 50%; 
            margin: 0px auto; 
        }

        footer button:hover {
            background: #0056b3; 
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Agregar Horas Trabajadas</h1>
        <form action="add_hours_user.php" method="POST">
            <input type="text" value="<?= htmlspecialchars($usuario['nombre']) ?>" disabled>
            <input type="hidden" name="usuario_id" value="<?= $usuarioId ?>">
            <select name="proyecto_id" required>
                <option value="">Seleccionar Proyecto</option>
                <?php foreach ($proyectos as $proyecto): ?>
                    <option value="<?= $proyecto['id'] ?>"><?= htmlspecialchars($proyecto['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="fecha" required>
            <input type="number" step="0.01" name="horas" placeholder="Horas (Máx 8)" required min="0.01">
            <button type="submit" name="addHours">Agregar Horas</button>
        </form>
        <footer>
            <button onclick="window.location.href='index_usuario.php'">Volver</button>
        </footer>
    </div>
</body>
</html>
