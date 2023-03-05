<?php

namespace Model;

use App\Model;

class Lead extends Model {
    const TABLES = [
        'lead',
        'LEFT JOIN lead_phone' => 'lead_phone.lead_id = `lead`.id',
        'LEFT JOIN lead_email' => 'lead_email.lead_id = `lead`.id',
    ];

    const FIELDS = [
        'lead' => ['*'],
        'lead_phone' => ['value' => 'phone_value'],
        'lead_email' => ['value' => 'email_value'],
    ];

    const DEPENDENCIES = [
        'dealer' => [
            'from' => [
                'LEFT JOIN `dealer`' => '`dealer`.`user_id` = `lead`.`user_id`',
                'LEFT JOIN `user`' => '`user`.`id` = `lead`.`user_id`',
                'LEFT JOIN `company`' => '`company`.`id` = `dealer`.`company_id`',
            ],
            'fields' => [
                '`user`.`name`' => 'dealer_name',
                '`company`.`name`' => 'dealer_company',
            ],
        ],
        'dealer.company' => [
            'from' => [],
        ]
    ];

    const STATUSES = [
        'new' => ['name' => 'Переговоры', 'color' => '#FFD954'],
        'single' => ['name' => 'Единственный', 'color' => '#24FF00'],
        'first' => ['name' => 'Первый', 'color' => '#24FF00'],
        'second' => ['name' => 'Второй', 'color' => '#FF9254'],
        'third' => ['name' => '3-й и более', 'color' => '#FF5C00'],
        'trash' => ['name' => 'В топке', 'color' => '#CDCDCD'],
        'deleted' => ['name' => 'Удалён', 'color' => '#000000'],
        'done' => ['name' => 'Завершено', 'color' => '#EB00FF'],
    ];

    protected static function bind($query, $bindings = [], $types = []) {
        global $CONNECTION;

        $stmt = $CONNECTION->prepare($query);

        if ($bindings) {
            $param = [
                implode('', array_fill(0, count($bindings), 's'))
            ];

            foreach ($bindings as $field => $value) {
                $param[] = &$bindings[$field];
            }

            call_user_func_array([$stmt, 'bind_param'], $param);
        }

        return $stmt;
    }

    protected static function build_what($criteria, $options) {
        $query = '`lead`.*, lead_phone.value AS lead_phone, lead_email.value AS lead_email';

        if (isset($options['with']) && $options['with']) {
            foreach ($options['with'] as $table => $rules) {
                if (is_numeric($table)) {
                    $table = $rules;
                    $rules = null;
                }

                switch ($table) {
                    case 'dealer':
                        $query .= ', user.name AS dealer_name';
                    break;

                    case 'dealer.company':
                        $query .= ', company.name AS dealer_company_name, company.id AS dealer_company_id';
                    break;

                    /*case 'lead.created_at':
                        $query .= ', company.name AS dealer_company_name, company.id AS dealer_company_id';
                    break;*/
                }
            }
        }

        return $query;
    }    

    public static function create($data) {
        global $CONNECTION;

        if (array_key_exists('user', $data)) {
            if ($data['user']) {
                $data['user_id'] = $data['user']->id;
            }

            unset($data['user']);
        }

        if ($data['company_name'] == '') {
            $data['inn'] = '';
        }

        $errors = self::validate($data);
        if ($errors) {
            return $errors;
        }



        $lead_data = array_filter(array_intersect_key(
            $data,
            array_fill_keys(['city', 'comment', 'company_name', 'inn', 'name', 'user_id'], '')
        ));



        if (isset($lead_data['inn'])) {

            $lead_data['inn_added_at'] = date('Y-m-d H:i:s');
            $lead_data['status'] = self::check_status($lead_data['inn'], [$data['phone1'], $data['phone2'], $data['phone3']], [$data['email1'], $data['email2'], $data['email3']]);
        } else {
            $lead_data['status'] = 'new';
        }        

        

        $query = sprintf(
            'INSERT `lead` (%s) VALUES (%s)',
            implode(',', array_map(function($field) {return "`$field`";}, array_keys($lead_data))),
            implode(',', array_fill(0, count($lead_data), '?'))
        );

        $CONNECTION->begin_transaction();

        try {
            $stmt = self::bind($query, $lead_data);

            $stmt->execute();



            $lead_id = $stmt->insert_id;

            foreach (['phone', 'email'] as $type) {
            
                for ($item = 1; $item <= 3; $item++) {
                    if ($data[$type . $item] != '') {
                        $value = $data[$type . $item];
                        $query = "INSERT INTO lead_" . $type . " (lead_id, value) VALUES ('$lead_id', '$value')";
                        $stmt = $CONNECTION->prepare($query);
                        $stmt->execute();
                    }
                    
                }
            }
           

            self::check_intersections($data['user_id'], $lead_id, $data['inn'], [$data['phone1'], $data['phone2'], $data['phone3']], [$data['email1'], $data['email2'], $data['email3']]);

            $CONNECTION->commit();
        } catch (\Exception $e) {
            $CONNECTION->rollback();
            throw $e;
        }

        return (object)$data;
    }

    
    public static function find($criteria = [], $options = []) {
        global $CONNECTION;

        self::check_trash_status();

        $query = 'SELECT ';
        $query .= self::build_what($criteria, $options);
        $query .= self::build_from($criteria, $options);
        $query .= self::build_query_tail($criteria, $options);
        //$query .= self::build_order($criteria, $options);
        $stmt = $CONNECTION->prepare($query);
        $stmt->execute();

        $leads = array_map(
            function($lead) { return (object)$lead; },
            $stmt->get_result()->fetch_all(MYSQLI_ASSOC)
        );

        foreach ($leads as &$lead) {
            $lead->dealer = (object)['id' => $lead->user_id];
            unset($lead->user_id);

            if (isset($lead->dealer_name)) {
                $lead->dealer->name = $lead->dealer_name;
                unset($lead->dealer_name);
            }

            if (isset($lead->dealer_company_name)) {
                $lead->dealer->company = (object)[
                    'id' => $lead->dealer_company_id,
                    'name' => $lead->dealer_company_name,
                ];
                unset($lead->dealer_company_id);
                unset($lead->dealer_company_name);
            }

            //$lead->inn_added_at = date('Y-m-d', strtotime($lead->inn_added_at));
            //$lead->inn_time = date('H:i', strtotime($lead->inn_added_at));

            $query = "SELECT value FROM lead_phone WHERE lead_id = '$lead->id'";
            $stmt = $CONNECTION->prepare($query);
            $stmt->execute();
            $lead->phones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            $query = "SELECT value FROM lead_email WHERE lead_id = '$lead->id'";
            $stmt = $CONNECTION->prepare($query);
            $stmt->execute();
            $lead->emails = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        return $leads;
    }
    

    
    public static function findOne($criteria, $options = []) {
        global $CONNECTION;

        $query = 'SELECT * FROM lead LEFT JOIN dealer ON (dealer.user_id = lead.user_id)';
        $bindings = [];

        if ($criteria) {
            $expressions = [];

            foreach ($criteria as $field => $value) {
                $expressions[] = "$field = $value";
                $bindings[] = $value;
            }

            if ($expressions) {
                $query .= ' WHERE ' . implode(' AND ', $expressions);
            }
        }

        //$stmt = self::bind($query, $bindings);
        $stmt = $CONNECTION->prepare($query);
        $stmt->execute();

        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if (count($rows) > 1) {
            throw new \Exception("Найдено более одного лида: $query");
        }

        if (count($rows) == 0) {
            return false;
        }

        $lead = (object)$rows[0];

        $query = "SELECT value FROM lead_phone WHERE lead_id = '$lead->id'";
        $stmt = $CONNECTION->prepare($query);
        $stmt->execute();
        $lead->phones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $query = "SELECT value FROM lead_email WHERE lead_id = '$lead->id'";
        $stmt = $CONNECTION->prepare($query);
        $stmt->execute();
        $lead->emails = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return $lead;
    }

    
    public static function get_count($criteria = [], $options = []) {
        global $CONNECTION;

        $options = array_merge(['unique' => false], $options);
        $query = $options['unique']
            ? 'SELECT COUNT(DISTINCT inn)'
            : 'SELECT COUNT(DISTINCT lead.id)';
        
        $query .= self::build_from($criteria, $options);
        $query .= self::build_query_tail($criteria, $options);

        $stmt = $CONNECTION->prepare($query);
        $stmt->execute();

        return $stmt->get_result()->fetch_array()[0];
    }

    public static function get_month_count($dealer_id, $month, $year = '') {
        $query = "SELECT COUNT(id) AS count FROM lead WHERE user_id = '$dealer_id' AND MONTH(created_at) = '$month'";

        if ($year != '') {
            $query .= " AND YEAR(created_at) = '$year'"; 
        }

        $stmt = self::bind($query);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }


    public static function get_monthly_stat($year = '') {
        if ($year == '') {
            $year = date('Y');
        }
        $query = <<<SQL
            SELECT MONTH(created_at) AS `month`, COUNT(id) AS `count`
            FROM `lead`
            WHERE YEAR(created_at) = '$year'
            GROUP BY `month`
            ORDER BY `month`
        SQL;

        $stmt = self::bind($query);
        $stmt->execute();

        $leads_stat = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $leads_stat_full = [];
        $max_month = 0;
    
        foreach ($leads_stat as $lead_month) {
            $max_month = $lead_month['month'] - 1;
        }
    
        for ($month = 0; $month <= $max_month; $month++) {
            if(!isset($leads_stat_full[$month])) {
                $leads_stat_full[$month] = ['month' => $month + 1, 'count' => 0];
            }
        }
    
        foreach ($leads_stat as $lead_month) {
            $leads_stat_full[$lead_month['month'] - 1] = $lead_month;
            $max_month = $lead_month['month'] - 1;
        }
    
        return $leads_stat_full;
    }

    public static function make($data) {
        $data['dealer'] = [
            'id' => $data['user_id'],
        ];
        unset($data['user_id']);

        if (isset($data['dealer_name'])) {
            $data['dealer']['name'] = $data['dealer_name'];
            unset($data['dealer_name']);
        }

        if (isset($data['dealer_company'])) {
            $data['dealer']['company'] = $data['dealer_company'];
            unset($data['dealer_company']);
        }

        return (object)$data;
    }

    public static function update_lead($lead_id, $options) {
        $query = "UPDATE lead SET ";

        $options_count = 0;

        foreach ($options['lead'] as $key => $option) {

            if ($option != '') {
                if ($options_count > 0) {
                    $query .= ',';
                }   

                $query .= $key . " = '" . $option . "'";
            }

            $options_count++; 
        }

        $query.= " WHERE id = '$lead_id'"; 
        $stmt = self::bind($query);
        $stmt->execute();

        
        if (isset($options['lead_email']) && $options['lead_email'] != '') {
            if (is_array($options['lead_email'])) {
                $query = "SELECT id FROM lead_email WHERE lead_id = '$lead_id'";
                $stmt = self::bind($query);
                $stmt->execute();

                $stmt_rows = []; 
                $stmt_rows = $stmt->get_result()->fetch_all();

                foreach($options['lead_email'] as $key => $lead_email) {
                    if ($lead_email != '') {
                        if ($key <= (count($stmt_rows) - 1)) {             
                            $query = "UPDATE lead_email SET value = '$lead_email' WHERE id = ". $stmt_rows[$key][0];  
                            $stmt = self::bind($query);
                            $stmt->execute();
                        } else {
                            $query = "INSERT INTO lead_email (lead_id, value) VALUES ('$lead_id', '" . $lead_email . "')";
                            $stmt = self::bind($query);
                            $stmt->execute();
                        }
                    }
                }
            } else {
                $query = "SELECT id FROM lead_email WHERE lead_id = '$lead_id' LIMIT 1";
                $stmt = self::bind($query);
                $stmt->execute();
    
                if ($stmt->get_result()->num_rows == 1) {
                    $email_id = $stmt->get_result()->fetch_array()[0];
                    $query = "UPDATE lead_email SET value = '" . $options['lead_email'] . "' WHERE id = '$email_id'";
                    $stmt = self::bind($query);
                    $stmt->execute();
                } else {
                    $query = "INSERT INTO lead_email (lead_id, value) VALUES ('$lead_id', '" . $options['lead_email'] . "')";
                    $stmt = self::bind($query);
                    $stmt->execute();
                } 
            }          
        }
        
        if (isset($options['lead_phone']) && $options['lead_phone'] != '') {
            if (is_array($options['lead_phone'])) {
                $query = "SELECT id FROM lead_phone WHERE lead_id = '$lead_id'";
                $stmt = self::bind($query);
                $stmt->execute();

                $stmt_rows = []; 
                $stmt_rows = $stmt->get_result()->fetch_all();

                foreach($options['lead_phone'] as $key => $lead_phone) {
                    if ($lead_phone != '') {
                        if ($key <= (count($stmt_rows) - 1)) {                
                            $query = "UPDATE lead_phone SET value = '$lead_phone' WHERE id = ". $stmt_rows[$key][0];  
                            $stmt = self::bind($query);
                            $stmt->execute();

                        } else {
                            $query = "INSERT INTO lead_phone (lead_id, value) VALUES ('$lead_id', '" . $lead_phone . "')";
                            $stmt = self::bind($query);
                            $stmt->execute();
                        }
                    }   
                }
            } else {
                $query = "SELECT id FROM lead_phone WHERE lead_id = '$lead_id' LIMIT 1";
                $stmt = self::bind($query);
                $stmt->execute();
    
                if ($stmt->get_result()->num_rows == 1) {
                    $phone_id = $stmt->get_result()->fetch_array()[0];
                    $query = "UPDATE lead_phone SET value = '" . $options['lead_phone'] . "' WHERE id = '$phone_id'";
                    $stmt = self::bind($query);
                    $stmt->execute();
                } else {
                    $query = "INSERT INTO lead_phone (lead_id, value) VALUES ('$lead_id', '" . $options['lead_phone'] . "')";
                    $stmt = self::bind($query);
                    $stmt->execute();
                }   
            } 
        }

        return 'true';
    }

    public static function validate($data) {
        if (is_object($data)) {
            $data = (array)$data;
        }

        $errors = [];

        if (!empty($data['inn'])) {
            /*if (empty($data['company_name'])) {
                $errors[] = ['field' => 'company_name', 'error' => 'Обязательное поле при заполнении ИНН'];
            }*/

            /*if (!preg_match('!^\\d{10}$!', $data['inn'])) {
                $errors[] = ['field' => 'inn', 'error' => 'ИНН должен состоять из 12 цифр'];
            }*/
        } else {
            if (!empty($data['company_name'])) {
                $errors[] = ['field' => 'inn', 'error' => 'Укажите ИНН компании'];
            }
        }

        foreach (['city', 'name', 'user_id'] as $field) {
            if (empty($data[$field])) {
                $errors[] = ['field' => $field, 'error' => 'Обязательное поле'];
            }
        }

        return $errors;
    }

    public static function update_lead_comment($lead_id, $comment) {
        $query = "UPDATE lead SET comment = '$comment' WHERE id = '$lead_id'"; 

        $stmt = self::bind($query);
        $stmt->execute();

        return true;
    }

    public static function delete($lead_id) {
        $query = "DELETE FROM lead WHERE id = '$lead_id'";

        $stmt = self::bind($query);
        $stmt->execute();

        return true;
    }

    public static function delete_lead($lead_id) {
        $query = "UPDATE lead SET status = 'deleted' WHERE id = '$lead_id'";

        $stmt = self::bind($query);
        $stmt->execute();

        return true;
    }

    public static function check_status($inn, $phones_array, $emails_array) {
        $query = "SELECT * FROM lead WHERE inn = '$inn'";

        $stmt = self::bind($query);
        $stmt->execute();

        $array_rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); 

        if (count($array_rows) == 0) {
            $phone_rows = [];
            $email_rows = [];
            foreach ($phones_array as $phone) {
                $query = "SELECT * FROM lead_phone WHERE value LIKE '$phone'";
                $stmt = self::bind($query);
                $stmt->execute();
                $phone_rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); 

                if ($phone_rows != []) {
                    break;
                } 
            }

            foreach ($emails_array as $email) {
                $query = "SELECT * FROM lead_email WHERE value LIKE '$email'";
                $stmt = self::bind($query);
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

    public static function check_trash_status() {
        $query = "SELECT * FROM lead WHERE (status = 'second' OR status = 'third') AND DATEDIFF(NOW(), created_at) >= 90";
        $stmt = self::bind($query);
        $stmt->execute();
        $lead_list = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        foreach ($lead_list as $lead) {
            $id = $lead['id'];
            $query = "UPDATE lead SET status = 'trash' WHERE id = '$id'";
            $stmt = self::bind($query);
            $stmt->execute();
        }

        return $lead_list;
    }

    public static function update_lead_status($lead_id, $status) {
        $query = "UPDATE lead SET status = '$status' WHERE id = '$lead_id'"; 

        $stmt = self::bind($query);
        $stmt->execute();

        return true;
    }

    public static function check_intersections ($dealer_id, $lead_id, $inn, $phones_array, $emails_array) {

        $query = "SELECT * FROM lead WHERE inn = '$inn' AND status <> 'deleted' AND id <> '$lead_id'";

        $intersections = [];

        $stmt = self::bind($query);
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
                    $stmt = self::bind($query);
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
                    $stmt = self::bind($query);
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
            Notification::create($dealer_id, $lead_id, $curr_lead_id, $intersection);
        }

        return $intersections;

    }

    public static function get_intersections($lead_id) {
        $stmt = self::bind("SELECT * FROM notification WHERE lead = '$lead_id'");
        $stmt->execute();
        $intersections = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        return $intersections;
    }

    public static function getYearsList() {
        $query = "SELECT DISTINCT(YEAR(created_at)) AS year FROM lead";
        $stmt = self::bind($query);
        $stmt->execute();
        $years_list = [];
        $years = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        if (count($years) > 0) {
            foreach ($years as $year) {
                if ($year['year'] == date('Y')) {
                    $year['curr_year'] = 'curr_year';
                }
                
                array_push($years_list, $year);
            }
        }

        return $years_list;
    }
}
