<?php

namespace App\Tablas;

use PDO;

class Factura extends Modelo
{
    protected static string $tabla = 'facturas';

    public $id;
    public $created_at;
    public $usuario_id;
    private $total;

    public function __construct(array $campos)
    {
        $this->id = $campos['id'];
        $this->created_at = $campos['created_at'];
        $this->usuario_id = $campos['usuario_id'];
        $this->total = isset($campos['total']) ? $campos['total'] : null;
    }

    public static function existe(int $id, ?PDO $pdo = null): bool
    {
        return static::obtener($id, $pdo) !== null;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function getUsuarioId()
    {
        return $this->usuario_id;
    }

    public function getTotal(?PDO $pdo = null)
    {
        $pdo = $pdo ?? conectar();

        if (!isset($this->total)) {
            $sent = $pdo->prepare('SELECT SUM(cantidad * precio) AS total
                                     FROM articulos_facturas l
                                     JOIN articulos a
                                       ON l.articulo_id = a.id
                                    WHERE factura_id = :id');
            $sent->execute([':id' => $this->id]);
            $this->total = $sent->fetchColumn();
        }



        return $this->total;
    }

    public function getTotalOferta(?PDO $pdo = null)
    {
        $pdo = $pdo ?? conectar();
        if (!isset($this->total)) {
            $sent = $pdo->prepare("SELECT o.oferta, a.precio, ae.cantidad FROM articulos a
                                JOIN ofertas o ON (o.id = a.oferta_id)
                                JOIN articulos_facturas ae ON (ae.articulo_id = a.id)
                                JOIN facturas f ON (f.id = ae.factura_id)
                                WHERE f.id = :id");
            $sent->execute([':id' => $this->id]);
            $fila = $sent->fetch(PDO::FETCH_ASSOC);

            $importe_original = $fila['cantidad'] * $fila['precio'];
            $importe = 0;

            switch ($fila['oferta']) {
                case '2x1':
                    $unidadesCompletas = floor($fila['cantidad'] / 2);
                    $unidadesIndividuales = $fila['cantidad'] % 2;
                    $importe = ($fila['precio'] * $unidadesCompletas) + ($unidadesIndividuales * $fila['precio']);
                    break;
                case '50%':
                    $importe = ($importe_original) / 2;
                    break;
                case '2ª Unidad a mitad de precio':
                    for ($i = 1; $i <= $fila['cantidad']; $i++) {
                        if ($i % 2 !== 0) {
                            $importe += $fila['precio'];
                        } else {
                            $importe += $fila['precio'] / 2;
                        }
                    }
                    break;
                default:
                    $importe = $importe_original;
                    break;
            }
            $ahorro = $importe_original - $importe;
            $this->total = $importe;
        }

        return $this->total;
    }

    public static function todosConTotal(
        array $where = [],
        array $execute = [],
        ?PDO $pdo = null
    ): array {
        $pdo = $pdo ?? conectar();

        $where = !empty($where)
            ? 'WHERE ' . implode(' AND ', $where)
            : '';
        $sent = $pdo->prepare("SELECT f.*, SUM(cantidad * precio) AS total
                                 FROM facturas f
                                 JOIN articulos_facturas l ON l.factura_id = f.id
                                 JOIN articulos a ON l.articulo_id = a.id
                               $where
                             GROUP BY f.id");
        $sent->execute($execute);
        $filas = $sent->fetchAll(PDO::FETCH_ASSOC);
        $res = [];
        foreach ($filas as $fila) {
            $res[] = new static($fila);
        }
        return $res;
    }

    public static function todosConTotalOferta(
        array $where = [],
        array $execute = [],
        ?PDO $pdo = null
    ): array {
        $pdo = $pdo ?? conectar();

        $where = !empty($where)
            ? 'WHERE ' . implode(' AND ', $where)
            : '';
        $sent = $pdo->prepare("SELECT f.*, o.oferta, a.precio, ae.cantidad
                                 FROM facturas f
                                 JOIN articulos_facturas ae ON ae.factura_id = f.id
                                 JOIN articulos a ON a.id = ae.articulo_id
                                 JOIN ofertas o ON o.id = a.oferta_id
                               $where");
        $sent->execute($execute);
        $filas = $sent->fetchAll(PDO::FETCH_ASSOC);
        $res = [];

        foreach ($filas as $fila) {
            $importe_original = $fila['cantidad'] * $fila['precio'];
            $importe = 0;

            switch ($fila['oferta']) {
                case '2x1':
                    $unidadesCompletas = floor($fila['cantidad'] / 2);
                    $unidadesIndividuales = $fila['cantidad'] % 2;
                    $importe = ($fila['precio'] * $unidadesCompletas) + ($unidadesIndividuales * $fila['precio']);
                    break;
                case '50%':
                    $importe = ($importe_original) / 2;
                    break;
                case '2ª Unidad a mitad de precio':
                    for ($i = 1; $i <= $fila['cantidad']; $i++) {
                        if ($i % 2 !== 0) {
                            $importe += $fila['precio'];
                        } else {
                            $importe += $fila['precio'] / 2;
                        }
                    }
                    break;
                default:
                    $importe = $importe_original;
                    break;
            }
            $ahorro = $importe_original - $importe;

            $fila['total'] = $importe;
            $fila['ahorro'] = $ahorro;

            $res[] = new static($fila);
        }
        return $res;
    }

    public function getLineas(?PDO $pdo = null): array
    {
        $pdo = $pdo ?? conectar();

        $sent = $pdo->prepare('SELECT *
                                 FROM articulos_facturas
                                WHERE factura_id = :factura_id');
        $sent->execute([':factura_id' => $this->id]);
        $lineas = $sent->fetchAll(PDO::FETCH_ASSOC);
        $res = [];
        foreach ($lineas as $linea) {
            $res[] = new Linea($linea);
        }
        return $res;
    }
}
