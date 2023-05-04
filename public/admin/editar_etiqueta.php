<?php
session_start();

require '../../vendor/autoload.php';

$id = obtener_post('id');
$etiqueta_nombre = obtener_post('etiqueta');
$set = [];
$execute = [];


$pdo = conectar();

if (!isset($id)) {
    return volver_etiqueta();
}

$etiqueta_nombre = ucfirst($etiqueta_nombre);

// Toma los valores actuales del artículo
$sent = $pdo->prepare("SELECT * FROM etiquetas WHERE id = :id");
$sent->execute([':id' => $id]);
$anterior = $sent->fetch(PDO::FETCH_ASSOC);

if (isset($id)) {
    $execute[':id'] = $id;
} else {
    $execute[':id'] = $anterior['id'];
}

if (isset($etiqueta_nombre) && $etiqueta_nombre != '') {
    $set[] = 'etiqueta = :etiqueta';
    $execute[':etiqueta'] = $etiqueta_nombre;
} else {
    $set[] = 'etiqueta = :etiqueta';
    $execute[':etiqueta'] = $anterior['etiqueta'];
}

$set = !empty($set) ? 'SET ' . implode(', ', $set) : '';
/* print_r($set);
die(); */

try {
    if ($set != '') {
        $sent = $pdo->prepare("UPDATE etiquetas
                                $set
                                WHERE  id = :id");
        $sent->execute($execute);
        $_SESSION['exito'] = 'La etiqueta se ha Modificado correctamente.';
    } else {
        $_SESSION['error'] = 'Debe rellenar el formulario para modificar la categoría';
    }
} catch (\Throwable $th) {
    print_r($th);
}


volver_etiqueta();