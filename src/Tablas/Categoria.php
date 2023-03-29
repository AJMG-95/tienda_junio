<?php

namespace App\Tablas;

use PDO;

class Categoria extends Modelo
{
    protected static string $tabla = 'articulos';

    public $id;
    private $categoria;

    public function __construct(array $campos)
    {
        $this->id = $campos['id'];
        $this->categoria = $campos['categoria'];
    }

    public function getCategoria()
    {
        return $this->categoria;
    }

    public function getId()
    {
        return $this->id;
    }
}
