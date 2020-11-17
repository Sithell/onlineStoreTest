<?php

function update_my_record($conn, $token, $args) {
    $stmt = $conn->prepare('SELECT * FROM user WHERE token = :token');
    $stmt->execute(array(
        'token' => $token
    ));
    if ($stmt->rowCount() == 0) {
        return array(
            'error' => 'token invalid'
        );
    }
    if (is_null($args['name'])) {
        $name = $stmt->fetchAll(PDO::FETCH_ASSOC)[0]['name'];
    } else {
        $name = $args['name'];
    }
    $stmt = $conn->prepare('
            UPDATE User
            SET name = :name
            WHERE token = :token;
        ');
    $stmt->execute(array(
        'token' => $token,
        'name' => $name
    ));
}
