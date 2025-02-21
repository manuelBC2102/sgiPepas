<?php
session_start();

include 'parametros.php';

if (!isset($_SESSION['ldap_user'])){
    header("location:".$urlLogin);
}

include 'datos_abrir_bd.php';
include 'func.php';
//$stmt = $mysqli->prepare("SELECT usu_id, usu_nombre FROM recursos.usuarios WHERE usu_ad=?");
$stmt = $mysqli->prepare('select u.id as usu_id,u.persona_id as usu_persona_id,
                                    case p.persona_tipo_id 
                                        when 2 then concat (ifnull(p.apellido_paterno,"")," ", ifnull(p.apellido_materno,"") ,", ",ifnull(p.nombre,"") )
                                        when 4 then concat (ifnull(p.nombre,"")," ", ifnull(p.apellido_paterno,"") ," ",ifnull(p.apellido_materno,"") )
                                    end as usu_nombre 
                        from '.$baseSGI.'.usuario u
                        inner join '.$baseSGI.'.persona p on u.persona_id=p.id
                        where u.usuario=?
                        and u.estado=1
                        and p.estado=1');
$stmt->bind_param("s", $_SESSION['ldap_user']);
$stmt->execute() or die ($mysqli->error);
//$result = $stmt->get_result();
//$fila=$result->fetch_array();
$stmt->store_result();
$fila = fetchAssocStatement($stmt);

$_SESSION['rec_usu_id'] = $fila['usu_id'];
//$_SESSION['rec_usu_id'] = 44;//75
$_SESSION['rec_usu_nombre'] = $fila['usu_nombre'];
$_SESSION['rec_usu_persona_id'] = $fila['usu_persona_id'];

//$stmt = $mysqli->prepare("SELECT usu_estado, gco_cobj FROM usu_detalle WHERE usu_id=?");
//$stmt->bind_param("i", $_SESSION['rec_usu_id']);
//$stmt->execute() or die ($mysqli->error);
//$stmt->store_result();
//$fila=fetchAssocStatement($stmt);
//
//$estado = $fila['usu_estado'];
//$adm_usu_gco_cobj = $fila['gco_cobj'];

include 'datos_cerrar_bd.php';

//if ($estado==0) die ('ERROR: Su cuenta de usuario no está activa, contactese con Administración o al Departamento de Tecnologías de Información.');
//if (strlen($adm_usu_gco_cobj) == 0) die ('ERROR: Su cuenta de usuario no tiene definido Collection Object, contactese con Administracion o al Departamento de Tecnologias de Informacion.');

//$_SESSION['adm_usu_gco_cobj'] = $adm_usu_gco_cobj;
//
//$msg_error_mssql = "ERROR: No se puede continuar. Su usuario no est&aacute; configurado correctamente en el m&oacute;dulo de Recursos Humanos. Envie correo electr&oacute;nico solicitando asistencia a TI.<br><br>";
//include 'datos_abrir_mssql.php';
//
//$query = "select IDCODIGOGENERAL from PERSONAL_VARIABLES where IDVARIABLE = 'USR' AND VALOR = ?";
//$stmt = odbc_prepare($connection, $query);
//odbc_execute($stmt, array($_SESSION['ldap_user']))or die(exit("Error en odbc_execute [1]"));
//
//if (odbc_num_rows($stmt) == 0) {
//	echo $msg_error_mssql;
//	echo "[Not found in PERSONAL VARIABLES]";
//	exit;
//}
//
//odbc_fetch_row($stmt);
//$_SESSION['rec_codigogeneral_id'] = odbc_result($stmt, 'IDCODIGOGENERAL');
//$_SESSION['rec_codigogeneral_id'] = $_SESSION['rec_usu_id'];

//include 'datos_cerrar_mssql.php';
header("location:menu.php");
?>
