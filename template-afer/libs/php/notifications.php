<?PHP


function Notificate($options)
{
  $modulo = $options["modulo"];
  unset($options["modulo"]);
  
  $avoided = array($_SESSION["user_id"]);

  if ($modulo != 4) {$avoided[] = 5;}

  /* obtener usuarios con ese permiso */ 
  $permisos = DB::query("SELECT DISTINCT sys_seguridad.id_usuario 
  	FROM sys_seguridad 
  	INNER JOIN sys_usuarios ON (sys_usuarios.id = sys_seguridad.id_usuario)
  	WHERE sys_seguridad.id_modulo IN %li0 AND (id_usuario NOT IN %li1) AND sys_usuarios.enabled = 1", array($modulo,"7"), $avoided ); /* tambien notifica a logistica */

  $insert =array();
  foreach ($permisos as $key => $value) {
    $insert[] = array_merge($options, array("id_usuario"=>$value["id_usuario"]));
  }
    

   $insert = DB::insert("sys_notificaciones",  $insert);
  
}

?>