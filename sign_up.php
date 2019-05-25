<?php
    session_start();
    if (isset($_SESSION['session_id']) && $_SESSION['session_id']!=-1)
    {
        header("Location: main_view.php");
        exit;
    }
?>

<!DOCTYPE>
<html>
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" type="text/css" href="css/sign_up.css">
    <title>Регистрация</title>
    </head>
    <body>
        <div class="container">       
            <form action="private/DBManager.php?f=save_user" method="post" id="register-form">
          
                <h2>Зарегистрироваться</h2>
                
                <hr/>
                <ul>
                    <li><input type="email" class="form-control" placeholder="Email" name="email" required  /></li>
                    <li><input type="text"  class="form-control" placeholder="Имя пользователя" name="username" required /></li>
                    <li><input type="password" class="form-control" placeholder="Пароль" name="password" required  /></li>
                </ul>  
                
                <hr />  
                <button type="submit" name="btn-signup">Создать аккаунт</button>
              
            </form>
            
            <a href="login.php" class="btn btn-default" style="float:right;">Войти здесь</a>
            <a href="main_view.php" class="btn btn-default" style="float:left; margin: 5px">Просмотр файлов</a>
            <a href="index.php" class="btn btn-default" style="float:left; margin: 5px">Главная</a>
        </div>   
    
    </body>
</html>