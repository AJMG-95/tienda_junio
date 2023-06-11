<?php
session_start();

require '../vendor/autoload.php';

// Obtener valores de formulario
$id = obtener_post('id');

// Verificar si se proporcionó un ID
if (!isset($id)) {
    volver_perfil();
}

// Establecer conexión a la base de datos
try {
    $pdo = conectar();

    $sent = $pdo->prepare("UPDATE usuarios SET email = NULL WHERE id = :id");
    $sent->execute([':id' => $id]);

    $sent = $pdo->prepare("SELECT email FROM usuarios WHERE id = :id");
    $sent->execute([':id' => $id]);
    $emailActual = $sent->fetchColumn();

    if ($emailActual) {
        $_SESSION['error'] = 'No se ha podidio elimienar el email.';
    } else {
        $_SESSION['exito'] = 'El email de usuario se ha eliminado correctamente.';
    }



} catch (\Throwable $th) {
    $_SESSION['error'] = 'Ha ocurrido un error en el servidor.';
    print_r($th);
    die();

}

volver_perfil();