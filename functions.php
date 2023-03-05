<?php
    require_once 'functions.form.php';

    // Назначает глобальную переменную сервера JS
    $SCRIPT = "SERVER = '".$SERVER."';";

    // Разбивает адрес
    {
        $catA = "";
        $catB = "";
        $catC = "";

        if ($_SERVER['REQUEST_URI'] != '/') {
            $url_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        	$uri_parts = explode('/', trim($url_path, ' /'));
        	$catA = array_shift($uri_parts) ?: '';
            $catB = array_shift($uri_parts) ?: '';
            $catC = array_shift($uri_parts) ?: '';
        }

        //$SCRIPT .= "catA = '".$catA."';";
        //$SCRIPT .= "catB = '".$catB."';";
        //$SCRIPT .= "catC = '".$catC."';";
    }

    // Соединяет с БД
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $GLOBALS['CONNECTION'] = $CONNECTION = mysqli_connect($DB_SERVER, $DB_USER, $DB_PASSWORD, $DB_NAME);
        if(mysqli_connect_errno($CONNECTION)) echo "Не удалось подключиться к MySQL: " . mysqli_connect_error();

        mysqli_query($CONNECTION, "SET NAMES utf8");
    }

    // Очищает входящие данные
    function clean($value){
        $value = trim($value);
        $value = stripslashes($value);
        $value = strip_tags($value);
        $value = htmlspecialchars($value);

        return $value;
    }

    // Оправляет почту
    function send_mail($to, $subject, $text){
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=utf-8\r\n";
        $headers .= "From: Робот <robot@SITENAME.RU>\r\n";

        mail($to, $subject, $text, $headers);
    }

    // Проверка входа на сайт
    if(isset($_COOKIE["login"]) AND isset($_COOKIE["pass"])){
        $login = clean($_COOKIE["login"]);
        $pass = clean($_COOKIE["pass"]);

        if($login == $LOGIN_ADMIN && $pass == md5($PASS_ADMIN.$SALT)){
            define("admin", $login);
        }
        else {
            setcookie("login","");
            setcookie("pass","");
        }
    }

    session_start();

?>