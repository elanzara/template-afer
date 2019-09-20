<?PHP

require_once("../mysql-functions.php");
require_once("../conn-data.php");
require_once("../session/ajax-session-validator.php");


if (!isset($_GET["tipo"],$_GET["term"])) { header("HTTP/1.0 400");die("400|Petición no válida");}
if (sizeof($_GET)<2) { header("HTTP/1.0 400");die("400|Petición no válida - sizeof()");}
if (strlen($_GET["term"])<3) { header("HTTP/1.0 401");die("401|Too short");}

$tablas = array("articulo"=>array("tabla"=>"pos_articulos","label"=>"descripcion","value"=>"id","donde"=>array("codigo_barras","id","descripcion")));


switch ($_GET["tipo"]) {
	case 'articulo':
		$where = new WhereClause('or');  
 		$donde = array("pos_articulos.codigo_barras","pos_articulos.id","pos_articulos.descripcion","pos_articulos.codigo_proveedor");

			if (substr($_GET["term"],0,3) == "ID-") 
				{
					$term = substr($_GET["term"],3);
					$where->add('pos_articulos.id = %d0', $term);
				}
			else
				{
					foreach ($donde as $value) {
						$where->add($value.' LIKE %ss', trim($_GET["term"]));
					}
				}
				if (!isset($_GET["id_cliente"])) $_GET["id_cliente"] = 0;

		$result = DB::query("SELECT abm_deposito.valor as str_deposito, id_deposito, CONCAT(pos_articulos.descripcion, ' [', abm_deposito.valor ,']') AS label, pos_articulos.id AS value, pos_articulos.descuenta_stock, pos_articulos.cant_min, pos_articulos.categoria,
			(SELECT cantidad FROM pos_stock WHERE id_articulo = pos_articulos.id) as cantidad_actual,
			CAST(IFNULL((SELECT valor FROM pos_articulos_promos WHERE id_articulo = pos_articulos.id AND (%s2 BETWEEN fecha_desde AND fecha_hasta) AND( pos_articulos_promos.id_cliente = %d3 or pos_articulos_promos.id_cliente = -1) AND (pos_articulos_promos.%d4 = 1) ORDER BY id_cliente DESC LIMIT 0,1 ),0) as DECIMAL(6,2)) as promo_precio,
			IFNULL((SELECT id_pago_tipo FROM pos_articulos_promos WHERE id_articulo = pos_articulos.id AND (%s2 BETWEEN fecha_desde AND fecha_hasta) AND( pos_articulos_promos.id_cliente = %d3 or pos_articulos_promos.id_cliente = -1) AND (pos_articulos_promos.%d4 = 1) ORDER BY id_cliente DESC LIMIT 0,1 ),0) as promo_tipo_pago,
			IFNULL((SELECT abm_pago_tipo.valor FROM abm_pago_tipo INNER JOIN pos_articulos_promos ON (pos_articulos_promos.id_pago_tipo = abm_pago_tipo.id) WHERE pos_articulos_promos.id_articulo = pos_articulos.id AND (%s2 BETWEEN pos_articulos_promos.fecha_desde AND pos_articulos_promos.fecha_hasta) AND( pos_articulos_promos.id_cliente = %d3 or pos_articulos_promos.id_cliente = -1) AND (pos_articulos_promos.%d4 = 1) ORDER BY id_cliente DESC LIMIT 0,1 ),0) as str_promo_tipo_pago,
			IFNULL((SELECT cant_min FROM pos_articulos_promos WHERE id_articulo = pos_articulos.id AND (%s2 BETWEEN fecha_desde AND fecha_hasta) AND( pos_articulos_promos.id_cliente = %d3 or pos_articulos_promos.id_cliente = -1) AND (pos_articulos_promos.%d4 = 1) ORDER BY id_cliente DESC LIMIT 0,1 ),0) as promo_cant_min,
			IFNULL((SELECT id_promos_tipo FROM pos_articulos_promos WHERE id_articulo = pos_articulos.id AND (%s2 BETWEEN fecha_desde AND fecha_hasta) AND( pos_articulos_promos.id_cliente = %d3 or pos_articulos_promos.id_cliente = -1) AND (pos_articulos_promos.%d4 = 1) ORDER BY id_cliente DESC LIMIT 0,1 ),0) as promo_tipo,

		  	(SELECT (neto_gravado + no_gravado + neto_exento + iva + otros_impuestos) FROM pos_articulos_precios WHERE id_articulo = pos_articulos.id  ORDER BY id DESC LIMIT 0,1) as precio_unitario,
		  	(SELECT iva_alicuota FROM pos_articulos_precios WHERE id_articulo = pos_articulos.id  ORDER BY id DESC LIMIT 0,1) as iva_alicuota

			FROM pos_articulos 
			INNER JOIN abm_deposito ON (abm_deposito.id = pos_articulos.id_deposito)
			WHERE (%l0) AND (pos_articulos.enabled = 1) AND (pos_articulos.id_deposito IN %li1)",$where->text(), $_SESSION["user_depositos"], date("Y-m-d G:i:s"), $_GET["id_cliente"], date("w"));
	break;

	case 'cliente':
		$where = new WhereClause('or');  
 		$donde = array("pos_clientes.razon_social","pos_clientes.id","pos_clientes.documento_numero","pos_clientes.club_numero_tarjeta");
		if (substr($_GET["term"],0,3) == "ID-") 
				{
					$term = substr($_GET["term"],3);
					$where->add('pos_clientes.id = %d0', $term);
				}
			else
				{
					foreach ($donde as $value) {
						$where->add($value.' LIKE %ss', trim($_GET["term"]));
					}
				}


		$result = DB::query("SELECT CONCAT(pos_clientes.razon_social, ' [',pos_clientes.documento_numero,']') as label, id as value, id_documento_tipo, documento_numero, direccion, id_iva_situacion, id_provincia, club_miembro, club_numero_tarjeta, IF(pos_clientes.referencia_obligatoria = 1 OR pos_clientes.id_tipo_control_cc = 2,1,0) as patente_obligatoria, pos_clientes.id_tipo_control_cc,
			IFNULL((SELECT abm_beneficiarios.id FROM abm_beneficiarios INNER JOIN pos_clientes t1 ON (t1.id_beneficiario = abm_beneficiarios.id) WHERE abm_beneficiarios.enabled = 1 AND t1.id = pos_clientes.id),0) as id_beneficiario, pos_clientes.id
			FROM pos_clientes 
			WHERE (%l0) AND (pos_clientes.enabled = 1)",$where->text());

		$enabled = [];

		foreach ($result as $key => $value) {
			require("../../../frontends/addons/cuenta-corriente/clientes/calcula-saldos.php");

			$result[$key]["patentes_habilitadas"] = [];
			$result[$key]["saldo_cc"] = "$ " . number_format($saldo_cc,2);
			
			if ($value["id_tipo_control_cc"] == 2) //limite por patente
			{
				//traigo todas las patentes habilitadas a cargar con su respectivo saldo actual
				$patentes_hab = DB::query("SELECT referencia, IFNULL((SELECT SUM(ROUND(creditos,2))-SUM(ROUND(debitos,2)) 
					FROM pos_clientes_cc 
					WHERE id_cliente = %d0 AND id_pago_tipo != 4 AND pos_clientes_cc.referencia = pos_clientes_cc_limites_individuales.referencia),0) as saldo_cc FROM pos_clientes_cc_limites_individuales WHERE id_cliente = %d0 AND pos_clientes_cc_limites_individuales.enabled = 1", $value["value"]);

				foreach ($patentes_hab as $k => $v) {
					$patentes_hab[$k]["saldo_cc"] = "$ ".number_format($v["saldo_cc"],2);
				}
				$result[$key]["patentes_habilitadas"] = $patentes_hab;
			}


		switch ($result[$key]["id_iva_situacion"]) {
			case 1:
				//solo fc a y remito y fca rece
				$enabled = [999998,999999,81,1];
				//si es venta libre, tambien nota de credito A, nota debito A, tkt nota credito A, tkt nota debito A
				if ($_SESSION["ventas"]["venta_libre"] == 1)
				{
					$enabled[] = 2;
					$enabled[] = 3;
					$enabled[] = 112;
					$enabled[] = 115;
				}
			break;
			case 5:
			case 4:
			//exento y consu final
				$enabled = [999999,999998,82,6]; //TKT FC B y fc b rece
				if ($result[$key]["value"] == 1) $enabled = [999999, 999998,82,83,6]; //SI ES ID 1 (ConsFinal) TIQUE CF
				if (in_array($result[$key]["value"], array(2,3,4))) $enabled = [999999, 999998]; //para las ventas a carrito e internas, promociones y carga virtual

				//si es venta libre, tambien nota de credito B, nota debito B, tkt nota credito B, tkt nota debito B

				if ($_SESSION["ventas"]["venta_libre"] == 1)
				{
					$enabled[] = 7;
					$enabled[] = 8;
					$enabled[] = 116;
					$enabled[] = 117;
				}
			break;
			case 3:
			case 6:
			case 7:
			case 13:
				//tkt fcb y fcb rece
				$enabled = [999999, 999998,82,6];
				//si es venta libre, tambien nota de credito B, nota debito B, tkt nota credito B, tkt nota debito B
				if ($_SESSION["ventas"]["venta_libre"] == 1)
				{
					$enabled[] = 7;
					$enabled[] = 8;
					$enabled[] = 116;
					$enabled[] = 117;
				}
			break;

			
			default:
				# code...
			break;
		}


			/*aca cruzar con lo que esta autorizado a emitir segun sys_usuarios_datos*/
			$autorizado = DB::queryFirstRow("SELECT pv_tktfiscal, pv_facelec FROM sys_usuarios_datos WHERE id_usuario = %d", $_SESSION["user_id"]);

			$remains = [];
			
			if ($autorizado["pv_tktfiscal"] != 0) {	$remains[] = 81; $remains[] = 82; $remains[] = 83; $remains[] = 999998; if ($_SESSION["ventas"]["venta_libre"] == 1) { $remains[] = 116; $remains[] = 117;$remains[] = 112; $remains[] = 115;}}
			if ($autorizado["pv_tktfiscal"] != 0 && isset(FORCE_USER_ONLY_REMITO[$_SESSION["user_id"]])) {$remains=[999998];}
			if ($autorizado["pv_facelec"] != 0) {$remains[] = 1; $remains[] = 6; $remains[] = 999999; if ($_SESSION["ventas"]["venta_libre"] == 1) {$remains[] = 7; $remains[] = 8;$remains[] = 2; $remains[] = 3;}}

			foreach ($enabled as $llave => $tipo_cbte) {
			
				if (in_array($tipo_cbte, $remains) === false)
				{
					unset($enabled[$llave]);
				}
			}

			//$result[$key]["saldo_cc"] = "$ ".number_format($value["saldo_cc"],2);
			$result[$key]["id_comprobante_tipo"] = $enabled;
		}

	break;
	case 'proveedor':
		$where = new WhereClause('or');  
 		$donde = array("pos_proveedores.razon_social","pos_proveedores.id","pos_proveedores.documento_numero");
		foreach ($donde as $value) {
			$where->add($value.' LIKE %ss', trim($_GET["term"]));
		}


		$result = DB::query("SELECT CONCAT(pos_proveedores.razon_social, ' [',pos_proveedores.documento_numero,']') as label, id as value
			FROM pos_proveedores 
			WHERE (%l0) AND (pos_proveedores.enabled = 1)",$where->text());
	break;
	
	default:
		if (!isset($tablas[$_GET["tipo"]])) { header("HTTP/1.0 500");die("500|No soportado");}
		$where = new WhereClause('or');  
 
		foreach ($tablas[$_GET["tipo"]]["donde"] as $value) {
			$where->add($value.' LIKE %ss', trim($_GET["term"]));
		}

		 
		$result = DB::query("SELECT ".$tablas[$_GET["tipo"]]["label"]." AS label, ".$tablas[$_GET["tipo"]]["value"]." AS value FROM pos_articulos WHERE (%l0) AND (enabled = 1)",$where->text());

	break;
}



echo json_encode($result);

?>