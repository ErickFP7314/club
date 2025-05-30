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

// Obtener el ID de la presentación a editar
$presentacion_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$presentacion = $presentaciones->getPresentacionPorId($presentacion_id);

// Verificar si la presentación existe y pertenece al usuario
if (!$presentacion || ($presentacion['usuario_id'] != $usuario['id'] && !$auth->hasRole('admin'))) {
    header('Location: index.php');
    exit();
}

// Procesar el formulario de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_STRING);
    $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);
    $url = filter_input(INPUT_POST, 'url', FILTER_SANITIZE_URL);
    $es_publica = isset($_POST['es_publica']) ? 1 : 0;
    $eliminar_imagen = isset($_POST['eliminar_imagen']) ? true : false;
    
    // Validar URL de presentación
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        $error = 'La URL de la presentación no es válida.';
    } else {
        $imagen_portada = $presentacion['imagen_portada'];
        
        // Procesar eliminación de imagen existente
        if ($eliminar_imagen && !empty($imagen_portada)) {
            $ruta_imagen = __DIR__ . '/..' . $imagen_portada;
            if (file_exists($ruta_imagen)) {
                unlink($ruta_imagen);
            }
            $imagen_portada = '';
        }
        
        // Procesar nueva imagen de portada si se subió
        if (isset($_FILES['imagen_portada']) && $_FILES['imagen_portada']['error'] === UPLOAD_ERR_OK) {
            // Eliminar imagen anterior si existe
            if (!empty($imagen_portada)) {
                $ruta_imagen_anterior = __DIR__ . '/..' . $imagen_portada;
                if (file_exists($ruta_imagen_anterior)) {
                    unlink($ruta_imagen_anterior);
                }
            }
            
            // Subir nueva imagen
            $uploadDir = __DIR__ . '/../uploads/portadas/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileExtension = pathinfo($_FILES['imagen_portada']['name'], PATHINFO_EXTENSION);
            $fileName = uniqid() . '.' . $fileExtension;
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['imagen_portada']['tmp_name'], $targetPath)) {
                $imagen_portada = '/sitio/uploads/portadas/' . $fileName;
            } else {
                $error = 'Error al subir la nueva imagen de portada.';
            }
        }
        
        if (empty($error)) {
            // Actualizar la presentación en la base de datos
            if ($presentaciones->actualizarPresentacion(
                $presentacion_id,
                $titulo,
                $descripcion,
                $url,
                $imagen_portada,
                $es_publica
            )) {
                $success = 'Presentación actualizada exitosamente.';
                // Actualizar los datos de la presentación mostrada
                $presentacion = $presentaciones->getPresentacionPorId($presentacion_id);
            } else {
                $error = 'Error al actualizar la presentación en la base de datos.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Presentación - Club de Ciencias</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 2rem;
            padding-bottom: 2rem;
        }
        .edit-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        .preview-image {
            max-width: 200px;
            max-height: 150px;
            margin-top: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .btn-save {
            background-color: #041936;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-save:hover {
            background-color: #020f1f;
            color: white;
        }
        .form-control:focus {
            border-color: #041936;
            box-shadow: 0 0 0 0.2rem rgba(4, 25, 54, 0.25);
        }
        .custom-file-label::after {
            content: "Examinar";
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="edit-container">
                    <h2 class="mb-4"><i class="fas fa-edit mr-2"></i>Editar Presentación</h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($success); ?>
                            <a href="index.php" class="alert-link">Volver a mis presentaciones</a>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data" id="editForm">
                        <div class="form-group">
                            <label for="titulo">Título de la Presentación</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" required 
                                   value="<?php echo htmlspecialchars($presentacion['titulo']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required><?php 
                                echo htmlspecialchars($presentacion['descripcion']); 
                            ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="url">URL de la Presentación</label>
                            <input type="url" class="form-control" id="url" name="url" required
                                   placeholder="https://docs.google.com/presentation/..."
                                   value="<?php echo htmlspecialchars($presentacion['url']); ?>">
                            <small class="form-text text-muted">Pega el enlace de tu presentación (Google Slides, PowerPoint Online, etc.)</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Imagen de Portada Actual</label>
                            <?php if (!empty($presentacion['imagen_portada'])): ?>
                                <div class="mb-2">
                                    <img src="<?php echo htmlspecialchars($presentacion['imagen_portada']); ?>" class="preview-image img-fluid" id="currentImagePreview">
                                </div>
                                <div class="custom-control custom-checkbox mb-3">
                                    <input type="checkbox" class="custom-control-input" id="eliminar_imagen" name="eliminar_imagen">
                                    <label class="custom-control-label text-danger" for="eliminar_imagen">Eliminar imagen actual</label>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No hay imagen de portada</p>
                            <?php endif; ?>
                            
                            <label for="imagen_portada">Nueva Imagen de Portada (opcional)</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="imagen_portada" name="imagen_portada" accept="image/*">
                                <label class="custom-file-label" for="imagen_portada">Seleccionar archivo</label>
                            </div>
                            <img id="imagePreview" src="#" alt="Vista previa" class="preview-image img-fluid mt-2" style="display: none;">
                        </div>
                        
                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="es_publica" name="es_publica" value="1" 
                                <?php echo $presentacion['es_publica'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="es_publica">Hacer pública esta presentación</label>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-save">
                                <i class="fas fa-save mr-2"></i>Guardar Cambios
                            </button>
                            <a href="index.php" class="btn btn-outline-secondary ml-2">
                                <i class="fas fa-arrow-left mr-2"></i>Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        // Mostrar vista previa de la nueva imagen seleccionada
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                
                reader.onload = function(e) {
                    $('#imagePreview').attr('src', e.target.result);
                    $('#imagePreview').show();
                }
                
                reader.readAsDataURL(input.files[0]);
                
                // Mostrar el nombre del archivo seleccionado
                var fileName = input.files[0].name;
                $(input).next('.custom-file-label').html(fileName);
                
                // Desmarcar la opción de eliminar imagen si se selecciona una nueva
                if ($(input).attr('id') === 'imagen_portada') {
                    $('#eliminar_imagen').prop('checked', false);
                }
            }
        }
        
        // Mostrar vista previa cuando se selecciona una imagen
        $("#imagen_portada").change(function() {
            readURL(this);
        });
        
        // Manejar la opción de eliminar imagen
        $('#eliminar_imagen').change(function() {
            if ($(this).is(':checked')) {
                $('#currentImagePreview').hide();
                $('#imagen_portada').val('').next('.custom-file-label').html('Seleccionar archivo');
                $('#imagePreview').hide();
            } else {
                $('#currentImagePreview').show();
            }
        });
        
        // Validar el formulario antes de enviar
        $("#editForm").on("submit", function() {
            var url = $("#url").val();
            if (!isValidUrl(url)) {
                alert("Por favor, ingresa una URL válida para la presentación.");
                return false;
            }
            return true;
        });
        
        function isValidUrl(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        }
    </script>
</body>
</html>
