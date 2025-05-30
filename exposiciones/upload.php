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

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_STRING);
    $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);
    $url = filter_input(INPUT_POST, 'url', FILTER_SANITIZE_URL);
    $es_publica = isset($_POST['es_publica']) ? 1 : 0;
    $usuario = $auth->getCurrentUser();
    
    // Validar URL de presentación (ejemplo: Google Slides, PowerPoint Online, etc.)
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        $error = 'La URL de la presentación no es válida.';
    } else {
        // Procesar imagen de portada si se subió
        $imagen_portada = '';
        if (isset($_FILES['imagen_portada']) && $_FILES['imagen_portada']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/portadas/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileExtension = pathinfo($_FILES['imagen_portada']['name'], PATHINFO_EXTENSION);
            $fileName = uniqid() . '.' . $fileExtension;
            $targetPath = $uploadDir . $fileName;
            
            // Mover el archivo subido
            if (move_uploaded_file($_FILES['imagen_portada']['tmp_name'], $targetPath)) {
                $imagen_portada = '/sitio/uploads/portadas/' . $fileName;
            } else {
                $error = 'Error al subir la imagen de portada.';
            }
        }
        
        if (empty($error)) {
            // Guardar la presentación en la base de datos
            $presentacionId = $presentaciones->subirPresentacion(
                $titulo,
                $descripcion,
                $url,
                $imagen_portada,
                $usuario['id'],
                $es_publica
            );
            
            if ($presentacionId) {
                $success = 'Presentación subida exitosamente.';
                // Limpiar el formulario
                $_POST = array();
            } else {
                $error = 'Error al guardar la presentación en la base de datos.';
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
    <title>Subir Presentación - Club de Ciencias</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 2rem;
            padding-bottom: 2rem;
        }
        .upload-container {
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
            display: none;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .btn-upload {
            background-color: #041936;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-upload:hover {
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
                <div class="upload-container">
                    <h2 class="mb-4"><i class="fas fa-upload mr-2"></i>Subir Nueva Presentación</h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($success); ?>
                            <a href="/sitio/exposiciones.php" class="alert-link">Volver a las presentaciones</a>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data" id="uploadForm">
                        <div class="form-group">
                            <label for="titulo">Título de la Presentación</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" required 
                                   value="<?php echo isset($_POST['titulo']) ? htmlspecialchars($_POST['titulo']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required><?php 
                                echo isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : ''; 
                            ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="url">URL de la Presentación</label>
                            <input type="url" class="form-control" id="url" name="url" required
                                   placeholder="https://docs.google.com/presentation/..."
                                   value="<?php echo isset($_POST['url']) ? htmlspecialchars($_POST['url']) : ''; ?>">
                            <small class="form-text text-muted">Pega el enlace de tu presentación (Google Slides, PowerPoint Online, etc.)</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Imagen de Portada</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="imagen_portada" name="imagen_portada" accept="image/*">
                                <label class="custom-file-label" for="imagen_portada">Seleccionar archivo</label>
                            </div>
                            <img id="imagePreview" src="#" alt="Vista previa" class="preview-image img-fluid mt-2">
                        </div>
                        
                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="es_publica" name="es_publica" checked>
                            <label class="form-check-label" for="es_publica">Hacer pública esta presentación</label>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-upload">
                                <i class="fas fa-upload mr-2"></i>Subir Presentación
                            </button>
                            <a href="/sitio/exposiciones.php" class="btn btn-outline-secondary ml-2">
                                <i class="fas fa-arrow-left mr-2"></i>Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        // Mostrar vista previa de la imagen seleccionada
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
            }
        }
        
        // Mostrar vista previa cuando se selecciona una imagen
        $("#imagen_portada").change(function() {
            readURL(this);
        });
        
        // Validar el formulario antes de enviar
        $("#uploadForm").on("submit", function() {
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
