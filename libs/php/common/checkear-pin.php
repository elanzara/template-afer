<?PHP 
require_once("../mysql-functions.php");
require_once("../conn-data.php");
require_once("../session/ajax-session-validator.php");


if (!isset($_POST["pin"])) { header("HTTP/1.0 400");die("400|Petición no válida");}
if (sizeof($_POST) != 1) { header("HTTP/1.0 400");die("400|Petición no válida");}
if (strlen($_POST["pin"]) != 4) { header("HTTP/1.0 400");die("400|PIN debe tener 4 dígitos");}

$pin = DB::queryFirstRow("SELECT id FROM sys_empleados_pin WHERE pin = %s0 AND sys_empleados_pin.id = (SELECT id FROM sys_empleados_pin t1 WHERE t1.id_empleado = sys_empleados_pin.id_empleado ORDER BY id DESC LIMIT 0,1)", $_POST["pin"]);

if (sizeof($pin) != 1)
{ header("HTTP/1.0 400");die("503|Pin no existe o corresponde a más de un empleado - consulte a administración");}
?>