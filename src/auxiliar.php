<?php

function conectar()
{
    return new \PDO('pgsql:host=localhost,dbname=tienda', 'tienda', 'tienda');
}

function hh($x)
{
    return htmlspecialchars($x ?? '', ENT_QUOTES | ENT_SUBSTITUTE);
}

function dinero($s)
{
    return number_format($s, 2, ',', ' ') . ' €';
}

function obtener_get($par)
{
    return obtener_parametro($par, $_GET);
}

function obtener_post($par)
{
    return obtener_parametro($par, $_POST);
}

function obtener_file($par, $key)
{
    return $_FILES[$par][$key];
}

function obtener_parametro($par, $array)
{
    return isset($array[$par]) ? trim($array[$par]) : null;
}

function volver()
{
    header('Location: /index.php');
}


function carrito()
{
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = serialize(new \App\Generico\Carrito());
    }

    return $_SESSION['carrito'];
}

function carrito_vacio()
{
    $carrito = unserialize(carrito());

    return $carrito->vacio();
}

function volver_a($locationn)
{
    header($locationn);
}


function volver_admin()
{
    volver_a("Location: /admin/");
}

function volver_categoria()
{
    volver_a("Location: /admin/categorias.php");
}

function volver_etiqueta()
{
    volver_a('Location: /admin/etiquetas.php');
}

function redirigir_login()
{
    volver_a('Location: /login.php');
}

function volver_modEtiquetas($id)
{
    volver_a('Location: /admin/mod_etiqueta_articulo.php?id=' . $id);
}


function volver_dashboard()
{
    volver_a('Location: /dashboard.php');
}

function ofertaFactura(string $oferta, int $cantidad, float $precioUnidad): array
    {
        $importe_original = $cantidad * $precioUnidad;
        $importe = 0;

        switch ($oferta) {
            case '2x1':
                $unidadesCompletas = floor($cantidad / 2);
                $unidadesIndividuales = $cantidad % 2;
                $importe = ($precioUnidad * $unidadesCompletas) + ($unidadesIndividuales * $precioUnidad);
                break;
            case '50%':
                $importe = ($importe_original) / 2;
                break;
            case '2ª Unidad a mitad de precio':
                for ($i = 1; $i <= $cantidad; $i++) {
                    if ($i % 2 !== 0) {
                        $importe += $precioUnidad;
                    } else {
                        $importe += $precioUnidad / 2;
                    }
                }
                break;
            default:
                $importe = $importe_original;
                break;
        }
        $ahorro = $importe_original - $importe;

        return ['importe' => $importe, 'ahorro' => $ahorro];
    }
