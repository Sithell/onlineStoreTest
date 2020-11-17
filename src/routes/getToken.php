<?php

function get_token($conn, $phone_number, $code) {
    $stmt = $conn->prepare('SELECT verification_code FROM user WHERE phone_number = :phone_number');
    $stmt->execute(array(
        'phone_number' => $phone_number
    ));
    if ($stmt->rowCount() == 0) {
        return array(
            'error' => "User not found"
        );
    }
    elseif ($stmt->fetchAll(PDO::FETCH_ASSOC)[0]["verification_code"] != $code) {
        return array(
            'error' => "Invalid code"
        );
    }
    else {
        $token = generate_token($conn);

        $stmt = $conn->prepare('
            UPDATE User
            SET token = :token
            WHERE phone_number = :phone_number;
        ');
        $stmt->execute(array(
            ':token' => $token,
            'phone_number' => $phone_number
        ));
        $stmt = $conn->prepare('
            UPDATE User
            SET verification_code = null
            WHERE phone_number = :phone_number;
        ');
        $stmt->execute(array(
            'phone_number' => $phone_number
        ));

        return array(
            "token" => $token
        );
    }
}

function generate_token($conn, $length = 40) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    while (true) {
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        $stmt = $conn->prepare('
            SELECT id FROM user
            WHERE token = :token;
        ');
        $stmt->execute(array(
            'token' => $string
        ));
        if ($stmt->rowCount() == 0) {
            return $string;
        }
    }
}