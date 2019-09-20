<?PHP
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') 
{
	session_start();
	if (isset($_SESSION['user_id']) === false){header("HTTP/1.0 403");die("403|Sesion no válida");};
	$request = DB::queryFirstRow("SELECT `session-id`,`last-ip` FROM `sys_usuarios` WHERE `id`=%d", $_SESSION["user_id"]);
	if ($request["session-id"] != session_id() ){CloseAll();header("HTTP/1.0 402");die("402|Sesion iniciada en otro dispositivo [IP:".$request["last-ip"]."]");};
}
else
{
header("HTTP/1.0 405");die("405|Bad Request");
}

function CloseAll()
{
@session_start();
$_SESSION = array(); if (ini_get("session.use_cookies")) { $params = session_get_cookie_params(); setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"] );} session_destroy();
}
?>