<?php
$db_server = 'localhost';
$db_user = 'backups';
$db_pass = 'FFfr2x8X8nPhLpus';
$db_name = 'kpos_generalroca';


$backup_path = "/home/futbolitocom/public_html/@sync/files";

date_default_timezone_set('America/Buenos_Aires');

function ftp_file_put_contents($file_path)
{
    // FTP login
    $ftp_port = 21;
    
    $ftp_server="ftp.minte.com.ar"; 
    $ftp_user_name="royalenergy.sistemaexterno.com.ar"; 
    $ftp_user_pass="kalia16";
    
    $ftp_server="kalia.royal-energy.com.ar"; 
    $ftp_user_name="generalroca"; 
    $ftp_user_pass="lic123$";

    @$file_handler = fopen($file_path, 'r');
	if(!$file_handler)
    {
        return ["error"=>true, "error_message"=>"Cant open file $file_path (".implode("--",error_get_last()).")"];
    }
    @fclose($file_handler);

    // Create FTP connection
    @$ftp_conn=ftp_connect($ftp_server,$ftp_port,120); 

    if(!$ftp_conn)
    {
        return ["error"=>true, "error_message"=>"Cant connect to $ftp_server:$ftp_port (".implode("--",error_get_last()).")"];
    }

    // FTP login
    @$login_result=ftp_login($ftp_conn, $ftp_user_name, $ftp_user_pass);
    if(!$login_result)
    {
        return ["error"=>true, "error_message"=>"Cant login on $ftp_server:$ftp_port with user '$ftp_user_name' - '$ftp_user_pass' (".implode("--",error_get_last()).")"];
    }
 

    // FTP Passive mode
 /*   $ftp_pasv = ftp_pasv($ftp_conn, true);
    if(!$ftp_pasv)
    {
        return ["error"=>true, "error_message"=>"Cant switch to passive mode on $ftp_server:$ftp_port (".implode("--",error_get_last()).")"];
    }*/


    $remote_file=explode("/", $file_path);
    $remote_file=$remote_file[sizeof($remote_file)-1];

	@$remote_dir = ftp_chdir($ftp_conn, "/");
	if(!$remote_dir)
 	{
      	return ["error"=>true, "error_message"=>"Cant change dir to '/' @ $ftp_server:$ftp_port (".implode("--",error_get_last()).")"];
    }

	//$upload_result=ftp_fput($ftp_conn, "/".$remote_file, $file_handler, FTP_BINARY);
	$upload_result=ftp_put ($ftp_conn, "/".$remote_file, $file_path, FTP_BINARY);
    if(!$upload_result)
 	{
      	return ["error"=>true, "error_message"=>"Cant upload file '$remote_file' to '/' @ $ftp_server:$ftp_port (".implode("--",error_get_last()).")"];
    }

    @ftp_close($ftp_conn);
    
}
?>