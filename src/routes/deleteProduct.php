<?php

function delete_product($conn, $token, $id) {
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
    $stmt = $conn->prepare('SELECT user_id FROM product WHERE id = :id');
    $stmt->execute(array(
        'id' => $id
    ));
    if ($stmt->rowCount() == 0) {
        return array(
            'error' => 'Product not found'
        );
    }
    if ($stmt->fetchAll(PDO::FETCH_ASSOC)[0]['user_id'] != $user_id) {
        return array(
            'error' => 'Access denied: this is not your product'
        );
    }
    $user_id = $stmt->fetchAll(PDO::FETCH_ASSOC)[0]['id'];
    $stmt = $conn->prepare('DELETE FROM product WHERE id = :id');
    $stmt->execute(array(
        'id' => $id
    ));
}