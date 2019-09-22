Pasos a seguir para la instalacin del templater afer:

1) Descargar fuentes y script de la BBDD (https://github.com/elanzara/template-afer
2) Dentro del hosting seleccionado crea la BBDD e impactar el script que se encuentra en /BBDD/template_afer.sql
3) Subir mediante ftp los fuentes al hosting dentro de la raiz (public_html)
4) Dentro del archivo de conexion /libs/php/conn-data.php configurar los datos de conexion a la bbdd:

DB::$host = 'localhost';
DB::$user = 'c1640611_afer';
DB::$password = 'baWU19gudo';
DB::$dbName = 'c1640611_afer';
DB::$encoding = 'utf8';

5) Ingresar a la aplicacion mediante la url asignada

Usuario: sistemas
Clave: sistemas17$ç