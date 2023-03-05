<?php

global $CONNECTION;

$names = ['Иван', 'Петр', 'Макар', 'Фрол', 'Сидор', 'Марк', 'Карл', 'Филипп', 'Егор', 'Максим'];
$families = array_map(function($name) {return "${name}ов";}, $names);
$suffixes = ['Лимитед', 'Девелопмент', 'Продактс', 'и партнёры', 'Моторс', 'и Ко', 'Студио', 'Юнайтед', 'Интернешнл', 'Констракшн'];
$cities = ['Москва', 'Санкт-Петербург', 'Ижевск', 'Барнаул', 'Ростов-на-Дону', 'Нижний Новгород', 'Сочи', 'Красноярск', 'Владивосток', 'New York'];

for ($i = 1; $i <= 100; $i++) {
    $family = $families[rand(0, count($families) - 1)];
    $dealer = Model\Dealer::create([
        'company_name' => $family . ' ' . $suffixes[rand(0, count($suffixes) - 1)],
        'name' => $names[rand(0, count($names) - 1)] . ' ' . $family,
        'city' => $cities[rand(0, count($cities) - 1)],
        'phone' => sprintf('+7(9%u)%u-%u-%u', rand(10,99),rand(100,999),rand(10,99),rand(10,99)),
        'email' => "user$i@example.com",
    ]);
}

$stmt = $CONNECTION->prepare('SELECT id FROM user WHERE role = "dealer"');
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

    $stmt = $CONNECTION->prepare('UPDATE user SET created_at = ? WHERE id = ?');
    $stmt->bind_param('si', $created_at, $row['id']);
    $stmt->execute();
}
