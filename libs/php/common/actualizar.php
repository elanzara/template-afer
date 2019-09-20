<?PHP

require_once("../mysql-functions.php");
require_once("../conn-data.php");
require_once("../session/ajax-session-validator.php");


if (!isset($_POST["DDBB_table"],$_POST["id"])) { header("HTTP/1.0 400");die("400|Petición no válida");}
if (sizeof($_POST) < 3) { header("HTTP/1.0 400");die("400|Petición no válida - sizeof()");}


$tabla = $_POST["DDBB_table"];
$id = $_POST["id"];
$date = date("Y-m-d G:i:s", time());

unset($_POST["DDBB_table"], $_POST["id"]);

if (in_array($tabla, array("pos_clientes","pos_proveedores"))) {if ($id < 99) {header("HTTP/1.0 400");die("400|No se puede editar este registro");}}

if ($tabla == "pos_clientes" && $_SESSION["user_type"] != 2 && isset($_POST["quota_diaria"])) unset($_POST["quota_diaria"]);
if ($tabla == "pos_clientes" && $_SESSION["user_type"] != 2 && isset($_POST["id_tipo_control_cc"])) unset($_POST["id_tipo_control_cc"]);
if ($tabla == "pos_clientes" && $_SESSION["user_type"] != 2 && isset($_POST["id_beneficiario"])) unset($_POST["id_beneficiario"]);
if ($tabla == "pos_clientes" && $_SESSION["user_type"] != 2 && isset($_POST["limite_global_cc"])) unset($_POST["limite_global_cc"]);

if ($tabla == "abm_bancos") {
	$_POST["moneda"] = $_POST["moneda"] * 2;

}

if ($tabla == "pos_clientes_cc_limites_individuales")
{
	$_POST["referencia"] = strtoupper(str_replace(" ", "", $_POST["referencia"]));
}

if (strpos($tabla,"prp-") === false)
{

switch ($tabla) {
	case 'pos_proyectos_etapas':
	DB::startTransaction();
		$status = DB::queryFirstRow("SELECT id_status, autorizacion_corte FROM pos_proyectos_etapas WHERE id = %d",$id);
			if (!in_array($_SESSION["user_id"], [1,2,9]))
			{
				unset($_POST["autorizacion_corte"]);
			}
			else
			{
				if ($_POST["autorizacion_corte"] != $status["autorizacion_corte"])
				{
					$_POST["id_usuario_autorizacion_corte"]  =$_SESSION["user_id"];
					$_POST["datetime_autorizacion_corte"]  =$date;
					$update = $_POST;
					unset($update["request"], $update["id_modulo_origen"], $update["DDBB_table"], $update["id"]);
					DB::update("pos_proyectos_etapas", $update, "id=%d0", $id);
					$rows1 = DB::affectedRows();
				}

			}

			if (isset($_POST["request"]))
			{
			$update = json_decode($_POST["request"],true)["order_detail"];

			foreach ($update as $key => $value) {
				$name = DB::queryFirstRow("SELECT descripcion FROM pos_proyectos_etapas_items WHERE id = %d0", $value["id"])["descripcion"];
				DB::update("pos_proyectos_etapas_items", ["id_status"=>$value["id_status"]], "id=%d0 AND id_etapa = %d1", $value["id"], $id);
				if (DB::affectedRows() > 0)
				{
					$str_status = DB::queryFirstRow("SELECT valor FROM abm_status_pedidos WHERE id = %d0", $value["id_status"])["valor"];
					$ins = array("id_usuario"=>$_SESSION["user_id"],"fecha"=>$date, "id_pedido" => $id, "id_modulo_origen"=> $_POST["id_modulo_origen"], "comment"=> "Ítem ".$name." nuevo estado: ".$str_status);
					DB::insert("pos_proyectos_comments",$ins);
				}
			}
			unset($_POST["request"]);
			}

			if (isset($_POST["status"]))
			{
					if ($_POST["id_status"] != $status["id_status"] && $_POST["id_status"] != "DEFAULT")
						{
						 	$insert = array("id_pedido" => $id,"id_usuario" =>$_SESSION["user_id"],"fecha" =>$date ,"id_modulo_origen" =>$_POST["id_modulo_origen"],"id_status"=>$_POST["id_status"]);
							DB::insert("pos_proyectos_status_history",$insert);
							$cambios_pedidos = array("datetime_last_change"=>$date, "id_last_change"=>$_SESSION["user_id"]);
							DB::update("pos_proyectos_etapas", $cambios_pedidos, "id=%d", $id);
						}
			}

		unset($_POST["id_modulo_origen"]);
	DB::commit();
	$rows1=1;
	break;
	
	default:
		DB::update($tabla, $_POST, "id=%d", $id);
		break;
}

	
}
else
{
	switch ($tabla) {
			case 'prp-ordenes_trabajo_editar':
			//las OT no se editan, se cancelan y se arman again
			break;
			case 'prp-ordenes_trabajo':
		DB::startTransaction();
		$update_ot = [
			"datetime_last_change"=>$date,
			"id_last_change"=>$_SESSION["user_id"]
		];

		$ot_data = json_decode($_POST["request"], true)["ot_data"];

		foreach ($ot_data as $key => $value) {
			$update_ot[$key] = $value;
		}
		unset($update_ot["id_modulo_origen"]);
		
		DB::update("pos_proyectos_ordenes_trabajo", $update_ot, "id = %d0", $id);
		$rows1 = DB::affectedRows();
		
		$ot_detail_items = json_decode($_POST["request"], true)["ot_detail"];

		if ($ot_detail_items)
		{

			foreach ($ot_detail_items as $key => $value) {
				$id_ot = $value["id"];
				unset($value["id"]);
				DB::update("pos_proyectos_ordenes_trabajo_items", $value, "id=%d0", $id_ot);
			}

		}
		
		DB::commit();
		break;
		case 'prp-abm_arquitectos':
			DB::startTransaction();
				$existe = DB::queryFirstRow("SELECT id FROM abm_arquitectos WHERE dni = %d0 AND id != %s1",$_POST["dni"], $id);
				if (sizeof($existe) > 0) 
				{
					 DB::rollback();
					 header("HTTP/1.0 502");die("502|DNI ya fue utilizado (Arquitecto #".$existe["id"].")");
				}
				DB::update("abm_arquitectos", $_POST, "id = %d0", $id);
				$rows1 = DB::affectedRows();
			DB::commit();	
		break;
		case 'prp-lotes_contenido':
		DB::startTransaction();
				DB::update("pos_articulos_lotes_contenido", $_POST, "id = %d0", $id);
				$rows1 = DB::affectedRows();
			DB::commit();	
		break;
		case 'prp-lotes':
			DB::startTransaction();
				DB::update("pos_articulos_lotes", $_POST, "id = %d0", $id);
				$rows1 = DB::affectedRows();
				DB::update("pos_articulos_lotes_contenido", ["enabled"=>$_POST["enabled"]], "id_lote = %d0", $id);
			DB::commit();	
		break;
		case 'prp-usuarios':
		DB::startTransaction();
			$username = $_POST["username"];
			$updArray= array("username"=>$_POST["username"],"enabled"=>$_POST["enabled"],"type"=> $_POST["type"]);
			//"external"=> $_POST["external"],
		if ($_POST["password"] != "")
			{
				$updArray["password"]=MD5($_POST["password"]);
			}
			$update = DB::update("sys_usuarios", $updArray, "id=%d", $id);
			$rows1 = DB::affectedRows();
			unset($_POST["password"],$_POST["type"], $_POST["username"],$_POST["enabled"], $_POST["external"]);

			$update = DB::update("sys_usuarios_datos", $_POST, "id_usuario=%d", $id);
			$rows1 += DB::affectedRows();
		DB::commit();
		break;
		case 'prp-pos_articulos':
			DB::startTransaction();
			$keys_precio = array("neto_gravado", "no_gravado", "neto_exento", "iva_alicuota", "iva", "otros_impuestos");
			$insert_precio = array();

		 	$where = new WhereClause('and');  

			foreach ($keys_precio as $value) {
				$insert_precio[$value] = $_POST[$value];
				$where->add($value.'=%d', $_POST[$value]);
				unset($_POST[$value]);
			}

			unset($_POST["precio_final"]);
			//unset($_POST["id_deposito"]);

			$update_articulo = $_POST;

			if ($id > 99) DB::update("pos_articulos",$update_articulo, "id = %d0", $id);

			DB::update("pos_stock", ["id_deposito"=>$_POST["id_deposito"]], "id_articulo = %d0", $id);

			
			$rows1 = 2;//DB::affectedRows(); 																			/*VER*/ 

			$es_precio_actual = DB::query("SELECT id FROM pos_articulos_precios WHERE %l0 AND id = (SELECT MAX(id) FROM pos_articulos_precios b WHERE id_articulo = %d1)",$where->text(), $id);

			if (sizeof($es_precio_actual) == 0) //no existe ese combo
			{
				$insert_precio["id_articulo"] = $id;
				$insert_precio["datetime"] = date("Y-m-d G:i:s");
				DB::insert("pos_articulos_precios",$insert_precio);
			}
			DB::commit();
		break;
		
		case "prp-pedidos":
		DB::startTransaction();
			$request = json_decode($_POST["request"], true);
			$modulo_origen = $_POST["id_modulo_origen"];

			if($request["aditional_data_fields"]["pagos_b"]+$request["aditional_data_fields"]["pagos_sf"] != 100)
			{
				header("HTTP/1.0 503");DB::rollback();die("503|Los porcentajes de pago deben sumar 100%");
			}
			
			if(!in_array($_SESSION["user_id"], [1, 2,31,9])) //solo pueden crear de este tipo damian afer rafa y yessica
			{
				unset($request["aditional_data_fields"]["orden_saldo"]);
			}
			unset($_POST["id_modulo_origen"]);

			$final = array_merge($request["client_data_fields"],$request["aditional_data_fields"],array("datetime_last_change"=>$date, "id_last_change"=>$_SESSION["user_id"]));
			$items_antes = DB::query("SELECT id_articulo, cant FROM pos_proyectos_etapas_items WHERE id_etapa = %d0", $id);
			$items_antes = DBHelper::reIndex($items_antes, "id_articulo");

			$estado = DB::queryFirstRow("SELECT id_status FROM pos_proyectos_etapas WHERE id = %d",$id);

			if ($request["aditional_data_fields"]["codigo_reposicion"] == $id)
			{
				header("HTTP/1.0 500");die("500|No es posible asignar orden de reposición a la misma");
			}
			if (isset($final["id_status"]))
			{
				if ($final["id_status"] != $estado["id_status"] && $final["id_status"] != "DEFAULT")
				{
				 	$insert = array("id_pedido" => $id,"id_usuario" =>$_SESSION["user_id"],"fecha" =>$date ,"id_modulo_origen" =>$modulo_origen,"id_status"=>$final["id_status"]);
					DB::insert("pos_proyectos_status_history",$insert);
				}
				if (in_array($final["id_status"], array("0","DEFAULT"))) unset($final["id_status"]);
			}

			DB::update("pos_proyectos_etapas", $final, "id=%d",$id);
			$rows1 =  DB::affectedRows(); 

			$cambios = DB::insert("pos_etapas_items_cambios", array("id_usuario"=>$_SESSION["user_id"], "datetime"=> $date, "id_etapa"=>$id));
			$id_cambio = DB::insertId();

			if (sizeof($request["order_detail"]) > 0){
				
			$no_delete = [];
			$changes = [];
			foreach ($request["order_detail"] as $key => $value) {

				//chequear contra el pedido anterior
				$anterior = 0;
				if (isset($items_antes[$value["id_articulo"]])) {
					$anterior = $items_antes[$value["id_articulo"]]["cant"];
				}
						
				$temp=["id_cambio"=> $id_cambio, "id_articulo"=>$value["id_articulo"], "posterior"=>$value["cant"], "anterior" => $anterior];
				unset($items_antes[$value["id_articulo"]]);
				$changes[] = $temp;

				unset($request["order_detail"][$key]["str_unidad_medida"]);
				unset($request["order_detail"][$key]["numero"]);

				if (isset($value["id"]))
				{	
					 if ($value["id"] == "0"){ 	
						$request["order_detail"][$key]["id_etapa"] = $id; //ETAPA E SUBPROY
						DB::insert("pos_proyectos_etapas_items",$request["order_detail"][$key]);
						$no_delete[] = DB::insertId();
						$rows1+=1;
					 }
					 else
					{
						unset($request["order_detail"][$key]["id"]);
						DB::update("pos_proyectos_etapas_items",$request["order_detail"][$key], "id=%d",$value["id"]);
						$no_delete[] = $value["id"];
						$rows1+=1;


						//chequear que la nueva cantidad no sea menor a la cantidad de OT generadas

						$OT_para_este = DB::queryFirstRow("SELECT SUM(pos_proyectos_ordenes_trabajo_items.cant) as cant_ud, COUNT(pos_proyectos_ordenes_trabajo.id) as cant_ot
						FROM  pos_proyectos_ordenes_trabajo_items 
						INNER JOIN pos_proyectos_ordenes_trabajo ON  (pos_proyectos_ordenes_trabajo.id = pos_proyectos_ordenes_trabajo_items.id_orden_trabajo)
						WHERE  pos_proyectos_ordenes_trabajo.enabled = 1 AND pos_proyectos_ordenes_trabajo_items.id_articulo_etapas_items = %d0", $value["id"]);

						if ($OT_para_este["cant_ud"] > ($value["cant"]*1.1))
						{

							 header("HTTP/1.0 500");
							 die("500|No se han hecho cambios $tabla:\nEl ítem '".$value["descripcion"]."' (número #".$value["orden"].") posee ".$OT_para_este["cant_ud"]." unidades asignadas en ".$OT_para_este["cant_ot"]." OT presentes en el sistema.\n\n No puede editar el pedido y asignar menos que esa cantidad (intentó asignar ".$value["cant"].")");
						}

					}

				}
				else
				{
						$request["order_detail"][$key]["id_etapa"] = $id; //ETAPA E SUBPROY
						DB::insert("pos_proyectos_etapas_items",$request["order_detail"][$key]);
						$no_delete[] = DB::insertId();
						$rows1+=1;
				}
				
			}

				DB::delete("pos_proyectos_etapas_items","id NOT IN %li0 AND id_etapa = %d1",$no_delete,$id);

				//recorro si quedo algo en el array de control
				foreach ($items_antes as $key => $value) {
					$changes[] = ["id_cambio"=> $id_cambio, "id_articulo"=>$value["id_articulo"], "posterior"=>0, "anterior" => $value["cant"]];
				}

				//insertar control de cambios
				DB::insert("pos_etapas_items_cambios_contenido", $changes);
			}

			DB::commit();
		break;		
		default:
			# code...
		break;
	}
}

$rows = DB::affectedRows();
if (isset($rows1)){
$rows = $rows + $rows1;
}

if ($rows > 0)
{
	header("HTTP/1.0 200");die("200|OK");
}
else
{
	 header("HTTP/1.0 500");die("500|No se han hecho cambios $tabla");
}

?>