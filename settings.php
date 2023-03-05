<?php
// Отображение всех ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);

// для подключения к базе данных
$DB_SERVER   =   "localhost";
$DB_USER     =   "a0443796_tech";
$DB_PASSWORD =   "IUv7aOpC";
$DB_NAME     =   "a0443796_tech";

// полное имя сайта
$SERVER      =   "//$_SERVER[HTTP_HOST]/";

// Админская почта
$MAIL_ADMIN = "slavikgolos@gmail.com";

// Технические работы
$TECHNICAL_WORKS = false;

if (file_exists(__DIR__ . '/settings.local.php')) {
    include __DIR__ . '/settings.local.php';
}
