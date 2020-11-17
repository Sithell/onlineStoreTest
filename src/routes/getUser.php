<?php

function get_user($conn, $id)
{
    $stmt = $conn->prepare('
        SELECT name, phone_number FROM user WHERE id=:id
    ');
    $stmt->execute(array(
        'id' => $id
    ));
    if ($stmt->rowCount() == 0) {
        return array(
            'error' => 'User not found'
        );
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
