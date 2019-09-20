<?PHP
$module_from_URL = explode("/", $_SERVER["REQUEST_URI"]);
$module_from_URL = $module_from_URL[1];

$request = DB::queryFirstRow("SELECT sys_seguridad.id FROM sys_seguridad 
	INNER JOIN sys_modulos 	ON (sys_modulos.id = sys_seguridad.id_modulo)
	INNER JOIN sys_usuarios 	ON (sys_usuarios.id = sys_seguridad.id_usuario)
	WHERE sys_modulos.filename=%s0 and sys_seguridad.id_usuario =%d1 AND type <> 'practico'",$module_from_URL, $_SESSION["user_id"]);
if (sizeof($request)==0) {header("HTTP/1.0 400");die("400|Sin privilegios ".$module_from_URL);}
?>