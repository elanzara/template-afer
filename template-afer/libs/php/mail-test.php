<?php
require_once("mysql-functions.php");
require_once("conn-data.php");
require_once("common-functions.php");
require_once("mail-functions.php");

//AlertEmpresaMail(25);

$datos = DB::queryOneColumn("numero_pedido","SELECT numero_pedido FROM tango_pedidos
 WHERE estado_logistica NOT IN (12) 
 AND enabled = 1 
 AND modulo_actual NOT IN (0,9,19)");

 var_dump($datos);

?>