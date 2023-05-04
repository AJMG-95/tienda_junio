<?php
session_start();

require '../../vendor/autoload.php';

$etiqueta = obtener_post('etiqueta');
$values = [];
$execute = [];

$pdo = conectar();

$etiqueta = ucfirst($etiqueta);

if (isset($etiqueta) && $etiqueta != '') {
    $values[] = ':etiqueta';
    $execute[':etiqueta'] = $etiqueta;
}

$values = !empty($values) ? 'VALUES (' . implode(' , ', $values) . ')'  : '';

try {
    $sent = $pdo->prepare("INSERT INTO etiquetas (etiqueta)
                            $values");
    $sent->execute($execute);
    $_SESSION['exito'] = 'La etiqueta se ha insertado correctamente.';
} catch (\Throwable $th) {
    print_r($th);
    die();
    $_SESSION['error'] = 'Debe rellenar todos los campos';
}

volver_etiqueta();