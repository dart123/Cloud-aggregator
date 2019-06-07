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
//if (isset($_POST['btn-login'])) {
// 
// $email = strip_tags($_POST['email']);
// $password = strip_tags($_POST['password']);
// 
// $email = $conn->real_escape_string($email);
// $password = $conn->real_escape_string($password);
// 
// $query = $conn->query("SELECT user_id, email, password FROM users WHERE email='$email'");
// $row=$query->fetch_array();
// 
// $count = $query->num_rows; // if email/password are correct returns must be 1 row
// 
// if (password_verify($password, $row['password']) && $count==1) {
//  $_SESSION['userSession'] = $row['user_id'];
//    header("Location: main_view.php");
//  }
 if (isset($_SESSION['session_id']) && $_SESSION['session_id'] == -1) {
  $msg = "<div class='alert alert-danger'>Неправильный email или пароль!</div>";
 }
// $conn->close();
//}
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