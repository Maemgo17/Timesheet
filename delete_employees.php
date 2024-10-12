<?php
session_start(); // Iniciar sesión
include 'db.php'; // Incluir la conexión a la base de datos

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php"); // Redirigir a la página de inicio de sesión si no está autenticado
    exit;
}

// Obtener todos los empleados
$stmt = $pdo->query("SELECT * FROM empleados");
$empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Eliminar un empleado
if (isset($_POST['deleteEmployee'])) {
    $id = $_POST['id'];

    // Inicia una transacción
    $pdo->beginTransaction();

    try {
        // Eliminar horas trabajadas asociadas al empleado
        $deleteHoursStmt = $pdo->prepare("DELETE FROM horas_trabajadas WHERE empleado_id = :id");
        $deleteHoursStmt->execute(['id' => $id]);

        // Eliminar el empleado
        $deleteEmployeeStmt = $pdo->prepare("DELETE FROM empleados WHERE id = :id");
        $deleteEmployeeStmt->execute(['id' => $id]);

        // Confirmar la transacción
        $pdo->commit();
        
        // Redirigir para evitar duplicación al recargar la página
        header("Location: index.php");
        exit;
    } catch (Exception $e) {
        // Si ocurre un error, revertir la transacción
        $pdo->rollBack();
        echo "<script>alert('Error al eliminar el empleado: " . $e->getMessage() . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Empleados</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            flex-direction: column;
        }

        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 400px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h1 {
            text-align: center; /* Centrar el título */
            margin-bottom: 20px; /* Espacio debajo del título */
        }

        form {
            width: 100%;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            align-items: center; /* Centrar el contenido del formulario */
        }

        select {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
        }

        button {
            background: #D9534F;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
            width: 100%; /* Botón ocupa el 100% del ancho */
        }

        button:hover {
            background: #c9302c;
        }

        footer {
            text-align: center;
            margin-top: 20px;
            color: #777;
        }
    </style>
    <script>
        function confirmDelete() {
            return confirm('¿Está seguro que desea eliminar este empleado? Esta acción también eliminará todas las horas trabajadas registradas para este empleado y no se podrá deshacer.');
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Eliminar Empleados</h1>
        <form action="" method="POST" onsubmit="return confirmDelete();">
            <select name="id" required>
                <option value="">Selecciona un empleado...</option>
                <?php foreach ($empleados as $empleado): ?>
                    <option value="<?= $empleado['id'] ?>"><?= htmlspecialchars($empleado['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="deleteEmployee">Eliminar Empleado</button>
        </form>
        <footer>
            <button onclick="window.location.href='index.php'">Volver</button>
        </footer>
    </div>
</body>
</html>
