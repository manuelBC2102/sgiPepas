<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<!--<title>Revisar Planilla de Movilidad - Administraci�n - Minapp</title>-->
<style type="text/css">
	body{font-size: 10pt; font-family: arial,helvetica}
	.titulo {font-size: 14pt; font-family: arial,helvetica}
</style>
</head>
<body>
<?php include ("header.php"); ?>

<h1>Revisar planilla de movilidad</h1>

<p>Estado: La planilla de movilidad ha sido revisada correctamente.<br></p>

<form method="post">
<input type="button" value="Cerrar ventana" onclick="window.close();">
</form>

<?php include ("footer.php"); ?>
</body>
</html>
