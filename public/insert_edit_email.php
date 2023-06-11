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

        //Comprovar que el email tiene el formato correcto (pregmatch)
        $pattern = "/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";
        
        if (preg_match($pattern, $email)) {
            $sent = $pdo->prepare("SELECT email FROM usuarios WHERE id = :id");
            $sent->execute([':id' => $id]);
            $emailActual = $sent->fetchColumn();

            $sent = $pdo->prepare("UPDATE usuarios SET email = :email WHERE id = :id");
            $sent->execute([':email' => $email, ':id' => $id]);

            $emailActual ?  $_SESSION['exito'] = 'El email de usuario se ha modificado correctamente.'
                        :  $_SESSION['exito'] = 'El email de usuario se ha insertado correctamente.';

        } else {
            $_SESSION['error'] = 'El email no tiene un formato correcto.';
        }

    } else {
        $_SESSION['error'] = 'Debe rellenar todos los campos del formulario.';
    }
} catch (\Throwable $th) {
    $_SESSION['error'] = 'Ha ocurrido un error en el servidor.';

}

volver_perfil();