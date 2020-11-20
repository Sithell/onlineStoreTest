<?php

class User
{
    private $conn;

    function __construct($conn) {
        $this->conn = $conn;
    }
    
    function add($phone_number) {
        $code = random_int(100000, 999999);

        $stmt = $this->conn->prepare('
            SELECT id FROM user 
            WHERE phone_number = :phone_number
        ');
        $stmt->execute(array(
            'phone_number' => $phone_number
        ));
        if ($stmt->rowCount() == 0) {
            $stmt = $this->conn->prepare('
                INSERT INTO User (phone_number, verification_code)
                VALUES (:phone_number, :code);
            ');
        }
        else {
            $stmt = $this->conn->prepare('
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
        return [
            'status' => 'ok',
            'message' => "Сообщение с кодом подтверждения отправлено на указанный номер"
        ];
    }

    function get_token($phone_number, $code) {
        $stmt = $this->conn->prepare('
            SELECT verification_code FROM user 
            WHERE phone_number=:phone_number
        ');
        $stmt->execute(array(
            'phone_number' => $phone_number
        ));
        if ($stmt->rowCount() == 0) {
            return USER_NOT_FOUND;
        }
        elseif ($stmt->fetchAll(PDO::FETCH_ASSOC)[0]["verification_code"] != $code) {
            return INVALID_CODE;
        }
        else {
            $token = generate_token($this->conn);
            $stmt = $this->conn->prepare('
                UPDATE User
                SET token=:token
                WHERE phone_number=:phone_number;
            ');
            $stmt->execute(array(
                'token' => $token,
                'phone_number' => $phone_number
            ));
            $stmt = $this->conn->prepare('
                UPDATE User
                SET verification_code=null
                WHERE phone_number=:phone_number;
            ');
            $stmt->execute([
                'phone_number' => $phone_number
            ]);
            return [
                'status' => 'ok',
                "token" => $token
            ];
        }
    }

    function get($id) {
        $stmt = $this->conn->prepare('
            SELECT * FROM user WHERE id=:id
        ');
        $stmt->execute([
            'id' => $id
        ]);
        if ($stmt->rowCount() == 0) {
            return USER_NOT_FOUND;
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function get_all($start, $per_page) {
        $args = use_default([$start, $per_page], [0, 5]);
        $start = $args[0];
        $per_page = $args[1];

        $stmt = $this->conn->prepare('SELECT * FROM user LIMIT '.$start.', '.$per_page);
        $stmt->execute();
        if ($stmt->rowCount() == 0) {
            return NO_USERS_FOUND;
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function get_by_token($token) {
        if (!isTokenValid($this->conn, $token)) {
            return INVALID_TOKEN;
        }

        $stmt = $this->conn->prepare('
            SELECT * FROM user WHERE token=:token
        ');
        $stmt->execute([
            'token' => $token
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function get_products($id, $start, $per_page) {
        $args = use_default([$start, $per_page], [0, 5]);
        $start = $args[0];
        $per_page = $args[1];

        $stmt = $this->conn->prepare('
            SELECT id FROM user WHERE id=:id
        ');
        $stmt->execute([
            'id' => $id
        ]);
        if ($stmt->rowCount() == 0) {
            return USER_NOT_FOUND;
        }
        $stmt = $this->conn->prepare('
            SELECT * FROM product 
            WHERE user_id=:user_id
            LIMIT '.$start.', '.$per_page.'
        ');
        $stmt->execute([
            'user_id' => $id
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function get_products_by_token($token, $start, $per_page) {
        if (!isTokenValid($this->conn, $token)) {
            return INVALID_TOKEN;
        }
        $stmt = $this->conn->prepare('
            SELECT id FROM user WHERE token=:token
        ');
        $stmt->execute([
            'token' => $token
        ]);
        $id = $stmt->fetchAll(PDO::FETCH_ASSOC)[0]['id'];
        return $this->get_products($id, $start, $per_page);
    }

    function set($token, $name) {
        if (!isTokenValid($this->conn, $token)) {
            return INVALID_TOKEN;
        }
        $stmt = $this->conn->prepare('
            UPDATE User
            SET name = :name
            WHERE token = :token;
        ');
        $stmt->execute(array(
            'token' => $token,
            'name' => $name
        ));
        return [
            'status' => 'ok',
            'user' => [
                'name' => $name
            ]
        ];
    }
}