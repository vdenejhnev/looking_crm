<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../settings.php';
require_once __DIR__ . '/../../functions.php';

Form\check_ajax();

$response = [];

switch ($_POST['action']) {
    case 'edit-profile':
    	if (isset($_POST['name']) && $_POST['name']) {
    		$response = Model\User::update("id = " . $_POST['id'], ['name' => $_POST['name']]);
    	}

    	if (isset($_POST['phone']) && $_POST['phone']) {
    		$filter['dealer.phone'] = ['like', "$_POST[phone]%"];
    		$dealer = Model\Dealer::find($filter);

    		if (count($dealer) == 0) {
    			$response = Model\Dealer::update_one("user_id = " . $_POST['id'], ['phone' => $_POST['phone']]);
    		} else {
    			$response['errors'][] = ['error' => "Пользователь с таким телефоном уже существует", 'field' => 'phone'];
    		}
    	}

    	if (isset($_POST['city']) && $_POST['city']) {
			$city = Model\City::get($_POST['city']);

			if (!$city) {
				$city = Model\City::create($_POST['city']);
			}

    		$response = Model\Dealer::update_one("user_id = " . $_POST['id'], ['city_id' => $city->id]);		
    	}

    	if (isset($_POST['email']) && $_POST['email']) {
    		$user = Model\User::find(['email' => $_POST['email'], 'role' => 'dealer']);
    		if ($user != []) {
    			$response['errors'][] = ['error' => "Пользователь с таким email уже существует", 'field' => 'email'];
    		} else {
    			$response = Model\User::update("id = " . $_POST['id'], ['email' => $_POST['email']]);
    		}
    	}

    	if (isset($_POST['new_pass']) && $_POST['new_pass'] != '') {
    		if (isset($_POST['new_pass_repeat']) && $_POST['new_pass_repeat']) {
    			if ($_POST['new_pass'] == $_POST['new_pass_repeat']) {
					if (isset($_POST['curr_pass']) && $_POST['curr_pass']) {
						$user = Model\User::find(['id' => $_POST['id'], 'role' => 'admin']);
						if (!password_verify($_POST['curr_pass'], $user[0]->password)) {
            				$response['errors'][] = ['error' => 'Неправильный пароль', 'field' => 'curr_pass'];
        				} else {
        					$password = password_hash( $_POST['new_pass'], PASSWORD_BCRYPT);
        					$response = Model\User::update("id = " . $_POST['id'], ['password' => $password]);
        				}
					} else {
						$response['errors'][] = ['error' => "Введите существующий пароль", 'field' => 'curr_pass'];
					}
				} else {
					$response['errors'][] = ['error' => "Пароли не совпадают", 'field' => 'new_pass_repeat'];
				}
    		} else {
    			$response['errors'][] = ['error' => "Повторите новый пароль", 'field' => 'new_pass_repeat'];
    		}
    	} else {
    		if (isset($_POST['new_pass_repeat'], $_POST['curr_pass']) && ($_POST['new_pass_repeat'] != '' || $_POST['curr_pass'] != '')) {
    			$response['errors'][] = ['error' => "Введите новый пароль", 'field' => 'new_pass'];
    		}
    	}
    break;

    default:
        http_response_code(400);
        $response['errors'][] = ['error' => "Неизвестный запрос: $_POST[action]"];
    break;
}

echo json_encode($response);
