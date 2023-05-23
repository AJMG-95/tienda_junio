<?php

namespace App\Tablas;

use PDO;

class Valoracion extends Modelo
{
    protected static string $tabla = 'valoraciones';

    private $articulo_id;
    private $usuario_id;
    private $valoracion;

    public function __construct(array $campos)
    {
        $this->articulo_id = $campos['articulo_id'];
        $this->usuario_id = $campos['usuario_id'];
        $this->valoracion = $campos['valoracion'];
    }

    public function getArticuloId()
    {
        return $this->articulo_id;
    }

    public function getUsuarioId()
    {
        return $this->usuario_id;
    }

    public function getValoracion()
    {
        return $this->valoracion;
    }

    public static function obtenerValoracionesArticulo(int $articulo_id, ?PDO $pdo = null): array
    {
        $pdo = $pdo ?? conectar();
        $sent = $pdo->prepare("SELECT * FROM valoraciones WHERE articulo_id = :articulo_id");
        $sent->execute(['articulo_id' => $articulo_id]);
        return $sent->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtenerValoracionPromedio(int $articulo_id, ?PDO $pdo = null): float
    {
        $pdo = $pdo ?? conectar();
        $sent = $pdo->prepare("SELECT AVG(valoracion) FROM valoraciones WHERE articulo_id = :articulo_id");
        $sent->execute(['articulo_id' => $articulo_id]);
        return (float) $sent->fetchColumn();
    }

    public static function obtenerCantidadValoraciones(int $articulo_id, ?PDO $pdo = null): int
    {
        $pdo = $pdo ?? conectar();
        $sent = $pdo->prepare("SELECT COUNT(*) FROM valoraciones WHERE articulo_id = :articulo_id");
        $sent->execute(['articulo_id' => $articulo_id]);
        return (int) $sent->fetchColumn();
    }

    public static function masValorados(?PDO $pdo = null)
    {
        $pdo = $pdo ?? conectar();
        $sent = $pdo->prepare("SELECT a.*, COUNT(v.*) AS total_valoraciones
                                FROM articulos a
                                JOIN valoraciones v ON a.id = v.articulo_id
                                GROUP BY a.id
                                HAVING COUNT(v.*) >= (SELECT COUNT(*)
                                                    FROM valoraciones
                                                    GROUP BY articulo_id
                                                    ORDER BY COUNT(*) DESC)");
        $sent->execute();
        $resultad = $sent->fetchAll();
    }
}
