<?php

use App\Tablas\Articulo;

session_start();

require '../vendor/autoload.php';

$categoria = obtener_get('categoria');
$nombre = obtener_get('nombre');
$valoracion = obtener_get('valoracion');

try {
    $id = obtener_get('id');

    if ($id === null) {
        volver();
    }

    $articulo = Articulo::obtener($id);

    if ($articulo === null) {
        volver();
    }

    $carrito = unserialize(carrito());

  
    
    
    $_SESSION['carrito'] = serialize($carrito);

    $params = "";
    if ($nombre !== null) {
        $params .= '&nombre=' . hh($nombre);
    }

        
    if ($categoria !== null) {
        $params .= '&categoria=' . hh($categoria);
    }



    header("Location: /index.php?$params");
} catch (ValueError $e) {
    // TODO: mostrar mensaje de error en un Alert
    volver();
}
