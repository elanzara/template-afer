<?PHP

require_once("../mysql-functions.php");
require_once("../conn-data.php");
require_once("../session/ajax-session-validator.php");

if (sizeof($_POST) < 2) { header("HTTP/1.0 400");var_dump($_POST);die("400|Petición no válida - sizeof()");}

$tabla = $_POST["DDBB_table"];
$date = date("Y-m-d G:i:s", time());
unset($_POST["DDBB_table"], $_POST["id"]);

if ($tabla == "pos_clientes" && $_SESSION["user_type"] != 2 && isset($_POST["quota_diaria"])) unset($_POST["quota_diaria"]);
if ($tabla == "pos_clientes" && $_SESSION["user_type"] != 2 && isset($_POST["id_tipo_control_cc"])) unset($_POST["id_tipo_control_cc"]);
if ($tabla == "pos_clientes" && $_SESSION["user_type"] != 2 && isset($_POST["id_beneficiario"])) unset($_POST["id_beneficiario"]);
if ($tabla == "pos_clientes" && $_SESSION["user_type"] != 2 && isset($_POST["limite_global_cc"])) unset($_POST["limite_global_cc"]);


if ($tabla == "pos_clientes")
{
	$num = DB::queryFirstRow("SELECT MAX(id) as max_id FROM pos_clientes")["max_id"];
	if ($num < 100) $_POST["id"] = 100;
}

if ($tabla == "pos_proveedores")
{
	$num = DB::queryFirstRow("SELECT MAX(id) as max_id FROM pos_proveedores")["max_id"];
	if ($num < 100) $_POST["id"] = 100;
}


if ($tabla == "pos_clientes_cc_limites_individuales")
{
	$_POST["referencia"] = strtoupper(str_replace(" ", "", $_POST["referencia"]));
}

if (strpos($tabla,"prp-") === false)
{
	DB::insert($tabla,$_POST);

}
else
{

	switch ($tabla) {
		case 'prp-abm_cheque_cf':
			$_POST["cf_calculador"] = (100/(100-$_POST["cf"])-1)*100;
			DB::startTransaction();
			DB::insert("abm_cheque_cf", $_POST);
			$rows1 = DB::affectedRows();
			DB::commit();
		break;
		case 'prp-pos_promociones':
			DB::startTransaction();

			//revisar desde, hasta
			$_POST["valor"] = floatval($_POST["valor"]);
			$fecha_desde = strtotime($_POST["fecha_desde"]);
			$fecha_hasta = strtotime($_POST["fecha_hasta"]);

			if ($fecha_desde > $fecha_hasta) {header("HTTP/1.0 501");die("502|La promoción debe empezar antes de finalizar");}
			if (time() > $fecha_hasta) {header("HTTP/1.0 501");die("503|La promoción debe finalizar por lo menos un día después de hoy");}	
			if (($fecha_hasta - $fecha_desde) < (24*60*60)) {header("HTTP/1.0 501");die("503|La promoción debe durar por lo menos un día (ingresó ". (($fecha_hasta - $fecha_desde) / 60/ 60 /24).")");}
			DB::insert("pos_articulos_promos", $_POST);
			DB::commit();	
		break;
		case 'prp-abm_empleados':
		$nuevo_pin = $_POST["pin"];
		unset($_POST["pin"]);
		DB::startTransaction();
			$existe = DB::queryFirstRow("SELECT id_empleado FROM sys_empleados_pin WHERE pin = %s0", $nuevo_pin);
			DB::insert("abm_empleados", $_POST);

			if (strlen($nuevo_pin) != 4)
			{
				 DB::rollback();
				 header("HTTP/1.0 502");die("502|PIN debe ser de 4 números");	
			}

			if (sizeof($existe) > 0) 
			{
				 DB::rollback();
				 header("HTTP/1.0 502");die("502|PIN ya existe (Empleado#".$existe["id_empleado"].")");
			}

			DB::insert("sys_empleados_pin", ["id_empleado"=>DB::insertId(), "pin"=> $nuevo_pin]);
		DB::commit();	
					
		break;
		case 'prp-usuarios':
		DB::startTransaction();
		$existe = DB::queryFirstRow("SELECT id FROM sys_usuarios WHERE username = %s0", $_POST["username"]);
		if (sizeof($existe) > 0) 
		{
			 header("HTTP/1.0 502");die("502|Usuario ya existe (#".$existe["id"].")");
		}
		else
		{
			$sys_usuarios = array("username"=>$_POST["username"],"password"=>md5($_POST["password"]));
			$sys_usuarios_datos = array("apellido"=>$_POST["apellido"],"pv_facelec"=>$_POST["pv_facelec"],"pv_tktfiscal"=>$_POST["pv_tktfiscal"],"pv_remito"=>$_POST["pv_remito"],"nombre"=>$_POST["nombre"],"email"=>$_POST["email"],"fecha_nacimiento"=>$_POST["fecha_nacimiento"]);

			DB::insert("sys_usuarios",$sys_usuarios);
			$id = DB::insertId();
			$sys_usuarios_datos["id_usuario"]=$id;
			DB::insert("sys_usuarios_datos",$sys_usuarios_datos);
		}
		DB::commit();
		break;
		case 'prp-sys_seguridad':
			$existe = DB::queryFirstRow("SELECT id FROM sys_seguridad WHERE id_modulo = %d0 AND id_usuario = %d1", $_POST["id_modulo"], $_POST["id_usuario"]);
			if (sizeof($existe) > 0) 
			{
				 header("HTTP/1.0 502");die("502|Autorizacion ya existe (#".$existe["id"].")");
			}
			else
			{
				DB::insert("sys_seguridad",$_POST);
			}
		break;
		case 'prp-cheques':
		//la combinacion es tipo banco serie numero cuit emisor
			DB::startTransaction();

			if (!isset($_POST["pin"]) ||  strlen($_POST["pin"]) != 4)
			{
		         DB::rollback();
				 header("HTTP/1.0 503");die("503|PIN inválido");
				 die();
		    }

			$pin = DB::queryFirstRow("SELECT id FROM sys_empleados_pin WHERE pin = %s0 AND sys_empleados_pin.id = (SELECT id FROM sys_empleados_pin t1 WHERE t1.id_empleado = sys_empleados_pin.id_empleado ORDER BY id DESC LIMIT 0,1)", $_POST["pin"]);

			if (sizeof($pin) != 1)
			{
				DB::rollback();
				header("HTTP/1.0 504");die("504|PIN no existe o corresponde a más de un usuario");
				die();
			}

			$existe = DB::queryFirstRow("SELECT id FROM pos_cheques WHERE id_tipo_cheque = %d0 AND id_banco = %d1 AND serie = %s2 AND numero = %d3 AND cuit_emisor = %d4 AND enabled = 1", $_POST["id_tipo_cheque"], $_POST["id_banco"], $_POST["serie"], $_POST["numero"], $_POST["cuit_emisor"]);
			if (sizeof($existe) > 0) 
			{
				 DB::rollback();
				 header("HTTP/1.0 501");die("501|Cheque ya existe (#".$existe["id"].")");
			}

			$modulo = explode("/", $_SERVER["HTTP_REFERER"])[3];
			if ($modulo == "ventas" && $_POST["id_tipo_cheque"] == 1)
			{
				 DB::rollback();
				 header("HTTP/1.0 502");die("502|Cheque propio no puede aplicarse a ventas");
			}
			elseif (($modulo == "compras" || $modulo == "proveedores") && $_POST["id_tipo_cheque"] == 2)
			{
				DB::rollback();
				header("HTTP/1.0 507");die("507|Cheque terceros no puede crearse desde ".ucfirst($modulo).". Primero ingresarlo a C/C cliente y luego usar función de búsqueda");
			}

			$_POST["id_segmento"] = 1; //ventas
			if ($modulo == "compras" || $modulo == "proveedores") $_POST["id_segmento"] = 2; //si es desde pantalla de compras o proveedores, es compras. sirve para identificar que hacer en la anulacion


			$insert = [];
			$referencia = $_POST["referencia"];
			unset($_POST["referencia"]);


			if ($_POST["id_tipo_cheque"] == 1) //alta cheque propio, va sin id_cliente con id_proveedor
			{
				$_POST["emisor"] = EMPRESA_RAZON_SOCIAL;
				$_POST["cuit_emisor"] = EMPRESA_CUIT;
				$campo = "id_proveedor";
				$_POST["id_estado"] = 5; //entra aplicado a proveedor si es propio

			}
			elseif ($_POST["id_tipo_cheque"] == 2)
			{
				$campo = "id_cliente";
				$_POST["id_estado"] = 3; //entra en cartera por defecto si es de tereceros
			}
			else
			{
				DB::rollback();
				header("HTTP/1.0 508");die("508|Error en datos recibidos");
			}

			$_POST[$campo] = $_POST["id_cuenta"];
			unset($_POST["id_cuenta"]);
			$insert["pos_cheques"] = $_POST;
			$insert["pos_cheques"]["serie"] = strtoupper($insert["pos_cheques"]["serie"]);
			$insert["pos_cheques"]["datetime"] = date("Y-m-d G:i:s");
			$insert["pos_cheques"]["id_usuario"] = $_SESSION["user_id"];

			if ($_POST["id_tipo_cheque"] == 1 && $_POST["id_segmento"] == 2) //cheque propio solo puede venir en modulo de compras pero igual pregunto
			{
				//el cheque en si está dado de alta, solo hacer movimientos de caja y proveedor si es propio y orden de pago papurri

				$num = DB::queryFirstRow("SELECT MAX(id) as max_id FROM pos_ordenes_pago")["max_id"];
				DB::insert("pos_ordenes_pago",array("id"=>$num+1,"id_compra"=>0,"id_proveedor"=>$_POST[$campo],"datetime"=>$date,"id_usuario"=>$_SESSION["user_id"]));
				$id_orden_pago = DB::insertId();

				$insert["pos_ordenes_pago_detalle"] = [];
				$insert["pos_ordenes_pago_detalle"][] = array(
					"id_orden_pago" => $id_orden_pago,
					"id_compra" => 0,
					"id_pago_tipo" => 6,
					"monto" => $_POST["importe"],
					"datetime" => $date,
					"comentario" => "Cheque ".EMPRESA_RAZON_SOCIAL." #".$_POST["numero"]
				);


				$insert["pos_proveedores_cc"] = [];
				$insert["pos_proveedores_cc"][] = array("id_proveedor" => $_POST[$campo], "descripcion" => "Crédito Cheque Propio ".$insert["pos_cheques"]["serie"]."-".$_POST["numero"], "creditos"=>$_POST["importe"],"debitos" =>0,"datetime" => $date, "id_movimiento_tipo" =>1,"id_pago_tipo"=>6,"id_usuario"=>$_SESSION["user_id"],"id_compra"=>0,"id_orden_pago"=>$id_orden_pago,"referencia"=>$referencia);
				
				$insert["pos_cajas_movimientos"] = [];
			 	$insert["pos_cajas_movimientos"][]= array("id_pago_tipo" => 6,"id_segmento"=>2, "datetime" => $date, "comentario" => "Crédito Cheque Propio ".$insert["pos_cheques"]["serie"]."-".$_POST["numero"], "debitos"=>0,"creditos" => $_POST["importe"], "id_usuario" => POS_CAJA_CHEQUES, "pin" => $_POST["pin"]);
			 	$insert["pos_cajas_movimientos"][]= array("id_pago_tipo" => 6,"id_segmento"=>2, "datetime" => $date, "comentario" => "Débito Cheque Propio ".$insert["pos_cheques"]["serie"]."-".$_POST["numero"], "creditos" =>0,"debitos" => $_POST["importe"], "id_usuario" => POS_CAJA_CHEQUES, "pin" => $_POST["pin"]);
			 	$id_final = 0;
			 	$insert["pos_cheques"]["id_orden_pago"] = $id_orden_pago;
			}
			elseif ($_POST["id_tipo_cheque"] == 2 && $_POST["id_segmento"] == 1) //cheque de terceros solo puede venir de ventas pero igual pregunto
			{
			//creo nota de debito solo si es cheque de tecero y es segmento clientes o ventas. ojo que si es clientes tengo que tener id_Cliente
				$costo_financiero = DB::queryFirstRow("SELECT cf, str_item FROM abm_cheque_cf WHERE id = %d0", $_POST["cf"]);
				$id_venta = 0;
				$str_item = $costo_financiero["str_item"];
				$float_cf = floatval(number_format($costo_financiero["cf"] * $_POST["importe"] / 100,2, ".", "")); //% a descontar del cheque, por ende, monto de la ND

				if ($float_cf > 0)
				{
					
					$num = DB::queryFirstRow("SELECT MAX(id) as max_id FROM pos_ventas FOR UPDATE")["max_id"];
					$pos_ventas = array("id"=>$num+1,$campo=>$_POST[$campo],"datetime"=>$date,"id_usuario"=>$_SESSION["user_id"],"es_devolucion"=>0,"id_venta_devuelta"=>0,"comentario"=>"",
				  "es_mayorista"=>0, "es_remito"=>0, "es_libre"=>1, "id_detalle_mayorista"=>0, "id_detalle_remito"=>0, "pin" => $pin);
					DB::insert("pos_ventas", $pos_ventas);
					$id_venta = DB::insertId();
			


				

				$insert["pos_ventas_items"] = [[
								"id_venta" => $id_venta, 
								"id_articulo" => 0, 
								"cantidad" => 1, 
								"total" => $float_cf, 
								"iva_alicuota" => 21, 
								"descripcion" => $str_item,
								"neto_gravado" => ($float_cf  / 1.21), 
								"iva" => $float_cf - ($float_cf  / 1.21), 
								"otros_impuestos" => 0, 
								"no_gravado" => 0, 
								"neto_exento" => 0, 
								"th" => 0, 
								"itc" => 0, 
								"igo" => 0]];

				//para determinar id_comprobante_tipo me fijo en situacion iva del cliente y luego chequeo, si usuario actual tiene PV TKT o PV FCE
			$autorizado = DB::queryFirstRow("SELECT pv_tktfiscal, pv_facelec FROM sys_usuarios_datos WHERE id_usuario = %d", $_SESSION["user_id"]);

			if ($autorizado["pv_tktfiscal"] == 0 && $autorizado["pv_facelec"] == 0)
			{
				DB::rollback();
			 	header("HTTP/1.0 510");die("510|Usuario actual no tiene ticket fiscal ni factura electrónica habilitada");
			}

			if ($float_cf > 24500 && $autorizado["pv_facelec"] == 0)
			{
				DB::rollback();
				header("HTTP/1.0 508");die("508|El monto del CF supera los $24.500, por tanto, este cheque no puede ser procesado por Controladora Fiscal. Deberá ser cargado por Gerencia.");
			}

			$datos_cliente = DB::queryFirstRow("SELECT id_documento_tipo, documento_numero, razon_social, id_provincia, id_iva_situacion FROM pos_clientes WHERE id = %d0", $_POST[$campo]);
			
			$insert["pos_ventas_comprobante"] = [];

			if($datos_cliente != null) 
  			{
				$insert["pos_ventas_comprobante"]["id_provincia"] = $datos_cliente["id_provincia"];
	    		$insert["pos_ventas_comprobante"]["id_iva_situacion"] = $datos_cliente["id_iva_situacion"];
	    		$insert["pos_ventas_comprobante"]["razon_social"] = $datos_cliente["razon_social"];
	    		$insert["pos_ventas_comprobante"]["id_documento_tipo"] = $datos_cliente["id_documento_tipo"];
	    		$insert["pos_ventas_comprobante"]["documento_numero"] =  $datos_cliente["documento_numero"];
	    		$insert["pos_ventas_comprobante"]["date"] = $date;
				$insert["pos_ventas_comprobante"]["id_moneda_tipo"] = "PES";
				$insert["pos_ventas_comprobante"]["moneda_tipo_cambio"] = 1;
				$insert["pos_ventas_comprobante"]["id_venta"] = $id_venta;
    		}
    		else
    		{
    			DB::rollback();
			 	header("HTTP/1.0 505");die("505|Cliente ". $_POST[$campo]. " no encontrado");
    		}

			switch ($datos_cliente["id_iva_situacion"]) {
				case 1:
					//solo fc a y remito y fca rece
					if ($autorizado["pv_tktfiscal"] != 0) $id_comprobante_tipo = 115;
					if ($autorizado["pv_facelec"] != 0) $id_comprobante_tipo = 2;
				break;
				case 5:
				case 4:
				case 3:
				case 6:
				case 7:
				case 13:
					if ($autorizado["pv_tktfiscal"] != 0) $id_comprobante_tipo = 116;
					if ($autorizado["pv_facelec"] != 0) $id_comprobante_tipo = 7;	
				break;
				default:
					DB::rollback();
				 	header("HTTP/1.0 506");die("506|Situacion IVA Cliente ". $_POST[$campo]. " no válida");
				break;
			}
			//agregar PV
			$insert["pos_ventas_comprobante"]["id_comprobante_tipo"] = $id_comprobante_tipo;	

    		}
			
			//inserto recibo
		 	$num = DB::queryFirstRow("SELECT MAX(id) as max_id FROM pos_ventas_recibos")["max_id"];
    		DB::insert("pos_ventas_recibos",array("id"=>$num+1,"id_venta"=>$id_venta,"id_cliente"=>$_POST[$campo],"datetime"=>$date,"id_usuario"=>$_SESSION["user_id"]));
    		$id_ventas_recibo = DB::insertId();

			$insert["pos_clientes_cc"] = [];
			$insert["pos_ventas_recibos_detalle"] = [];
			$insert["pos_cajas_movimientos"] = [];
			
			$insert["pos_clientes_cc"][] = array("id_cliente" => $_POST[$campo], "descripcion" => "Crédito Cheque ".$insert["pos_cheques"]["serie"]."-".$_POST["numero"], "creditos"=>$_POST["importe"],"debitos" =>0,"datetime" => $date, "id_movimiento_tipo" =>1,"id_pago_tipo"=>6,"id_usuario"=>$_SESSION["user_id"],"id_venta"=>$id_venta,"id_venta_recibo"=>$id_ventas_recibo,"referencia"=>$referencia);
			$insert["pos_ventas_recibos_detalle"][] = array("id_venta_recibo" => $id_ventas_recibo, "id_venta" => $id_venta, "id_pago_tipo" => 6, "monto" => $_POST["importe"], "datetime" => $date, "comentario" => "Crédito Cheque ".$insert["pos_cheques"]["serie"]."-".$_POST["numero"]);
			$insert["pos_cajas_movimientos"][]= array("id_pago_tipo" => 6,"id_segmento"=>1, "datetime" => $date, "comentario" => "Crédito Cheque ".$insert["pos_cheques"]["serie"]."-".$_POST["numero"], "debitos"=>0,"creditos" => $_POST["importe"], "id_usuario" => POS_CAJA_CHEQUES, "pin" => $_POST["pin"]);

			if ($float_cf > 0)
			{
				$insert["pos_clientes_cc"][] = array("id_cliente" => $_POST[$campo], "descripcion" => "Débito ".$str_item." Cheque ".$insert["pos_cheques"]["serie"]."-".$_POST["numero"], "creditos"=>0,"debitos" =>$float_cf,"datetime" => $date, "id_movimiento_tipo" =>1,"id_pago_tipo"=>6,"id_usuario"=>$_SESSION["user_id"],"id_venta"=>$id_venta,"id_venta_recibo"=>$id_ventas_recibo,"referencia"=>$referencia);
			
				/*$insert["pos_ventas_recibos_detalle"][] = array("id_venta_recibo" => $id_ventas_recibo, "id_venta" => $id_venta, "id_pago_tipo" => 6, "monto" => -($float_cf), "datetime" => $date, "comentario" => "Débito CF Cheque #".$_POST["numero"]);*/
			}

		 	$id_final = $id_venta;
		 	$insert["pos_cheques"]["id_ventas_recibo"] = $id_ventas_recibo;
  			}
  			else
  			{
  				DB::rollback();
				header("HTTP/1.0 509");die("509|Segmento no válido para la operación solicitada - ".$_POST["id_tipo_cheque"] . " / " .$_POST["id_segmento"] . " / ". $modulo);
  			}
			
			//inserto pos_cheques pos_ventas pos_ventas_items pos_ventas_recibo pos_ventas_recibo_detalle pos_clientes_cc pos_cajas_movimientos y devuelvo el id_venta para que abra en una nueva ventana la impresion del comprobante
			
			//header("HTTP/1.0 400");

			foreach ($insert as $tabla => $valores) {
				//var_dump($tabla, $valores);
				DB::insert($tabla, $valores);
			}
			//die("400|".$id_final);

			DB::commit();
			header("HTTP/1.0 200");die("200|".$id_final);
		break;
		case 'prp-pos_articulos':
			DB::startTransaction();
			
			$num = DB::queryFirstRow("SELECT MAX(id) as max_id FROM pos_articulos")["max_id"];
			if ($num < 99) $_POST["id"] = 100;

			$keys_precio = array("neto_gravado", "no_gravado", "neto_exento", "iva_alicuota", "iva", "otros_impuestos");
			$insert_precio = array();

			/* separo los valores */
			foreach ($keys_precio as $value) {
				$insert_precio[$value] = $_POST[$value];
				unset($_POST[$value]);
			}

			unset($_POST["precio_final"]);

			$insert_articulo = $_POST;

			DB::insert("pos_articulos",$insert_articulo);

			$insert_precio["id_articulo"] = DB::insertId();
			$insert_precio["datetime"] = date("Y-m-d G:i:s");
			DB::insert("pos_articulos_precios",$insert_precio);
			DB::insert("pos_stock",array("id_articulo"=>$insert_precio["id_articulo"], "id_deposito"=>$insert_articulo["id_deposito"]));
			DB::commit();
			
		break;
		case "prp-salidas-mayoristas":
			DB::startTransaction();
			$datos_venta_mayorista = json_decode($_POST["datos"],true);

			$pin = DB::queryFirstRow("SELECT id FROM sys_empleados_pin WHERE pin = %s0 AND sys_empleados_pin.id = (SELECT id FROM sys_empleados_pin t1 WHERE t1.id_empleado = sys_empleados_pin.id_empleado ORDER BY id DESC LIMIT 0,1)", $datos_venta_mayorista["pin"]);

				if (sizeof($pin) != 1)
				{
				 DB::rollback();
				header("HTTP/1.0 503");die("504|PIN no válido - consulte a administracion");
				die();
				}


			$datos_transporte = DB::queryFirstRow("SELECT capacidad, valor, saldo, id_articulo FROM abm_transportes_mayorista WHERE id = %d0",$datos_venta_mayorista["id_transporte_mayorista"]);

			if ($datos_venta_mayorista["id_articulo"] != $datos_transporte["id_articulo"] && $datos_transporte["saldo"] > 0)
			{
				DB::rollback();
				header("HTTP/1.0 503");die("503|El transporte solicitado tiene saldo de otro producto");
				die();
			}


			$insert_ventas_mayoristas["datetime_creacion"] = $date;
			$insert_ventas_mayoristas["id_usuario"] = $_SESSION["user_id"];
			$insert_ventas_mayoristas["id_transporte_mayorista"] = $datos_venta_mayorista["id_transporte_mayorista"];
			$insert_ventas_mayoristas["id_articulo"] = $datos_venta_mayorista["id_articulo"];
			$insert_ventas_mayoristas["saldo_anterior"] = $datos_transporte["saldo"];
			$insert_ventas_mayoristas["litros_diferencia"] = 0;
			$insert_ventas_mayoristas["pin_creacion"] = $datos_venta_mayorista["pin"];

			//hay tres tablas ventas_mayoristas, ventas_mayoristas_picos, ventas_mayoristas_detalle.
			
			
			DB::insert("pos_ventas_mayoristas",$insert_ventas_mayoristas);
			$id_venta_mayorista = DB::insertId();

			$picos = DBHelper::reIndex($datos_venta_mayorista["datos_salida"],"id_pico");
			$productos = DB::query("SELECT descripcion, pos_articulos.id_deposito, abm_surtidores_picos.id_articulo, abm_surtidores_picos.id as id_pico
				FROM abm_surtidores_picos 
				INNER JOIN pos_articulos ON (pos_articulos.id = abm_surtidores_picos.id_articulo)
			 WHERE abm_surtidores_picos.id IN %li0", array_keys($picos));
			$productos = DBHelper::reIndex($productos,"id_pico");
			//tengo ordenado el array de picos y de productos por id_pico


			$insert_picos = [];
			$litros_total_salida = 0;

			foreach ($picos as $key => $value) {
				
				$litros_total_salida += $value["cantidad"];

				$insert_stock = [];
				//recorro el array para armar salidas de stock y detalle de venta mayorista
				$nombre = $productos[$key]["descripcion"];
				$deposito = $productos[$key]["id_deposito"];
				$id_articulo = $productos[$key]["id_articulo"];
				$cantidad = DB::queryFirstRow("SELECT cantidad FROM pos_stock WHERE id_articulo = %d0 FOR UPDATE", $id_articulo)["cantidad"];

				if ($cantidad < $value["cantidad"])
				{
					DB::rollback();
					header("HTTP/1.0 503");die("503|No hay disponible del artículo ".$nombre." pico #".$key." (disponible ".$cantidad. " L - solicitado ".$value["cantidad"]. " L)");
					die();
				}

				$nueva_cantidad = $cantidad - (float)$value["cantidad"];

				//movimientos de stock
				$insert_stock["cantidad"] = -$value["cantidad"];
				$insert_stock["comentario"] = "Salida mayorista #".str_pad($id_venta_mayorista,5,"0",STR_PAD_LEFT);
				$insert_stock["id_articulo"] = $id_articulo;
				$insert_stock["id_usuario"] = $_SESSION["user_id"];
				$insert_stock["id_segmento"] = 9; //segmento ventas mayoristas
				$insert_stock["datetime"] = $date;
				$insert_stock["id_deposito"] = $deposito;
				$insert_stock["id_movimiento_tipo"] = 1;
				DB::insert("pos_stock_movimientos", $insert_stock);
				DB::update("pos_stock", array("cantidad"=>$nueva_cantidad), "id_articulo = %d0", $id_articulo);
				//fin movimientos de stock

				$insert_picos[] = ["id_venta_mayorista"=>$id_venta_mayorista, "id_articulo" => $id_articulo,"id_pico" => $value["id_pico"], "litros_salida" => $value["cantidad"]];
			}

			/*if ($datos_transporte["capacidad"] < $litros_total_salida)
			{
				DB::rollback();
				header("HTTP/1.0 504");die("504|Capacidad de transporte ".$datos_transporte["valor"]." excedida #".$key." (capacidad ".$datos_transporte["capacidad"]. " L - asignado ".$litros_total_salida. " L)");
				die();
			}*/
						
			DB::insert("pos_ventas_mayoristas_picos", $insert_picos);
			DB::update("abm_transportes_mayorista", array("status"=>1), "id = %d0", $insert_ventas_mayoristas["id_transporte_mayorista"]);
			DB::commit();
		break;
		case "prp-anular-asignar-cheque":
			//obtener datos cheque
			DB::startTransaction();

			$datos_cheque = DB::queryFirstRow("SELECT * FROM pos_cheques WHERE id = %d0 AND enabled = 1", $_POST["id_cheque"]);
			
			//si estado no es entregado a proveedor y segmento es terceros, rechazar op
			if ($datos_cheque["id_estado"] != 5 || $datos_cheque["id_tipo_cheque"] != 2)
			{
				DB::rollback();
				header("HTTP/1.0 501");die("501|Cheque no asignado a proveedor o es propio");
			}

			$num = DB::queryFirstRow("SELECT MAX(id) as max_id FROM pos_ordenes_pago")["max_id"];
			DB::insert("pos_ordenes_pago",array("id"=>$num+1,"id_compra"=>0,"id_proveedor"=>$datos_cheque["id_proveedor"],"datetime"=>$date,"id_usuario"=>$_SESSION["user_id"]));
			$id_orden_pago = DB::insertId();

			$insert["pos_ordenes_pago_detalle"] = [];
			$insert["pos_ordenes_pago_detalle"][] = array(
				"id_orden_pago" => $id_orden_pago,
				"id_compra" => 0,
				"id_pago_tipo" => 6,
				"monto" => -$datos_cheque["importe"],
				"datetime" => $date,
				"comentario" => "Desasignación Cheque #".$datos_cheque["numero"]);

			//entrada de caja
			//salida a CC proveedor

			$insert["pos_proveedores_cc"] = [];
			$insert["pos_cajas_movimientos"] = [];
			$insert["pos_proveedores_cc"][] = array("id_proveedor" => $datos_cheque["id_proveedor"], "descripcion" => "Desasignación Cheque #".$datos_cheque["numero"], "creditos"=>0,"debitos" =>$datos_cheque["importe"],"datetime" => $date, "id_movimiento_tipo" =>1,"id_pago_tipo"=>6,"id_usuario"=>$_SESSION["user_id"],"id_compra"=>0,"id_orden_pago"=>$id_orden_pago);

			$insert["pos_cajas_movimientos"][]= array("id_pago_tipo" => 6,"id_segmento"=>1, "datetime" => $date, "comentario" => "Reingreso  Cheque #".$datos_cheque["numero"], "debitos"=>0,"creditos" =>  $datos_cheque["importe"], "id_usuario" => POS_CAJA_CHEQUES, "pin" => "");

			foreach ($insert as $tabla => $valores) {
				echo $tabla;
				DB::insert($tabla, $valores);
			}

			DB::update("pos_cheques", ["id_estado"=> 3, "id_proveedor"=> 0], "id=%d0", $_POST["id_cheque"]);
			DB::commit();
		break;
		case "prp-negociar-cheque":
			DB::startTransaction();

			if (!isset($_POST["pin"]) ||  strlen($_POST["pin"]) != 4)
			{
		         DB::rollback();
				 header("HTTP/1.0 502");die("502|PIN inválido");
				 die();
		    }

			$pin = DB::queryFirstRow("SELECT id FROM sys_empleados_pin WHERE pin = %s0 AND sys_empleados_pin.id = (SELECT id FROM sys_empleados_pin t1 WHERE t1.id_empleado = sys_empleados_pin.id_empleado ORDER BY id DESC LIMIT 0,1)", $_POST["pin"]);

			if (sizeof($pin) != 1)
			{
				DB::rollback();
				header("HTTP/1.0 503");die("503|PIN no existe o corresponde a más de un usuario");
				die();
			}

			$datos_cheque = DB::queryFirstRow("SELECT * FROM pos_cheques WHERE id = %d0 AND enabled = 1", $_POST["id_cheque"]);
			//si estado no es en cartera y segmento es terceros, rechazar prop
			if ($datos_cheque["id_estado"] != 3 || $datos_cheque["id_tipo_cheque"] != 2)
			{
				DB::rollback();
				header("HTTP/1.0 501");die("501|Cheque no válido para marcar como negociado");
			}
			//aca crear minuta?

	 		$insert = array("id_pago_tipo" => 6,"id_segmento"=>4, "datetime" => $date, "comentario" => "Salida Cheque #".$datos_cheque["numero"], "creditos" =>0,"debitos" => $datos_cheque["importe"], "id_usuario" => POS_CAJA_CHEQUES, "pin" => $_POST["pin"]);
	 		DB::insert("pos_cajas_movimientos", $insert);
			DB::update("pos_cheques", ["id_estado"=> 6, "id_entidad"=> $_POST["id_entidad"]], "id=%d0", $_POST["id_cheque"]);
			DB::commit();
		break;
		case "prp-asignar-cheque":
			//obtener datos cheque
			DB::startTransaction();

			if (!isset($_POST["pin"]) ||  strlen($_POST["pin"]) != 4)
			{
		         DB::rollback();
				 header("HTTP/1.0 502");die("502|PIN inválido");
				 die();
		    }

			$pin = DB::queryFirstRow("SELECT id FROM sys_empleados_pin WHERE pin = %s0 AND sys_empleados_pin.id = (SELECT id FROM sys_empleados_pin t1 WHERE t1.id_empleado = sys_empleados_pin.id_empleado ORDER BY id DESC LIMIT 0,1)", $_POST["pin"]);

			if (sizeof($pin) != 1)
			{
				DB::rollback();
				header("HTTP/1.0 503");die("503|PIN no existe o corresponde a más de un usuario");
				die();
			}

			$datos_cheque = DB::queryFirstRow("SELECT * FROM pos_cheques WHERE id = %d0 AND enabled = 1", $_POST["id_cheque"]);
			
			//si estado no es en cartera y segmento es terceros, rechazar prop
			if ($datos_cheque["id_estado"] != 3 || $datos_cheque["id_tipo_cheque"] != 2)
			{
				DB::rollback();
				header("HTTP/1.0 501");die("501|Cheque no válido para asignación a Proveedores");
			}

			$num = DB::queryFirstRow("SELECT MAX(id) as max_id FROM pos_ordenes_pago")["max_id"];
			DB::insert("pos_ordenes_pago",array("id"=>$num+1,"id_compra"=>0,"id_proveedor"=>$_POST["id_proveedor"],"datetime"=>$date,"id_usuario"=>$_SESSION["user_id"]));
			$id_orden_pago = DB::insertId();

			$insert["pos_ordenes_pago_detalle"] = [];
			$insert["pos_ordenes_pago_detalle"][] = array(
				"id_orden_pago" => $id_orden_pago,
				"id_compra" => 0,
				"id_pago_tipo" => 6,
				"monto" => $datos_cheque["importe"],
				"datetime" => $date,
				"comentario" => "Asignación Cheque #".$datos_cheque["numero"]);

			//salida de caja
			//entrada a CC proveedor

			$insert["pos_proveedores_cc"] = [];
			$insert["pos_cajas_movimientos"] = [];
			$insert["pos_proveedores_cc"][] = array("id_proveedor" => $_POST["id_proveedor"], "descripcion" => "Asignación Cheque #".$datos_cheque["numero"], "creditos"=>$datos_cheque["importe"],"debitos" =>0,"datetime" => $date, "id_movimiento_tipo" =>1,"id_pago_tipo"=>6,"id_usuario"=>$_SESSION["user_id"],"id_compra"=>0,"id_orden_pago"=>$id_orden_pago);
			$insert["pos_cajas_movimientos"][]= array("id_pago_tipo" => 6,"id_segmento"=>1, "datetime" => $date, "comentario" => "Salida Cheque #".$datos_cheque["numero"], "debitos"=> $datos_cheque["importe"],"creditos" => 0, "id_usuario" => POS_CAJA_CHEQUES, "pin" => $_POST["pin"]);

			foreach ($insert as $tabla => $valores) {
				echo $tabla;
				DB::insert($tabla, $valores);
			}

			DB::update("pos_cheques", ["id_estado"=> 5, "id_proveedor"=> $_POST["id_proveedor"]], "id=%d0", $_POST["id_cheque"]);
			DB::commit();
		break;

		case "prp-tickets-masivos":
			DB::startTransaction();



			//hago las cuentas

			$paso = $_POST["salto"];
			$monto_minimo = $_POST["monto_minimo"];
			$monto_maximo = $_POST["monto_maximo"];
			$id_articulo = $_POST["id_articulo"];

			$a_facturar = [];

			$continuar;
			$datos_art = DB::queryFirstRow("SELECT abm_deposito.valor as str_deposito, id_deposito, pos_articulos.descripcion, pos_articulos.id, pos_articulos.categoria,
		  	(SELECT (neto_gravado + no_gravado + neto_exento + iva + otros_impuestos) FROM pos_articulos_precios WHERE id_articulo = pos_articulos.id  ORDER BY id DESC LIMIT 0,1) as precio_unitario,
		  	(SELECT iva_alicuota FROM pos_articulos_precios WHERE id_articulo = pos_articulos.id  ORDER BY id DESC LIMIT 0,1) as iva_alicuota

			FROM pos_articulos 
			INNER JOIN abm_deposito ON (abm_deposito.id = pos_articulos.id_deposito)
			WHERE pos_articulos.id = %d0 AND (pos_articulos.enabled = 1) AND (pos_articulos.id_deposito IN %li1)",$id_articulo, $_SESSION["user_depositos"]);

			$monto_facturar = $_POST["litros"] * $datos_art["precio_unitario"];

			$precios = [$monto_minimo];
			$acumulado = 0;

			while ($acumulado < $monto_maximo)
			{
				$precios[] = $precios[sizeof($precios)-1] + $paso;
				$acumulado = $precios[sizeof($precios)-1];
			}

			$acumulado = 0;
			$tickets = [];

			while ($acumulado < $monto_facturar)
			{
			
				$random = rand(0, sizeof($precios) - 1);

				$acumulado += $precios[$random];

				if ($acumulado > $monto_facturar)
				{
					$precios[$random] = $monto_facturar - ($acumulado - $precios[$random]); 
				}

				$temp = [
					"id_articulo" => $id_articulo,
					"descripcion" => $datos_art["descripcion"],
					"categoria" => $datos_art["categoria"],
					"total" => $precios[$random],
					"litros" => $precios[$random] / $datos_art["precio_unitario"],
				];

				$tickets[] = $temp;
			}

			 
			foreach ($tickets as $key => $value) {
				$impuestos = array("GASOIL"=> array("itc"=>"ITC_gasoil","igo"=>"IGO_gasoil"), "NAFTAS" => array("th"=>"TH_naftas","itc"=>"ITC_naftas"));

				$num = DB::queryFirstRow("SELECT MAX(id) as max_id FROM pos_ventas FOR UPDATE")["max_id"];
				$pos_ventas = array("id"=>$num+1,"id_cliente"=>1,"datetime"=>$date,"id_usuario"=>1,"es_devolucion"=>0,"id_venta_devuelta"=>0,"comentario"=>"", "es_mayorista"=>0, "es_remito"=>0, "es_libre"=>0, "id_detalle_mayorista"=>0, "id_detalle_remito"=>0, "pin" => "9999");
					DB::insert("pos_ventas", $pos_ventas);
					$id_venta = DB::insertId();
					$insert["pos_ventas_items"] = [];

					$insert["pos_ventas_items"] =
								["id_venta" => $id_venta, 
								"id_articulo" => $value["id_articulo"], 
								"cantidad" => $value["litros"], 
								"total" => $value["total"], 
								"id_deposito" => $datos_art["id_deposito"], 
								"iva_alicuota" => 21, 
								"descripcion" => $value["descripcion"]];

					switch ($value["categoria"]) {
				    case 'GASOIL':
				    case 'NAFTAS':
						$insert["pos_ventas_items"]["no_gravado"] = 0;
						$insert["pos_ventas_items"]["iva_alicuota"] = "21.00";

				    	$real_total = $value["total"]; //me guardo el real total para que despues en la cc sea correcto
				    	$insert["pos_ventas_items"]["total"] = abs($value["total"]);
						$insert["pos_ventas_items"]["iva"] = $value["total"];
				      
				      //traer valores para la familia registrada. aca ver si es GASOIL o NAFTAS si no, directamente iva 21 y chau
				      foreach ($impuestos[$value["categoria"]] as $llave => $falor) {
				        $query = DB::queryFirstRow("SELECT valor FROM abm_valores_impuestos WHERE datetime <= %s0 AND nombre = %s1 AND enabled = 1 ORDER BY datetime DESC LIMIT 0,1", date("Y-m-d"), $falor);
				        $insert["pos_ventas_items"][$llave] = $query["valor"] * abs((float)$value["litros"]);
				        $insert["pos_ventas_items"]["iva"] -= $insert["pos_ventas_items"][$llave]; //hasta aca para ir restando del total los impuestos
				       }

				      $insert["pos_ventas_items"]["neto_gravado"] = $insert["pos_ventas_items"]["iva"]/1.21;
				      $insert["pos_ventas_items"]["iva"] =  $insert["pos_ventas_items"]["neto_gravado"] *.21;
				       //var_dump($insert["pos_ventas_items"]["neto_gravado"],$insert["pos_ventas_items"]["iva"]);
				      //uso iva como variable temporal porque venia descontando de ahi y llegaba al iva

				      foreach ($impuestos[$value["categoria"]] as $llave => $falor) {
				        $insert["pos_ventas_items"][$llave] /=  abs((float)$value["litros"]);
				      }
				      $insert["pos_ventas_items"]["neto_gravado"] /=  abs((float)$value["litros"]);
				      $insert["pos_ventas_items"]["iva"] /=  abs((float)$value["litros"]);

				      $insert["pos_ventas_items"]["total"] = str_replace(",","",($insert["pos_ventas_items"]["total"]));
				      $insert["pos_ventas_items"]["neto_gravado"] = str_replace(",","",($insert["pos_ventas_items"]["neto_gravado"]));
				      $insert["pos_ventas_items"]["neto_exento"] = 0;


				     $insert["pos_ventas_items"]["total"] = $real_total;
				    break;			
				}
					$insert["pos_ventas_comprobante"] = [];

					$insert["pos_ventas_comprobante"]["id_provincia"] = 22;
	    			$insert["pos_ventas_comprobante"]["id_iva_situacion"] = 5;
	    			$insert["pos_ventas_comprobante"]["razon_social"] = "CONSUMIDOR FINAL";
	    			$insert["pos_ventas_comprobante"]["id_documento_tipo"] = 99;
	    			$insert["pos_ventas_comprobante"]["documento_numero"] =  99999999999;
	    			$insert["pos_ventas_comprobante"]["date"] = $date;
					$insert["pos_ventas_comprobante"]["id_moneda_tipo"] = "PES";
					$insert["pos_ventas_comprobante"]["moneda_tipo_cambio"] = 1;
					$insert["pos_ventas_comprobante"]["id_venta"] = $id_venta;
					$insert["pos_ventas_comprobante"]["id_comprobante_tipo"] = 6;
					#$insert["pos_ventas_comprobante"]["id_comprobante_tipo"] = 83;

					//header("HTTP/1.0 400");
    				foreach ($insert as $tabla => $valores) {
					//var_dump($tabla, $valores);
					DB::insert($tabla, $valores);
					}

					$id_comprobante = DB::queryFirstRow("SELECT id FROM pos_ventas_comprobante WHERE id_venta = %d0", $id_venta)["id"];

					#DB::insert("pos_ventas_tkt_fiscales", array("id_venta"=>$id_venta, "id_comprobante" => $id_comprobante, "id_comprobante_tipo"=>83, "datetime_queue"=>$date, "pv"=>$_POST["pv"]));	
				}

					//DB::rollback();
					//die();	

					$pos_stock_movimientos = array("id_usuario"=>1,"id_segmento"=>1, "datetime"=>$date, "id_movimiento_tipo"=>1, "id_deposito"=>$datos_art["id_deposito"], "cantidad"=>(float)$_POST["litros"] * -1, "id_articulo"=>$id_articulo,"comentario"=>"Venta masiva ".$date);
					DB::insert("pos_stock_movimientos", $pos_stock_movimientos);

		     		 $cantidad = (float)DB::queryFirstRow("SELECT cantidad FROM pos_stock WHERE id_articulo = %d0 FOR UPDATE",$id_articulo)["cantidad"] - (float)$_POST["litros"];

		      		DB::update("pos_stock", array("cantidad"=>$cantidad), "id_articulo = %d0", $id_articulo);
			DB::commit();
		break;

		default:
			echo $tabla;
		break;
	}
}

header("HTTP/1.0 200");die("200|OK");
?>