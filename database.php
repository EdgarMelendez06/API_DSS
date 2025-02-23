<?php

function conectar_db() {
    $host = "db";
    $usuario = 'root';
    $password = '';
    $dbname = 'api_db';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $usuario, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die(json_encode(["error" => "Error de conexión: " . $e->getMessage()]));
    }
}
