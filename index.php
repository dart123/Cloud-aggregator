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
        <link rel="stylesheet" type="text/css" href="css/index.css">
        <title>Агрегатор облачных хранилищ</title>
    </head>
    <body>
        <ul>
            <!--<li><a href="main_view.php" class="btn btn-default">Просмотр файлов</a></li>-->
            <li><a href="login.php" class="btn btn-default">Войти</a></li>
            <li><a href="sign_up.php" class="btn btn-default">Зарегистрироваться</a></li>
        </ul>
        <h1 id="main_title">Агрегатор облачных хранилищ</h1>
    </body>
</html>