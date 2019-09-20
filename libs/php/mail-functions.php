<?php
require_once("mysql-functions.php");
require_once("conn-data.php");
require_once("common-functions.php");



define("header", "<table cellSpacing=0 cellPadding=0 width='100%' border=0 align='center'  style='background-color:#f2f2f2;'>
<tbody>
<tr><td style='background:white;padding:0 10px;'>
<div style='width:500px;height:100px;margin:0 auto;'>
<img src='http://sistemaexterno.com.ar/header-fenzi-new.jpg' alt='logo_fenzi'/>
</div></td></tr>
<tr><td style='background:#fff;padding:0 10px;'>
<div style='width:500px;background:#fff;margin:15px auto;font-family:Arial, sans-serif;'>");

define("footer", "</div><div style='color:#333;font-size:14px;line-height:20px;text-align:right;'>
<p>Atentamente,<br/><b>Fenzi South America</b></p></div></div></td></tr>");

function FinishMail($id_pedido, $replace = null)
{

  $datos_pedido = DB::queryFirstRow("SELECT tango_pedidos.*, abm_codigos_tango.valor as emails, abm_tipo_envio.valor as str_tipo_envio
    FROM tango_pedidos 
    INNER JOIN abm_codigos_tango ON (abm_codigos_tango.codigo_tango = tango_pedidos.codigo_cliente)
    INNER JOIN abm_tipo_envio ON (abm_tipo_envio.id = tango_pedidos.tipo_envio)
    WHERE tango_pedidos.id = %d0 AND abm_codigos_tango.valor <> '' ", $id_pedido);

  $destinos = explode(";", $datos_pedido["emails"]);
  foreach ($destinos as $key => $value) {
     $destinos[$key] = trim($value);
  }
  $destinos_oculto = array();
  $destinos_oculto[] = "sip@fenzisouthamerica.com";

  if ($replace != null)
  {
    $destinos = $replace;
  }

  if (sizeof($datos_pedido) > 0)
  {
    if ($datos_pedido["str_tipo_envio"] == "Flete") $datos_pedido["str_tipo_envio"] = "Trasporte propio";

    $tabla_items = DatosPedido($datos_pedido["numero_pedido"]);
  $body = "<h2 style='color:#000;font-size:20px;'>Estimado ".$datos_pedido["razon_social"].":</h2>
  <div style='color:#333;font-size:14px;line-height:20px;text-align:justify;'>
  <p>Por medio de la presente le informamos que su pedido #".$datos_pedido["numero_pedido"]." ha sido entregado en el d&iacute;a de la fecha.</p>
  <p><b>Entrega: </b>".$datos_pedido["str_tipo_envio"]."<br/><b>Bultos: </b>".$datos_pedido["bultos"]."</p>
  <p><b>Detalle del pedido:</b></p><table><thead><tr><th style='width:400px;text-align:center;'>Descripci&oacute;n</th><th style='text-align:right;width:100px'>Cantidad</th></tr></thead><tbody>";
  foreach ($tabla_items as $value) {
    $body .= "<tr><td style='width:400px;text-align:center;'>".$value["descripcion"]."</td><td style='text-align:right;width:100px'>".Number($value["cantidad_pedida"],2)."</td></tr>";
  }

  $body .= "</tbody></table><p>Muchas gracias por su compra.</p></div>";

  DB::insert("mail_queue",array(
    "from"=>"logistica@fenzisouthamerica.com",
    "to" => json_encode($destinos),
    "to_bcc"=>json_encode($destinos_oculto),
    "text" => header.$body.footer,
    "subject" => "Pedido #".$datos_pedido["numero_pedido"]." finalizado"));
}

else
{
  $log = array();
  $log["modulo"] = "mail-not-sent";
  $log["timestamp"] = date("Y-m-d G:i:s");
  $log["data"] = array("pedido"=>$id_pedido,"trama"=>"finish");
  $log["data"]  = json_encode($log["data"]);
  $log["exec_time"] =  0;
  DB::insert("tango_updates_log", $log);
}


}

function BirthdayMail($nombre, $apellido, $email)
{

$body = "<html xmlns:v='urn:schemas-microsoft-com:vml'>
    <head></head>
    <body>

    <div style='background-color:#ebda7b;width:654px;height:500px;padding:0 !important;'>
  <table height='500px' width='654px' cellpadding='0' cellspacing='0' border='0'>
  <!--[if (gte mso 9)|(IE)]>
    <v:rect style='width:654px;height:500px;' strokecolor='none'>
         <v:fill type='tile' color='#ebda7b' src='http://sistemaexterno.com.ar/cumple2.jpg' /></v:fill>
       </v:rect>
  <![endif]-->
    <tr>
      <td valign='middle' align='left' height='500px' width='654px' background='http://sistemaexterno.com.ar/cumple2.jpg'>
          <p style='font-size:2.5em;color:#FFF;text-shadow:2px 2px #000;padding-left:15px;font-family:Arial, sans-serif;height:500px;line-height:500px;margin:0 !important;'>".$nombre.",</p>
      </td>
      <!--[if (gte mso 9)|(IE)]>
      <v:shape id='NameHere' style='position:absolute;width:654px;height:250px;'>
        <p style='font-size:2.5em;color:#FFF;text-shadow:2px 2px #000;padding-left:15px;font-family:Arial, sans-serif;height:260px;line-height:240px;margin:0 !important;'>".$nombre."22,</p>
      </v:shape>
      <![endif]-->
    </tr>
  </table>
</div>
    </body>
</html>";



  DB::insert("mail_queue",array(
    "from"=>"fenzi@fenzisouthamerica.com",
    "to" => json_encode(array($email)),
    "to_bcc"=>json_encode(array("sip@fenzisouthamerica.com","nsantana@fenzisouthamerica.com")),
    "text" => $body,
    "subject" => "¡".$nombre.", Feliz Cumpleaños!"));
}

function StatusMail($id_pedido)
{
  $datos_pedido = DB::queryFirstRow("SELECT sys_access_token.id_pedido, sys_access_token.token, 
    tango_pedidos.razon_social,abm_codigos_tango.valor as emails, tango_pedidos.numero_pedido
    FROM tango_pedidos 
    INNER JOIN sys_access_token ON (sys_access_token.id_pedido = tango_pedidos.id)
    INNER JOIN abm_codigos_tango ON (abm_codigos_tango.codigo_tango = tango_pedidos.codigo_cliente)
    WHERE tango_pedidos.id =%d0 AND abm_codigos_tango.valor <> ''", $id_pedido);

$destinos = explode(";", $datos_pedido["emails"]);
  foreach ($destinos as $key => $value) {
     $destinos[$key] = trim($value);
  }
  $destinos= array();
  $destinos[] = "info_fenzi@afer.com.ar";

if (sizeof($destinos) > 0)
{
  $hash = base64_encode($id_pedido."|".$datos_pedido["token"]);

  $body = "<h2 style='color:#000;font-size:20px;'>Estimado ".$datos_pedido["razon_social"].":</h2>
  <div style='color:#333;font-size:14px;line-height:20px;text-align:justify;'>
  <p>Su pedido #".$datos_pedido["numero_pedido"]." ha sido ingresado en nuestro sistema.</p>
  <p>Puede verificar el estado de preparaci&oacute;n en tiempo real del mismo ingresando al siguiente link:</p>
  <p><b><a href='http://fenzi.ddns.net:665/externo/estado-pedido/".$hash."/'>http://fenzi.ddns.net:665/externo/estado-pedido/".$hash."/</a></b></p>
  <p>Si el link anterior no funciona, copie y pegue el texto en la barra de direcciones de su navegador, luego apriete la tecla ENTER</p>";

   DB::insert("mail_queue",array(
    "from"=>"fenzi@fenzisouthamerica.com",
    "to" => json_encode($destinos),
    "to_bcc"=>"[]",
    "text" => header.$body.footer,
    "subject" => "Pedido #".$datos_pedido["numero_pedido"]." disponible"));
}
else
{
  $log = array();
  $log["modulo"] = "mail-not-sent-no-tango-codigo-email";
  $log["timestamp"] = date("Y-m-d G:i:s");
  $log["data"] = array("pedido"=>$id_pedido,"trama"=>"status");
  $log["data"]  = json_encode($log["data"]);
  $log["exec_time"] =  0;
  DB::insert("tango_updates_log", $log);
}
}

function InactivityMail($id_pedido)
{

  $datos_pedido = DB::queryFirstRow("SELECT tango_pedidos.*, sys_modulos.valor as str_modulo_actual
    FROM tango_pedidos 
    INNER JOIN sys_modulos ON (sys_modulos.id = tango_pedidos.modulo_actual)
    WHERE tango_pedidos.id =%d0", $id_pedido);


  $mails= array();
  $mails[] = "a.hampel@fenzisouthamerica.com";
  $mails[] = "r.toledo@fenzisouthamerica.com";
  $mails[] = "c.vargas@fenzisouthamerica.com";
  $mails[] = "d.albornoz@fenzisouthamerica.com";
  $mails[] = "l.beltran@fenzisouthamerica.com";
  $mails[] = "p.soifer@fenzisouthamerica.com";
	$mails[] = "f.kartofel@fenzisouthamerica.com";
	$mails[] = "c.bernardi@fenzisouthamerica.com";

  if (sizeof($mails) > 0)
  {

  $body = "<h2 style='color:#000;font-size:20px;'>Alerta de inactividad:</h2>
  <div style='color:#333;font-size:14px;line-height:20px;text-align:justify;'>El pedido #".$datos_pedido["numero_pedido"]." de ".$datos_pedido["razon_social"]." ha cumplido m&aacute;s de 72 horas sin cambios. El mismo se encuentra en el m&oacute;dulo <b>".$datos_pedido['str_modulo_actual']."</b>
  <p><b>Link acceso: </b><a href='http://192.168.0.10:665/pedidos-en-curso/editar/".$id_pedido."/'>Interno a FENZI</a> | <a href='http://fenzi.ddns.net:665/pedidos-en-curso/editar/".$id_pedido."/'>Externo a FENZI</a></p>
  .</div>";

  DB::insert("mail_queue",array(
    "from"=>"sip@fenzisouthamerica.com",
    "to" => json_encode($mails),
    "to_bcc"=>"[]",
    "text" => header.$body.footer,
    "subject" => "Pedido #".$datos_pedido["numero_pedido"]." inactivo"));
}
}

function AlertMail($id_alerta)
{

  $datos_alerta = DB::queryFirstRow("SELECT CONCAT(t1.nombre, ' ', t1.apellido) AS  str_user_from,
    texto, added,fecha, id_user_to, id_pedido,
    tango_pedidos.numero_pedido, tango_pedidos.razon_social
    FROM sys_alertas 
    INNER JOIN sys_usuarios_datos t1 ON (t1.id_usuario = sys_alertas.id_user_from)
    INNER JOIN tango_pedidos ON (tango_pedidos.id = sys_alertas.id_pedido)
    WHERE sys_alertas.id = %d0
    ", $id_alerta);

$id_usuarios_to = json_decode($datos_alerta["id_user_to"],true);
$mail_to_list = DB::queryOneColumn("email","SELECT email FROM sys_usuarios_datos WHERE id_usuario IN %li0",$id_usuarios_to );

$id_pedido = $datos_alerta["id_pedido"];

  if (sizeof($datos_alerta) > 0)
  {
  $body = "<h2 style='color:#000;font-size:20px;'>Atenci&oacute;n:</h2>
  <div style='color:#333;font-size:14px;line-height:20px;text-align:justify;'>
  <p>".$datos_alerta["str_user_from"]." recuerda</p>
  <p><b>Pedido #".$datos_alerta["numero_pedido"]." de ".$datos_alerta["razon_social"]."</b></p>
  <p><b>Fecha de programaci&oacute;n: </b>".$datos_alerta["fecha"]."</p>
  <p><b>Link acceso: </b><a href='http://192.168.0.10:665/pedidos-en-curso/editar/".$id_pedido."/'>Interno a FENZI</a> | <a href='http://fenzi.ddns.net:665/pedidos-en-curso/editar/".$id_pedido."/'>Externo a FENZI</a></p>
  <p><b>Texto: </b>".$datos_alerta["texto"]."</p>";

  DB::insert("mail_queue",array(
    "from"=>"sip@fenzisouthamerica.com",
    "to" => json_encode($mail_to_list),
    "to_bcc"=>"[]",
    "text" => header.$body.footer,
    "subject" => "Alerta Pedido #".$datos_alerta["numero_pedido"]));
}

}


function DatosPedido($pedido_tango)
{
$serverName = "SERVER\AXSQLEXPRESS";
$connectionInfo = array( "Database"=>"Fenzi_2006");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}

$sql = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED
SET DATEFORMAT DMY 
SET DATEFIRST 7 
SELECT 
  GVA03.COD_ARTICU_KIT AS [codigo_articulo_kit] ,
  GVA03.N_RENGLON AS [Renglón] ,
  GVA03.TALON_PED AS [talonario_pedido] ,
  GVA03.NRO_PEDIDO AS [numero_pedido] ,
  CASE GVA03.Cod_Articu WHEN '' THEN '' ELSE GVA03.Cod_Articu  END AS [codigo_articulo] ,
  CASE WHEN GVA03.COD_ARTICU = '' THEN GVA45.[DESC] WHEN GVA45.T_COMP = 'PED' AND GVA03.NRO_PEDIDO = GVA45.N_COMP AND GVA03.N_RENGLON =GVA45.N_Renglon THEN GVA45.[DESC] ELSE STA11.DESCRIPCIO END AS [descripcion] ,

  GVA03.CANT_PEDID AS [cantidad_pedida] ,
  GVA03.PRECIO AS [precio_unitario] ,
  GVA03.DESCUENTO AS [descuento] ,
  ((CASE GVA10.MON_CTE WHEN 1 THEN GVA03.PRECIO ELSE (GVA03.PRECIO * GVA21.Cotiz)END) * (1 - (GVA03.DESCUENTO) / 100)) * (GVA03.Cant_Pedid) AS [importe]
  
FROM 
GVA03 INNER JOIN GVA21 ON (GVA21.TALON_PED = GVA03.TALON_PED AND  GVA21.NRO_PEDIDO = GVA03.NRO_PEDIDO) LEFT JOIN STA11 (NOLOCK) ON gva03.COD_ARTICU = STA11.Cod_Articu INNER JOIN GVA10 ON GVA21.N_Lista = GVA10.Nro_de_Lis LEFT JOIN GVA81 ON GVA81.COD_CLASIF = GVA03.COD_CLASIF LEFT JOIN GVA45 ON GVA45.TALONARIO = GVA03.TALON_PED AND GVA45.T_COMP = 'PED' AND GVA03.NRO_PEDIDO = GVA45.N_COMP AND GVA03.N_RENGLON =GVA45.N_Renglon LEFT JOIN MEDIDA ON MEDIDA.ID_MEDIDA = GVA03.ID_MEDIDA_STOCK LEFT JOIN MEDIDA MEDIDA_STOCK_2 ON MEDIDA_STOCK_2.ID_MEDIDA = GVA03.ID_MEDIDA_STOCK_2 LEFT JOIN MEDIDA MEDIDA_VENTAS ON MEDIDA_VENTAS.ID_MEDIDA = GVA03.ID_MEDIDA_VENTAS
WHERE 

(((GVA03.RENGL_PADR = 0) and (GVA03.PROMOCION = 0)) or (GVA03.PROMOCION = 1)) AND (GVA03.NRO_PEDIDO = ' ".str_pad($pedido_tango,12,"0",STR_PAD_LEFT)."') AND (GVA03.TALON_PED = '18')

ORDER BY 
  [Renglón],[COD_ARTICU_KIT] ";

  $stmt = sqlsrv_query( $conn, $sql );
if( $stmt === false) {
  echo "errror<br/>";
  echo $sql."<br/>";
    die( print_r( sqlsrv_errors(), true) );
}
$items_pedido=array();
while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
 $items_pedido[] = $row;
}
sqlsrv_free_stmt($stmt);

return $items_pedido;
}
?>