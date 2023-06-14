<?php

namespace App\Tablas;

use PDO;

class Usuario extends Modelo
{
    protected static string $tabla = 'usuarios';

    public $id;
    public $usuario;
    public $validado;
    public $fecha_nacimiento;
    public $email;
    public $puntuacion;

    public function __construct(array $campos)
    {
        $this->id = $campos['id'];
        $this->usuario = $campos['usuario'];
        $this->validado = $campos['validado'];
        $this->fecha_nacimiento = $campos['fecha_nacimiento'];
        $this->email = isset($campos['email']) ? $campos['email'] : null;
        $this->puntuacion = $campos['puntuacion'];
    }

    public function getNombre()
    {
        return $this->usuario;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getFechaNacimiento()
    {
        return $this->fecha_nacimiento;
    }

    public function getPuntuacion()
    {
        return $this->puntuacion;
    }

    public function decreasePuntuacion($subtotal, $puntos, ?PDO $pdo = null)
    {
        $total = $subtotal - $puntos;
        
        if ($total <= 0.00) {
            $puntos = $puntos - $subtotal;
            $total = 0.00;
        } else {
            $puntos = 0;
        }
        
        $pdo = $pdo ?? conectar();
        $sent = $pdo->prepare("UPDATE usuarios
                                    SET puntuacion = :puntos
                                    WHERE id = :id");
        $sent->execute([':puntos' => $puntos, ':id' =>  $this->id]);
    }

    public function increasePuntuacion($total, $proporcion, ?PDO $pdo = null)
    {
        $pdo = $pdo ?? conectar();
        $this->puntuacion = floor(($total * $proporcion) + $this->puntuacion);
        $sent = $pdo->prepare("UPDATE usuarios
                                SET puntuacion = :puntos
                                WHERE id = :id");
        $sent->execute([':puntos' => $this->puntuacion, ':id' => $this->id]);
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email, ?PDO $pdo = null)
    {
        $pdo = $pdo ?? conectar();

        $sent = $pdo->prepare('INSERT INTO usuarios (email)
                                VALUES (:email)');
        $sent->execute([':email' => $email]);
    }

    public function es_admin(): bool
    {
        return $this->usuario == 'admin';
    }

    public static function esAdmin($usuario)
    {
        return $usuario == "admin";
    }

    public static function esta_logueado(): bool
    {
        return isset($_SESSION['login']);
    }

    public static function logueado(): ?static
    {
        return isset($_SESSION['login']) ? unserialize($_SESSION['login']) : null;
    }

    public static function comprobar($login, $password, ?PDO $pdo = null)
    {
        $pdo = $pdo ?? conectar();

        $sent = $pdo->prepare('SELECT *
                                FROM usuarios
                                WHERE usuario = :login');
        $sent->execute([':login' => $login]);
        $fila = $sent->fetch(PDO::FETCH_ASSOC);

        if ($fila == false) {
            return false;
        }

        return password_verify($password, $fila['password'])
            ? new static($fila)
            : false;
    }

    public static function existe($login, ?PDO $pdo = null): bool
    {
        $pdo = $pdo ?? conectar();

        return $login == '' ? false :
            !empty(static::todos(
                ['usuario = :usuario'],
                [':usuario' => $login],
                $pdo
            ));
    }

    public static function registrar($login, $password, $fechaNacimiento, ?PDO $pdo = null)
    {
        $pdo = $pdo ?? conectar();
        $sent = $pdo->prepare('INSERT INTO usuarios (usuario, password, fecha_nacimiento, validado)
                               VALUES (:login, :password, :fecha_nacimiento, false)');
        $sent->execute([
            ':login' => $login,
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':fecha_nacimiento' => $fechaNacimiento
        ]);
    }
}
