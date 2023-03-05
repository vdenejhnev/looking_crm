<?php

// Модуль авторизации

$USER = Model\User::get_current();

if ($USER) {
    return;
}

$USER = Model\User::auth();

if (is_object($USER)) {
    header('Location: ' . ($USER['role'] === 'admin' ? '/cp/dealers' : '/'));
    die;
}
