<?php
session_start();

require '../vendor/autoload.php';

// Obtener valores de formulario
$id = obtener_post('id');
$currentPasswd = obtener_post('currentPasswd');
$newPasswd = obtener_post('newPasswd');
$repeatPasswd = obtener_post('repeatPasswd');

// Verificar si se proporcionó un ID
if (!isset($id)) {
    volver_perfil();
}

$errores = '';

// Establecer conexión a la base de datos
$pdo = conectar();


// Verifica que se rellenó el formulario
if (isset($currentPasswd, $newPasswd, $repeatPasswd) && !empty($currentPasswd) && !empty($newPasswd) && !empty($repeatPasswd)) {

    if ($newPasswd == $repeatPasswd) {
        if ($newPasswd == '') {
            $errores .= '  - ' . "La contraseña es obligatoria.";
        }

        if (preg_match('/[a-z]/', $newPasswd) !== 1) {
            $errores .= '  - ' . "Debe contener al menos una minúscula.";
        }

        if (preg_match('/[A-Z]/', $newPasswd) !== 1) {
            $errores .= '  - ' . "Debe contener al menos una mayúscula.";
        }

        if (preg_match('/[[:digit:]]/', $newPasswd) !== 1) {
            $errores .= '  - ' . "Debe contener al menos un dígito.";
        }

        if (preg_match('/[[:punct:]]/', $newPasswd) !== 1) {
            $errores .= '  - ' . "Debe contener al menos un signo de puntuación.";
        }

        if (mb_strlen($newPasswd) < 8) {
            $errores .= '  - ' . "Debe tener al menos 8 caracteres.";
        }
    } else {
        $errores .= '  - ' . "La nueva contraseña no coinciden con la verificación.";
    }

    $noErrors = true;

    if($errores != '') {
        $noErrors = false;
    }


    if ($noErrors) {
        $sent = $pdo->prepare("SELECT password FROM usuarios WHERE id = :id");
        $sent->execute([':id' => $id]);
        $passwd = $sent->fetchColumn();

        $sent = $pdo->prepare("SELECT crypt(:currentPasswd, :passwd) = :passwd AS validado");
        $sent->execute([':currentPasswd' => $currentPasswd, ':passwd' => $passwd]);
        $psswdValidada = $sent->fetchColumn();


        if ($psswdValidada) {
            $sent = $pdo->prepare("UPDATE usuarios SET password = crypt(:newPasswd, gen_salt('bf', 10)) WHERE id = :id");
            $sent->execute([':newPasswd' => $newPasswd, ':id' => $id]);

            $_SESSION['exito'] = 'La contraseña se ha modificado correctamente.';
        } else {
            $_SESSION['error'] = "La contraseña no cohincide con la actual.";
        }
    } else {
        $_SESSION['error'] = $errores;
    }


} else {
    $_SESSION['error'] = "Debe rellenar todos los campos del formulario.";
}

volver_perfil();