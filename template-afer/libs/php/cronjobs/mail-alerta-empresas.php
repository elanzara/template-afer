ALERTA EMPRESAS: <?php

define("header", "<table cellSpacing=0 cellPadding=0 width='100%' border=0 align='center'  style='background-color:#f2f2f2;'>
<tbody>
<tr><td style='background:white;padding:0 10px;'>
<div style='width:500px;height:100px;margin:0 auto;'>
<img src='http://sistemaexterno.com.ar/header-fenzi-new.jpg' alt='logo_fenzi'/>
</div></td></tr>
<tr><td style='background:#fff;padding:0 10px;'>
<div style='width:500px;background:#fff;margin:15px auto;font-family:Arial, sans-serif;'>");

define("footer", "</div><div style='color:#333;font-size:14px;line-height:20px;text-align:right;'>
<p>Atentamente,<br/><b>Fenzi South America</b></p></div></div></td></tr>");

  $alertas = DB::query("SELECT CONCAT(t1.nombre, ' ', t1.apellido) AS  str_user_from,
    texto, added,fecha, id_user_to, codigo_cliente, sys_alertas_empresas.id as id_alerta,
    abm_codigos_tango.codigo_tango, abm_codigos_tango.razon_social
    FROM sys_alertas_empresas 
    INNER JOIN sys_usuarios_datos t1 ON (t1.id_usuario = sys_alertas_empresas.id_user_from)
    INNER JOIN abm_codigos_tango ON (abm_codigos_tango.codigo_tango = sys_alertas_empresas.codigo_cliente)
    WHERE sys_alertas_empresas.status = 0 AND fecha = %s0", date("Y-m-d"));

  foreach ($alertas as $key => $datos_alerta) {

    $id_usuarios_to = json_decode($datos_alerta["id_user_to"],true);
    $mail_to_list = DB::queryOneColumn("email","SELECT email FROM sys_usuarios_datos WHERE id_usuario IN %li0",$id_usuarios_to);
    //$mail_to_list = array("info@afer.com.ar");
     
      $body = "<h2 style='color:#000;font-size:20px;'>Atenci&oacute;n:</h2>
      <div style='color:#333;font-size:14px;line-height:20px;text-align:justify;'>
      <p>".$datos_alerta["str_user_from"]." recuerda</p>
      <p><b>Cliente #".$datos_alerta["codigo_tango"]." - ".$datos_alerta["razon_social"]."</b></p>
      <p><b>Fecha de programaci&oacute;n: </b>".$datos_alerta["fecha"]."</p>
      <p><b>Alerta: </b>".$datos_alerta["texto"]."</p>
      <p><b>Resumen de comentarios: </b></p>";

    $comments = DB::query("SELECT fecha, comment as texto,
    sys_usuarios_datos.nombre as usuario_nombre,
    sys_usuarios_datos.apellido as usuario_apellido
    FROM sys_empresas_comments 
    INNER JOIN sys_usuarios_datos ON (sys_usuarios_datos.id_usuario = sys_empresas_comments.id_usuario)
    WHERE codigo_cliente = %d0 and sys_empresas_comments.enabled = 1 
    AND (fecha BETWEEN %s1 AND %s2)
    ORDER BY fecha ASC",$datos_alerta["codigo_tango"],substr($datos_alerta["added"],0,10),$datos_alerta["fecha"]);

   /* $alertas = DB::query("SELECT texto, added as fecha,
    sys_usuarios_datos.nombre as usuario_nombre,
    sys_usuarios_datos.apellido as usuario_apellido
    FROM sys_alertas_empresas 
    INNER JOIN sys_usuarios_datos ON (sys_usuarios_datos.id_usuario = sys_alertas_empresas.id_user_from)
    WHERE codigo_cliente = %d0 and sys_alertas_empresas.status ORDER BY fecha DESC",$datos_alerta["codigo_tango"]);
    var_dump($alertas);

    $final = array_merge($comments, $alertas);*/

    foreach ($comments as $key => $value) {
      $body .= "<p><b>".$value["usuario_nombre"]." ".$value["usuario_apellido"]." - ".$value["fecha"] ."</b>: ".$value["texto"] ."</p>";
    }

    $body .= "<p><b>Link acceso: </b><a href='http://192.168.0.10:665/clientes/".$datos_alerta["codigo_cliente"]."/'>Interno a FENZI</a> | <a href='http://fenzi.ddns.net:665/clientes/".$datos_alerta["codigo_cliente"]."/'>Externo a FENZI</a></p>";

      DB::insert("mail_queue",array(
        "from"=>"sip@fenzisouthamerica.com",
        "to" => json_encode($mail_to_list),
        "to_bcc"=>"[]",
        "text" => header.$body.footer,
        "subject" => "Alerta Cliente #".$datos_alerta["codigo_tango"]));

      $rows = DB::affectedRows();

      if ($rows > 0)
      {
        DB::update("sys_alertas_empresas",array("status"=>1), "id=%d", $datos_alerta["id_alerta"]);
      }
}

?>