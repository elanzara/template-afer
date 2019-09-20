<div class="col-sm-3 col-md-2 sidebar" >
<div class="menu-list">
          <ul class="nav nav-sidebar"  id="MainMenu">
           <!-- <li class="active"><a href="#">Overview <span class="sr-only">(current)</span></a></li> -->
             <?php 
              $enabled_modules = DB::query("SELECT sys_modulos.valor, sys_modulos.filename, sys_modulos.icon, sys_seguridad.id,  sys_modulos.group_id FROM sys_seguridad INNER JOIN sys_modulos ON (sys_modulos.id = sys_seguridad.id_modulo) WHERE sys_seguridad.id_usuario = %d AND public = 0 AND sys_modulos.enabled = 1 ORDER BY sort ASC", $_SESSION["user_id"]);
             
              $enabled_groups = DB::query("SELECT DISTINCT(group_id) as groups_id FROM sys_seguridad INNER JOIN sys_modulos ON (sys_modulos.id = sys_seguridad.id_modulo) WHERE sys_seguridad.id_usuario = %d AND public = 0 AND sys_modulos.enabled = 1 ORDER BY sort ASC", $_SESSION["user_id"]);

              $enabled_groups = array_keys(DBHelper::reIndex($enabled_groups,"groups_id"));

              
              foreach ($enabled_modules as $key => $value)  { if ( $value["group_id"] == 0) { //primero todos los solos ?>
            <li><a href="/<?php echo $value["filename"]; ?>/"><?php if ($value["icon"] != "") {?><i class="<?php echo $value["icon"]; ?>"></i> <?php } echo $value["valor"]; ?></a></li>
          <?php } } ?>

          <?php 
          if (sizeof($enabled_groups) > 0)
          {


              $groups = DB::query("SELECT sys_modulos_groups.id, sys_modulos_groups.valor, sys_modulos_groups.icon FROM sys_modulos_groups WHERE sys_modulos_groups.public = 0 AND sys_modulos_groups.enabled = 1 AND (sys_modulos_groups.id IN %li0)  ORDER BY sort ASC", $enabled_groups);

              foreach ($groups as $key => $value)  { 
              	?>

              	<li data-toggle="collapse" data-target="#group<?php echo $value["id"]; ?>" class="collapsed">
                 <a href="#"><i class="<?php echo $value["icon"]; ?>"></i> <?php echo $value["valor"]; ?> <i class="fa fa-caret-down"></i></a>
                </li>
                         
				<ul class="sub-menu collapse" id="group<?php echo $value["id"]; ?>">
                              	 <?php foreach ($enabled_modules as $k => $v)  { if ( $v["group_id"] == $value["id"]) { ?>
            <li><a href="/<?php echo $v["filename"]; ?>/"><?php if ($v["icon"] != "") {?><i class="<?php echo $v["icon"]; ?>"></i> <?php } echo $v["valor"]; ?></a></li>
          <?php } } ?>
                </ul>
          <?php } }  ?>


          <!-- <li><a href="http://energyclub.com.ar/admin/" target="_blank"><i class="fa fa-external-link"></i> Energy Club</a></li> -->

          </ul>
        </div>
        </div>