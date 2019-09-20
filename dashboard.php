<?php 
session_start();
  require_once("common/libraries.php");
  require_once("common/header.php"); 
  $enabled_modules = DB::query("SELECT sys_modulos.valor, sys_modulos.filename, sys_modulos.icon, sys_seguridad.id FROM sys_seguridad INNER JOIN sys_modulos ON (sys_modulos.id = sys_seguridad.id_modulo) WHERE sys_seguridad.id_usuario = %d AND sys_modulos.enabled = 1 ORDER BY sort ASC", $_SESSION["user_id"]);
  $modulos_publicos = DB::query("SELECT * FROM sys_modulos WHERE public = 1 AND enabled = 1 ORDER BY sort ASC");
  if (!isset($_GET["modulo"])) $_GET["modulo"]="estado";

    require_once("libs/php/session/privileges-validator.php"); 
  ?>
  <body role="document">
  <?php require_once("common/navbar-header.php"); ?>
  <?php require_once("common/javascript.php"); ?>
  <?php require_once("frontends/addons/dashboard/modal.php"); ?>
  <?php if (file_exists("frontends/addons/".$_GET["modulo"]."/modal.php")) require_once("frontends/addons/".$_GET["modulo"]."/modal.php"); ?>

<div class="container-fluid">
      <div class="row">
        <?php require_once("common/navbar-sidebar.php"); ?>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main content-slide">
        <?php //require_once("common/fiscal-printer-errors.php"); ?> 
    <?PHP if(isset($_GET['error'])){ $txt= "Error al procesar la última acción";if(isset($_GET["txt"])) $txt=$_GET["txt"];?> 
      <div class="row text-center bg-danger"><p><?php echo $txt ?></p></div><div class="divide10"></div>
      <?php } if(isset($_GET['success'])) {?> 
      <div class="row text-center bg-success"><p>Operación exitosa</p></div><div class="divide10"></div>
      <?php } if(isset($_GET['badParameters'])) {?> 
      <div class="row text-center bg-danger"><p>Los parámetros no son correctos</p></div><div class="divide10"></div>
      <?php } if(isset($_GET['warning'])) { if(isset($_GET["txt"])) $txt=$_GET["txt"];?> 
      <div class="row text-center bg-warning"><p><?php echo $txt ?></p></div><div class="divide10"></div>
      <?php } ?> 
        <?php require_once("frontends/".$_GET["modulo"].".php"); ?>
        </div>
      </div>
      <script type="text/javascript">active_module = '<?php echo $_GET["modulo"]; ?>';</script>
     
     <script type="text/javascript">

    $(document).ready(function() {
        document.title += " | " + $(".content-slide .page-header:eq(0)").text();
        active = window.location.pathname;
        active = active.split("/");
        final = "";
        for (var i = 0; i < active.length; i++) {
          if (active[i] !="") final +=  active[i]+"/";
        }
        $("a[href='/"+final+"']").addClass("active")
        target = $("a[href='/"+final+"']").parents("ul").attr("id");
        $('li[data-target="#'+target+'"] a').click();
        <?php //require_once("common/validar-cierre-turno.php"); ?>
	});
</script>
  </body>
</html>