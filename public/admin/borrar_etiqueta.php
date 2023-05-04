<?php
session_start();

require '../../vendor/autoload.php';

$id = obtener_post('id');

if (!isset($id)) {
    return volver_etiqueta();
}



$pdo = conectar();

$sent = $pdo->prepare("SELECT a.id FROM articulos a
                                    JOIN articulos_etiquetas ae ON (ae.articulo_id = a.id)
                                    WHERE ae.etiqueta_id = :id ");
$sent->execute([':id' => $id]);
$res = $sent->fetchColumn();

if ($res < 1) {
    $sent = $pdo->prepare("DELETE FROM etiquetas WHERE id = :id");
    $sent->execute([':id' => $id]);
    $_SESSION['exito'] = 'LA etiqueta se ha borrado correctamente.';
} else {
    $_SESSION['error'] = 'La etiqueta se encuentra asociada a un artículo.';
}

volver_etiqueta();