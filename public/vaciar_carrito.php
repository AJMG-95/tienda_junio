<?php
session_start();

require_once '../src/auxiliar.php';

unset($_SESSION['carrito']);

if (isset($_SESSION['vistaDetalle'])) {
    $url = $_SESSION['vistaDetalle'];
    header("Location: $url");
} else {
    volver();
}

