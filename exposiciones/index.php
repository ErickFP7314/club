<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/Presentaciones.php';

session_start();

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);
$presentaciones = new Presentaciones($db);

// Verificar si el usuario está autenticado
$usuario_actual = $auth->getCurrentUser();
$es_miembro = $auth->isLoggedIn() && $auth->hasRole('miembro');
$es_admin = $auth->isLoggedIn() && $auth->hasRole('admin');

// Obtener las presentaciones públicas
$presentaciones_publicas = $presentaciones->getPresentacionesPublicas();

// Obtener las presentaciones del usuario si está autenticado como miembro
$mis_presentaciones = [];
if ($es_miembro) {
    $mis_presentaciones = $presentaciones->getPresentacionesPorUsuario($usuario_actual['id']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exposiciones - Club de Ciencias</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 2rem;
            padding-bottom: 2rem;
        }
        .presentation-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 20px;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .presentation-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        .card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .card-text {
            flex: 1;
        }
        .badge-custom {
            background-color: #041936;
            color: white;
        }
        .btn-custom {
            background-color: #041936;
            color: white;
            border: none;
        }
        .btn-custom:hover {
            background-color: #020f1f;
            color: white;
        }
        .nav-tabs .nav-link.active {
            color: #041936;
            font-weight: bold;
            border-bottom: 3px solid #041936;
        }
        .nav-tabs .nav-link {
            color: #6c757d;
        }
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #dee2e6;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-chalkboard-teacher mr-2"></i>Exposiciones</h1>
            <?php if ($es_miembro): ?>
                <a href="upload.php" class="btn btn-custom">
                    <i class="fas fa-plus mr-2"></i>Nueva Presentación
                </a>
            <?php endif; ?>
        </div>

        <ul class="nav nav-tabs mb-4" id="presentacionesTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="publicas-tab" data-toggle="tab" href="#publicas" role="tab">
                    <i class="fas fa-globe-americas mr-1"></i> Públicas
                    <span class="badge badge-secondary"><?php echo count($presentaciones_publicas); ?></span>
                </a>
            </li>
            <?php if ($es_miembro): ?>
            <li class="nav-item">
                <a class="nav-link" id="mis-presentaciones-tab" data-toggle="tab" href="#mis-presentaciones" role="tab">
                    <i class="fas fa-user mr-1"></i> Mis Presentaciones
                    <span class="badge badge-secondary"><?php echo count($mis_presentaciones); ?></span>
                </a>
            </li>
            <?php endif; ?>
        </ul>

        <div class="tab-content" id="presentacionesTabContent">
            <!-- Pestaña de Presentaciones Públicas -->
            <div class="tab-pane fade show active" id="publicas" role="tabpanel">
                <?php if (empty($presentaciones_publicas)): ?>
                    <div class="empty-state">
                        <i class="fas fa-folder-open"></i>
                        <h3>No hay presentaciones públicas</h3>
                        <p class="lead">Aún no se han compartido presentaciones públicas.</p>
                        <?php if ($es_miembro): ?>
                            <a href="upload.php" class="btn btn-custom">
                                <i class="fas fa-upload mr-2"></i>Comparte la tuya
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($presentaciones_publicas as $presentacion): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card presentation-card">
                                    <?php if (!empty($presentacion['imagen_portada'])): ?>
                                        <img src="<?php echo htmlspecialchars($presentacion['imagen_portada']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($presentacion['titulo']); ?>">
                                    <?php else: ?>
                                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                            <i class="fas fa-file-powerpoint fa-5x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($presentacion['titulo']); ?></h5>
                                        <p class="card-text text-muted">
                                            <small>Por <?php echo htmlspecialchars($presentacion['autor'] ?? 'Anónimo'); ?></small>
                                        </p>
                                        <p class="card-text">
                                            <?php echo nl2br(htmlspecialchars(mb_strimwidth($presentacion['descripcion'], 0, 100, '...'))); ?>
                                        </p>
                                    </div>
                                    <div class="card-footer bg-white border-top-0">
                                        <a href="<?php echo htmlspecialchars($presentacion['url']); ?>" target="_blank" class="btn btn-custom btn-block">
                                            <i class="fas fa-external-link-alt mr-2"></i>Ver Presentación
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($es_miembro): ?>
            <!-- Pestaña de Mis Presentaciones -->
            <div class="tab-pane fade" id="mis-presentaciones" role="tabpanel">
                <?php if (empty($mis_presentaciones)): ?>
                    <div class="empty-state">
                        <i class="fas fa-folder-open"></i>
                        <h3>No tienes presentaciones</h3>
                        <p class="lead">Comienza compartiendo tu primera presentación.</p>
                        <a href="upload.php" class="btn btn-custom">
                            <i class="fas fa-upload mr-2"></i>Subir Presentación
                        </a>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($mis_presentaciones as $presentacion): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card presentation-card">
                                    <?php if (!empty($presentacion['imagen_portada'])): ?>
                                        <img src="<?php echo htmlspecialchars($presentacion['imagen_portada']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($presentacion['titulo']); ?>">
                                    <?php else: ?>
                                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                            <i class="fas fa-file-powerpoint fa-5x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <h5 class="card-title mb-1"><?php echo htmlspecialchars($presentacion['titulo']); ?></h5>
                                            <span class="badge badge-<?php echo $presentacion['es_publica'] ? 'success' : 'secondary'; ?> badge-pill">
                                                <?php echo $presentacion['es_publica'] ? 'Pública' : 'Privada'; ?>
                                            </span>
                                        </div>
                                        <p class="card-text text-muted">
                                            <small>Subida el <?php echo date('d/m/Y', strtotime($presentacion['fecha_subida'])); ?></small>
                                        </p>
                                        <p class="card-text">
                                            <?php echo nl2br(htmlspecialchars(mb_strimwidth($presentacion['descripcion'], 0, 100, '...'))); ?>
                                        </p>
                                    </div>
                                    <div class="card-footer bg-white border-top-0">
                                        <div class="btn-group w-100">
                                            <a href="<?php echo htmlspecialchars($presentacion['url']); ?>" target="_blank" class="btn btn-outline-secondary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="editar.php?id=<?php echo $presentacion['id']; ?>" class="btn btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="eliminar.php" class="d-inline" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta presentación?');">
                                                <input type="hidden" name="id" value="<?php echo $presentacion['id']; ?>">
                                                <button type="submit" class="btn btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Activar las pestañas de Bootstrap
        $(document).ready(function() {
            // Si hay un hash en la URL, activar la pestaña correspondiente
            var hash = window.location.hash;
            if (hash) {
                $('.nav-tabs a[href="' + hash + '"]').tab('show');
            }
            
            // Manejar el cambio de pestañas
            $('.nav-tabs a').on('click', function (e) {
                e.preventDefault();
                $(this).tab('show');
                // Actualizar el hash en la URL sin recargar la página
                window.history.pushState(null, null, $(this).attr('href'));
            });
            
            // Manejar el botón de retroceso/avance del navegador
            window.addEventListener('popstate', function() {
                var activeTab = $('.nav-tabs a[href="' + location.hash + '"]');
                if (activeTab.length) {
                    activeTab.tab('show');
                } else {
                    $('.nav-tabs a:first').tab('show');
                }
            });
        });
    </script>
</body>
</html>
