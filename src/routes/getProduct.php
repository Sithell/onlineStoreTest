<?php

function get_product($conn, $id) {
    $stmt = $conn->prepare('
        SELECT * FROM product WHERE id=:id
    ');
    $stmt->execute(array(
        'id' => $id
    ));
    if ($stmt->rowCount() == 0) {
        return array(
            'error' => 'Product not found'
        );
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
