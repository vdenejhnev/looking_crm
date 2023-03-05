<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../settings.php';
require_once __DIR__ . '/../../functions.php';

Form\check_ajax();

$result = [];

switch ($_POST['action']) {
    case 'change_channel':
        if (isset($_POST['dealer_id'], $_POST['channel']) && $_POST['dealer_id'] && $_POST['channel']) {    
            $result = Model\Notification::change_channel($_POST['dealer_id'], $_POST['channel']);
        }
    break;

    case 'get_status_channel':
        if (isset($_POST['dealer_id'], $_POST['channel']) && $_POST['dealer_id'] && $_POST['channel']) {    
            $result = Model\Notification::get_status_channel($_POST['dealer_id'], $_POST['channel']);
        }
    break;

    case 'close_notification':
        if (isset($_POST['id']) && $_POST['id']) {
            $result = Model\Notification::close_notification($_POST['id']);
        }    
    break;

    case 'delete_notification':
        if (isset($_POST['options']) && $_POST['options'] != []) {
            $result = Model\Notification::delete_notification($_POST['options']);
        }    
    break;

    default:
        http_response_code(400);
        $response['errors'][] = ['error' => "Неизвестный запрос: $_POST[action]"];
    break;
}

echo json_encode($result);
