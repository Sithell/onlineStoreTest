<?php


class ProductAttribute
{
    function __construct($conn) {
        $this->conn = $conn;
    }

    function get($data) {
        return $data;
    }
}