<?php session_start() ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/css/output.css" rel="stylesheet">
    <title>Listado de etiquetas</title>
</head>

<body>
    <?php
    require '../../vendor/autoload.php';

    // Verificamos si el usuario está logueado y es administrador
    if ($usuario = \App\Tablas\Usuario::logueado()) {
        if (!$usuario->es_admin()) {
            $_SESSION['error'] = 'Acceso no autorizado.';
            return volver();
        }
    } else {
        return redirigir_login();
    }

    $id_articulo = obtener_get('id');

    // Creamos un array vacío para almacenar los ids de las etiquetas
    $ids_etiquetas = [];

    $pdo = conectar();

    // Obtenemos los ids de las etiquetas asociadas al artículo
    $sent = $pdo->prepare("SELECT e.id FROM etiquetas e
                        JOIN articulos_etiquetas ae ON (e.id = ae.etiqueta_id)
                        WHERE articulo_id = :id
                        ORDER BY e.etiqueta");
    $sent->execute([':id' => $id_articulo]);
    $original = $sent->fetchAll(PDO::FETCH_COLUMN);

    // Obtenemos todas las etiquetas de la base de datos
    $sent = $pdo->query("SELECT * FROM etiquetas ORDER BY etiqueta");

    // Si se ha enviado el formulario por POST, actualizamos las etiquetas asociadas al artículo
    if ($_POST) {
        $pdo->beginTransaction();
        try {
            // Eliminamos todas las etiquetas asociadas al artículo
            $sent = $pdo->prepare("DELETE FROM articulos_etiquetas WHERE articulo_id = :id");
            $sent->execute([':id' => $id_articulo]);

            // Insertamos las nuevas etiquetas asociadas al artículo
            $sent = $pdo->prepare("INSERT INTO articulos_etiquetas (articulo_id, etiqueta_id) VALUES (:articulo_id, :etiqueta_id)");
            foreach ($_POST['etiquetas'] as $etiqueta_id) {
                $sent->execute([':articulo_id' => $id_articulo, ':etiqueta_id' => $etiqueta_id]);
            }

            // Confirmación de la transacción
            $pdo->commit();

            $_SESSION['exito'] = 'Etiquetas actualizadas correctamente.';
            return volver_modEtiquetas($id_articulo);
        } catch (PDOException $e) {
            // En caso de error, se deshace la transacción
            $pdo->rollBack();

            $_SESSION['error'] = 'No se pudieron guardar los cambios en las etiquetas.';
            return volver_modEtiquetas($id_articulo);
        }
    }

    ?>
    <div class="container mx-auto">
        <?php require '../../src/_menu.php' ?>
        <?php require '../../src/_alerts.php' ?>
        <div class="overflow-x-auto relative mt-4">
            <table class="mx-auto text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <th scope="col" class="py-3 px-6">etiqueta</th>
                </thead>
                <tbody>
                    <form action="" method="POST">
                        <button type="submit" class="focus:outline-none text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-900">Modificar etiquetas</button>
                        <?php foreach ($sent as $fila) : ?>
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td class="py-4 px-6"><?= hh($fila['etiqueta']) ?>
                                    <input type="checkbox" name="etiquetas[]" value="<?= hh($fila['id']) ?>" <?= in_array($fila['id'], $original) ? "checked" : "" ?>>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </form>
                </tbody>
            </table>
        </div>
    </div>
    <script src="/js/flowbite/flowbite.js"></script>
</body>

</html>