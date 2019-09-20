<?PHP

require_once("../mysql-functions.php");
require_once("../conn-data.php");
require_once("../session/ajax-session-validator.php");


if (!isset($_POST["DDBB_table"],$_POST["id"])) { header("HTTP/1.0 400");die("400|Petición no válida");}
if (sizeof($_POST) != 2) { header("HTTP/1.0 400");die("400|Petición no válida - sizeof()");}

$tabla = $_POST["DDBB_table"];
unset($_POST["DDBB_table"]);

if ($tabla == "prp-cheques") $tabla = "pos_cheques";

if (strpos($tabla,"prp") === false)
{
	$datos = DB::queryFirstRow("SELECT * FROM `$tabla` WHERE id = %d0", $_POST["id"]);
}
else
{
	switch ($tabla) {
		case 'prp-abm_empleados':
			$datos = DB::queryFirstRow("SELECT * FROM abm_empleados WHERE id = %d0", $_POST["id"]);
		break;
		case 'prp-pos_promociones':
			$datos = DB::queryFirstRow("SELECT * FROM pos_articulos_promos WHERE id = %d0", $_POST["id"]);
			$datos["fecha_desde"] = substr($datos["fecha_desde"], 0,10);
			$datos["fecha_hasta"] = substr($datos["fecha_hasta"], 0,10);
		break;
		case 'prp-abm_cheque_cf':
			$datos = DB::queryFirstRow("SELECT * FROM `abm_cheque_cf` WHERE id = %d0", $_POST["id"]);
		break;
		case 'prp-usuarios':
			$datos = DB::queryFirstRow("SELECT sys_usuarios.username,sys_usuarios.enabled, sys_usuarios_datos.*, sys_usuarios.id FROM sys_usuarios 
				INNER JOIN sys_usuarios_datos ON (sys_usuarios_datos.id_usuario = sys_usuarios.id) WHERE sys_usuarios.id = %d0", $_POST["id"]);
		break;

		case 'prp-salidas-mayoristas':
		$datos_venta = DB::queryFirstRow("
		SELECT pos_ventas_mayoristas.datetime_creacion, pos_ventas_mayoristas.datetime_cierre, pos_ventas_mayoristas.comentario, litros_remanente, saldo_anterior as litros_saldo,  abm_transportes_mayorista.valor as str_transporte,  abm_ventas_mayoristas_estados.valor as str_estado, litros_desperdicio, sys_usuarios.username as str_usuario, 
	  	(SELECT SUM(litros_salida) FROM pos_ventas_mayoristas_picos WHERE id_venta_mayorista = pos_ventas_mayoristas.id) as litros_salida, pos_ventas_mayoristas.litros_diferencia,
	  	(SELECT SUM(litros_asignados) FROM pos_ventas_mayoristas_detalle WHERE id_venta_mayorista = pos_ventas_mayoristas.id) as litros_final,
	  	CONCAT(t4.apellido, ', ', t4.nombre) as str_pin_asignacion,
	  	CONCAT(t2.apellido, ', ', t2.nombre) as str_pin_creacion,
	  	IFNULL(abm_deposito.valor, '---') as str_deposito_desperdicio

	 	FROM pos_ventas_mayoristas
		INNER JOIN abm_transportes_mayorista ON (abm_transportes_mayorista.id = pos_ventas_mayoristas.id_transporte_mayorista)
	  	INNER JOIN abm_ventas_mayoristas_estados ON (abm_ventas_mayoristas_estados.id = pos_ventas_mayoristas.enabled)
	  	LEFT JOIN abm_deposito ON (abm_deposito.id = pos_ventas_mayoristas.id_deposito_desperdicio)
	  	INNER JOIN sys_usuarios ON (sys_usuarios.id = pos_ventas_mayoristas.id_usuario)

	  	LEFT JOIN sys_empleados_pin t1 ON (t1.pin = pos_ventas_mayoristas.pin_asignacion)
  		LEFT JOIN abm_empleados t2 ON (t2.id = t1.id_empleado)

  		LEFT JOIN sys_empleados_pin t3 ON (t3.pin = pos_ventas_mayoristas.pin_asignacion)
  		LEFT JOIN abm_empleados t4 ON (t4.id = t3.id_empleado)

	 	WHERE pos_ventas_mayoristas.id =%d0",$_POST["id"]);

		$datos_venta_items = DB::query("
		SELECT  IFNULL(pos_clientes.razon_social,'---') as str_cliente, pos_articulos.descripcion as str_articulo, camion_chofer, camion_patente, remito_numero, litros_asignados, pos_ventas_fe_requests.local_path
		FROM pos_ventas_mayoristas_detalle
	  	INNER JOIN pos_articulos ON (pos_articulos.id = pos_ventas_mayoristas_detalle.id_articulo)
		LEFT JOIN pos_ventas_fe_requests ON (pos_ventas_fe_requests.id_venta = pos_ventas_mayoristas_detalle.id_ventas_comprobante)
		LEFT JOIN pos_clientes ON (pos_clientes.id = pos_ventas_mayoristas_detalle.id_cliente)
		WHERE id_venta_mayorista =%d0",$_POST["id"]);

		/* if (sizeof($datos_venta_items) > 0)
		{*/
			$datos_venta["litros_reintegro"] = number_format($datos_venta["litros_salida"]+$datos_venta["litros_diferencia"] + $datos_venta["litros_saldo"] -$datos_venta["litros_final"]-$datos_venta["litros_desperdicio"]-$datos_venta["litros_remanente"],4);
		/*}
		else
		{
			$datos_venta["litros_reintegro"] = "---";
			$datos_venta["litros_final"] = "---";
			$datos_venta["litros_desperdicio"] = "---";
		}*/

		$datos_venta["litros_totales"] = $datos_venta["litros_saldo"] + $datos_venta["litros_salida"];

		$datos = ["datos_venta"=> $datos_venta, "datos_venta_items"=>$datos_venta_items];

		break;
		case 'prp-pos_ventas_tkt_fiscales':
			$datos = DB::queryFirstRow("SELECT * FROM pos_ventas_tkt_fiscales WHERE id = %d0", $_POST["id"]);
		break;
		case 'prp-pos_compras':
			$datos = DB::queryFirstRow("SELECT pos_compras_comprobante.*, pos_compras.comentario, pos_compras.id as id FROM pos_compras INNER JOIN pos_compras_comprobante ON (pos_compras.id = pos_compras_comprobante.id_compra) WHERE pos_compras.id = %d0", $_POST["id"]);
		break;
		case 'prp-pos_articulos':
			$datos = DB::queryFirstRow("SELECT * FROM pos_articulos a LEFT JOIN (SELECT id_articulo, neto_gravado, otros_impuestos, no_gravado, neto_exento, iva, iva_alicuota, (neto_exento+neto_gravado+no_gravado+iva+otros_impuestos) as precio_final FROM pos_articulos_precios b  WHERE id_articulo = %d0  ORDER BY id DESC LIMIT 0,1) d ON (d.id_articulo=a.id) WHERE a.id = %d0", $_POST["id"]);

		break;
		
		default:
			# code...
		break;
	}
}

if ($datos)
{
	 header("HTTP/1.0 200");echo json_encode($datos);
}
else
{
	 header("HTTP/1.0 500");die("500|Error leyendo tabla $tabla");
}

?>