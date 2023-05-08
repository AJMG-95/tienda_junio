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
