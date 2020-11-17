<?php

function get_products_list($conn, $per_page, $start)
{
    $stmt = $conn->prepare('SELECT * FROM product LIMIT '.$start.', '.$per_page);

    $stmt->execute(array(
        'start' => strval($start),
        'per_page' => strval($per_page)
    ));
    if ($stmt->rowCount() == 0) {
        return array(
            'error' => 'No products found'
        );
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
