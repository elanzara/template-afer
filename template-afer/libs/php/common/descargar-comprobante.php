<?php
require_once("../mysql-functions.php");
require_once("../conn-data.php");
require_once("../session/session-validator.php");

$path = INSTALL_PATH."../pdf/".$_GET["path"];

if (!is_file($path)) {
 header("Location: /estado/?error&txt=El archivo '".$path."' no existe");
}

header('Content-Type: application/pdf');
header('Content-Length: ' . filesize($path));

readfile($path);