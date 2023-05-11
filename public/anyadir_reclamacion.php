<?php
session_start();

require '../vendor/autoload.php';

$reclamacion = obtener_post('reclamacion');
$usuario_id = obtener_post('usuario_id');
$factura_id = obtener_post('factura_id');
$values = [];
$execute = [];

$pdo = conectar();

$reclamacion = ucfirst($reclamacion);

if (isset($reclamacion) && $reclamacion != '') {
    $values[] = ':reclamacion';
    $execute[':reclamacion'] = $reclamacion;
}

if (isset($usuario_id) && $usuario_id != '') { 
    $values[] = ':usuario_id';
    $execute[':usuario_id'] = $usuario_id;
}

if (isset($factura_id) && $factura_id != '') { 
    $values[] = ':factura_id';
    $execute[':factura_id'] = $factura_id;
}

$values = !empty($values) ? 'VALUES (' . implode(' , ', $values) . ')'  : '';

try {
    $sent = $pdo->prepare("INSERT INTO reclamaciones (reclamacion, usuario_id, factura_id) $values");
    $sent->execute($execute);
    $_SESSION['exito'] = 'La reclamacion se ha insertado correctamente.';
} catch (\Throwable $th) {
    print_r($th);
    die();
    $_SESSION['error'] = 'Debe rellenar todos los campos';
}

volver_dashboard();
