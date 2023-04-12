<?php
session_start();

require '../../vendor/autoload.php';

$id = obtener_post('id');
$codigo = obtener_post('codigo');
$descripcion = obtener_post('descripcion');
$precio = obtener_post('precio');
$stock = obtener_post('stock');
$categoria_nombre = obtener_post('categoria');
$etiquetas = obtener_post('etiquetas');
$set = [];
$execute = [];

$pdo = conectar();

if (!isset($id)) {
    return volver_admin();
}


// Toma los valores actuales del artículo
$sent = $pdo->prepare("SELECT * FROM articulos WHERE id = :id");
$sent->execute([':id' => $id]);
$anterior = $sent->fetch(PDO::FETCH_ASSOC);

/* print_r($anterior);
die(); */

if (isset($id)) {
    $execute[':id'] = $id;
} else {
    $execute[':id'] = $anterior['id'];
}

if (isset($codigo)  && $codigo != '') {
    $set[] = 'codigo = :codigo';
    $execute[':codigo'] = $codigo;
} else {
    $set[] = 'codigo = :codigo';
    $execute[':codigo'] = $anterior['codigo'];
}

if (isset($descripcion) && $descripcion != '') {
    $set[] = 'descripcion = :descripcion';
    $execute[':descripcion'] = $descripcion;
} else {
    $set[] = 'descripcion = :descripcion';
    $execute[':descripcion'] = $anterior['descripcion'];
}

if (isset($precio) && $precio != '') {
    $set[] = 'precio = :precio';
    $execute[':precio'] = $precio;
} else {
    $set[] = 'precio = :precio';
    $execute[':precio'] = isset($anterior['precio']) ? $anterior['precio'] : 0;
}

if (isset($stock) && $stock != '') {
    $set[] = 'stock = :stock';
    $execute[':stock'] = $stock;
} else {
    $set[] = 'stock = :stock';
    $execute[':stock'] = $anterior['stock'];
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
        $set[] = 'id_categoria = :id_categoria';
        $execute[':id_categoria'] = $categoria['id'];
    }
} else {
    $set[] = 'id_categoria = :id_categoria';
    $execute[':id_categoria'] = $anterior['id_categoria'];
}


$etiquetas_anterior = [];
$sent = $pdo->prepare("SELECT e.*
                        FROM etiquetas e JOIN articulos_etiquetas ae ON e.id = ae.id_etiqueta
                        WHERE ae.id_articulo = :id");
$sent->execute([':id' => $id]);
while ($etiqueta = $sent->fetch(PDO::FETCH_ASSOC)) {
    $etiquetas_anterior[] = $etiqueta['id'];
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
$set = !empty($set) ? 'SET ' . implode(' , ', $set) : '';


$sent = $pdo->prepare("UPDATE articulos
                                $set
                                WHERE  id = :id");
$sent->execute($execute);


$_SESSION['exito'] = 'El artículo se ha Modificado correctamente.';

volver_admin();
