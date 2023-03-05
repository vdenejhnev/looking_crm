<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

    $statuses = [
        'new' => 'Переговоры',
        'single' => 'Единственный',
        'first' => 'Первый',
        'second' => 'Второй',
        'third' => '3-й и более',
        'trash' => 'В топке',
        'deleted' => 'Удалён',
        'done' => 'Завершено',
    ];

    $months = [
        'Январь',
        'Февраль',
        'Март',
        'Апрель',
        'Март',
        'Июнь',
        'Июль',
        'Август',
        'Сентябрь',
        'Октябрь',
        'Ноябрь',
        'Декабрь'
    ];

function xls_handler_admin_statistics($args) {
    global $months;

    if (isset($_GET['table'])) {
        switch ($_GET['table']) {
            case 'dealers_stat':
                $rows = [['Активные диллеры', '', '']];
                $dealers_stat = Model\Dealer::get_active_stat();

                foreach ($dealers_stat as $key => $dealer) {                      
                    $rows[$key + 1][0] = $dealer['name'];
                    $rows[$key + 1][1] = $dealer['leads_count'];
                    $rows[$key + 1][2] = '';
                }
                break;
    
            case 'companies_stat':
                $rows = [['Активные компании', '', '']];
                $companies_stat = Model\Company::get_active_stat();

                foreach ($companies_stat as $key => $company){
                    $rows[$key + 1][0] = $company['name'];
                    $rows[$key + 1][1] = $company['leads_count'];
                    $rows[$key + 1][2] = '';
                } 
                break;
    
            case 'months_dealers_stat':
                $rows = [['За месяц', '', '', '']];
                $months_dealers_stat =Model\Dealer::get_active_stat([['YEAR(L.created_at)', $_GET['year']], ['MONTH(L.created_at)', $_GET['month']]]);

                foreach ($months_dealers_stat as $key => $dealer){
                    if ($dealer['leads_count'] > 0) {
                        $rows[$key + 1][0] = $months[$_GET['month'] - 1] . ' ' .  $_GET['year'];
                        $rows[$key + 1][1] = $dealer['name'];
                        $rows[$key + 1][2] = $dealer['leads_count'];
                        $rows[$key + 1][3] = '';
                    }
                }
                break;
    
            case 'months_companies_stat':
                $rows = [['Компании по месяцам', '', '', '']];
                $months_companies_stat = Model\Company::get_active_stat([['YEAR(L.created_at)', $_GET['year']], ['MONTH(L.created_at)', $_GET['month']]]);

                foreach ($months_companies_stat as $key => $company){
                    if ($company['leads_count'] > 0) {
                        $rows[$key + 1][0] = $months[$_GET['month'] - 1] . ' ' .  $_GET['year'];
                        $rows[$key + 1][1] = $company['name'];
                        $rows[$key + 1][2] = $company['leads_count'];
                        $rows[$key + 1][3] = '';
                    }
                }
            
            default:
                break;
        }
    } else {
        $rows = [['Активные диллеры', '', '', 'Активные компании', '', '', 'За месяц', '', '', '', 'Компании по месяцам', '', '', '']];
        $dealers_stat = Model\Dealer::get_active_stat();
        $companies_stat = Model\Company::get_active_stat();
        $months_dealers_stat =Model\Dealer::get_active_stat([['YEAR(L.created_at)', $_GET['year']], ['MONTH(L.created_at)', $_GET['month']]]);
        $months_companies_stat = Model\Company::get_active_stat([['YEAR(L.created_at)', $_GET['year']], ['MONTH(L.created_at)', $_GET['month']]]);

        foreach ($dealers_stat as $key => $dealer) {                      
            $rows[$key + 1][0] = $dealer['name'];
            $rows[$key + 1][1] = $dealer['leads_count'];
            $rows[$key + 1][2] = '';
        }
    
        foreach ($companies_stat as $key => $company){
            $rows[$key + 1][3] = $company['name'];
            $rows[$key + 1][4] = $company['leads_count'];
            $rows[$key + 1][5] = '';
        }    
    
        foreach ($months_dealers_stat as $key => $dealer){
            if ($dealer['leads_count'] > 0) {
                $rows[$key + 1][6] = $months[$_GET['month'] - 1] . ' ' .  $_GET['year'];
                $rows[$key + 1][7] = $dealer['name'];
                $rows[$key + 1][8] = $dealer['leads_count'];
                $rows[$key + 1][9] = '';
            }
        }
    
        foreach ($months_companies_stat as $key => $company){
            if ($company['leads_count'] > 0) {
                $rows[$key + 1][10] = $months[$_GET['month'] - 1] . ' ' .  $_GET['year'];
                $rows[$key + 1][11] = $company['name'];
                $rows[$key + 1][12] = $company['leads_count'];
                $rows[$key + 1][13] = '';
            }
        }
    }

    return $rows;
}

function xls_handler_admin_dealers($args) {
    $rows = [['ID', 'Имя', 'Организация', 'Город', 'Телефон', 'Эл. почта', 'Лидов']];

    $dealers = Model\Dealer::find([], ['dealer_leads_count' => []]);

    foreach ($dealers as $dealer) {
        if ($dealer->leads_count == '') {
            $dealer->leads_count = 0;
        }

        $rows[] = [
            $dealer->id,
            $dealer->name,
            $dealer->company->name,
            $dealer->city->name,
            $dealer->phone,
            $dealer->email,
            $dealer->leads_count
        ];
    }

    return $rows;
}

function xls_handler_admin_leads($args) {
    $rows = [['Статус', 'Номер', 'ИНН', 'Организация', 'ИНН', 'Добавлен', 'Дилер', 'Организация дилера']];

    $leads = Model\Lead::find();
    global $statuses;

    //print_r($leads);

    foreach ($leads as $lead) {
        $dealer = Model\Dealer::findOne(['user.id' => $lead->dealer->id]);

        if ($dealer != false) {
            $lead_status = 'Не указан';
            $lead_phones = '';
            $lead_emails = '';
            if ($lead->status != ''){
                $lead_status = $statuses[$lead->status];
            }

            if (count($lead->phones) > 0) {
                foreach($lead->phones as $phone) {
                    $lead_phones .= $phone['value'] . ' ';
                }
            }

            if (count($lead->emails) > 0) {
                foreach($lead->emails as $email) {
                    $lead_emails .= $email['value'] . ' ';
                }
            }
            $rows[] = [
                $lead_status,
                $lead->id,
                $lead->inn,
                $lead->company_name,
                $lead_phones,
                $lead_emails,
                $lead->created_at,
                $lead->inn_added_at,
                $dealer->name,
                $dealer->company->name
            ];
        }
    }

    return $rows;
}

function xls_handler_front_statistics($args) {
    global $months;

    if (isset($_GET['table'])) {
        switch ($_GET['table']) {
            case 'dealers_stat':
                $rows = [['Активные диллеры', '', '']];
                $dealers_stat = Model\Dealer::get_active_stat();

                foreach ($dealers_stat as $key => $dealer) {                      
                    $rows[$key + 1][0] = $dealer['name'];
                    $rows[$key + 1][1] = $dealer['leads_count'];
                    $rows[$key + 1][2] = '';
                }
                break;
    
            case 'companies_stat':
                $rows = [['Активные компании', '', '']];
                $companies_stat = Model\Company::get_active_stat();

                foreach ($companies_stat as $key => $company){
                    $rows[$key + 1][0] = $company['name'];
                    $rows[$key + 1][1] = $company['leads_count'];
                    $rows[$key + 1][2] = '';
                } 
                break;
    
            case 'months_dealers_stat':
                $rows = [['За месяц', '', '', '']];
                $months_dealers_stat =Model\Dealer::get_active_stat([['YEAR(L.created_at)', $_GET['year']], ['MONTH(L.created_at)', $_GET['month']]]);

                foreach ($months_dealers_stat as $key => $dealer){
                    if ($dealer['leads_count'] > 0) {
                        $rows[$key + 1][0] = $months[$_GET['month'] - 1] . ' ' .  $_GET['year'];
                        $rows[$key + 1][1] = $dealer['name'];
                        $rows[$key + 1][2] = $dealer['leads_count'];
                        $rows[$key + 1][3] = '';
                    }
                }
                break;
    
            case 'months_companies_stat':
                $rows = [['Компании по месяцам', '', '', '']];
                $months_companies_stat = Model\Company::get_active_stat([['YEAR(L.created_at)', $_GET['year']], ['MONTH(L.created_at)', $_GET['month']]]);

                foreach ($months_companies_stat as $key => $company){
                    if ($company['leads_count'] > 0) {
                        $rows[$key + 1][0] = $months[$_GET['month'] - 1] . ' ' .  $_GET['year'];
                        $rows[$key + 1][1] = $company['name'];
                        $rows[$key + 1][2] = $company['leads_count'];
                        $rows[$key + 1][3] = '';
                    }
                }
            
            default:
                break;
        }
    } else {
        $rows = [['Активные диллеры', '', '', 'Активные компании', '', '', 'За месяц', '', '', '', 'Компании по месяцам', '', '', '']];
        $dealers_stat = Model\Dealer::get_active_stat();
        $companies_stat = Model\Company::get_active_stat();
        $months_dealers_stat =Model\Dealer::get_active_stat([['YEAR(L.created_at)', $_GET['year']], ['MONTH(L.created_at)', $_GET['month']]]);
        $months_companies_stat = Model\Company::get_active_stat([['YEAR(L.created_at)', $_GET['year']], ['MONTH(L.created_at)', $_GET['month']]]);

        foreach ($dealers_stat as $key => $dealer) {                      
            $rows[$key + 1][0] = $dealer['name'];
            $rows[$key + 1][1] = $dealer['leads_count'];
            $rows[$key + 1][2] = '';
        }
    
        foreach ($companies_stat as $key => $company){
            $rows[$key + 1][3] = $company['name'];
            $rows[$key + 1][4] = $company['leads_count'];
            $rows[$key + 1][5] = '';
        }    
    
        foreach ($months_dealers_stat as $key => $dealer){
            if ($dealer['leads_count'] > 0) {
                $rows[$key + 1][6] = $months[$_GET['month'] - 1] . ' ' .  $_GET['year'];
                $rows[$key + 1][7] = $dealer['name'];
                $rows[$key + 1][8] = $dealer['leads_count'];
                $rows[$key + 1][9] = '';
            }
        }
    
        foreach ($months_companies_stat as $key => $company){
            if ($company['leads_count'] > 0) {
                $rows[$key + 1][10] = $months[$_GET['month'] - 1] . ' ' .  $_GET['year'];
                $rows[$key + 1][11] = $company['name'];
                $rows[$key + 1][12] = $company['leads_count'];
                $rows[$key + 1][13] = '';
            }
        }
    }

    return $rows;
}

function xls_handler_front_leads($args) {
    $rows = [['Статус', 'Номер', 'ИНН', 'Организация', 'Телефон', 'Эл. почта', 'Добавлен', 'ИНН', 'Дилер', 'Организация дилера']];

    $leads = Model\Lead::find();
    global $statuses;

    //print_r($leads);

    foreach ($leads as $lead) {
        $dealer = Model\Dealer::findOne(['user.id' => $lead->dealer->id]);

        if ($dealer != false) {
            $lead_status = 'Не указан';
            $lead_phones = '';
            $lead_emails = '';
            if ($lead->status != ''){
                $lead_status = $statuses[$lead->status];
            }

            if (count($lead->phones) > 0) {
                foreach($lead->phones as $phone) {
                    $lead_phones .= $phone['value'] . ' ';
                }
            }

            if (count($lead->emails) > 0) {
                foreach($lead->emails as $email) {
                    $lead_emails .= $email['value'] . ' ';
                }
            }
            $rows[] = [
                $lead_status,
                $lead->id,
                $lead->inn,
                $lead->company_name,
                $lead_phones,
                $lead_emails,
                $lead->created_at,
                $lead->inn_added_at,
                $dealer->name,
                $dealer->company->name
            ];
        }
    }

    return $rows;
}



//print_r($catC);

$name = $AREA === 'admin' ? $catC : $catB;
$handler = "xls_handler_${AREA}_$name";

if (!function_exists($handler)) {
    http_response_code(404);
    echo 'Not found';
    die;
}

$rows = call_user_func($handler, $_GET);

$spreadsheet = new Spreadsheet;
$sheet = $spreadsheet->getActiveSheet();

foreach ($rows as $row_number => $row) {
    foreach ($row as $col_number => $cell) {
        $sheet->setCellValueByColumnAndRow($col_number + 1, $row_number + 1, $cell);
    }
}

$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="table.xlsx"');
$writer->save('php://output');
