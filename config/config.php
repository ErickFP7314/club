<?php
// Configuración de la aplicación
define('SITE_NAME', 'Club de Ciencias');
define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/sitio');

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'club_ciencias');

// Directorios
$base_dir = __DIR__ . '/..';
define('UPLOAD_DIR', $base_dir . '/uploads');
define('PORTADAS_DIR', UPLOAD_DIR . '/portadas');

// Configuración de subida de archivos
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

// Configuración de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
session_start();

// Zona horaria
date_default_timezone_set('America/Mexico_City');

// Manejo de errores
if (defined('ENVIRONMENT')) {
    switch (ENVIRONMENT) {
        case 'development':
            error_reporting(-1);
            ini_set('display_errors', 1);
            break;
        case 'production':
            ini_set('display_errors', 0);
            error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
            break;
        default:
            header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
            echo 'El entorno de la aplicación no está configurado correctamente.';
            exit(1);
    }
}

// Función para generar URLs amigables
function url_amigable($string) {
    $string = trim($string);
    $string = str_replace(
        ['á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'],
        ['a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'],
        $string
    );
    $string = str_replace(
        ['é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'],
        ['e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'],
        $string
    );
    $string = str_replace(
        ['í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'],
        ['i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'],
        $string
    );
    $string = str_replace(
        ['ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'],
        ['o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'],
        $string
    );
    $string = str_replace(
        ['ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'],
        ['u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'],
        $string
    );
    $string = str_replace(
        ['ñ', 'Ñ', 'ç', 'Ç'],
        ['n', 'N', 'c', 'C'],
        $string
    );
    $string = str_replace(
        ["\\", "\0", "\n", "\r", "/", " ", ",", "?", ";", ":", ".", "!", "'", '"', "&", "(", ")", "[", "]", "{", "}", "~", "`", "#", "%", "^", "*", "+", "=", "|", "<", ">"],
        "-",
        $string
    );
    $string = preg_replace('/-+/', '-', $string);
    $string = trim($string, '-');
    return strtolower($string);
}

// Función para redireccionar
function redirect($url) {
    header('Location: ' . SITE_URL . '/' . ltrim($url, '/'));
    exit();
}

// Función para verificar si la petición es AJAX
function is_ajax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// Función para generar token CSRF
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Función para verificar token CSRF
function verify_csrf_token($token) {
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        die('Token CSRF no válido');
    }
    return true;
}

// Función para sanitizar datos de entrada
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Función para formatear fechas
function format_date($date, $format = 'd/m/Y H:i') {
    $date = new DateTime($date);
    return $date->format($format);
}

// Inicializar la configuración de la base de datos
require_once 'database.php';

// Inicializar autenticación
require_once '../includes/Auth.php';
$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);
?>
