<?php
use App\Tablas\Articulo;

session_start();

require '../vendor/autoload.php';


try {
    $id = obtener_get('id');
    
    if ($id === null) {
        return volver();
    }
    
    $articulo = Articulo::obtener($id);
    
    if ($articulo === null) {
        return volver();
    }

    if ($articulo->getStock() <= 0) {
        $_SESSION['error'] = 'No hay existencias suficientes.';
        volver();
    }
    
    $carrito = unserialize(carrito());

    // Impide insertar en el carrito más articículos que los que hay en stock
    $stock = $articulo->getStock();
    $lineas = $carrito->getLineas();
    $cantidad = empty($lineas) ? 0 : $lineas[$id]->getCantidad();

    if ($stock > $cantidad) {
        $carrito->insertar($id);
    }
    
    $_SESSION['carrito'] = serialize($carrito);
} catch (ValueError $e) {
    // TODO: mostrar mensaje de error en un Alert
}

volver();
