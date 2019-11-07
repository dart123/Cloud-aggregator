<?php
    session_start();
    if (isset($_SESSION['session_id']) && $_SESSION['session_id']!=-1)
    {
        header("Location: main_view.php");
        exit;
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="X-UA-Compatible" content="IE9">
        <!--<link rel="stylesheet" type="text/css" href="libs/bootstrap/css/bootstrap.min.css">-->
        <link rel="stylesheet" type="text/css" href="css/authorization_module.css">
        <title>Агрегатор облачных хранилищ</title>
    </head>
    <body>
        <ul class="nav">
            <!--<li><a href="main_view.php" class="btn btn-default">Просмотр файлов</a></li>-->
            <li><a href="login.php">Войти</a></li>
            <li><a href="sign_up.php">Зарегистрироваться</a></li>
        </ul>
        <h1 class="main_title">Агрегатор облачных хранилищ</h1>
    </body>
</html>