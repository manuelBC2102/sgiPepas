<?php
include ("seguridad.php");
include 'func.php';

$count = getPermisosAdministrativos($_SESSION['rec_usu_id'], 'ADMINIST');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'TI');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'SUP_CONT');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'CONTROLLER');
if ($count == 0) {
	echo "<font color='red'><b>ERROR: P&aacute;gina no existe</b></font><br>";
	exit;
}

if (isset($_SESSION['arr_gco_obj'])) {
	$arr = $_SESSION['arr_gco_obj'];
}
else {
	$arr = array();
}

if (count($arr) > 0) {

include 'datos_abrir_bd.php';

$mysqli->autocommit(FALSE);

$query = "SELECT now()";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$ahora = $fila[0];

foreach ($arr as $v) {
	if (count($v)==3) {
		$error=0;
		if (is_numeric($v[2])) {
			$est = abs((int) filter_var($v[2], FILTER_SANITIZE_NUMBER_INT));
			if ($est < 0 || $est > 2) {
				$error=1;
			}
		}
		else {
			$error=1;
		}
		
		if ($error==0) {
			if (substr($v[1], 0, 3) == 'PE-') {
				$gti = 3;
			}
			else if (substr($v[1], 0, 2) == 'PE') {
				$gti = 2;
			}
			else {
				$gti = 4;
			}
			
			$query = "SELECT COUNT(*) FROM gastos_colobjects WHERE gco_cobj='".$v[1]."'";
			$result = $mysqli->query($query) or die ($mysqli->error);
			$fila=$result->fetch_array();
			$count2 = $fila[0];
			
			if ($est == 2) {
				$stmt = $mysqli->prepare("DELETE FROM gastos_colobjects WHERE gco_cobj=?") or die ($mysqli->error);
				$stmt->bind_param('s',
					$v[1]);
				$stmt->execute() or die ($mysqli->error);
			}
			else {
				if ($count2 == 0) {
					$stmt = $mysqli->prepare("INSERT INTO gastos_colobjects VALUES (null, ?, ?, ?, ?)") or die ($mysqli->error);
					$stmt->bind_param('issi',
						$gti,
						$v[0],
						$v[1],
						$v[2]);
					$stmt->execute() or die ($mysqli->error);
				}
				else {
					$stmt = $mysqli->prepare("UPDATE gastos_colobjects SET gti_id=?, gco_nom=?, gco_act=? WHERE gco_cobj=?") or die ($mysqli->error);
					$stmt->bind_param('isis',
						$gti,
						$v[0],
						$v[2],
						$v[1]);
					$stmt->execute() or die ($mysqli->error);
				}
			}
		}
	}
}

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Actualizacion masiva de gco objects, hecha por ".$_SESSION['rec_usu_nombre'];
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);
$stmt->close();

$mysqli->commit();

include 'datos_cerrar_bd.php';

unset($_SESSION['arr_gco_obj']);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Importar Gastos Colobjects (GCO COBJ) - Administraci�n MinappES</title>
<style type="text/css">
	body{font-size: 10pt; font-family: arial,helvetica}
</style>
<style>
.encabezado_h {
	background-color: silver;
	text-align: center;
}

.iconos {
	vertical-align:text-top;
	cursor: pointer;
}
</style>
</head>
<body>
<?php include ("header.php"); ?>

<h1>Importar Gastos Colobjects (GCO COBJ) Formato Excel</h1>

<p>Los cambios han sido aplicados, para verificar la data, haga clic en el siguiente enlace: <a href='mant_colobjects.php'>Mantenedor Gastos Colobjects (GCO COBJ)</a></p>

<?php include ("footer.php"); ?>
</body>
</html>
