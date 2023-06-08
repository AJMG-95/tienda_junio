<?php
session_start();

require '../vendor/autoload.php';

// Obtener valores de formulario
$id = obtener_post('id');
$nombre = obtener_post('nombre');

// Verificar si se proporcionó un ID
if (!isset($id)) {
    volver_perfil();
}

// Establecer conexión a la base de datos
try {
    $pdo = conectar();

    // Verificar si se proporcionó un nombre de usuario válido
    if (isset($nombre) && !empty($nombre)) {
        $sent = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE usuario = :usuario");
        $sent->execute([':usuario' => $nombre]);
        $existe = $sent->fetchColumn();

        if ($existe > 0) {
            $_SESSION['error'] = 'El nombre de usuario ya existe.';
        } else {
            // Actualizar el nombre de usuario en la base de datos
            $sent = $pdo->prepare("UPDATE usuarios SET usuario = :nombre WHERE id = :id");
            $sent->execute([':nombre' => $nombre, ':id' => $id]);

            $_SESSION['exito'] = 'El nombre de usuario se ha modificado correctamente.';
        }
    } else {
        $_SESSION['error'] = 'Debe proporcionar un nombre de usuario válido.';
    }
} catch (\Throwable $th) {
    $_SESSION['error'] = 'Ha ocurrido un error en el servidor.';
}

volver_perfil();