<?php

namespace App\Tablas;

use App\Tablas\Modelo;

use PDO;

class Etiqueta extends Modelo
{
    protected static string $tabla = 'etiquetas';

    public $id;
    public $etiqueta;

    public function __construct(array $campos)
    {
        $this->id = $campos['id'];
        $this->etiqueta = $campos['etiqueta'];
    }

    public static function filtraEtiquetas(string $etiquetas, ?PDO $pdo = null) : array
    {
        $etiquetas = explode(' ', $etiquetas);
        $idsEtiquetas = [];

        foreach ($etiquetas as $etiqueta) {
            $sent = $pdo->prepare("SELECT id
                                FROM etiquetas
                                WHERE unaccent(lower(etiqueta)) LIKE unaccent(lower(:etiqueta))");
            $sent->execute([':etiqueta' => $etiqueta]);
            $id = $sent->fetchColumn();

            if ($id !== false) {
                $idsEtiquetas[] = $id;
            }
        }

        $idsEtiquetas = array_filter($idsEtiquetas);
        return $idsEtiquetas ?: null;
    }
}
