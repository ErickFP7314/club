<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/Presentaciones.php';

session_start();

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);
$presentaciones = new Presentaciones($db);

// Verificar si el usuario está autenticado y es miembro
if (!$auth->isLoggedIn() || !$auth->hasRole('miembro')) {
    header('Location: /sitio/auth/login.php');
    exit();
}

$usuario = $auth->getCurrentUser();
$error = '';
$success = '';

// Obtener el ID de la presentación a eliminar
$presentacion_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$presentacion = $presentaciones->getPresentacionPorId($presentacion_id);

// Verificar si la presentación existe y pertenece al usuario (o es admin)
if (!$presentacion || ($presentacion['usuario_id'] != $usuario['id'] && !$auth->hasRole('admin'))) {
    header('Location: index.php');
    exit();
}

// Procesar la eliminación de la presentación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Eliminar la imagen de portada si existe
    if (!empty($presentacion['imagen_portada'])) {
        $ruta_imagen = __DIR__ . '/..' . $presentacion['imagen_portada'];
        if (file_exists($ruta_imagen)) {
            unlink($ruta_imagen);
        }
    }
    
    // Eliminar la presentación de la base de datos
    if ($presentaciones->eliminarPresentacion($presentacion_id)) {
        $success = 'Presentación eliminada correctamente.';
        // Redirigir después de 2 segundos
        header('Refresh: 2; URL=index.php');
    } else {
        $error = 'Error al eliminar la presentación de la base de datos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Presentación - Club de Ciencias</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 2rem;
            padding-bottom: 2rem;
        }
        .delete-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 600px;
            margin: 0 auto;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-delete:hover {
            background-color: #c82333;
            color: white;
        }
        .presentation-preview {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            background-color: #f8f9fa;
        }
        .presentation-preview img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="delete-container text-center">
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle fa-2x mb-3"></i>
                            <h4 class="alert-heading">¡Eliminada!</h4>
                            <p><?php echo htmlspecialchars($success); ?></p>
                            <hr>
                            <p class="mb-0">Redirigiendo a la lista de presentaciones...</p>
                        </div>
                        <a href="index.php" class="btn btn-primary mt-3">
                            <i class="fas fa-arrow-left mr-2"></i>Volver ahora
                        </a>
                    <?php else: ?>
                        <i class="fas fa-exclamation-triangle fa-4x text-warning mb-4"></i>
                        <h2>¿Estás seguro de que deseas eliminar esta presentación?</h2>
                        <p class="lead">Esta acción no se puede deshacer. Se eliminará la presentación y todos sus datos asociados.</p>
                        
                        <div class="presentation-preview text-left">
                            <h4><?php echo htmlspecialchars($presentacion['titulo']); ?></h4>
                            <?php if (!empty($presentacion['imagen_portada'])): ?>
                                <img src="<?php echo htmlspecialchars($presentacion['imagen_portada']); ?>" alt="Portada" class="img-fluid">
                            <?php endif; ?>
                            <p class="text-muted">
                                <small>Creada el <?php echo date('d/m/Y', strtotime($presentacion['fecha_subida'])); ?></small>
                            </p>
                        </div>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" class="mt-4">
                            <input type="hidden" name="id" value="<?php echo $presentacion_id; ?>">
                            <button type="submit" class="btn btn-delete">
                                <i class="fas fa-trash-alt mr-2"></i>Sí, eliminar permanentemente
                            </button>
                            <a href="index.php" class="btn btn-outline-secondary ml-2">
                                <i class="fas fa-times mr-2"></i>Cancelar
                            </a>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
