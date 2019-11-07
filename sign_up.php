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
    <link rel="stylesheet" type="text/css" href="css/authorization_module.css">
    <title>Регистрация</title>
    </head>
    <body>
        <ul class="nav">
            <li><a href="login.php" class="btn btn-default">Войти здесь</a></li>
            <li><a href="index.php" class="btn btn-default">Главная</a></li>
        </ul>
        
        <div id="container_signup">       
            <form action="private/DBManager.php?f=save_user" method="post" id="register-form">
          
                <h2 class="main_title">Зарегистрироваться</h2>
                
                <hr/>
                <ul id="signup_form">
                    <li><input type="email" class="form-control" placeholder="Email" name="email" required  /></li>
                    <li><input type="text"  class="form-control" placeholder="Имя пользователя" name="username" required /></li>
                    <li><input type="password" class="form-control" placeholder="Пароль" name="password" required  /></li>
                </ul>  
                
                <hr />  
                <button type="submit" name="btn-signup">Создать аккаунт</button>
              
            </form>
        </div>   
    
    </body>
</html>