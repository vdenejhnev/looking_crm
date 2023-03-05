<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../settings.php';
require_once __DIR__ . '/../../functions.php';

Form\check_ajax();

$response = [];

switch ($_POST['action']) {
    case 'login':
        try {
            $USER = Model\User::auth();
        } catch (\Exception $e) {}

        if (is_object($USER)) {
            $response['success'] = true;
        } elseif (is_array($USER)) {
            $response['errors'] = $USER;
        } else {
            $response['errors'] = [['error' => 'Авторизация не удалась']];
        }
    break;

    default:
        http_response_code(400);
        $response['errors'] = [['error' => "Неизвестное действие: $_POST[action]"]];
    break;
}

echo json_encode($response);
