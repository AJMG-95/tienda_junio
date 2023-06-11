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
    <title>Perfil</title>


    <script>
        function modNombre(el, id) {
            el.preventDefault();
            const ocultoModificarNombre = document.getElementById('oculto-mod-nombre');
            ocultoModificarNombre.setAttribute('value', id);
        }

        function modPasswd(el, id) {
            el.preventDefault();
            const ocultoModificarPasswd = document.getElementById('oculto-mod-passwd');
            ocultoModificarPasswd.setAttribute('value', id);
        }

        function insertModEmail(el, id) {
            el.preventDefault();
            const ocultoModificarEmail = document.getElementById('oculto-isert-mod-email');
            ocultoModificarEmail.setAttribute('value', id);
        }

        function insertEmail(el, id) {
            el.preventDefault();
            const ocultoModificarEmail = document.getElementById('oculto-isert-mod-email');
            ocultoModificarEmail.setAttribute('value', id);
        }

        function delEmail(el, id) {
            el.preventDefault();
            const ocultoModificarEmail = document.getElementById('oculto-del-email');
            ocultoModificarEmail.setAttribute('value', id);
        }
    </script>
</head>

<body>
    <?php

    require '../vendor/autoload.php';

    $usuario = \App\Tablas\Usuario::logueado();
    $usuario_id = $usuario ? $usuario->id : null;

    $pdo = conectar();

    $sent = $pdo->prepare('SELECT * FROM usuarios WHERE id = :id');
    $sent->execute([':id' => $usuario_id]);
    $usuario = $sent->fetch(PDO::FETCH_ASSOC);

    $usuarioObj = new Usuario($usuario);

    ?>
    <div class="container mx-auto">
        <?php require '../src/_menu.php' ?>
        <?php require '../src/_alerts.php' ?>

        <main class="w-full mx-auto flex items-center w-auto mb-3 flex-col">

            <table>
                <thead>
                    <th class="text-2xl font-bold mb-4">Perfil de Usuario</th>
                </thead>
                <tbody>
                    <tr class="flex items-center justify-center">
                        <td class="flex items-center mb-4">
                            <img class=" h-14 w-14 ml-1 my-4" src="/img/profile-picture-3.jpg" alt="user photo">
                        </td>
                        <td>
                            <p class="mb-2 mx-4"><strong> Puntuación: </strong> <?= $usuarioObj->getPuntuacion() ?></p>
                        </td>
                    </tr>
                    <tr class="m-2 p-2">
                        <td>
                            <p class="mb-2 mx-4"><strong>Nombre:</strong> <?= $usuarioObj->getNombre() ?></p>
                        </td>
                        <td>
                            <form action="/editar_nombre.php" method="POST" class="inline ml-3">
                                <input type="hidden" name="id" value="<?= $usuarioObj->getId() ?>">
                                <button type="submit" onclick="modNombre(event, <?= $usuarioObj->getId() ?>)" class="focus:outline-none text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-900" data-modal-toggle="mod-nombre">Editar nombre</button>
                            </form>
                        </td>
                    </tr>
                    <tr class="m-2 p-2">
                        <td>
                            <p class="mb-2"><strong>Fecha nacimiento: </strong> <?= $usuarioObj->getFechaNacimiento() ?> </p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p class="mb-2"><strong>Edad: </strong> <?= floor((strtotime(date('Y-m-d')) - strtotime($usuarioObj->getFechaNacimiento())) / (365 * 24 * 60 * 60)); ?></p>
                        </td>
                    </tr>
                    <tr class="m-2 p-2">
                        <td>
                            <p class="mb-2"><strong>Email: </strong> <?= $usuarioObj->getEmail() ?> </p>
                        </td>
                        <td>
                            <form action="/insert_edit_email.php" method="POST" class="inline ml-3">
                                <input type="hidden" name="id" value="<?= $usuarioObj->getId() ?>">
                                <button type="submit" onclick="insertModEmail(event, <?= $usuarioObj->getId() ?>)" class="focus:outline-none text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-900" data-modal-toggle="isert-mod-email">
                                    <?php if ($usuarioObj->getEmail() == null) : ?>
                                        Insertar email
                                    <?php else : ?>
                                        Editar email
                                    <?php endif ?>
                                </button>
                            </form>
                            <form action="/borrar_email.php" method="POST" class="inline ml-3">
                                <input type="hidden" name="id" value="<?= $usuarioObj->getId() ?>">
                                <button type="submit" onclick="delEmail(event, <?= $usuarioObj->getId() ?>)" class="focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900" data-modal-toggle="del-email">Borrar email</button>
                            </form>
                        </td>
                    </tr <tr class="m-2 p-2">
                    <td>
                        <p class="mb-2"><strong>Contraseña:</strong> ********* </p>
                    </td>
                    <td>
                        <form action="/editar_passwd.php" method="POST" class="inline ml-3">
                            <input type="hidden" name="id" value="<?= $usuarioObj->getId() ?>">
                            <button type="submit" onclick="modPasswd(event, <?= $usuarioObj->getId() ?>)" class="focus:outline-none text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-900" data-modal-toggle="mod-passwd">Editar contraseña</button>
                        </form>
                    </td>
                    </tr>
                </tbody>
            </table>
        </main>
    </div>

    <!-- Esto es para modificar el nombre -->
    <div id="mod-nombre" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 md:inset-0 h-modal md:h-full">
        <div class="relative p-4 w-full max-w-md h-full md:h-auto">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <button type="button" class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-800 dark:hover:text-white" data-modal-toggle="mod-nombre">
                    <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="sr-only">Cerrar ventana</span>
                </button>
                <div class="p-6 text-center">
                    <svg aria-hidden="true" class="mx-auto mb-4 w-14 h-14 text-gray-400 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">¿Seguro que desea modificar su nombre de usuario?</h3>
                    <form action="/editar_nombre.php" method="POST">

                        <label for="nombre" class="block mb-2 text-sm font-medium">
                            Nombre:
                            <input type="text" name="nombre" id="nombre" class="border text-sm rounded-lg w-full p-2.5">
                        </label>
                        <input id="oculto-mod-nombre" type="hidden" name="id">
                        <button data-modal-toggle="mod-nombre" type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                            Sí, seguro
                        </button>
                        <button data-modal-toggle="mod-nombre" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">No, cancelar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Esto es para modificar el contraseña -->
    <div id="mod-passwd" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 md:inset-0 h-modal md:h-full">
        <div class="relative p-4 w-full max-w-md h-full md:h-auto">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <button type="button" class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-800 dark:hover:text-white" data-modal-toggle="mod-passwd">
                    <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="sr-only">Cerrar ventana</span>
                </button>
                <div class="p-6 text-center">
                    <svg aria-hidden="true" class="mx-auto mb-4 w-14 h-14 text-gray-400 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">¿Seguro que desea modificar su contraseña?</h3>
                    <form action="/editar_passwd.php" method="POST">
                        <label for="currentPasswd" class="block mb-2 text-sm font-medium">
                            Contraseña actual:
                            <input type="text" name="currentPasswd" id="currentPasswd" class="border text-sm rounded-lg w-full p-2.5">
                        </label>
                        <label for="newPasswd" class="block mb-2 text-sm font-medium">
                            Nueva Contraseña:
                            <input type="text" name="newPasswd" id="newPasswd" class="border text-sm rounded-lg w-full p-2.5">
                        </label>
                        <label for="repeatPasswd" class="block mb-2 text-sm font-medium">
                            Verificar Contraseña:
                            <input type="text" name="repeatPasswd" id="repeatPasswd" class="border text-sm rounded-lg w-full p-2.5">
                        </label>
                        <input id="oculto-mod-passwd" type="hidden" name="id">
                        <button data-modal-toggle="mod-passwd" type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                            Sí, seguro
                        </button>
                        <button data-modal-toggle="mod-passwd" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">No, cancelar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Esto es para modificar/insertar el Email -->
    <div id="isert-mod-email" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 md:inset-0 h-modal md:h-full">
        <div class="relative p-4 w-full max-w-md h-full md:h-auto">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <button type="button" class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-800 dark:hover:text-white" data-modal-toggle="isert-mod-email">
                    <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="sr-only">Cerrar ventana</span>
                </button>
                <div class="p-6 text-center">
                    <svg aria-hidden="true" class="mx-auto mb-4 w-14 h-14 text-gray-400 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">¿Seguro que desea modificar su email?</h3>
                    <form action="/insert_edit_email.php" method="POST">
                        <label for="email" class="block mb-2 text-sm font-medium">
                            Email:
                            <input type="text" name="email" id="email" class="border text-sm rounded-lg w-full p-2.5">
                        </label>
                        <input id="oculto-isert-mod-email" type="hidden" name="id">
                        <button data-modal-toggle="isert-mod-email" type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                            Sí, seguro
                        </button>
                        <button data-modal-toggle="isert-mod-email" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">No, cancelar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Esto es para borrar email -->
    <div id="del-email" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 md:inset-0 h-modal md:h-full">
        <div class="relative p-4 w-full max-w-md h-full md:h-auto">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <button type="button" class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-800 dark:hover:text-white" data-modal-toggle="del-email">
                    <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="sr-only">Cerrar ventana</span>
                </button>
                <div class="p-6 text-center">
                    <svg aria-hidden="true" class="mx-auto mb-4 w-14 h-14 text-gray-400 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">¿Seguro que desea borrar su email?</h3>
                    <form action="/borrar_email.php" method="POST">
                        <input id="oculto-del-email" type="hidden" name="id">
                        <button data-modal-toggle="del-email" type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                            Sí, seguro
                        </button>
                        <button data-modal-toggle="del-email" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">No, cancelar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="/js/flowbite/flowbite.js"></script>
</body>

</html>