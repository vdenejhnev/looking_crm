<?
	$send_data = [
		'auth' => [
			'text' => 'Введите пин-код для авторизации',
		],
		'auth_failed' => [
			'text' => 'Попытка авторизации не удалась',
		],
		'auth_complete' => [
			'text' => '%name%' . ', вы успешно авторизованы',
		],
		'main_menu' => [
			'text' => 'Для добавления нового лида нажмите на кнопку "Добавить лид"',
			'reply_markup' => [
				'resize_keyboard' => true,
					'keyboard' => [
					[
						['text' => 'Добавить лид'],
					]
				]
			]
		],
		'add_inn' => [
			'text' => 'Введите ИНН организации',
			'reply_markup' => [
				'resize_keyboard' => true,
					'keyboard' => [
					[
						['text' => 'Без ИНН'],
					]
				]
			]
		],
		'add_phone' => [
			'text' => 'Введите номер телефона в формате +7XXXXXXXXXX',
			'reply_markup' => [
				'resize_keyboard' => true,
					'keyboard' => [
					[
						['text' => 'Без телефона'],
					]
				]
			]
		],
		'add_email' => [
			'text' => 'Введите эл.почту',
			'reply_markup' => [
				'resize_keyboard' => true,
					'keyboard' => [
					[
						['text' => 'Без эл.почты'],
					]
				]
			]
		],
		'add_name' => [
			'text' => 'Укажите имя клиента',
			'reply_markup' => [
				'resize_keyboard' => true,
					'keyboard' => [
					[
						['text' => 'Пропустить'],
					]
				]
			]
		],
		'add_lead_complete' => [
			'text' => 'Добавлен лид #%lead_id%' . "\n" . 'Статус: %lead_status%',
		],
		'lead_intersections' => [
			'text' => 'Пересечения(%intersections_num%):',
		],
		'lead_intersection' => [
			'text' => '%intersection%. Добавленный лид #%lead_id% совпадает с другим. #%recurring_lead_id% от %recurring_lead_date% (%intersection_fields%). %recurring_lead_name% %recurring_lead_phone%. ',
		],
		'return_main_menu' => [
			'text' => 'Вернуться в меню',
			'reply_markup' => [
				'resize_keyboard' => true,
					'keyboard' => [
					[
						['text' => 'Вернуться'],
					]
				]
			]
		],
		'find_company' => [
			'text' => 'Найдена компания: ' . '%name_company%',
		],
		'undefined_company' => [
			'text' => 'Не найдено компании с таким ИНН',
		],
		'add_lead_failed' => [
			'text' => 'Лид не добавлен',
		],
		'error_value' => [
			'text' => 'Неправильное значение',
		],
		'undefined_command' => [
			'text' => 'Неизвестная команда',
		]
	];