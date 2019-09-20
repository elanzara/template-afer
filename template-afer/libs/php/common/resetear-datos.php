<?PHP
require_once("../mysql-functions.php");
require_once("../conn-data.php");
session_start();

if (!isset($_GET["segmento"])) {header("Location: /estado/?error&txt=Limpiar: no se especificó módulo");die();}

$modulo = $_GET["segmento"];
unset($_SESSION[$modulo]);

header("Location: /".$modulo."/?warning&txt=".ucfirst($modulo).": datos reseteados");
?>