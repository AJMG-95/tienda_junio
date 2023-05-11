<?php

namespace App\Tablas;

use PDO;

class Reclamacion extends Modelo
{
    protected static string $tabla = 'reclamaciones';

    public $id;
    public $reclamacion;
    public $usuario_id;
    public $factura_id;

    public function __construct(array $campos)
    {
        $this->id = $campos['id'];
        $this->reclamacion = $campos['reclamacion'];
        $this->usuario_id = $campos['usuario_id'];
        $this->factura_id = $campos['factura_id'];
    }

    public static function todos(array $where = [], array $execute = [], ?PDO $pdo = null): array
    {
        $pdo = $pdo ?? conectar();

        $where = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $sent = $pdo->prepare("SELECT * FROM reclamaciones $where");
        $sent->execute($execute);
        $filas = $sent->fetchAll(PDO::FETCH_ASSOC);
        $res = [];
        foreach ($filas as $fila) {
            $res[] = new static($fila);
        }
        return $res;
    }

    public static function obtener(int $id, ?PDO $pdo = null): ?self
    {
        $pdo = $pdo ?? conectar();

        $sent = $pdo->prepare('SELECT * FROM reclamaciones WHERE id = :id');
        $sent->execute([':id' => $id]);
        $fila = $sent->fetch(PDO::FETCH_ASSOC);
        return $fila ? new static($fila) : null;
    }

    public function guardar(?PDO $pdo = null): bool
    {
        $pdo = $pdo ?? conectar();

        $sent = $pdo->prepare('INSERT INTO reclamaciones (reclamacion, usuario_id, factura_id)
                               VALUES (:reclamacion, :usuario_id, :factura_id)');
        return $sent->execute([
            ':reclamacion' => $this->reclamacion,
            ':usuario_id' => $this->usuario_id,
            ':factura_id' => $this->factura_id,
        ]);
    }

    public function actualizar(?PDO $pdo = null): bool
    {
        $pdo = $pdo ?? conectar();

        $sent = $pdo->prepare('UPDATE reclamaciones SET reclamacion = :reclamacion, usuario_id = :usuario_id, 
                               factura_id = :factura_id = WHERE id = :id');
        return $sent->execute([
            ':id' => $this->id,
            ':reclamacion' => $this->reclamacion,
            ':usuario_id' => $this->usuario_id,
            ':factura_id' => $this->factura_id,
        ]);
    }

    public function borrar(?PDO $pdo = null): bool
    {
        $pdo = $pdo ?? conectar();

        $sent = $pdo->prepare('DELETE FROM reclamaciones WHERE id = :id');
        return $sent->execute([':id' => $this->id]);
    }

    public function getUsuario(?PDO $pdo = null): ?Usuario
    {
        return Usuario::obtener($this->usuario_id, $pdo);
    }
}
