<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Auth.php';

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
    $apellido = filter_input(INPUT_POST, 'apellido', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validaciones
    if (empty($nombre) || empty($apellido) || empty($email) || empty($password)) {
        $error = 'Todos los campos son obligatorios.';
    } elseif ($password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (strlen($password) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';
    } else {
        // Registrar al usuario
        if ($auth->registrar($nombre, $apellido, $email, $password, 'miembro')) {
            $success = '¡Registro exitoso! Ahora puedes iniciar sesión.';
            // Redirigir después de 2 segundos
            header('Refresh: 2; URL=login.php');
        } else {
            $error = 'Error al registrar el usuario. El correo electrónico ya está en uso.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Club de Ciencias</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            background: url('../imganes club/fondo1.gif') no-repeat center center fixed;
            background-size: cover;
        }
        .register-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 500px;
            width: 100%;
            margin: 20px auto;
        }
        .register-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .register-logo img {
            max-width: 150px;
            margin-bottom: 15px;
        }
        .form-control:focus {
            border-color: #041936;
            box-shadow: 0 0 0 0.2rem rgba(4, 25, 54, 0.25);
        }
        .btn-register {
            background-color: #041936;
            border: none;
            padding: 10px;
            font-weight: 600;
        }
        .btn-register:hover {
            background-color: #020f1f;
        }
        .password-hint {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <div class="register-logo">
                <img src="../imganes club/logo.png" alt="Logo Club de Ciencias" class="img-fluid">
                <h3>Crear Cuenta</h3>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php else: ?>
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="nombre">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required 
                                   value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="apellido">Apellido</label>
                            <input type="text" class="form-control" id="apellido" name="apellido" required
                                   value="<?php echo isset($_POST['apellido']) ? htmlspecialchars($_POST['apellido']) : ''; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email">Correo Electrónico</label>
                        <input type="email" class="form-control" id="email" name="email" required
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <small class="password-hint">Mínimo 8 caracteres</small>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirmar Contraseña</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-register">Registrarse</button>
                </form>
            <?php endif; ?>
            
            <div class="text-center mt-3">
                <a href="login.php" class="text-muted">¿Ya tienes una cuenta? Inicia Sesión</a>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
