<?php
session_start();

use App\Tablas\Factura;

require '../vendor/autoload.php';

if (!($usuario = \App\Tablas\Usuario::logueado())) {
    return volver();
}

$id = obtener_get('id');

if (!isset($id)) {
    return volver();
}

$pdo = conectar();

$factura = Factura::obtener($id, $pdo);

if (!isset($factura)) {
    return volver();
}

if ($factura->getUsuarioId() != $usuario->id) {
    return volver();
}

$filas_tabla = '';
$total = 0;

foreach ($factura->getLineas($pdo) as $linea) {
    $articulo = $linea->getArticulo();
    $codigo = $articulo->getCodigo();
    $descripcion = $articulo->getDescripcion();
    $cantidad = $linea->getCantidad();
    $precio = $articulo->getPrecio();
    $oferta = $articulo->getOferta() ? $articulo->getOferta() : '';
    $importe_original = $cantidad * $precio;
    $importe = 0;
    switch ($oferta) {
        case '2x1':
            $unidadesCompletas = floor($cantidad / 2);
            $unidadesIndividuales = $cantidad % 2;
            $importe = ($precio * $unidadesCompletas) + ($unidadesIndividuales * $precio);
            break;
        case '50%':
            $importe = ($importe_original) / 2;
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
            $importe = $importe_original;
            break;
    }
    $ahorro = $importe_original - $importe;
    $total += $importe;

    $precio = dinero($precio);
    $importe = dinero($importe);
    $ahorro = dinero($ahorro);
    $total = dinero($total);
    
    $filas_tabla .= <<<EOF
        <tr>
            <td>$codigo</td>
            <td>$descripcion</td>
            <td>$cantidad</td>
            <td>$precio</td>
            <td>$importe</td>
            <td>$ahorro</td>
            <td>$oferta</td>
        </tr>
    EOF;
}



$res = <<<EOT
<p>Factura número: {$factura->id}</p>

<table border="1" class="font-sans mx-auto">
    <tr>
        <th>Código</th>
        <th>Descripción</th>
        <th>Cantidad</th>
        <th>Precio</th>
        <th>Importe</th>
        <td>Ahorro</td>
        <td>Oferta</td>
    </tr>
    <tbody>
        $filas_tabla
    </tbody>
</table>

<p>Total: $total</p>
EOT;

// Create an instance of the class:
$mpdf = new \Mpdf\Mpdf();

// Write some HTML code:
$mpdf->WriteHTML(file_get_contents('css/output.css'), \Mpdf\HTMLParserMode::HEADER_CSS);
$mpdf->WriteHTML($res, \Mpdf\HTMLParserMode::HTML_BODY);

// Output a PDF file directly to the browser
$mpdf->Output();
