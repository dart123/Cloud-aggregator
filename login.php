<?php
session_start();
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
<link rel="stylesheet" href="css/login.css" type="text/css" />
</head>
<body>

<div class="signin-form">

 <div class="container">
      
        
        <?php
  if(isset($msg)){
   echo $msg;
  }
  ?>
        <form action="private/DBManager.php?f=check_login" method="post" id="login-form">
           <h2 class="form-signin-heading">Войти</h2><hr />
           <input type="email" id="email" class="form-control" placeholder="Email address" name="email" required />
           <input type="password" class="form-control" placeholder="Password" name="password" required />
           
           <hr />
           
           <button type="submit" class="btn btn-default" name="btn-login" id="btn-login">Войти</button> 
        </form>
        
            <a href="sign_up.php" class="btn btn-default" style="float:right;">Зарегистрироваться</a>
            <a href="index.php" class="btn btn-default" style="float:right; margin-right: 5px">Главная</a>

    </div>
    
</div>

</body>
</html>