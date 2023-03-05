<?php

// Функции для работы с пользователями

namespace Model;

class User {
    public static function auth() {
        global $CONNECTION; 

        if (strtolower($_SERVER['REQUEST_METHOD']) !== 'post') {
            return false;
        }

        if (!isset($_POST['_form']) || $_POST['_form'] !== 'login') {
            return false;
        }

        $user = self::get_by('email', $_POST['email']);
        if (!$user) {
            return [['field' => 'email', 'error' => 'Пользователь не найден']];
        }

        if (!password_verify($_POST['password'], $user->password)) {
            return [['field' => 'password', 'error' => 'Неправильный пароль']];
        }

        if ($user->role == 'dealer') {
            $id = $user->id;
            $stmt = $CONNECTION->prepare("SELECT * FROM dealer WHERE user_id = " . $id);
            $stmt->execute();
            $data = $stmt->get_result()->fetch_assoc();

            if ($data['enabled'] == 0) {
                return [['field' => 'email', 'error' => 'Пользователь не найден']];
            }
        }

        $_SESSION['user'] = ['id' => $user->id];

        //return $data;

        return (object)$user;
    }

    private static function bind($query, $bindings, $types = []) {
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

    public static function create($user) {
        global $CONNECTION;

        if (isset($user['id'])) {
            throw new \Exception('Unable to create new user with specified id');
        }

        $errors = self::validate($user);
        if ($errors) {
            return $errors;
        }
        
        $password = password_hash($user['password'], PASSWORD_BCRYPT);
        $role = $user['role'] ?? null;

        $stmt = $CONNECTION->prepare('INSERT user (name, email, password, role) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $user['name'], $user['email'], $password, $role);
        $created = $stmt->execute();

        if (!$created) {
            throw new \Exception('Не удалось создать пользователя: ' . $stmt->error);
        }

        return self::get_by('id', $stmt->insert_id, 'i');
    }

    public static function find($criteria = [], $options = []) {
        $query = 'SELECT * FROM user';
        $bindings = [];

        if ($criteria) {
            $expressions = [];

            foreach ($criteria as $field => $value) {
                $expressions[] = "$field = ?";
                $bindings[] = $value;
            }

            if ($expressions) {
                $query .= ' WHERE ' . implode(' AND ', $expressions);
            }
        }

        $stmt = static::bind($query, $bindings);
        $stmt->execute();

        return array_map(
            function($user) {return (object)$user;},
            $stmt->get_result()->fetch_all(MYSQLI_ASSOC)
        );
    }

    public static function get_by($field, $value, $type = 's') {
        global $CONNECTION;

        $stmt = $CONNECTION->prepare("SELECT * FROM user WHERE $field = ?");
        $stmt->bind_param($type, $value);
        $stmt->execute();

        $data = $stmt->get_result()->fetch_assoc();

        return $data ? (object)$data : false;
    }

    public static function get_current() {
        if (!isset($_SESSION['user'])) {
            return false;
        }

        return self::get_by('id', $_SESSION['user']['id']);
    }

    public static function validate($data) {
        $errors = [];

        if (!isset($data['name'])) {
            $errors[] = ['field' => 'name', 'error' => 'Обязательное поле'];
        }

        if (!isset($data['email']) || !preg_match('!^[^@]+@[^@]+\.[^@]+$!', $data['email'])) {
            $errors[] = ['field' => 'email', 'error' => 'Нужно указать действительный e-mail'];
        }

        if (!isset($data['password']) || strlen($data['password']) < 6 || strlen($data['password']) > 72) {
            $errors[] = ['field' => 'password', 'error' => 'Пароль должен быть от 6 до 72 символов'];
        }

        return $errors;
    }

    public static function unauth() {
        unset($_SESSION['user']);
    }

    public static function update($options, $criteria = []) {
        global $CONNECTION;

        $query = "UPDATE user SET updated_at = CURRENT_TIMESTAMP";

        if ($criteria != []) {
            foreach ($criteria as $field => $value) {
                $query .= ", $field = '$value'";
            }  
        }

        $query .= " WHERE $options";

        $stmt = $CONNECTION->prepare($query);
        $stmt->execute();

        return $query;
    }
}
