<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';
include 'reportesPDF.php';
include 'parametros.php';
//include dirname(dirname(__FILE__))."/Mailer/Entidades/ConstructorMail.php";

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

$tot_mon = abs((float) filter_var($f_tot_mon_inp, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
$id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));
$exc = abs((int) filter_var($f_exc, FILTER_SANITIZE_NUMBER_INT));
$gti = abs((int) filter_var($f_lid_gti_def, FILTER_SANITIZE_NUMBER_INT));
$dg_json = $f_lid_dg_json_def;

if (isset($f_act2)) {
	// Redirect para planillas EAR
	header("Location: movi_anular_p.php?id=$id");
	exit;
}
if (isset($f_act6)) {
	// Redirect para planillas CCH
	header("Location: movi_anular_p.php?id=$id&cch=1");
	exit;
}
else if (!isset($f_act1) && !isset($f_act3) && !isset($f_act5)) {
	echo "<font color='red'><b>ERROR: Operaci&oacute;n err&oacute;nea</b></font><br>";
	exit;
}

list($pla_numero, $est_id, $pla_reg_fec, $ear_numero, $tope_maximo, $usu_id, $ear_id,
	$est_nom, $pla_monto, $pla_gti, $pla_dg_json, $pla_env_fec,
	$pla_exc, $pla_com1, $pla_com2, $pla_com3,
	$pla_tipo, $ccl_id, $cch_id) = getPlanillaMovilidadInfo($id);

if ($est_id==2) {
	echo "<font color='red'><b>ERROR: No se puede modificar planilla, estado incorrecto</b></font><br>";
	exit;
}
$count = getPermisosAdministrativos($_SESSION['rec_usu_id'], 'JEFEOGERENTE');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pTI);
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pADMINIST);
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pGERENTE);
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pASISTENTE_ADMINISTRATIVO);

//PERMISO DE CONTABILIDAD
$count += getPermisosAdministrativos($_SESSION['rec_usu_persona_id'], $pSUP_CONT);

if ($usu_id!=$_SESSION['rec_usu_persona_id'] && $count == 0) {
	echo "<font color='red'><b>ERROR: No puede modificar planilla de otros usuarios, se ha notificado esta operacion al administrador</b></font><br>";
	exit;
}

include 'datos_abrir_bd.php';

$mysqli->autocommit(FALSE);

$query = "SELECT now(), year(now())";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$ahora = $fila[0];
$anio = $fila[1];

$stmt = $mysqli->prepare("UPDATE pla_mov SET pla_monto=?, pla_exc=?, pla_com1=?, pla_gti=?, pla_dg_json=? WHERE pla_id=?") or die ($mysqli->error);
$stmt->bind_param('disisi',
	$tot_mon,
	$exc,
	$f_comentario1,
	$gti,
	$dg_json,
	$id);
$stmt->execute() or die ($mysqli->error);

if (isset($f_comentario2)) {
	$stmt = $mysqli->prepare("UPDATE pla_mov SET pla_com2=? WHERE pla_id=?") or die ($mysqli->error);
	$stmt->bind_param('si',
		$f_comentario2,
		$id);
	$stmt->execute() or die ($mysqli->error);
}

if (isset($f_comentario3)) {
	$stmt = $mysqli->prepare("UPDATE pla_mov SET pla_com3=? WHERE pla_id=?") or die ($mysqli->error);
	$stmt->bind_param('si',
		$f_comentario3,
		$id);
	$stmt->execute() or die ($mysqli->error);
}

if ($est_id==3) {
	$stmt = $mysqli->prepare("UPDATE pla_mov SET est_id=4 WHERE pla_id=?") or die ($mysqli->error);
	$stmt->bind_param('i',
		$id);
	$stmt->execute() or die ($mysqli->error);
}

if ($est_id==4) {
	$stmt = $mysqli->prepare("UPDATE pla_mov SET est_id=5 WHERE pla_id=?") or die ($mysqli->error);
	$stmt->bind_param('i',
		$id);
	$stmt->execute() or die ($mysqli->error);
}

$stmt = $mysqli->prepare("DELETE dc FROM cont_distribucion_contable dc inner join pla_mov_detalle pm on pm.pla_det_id = dc.pla_det_id where pm.pla_id =?") or die($mysqli->error);
$stmt->bind_param('i', $id);
$stmt->execute() or die($mysqli->error);

$stmt = $mysqli->prepare("DELETE FROM pla_mov_detalle WHERE pla_id=?") or die ($mysqli->error);
$stmt->bind_param('i',
	$id);
$stmt->execute() or die ($mysqli->error);

if (isset($f_motivo_inp)) {
	foreach ($f_motivo_inp as $k => $v) {
		$motivo_inp = $v;
		$fecdoc_inp = $f_fecdoc_inp[$k];
		if (strlen($fecdoc_inp) == 10) { $fecdoc_inp = substr($fecdoc_inp, 6, 4)."-".substr($fecdoc_inp, 3, 2)."-".substr($fecdoc_inp, 0, 2); } // Cambia formato fecha a ISO
		$salida_inp = $f_salida_inp[$k];
		$destino_inp = $f_destino_inp[$k];
		$monto_inp = $f_monto_inp[$k];

		$stmt = $mysqli->prepare("INSERT INTO pla_mov_detalle (pla_id, pmd_motivo, pmd_fec, pmd_salida, pmd_destino, pmd_monto, pmd_aprob, pmd_emp_asume)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?)") or die ($mysqli->error);
		if ($est_id==1) {
			$pmd_aprob = 1;
			$stmt->bind_param('issssdid',
				$id,
				$motivo_inp,
				$fecdoc_inp,
				$salida_inp,
				$destino_inp,
				$monto_inp,
				$pmd_aprob,
				$monto_inp);
		}
		else if ($est_id>=3 || $est_id<=5 || isset($f_act5)) {
			$aprob = $f_aprob_sel[$k];
			$emp_asume = $f_gast_asum[$k];

			$stmt->bind_param('issssdid',
				$id,
				$motivo_inp,
				$fecdoc_inp,
				$salida_inp,
				$destino_inp,
				$monto_inp,
				$aprob,
				$emp_asume);
		}
		$stmt->execute() or die ($mysqli->error);
                $insert_id_mov_det =  $mysqli->insert_id;

                if (isset($f_lid_distribucion[$k])) {
                 foreach (json_decode($f_lid_distribucion[$k]) as $index => $item) {
                            $stmt = $mysqli->prepare("INSERT INTO cont_distribucion_contable(pla_det_id,plan_contable_id,centro_costo_id,porcentaje,monto,usuario_creacion) VALUES (?,?, ?, ?, ?, ?)") or die($mysqli->error);
                            $stmt->bind_param('iiiddi',$insert_id_mov_det,$item->cuenta_contable,$item->centro_costo,$item->porcentaje,$item->monto, $_SESSION['rec_usu_id']);
                            $stmt->execute() or die($mysqli->error);
                    }
                }
	}
}

if (!is_null($ear_id)) {
	$stmt = $mysqli->prepare("INSERT INTO ear_actualizaciones VALUES (?, 31, ?, ?, null)") or die ($mysqli->error);
	$stmt->bind_param('iis', $ear_id, $_SESSION['rec_usu_id'], $ahora);
	$stmt->execute() or die ($mysqli->error);
}
else if (isset($f_act5)) {
	$stmt = $mysqli->prepare("UPDATE pla_mov SET pla_env_fec=?, est_id=4 WHERE pla_id=?") or die ($mysqli->error);
	$stmt->bind_param('si', $ahora, $id);
	$stmt->execute() or die ($mysqli->error);
}

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
if ($est_id==1 && !isset($f_act5)) {
	$desc = "Actualizacion Borrador Planilla Movilidad $pla_tipo (".$id.") hecho por ".$_SESSION['rec_usu_nombre'];
}
else if ($est_id>=3 || $est_id<=5 || isset($f_act5)) {
	$desc = "Revision Planilla Movilidad $pla_tipo (".$id.") hecho por ".$_SESSION['rec_usu_nombre'];
}
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);
$stmt->close();

$mysqli->commit();

if (isset($f_act5) && false) {//para caja chica. Luego quitar el false
	// Adjuntar pdf si es que se aprueba
	$attachString = getPlanillaMov($id, 'S');
	if (is_null($attachString)) die("Error en la generaci�n del archivo PDF, no se complet� la transacci�n. (Cadena vac�a)");
	$attachFilename = __DIR__ ."/PLA_".str_replace("/", "_", $pla_numero).".pdf";

	$nom_tra = getNombreTrabajador(getCodigoGeneral(getUsuAd($usu_id)));

	list($cch_id, $cch_nombre, $suc_nombre, $mon_nom, $mon_iso, $mon_img, $cch_monto,
		$cch_abrv, $cch_gti, $cch_dg_json, $cch_cta_bco, $cch_act,
		$suc_id, $mon_id) = getCajasChicasInfo($cch_id);

	$to = getCorreoUsuario($usu_id); // Jefe o usuario que aprueba esta planilla
	$cc = array();
	array_push ($cc, getCorreoUsuario($_SESSION['rec_usu_id'])); // El usuario al que pertenece esta planilla
	array_push ($cc, getCorreoUsuario(getUsuAdmin()));
	array_push ($cc, 'mngmt@Minapp.com.pe');
	$subject = "Aprobacion de Planilla de Movilidad CAJA CHICA de $cch_nombre de $nom_tra";
	$body  = "Se ha aprobado la Planilla de Movilidad de CAJA CHICA $pla_numero de $cch_nombre perteneciente a $nom_tra, realizado por ".getNombreTrabajador(getCodigoGeneral(getUsuAd($_SESSION['rec_usu_id']))).".";
	$espe  = $body;
	$body .= "\n\nNota al colaborador: Imprime, firme y entregue el PDF anexado a este correo.";
	$body .= "\n\nEsperando la aprobaci�n del Encargado para que esta planilla sea ingresada a Caja Chica.";
	//R. enviarCorreo($to, $cc, $subject, $body, $attachString, $attachFilename);
	ConstructorMail::enviarCorreoInfo($to,$cc,$subject,$body,$var_modulo_ident,"info",$attachString,$attachFilename);

	$arr = getEncargadosCaja($cch_id);
	foreach ($arr as $v) {
		$ventana   = "admin/cch_lote_plm_add.php";
		$aprobador = getUsuAd($v);
		$detalle   = ConstructorMail::armarDetalle(null, $subject, $espe, $attachString, $attachFilename);
		ConstructorMail::enviarCorreoInfoConLink($id, $aprobador, $ventana, $var_modulo_ident, $detalle);
	}
}

include 'datos_cerrar_bd.php';

if ($est_id==1) {
	if (isset($f_plm_cch_aprob)) {
		header("Location: movi_aprobacion_cch.php");
	}
	else if ($usu_id == $_SESSION['rec_usu_id']) {
		header("Location: movi_consulta.php?cons_id=1");
	}
	else {
		header("Location: movi_consulta_otro.php?cons_id=1&usuId=".$usu_id);
	}
}
else if ($est_id>=3 || $est_id<=5) {
	header("Location: movi_revisado.php");
}
exit;
?>
