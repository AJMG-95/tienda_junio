<?php session_start() ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/css/output.css" rel="stylesheet">
    <script>
        function cambiar(el, id) {
            el.preventDefault();
            const oculto = document.getElementById('oculto');
            oculto.setAttribute('value', id);
        }

        function cambiarModificar(el, id) {
            el.preventDefault();
            const ocultoModificar = document.getElementById('ocultoModificar');
            ocultoModificar.setAttribute('value', id);
        }

    </script>
    <title>Listado de artículos</title>
</head>

<body>
    <?php
    require '../../vendor/autoload.php';

    if ($usuario = \App\Tablas\Usuario::logueado()) {
        if (!$usuario->es_admin()) {
            $_SESSION['error'] = 'Acceso no autorizado.';
            return volver();
        }
    } else {
        return redirigir_login();
    }

    $pdo = conectar();
    $sent = $pdo->query("SELECT a.*, c.categoria FROM articulos a JOIN categorias c ON (categoria_id = c.id) ORDER BY a.codigo");
    ?>
    <div class="container mx-auto">
        <?php require '../../src/_menu.php' ?>
        <?php require '../../src/_alerts.php' ?>
        <div class="overflow-x-auto relative mt-4">
            <button type="submit" class="focus:outline-none text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-900" data-modal-toggle="popup-modal-add">Añadir nuevo artículo</button>
            <table class="mx-auto text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <th scope="col" class="py-3 px-6">Código</th>
                    <th scope="col" class="py-3 px-6">Descripción</th>
                    <th scope="col" class="py-3 px-6">Precio</th>
                    <th scope="col" class="py-3 px-6">Stock</th>
                    <th scope="col" class="py-3 px-6">Categoria</th>
                    <th scope="col" class="py-3 px-6">Etiquetas</th>
                    <th scope="col" class="py-3 px-6 text-center">Acciones</th>
                </thead>
                <tbody>
                    <?php foreach ($sent as $fila) : ?>
                        <?php $fila_id = hh($fila['id']) ?>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="py-4 px-6"><?= hh($fila['codigo']) ?></td>
                            <td class="py-4 px-6"><?= hh($fila['descripcion']) ?></td>
                            <td class="py-4 px-6"><?= hh($fila['precio']) ?></td>
                            <td class="py-4 px-6"><?= hh($fila['stock']) ?></td>
                            <td class="py-4 px-6"><?= hh($fila['categoria']) ?></td>
                            <td class="py-4 px-6">
                                <?php
                                $sent2 = $pdo->prepare("SELECT etiqueta
                                                            FROM articulos_etiquetas ae JOIN etiquetas e ON (ae.etiqueta_id = e.id) 
                                                            WHERE articulo_id = :id");
                                $sent2->execute([':id' => $fila['id']]);
                                ?>
                                <select name="ets" id="ets" class="border text-sm rounded-lg w-full p-2.5">
                                    <?php foreach ($sent2 as $row) : ?>
                                        <option value=""><?= hh($row['etiqueta']) ?></option>
                                    <?php endforeach ?>
                                </select>
                                <br />
                                <a href="/admin/mod_etiqueta_articulo.php?id=<?= hh($fila['id']) ?>" class="inline-flex mt-2 items-center py-2 px-3.5 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                    Modificar etiquetas
                                    <svg aria-hidden="true" class="ml-3 -mr-1 w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </a>
                            </td>
                            <td class="px-6 text-center">

                                <form action="/admin/editar.php" method="POST" class="inline">
                                    <input type="hidden" name="id" value="<?= $fila_id ?>">
                                    <button type="submit" onclick="cambiarModificar(event, <?= $fila_id ?>)" class="focus:outline-none text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-900" data-modal-toggle="modificar">Editar</button>
                                </form>
                                <form action="/admin/borrar.php" method="POST" class="inline">
                                    <input type="hidden" name="id" value="<?= $fila_id ?>">
                                    <button type="submit" onclick="cambiar(event, <?= $fila_id ?>)" class="focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900" data-modal-toggle="popup-modal-del">Borrar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Esto es para borrar un articulo -->
    <div id="popup-modal-del" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 md:inset-0 h-modal md:h-full">
        <div class="relative p-4 w-full max-w-md h-full md:h-auto">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <button type="button" class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-800 dark:hover:text-white" data-modal-toggle="popup-modal-del">
                    <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="sr-only">Cerrar ventana</span>
                </button>
                <div class="p-6 text-center">
                    <svg aria-hidden="true" class="mx-auto mb-4 w-14 h-14 text-gray-400 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">¿Seguro que desea borrar este artículo?</h3>
                    <form action="/admin/borrar.php" method="POST">
                        <input id="oculto" type="hidden" name="id">
                        <button data-modal-toggle="popup-modal-del" type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                            Sí, seguro
                        </button>
                        <button data-modal-toggle="popup-modal-del" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">No, cancelar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Esto es para modificar -->
    <div id="modificar" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 md:inset-0 h-modal md:h-full">
        <div class="relative p-4 w-full max-w-md h-full md:h-auto">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <button type="button" class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-800 dark:hover:text-white" data-modal-toggle="modificar">
                    <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="sr-only">Cerrar ventana</span>
                </button>
                <div class="p-6 text-center">
                    <svg aria-hidden="true" class="mx-auto mb-4 w-14 h-14 text-gray-400 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">¿Seguro que desea borrar este artículo?</h3>
                    <form action="/admin/editar.php" method="POST">

                        <label for="codigo" class="block mb-2 text-sm font-medium">
                            Codigo:
                            <input type="text" name="codigo" id="codigo" class="border text-sm rounded-lg w-full p-2.5">
                        </label>
                        <label for="descripcion" class="block mb-2 text-sm font-medium">
                            Descripción:
                            <input type="text" name="descripcion" id="descripcion" class="border text-sm rounded-lg w-full p-2.5">
                        </label>
                        <label class="block mb-2 text-sm font-medium">
                            Categoría:
                            <select name="categoria" id="categoria" class="border text-sm rounded-lg w-full p-2.5">
                                <?php
                                $sent2 = $pdo->query("SELECT * FROM categorias");
                                ?>
                                <option value="">Todas las categorías</option>
                                <?php foreach ($sent2 as $fila) : ?>
                                    <option value=<?= hh($fila['id']) ?> <?= ($fila['id'] == $categoria) ? 'selected' : '' ?>>
                                        <?= hh($fila['categoria']) ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </label>
                        <label for="precio" class="block mb-2 text-sm font-medium">
                            Precio:
                            <input type="number" name="precio" id="precio" min="0" step="any" class="border text-sm rounded-lg w-full p-2.5">
                        </label>
                        <label for="stock" class="block mb-2 text-sm font-medium">
                            Stock:
                            <input type="number" name="stock" id="stock" min="0" class="border text-sm rounded-lg w-full p-2.5">
                        </label>
                        <input id="ocultoModificar" type="hidden" name="id">
                        <button data-modal-toggle="modificar" type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                            Sí, seguro
                        </button>
                        <button data-modal-toggle="modificar" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">No, cancelar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Esto es para añadir un nuevo articulo -->
    <div id="popup-modal-add" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 md:inset-0 h-modal md:h-full">
        <div class="relative p-4 w-full max-w-md h-full md:h-auto">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <button type="button" class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-800 dark:hover:text-white" data-modal-toggle="popup-modal-add">
                    <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="sr-only">Cerrar ventana</span>
                </button>
                <div class="p-6 text-center">
                    <svg aria-hidden="true" class="mx-auto mb-4 w-14 h-14 text-gray-400 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">¿Seguro que desea borrar este artículo?</h3>
                    <form action="/admin/anyadir_articulo.php" method="POST">
                        <label for="codigo" class="block mb-2 text-sm font-medium">
                            Codigo:
                            <input type="text" name="codigo" id="codigo" class="border text-sm rounded-lg w-full p-2.5">
                        </label>
                        <label for="descripcion" class="block mb-2 text-sm font-medium">
                            Descripción:
                            <input type="text" name="descripcion" id="descripcion" class="border text-sm rounded-lg w-full p-2.5">
                        </label>
                        <label class="block mb-2 text-sm font-medium">
                            Categoría:
                            <select name="categoria" id="categoria" class="border text-sm rounded-lg w-full p-2.5">
                                <?php
                                $sent2 = $pdo->query("SELECT * FROM categorias");
                                ?>
                                <option value="">Todas las categorías</option>
                                <?php foreach ($sent2 as $fila) : ?>
                                    <option value=<?= hh($fila['id']) ?> <?= ($fila['id'] == $categoria) ? 'selected' : '' ?>>
                                        <?= hh($fila['categoria']) ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </label>
                        <label for="precio" class="block mb-2 text-sm font-medium">
                            Precio:
                            <input type="number" name="precio" id="precio" min="0" step="any" class="border text-sm rounded-lg w-full p-2.5">
                        </label>
                        <label for="stock" class="block mb-2 text-sm font-medium">
                            Stock:
                            <input type="number" name="stock" id="stock" min="0" class="border text-sm rounded-lg w-full p-2.5">
                        </label>
                        <button data-modal-toggle="popup-modal-add" type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                            Sí, seguro
                        </button>
                        <button data-modal-toggle="popup-modal-add" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">No, cancelar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="/js/flowbite/flowbite.js"></script>
</body>

</html>