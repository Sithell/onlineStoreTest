<?php

function get_user_products($conn, $id, $per_page, $start)
{
    $stmt = $conn->prepare('SELECT * FROM product WHERE user_id=:id LIMIT ' . $start . ', ' . $per_page);

    $stmt->execute(array(
        'id' => $id
    ));
    if ($stmt->rowCount() == 0) {
        return array(
            'error' => 'No products found'
        );
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
