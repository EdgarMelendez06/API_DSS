<?php

header("Content-Type: application/json");
require 'jwt_utils.php';
require 'database.php';

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
$endpoint = array_shift($request);

// Rutas de la API
if ($method === 'POST' && $endpoint === 'login') {
    echo json_encode(login());
} elseif ($method === 'POST' && $endpoint === 'register') {
    echo json_encode(register());
} elseif ($method === 'GET' && $endpoint === 'datos') {
    echo json_encode(proteger('obtener_datos'));
} else {
    http_response_code(404);
    echo json_encode(["error" => "Endpoint no encontrado"]);
}

// Función para iniciar sesión
function login() {
    $json = json_decode(file_get_contents("php://input"), true);
    
    if (!$json || !isset($json['usuario']) || !isset($json['password'])) {
        return ["error" => "Datos incompletos"];
    }
    
    $pdo = conectar_db();
    $stmt = $pdo->prepare("SELECT password FROM usuarios WHERE usuario = ?");
    $stmt->execute([$json['usuario']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario && password_verify($json['password'], $usuario['password'])) {
        $token = generar_jwt(["usuario" => $json['usuario']]);
        return ["token" => $token];
    }
    
    return ["error" => "Credenciales inválidas"];
}

// Función para registrar usuarios
function register() {
    $json = json_decode(file_get_contents("php://input"), true);
    
    if (!$json || !isset($json['usuario']) || !isset($json['password'])) {
        return ["error" => "Datos incompletos"];
    }

    $pdo = conectar_db();
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = ?");
    $stmt->execute([$json['usuario']]);
    if ($stmt->fetch()) {
        return ["error" => "El usuario ya existe"];
    }
    
    $hashed_password = password_hash($json['password'], PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, password) VALUES (?, ?)");
    $stmt->execute([$json['usuario'], $hashed_password]);
    
    return ["mensaje" => "Usuario registrado con éxito"];
}

// Función para validar el acceso con JWT
function proteger($callback) {
    $headers = getallheaders();
    
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        return ["error" => "Token no proporcionado"];
    }
    
    $token = str_replace("Bearer ", "", $headers['Authorization']);
    $datos = validar_jwt($token);
    
    if (!$datos) {
        http_response_code(401);
        return ["error" => "Token inválido"];
    }
    
    return call_user_func($callback);
}

// Función para devolver datos protegidos
function obtener_datos() {
    return ["mensaje" => "Datos protegidos accedidos"];
}
