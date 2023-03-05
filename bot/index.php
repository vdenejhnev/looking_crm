<?
	require_once ($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');
	require_once ($_SERVER['DOCUMENT_ROOT'] . '/settings.php');
	require_once ($_SERVER['DOCUMENT_ROOT'] . '/functions.php');
	require_once ($_SERVER['DOCUMENT_ROOT'] . '/bot/send_data.php');

	$data = json_decode(file_get_contents('php://input'), true);
	file_put_contents('data.txt', '$data: ' . print_r($data, 1) . "\n", FILE_APPEND);

	$data = $data['callback_query'] ? $data['callback_query'] : $data['message'];

	define('TOKEN', '5756112416:AAE9UKhfowIw5LlWLg043ndV9hRpxM3kC2Q');

	$message = mb_strtolower(($data['text'] ? $data['text'] : $data['data']), 'utf-8');
	
	/*$send_data = [
		'text' => $message,
		'reply_markup' => [
			'resize_keyboard' => true,
			'keyboard' => [
				[
					['text' => 'Кнопка1'],
					['text' => 'Кнопка2']
				]
			]
		]
	];*/
	
	//$send_data['chat_id'] = $data['chat']['id'];
	//print_r(set_lead_email(['lead_id', 'value'], [4207, 'lead8956856@example.com']));
	//print_r(set_lead_data(['name', 'status'], ['Вячеслав', 'new'], '1002298082'));
	$chat_id = $data['chat']['id'];
	$command = set_command($message, $chat_id);
	check_command($command, $chat_id, $message);

	function set_command($message, $chat_id){
		if (get_dealer($chat_id) == false) {
			if ($message == '/start') {
				return 'start';
			} else if (strlen($message) == 5 && is_numeric($message)) {
				if (find_dealer($message) != false) {
					return 'auth_complete';
				} else {
					return 'auth_failed';
				}
			} else {
				return 'auth_failed';
			}
		} else {
			switch(get_state($chat_id)) {
				case 'main_menu': 
					if ($message == 'добавить лид') {
						return 'add_inn';
					} else {
						return 'main_menu';
					}
					break;
				case 'add_lead':
					if ($message == 'без инн') {
						return 'add_phone';
					} else if (validate('inn', $message) == true) {
						return 'add_phone';
					} else {
						return 'error_value';
					}
					break;
				case 'add_phone':
					if ($message == 'без телефона') {
						return 'add_email';
					} else if (validate('phone', $message) == true) {
						return 'add_email';
					} else {
						return 'error_value';
					}
					break;
				case 'add_email':
					if ($message == 'без эл.почты') {
						return 'add_name';
					} else if (validate('email', $message) == true) {
						return 'add_name';
					} else {
						return 'error_value';
					}
					break;
				case 'add_name':
					if ($message == 'пропустить') {
						return 'get_lead_intersections';
					} else if (validate('name', $message) == true) {
						return 'get_lead_intersections';
					} else {
						return 'error_value';
					}
					break;
				default:
					return 'default';
					break;
			}	
		}
	}

	function check_command($command, $chat_id, $message) {

		switch ($command) {

			case 'start':
				send('sendMessage', get_send_data('auth'));
				break;

			case 'auth_failed': 
				send('sendMessage', get_send_data('auth_failed'));
				send('sendMessage', get_send_data('auth'));
				break;

			case 'auth_complete':
				$dealer = find_dealer($message);
				set_chat_id($message, $chat_id);
				send('sendMessage', get_send_data('auth_complete', ['name' => $dealer['name']]));
				send('sendMessage', get_send_data('main_menu'));
				set_state('main_menu', $chat_id);
				break;

			case 'main_menu':
				send('sendMessage', get_send_data('main_menu'));
				set_state('main_menu', $chat_id);
				break;

			case 'add_inn':
				$dealer = find_dealer($chat_id);
				set_lead_data(['user_id', 'chat_id'], [$dealer['user_id'], $chat_id]);
				send('sendMessage', get_send_data('add_inn'));
				set_state('add_lead', $chat_id);
				break;

			case 'add_phone':
				if ($message == 'без инн') {
					set_lead_data('inn', '', $chat_id);
					send('sendMessage', get_send_data('add_phone'));
					set_state('add_phone', $chat_id);
				} else {
					$company = get_company($message);
					if ($company != false) {
						set_lead_data('inn', $message, $chat_id);
						set_lead_data('inn_added_at', date('Y-m-d H:i:s'), $chat_id);
						set_lead_data('company_name', $company, $chat_id);
						send('sendMessage', get_send_data('find_company', ['name_company' => $company]));
						send('sendMessage', get_send_data('add_phone'));
						set_state('add_phone', $chat_id);
					} else {
						send('sendMessage', get_send_data('undefined_company'));
						send('sendMessage', get_send_data('add_inn'));
					}	
				}
				break; 

			case 'add_email':
				$lead = get_lead($chat_id);
				if ($message == 'без телефона') {
					set_lead_phone(['lead_id', 'value'], [$lead['id'], '']);
				} else {
					set_lead_phone(['lead_id', 'value'], [$lead['id'], format_phone($message)]);
				}
				send('sendMessage', get_send_data('add_email'));
				set_state('add_email', $chat_id);
				break;

			case 'add_name':
				$lead = get_lead($chat_id);
				if ($message == 'без эл.почты') {
					set_lead_email(['lead_id', 'value'], [$lead['id'], '']);

					if ( get_lead($chat_id, ['lead_phone'])['inn'] == '' && get_lead($chat_id, ['lead_phone'])['value'] == '') {
						delete_lead(get_lead($chat_id)['id']);
						send('sendMessage', get_send_data('add_lead_failed'));
						send('sendMessage', get_send_data('return_main_menu'));
						set_state('main_menu', $chat_id);
						break;
					}
				} else {
					set_lead_email(['lead_id', 'value'], [$lead['id'], $message]);
				}
				send('sendMessage', get_send_data('add_name'));
				set_state('add_name', $chat_id);
				break;

			case 'get_lead_intersections':
				$lead = get_lead($chat_id);
				$dealer = get_dealer($chat_id);
				$status = get_lead_status($lead['inn'], [get_lead_phone($lead['id'])], [get_lead_email($lead['id'])]);

				if ($message == 'пропустить') {
					set_lead_data(['name', 'status', 'chat_id'], ['', $status, ''], $chat_id);
				} else {
					set_lead_data(['name', 'status', 'chat_id'], [name_format($message), $status, ''], $chat_id);
				}
				send('sendMessage', get_send_data('add_lead_complete', ['lead_id' => lead_id_format($lead['id']), 'lead_status' => get_lead_status_full($status)]));

				set_intersections($dealer['user_id'], $lead['id'], $lead['inn'], [get_lead_phone($lead['id'])], [get_lead_email($lead['id'])]);
				$intersections = get_intersections($lead['id']);
				send('sendMessage', get_send_data('lead_intersections', ['intersections_num' => count($intersections)]));

				foreach ($intersections as $key => $intersection) {
					$recurring_lead = get_lead_with_dealer($intersection['recurring_lead']);
					send('sendMessage', get_send_data('lead_intersection', ['intersection' => $key + 1, 'lead_id' => lead_id_format($intersection['lead']), 'recurring_lead_id' => lead_id_format($recurring_lead['id']), 'recurring_lead_date' => $recurring_lead['created_at'], 'intersection_fields' => $intersection['fields'], 'recurring_lead_name' => $recurring_lead['name'], 'recurring_lead_phone' => $recurring_lead['phone']]));
				}
				
				send('sendMessage', get_send_data('return_main_menu'));
				set_state('main_menu', $chat_id);
				break;

			case 'error_value':
				send('sendMessage', get_send_data('error_value'));
				break;
			
			default:
				send('sendMessage', get_send_data('undefined_command'));
				break;
		}
	}

	function validate($param, $value) {
		switch ($param) {
			case 'inn':
				if (strlen($value) == 10 && is_numeric($value)) {
					return true;
				} else {
					return false;
				}
				break;
			case 'phone':
				if (strlen($value) == 12 && is_numeric(substr($value, 1))) {
					return true;
				} else {
					return false;
				}
			case 'email':
				if (stripos($value, '@') != false) {
					return true;
				} else {
					return false;
				}
			case 'name':
				for($item = 0; $item < strlen($value); $item++) {
					if (is_numeric($value[$item])) {
						return false;
					}
				}
				return true;
			default:
				return false;
				break;
		}

	}

	function send($method, $data, $headers = []) {
		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_POST => 1,
			CURLOPT_HEADER => 0,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => 'https://api.telegram.org/bot' . TOKEN . '/' . $method,
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => array_merge(array("Content-Type: application/json"), $headers)
		]);

		$result = curl_exec($curl);
		curl_close($curl);
		return (json_decode($result, 1) ? json_decode($result, 1) : $result);
	}

	function name_format($name) {
		if (strlen($name) > 2) {
			$name_str = explode(' ', $name);

			$name = '';

			foreach ($name_str as $name_item) {
				$name .= substr(mb_strtoupper($name_item), 0, 2) . substr($name_item, 2) . ' ';
			}
			return $name;
		} else {
			return mb_strtoupper($name);
		}
	}

	function lead_id_format($lead_id) {
		return sprintf("%06s", $lead_id);
	}

	function get_dealer($chat_id) {
		global $CONNECTION;
		$query = "SELECT * FROM dealer INNER JOIN user ON dealer.user_id = user.id WHERE tg_notification = '$chat_id'";
   	 	$stmt = $CONNECTION->prepare($query);
    	$stmt->execute();

    	$dealer = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    	if (count($dealer) == 1) {
    		return $dealer[0];
    	} else {
    		return false;
    	}
	}

	function find_dealer($auth_code) {
		global $CONNECTION;
		$query = "SELECT * FROM dealer INNER JOIN user ON dealer.user_id = user.id WHERE tg_notification = '$auth_code'";
   	 	$stmt = $CONNECTION->prepare($query);
    	$stmt->execute();

    	$dealer = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    	if (count($dealer) == 1) {
    		return $dealer[0];
    	} else {
    		return false;
    	}
	}

	function set_chat_id($auth_code, $chat_id) {
		global $CONNECTION;
		$query = "UPDATE dealer SET tg_notification = '$chat_id' WHERE tg_notification = '$auth_code'";
   	 	$stmt = $CONNECTION->prepare($query);
    	$stmt->execute();
	}

	function set_state($state, $chat_id) {
		global $CONNECTION;
		$query = "UPDATE dealer SET tg_state = '$state' WHERE tg_notification = '$chat_id'";
   	 	$stmt = $CONNECTION->prepare($query);
    	$stmt->execute();
	}

	function get_state($chat_id) {
		global $CONNECTION;
		$query = "SELECT tg_state FROM dealer WHERE tg_notification = '$chat_id'";
   	 	$stmt = $CONNECTION->prepare($query);
    	$stmt->execute();

    	return $stmt->get_result()->fetch_all(MYSQLI_ASSOC)[0]['tg_state'];
	}

	function get_send_data($data_name, $params = []) {
		global $chat_id;
		global $send_data;

		$send_data_get = $send_data[$data_name];
		$send_data_get['chat_id'] = $chat_id;

		foreach ($params as $key => $param) {
			$send_data_get['text'] = str_replace('%' . $key . '%', $param, $send_data_get['text']);
		}

		return $send_data_get;
	}

	function get_lead($chat_id, $tables = []) {
		global $CONNECTION;

		$query = "SELECT * FROM lead ";

		foreach ($tables as $table) {
			$query .= "INNER JOIN " . $table . " ON lead.id = " . $table . ".lead_id ";
		}

		$query .= "WHERE lead.chat_id = " . $chat_id;

    	$stmt = $CONNECTION->prepare($query);
    	$stmt->execute();

    	return $stmt->get_result()->fetch_all(MYSQLI_ASSOC)[0];
	}

	function get_lead_with_dealer($lead_id) {
		global $CONNECTION;

        $query = 'SELECT * FROM lead LEFT JOIN dealer ON (dealer.user_id = lead.user_id) WHERE lead.id = ' . $lead_id;
        $stmt = $CONNECTION->prepare($query);
        $stmt->execute();

        $lead = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if (count($lead) == 0) {
            return false;
        } else {
        	$lead = $lead[0];
        	$query = "SELECT value FROM lead_phone WHERE lead_id = " . $lead_id;
        	$stmt = $CONNECTION->prepare($query);
        	$stmt->execute();
        	$lead['phones'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
	
        	$query = "SELECT value FROM lead_email WHERE lead_id = " . $lead_id;
        	$stmt = $CONNECTION->prepare($query);
        	$stmt->execute();
        	$lead['emails'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        	return $lead;
        }       
	}

	function get_company($inn) {
		$token = "04e24a48faf5e9dc41b1179f6e22b332e4cccbaa";

		$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, "https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/party");
    	curl_setopt($ch, CURLOPT_POST, 1);
    	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', "Accept: application/json", "Authorization: Token " . $token . ""));
    	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['query' => $inn]));
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	$output = curl_exec($ch);
    	curl_close($ch);

    	if (json_decode($output)->suggestions[0]->value != '') {
    		$company_string = explode(' ', json_decode($output)->suggestions[0]->value);

    		if ((mb_strtolower($company_string[0]) == 'индивидуальный') && (mb_strtolower($company_string[1]) == 'предприниматель')) {
    			$company_name = 'ИП ';

    			foreach ($company_string as $key => $company_item) {
    				if ($key > 1) {
    					$company_name .= $company_item . ' ';
    				}
    			}
    		} else if ((mb_strtolower($company_string[0]) == 'общество') && (mb_strtolower($company_string[3]) == 'ответственностью')) {
    			$company_name = 'ООО ';

    			foreach ($company_string as $key => $company_item) {
    				if ($key > 3) {
    					$company_name .= $company_item . ' ';
    				}
    			}
    		} else {
	   			foreach ($company_string as $key => $company_item) {
    				$company_name .= $company_item  . ' ';
    			}
    		}
    		return $company_name;
    	} else {
    		return false;
    	}
	}

	function get_lead_phone($lead_id) {
		global $CONNECTION;

		$query = "SELECT * FROM lead_phone WHERE lead_id = " . $lead_id;

		$stmt = $CONNECTION->prepare($query);
    	$stmt->execute();

    	return $stmt->get_result()->fetch_all(MYSQLI_ASSOC)[0]['value'];
	}

	function get_lead_email($lead_id) {
		global $CONNECTION;

		$query = "SELECT * FROM lead_email WHERE lead_id = " . $lead_id;

		$stmt = $CONNECTION->prepare($query);
    	$stmt->execute();

    	return $stmt->get_result()->fetch_all(MYSQLI_ASSOC)[0]['value'];
	}
	
	function set_lead_data($field, $data, $criterion = '') {
		global $CONNECTION;

		if ($criterion == '') {
			if (is_array($field) && is_array($data)) {
				$query = "INSERT INTO lead (";
				foreach($field as $key => $field_item) {
					if ($key != 0) {
						$query .= ", ";
					}
					$query .= $field_item;
				}

				$query .= ") VALUES (";

				foreach($data as $key => $data_item) {
					if ($key != 0) {
						$query .= ", ";
					}

					$query .= "'" . $data_item . "'";
				}

				$query .= ")";
			} else {
				$query = "INSERT INTO lead (" . $field . ") VALUES (" . $data . ")";
			}	
		} else {
			if (is_array($field) && is_array($data)) {
				$query = "UPDATE lead SET ";

				foreach($field as $key => $field_item) {
					if ($key != 0) {
						$query .= ", ";
					}

					$query .= $field_item . " = '" . $data[$key] . "'";
				}

				$query .= " WHERE chat_id = " . $criterion;
			} else {
				$query = "UPDATE lead SET " . $field . " = '" . $data . "' WHERE chat_id = " . $criterion;
			}
		}
		
   	 	$stmt = $CONNECTION->prepare($query);
    	$stmt->execute();
	}

	function format_phone($phone) {
		$phone = sprintf("%s (%s) %s-%s-%s",
            substr($phone, 0, 2),
            substr($phone, 2, 3),
            substr($phone, 5, 3),
            substr($phone, 8, 2),
            substr($phone, 10)
        );

        return $phone;
	}

	function set_lead_phone($field, $data) {
		global $CONNECTION;

		if (is_array($field) && is_array($data)) {
			$query = "INSERT INTO lead_phone (";
			foreach($field as $key => $field_item) {
				if ($key != 0) {
					$query .= ", ";
				}
				$query .= $field_item;
			}

			$query .= ") VALUES (";

			foreach($data as $key => $data_item) {
				if ($key != 0) {
					$query .= ", ";
				}

				$query .= "'" . $data_item . "'";	
			}

			$query .= ")";
		} else {
			$query = "INSERT INTO lead_phone (" . $field . ") VALUES ('" . $data . "')";
		}
		
		$stmt = $CONNECTION->prepare($query);
    	$stmt->execute();  
	}

	function set_lead_email($field, $data) {
		global $CONNECTION;

		if (is_array($field) && is_array($data)) {
			$query = "INSERT INTO lead_email (";
			foreach($field as $key => $field_item) {
				if ($key != 0) {
					$query .= ", ";
				}
				$query .= $field_item;
			}

			$query .= ") VALUES (";

			foreach($data as $key => $data_item) {
				if ($key != 0) {
					$query .= ", ";
				}

				$query .= "'" . $data_item . "'";			
			}

			$query .= ")";
		} else {
			$query = "INSERT INTO lead_email (" . $field . ") VALUES ('" . $data . "')";
		}
		
		$stmt = $CONNECTION->prepare($query);
    	$stmt->execute(); 
	}

	function delete_lead($lead_id) {
		global $CONNECTION;

		$query = "DELETE FROM lead WHERE id = '$lead_id'";
		$stmt = $CONNECTION->prepare($query);
    	$stmt->execute();  

    	$query = "DELETE FROM lead_phone WHERE lead_id = '$lead_id'";
		$stmt = $CONNECTION->prepare($query);
    	$stmt->execute();  

    	$query = "DELETE FROM lead_email WHERE lead_id = '$lead_id'";
		$stmt = $CONNECTION->prepare($query);
    	$stmt->execute();  
	}

	function get_lead_status($inn, $phones_array, $emails_array) {	
		global $CONNECTION;

        $query = "SELECT * FROM lead WHERE inn = '$inn'";

        $stmt = $CONNECTION->prepare($query);
        $stmt->execute();

        $array_rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); 

        if (count($array_rows) == 0) {
            $phone_rows = [];
            $email_rows = [];
            foreach ($phones_array as $phone) {
                $query = "SELECT * FROM lead_phone WHERE value LIKE '$phone'";
                $stmt =  $CONNECTION->prepare($query);
                $stmt->execute();
                $phone_rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); 

                if ($phone_rows != []) {
                    break;
                } 
            }

            foreach ($emails_array as $email) {
                $query = "SELECT * FROM lead_email WHERE value LIKE '$email'";
                $stmt =  $CONNECTION->prepare($query);
                $stmt->execute();
                $email_rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); 

                if ($email_rows != []) {
                    break;
                } 
            }

            if ((count($phone_rows) == 0) && (count($email_rows) == 0)) {
                return 'single';
            } else {
                return 'first';
            }
            
        } else if (count($array_rows) == 1) {
            return 'second';
        } else {
            return 'third';
        }
    }

    function get_lead_status_full($status) {
    	$STATUSES = [
        	'new' => 'Переговоры',
        	'single' => 'Единственный',
        	'first' => 'Первый',
        	'second' => 'Второй',
        	'third' => '3-й и более',
        	'trash' => 'В топке',
        	'deleted' => 'Удалён',
        	'done' => 'Завершено',
    	];

    	return $STATUSES[$status];
    }

    function get_intersections ($lead_id) {
    	global $CONNECTION;

        $stmt = $CONNECTION->prepare('SELECT * FROM notification WHERE lead = ' . $lead_id . ' AND reading = 0 ORDER BY id DESC');
        $stmt->execute();

        $intersections = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        return $intersections;
    }

    function set_intersections ($dealer_id, $lead_id, $inn, $phones_array, $emails_array) {
    	global $CONNECTION;

        $query = "SELECT * FROM lead WHERE inn = '$inn' AND status <> 'deleted' AND id <> '$lead_id'";

        $intersections = [];

        $stmt = $CONNECTION->prepare($query);
        $stmt->execute();
        $lead_list = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); 

        if (count($lead_list) > 0) {
            foreach ($lead_list as $lead) {
                $intersections[$lead['id']] = 'ИНН';
            }
        }

        if ($phones_array != []) {
            foreach ($phones_array as $phone) {
                if ($phone != '') {
                    $query = "SELECT * FROM lead_phone WHERE value LIKE '$phone' AND lead_id <> '$lead_id'";
                    $stmt = $CONNECTION->prepare($query);
                    $stmt->execute();
                    $lead_list = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); 

                    if (count($lead_list) > 0) {
                        foreach ($lead_list as $lead) {
                            if (array_key_exists($lead['lead_id'], $intersections)) {
                                $intersections[$lead['lead_id']] =  $intersections[$lead['lead_id']] . ', Телефон';
                            } else {
                                $intersections[$lead['lead_id']] = 'Телефон';
                            }
                        }
                    }
                }
            }
        }


        if ($emails_array != []) {
            foreach ($emails_array as $email) {
                if ($email != '') {
                    $query = "SELECT * FROM lead_email WHERE value LIKE '$email' AND lead_id <> '$lead_id'";
                    $stmt = $CONNECTION->prepare($query);
                    $stmt->execute();
                    $lead_list = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);  
                
                    if (count($lead_list) > 0) {
                        foreach ($lead_list as $lead) {
                            if (array_key_exists($lead['lead_id'], $intersections)) {
                                $intersections[$lead['lead_id']] =  $intersections[$lead['lead_id']] . ', Email';
                            } else {
                                $intersections[$lead['lead_id']] = 'Email';
                            }
                        }
                    }
                }
            }
        }

        foreach ($intersections as $curr_lead_id => $intersection) {
            add_intersections($dealer_id, $lead_id, $curr_lead_id, $intersection);
        }
    }

    function add_intersections($dealer, $lead, $recurring_lead, $fields) {
        global $CONNECTION;

        $stmt = $CONNECTION->prepare("INSERT INTO notification (dealer, lead, recurring_lead, type, fields) VALUES ('$dealer', '$lead', '$recurring_lead', 'coincidence', '$fields')");
        $stmt->execute();

        /*$dealer_data = Dealer::findOne(['user_id' => $dealer]); 

        if ($dealer_data->email_notification == 1 ){
            $recurring_lead_data = Lead::findOne(['id' => $recurring_lead]);

            
            self::send_mail($dealer_data->email, 'Добавленный лид #' . $lead . ' пересекается c добавленным ранее лидом', "Добавленный лид #" . $lead . " совпадает с другим\n#" . $recurring_lead . " от " . $recurring_lead_data->created_at . " " . $recurring_lead_data->name . " " . $recurring_lead_data->phone . "\nПересечение: " . $fields);
        }

        if ($dealer_data->tg_notification_send == 1 ){
            $recurring_lead_data = Lead::findOne(['id' => $recurring_lead]);


            send('sendMessage', ['text' => "Добавленный лид #" . $lead . " совпадает с другим\n#" . $recurring_lead . " от " . $recurring_lead_data->created_at . " " . $recurring_lead_data->name . " " . $recurring_lead_data->phone . "\nПересечение: " . $fields, 'chat_id' => $dealer_data->tg_notification]);
        }*/
        

        $stmt = $CONNECTION->prepare("SELECT user_id FROM lead WHERE id = '$recurring_lead'");
        $stmt->execute();
        $dealer = $stmt->get_result()->fetch_all(MYSQLI_ASSOC)[0]['user_id'];

        $stmt = $CONNECTION->prepare("INSERT INTO notification (dealer, recurring_lead, lead, type, fields) VALUES ('$dealer', '$lead', '$recurring_lead', 'reference', '$fields')");
        $stmt->execute();

        /*$dealer_data = Dealer::findOne(['user_id' => $dealer]); 

        if ($dealer_data->email_notification == 1 ){
            $lead_data = Lead::findOne(['id' => $lead]);

            self::send_mail($dealer_data->email, 'Ваш лид #' . $recurring_lead . ' упоминается в другом лиде', "Ваш лид #" . $recurring_lead . " упоминается в другом лиде\n#" . $lead . " от " . $lead_data->created_at . " " . $lead_data->name . " " . $lead_data->phone . "\nПересечение: " . $fields);
        }

        if ($dealer_data->tg_notification_send == 1 ){
            $lead_data = Lead::findOne(['id' => $lead]);

            send('sendMessage', ['text' => "Ваш лид #" . $recurring_lead . " упоминается в другом лиде\n#" . $lead . " от " . $lead_data->created_at . " " . $lead_data->name . " " . $lead_data->phone . "\nПересечение: " . $fields, 'chat_id' => $dealer_data->tg_notification]);
        } 

        return $fields;*/
    }