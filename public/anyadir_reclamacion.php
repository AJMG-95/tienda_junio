<?php
session_start();

require '../vendor/autoload.php';

$reclamacion_txt = obtener_post('reclamacion_txt');
$reclamacion_img = obtener_post('reclamacion_img');
$reclamacion_img_temp = $_FILES['reclamacion_img']['tmp_name'];
$reclamacion_img_nombre = $_FILES['reclamacion_img']['name'];
$usuario_id = obtener_post('usuario_id');
$factura_id = obtener_post('factura_id');

print_r($reclamacion_img_nombre);
die();

$img_extension = pathinfo($reclamacion_img_nombre, PATHINFO_EXTENSION);
$extensiones_Validas = ['jpeg', 'jpg', 'png'];

if (in_array($img_extension, $extensiones_Validas)) {
    $ruta_destino = '../src/Imagenes/' . $reclamacion_img_nombre;

    move_uploaded_file($reclamacion_img_temp, $ruta_destino);
}

$values = [];
$execute = [];

$pdo = conectar();

$reclamacion_txt = ucfirst($reclamacion_txt);

if (isset($reclamacion_txt) && $reclamacion_txt != '') {
    $values[] = ':reclamacion';
    $execute[':reclamacion'] = $reclamacion_txt;
}

if (isset($reclamacion_img) && $reclamacion_img != '') {
    $values[] = ':imagen';
    $execute[':imagen'] = $reclamacion_img;
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
    $sent = $pdo->prepare("INSERT INTO reclamaciones (reclamacion, imagen, usuario_id, factura_id) $values");
    $sent->execute($execute);
    $_SESSION['exito'] = 'La reclamacion se ha insertado correctamente.';
} catch (\Throwable $th) {
    $_SESSION['error'] = 'Debe rellenar todos los campos';
    print_r($th);
    die();
}

volver_dashboard();
