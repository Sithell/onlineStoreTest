<?php

function auth($conn, $phone_number) {
    $code = random_int(100000, 999999);

    $stmt = $conn->prepare('SELECT id FROM user WHERE phone_number = :phone_number');
    $stmt->execute(array(
        'phone_number' => $phone_number
    ));
    if ($stmt->rowCount() == 0) {
        $stmt = $conn->prepare('
            INSERT INTO User (phone_number, verification_code)
            VALUES (:phone_number, :code);
        ');
    }
    else {
        $stmt = $conn->prepare('
            UPDATE User
            SET verification_code = :code
            WHERE phone_number = :phone_number;
        ');
    }
    $stmt->execute(array(
        'code' => $code,
        'phone_number' => $phone_number
    ));
    sendMessage("Ваш код подтверждения: ".$code);
}
