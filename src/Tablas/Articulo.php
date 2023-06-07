<?php

namespace App\Tablas;

use PDO;

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

    public function aplicarOferta(string $oferta, int $cantidad, float $precio)
    {
        $importe_original = $cantidad * $precio;
        $importe = 0;

        switch ($oferta) {
            case '2x1':
                $unidadesCompletas = floor($cantidad / 2);
                $unidadesIndividuales = $cantidad % 2;
                $importe += ($precio * $unidadesCompletas) + ($unidadesIndividuales * $precio);
                break;
            case '50%':
                $importe += ($importe_original) / 2;
                break;
            case '2ª Unidad a mitad de precio':
                for ($i = 1; $i <= $cantidad; $i++) {
                    if ($i % 2 !== 0) {
                        $importe += $precio;
                    } else {
                        $importe += $precio / 2;
                    }
                }
                break;
            default:
                $importe += $importe_original;
                break;
        }

        return  $importe;
    }

    
    public function aplicarOferta2x1(int $cantidad, float $precio): float
    {
        $unidadesCompletas = floor($cantidad / 2);
        $unidadesIndividuales = $cantidad % 2;
        $importe = ($precio * $unidadesCompletas) + ($unidadesIndividuales * $precio);
        return $importe;
    }

    public function aplicarOferta50Porciento(float $importe_original): float
    {
        return $importe_original / 2;
    }

    public function aplicarOferta2UnidadMitadPrecio(int $cantidad, float $precio): float
    {
        $importe = 0;
        for ($i = 1; $i <= $cantidad; $i++) {
            if ($i % 2 !== 0) {
                $importe += $precio;
            } else {
                $importe += $precio / 2;
            }
        }
        return $importe;
    }
}
