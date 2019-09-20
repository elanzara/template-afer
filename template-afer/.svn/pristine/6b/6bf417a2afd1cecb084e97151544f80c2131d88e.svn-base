<?PHP
if (!(isset($_POST['username'],$_POST['password']))) {header("Location: /?badLogin");die();}

// var_dump(require_once ("../mysql-functions.php"));
require_once ("../mysql-functions.php");

require_once ("../conn-data.php");
require_once ("session-functions.php");

$result = DoLogin($_POST['username'],$_POST['password']);

if ($result === true) {
header("Location: /estado/");
}
else {
header("Location: /?badLogin");
}
?>