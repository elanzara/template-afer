<?php
@session_start(); 

  if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    header("Location: /estado/");
    die();
  }
  $_SESSION = array(); if (ini_get("session.use_cookies")) { $params = session_get_cookie_params(); setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"] );} session_destroy();
  const EMPRESA_RAZON_SOCIAL ="";
  require_once("common/header.php"); 
  ?>
  <body role="document">

<div class="container">

      <form class="form-signin col-xs-12 col-sm-offset-3 col-sm-6 col-md-offset-4 col-md-4" method="POST" action="/iniciar-sesion/">
        <h2 class="form-signin-heading"><img src='/img/logo.png' alt='K-PoS Royal Energy SA' class="img-responsive"/></h2>
        <?PHP 
  $text= "";
  if(isset($_GET['badLogin'])) $text = "Los datos ingresados no son v&aacute;lidos"; 
  if(isset($_GET['sessionExpired'])) $text = "Su sesi&oacute;n no est&aacute; iniciada o fue terminada"; 
  if(isset($_GET['sessionOut'])) {$ip = $_GET['addData'];$text = "Su sesi&oacute;n fue iniciada desde otra PC [IP: $ip]"; }
  if(isset($_GET['securityError'])) $text = "Intento de acceso a secci&oacute;n restringida registrado correctamente"; 
  if(isset($_GET['logoutSuccessful'])) $text = "Sesi&oacute;n cerrada correctamente"; 
  if(isset($_GET['ipBan'])) $text = "Su IP está bloqueada temporalmente. Intente más tarde.";
  if(isset($_GET['userBan'])) $text = "Su usuario está bloqueado temporalmente. Intente más tarde.";
  if(isset($_GET['ipBanuserBan'])) $text = "Su IP y usuario están bloqueados temporalmente. Intente más tarde.";?>

      <div class="row">
  <?php if ($text!= "") { ?>
  <div class="col-xs-12 bg-danger text-center info-text">
      <p><?php echo $text; ?></p>
    </div>
  <?php } ?> 
        <label for="username" class="sr-only">Usuario</label>
        <input type="text" id="username" name="username" class="form-control" placeholder="Ingrese nombre de usuario" required autofocus>
        <label for="password" class="sr-only">Contraseña</label>
        <input type="password" id="password" name="password" class="form-control" placeholder="Ingrese contraseña" required>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Iniciar sesión </button>
      </form>

    </div> <!-- /container -->

  </body>
</html>