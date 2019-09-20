<?php
require_once("../common-functions.php");
require_once("../mysql-functions.php");
require_once("../conn-data.php");

@session_start();
unset($_SESSION["factura_electronica"]);
if (isset($_SESSION["factura_electronica"])) die("Factura electrónica en proceso");


$_GET["id"] = DB::queryFirstRow("SELECT id_venta 
  FROM pos_ventas_comprobante 
  INNER JOIN pos_ventas ON (pos_ventas.id = pos_ventas_comprobante.id_venta)
  WHERE pos_ventas.es_devolucion = 0 AND comprobante_pv = 0 AND comprobante_numero = 0 AND id_comprobante_tipo IN (1,2,3,6,7,8,51,52,53) AND IFNULL((SELECT id FROM pos_ventas t1 WHERE t1.id_venta_devuelta = pos_ventas_comprobante.id_venta),0) = 0 AND pos_ventas.id_usuario = 664 ORDER BY pos_ventas.id ASC")["id_venta"];

$_SESSION["factura_electronica"] = true;

if ($_GET["id"]==null) die("nada");


$datos_comprobante = DB::queryFirstRow("SELECT pos_ventas_comprobante.id_comprobante_tipo, pos_ventas.id,pos_ventas.id_usuario, pos_ventas.comentario, pos_ventas_comprobante.id as id_cbte, pos_ventas.es_devolucion, pos_ventas.id_venta_devuelta,
   IFNULL((SELECT id FROM pos_ventas t1 WHERE t1.id_venta_devuelta = pos_ventas.id),0) as fue_devuelta,

  77 as pv_facelec, abm_comprobante_tipo.abreviatura
 FROM pos_ventas 
  LEFT JOIN pos_ventas_comprobante ON (pos_ventas.id = pos_ventas_comprobante.id_venta)
  INNER JOIN abm_comprobante_tipo ON (abm_comprobante_tipo.id = pos_ventas_comprobante.id_comprobante_tipo) 
  WHERE pos_ventas.id = %d0 AND pos_ventas_comprobante.id_venta = %d0",$_GET["id"]);
if (sizeof($datos_comprobante) == 0) {echo $_GET["id"]. " no existe"; die();}



$datos_cbte = DB::queryFirstRow("SELECT date, razon_social, id_documento_tipo, documento_numero, id_iva_situacion, id_moneda_tipo, moneda_tipo_cambio FROM pos_ventas_comprobante WHERE id_venta = %d0",$_GET["id"]);

$tieneCAE = DB::queryFirstRow("SELECT link, local_path FROM pos_ventas_fe_requests WHERE id_venta = %d0 AND fallo = 0 AND cae_numero != 0", $_GET["id"]);

if (sizeof($tieneCAE)>0)
{
  $path = "/var/www/pdf/";
  if (isset($_GET["forceDownload"]))
  {
    @unlink($path.$tieneCAE["local_path"]);
  }

  if (!file_exists($path.$tieneCAE["local_path"]))
  {
      file_put_contents($path.$tieneCAE["local_path"], fopen($tieneCAE["link"], 'r'));
  }
  unset($_SESSION["factura_electronica"]);
  ForcePDFDownload($path, $tieneCAE["local_path"]);
}

extract($datos_cbte);
$date = str_replace("-", "", $date);


/*  =================== CABECERA =================== */
$datos_venta = DB::queryFirstRow("SELECT 

  pos_clientes.razon_social, pos_clientes.direccion, pos_clientes.direccion_cp, abm_provincia.valor as str_provincia, pos_clientes.documento_numero, pos_clientes.id_documento_tipo, pos_clientes.id_iva_situacion,pos_ventas.id_detalle_mayorista,pos_ventas.id_detalle_remito,

  pos_ventas.id, pos_ventas.es_devolucion, vta_dev_cbte.id_comprobante_tipo as vta_dev_id_cbte,  vta_dev_cbte.comprobante_pv as vta_dev_pv, vta_dev_cbte.comprobante_numero as vta_dev_num,

  (SELECT SUM(pos_ventas_items.neto_gravado * ABS(pos_ventas_items.cantidad)) FROM pos_ventas_items WHERE pos_ventas_items.id_venta = pos_ventas.id GROUP BY pos_ventas_items.id_venta) as neto_gravado,
  (SELECT SUM(pos_ventas_items.no_gravado * ABS(pos_ventas_items.cantidad)) FROM pos_ventas_items WHERE pos_ventas_items.id_venta = pos_ventas.id GROUP BY pos_ventas_items.id_venta) as no_gravado,
  (SELECT SUM(pos_ventas_items.neto_exento * ABS(pos_ventas_items.cantidad)) FROM pos_ventas_items WHERE pos_ventas_items.id_venta = pos_ventas.id GROUP BY pos_ventas_items.id_venta) as neto_exento,
  (SELECT SUM(pos_ventas_items.otros_impuestos * ABS(pos_ventas_items.cantidad)) FROM pos_ventas_items WHERE pos_ventas_items.id_venta = pos_ventas.id GROUP BY pos_ventas_items.id_venta) as otros_impuestos,
  (SELECT SUM(pos_ventas_items.iva * ABS(pos_ventas_items.cantidad)) FROM pos_ventas_items WHERE pos_ventas_items.id_venta = pos_ventas.id GROUP BY pos_ventas_items.id_venta)  as iva,
  (SELECT SUM(pos_ventas_items.itc * ABS(pos_ventas_items.cantidad)) FROM pos_ventas_items WHERE pos_ventas_items.id_venta = pos_ventas.id GROUP BY pos_ventas_items.id_venta) as itc,
  (SELECT SUM(pos_ventas_items.igo * ABS(pos_ventas_items.cantidad)) FROM pos_ventas_items WHERE pos_ventas_items.id_venta = pos_ventas.id GROUP BY pos_ventas_items.id_venta) as igo,
  (SELECT SUM(pos_ventas_items.th * ABS(pos_ventas_items.cantidad)) FROM pos_ventas_items WHERE pos_ventas_items.id_venta = pos_ventas.id GROUP BY pos_ventas_items.id_venta) as th,
  (SELECT SUM(pos_ventas_items.total) FROM pos_ventas_items WHERE pos_ventas_items.id_venta = pos_ventas.id GROUP BY pos_ventas_items.id_venta) as total_venta
  FROM pos_ventas
  LEFT JOIN pos_ventas_comprobante vta_dev_cbte ON (pos_ventas.id_venta_devuelta = vta_dev_cbte.id_venta)
  INNER JOIN pos_clientes ON (pos_clientes.id = pos_ventas.id_cliente)
  INNER JOIN abm_provincia ON (abm_provincia.id = pos_clientes.id_provincia)
  WHERE pos_ventas.id = %d0", $_GET["id"]);

/*  =================== ITEMS/OTRIMP =================== */
$items = DB::query("SELECT pos_ventas_items.descripcion, pos_articulos.id, pos_articulos.id_unidad_medida, IFNULL(abm_iva_alicuota.id,0) as id_alicuota,
  ABS(pos_ventas_items.cantidad) as cantidad, 
  (pos_ventas_items.neto_gravado + pos_ventas_items.no_gravado + pos_ventas_items.neto_exento ) as precio_individual, 
  pos_ventas_items.iva, itc, th, igo, pos_ventas_items.neto_gravado, pos_ventas_items.otros_impuestos
  FROM pos_ventas_items
  LEFT JOIN pos_articulos ON (pos_articulos.id = pos_ventas_items.id_articulo)
  INNER JOIN abm_iva_alicuota ON ((abm_iva_alicuota.porcentaje = pos_ventas_items.iva_alicuota) AND abm_iva_alicuota.enabled = 1)
  WHERE pos_ventas_items.id_venta = %d0", $_GET["id"]);

if (sizeof($items) == 0)
{
  $return = ["observaciones"=>"Venta #".$_GET["id"]." no tiene ítems. Contacte con soporte técnico"];
  $server_output = "Triggered error";
   LogCSV($_GET["id"].";BAD;Venta sin items\n");
     die();
}

if ($datos_venta["total_venta"] <= 5000 && in_array($datos_comprobante["id_comprobante_tipo"], array(6,7,8)))
{
  $documento_numero = $datos_venta["documento_numero"];
  $id_documento_tipo = $datos_venta["id_documento_tipo"];
}
if ($datos_venta["total_venta"] <= 5000 && in_array($datos_comprobante["id_comprobante_tipo"], array(6,7,8)))
{
  $documento_numero = 0;
  $id_documento_tipo = 99;
}

$str_cabecera = $datos_comprobante["pv_facelec"].",".$datos_comprobante["id_comprobante_tipo"].",1,". $date.",,,,".
 $documento_numero.",". $id_documento_tipo.",". number_format($datos_venta["neto_gravado"],2,".","").",".
  number_format($datos_venta["no_gravado"],2,".","").",". number_format($datos_venta["neto_exento"],2,".","").","
  .number_format(($datos_venta["neto_gravado"] + $datos_venta["no_gravado"] + $datos_venta["neto_exento"] + $datos_venta["iva"] + $datos_venta["otros_impuestos"] + $datos_venta["itc"]+ $datos_venta["igo"]+ $datos_venta["th"]),2,".","").",".
  $id_moneda_tipo.",". $moneda_tipo_cambio."\r\n";


/*  =================== IVA =================== */
$iva = DB::query("SELECT SUM(neto_gravado*ABS(cantidad)) as neto_gravado, SUM(iva * ABS(cantidad)) as iva, iva_alicuota, abm_iva_alicuota.id as id_alicuota FROM pos_ventas_items INNER JOIN abm_iva_alicuota ON ((abm_iva_alicuota.porcentaje = pos_ventas_items.iva_alicuota) AND abm_iva_alicuota.enabled = 1) WHERE id_venta = %d0 GROUP BY iva_alicuota",$_GET["id"]);

$str_iva = "";
foreach ($iva as $llave => $valor) {
	//id_alicuota, mng, total iva
	$str_iva .= $valor["id_alicuota"].",".number_format($valor["neto_gravado"],2,".","").",".number_format($valor["iva"],2,".","")."\r\n";
}




$str_items = "";
$impuestos = array("itc"=>array("base_imponible"=>0,"total"=>0, "codigo"=> "04"),"otros_impuestos"=>array("base_imponible"=>0,"total"=>0, "codigo"=> "99"),"igo"=>array("base_imponible"=>0,"total"=>0, "codigo"=> "04"),"th"=>array("base_imponible"=>0,"total"=>0, "codigo"=> "04"));
foreach ($items as $key => $value) {
  $total_unitario = $value["precio_individual"];
  $keys = array_keys($impuestos);
  $str_descripcion = $value["descripcion"];
  $str_impu = array();
  foreach ($keys as $nombre) {
    if ($value[$nombre] > 0)
    {
      $text = $nombre;
      if ($text == "th") $text = "ICO2_N";
      if ($text == "igo") $text = "ICO2_G";

      $str_impu[] = strtoupper($text) . " x L: $".number_format($value[$nombre],4);
      $impuestos[$nombre]["base_imponible"] += $value["neto_gravado"]*$value["cantidad"];
      $impuestos[$nombre]["total"] += $value[$nombre]*$value["cantidad"];
    }
  }
  
  if (sizeof($str_impu) > 0) 
  {
    $str_impu = implode(" - ", $str_impu);
    $str_descripcion .= " (".$str_impu.")";
  }

  if (!in_array($datos_comprobante["id_comprobante_tipo"], array(1,2,3,51,52,53)))
  {
    /*si no es comprobante A o M, sumar el iva en el precio individual*/
    $total_unitario += $value["iva"];
  }


$str_items .= 
str_pad($value["id"], 5,"0", STR_PAD_LEFT).",".
str_replace(",", ";", $str_descripcion).",".
$value["id_unidad_medida"].",".
$value["id_alicuota"].",".
$value["cantidad"].",".
number_format($total_unitario,10,".","").",0,".//0 = bonificacion
number_format($value["iva"]*$value["cantidad"],10,".","").",".
number_format($total_unitario*$value["cantidad"],10,".","").",".//0 = bonificacion
str_pad($value["id"], 5,"0", STR_PAD_LEFT).",".
str_pad($value["id"], 5,"0", STR_PAD_LEFT)."\r\n";
}

//var_dump($str_items);die();


/*  =================== OTRIMP =================== */
$str_otrosimp = "";
foreach ($impuestos as $key => $value) {
  if ($value["base_imponible"] > 0)
  {
    $text = $key;
    if ($text == "th") $text = "ICO2_NAFTAS";
    if ($text == "igo") $text = "ICO2_GASOIL";

  $alicuota  = number_format($value["total"] / $value["base_imponible"],2,".","");
  $str_otrosimp .= $value["codigo"].",".strtoupper($text).",".number_format($value["base_imponible"],2,".","").",".$alicuota.",".number_format($value["total"],2,".","")."\r\n";
  }
}

/*  =================== BUYER =================== */
//"buyer":"JUAN PATALANO E HIJOS SRL, Acceso Martin Fierro 1870, Allen (8328) - Rio Negro, 30641773995, EFECTIVO CONTRA ENTREGA, 1\r\n"
$str_buyer=
str_replace(",","",$datos_venta["razon_social"]).",".
str_replace(",","",$datos_venta["direccion"]).",".
str_replace(",","",$datos_venta["str_provincia"]).",".
str_replace(",","",$datos_venta["documento_numero"]).",".
"EFECTIVO,".$datos_venta["id_iva_situacion"]."\r\n";

/*  =================== COMPASOC =================== */
$str_compasoc = "";
if ($datos_venta["es_devolucion"] == "1")
{
$str_compasoc = $datos_venta["vta_dev_id_cbte"].",".$datos_venta["vta_dev_pv"].",".$datos_venta["vta_dev_num"]."\r\n";
}


/*  =================== VARIOS =================== */
$produccion = false; //sin comillas false es modo test, true es produccion
#if (defined("FE_PRODUCCION")) $produccion = FE_PRODUCCION;

$license = FE_LICENSE;
$beacon = base64_encode("0000".EMPRESA_CUIT."|".date("Y-m-d G:i:s")."|".RandomString(6));

//die("seguridad on");
//uncomment 139 138 para salir con afer
//$license = array("codigo"=>"0000","CUIT-emisor"=>"20352660362","hash"=>"f7490596a913f06a4a3942dd52171b84");
//$beacon = base64_encode("000020352660362|".date("Y-m-d G:i:s")."|".RandomString(6));



/* 
echo "<br/>cabecera:";echo(nl2br($str_cabecera));
echo "<br/>buyer:";echo(nl2br($str_buyer));
echo "<br/>compasoc:";echo(nl2br($str_compasoc));
echo "<br/>items:";echo(nl2br($str_items));
echo "<br/>otrimp:";echo(nl2br($str_otrosimp));
echo "<br/>iva: ";echo(nl2br($str_iva));
die();
*/

$json = json_encode(array(
"cabecera"=>$str_cabecera,
"iva"=>$str_iva,
"compasoc"=>$str_compasoc,
"items"=>$str_items,
"buyer"=>$str_buyer,
"otrosimp"=>$str_otrosimp,
"beacon"=>$beacon,
"license"=>$license,
"produccion"=>$produccion
));


/*echo "<pre>";
print_r($json);
echo "</pre>";
die();*/

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL,"http://fe.minte.com.ar/wsfev1.php");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
curl_setopt($ch, CURLOPT_TIMEOUT, 120);
curl_setopt($ch, CURLOPT_POSTFIELDS,$json);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

session_write_close();
$server_output = curl_exec ($ch);
session_start();

curl_close ($ch);

$return = json_decode($server_output,true);

if ($return)
{

  if (!isset($return["id_comprobante"])) $return["id_comprobante"] = 0;
  if (!isset($return["afip_numero_comprobante"])) $return["afip_numero_comprobante"] = 0;
  
  unset($return["proccess_result"]); //al parecer, no tan negrada

  $name = str_pad($_GET["id"],5,"0", STR_PAD_LEFT)."-".str_replace(" ", "", $datos_comprobante["abreviatura"])."-".str_pad( $datos_comprobante["pv_facelec"],4,"0", STR_PAD_LEFT)."-".str_pad($return["afip_numero_comprobante"],8,"0", STR_PAD_LEFT).".pdf";
  $return["id_feminte"] = $return["id_comprobante"];
  $return["afip_pv"] = $datos_comprobante["pv_facelec"];
  $return["id_venta"] = $_GET["id"];
  $return["local_path"] = $name;
  $return["monto_total"] = number_format(($datos_venta["neto_gravado"] + $datos_venta["no_gravado"] + $datos_venta["otros_impuestos"] + $datos_venta["neto_exento"] + $datos_venta["iva"] + $datos_venta["itc"]+ $datos_venta["igo"]+ $datos_venta["th"]),2,".","");
  unset($return["id_comprobante"],$return["cuit"]);

  DB::delete("pos_ventas_fe_requests", "id_venta = %d0 AND resultado = 'W'", $_GET["id"]);
  DB::insert("pos_ventas_fe_requests", $return);

  if ($return["fallo"] === false)
  {
    file_put_contents("/var/www/pdf/".$name, fopen($return["link"], 'r'));

    $upd = array("comprobante_numero"=>$return["afip_numero_comprobante"], "comprobante_pv"=>$return["afip_pv"]);
    DB::update("pos_ventas_comprobante", $upd, "id = %d0",$datos_comprobante["id_cbte"]);
    
    LogCSV($_GET["id"].";OK;".$return["afip_pv"].";".$return["afip_numero_comprobante"].";".$return["cae_numero"]."\n");
    die();
  }
}
else
{
  $fname = date("Y-m-d G_i_s").".log";
  file_put_contents("/var/www/pdf/json_".$fname, $json);
  file_put_contents("/var/www/pdf/server_output_".$fname, $server_output);
}

if (!$return || $return["fallo"]==true)
{
 LogCSV($_GET["id"].";BAD;".print_r($observaciones, true)."\n");
 die();
}

function RandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function LogCSV($str)
{
  $name = "batch-factura/".date("Ymd")."-batch-factura-electronica.csv";
  file_put_contents($name, $str, FILE_APPEND);
}
?>