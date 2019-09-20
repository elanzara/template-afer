QUEUE ALERTS: DISABLED (se encolan directamente)
<?php
die();
$start = microtime(true);
require_once("../mysql-functions.php");
require_once("../conn-data.php");
require_once("../mail-functions.php");

$log = array();
$log["modulo"] = "queue-alerts";
$log["timestamp"] = date("Y-m-d G:i:s");
$log["data"] = array();

$new_alerts = DB::queryOneColumn("id","SELECT id FROM sys_alertas WHERE fecha = %s AND status = 0", date("Y-m-d"));

$cant = sizeof($new_alerts);

if ($cant > 0)
{
	foreach ($new_alerts as $valor) {
		AlertMail($valor);
	}
	
	$log["data"]["found"] = $cant;
	DB::update("sys_alertas", array("status"=>1), "id IN %li0", $new_alerts);
}
else
{
	$log["data"]["found"] = 0;
	echo "Sin alertas ";
}

$log["data"]  = json_encode($log["data"]);
$log["exec_time"] =  microtime(true) - $start;
DB::insert("tango_updates_log", $log);
echo "Last update: ".$log["timestamp"];
?>