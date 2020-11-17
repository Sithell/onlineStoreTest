<?php

function add_product($conn, $token, $name, $description, $price) {
    $stmt = $conn->prepare('SELECT id FROM user WHERE token = :token');
    $stmt->execute(array(
        'token' => $token
    ));
    if ($stmt->rowCount() == 0) {
        return array(
            'error' => 'token invalid'
        );
    }

    $user_id = $stmt->fetchAll(PDO::FETCH_ASSOC)[0]['id'];
    $stmt = $conn->prepare('
        INSERT INTO product (name, description, price, user_id)
        VALUES (:name, :description, :price, :user_id)
    ');
    $stmt->execute(array(
        'name' => $name,
        'description' => $description,
        'price' => $price,
        'user_id' => $user_id,
    ));
}