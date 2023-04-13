<?php
session_start();

require '../../vendor/autoload.php';

$codigo = obtener_post('codigo');
$descripcion = obtener_post('descripcion');
$precio = obtener_post('precio');
$stock = obtener_post('stock');
$categoria_nombre = obtener_post('categoria');
$etiquetas = obtener_post('etiquetas');
$values = [];
$execute = [];

$pdo = conectar();

if (isset($codigo)  && $codigo != '') {
    $values[] = 'codigo = :codigo';
    $execute[':codigo'] = $codigo;
} 

if (isset($descripcion) && $descripcion != '') {
    $values[] = 'descripcion = :descripcion';
    $execute[':descripcion'] = $descripcion;
}

if (isset($precio) && $precio != '') {
    $values[] = 'precio = :precio';
    $execute[':precio'] = $precio;
}

if (isset($stock) && $stock != '') {
    $values[] = 'stock = :stock';
    $execute[':stock'] = $stock;
}

if (isset($categoria_nombre) && $categoria_nombre != '') {
    // Comprobar si la categoría existe
    $sent = $pdo->prepare("SELECT *
                            FROM categorias
                            WHERE lower(unaccent(categoria)) LIKE lower(unaccent(:categoria))");
    $sent->execute([':categoria' => $categoria_nombre]);
    $categoria = $sent->fetch(PDO::FETCH_ASSOC);

    if (isset($categoria) && !empty($categoria)) {
        // Si la categoría existe, obtener su id y actualizar el valor de id_categoria en la tabla articulos
        $values[] = 'id_categoria = :id_categoria';
        $execute[':id_categoria'] = $categoria['id'];
    }
} else {
    $values[] = 'id_categoria = :id_categoria';
    $execute[':id_categoria'] = $anterior['id_categoria'];
}


if (isset($etiquetas) && $etiqueta != '') {
    $etiquetas = explode(' ', $etiquetas);

    foreach ($etiquetas as $etiqueta) {
        $sent = $pdo->prepare("SELECT *
                                FROM etiquetas
                                WHERE lower(unaccent(etiqueta)) LIKE lower(unaccent(:etiqueta))");
        $sent->execute([':etiqueta' => $etiqueta]);
        $etiqueta = $sent->fetch(PDO::FETCH_ASSOC);
        
        if (isset($etiqueta) && !empty($etiqueta)) {
            $id_etiqueta = $etiqueta['id'];
        } else {
            // Si la etiqueta no existe, se crea y se obtiene su id
            $sent = $pdo->prepare("INSERT INTO etiquetas (etiqueta) VALUES (lower(unaccent(:etiqueta)))");
            $sent->execute([':etiqueta' => $etiqueta]);
            $id_etiqueta = $pdo->lastInsertId();
        }
        // Verificar que la etiqueta no esté ya asociada al artículo
        if (!in_array($id_etiqueta, $etiquetas_anterior)) {
            $sent = $pdo->prepare("INSERT INTO articulos_etiquetas (id_articulo, id_etiqueta)
                                        VALUES (:id_articulo, :id_etiqueta)");
            $sent->execute([':id_articulo' => $id, ':id_etiqueta' => $id_etiqueta]);
        }
    }
}

$values = !empty($values) ? 'VALUES (' . implode(' , ', $values) . ')' : '';

$sent = $pdo->prepare("UPDATE articulos
                                $values
                                WHERE  id = :id");
$sent->execute($execute);

$_SESSION['exito'] = 'El artículo se ha Modificado correctamente.';

volver_admin();
