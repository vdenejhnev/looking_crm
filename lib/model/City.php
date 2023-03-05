<?php

namespace Model;

class City {
    public static function create($name) {
        global $CONNECTION;

        $stmt = $CONNECTION->prepare('INSERT city (name) VALUES (?)');
        $stmt->bind_param('s', $name);
        $stmt->execute();

        return self::get($name);
    }

    public static function get($name) {
        global $CONNECTION;

        $stmt = $CONNECTION->prepare('SELECT * FROM city WHERE name = ?');
        $stmt->bind_param('s', $name);
        $stmt->execute();

        $company_data = $stmt->get_result()->fetch_assoc();

        return $company_data ? (object)$company_data : false;
    }

    public static function get_all() {
        global $CONNECTION;

        $stmt = $CONNECTION->prepare('SELECT * FROM city');
        $stmt->execute();

        return array_map(
            function($city) { return (object)$city; },
            $stmt->get_result()->fetch_all(MYSQLI_ASSOC)
        );

       
    }
}
