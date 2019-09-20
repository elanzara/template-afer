<?PHP

function SelectUsuario($valor="",$alias="", $class="", $filter = null, $multiple = false)
{
   $where = new WhereClause('and');  
 
  if ($filter != null)
  {
    foreach ($filter as $key => $value) {
    $where->add($key.'=%d', $value);
  }

  }


  $result = DB::query("SELECT sys_usuarios.id, 
    CONCAT(sys_usuarios_datos.nombre, ' ', sys_usuarios_datos.apellido) as str_n_completo
   FROM `sys_usuarios` INNER JOIN sys_usuarios_datos ON (sys_usuarios_datos.id_usuario = sys_usuarios.id)
    WHERE %l AND sys_usuarios.enabled = 1 ORDER BY str_n_completo ASC", $where->text());

  if ($multiple==true) {$multiple = ' multiple size='.sizeof($result);}else{$multiple="";}

  if ($alias=="null"){$name = "";}
  elseif ($alias != "") {$name = $alias;}
  else {$name=$tabla;}

  $return = "<select ".$multiple." name='".$name."[]' class='$class'>";
  if ($multiple == false) $return .= "<option value='DEFAULT'>Elija usuario</option>";
    foreach ($result as $row) 
    {
      $a="";
        if ($row['id'] == $valor) {$a = " selected='selected'";}
        $return = $return . "<option value='".$row['id']."'$a>".ucwords($row["str_n_completo"])."</option>\r\n";
    }

  $return = $return . "</select>"; 
  return $return;
}


function SelectPico($valor="",$alias="", $class="", $filter = null,$extra="", $value_field="id",$fixed_options=[])
{

   $where = new WhereClause('and');  
  if ($filter != null)
  {
    foreach ($filter as $key => $value) {
      if (is_array($value)) {$where->add($key.' IN %li0', $value);}
      else {$where->add($key.' = %d0', $value);}
    }
  }

$result = DB::query("SELECT abm_surtidores_picos.id, abm_surtidores.nombre as surtidor_nombre,abm_surtidores_picos.id_deposito,
  abm_surtidores_picos.lado as surtidor_lado, abm_surtidores_picos.numero as pico_numero, pos_articulos.descripcion as producto_nombre,
   abm_deposito.valor as  tanque_nombre, pos_articulos.id as id_articulo

  FROM abm_surtidores_picos 
  INNER JOIN abm_surtidores ON (abm_surtidores.id = abm_surtidores_picos.id_surtidor) 
  INNER JOIN pos_articulos ON (pos_articulos.id = abm_surtidores_picos.id_articulo) 
  INNER JOIN abm_deposito ON (abm_deposito.id = abm_surtidores_picos.id_deposito) 
  WHERE %l0 AND abm_surtidores_picos.enabled = 1  ORDER BY `abm_surtidores_picos`.`id` ASC ", $where->text());

$result = array_merge($result,$fixed_options);

if ($alias=="null"){$name = "";}
  elseif ($alias != "") {$name = $alias;}
  else {$name="abm_surtidores_picos";}

  $return = "<select name='$name' class='$class' $extra><option value='DEFAULT'>Elija opción</option>";
    foreach ($result as $row) 
    {
      $a="";
        if ($row[$value_field] == $valor) {$a = " selected='selected'";}
        $return = $return . "<option value='".$row[$value_field]."' data-articulo='".$row["id_articulo"]."'>". $row["surtidor_nombre"] . " - Lado #". $row["surtidor_lado"]. " - Pico #". $row["pico_numero"]. " - ". $row["producto_nombre"] . "</option>\r\n";
    }


  $return = $return . "</select>"; 
  return $return;

}

function SelectTransporteMayorista($valor="",$alias="", $class="", $field="valor", $filter = null, $value_field="id", $extra="",$fixed_options=[])
{
  $tabla = "abm_transportes_mayorista";
   $where = new WhereClause('and');  
  //$where->add('enabled=%d', 1);

  if ($filter != null)
  {
    foreach ($filter as $key => $value) {
      if (is_array($value)) {$where->add($key.' IN %li0', $value);}
      else {$where->add($key.' = %d0', $value);}
    }
  }

  $result = DB::query("SELECT $tabla.*, pos_articulos.descripcion as str_articulo FROM `$tabla` LEFT JOIN pos_articulos ON ($tabla.id_articulo = pos_articulos.id) WHERE %l AND $tabla.enabled = 1 ORDER BY `$field` ASC", $where->text());

  if ($alias=="null"){$name = "";}
  elseif ($alias != "") {$name = $alias;}
  else {$name=$tabla;}

  $result = array_merge($result,$fixed_options);
  $return = "<select name='$name' class='$class' $extra><option value='DEFAULT'>Elija opci&oacute;n</option>";
    foreach ($result as $row) 
    {
      $a="";
        if ($row[$value_field] == $valor) {$a = " selected='selected'";}
        $extra_art = " [Vacío]";
        if ($row["saldo"] != 0) {$extra_art = " [Saldo: ".$row["saldo"]."L de ".$row["str_articulo"]."]";}
        $return = $return . "<option value='".$row[$value_field]."'$a>".ucwords($row[$field]).$extra_art."</option>\r\n";
    }


  $return = $return . "</select>"; 
  return $return;
}


function SelectABMCampoEnum($tabla,$valor="",$alias="", $class="", $field="valor", $filter = null, $value_field="id", $extra="",$fixed_options=[])
{
   $where = new WhereClause('and');  
  //$where->add('enabled=%d', 1);
  if ($filter != null)
  {
    foreach ($filter as $key => $value) {
      if (is_array($value)) {$where->add($key.' IN %li0', $value);}
      else {$where->add($key.' = %d0', $value);}
    }
  }

  $result = DB::query("SELECT * FROM `$tabla` WHERE %l AND enabled = 1 ORDER BY `$field` ASC", $where->text());

  if ($alias=="null"){$name = "";}
  elseif ($alias != "") {$name = $alias;}
  else {$name=$tabla;}

  $result = array_merge($result,$fixed_options);
  $return = "<select name='$name' class='$class' $extra><option value='DEFAULT'>Elija opci&oacute;n</option>";
    foreach ($result as $row) 
    {
      $a="";
        if ($row[$value_field] == $valor) {$a = " selected='selected'";}
        $return = $return . "<option value='".$row[$value_field]."'$a>".ucwords($row[$field])."</option>\r\n";
    }


  $return = $return . "</select>"; 
  return $return;
}

function SelectComprobante($tabla,$name, $filter, $class,$handler_table,$field_name, $fixed_options=[], $extra = null)
{
  $where = new WhereClause('and');  
  $where->add($tabla.'.enabled = 1');
  if ($filter != null)
  {
    foreach ($filter as $key => $value) {
      if (is_array($value)) {$where->add($key.' IN %li0', $value);}
      else {$where->add($key.' = %d0', $value);}
    }
  }

  $result = DB::query("SELECT $tabla.$field_name as id, comprobante_pv, comprobante_numero, abm_comprobante_tipo.valor as str_comprobante_tipo FROM `$tabla` 
    INNER JOIN $handler_table ON ($handler_table.id = $tabla.$field_name)  
    INNER JOIN abm_comprobante_tipo ON (abm_comprobante_tipo.id = $tabla.id_comprobante_tipo)  
    WHERE %l ORDER BY $tabla.id ASC", $where->text());

   $result = array_merge($result,$fixed_options);
  $return = "<select name='$name' class='$class' $extra><option value='DEFAULT'>Elija opci&oacute;n</option>";
    foreach ($result as $row) 
    {
      $a="";
        $return = $return . "<option value='".$row["id"]."'>#".str_pad($row["id"], 5, "0", STR_PAD_LEFT)." ".$row["str_comprobante_tipo"]. " " . str_pad($row["comprobante_pv"], 4, "0", STR_PAD_LEFT)."-".str_pad($row["comprobante_numero"], 8, "0", STR_PAD_LEFT)."</option>\r\n";
    }


  $return = $return . "</select>"; 
  return $return;
}

function UlABMCampoEnum($tabla,$valor="",$alias="", $class="", $field="valor", $filter = null)
{
   $where = new WhereClause('and');  
  //$where->add('enabled=%d', 1);
  if ($filter != null)
  {
    foreach ($filter as $key => $value) {
    $where->add($key.'=%d', $value);
  }

  }

  $result = DB::query("SELECT * FROM `$tabla`  WHERE %l AND enabled = 1 ORDER BY `$field` ASC", $where->text());

  if ($alias=="null"){$name = "";}
  elseif ($alias != "") {$name = $alias;}
  else {$name=$tabla;}
  $return = "<ul class='$class' role='menu'><li><a href='DEFAULT'>Todas las categorías</a></li><li class='divider'></li><li class='dropdown-header'>Categorías</li>";
    foreach ($result as $row) 
    {
      $a="";
        if ($row['id'] == $valor) {$a = " selected='selected'";}
        $return = $return . "<li><a href='".$row['id']."' >".ucwords($row[$field])."</a></li>\r\n";
    }

  $return = $return . "</ul>"; 
  return $return;
}

function SelectABMCampoEnumFixed($tabla,$valor="",$alias="", $class="", $field="valor", $filter = null, $value_field="id", $extra="",$fixed_options=[])
{
   $where = new WhereClause('and');  
  //$where->add('enabled=%d', 1);
  if ($filter != null)
  {
    foreach ($filter as $key => $value) {
      if (is_array($value)) {$where->add($key.' IN %li0', $value);}
      else {$where->add($key.' = %d0', $value);}
    }
  }

  $result = DB::query("SELECT * FROM `$tabla`  WHERE %l AND enabled = 1 ORDER BY `$field` ASC", $where->text());

  if ($alias=="null"){$name = "";}
  elseif ($alias != "") {$name = $alias;}
  else {$name=$tabla;}

  $result = array_merge($result,$fixed_options);
  $return = "<select name='$name' class='$class' $extra><option value='DEFAULT'>Elija opci&oacute;n</option>";
    foreach ($result as $row) 
    {
      $a="";
        if ($row[$value_field] == $valor) {$a = " selected='selected'";}
        $return = $return . "<option value='".$row[$value_field]."'$a>".ucwords($row[$field])."</option>\r\n";
    }


  $return = $return . "</select>"; 
  return $return;
}

function CheckTodosConCbteEmitido($user_id, $delay = 5)
{
  //para evitar si no llega a navegar a impresion-comprobante, revisar que todos las ventas no devueltas tengan algo +0 o algo asi

  $ultimo_cierre = DB::queryFirstRow("SELECT datetime FROM pos_cierre_turnos WHERE id_usuario = %d0 ORDER BY id DESC LIMIT 0,1", $user_id);
   $datetime = date("Y-m-d G:i:s", strtotime("now -$delay seconds"));
$sql = DB::queryOneColumn('id', 'SELECT 
pos_ventas.id
  FROM pos_ventas 
  LEFT JOIN pos_ventas_comprobante ON (pos_ventas_comprobante.id_venta = pos_ventas.id)
  LEFT JOIN pos_clientes ON (pos_clientes.id = pos_ventas.id_cliente)
  LEFT JOIN abm_comprobante_tipo ON (abm_comprobante_tipo.id = pos_ventas_comprobante.id_comprobante_tipo)
  LEFT JOIN pos_ventas_tkt_fiscales ON (pos_ventas_tkt_fiscales.id = (SELECT id FROM pos_ventas_tkt_fiscales WHERE pos_ventas_tkt_fiscales.id_comprobante = pos_ventas_comprobante.id ORDER BY id DESC LIMIT 1)) 
  WHERE (pos_ventas.datetime BETWEEN %s0 AND %s1) 
  AND pos_ventas.id_usuario  = %d2 AND pos_ventas_comprobante.id_comprobante_tipo NOT IN (999999,1,6,3,8)
  /* remito y electronica NO se validan aca */
  AND (pos_ventas_tkt_fiscales.status <= 0 OR (pos_ventas.es_devolucion = 0 AND pos_ventas_tkt_fiscales.status IS NULL)
 /* si la propia tiene estado fiscal menor a cero o no es devolucion y no hay registro en tabla de tkt fiscal */
  OR (pos_ventas.es_devolucion = 1 AND pos_ventas_tkt_fiscales.status IS NULL AND 
 /*si es devolucion y no hay registro en tabla de tkt fiscal */
  (IFNULL((SELECT t1.comprobante_numero FROM pos_ventas_comprobante t1
        LEFT JOIN pos_ventas_tkt_fiscales t2 ON (t2.id = (SELECT id FROM pos_ventas_tkt_fiscales t3 WHERE t3.id_comprobante = t1.id ORDER BY id DESC LIMIT 1)) WHERE t1.id_venta = pos_ventas.id_venta_devuelta), 0)) > 0)
  )
  /* si el numero de la que deberian haber devuelto es mayor a cero tambien salta */

/*
Te explico: va a saltar si el propio estado fiscal es menor a cero, si no es devolucion y no tiene en tabla tkt fiscal y
si es devolucion pero no tiene tkt fiscal busca a ver si le correspondia tener (cbte_numero menor a 0)
*/

  AND IFNULL((SELECT id FROM pos_ventas t2 WHERE t2.id_venta_devuelta = pos_ventas.id),0) = 0

  /*
  salta cn todo lo de arriba y aparte no tiene que estar devuelta esa misma
  */

  ',$ultimo_cierre["datetime"],$datetime , $user_id);

//Validacione de electronicas: es ver si tiene A y si tiene devolucion (ver cuando no tiene en FE_REQUESTS)

$sql2 = DB::queryOneColumn('id', 'SELECT 
pos_ventas.id
  FROM pos_ventas 
  LEFT JOIN pos_ventas_comprobante ON (pos_ventas_comprobante.id_venta = pos_ventas.id)
  LEFT JOIN pos_ventas_fe_requests ON (pos_ventas_fe_requests.id = (SELECT id FROM pos_ventas_fe_requests t4 WHERE t4.id_venta = pos_ventas.id ORDER BY id DESC LIMIT 0,1))
  WHERE 
  ((pos_ventas.datetime BETWEEN %s0 AND %s1) AND pos_ventas.id_usuario  = %d2 AND pos_ventas_comprobante.id_comprobante_tipo IN (1,6,3,8)) AND 

  (
    (pos_ventas_fe_requests.resultado != "A" AND (SELECT id FROM pos_ventas t2 WHERE t2.id_venta_devuelta = pos_ventas.id) IS NULL) 
    OR /* si no tiene op Aprobada y no está devuelta */
    (
      pos_ventas_fe_requests.resultado IS NULL AND 
      pos_ventas.es_devolucion = 1 AND  /* o no tiene en fe_requests, es devolucion y la venta que devuelve tiene op aprobada */
      (
        IFNULL
        (
          (SELECT t1.id FROM pos_ventas_fe_requests t1 WHERE t1.id_venta = pos_ventas.id_venta_devuelta AND t1.resultado = "A" ORDER BY id DESC LIMIT 0,1), 0
        )

         > 0
      )
    )
  )',$ultimo_cierre["datetime"],$datetime , $user_id);



/* ver si nunca me entero que estuvo OK. Si hay -4 con numero pedir subir CSV? */

$sql = array_merge($sql, $sql2);

if (sizeof($sql)>0)
{
  $ret = implode(", ", $sql);
  return $ret;
} 
return false;

}


function Number($input, $decimals)
{
return number_format((float)$input,$decimals);
}

function AddZero($valor){return str_pad( $valor, 2, "0", STR_PAD_LEFT );}
function MonthDays($month, $year){return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);} 
function ParseDateToTime($date){$date = str_replace(array("/","-","."),"-", $date);$date = str_replace("-13","-2013", $date);$time = strtotime($date);return $time;}
function ParseDate($date){$date = str_replace(array("/","-","."),"-", $date);$date = str_replace("-13","-2013", $date);$time = strtotime($date);$date = date("d/m/Y", $time);return $date;}
function MakeSecondsFromTime($time){if (strlen($time) > 8) $time = "00:00:00";$split = explode(":",$time);$seconds = intval($split[1]) * 60 + intval($split[0])*60*60;return intval($seconds);}
function MakeTimeFromSeconds($total_seconds){$horas = floor ( $total_seconds / 3600 ); $minutes = ( ( $total_seconds / 60 ) % 60 );$seconds = ( $total_seconds % 60);if ($horas < 0 || $minutes <0 || $seconds <0) return "00:00:00";$time['horas']= str_pad( $horas, 2, "0", STR_PAD_LEFT );$time['minutes']= str_pad( $minutes, 2, "0", STR_PAD_LEFT );$time['seconds']= str_pad( $seconds, 2, "0", STR_PAD_LEFT );$time= implode( ':', $time );return $time;}
function LogAndEcho($text){echo "$text<br/>";$abre = fopen("log.txt", "a");$grabar = fwrite($abre, $text."\n");fclose($abre);}
function myTruncate($string, $limit, $break=" ", $pad="...") {if(strlen($string) <= $limit) return $string; if(false !== ($breakpoint = strpos($string, $break, $limit))) { if($breakpoint < strlen($string) - 1) { $string = substr($string, 0, $breakpoint) . $pad; } } return $string; }

/** Actual month last day **/
  function last_month_day($month, $year) { 
      $day = date("d", mktime(0,0,0, $month+1, 0, $year));
 
      return date('Y-m-d', mktime(0,0,0, $month, $day, $year));
  };
 
  /** Actual month first day **/
  function first_month_day($month, $year) {
      return date('Y-m-d', mktime(0,0,0, $month, 1, $year));
  }

function ForcePDFDownload($path, $name)
{
  if (!is_file($path. $name)) {
  header("Location: /estado/?error&txt=Error al acceder al PDF (path check)");die("401|FORBIDDEN");
    die();
  }

  header('Content-Disposition: attachment; filename="'.$name.'"');
  header('Content-type:application/pdf');
  header('Content-Length: ' . filesize($path. $name));
  readfile($path. $name);
  die();
}
?>