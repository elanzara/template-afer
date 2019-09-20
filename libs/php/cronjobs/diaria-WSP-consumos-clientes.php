<pre>
<?php

require_once("../mysql-functions.php");
require_once("../conn-data.php");
require_once("../common-functions.php");


$hoy = date("Y-m-d", strtotime("today"));
$ahora = date("G:i");
$hoy_2359 = date("Y-m-d", strtotime("today")). " ". $ahora;

$clientes = DB::query("SELECT id, razon_social, wsp_numero FROM pos_clientes WHERE wsp_envia = 1 AND wsp_numero != '' ");


foreach ($clientes as $key => $value) {
	# code...

$ventas = DB::query("SELECT pos_ventas_items.*, pos_ventas.comentario, pos_ventas.referencia as patente
	FROM pos_ventas_items
	INNER JOIN pos_ventas ON (pos_ventas.id = pos_ventas_items.id_venta)
	WHERE pos_ventas.datetime BETWEEN %s0 AND %s1 AND pos_ventas.id_cliente = %d2 AND es_remito = 0", $hoy, $hoy_2359, $value["id"]);

$facturas = DB::query("SELECT * FROM pos_ventas_fe_requests INNER JOIN pos_ventas ON (pos_ventas.id = pos_ventas_fe_requests.id_venta) WHERE pos_ventas.id_cliente = %d0 AND pos_ventas.datetime BETWEEN %s1 AND %s2 AND resultado = 'A'", $value["id"], $hoy, $hoy_2359);

if (sizeof($ventas) != 0 || sizeof($facturas) != 0)
{

$wsptexto = EMPRESA_RAZON_SOCIAL." - SUC. ". EMPRESA_SUCURSAL_NOMBRE."\n*".$value["razon_social"]."*: resumen ".date("d/m",strtotime("today"))." ".$ahora."\n\n";

if (sizeof($ventas) != 0)
{
	$wsptexto.="Consumos: \n";
	foreach ($ventas as $ll => $v) {
		$v["patente"] = "PATENTE: ".$v["patente"];
		$wsptexto .= "#".str_pad($v["id_venta"],5,0,STR_PAD_LEFT). " ".$v["descripcion"]." x ".number_format($v["cantidad"],2)." - $".number_format($v["total"],2)." (".$v["patente"]." - ".$v["comentario"].")\n";
	}
	$wsptexto.="\n";
}

if (sizeof($facturas) != 0)
{
	$wsptexto.="Comprobantes electrónicos: \n";
	foreach ($facturas as $ll => $v) {
		$wsptexto .=str_pad($v["afip_pv"],2,0,STR_PAD_LEFT)."-".str_pad($v["afip_numero_comprobante"],6,0,STR_PAD_LEFT)." - $".number_format($v["monto_total"],2)." ".$v["link"]."\n";
	}
}

$json = [];
$celulares = [];

$temp = explode(",",$value["wsp_numero"]);
$celulares = array_merge($celulares, $temp);

foreach ($celulares as $numero) {
	if (trim($numero) != "") $json[] = ["to"=>trim($numero), "text"=>$wsptexto];
}

$username = "bafsrl"; //usuario
$token 	 = "da47b083a28137ee75cc7de119d33544"; //token

$smsrespuesta = "not sent";
$json = json_encode($json);

echo EMPRESA_SUCURSAL_NOMBRE;
echo "<br/>";
echo($wsptexto);
echo "<br/>";
echo "<br/>";
//echo($json);
echo "<br/>";
echo "<br/>";

$smsrespuesta = "OK"; #file_get_contents("http://wspsrv.minte.com.ar/external.php?username=". urlencode($username) ."&token=". urlencode($token) . "&json=" .urlencode($json));


echo $smsrespuesta;
echo "<br/>";
echo "<br/>";

file_put_contents("wsp-logs/".$username.".txt", date("Y-m-d G:i:s")."\n", FILE_APPEND);
file_put_contents("wsp-logs/".$username.".txt", $wsptexto."\n", FILE_APPEND);
file_put_contents("wsp-logs/".$username.".txt", $json."\n", FILE_APPEND);
file_put_contents("wsp-logs/".$username.".txt", "Respuesta: ".$smsrespuesta."\n\n", FILE_APPEND);
}
}

?>