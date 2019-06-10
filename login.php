<?php
    session_start();
    if (isset($_SESSION['session_id']) && $_SESSION['session_id']!=-1)
    {
        header("Location: main_view.php");
        exit;
    }
require_once 'private/DBManager.php';

//if (isset($_SESSION['userSession'])!="") {
//        header("Location: main_view.php");
// exit;
//}
//
 if (isset($_SESSION['session_id']) && $_SESSION['session_id'] == -1) {
  $msg = "<div class='alert alert-danger'>Неправильный email или пароль!</div>";
 }
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Login</title>
<!--<link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
<link href="css/bootstrap-theme.min.css" rel="stylesheet" media="screen"> -->
<link rel="stylesheet" href="css/authorization_module.css" type="text/css" />
</head>
<body>
 <ul class="nav">
  <li><a href="sign_up.php">Зарегистрироваться</a></li>
  <li><a href="index.php">Главная</a></li>
 </ul>
 
 <div class="signin-form">

 <div id="container_login">
      
        
        <?php
  if(isset($msg)){
   echo $msg;
  }
  ?>
        <form action="private/DBManager.php?f=check_login" method="post" id="login-form">
           <h2 class="main_title">Войти</h2><hr />
           
           <ul id="login_form">
            <li><input type="email" id="email" class="form-control" placeholder="Email address" name="email" required /></li>
            <li><input type="password" class="form-control" placeholder="Password" name="password" required /></li>
           </ul>
            
           <hr />
           
           <button type="submit" class="btn btn-default" name="btn-login" id="btn-login">Войти</button> 
        </form>

    </div>
    
</div>

</body>
</html>