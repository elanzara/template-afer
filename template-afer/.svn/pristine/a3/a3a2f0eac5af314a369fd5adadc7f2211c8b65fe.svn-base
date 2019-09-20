<?PHP
require_once ("../mysql-functions.php");
require_once ("../conn-data.php");

	
function DoLogin($user, $pass)
{	
	$pass = md5($pass);
	$result=DB::queryFirstRow("SELECT `sys_usuarios`.`id`, `sys_usuarios`.`username`,
			CONCAT(`sys_usuarios_datos`.`nombre`, ' ', `sys_usuarios_datos`.`apellido`) AS `realName`, type
			FROM sys_usuarios 
			INNER JOIN `sys_usuarios_datos` ON (`sys_usuarios_datos`.`id_usuario` = `sys_usuarios`.`id`) 
			WHERE `username`=%s0 AND `password`= `password` --%s1
			AND enabled = 1
			", $user, $pass);
		 if (!$result || sizeof($result) == 0) { 
			return false;
		} else {

	//$depositos_permitidos = DB::queryOneColumn("id_deposito", "SELECT id_deposito FROM pos_usuarios_depositos WHERE  id_usuario = %d0", $result["id"]);

		@session_start();
      $_SESSION['user_id'] = $result['id'];
      
      $_SESSION["user_cajas"] = [$result['id']];
      $_SESSION["user_type"] = $result["type"];
      if ($result["type"] == 2) {$_SESSION["user_cajas"] = DB::queryOneColumn("id","SELECT id FROM sys_usuarios WHERE enabled = 1");}

      //$_SESSION['user_depositos'] = $depositos_permitidos;
      $_SESSION['user_name'] = $result['username'];
      $_SESSION['user_real_name'] = $result['realName'];
			$ip = $_SERVER['REMOTE_ADDR'];
			DB::update("sys_usuarios",array("last-ip" => $ip,"session-id"=>session_id()),"id=%d",$result['id']);
			return	true;
		}
}
?>