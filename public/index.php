<?php

use App\Tablas\Articulo;
use App\Tablas\Etiqueta;

session_start() ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/css/output.css" rel="stylesheet">
    <title>Portal</title>
    <script>
        function cambiar(el, articulo_id, usuario_id) {
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
    unset($_SESSION['vistaDetalle']);
    
    $categoria = obtener_get('categoria');
    $etiquetas = obtener_get('etiqueta');
    $valoracion = obtener_get('valoracion');
    $precio_min = obtener_get('precio_min');
    $precio_max = obtener_get('precio_max');
    $conSinValoracion = obtener_get('conSinValoracion');
    $nAvgvaloracon = obtener_get('nAvgvaloracon');
    $usuario = \App\Tablas\Usuario::logueado();
    $usuario_id = $usuario ? $usuario->id : null;


    $where = [];
    $execute = [];
    $having = "";
    $order1 = "";
    $order2 = "";

    $pdo = conectar();

    if (isset($etiquetas) && $etiquetas != '') {
        $etiqueta_art_id = [];
        $ids_etiquetas_validas = Etiqueta::filtraEtiquetas($etiquetas, $pdo);
        $etiqueta_art_id = Articulo::filtraArticuloEtiqueta($ids_etiquetas_validas, $pdo);
        $where[] = "a.id IN (" . $etiqueta_art_id . ")";
    }

    if (isset($categoria) && $categoria != '') {
        $where[] = "c.id = " . $categoria;
    }

    if (isset($precio_min) && $precio_min != '') {
        $where[] = "a.precio >= " . $precio_min;
    }

    if (isset($precio_max) && $precio_max != '') {
        $where[] = "a.precio <= " . $precio_max;
    }

    if (isset($conSinValoracion) && $conSinValoracion != '') {
        $where[] = $conSinValoracion;
    }

    if (isset($nAvgvaloracon) && $nAvgvaloracon != '') {
        if ($nAvgvaloracon == 'n') {
            $order1 = ', COUNT(v.*) AS total_valoraciones';
            $order2 = 'GROUP BY o.oferta, a.id, c.id ORDER BY COUNT(v.*) DESC, o.oferta';
        } elseif ($nAvgvaloracon == 'avg') {
            $order1 = ', CASE WHEN AVG(v.valoracion) IS NULL THEN 1 ELSE 0 END, AVG(v.valoracion)';
            $order2 = 'GROUP BY o.oferta, a.id, c.id ORDER BY CASE WHEN AVG(v.valoracion) IS NULL THEN 1 ELSE 0 END, AVG(v.valoracion) DESC, o.oferta';
        }
    } else {
        $order2 = 'ORDER BY a.oferta_id, a.descripcion';
    }

    $where = !empty($where) ?  'WHERE ' . implode(' AND ', $where) : "";

    $sent = $pdo->prepare("SELECT DISTINCT a.*, c.id AS catid, c.categoria, o.oferta $order1
                            FROM articulos a
                            JOIN categorias c ON (a.categoria_id = c.id)
                            LEFT JOIN ofertas o ON (o.id = a.oferta_id)
                            LEFT JOIN valoraciones v ON (a.id = v.articulo_id)
                            $where
                            $order2");
    $sent->execute();

    ?>
    <div class="container mx-auto">
        <?php require '../src/_menu.php' ?>
        <?php require '../src/_alerts.php' ?>
        <div>
            <form action="" method="GET">
                <fieldset>
                    <legend><b>Criterios de búsqueda</b></legend>
                    <br>
                    <div class="flex mb-3 font-normal text-gray-700 dark:text-gray-400">
                        <label class="block mb-2 text-sm font-medium w-1/4 pr-4">
                            Categoría:
                            <select name="categoria" id="categoria" class="border text-sm rounded-lg w-full p-2.5">
                                <?php
                                $sent_categoria = $pdo->query("SELECT * FROM categorias");
                                ?>
                                <option value="">Todas las categorías</option>
                                <?php foreach ($sent_categoria as $filaCategoria) : ?>
                                    <option value=<?= hh($filaCategoria['id']) ?> <?= ($filaCategoria['id'] == $categoria) ? 'selected' : '' ?>>
                                        <?= hh($filaCategoria['categoria']) ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </label>
                        <label class="block mb-2 text-sm font-medium w-1/4 pr-4">
                            Etiquetas:
                            <input type="text" name="etiqueta" value="<?= isset($etiquetas) ? $etiquetas : '' ?>" class="border text-sm rounded-lg w-full p-2.5">
                        </label>
                        <label class="block mb-2 text-sm font-medium w-1/4 pr-4">
                            Precio mínimo:
                            <input type="text" name="precio_min" value="<?= isset($precio_min) ? $precio_min : '' ?>" class="border text-sm rounded-lg w-full p-2.5">
                        </label>
                        <label class="block mb-2 text-sm font-medium w-1/4 pr-4">
                            Precio máximo:
                            <input type="text" name="precio_max" value="<?= isset($precio_max) ? $precio_max : '' ?>" class="border text-sm rounded-lg w-full p-2.5">
                        </label>
                    </div>
                    <div class="flex w-auto">
                        <div class="flex mb-3 font-normal text-gray-700 dark:text-gray-400">
                            <label class="block mb-2 text-sm font-medium w-auto mr-3">
                                <p>Mostrar sólo: </p>
                                <input type="radio" name="conSinValoracion" value="v.articulo_id IS NULL">
                                Artículos sin valoración <br>
                                <input type="radio" name="conSinValoracion" value="v.articulo_id IS NOT NULL">
                                Artículos valorados <br>
                            </label>
                        </div>
                        <div class="flex mb-3 font-normal text-gray-700 dark:text-gray-400">
                            <label class="block mb-2 text-sm font-medium w-auto ml-3">
                                <p>Ordenar artículos por: </p>
                                <input type="radio" name="nAvgvaloracon" value="n">
                                Número de valoraciones <br>
                                <input type="radio" name="nAvgvaloracon" value="avg">
                                Mayor valoración media<br>
                                <br>
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Buscar</button>
                </fieldset>
            </form>
        </div>
        <div class="flex">
            <main class="flex-1 grid grid-cols-3 gap-4 justify-center justify-items-center">
                <?php foreach ($sent as $fila) :
                ?>
                    <div class="p-6 max-w-xs min-w-full bg-white rounded-lg border border-gray-200 shadow-md dark:bg-gray-800 dark:border-gray-700">
                        <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white"><?= hh($fila['descripcion']) ?> - <?= hh($fila['precio']) ?> € </h5>
                        <h6 class="mb-2 text-2xl tracking-tight text-red-700"><?= hh($fila['oferta']) ?></h6>
                        <p class="mb-3 font-normal text-gray-700 dark:text-gray-400"><?= hh($fila['categoria']) ?></p>
                        <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Existencias: <?= hh($fila['stock']) ?></p>
                        <?php if ($fila['stock'] > 0) : ?>
                            <a href="/insertar_en_carrito.php?id=<?= $fila['id'] ?>&categoria=<?= hh($categoria) ?>&etiquetas=<?= hh($etiquetas) ?>" class="inline-flex items-center py-2 px-3.5 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                Añadir al carrito
                                <svg aria-hidden="true" class="ml-3 -mr-1 w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </a>
                        <?php else : ?>
                            <a class="inline-flex items-center py-2 px-3.5 text-sm font-medium text-center text-white bg-gray-700 rounded-lg hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800">
                                Sin existencias
                            </a>
                        <?php endif ?>
                        <div class="flex mb-3 font-normal text-gray-700 dark:text-gray-400">
                            <form action="valorar_articulo.php" method="GET">
                                <label class="block mb-2 text-sm font-medium w-1/4 pr-4">
                                    Valorar:
                                    <?php
                                    $sent_valoraciones = $pdo->prepare("SELECT *
                                                                        FROM valoraciones
                                                                        WHERE usuario_id = :usuario_id AND articulo_id = :articulo_id
                                                                        ORDER BY created_at DESC LIMIT 1");
                                    $sent_valoraciones->execute(['usuario_id' => $usuario_id, 'articulo_id' => $fila['id']]);
                                    $valoracion_usuario = $sent_valoraciones->fetch(PDO::FETCH_ASSOC);
                                    ?>
                                    <select name="valoracion" id="valoracion">
                                        <option value="" <?= (!$usuario_id) ? 'selected' : '' ?>></option>
                                        <?php for ($i = 1; $i <= 5; $i++) : ?>
                                            <option value="<?= $i ?>" <?= isset($valoracion_usuario['valoracion']) && ($valoracion_usuario['valoracion'] == $i) ? 'selected' : '' ?>><?= $i ?></option>
                                        <?php endfor ?>
                                    </select>
                                </label>
                                <input type="hidden" name="articulo_id" value="<?= $fila['id'] ?>">
                                <input type="hidden" name="usuario_id" value="<?= $usuario_id ?>">

                                <?php if (!\App\Tablas\Usuario::esta_logueado()) : ?>
                                    <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800" disabled>Votar</button>
                                <?php else : ?>
                                    <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Votar</button>
                                <?php endif ?>
                            </form>
                            <div>
                                <label class="block text-m font-medium pl-3 ml-3">
                                    Valoración media:
                                    <?php
                                    $sent_valoracionMedia = $pdo->prepare("SELECT avg(valoracion)::numeric(10,2)
                                                            FROM valoraciones
                                                            WHERE articulo_id = :articulo_id");
                                    $sent_valoracionMedia->execute(['articulo_id' => $fila['id']]);
                                    $valoracionMedia = $sent_valoracionMedia->fetchColumn();
                                    ?>
                                    <p class="mb-3 pl-3 font-normal text-gray-700 dark:text-gray-400"><?= hh($valoracionMedia) ?></p>
                                </label>
                            </div>
                        </div>

                        <?php if (\App\Tablas\Usuario::esta_logueado()) : ?>
                            <form action="comentar_articulo.php" method="POST" class="inline">
                                <input type="hidden" name="articulo_id" value="<?= $fila['id'] ?>">
                                <input type="hidden" name="usuario_id" value="<?= $usuario_id ?>">
                                <button type="submit" onclick="cambiar(event, <?= $fila['id'] ?>, <?= $usuario_id ?>)" class="focus:outline-none text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 mr-2 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-900" data-modal-toggle="insertar_comentario">Comentar</button>
                            </form>
                        <?php endif ?>

                        <a href="/vista_detalle.php?id=<?= hh($fila['id']) ?>&usuario=<?= hh($usuario_id) ?>" class="inline-flex items-center py-2 px-3.5 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                            Ver producto
                            <svg aria-hidden="true" class="ml-3 -mr-1 w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </a>
                    </div>
                <?php endforeach ?>
            </main>
            <?php if (!$carrito->vacio()) : ?>
                <aside class="flex flex-col items-center w-1/4" aria-label="Sidebar">
                    <div class="overflow-y-auto py-4 px-3 bg-gray-50 rounded dark:bg-gray-800">
                        <table class="mx-auto text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <th scope="col" class="py-3 px-6">Descripción</th>
                                <th scope="col" class="py-3 px-6">Cantidad</th>
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
    <div id="insertar_comentario" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 md:inset-0 h-modal md:h-full">
        <div class="relative p-4 w-full max-w-md h-full md:h-auto">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <button type="button" class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-800 dark:hover:text-white" data-modal-toggle="insertar_comentario">
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
                        <button data-modal-toggle="insertar_comentario" type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                            Enviar
                        </button>
                        <button data-modal-toggle="insertar_comentario" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">
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