<nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Cambiar navegación</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="/">E-POS <span class="hidden-sm hidden-xs">- <?php echo strtoupper($_SESSION["user_name"]) ?> - <?php echo date("d-m-Y G:i:s"); ?></span></a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">

          <ul class="nav navbar-nav navbar-right">
          <li class="hidden-xs"><a id="widen" href="#"><i class="fa fa-chevron-circle-left"></i></a></li>
<!--           <li><a href="/manuales/manual.pdf" target="_blank"><i class="fa fa-question-circle"></i> Manual</a></li>
          <li><a data-toggle="modal" href="#" data-target="#modal_alivio_de_dinero" ><i class="fa fa-usd"></i> Alivio de dinero</a> -->
          <?php 
            $modulos_publicos = DB::query("SELECT * FROM sys_modulos WHERE public = 1 AND enabled = 1 ORDER BY sort ASC");
            foreach ($modulos_publicos as $key => $value) { ?>
            <li><a href="/<?php echo $value["filename"]; ?>/"><?php if ($value["icon"] != "") {?> <i class="<?php echo $value["icon"]; ?>"></i>
              <?php } echo $value["valor"]; ?></a></li>
          <?php }
          
              foreach ($enabled_modules as $key => $value)  { ?>
            <li class="hidden-sm hidden-md hidden-lg"><a href="/<?php echo $value["filename"]; ?>/"><?php if ($value["icon"] != "") {?><i class="<?php echo $value["icon"]; ?>"></i> <?php } echo $value["valor"]; ?></a></li>
          <?php } ?>
           </li>
          </ul>
        </div>
      </div>
    </nav>