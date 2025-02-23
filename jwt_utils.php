<?php

function generar_jwt($data) {
    $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload = base64_encode(json_encode($data));
    
    $secret = 'secreto123';
    $signature = base64_encode(hash_hmac('sha256', "$header.$payload", $secret, true));
    return "$header.$payload.$signature";
}

function validar_jwt($token) {
    $secret = 'secreto123';
    $parts = explode('.', $token);
    if (count($parts) !== 3) return false;

    $signature = base64_encode(hash_hmac('sha256', "$parts[0].$parts[1]", $secret, true));
    return ($signature === $parts[2]) ? json_decode(base64_decode($parts[1]), true) : false;
}
