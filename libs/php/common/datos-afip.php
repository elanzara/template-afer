<?php
require_once("../mysql-functions.php");
require_once("../conn-data.php");
require_once("../session/ajax-session-validator.php");


if (sizeof($_GET) != 1 || $_GET["cuit"] == "" || !(isset($_GET["cuit"]))) {header("HTTP/1.0 400");die("400|Parámetros incorrectos");};

  $datos = array();

  $datos["license"] = array("codigo"=>"0000","CUIT-emisor"=>"30715558838","hash"=>"7d97887f025632eda7504d1ab6096684");;
  $datos["datos"] = array("cuit"=>$_GET["cuit"], "pv-emisor"=>9999);
  $datos["produccion"] = true;

  $url = 'http://fe.minte.com.ar/wspadrona4.php'; 
  $enviar = json_encode($datos);

  $params = array('http' => array(
                'method' => 'POST',
                'content' => $enviar
                ));
 
  $params['http']['header'] = array(
    'Content-Type: application/json',                                                                                
    'Content-Length: ' . strlen($enviar));

   $ctx = stream_context_create($params);

  $fp = @fopen( $url, 'rb', false, $ctx);
   if(!$fp)
  {
   header("HTTP/1.0 503");die("503|Servicio no disponible (AFIP caído, saturado o Internet no disponible)");
  }

  $response = stream_get_contents($fp);

  $result = json_decode($response,true);

  if ($result === null)
  {
   header("HTTP/1.0 500");die("500|Error en la respuesta del servicio - verifique CUIT ingresado ($response)"); 
  }
  elseif (isset($result["resultado"])) {
                 header("HTTP/1.0 502");
                 die("502|Operación rechazada - ".$result["observaciones"]); 

  }
  else
  {
    //analizar respuesta
    //tengo que mandar: razon_social, direccion, id_provincia, telefono, email, direccion_cp

    $respuesta = [];
    if (!isset($result["datosGenerales"]))
    {
         header("HTTP/1.0 501");
         var_dump($result);
         die("501|CUIT ingresado no tiene datos de persona física o jurídica"); 
    }

      if (isset($result["datosGenerales"]["domicilioFiscal"]))
      {
        
        $respuesta["direccion_cp"] = trim($result["datosGenerales"]["domicilioFiscal"]["codPostal"]);
        $respuesta["direccion"] = trim($result["datosGenerales"]["domicilioFiscal"]["direccion"]);
        if (isset($result["datosGenerales"]["domicilioFiscal"]["localidad"])) $respuesta["direccion"] .= " - ".trim($result["datosGenerales"]["domicilioFiscal"]["localidad"]);
        $respuesta["id_provincia"] = $result["datosGenerales"]["domicilioFiscal"]["idProvincia"];
        if ($respuesta["id_provincia"] == 0) $respuesta["id_provincia"] = 25;
      }

   if (isset($result["datosGenerales"]["email"])) $respuesta["email"] = trim($result["datosGenerales"]["email"][0]["direccion"]);
  if (isset($result["datosGenerales"]["telefono"])) $respuesta["telefono"] = trim($result["datosGenerales"]["telefono"][0]["numero"]);

    if (isset($result["datosGenerales"]["razonSocial"])) {$respuesta["razon_social"] = trim($result["datosGenerales"]["razonSocial"]);}
    else
    {
      $respuesta["razon_social"] = trim(trim(@$result["datosGenerales"]["apellido"]). " ".trim(@$result["datosGenerales"]["nombre"]));
    }

    echo json_encode($respuesta);
  }
  
?>