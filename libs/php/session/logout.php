<?php
// Inicializar la sesión.
// Si está usando session_name("algo"), ¡no lo olvide ahora!
@session_start();

require_once ("../mysql-functions.php");
require_once ("../conn-data.php");

//DB::update("sys_usuarios", array("session-id" => ""), "id=%d",$_SESSION["user_id"]);

// Destruir todas las variables de sesión.
$ses = $_SESSION;
$_SESSION = array();

// Si se desea destruir la sesión completamente, borre también la cookie de sesión.
// Nota: ¡Esto destruirá la sesión, y no la información de la sesión!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruir la sesión.
session_destroy();
// poner en 0 is logged
header ("Location: /");
?>