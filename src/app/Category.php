<?php


class Category
{
    private $conn;

    function __construct($conn) {
        $this->conn = $conn;
    }

    function get_name($id) {
        $stmt = $this->conn->prepare('
            SELECT name FROM category
            WHERE id=:id
        ');
        $stmt->execute([
            'id' => $id
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['name'];
    }

    function get_full_path($id) {
        $path = "";
        $parent_id = $id;
        do {
            $path = $this->get_name($parent_id).CATEGORY_DELIMITER.$path;
            $stmt = $this->conn->prepare('
                SELECT parent_id FROM category
                WHERE id=:id
            ');
            $stmt->execute([
                'id' => $parent_id
            ]);
            $parent_id = $stmt->fetch(PDO::FETCH_ASSOC)['parent_id'];
        } while (!is_null($parent_id));
        return substr($path, 0, -1);
    }

    function get_id($path) {
        $parent_id = 0;
        do {
            $node = explode(CATEGORY_DELIMITER, $path)[0];
            $stmt = $this->conn->prepare('
                SELECT id FROM category
                WHERE name=:name AND parent_id=:parent_id
            ');
            $stmt->execute([
                'name' => $node,
                'parent_id' => $parent_id
            ]);
            if ($stmt->rowCount() == 0) {
                return INVALID_CATEGORY_PATH;
            }
            $parent_id = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
            $path = substr($path, strlen($node)+1);
        } while ($path != '');
        return $parent_id;
    }
}