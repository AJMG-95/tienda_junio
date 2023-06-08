<?php
session_start();

require '../vendor/autoload.php';

// Obtener valores de formulario
$id = obtener_post('id');
$email = obtener_post('email');

// Verificar si se proporcionó un ID
if (!isset($id)) {
    volver_perfil();
}

// Establecer conexión a la base de datos
try {
    $pdo = conectar();

    // Verificar si se proporcionó un email 
    if (isset($email) && !empty($email)) {

        //Comprovar que el email tiene forma de email (email correcto)

        //if email correcto
        $sent = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE id = :id");
        $sent->execute([':email' => $email, ':id' => $id]);
        $existe = $sent->fetchColumn();

        if ($existe > 0) {
            // Update al email 
            $_SESSION['exito'] = 'El email de usuario se ha modificado correctamente.';
        } else {
            // insert del emaul
            $_SESSION['exito'] = 'El email de usuario se ha insertado correctamente.';
        }

        // else email correcto

        //error de sesion

        // endif email correcto

    } else {
        $_SESSION['error'] = 'Debe rellenar todos los campos del formulario.';
    }
} catch (\Throwable $th) {
    $_SESSION['error'] = 'Ha ocurrido un error en el servidor.';
}

volver_perfil();