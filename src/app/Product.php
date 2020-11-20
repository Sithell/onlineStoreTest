<?php

class Product
{
    private $conn;
    private $category;

    function __construct($conn, $category) {
        $this->conn = $conn;
        $this->category = $category;
    }

    function get($id) {
        $stmt = $this->conn->prepare('
            SELECT * FROM product WHERE id=:id
        ');
        $stmt->execute([
            'id' => $id
        ]);
        if ($stmt->rowCount() == 0) {
            return PRODUCT_NOT_FOUND;
        }

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $result['category'] = $this->category->get_full_path($result['category_id']);
        return $result;
    }

    function get_all($start, $per_page) {
        $args = use_default([$start, $per_page], [0, 5]);
        $start = $args[0];
        $per_page = $args[1];

        $stmt = $this->conn->prepare('SELECT * FROM product LIMIT '.$start.', '.$per_page);
        $stmt->execute();
        if ($stmt->rowCount() == 0) {
            return NO_PRODUCTS_FOUND;
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function add($token, $name, $description, $price) {
        if (!isTokenValid($this->conn, $token)) {
            return INVALID_TOKEN;
        }
        $stmt = $this->conn->prepare('
            SELECT id FROM user WHERE token=:token
        ');
        $stmt->execute([
            'token' => $token
        ]);
        $user_id = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
        $stmt = $this->conn->prepare('
            INSERT INTO product (name, description, price, user_id)
            VALUES (:name, :description, :price, :user_id)
        ');
        $stmt->execute([
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'user_id' => $user_id,
        ]);
        return [
            'status' => 'ok',
            'product' => [
                'id' => $this->conn->lastInsertId(),
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'user_id' => $user_id,
            ]
        ];
    }

    function delete($id, $token) {
        if (!isTokenValid($this->conn, $token)) {
            return INVALID_TOKEN;
        }
        $stmt = $this->conn->prepare('
            SELECT user_id FROM product
            WHERE id=:id
        ');
        $stmt->execute([
            'id' => $id
        ]);
        if ($stmt->rowCount() == 0) {
            return PRODUCT_NOT_FOUND;
        }
        $user_id = $stmt->fetch(PDO::FETCH_ASSOC)['user_id'];
        $stmt = $this->conn->prepare('
            SELECT id FROM user
            WHERE token=:token
        ');
        $stmt->execute([
            'token' => $token
        ]);
        if ($user_id != $stmt->fetch(PDO::FETCH_ASSOC)['id']) {
            return ACCESS_DENIED;
        }
        $stmt = $this->conn->prepare('DELETE FROM product WHERE id = :id');
        $stmt->execute(array(
            'id' => $id
        ));
        return [
            'status' => 'ok'
        ];
    }
}