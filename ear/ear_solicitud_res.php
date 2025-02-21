<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';

extract($_REQUEST, EXTR_PREFIX_ALL, "f");
if (!isset($f_zona_id)) die ('ERROR: Solicitud no v�lida');
$zona_id = filter_var($f_zona_id, FILTER_SANITIZE_STRING);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<!--<title>Registrar Solicitud EAR - Administracion - Minapp</title>-->
<style type="text/css">
	body{font-size: 10pt; font-family: arial,helvetica}
	.titulo {font-size: 14pt; font-family: arial,helvetica}
</style>
</head>
<body>
<?php // include ("header.php"); ?>

    <h1>Registro de solicitud <?php echo strtolower(getNomZona($zona_id)); ?></h1>

<p>Estado: Su solicitud <?php echo ( isset($_SESSION['ear_last_id']) ? getSolicitudCorrelativo($_SESSION['ear_last_id']) : "" ) ; ?> ha sido registrada, esperando visto bueno para realizar la transferencia.<br></p>

<?php include ("footer.php"); ?>
</body>
</html>
