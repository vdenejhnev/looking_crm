<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../settings.php';
require_once __DIR__ . '/../../functions.php';

Form\check_ajax('admin');

$response = [];

switch ($_POST['action']) {
    case 'get_monthly_stat':
        if (isset($_POST['year']) && $_POST['year'] != '') {
            $months = Model\Lead::get_monthly_stat($_POST['year']);
            $item = 0;
            foreach ($months as $month) {
                $response[$item] = $month['count'];
                $item++;
            }
        }
        break;

    case 'get_year_count':
        if (isset($_POST['year']) && $_POST['year'] != '') {
            $response = Model\Lead::get_count(['lead.created_at' => [['>=', date('Y-m-d H:i:s', strtotime($_POST['year'].'-01-01 00:00:00'))], ['<=', date('Y-m-d H:i:s', strtotime($_POST['year'].'-12-31 23:59:59'))]]]);
        }
        break;

    case 'get_uniq_year_count':
        if (isset($_POST['year']) && $_POST['year'] != '') {
            $response = Model\Lead::get_count(['lead.created_at' => [['>=', date('Y-m-d H:i:s', strtotime($_POST['year'].'-01-01 00:00:00'))], ['<=', date('Y-m-d H:i:s', strtotime($_POST['year'].'-12-31 23:59:59'))]]], ['unique' => true]);
        }
        break;

    case 'get_month_dealer_stat':
        if (isset($_POST['year'], $_POST['month']) && $_POST['year'] != '' && $_POST['month'] != '') {
            $response = Model\Dealer::get_active_stat([['YEAR(L.created_at)', $_POST['year']], ['MONTH(L.created_at)', $_POST['month']]]);
        }
        break;

    case 'get_month_company_stat':
        if (isset($_POST['year'], $_POST['month']) && $_POST['year'] != '' && $_POST['month'] != '') {
            $response = Model\Company::get_active_stat([['YEAR(L.created_at)', $_POST['year']], ['MONTH(L.created_at)', $_POST['month']]]);
        }
        break;
}

echo json_encode($response);