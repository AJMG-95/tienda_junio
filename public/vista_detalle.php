<?php

use App\Tablas\Articulo;
use App\Tablas\Etiqueta;
use App\Tablas\Usuario;
use ValueError;

session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/css/output.css" rel="stylesheet">
    <title>Vista Detalle Producto</title>
</head>

<body>
    <?php
    require '../vendor/autoload.php';

    $pdo = conectar();

    $producto_id = obtener_get('id');
    $usuario_id = obtener_get('usuario');

    $sent = $pdo->prepare("SELECT a.* FROM articulos a WHERE a.id = :id");
    $sent->execute(['id' => $producto_id]);
    $producto = $sent->fetch(PDO::FETCH_ASSOC);

    $producto = new Articulo($producto); // Convertir el array en objeto Articulo

    $sent = $pdo->prepare("SELECT u.* FROM usuarios u WHERE u.id = :id");
    $sent->execute(['id' => $usuario_id]);
    $usuario = $sent->fetch(PDO::FETCH_ASSOC);

    $usuario = new Usuario($usuario);

    ?>

    <div class="container mx-auto">
        <?php require '../src/_menu.php' ?>
        <?php require '../src/_alerts.php' ?>
        <div>
            <div class="mx-auto flex items-center w-auto mb-3">
                <table class="w-full border-collapse border border-gray-300 rounded-lg">
                    <thead>
                        <tr>
                            <th colspan="3" class="border-b border-gray-300 py-4 text-center bg-gray-200"><?= $producto->getDescripcion() ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td rowspan="5" class="border-b border-gray-300 py-4">Foto</td>
                            <td class="font-bold py-2">Nombre:</td>
                            <td><?= $producto->getDescripcion() ?></td>
                        </tr>
                        <tr>
                            <td class="font-bold py-2">Categoría:</td>
                            <td><?= $producto->getCategoriaNombre() ?></td>
                        </tr>
                        <tr>
                            <td class="font-bold py-2">Etiquetas:</td>
                            <td><?= $producto->getEtiquetaNombre() ?></td>
                        </tr>
                        <tr>
                            <td class="font-bold py-2">Stock:</td>
                            <td><?= $producto->getStock() ?></td>
                        </tr>
                        <tr>
                            <td class="font-bold py-2">Valoración:</td>
                            <td><?= $producto->getValoracionMedia() ?></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="border-t border-gray-300 py-4 text-center bg-gray-200">Precio:</td>
                            <td class="border-t border-gray-300 py-4 text-center"><?=$producto->getPrecio()?></td>
                            <td class="border-t border-gray-300 py-4 text-center bg-gray-200">Aquí iria el botón de comprar</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="mx-auto flex items-center w-auto">
                <table class="w-full border-collapse border border-gray-300 rounded-lg">
                    <thead>
                        <tr>
                            <th colspan="3" class="border-b border-gray-300 py-4 text-center bg-gray-200">Comentarios</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border-b border-gray-300 py-4">Foto</td>
                            <td class="font-bold py-2">Usuario</td>
                            <td>Comentario</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>