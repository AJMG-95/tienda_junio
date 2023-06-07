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
    private $ahorro;

    public function __construct(array $campos)
    {
        $this->id = $campos['id'];
        $this->created_at = $campos['created_at'];
        $this->usuario_id = $campos['usuario_id'];
        $this->total = isset($campos['total']) ? $campos['total'] : null;
        $this->ahorro = isset($campos['ahorro']) ? $campos['ahorro'] : null;
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

    public function getId()
    {
        return $this->id;
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

    public static function todosConTotal(
        array $where = [],
        array $execute = [],
        ?PDO $pdo = null
    ): array {
        $pdo = $pdo ?? conectar();

        $where = !empty($where)
            ? 'WHERE ' . implode(' AND ', $where)
            : '';
        $sent = $pdo->prepare("SELECT f.*, SUM(cantidad * a.precio) AS total
                                 FROM facturas f
                                 JOIN articulos_facturas l
                                   ON l.factura_id = f.id
                                 JOIN articulos a
                                   ON l.articulo_id = a.id
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

    public function calcularDescuento($facturaId, ?PDO $pdo = null)
    {

        $pdo = $pdo ?? conectar();

        $sent = $pdo->prepare("SELECT o.oferta, af.cantidad, a.precio
                                    FROM articulos a
                                    LEFT JOIN ofertas o ON a.oferta_id = o.id
                                    JOIN articulos_facturas af ON (a.id = af.articulo_id)
                                    JOIN facturas f ON (f.id = af.factura_id)
                                    WHERE f.id = :factura_id");
        $sent->execute([':factura_id' => $facturaId]);

        $importe = 0;
        foreach ($sent as $res) {
            $oferta = $res['oferta'];
            $precio = $res['precio'];
            $cantidad = $res['cantidad'];
    
            $importe_original = $cantidad * $precio;
    
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
        }


        return $importe;
    }

    public function getTotalDescuento(?PDO $pdo = null)
    {
        $pdo = $pdo ?? conectar();

        if (!isset($this->total)) {
            $sent = $pdo->prepare('SELECT SUM(cantidad * precio) AS total
                                 FROM articulos_facturas l
                                 JOIN articulos a
                                   ON l.articulo_id = a.id
                                WHERE factura_id = :id');
            $sent->execute([':id' => $this->id]);
            $subtotal = $sent->fetchColumn();

            $importe = $this->calcularDescuento($this->id);
            $this->ahorro = $subtotal - $importe;

            $this->total = $importe;
        }

        return ['total' => $this->total, 'ahorro' => $this->ahorro ];
    }

    public static function todosConTotalDescuento(
        array $where = [],
        array $execute = [],
        ?PDO $pdo = null
    ): array {
        $pdo = $pdo ?? conectar();

        $where = !empty($where)
            ? 'WHERE ' . implode(' AND ', $where)
            : '';
        $sent = $pdo->prepare("SELECT f.*, SUM(cantidad * a.precio) AS subtotal
                             FROM facturas f
                             JOIN articulos_facturas l
                               ON l.factura_id = f.id
                             JOIN articulos a
                               ON l.articulo_id = a.id
                           $where
                         GROUP BY f.id");
        $sent->execute($execute);
        $filas = $sent->fetchAll(PDO::FETCH_ASSOC);
        $res = [];
        foreach ($filas as $fila) {
            $factura = new static($fila);
            $importe = $factura->calcularDescuento($factura->id);
            $factura->total = $importe;
            $factura->ahorro = $fila['subtotal'] - $importe;
            $res[] = $factura;
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
