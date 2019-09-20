<?PHP
date_default_timezone_set('America/Buenos_Aires');

DB::$host = 'localhost';
DB::$user = 'futbolitocom_futbolitocom';
DB::$password = 'w8KQuu=}OU(V';
DB::$dbName = 'futbolitocom_afer';
DB::$encoding = 'utf8';

DB::$error_handler = 'my_error_handler';
DB::$nonsql_error_handler = 'my_error_handler';
define("VAR_FIXED_DECIMALS",4);

define("EMPRESA_CUIT","33716175869");
define("EMPRESA_RAZON_SOCIAL","ROYAL POWER SIMPLE ASOCIACION");
define("EMPRESA_DOMICILIO", "Presidente Perón 2434, Laguna Paiva, Santa Fe");
define("EMPRESA_TELEFONO", "(0342) 4940162");
define("EMPRESA_EMAIL", "info@royalpower.com.ar");
define("EMPRESA_ID_SUCURSAL", 2);
define("EMPRESA_ID_PROVINCIA", 12);
define("EMPRESA_CP", "3020");
define("EMPRESA_PROVINCIA", 12);
define("EMPRESA_SUCURSAL_NOMBRE", "LAGUNAPAIVA");
//define("INSTALL_PATH", "/var/www/html/");
define("INSTALL_PATH", "/home/futbolitocom/public_html/");

const POS_CIERRE_DIFERENCIA_MAXIMA = array(1=>600, 2=>0, 3=>0, 4=>0, 5=>0, 6=>0, 8=>0);
const POS_HLP_MESES = array(1=>"Enero", 2=>"Febrero", 3=>"Marzo", 4=>"Abril", 5=>"Mayo", 6=>"Junio",7=>"Julio", 8=>"Agosto", 9=>"Septiembre", 10=>"Octubre", 11 =>"Noviembre", 12=>"Diciembre");
const POS_LIMITE_ALIVIO_SIN_CREDITO = 1000000;
const POS_MAX_CT_DIF = array(1=>24);

const POS_CT_MULTIPIN = [3=>true]; //id de usuario a los cuales se le pide multipin para cerrar turno, isset con cualquier valor
const POS_ADMIN_FILTRO_CC = false;
const POS_ADMIN_DEPOSITOS = [8,12];
const POS_CT_PLAYA_SOBREFACT = false;
const POS_CAJA_CHEQUES = 1;
const POS_RMT_SALDO_CC = true;
const POS_RMT_SUBTOT_RENGLON = true;
const POS_RMT_TOTAL = true;
const POS_VENTAS_FACTURA_A_SALDO = true;
const TKT_ATENDIDO_POR = true;
const FE_PRODUCCION = false;
const FORCE_USER_ONLY_REMITO = [];
const FE_LICENSE = ["codigo"=>"0000","CUIT-emisor"=>EMPRESA_CUIT,"hash"=>"f6e7d56d6ab289eaf54ee55275d7ac97"];

const WSP_USERNAME = "amimanera";
const WSP_TOKEN = "671b03d77386ae6906f5b6bbd95dc645";
const WSP_GRACIAS = "Muchas gracias por confiar en nosotros";
const WSP_CONSUMOS_DESTINOS = ["5491153295893"];

/*16-10*/
/*
 afecta a playa-script-operaciones, modal-nueva-salida-mayorista, modal-asignar-salida-mayorista, resumen-eess, cierre-de-turnos/playa.php
*/
const POS_DEPOSITOS_PLAYA = array(3,4,5,6,7,9);
const POS_DEPOSITOS_OTROS = array(2,10); //lub y otros
const POS_DEPOSITO_RTE = 8;
const POS_DEPOSITO_COSTOS_FINANCIEROS = 11;
const POS_DEPOSITOS_SHOP = array(1);
const POS_ARTICULOS_VTAMAY = array(5,6,3);
const POS_DEPOSITOS_REINT_VTAMAY = array(5,7,9);
 
function my_error_handler($params) {

 header("HTTP/1.0 503");echo("503|");
unset($_SESSION["busy"]);

  echo "Error: " . $params['error'] . "<br>\n";
  if (isset($params['query'])) echo "Query: " . $params['query'] . "<br>\n";
  file_put_contents(INSTALL_PATH."../php_mysql_error.log", "====".date("Ymd G:i:s")."====\n".$params["error"]."\n".json_encode(debug_backtrace())."\n", FILE_APPEND);
  die;
}
?>