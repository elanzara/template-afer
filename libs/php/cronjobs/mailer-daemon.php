<?php
 require_once("../mysql-functions.php");
require_once("../conn-data.php");
require_once("mailing-libs/class.phpmailer.php");
require_once("mail-alerta-empresas.php");

 ?>    
MAILER DAEMON: 
<?php
$start = microtime(true);



set_time_limit(250);

$result = DB::query("SELECT * FROM mail_queue WHERE status = 0 LIMIT 10");


if (sizeof($result) == 0 ) echo "Todo fue enviado ";

foreach ($result as $row)
{
$subject = utf8_decode($row["subject"]);
$to = json_decode($row['to']);
$to_bcc = json_decode($row['to_bcc']);
if ($to_bcc == null) $to_bcc = array();

$from = $row['from'];
$message = htmlspecialchars_decode($row["text"]);

$nameto = "";

authSendEmail($to, $from, "", $subject, $message, $row["id"], $to_bcc);
}

$log["timestamp"] = date("Y-m-d G:i:s");

echo "Last update: ".$log["timestamp"];



function authSendEmail($to,$from, $nameto, $subject, $message, $id, $bcc)
{
$mail = new PHPMailer();

$mail->IsSMTP(); 
$mail->SMTPAuth   = true;   
$mail->Host       = "mail.fenzisouthamerica.com";     
$mail->Port       = 25;               /*2525*/  
$mail->Username   = "sip@fenzisouthamerica.com"; 
$mail->Password   = "1234fenzi";         
$mail->SetFrom($from, "Fenzi South America");

$mail->Subject = $subject;
$mail->MsgHTML($message);

foreach ($to as $value) {
	$mail->AddAddress($value, $nameto);
}

foreach ($bcc as $value) {
	$mail->AddCC($value, $nameto);
}

if(!$mail->Send()) {
  LogToFile("0;".json_encode($to).";".json_encode($bcc).";" . $mail->ErrorInfo); 
  $status = 2;
} else {
  LogToFile("1;".json_encode($to).";".json_encode($bcc).";Success!");
  $status = 1;
}

DB::update('mail_queue', array('status' => $status,"timestamp"=>date("Y-m-d G:i",time())), "id=%d", $id);

}

function LogToFile($text)
{
$filename = "mail-log-".date("d-m-Y").".csv";
$abre = fopen("mailing-logs/".$filename, "a");
//echo $text."\n";
$grabar = fwrite($abre, $text."\n");
fclose($abre);
}


?>