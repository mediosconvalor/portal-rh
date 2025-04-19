<?php
session_start();

// Obtener datos del formulario
$email = $_POST['correo'] ?? '';
$password = $_POST['contrasena'] ?? '';
$ubicacion = $_POST['ubicacion'] ?? 'No disponible';

// Preparar JSON para enviar a GAS
$datos = json_encode([
    "action" => "login",
    "email" => $email,
    "password" => $password,
    "ubicacion" => $ubicacion
]);

// URL del nuevo script de GAS
require_once __DIR__ . '/../config/urls.php';
$gas_url = GAS_URL;

// Llamada POST a GAS
$ch = curl_init($gas_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $datos);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$response = curl_exec($ch);
curl_close($ch);

// Procesar respuesta
$data = json_decode($response, true);

if ($data && isset($data['success']) && $data['success']) {
    $_SESSION['usuario'] = $data['usuario'];
    $_SESSION['rol'] = obtenerRolDesdeUsuario($data['usuario']);

    // Crear token persistente por 30 días
    $token = base64_encode(random_bytes(32));
    setcookie('login_token', $token, time() + (86400 * 30), "/");

    // Guardar token en archivo .json
    $token_path = __DIR__ . "/../tokens/{$token}.json";
    file_put_contents($token_path, json_encode($data['usuario']));

    // Redirigir al dashboard
    header("Location: /portal-rh/dashboard/usuario/index.php");
    exit();
} else {
    // En caso de error, redirigir con mensaje
    $_SESSION['login_error'] = "Correo o contraseña inválidos.";
    header("Location: /portal-rh/index.php");    
    exit();
}

// Función para asignar rol según permisos
function obtenerRolDesdeUsuario($usuario) {
    if (!empty($usuario['administrador'])) return 'admin';
    if (!empty($usuario['moderador'])) return 'mod';
    if (!empty($usuario['empleado'])) return 'empleado';
    return 'desconocido';
}