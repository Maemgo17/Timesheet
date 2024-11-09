<?php
session_start(); 
include 'db.php'; 

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->query("SELECT * FROM usuarios");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['deleteUser'])) {
    $id = $_POST['id'];
    $currentUserId = $_SESSION['usuario_id']; 
    if ($id == $currentUserId) {
        echo "<script>alert('No puedes eliminar tu propio usuario.');</script>";
    } else {
        $pdo->beginTransaction();

        try {
            $deleteAssignmentsStmt = $pdo->prepare("DELETE FROM proyectos_asignados WHERE usuario_id = :id");
            $deleteAssignmentsStmt->execute(['id' => $id]);

            $deleteHoursStmt = $pdo->prepare("DELETE FROM horas_trabajadas WHERE usuario_id = :id");
            $deleteHoursStmt->execute(['id' => $id]);

            $deleteUserStmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
            $deleteUserStmt->execute(['id' => $id]);

            $pdo->commit();

            if ($id == $currentUserId) {
                header("Location: login.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            echo "<script>alert('Error al eliminar el usuario: " . htmlspecialchars($e->getMessage()) . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Usuarios</title>
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
        function confirmDelete() {
            return confirm('¿Está seguro que desea eliminar este usuario? Esta acción también eliminará todas las horas trabajadas registradas para este usuario y no se podrá deshacer.');
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Eliminar Usuario</h1>
        <form action="" method="POST" onsubmit="return confirmDelete();">
            <select name="id" required>
                <option value="">Selecciona un usuario...</option>
                <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?= $usuario['id'] ?>"><?= htmlspecialchars($usuario['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="deleteUser">Eliminar Usuario</button>
        </form>
        <footer>
            <button onclick="window.location.href='index.php'">Volver</button>
        </footer>
    </div>
</body>
</html>
