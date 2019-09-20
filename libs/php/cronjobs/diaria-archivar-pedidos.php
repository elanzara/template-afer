Archivar: 
<?php
/* checkear que no sea sabado o domingo */
date_default_timezone_set('America/Buenos_Aires');

$start = microtime(true);
require_once("../mysql-functions.php");
require_once("../conn-data.php");
require_once("../mail-functions.php");
$timestamp = date("Y-m-d G:i:s");

/*
	Obtener todos los pedidos con etapa pedido finalizado y estado pedido entregado
	pedido finalizado: modulo_actual = 9
	pedido entregado: estado_logistica = 12
	id user sistema: 14
*/

$archivar = DB::queryOneColumn("id","SELECT id FROM tango_pedidos WHERE modulo_actual = 9 AND estado_logistica = 12");
$insert=array();

if (sizeof($archivar) > 0)
{
	
$upd = DB::update("tango_pedidos", array("modulo_actual"=>19,"ultimo_cambio"=>$timestamp,"usuario_uc"=>14),  "id IN %li0", $archivar);

/* para todos insertar un comentario */
foreach ($archivar as $value) {
	$insert[] = array("id_pedido"=>$value,
					  "id_usuario" => 14,
					  "id_modulo_origen"=>9,
					  "fecha" => $timestamp,
					  "comment" => "Pedido archivado automáticamente por el sistema el ".$timestamp
		);
	/*FinishMail($value);*/
}

DB::insert("sys_pedidos_comments", $insert);
}

echo "(".sizeof($insert).") Last update: ".$timestamp;
?>