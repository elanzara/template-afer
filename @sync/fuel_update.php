<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
<pre>
SINCRONIZANDO...
<?php
require_once("../libs/php/mysql-functions.php");
require_once("../libs/php/conn-data.php");

$date = date("Y-m-d G:i:s");

$last_synch = DB::query("
SELECT pos_articulos.descripcion, pos_articulos.id as id_articulo, IFNULL(cloud_fuel_updates.datetime, '0000-00-00 00:00:00') as datetime
FROM pos_articulos
LEFT JOIN cloud_fuel_updates ON (cloud_fuel_updates.id_articulo = pos_articulos.id AND cloud_fuel_updates.id_synch = (SELECT id_synch FROM cloud_fuel_updates t1 WHERE id_articulo = cloud_fuel_updates.id_articulo ORDER BY id_synch DESC LIMIT 1))
WHERE pos_articulos.id < 100 GROUP BY pos_articulos.descripcion
");


$id_synch = DB::queryFirstRow("SELECT IFNULL(MAX(id_synch),0) as id_synch FROM cloud_fuel_updates")["id_synch"] + 1;

$movimientos = array();
foreach ($last_synch as $key => $value) {
	# code...
$movimientos[] = DB::queryFirstRow("SELECT IFNULL(descripcion, %s2) as descripcion, IFNULL(id_articulo, %d3) as id_articulo, IFNULL(ABS(SUM(cantidad)),0) as litros, %d4 as id_synch, %s1 as datetime
	FROM pos_stock_movimientos 
	LEFT JOIN pos_articulos ON (pos_articulos.id = pos_stock_movimientos.id_articulo)

	WHERE datetime BETWEEN %s0 AND %s1 AND pos_articulos.descripcion = %s2 AND id_segmento IN (1,9)", $value["datetime"], $date, $value["descripcion"], $value["id_articulo"], $id_synch);
}

DB::insert("cloud_fuel_updates", $movimientos);
// hasta aca, insertar. ahora todoo lo que no este sincronizado lo mando por JSON

$not_synched = DB::query("
SELECT id, id_synch, datetime, id_articulo, descripcion, litros, %d0 as id_sucursal
FROM cloud_fuel_updates
WHERE synched = 0
", EMPRESA_ID_SUCURSAL);


$ids = array_keys(DBHelper::reIndex( $not_synched,"id"));

foreach ($not_synched as $key => $value) {
	unset($not_synched[$key]["id"]);
}

$json = json_encode($not_synched);


$url = "http://kalia.royal-energy.com.ar/@sync/listen-fuel.php";
$data = array('json' => $json, 'key' => md5("QUEBROLO#664"));
// use key 'http' even if you send the request to https://...
$options = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data)
    )
);

$context  = stream_context_create($options);
$result = @file_get_contents($url, false, $context);

if ($result === FALSE) {die("ERROR $url NO RESPONDE");}
else
{	
		 $status = explode("|", $result);

		 var_dump($status);
		 if ($status[0] = "200")
		 {
		 	DB::update("cloud_fuel_updates", ["synched"=>1, "synch_time"=> $date], "id IN %li0", $ids);
		}
}

?>
</pre>
</body>
</html>