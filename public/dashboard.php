<?php

use App\Tablas\Factura;
use App\Tablas\Usuario;

session_start() ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/css/output.css" rel="stylesheet">
    <title>Dashboard</title>
</head>

<body>
    <?php require '../vendor/autoload.php';

    if (!\App\Tablas\Usuario::esta_logueado()) {
        return redirigir_login();
    }

    $usuario = \App\Tablas\Usuario::logueado();
    $usuario_id = $usuario ? $usuario->id : null;

    $facturas = Factura::todosConTotalDescuento(
        ['usuario_id = :usuario_id'],
        [':usuario_id' => Usuario::logueado()->id]
    );

  /*   print_r($facturas); */

    //modificar para realizar una reclamacion
    ?>

    <div class="container mx-auto">
        <?php require_once '../src/_menu.php' ?>
        <?php require '../src/_alerts.php' ?>
        <div class="overflow-y-auto py-4 px-3 bg-gray-50 rounded dark:bg-gray-800">
            <a href="reclamaciones.php?id=<?= $usuario_id ?>" class="focus:outline-none text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 mr-2 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-900">
                <span class="relative px-5 py-2.5 transition-all ease-in duration-75 bg-green dark:bg-gray-900 rounded-md group-hover:bg-opacity-1">
                    Ver mis reclamaciones
                </span>
            </a>
            <table class="mx-auto text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <th scope="col" class="py-3 px-6">Fecha</th>
                    <th scope="col" class="py-3 px-6">Total</th>
                    <th scope="col" class="py-3 px-6">Ahorro</th>
                    <th scope="col" class="py-3 px-6 text-center">Acciones</th>
                </thead>
                <tbody>
                    <?php foreach ($facturas as $factura) : ?>
                        <?php
                        $created_at = DateTime::createFromFormat(
                            'Y-m-d H:i:s',
                            $factura->getCreatedAt()
                        )->setTimezone(new DateTimeZone('Europe/Madrid'));
                        ?>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="py-4 px-6">
                                <?= hh($created_at->format('d-m-Y H:i:s')) ?>
                            </td>
                            <td class="py-4 px-6">
                                <?= hh(dinero($factura->getTotalDescuento()['total'])) ?>
                            </td>
                            <td class="py-4 px-6">
                                <?= hh(dinero($factura->getTotalDescuento()['ahorro'])) ?>
                            </td>
                            <td class="px-6 text-center">
                                <a href="/factura_pdf.php?id=<?= $factura->id ?>" target="_blank">
                                    <button class="focus:outline-none text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 mr-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-900">PDF</button>
                                </a>
                            </td>
                            <td>
                                <button data-modal-toggle="insertar_reclamacion" class="focus:outline-none text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 mr-2 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-900">
                                    <span class="relative px-5 py-2.5 transition-all ease-in duration-75 bg-green dark:bg-gray-900 rounded-md group-hover:bg-opacity-1">
                                        Reclamación
                                    </span>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
        <!-- Esto es para añadir un nuevo reclamacion -->
        <div id="insertar_reclamacion" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 md:inset-0 h-modal md:h-full">
            <div class="relative p-4 w-full max-w-md h-full md:h-auto">
                <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                    <button type="button" class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-800 dark:hover:text-white" data-modal-toggle="insertar_reclamacion">
                        <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="sr-only">Cerrar ventana</span>
                    </button>
                    <div class="p-6 text-center">
                        <form action="/anyadir_reclamacion.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="factura_id" value="<?= $factura->id ?>">
                            <input type="hidden" name="usuario_id" value="<?= $usuario_id ?>">
                            <div class="mb-6">
                                <label for="reclamacion" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Reclamación</label>
                                <input type="text" name="reclamacion_txt" id="reclamacion_txt" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600  dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required>
                                <br>
                                <label for="reclamacion_img" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Adjuntar archivo</label>
                                <input type="file" name="reclamacion_img" id="reclamacion_img" accept="image/jgep, image/jpg, image/png" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600  dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 mb-4">
                            </div>
                            <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                Enviar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="/js/flowbite/flowbite.js"></script>
</body>

</html>