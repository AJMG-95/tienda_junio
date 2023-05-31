<?php

namespace App\Tablas;

use App\Tablas\Modelo;

use PDO;

class Oferta extends Modelo
{
    protected static string $tabla = 'ofertas';

    public $id;
    public $oferta;

    public function __construct(array $campos)
    {
        $this->id = $campos['id'];
        $this->oferta = $campos['oferta'];
    }

    public static function obtener(int $id, ?PDO $pdo = null): ?self
    {
        $sent = $pdo->prepare("SELECT * FROM ofertas WHERE id = :id");
        $sent->execute(['id' => $id]);
        $registro = $sent->fetch();
        return $registro ? new self($registro) : null;
    }
}