<pre>
<?php

require_once("../libs/php/mysql-functions.php");
require_once("../libs/php/conn-data.php");
require_once("../libs/php/common-functions.php");

$celulares = WSP_CONSUMOS_DESTINOS;

$ayer = date("Y-m-d", strtotime("yesterday"));
$ayer_2359 = date("Y-m-d", strtotime("yesterday")). " 23:59:59";

$ventas_rango = DB::query("
  SELECT pos_articulos.descripcion, (SUM(pos_stock_movimientos.cantidad)*-1) AS total
  FROM pos_stock_movimientos
  INNER JOIN pos_articulos ON (pos_articulos.id = pos_stock_movimientos.id_articulo)
  WHERE pos_articulos.id < 100 AND (pos_stock_movimientos.datetime BETWEEN %s0 AND %s1)
  AND pos_stock_movimientos.id_segmento IN (9, 1, 7) AND pos_stock_movimientos.id_deposito IN %li2
  GROUP BY pos_articulos.descripcion", $ayer, $ayer_2359, POS_DEPOSITOS_PLAYA );

$wsptexto = "*EESS ".EMPRESA_SUCURSAL_NOMBRE." - Ventas ".date("d/m",strtotime("yesterday")).":* \n\n";

foreach ($ventas_rango as $key => $value) {
	$wsptexto.=str_replace([" ENERGY", " 95", " 98"], "", $value["descripcion"]). " ".number_format($value["total"],2,".","")." L\n";
}

$wsptexto = substr($wsptexto, 0, -2);

$json = [];

foreach ($celulares as $numero) {
	$json[] = ["to"=>$numero, "text"=>$wsptexto];
}

$username = WSP_USERNAME; //usuario
$token 	 = WSP_TOKEN; //token
$smsrespuesta = "not sent";
$json = json_encode($json);

echo EMPRESA_SUCURSAL_NOMBRE;
echo "<br/>";
echo($wsptexto);
echo "<br/>";
echo "<br/>";
echo($json);
echo "<br/>";
echo "<br/>";

$smsrespuesta = file_get_contents("http://wspsrv.minte.com.ar/external.php?username=". urlencode($username) ."&token=". urlencode($token) . "&json=" .urlencode($json));


echo $smsrespuesta;
echo "<br/>";
echo "<br/>";

file_put_contents("sms-logs/".$username.".txt", date("Y-m-d G:i:s")."\n", FILE_APPEND);
file_put_contents("sms-logs/".$username.".txt", $wsptexto."\n", FILE_APPEND);
file_put_contents("sms-logs/".$username.".txt", $json."\n", FILE_APPEND);
file_put_contents("sms-logs/".$username.".txt", "Respuesta: ".$smsrespuesta."\n\n", FILE_APPEND);

?>