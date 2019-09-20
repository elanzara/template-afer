<?php 

if (isset(POS_MAX_CT_DIF[$_SESSION["user_id"]]))
{
  $ultimo_cierre = DB::queryFirstRow("SELECT id, datetime FROM pos_cierre_turnos WHERE id_usuario = %d0 ORDER BY id DESC LIMIT 0,1", $_SESSION["user_id"]);

  if ($ultimo_cierre == null) $ultimo_cierre["datetime"] = "2010-01-01 00:00:00";
  $diff = time() - strtotime($ultimo_cierre["datetime"]);
  if ($diff > POS_MAX_CT_DIF[$_SESSION["user_id"]] * 60 * 60)
  { 
  ?>
  alert("ULTIMO CIERRE DE TURNO MAYOR A 24 HS - CIERRE PARA EVITAR ESTA ALERTA");
  <?php 
  } 
}
?>