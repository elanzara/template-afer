<?PHP

require_once("../mysql-functions.php");
require_once("../conn-data.php");
require_once("../session/ajax-session-validator.php");

if (!isset($_POST["DDBB_table"],$_POST["id"])) { header("HTTP/1.0 400");die("400|Petición no válida");}
if (sizeof($_POST) != 2) { header("HTTP/1.0 400");die("400|Petición no válida - sizeof()");}


$tabla = $_POST["DDBB_table"];
$date = date("Y-m-d G:i:s");
unset($_POST["DDBB_table"]);

if (in_array($tabla, array("pos_clientes"))) {if ($_POST["id"] < 99) {header("HTTP/1.0 400");die("400|No se puede eliminar este registro");}}

$id = $_POST["id"];

if (strpos($tabla,"prp-") === false)
{
	DB::update($tabla, array("enabled"=>0),"id=%d", $_POST["id"]);
	$cantUpd = DB::affectedRows();
}
else
{
	switch ($tabla) {
		case 'prp-clientes-cc':
		 if (in_array($_SESSION["user_id"], [12,13]))
		 {
		 	DB::update("pos_clientes_cc", array("enabled"=>0),"id=%d", $_POST["id"]);
			$cantUpd = DB::affectedRows();
		 }
		 else
		 {
			header("HTTP/1.0 400");die("400|Usuario no autorizado");
		 }
		break;
		case 'prp-proveedores-cc':
		 if (in_array($_SESSION["user_id"], [12,13]))
		 {
		 	DB::update("pos_proveedores_cc", array("enabled"=>0),"id=%d", $_POST["id"]);
			$cantUpd = DB::affectedRows();
		 }
		 else
		 {
			header("HTTP/1.0 400");die("400|Usuario no autorizado");
		 }
		break;
		case 'prp-alivios':
		DB::startTransaction();
			if ($_SESSION["user_id"] == 1)
			{
				header("HTTP/1.0 400");die("400|Los alivios se anulan desde la caja que envió el alivio");
			}
			//revierto un credito 
			$datos = DB::queryFirstRow("SELECT pos_cajas_movimientos.id_usuario, debitos, creditos, pos_cajas_movimientos.pin, pv_tktfiscal, sys_usuarios.username,
				pos_alivios.id as id_alivio
				FROM pos_cajas_movimientos 
				INNER JOIN sys_usuarios_datos ON (sys_usuarios_datos.id_usuario = pos_cajas_movimientos.id_usuario) 
				INNER JOIN sys_usuarios ON (sys_usuarios_datos.id_usuario = sys_usuarios.id) 
				INNER JOIN pos_alivios ON (pos_alivios.id_movimiento = pos_cajas_movimientos.id) 
				WHERE pos_cajas_movimientos.id = %d0 AND anulado = 0",$id);

			if (sizeof($datos) == 0)
				{
					header("HTTP/1.0 400");die("400|El movimiento fue anulado o es anulación de uno anterior");
				}
			$insert = [];


			if ($datos["creditos"] != 0)
			{
				header("HTTP/1.0 400");die("400|Solo puede eliminar alivios con débitos a su caja");
			}
			if ($datos["debitos"] != 0)
			{
				$insert[] = array("datetime" => $date, "pin"=>$datos["pin"],"id_segmento"=>3, "id_pago_tipo"=>1,"comentario" => "Anulación de alivio #".str_pad($datos["id_alivio"], 5, "0", STR_PAD_LEFT). " (".$datos["username"].")", "debitos"=>$datos["debitos"],"creditos" => 0, "id_usuario" => 1);
				$insert[] = array("datetime" => $date, "pin"=>$datos["pin"],"id_segmento"=>3, "id_pago_tipo"=>1,"comentario" => "Anulación de alivio #".str_pad($datos["id_alivio"], 5, "0", STR_PAD_LEFT). " (".$datos["username"].")", "debitos"=>0,"creditos" => $datos["debitos"], "id_usuario" => $datos["id_usuario"]);
			}

			DB::insert("pos_cajas_movimientos", $insert);
			$id_movimiento = DB::insertId(); //revisar que sea el id de la caja de playa o market
			$cantUpd = DB::affectedRows();

			$insert_alivios = array(
			"id_movimiento" => $id_movimiento,
			"id_usuario" => $_SESSION["user_id"],
			"datetime" => $date,
			"pin" => $datos["pin"]
			);

		DB::insert("pos_alivios", $insert_alivios);

		$id_alivio = DB::insertId();

		//nuevo alivio detalles

		$billetes = DB::query("SELECT monto, valor, cantidad FROM pos_alivios_detalle WHERE id_alivio = %d0", $datos["id_alivio"]);

		foreach ($billetes as $key => $value) {
			$billetes[$key]["id_alivio"] = $id_alivio;
			$billetes[$key]["cantidad"] *= -1;
		}

		DB::insert("pos_alivios_detalle", $billetes);

			DB::insert("pos_ventas_tkt_fiscales", array("id_venta"=>$id_alivio, "id_comprobante_tipo"=>801, "datetime_queue"=>$date, "pv"=>$datos["pv_tktfiscal"]));


			if ($cantUpd > 0)
			{
				DB::update("pos_cajas_movimientos", ["anulado"=>1],"id = %d0", $id);
			}

		DB::commit();
		break;
		case 'prp-pos_compras':
		//hay que dar de baja compras, compras_comprobante, compras_items, compras_pagos, compensar cc del proveedor, compensar stock, anular la orden de pago y la caja, devolver el cheque a pendiente
		
		$es_devolucion = DB::queryFirstRow("SELECT es_devolucion FROM pos_compras WHERE id = %d0",$id)["es_devolucion"];
		if ($es_devolucion) {require_once("eliminar-devolucion-compra.php");die();}
		DB::startTransaction();

		//datos varios de la compra
		$datos_compra = DB::queryFirstRow("SELECT id_proveedor, id_usuario FROM pos_compras WHERE id = %d0", $id);

		//datos del total de la fc
		$total_factura = DB::queryFirstRow("SELECT 
  		IFNULL((SELECT SUM(pos_compras_items.neto_gravado * pos_compras_items.cantidad) FROM pos_compras_items WHERE pos_compras_items.id_compra = pos_compras_comprobante.id_compra GROUP BY pos_compras_items.id_compra),0) + IFNULL((SELECT SUM(pos_compras_items_otros.neto_gravado * pos_compras_items_otros.cantidad) FROM pos_compras_items_otros WHERE pos_compras_items_otros.id_compra = pos_compras_comprobante.id_compra GROUP BY pos_compras_items_otros.id_compra),0)+
  		IFNULL((SELECT SUM(pos_compras_items.no_gravado * pos_compras_items.cantidad) FROM pos_compras_items WHERE pos_compras_items.id_compra = pos_compras_comprobante.id_compra GROUP BY pos_compras_items.id_compra),0) + IFNULL((SELECT SUM(pos_compras_items_otros.no_gravado * pos_compras_items_otros.cantidad) FROM pos_compras_items_otros WHERE pos_compras_items_otros.id_compra = pos_compras_comprobante.id_compra GROUP BY pos_compras_items_otros.id_compra),0)+
  		IFNULL((SELECT SUM(pos_compras_items.neto_exento * pos_compras_items.cantidad) FROM pos_compras_items WHERE pos_compras_items.id_compra = pos_compras_comprobante.id_compra GROUP BY pos_compras_items.id_compra),0) + IFNULL((SELECT SUM(pos_compras_items_otros.neto_exento * pos_compras_items_otros.cantidad) FROM pos_compras_items_otros WHERE pos_compras_items_otros.id_compra = pos_compras_comprobante.id_compra GROUP BY pos_compras_items_otros.id_compra),0)+
  		IFNULL((SELECT SUM(pos_compras_items.iva * pos_compras_items.cantidad) FROM pos_compras_items WHERE pos_compras_items.id_compra = pos_compras_comprobante.id_compra GROUP BY pos_compras_items.id_compra),0) + IFNULL((SELECT SUM(pos_compras_items_otros.iva * pos_compras_items_otros.cantidad) FROM pos_compras_items_otros WHERE pos_compras_items_otros.id_compra = pos_compras_comprobante.id_compra GROUP BY pos_compras_items_otros.id_compra),0)+ 
  		IFNULL((SELECT SUM(pos_compras_items.itc * pos_compras_items.cantidad) FROM pos_compras_items WHERE pos_compras_items.id_compra = pos_compras_comprobante.id_compra GROUP BY pos_compras_items.id_compra),0) + IFNULL((SELECT SUM(pos_compras_items_otros.itc * pos_compras_items_otros.cantidad) FROM pos_compras_items_otros WHERE pos_compras_items_otros.id_compra = pos_compras_comprobante.id_compra GROUP BY pos_compras_items_otros.id_compra),0)+ 
  		IFNULL((SELECT SUM(pos_compras_items.igo * pos_compras_items.cantidad) FROM pos_compras_items WHERE pos_compras_items.id_compra = pos_compras_comprobante.id_compra GROUP BY pos_compras_items.id_compra),0) + IFNULL((SELECT SUM(pos_compras_items_otros.igo * pos_compras_items_otros.cantidad) FROM pos_compras_items_otros WHERE pos_compras_items_otros.id_compra = pos_compras_comprobante.id_compra GROUP BY pos_compras_items_otros.id_compra),0)+ 
  		IFNULL((SELECT SUM(pos_compras_items.th * pos_compras_items.cantidad) FROM pos_compras_items WHERE pos_compras_items.id_compra = pos_compras_comprobante.id_compra GROUP BY pos_compras_items.id_compra),0) + IFNULL((SELECT SUM(pos_compras_items_otros.th * pos_compras_items_otros.cantidad) FROM pos_compras_items_otros WHERE pos_compras_items_otros.id_compra = pos_compras_comprobante.id_compra GROUP BY pos_compras_items_otros.id_compra),0)+ percepciones_iva + percepciones_nacionales + percepciones_municipales + percepciones_iibb_capfed + percepciones_iibb_bsas + percepciones_iibb_rionegro + percepciones_iibb_stafe + percepciones_iibb_erios + percepciones_iibb_sircreb + impuestos_internos + otros_tributos AS total_factura FROM pos_compras_comprobante WHERE id_compra = %d0
			", $id)["total_factura"];

		

		$insert_cc = [];
		//cancelar la compra es un credito a la cc del proveedor
		$insert_cc[] = array("id_proveedor" => $datos_compra["id_proveedor"],"id_compra"=>$id, "descripcion" => "Cancelación de compra #".str_pad($id, 5, "0",STR_PAD_LEFT),"debitos"=>0, "creditos" => floatval($total_factura),"datetime" => $date, "id_movimiento_tipo" =>1,"id_pago_tipo"=>0);
		/* fin compensar cc */


		/* compensar caja y pagos en la cc del proveedor */
		$compensar_caja = DB::query("SELECT SUM(monto) AS total, id_pago_tipo  FROM pos_ordenes_pago_detalle WHERE id_compra = %d0  AND pos_ordenes_pago_detalle.enabled = 1 GROUP BY id_pago_tipo", $id);
		$insert_caja = [];
		foreach ($compensar_caja as $key => $value) {
		//anular un pago es un debito al proveedor
		 	if ($value["id_pago_tipo"] != 4) $insert_cc[] = array("id_proveedor" => $datos_compra["id_proveedor"], "descripcion" => "Cancelación de pagos de compra #".str_pad($id, 5, "0",STR_PAD_LEFT), "creditos"=>0,"debitos" => $value["total"],"datetime" => $date, "id_movimiento_tipo" =>1,"id_pago_tipo"=>$value["id_pago_tipo"],"id_compra"=>$id,"id_usuario"=>$datos_compra["id_usuario"]);
		//anular un pago es un credito a la caja
		$insert_caja[] = array("comentario" => "Cancelación de compra #".str_pad($id, 5, "0",STR_PAD_LEFT),"id_segmento"=>2, "debitos"=>0, "creditos" => $value["total"],"datetime" => $date, "id_pago_tipo" => $value["id_pago_tipo"],"id_usuario"=>$datos_compra["id_usuario"]);

		}



		/* recalcular stock por item */
		$insert_stock_items = [];
		$items_compra = DB::query("SELECT * FROM pos_compras_items WHERE id_compra = %d0", $id);
		
		foreach ($items_compra as $key => $value) {
			$insert_stock_items[] = array("id_usuario"=>$_SESSION["user_id"], "id_segmento"=>2,"datetime"=>$date, "id_movimiento_tipo"=>1, "id_deposito"=>$value["id_deposito"], "cantidad"=>-(float)$value["cantidad"], "id_articulo"=>$value["id_articulo"],"comentario"=>"Cancelación de compra #".str_pad($id, 8, "0",STR_PAD_LEFT));
			$cantidad = (float)DB::queryFirstRow("SELECT cantidad FROM pos_stock WHERE id_articulo = %d0 FOR UPDATE",$value["id_articulo"])["cantidad"] - (float)$value["cantidad"];
			//DB::query("SELECT pos_stock.cantidad FROM pos_stock WHERE id_articulo =%d0 FOR UPDATE",$value["id_articulo"]);
			DB::update("pos_stock", array("cantidad"=>$cantidad), "id_articulo = %d0", $value["id_articulo"]);
			$cant = DB::affectedRows();
		}
			DB::insert("pos_stock_movimientos", $insert_stock_items); //insertar compensacion movimientos de stock
			$cantUpd = DB::affectedRows();
			DB::insert("pos_proveedores_cc",$insert_cc); //insertar compensacion cc proveedor
			DB::insert("pos_cajas_movimientos",$insert_caja); //insertar compensacion caja

			DB::update("pos_compras_comprobante", array("enabled"=>0), "id_compra = %d0", $id); //borrar comprobante
			DB::update("pos_compras_items_otros", array("enabled"=>0), "id_compra = %d0", $id); //borrar items
			DB::update("pos_compras_items", array("enabled"=>0), "id_compra = %d0", $id); //borrar items
			DB::update("pos_compras", array("enabled"=>0), "id = %d0", $id); //borrar compra
			DB::update("pos_ordenes_pago_detalle", array("enabled"=>0), "id_compra = %d0", $id); //borrar pagos
			DB::update("pos_ordenes_pago", array("enabled"=>0), "id_compra = %d0", $id); //borrar pagos
		DB::commit();

		break;
		
		case 'prp-sys_seguridad':
			DB::delete("sys_seguridad", "id=%d", $id);
			$cantUpd = DB::affectedRows();
		break;

		case 'prp-cheques':
			echo "ASDADS";
		break;
		
		case "prp-ordenes-pago":
			//simplemente deshabilito la orden de pago, balanceo la CC del proveedor y la caja
			DB::startTransaction();
			$pagos_hechos = DB::query("SELECT SUM(monto) AS total, id_pago_tipo  FROM pos_ordenes_pago_detalle WHERE id_orden_pago = %d0 GROUP BY id_pago_tipo", $id);
			$datos_orden_pago = DB::queryFirstRow("SELECT * FROM pos_ordenes_pago WHERE id = %d0",$id);
		
			$insert_cc = [];
			$insert_caja = [];

			foreach ($pagos_hechos as $key => $value) {
				if($value["total"] < 0)
				{
					if ($value["id_pago_tipo"] != 4) $insert_cc[] = array("id_proveedor" => $datos_orden_pago["id_proveedor"],"id_usuario"=>$datos_orden_pago["id_usuario"],"id_compra"=>$datos_orden_pago["id_compra"],"descripcion" => "Cancelación de pagos de devolución de compra #".str_pad($datos_orden_pago["id_compra"], 5, "0",STR_PAD_LEFT), "debitos"=>0,"credito" => $value["total"]*-1,"datetime" => $date, "id_movimiento_tipo" =>1,"id_pago_tipo"=>$value["id_pago_tipo"]);
						$insert_caja[] = array("comentario" => "Cancelación de pagos de devolución de compra #".str_pad($datos_orden_pago["id_compra"], 5, "0",STR_PAD_LEFT), "creditos"=>0, "debitos" => $value["total"]*-1,"datetime" => $date, "id_pago_tipo" => $value["id_pago_tipo"],"id_usuario"=>$datos_orden_pago["id_usuario"]);
				}
				else
				{
				//anular un pago es un debito al proveedor
						if ($value["id_pago_tipo"] != 4) $insert_cc[] = array("id_proveedor" => $datos_orden_pago["id_proveedor"],"id_usuario"=>$datos_orden_pago["id_usuario"],"id_compra"=>$datos_orden_pago["id_compra"],"descripcion" => "Cancelación de orden de pago #".str_pad($id, 5, "0",STR_PAD_LEFT), "creditos"=>0,"debitos" => $value["total"],"datetime" => $date, "id_movimiento_tipo" =>1,"id_pago_tipo"=>$value["id_pago_tipo"]);
						//anular un pago es un credito a la caja
						$insert_caja[] = array("comentario" => "Cancelación de orden de pago #".str_pad($id, 5, "0",STR_PAD_LEFT), "debitos"=>0, "creditos" => $value["total"],"datetime" => $date, "id_pago_tipo" => $value["id_pago_tipo"],"id_usuario"=>$datos_orden_pago["id_usuario"]);
				}
	       
			}

		

			if ($datos_orden_pago["id_proveedor"] != 0) DB::insert("pos_proveedores_cc",$insert_cc); //insertar compensacion cc proveedor
			DB::insert("pos_cajas_movimientos",$insert_caja); //insertar compensacion caja
			DB::update("pos_ordenes_pago_detalle", array("enabled"=>0), "id_orden_pago = %d0", $id); //borrar pagos
			DB::update("pos_ordenes_pago", array("enabled"=>0), "id = %d0", $id); //borrar pagos
			DB::commit();
			$cantUpd=1;
		break;
			case "prp-recibos-cobro":

			$cantUpd = 0;
		break;
		case "prp-salidas-mayoristas":
		//por cada movimiento de detalles, compensar el stock y actualizar el pos_ventas_mayoristas
			DB::startTransaction();
			
			$salida_datos = DB::queryFirstRow("SELECT enabled, id_transporte_mayorista,id_deposito_desperdicio,id_articulo,litros_remanente, 
			(SELECT MAX(facturado) FROM pos_ventas_mayoristas_detalle WHERE id_venta_mayorista = pos_ventas_mayoristas.id) as facturado, 
		   	(SELECT SUM(litros_salida) FROM pos_ventas_mayoristas_picos WHERE id_venta_mayorista = pos_ventas_mayoristas.id) as litros_salida, 
			litros_desperdicio FROM pos_ventas_mayoristas WHERE id = %d0", $id);
			
			$salida_picos = DB::query("SELECT * FROM pos_ventas_mayoristas_picos WHERE id_venta_mayorista = %d0",$id);
			$salida_picos = DBHelper::reIndex($salida_picos,"id_pico");
			$id_articulos_picos = array_keys(DBHelper::reIndex($salida_picos,"id_articulo"));
			
			$cantidad = DB::query("SELECT pos_stock.cantidad as cantidad, pos_articulos.id_deposito, id_articulo 
				FROM pos_stock 
				INNER JOIN pos_articulos ON (pos_articulos.id = pos_stock.id_articulo)
				WHERE id_articulo IN %li0 FOR UPDATE", $id_articulos_picos); //congelo todos los productos involucrados

			$cantidad = DBHelper::reIndex($cantidad,"id_articulo");
			if ($salida_datos["enabled"] == 2)
			{
				DB::rollback();
							header("HTTP/1.0 504");die("504|Esta venta mayorista no puede ser anulada dado que fue confirmada asignando litros a clientes");
			}


			if ( in_array($salida_datos["enabled"], array(1,2) ) && $salida_datos["facturado"] == 0) 
			//si esta en estado 1 o 2 y no tiene nada facturado
			{

				foreach ($salida_picos as $key => $value) {
					$insert_stock["cantidad"] = $value["litros_salida"];
					$insert_stock["comentario"] = "Cancelación de salida mayorista #".str_pad($id,5,"0",STR_PAD_LEFT);
					$insert_stock["id_articulo"] = $value["id_articulo"];
					$insert_stock["id_usuario"] = $_SESSION["user_id"];
					$insert_stock["datetime"] = $date;
					$insert_stock["id_segmento"] = 9;
					$insert_stock["id_deposito"] = $cantidad[$value["id_articulo"]]["id_deposito"];
					$insert_stock["id_movimiento_tipo"] = 1;
					DB::insert("pos_stock_movimientos", $insert_stock);
					$cantidad_actual = DB::queryFirstRow("SELECT pos_stock.cantidad as cantidad FROM pos_stock WHERE id_articulo =%d0", $value["id_articulo"])["cantidad"];
					//la nueva cant es lo que habia actualizado mas lo que le estoy devolviendo
					$nueva_cantidad = (float) $cantidad_actual + (float) $insert_stock["cantidad"];

					/*var_dump($cantidad_actual, (float) $insert_stock["cantidad"], $nueva_cantidad, $value["id_articulo"]);
					echo "\n";*/

					DB::update("pos_stock", array("cantidad"=>$nueva_cantidad), "id_articulo = %d0", $value["id_articulo"]);
			}
					/*echo "\n";
					echo "\n";
					echo "\n";*/

			if ($salida_datos["enabled"] == 2)
				//si ya fue asignado los litros
			{
				$litros_asignados = DB::queryFirstRow("SELECT SUM(litros_asignados) as litros_asignados FROM pos_ventas_mayoristas_detalle WHERE id_venta_mayorista = %d0",$id)["litros_asignados"];
				$neto_desperdicio_sobrante = $salida_datos["litros_salida"]-$litros_asignados-$salida_datos["litros_desperdicio"]-$salida_datos["litros_remanente"];
				$id_articulo_depo_sobrante = DB::queryFirstRow("SELECT id FROM pos_articulos WHERE id_deposito = %d0",$salida_datos["id_deposito_desperdicio"]);

				if ($neto_desperdicio_sobrante !=0)
				{
					$insert_stock["cantidad"] = $neto_desperdicio_sobrante * -1;
					$insert_stock["comentario"] = "Cancelación de salida mayorista #".str_pad($id,5,"0",STR_PAD_LEFT);
					$insert_stock["id_articulo"] = $id_articulo_depo_sobrante["id"];
					$insert_stock["id_usuario"] = $_SESSION["user_id"];
					$insert_stock["datetime"] = $date;
					$insert_stock["id_segmento"] = 9;
					$insert_stock["id_deposito"] = $salida_datos["id_deposito_desperdicio"];
					$insert_stock["id_movimiento_tipo"] = 1;
					DB::insert("pos_stock_movimientos", $insert_stock);

					$cantidad_actual = DB::queryFirstRow("SELECT pos_stock.cantidad as cantidad FROM pos_stock WHERE id_articulo =%d0", $id_articulo_depo_sobrante["id"])["cantidad"];
					//la nueva cant es lo que habia mas lo que le estoy devolviendo
					$nueva_cantidad = (float) $cantidad_actual + (float) $insert_stock["cantidad"];

					//var_dump($cantidad_actual, (float) $insert_stock["cantidad"],$nueva_cantidad, $id_articulo_depo_sobrante["id"]);

					DB::update("pos_stock", array("cantidad"=>$nueva_cantidad), "id_articulo = %d0", $insert_stock["id_articulo"]);
				}
					//header("HTTP/1.0 503");die("503|Esta venta mayorista no puede ser anulada");

			}

			DB::update("pos_ventas_mayoristas", array("enabled"=>0), "id = %d0", $id);
			//si estaba activa la transaccion, cambiar el status del carrito a cero
			if ($salida_datos["enabled"] == 1) DB::update("abm_transportes_mayorista", array("status"=>0), "id = %d0", $salida_datos["id_transporte_mayorista"]);
			DB::commit();
			}
			else
			{
				DB::rollback();
				header("HTTP/1.0 503");die("503|Esta venta mayorista no puede ser anulada");
			}
			$cantUpd = 1;

		break;

		default:
			$cantUpd = 0;
		break;
	}
}


if ($cantUpd > 0)
{
	 header("HTTP/1.0 200");die("200|OK");
}
else
{
	 header("HTTP/1.0 500");die("500|Sin cambios");
}

?>