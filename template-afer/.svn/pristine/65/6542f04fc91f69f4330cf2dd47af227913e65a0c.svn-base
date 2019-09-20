<?php 
if($_SESSION["user_type"] != 2) { 
?>
<script type="text/javascript">
  location.href="/clientes/?error&txt=Acceso a sección restringida.";
</script>
<?php 
die(); 
}

$permisos = DB::query("SELECT sys_modulos.valor as str_modulo, sys_seguridad.id FROM sys_seguridad
  INNER JOIN sys_modulos ON (sys_modulos.id = sys_seguridad.id_modulo) WHERE sys_seguridad.id_usuario = %d0",$_GET["id"]);

$usuario = DB::queryFirstRow("SELECT apellido, nombre FROM sys_usuarios_datos WHERE id_usuario = %d", $_GET["id"]);

?>
  <h1 class="page-header">Edición de Permisos</h1>
  <h3>Usuario: <?php echo $usuario["nombre"] . " ". $usuario["apellido"]; ?></h3>

<div class="form panel panel-success hidden">
  <div class="panel-heading">
    <h3 class="form panel-title">Nuevo registro</h3>
  </div>
  <div class="panel-body">
  <form method="POST" id="abml">
      <input id="DDBB_table" name="DDBB_table" type="hidden" value="prp-sys_seguridad" class="fixed">
      <input id="id_usuario" name="id_usuario" type="hidden" value="<?php echo $_GET['id']; ?>" class="fixed">
      <div class="form-group col-xs-12"> <label for="id_modulo">Módulo</label><?php echo SelectABMCampoEnum("sys_modulos",null,"id_modulo", "obligatorio form-control"); ?></div>
<div class="row text-center">
               <button class="btn btn-success" id="aceptar" role="button"><i class="fa fa-check"></i> Aceptar</button>&nbsp;&nbsp;
               <button class="btn btn-danger" id="cancelar" role="button"><i class="fa fa-times"></i> Cancelar</button>
</div>
  </form>
</div>
</div>


          <div class="panel panel-primary">
            <div class="panel-heading with-buttons">
              <h3 class="panel-title pull-left hidden-xs">Registros</h3>
              <div class="pull-right">
               <button class="btn btn-success" id="addRec" role="button"><i class="fa fa-plus"></i> Nuevo registro</button>
               <button class="btn btn-danger" id="cleanF" role="button"><i class="fa fa-paint-brush"></i> Limpiar filtros</button>&nbsp;
             </div>
                 <div class="clearfix"></div>
            </div>
            <div class="panel-body">
			  <div class="table-responsive">
			  <table id="tblRegistros" class="table">
			    <thead>
            <tr>
        <th filter="false" style="width:11px;"><i class="fa fa-times"></i></th>
        <th style="width:100%;">Nombre módulo</th>
            </tr>
          </thead>
			    <tbody>
      <?php foreach ($permisos as $key => $value) { ?>
     <tr data-id="<?php echo $value["id"]; ?>">
        <td class="action delete" title="Eliminar permiso"><i class="fa fa-times"></i></td>
          <td data-field="valor"><?php echo $value["str_modulo"]; ?></td>
      </tr>
     <?php }
     if (sizeof($permisos) == 0)
     {?>
        <tr><td colspan="99" class="text-center">No hay permisos en el sistema para el usuario elegido</td></tr>
     <?php }

      ?>
     </tbody>
			  </table>
				</div>

            </div>
          </div>