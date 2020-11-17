<?php

function get_my_products($conn, $token, $per_page, $start)
{
    $stmt = $conn->prepare('SELECT id FROM user WHERE token=:token');
    $stmt->execute(array(
        'token' => $token
    ));
    if ($stmt->rowCount() == 0) {
        return array(
            'error' => 'Invalid token'
        );
    }
    $user_id = $stmt->fetchAll(PDO::FETCH_ASSOC)[0]['id'];
    $stmt = $conn->prepare('SELECT * FROM product WHERE user_id=:id LIMIT ' . $start . ', ' . $per_page);
    $stmt->execute(array(
        'id' => $user_id
    ));
    if ($stmt->rowCount() == 0) {
        return array(
            'error' => 'No products found'
        );
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
