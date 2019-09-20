<?PHP

require_once("../mysql-functions.php");
require_once("../conn-data.php");
require_once("../session/session-validator.php");

if (!isset($_GET["id"])) { header("HTTP/1.0 400");die("400|Petición no válidas");}
if (!isset($_SESSION["cuentas-corrientes"])) { header("HTTP/1.0 400");die("400|Petición no válidassss");}
if (sizeof($_GET) != 3) { header("HTTP/1.0 400");die("400|Petición no válida - sizeof()");}
if (sizeof($_SESSION["cuentas-corrientes"]) != 3) { header("HTTP/1.0 400");die("400|Petición no válida - sizeof(2)");}

$saldo_prev = $_SESSION["cuentas-corrientes"]["saldo-anterior"];
$movimientos = $_SESSION["cuentas-corrientes"]["movimientos"];
$total_remitos_sin_fc =  $_SESSION["cuentas-corrientes"]["remitos-sin-facturar"];

switch ($_GET["parent"]) {
  case 'clientes':
    $associated = "pos_clientes";
    $table = "pos_clientes_cc";
  break;
  
  case 'proveedores':
    $associated = "pos_proveedores";
        $table = "pos_proveedores_cc";
  break;
}

$datos = DB::queryFirstRow("SELECT $associated.razon_social FROM $associated WHERE id = %d0",$_GET["id"]);

$final = ["Operación;Cbte.;Fecha;Tipo;Método;Descripción;Referencia;Créditos;Débitos"];
if (sizeof($movimientos)+sizeof($saldo_prev) > 0) {
	
	 $saldo = 0;
      if (sizeof($saldo_prev) > 0)
      { 
		$temp = ";;Previo;Automático;N/A;Saldo anterior (".number_format($saldo_prev["cant_op"],0, ",", "")." operaciones);;";

		if ($saldo_prev["saldo_prev"] >= 0) 
		{
			$temp .= number_format(abs($saldo_prev["saldo_prev"]),2, ",", "").";";
		}
		else
		{
			$temp .= ";".number_format(abs($saldo_prev["saldo_prev"])*-1,2, ",", "");

		}
      $saldo += $saldo_prev["saldo_prev"];
     $final[] = $temp;
  	}
      foreach ($movimientos as $key => $value) {
      	$temp = str_pad($value["id_operacion_relacionada"], 5,"0", STR_PAD_LEFT).";".str_pad($value["id_comprobante_relacionado"], 5,"0", STR_PAD_LEFT).";".substr($value["datetime"],0,10).";".$value["str_moviemiento_tipo"].";".$value["str_metodo_tipo"].";".$value["descripcion"].";".$value["referencia"].";";
      	if ($value["creditos"]!=0) {$temp .= number_format($value["creditos"],2, ",", ""); $saldo += ($value["creditos"]);}
      	if ($value["debitos"]!=0) {$temp .= ";".number_format($value["debitos"]*-1,2, ",", ""); $saldo += ($value["debitos"]*-1);}

      	$final[] = $temp;
	} 

	$temp = ";;;;;Saldo ";


	if ($_GET["parent"] == "proveedores") {if($saldo>0) {$temp .= "(a favor de ".EMPRESA_RAZON_SOCIAL.") ";} if($saldo<0) {$temp .= "(a favor del proveedor) ";}}else{if($saldo>0) {$temp .= "(a favor del cliente) ";} if($saldo<0) {$temp .= "(a favor de ".EMPRESA_RAZON_SOCIAL.") ";}}

  $temp .=  ";;;".number_format($saldo,2,",", "");

	$final[] = $temp;


if ($total_remitos_sin_fc > 0) { $temp = ";;;;;Remitos sin Facturar;;;". number_format($total_remitos_sin_fc,2,",", "");  $final[] = $temp;}
}

$final = implode("\n", $final);
file_put_contents("csv/".urlencode($datos["razon_social"]).".csv", utf8_decode($final));
header("Location: /recursos/php/common/csv/".urlencode($datos["razon_social"]).".csv");
die();
?>