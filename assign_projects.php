<?php
session_start(); 
include 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php"); 
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt->execute(['id' => $_SESSION['usuario_id']]);
$usuarioActual = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuarioActual['rol'] !== 'admin') {
    echo "<script>alert('No tiene permiso para asignar proyectos.'); window.location.href='index.php';</script>";
    exit;
}

$stmt = $pdo->query("SELECT * FROM proyectos");
$proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT * FROM usuarios WHERE rol != 'admin'");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarioId = $_POST['usuario_id'];
    $proyectoId = $_POST['proyecto_id'];

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM proyectos_asignados WHERE usuario_id = :usuario_id AND proyecto_id = :proyecto_id");
    $stmt->execute(['usuario_id' => $usuarioId, 'proyecto_id' => $proyectoId]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        echo "<script>alert('El proyecto ya está asignado a este usuario.'); window.location.href='assign_projects.php';</script>";
        exit;
    } else {
        $stmt = $pdo->prepare("INSERT INTO proyectos_asignados (usuario_id, proyecto_id) VALUES (:usuario_id, :proyecto_id)");
        $stmt->execute(['usuario_id' => $usuarioId, 'proyecto_id' => $proyectoId]);

        echo "<script>alert('Proyecto asignado exitosamente.'); window.location.href='index.php';</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Proyectos a Usuarios</title>
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
            align-items: center;
        }

        select {
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
            margin: 0 auto; 
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
            margin: 0 auto; 
        }

        footer button:hover {
            background: #0056b3; 
        }
    </style>
    <script>
        function confirmAssignment() {
            return confirm('¿Está seguro que desea asignar este proyecto al usuario?');
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Asignar Proyectos</h1>
        
        <form method="POST" onsubmit="return confirmAssignment();">
            <div class="form-group">
                <label for="usuario_id">Selecciona el Usuario:</label>
                <select name="usuario_id" required>
                    <option value="">Seleccione un usuario</option>
                    <?php foreach ($usuarios as $usuario): ?>
                        <option value="<?= $usuario['id']; ?>"><?= htmlspecialchars($usuario['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="proyecto_id">Selecciona el Proyecto:</label>
                <select name="proyecto_id" required>
                    <option value="">Seleccione un proyecto</option>
                    <?php foreach ($proyectos as $proyecto): ?>
                        <option value="<?= $proyecto['id']; ?>"><?= htmlspecialchars($proyecto['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" name="assignProject">Asignar Proyecto</button>
        </form>
        <footer>
            <button onclick="window.location.href='index.php'">Volver</button>
        </footer>
    </div>
</body>
</html>
