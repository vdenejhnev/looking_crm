<?php

namespace App;

abstract class Model {
    public static function find($filter = [], $options = []) {
        $stmt = static::execute(static::build_select(null, $filter, $options));
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return array_map(['static', 'make'], static::group_rows($rows));
    }

    public static function find_one($filter = [], $options = []) {
        $items = static::find($filter, array_merge($options, ['limit' => 2]));

        if (count($items) > 1) {
            throw new \Exception('More than one row found');
        }

        return $items ? current($items) : false;
    }

    public static function send_mail($to, $subject, $message) {
        $headers = 'From: spectech@spectech.com' . "\r\n" .
            'Reply-To: spectech@spectech.com' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        mail($to, $subject, $message, $headers);
    }

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

    protected static function build_query_tail($criteria = [], $options = [], $table_alias = null) {
        global $CONNECTION;

        $query = '';
        $expressions = [];

        if ($criteria) {
            foreach ($criteria as $field => $condition) {
                if (is_numeric($field)) {
                    if (!is_array($condition)) {
                        throw new \Exception('Only array-conditions are allowed with numeric keys');
                    }

                    $field = array_shift($condition);

                    if (count($condition) === 1) {
                        $condition = array_shift($condition);   
                    }
                }

                $field = strtr($field, ['`' => '', '.' => '`.`']);

                if (is_scalar($condition)) {
                    $expressions[] = sprintf(
                        "`%s` = '%s'",
                        $field,
                        $CONNECTION->real_escape_string($condition)
                    );
                } elseif (is_array($condition)) {
                    if (is_array($condition[0])) {
                        foreach ($condition as $condition_item) {                  
                            if (in_array($condition_item[0], ['<', '>', '<=', '>='])) {
                                $expressions[] = sprintf(
                                    "`%s` $condition_item[0] '%s'",
                                    $field,
                                    $CONNECTION->real_escape_string($condition_item[1])
                                );
                            }
                        }
                    } else {
                        if ($condition[0] === 'like') {
                            $expressions[] = sprintf(
                                "`%s` LIKE '%s'",
                                $field,
                                $CONNECTION->real_escape_string($condition[1])
                            );
                        } elseif ($condition[0] === 'not') {
                            $expressions[] = sprintf(
                                "`%s` <> '%s'",
                                $field,
                                $CONNECTION->real_escape_string($condition[1])
                            );
                        } elseif ($condition[0] === 'between') {
                            /*$expressions[] = sprintf(
                                "`%s` BETWEEN '%s' AND '%s'",
                                $field,
                                $condition[1],
                                $condition[2],
                            );*/
                        } elseif (in_array($condition[0], ['<', '>', '<=', '>='])) {
                            $expressions[] = sprintf(
                                "`%s` $condition[0] '%s'",
                                $field,
                                $CONNECTION->real_escape_string($condition[1])
                            );
                        } else {
                            throw new \Exception("Wrong type of array-condition value for field: $field");
                        }
                    }
                } else {
                    throw new \Exception("Wrong type of condition value for field: $field");
                }
            }

            if ($expressions) {
                $query .= ' WHERE(' . implode(')AND(', $expressions) . ')';
            }
        }

        if ($options['group'] ?? false) {
            $query .= " GROUP BY $options[group]";
        }

        if ($options['order'] ?? false) {
            $order = is_array($options['order']) ? $options['order'] : [$options['order']];
            $expressions = [];

            foreach ($order as $key => $value) {
                $expressions[] = is_numeric($key) ? "$value ASC" : "$key $value";
            }

            $query .= ' ORDER BY ' . implode(',', $expressions);
        }

        if ($options['order_by'] ?? false) {
            foreach($options['order_by'] as $order) {
                $query = ' ORDER BY ' . $order;
            }
           
        }

        if (isset($options['limit'])) {
            $query .= ' LIMIT ' . $CONNECTION->real_escape_string($options['limit']);
        }

        if (isset($options['offset'])) {
            $query .= ' OFFSET ' . $CONNECTION->real_escape_string($options['offset']);
        }

        return $query;
    }

    protected static function build_from($filter = [], $options = []) {
        $query = ' FROM `' . static::TABLES[0] . '`';

        foreach (static::TABLES as $table => $condition) {
            if (is_numeric($table)) {
                continue;
            }

            $query .= " $table ON ($condition)";
        }

        if (isset($options['with'])) {
            if (!is_array($options['with'])) {
                $options['with'] = [$options['with']];
            }

            foreach ($options['with'] as $link) {
                $tables = static::get_dependency($link, 'from');

                foreach ($tables as $what => $how) {
                    $query .= " $what ON ($how)";
                }
            }
       }

        return $query;
    }

    protected static function build_limit($filter, $options) {
        return '';
    }

    protected static function build_order($filter, $options) {
        $query = ' ORDER BY ' . $options['order_by'] . ' ASC';
        return $query;
    }

    protected static function build_select($fields, $filter, $options) {
        if (!$fields) {
            $fields = '`' . static::TABLES[0] . '`.*';
        }

        if (!is_array($fields)) {
            $fields = [$fields];
        }

        if (isset($options['with'])) {
            if (!is_array($options['with'])) {
                $options['with'] = [$options['with']];
            }

            foreach ($options['with'] as $link) {
                $dep_fields = static::get_dependency($link, 'fields');

                foreach ($dep_fields as $field => $alias) {
                    $fields[] = "$field AS $alias";
                }
            }
        }

        $query = 'SELECT ' . implode(',', $fields);
        $query .= static::build_from($filter, $options);
        $query .= static::build_where($filter, $options);
        $query .= static::build_order($filter, $options);
        $query .= static::build_limit($filter, $options);

        return $query;
    }

    protected static function build_where($filter, $options) {
        global $CONNECTION;

        $expressions = [];

        foreach ($filter as $field => $value) {
            $field = explode('.', $field);
            if (count($field) == 1) {
                array_unshift($field, static::TABLES[0]);
            }
            $field = implode(
                '.',
                array_map(function($part) {return "`$part`";}, $field)
            );

            $expressions[] = "$field='" . $CONNECTION->real_escape_string($value) . "'";
        }

        if (empty($expressions)) {
            return '';
        }

        return ' WHERE (' . implode(')AND(', $expressions) . ')';
    }

    public static function getTodayDate() {
        return date('Y-m-d');
    }

    public static function getFirstYearDate() {
        return date('Y-m-d', strtotime('01.01.'.date('Y')));
    }

    protected static function execute($query) {
        global $CONNECTION;

        var_dump($query);

        $stmt = $CONNECTION->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    protected static function get_dependency($name, $field = null) {
        if (!isset(static::DEPENDENCIES[$name])) {
            throw new \Exception("Unknown dependency $name in " . get_called_class());
        }

        $dependency = static::DEPENDENCIES[$name];

        return $field ? $dependency[$field] : $dependency;
    }

    protected static function group_rows($rows) {
        return $rows;
    }

}
