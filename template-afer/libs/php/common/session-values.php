<?PHP

require_once("../mysql-functions.php");
require_once("../conn-data.php");
require_once("../session/ajax-session-validator.php");

$modulo = explode("/", $_SERVER["HTTP_REFERER"])[3];

$_SESSION[$modulo][$_POST["key"]] = $_POST["value"];

header("HTTP/1.0 200");die("200|OK|$modulo");

?>