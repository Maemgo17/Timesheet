<?php
session_start(); // Iniciar sesión
include 'db.php'; // Incluir la conexión a la base de datos

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php"); // Redirigir a la página de inicio de sesión si no está autenticado
    exit;
}

// Obtener la información del usuario actual
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt->execute(['id' => $_SESSION['usuario_id']]);
$usuarioActual = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar si el usuario es un administrador
if ($usuarioActual['rol'] !== 'admin') {
    echo "<script>alert('No tiene permiso para asignar proyectos.'); window.location.href='settings.php';</script>";
    exit;
}

// Obtener todos los proyectos
$stmt = $pdo->query("SELECT * FROM proyectos");
$proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener todos los usuarios que no son administradores
$stmt = $pdo->query("SELECT * FROM usuarios WHERE rol != 'admin'");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Asignar proyecto a usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarioId = $_POST['usuario_id'];
    $proyectoId = $_POST['proyecto_id'];

    // Verificar si el proyecto ya está asignado al usuario
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM proyectos_asignados WHERE usuario_id = :usuario_id AND proyecto_id = :proyecto_id");
    $stmt->execute(['usuario_id' => $usuarioId, 'proyecto_id' => $proyectoId]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        // Si ya existe la asignación, mostrar un mensaje de error
        echo "<script>alert('El proyecto ya está asignado a este usuario.'); window.location.href='assign_projects.php';</script>";
        exit;
    } else {
        // Insertar en la tabla de proyectos asignados
        $stmt = $pdo->prepare("INSERT INTO proyectos_asignados (usuario_id, proyecto_id) VALUES (:usuario_id, :proyecto_id)");
        $stmt->execute(['usuario_id' => $usuarioId, 'proyecto_id' => $proyectoId]);

        // Redirigir a settings.php después de la asignación exitosa
        echo "<script>alert('Proyecto asignado exitosamente.'); window.location.href='settings.php';</script>";
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
            max-width: 400px; /* Ancho máximo del contenedor */
            display: flex;
            flex-direction: column;
            align-items: center; /* Centrar elementos dentro del contenedor */
        }

        h1 {
            color: #333;
            margin-bottom: 20px; /* Espacio debajo del título */
            text-align: center; /* Centrar el texto del título */
        }

        form {
            width: 100%; /* El formulario ocupa todo el ancho del contenedor */
            display: flex;
            flex-direction: column; /* Organiza los elementos verticalmente */
            align-items: center; /* Centrar el contenido del formulario */
        }

        select {
            padding: 12px; /* Más espacio para mayor comodidad */
            margin-bottom: 15px; /* Espaciado entre los campos */
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%; /* El select ocupa todo el ancho del contenedor */
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
            width: 50%; /* Ajustado para ser más estrecho */
            margin: 0 auto; /* Espaciado y centrado automático */
        }

        button:hover {
            background: #45a049; /* Color más oscuro al pasar el mouse */
        }

        footer {
            text-align: center;
            margin-top: 20px;
            color: #777;
            width: 100%; /* Asegurar que el footer ocupe el 100% */
        }

        footer button {
            background: #007BFF; /* Color azul para el botón de volver */
            padding: 12px; /* Igualar el padding con el botón de eliminar */
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
            transition: background 0.3s;
            width: 50%; /* Ajustado para ser más estrecho */
            margin: 0 auto; /* Espaciado y centrado automático */
        }

        footer button:hover {
            background: #0056b3; /* Color más oscuro al pasar el mouse */
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
            <button onclick="window.location.href='settings.php'">Volver</button> <!-- Botón Volver -->
        </footer>
    </div>
</body>
</html>
