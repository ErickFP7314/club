<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Auth.php';

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

// Cerrar la sesión
$auth->logout();

// Redirigir a la página de inicio
header('Location: /sitio/index.php');
exit();
?>
