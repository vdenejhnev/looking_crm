<?php

    require "../../settings.php";
    require "../../functions.php";

    // Вход на сайт
    if($_POST["methodName"] == "enter"){
        $login = clean($_POST["login"]);
        $pass = clean($_POST["pass"]);
        if($login == $LOGIN_ADMIN && $pass == $PASS_ADMIN){
            $pass = md5($pass.$SALT);
            setcookie("login", $login, time() + 6048000, "/");
            setcookie("pass", $pass, time() + 6048000, "/");
        }
    }
?>