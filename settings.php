<?php
// Отображение всех ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);

// для подключения к базе данных
$DB_SERVER   =   "localhost";
$DB_USER     =   "root";
$DB_PASSWORD =   "root";
$DB_NAME     =   "taxi-trust";

// полное имя сайта
$SERVER      =   "//$_SERVER[HTTP_HOST]/";

// Админская почта
$MAIL_ADMIN = "slavikgolos@gmail.com";

// Технические работы
$TECHNICAL_WORKS = false;

if (file_exists(__DIR__ . '/settings.local.php')) {
    include __DIR__ . '/settings.local.php';
}
