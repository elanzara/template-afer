<?php 
/* incluye todos los modales */
foreach(glob(dirname(__FILE__).'/modal-*.php') as $file) {
require_once($file);
}
?>