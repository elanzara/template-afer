@echo off
echo ============================
echo. 
echo    PRENDER SERVIDOR K-POS   
echo. 
echo ============================
echo. 
WAKEMEONLAN /wakeup 8C89A5DB85F3
echo         WOL ENVIADO
echo. 
echo ============================  
echo. 
echo   SERVIDOR K-POS INICIANDO 
echo      AGUARDE 03 MINUTOS 
echo     NO APRETAR CONTROL+C
echo. 
echo ============================  
timeout /t 180 /nobreak
echo. 
echo ============================
echo. 
echo  INICIANDO DIAGNOSTICO PING   
echo. 
echo ============================
ping 172.20.78.50
echo ============================  
echo. 
echo  SI LOS PAQUETES ENVIADOS Y
echo  RECIBIDOS FUERON CORRECTOS
echo  YA PUEDE INICIAR SESION EN
echo       EL SISTEMA K-POS   
echo. 
echo ============================
pause