<?php
    return [
        'companies_stat' => Model\Company::get_active_stat(),
        'dealers_stat' => Model\Dealer::get_active_stat(),
       // 'dealers_month_stat' => Model\Dealer::get_active_stat([['YEAR(L.created_at)', date('Y')], ['MONTH(L.created_at)', date('m')]]),
        'dealers_count' => Model\Dealer::get_active_count(),
        'leads_stat' => Model\Lead::get_monthly_stat(),
        'leads_count' => Model\Lead::get_count(['lead.created_at' => [['>=', date('Y-m-d H:i:s', strtotime(date('Y').'-01-01 00:00:00'))], ['<=', date('Y-m-d H:i:s', strtotime(date('Y').'-12-31 23:59:59'))]]]),
        'leads_uniq' => Model\Lead::get_count(['lead.created_at' => [['>=', date('Y-m-d H:i:s', strtotime(date('Y').'-01-01 00:00:00'))], ['<=', date('Y-m-d H:i:s', strtotime(date('Y').'-12-31 23:59:59'))]]], ['unique' => true]),
    ];
