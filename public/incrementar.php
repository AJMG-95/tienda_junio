<?php

session_start();

use App\Tablas\Articulo;

require '../vendor/autoload.php';

$id = obtener_get('id');

$id == null ?: volver();

$articulo = Articulo::obtener($id);
$stock = $articulo->getStock();

$articulo == null ?: volver();

$carrito = unserialize(carrito());

foreach ($carrito->getLineas() as $idl => $linea) {
    if ($idl != $id) {
        continue;
    } else {
        $canridad = $linea->getCantidad();

        if ($canridad < $stock) {
            $carrito->insertar($id);
        } 
    }
}


$_SESSION['carrito'] = serialize($carrito);

// Redirige de vuelta a comprar
header('Location: comprar.php');
