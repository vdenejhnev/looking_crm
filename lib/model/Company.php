<?php

namespace Model;

use App\Model;

class Company extends Model {
    protected static function bind($query, $bindings = [], $types = []) {
        global $CONNECTION;

        $stmt = $CONNECTION->prepare($query);

        if ($bindings) {
            $param = [
                implode('', array_fill(0, count($bindings), 's'))
            ];

            foreach ($bindings as $field => $value) {
                $param[] = &$bindings[$field];
            }

            call_user_func_array([$stmt, 'bind_param'], $param);
        }

        return $stmt;
    }

    public static function create($name) {
        global $CONNECTION;

        $stmt = $CONNECTION->prepare('INSERT company (name) VALUES (?)');
        $stmt->bind_param('s', $name);
        $stmt->execute();

        return self::get($name);
    }

    public static function get($name) {
        global $CONNECTION;

        $stmt = $CONNECTION->prepare('SELECT * FROM company WHERE name = ?');
        $stmt->bind_param('s', $name);
        $stmt->execute();

        $company_data = $stmt->get_result()->fetch_assoc();

        return $company_data ? (object)$company_data : false;
    }

    public static function get_active_stat($criteria = [], $options = []) {
        $query = 
            "SELECT C.name, COUNT(L.id) AS leads_count
            FROM company C
                INNER JOIN dealer D ON D.company_id = C.id
                LEFT JOIN `lead` L ON L.user_id = D.user_id";

        $expressions = [];

        if ($criteria != []) {
            foreach ($criteria as $condition) {
                $expressions[] = $condition[0] . ' = ' . $condition[1];
            } 

            $query .= ' WHERE(' . implode(')AND(', $expressions) . ')';
        }

        $query .= "        
            GROUP BY C.id
            ORDER BY leads_count DESC";

        $stmt = self::bind($query);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function find($filter = [], $options = []) {
        global $CONNECTION;

        $query = 'SELECT * FROM company' . self::build_query_tail($filter, $options);
        $stmt = $CONNECTION->prepare($query);
        $stmt->execute();

        return array_map(
            function($company) { return (object)$company; },
            $stmt->get_result()->fetch_all(MYSQLI_ASSOC)
        );
    }
}
