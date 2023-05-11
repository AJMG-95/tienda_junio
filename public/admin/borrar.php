<?php
session_start();

require '../../vendor/autoload.php';


$id = obtener_post('id');

if (!isset($id)) {
    return volver_admin();
}

$pdo = conectar();
try {
    $sent = $pdo->prepare("DELETE FROM articulos_etiquetas WHERE articulo_id = :id");
    $sent->execute([':id' => $id]);
} catch (\Throwable $th) {
    //throw $th;
}

try {
    $sent = $pdo->prepare("DELETE FROM valoraciones WHERE articulo_id = :id");
    $sent->execute([':id' => $id]);
} catch (\Throwable $th) {
    //throw $th;
}

$sent = $pdo->prepare("DELETE FROM articulos WHERE id = :id");
$sent->execute([':id' => $id]);

$_SESSION['exito'] = 'El artículo se ha borrado correctamente.';

volver_admin();