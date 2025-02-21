<?php
//----------------- SGI -----------------------------------
//PARAMETROS DE SGI
$baseSGI="imaginatec_abc";
$carpetaSGI='';
$urlLogin="http://localhost/login.php";

//PERFILES DE SGI USADOS EN EAR
$pTI="Administrador TI";
$pADMINIST="Administrador";
$pSUP_CONT="Contabilidad";
$pTESO="Tesoreria";
$pCOMP="Tesoreria";
$pASISTENTE_ADMINISTRATIVO="Asistente administrativo";// PERFIL DE CECILIA CALONGE PEREZ
$pGERENTE='Gerente General'; // PERFIL DE ROLANDO RETO GONZALEZ, PARA PERFIL DE USUARIOS Y GERENTE
$correoEnvioUsuario='mreto@abcservicios.pe';

//ID DE USUARIO DE SGI
$pAXISADUANA = 111;
$pAXISGLOBAL = 112;

//PERFILES
$pPERFIL_PROVEEDOR_DUA=125;

//DOCUMENTO TIPO QUE SE SINCRONIZAN CON SGI
$documentoTipoIdDesembolsoSGI=241;//EAR Desembolso: tipo 6, de pago (pago).
$documentoTipoIdPlanillaSGI=249;//Planilla de Movilidad: tipo 4, provicion de pago
$documentoTipoIdDevolucionSGI=250;//EAR Devolución: tipo 1, provicion de cobranza
$documentoTipoIdTicketSGI=135;//Ticket transferencia - depósito: tipo 2,de pago (cobranza).
$documentoTipoIdReembolsoSGI=251;//EAR Reembolso: tipo 6, de pago (pago).
$documentoTipoIdDevolucionSisSGI=266;//EAR Devolución sistema: tipo 4, provicion de pago. En caso de devolucion EAR.

//OTROS PARAMETROS
$longMotivo=40;
$actividadIdSGI=10;//para documento de pago en devolucion: $documentoTipoIdTicketSGI

//------------------------------- EAR -----------------------------------------
//DATOS DE CONEXION PARA BASE DE DATOS
//NUBE
//$dbhost = 'localhost';
//$dbuser = 'adminsys';
//$dbpass = '1m4g1n4T3c';
//$dbname = 'imaginatec_bhdt_ear';

// private $host = 'localhost';
   // private $dbname = 'xbkohbsw_dvm';
   // private $username = 'xbkohbsw_admin';
   // private $password = '1m4g1n4T3c';
//LOCAL
 $dbhost = 'localhost';
 $dbuser = 'root';
 $dbpass = '';
 $dbname = 'imaginatec_abc_ear';

//Abre la conexion
$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname) or die ('Error de coneccion a mysql');
$mysqli->set_charset("utf8mb4");
?>
