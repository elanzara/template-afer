<?PHP
@session_start();
if (isset($_SESSION['user_id']) === false){header("HTTP/1.0 403");header("Location: /?sessionExpired&segmento=common");die();};
$request = DB::queryFirstRow("SELECT `session-id`,`last-ip` FROM `sys_usuarios` WHERE `id`=%d", $_SESSION["user_id"]);

if ($request["session-id"] != session_id() ){session_destroy();header("Location: /?sessionOut&addData=".$request["last-ip"]);die();};
?>