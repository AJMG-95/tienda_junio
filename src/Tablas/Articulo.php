<?php

namespace App\Tablas;

use PDO;
use App\Tablas\Etiqueta;

class Articulo extends Modelo
{
    protected static string $tabla = 'articulos';

    private $id;
    private $codigo;
    private $descripcion;
    private $precio;
    private $stock;
    private $categoria_id;
    private $oferta_id;

    public function __construct(array $campos)
    {
        $this->id = $campos['id'];
        $this->codigo = $campos['codigo'];
        $this->descripcion = $campos['descripcion'];
        $this->precio = $campos['precio'];
        $this->stock = $campos['stock'];
        $this->categoria_id = $campos['categoria_id'];
        $this->oferta_id = $campos['oferta_id'];


    }

    public static function existe(int $id, ?PDO $pdo = null): bool
    {
        return static::obtener($id, $pdo) !== null;
    }

    public function getCodigo()
    {
        return $this->codigo;
    }

    public function getDescripcion()
    {
        return $this->descripcion;
    }

    public function getPrecio()
    {
        return $this->precio;
    }

    public function getStock()
    {
        return $this->stock;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCategoriaNombre(?PDO $pdo = null)
    {
        $pdo = $pdo ?? conectar();
        $sent = $pdo->prepare("SELECT categoria FROM categorias WHERE id = :categoria_id");
        $sent->execute(['categoria_id' => $this->categoria_id]);
        return $sent->fetchColumn();
    }

    public function getCategoriaId(?PDO $pdo = null)
    {
        $pdo = $pdo ?? conectar();
        $sent = $pdo->prepare("SELECT id FROM categorias WHERE id = :categoria_id");
        $sent->execute(['categoria_id' => $this->categoria_id]);
        return $sent->fetchColumn();
    }

    public function getOferta(?PDO $pdo = null)
    {
        $pdo = $pdo ?? conectar();
        $sent = $pdo->prepare("SELECT oferta FROM ofertas WHERE id = :oferta_id");
        $sent->execute(['oferta_id' => $this->oferta_id]);
        return $sent->fetchColumn();
    }

    public function getEtiquetaNombre(?PDO $pdo = null)
    {
        $pdo = $pdo ?? conectar();
        $sent = $pdo->prepare("SELECT e.etiqueta
                                FROM etiquetas e JOIN articulos_etiquetas ae ON (e.id = ae.etiqueta_id)
                                WHERE ae.articulo_id = :articulo_id");
        $sent->execute(['articulo_id' => $this->id]);
        $etiquetas = $sent->fetchAll(PDO::FETCH_COLUMN);
        return implode(', ', $etiquetas);
    }


    public function getValoracionMedia(?PDO $pdo = null)
    {
        $pdo = $pdo ?? conectar();
        $sent = $pdo->prepare("SELECT ROUND(AVG(valoracion), 2) FROM valoraciones WHERE articulo_id = :id");
        $sent->execute([':id' => $this->id]);
        $avg = $sent->fetchColumn();

        if ($avg == false) {
            return 'Sin valorar';
        }

        return $avg;
    }

    public static function filtraArticuloEtiqueta(array $etiquetas, ?PDO $pdo = null): string
    {
        $nEtiquetas = sizeof($etiquetas);
        $etiquetas = implode(",", $etiquetas);


        $sql_etiquetas = $pdo->prepare("SELECT DISTINCT a.id
                                FROM articulos a
                                INNER JOIN articulos_etiquetas ae ON (a.id = ae.articulo_id)
                                WHERE ae.etiqueta_id IN ($etiquetas)
                                GROUP BY a.id
                                HAVING COUNT(ae.etiqueta_id) >= $nEtiquetas");
        $sql_etiquetas->execute();
        $resultado = $sql_etiquetas->fetchAll(PDO::FETCH_ASSOC);
        foreach ($resultado as $res) {
            $etiqueta_art_id[] = $res['id'];
        }
        $etiqueta_art_id = implode(", ", $etiqueta_art_id);
        return $etiqueta_art_id;
    }

}
