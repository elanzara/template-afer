<pre>
<?php
/* checkear que no sea sabado o domingo */
date_default_timezone_set('America/Buenos_Aires');

$start = microtime(true);
require_once("../mysql-functions.php");
require_once("../conn-data.php");


$timestamp = date("Y-m-d G:i:s");

/*

IdMovimiento (autoincrement)
NumeroDoc: varchar(13) - Documento del cliente. Sin puntos ni espacios.
IdSucursal: pk de la sucursal
IdUsuario: pk del usuario
FechaCarga: Fecha de compra DATETIME (YYYY-MM-DD HH:ii:SS)
DescripcionMovimiento: 1 = Compra - INT
Ticket: varhcar(15) - Numero de comprobante
ImporteCompra: Decimal (7,2)
Detalle: varchar(250)
Importado
IngresoMovimiento

usuarios_equivalencias
sucursales_equivalencias

*/

$datos_venta = DB::query("SELECT 
	pos_clientes.documento_numero AS NumeroDoc,
	%d0 AS IdSucursal,
	pos_ventas.id_usuario AS IdUsuario,
	pos_ventas.datetime AS FechaCarga,
	IF(pos_ventas.es_devolucion =1,0,1) as DescripcionMovimiento,
	%s1 as IngresoMovimiento,
	CONCAT(LPAD(pos_ventas_comprobante.comprobante_pv,4,'0'),'-',LPAD(pos_ventas_comprobante.comprobante_numero,8,'0')) as Ticket,
	ROUND(ABS((SELECT SUM(pos_ventas_items.neto_gravado * pos_ventas_items.cantidad) FROM pos_ventas_items WHERE pos_ventas_items.id_venta = pos_ventas.id GROUP BY pos_ventas_items.id_venta) +
	(SELECT SUM(pos_ventas_items.no_gravado * pos_ventas_items.cantidad) FROM pos_ventas_items WHERE pos_ventas_items.id_venta = pos_ventas.id GROUP BY pos_ventas_items.id_venta) +
	(SELECT SUM(pos_ventas_items.neto_exento * pos_ventas_items.cantidad) FROM pos_ventas_items WHERE pos_ventas_items.id_venta = pos_ventas.id GROUP BY pos_ventas_items.id_venta) +
	(SELECT SUM(pos_ventas_items.iva * pos_ventas_items.cantidad) FROM pos_ventas_items WHERE pos_ventas_items.id_venta = pos_ventas.id GROUP BY pos_ventas_items.id_venta)  +
	(SELECT SUM(pos_ventas_items.itc * pos_ventas_items.cantidad) FROM pos_ventas_items WHERE pos_ventas_items.id_venta = pos_ventas.id GROUP BY pos_ventas_items.id_venta) +
	(SELECT SUM(pos_ventas_items.igo * pos_ventas_items.cantidad) FROM pos_ventas_items WHERE pos_ventas_items.id_venta = pos_ventas.id GROUP BY pos_ventas_items.id_venta) +
	(SELECT SUM(pos_ventas_items.th * pos_ventas_items.cantidad) FROM pos_ventas_items WHERE pos_ventas_items.id_venta = pos_ventas.id GROUP BY pos_ventas_items.id_venta)),2) as ImporteCompra,
	LPAD(pos_ventas.id,5,'0') as Detalle
	FROM pos_ventas
	INNER JOIN pos_ventas_comprobante ON (pos_ventas_comprobante.id_venta = pos_ventas.id)
	INNER JOIN pos_clientes ON (pos_clientes.id = pos_ventas.id_cliente)
	WHERE pos_clientes.documento_numero != 99999999999 AND enviado_club = 0 AND pos_ventas_comprobante.id_comprobante_tipo IN (81, 82, 83, 110, 112, 113) ORDER BY pos_ventas.id ASC LIMIT 0, 100
	
	",EMPRESA_ID_SUCURSAL,$timestamp);



if (sizeof($datos_venta) == 0)
{
	echo "Nada que exportar a CLUB";
	die();
}

$mdb = new MeekroDB();
$mdb->host = '173.237.185.218';
$mdb->user = 'cphermes_energy';
$mdb->password = 'm)m*^n^{$0?q';
$mdb->dbName = 'cphermes_royalenergy';
$mdb->encoding = 'utf8';

$mdb->insert("compras", $datos_venta);

if (sizeof($datos_venta) == $mdb->affectedRows())
{
	$ids = array_keys(DBHelper::reIndex($datos_venta,"Detalle"));
	DB::update("pos_ventas",array("enviado_club"=>1), "id IN %li0", $ids);
	$name = "logs/insert-club-".date("Y-m-d").".log";
	file_put_contents($name, microtime(true)-$start." - (".sizeof($datos_venta)."): ".implode(",", $ids)."\r\n", FILE_APPEND);
}


echo "(".sizeof($datos_venta).") Last update: ".$timestamp;
?>
</pre>