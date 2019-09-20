<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
<pre>
SINCRONIZANDO...
<?php
ob_flush();
require_once("config.php");
require_once("../libs/php/mysql-functions.php");
require_once("../libs/php/conn-data.php");
set_time_limit(60);

$mtime = microtime(true);
$insert = array("datetime"=>date("Y-m-d G:i:s"));
 
$backup_filename  = $backup_path."/".$db_name."_".date('Y-m-d_G-i-s').".sql.gz";

$first_step = 'mysqldump --single-transaction --extended-insert -u' . $db_user . ' -h' . $db_server . ' -p' . $db_pass . " ". $db_name . ' | gzip > ' . $backup_filename;

//echo $first_step;

chdir(__DIR__);

@shell_exec($first_step);


if (file_exists($backup_filename))
{
	if (filesize($backup_filename) > 0)
	{
//		$ret = ftp_file_put_contents($backup_filename);
		$insert["transfer"] = microtime(true)-$mtime; // tiempo que tarda en conectar a FTP

		//if ($ret["error"] == true)
		if (false)
		{
			$status = "BAD";
			$status_desc = "FTP ". $ret["error_message"];
		}
		else
		{
			$status = "OK";
			$status_desc = "OK";
			//DB::query("FLUSH LOGS");
		}
}
	else
	{
		$status = "BAD";
		$status_desc = "GZIP SQL Filesize of $backup_filename is 0 ";
	}
}
else
{
	$status = "BAD";
	$status_desc = "GZIP SQL Can't create $backup_filename";
}


$insert["status"]= $status;
$insert["status_desc"]= $status_desc;
echo $status_desc;

DB::insert("sys_sync_log", $insert);
$older_time = strtotime("today -3 days");

DB::delete("sys_sync_log","datetime < '".date("Y-m-d G:i:s",$older_time)."'");
$files = glob($backup_path."\\*");

foreach ($files as $file) if (is_file($file)) if (filemtime($file) < $older_time) unlink($file);
?>
</pre>
<script type="text/javascript">//location.href="/";</script>
</body>
</html>
