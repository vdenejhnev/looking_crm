<?php

// Функции для работы с дилерами

namespace Model;

use App\Model;
use Model\User;

class Dealer extends Model {
    const FIELD_TYPES = [
        'user_id' => 'i'
    ];

    private static function build_query($criteria, $options) {
        global $CONNECTION;

        $with_leads_count = false;

        if (isset($options['with'])) {
            if (!is_array($options['with'])) {
                throw new \Exception('"with" option must be an array');
            }

            if ($options['with']) {
                if (count($options['with']) === 1 && isset($options['with']['leads_count'])) {
                    $with_leads_count = $options['with']['leads_count']['between'] ?? [null, null];
                }else {
                    throw new \Exception('Only "leads_count" in "with" option available');
                }
            }
        }

        $fields = $options['select'] ?? <<<SQL
            user.id, user.created_at, user.updated_at, user.email, user.name,
            dealer.*,
            company.id AS company_id, company.name AS company_name,
            city.id AS city_id, city.name AS city_name
        SQL;

        if ($with_leads_count) {
            $fields .= ', COUNT(lead.id) AS leads_count';
        }

        if (isset($options['dealer_leads_count']) && $options['dealer_leads_count'] != []) {
            $with_dealer_leads_count = true;
            $leads_between_from = $options['dealer_leads_count'][0];
            $leads_between_to = $options['dealer_leads_count'][1];
            $fields .= ", (SELECT COUNT(lead.id) FROM lead WHERE user_id = user.id AND (lead.created_at >= '$leads_between_from' AND lead.created_at <= '$leads_between_to') GROUP BY lead.user_id) AS leads_count";
        } else if (isset($options['dealer_leads_count']) && $options['dealer_leads_count'] == []) {
            $with_dealer_leads_count = true;
            $fields .= ", (SELECT COUNT(lead.id) FROM lead WHERE user_id = user.id GROUP BY lead.user_id) AS leads_count";
        }

        $query = <<<SQL
            SELECT $fields
            FROM user
                INNER JOIN dealer ON dealer.user_id = user.id
                LEFT JOIN company ON company.id = dealer.company_id
                LEFT JOIN city ON city.id = dealer.city_id

        SQL;

        if (isset($options['dealer_leads_count']) && $options['dealer_leads_count'] == true) {
            $query .= 'LEFT JOIN `lead` ON `lead`.user_id = user.id';
            $options['group'] = 'user.id';
        }

        if ($with_leads_count) {
            $query .= 'LEFT JOIN `lead` ON `lead`.user_id = user.id';

            if ($with_leads_count[0]) {
                $query .= sprintf(
                    " AND `lead`.created_at >= '%s'",
                    $CONNECTION->real_escape_string($with_leads_count[0])
                );
            }

            if ($with_leads_count[1]) {
                $query .= sprintf(
                    " AND `lead`.created_at <= '%s'",
                    $CONNECTION->real_escape_string($with_leads_count[1])
                );
            }

            $options['group'] = 'user.id';
        }

        $query .= self::build_query_tail($criteria, $options);

        return $query;
    }

    public static function build_object($data) {
        $company = (object)['id' => $data['company_id'], 'name' => $data['company_name']];
        unset($data['company_id'], $data['company_name']);
        $city = (object)['id' => $data['city_id'], 'name' => $data['city_name']];
        unset($data['city_id'], $data['city_name']);

        $dealer = (object)$data;
        $dealer->company = $company;
        $dealer->city = $city;

        return $dealer;
    }

    public static function create($data) {
        global $CONNECTION;

        $errors = self::validate($data);
        if ($errors) {
            return $errors;
        }

        $CONNECTION->begin_transaction();

        try {
            $password = substr(md5(time()), 0, 7);

            $user_data = ['name' => $data['name'], 'email' => $data['email'], 'password' => $password, 'role' => 'dealer'];
            $user = User::create($user_data);
            if (!is_object($user)) {
                return $user;
            }

            $company = Company::get($data['company_name']);
            if (!$company) {
                $company = Company::create($data['company_name']);
            }

            $city = City::get($data['city']);
            if (!$city) {
                $city = City::create($data['city']);
            }

            $permitted_chars = '123456789';
            $auth_code = substr(str_shuffle($permitted_chars), 0, 5);

            $stmt = $CONNECTION->prepare(<<<'SQL'
                INSERT dealer (user_id, company_id, city_id, phone, tg_notification) VALUES (?, ?, ?, ?, ?)
                SQL
            );
            $stmt->bind_param(
                'isssi',
                $user->id,
                $company->id,
                $city->id,
                $data['phone'],
                $auth_code
            );
            $stmt->execute();

            $CONNECTION->commit();

            
            $mail_text = "Здравствуйте, " . $data['name'] . ". Вы добавлены в систему учета лидов компании “СпецТехника”.\n\nДоступ в систему по адресу: lid.sptstech.ru\nЛогин: " . $data['email'] . "\nПароль: " . $password . "\nЛиды также можно добавлять через телеграм-бот:\n@lid_spetech\nПин-код для авторизации: " . $auth_code;

            self::send_mail($data['email'], 'СпецТехника: вы добавлены', $mail_text);
        } catch (\Exception $e) {
            $CONNECTION->rollback();
            throw $e;
        }

        return (object)self::fetch_by('user_id', $stmt->insert_id);
    }

    private static function fetch_by($field, $value) {
        global $CONNECTION;

        if (!isset(self::FIELD_TYPES[$field])) {
            throw new \Exception("Unable to get dealer by $field");
        }

        $stmt = $CONNECTION->prepare("SELECT * FROM dealer WHERE $field = ?");
        $stmt->bind_param(self::FIELD_TYPES[$field], $value);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public static function get_count($criteria = [], $options = []) {
        global $CONNECTION;

        $options['select'] = 'COUNT(user.id)';
        $stmt = $CONNECTION->prepare(self::build_query($criteria, $options));
        $stmt->execute();

        return $stmt->get_result()->fetch_row()[0];
    }

    public static function get_active_count($criteria = [], $options = []) {
         $query = <<<SQL
            SELECT COUNT(L.id) AS leads_count
            FROM user U
                INNER JOIN dealer D ON D.user_id = U.id
                LEFT JOIN `lead` L ON L.user_id = U.id
            ORDER BY leads_count DESC
        SQL;

        $stmt = self::bind($query);
        $stmt->execute();

        return $stmt->get_result()->fetch_row()[0];
    }

    public static function get_active_stat($criteria = [], $options = []) {
        $query = 
            "SELECT U.name, COUNT(L.id) AS leads_count
            FROM user U
                INNER JOIN dealer D ON D.user_id = U.id
                LEFT JOIN `lead` L ON L.user_id = U.id";

        $expressions = [];

        if ($criteria != []) {
            foreach ($criteria as $condition) {
                $expressions[] = $condition[0] . ' = ' . $condition[1];
            } 

            $query .= ' WHERE(' . implode(')AND(', $expressions) . ')';
        }

        $query .= "
            GROUP BY U.id
            ORDER BY leads_count DESC";

        $stmt = self::bind($query);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function get_month_dealer_stat() {
        $query = 
            "SELECT MONTH(L.created_at), YEAR(L.created_at), U.name, COUNT(L.id) AS leads_count
            FROM user U
                INNER JOIN dealer D ON D.user_id = U.id
                LEFT JOIN `lead` L ON L.user_id = U.id
            GROUP BY U.id, MONTH(L.created_at), YEAR(L.created_at)
            ORDER BY L.created_at DESC";

        $stmt = self::bind($query);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function get_month_companies_stat() {
        $query = 
            "SELECT MONTH(L.created_at), YEAR(L.created_at), C.name, COUNT(L.id) AS leads_count
            FROM company C
                INNER JOIN dealer D ON D.company_id = C.id
                LEFT JOIN `lead` L ON L.user_id = D.user_id
            GROUP BY C.id, MONTH(L.created_at), YEAR(L.created_at) 
            ORDER BY L.created_at DESC;";

        $stmt = self::bind($query);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function find($criteria = [], $options = []) {
        global $CONNECTION;

        $stmt = $CONNECTION->prepare(self::build_query($criteria, $options));
        $stmt->execute();

        return array_map(['self', 'build_object'], $stmt->get_result()->fetch_all(MYSQLI_ASSOC));
    }

    public static function findOne($criteria = [], $options = []) {
        $dealers = self::find($criteria, $options);
        if (count($dealers) > 1) {
            throw new \Exception('More than one record found');
        }

        return $dealers[0] ?? false;
    }

    public static function validate($dealer) {
        if (is_object($dealer)) {
            $dealer = (array)$dealer;
        }

        $errors = [];

        foreach (['company_name', 'name', 'city', 'phone', 'email'] as $field) {
            if ( ! (isset($dealer[$field]) && !empty($dealer[$field]))) {
                $errors[] = ['field' => $field, 'error' => "Обязательное свойство: $field"];
            }
        }

        // Check email usage
        $query = 'SELECT COUNT(*) FROM `user` WHERE email = ?';
        $bindings = [ $dealer['email'] ];
        if (isset($dealer['id'])) {
            $query .= ' AND id != ?';
            $bindings[] = $dealer['id'];
        }

        $stmt = self::bind($query, $bindings);
        $stmt->execute();

        if ($stmt->get_result()->fetch_row()[0] > 0) {
            return [['field' => 'email', 'error' => 'Эл. почта уже используется']];
        }

        // Check phone usage
        $query = 'SELECT COUNT(*) FROM `dealer` WHERE phone = ?';
        $bindings = [ $dealer['phone'] ];
        if (isset($dealer['id'])) {
            $query .= ' AND user_id != ?';
            $bindings[] = $dealer['id'];
        }
        $stmt = self::bind($query, $bindings);
        $stmt->execute();

        if ($stmt->get_result()->fetch_row()[0] > 0) {
            return [['field' => 'phone', 'error' => 'Номер уже используется']];
        }

        return $errors;
    }

    public static function update($data) {
        global $CONNECTION;

        $dealer = self::find(['user.id' => $data['id']]);
        if (!$dealer) {
            return [['error' => 'Дилер не найден']];
        }

        $errors = self::validate($data);
        if ($errors) {
            return $errors;
        }

        $CONNECTION->begin_transaction();

        try {
            $stmt = $CONNECTION->prepare(<<<SQL
                UPDATE user SET name = ?, email = ? WHERE id = ?
            SQL);
            $stmt->bind_param('ssi', $data['name'], $data['email'], $data['id']);
            $stmt->execute();

            $company = Company::get($data['company_name']);
            if (!$company) {
                $company = Company::create($data['company_name']);
            }

            $city = City::get($data['city']);
            if (!$city) {
                $city = City::create($data['city']);
            }

            $stmt = $CONNECTION->prepare(<<<SQL
                UPDATE dealer SET company_id = ?, city_id = ?, phone = ? WHERE user_id = ?
            SQL);
            $stmt->bind_param('iisi', $company->id, $city->id, $data['phone'], $data['id']);
            $stmt->execute();

            $CONNECTION->commit();
        } catch (\Exception $e) {
            var_dump($e);
            $CONNECTION->rollback();
        }

        return self::findOne(['user.id' => $data['id']]);
    }

    public static function update_one($options, $criteria = []) {
        global $CONNECTION;
        $criteria_num = 0;

        $query = "UPDATE dealer SET ";

        if ($criteria != []) {
            foreach ($criteria as $field => $value) {
                if ($criteria_num != 0) {
                    $query .= ",";
                }
                $query .= " $field = '$value'";
                $criteria_num++;
            }  
        }

        $query .= " WHERE $options";

        $stmt = $CONNECTION->prepare($query);
        $stmt->execute();

        return $query;
    }
/*
    public static function getCompany($dealer_id) {
        global $CONNECTION;

        $stmt = $CONNECTION->prepare("SELECT dealer.*, company.name AS company_name FROM dealer LEFT JOIN company C ON dealer.company_id = C.id WHERE user_id = " . $dealer_id);
        $stmt->execute();

        return true;
    }
*/
    public static function delete($dealer_id) {
        global $CONNECTION;

        $stmt = $CONNECTION->prepare("DELETE FROM dealer WHERE user_id = " . $dealer_id);
        $stmt->execute();

        $stmt = $CONNECTION->prepare("DELETE FROM user WHERE id = " . $dealer_id);
        $stmt->execute();

        return true;
    }

    public static function disable($dealer_id, $enabled) {
        global $CONNECTION;

        if ($enabled == 1) {
            $enabled = 0;
        } else {
            $enabled = 1;
        }

        $query = "UPDATE dealer SET enabled = " . $enabled . " WHERE user_id = " . $dealer_id;
        $stmt = $CONNECTION->prepare($query);
        $stmt->execute();

        return $query;
    }
}
