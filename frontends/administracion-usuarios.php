<?php 

$usuarios = DB::query("SELECT sys_usuarios.username,sys_usuarios.enabled, sys_usuarios.id, sys_usuarios_datos.nombre,sys_usuarios_datos.pv_tktfiscal, sys_usuarios_datos.apellido, sys_usuarios_datos.email FROM sys_usuarios
  INNER JOIN sys_usuarios_datos ON (sys_usuarios_datos.id_usuario = sys_usuarios.id) WHERE 1");

?>
  <h1 class="page-header">Administración de usuarios
  </h1>

<div class="form panel panel-success hidden">
  <div class="panel-heading">
    <h3 class="form panel-title">Nuevo registro</h3>
  </div>
  <div class="panel-body">
  <form method="POST" id="abml">
      <input id="DDBB_table" name="DDBB_table" type="hidden" value="prp-usuarios" class="fixed">
      <input id="id" name="id" type="hidden" value="">
      <div class="form-group col-xs-12 col-sm-3"> <label for="apellido">Apellido</label><input id="apellido" name="apellido" class="form-control obligatorio" placeholder="Ingrese apellido" autocomplete="off" type="text"> </div>
      <div class="form-group col-xs-12 col-sm-3"> <label for="nombre">Nombre</label><input id="nombre" name="nombre" class="form-control obligatorio" placeholder="Ingrese nombre" autocomplete="off" type="text"> </div>
      <div class="form-group col-xs-12 col-sm-3"> <label for="email">Email</label><input id="email" name="email" class="form-control obligatorio" placeholder="Ingrese email" autocomplete="off" type="email"> </div>
      <div class="form-group col-xs-12 col-sm-3"> <label for="fecha_nacimiento">Nacimiento</label><input id="fecha_nacimiento" name="fecha_nacimiento" class="form-control obligatorio datepick" placeholder="Ingrese fecha de nacimiento" autocomplete="off" type="text"> </div>
      <div class="form-group col-xs-12 col-sm-2"> <label for="username">Usuario</label><input id="username" name="username" class="form-control obligatorio" placeholder="Ingrese usuario" autocomplete="off" type="text"> </div>
      <div class="form-group col-xs-12 col-sm-2"> <label for="password">Password</label><input id="password" name="password" class="form-control obligatorio" placeholder="Ingrese nuevo password" autocomplete="off" type="password"> </div>
      <div class="form-group col-xs-12 col-sm-2"> <label for="pv_remito">PV Remito NF</label><input id="pv_remito" name="pv_remito" class="form-control" placeholder="Ingrese PV Fiscal" autocomplete="off" type="text"> </div>
      <div class="form-group col-xs-12 col-sm-2"> <label for="pv_facelec">PV FC Elec</label><input id="pv_facelec" name="pv_facelec" class="form-control" placeholder="Ingrese PV Fiscal" autocomplete="off" type="text"> </div>
      <div class="form-group col-xs-12 col-sm-2"> <label for="pv_tktfiscal">PV TKT Fisc</label><input id="pv_tktfiscal" name="pv_tktfiscal" class="form-control" placeholder="Ingrese PV Fiscal" autocomplete="off" type="text"> </div>
      <div class="form-group col-xs-12 col-sm-2"> <label for="enabled">Habilitado?</label><?php echo SelectABMCampoEnum("abm_yesno",null,"enabled", "obligatorio form-control"); ?></div>
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
        <th filter="false" style="width:11px;"><i class="fa fa-pencil-square-o"></i></th>
        <th style="width:150px;">Usuario</th>
        <th style="width:25%;">Apellido</th>
        <th style="width:25%;">Nombre</th>
        <th style="width:25%;">Email</th>
        <th style="width:25px;">Habilitado</th>
        <th style="width:25px;">Permisos</th>
        <th style="width:25px;">TKT P</th>
            </tr>
          </thead>
			    <tbody>
      <?php foreach ($usuarios as $key => $value) { ?>
     <tr data-id="<?php echo $value["id"]; ?>">
        <td class="action edit" title="Editar"><i class="fa fa-pencil-square-o"></i></td>
          <td data-field="username"><?php echo $value["username"]; ?></td>
          <td data-field="apellido"><?php echo $value["apellido"]; ?></td>
          <td data-field="nombre"><?php echo $value["nombre"]; ?></td>
          <td data-field="email"><?php echo $value["email"]; ?></td>
          <?php $fa = "text-danger fa-exclamation-triangle"; if ( $value["enabled"] == 1) $fa = "fa-check-square-o text-success"; ?>
          <td data-field="enabled" class="text-center"><i class="fa <?php echo $fa; ?>"></i></td>
          <td class="text-center"><a href="editar-permisos/<?php echo $value['id']; ?>/"><i class="fa fa-share "></i></a></td>
          <td class="text-center"><?php if ($value["pv_tktfiscal"] != 0) { ?><a href="/ticket-fiscal/dummy-ticket.php?pv=<?php echo $value['pv_tktfiscal']; ?>" target="_newTab"><i class="fa fa-file-text "></i></a> <?php } ?></td>
      </tr>
     <?php }
     if (sizeof($usuarios) == 0)
     {?>
        <tr><td colspan="99" class="text-center">No hay usuarios en el sistema</td></tr>
     <?php }

      ?>
     </tbody>
			  </table>
				</div>

            </div>
          </div>
          <script type="text/javascript">
$(document).ready(function(){

               /* agregar registro */  $("#synch").click(function(){Modal("SINCRONIZANDO")});
             });

          </script>