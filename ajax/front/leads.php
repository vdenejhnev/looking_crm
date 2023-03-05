<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../settings.php';
require_once __DIR__ . '/../../functions.php';

Form\check_ajax('dealer');

$response = [];

switch ($_POST['action']) {
    case 'add-lead':
        $lead = Model\Lead::create($_POST);

        /*if (is_object($lead)) {
            $response['lead'] = $lead;
        } elseif (is_array($lead)) {
            $response['errors'] = $lead;
        } else {
            $response['errors'][] = ['error' => 'Произошла ошибка'];
        }*/

        $response = $lead;
    break;

    case 'get-lead':
        $lead = Model\Lead::findOne(['id' => $_POST['id']]);

        if (is_object($lead) || $lead === false) {
            $response['lead'] = $lead;
        } elseif (is_array($lead)) {
            $response['errors'] = $lead;
        } else {
            $response['errors'][] = ['error' => 'Произошла ошибка'];
        }
    break;

    case 'list-leads':
       // $response = Model\Lead::check_trash_status();
        
        $filter = [];
        $options = ['with' => ['dealer', 'dealer.company']];

        if (isset($_POST['filter_dealer_id']) && $_POST['filter_dealer_id']) {
            $filter['user.id'] = $_POST['filter_dealer_id'];
        }
        if (isset($_POST['filter_status']) && $_POST['filter_status']) {
            $filter['lead.status'] = $_POST['filter_status'];
        } else {
            $filter['lead.status'] = ['not', "deleted"];
        }
        if (isset($_POST['filter_id']) && $_POST['filter_id']){ 
            $filter['lead.id'] = $_POST['filter_id'];

            if (strpos($filter['lead.id'], '#') === 0) {
                $filter['lead.id'] = substr($filter['lead.id'], 1);
            }
        }
        if (isset($_POST['filter_inn']) && $_POST['filter_inn']) {
            $filter['lead.inn'] = ['like', "%$_POST[filter_inn]%"];
        }
        if (isset($_POST['filter_company_name']) && $_POST['filter_company_name']) {
            $filter['lead.company_name'] = ['like', "%$_POST[filter_company_name]%"];
        }
        if (isset($_POST['filter_dealer']) && $_POST['filter_dealer']) {
            $filter['user.name'] = ['like', "%$_POST[filter_dealer]%"];
        }
        if (isset($_POST['filter_dealer_company']) && $_POST['filter_dealer_company']) {
            $filter['dealer.company_id'] = $_POST['filter_dealer_company'];
        }

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
            $filter['lead.created_at'] = [['>=', $leads_between[0]], ['<=', $leads_between[1]]];
        }
        
       // $count = Model\Lead::get_count();

        $count = Model\Lead::get_count($filter, $options);
        $limit = max(1, min($_POST['limit'] ?? 20, 100));
        $pages_count = intdiv($count, $limit) + ($count % $limit ? 1 : 0);
        $page = max(1, min($_POST['page'] ?? 1, $pages_count));

        $options['group'] = 'lead.id';

        if (isset($_POST['filter_date_sort']) && $_POST['filter_date_sort'] != '') {
            $options['order']['lead.created_at'] = $_POST['filter_date_sort'];
        }

        if (isset($_POST['filter_inn_sort']) && $_POST['filter_inn_sort'] != '') {
            $options['order']['lead.inn_added_at'] = $_POST['filter_inn_sort'];
        }

        $options['limit'] = $limit;
        $options['offset'] = $limit * ($page - 1);

        $response['leads'] = Model\Lead::find($filter, $options);
        $response['pager'] = [
            'current' => $page,
            'pages' => $pages_count,
            'total' => $count,
        ];
        
    break;

    case 'edit-lead':
        if (isset($_POST['lead_id']) && $_POST['lead_id'] != '') {
            $options['lead'] = [
                'name' => $_POST['name'],
                'inn' => $_POST['inn'],
                'inn_added_at' => date('Y-m-d H:i:s'),
                'company_name' => $_POST['company_name'],
                'city' => $_POST['city'],
                'comment' => $_POST['comment']
            ];

            //$options['lead_phone'] = '';
            //$options['lead_email'] = '';
            $options['lead_phone'] = [$_POST['phone1'], $_POST['phone2'], $_POST['phone3']];
            $options['lead_email'] = [$_POST['email1'], $_POST['email2'], $_POST['email3']];
            
            Model\Lead::update_lead($_POST['lead_id'], $options);
            $response = Model\Lead::findOne(['id' => $_POST['lead_id']]);
            //$response = $_POST;
        }
    break;

    case 'delete-lead':
        if (isset($_POST['id']) && $_POST['id'] != '') {
            $response = Model\Lead::delete_lead($_POST['id']);
        }
    break;

    case 'update-lead-comment':
        $response = Model\Lead::update_lead_comment($_POST['id'], $_POST['comment']);
    break;

    case 'get-intersections-lead': 
        if (isset($_POST['lead_id']) && $_POST['lead_id'] != '') {
            $response = Model\Lead::get_intersections($_POST['lead_id']);
        }
    break;

    case 'get-notification-by-id': 
        if (isset($_POST['id']) && $_POST['id'] != '') {
            $response = Model\Notification::get_by_id($_POST['id']);
        }
    break;

    case 'check-intersections-lead': 
       //$response = Model\Lead::check_intersections(5240, '209655943440', ['+7(960)888-84-35'], ['lead1330_0@example.com', 'lead1318_0@example.com']);
    break;

    default:
        $response['errors'][] = ['error' => "Неизвестное действие: $_POST[action]"];
    break;
}

echo json_encode($response);
