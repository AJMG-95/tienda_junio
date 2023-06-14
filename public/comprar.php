<?php

use App\Tablas\Articulo;
use App\Tablas\Usuario;

session_start() ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/css/output.css" rel="stylesheet">
    <title>Comprar</title>

</head>

<body>
    <?php require '../vendor/autoload.php';

    if (!\App\Tablas\Usuario::esta_logueado()) {
        return redirigir_login();
    }

    $usaPuntos = obtener_post('_puntos');

    $carrito = unserialize(carrito());
    $ids_art_carrito = implode(', ', $carrito->getIds());
    $where = "WHERE id IN (" . $ids_art_carrito . ")";

    function calculaTotal($carrito)
    {
        $total = 0;

        foreach ($carrito->getLineas() as $id => $linea) {
            $articulo = $linea->getArticulo();
            $cantidad = $linea->getCantidad();
            $precio = $articulo->getPrecio();
            $oferta = $articulo->getOferta() ? $articulo->getOferta() : '';
            $importe = $articulo->aplicarOferta($oferta, $cantidad, $precio);
            $total += $importe;
        }

        return $total;
    }

    $usuario = \App\Tablas\Usuario::logueado();
    $usuario_id = $usuario ? $usuario->id : null;

    $pdo = conectar();

    $sent = $pdo->prepare('SELECT * FROM usuarios WHERE id = :id');
    $sent->execute([':id' => $usuario_id]);
    $usu = $sent->fetch(PDO::FETCH_ASSOC);

    $usuarioObj = new Usuario($usu);

    $total = calculaTotal($carrito);
    $subtotal = $total;
    if (isset($usaPuntos)) {
        $total = ($total - $usuarioObj -> getPuntuacion()) <= 0 ? 0 : $total - $usuarioObj -> getPuntuacion();
    }

    if (obtener_post('_puntos') !== null && obtener_post('_testigo') !== null) {
        $usuarioObj->decreasePuntuacion($subtotal, $usuarioObj -> getPuntuacion());
    } else if (obtener_post('_testigo') !== null) {
        $usuarioObj->increasePuntuacion($total, 0.5);
    }

    if (obtener_post('_testigo') !== null) {

        $sent = $pdo->prepare("SELECT *
                                FROM articulos
                                $where");
        $sent->execute();
        $res = $sent->fetchAll(PDO::FETCH_ASSOC);

        foreach ($res as $fila) {
            if ($fila['stock'] < $carrito->getLinea($fila['id'])->getCantidad()) {
                $_SESSION['error'] = 'No hay existencias suficientes para crear la factura.';
                return volver();
            }
        }

        // Crear factura
        $pdo->beginTransaction();
        $sent = $pdo->prepare('INSERT INTO facturas (usuario_id)
                                VALUES (:usuario_id)
                                RETURNING id');
        $sent->execute([':usuario_id' => $usuario_id]);
        $factura_id = $sent->fetchColumn();
        $lineas = $carrito->getLineas();
        $values = [];
        $execute = [':f' => $factura_id];
        $i = 1;

        foreach ($lineas as $id => $linea) {
            $values[] = "(:a$i, :f, :c$i)";
            $execute[":a$i"] = $id;
            $execute[":c$i"] = $linea->getCantidad();
            $i++;
        }

        $values = implode(', ', $values);
        $sent = $pdo->prepare("INSERT INTO articulos_facturas (articulo_id, factura_id, cantidad)
                               VALUES $values");
        $sent->execute($execute);
        foreach ($lineas as $id => $linea) {
            $cantidad = $linea->getCantidad();
            $sent = $pdo->prepare('UPDATE articulos
                                      SET stock = stock - :cantidad
                                    WHERE id = :id');
            $sent->execute([':id' => $id, ':cantidad' => $cantidad]);
        }


        $pdo->commit();
        $_SESSION['exito'] = 'La factura se ha creado correctamente.';
        unset($_SESSION['carrito']);
        return volver();
    }

    ?>

    <div class="container mx-auto">
        <?php require '../src/_menu.php' ?>
        <div class="overflow-y-auto py-4 px-3 bg-gray-50 rounded dark:bg-gray-800">
            <table class="mx-auto text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <th scope="col" class="py-3 px-6">Código</th>
                    <th scope="col" class="py-3 px-6">Descripción</th>
                    <th scope="col" class="py-3 px-6">Cantidad</th>
                    <th scope="col" class="py-3 px-6">Precio</th>
                    <th scope="col" class="py-3 px-6">Importe</th>
                    <th scope="col" class="py-3 px-6">Ahorro</th>
                    <th scope="col" class="py-3 px-6">Oferta</th>
                </thead>
                <tbody>
                    <?php

                    foreach ($carrito->getLineas() as $id => $linea) :
                        $articulo = $linea->getArticulo();
                        $codigo = $articulo->getCodigo();
                        $cantidad = $linea->getCantidad();
                        $precio = $articulo->getPrecio();
                        $oferta = $articulo->getOferta() ? $articulo->getOferta() : '';
                        $importe = $articulo->aplicarOferta($oferta, $cantidad, $precio);
                        $ahorro = ($precio * $cantidad) - $importe;

                    ?>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="py-4 px-6"><?= $articulo->getCodigo() ?></td>
                            <td class="py-4 px-6"><?= $articulo->getDescripcion() ?></td>
                            <td class="py-4 px-6 text-center"><?= $cantidad ?></td>
                            <td class="py-4 px-6 text-center"><?= dinero($precio) ?></td>
                            <td class="py-4 px-6 text-center"><?= dinero($importe) ?></td>
                            <td class="py-4 px-6 text-center"><?= dinero($ahorro) ?></td>
                            <td class="py-4 px-6 text-center"><?= $oferta ?></td>
                            <td>
                                <a href="/incrementar.php?id=<?= $articulo->getId() ?>" class="focus:outline-none text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-900">+</a>
                                <a href="/decrementar.php?id=<?= $articulo->getId() ?>" class="focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900">-</a>
                            </td>
                        </tr>
                    <?php endforeach ?>

                </tbody>
                <tfoot>
                    <td colspan="3"></td>
                    <td class="text-center font-semibold">TOTAL:</td>
                    <td class="text-center font-semibold"><?= dinero($total) ?></td>
                </tfoot>
            </table>
            <br>

            <form action="" method="POST">
                <div class="flex justify-center font-normal text-gray-700 dark:text-gray-400">
                    <label class="block mb-2 text-sm font-medium w-1/4 pr-4">
                        <input type="checkbox" name="_puntos" value="1" <?= isset($usaPuntos) ? 'checked' : '' ?>>
                        Utilizar los puntos acumulados: <?= $usuarioObj->getPuntuacion() ?>
                    </label>
                    <button type="submit" href="" class="focus:outline-none text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-900"> Usar puntos</button>
                </div> <br>
            </form>
            <form action="" method="POST">
                <div class="flex justify-center">
                    <input type="hidden" name="_testigo" value="1">
                    <button type="submit" href="" class="focus:outline-none text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-900">Realizar pedido</button>
                </div>
            </form>
        </div>
    </div>
    <?php 
        if (isset($usaPuntos)) {
           
            volver_comprar();
        }
    ?>
    <script src="/js/flowbite/flowbite.js"></script>
</body>

</html>