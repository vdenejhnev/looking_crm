<?php


require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../settings.php';
require_once __DIR__ . '/../../functions.php';

$names = ['Иван', 'Петр', 'Макар', 'Фрол', 'Сидор', 'Марк', 'Карл', 'Филипп', 'Егор', 'Максим'];
$families = array_map(function($name) {return "${name}ов";}, $names);
$suffixes = ['Лимитед', 'Девелопмент', 'Продактс', 'и партнёры', 'Моторс', 'и Ко', 'Студио', 'Юнайтед', 'Интернешнл', 'Констракшн'];
$cities = ['Москва', 'Санкт-Петербург', 'Ижевск', 'Барнаул', 'Ростов-на-Дону', 'Нижний Новгород', 'Сочи', 'Красноярск', 'Владивосток', 'New York'];
$users = array_filter(Model\User::find(), function($user) {return $user->role === 'dealer';});

for ($i = 1; $i <= 2000; $i++) {
    $data = [
        'city' => $cities[rand(0, count($cities) - 1)],
        'name' => sprintf(
            '%s %s',
            $names[rand(0, count($names) - 1)],
            $families[rand(0, count($families) - 1)]
        ),
        'user' => $users[rand(0, count($users) - 1)],
    ];

    if (rand(0, 1)) {
        $data['inn'] = rand(100000, 999999) . rand(100000, 999999);
        $data['company_name'] = sprintf(
            '%s %s',
            $families[rand(0, count($families) - 1)],
            $suffixes[rand(0, count($suffixes) - 1)]
        );
    }

    $data['phones'] = array_fill(0, rand(0, 3), '');
    foreach ($data['phones'] as $index => &$phone) {
        $phone = sprintf('+7(9%u)%u-%u-%u', rand(10,99),rand(100,999),rand(10,99),rand(10,99));
    }

    $data['emails'] = array_fill(0, rand(0, 3), '');
    foreach ($data['emails'] as $index => &$email) {
        $email = "lead${i}_$index@example.com";
    }

    if (rand(0, 1)) {
        $data['comment'] = "Здесь комментарий к лиду #$i";
    }

    Model\Lead::create($data);
}

$stmt = $CONNECTION->prepare('SELECT id FROM `lead`');
$stmt->execute();

foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $row) {
    $created_at = sprintf(
        '2022-%02u-%02u %02u:%02u:%02u',
        rand(1, 12),
        rand(1, 28),
        rand(0, 23),
        rand(0, 59),
        rand(0, 59)
    );

    $stmt = $CONNECTION->prepare('UPDATE `lead` SET created_at = ? WHERE id = ?');
    $stmt->bind_param('si', $created_at, $row['id']);
    $stmt->execute();
}
