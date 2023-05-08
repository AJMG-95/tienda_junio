<?php
session_start();

require '../../vendor/autoload.php';

$reclamacion = obtener_post('reclamacion');
$values = [];
$execute = [];

$pdo = conectar();

$reclamacion = ucfirst($reclamacion);

if (isset($reclamacion) && $reclamacion != '') {
    $values[] = ':reclamacion';
    $execute[':reclamacion'] = $reclamacion;
}

$values = !empty($values) ? 'VALUES (' . implode(' , ', $values) . ')'  : '';

try {
    $sent = $pdo->prepare("INSERT INTO reclamaciones (reclamacion)
                            $values");
    $sent->execute($execute);
    $_SESSION['exito'] = 'La reclamacion se ha insertado correctamente.';
} catch (\Throwable $th) {
    print_r($th);
    die();
    $_SESSION['error'] = 'Debe rellenar todos los campos';
}

volver_dashboard();