<?php

namespace Form;

use Model\User;

// Проверяет правильность входных данных AJAX-запросов.
// Все AJAX-запросы должны приходить POST'ом и иметь свойство action.
// Параметр $permission определяет уровень требуемых прав - admin/user/false
function check_ajax($permission = null) {
    global $USER;

    $USER = User::get_current();
    if ($permission && !$USER) {
        http_response_code(401);
        echo json_encode(['error' => 'Требуется авторизация']);
        die;
    }

    if ($permission === 'admin' && $USER->role !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Недостаточно прав']);
        die;
    }

    $method = strtolower($_SERVER['REQUEST_METHOD']);
    $action = $_POST['action'] ?? null;
    if ( ! ($method === 'post' && !empty($action))) {
        http_response_code(400);
        echo json_encode(['error' => 'AJAX-запросы отправляются POST-ом с указанием action']);
        die;
    }
}
