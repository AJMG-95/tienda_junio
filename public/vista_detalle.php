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
    <title>Vista Detalle</title>

    <script>
        function comentar(el, articulo_id, usuario_id) {
            el.preventDefault();
            const oculto_art = document.getElementById('ocultoId');
            oculto_art.setAttribute('value', articulo_id);
            const oculto_usuario = document.getElementById('ocultoIdUsuario');
            oculto_usuario.setAttribute('value', usuario_id);
        }
    </script>

</head>

<body>
    <?php

    require '../vendor/autoload.php';

    
    $carrito = unserialize(carrito());
    $pdo = conectar();
    
    $producto_id = obtener_get('id');
    $usuario_id = obtener_get('usuario');
    
    $vistaDetalleURL = $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
    $vistaDetalleURL = substr($vistaDetalleURL, strlen('127.0.0.1'), strlen($vistaDetalleURL));
    $_SESSION['vistaDetalle'] =  $vistaDetalleURL;

    $sent = $pdo->prepare("SELECT a.* FROM articulos a WHERE a.id = :id");
    $sent->execute(['id' => $producto_id]);
    $producto = $sent->fetch(PDO::FETCH_ASSOC);

    $producto = new Articulo($producto); // Convertir el array en objeto Articulo


    if (isset($usuario_id) && $usuario_id != '') {
        $sent = $pdo->prepare("SELECT u.* FROM usuarios u WHERE u.id = :id");
        $sent->execute(['id' => $usuario_id]);
        $usuario = $sent->fetch(PDO::FETCH_ASSOC);

        $usuario = new Usuario($usuario);
        /*         print_r($usuario->getId());
        die(); */
    }

    $sent = $pdo->query("SELECT u.usuario AS usuario, c.comentario AS ultimo_comentario
                           FROM usuarios u INNER JOIN comentarios c ON (u.id = c.usuario_id)
                          WHERE c.fecha_creacion = (SELECT MAX(fecha_creacion)
                                                       FROM comentarios
                                                      WHERE usuario_id = u.id)");
    ?>
    <div class="container mx-auto">
        <?php require '../src/_menu.php' ?>
        <?php require '../src/_alerts.php' ?>
        <div class="w-full justify-center justify-items-center flex flex-row">

            <main class="w-full mx-auto flex items-center w-auto mb-3 flex-col">
                <table class="w-full border-collapse border border-gray-300 rounded-lg">
                    <thead>
                        <tr>
                            <th colspan="4" class="border-b border-gray-300 py-4 text-center bg-gray-200"><?= $producto->getDescripcion() ?></th>
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
                            <td colspan="2"><?= $producto->getCategoriaNombre() ?></td>
                        </tr>
                        <tr>
                            <td class="font-bold py-2">Etiquetas:</td>
                            <td colspan="2"><?= $producto->getEtiquetaNombre() ?></td>
                        </tr>
                        <tr>
                            <td class="font-bold py-2">Stock:</td>
                            <td colspan="2"><?= $producto->getStock() ?></td>
                        </tr>
                        <tr>
                            <td class="font-bold py-2">Valoración:</td>
                            <td colspan="2"><?= $producto->getValoracionMedia() ?></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="border-t border-gray-300 py-4 text-center bg-gray-200">Precio:</td>
                            <td class="border-t border-gray-300 py-4 text-center"><?= $producto->getPrecio() ?> €</td>
                            <td colspan="2" class="border-t border-gray-300 py-4 text-center bg-gray-200">
                                <a href="/insertar_en_carrito.php?id=<?= $producto_id ?>&categoria=<?= $producto->getCategoriaId() ?>&etiquetas=<?= $producto->getEtiquetaNombre() ?>" class="inline-flex items-center py-2 px-3.5 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                    Añadir al carrito
                                    <svg aria-hidden="true" class="ml-3 -mr-1 w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td class="ml-3 pl-3">
                                Valorar:
                                <?php if ($usuario) : ?>
                                    <form action="valorar_articulo.php" method="GET">

                                        <?php
                                        $sent_valoraciones = $pdo->prepare("SELECT *
                                                                            FROM valoraciones
                                                                            WHERE usuario_id = :usuario_id AND articulo_id = :articulo_id
                                                                            ORDER BY created_at DESC LIMIT 1");
                                        $sent_valoraciones->execute(['usuario_id' => $usuario_id, 'articulo_id' => $producto_id]);
                                        $valoracion_usuario = $sent_valoraciones->fetch(PDO::FETCH_ASSOC);
                                        ?>
                                        <select name="valoracion" id="valoracion">
                                            <option value="" <?= (!$usuario_id) ? 'selected' : '' ?>></option>
                                            <?php for ($i = 1; $i <= 5; $i++) : ?>
                                                <option value="<?= $i ?>" <?= ($valoracion_usuario['valoracion'] == $i) ? 'selected' : '' ?>><?= $i ?></option>
                                            <?php endfor ?>
                                        </select>
                                        <input type="hidden" name="articulo_id" value="<?= $producto_id ?>">
                                        <input type="hidden" name="usuario_id" value="<?= $usuario_id ?>">
                                        <?php if (!\App\Tablas\Usuario::esta_logueado()) : ?>
                                            <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800" disabled>Votar</button>
                                        <?php else : ?>
                                            <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Votar</button>
                                        <?php endif ?>
                                    </form>
                                <?php else : ?>
                                    <p class="font-bold text-red-600">Debe iniciar sesión para valorar el articulo </p>
                                <?php endif ?>
                            </td>
                            <td>
                                <?php if (\App\Tablas\Usuario::esta_logueado()) : ?>
                                    <form action="comentar_articulo.php" method="POST" class="inline">
                                        <input type="hidden" name="articulo_id" value="<?= $producto_id ?>">
                                        <input type="hidden" name="usuario_id" value="<?= $usuario_id ?>">
                                        <button type="submit" onclick="comentar(event, <?= $producto_id ?>, <?= $usuario_id ?>)" class="focus:outline-none text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 mr-2 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-900" data-modal-toggle="comentar">Comentar</button>
                                    </form>
                                <?php else : ?>
                                    <p class="font-bold text-red-600">Debe iniciar sesión para comentar el articulo </p>
                                <?php endif ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
                <br>

                <table class="w-full border-collapse border border-gray-300 rounded-lg">
                    <thead>
                        <tr>
                            <th colspan="3" class="border-b border-gray-300 py-4 text-center bg-gray-200">Comentarios</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sent as $comentario) : ?>
                            <tr>
                                <td class="border-b border-gray-300 py-4">Foto</td>
                                <td class="font-bold py-2"><?= $comentario['usuario'] ?></td>
                                <td><?= $comentario['ultimo_comentario'] ?></td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>

            </main>
            <?php if (!$carrito->vacio()) : ?>
                <aside class="flex flex-col items-center w-1/4" aria-label="Sidebar">
                    <div class="overflow-y-auto py-4 px-3 bg-gray-50 rounded dark:bg-gray-800">
                        <table class="mx-auto text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <th scope="col" class="py-3 px-6">Descripción</th>
                                <thead scope="col" class="py-3 px-6">Cantidad</th>
                                </thead>
                            <tbody>
                                <?php foreach ($carrito->getLineas() as $id => $linea) : ?>
                                    <?php
                                    $articulo = $linea->getArticulo();
                                    $cantidad = $linea->getCantidad();
                                    ?>
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td class="py-4 px-6"><?= $articulo->getDescripcion() ?> <br>
                                            <?= $articulo->getCategoriaNombre($pdo) ?>
                                            <?= $articulo->getEtiquetaNombre($pdo) ?>

                                        </td>
                                        <td class="py-4 px-6 text-center"><?= $cantidad ?></td>
                                    </tr>
                                <?php endforeach   //lndlfnoi
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div>
                        <a href="/vaciar_carrito.php" class="focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900">Vaciar carrito</a>
                        <a href="/comprar.php" class="focus:outline-none text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-900">Comprar</a>
                    </div>
                </aside>
            <?php endif ?>
        </div>
    </div>
    <!-- Esto es para añadir un nuevo comentario -->
    <div id="comentar" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 md:inset-0 h-modal md:h-full">
        <div class="relative p-4 w-full max-w-md h-full md:h-auto">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <button type="button" class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-800 dark:hover:text-white" data-modal-toggle="comentar">
                    <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="sr-only">Cerrar ventana</span>
                </button>
                <div class="p-6 text-center">
                    <form action="/comentar_articulo.php" method="POST">
                        <div class="mb-6">
                            <label for="comentario" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Comentario
                                <textarea name="comentario" id="comentario" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600  dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required rows="5"></textarea>
                            </label>
                            <input id="ocultoId" type="hidden" name="articulo_id">
                            <input id="ocultoIdUsuario" type="hidden" name="usuario_id">
                        </div>
                        <button data-modal-toggle="comentar" type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                            Enviar
                        </button>
                        <button data-modal-toggle="comentar" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">
                            No, cancelar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="/js/flowbite/flowbite.js"></script>
</body>

</html>