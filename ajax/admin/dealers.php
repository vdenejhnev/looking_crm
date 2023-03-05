<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../settings.php';
require_once __DIR__ . '/../../functions.php';

Form\check_ajax('admin');

$result = [];

switch ($_POST['action']) {
    case 'get-dealer':
        $dealer = Model\Dealer::findOne(['user.id' => $_POST['id']]);
        if (!$dealer) {
            $result['errors'][] = ['error' => 'Дилер не найден'];
        }

        $result['dealer'] = $dealer;
    break;

    case 'list-dealers':
        $filter = [];
        $options = [];

        if (isset($_POST['filter_name']) && $_POST['filter_name']) {
            $filter['user.name'] = ['like', "%$_POST[filter_name]%"];
        }
        if (isset($_POST['filter_company']) && $_POST['filter_company']) {
            $filter['company_id'] = $_POST['filter_company'];
        }
        if (isset($_POST['filter_city']) && $_POST['filter_city']) {
            $filter['city.name'] = ['like', "%$_POST[filter_city]%"];
        }
        if (isset($_POST['filter_phone']) && $_POST['filter_phone']) {
            $phone = $_POST['filter_phone'];

            switch($phone[0]) {
                case '+':
                    $phone = sprintf("%s (%s) %s-%s-%s",
                        substr($phone, 0, 2),
                        substr($phone, 2, 3),
                        substr($phone, 5, 3),
                        substr($phone, 8, 2),
                        substr($phone, 10)
                    );

                    if(strlen($_POST['filter_phone']) < 3) {
                        $_POST['filter_phone'] = substr($phone, 0, strlen($_POST['filter_phone']));
                    } else if (strlen($_POST['filter_phone']) < 6) {
                        $_POST['filter_phone'] = substr($phone, 0, strlen($_POST['filter_phone']) + 3);
                    } else if (strlen($_POST['filter_phone']) < 9) {
                        $_POST['filter_phone'] = substr($phone, 0, strlen($_POST['filter_phone']) + 4);
                    } else if (strlen($_POST['filter_phone']) < 11) {
                        $_POST['filter_phone'] = substr($phone, 0, strlen($_POST['filter_phone']) + 5);
                    } else if (strlen($_POST['filter_phone']) < 13) {
                        $_POST['filter_phone'] = substr($phone, 0, strlen($_POST['filter_phone']) + 6);
                    }

                    break;
                case '7':
                    $phone = sprintf("%s (%s) %s-%s-%s",
                        substr($phone, 0, 1),
                        substr($phone, 1, 3),
                        substr($phone, 4, 3),
                        substr($phone, 7, 2),
                        substr($phone, 9)
                    );

                    if(strlen($_POST['filter_phone']) < 2) {
                        $_POST['filter_phone'] = substr($phone, 0, strlen($_POST['filter_phone']));
                    } else if (strlen($_POST['filter_phone']) < 5) {
                        $_POST['filter_phone'] = substr($phone, 0, strlen($_POST['filter_phone']) + 2);
                    } else if (strlen($_POST['filter_phone']) < 8) {
                        $_POST['filter_phone'] = substr($phone, 0, strlen($_POST['filter_phone']) + 4);
                    } else if (strlen($_POST['filter_phone']) < 10) {
                        $_POST['filter_phone'] = substr($phone, 0, strlen($_POST['filter_phone']) + 5);
                    } else if (strlen($_POST['filter_phone']) < 12) {
                        $_POST['filter_phone'] = substr($phone, 0, strlen($_POST['filter_phone']) + 6);
                    }

                    break;
                case '8':
                    $phone = sprintf("%s (%s) %s-%s-%s",
                        '7',
                        substr($phone, 1, 3),
                        substr($phone, 4, 3),
                        substr($phone, 7, 2),
                        substr($phone, 9)
                    );

                    if(strlen($_POST['filter_phone']) < 2) {
                        $_POST['filter_phone'] = substr($phone, 0, strlen($_POST['filter_phone']));
                    } else if (strlen($_POST['filter_phone']) < 5) {
                        $_POST['filter_phone'] = substr($phone, 0, strlen($_POST['filter_phone']) + 2);
                    } else if (strlen($_POST['filter_phone']) < 8) {
                        $_POST['filter_phone'] = substr($phone, 0, strlen($_POST['filter_phone']) + 4);
                    } else if (strlen($_POST['filter_phone']) < 10) {
                        $_POST['filter_phone'] = substr($phone, 0, strlen($_POST['filter_phone']) + 5);
                    } else if (strlen($_POST['filter_phone']) < 12) {
                        $_POST['filter_phone'] = substr($phone, 0, strlen($_POST['filter_phone']) + 6);
                    }

                    break;
                case '9':
                    $phone = sprintf("(%s) %s-%s-%s",
                        substr($phone, 0, 3),
                        substr($phone, 3, 3),
                        substr($phone, 6, 2),
                        substr($phone, 8)
                    );

                    if(strlen($_POST['filter_phone']) < 4) {
                        $_POST['filter_phone'] = substr($phone, 0, strlen($_POST['filter_phone'])+1);
                    } else if (strlen($_POST['filter_phone']) < 7) {
                        $_POST['filter_phone'] = substr($phone, 0, strlen($_POST['filter_phone']) + 3);
                    } else if (strlen($_POST['filter_phone']) < 9) {
                        $_POST['filter_phone'] = substr($phone, 0, strlen($_POST['filter_phone']) + 4);
                    } else if (strlen($_POST['filter_phone']) < 11) {
                        $_POST['filter_phone'] = substr($phone, 0, strlen($_POST['filter_phone']) + 5);
                    } 

                    break;
                default:
                    break;
            }

            //$result = $_POST['filter_phone'];
            $filter['dealer.phone'] = ['like', "%$_POST[filter_phone]%"];
        }

        if (isset($_POST['filter_email']) && $_POST['filter_email']) {
            $filter['user.email'] = ['like', "%$_POST[filter_email]%"];
        }

        $count = Model\Dealer::get_count($filter, $options);
        $limit = max(1, min($_POST['limit'] ?? 3, 100));
        $pages_count = intdiv($count, $limit) + ($count % $limit ? 1 : 0);
        $page = max(1, min($_POST['page'] ?? 1, $pages_count));

        $leads_between = [null, null];
        if (isset($_POST['filter_leads_from']) && $_POST['filter_leads_from']) {
            $_POST['filter_leads_from'] = date('d.m.Y', strtotime($_POST['filter_leads_from']));
            $from = explode('.', $_POST['filter_leads_from']);
            $leads_between[0] = "$from[2]-$from[1]-$from[0] 00:00:00";
        }
        if (isset($_POST['filter_leads_to']) && $_POST['filter_leads_to']) {
            $_POST['filter_leads_to'] = date('d.m.Y', strtotime($_POST['filter_leads_to']));
            $to = explode('.', $_POST['filter_leads_to']);
            $leads_between[1] = "$to[2]-$to[1]-$to[0] 23:59:59";
            //$filter['user.created_at'] = [['>=', $leads_between[0]], ['<=', $leads_between[1]]];
        }

        $options['order'] = 'user_id';

        $count = Model\Dealer::get_count($filter, $options);
        $limit = max(1, min($_POST['limit'] ?? 3, 100));
        $pages_count = intdiv($count, $limit) + ($count % $limit ? 1 : 0);
        $page = max(1, min($_POST['page'] ?? 1, $pages_count));

        $options['offset'] =$limit * ($page - 1);
        $options['limit'] =$limit;
        $options['dealer_leads_count'] = [$leads_between[0], $leads_between[1]];

        $dealers = Model\Dealer::find($filter, $options);
        $result['dealers'] = array_map(
            function($dealer) {return (array)$dealer;},
            $dealers
        );
        $result['pager'] = [
            'current' => $page,
            'pages' => $pages_count,
            'total' => $count,
        ];
    break;

    case 'save-dealer':
        try {
            $dealer = isset($_POST['id']) && $_POST['id']
                ? Model\Dealer::update($_POST)
                : Model\Dealer::create($_POST);            
            if (is_object($dealer)) {
                $result['dealer'] = (array)$dealer;
            } elseif (is_array($dealer)) {
                $result['errors'] = $dealer;
            } else {
                throw new \Exception('Дилер не был добавлен');
            }
        } catch(\Exception $e) {
            var_dump($e);
            $result['errors'][] = (array)$e;
        }
    break;

    case 'delete-dealer':
        if (isset($_POST['dealer_id']) && $_POST['dealer_id']) {
            Model\Dealer::delete($_POST['dealer_id']);
            $result = true;
        }
    break;

    case 'change_pass-dealer':
        if (isset($_POST['dealer_id']) && $_POST['dealer_id']) {
            $password = substr(md5(time()), 0, 8);
            $response = Model\User::update("id = " . $_POST['dealer_id'], ['password' => password_hash( $password, PASSWORD_BCRYPT)]);

            $mail_text = "Администратор изменил ваш пароль для входа \nНовый пароль: " . $password;

            App\Model::send_mail($_POST['dealer_email'], 'Новый пароль на сайте spectech', $mail_text);
            $result = $password;
        }
    break;

    case 'disable-dealer':
        if (isset($_POST['dealer_id']) && $_POST['dealer_id']) {
            $result = Model\Dealer::disable($_POST['dealer_id'], $_POST['enabled']);
            //$result = $_POST['enabled'];
        }
    break;

    default:
        http_response_code(400);
        $response['errors'][] = ['error' => "Неизвестный запрос: $_POST[action]"];
    break;
}

echo json_encode($result);
