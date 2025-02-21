<?php

function diaconfecha($fecha, $ext = NULL)
{
  $fec_time = strtotime($fecha);
  $dia = date("D", $fec_time);

  if ($dia == "Mon")
    $dia = "Lun";
  if ($dia == "Tue")
    $dia = "Mar";
  if ($dia == "Wed")
    $dia = "Mie";
  if ($dia == "Thu")
    $dia = "Jue";
  if ($dia == "Fri")
    $dia = "Vie";
  if ($dia == "Sat")
    $dia = "Sab";
  if ($dia == "Sun")
    $dia = "Dom";

  $mes = date("M", $fec_time);

  if ($mes == "Jan")
    $mes = "Ene";
  if ($mes == "Feb")
    $mes = "Feb";
  if ($mes == "Mar")
    $mes = "Mar";
  if ($mes == "Apr")
    $mes = "Abr";
  if ($mes == "May")
    $mes = "May";
  if ($mes == "Jun")
    $mes = "Jun";
  if ($mes == "Jul")
    $mes = "Jul";
  if ($mes == "Aug")
    $mes = "Ago";
  if ($mes == "Sep")
    $mes = "Set";
  if ($mes == "Oct")
    $mes = "Oct";
  if ($mes == "Nov")
    $mes = "Nov";
  if ($mes == "Dec")
    $mes = "Dic";

  $anio = date("Y", $fec_time);
  $dia2 = date("j", $fec_time);

  if (is_null($ext))
    return "$dia, $dia2 $mes $anio";
  else
    $hora = date("h:i:s A", $fec_time);
  return "$dia, $dia2 $mes $anio $hora";
}

function eliminar_tildes($cadena)
{
  // Codificamos la cadena en formato utf8 en caso de que nos de errores
  // $cadena = utf8_encode($cadena);
  // Ahora reemplazamos las letras
  $cadena = str_replace(
    array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
    array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
    $cadena
  );

  $cadena = str_replace(
    array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
    array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
    $cadena
  );

  $cadena = str_replace(
    array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
    array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
    $cadena
  );

  $cadena = str_replace(
    array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
    array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
    $cadena
  );

  $cadena = str_replace(
    array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
    array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
    $cadena
  );

  $cadena = str_replace(
    array('ñ', 'Ñ', 'ç', 'Ç'),
    array('n', 'N', 'c', 'C'),
    $cadena
  );

  return $cadena;
}

function nombreMes($n)
{
  $nombre = "";
  switch ($n) {
    case 1:
      $nombre = "Enero";
      break;
    case 2:
      $nombre = "Febrero";
      break;
    case 3:
      $nombre = "Marzo";
      break;
    case 4:
      $nombre = "Abril";
      break;
    case 5:
      $nombre = "Mayo";
      break;
    case 6:
      $nombre = "Junio";
      break;
    case 7:
      $nombre = "Julio";
      break;
    case 8:
      $nombre = "Agosto";
      break;
    case 9:
      $nombre = "Septiembre";
      break;
    case 10:
      $nombre = "Octubre";
      break;
    case 11:
      $nombre = "Noviembre";
      break;
    case 12:
      $nombre = "Diciembre";
      break;
    default:
      $nombre = "Invalido";
  }
  return $nombre;
}

function getViaticosMonto($cod, $mon_id, $zona_id)
{
  $id = "$cod$zona_id";
  $monto = 0;

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT via_monto FROM ear_viaticos WHERE via_cod=? AND mon_id=?");
  $stmt->bind_param("si", $id, $mon_id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $monto = $fila[0];
  }

  include 'datos_cerrar_bd.php';

  return $monto;
}

function getViaticosMontoId($id)
{
  $monto = 0;

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT via_monto FROM ear_viaticos WHERE via_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $monto = $fila[0];
  }

  include 'datos_cerrar_bd.php';

  return $monto;
}

function getViaHospedajes($mon_id, $zona_id)
{
  $id = "03$zona_id%";
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT via_id, via_monto, via_nom FROM ear_viaticos WHERE via_cod LIKE ? AND via_monto IS NOT NULL AND mon_id=? ORDER BY via_nom");
  $stmt->bind_param("si", $id, $mon_id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getNomMoneda($id)
{
  $arr = array('Sin definir', 'Sin definir', 'Sin definir', '');

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT mon_nom, mon_iso, mon_simb, mon_img FROM monedas WHERE mon_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array($fila[0], $fila[1], $fila[2], $fila[3]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getNomZona($id)
{
  $valor = "Sin definir";

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT zona_nom FROM ear_zonas WHERE zona_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $valor = $fila[0];
  }

  include 'datos_cerrar_bd.php';

  return $valor;
}

function getInfoTrabajador($personaId)
{ //antes $codigogeneral_id
  $arr = array('', '', '', '', '', '', '', '', '', '', '', '', '');

  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $stmt = $mysqli->prepare("
                            SELECT codigo_identificacion as NRODOCUMENTO,
                                    nombre as NOMBRES,
                                    apellido_paterno as A_PATERNO,
                                    apellido_materno as A_MATERNO,
                                    '' as IDCARGO,
                                    '' as FECHA_OCURRENCIA,
                                    '' as DESCRIPCION_CP,
                                    '' as IDGRUPOTRABAJO,
                                    '' as DESCRIPCION_GP,
                                    '' as IDCCOSTO,
                                    '' as IDBANCO,
                                    '' as CUENTA_BANCO,
                                    '' as DESCRIPCION_SUC
                                FROM " . $baseSGI . ".persona where id=?");
  $stmt->bind_param("i", $personaId);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5], $fila[6], $fila[7], $fila[8], $fila[9], $fila[10], $fila[11], $fila[12]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getNombreTrabajador($codigogeneral_id)
{
  include 'datos_abrir_mssql.php';

  if ($debug_db == 1) {
    $query = "SELECT NRODOCUMENTO,NOMBRES,A_PATERNO,A_MATERNO,
			IDCARGO,FECHA_OCURRENCIA,DESCRIPCION_CP,
			IDGRUPOTRABAJO,DESCRIPCION_GP,
			IDCCOSTO,
			IDBANCO,CUENTA_BANCO,
			DESCRIPCION_SUC
			FROM infoTrabajador WHERE NRODOCUMENTO=?";
  } elseif ($debug_db == 2) {
    $query = "select TOP 1
			NRODOCUMENTO,LTRIM(RTRIM(NOMBRES)) NOMBRES,LTRIM(RTRIM(A_PATERNO)) A_PATERNO,LTRIM(RTRIM(A_MATERNO)) A_MATERNO,
			P.IDCARGO,PE.FECHA_OCURRENCIA,CP.DESCRIPCION,
			P.IDGRUPOTRABAJO,GP.DESCRIPCION,
			CCP.IDCCOSTO,
			PG.IDBANCO,PG.CUENTA_BANCO,
			S.DESCRIPCION
			from PERSONAL_GENERAL PG
			left join PERSONAL_EMPRESA PE ON PG.IDCODIGOGENERAL =  PE.IDCODIGOGENERAL
			left join PERSONAL P ON PG.IDCODIGOGENERAL =  P.IDCODIGOGENERAL
			left join CARGOS_PERSONAL CP ON CP.IDCARGO = P.IDCARGO
			left join CCOSTO_PERSONAL CCP ON CCP.IDCODIGOGENERAL = PG.IDCODIGOGENERAL
			left join GRUPO_TRABAJO GP ON GP.IDGRUPOTRABAJO = P.IDGRUPOTRABAJO
			left JOIN SUCURSALES S ON P.IDSUCURSAL = S.IDSUCURSAL
			where PG.IDCODIGOGENERAL=?
			AND PE.IDTIPO <> 2
			AND HASTA IS NULL
			AND ACTIVADO_EN_ESTAPLANI = 1
			AND P.IDEMPRESA = '001'
			ORDER BY PE.FECHA_OCURRENCIA DESC";
  }

  $stmt = odbc_prepare($connection, $query);
  odbc_execute($stmt, array($codigogeneral_id)) or die(exit("Error en odbc_execute: " . odbc_errormsg()));

  odbc_fetch_row($stmt);
  $var02 = odbc_result($stmt, 2) . " " . odbc_result($stmt, 3) . " " . odbc_result($stmt, 4);

  include 'datos_cerrar_mssql.php';

  return $var02;
}

function getCodigoGeneral($usu_ad)
{
  include 'datos_abrir_pdo_odbc.php';

  if ($debug_db == 1) {
    $query = 'select IDCODIGOGENERAL from PERSONAL_VARIABLES where IDVARIABLE = ? AND VALOR = ?';
  } elseif ($debug_db == 2) {
    $query = "select IDCODIGOGENERAL from PERSONAL_VARIABLES where IDVARIABLE = 'USR' AND VALOR = '$usu_ad'";
  }

  $stmt = $pdo->prepare($query);
  $stmt->execute(array('USR', $usu_ad));
  //$stmt->execute();
  if (!$stmt)
    exit('Query error: ' . print_r($pdo->errorInfo(), true));

  $var01 = null;

  while ($row = $stmt->fetch()) {
    $var01 = $row[0];
  }

  unset($pdo);

  return $var01;
}

function getListaTrabajadores()
{
  $arr = array();
  include 'datos_abrir_pdo_odbc.php';

  if ($debug_db == 1) {
    $query = "select NRODOCUMENTO, NOMBRES, A_PATERNO, A_MATERNO
			from PERSONAL_LISTA
			ORDER BY 2";
  } elseif ($debug_db == 2) {
    $query = "select distinct
			RIGHT(NRODOCUMENTO,8) NRODOCUMENTO,LTRIM(RTRIM(NOMBRES)) NOMBRES,LTRIM(RTRIM(A_PATERNO)) A_PATERNO,LTRIM(RTRIM(A_MATERNO)) A_MATERNO
			from PERSONAL_GENERAL PG
			left join PERSONAL_EMPRESA PE ON PG.IDCODIGOGENERAL =  PE.IDCODIGOGENERAL
			left join PERSONAL P ON PG.IDCODIGOGENERAL =  P.IDCODIGOGENERAL
			left join CARGOS_PERSONAL CP ON CP.IDCARGO = P.IDCARGO
			left join CCOSTO_PERSONAL CCP ON CCP.IDCODIGOGENERAL = PG.IDCODIGOGENERAL
			left join GRUPO_TRABAJO GP ON GP.IDGRUPOTRABAJO = P.IDGRUPOTRABAJO
			left JOIN SUCURSALES S ON P.IDSUCURSAL = S.IDSUCURSAL
			where PE.IDTIPO <> 2
			AND HASTA IS NULL
			AND ACTIVADO_EN_ESTAPLANI = 1
			AND P.IDEMPRESA = '001'
			AND P.IDPLANILLA IN ('EMP','PRA','EXT')
			ORDER BY 2";
  }

  $stmt = $pdo->prepare($query);
  $stmt->execute();
  if (!$stmt)
    exit('Query error: ' . print_r($pdo->errorInfo(), true));

  while ($row = $stmt->fetch()) {
    $arr[] = array($row[0], $row[1], $row[2], $row[3]);
  }

  unset($pdo);

  return $arr;
}

function getUsuAd($usu_id)
{
  $usu_ad = "";
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT usu_ad
		FROM recursos.usuarios
		WHERE usu_id=?");
  $stmt->bind_param("i", $usu_id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $usu_ad = $fila[0];
  }

  return $usu_ad;
}

function getViaId($via_cod, $mon_id = NULL)
{
  $valor = 0;

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT via_id FROM ear_viaticos WHERE via_cod=? AND mon_id <=> ?");
  $stmt->bind_param("si", $via_cod, $mon_id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $valor = $fila[0];
  }

  include 'datos_cerrar_bd.php';

  return $valor;
}

function getSolicitudInfo($id)
{
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $stmt = $mysqli->prepare("SELECT
                                concat(ifnull(sp3.nombre,''),' ',ifnull(sp3.apellido_paterno,''),' ',ifnull(sp3.apellido_materno,'')) as ear_tra_nombres,
                                concat(e.ear_anio, '-', LPAD(e.ear_mes, 2, '0'), '-', LPAD(e.ear_nro, 3, '0'), '/',
                                    fn_obtener_iniciales(concat(ifnull(sp3.nombre,''),' ',ifnull(sp3.apellido_paterno,''),' ',ifnull(sp3.apellido_materno,'')))) as ear_numero,
                                z.zona_nom, m.mon_nom, m.mon_iso, m.mon_simb, m.mon_img, e.ear_monto, te.est_nom, e.ear_sol_fec, e.ear_liq_fec,
                                e.ear_sol_motivo, sp3.codigo_identificacion as ear_tra_dni, e.ear_tra_cargo, e.ear_tra_area, e.ear_tra_sucursal, e.ear_tra_cta,
                                concat(sp2.nombre,' ',sp2.apellido_paterno,' ',sp2.apellido_materno) as usu_act, e.ear_act_fec,
                                e.ear_act_motivo, e.mon_id, e.zona_id, e.est_id, e.usu_id,
                                IFNULL(e.ear_liq_mon, 0) ear_liq_mon, IFNULL(e.ear_liq_ret, 0) ear_liq_ret, IFNULL(e.ear_liq_ret_no, 0) ear_liq_ret_no,
                                IFNULL(e.ear_liq_det, 0) ear_liq_det, IFNULL(e.ear_liq_det_no, 0) ear_liq_det_no, e.ear_liq_dcto,
                                IFNULL(e.ear_liq_gast_asum, 0) ear_liq_gast_asum, e.pla_id, e.ear_act_obs1, e.ear_aprob_usu, e.master_usu_id,e.tipo_usu_id,
                                fn_obtener_iniciales(concat(ifnull(sp.nombre,''),' ',ifnull(sp.apellido_paterno,''),' ',ifnull(sp.apellido_materno,''))) as usu_iniciales,
                                su.persona_id, e.dua_id,e.tipo_cambio,e.guardar_tc_sgi,e.periodo_id,e.dua_serie, e.dua_numero,
                                CONCAT(sd.serie,'-',sd.numero) as ear_ord_trabajo,
                                e.usuario_reg_id
                        FROM ear_solicitudes e
                        LEFT JOIN ear_zonas z ON z.zona_id=e.zona_id
                        LEFT JOIN monedas m ON m.mon_id=e.mon_id
                        LEFT JOIN tablas_estados te ON te.tabla_id=1 AND te.est_id=e.est_id
                        JOIN tablas_nombres tn ON tn.tabla_id=te.tabla_id AND tabla_nom='ear_solicitudes'
                        LEFT JOIN " . $baseSGI . ".usuario su2 ON su2.id=e.ear_act_usu
                        left join " . $baseSGI . ".persona sp2 on sp2.id=su2.persona_id
                        inner join " . $baseSGI . ".usuario su on su.id=e.usuario_reg_id
                        left join ear_liq_detalle eliq on eliq.ear_id=e.ear_id
                        LEFT JOIN " . $baseSGI . ".documento sd on sd.id=eliq.orden_trabajo_id
                        left join " . $baseSGI . ".persona sp on sp.id=su.persona_id # para usuario_reg
                        left join " . $baseSGI . ".persona sp3 on sp3.id=e.usu_id #para terceros
                    WHERE e.ear_id=? and su.estado=1 and sp.estado=1");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array(
      $fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5], $fila[6], $fila[7], $fila[8], $fila[9],
      $fila[10], $fila[11], $fila[12], $fila[13], $fila[14], $fila[15], $fila[16], $fila[17], $fila[18], $fila[19],
      $fila[20], $fila[21], $fila[22], $fila[23],
      $fila[24], $fila[25], $fila[26],
      $fila[27], $fila[28], $fila[29],
      $fila[30], $fila[31], $fila[32], $fila[33],
      $fila[34], $fila[35], $fila[36], $fila[37], $fila[38], $fila[39], $fila[40], $fila[41], $fila[42],
      $fila[43], $fila[44], $fila[45]
    );
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getSolicitudDetalle($id)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT ev.via_cod, ev.via_nom, UPPER(ed.via_desc) via_desc, TRIM(ed.via_dias)+0 via_dias, ed.via_monto, ed.via_id
		FROM ear_sol_detalle ed
		LEFT JOIN ear_viaticos ev ON ev.via_id=ed.via_id
		WHERE ed.ear_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getViaticoNom($id)
{
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT via_nom FROM ear_viaticos WHERE via_cod=?");
  $stmt->bind_param("s", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $valor = $fila[0];
  }

  include 'datos_cerrar_bd.php';

  return $valor;
}

function getSolicitudActualizaciones($id)
{
  $arr = array();

  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $stmt = $mysqli->prepare("SELECT case ea.est_id
                                            when 9 then
                                                case when e.ear_liq_dcto > 0 then 'Por devolver'
                                                else 'Por reembolsar' end
                                            else te.est_nom
                                        end as est_nom,
                                 concat(sp2.nombre,' ',sp2.apellido_paterno,' ',sp2.apellido_materno) usu_act,
                    ea.ear_act_fec, ea.ear_act_motivo
                    FROM ear_actualizaciones ea
                    inner join ear_solicitudes e on e.ear_id=ea.ear_id
                    LEFT JOIN tablas_estados te ON te.tabla_id=1 AND te.est_id=ea.est_id
                    JOIN tablas_nombres tn ON tn.tabla_id=te.tabla_id AND tabla_nom='ear_solicitudes'
                    LEFT JOIN " . $baseSGI . ".usuario su2 ON su2.id=ea.ear_act_usu
                    left join " . $baseSGI . ".persona sp2 on sp2.id=su2.persona_id
		WHERE ea.ear_id=?
		ORDER BY ea.ear_act_fec, ea.est_id ASC");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getLoteCajaChicaActualizaciones($id)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT te.est_nom, ru.usu_nombre usu_act, ea.ccl_act_fec, ea.ccl_act_motivo
		FROM cajas_chicas_lote_act ea
		LEFT JOIN tablas_estados te ON te.tabla_id=3 AND te.est_id=ea.est_id
		JOIN tablas_nombres tn ON tn.tabla_id=te.tabla_id AND tabla_nom='cajas_chicas_lote'
		LEFT JOIN recursos.usuarios ru ON ru.usu_id=ea.ccl_act_usu
		WHERE ea.ccl_id=?
		ORDER BY ea.ccl_act_fec, ea.est_id ASC");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getSolicitudCorrelativo($id)
{
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $stmt = $mysqli->prepare("SELECT CONCAT(e.ear_anio, '-', LPAD(e.ear_mes, 2, '0'), '-', LPAD(e.ear_nro, 3, '0'), '/',
                                        fn_obtener_iniciales(concat(ifnull(p.nombre,''),' ',ifnull(p.apellido_paterno,''),' ',ifnull(p.apellido_materno,'')))) ear_numero
                                FROM ear_solicitudes e
                                LEFT JOIN " . $baseSGI . ".usuario ud ON ud.id=e.usu_id
                                LEFT JOIN " . $baseSGI . ".persona p ON p.id=ud.persona_id
                                WHERE e.ear_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $valor = $fila[0];
  }

  include 'datos_cerrar_bd.php';

  return $valor;
}

function startsWith($haystack, $needle)
{
  return $needle === "" || strpos($haystack, $needle) === 0;
}

function endsWith($haystack, $needle)
{
  return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}

function getRucDatos($ruc_nro, $tipo_doc = null)
{
  $arr = array('', -1, -1, -1, -1, -1, '000');
  $ruc_exp = -1; // Variable para almacenar el valor del RUC si esta expirado en la base o no

  if (strlen($ruc_nro) == 11) {
    include 'datos_abrir_bd.php';

    $stmt = $mysqli->prepare("SELECT prov_nom, ruc_act, ruc_ret, ruc_hab, prov_factura, prov_provincia, IF(DATEDIFF(CURDATE(), ruc_chk_fec)<30, 0, 1) ruc_exp FROM proveedores WHERE ruc_nro=?");
    $stmt->bind_param("s", $ruc_nro);
    $stmt->execute() or die($mysqli->error);
    $stmt->store_result();
    while ($fila = fetchAssocStatement($stmt)) {
      $arr = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5], '000');
      $ruc_exp = $fila['ruc_exp'];
    }

    if ($arr[1] == -1 || $ruc_exp == 1) {
      $http_status = 200;
      try {
        $conexion = new SoapClient("http://44.194.84.229/ConsultaSunatWS/ConsultaSunatWS.asmx?WSDL");
        $parametros = new stdClass();
        $parametros->ruc = $ruc_nro;
        $resultadoSap = $conexion->consultaRUC2((array) $parametros)->consultaRUC2Result;
        $respuesta = json_decode($resultadoSap);
        $arrayRespuesta = array();
        if ($respuesta->status == 'OK') {
          if ($respuesta->data != null) {
            foreach ($respuesta->data as $index => $item) {
              foreach ($item as $index => $itemString) {
                $arrayRespuesta = array_merge($arrayRespuesta, array(($itemString[0]) => ($itemString[1])));
              }
            }
          }
        }

        if (count($arrayRespuesta) > 0) {
          $prov_nom = @trim($arrayRespuesta['razonSocial']);

          $ruc_act_s = @trim($arrayRespuesta['Estado']);
          if ($ruc_act_s == "ACTIVO")
            $ruc_act = 1;
          else
            $ruc_act = 0;

          $ruc_hab_s = @trim($arrayRespuesta['Condición']);
          if ($ruc_hab_s == "HABIDO")
            $ruc_hab = 1;
          else
            $ruc_hab = 0;

          $prov_dir = preg_replace('/\s+/', ' ', @trim($arrayRespuesta['DomicilioFiscal']));
          $arrUbi = getUbigeos();
          $matches = array('ERROR', 'ERROR', -1);

          foreach ($arrUbi as $k => $v) {
            if (strpos($prov_dir, $v[0]) !== false) {
              $matches = $v;
              break;
            }
          }
          $prov_factura = 1;
          $ruc_ret = 0;

          //				if (strpos($arr2[4], 'FACTURA') !== false) $prov_factura=1; else $prov_factura=0;
          //				if (strpos($arr2[5], 'Incorporado al Régimen de Agentes de Retención de IGV') !== false) $ruc_ret=1; else $ruc_ret=0;
          // Actualiza la info en la base de datos
          if ($ruc_exp == 1) {
            $stmt = $mysqli->prepare("UPDATE proveedores SET prov_nom=?, ruc_act=?, ruc_ret=?, ruc_hab=?, ruc_chk_fec=now(), prov_factura=?, prov_provincia=? WHERE ruc_nro=?") or die($mysqli->error);
            $stmt->bind_param('siiiiss', $prov_nom, $ruc_act, $ruc_ret, $ruc_hab, $prov_factura, $matches[1], $ruc_nro);
          } else {
            $stmt = $mysqli->prepare("INSERT INTO proveedores (ruc_nro, prov_nom, ruc_act, ruc_ret, ruc_hab, ruc_chk_fec, prov_factura, prov_provincia) VALUES (?, ?, ?, ?, ?, now(), ?, ?)") or die($mysqli->error);
            $stmt->bind_param('ssiiiis', $ruc_nro, $prov_nom, $ruc_act, $ruc_ret, $ruc_hab, $prov_factura, $matches[1]);
          }

          $stmt->execute() or die($mysqli->error);
          $stmt->close();

          $arr = array($prov_nom, $ruc_act, $ruc_ret, $ruc_hab, $prov_factura, $matches[1], '000');
        }
      } catch (Exception $exc) {
      }
      $arr[6] = $http_status;
    }

    include 'datos_cerrar_bd.php';
  } else if (strlen($ruc_nro) == 8 && $tipo_doc == 10) {
    include 'datos_abrir_bd.php';

    $stmt = $mysqli->prepare("SELECT prov_nom, ruc_act, ruc_ret, ruc_hab, prov_factura, prov_provincia, IF(ruc_chk_fec=CURDATE(), 0, 1) ruc_exp FROM proveedores WHERE ruc_nro=?");
    $stmt->bind_param("s", $ruc_nro);
    $stmt->execute() or die($mysqli->error);
    $stmt->store_result();
    while ($fila = fetchAssocStatement($stmt)) {
      $arr = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5], '000');
      $ruc_exp = $fila['ruc_exp'];
    }

    if ($arr[1] == -1 || $ruc_exp == 1) {
      list(
        $dni, $nombres, $cargo_id, $fecha_ing,
        $cargo_desc, $area_id, $area_desc, $idccosto, $banco, $ctacte, $sucursal
      ) = getInfoTrabajador($ruc_nro);
      $nombres = trim($nombres);
      $sucursal = trim($sucursal);

      if (strlen($nombres) > 0) {
        if ($ruc_exp == 1) {
          $stmt = $mysqli->prepare("UPDATE proveedores SET prov_nom=?, ruc_act=1, ruc_ret=1, ruc_hab=1, ruc_chk_fec=now(), prov_factura=0, prov_provincia=? WHERE ruc_nro=?") or die($mysqli->error);
          $stmt->bind_param('sss', $nombres, $sucursal, $ruc_nro);
        } else {
          $stmt = $mysqli->prepare("INSERT INTO proveedores (ruc_nro, prov_nom, ruc_act, ruc_ret, ruc_hab, ruc_chk_fec, prov_factura, prov_provincia) VALUES (?, ?, 1, 1, 1, now(), 0, ?)") or die($mysqli->error);
          $stmt->bind_param('sss', $ruc_nro, $nombres, $sucursal);
        }

        $stmt->execute() or die($mysqli->error);
        $stmt->close();

        $arr = array($nombres, 1, 1, 1, 0, $sucursal, '000');
      }
    }


    include 'datos_cerrar_bd.php';
  }

  return $arr;
}

function getRucDatosOld($ruc_nro, $tipo_doc = null)
{
  // ESTA FUNCION HA QUEDADO OBSOLETA POR QUE LA SUNAT HA DADO DE BAJA EL SERVICIO DE CONSULTA POR WAP (09/02/2015)
  // NO UTILIZAR

  $arr = array('', -1, -1, -1, -1, -1, '000');
  $ruc_exp = -1; // Variable para almacenar el valor del RUC si esta expirado en la base o no

  if (strlen($ruc_nro) == 11) {
    include 'datos_abrir_bd.php';

    $stmt = $mysqli->prepare("SELECT prov_nom, ruc_act, ruc_ret, ruc_hab, prov_factura, prov_provincia, IF(ruc_chk_fec=CURDATE(), 0, 1) ruc_exp FROM proveedores WHERE ruc_nro=?");
    $stmt->bind_param("s", $ruc_nro);
    $stmt->execute() or die($mysqli->error);
    $stmt->store_result();
    while ($fila = fetchAssocStatement($stmt)) {
      $arr = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5], '000');
      $ruc_exp = $fila['ruc_exp'];
    }

    if ($arr[1] == -1 || $ruc_exp == 1) {
      // Primero revisa si el RUC emite factura
      $prov_factura = getRUCEmiteFactura($ruc_nro);
      if ($prov_factura == -2)
        $arr[6] = '404';

      if ($prov_factura >= 0) {
        $arr2 = array();
        $str0 = "N�mero Ruc.";
        $str1 = "Estado.";
        $str2 = "Agente Retenci�n IGV.";
        $str3 = "Direcci�n.";
        $str4 = "Situaci�n.";

        # Use the Curl extension to query SUNAT and get back a page of results
        $url = "http://www.sunat.gob.pe/w/wapS01Alias?ruc=" . $ruc_nro;
        $ch = curl_init();
        $timeout = 8;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_LOW_SPEED_LIMIT, 2); // bytes per second
        curl_setopt($ch, CURLOPT_LOW_SPEED_TIME, $timeout); // seconds
        $html = curl_exec($ch);
        curl_close($ch);

        # Create a DOM parser object
        $dom = new DOMDocument();

        # Parse the HTML from SUNAT.
        # The @ before the method call suppresses any warnings that
        # loadHTML might throw because of invalid HTML in the page.
        @$dom->loadHTML($html);

        # Iterate over all the <small> tags
        foreach ($dom->getElementsByTagName('small') as $link) {
          if (startsWith($link->nodeValue, $str0) || startsWith($link->nodeValue, $str1) || startsWith($link->nodeValue, $str2) || startsWith($link->nodeValue, $str3) || startsWith($link->nodeValue, $str4)) {
            $arr2[] = mb_convert_encoding($link->nodeValue, 'ISO-8859-1', 'utf-8');
          }
        }

        $prov_nom = @trim(substr($arr2[0], strpos($arr2[0], "-") + 2));
        $prov_nom = filter_var($prov_nom, FILTER_SANITIZE_STRING);
        $ruc_act_s = @substr(trim(substr($arr2[1], strlen($str1))), 0, 6);
        $ruc_ret_s = @substr(trim(substr($arr2[2], strlen($str2))), 0, 2);
        $prov_dir = @trim(substr($arr2[3], strlen($str3) - 1));
        $ruc_hab_s = @substr(trim(substr($arr2[4], strlen($str4))), 0, 6);

        if (strlen($prov_nom) > 0) {
          if ($ruc_act_s == "ACTIVO")
            $ruc_act = 1;
          else
            $ruc_act = 0;
          if ($ruc_ret_s == "SI")
            $ruc_ret = 1;
          else
            $ruc_ret = 0;
          if ($ruc_hab_s == "HABIDO")
            $ruc_hab = 1;
          else
            $ruc_hab = 0;

          $arrUbi = getUbigeos();
          $matches = array('ERROR', 'ERROR', -1);

          foreach ($arrUbi as $k => $v) {
            if ((strpos($prov_dir, $v[0]) !== false)) {
              $matches = $v;
              break;
            }
          }

          if ($ruc_exp == 1) {
            $stmt = $mysqli->prepare("UPDATE proveedores SET prov_nom=?, ruc_act=?, ruc_ret=?, ruc_hab=?, ruc_chk_fec=now(), prov_factura=?, prov_provincia=? WHERE ruc_nro=?") or die($mysqli->error);
            $stmt->bind_param('siiiiss', $prov_nom, $ruc_act, $ruc_ret, $ruc_hab, $prov_factura, $matches[1], $ruc_nro);
          } else {
            $stmt = $mysqli->prepare("INSERT INTO proveedores (ruc_nro, prov_nom, ruc_act, ruc_ret, ruc_hab, ruc_chk_fec, prov_factura, prov_provincia) VALUES (?, ?, ?, ?, ?, now(), ?, ?)") or die($mysqli->error);
            $stmt->bind_param('ssiiiis', $ruc_nro, $prov_nom, $ruc_act, $ruc_ret, $ruc_hab, $prov_factura, $matches[1]);
          }

          $stmt->execute() or die($mysqli->error);
          $stmt->close();

          $arr = array($prov_nom, $ruc_act, $ruc_ret, $ruc_hab, $prov_factura, $matches[1], '000');
        }
      }
    }

    include 'datos_cerrar_bd.php';
  } else if (strlen($ruc_nro) == 8 && $tipo_doc == 10) {
    include 'datos_abrir_bd.php';

    $stmt = $mysqli->prepare("SELECT prov_nom, ruc_act, ruc_ret, ruc_hab, prov_factura, prov_provincia, IF(ruc_chk_fec=CURDATE(), 0, 1) ruc_exp FROM proveedores WHERE ruc_nro=?");
    $stmt->bind_param("s", $ruc_nro);
    $stmt->execute() or die($mysqli->error);
    $stmt->store_result();
    while ($fila = fetchAssocStatement($stmt)) {
      $arr = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5], '000');
      $ruc_exp = $fila['ruc_exp'];
    }

    if ($arr[1] == -1 || $ruc_exp == 1) {
      list(
        $dni, $nombres, $cargo_id, $fecha_ing,
        $cargo_desc, $area_id, $area_desc, $idccosto, $banco, $ctacte, $sucursal
      ) = getInfoTrabajador($ruc_nro);
      $nombres = trim($nombres);
      $sucursal = trim($sucursal);

      if (strlen($nombres) > 0) {
        if ($ruc_exp == 1) {
          $stmt = $mysqli->prepare("UPDATE proveedores SET prov_nom=?, ruc_act=1, ruc_ret=1, ruc_hab=1, ruc_chk_fec=now(), prov_factura=0, prov_provincia=? WHERE ruc_nro=?") or die($mysqli->error);
          $stmt->bind_param('sss', $nombres, $sucursal, $ruc_nro);
        } else {
          $stmt = $mysqli->prepare("INSERT INTO proveedores (ruc_nro, prov_nom, ruc_act, ruc_ret, ruc_hab, ruc_chk_fec, prov_factura, prov_provincia) VALUES (?, ?, 1, 1, 1, now(), 0, ?)") or die($mysqli->error);
          $stmt->bind_param('sss', $ruc_nro, $nombres, $sucursal);
        }

        $stmt->execute() or die($mysqli->error);
        $stmt->close();

        $arr = array($nombres, 1, 1, 1, 0, $sucursal, '000');
      }
    }


    include 'datos_cerrar_bd.php';
  }

  return $arr;
}

function getRUCEmiteFactura($ruc_nro)
{
  // ESTA FUNCION HA QUEDADO OBSOLETA POR QUE LA SUNAT HA DADO DE BAJA EL SERVICIO DE CONSULTA POR WAP (09/02/2015)
  // NO UTILIZAR
  // Valores que retorna esta funcion:
  // -2 : Error 404 HTTP
  // -1 : Error de conexion, ruc no existe, ruc invalido
  //  0 : No emite factura
  //  1 : Si emite factura

  $val = -1;

  # Use the Curl extension to query SUNAT and get back a page of results
  $url = "http://www.sunat.gob.pe/w/wapS04Alias?ruc=" . $ruc_nro . "&aut=";
  $ch = curl_init();
  $timeout = 8;
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
  curl_setopt($ch, CURLOPT_LOW_SPEED_LIMIT, 2); // bytes per second
  curl_setopt($ch, CURLOPT_LOW_SPEED_TIME, $timeout); // seconds
  $html = curl_exec($ch);
  $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  if ($http_status == "404")
    $val = -2;
  curl_close($ch);

  # Create a DOM parser object
  $dom = new DOMDocument();

  # Parse the HTML from SUNAT.
  # The @ before the method call suppresses any warnings that
  # loadHTML might throw because of invalid HTML in the page.
  @$dom->loadHTML($html);

  # Iterate over all the <small> tags
  foreach ($dom->getElementsByTagName('small') as $link) {
    if (strpos($link->nodeValue, 'FACTURA') !== false) {
      $val = 1;
      break;
    } else if (strpos($link->nodeValue, 'invalido') !== false) {
      $val = -1;
      break;
    } else {
      $val = 0;
    }
  }

  return $val;
}

function getUbigeosSunat()
{
  $arr = array();
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $query = "select   codigo,departamento,provincia,distrito
                    from  ubigeo u  ";

  $stmt = $mysqli->prepare($query);
  $stmt->execute() or die($mysqli->error);

  $result = $stmt->get_result();
  while ($fila = $result->fetch_array()) {
    $arr[$fila[0]] = array($fila[0], $fila[1], $fila[2], $fila[3]);
  }
  include 'datos_cerrar_bd.php';
  return $arr;
}

function getProveedorMaximoRegistrado($tablaProveedor, $fec)
{
  $arr = array();
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $query = "select   MAX(prov_id) as id,ruc_nro,ruc_chk_fec
                    from  " . $tablaProveedor . " where ruc_chk_fec = ?";

  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("s", $fec);
  $stmt->execute() or die($mysqli->error);
  $result = $stmt->get_result();
  while ($fila = $result->fetch_array()) {
    $arr[] = array($fila[0], $fila[1], $fila[2]);
  }
  include 'datos_cerrar_bd.php';
  return $arr;
}

function getProveedorSunatLog($fec)
{
  $arr = array();
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $query = "select log_id,fecha,contador,estado from proveedor_sunat_log where fecha = ?";

  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("s", $fec);
  $stmt->execute() or die($mysqli->error);
  $result = $stmt->get_result();
  while ($fila = $result->fetch_array()) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3]);
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function insertProveedorSunatLog($fec)
{
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $stmt = $mysqli->prepare("INSERT INTO proveedor_sunat_log (fecha) VALUES (?)") or die($mysqli->error);
  $stmt->bind_param("s", $fec);
  $stmt->execute() or die($mysqli->error);
  $insertion_id = $mysqli->insert_id;
  include 'datos_cerrar_bd.php';
  return $insertion_id;
}

function updateContadorProveedorSunatLog($id)
{
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $stmt = $mysqli->prepare("UPDATE proveedor_sunat_log set contador = ifnull(contador,0) + 1  where log_id = ?") or die($mysqli->error);
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  include 'datos_cerrar_bd.php';
  return 1;
}

function updateEstadoCierreProveedorSunatLog($id, $comentario)
{
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $stmt = $mysqli->prepare("UPDATE proveedor_sunat_log set estado = 1, contador = ifnull(contador,0) + 1 , observacion = ? where log_id = ?") or die($mysqli->error);
  $stmt->bind_param("si", $comentario, $id);
  $stmt->execute() or die($mysqli->error);
  include 'datos_cerrar_bd.php';
  return 1;
}

function getUbigeos()
{
  $arr = array();
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $query = "select  remover_tildes(UPPER(concat(RTRIM(u3.descripcion),' - ',RTRIM(u2.descripcion),' - ',RTRIM(u.descripcion)))) as UBIGEO,
                          remover_tildes(UPPER(RTRIM(u2.descripcion))) as PROVINCIA,
                          u.id as IDUBIGEO
                    from " . $baseSGI . ".ubigeo u
                            inner join " . $baseSGI . ".ubigeo u2 on u.ubigeo_padre_id=u2.id and u2.ubigeo_tipo_id=2
                            inner join " . $baseSGI . ".ubigeo u3 on u2.ubigeo_padre_id=u3.id and u3.ubigeo_tipo_id=1
                    where u.estado=1
                            and u.ubigeo_tipo_id=3";

  $stmt = $mysqli->prepare($query);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2]);
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function getTipoCambio($mon_id, $fec)
{
  include 'parametros.php';
  $tc_precio = -1;
  $fec = substr($fec, 0, 10);

  if (!is_null($fec)) {
    include 'datos_abrir_bd.php';

    $stmt = $mysqli->prepare("select equivalencia_venta from " . $baseSGI . ".tipo_cambio where estado=1 and date(fecha)<=? order by fecha desc limit 1");
    $stmt->bind_param("s", $fec);
    $stmt->execute() or die($mysqli->error);
    $stmt->store_result();
    while ($fila = fetchAssocStatement($stmt)) {
      $tc_precio = $fila[0];
    }
    include 'datos_cerrar_bd.php';
  }

  return $tc_precio;
}

function getGastosTipos()
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT gti_id, gti_nom
		FROM gastos_tipos
		ORDER BY gti_id");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getGastosColObjects($id)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  if ($id == 1) {
    //		$stmt = $mysqli->prepare("SELECT gco_cobj, ru.usu_nombre gco_nom
    //			FROM usu_detalle ud
    //			LEFT JOIN recursos.usuarios ru ON ru.usu_id=ud.usu_id
    //			ORDER BY ru.usu_nombre");
    $stmt = $mysqli->prepare("SELECT null");
  } else {
    $stmt = $mysqli->prepare("SELECT gco_cobj, SUBSTR(gco_nom, 1, 45) gco_nom
			FROM gastos_colobjects
			WHERE gti_id=? AND gco_act=1
			ORDER BY gco_cobj");
    $stmt->bind_param("i", $id);
  }

  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getLiqConceptos($mon_id, $conc_cod)
{
  $conc_cod = "$conc_cod%";
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT conc_cta_cont, conc_nom, ec.conc_cod, ec.conc_id, conc_cve
		FROM ear_conceptos ec
		WHERE mon_id=? AND ec.conc_cod LIKE ? AND ec.conc_act=1
		ORDER BY ec.conc_cod");
  $stmt->bind_param("is", $mon_id, $conc_cod);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getLiqConceptosLista()
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT ec.conc_id, ec.conc_cod, ec.conc_nom, m.mon_nom, m.mon_iso, m.mon_img, ec.conc_cta_cont, ec.conc_act, ec.conc_acf, ec.conc_cve
		FROM ear_conceptos ec
		LEFT JOIN monedas m ON m.mon_id=ec.mon_id
		ORDER BY ec.conc_id");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5], $fila[6], $fila[7], $fila[8], $fila[9]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getLiqConceptosCodigos()
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT conc_id, conc_cod, conc_nom
		FROM ear_conceptos
		WHERE mon_id IS NULL
		ORDER BY conc_id");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getLiqConceptoInfo($id)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT ec.conc_cod, ec.conc_nom, m.mon_nom, m.mon_iso, m.mon_img, ec.conc_cta_cont, ec.conc_act, ec.conc_acf, ec.conc_cve, ec.conc_fmt_glosa
		FROM ear_conceptos ec
		LEFT JOIN monedas m ON m.mon_id=ec.mon_id
		WHERE ec.conc_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5], $fila[6], $fila[7], $fila[8], $fila[9]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getLiqConceptosRetDet($conc_id, $fec)
{
  $arr = array();
  list($rete_tasa, $rete_min_monto) = getSUNATRetencion($fec);

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT IFNULL(detr_tasa,0.00) detr_tasa, detr_min_monto
		FROM ear_conceptos ec
		LEFT JOIN (
			SELECT d.conc_cod, d.detr_tasa, d.detr_min_monto, d.detr_desde_fec
			FROM (
				SELECT conc_cod, MAX(detr_desde_fec) AS detr_desde_fec
				FROM detracciones
				WHERE detr_desde_fec<=?
				GROUP BY conc_cod
			) AS x
			INNER JOIN detracciones d ON d.conc_cod=x.conc_cod AND d.detr_desde_fec=x.detr_desde_fec
		) y ON y.conc_cod=ec.conc_cod
		WHERE ec.conc_id=?
		ORDER BY ec.conc_cod");
  $stmt->bind_param("si", $fec, $conc_id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  $fila = fetchAssocStatement($stmt);
  $arr = array($rete_tasa, $rete_min_monto, $fila[0], $fila[1]);

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getTipoDoc()
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT doc_id, doc_abrv, doc_ruc_req, doc_apl_ret, doc_apl_det, doc_nro, doc_desc, doc_cod, doc_tax_code, doc_edit, doc_borr, doc_act,doc_requiere_distribucion,sgi_documento_tipo_id,doc_valida_sunat,doc_valida_sgi FROM doc_tipos WHERE doc_id <> 20 ORDER BY doc_nro");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5], $fila[6], $fila[7], $fila[8], $fila[9], $fila[10], $fila[11], $fila[12], $fila[13], $fila[14], $fila[15]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getOrdenTrabajo($parametro1, $parametro2)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("select d.id,
      concat(d.serie,'-',d.numero) as serie_numero,
      concat(ifnull(p.nombre,''),' ', ifnull(p.apellido_paterno,'') ,' ',ifnull(p.apellido_materno,'') )  as persona_nombre_completo,
      ifnull(ddv.valor_cadena,'') as proyecto
      from ".$baseSGI.".documento d
      left join ".$baseSGI.".persona p on d.persona_id = p.id
      left join ".$baseSGI.".documento_dato_valor ddv on ddv.documento_id=d.id and ddv.documento_tipo_dato_id=2924
      where d.documento_tipo_id = 269 and d.estado = 1 and d.fecha_emision like '%".$parametro1."%' and concat(d.serie,'-',d.numero) like '%".$parametro2."%'");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getTipoDocNota()
{
  $arr = array();
  include 'datos_abrir_bd.php';
  $stmt = $mysqli->prepare("SELECT doc_id, doc_abrv, doc_ruc_req, doc_apl_ret, doc_apl_det, doc_nro, doc_desc, doc_cod, doc_tax_code, doc_edit, doc_borr, doc_act,doc_requiere_distribucion,sgi_documento_tipo_id,doc_valida_sunat,doc_valida_sgi FROM doc_tipos WHERE doc_id in (18,19) ORDER BY doc_nro");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5], $fila[6], $fila[7], $fila[8], $fila[9], $fila[10], $fila[11], $fila[12], $fila[13], $fila[14], $fila[15]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getTipoDocInfo($id)
{
  $arr = array();

  include 'datos_abrir_bd.php';
  $mysqli->set_charset("utf8");
  $stmt = $mysqli->prepare("SELECT doc_id, doc_abrv, doc_ruc_req, doc_apl_ret, doc_apl_det, doc_nro, doc_desc, doc_cod, doc_tax_code, doc_edit, doc_borr, doc_act, sgi_documento_tipo_id, doc_valida_sunat,doc_valida_sgi
		FROM doc_tipos
		WHERE doc_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5], $fila[6], $fila[7], $fila[8], $fila[9], $fila[10], $fila[11], $fila[12], $fila[13], $fila[14]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getTipoMon()
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT mon_id, mon_iso FROM monedas ORDER BY mon_id");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getCentroCosto()
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("select id,descripcion,centro_costo_padre_id,codigo from $baseSGI.centro_costo  where estado = 1 and empresa_id = 2 ORDER BY codigo ASC;");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getCuentasContables($isDua)
{

  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("
                  select p.id, concat(p.codigo,' | ',p.descripcion) descripcion,p.plan_contable_padre_id, exists(SELECT pp.id FROM $baseSGI.plan_contable pp where pp.estado = 1 and pp.plan_contable_padre_id = p.id) esPadre from $baseSGI.plan_contable p
                   inner join $baseSGI.cont_operacion_tipo op on op.id = " . ($isDua ? "2" : "3") . " and p.codigo  regexp  concat('^',replace(op.cuentas_relacionadas,',','|^'))
                   where p.empresa_id = 2 and p.estado = 1
                   order by p.codigo;");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}
function getCuentasContablesXOperacionTipoId($operacionTipoId)
{

  $arr = array();
  include 'datos_abrir_bd.php';
  $stmt = $mysqli->prepare("
                  select p.id,
                         concat(p.codigo,' | ',p.descripcion) descripcion,
                         p.plan_contable_padre_id,
                         exists(SELECT pp.id FROM $baseSGI.plan_contable pp where pp.estado = 1 and pp.plan_contable_padre_id = p.id) esPadre
                   from $baseSGI.plan_contable p
                   inner join $baseSGI.cont_operacion_tipo op on op.id = " . $operacionTipoId . " and p.codigo  regexp  concat('^',replace(op.cuentas_relacionadas,',','|^'))
                   where p.empresa_id = 2 and p.estado = 1
                   order by p.codigo;");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3]);
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function getCuentasContablesPlanillaMovilidad()
{

  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("
                  select p.id, concat(p.codigo,' | ',p.descripcion) descripcion,p.plan_contable_padre_id, exists(SELECT pp.id FROM $baseSGI.plan_contable pp where pp.estado = 1 and pp.plan_contable_padre_id = p.id) esPadre from $baseSGI.plan_contable p
                   -- inner join $baseSGI.cont_operacion_tipo op on op.id = " . ($isDua ? "2" : "3") . " and p.codigo  regexp  concat('^',replace(op.cuentas_relacionadas,',','|^'))
                   where p.empresa_id = 2 and p.estado = 1 and p.codigo in ('631122','631124')
                   order by p.codigo;");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getSUNATRetencion($fec)
{
  $arr = array(0, 0);

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT rete_tasa, rete_min_monto
		FROM `retenciones`
		WHERE rete_desde_fec<=?
		ORDER BY rete_desde_fec DESC
		LIMIT 1");
  $stmt->bind_param("s", $fec);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array($fila[0], $fila[1]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getLiqDetalle($id)
{
  $arr = array();
  //eld.eld_id
  include 'datos_abrir_bd.php';

  $mysqli->set_charset('utf8');
  $stmt = $mysqli->prepare("SELECT eld.conc_id, ec.conc_cod, eld.doc_id, eld.ruc_nro, CASE WHEN LENGTH(eld.ruc_nro)=8 THEN '' ELSE pr.prov_nom END AS prov_nom,
			IFNULL(pr.ruc_ret,0) ruc_ret, eld.lid_fec, eld.lid_ser, eld.lid_nro, UPPER(eld.lid_glo) lid_glo,
			eld.mon_id, eld.lid_afe, eld.lid_mon_afe, eld.lid_mon_naf, eld.lid_tc,
			eld.lid_retdet_apl, eld.lid_retdet_tip, eld.lid_retdet_mon, eld.lid_gti, eld.lid_dg_json,
			eld.lid_cta_cont, eld.lid_aprob, eld.lid_emp_asume, dt.doc_ruc_req, mo.mon_iso,
			dt.doc_nro, dt.doc_cod, ec.conc_acf, IFNULL(pr.ruc_act,-1) ruc_act, IFNULL(pr.ruc_hab,-1) ruc_hab,
			IFNULL(pr.prov_factura,-1) prov_factura, IFNULL(pr.prov_provincia,'ERROR') prov_provincia, dt.doc_tax_code,
			ec.conc_cve, eld.veh_id, eld.veh_km, UPPER(eld.lid_glo2) lid_glo2, ec.conc_fmt_glosa
                        ,ec.conc_nom,eld.eld_id,dt.sgi_documento_tipo_id,dt.doc_requiere_distribucion,eld.lid_deducible,
                        eld.lid_mon_otro,eld.documento_relacionado_id, eld.lid_mon_icbp,
                        eld.operacion_tipo_id, eld.monto_igv, eld.orden_trabajo_id, IFNULL((select concat(serie,'-' ,numero)as serie_numero from $baseSGI.documento where id=eld.orden_trabajo_id),'') as orden_trabajo
		FROM ear_liq_detalle eld
		LEFT JOIN ear_conceptos ec ON ec.conc_id=eld.conc_id
		LEFT JOIN proveedores pr ON pr.ruc_nro=eld.ruc_nro
		LEFT JOIN doc_tipos dt ON dt.doc_id=eld.doc_id
		LEFT JOIN monedas mo ON mo.mon_id=eld.mon_id
		WHERE ear_id=?
		ORDER BY conc_cod");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array(
      $fila[0], $fila[1], $fila[2], $fila[3], $fila[4],
      $fila[5], $fila[6], $fila[7], $fila[8], $fila[9],
      $fila[10], $fila[11], $fila[12], $fila[13], $fila[14],
      $fila[15], $fila[16], $fila[17], $fila[18], $fila[19],
      $fila[20], $fila[21], $fila[22], $fila[23], $fila[24],
      $fila[25], $fila[26], $fila[27], $fila[28], $fila[29],
      $fila[30], $fila[31], $fila[32],
      $fila[33], $fila[34], $fila[35], $fila[36], $fila[37],
      $fila[38], $fila[39], $fila[40], $fila[41], $fila[42], $fila[43],
      $fila[44], $fila[45], $fila[46], $fila[47], $fila[48], $fila[49]
    );
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getDistribucionDetalle($id)
{
  $arr = array();

  include 'datos_abrir_bd.php';
  include 'parametros.php';
  $mysqli->set_charset('utf8');
  $stmt = $mysqli->prepare("SELECT dc.id,dc.eld_id,dc.plan_contable_id,dc.centro_costo_id,dc.porcentaje,dc.monto,
                                          pc.codigo as plan_contable_codigo, cc.codigo as centro_costo_codigo
                                    FROM cont_distribucion_contable dc
                                    inner join ear_liq_detalle ed on ed.eld_id = dc.eld_id
                                    LEFT JOIN " . $baseSGI . ".plan_contable pc on pc.id = dc.plan_contable_id
                                    LEFT JOIN " . $baseSGI . ".centro_costo cc on cc.id = dc.centro_costo_id
                                    where ed.ear_id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5], $fila[6], $fila[7]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getLiqDetalleAsumidos($id)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $mysqli->set_charset('utf8');
  $stmt = $mysqli->prepare("SELECT eld.conc_id, ec.conc_cod, eld.doc_id, eld.ruc_nro, CASE WHEN LENGTH(eld.ruc_nro)=8 THEN '' ELSE pr.prov_nom END AS prov_nom,
			IFNULL(pr.ruc_ret,0) ruc_ret, eld.lid_fec, eld.lid_ser, eld.lid_nro, UPPER(eld.lid_glo) lid_glo,
			eld.mon_id, eld.lid_afe, eld.lid_mon_afe, eld.lid_mon_naf, eld.lid_tc,
			eld.lid_retdet_apl, eld.lid_retdet_tip, eld.lid_retdet_mon, eld.lid_gti, eld.lid_dg_json,
			eld.lid_cta_cont, eld.lid_aprob, eld.lid_emp_asume, dt.doc_ruc_req, mo.mon_iso,
			dt.doc_nro, dt.doc_cod, ec.conc_acf, IFNULL(pr.ruc_act,-1) ruc_act, IFNULL(pr.ruc_hab,-1) ruc_hab,
			IFNULL(pr.prov_factura,-1) prov_factura, IFNULL(pr.prov_provincia,'ERROR') prov_provincia, dt.doc_tax_code,
			ec.conc_cve, eld.veh_id, eld.veh_km, UPPER(eld.lid_glo2) lid_glo2, ec.conc_fmt_glosa
                        ,ec.conc_nom,eld.eld_id,dt.sgi_documento_tipo_id,eld.eld_id,eld.lid_deducible,dt.doc_requiere_distribucion, null as sgi_detraccion_id
                        ,eld.lid_mon_otro, eld.documento_relacionado_id, eld.lid_mon_icbp, eld.monto_igv
		FROM ear_liq_detalle eld
		LEFT JOIN ear_conceptos ec ON ec.conc_id=eld.conc_id
		LEFT JOIN proveedores pr ON pr.ruc_nro=eld.ruc_nro
		LEFT JOIN doc_tipos dt ON dt.doc_id=eld.doc_id
		LEFT JOIN monedas mo ON mo.mon_id=eld.mon_id
                LEFT JOIN detracciones d on d.detr_id = (SELECT dd.detr_id FROM detracciones dd
							 WHERE dd.conc_cod = ec.conc_cod
                                                         and dd.detr_desde_fec <= eld.lid_fec
                                                         ORDER BY dd.detr_desde_fec DESC limit 0,1)
		WHERE ear_id=?
                and eld.lid_aprob = 1
		ORDER BY conc_cod");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array(
      $fila[0], $fila[1], $fila[2], $fila[3], $fila[4],
      $fila[5], $fila[6], $fila[7], $fila[8], $fila[9],
      $fila[10], $fila[11], $fila[12], $fila[13], $fila[14],
      $fila[15], $fila[16], $fila[17], $fila[18], $fila[19],
      $fila[20], $fila[21], $fila[22], $fila[23], $fila[24],
      $fila[25], $fila[26], $fila[27], $fila[28], $fila[29],
      $fila[30], $fila[31], $fila[32],
      $fila[33], $fila[34], $fila[35], $fila[36], $fila[37],
      $fila[38], $fila[39], $fila[40], $fila[41], $fila[42], $fila[43], $fila[44], $fila[45], $fila[46], $fila[47], $fila[48]
    );
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getLiqDetalleGrupo($id)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $mysqli->set_charset('utf8');
  $stmt = $mysqli->prepare("SELECT eld.conc_id, ec.conc_cod, eld.doc_id, eld.ruc_nro, CASE WHEN LENGTH(eld.ruc_nro)=8 THEN '' ELSE pr.prov_nom END AS prov_nom,
			IFNULL(pr.ruc_ret,0) ruc_ret, eld.lid_fec, eld.lid_ser, eld.lid_nro, group_concat(UPPER(eld.lid_glo) separator ' - ') lid_glo,
			eld.mon_id, eld.lid_afe, sum(eld.lid_mon_afe) as lid_mon_afe, sum(eld.lid_mon_naf) as lid_mon_naf, eld.lid_tc,
			eld.lid_retdet_apl, eld.lid_retdet_tip, eld.lid_retdet_mon, eld.lid_gti, eld.lid_dg_json,
			eld.lid_cta_cont, eld.lid_aprob, sum(eld.lid_emp_asume) as lid_emp_asume, dt.doc_ruc_req, mo.mon_iso,
			dt.doc_nro, dt.doc_cod, ec.conc_acf, IFNULL(pr.ruc_act,-1) ruc_act, IFNULL(pr.ruc_hab,-1) ruc_hab,
			IFNULL(pr.prov_factura,-1) prov_factura, IFNULL(pr.prov_provincia,'ERROR') prov_provincia, dt.doc_tax_code,
			ec.conc_cve, eld.veh_id, eld.veh_km, UPPER(eld.lid_glo2) lid_glo2, ec.conc_fmt_glosa
                        ,group_concat(ec.conc_nom separator ' - ') as conc_nom,max(eld.eld_id) as eld_id,dt.sgi_documento_tipo_id,eld.eld_id,eld.lid_deducible,dt.doc_requiere_distribucion
                        ,null as sgi_detraccion_id, eld.lid_mon_otro, eld.documento_relacionado_id, eld.lid_mon_icbp,eld.operacion_tipo_id,eld.ear_id, eld.monto_igv
		FROM ear_liq_detalle eld
		LEFT JOIN ear_conceptos ec ON ec.conc_id=eld.conc_id
		LEFT JOIN proveedores pr ON pr.ruc_nro=eld.ruc_nro
		LEFT JOIN doc_tipos dt ON dt.doc_id=eld.doc_id
		LEFT JOIN monedas mo ON mo.mon_id=eld.mon_id
                LEFT JOIN detracciones d on d.detr_id = (SELECT dd.detr_id FROM detracciones dd
							 WHERE dd.conc_cod = ec.conc_cod
                                                         and dd.detr_desde_fec <= eld.lid_fec
                                                         ORDER BY dd.detr_desde_fec DESC limit 0,1)
		WHERE ear_id=?
        group by eld.eld_id, eld.doc_id,eld.ruc_nro,eld.lid_ser,eld.lid_nro
		ORDER BY conc_cod");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array(
      $fila[0], $fila[1], $fila[2], $fila[3], $fila[4],
      $fila[5], $fila[6], $fila[7], $fila[8], $fila[9],
      $fila[10], $fila[11], $fila[12], $fila[13], $fila[14],
      $fila[15], $fila[16], $fila[17], $fila[18], $fila[19],
      $fila[20], $fila[21], $fila[22], $fila[23], $fila[24],
      $fila[25], $fila[26], $fila[27], $fila[28], $fila[29],
      $fila[30], $fila[31], $fila[32],
      $fila[33], $fila[34], $fila[35], $fila[36], $fila[37],
      $fila[38], $fila[39], $fila[40], $fila[41], $fila[42], $fila[43], $fila[44], $fila[45], $fila[46], $fila[47], $fila[48], $fila[49], $fila[50]
    );
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getConcTemplate($mon_id_sel, $conc_cod, $conc_id, $ret_tasa, $ret_minmonto, $det_tasa, $det_minmonto, $cve, $unique_id)
{
  $unique_id = "S" . $unique_id;
  $html = "\t\t<select class='conc_l' id='conc_l[$unique_id]' name='conc_l[$unique_id]'>\n";

  $html .= "\t\t\t<option value='-100' conc_id='-100' cve='-100'>Seleccione</option>\n";
  $arr = getLiqConceptos($mon_id_sel, $conc_cod);

  foreach ($arr as $v) {
    $html .= "\t\t\t<option value='$v[0]' conc_id='$v[3]'" . ($v[3] == $conc_id ? " selected" : "") . " cve='$v[4]'>$v[1]</option>\n";
  }

  $html .= "\t\t</select>\n";
  $html .= "\t\t<input type='hidden' class='conc_id_inp' name='conc_id[$unique_id]' value='$conc_id'>\n";
  $html .= "\t\t<input type='hidden' class='cve_inp' name='cve[$unique_id]' value='$cve'>\n";
  $html .= "\t\t<input type='hidden' class='ret_tasa_inp' name='ret_tasa[$unique_id]' value='$ret_tasa'>\n";
  $html .= "\t\t<input type='hidden' class='ret_min_monto_inp' name='ret_min_monto[$unique_id]' value='$ret_minmonto'>\n";
  $html .= "\t\t<input type='hidden' class='det_tasa_inp' name='det_tasa[$unique_id]' value='$det_tasa'>\n";
  $html .= "\t\t<input type='hidden' class='det_min_monto_inp' name='det_min_monto[$unique_id]' value='$det_minmonto'>\n";

  return $html;
}

function getTipoDocTemplate($doc_id, $unique_id, $pre = '')
{
  if (strlen($pre) == 0) {
    $unique_id = "S" . $unique_id;
  } else {
    $unique_id = "U" . $unique_id;
  }
  $html = "\t\t<select class='" . $pre . "tipo_doc' id='" . $pre . "tipo_doc[$unique_id]' name='" . $pre . "tipo_doc[$unique_id]'>\n";
  if ($doc_id == 20 || $doc_id == 18 || $doc_id == 19) {
    $arr = getTipoDocNota();
  } else {
    $arr = getTipoDoc();
  }
  foreach ($arr as $v) {
    $html .= "\t\t\t<option value='$v[0]' rucreq='$v[2]' aplret='$v[3]' apldet='$v[4]' taxcode='$v[8]'  apldistri='$v[12]' sgiDocumentoId='$v[13]'  aplvalidacion='$v[14]' aplvalidacionsgi='$v[15]' " . ($v[0] == $doc_id ? " selected" : "") . ">$v[1]</option>\n";
  }

  $html .= "\t\t</select>\n";

  $html .= "\t\t<img src='img/modal.gif' class='pers_cla' title='Abrir selector de personal' style='display: none;'>\n";

  return $html;
}

function getProvNomTemplate($prov_nom, $ruc_ret, $ruc_act, $ruc_hab, $unique_id)
{
  if (strlen($prov_nom) > 0) {
    if ($ruc_act == 0) {
      $prov_nom = "<font color='red'><i>ERROR: RUC de " . $prov_nom . " no esta ACTIVO. El tipo de documento debe ser Otros.</i></font>";
    } else if ($ruc_hab == 0) {
      $prov_nom .= " <img src='img/alert.png' title='RUC NO HABIDO' class='iconos'>";
    }
  } else {
    //		$prov_nom = "<font color='red'><i>ERROR: RUC no existe. Verificar o cambiar el tipo de documento a Recibo de gastos.</i></font>";
    $prov_nom = '';
  }

  $unique_id = "S" . $unique_id;
  $html = "\t\t<div class='prov_nom_i' id='prov_nom[$unique_id]' name='prov_nom[$unique_id]'>$prov_nom</div>\n";
  $html .= "\t\t<input type='hidden' class='prov_ret' value='$ruc_ret'>\n";
  $html .= "\t\t<input type='hidden' class='prov_act' value='$ruc_act'>\n";

  return $html;
}

function getDetDocTemplate($lid_glo, $conc_cve, $veh_id, $veh_km, $lid_glo2, $unique_id)
{
  $unique_id = "S" . $unique_id;
  $html = "\t\t<span class='peaje_span'" . (($conc_cve == 2) ? "" : " style='display: none;'") . ">Peaje: <input type='text' value='$lid_glo2' size='10' maxlength='100' class='peaje_inp' name='peaje[$unique_id]'></span>\n";
  $html .= "\t\t<select class='veh_l' id='veh_l[$unique_id]' name='veh_l[$unique_id]'" . (($conc_cve == 0 || $conc_cve == 4) ? " style='display: none;'" : "") . ">\n";

  $arr = getVehiculosActivosLista();

  foreach ($arr as $v) {
    $html .= "\t\t\t<option value='$v[0]'" . ($veh_id == $v[0] ? ' selected' : '') . ">$v[1]</option>\n";
  }

  $html .= "\t\t\t<option value='-1'" . ((($conc_cve >= 1 && $conc_cve <= 3) && $veh_id == null) ? " selected" : "") . ">Otros</option>\n";
  $html .= "\t\t</select>\n";

  if ($conc_cve == 0 || ($conc_cve == 1 && $veh_id == null) || ($conc_cve >= 2 && $conc_cve <= 4)) {
    $km_hide = 1;
  } else {
    $km_hide = 0;
  }
  $html .= "\t\t<span class='km_span'" . ($km_hide == 1 ? " style='display: none;'" : "") . "><input type='text' value='$veh_km' size='10' maxlength='10' class='km_inp' name='km[$unique_id]'> km</span>\n";

  $html .= "\t\t<input type='text' value='$lid_glo' size='14' maxlength='200' class='det_doc_inp' name='det_doc[$unique_id]'" . ((($conc_cve == 1 || $conc_cve == 2) && $veh_id != null) ? " style='display: none;'" : "") . ">\n";

  return $html;
}

function getTipoMonTemplate($mon_id_sel, $unique_id)
{
  $unique_id = "S" . $unique_id;
  $html = "\t\t<select class='tipo_mon' id='tipo_mon[$unique_id]' name='tipo_mon[$unique_id]' >\n";

  $arr = getTipoMon();

  foreach ($arr as $v) {
    $html .= "\t\t\t<option value='$v[0]'" . ($mon_id_sel == $v[0] ? " selected" : "") . ">$v[1]</option>\n";
  }

  $html .= "\t\t</select>\n";

  return $html;
}

function getAfectoSelTemplate($afe_sel, $mon_id_sel, $mon_id, $afecto, $noafecto, $montOtro, $montIcbp, $tc, $unique_id)
{
  $unique_id = "S" . $unique_id;
  $html = "\t<td class='afecto_sel_td'>\n";
  $html .= "\t\t<select id='afecto_sel[$unique_id]' class='afecto_sel' name='afecto_sel[$unique_id]'>\n";
  $html .= "\t\t\t<option value='1'" . ($afe_sel == 1 ? " selected" : "") . ">Si 18%</option>\n";
  $html .= "\t\t\t<option value='2'" . ($afe_sel == 2 ? " selected" : "") . ">Si 10%</option>\n";
  $html .= "\t\t\t<option value='3'" . ($afe_sel == 3 ? " selected" : "") . ">No</option>\n";
  $html .= "\t\t\t<option value='4'" . ($afe_sel == 4 ? " selected" : "") . ">Mixto 18%</option>\n";
  $html .= "\t\t\t<option value='5'" . ($afe_sel == 5 ? " selected" : "") . ">Mixto 10%</option>\n";
  $html .= "\t\t</select>\n";
  $html .= "\t</td>\n";

  $conv_afe_hide = 1;
  $conv_naf_hide = 1;
  $conv_otro_hide = 1;
  $conv_icbp_hide = 1;

  switch ($afe_sel) {
    case 1:
    case 2:
      $afe_hide = 0;
      $naf_hide = 1;
      // prueba
      $montOtro_hide = 0;
      $montIcbp_hide = 0;

      if ($mon_id_sel != $mon_id) {
        $conv_afe_hide = 0;
        $conv_otro_hide = 1;
        $conv_icbp_hide = 1;
      }
      break;
    case 3:
    case 6:
      $afe_hide = 1;
      $naf_hide = 0;
      //prueba
      $montOtro_hide = 0;
      $montIcbp_hide = 0;
      if ($mon_id_sel != $mon_id) {
        $conv_naf_hide = 0;
        $conv_otro_hide = 0;
        $conv_icbp_hide = 0;
      }
      break;
    case 4:
    case 5:
      $afe_hide = 0;
      $naf_hide = 0;
      $montOtro_hide = 0;
      $montIcbp_hide = 0;
      if ($mon_id_sel != $mon_id) {
        $conv_afe_hide = 0;
        $conv_naf_hide = 0;
        $conv_otro_hide = 0;
        $conv_icbp_hide = 0;
      }
      break;
  }

  $tc_div = "";
  if ($mon_id_sel == $mon_id) {
    $conv_afecto = $afecto;
    $conv_noafecto = $noafecto;
    $conv_otro = $montOtro;
    $conv_icbp = $montIcbp;
  } else {
    if ($tc > 0)
      $tc_div = $tc;
    if ($mon_id_sel == 2 && $mon_id == 1) {
      $conv_afecto = number_format($afecto * $tc, 2, '.', '');
      $conv_noafecto = number_format($noafecto * $tc, 2, '.', '');
      $conv_otro = number_format($montOtro * $tc, 2, '.', '');
      $conv_icbp = number_format($montIcbp * $tc, 2, '.', '');
    } else if ($mon_id_sel == 1 && $mon_id == 2) {
      $conv_afecto = number_format($afecto / $tc, 2, '.', '');
      $conv_noafecto = number_format($noafecto / $tc, 2, '.', '');
      $conv_otro = number_format($montOtro / $tc, 2, '.', '');
      $conv_icbp = number_format($montIcbp / $tc, 2, '.', '');
    }
  }

  $html .= "\t<td class='afecto_td'><input type='text' value='$afecto' size='8' maxlength='9' id='afecto_inp[$unique_id]' class='afecto_inp' name='afecto_inp[$unique_id]'" . ($afe_hide == 1 ? " style='display: none;'" : "") . "></td>\n"; //Monto Afecto
  $html .= "\t<td class='noafecto_td'><input type='text' value='$noafecto' size='8' maxlength='9' id='noafecto_inp[$unique_id]' class='noafecto_inp' name='noafecto_inp[$unique_id]'" . ($naf_hide == 1 ? " style='display: none;'" : "") . "></td>\n"; //Monto NoAfecto
  $html .= "\t<td class='montOtro_td'><input type='text' value='$montOtro' size='8' maxlength='9' id='montOtro_inp[$unique_id]' class='montOtro_inp' name='montOtro_inp[$unique_id]'" . ($montOtro_hide == 1 ? " style='display: none;'" : "") . "></td>\n"; //Monto Otro
  $html .= "\t<td class='montIcbp_td'><input type='text' value='$montIcbp' size='8' maxlength='9' id='montIcbp_inp[$unique_id]' class='montIcbp_inp' name='montIcbp_inp[$unique_id]'" . ($montIcbp_hide == 1 ? " style='display: none;'" : "") . "></td>\n"; //Monto ICBP
  $html .= "\t<td class='tc_td' ><div class='tc_div' style='display: none;'>$tc_div</div><input size='6' maxlength='9' class='tc_inp' name='tc_inp[$unique_id]' value='$tc'></td>\n"; //T/C;
  $html .= "\t<td class='conv_afecto_td' ><input type='text' value='$conv_afecto' size='8' maxlength='9' id='conv_afecto_inp[$unique_id]' class='conv_afecto_inp' name='conv_afecto_inp[$unique_id]' readonly" . ($conv_afe_hide == 1 ? " style='display: none;'" : "") . "></td>\n"; //Conversion Afecto
  $html .= "\t<td class='conv_noafecto_td' ><input type='text' value='$conv_noafecto' size='8' maxlength='9' id='conv_noafecto_inp[$unique_id]' class='conv_noafecto_inp' name='conv_noafecto_inp[$unique_id]' readonly" . ($conv_naf_hide == 1 ? " style='display: none;'" : "") . "></td>\n"; //Conversion NoAfecto
  $html .= "\t<td class='conv_otro_td' ><input type='text' value='$conv_otro' size='8' maxlength='9' id='conv_otro_inp[$unique_id]' class='conv_otro_inp' name='conv_otro_inp[$unique_id]' readonly" . ($conv_otro_hide == 1 ? " style='display: none;'" : "") . "></td>\n"; //Conversion Otro
  $html .= "\t<td class='conv_icbp_td' ><input type='text' value='$conv_icbp' size='8' maxlength='9' id='conv_icbp_inp[$unique_id]' class='conv_icbp_inp' name='conv_icbp_inp[$unique_id]' readonly" . ($conv_icbp_hide == 1 ? " style='display: none;'" : "") . "></td>\n"; //Conversion ICBP

  switch ($afe_sel) {
    case 1:
    case 4:
      $html .= "\t<td class='monto_igv_td' hidden><input type='hidden' value='18' id='monto_igv[$unique_id]' name='monto_igv[$unique_id]' class='monto_igv' readonly></td>\n"; // Monto IGV
      break;
    case 2:
    case 5:
      $html .= "\t<td class='monto_igv_td' hidden><input type='hidden' value='10' id='monto_igv[$unique_id]' name='monto_igv[$unique_id]' class='monto_igv'  readonly></td>\n"; // Monto IGV
      break;
    case 3:
      $html .= "\t<td class='monto_igv_td' hidden><input type='hidden' value='0' id='monto_igv[$unique_id]' name='monto_igv[$unique_id]' class='monto_igv'  readonly></td>\n"; // Monto IGV
      break;
  }

  return $html;
}

function getAplicRetDetTemplate($aplic_retdet, $doc_ruc_req, $ruc_ret, $det_tasa, $unique_id)
{
  $unique_id = "S" . $unique_id;
  $html = "\t<td class='aplic_retdet_td'>\n";
  $html .= "\t\t<select class='aplic_retdet' name='aplic_retdet[$unique_id]'" . (($doc_ruc_req != 1 || ($ruc_ret == 1 && $det_tasa == 0)) ? " style='display: none;'" : "") . ">\n";
  $html .= "\t\t\t<option value='1'" . ($aplic_retdet == 1 ? " selected" : "") . ">Si</option>\n";
  $html .= "\t\t\t<option value='0'" . ($aplic_retdet == 0 ? " selected" : "") . ">No</option>\n";
  $html .= "\t\t</select>\n";
  $html .= "\t</td>\n";

  return $html;
}

function getRetDetTemplate($retdet_tip, $retdet_monto, $mon_id_sel, $mon_iso, $mon_id, $tc, $doc_ruc_req, $fec_doc, $ruc_ret, $unique_id)
{
  $unique_id = "S" . $unique_id;
  $retdet_div = "Exonerado";

  if ($doc_ruc_req == 1 && $tc > 0) {
    $otra_moneda = '';
    if ($mon_id_sel == 2 && $mon_id == 1) {
      $otra_moneda = " (" . number_format($retdet_monto * $tc, 2, '.', '') . " PEN)";
    } else if ($mon_id_sel == 1 && $mon_id == 2) {
      $otra_moneda = " (" . number_format($retdet_monto / $tc, 2, '.', '') . " USD)";
    }
    if ($retdet_tip == 1) {
      $retdet_div = "Aplica detraccion de $retdet_monto $mon_iso$otra_moneda";
    } else if ($retdet_tip == 2 && $ruc_ret == 0) {
      $retdet_div = "Aplica retencion de $retdet_monto $mon_iso$otra_moneda";
    }
  }
  if (strlen($fec_doc) == 0) {
    $retdet_div = "Falta fecha";
  }


  $html = "\t<td class='retdet_td'>\n";
  $html .= "\t\t<div class='retdet_div'>$retdet_div</div>\n";
  $html .= "\t\t<input type='hidden' class='retdet_tip' name='retdet_tip[$unique_id]' value='$retdet_tip'>\n";
  $html .= "\t\t<input type='hidden' class='retdet_monto' name='retdet_monto[$unique_id]' value='$retdet_monto'>\n";
  $html .= "\t</td>\n";

  return $html;
}

function getDistGastTemplate($gti_id, $dist_gast_json, $unique_id, $mode = 0)
{
  if ($mode == 0) {
    $unique_id = "S" . $unique_id;
  }

  if (!is_null($dist_gast_json)) {
    $arr_dg = json_decode(utf8_encode($dist_gast_json));
    $gast_info_tooltip = "";
    foreach ($arr_dg as $v) {
      $arr_gast_info_tooltip[] = utf8_decode($v[0]) . " ($v[2]%)";
    }
    $gast_info_tooltip = implode("&#013;", $arr_gast_info_tooltip);
  } else {
    $gast_info_tooltip = 'N/A';
  }

  switch ($gti_id) {
    case 1:
      $gast_img = 'img/persona.png';
      $gast_img_tooltip = 'Personas';
      break;
    case 2:
      $gast_img = 'img/centro-costo.png';
      $gast_img_tooltip = 'Centro de Costos';
      break;
    case 3:
      $gast_img = 'img/wbs.png';
      $gast_img_tooltip = 'Proyectos WBS';
      break;
    case 4:
      $gast_img = 'img/internal-order.png';
      $gast_img_tooltip = 'Internal Order';
      break;
    default:
      $gast_img = 'img/error.png';
      $gast_img_tooltip = 'ERROR';
  }

  $html = "\t<td>\n";
  $html .= "\t\t<img src='img/modal.gif' class='modal' title='Abrir Distribuci&oacute;n de Gastos'>\n";
  $html .= "\t\t<img src='$gast_img' class='dist_gast_tipo' title='$gast_img_tooltip'>\n";
  $html .= "\t\t<img src='img/info.gif' class='dist_gast_info' title='$gast_info_tooltip'>\n";
  $html .= "\t\t<input type='hidden' class='gti_id_i' id='gti_id[$unique_id]' name='gti_id[$unique_id]' value='$gti_id'>\n";
  $html .= "\t\t<input type='hidden' class='dist_gast_json_i' id='dist_gast_json[$unique_id]' name='dist_gast_json[$unique_id]' value='$dist_gast_json'>\n";
  $html .= "\t</td>\n";

  return $html;
}

function getFilasPrevias($arrLiqDet, $mon_id, $conc_cod, $mode_id, $dele = 1, $arrDistribucionDetalle = NULL, $bandera_distribucion = NULL)
{
  /*
      Valores para $mode_id:
      1: Liquidacion registro
      2: Liquidacion revision
     */
  $html = '';
  foreach ($arrLiqDet as $k => $v) {
    /*
          Valores de $v[*] : Filas registradas :
          0  = conc_id
          1  = conc_cod
          2  = doc_id
          3  = ruc_nro
          4  = prov_nom
          5  = ruc_ret (ruc agente retencion)
          6  = lid_fec
          7  = lid_ser
          8  = lid_nro
          9  = lid_glo
          10 = mon_id
          11 = lid_afe
          12 = lid_mon_afe
          13 = lid_mon_naf
          14 = lid_tc
          15 = lid_retdet_apl
          16 = lid_retdet_tip
          17 = lid_retdet_mon
          18 = lid_gti
          19 = lid_dg_json
          20 = lid_cta_cont
          21 = lid_aprob
          22 = lid_emp_asume
          23 = doc_ruc_req
          24 = mon_iso
          25 = doc_nro
          26 = doc_cod
          27 = conc_acf (activo fijo)
          28 = ruc_act (ruc activo)
          29 = ruc_hab (ruc habido; hallado)
          30 = prov_factura
          31 = prov_provincia
          32 = doc_tax_code
          33 = conc_cve
          34 = veh_id
          35 = veh_km
          36 = lid_glo2 (por el momento solo guarda nombre de peajes)
          43 = lid_mon_otro
          44 = documento_relacionado_id
          45 = lid_mon_icbp
         */

    if (startsWith($v[1], $conc_cod)) {
      $fec_doc = "";
      if (strlen($v[6]) == 10 && !startsWith($v[6], "0000")) {
        $pzas = explode("-", $v[6]);
        $fec_doc = $pzas[2] . "/" . $pzas[1] . "/" . $pzas[0];
      }

      list($ret_tasa, $ret_minmonto, $det_tasa, $det_minmonto) = getLiqConceptosRetDet($v[0], $v[6]);
      $orden_t =  "&nbsp;<input type='hidden' value='".$v[48]."'  id='lid_orden_trabajo[S$k]' name='lid_orden_trabajo[S$k]' /><label id='des_orden_trabajo[S$k]' name='des_orden_trabajo[S$k]'>".$v[49]."</label>&nbsp;<img class='orden_trabajo' src='img/plus.png'  onclick=\"abrirModalOrdenTrabajo('[S$k]')\" title='Agregar Orden de Trabajo'>&nbsp;";

      $html .= "<tr class='fila_dato'>\n";
      //                        $html .="\t<td class='bandera' hidden>\n\t\t<input type='text' value='$v[44]' size='4' maxlength='5' class='bandera' name='bandera[S$k]'>\n\t</td>\n";
      //			$html .= "\t<td class='conc_td'>\n".getConcTemplate($v[10], $conc_cod, $v[0], $ret_tasa, $ret_minmonto, $det_tasa, $det_minmonto, $v[33], $k)."\t</td>\n";
      $html .= "\t<td class='conc_td'>\n" . getConcTemplate($mon_id, $conc_cod, $v[0], $ret_tasa, $ret_minmonto, $det_tasa, $det_minmonto, $v[33], $k) . "\t</td>\n";
      $html .= "\t<td class='tipo_doc_td'>\n" . getTipoDocTemplate($v[2], $k) . "\t</td>\n";
      $html .= "\t<td class='orden_t' style='white-space: nowrap;'>\n".$orden_t ."\t</td>\n"; //Orden trabajo
      $html .= "\t<td class='ruc_nro_td'>\n\t\t<input type='text' class='ruc_nro_i' size='13' maxlength='11' id='ruc_nro[S$k]' name='ruc_nro[S$k]' value='$v[3]'>\n\t</td>\n";
      $html .= "\t<td class='prov_nom_td'>\n" . getProvNomTemplate($v[4], $v[5], $v[28], $v[29], $k) . "\t</td>\n";
      $html .= "\t<td class='fec_doc_td'>\n\t\t<input type='text' value='$fec_doc' size='11' maxlength='10' class='fecha_inp' readonly name='fec_doc[S$k]'>\n\t</td>\n";
      $html .= "\t<td class='ser_doc_td'>\n\t\t<input type='text' value='$v[7]' size='6' maxlength='5' class='ser_doc_inp' name='ser_doc[S$k]'>\n\t</td>\n";

      //NUMERO DE CARACTERES MAXIMOS PARA TICKET DE DEPOSITO: 15
      //                        $numMax=7;
      //                        if($v[2]==11){
      $numMax = 15;
      //                        }

      $html .= "\t<td class='num_doc_td'>\n\t\t<input type='text' value='$v[8]' size='9' maxlength='$numMax' class='num_doc_inp' name='num_doc[S$k]'>\n\t</td>\n";
      $html .= "\t<td class='det_doc_td'>\n" . getDetDocTemplate($v[9], $v[33], $v[34], $v[35], $v[36], $k) . "\t</td>\n";
      $html .= "\t<td class='tipo_mon_td'>\n" . getTipoMonTemplate($v[10], $k) . "\t</td>\n";
      $html .= getAfectoSelTemplate($v[11], $v[10], $mon_id, $v[12], $v[13], $v[43], $v[45], $v[14], $k);
      $html .= getAplicRetDetTemplate($v[15], $v[23], $v[5], $det_tasa, $k);
      $html .= getRetDetTemplate($v[16], $v[17], $v[10], $v[24], $mon_id, $v[14], $v[23], $fec_doc, $v[5], $k);
      //			$html .= getDistGastTemplate($v[18], $v[19], $k);

      if ($mode_id == 2) { //liquidacion revision
        $html .= getAprobGastAsumTemplate($v[21], $v[22], $k);
      }
      if ($mode_id == 3) {
        $html .= getAprobGastAsumTemplate($v[21], $v[22], $k);
        $html .= getDocumentoDeducible((!isEmpty($v[42]) ? $v[42] : $v[41]), $k);
      }

      $dataDistribucion = array();
      foreach ($arrDistribucionDetalle as $indice => $item) {
        if ($item[1] == $v[39]) {
          $objeto = NULL;
          $objeto->tipo = $conc_cod;
          $objeto->fila = "[S$k]";
          $objeto->cuenta_contable = $item[2];
          $objeto->centro_costo = $item[3];
          $objeto->porcentaje = $item[4];
          $objeto->monto = $item[5];
          array_push($dataDistribucion, $objeto);
        }
      }
      if ($dele == 1 || $mode_id == 3) {
        $html .= "\t<td>";
        //Opción para generar notas de crédito/débito/detracción.
        if ($v[2] == 2 || $v[2] == 4) {
          $html .= "<img src='img/n.png' class='tipo_nota'  title='NC|ND|DETRACCION' tbodyId='" . $conc_cod . "'>&nbsp;";
        } elseif ($v[2] == 20 || $v[2] == 18 || $v[2] == 19) {
          $html .= "<input type='hidden' value='$v[44]' id='documento_relacion[S$k]' name='documento_relacion[S$k]'/>";
        }


        //Opción para generar la distribución contable.
        //if($bandera_distribucion  != '' || $bandera_distribucion != null){
          $html .= "<input type='hidden' value='" . (!isEmpty($dataDistribucion) ? json_encode($dataDistribucion) : "") . "' id='lid_distribucion[S$k]' name='lid_distribucion[S$k]' /><input type='hidden' value='" . (!isEmpty($v[46]) ? $v[46] : "") . "'  id='lid_operacion_tipo[S$k]' name='lid_operacion_tipo[S$k]' /><img  id='op_distribucion[S$k]' name='op_distribucion[S$k]' class='op_distribucion' src='img/plus.png' onclick='abrirModalDistribucion(\"[S$k]\",\"" . $conc_cod . "\")' " . ($v[41] == "1" ? "" : "hidden") . "  title='Agregar distribucion contable'>&nbsp;";
        //}
        $html .= "<img src='img/delete.png' class='dele' title='Borrar'>";
      } else {
        $html .= "\t<td></td>\n";
      }


      $html .= "</td></tr>\n";
    }
  }

  return $html;
}

function getDocumentoDeducible($apr_sel, $unique_id)
{
  $unique_id = "S" . $unique_id;
  $html = "\t<td class='doc_deducible_td'>\n";
  $html .= "\t\t<select id='doc_deducible[$unique_id]' class='doc_deducible' name='doc_deducible[$unique_id]'>\n";
  $html .= "\t\t\t<option value='1'" . ($apr_sel == 1 ? " selected" : "") . ">Si</option>\n";
  $html .= "\t\t\t<option value='0'" . ($apr_sel == 0 ? " selected" : "") . ">No</option>\n";
  $html .= "\t\t</select>\n";
  $html .= "\t</td>\n";
  return $html;
}

function getAprobGastAsumTemplate($apr_sel, $gasta_sum, $unique_id)
{
  $unique_id = "S" . $unique_id;
  $html = "\t<td class='aprob_sel_td'>\n";
  $html .= "\t\t<select id='aprob_sel[$unique_id]' class='aprob_sel' name='aprob_sel[$unique_id]'>\n";
  $html .= "\t\t\t<option value='1'" . ($apr_sel == 1 ? " selected" : "") . ">Si</option>\n";
  $html .= "\t\t\t<option value='0'" . ($apr_sel == 0 ? " selected" : "") . ">No</option>\n";
  $html .= "\t\t</select>\n";
  $html .= "\t</td>\n";
  $html .= "\t<td class='gast_asum_td'>\n";
  $html .= "\t\t<input type='text' value='$gasta_sum' size='8' maxlength='9' class='gast_asum_i' name='gast_asum[$unique_id]'" . ($apr_sel == 0 ? " style='display: none;'" : "") . ">\n";
  $html .= "\t</td>\n";

  return $html;
}

function getValorSistema($id)
{
  $monto = "";

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT val_val FROM valores_sistema WHERE val_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $monto = $fila[0];
  }

  include 'datos_cerrar_bd.php';

  return $monto;
}

function getLiqDesembolsadas($id)
{
  $arr = array();
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $stmt = $mysqli->prepare("SELECT e.ear_id,
                    CONCAT(e.ear_anio, '-', LPAD(e.ear_mes, 2, '0'), '-', LPAD(e.ear_nro, 3, '0'), '/',
                    fn_obtener_iniciales(concat(ifnull(sp.nombre,''),' ',ifnull(sp.apellido_paterno,''),' ',ifnull(sp.apellido_materno,''))))  as ear_numero
                FROM ear_solicitudes e
                    LEFT JOIN monedas m ON m.mon_id=e.mon_id
                    inner join " . $baseSGI . ".persona sp on sp.id=e.usu_id
		WHERE e.usu_id=? AND e.est_id=4 AND e.pla_id IS NULL
		ORDER BY ear_id DESC");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1]);
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function getPlanillasMovilidad($cons_id, $usu_id, $otro_jefe_usu_id = null)
{
  $arr = array();
  $q_joins = "";
  $q_cons = "";
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  // Filtro de acuerdo al tipo de consulta
  if ($cons_id == 1) {
    $q_cons = "AND p.usu_id=$usu_id";
  } else if ($cons_id == 2) {
    $q_joins = "LEFT JOIN recursos.usuarios ru ON ru.usu_id=e.ear_act_usu";
    // $usu_ad = getUsuAd($usu_id);
    // $q_cons = "AND ( ru2.usu_jefe='$usu_ad' OR ru2.usu_gerente='$usu_ad' )";
    if (is_null($otro_jefe_usu_id)) {
      $q_cons = "AND ( ud.usu_jefe=$usu_id )";
    } else {
      $q_cons = "AND ( ud.usu_jefe=$otro_jefe_usu_id )";
    }
  } else if ($cons_id == 4) {
    $q_cons = "AND (e.master_usu_id=$usu_id OR p.usu_id IN (" . implode(",", getUsuRegOtroSlavesIds($usu_id)) . "))";
  }

  $query = "SELECT p.pla_id, CONCAT(p.pla_serie, '-', LPAD(p.pla_nro, 7, '0')) pla_numero, p.pla_monto, te.est_nom, p.pla_reg_fec,
		CASE
			WHEN p.ear_id IS NOT NULL THEN
				CONCAT(e.ear_anio, '-', LPAD(e.ear_mes, 2, '0'), '-', LPAD(e.ear_nro, 3, '0'), '/',
                fn_obtener_iniciales(concat(ifnull(sp.nombre,''),' ',ifnull(sp.apellido_paterno,''),' ',ifnull(sp.apellido_materno,'')))
                )
			-- WHEN p.cch_id IS NOT NULL AND p.ccl_id IS NULL THEN
				-- cch.cch_nombre
			-- WHEN p.cch_id IS NOT NULL AND p.ccl_id IS NOT NULL THEN
				-- CONCAT(ccl.ccl_anio, '-', LPAD(ccl.ccl_mes, 2, '0'), '-', LPAD(ccl.ccl_nro, 3, '0'), '/', cch.cch_abrv)
			ELSE
				null
		END ear_numero,
		m.mon_nom, m.mon_iso, m.mon_img, p.est_id,
		CASE
			WHEN p.ear_id IS NOT NULL THEN
				'EAR'
			WHEN p.cch_id IS NOT NULL THEN
				'CCH'
			ELSE
				null
		END pla_tipo,
		concat(sp.nombre,' ',sp.apellido_paterno,' ',sp.apellido_materno) as usu_nombre
	FROM pla_mov p
	LEFT JOIN tablas_estados te ON te.tabla_id=2 AND te.est_id=p.est_id
	JOIN tablas_nombres tn ON tn.tabla_id=te.tabla_id AND tabla_nom='pla_mov'
	LEFT JOIN ear_solicitudes e ON e.ear_id=p.ear_id
	LEFT JOIN monedas m ON m.mon_id=p.mon_id
	inner join " . $baseSGI . ".persona sp on sp.id=e.usu_id
	LEFT JOIN cajas_chicas cch ON cch.cch_id=p.cch_id
	LEFT JOIN cajas_chicas_lote ccl ON ccl.ccl_id=p.ccl_id
	$q_joins
	WHERE 0=0 $q_cons
	LIMIT 2000";
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($fila = $result->fetch_array()) {
    $arr[] = array(
      "pla_id" => $fila[0],
      "pla_numero" => $fila[1],
      "pla_monto" => $fila[2],
      "est_nom" => $fila[3],
      "pla_reg_fec" => $fila[4],
      "ear_numero" => $fila[5],
      "mon_nom" => $fila[6],
      "mon_iso" => $fila[7],
      "mon_img" => $fila[8],
      "est_id" => $fila[9],
      "pla_tipo" => $fila[10],
      "usu_nombre" => $fila[11]
    );
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function getPlanillaMovilidadInfo($id)
{
  $arr = array();
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $mysqli->set_charset('utf8');
  $stmt = $mysqli->prepare("SELECT CONCAT(LPAD(p.pla_serie, 5, '0'), '-', LPAD(p.pla_nro, 7, '0')) pla_numero, p.est_id, p.pla_reg_fec,
			CASE
				WHEN p.ear_id IS NOT NULL THEN
                    CONCAT(e.ear_anio, '-', LPAD(e.ear_mes, 2, '0'), '-', LPAD(e.ear_nro, 3, '0'), '/',
					CAST(fn_obtener_iniciales(concat(ifnull(sp.nombre,''),' ',ifnull(sp.apellido_paterno,''),' ',ifnull(sp.apellido_materno,'')))
                    AS CHAR CHARACTER SET utf8))
				WHEN p.cch_id IS NOT NULL AND p.ccl_id IS NULL THEN
					cch.cch_nombre
				WHEN p.cch_id IS NOT NULL AND p.ccl_id IS NOT NULL THEN
					CONCAT(ccl.ccl_anio, '-', LPAD(ccl.ccl_mes, 2, '0'), '-', LPAD(ccl.ccl_nro, 3, '0'), '/', cch.cch_abrv)
				ELSE
					null
			END ear_numero,
			pla_tope, p.usu_id, p.ear_id, te.est_nom, p.pla_monto, p.pla_gti, p.pla_dg_json,
			p.pla_env_fec, p.pla_exc, p.pla_com1, p.pla_com2, p.pla_com3,
			CASE
				WHEN p.ear_id IS NOT NULL THEN
					'EAR'
				WHEN p.cch_id IS NOT NULL THEN
					'CCH'
				ELSE
					null
			END pla_tipo,
			p.ccl_id, p.cch_id,
      p.orden_trabajo_id,
      CONCAT(sd.serie,'-',sd.numero) as ear_ord_trabajo
		FROM pla_mov p
		LEFT JOIN tablas_estados te ON te.tabla_id=2 AND te.est_id=p.est_id
		LEFT JOIN ear_solicitudes e ON e.ear_id=p.ear_id
		inner join " . $baseSGI . ".persona sp on sp.id=p.usu_id
    LEFT JOIN " . $baseSGI . ".documento sd on sd.id=p.orden_trabajo_id
		LEFT JOIN cajas_chicas cch ON cch.cch_id=p.cch_id
		LEFT JOIN cajas_chicas_lote ccl ON ccl.ccl_id=p.ccl_id
		WHERE p.pla_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array(
      $fila[0], $fila[1], $fila[2],
      $fila[3],
      $fila[4], $fila[5], $fila[6], $fila[7], $fila[8], $fila[9], $fila[10],
      $fila[11], $fila[12], $fila[13], $fila[14], $fila[15], $fila[16],
      $fila[17], $fila[18], $fila[19], $fila[20]
    );
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function getPlanillaMovDetalle($id)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT UPPER(pmd_motivo) pmd_motivo, pmd_fec, UPPER(pmd_salida) pmd_salida, UPPER(pmd_destino) pmd_destino, pmd_monto, pmd_aprob, pmd_emp_asume, pmd_monto-pmd_emp_asume pmd_no_asume,pla_det_id
		FROM pla_mov_detalle
		WHERE pla_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5], $fila[6], $fila[7], $fila[8]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getPlanillaMovDistribucionContable($id)
{
  $arr = array();
  include 'datos_abrir_bd.php';
  include 'parametros.php';
  $stmt = $mysqli->prepare(" SELECT dc.id,dc.pla_det_id,dc.plan_contable_id,dc.centro_costo_id,dc.porcentaje,dc.monto,
                                           pc.codigo as plan_contable_codigo, cc.codigo as centro_costo_codigo
                                    FROM cont_distribucion_contable dc
                                    INNER JOIN pla_mov_detalle pm on pm.pla_det_id = dc.pla_det_id
                                    LEFT JOIN " . $baseSGI . ".plan_contable pc on pc.id = dc.plan_contable_id
                                    LEFT JOIN " . $baseSGI . ".centro_costo cc on cc.id = dc.centro_costo_id
                                    WHERE pm.pla_id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5], $fila[6], $fila[7]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getFilasPreviasPlaMovDet($arrPlaMovDet, $opc, $est_id, $arrDistribucionDetalle)
{
  /*
      Valores para $opc:
      1: Planilla de movilidad consulta basica hasta antes que sea revisada (no muestra columnas de aprobado y gastos asumidos, ni columna de borrar)
      2: Planilla de movilidad edicion basica antes que sea enviada (no muestra columnas de aprobado y gastos asumidos, si muestra columna de borrar)
      3: Planilla de movilidad revision de filas para aprobar/desaprobar y gastos asumidos
     */
  $html = '';
  foreach ($arrPlaMovDet as $k => $v) {
    /*
          Valores de $v[*] : Filas registradas :
          0  = pmd_motivo
          1  = pmd_fec
          2  = pmd_salida
          3  = pmd_destino
          4  = pmd_monto
          5  = pmd_aprob
          6  = pmd_emp_asume
         */

    $fec_doc = "";
    if (strlen($v[1]) == 10 && !startsWith($v[1], "0000")) {
      $pzas = explode("-", $v[1]);
      $fec_doc = $pzas[2] . "/" . $pzas[1] . "/" . $pzas[0];
    }

    if ($opc == 1) {
      $readonly = 1;
    } else {
      $readonly = 0;
    }

    $html .= "<tr class='fila_dato'>\n";
    $html .= "\t<td class='motivo_td'><input type='text' value='$v[0]' size='42' maxlength='100' class='motivo_inp' name='motivo_inp[S$k]'" . ($readonly == 1 ? " readonly" : "") . "></td>\n";
    $html .= "\t<td class='fecdoc_td'><input type='text' value='$fec_doc' size='11' maxlength='10' class='fecdoc_inp' readonly name='fecdoc_inp[S$k]'></td>\n"; // Fecha
    $html .= "\t<td class='salida_td'><input type='text' value='$v[2]' size='28' maxlength='100' class='salida_inp' name='salida_inp[S$k]'" . ($readonly == 1 ? " readonly" : "") . "></td>\n";
    $html .= "\t<td class='destino_td'><input type='text' value='$v[3]' size='28' maxlength='100' class='destino_inp' name='destino_inp[S$k]'" . ($readonly == 1 ? " readonly" : "") . "></td>\n";
    $html .= "\t<td class='monto_td'><input type='text' value='$v[4]' size='8' maxlength='9' id='monto_inp[S$k]' class='monto_inp' name='monto_inp[S$k]'" . ($readonly == 1 ? " readonly" : "") . "></td>\n"; // Monto
    $html .= "\t<td class='estado_td'><div class='estado_div'></div></td>\n";

    $dataDistribucion = array();
    foreach ($arrDistribucionDetalle as $indice => $item) {
      if ($item[1] == $v[8]) {
        $objeto = NULL;
        $objeto->tipo = $conc_cod;
        $objeto->fila = "[S$k]";
        $objeto->cuenta_contable = $item[2];
        $objeto->centro_costo = $item[3];
        $objeto->porcentaje = $item[4];
        $objeto->monto = $item[5];
        array_push($dataDistribucion, $objeto);
      }
    }
    $htmlDistribucionMostrar = "<input type='hidden' value='" . (!isEmpty($dataDistribucion) ? json_encode($dataDistribucion) : "") . "' id='lid_distribucion[S$k]' name='lid_distribucion[S$k]' class='distribucionContable' /><img id='op_distribucion[S$k]' name='op_distribucion[S$k]' class='op_distribucion' src='img/plus.png' onclick='abrirModalDistribucion(\"[S$k]\",0)' title='Agregar distribucion contable'>&nbsp;";
    $htmlDistribucionEditar = "<input type='hidden' value='" . (!isEmpty($dataDistribucion) ? json_encode($dataDistribucion) : "") . "' id='lid_distribucion[S$k]' name='lid_distribucion[S$k]' class='distribucionContable' /><img id='op_distribucion[S$k]' name='op_distribucion[S$k]' class='op_distribucion'src='img/plus.png' onclick='abrirModalDistribucion(\"[S$k]\",1)' title='Agregar distribucion contable'>&nbsp;";

    if ($opc == 1 && $est_id <> 4 && $est_id <> 5)
      $html .= "\t<td>$htmlDistribucionMostrar</td>\n";
    else if ($opc == 1 && ($est_id == 4 || $est_id == 5))
      $html .= "\t" . getAprobGastAsumTemplate($v[5], $v[6], $k) . "<td>$htmlDistribucionEditar</td>\n";
    else if ($opc == 2)
      $html .= "\t<td>$htmlDistribucionEditar<img src='img/delete.png' class='dele' title='Borrar'></td>\n";
    else if ($opc == 5)
      $html .= "\t" . getAprobGastAsumTemplate($v[5], $v[6], $k) . "<td>$htmlDistribucionMostrar</td>\n";
    else
      $html .= "\t" . getAprobGastAsumTemplate($v[5], $v[6], $k) . "<td>$htmlDistribucionEditar<img src='img/delete.png' class='dele' title='Borrar'></td>\n";
    $html .= "</tr>\n";
  }

  return $html;
}

function getFilaPlanillaMovilidad($id, $mon_id, $mode_id, $usu_nombre = "ERROR", $usu_gco_cobj = "ERROR")
{
  // Valores mode_id:
  // 1: ear_liq_registro.php
  // 2: ear_liq_revision.php
  // 3: ear_liq_vistobueno.php
  // 4: ear_liq_contabilidad.php
  // 100: ear_liq_consulta.php

  $html = '';
  list(
    $pla_numero, $est_id, $pla_reg_fec, $ear_numero, $tope_maximo, $usu_id, $ear_id,
    $est_nom, $pla_monto, $pla_gti, $pla_dg_json, $pla_env_fec,
    $pla_exc, $pla_com1, $pla_com2, $pla_com3, $pla_com4, $pla_com5, $pla_com6, $orden_trabajo_id, $orden_trabajo
  ) = getPlanillaMovilidadInfo($id);

  if ($mode_id == 1) {
    $fec_doc = date('d/m/Y');
    $tc = getTipoCambio(2, date('Y-m-d'));
  } else {
    $pzas = explode("-", substr($pla_env_fec, 0, 10));
    $fec_doc = $pzas[2] . "/" . $pzas[1] . "/" . $pzas[0];
    $tc = getTipoCambio(2, $pla_env_fec);
  }
  $pzas = explode("-", $pla_numero);
  $ser = $pzas[0];
  $nro = $pzas[1];
  $unique_id = "Splamov";
  $conv_afe_hide = 1;
  $conv_naf_hide = 1;
  $tc_div = "";
  if ($mon_id == 2) {
    $tc_div = $tc;
    $conv_naf_hide = 0;
    $conv = number_format($pla_monto / $tc, 2, '.', '');
  } else {
    $conv = $pla_monto;
  }
  if (is_null($pla_gti)) {
    $gti_id = 1;
    $dist_gast_json = '[["' . $usu_nombre . '","' . $usu_gco_cobj . '","100.00"]]';
  } else {
    $gti_id = $pla_gti;
    $dist_gast_json = $pla_dg_json;
  }
  $orden_t =  "&nbsp;<input type='hidden' value='$orden_trabajo_id' id='lid_orden_trabajo[$unique_id]' name='lid_orden_trabajo[$unique_id]' /><label id='des_orden_trabajo[$unique_id]' name='des_orden_trabajo[$unique_id]'>$orden_trabajo</label>&nbsp;<img class='orden_trabajo' src='img/plus.png'  onclick=\"abrirModalOrdenTrabajo('[$unique_id]')\" title='Agregar Orden de Trabajo'>&nbsp;";

  $html .= "<tr class='fila_dato'>\n";
  $html .= "\t<td>Movilidad</td>\n";
  $html .= "\t<td>Planilla de Movilidad</td>\n";
  $html .= "\t<td class='orden_t' style='white-space: nowrap;'>\n".$orden_t ."\t</td>\n"; //Orden trabajo
  $html .= "\t<td></td>\n";
  $html .= "\t<td></td>\n";
  $html .= "\t<td class='fec_doc_td'>$fec_doc<input type='hidden' value='$fec_doc' size='11' maxlength='10' class='fecha_inp' readonly name='fec_doc[$unique_id]'></td>\n";
  $html .= "\t<td>$ser</td>\n";
  $html .= "\t<td>$nro</td>\n";
  if ($mode_id == 1) {
    $html .= "\t<td><a href='movi_consulta_detalle.php?id=$id&opc=1&close=1' target='_blank'><img src='img/modal.gif' id='abrir_plamov' class='iconos' title='Abrir Planilla de Movilidad en una nueva ventana'></a></td>\n";
  } else {
    $html .= "\t<td></td>\n";
  }
  $html .= "\t<td class='tipo_mon_td'>PEN<input type='hidden' class='tipo_mon' id='pla_tipo_mon' value='1'></td>\n";
  $html .= "\t<td>No</td>\n";
  $html .= "\t<td></td>\n";
  $html .= "\t<td><div id='pla_monto_div'>$pla_monto</div></td>\n";
  $html .= "\t<td class='tc_td' hidden='true'><div class='tc_div'>$tc_div</div><input type='hidden' class='tc_inp' name='tc_inp[$unique_id]' id='tc_inp[$unique_id]' value='$tc'></td>\n"; //T/C;
  $html .= "\t<td class='conv_afecto_td' hidden='true'><input type='text' value='0' size='8' maxlength='9' id='conv_afecto_inp[$unique_id]' class='conv_afecto_inp' name='conv_afecto_inp[$unique_id]' readonly" . ($conv_afe_hide == 1 ? " style='display: none;'" : "") . "></td>\n"; //Conversion Afecto
  $html .= "\t<td class='conv_noafecto_td' hidden='true'><input type='text' value='$conv' size='8' maxlength='9' id='conv_noafecto_inp[$unique_id]' class='conv_noafecto_inp' name='conv_noafecto_inp[$unique_id]' readonly" . ($conv_naf_hide == 1 ? " style='display: none;'" : "") . "></td>\n"; //Conversion NoAfecto
  $html .= "\t<td></td>\n";
  $html .= "\t<td>Exonerado</td>\n";
  //	$html .= getDistGastTemplate($gti_id, $dist_gast_json, "plamov");
  if ($mode_id == 2) {
    if ($est_id == 3)
      $html .= "\t<td><div id='pm_estado'>Falta revisar <a href='movi_consulta_detalle.php?id=$id&opc=3' target='_blank'><img src='img/modal.gif' id='abrir_plamov' class='iconos' title='Abrir Planilla de Movilidad en una nueva ventana'></a> <img src='img/reload.gif' id='act_est_plamov' class='iconos' title='Actualizar estado'></div></td>\n";
    else if ($est_id == 4)
      $html .= "\t<td><div id='pm_estado'>Revisado <a href='movi_consulta_detalle.php?id=$id&opc=3' target='_blank'><img src='img/modal.gif' id='abrir_plamov' class='iconos' title='Abrir Planilla de Movilidad en una nueva ventana'></a> <img src='img/reload.gif' id='act_est_plamov' class='iconos' title='Actualizar estado'></div></td>\n";
    else if ($est_id == 5)
      $html .= "\t<td><div id='pm_estado'>Revisado VB <a href='movi_consulta_detalle.php?id=$id&opc=3' target='_blank'><img src='img/modal.gif' id='abrir_plamov' class='iconos' title='Abrir Planilla de Movilidad en una nueva ventana'></a> <img src='img/reload.gif' id='act_est_plamov' class='iconos' title='Actualizar estado'></div></td>\n";
    else
      $html .= "\t<td><div id='pm_estado'>ERROR: Estado invalido</div></td>\n";
    $html .= "\t<td class='gast_asum_td'><input type='text' value='$pla_monto' size='8' maxlength='9' class='gast_asum_i' name='gast_asum[$unique_id]' id='gast_asum[$unique_id]' readonly><input type='hidden' value='$est_id' name='pm_rev' id='pm_rev'></td>\n";
  } else if ($mode_id == 3) {
    if ($est_id == 4)
      $html .= "\t<td><div id='pm_estado'>Falta revisar <a href='movi_consulta_detalle.php?id=$id&opc=4' target='_blank'><img src='img/modal.gif' id='abrir_plamov' class='iconos' title='Abrir Planilla de Movilidad en una nueva ventana'></a> <img src='img/reload.gif' id='act_est_plamov' class='iconos' title='Actualizar estado'></div></td>\n";
    else if ($est_id == 5)
      $html .= "\t<td><div id='pm_estado'>Revisado <a href='movi_consulta_detalle.php?id=$id&opc=4' target='_blank'><img src='img/modal.gif' id='abrir_plamov' class='iconos' title='Abrir Planilla de Movilidad en una nueva ventana'></a> <img src='img/reload.gif' id='act_est_plamov' class='iconos' title='Actualizar estado'></div></td>\n";
    else
      $html .= "\t<td><div id='pm_estado'>ERROR: Estado invalido</div></td>\n";
    $html .= "\t<td class='gast_asum_td'><input type='text' value='$pla_monto' size='8' maxlength='9' class='gast_asum_i' name='gast_asum[$unique_id]' id='gast_asum[$unique_id]' readonly><input type='hidden' value='$est_id' name='pm_rev' id='pm_rev'></td>\n";
  } else if ($mode_id == 4) {
    $html .= "\t<td><div id='pm_estado'>Revisado <a href='movi_consulta_detalle.php?id=$id&opc=5' target='_blank'><img src='img/modal.gif' id='abrir_plamov' class='iconos' title='Abrir Planilla de Movilidad en una nueva ventana'></a> <img src='img/reload.gif' id='act_est_plamov' class='iconos' title='Actualizar estado'></div></td>\n";
    $html .= "\t<td class='gast_asum_td'><input type='text' value='$pla_monto' size='8' maxlength='9' class='gast_asum_i' name='gast_asum[$unique_id]' id='gast_asum[$unique_id]' readonly><input type='hidden' value='$est_id' name='pm_rev' id='pm_rev'></td>\n";
  } else if ($mode_id == 100) {
    $html .= "\t<td><div id='pm_estado'>Detalle <a href='movi_consulta_detalle.php?id=$id&opc=1&close=1' target='_blank'><img src='img/modal.gif' id='abrir_plamov' class='iconos' title='Abrir Planilla de Movilidad en una nueva ventana'></a></div></td>\n";
    $html .= "\t<td class='gast_asum_td'><input type='text' value='$pla_monto' size='8' maxlength='9' class='gast_asum_i' name='gast_asum[$unique_id]' id='gast_asum[$unique_id]' readonly><input type='hidden' value='$est_id' name='pm_rev' id='pm_rev'></td>\n";
  }
  $html .= "\t<td><input type='hidden' value='$id' name='pm_id'></td>\n";
  $html .= "</tr>\n";

  return $html;
}

function getFilaVaciaPlaMov($mode_id)
{
  if ($mode_id == 1) {
    $colspan = 23;
    $adicional = " No se podr&aacute; adjuntar la planilla una vez enviada la liquidaci&oacute;n.";
  } else {
    $colspan = 23;
    $adicional = "";
  }
  return "<tr class='fila_dato'>\n\t<td colspan='$colspan' style='background-color:yellow;'><span style='font-style:italic;'>Nota: No se ha generado planilla de movilidad para esta liquidaci&oacute;n.$adicional</span></td>\n</tr>\n";
}

function getUsuJefeYGerenteDirecto($usu_id)
{
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $stmt = $mysqli->prepare("select usuario_padre_id from " . $baseSGI . ".usuario where id=?");
  $stmt->bind_param("i", $usu_id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  $fila = fetchAssocStatement($stmt);
  $usu_id_jefe = $fila['usuario_padre_id'];
  $usu_id_gerente = $fila['usuario_padre_id'];

  $stmt = $mysqli->prepare("select up.usuario_id
                                    from " . $baseSGI . ".usuario_perfil up
                                            inner join " . $baseSGI . ".perfil p on p.id=up.perfil_id
                                            inner join " . $baseSGI . ".usuario u on u.id=up.usuario_id
                                            inner join " . $baseSGI . ".persona pp on pp.id=u.persona_id
                                    where up.estado=1
                                        and p.estado=1
                                        and u.estado=1
                                        and pp.estado=1
                                        and p.nombre = '" . $pADMINIST . "'
                                    limit 1");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  $fila = fetchAssocStatement($stmt);
  $usu_id_administ = $fila['usuario_id'];

  if (is_null($usu_id_jefe))
    $usu_id_jefe = $usu_id_administ;
  if (is_null($usu_id_gerente))
    $usu_id_gerente = $usu_id_administ;

  include 'datos_cerrar_bd.php';

  $arr = array($usu_id_jefe, $usu_id_gerente);

  return $arr;
}

function getUsuAdmin()
{
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $stmt = $mysqli->prepare("select up.usuario_id
                                    from " . $baseSGI . ".usuario_perfil up
                                            inner join " . $baseSGI . ".perfil p on p.id=up.perfil_id
                                    where up.estado=1
                                        and p.estado=1
                                        and p.nombre = '" . $pADMINIST . "'
                                    limit 1");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  $fila = fetchAssocStatement($stmt);
  $usu_id = $fila['usuario_id'];

  include 'datos_cerrar_bd.php';
  return $usu_id;
}

function getUsuController()
{
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT usu_id FROM usu_detalle WHERE usu_rol='CONTROLLER' AND usu_estado=1");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  $fila = fetchAssocStatement($stmt);
  $usu_id = $fila['usu_id'];

  include 'datos_cerrar_bd.php';
  return $usu_id;
}

function getUsuTesoreria()
{
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT usu_id FROM usu_detalle WHERE usu_rol='TESO' AND usu_estado=1");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  $fila = fetchAssocStatement($stmt);
  $usu_id = $fila['usu_id'];

  include 'datos_cerrar_bd.php';
  return $usu_id;
}

function getUsuSupCont()
{
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT usu_id FROM usu_detalle WHERE usu_rol='SUP_CONT' AND usu_estado=1");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  $fila = fetchAssocStatement($stmt);
  $usu_id = $fila['usu_id'];

  include 'datos_cerrar_bd.php';
  return $usu_id;
}

function getUsuRegCont()
{
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT usu_id FROM usu_detalle WHERE usu_rol='REG_CONT' AND usu_estado=1");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  $fila = fetchAssocStatement($stmt);
  $usu_id = $fila['usu_id'];

  include 'datos_cerrar_bd.php';
  return $usu_id;
}

function getUsuAnaCont()
{
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT usu_id FROM usu_detalle WHERE usu_rol='ANA_CONT' AND usu_estado=1");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  $fila = fetchAssocStatement($stmt);
  $usu_id = $fila['usu_id'];

  include 'datos_cerrar_bd.php';
  return $usu_id;
}

function getUsuCompensaciones()
{
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT usu_id FROM usu_detalle WHERE usu_rol='COMP' AND usu_estado=1");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  $fila = fetchAssocStatement($stmt);
  $usu_id = $fila['usu_id'];

  include 'datos_cerrar_bd.php';
  return $usu_id;
}

function getUsuTI()
{
  $arr = array();
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $stmt = $mysqli->prepare("select up.usuario_id
                                    from " . $baseSGI . ".usuario_perfil up
                                            inner join " . $baseSGI . ".perfil p on p.id=up.perfil_id
                                    where up.estado=1
                                        and p.estado=1
                                        and p.nombre = '" . $pTI . "'");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = $fila['usuario_id'];
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function date2serial($date)
{
  // Para usar en exportacion de Excel
  $timestamp = strtotime($date);
  return round(($timestamp + 25569 * 86400) / 86400);
}

function getPeriodoLiq($id)
{
  $arr = array();
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT MIN(lid_fec) fec_ini, MAX(lid_fec) fec_fin
		FROM ear_liq_detalle
		WHERE ear_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array($fila[0], $fila[1]);
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function getPeriodoPlaMov($id)
{
  $arr = array();
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT MIN(pmd.pmd_fec) fec_ini, MAX(pmd.pmd_fec) fec_fin
		FROM pla_mov_detalle pmd
		#JOIN pla_mov pm ON pm.pla_id=pmd.pla_id
		WHERE pmd.pla_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array($fila[0], $fila[1]);
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function getInfoAdminist()
{
  $arr = array();
  $usu_ad = "";
  $codigo_general = "";
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT ru.usu_ad
		FROM recursos.usuarios ru
		JOIN admin.usu_detalle ud ON ud.usu_id=ru.usu_id
		WHERE ud.usu_rol='ADMINIST' AND ud.usu_estado=1");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  $fila = fetchAssocStatement($stmt);
  $usu_ad = $fila[0];

  $codigo_general = getCodigoGeneral($usu_ad);

  list(
    $dni, $nombres, $cargo_id, $fecha_ing,
    $cargo_desc, $area_id, $area_desc, $idccosto, $banco, $ctacte, $sucursal
  ) = getInfoTrabajador($codigo_general);

  $arr = array($nombres, $dni);

  include 'datos_cerrar_bd.php';
  return $arr;
}

function getSubTotalConcepto($arrLiqDet, $mon_id, $conc_cod)
{
  $subtotal = 0;
  $liq_mon_id = $mon_id;

  foreach ($arrLiqDet as $k => $v) {
    /*
          Valores de $v[*] : Filas registradas :
          0  = conc_id
          1  = conc_cod
          2  = doc_id
          3  = ruc_nro
          4  = prov_nom
          5  = ruc_ret
          6  = lid_fec
          7  = lid_ser
          8  = lid_nro
          9  = lid_glo
          10 = mon_id
          11 = lid_afe
          12 = lid_mon_afe
          13 = lid_mon_naf
          14 = lid_tc
          15 = lid_retdet_apl
          16 = lid_retdet_tip
          17 = lid_retdet_mon
          18 = lid_gti
          19 = lid_dg_json
          20 = lid_cta_cont
          21 = lid_aprob
          22 = lid_emp_asume
          23 = doc_ruc_req
          24 = mon_iso
          25 = doc_nro
         *
         *      26 = lid_mon_otro
         */

    if (startsWith($v[1], $conc_cod) && $v[21] == 1) {
      list($ret_tasa, $ret_minmonto, $det_tasa, $det_minmonto) = getLiqConceptosRetDet($v[0], $v[6]);

      $total_doc = $v[22];
      if ($v[15] == 1)
        $total_doc -= $v[17];

      if ($liq_mon_id == 1 && $v[10] == 2)
        $total_doc = $total_doc * $v[14];
      else if ($liq_mon_id == 2 && $v[10] == 1)
        $total_doc = $total_doc / $v[14];

      $subtotal += $total_doc;
    }
  }

  return $subtotal;
}

function getCorreoUsuario($usu_id)
{
  include 'datos_abrir_bd.php';

  include 'parametros.php';
  $correo = "";

  $stmt = $mysqli->prepare("select p.id as persona_id,p.nombre,p.email from " . $baseSGI . ".persona p
                                    where p.id in (select u.persona_id from " . $baseSGI . ".usuario u where u.id=?);");
  $stmt->bind_param("i", $usu_id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  $fila = fetchAssocStatement($stmt);

  $correo = $fila[2];

  include 'datos_cerrar_bd.php';

  return trim($correo);
}

function enviarCorreo($to, $cc, $subject, $body, $attachString = NULL, $attachFilename = NULL)
{
  $body = 'Para: ' . $to . 'CC: ' . $cc . ' <br>' . $body;
  $to = $cc = 'ccabanillas';
  include 'parametros.php';
  require_once '../Util/PHPMailer/class.phpmailer.php';


  $mail = new PHPMailer;
  $mail->IsSMTP();        // Set mailer to use SMTP
  $mail->CharSet = 'UTF-8';
  $mail->Host = "mocha4004.mochahost.com";
  $mail->SMTPAuth = true;
  $mail->SMTPSecure = "ssl";
  $mail->Port = 465;

  $mail->Username = "abc@itecsac.com";
  $mail->Password = "Imagina123+-*/n05";
  $mail->From = "abc@itecsac.com";
  $mail->FromName = "ABC MULTISERVICIOS GENERALES S.A.C.";

  if (strlen($to) > 0)
    $mail->AddAddress($to);  // Add a recipient

  if (!is_null($cc)) {
    $cc = array_unique($cc);
    foreach ($cc as $key => $value) {
      $mail->AddCC($cc[$key]);
    }
  }


  $mail->WordWrap = 90;                                 // Set word wrap to 50 characters
  //$mail->AddAttachment('/var/tmp/file.tar.gz');         // Add attachments
  //$mail->AddAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
  if (!is_null($attachString))
    $mail->AddStringAttachment($attachString, $attachFilename);
  $mail->IsHTML(false);                                  // Set email format to HTML

  $mail->Subject = $subject;

  $body .= "\n\nPor favor, no responda a este correo. Este es un correo automatico solo utilizado para enviarle avisos por email. Las respuestas a este mensaje se redirigen a un buzon de correo sin supervision.";
  $body .= "\nSi tiene cualquier pregunta o sugerencia puede ponerse en contacto con nosotros en la direccion de correo electronico " . getCorreoUsuario(getUsuAdmin());
  $body .= "\n\nSaludos.\n\nAdministracion\nMinapp";
  $mail->Body = $body;
  //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

  if (!$mail->Send()) {
    echo 'Message could not be sent.<br>';
    echo 'Mailer Error: ' . $mail->ErrorInfo . '<br>';
    echo '<p>Error de envio de correo... no se completo la transaccion, comuniquese con el departamento de TI</p>';
    exit;
  }
}

function getDiferenciasDetLiq($ear_id, $hist_id)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  // Primer y segundo SELECT solo procesan documentos que no sean RGS (valida la diferencia en la serie y numero del documento)
  // Tercer y cuarto SELECT solo procesan documentos que sean RGS (no valida la diferencia en la serie y numero del documento)
  $stmt = $mysqli->prepare("SELECT 'liq_a' AS 'set', e1.conc_id, e1.doc_id, e1.ruc_nro, e1.lid_fec, e1.lid_ser, e1.lid_nro, e1.lid_glo,
			e1.mon_id, e1.lid_afe, e1.lid_mon_afe, e1.lid_mon_naf, e1.lid_tc, e1.lid_retdet_apl, e1.lid_retdet_tip, e1.lid_retdet_mon,
			e1.lid_gti, e1.lid_dg_json, e1.lid_cta_cont, e1.lid_aprob, e1.lid_emp_asume
		FROM ear_liq_detalle e1
		WHERE ROW(e1.conc_id, e1.doc_id, e1.ruc_nro, e1.lid_fec, e1.lid_ser, e1.lid_nro, e1.lid_glo,
				e1.mon_id, e1.lid_afe, e1.lid_mon_afe, e1.lid_mon_naf, e1.lid_tc, e1.lid_retdet_apl, e1.lid_retdet_tip, e1.lid_retdet_mon,
				e1.lid_gti, e1.lid_dg_json, e1.lid_cta_cont, e1.lid_aprob, e1.lid_emp_asume) NOT IN
			(
			SELECT conc_id, doc_id, ruc_nro, lid_fec, lid_ser, lid_nro, lid_glo,
				mon_id, lid_afe, lid_mon_afe, lid_mon_naf, lid_tc, lid_retdet_apl, lid_retdet_tip, lid_retdet_mon,
				lid_gti, lid_dg_json, lid_cta_cont, lid_aprob, lid_emp_asume
			FROM ear_liq_detalle_hist
			WHERE ear_id=? AND hist_id=? AND doc_id!=10
			)
		AND ear_id=? AND doc_id!=10
		UNION ALL
		SELECT 'liq_h' AS 'set', e2.conc_id, e2.doc_id, e2.ruc_nro, e2.lid_fec, e2.lid_ser, e2.lid_nro, e2.lid_glo,
			e2.mon_id, e2.lid_afe, e2.lid_mon_afe, e2.lid_mon_naf, e2.lid_tc, e2.lid_retdet_apl, e2.lid_retdet_tip, e2.lid_retdet_mon,
			e2.lid_gti, e2.lid_dg_json, e2.lid_cta_cont, e2.lid_aprob, e2.lid_emp_asume
		FROM ear_liq_detalle_hist e2
		WHERE ROW(e2.conc_id, e2.doc_id, e2.ruc_nro, e2.lid_fec, e2.lid_ser, e2.lid_nro, e2.lid_glo,
				e2.mon_id, e2.lid_afe, e2.lid_mon_afe, e2.lid_mon_naf, e2.lid_tc, e2.lid_retdet_apl, e2.lid_retdet_tip, e2.lid_retdet_mon,
				e2.lid_gti, e2.lid_dg_json, e2.lid_cta_cont, e2.lid_aprob, e2.lid_emp_asume) NOT IN
			(
			SELECT conc_id, doc_id, ruc_nro, lid_fec, lid_ser, lid_nro, lid_glo,
				mon_id, lid_afe, lid_mon_afe, lid_mon_naf, lid_tc, lid_retdet_apl, lid_retdet_tip, lid_retdet_mon,
				lid_gti, lid_dg_json, lid_cta_cont, lid_aprob, lid_emp_asume
			FROM ear_liq_detalle
			WHERE ear_id=? AND doc_id!=10
			)
		AND ear_id=? AND hist_id=? AND doc_id!=10
		UNION ALL
		SELECT 'liq_a' AS 'set', e1.conc_id, e1.doc_id, e1.ruc_nro, e1.lid_fec, '0' AS 'lid_ser', '0' AS 'lid_nro', e1.lid_glo,
			e1.mon_id, e1.lid_afe, e1.lid_mon_afe, e1.lid_mon_naf, e1.lid_tc, e1.lid_retdet_apl, e1.lid_retdet_tip, e1.lid_retdet_mon,
			e1.lid_gti, e1.lid_dg_json, e1.lid_cta_cont, e1.lid_aprob, e1.lid_emp_asume
		FROM ear_liq_detalle e1
		WHERE ROW(e1.conc_id, e1.doc_id, e1.ruc_nro, e1.lid_fec, e1.lid_glo,
				e1.mon_id, e1.lid_afe, e1.lid_mon_afe, e1.lid_mon_naf, e1.lid_tc, e1.lid_retdet_apl, e1.lid_retdet_tip, e1.lid_retdet_mon,
				e1.lid_gti, e1.lid_dg_json, e1.lid_cta_cont, e1.lid_aprob, e1.lid_emp_asume) NOT IN
			(
			SELECT conc_id, doc_id, ruc_nro, lid_fec, lid_glo,
				mon_id, lid_afe, lid_mon_afe, lid_mon_naf, lid_tc, lid_retdet_apl, lid_retdet_tip, lid_retdet_mon,
				lid_gti, lid_dg_json, lid_cta_cont, lid_aprob, lid_emp_asume
			FROM ear_liq_detalle_hist
			WHERE ear_id=? AND hist_id=? AND doc_id=10
			)
		AND ear_id=? AND doc_id=10
		UNION ALL
		SELECT 'liq_h' AS 'set', e2.conc_id, e2.doc_id, e2.ruc_nro, e2.lid_fec, '0' AS 'lid_ser', '0' AS 'lid_nro', e2.lid_glo,
			e2.mon_id, e2.lid_afe, e2.lid_mon_afe, e2.lid_mon_naf, e2.lid_tc, e2.lid_retdet_apl, e2.lid_retdet_tip, e2.lid_retdet_mon,
			e2.lid_gti, e2.lid_dg_json, e2.lid_cta_cont, e2.lid_aprob, e2.lid_emp_asume
		FROM ear_liq_detalle_hist e2
		WHERE ROW(e2.conc_id, e2.doc_id, e2.ruc_nro, e2.lid_fec, e2.lid_glo,
				e2.mon_id, e2.lid_afe, e2.lid_mon_afe, e2.lid_mon_naf, e2.lid_tc, e2.lid_retdet_apl, e2.lid_retdet_tip, e2.lid_retdet_mon,
				e2.lid_gti, e2.lid_dg_json, e2.lid_cta_cont, e2.lid_aprob, e2.lid_emp_asume) NOT IN
			(
			SELECT conc_id, doc_id, ruc_nro, lid_fec, lid_glo,
				mon_id, lid_afe, lid_mon_afe, lid_mon_naf, lid_tc, lid_retdet_apl, lid_retdet_tip, lid_retdet_mon,
				lid_gti, lid_dg_json, lid_cta_cont, lid_aprob, lid_emp_asume
			FROM ear_liq_detalle
			WHERE ear_id=? AND doc_id=10
			)
		AND ear_id=? AND hist_id=? AND doc_id=10");
  $stmt->bind_param("iiiiiiiiiiii", $ear_id, $hist_id, $ear_id, $ear_id, $ear_id, $hist_id, $ear_id, $hist_id, $ear_id, $ear_id, $ear_id, $hist_id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array(
      $fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5], $fila[6],
      $fila[7], $fila[8], $fila[9], $fila[10], $fila[11], $fila[12], $fila[13], $fila[14],
      $fila[15], $fila[16], $fila[17], $fila[18], $fila[19]
    );
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function conComas($valor)
{
  return number_format($valor, 2, '.', ',');
}

function NumerosALetras($monto)
{
  $maximo = pow(10, 9);
  $unidad = array(1 => "UNO", 2 => "DOS", 3 => "TRES", 4 => "CUATRO", 5 => "CINCO", 6 => "SEIS", 7 => "SIETE", 8 => "OCHO", 9 => "NUEVE");
  $decena = array(10 => "DIEZ", 11 => "ONCE", 12 => "DOCE", 13 => "TRECE", 14 => "CATORCE", 15 => "QUINCE", 20 => "VEINTE", 30 => "TREINTA", 40 => "CUARENTA", 50 => "CINCUENTA", 60 => "SESENTA", 70 => "SETENTA", 80 => "OCHENTA", 90 => "NOVENTA");
  $prefijo_decena = array(10 => "DIECI", 20 => "VEINTI", 30 => "TREINTA Y ", 40 => "CUARENTA Y ", 50 => "CINCUENTA Y ", 60 => "SESENTA Y ", 70 => "SETENTA Y ", 80 => "OCHENTA Y ", 90 => "NOVENTA Y ");
  $centena = array(100 => "CIEN", 200 => "DOSCIENTOS", 300 => "TRESCIENTOS", 400 => "CUANTROCIENTOS", 500 => "QUINIENTOS", 600 => "SEISCIENTOS", 700 => "SETECIENTOS", 800 => "OCHOCIENTOS", 900 => "NOVECIENTOS");
  $prefijo_centena = array(100 => "CIENTO ", 200 => "DOSCIENTOS ", 300 => "TRESCIENTOS ", 400 => "CUANTROCIENTOS ", 500 => "QUINIENTOS ", 600 => "SEISCIENTOS ", 700 => "SETECIENTOS ", 800 => "OCHOCIENTOS ", 900 => "NOVECIENTOS ");
  $sufijo_miles = "MIL";
  $sufijo_millon = "UN MILLON";
  $sufijo_millones = "MILLONES";

  //echo var_dump($monto); die;

  $base = strlen(strval($monto));
  $pren = intval(floor($monto / pow(10, $base - 1)));
  $prencentena = intval(floor($monto / pow(10, 3)));
  $prenmillar = intval(floor($monto / pow(10, 6)));
  $resto = $monto % pow(10, $base - 1);
  $restocentena = $monto % pow(10, 3);
  $restomillar = $monto % pow(10, 6);

  if (!$monto)
    return "";

  if (is_int($monto) && $monto > 0 && $monto < abs($maximo)) {
    switch ($base) {
      case 1:
        return $unidad[$monto];
      case 2:
        return array_key_exists($monto, $decena) ? $decena[$monto] : $prefijo_decena[$pren * 10] . NumerosALetras($resto);
      case 3:
        return array_key_exists($monto, $centena) ? $centena[$monto] : $prefijo_centena[$pren * 100] . NumerosALetras($resto);
      case 4:
      case 5:
      case 6:
        return ($prencentena > 1) ? NumerosALetras($prencentena) . " " . $sufijo_miles . " " . NumerosALetras($restocentena) : $sufijo_miles . " " . NumerosALetras($restocentena);
      case 7:
      case 8:
      case 9:
        return ($prenmillar > 1) ? NumerosALetras($prenmillar) . " " . $sufijo_millones . " " . NumerosALetras($restomillar) : $sufijo_millon . " " . NumerosALetras($restomillar);
    }
  } else {
    return "ERROR con el numero - $monto<br/> Debe ser un numero entero menor que " . number_format($maximo, 0, ".", ",") . ".";
  }

  //return $texto;
}

function MontoMonetarioEnLetras($monto)
{

  $monto = str_replace(',', '', $monto); //ELIMINA LA COMA

  $pos = strpos($monto, '.');

  if ($pos == false) {
    $monto_entero = $monto;
    $monto_decimal = '00';
  } else {
    $monto_entero = substr($monto, 0, $pos);
    $monto_decimal = substr($monto, $pos, strlen($monto) - $pos);
    $monto_decimal = $monto_decimal * 100;
  }

  $monto = (int) ($monto_entero);

  $texto_con = " CON $monto_decimal/100";

  return NumerosALetras($monto) . $texto_con;
}

function getSolicitudSubtotales($id)
{
  $arr = array();
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT LEFT(ev.via_cod, 2) via_cod, FORMAT(SUM(IFNULL(esd.via_dias, 1)*esd.via_monto), 2) via_subtotal
		FROM ear_sol_detalle esd
		LEFT JOIN ear_viaticos ev ON ev.via_id=esd.via_id
		WHERE esd.ear_id=?
		GROUP BY LEFT(ev.via_cod, 2)");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[$fila[0]] = $fila[1];
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function getSolicitudTotal($id)
{
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT IFNULL(SUM(IFNULL(esd.via_dias, 1)*esd.via_monto), 0) via_subtotal
		FROM ear_sol_detalle esd
		WHERE esd.ear_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $valor = number_format($fila[0], 2, '.', '');
  }

  include 'datos_cerrar_bd.php';
  return $valor;
}

function getSolicitudAlimDiasTope($id)
{
  $arr = array();
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT esd.via_dias, esd.via_monto
		FROM ear_sol_detalle esd
		LEFT JOIN ear_viaticos ev ON ev.via_id=esd.via_id
		WHERE esd.ear_id=? AND LEFT(ev.via_cod, 2)='02'");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array($fila[0], $fila[1]);
  }

  if (count($arr) == 0) {
    $arr = array(0, 0);
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function getListaSolicitudes($id)
{
  $arr = array();
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $stmt = $mysqli->prepare("SELECT e.ear_id,concat(ifnull(sp3.nombre,''),' ',ifnull(sp3.apellido_paterno,''),' ',ifnull(sp3.apellido_materno,'')) as ear_tra_nombres,
                    CONCAT(e.ear_anio, '-', LPAD(e.ear_mes, 2, '0'), '-', LPAD(e.ear_nro, 3, '0'), '/',
                    fn_obtener_iniciales(concat(ifnull(sp3.nombre,''),' ',ifnull(sp3.apellido_paterno,''),' ',ifnull(sp3.apellido_materno,''))))  as ear_numero,
                    z.zona_nom, m.mon_nom, m.mon_iso, m.mon_img, e.ear_monto, e.est_id,
                    case e.est_id
                        when 9 then
                            case when e.ear_liq_dcto > 0 then 'Por devolver'
                            else 'Por reembolsar' end
                        else te.est_nom
                    end as est_nom,
                    e.ear_sol_fec, e.ear_liq_fec,
                    concat(sp2.nombre,' ',sp2.apellido_paterno,' ',sp2.apellido_materno) as usu_act,
                    e.ear_act_fec, e.ear_act_motivo, e.ear_liq_dcto, e.usu_id, e.master_usu_id
                    ,e.ear_sol_motivo
            FROM ear_solicitudes e
		LEFT JOIN ear_zonas z ON z.zona_id=e.zona_id
		LEFT JOIN monedas m ON m.mon_id=e.mon_id
		LEFT JOIN tablas_estados te ON te.tabla_id=1 AND te.est_id=e.est_id
		JOIN tablas_nombres tn ON tn.tabla_id=te.tabla_id AND tabla_nom='ear_solicitudes'
		LEFT JOIN " . $baseSGI . ".usuario su2 ON su2.id=e.ear_act_usu
		left join " . $baseSGI . ".persona sp2 on sp2.id=su2.persona_id
		inner join " . $baseSGI . ".usuario su on su.id=e.usuario_reg_id
		inner join " . $baseSGI . ".persona sp on sp.id=su.persona_id
    left join " . $baseSGI . ".persona sp3 on sp3.id=e.usu_id #para terceros
            WHERE e.est_id=?
		LIMIT 2000");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array(
      $fila[0], $fila[1], $fila[2],
      $fila[3], $fila[4], $fila[5], $fila[6], $fila[7], $fila[8], $fila[9], $fila[10], $fila[11],
      $fila[12], $fila[13], $fila[14], $fila[15], $fila[16], $fila[17], $fila[18]
    );
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function getDiaTopeDescuentos($anio, $mes)
{
  $valor = '';
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT CONCAT(c.anio, '-', LPAD(c.mes, 2, '0'), '-', LPAD(c.dia_tope, 2, '0')) fecha_tope
		FROM calendario_dctos c
		WHERE c.anio=? AND c.mes=?");
  $stmt->bind_param("ii", $anio, $mes);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $valor = $fila[0];
  }
  include 'datos_cerrar_bd.php';

  // Notifica por correo si es que no se ha definido el dia tope para el anio y mes correspondiente de la consulta realizada
  if ($valor == '') {
    $to = getCorreoUsuario(getUsuCompensaciones());
    $cc = array();
    array_push($cc, getCorreoUsuario(getUsuController()));
    array_push($cc, getCorreoUsuario(getUsuAdmin()));
    $subject = "No se ha definido dia tope para " . nombreMes($mes) . " $anio (Devoluciones)";
    $body = "Se gener&oacute; una devoluci&oacute;n pero no se encontro el dia tope para el mes de " . nombreMes($mes) . " $anio.";
    $body .= "\n\nFavor de ingresar al modulo Administracion de la web intranet, y efectuar el llenado de los datos en el mantenedor de Fechas topes para descuentos.";
    //		enviarCorreo($to, $cc, $subject, $body);
  }

  return $valor;
}

function getPermisosAdministrativos($usu_id, $rol)
{
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  if ($rol == 'JEFEOGERENTE') {
    $stmt = $mysqli->prepare("select count(1) from " . $baseSGI . ".usuario u
                                          where u.usuario_padre_id =?
                                             and u.estado=1");
    $stmt->bind_param('i', $usu_id);
  } else {
    $stmt = $mysqli->prepare("select count(1)
                                            from " . $baseSGI . ".usuario_perfil up
                                                    inner join " . $baseSGI . ".perfil p on p.id=up.perfil_id
                                            where up.estado=1
                                                and p.estado=1
                                                and up.usuario_id=?
                                                and p.nombre = ?");
    $stmt->bind_param('is', $usu_id, $rol);
  }
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  $fila = fetchAssocStatement($stmt);

  $count = $fila[0];

  include 'datos_cerrar_bd.php';

  return $count;
}

function getCtaContPlaMov($id)
{
  switch ($id) {
    case 1:
      // EAR
      $val_id = 2;
      break;
    case 2:
      // Caja Chica
      $val_id = 3;
      break;
    default:
      $val_id = -1;
  }

  $valor = '';

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT val_val
		FROM valores_sistema
		WHERE val_id=?");
  $stmt->bind_param("i", $val_id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $valor = $fila[0];
  }
  include 'datos_cerrar_bd.php';

  return $valor;
}

function getListaEARs($max, $cons_id, $usu_id, $zon_id, $mon_id, $est_id, $fec_sol_ini, $fec_sol_fin, $opc_id, $otro_jefe_usu_id = null)
{
  $arr = array();
  $q_vals1 = "1 uno";
  $q_vals2 = "2 dos";
  $q_joins = "";
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  // Filtro de fechas
  $q_fec_sol = "e.ear_sol_fec BETWEEN '$fec_sol_ini 00:00:00' AND '$fec_sol_fin 23:59:59'";

  // Filtro de acuerdo al tipo de consulta
  if ($cons_id == 1) {
    if ($opc_id == 1) {
      $q_vals1 = "DATEDIFF(now(), ear_dese_fec) dias_trans";
      $q_cons = "AND e.est_id=4 AND e.usu_id=$usu_id";
    } else if ($opc_id == 2) {
      $q_vals1 = "e.ear_liq_dcto";
      $q_cons = "AND e.est_id=9 AND e.usu_id=$usu_id AND e.ear_liq_dcto<0";
    } else if ($opc_id == 3) {
      $q_vals1 = "e.ear_liq_dcto";
      $q_cons = "AND (e.est_id=9 OR e.est_id=51) AND e.usu_id=$usu_id AND e.ear_liq_dcto>0";
    } else if ($opc_id == 10) {
      $q_cons = "AND (e.master_usu_id=$usu_id OR e.usu_id IN (" . implode(",", getUsuRegOtroSlavesIds($usu_id)) . "))";
    } else if ($opc_id == 11) {
      $q_vals1 = "DATEDIFF(now(), ear_dese_fec) dias_trans";
      $q_cons = "AND e.est_id=4 AND (e.master_usu_id=$usu_id OR e.usu_id IN (" . implode(",", getUsuRegOtroSlavesIds($usu_id)) . "))";
    } else if ($opc_id == 12) {
      $q_vals1 = "e.ear_liq_dcto";
      $q_cons = "AND e.est_id=9 AND (e.master_usu_id=$usu_id OR e.usu_id IN (" . implode(",", getUsuRegOtroSlavesIds($usu_id)) . ")) AND e.ear_liq_dcto<0";
    } else if ($opc_id == 13) {
      $q_vals1 = "e.ear_liq_dcto";
      $q_cons = "AND (e.est_id=9 OR e.est_id=51) AND (e.master_usu_id=$usu_id OR e.usu_id IN (" . implode(",", getUsuRegOtroSlavesIds($usu_id)) . ")) AND e.ear_liq_dcto>0";
    } else {
      $q_cons = "AND e.usu_id=".$_SESSION['rec_usu_persona_id']."";
    }
  } else if ($cons_id == 2) {
    $count = getPermisosAdministrativos($_SESSION['rec_usu_id'], $pADMINIST);
    $count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pTI);
    $count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pGERENTE);
    $count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pASISTENTE_ADMINISTRATIVO);
    if ($count > 0) {
      $q_cons = "";
    } else {
      // $usu_ad = getUsuAd($usu_id);
      // $q_cons = "AND ( ru2.usu_jefe='$usu_ad' OR ru2.usu_gerente='$usu_ad' )";
      if (is_null($otro_jefe_usu_id)) {
        $q_cons = "AND ( ud.usu_jefe=$usu_id )";
      } else {
        $q_cons = "AND ( ud.usu_jefe=$otro_jefe_usu_id )";
      }
    }
  } else if ($cons_id == 3) {
    if ($opc_id == 1) {
      $q_cons = "AND e.est_id BETWEEN 7 AND 12";
    } else if ($opc_id == 2) {
      $q_vals1 = "DATEDIFF(now(), ear_dese_fec) dias_trans";
      $q_cons = "AND e.est_id=4";
    }
  } else if ($cons_id == 4) {
    if ($opc_id == 1) {
      $q_vals1 = "dese.ear_dese_fec";
      $q_joins = "JOIN (SELECT MAX(ear_act_fec) ear_dese_fec, ear_id
				FROM ear_actualizaciones
				WHERE est_id=4
				GROUP BY ear_id) dese ON dese.ear_id=e.ear_id";
      $q_cons = "AND (e.est_id BETWEEN 4 AND 12 OR e.est_id BETWEEN 51 AND 53)";
    } else {
      $q_vals1 = "e.ear_liq_dcto";
      $q_cons = "AND e.est_id = 10";
    }
  } else if ($cons_id == 5) {
    $q_vals1 = "e.ear_liq_dcto";
    $q_cons = "AND (e.est_id = 11 OR e.est_id = 52)";
  } else if ($cons_id == 6 && $opc_id == 2) {
    $q_vals1 = "IFNULL(DATEDIFF(now(), ear_act_fec), DATEDIFF(now(), ear_sol_fec)) dias_trans";
    $q_vals2 = "e.ear_liq_dcto";
    $q_cons = "";
  } else {
    $q_cons = "";
  }

  // Filtro de zonas
  if ($zon_id == "255") {
    $q_zona = "";
  } else {
    $q_zona = "AND e.zona_id='$zon_id'";
  }

  // Filtro de monedas
  if ($mon_id == "255") {
    $q_moneda = "";
  } else {
    $q_moneda = "AND e.mon_id=$mon_id";
  }

  // Filtro de estados
  if ($est_id == "255") {
    $q_estado = "";
  } else {
    $q_estado = "AND e.est_id=$est_id";
  }

  $stmt = $mysqli->prepare("SELECT e.ear_id,
                                          concat(ifnull(sp3.nombre,''),' ',ifnull(sp3.apellido_paterno,''),' ',ifnull(sp3.apellido_materno,'')) as ear_tra_nombres,
                                          concat(e.ear_anio, '-', LPAD(e.ear_mes, 2, '0'), '-', LPAD(e.ear_nro, 3, '0'), '/',
                                            fn_obtener_iniciales(concat(ifnull(sp3.nombre,''),' ',ifnull(sp3.apellido_paterno,''),' ',ifnull(sp3.apellido_materno,'')))) as ear_numero,
                                         z.zona_nom,
                                         m.mon_nom,
                                         m.mon_iso,
                                         m.mon_img,
                                         e.ear_monto,
                                         e.est_id,
                                        (case e.est_id
                                            when 9 then
                                                case when e.ear_liq_dcto > 0 then 'Por devolver'
                                                else 'Por reembolsar' end
                                            else te.est_nom
                                        end) as est_nom,
                                        e.ear_sol_fec,
                                        e.ear_liq_fec,
                                        concat(sp2.nombre,' ',sp2.apellido_paterno,' ',sp2.apellido_materno) as usu_act,
                                        e.ear_act_fec,
                                        e.ear_act_motivo,
                                        1 uno,
                                        2 dos,
                                        e.master_usu_id,
                                        e.ear_dese_fec,
                                        e.ear_liq_dcto,
                                        e.ear_sol_motivo,
                                        concat(ifnull(sp.nombre,''),' ',ifnull(sp.apellido_paterno,''),' ',ifnull(sp.apellido_materno,'')) as ear_usuario_reg
                        FROM ear_solicitudes e
                        LEFT JOIN ear_zonas z ON z.zona_id=e.zona_id
                        LEFT JOIN monedas m ON m.mon_id=e.mon_id
                        LEFT JOIN tablas_estados te ON te.tabla_id=1 AND te.est_id=e.est_id
                        JOIN tablas_nombres tn ON tn.tabla_id=te.tabla_id AND tabla_nom='ear_solicitudes'
                        LEFT JOIN " . $baseSGI . ".usuario su2 ON su2.id=e.ear_act_usu
                        left join " . $baseSGI . ".persona sp2 on sp2.id=su2.persona_id
                        left join " . $baseSGI . ".usuario su on su.id=e.usuario_reg_id
                        left join " . $baseSGI . ".persona sp on sp.id=su.persona_id # para usuario_reg
                        left join " . $baseSGI . ".persona sp3 on sp3.id=e.usu_id #para terceros
                        $q_joins
                    WHERE $q_fec_sol $q_cons $q_zona $q_moneda $q_estado
                    and su.estado=1 and sp.estado=1
                    LIMIT ?");
  $stmt->bind_param("i", $max);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array(
      $fila[0], $fila[1], $fila[2],
      $fila[3], $fila[4], $fila[5], $fila[6], $fila[7], $fila[8], $fila[9], $fila[10], $fila[11],
      $fila[12], $fila[13], $fila[14], $fila[15], $fila[16], $fila[17], $fila[18], $fila[19], $fila[20], $fila[21]
    );
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function getListaEARXId($id, $maximoRegistros)
{
  $arr = array();
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $stmt = $mysqli->prepare("SELECT e.ear_id,
                                         concat(ifnull(sp.nombre,''),' ',ifnull(sp.apellido_paterno,''),' ',ifnull(sp.apellido_materno,'')) as ear_tra_nombres,
                                         concat(e.ear_anio, '-', LPAD(e.ear_mes, 2, '0'), '-', LPAD(e.ear_nro, 3, '0'), '/',
                                         fn_obtener_iniciales(concat(ifnull(sp.nombre,''),' ',ifnull(sp.apellido_paterno,''),' ',ifnull(sp.apellido_materno,'')))) ear_numero,
                                         z.zona_nom,
                                         m.mon_nom,
                                         m.mon_iso,
                                         m.mon_img,
                                         e.ear_monto,
                                         e.est_id,
                                        (case e.est_id
                                            when 9 then
                                                case when e.ear_liq_dcto > 0 then 'Por devolver'
                                                else 'Por reembolsar' end
                                            else te.est_nom
                                        end) as est_nom,
                                        e.ear_sol_fec,
                                        e.ear_liq_fec,
                                        concat(sp2.nombre,' ',sp2.apellido_paterno,' ',sp2.apellido_materno) as usu_act,
                                        e.ear_act_fec,
                                        e.ear_act_motivo,
                                        1 uno,
                                        2 dos,
                                        e.master_usu_id,
                                        e.ear_dese_fec,
                                        e.ear_liq_dcto,
                                        e.ear_sol_motivo
                        FROM ear_solicitudes e
                        LEFT JOIN ear_zonas z ON z.zona_id=e.zona_id
                        LEFT JOIN monedas m ON m.mon_id=e.mon_id
                        LEFT JOIN tablas_estados te ON te.tabla_id=1 AND te.est_id=e.est_id
                        JOIN tablas_nombres tn ON tn.tabla_id=te.tabla_id AND tabla_nom='ear_solicitudes'
                        LEFT JOIN " . $baseSGI . ".usuario su2 ON su2.id=e.ear_act_usu
                        left join " . $baseSGI . ".persona sp2 on sp2.id=su2.persona_id
                        inner join " . $baseSGI . ".usuario su on su.id=e.usu_id
                        inner join " . $baseSGI . ".persona sp on sp.id=su.persona_id

                    WHERE  e.ear_id in (" . $id . ")
                    and su.estado=1 and sp.estado=1
                    LIMIT ?");
  $stmt->bind_param("i", $maximoRegistros);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array(
      $fila[0], $fila[1], $fila[2],
      $fila[3], $fila[4], $fila[5], $fila[6], $fila[7], $fila[8], $fila[9], $fila[10], $fila[11],
      $fila[12], $fila[13], $fila[14], $fila[15], $fila[16], $fila[17], $fila[18], $fila[19], $fila[20]
    );
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function getListaEARsDescargaExcelLiq($max, $cons_id, $usu_id, $zon_id, $mon_id, $est_id, $fec_sol_ini, $fec_sol_fin, $opc_id, $otro_jefe_usu_id = null)
{
  $arr = array();
  $q_vals1 = "1 uno";
  $q_vals2 = "2 dos";
  $q_joins = "";
  include 'datos_abrir_bd.php';

  // Filtro de fechas
  $q_fec_sol = "e.ear_sol_fec BETWEEN '$fec_sol_ini 00:00:00' AND '$fec_sol_fin 23:59:59'";

  // Filtro de acuerdo al tipo de consulta
  if ($cons_id == 3) {
    if ($opc_id == 1) {
      $q_cons = "AND e.est_id BETWEEN 7 AND 12";
    } else if ($opc_id == 2) {
      $q_vals1 = "DATEDIFF(now(), ear_dese_fec) dias_trans";
      $q_cons = "AND e.est_id=4";
    }
  } else {
    $q_cons = "";
  }

  // Filtro de zonas
  if ($zon_id == "255") {
    $q_zona = "";
  } else {
    $q_zona = "AND e.zona_id='$zon_id'";
  }

  // Filtro de monedas
  if ($mon_id == "255") {
    $q_moneda = "";
  } else {
    $q_moneda = "AND e.mon_id=$mon_id";
  }

  // Filtro de estados
  if ($est_id == "255") {
    $q_estado = "";
  } else {
    $q_estado = "AND e.est_id=$est_id";
  }

  // $stmt = $mysqli->prepare("SELECT e.ear_id, e.ear_tra_nombres, CONCAT(e.ear_anio, '-', LPAD(e.ear_mes, 2, '0'), '-', LPAD(e.ear_nro, 3, '0'), '/', ud.usu_iniciales) ear_numero,
  // z.zona_nom, m.mon_nom, m.mon_iso, m.mon_img, e.ear_monto, e.est_id, te.est_nom, e.ear_sol_fec, e.ear_liq_fec,
  // ru.usu_nombre usu_act, e.ear_act_fec, e.ear_act_motivo, $q_vals1, $q_vals2, e.master_usu_id, e.ear_dese_fec
  // FROM ear_solicitudes e
  // LEFT JOIN ear_zonas z ON z.zona_id=e.zona_id
  // LEFT JOIN monedas m ON m.mon_id=e.mon_id
  // LEFT JOIN tablas_estados te ON te.est_id=e.est_id
  // JOIN tablas_nombres tn ON tn.tabla_id=te.tabla_id AND tabla_nom='ear_solicitudes'
  // LEFT JOIN usu_detalle ud ON ud.usu_id=e.usu_id
  // LEFT JOIN recursos.usuarios ru ON ru.usu_id=e.ear_act_usu
  // $q_joins
  // WHERE $q_fec_sol $q_cons $q_zona $q_moneda $q_estado
  // LIMIT ?");
  $stmt = $mysqli->prepare("SELECT ear_id, ear_tra_nombres, ear_numero, zona_nom, mon_nom, mon_iso, mon_img, ear_monto, est_id, est_nom, ear_sol_fec, ear_liq_fec,
	usu_act, ear_act_fec, ear_act_motivo, $q_vals1, $q_vals2, master_usu_id, ear_dese_fec,
	SUM(deta_liq) deta_liq, SUM(deta_ret) deta_ret, SUM(deta_det) deta_det, SUM(deta_acf) deta_acf, SUM(deta_aju) deta_aju
	FROM
	(
		SELECT e.ear_id, e.ear_tra_nombres, CONCAT(e.ear_anio, '-', LPAD(e.ear_mes, 2, '0'), '-', LPAD(e.ear_nro, 3, '0'), '/', ud.usu_iniciales) ear_numero,
			z.zona_nom, m.mon_nom, m.mon_iso, m.mon_img, e.ear_monto, e.est_id, te.est_nom, e.ear_sol_fec, e.ear_liq_fec,
			ru.usu_nombre usu_act, e.ear_act_fec, e.ear_act_motivo, e.master_usu_id, e.ear_dese_fec,
			CASE WHEN eld.lid_aprob = 1 AND eld.lid_retdet_tip = 0 AND ec.conc_acf = 0 THEN 1 ELSE 0 END as deta_liq,
			CASE WHEN eld.lid_aprob = 1 AND eld.lid_retdet_tip = 2 THEN 1 ELSE 0 END as deta_ret,
			CASE WHEN eld.lid_aprob = 1 AND eld.lid_retdet_tip = 1 THEN 1 ELSE 0 END as deta_det,
			CASE WHEN eld.lid_aprob = 1 AND ec.conc_acf = 1 THEN 1 ELSE 0 END as deta_acf,
			CASE WHEN eld.lid_aprob = 1 AND eld.lid_mon_afe + eld.lid_mon_naf - eld.lid_emp_asume > 0 THEN 1 ELSE 0 END as deta_aju
		FROM ear_solicitudes e
		LEFT JOIN ear_liq_detalle eld ON eld.ear_id=e.ear_id
		LEFT JOIN ear_conceptos ec ON ec.conc_id=eld.conc_id
		LEFT JOIN ear_zonas z ON z.zona_id=e.zona_id
		LEFT JOIN monedas m ON m.mon_id=e.mon_id
		LEFT JOIN tablas_estados te ON te.tabla_id=1 AND te.est_id=e.est_id
		JOIN tablas_nombres tn ON tn.tabla_id=te.tabla_id AND tabla_nom='ear_solicitudes'
		LEFT JOIN usu_detalle ud ON ud.usu_id=e.usu_id
		LEFT JOIN recursos.usuarios ru ON ru.usu_id=e.ear_act_usu
		WHERE $q_fec_sol $q_cons $q_zona $q_moneda $q_estado

		UNION

		SELECT e.ear_id, e.ear_tra_nombres, CONCAT(e.ear_anio, '-', LPAD(e.ear_mes, 2, '0'), '-', LPAD(e.ear_nro, 3, '0'), '/', ud.usu_iniciales) ear_numero,
			z.zona_nom, m.mon_nom, m.mon_iso, m.mon_img, e.ear_monto, e.est_id, te.est_nom, e.ear_sol_fec, e.ear_liq_fec,
			ru.usu_nombre usu_act, e.ear_act_fec, e.ear_act_motivo, e.master_usu_id, e.ear_dese_fec,
			CASE WHEN pm.pla_monto > 0 THEN 1 ELSE 0 END AS deta_liq,
			0 AS deta_ret,
			0 AS deta_det,
			0 AS deta_acf,
			0 AS deta_aju
		FROM ear_solicitudes e
		LEFT JOIN ear_zonas z ON z.zona_id=e.zona_id
		LEFT JOIN monedas m ON m.mon_id=e.mon_id
		LEFT JOIN tablas_estados te ON te.tabla_id=1 AND te.est_id=e.est_id
		JOIN tablas_nombres tn ON tn.tabla_id=te.tabla_id AND tabla_nom='ear_solicitudes'
		LEFT JOIN usu_detalle ud ON ud.usu_id=e.usu_id
		LEFT JOIN recursos.usuarios ru ON ru.usu_id=e.ear_act_usu
		JOIN pla_mov pm ON pm.ear_id=e.ear_id
		WHERE $q_fec_sol $q_cons $q_zona $q_moneda $q_estado AND e.pla_id IS NOT NULL
	) AS A

	GROUP BY ear_id, ear_tra_nombres, ear_numero, zona_nom, mon_nom, mon_iso, mon_img, ear_monto, est_id, est_nom, ear_sol_fec, ear_liq_fec,
	usu_act, ear_act_fec, ear_act_motivo, master_usu_id, ear_dese_fec

	LIMIT ?");
  $stmt->bind_param("i", $max);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array(
      $fila[0], $fila[1], $fila[2],
      $fila[3], $fila[4], $fila[5], $fila[6], $fila[7], $fila[8], $fila[9], $fila[10], $fila[11],
      $fila[12], $fila[13], $fila[14], $fila[15], $fila[16], $fila[17], $fila[18],
      $fila[19], $fila[20], $fila[21], $fila[22], $fila[23]
    );
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function getMensajeEstadoTopes($sol, $liq)
{
  if ($sol >= $liq) {
    $html = '<div style="font-weight:bold;color:white;background-color:green; text-align:center; width:100px;">Normal</div>';
  } else {
    $html = '<div style="font-weight:bold;color:white;background-color:red; text-align:center; width:100px;">Excedido</div>';
  }

  return $html;
}

function getValoresSemaforoEAR()
{
  $arr = array();
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT est_id, val_min_verde, val_min_ambar, val_min_rojo
		FROM ear_semaforo
		WHERE tabla_id=1");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[$fila[0]] = array($fila[1], $fila[2], $fila[3]);
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function getHabilitadoRegistrarEAR($usu_id)
{
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT val_val
		FROM valores_sistema
		WHERE val_id=4");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $nro_max_ear = $fila[0];
  }

  $stmt = $mysqli->prepare("SELECT val_val
		FROM valores_sistema
		WHERE val_id=5");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $nro_max_dias = $fila[0];
  }

  $stmt = $mysqli->prepare("SELECT COUNT(*)
		FROM ear_solicitudes
		WHERE usu_id=? AND est_id=4 AND DATEDIFF( NOW(), ear_act_fec ) >= ?");
  $stmt->bind_param("ii", $usu_id, $nro_max_dias);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $nro_sol = $fila[0];
  }

  if ($nro_sol < $nro_max_ear) {
    $valor = 1;
  } else {
    $valor = 0;
  }

  include 'datos_cerrar_bd.php';
  return $valor;
}

function getListaValSist()
{
  $arr = array();
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT val_id, val_desc, val_val
		FROM valores_sistema");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2]);
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function getSiNo($nro)
{
  if ($nro == 1)
    $val = 'Si';
  else if ($nro == 0)
    $val = 'No';
  else
    $val = 'Error';

  return $val;
}

function getTaxCodeDesc($nro)
{
  if ($nro == 1)
    $val = 'C1';
  else if ($nro == 2)
    $val = 'C0';
  else if ($nro == 3)
    $val = 'C1 C0';
  else if ($nro == 4)
    $val = 'C9';
  else if ($nro == 5)
    $val = 'Reglas';
  else
    $val = 'Error';

  return $val;
}

function getTipoCambioLista()
{
  $arr = array();
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT tc.tc_id, m.mon_nom, m.mon_iso, m.mon_img, tc.tc_fec, tc.tc_precio
		FROM tipo_cambio tc
		INNER JOIN monedas m ON m.mon_id=tc.mon_id
		ORDER BY tc.tc_fec DESC
		LIMIT 5000");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5]);
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function getTipoCambioInfo($id)
{
  $arr = array();
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT tc.tc_id, m.mon_nom, m.mon_iso, m.mon_img, tc.tc_fec, tc.tc_precio
		FROM tipo_cambio tc
		INNER JOIN monedas m ON m.mon_id=tc.mon_id
		WHERE tc.tc_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5]);
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function getUsuariosLista()
{
  $arr = array();
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT ru.usu_id, ru.usu_nombre, ud.usu_iniciales, ud.usu_estado, ud.gco_cobj, ud.usu_rol, ru2.usu_nombre
		FROM recursos.usuarios ru
		LEFT JOIN usu_detalle ud ON ud.usu_id=ru.usu_id
		LEFT JOIN recursos.usuarios ru2 ON ru2.usu_id=ud.usu_jefe
		LIMIT 5000");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5], $fila[6]);
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function getUsuarioInfo($id)
{
  $arr = array();
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT ud.usu_id, ru.usu_nombre, ud.usu_iniciales, ud.usu_estado, ud.gco_cobj, ud.usu_rol, ud.usu_jefe
		FROM recursos.usuarios ru
		LEFT JOIN usu_detalle ud ON ud.usu_id=ru.usu_id
		WHERE ru.usu_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5], $fila[6]);
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function getUsuarioNombre($id)
{
  $val = "";
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $stmt = $mysqli->prepare("select concat(ifnull(p.nombre,''),' ',ifnull(p.apellido_paterno,''),' ',ifnull(p.apellido_materno,''))
                                    as usu_nombre
                            from " . $baseSGI . ".persona p
                                    inner join " . $baseSGI . ".usuario u on p.id=u.persona_id
                            where u.id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $val = $fila[0];
  }

  include 'datos_cerrar_bd.php';
  return $val;
}

function getUsuariosListaRecursos()
{
  $arr = array();
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT ru.usu_id, ru.usu_nombre
		FROM recursos.usuarios ru");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1]);
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function getColObjectsLista($max)
{
  $arr = array();
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT gco.gco_id, gti.gti_nom, gco.gco_nom, gco.gco_cobj, gco.gco_act
		FROM gastos_colobjects gco
		INNER JOIN gastos_tipos gti ON gti.gti_id=gco.gti_id
		LIMIT ?");
  $stmt->bind_param("i", $max);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4]);
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function getColObjectsInfo($id)
{
  $arr = array();
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT gco.gco_id, gco.gti_id, gco.gco_nom, gco.gco_cobj, gco.gco_act
		FROM gastos_colobjects gco
		WHERE gco.gco_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4]);
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function getColObjectsTipos()
{
  $arr = array();
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT gti_id, gti_nom
		FROM gastos_tipos
		WHERE gti_id BETWEEN 2 AND 4
		ORDER BY gti_id");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1]);
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function getDetraccionesLista($max)
{
  $arr = array();
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT det.detr_id, det.conc_cod, ec.conc_nom, det.detr_desde_fec, det.detr_tasa, det.detr_min_monto
		FROM detracciones det
		LEFT JOIN ear_conceptos ec ON ec.conc_cod=det.conc_cod AND ec.mon_id=1
		LIMIT ?");
  $stmt->bind_param("i", $max);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5]);
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function getDetraccionInfo($id)
{
  $arr = array();
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT det.detr_id, det.conc_cod, ec.conc_nom, det.detr_desde_fec, det.detr_tasa, det.detr_min_monto
		FROM detracciones det
		LEFT JOIN ear_conceptos ec ON ec.conc_cod=det.conc_cod AND ec.mon_id=1
		WHERE det.detr_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5]);
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function getLiqConceptosSubcodigos()
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT DISTINCT conc_cod, conc_nom
		FROM ear_conceptos
		WHERE LENGTH(conc_cod)=4
		ORDER BY conc_cod");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getRetencionesLista($max)
{
  $arr = array();
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT ret.rete_id, ret.rete_desde_fec, ret.rete_tasa, ret.rete_min_monto
		FROM retenciones ret
		LIMIT ?");
  $stmt->bind_param("i", $max);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3]);
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function getRetencionInfo($id)
{
  $arr = array();
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT ret.rete_id, ret.rete_desde_fec, ret.rete_tasa, ret.rete_min_monto
		FROM retenciones ret
		WHERE ret.rete_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array($fila[0], $fila[1], $fila[2], $fila[3]);
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function getViaticosLista($max)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT ev.via_id, ev.via_cod, ev.via_nom, m.mon_nom, m.mon_iso, m.mon_img, ev.via_monto
		FROM ear_viaticos ev
		LEFT JOIN monedas m ON m.mon_id=ev.mon_id
		ORDER BY ev.via_cod
		LIMIT ?");
  $stmt->bind_param("i", $max);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5], $fila[6]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getViaticosInfo($id)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT ev.via_id, ev.via_cod, ev.via_nom, m.mon_nom, m.mon_iso, m.mon_img, ev.via_monto
		FROM ear_viaticos ev
		LEFT JOIN monedas m ON m.mon_id=ev.mon_id
		WHERE ev.via_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5], $fila[6]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getProveedoresLista($max)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT p.prov_id, p.ruc_nro, p.prov_nom, p.ruc_act, p.ruc_ret, p.ruc_hab, p.prov_factura, p.prov_provincia, p.ruc_chk_fec
		FROM proveedores p
		LIMIT ?");
  $stmt->bind_param("i", $max);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5], $fila[6], $fila[7], $fila[8]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getProveedoresInfo($id)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT p.prov_id, p.ruc_nro, p.prov_nom, p.ruc_act, p.ruc_ret, p.ruc_hab, p.prov_factura, p.prov_provincia, p.ruc_chk_fec
		FROM proveedores p
		WHERE p.prov_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5], $fila[6], $fila[7], $fila[8]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getSemaforosLista($max)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT es.est_id, te.est_nom, es.val_min_verde, es.val_min_ambar, es.val_min_rojo, tn.tabla_nom, es.sema_id
		FROM ear_semaforo es
		INNER JOIN tablas_estados te ON te.tabla_id=es.tabla_id AND te.est_id=es.est_id
		INNER JOIN tablas_nombres tn ON tn.tabla_id=es.tabla_id
		LIMIT ?");
  $stmt->bind_param("i", $max);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5], $fila[6]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getSemaforosInfo($id)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT es.est_id, te.est_nom, es.val_min_verde, es.val_min_ambar, es.val_min_rojo, tn.tabla_nom, es.sema_id
		FROM ear_semaforo es
		INNER JOIN tablas_estados te ON te.tabla_id=es.tabla_id AND te.est_id=es.est_id
		INNER JOIN tablas_nombres tn ON tn.tabla_id=es.tabla_id
		WHERE es.sema_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5], $fila[6]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getCalDctosLista($max)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT CONCAT(anio, '/', LPAD(mes, 2, '0')) aniomes, dia_tope, anio, mes
		FROM calendario_dctos
		LIMIT ?");
  $stmt->bind_param("i", $max);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getDiaTopeInfo($anio, $mes)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT dia_tope
		FROM calendario_dctos
		WHERE anio=? AND mes=?");
  $stmt->bind_param("ii", $anio, $mes);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array($fila[0]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getUsuRegOtrLista($max)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT uro.uro_id, ru1.usu_nombre, ru2.usu_nombre, uro.uro_act
		FROM usu_reg_otro uro
		INNER JOIN recursos.usuarios ru1 ON ru1.usu_id=uro.master_usu_id
		INNER JOIN recursos.usuarios ru2 ON ru2.usu_id=uro.slave_usu_id
		LIMIT ?");
  $stmt->bind_param("i", $max);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getUsuLista()
{
  $arr = array();

  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $stmt = $mysqli->prepare("select distinct u.id as usu_id,
                concat(p.nombre,' ',p.apellido_paterno,' ' ,p.apellido_materno) as usu_nombre
            from " . $baseSGI . ".usuario u
                inner join " . $baseSGI . ".persona p on u.persona_id = p.id
            where p.estado='1'
		and u.estado='1'");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getUsuRegOtrInfo($id)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT uro_id, master_usu_id, slave_usu_id, uro_act
		FROM usu_reg_otro
		WHERE uro_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array($fila[0], $fila[1], $fila[2], $fila[3]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getUsuRegOtroSlaves($id)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT uro.slave_usu_id, ru2.usu_nombre
		FROM usu_reg_otro uro
		INNER JOIN recursos.usuarios ru2 ON ru2.usu_id=uro.slave_usu_id
		WHERE uro.master_usu_id=? AND uro.uro_act=1");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getUsuRegOtroSlavesIds($id)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT uro.slave_usu_id
		FROM usu_reg_otro uro
		WHERE uro.master_usu_id=? AND uro.uro_act=1");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = $fila[0];
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getUsuRegOtroValidarAsig($master_usu_id, $slave_usu_id)
{
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT COUNT(*)
		FROM usu_reg_otro uro
		WHERE uro.master_usu_id=? AND uro.slave_usu_id=?");
  $stmt->bind_param("ii", $master_usu_id, $slave_usu_id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $val = $fila[0];
  }

  include 'datos_cerrar_bd.php';

  return $val;
}

function getDistGastDefault($id, $rec_usu_nombre_def, $adm_usu_gco_cobj_def)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT lid_gti, lid_dg_json
		FROM ear_dist_gast
		WHERE ear_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array($fila[0], $fila[1]);
  }

  if (count($arr) == 0) {
    $arr = array(1, "[[\"$rec_usu_nombre_def\",\"$adm_usu_gco_cobj_def\",\"100.00\"]]");
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getUsuGcoObj($id)
{
  $val = "";
  include 'datos_abrir_bd.php';

  //	$stmt = $mysqli->prepare("SELECT gco_cobj
  //		FROM usu_detalle
  //		WHERE usu_id=?");
  $stmt = $mysqli->prepare("SELECT '' as gco_cobj");
  //	$stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $val = $fila[0];
  }

  include 'datos_cerrar_bd.php';
  return $val;
}

function getUsuLiquidador($id)
{
  $val = "";
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT ear_act_usu
		FROM ear_actualizaciones
		WHERE ear_id=? AND est_id=5
		ORDER BY ear_act_fec DESC
		LIMIT 1");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $val = $fila[0];
  }

  include 'datos_cerrar_bd.php';
  return $val;
}

function getFechaEnvioLiq($id)
{
  $val = "";
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT ear_act_fec
		FROM ear_actualizaciones
		WHERE ear_id=? AND est_id=5
		ORDER BY ear_act_fec DESC
		LIMIT 1");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $val = $fila[0];
  }

  include 'datos_cerrar_bd.php';
  return $val;
}

function getUsuRegLssLista($max)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT url.usu_id, ru1.usu_nombre, url.url_act
		FROM usu_reg_liqsinsol url
		INNER JOIN recursos.usuarios ru1 ON ru1.usu_id=url.usu_id
		LIMIT ?");
  $stmt->bind_param("i", $max);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getUsuRegLssInfo($id)
{
  $val = "";

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT url_act
		FROM usu_reg_liqsinsol
		WHERE usu_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $val = $fila[0];
  }

  include 'datos_cerrar_bd.php';

  return $val;
}

function getAccesoRegLss($id)
{
  $val = "";

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT COUNT(*)
		FROM usu_reg_liqsinsol
		WHERE usu_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $val = $fila[0];
  }

  include 'datos_cerrar_bd.php';

  return $val;
}

function getUsuRegOtroSlavesJefes($id)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT uro.slave_usu_id, ru2.usu_nombre
		FROM usu_reg_otro uro
		INNER JOIN recursos.usuarios ru2 ON ru2.usu_id=uro.slave_usu_id
		INNER JOIN (SELECT DISTINCT usu_jefe FROM usu_detalle) uj ON uj.usu_jefe=uro.slave_usu_id
		WHERE uro.master_usu_id=? AND uro.uro_act=1");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getUsuRegOtroSlavesJefesIds($id)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT DISTINCT uro.slave_usu_id
		FROM usu_reg_otro uro
		INNER JOIN (SELECT DISTINCT usu_jefe FROM usu_detalle) uj ON uj.usu_jefe=uro.slave_usu_id
		WHERE uro.master_usu_id=? AND uro.uro_act=1");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = $fila[0];
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getUsuRegOtroMastersJefesIds($id)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT DISTINCT uro.master_usu_id
		FROM usu_reg_otro uro
		INNER JOIN (SELECT DISTINCT usu_jefe FROM usu_detalle) uj ON uj.usu_jefe=uro.slave_usu_id
		WHERE uro.master_usu_id=? AND uro.uro_act=1");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = $fila[0];
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getUsuEarAutoLista($max)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT eua.eua_id, ru1.usu_nombre, eua.fec_ini, eua.fec_fin, z.zona_nom, m.mon_nom, m.mon_iso, m.mon_img, eua.eua_monto, eua.eua_act
		FROM ear_usu_auto eua
		INNER JOIN recursos.usuarios ru1 ON ru1.usu_id=eua.usu_id
		LEFT JOIN ear_zonas z ON z.zona_id=eua.zona_id
		LEFT JOIN monedas m ON m.mon_id=eua.mon_id
		LIMIT ?");
  $stmt->bind_param("i", $max);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5], $fila[6], $fila[7], $fila[8], $fila[9]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getUsuEarAutoInfo($id)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT eua.eua_id, ru1.usu_nombre, eua.fec_ini, eua.fec_fin, z.zona_nom, m.mon_nom, m.mon_iso, m.mon_img, eua.eua_monto, eua.eua_act, eua.zona_id, eua.mon_id, eua.usu_id,
			eua.eua_tra_cta
		FROM ear_usu_auto eua
		INNER JOIN recursos.usuarios ru1 ON ru1.usu_id=eua.usu_id
		LEFT JOIN ear_zonas z ON z.zona_id=eua.zona_id
		LEFT JOIN monedas m ON m.mon_id=eua.mon_id
		WHERE eua.eua_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array(
      $fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5], $fila[6], $fila[7], $fila[8], $fila[9], $fila[10], $fila[11], $fila[12],
      $fila[13]
    );
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getUsuEarAutoListaVigente($fecha)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT eua.eua_id, ru1.usu_nombre, eua.fec_ini, eua.fec_fin, z.zona_nom, m.mon_nom, m.mon_iso, m.mon_img, eua.eua_monto, eua.eua_act, eua.zona_id, eua.mon_id, eua.usu_id,
			eua.eua_tra_cta
		FROM ear_usu_auto eua
		INNER JOIN recursos.usuarios ru1 ON ru1.usu_id=eua.usu_id
		LEFT JOIN ear_zonas z ON z.zona_id=eua.zona_id
		LEFT JOIN monedas m ON m.mon_id=eua.mon_id
		WHERE ? BETWEEN eua.fec_ini AND eua.fec_fin AND eua.eua_act=1");
  $stmt->bind_param("s", $fecha);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array(
      $fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5], $fila[6], $fila[7], $fila[8], $fila[9], $fila[10], $fila[11], $fila[12],
      $fila[13]
    );
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getMonedasLista($max)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT m.mon_id, m.mon_nom, m.mon_iso, m.mon_img
		FROM monedas m
		LIMIT ?");
  $stmt->bind_param("i", $max);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getIdHospOtros($mon_id)
{
  $via_cod = "030199";

  $val = "";

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT via_id
		FROM ear_viaticos
		WHERE via_cod=? AND mon_id=?");
  $stmt->bind_param("si", $via_cod, $mon_id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $val = $fila[0];
  }

  include 'datos_cerrar_bd.php';

  return $val;
}

function getDiasMinDesembolsado()
{
  $id = 4;

  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT val_min_ambar, val_min_rojo
		FROM ear_semaforo
		WHERE est_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array($fila[0], $fila[1]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getUsuariosEARsinliqLista($val_min_ambar)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT usu_id
		FROM ear_solicitudes
		WHERE est_id=4 AND DATEDIFF(now(), ear_dese_fec) > ?
		GROUP BY usu_id");
  $stmt->bind_param("i", $val_min_ambar);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = $fila[0];
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getUsuariosEARsinliqListaUsuarios()
{
  $arr = array();
  include 'parametros.php';
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT group_concat(s.ear_id) as id,
			   s.usu_id,
			   p.email,
			   concat(ifnull(p.nombre,''),' ',ifnull(p.apellido_paterno,''),' ',ifnull(p.apellido_materno,'')) as nombre,
			   u.persona_id
		FROM ear_solicitudes s
                INNER JOIN " . $baseSGI . ".usuario u on u.id = s.usu_id
                INNER JOIN " . $baseSGI . ".persona p on p.id = u.persona_id
		WHERE s.est_id = 4 AND now()  > s.ear_liq_estimada and p.email is not null
		GROUP BY s.usu_id");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array("ear_id" => $fila[0], "usuario_id" => $fila[1], "email" => $fila[2], "nombre" => $fila[3], "persona_id" => $fila[4]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getUsuariosEARsinliqListaJefe()
{
  $arr = array();
  include 'parametros.php';
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT group_concat(s.ear_id) as id,
			   u.usuario_padre_id,
			   p.email,
			   concat(ifnull(p.nombre,''),' ',ifnull(p.apellido_paterno,''),' ',ifnull(p.apellido_materno,'')) as nombre,
			   u.persona_id
		FROM ear_solicitudes s
		INNER JOIN " . $baseSGI . ".usuario u on u.id = s.usu_id
                INNER JOIN " . $baseSGI . ".usuario up on up.id = u.usuario_padre_id
		INNER JOIN " . $baseSGI . ".persona p on p.id = up.persona_id
		WHERE s.est_id = 4 AND now()  > s.ear_liq_estimada and p.email is not null
		GROUP BY u.usuario_padre_id");

  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array("ear_id" => $fila[0], "usuario_id" => $fila[1], "email" => $fila[2], "nombre" => $fila[3], "persona_id" => $fila[4]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getSumaDetallePlaMov($id)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT SUM(pmd_monto) sum_pmd_monto, SUM(pmd_emp_asume) sum_pmd_emp_asume
		FROM pla_mov_detalle
		WHERE pla_id=? AND pmd_aprob=1");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array($fila[0], $fila[1]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getSiNoActFijo($nro)
{
  if ($nro == 1)
    $val = "<img src='img/a.png' border='0' title='Activo Fijo' class='iconos'>";
  else if ($nro == 0)
    $val = "<img src='img/transparent.gif' border='0' class='iconos'>";
  else
    $val = 'Error';

  return $val;
}

function getSiNoCtrlVeh($nro)
{
  if ($nro >= 1 && $nro <= 3)
    $val = "<img src='img/vehiculo.png' border='0' title='Control Vehicular' class='iconos'>";
  else if ($nro == 4)
    $val = "<img src='img/ruta.png' border='0' title='Origen/Destino' class='iconos'>";
  else if ($nro == 0)
    $val = "<img src='img/transparent.gif' border='0' class='iconos'>";
  else
    $val = 'Error';

  return $val;
}

function getVehMarcasLista($max = 25000)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT vm_id, vm_nombre
		FROM veh_marcas
		ORDER BY vm_nombre
		LIMIT ?");
  $stmt->bind_param("i", $max);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getVehMarcasInfo($id)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT vm_id, vm_nombre
		FROM veh_marcas
		WHERE vm_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array($fila[0], $fila[1]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getVehiculosLista($max)
{
  $arr = array();

  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $stmt = $mysqli->prepare("SELECT v.veh_id, v.veh_placa, vm.vm_nombre, v.veh_modelo,
                    concat(sp.nombre,' ',sp.apellido_paterno,' ',sp.apellido_materno) as usu_nombre, v.veh_act
		FROM vehiculos v
                    INNER JOIN veh_marcas vm ON vm.vm_id=v.vm_id
                    left join " . $baseSGI . ".usuario su on su.id=v.usu_id
                    left join " . $baseSGI . ".persona sp on sp.id=su.persona_id
		LIMIT ?");
  $stmt->bind_param("i", $max);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getVehiculosInfo($id)
{
  $arr = array();

  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $stmt = $mysqli->prepare("SELECT v.veh_id, v.veh_placa, v.vm_id, v.veh_modelo, v.usu_id, v.veh_act, vm.vm_nombre,
                concat(sp.nombre,' ',sp.apellido_paterno,' ',sp.apellido_materno) as usu_nombre
            FROM vehiculos v
		INNER JOIN veh_marcas vm ON vm.vm_id=v.vm_id
		left join " . $baseSGI . ".usuario su on su.id=v.usu_id
		left join " . $baseSGI . ".persona sp on sp.id=su.persona_id
            WHERE v.veh_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5], $fila[6], $fila[7]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getVehiculosActivosLista()
{
  $arr = array();

  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $stmt = $mysqli->prepare("SELECT  v.veh_id, v.veh_placa, v.vm_id, v.veh_modelo, v.usu_id, v.veh_act, vm.vm_nombre,
		(select concat(ifnull(p.nombre,''),' ',ifnull(p.apellido_paterno,''),' ',ifnull(p.apellido_materno,''))
			from " . $baseSGI . ".persona p where p.id=ru.persona_id) as usu_nombre
		FROM vehiculos v
		INNER JOIN veh_marcas vm ON vm.vm_id=v.vm_id
		LEFT JOIN " . $baseSGI . ".usuario ru ON ru.id=v.usu_id
		WHERE v.veh_act=1");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5], $fila[6], $fila[7]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getVehiculoAsignado($usu_id)
{
  $val = -2;

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT veh_id
		FROM vehiculos
		WHERE usu_id=?");
  $stmt->bind_param("i", $usu_id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $val = $fila[0];
  }

  include 'datos_cerrar_bd.php';

  return $val;
}

function getCajasChicasLista($max = 25000)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT cch.cch_id, cch.cch_nombre, rsu.suc_nombre,
			m.mon_nom, m.mon_iso, m.mon_img, cch.cch_monto,
			cch.cch_abrv, cch.cch_gti, cch.cch_dg_json, cch.cch_cta_bco, cch.cch_act
		FROM cajas_chicas cch
		LEFT JOIN recursos.sucursales rsu ON rsu.suc_id=cch.suc_id
		LEFT JOIN monedas m ON m.mon_id=cch.mon_id
		ORDER BY cch_nombre
		LIMIT ?");
  $stmt->bind_param("i", $max);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array(
      $fila[0], $fila[1], $fila[2],
      $fila[3], $fila[4], $fila[5], $fila[6],
      $fila[7], $fila[8], $fila[9], $fila[10], $fila[11]
    );
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getCajasChicasActivas()
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT cch.cch_id, cch.cch_nombre, cch.cch_abrv, m.mon_iso
		FROM cajas_chicas cch
		LEFT JOIN monedas m ON m.mon_id=cch.mon_id
		WHERE cch.cch_act=1");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getSucursalesLista($max = 25000)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT suc_id, suc_nombre
		FROM recursos.sucursales
		WHERE suc_activo=1
		ORDER BY suc_nombre
		LIMIT ?");
  $stmt->bind_param("i", $max);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getCajasChicasInfo($id)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT cch.cch_id, cch.cch_nombre, rsu.suc_nombre,
			m.mon_nom, m.mon_iso, m.mon_img, cch.cch_monto,
			cch.cch_abrv, cch.cch_gti, cch.cch_dg_json, cch.cch_cta_bco, cch.cch_act,
			cch.suc_id, cch.mon_id
		FROM cajas_chicas cch
		LEFT JOIN recursos.sucursales rsu ON rsu.suc_id=cch.suc_id
		LEFT JOIN monedas m ON m.mon_id=cch.mon_id
		WHERE cch.cch_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array(
      $fila[0], $fila[1], $fila[2],
      $fila[3], $fila[4], $fila[5], $fila[6],
      $fila[7], $fila[8], $fila[9], $fila[10], $fila[11],
      $fila[12], $fila[13]
    );
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getCajasChicasRespLista($max = 25000)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT ccr.ccr_id, cch.cch_nombre, cch.cch_abrv, ru.usu_nombre, ccr.ccr_act
		FROM cajas_chicas_resp ccr
		INNER JOIN cajas_chicas cch ON cch.cch_id=ccr.cch_id
		LEFT JOIN recursos.usuarios ru ON ru.usu_id=ccr.usu_id
		ORDER BY cch.cch_nombre, ru.usu_nombre
		LIMIT ?");
  $stmt->bind_param("i", $max);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getCajasChicasActivasLista($max = 25000)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT cch.cch_id, cch.cch_nombre, cch.cch_abrv
		FROM cajas_chicas cch
		WHERE cch.cch_act=1
		ORDER BY cch_nombre
		LIMIT ?");
  $stmt->bind_param("i", $max);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getCajasChicasRespInfo($id)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT ccr.ccr_id, cch.cch_nombre, cch.cch_abrv, ru.usu_nombre, ccr.ccr_act,
			ccr.cch_id, ccr.usu_id
		FROM cajas_chicas_resp ccr
		INNER JOIN cajas_chicas cch ON cch.cch_id=ccr.cch_id
		LEFT JOIN recursos.usuarios ru ON ru.usu_id=ccr.usu_id
		WHERE ccr.ccr_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array(
      $fila[0], $fila[1], $fila[2], $fila[3], $fila[4],
      $fila[5], $fila[6]
    );
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getCajasChicasEncLista($max = 25000)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT cce.cce_id, cch.cch_nombre, cch.cch_abrv, ru.usu_nombre, cce.cce_act
		FROM cajas_chicas_enc cce
		INNER JOIN cajas_chicas cch ON cch.cch_id=cce.cch_id
		LEFT JOIN recursos.usuarios ru ON ru.usu_id=cce.usu_id
		ORDER BY cch.cch_nombre, ru.usu_nombre
		LIMIT ?");
  $stmt->bind_param("i", $max);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getCajasChicasEncInfo($id)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT cce.cce_id, cch.cch_nombre, cch.cch_abrv, ru.usu_nombre, cce.cce_act,
			cce.cch_id, cce.usu_id
		FROM cajas_chicas_enc cce
		INNER JOIN cajas_chicas cch ON cch.cch_id=cce.cch_id
		LEFT JOIN recursos.usuarios ru ON ru.usu_id=cce.usu_id
		WHERE cce.cce_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array(
      $fila[0], $fila[1], $fila[2], $fila[3], $fila[4],
      $fila[5], $fila[6]
    );
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getCajasChicasEncAcceso($id)
{
  if (getUserGodMode($id) > 0) {
    //god_mode
    $q_cons = "(0=0 OR 0=?)";
  } else {
    //normal_mode
    $q_cons = "(cce.usu_id=? AND cce.cce_act=1)";
  }

  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT DISTINCT cch.cch_id, cch.cch_nombre, cch.cch_abrv
		FROM cajas_chicas cch
		INNER JOIN cajas_chicas_enc cce ON cce.cch_id=cch.cch_id
		WHERE cch.cch_act=1 AND $q_cons
		ORDER BY cch.cch_nombre");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getCajasChicasRespAcceso($id)
{
  if (getUserGodMode($id) > 0) {
    //god_mode
    $q_cons = "0=0 OR 0=?";
  } else {
    //normal_mode
    $q_cons = "ccr.usu_id=? AND ccr.ccr_act=1";
  }

  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT cch.cch_id, cch.cch_nombre, cch.cch_abrv
		FROM cajas_chicas cch
		LEFT JOIN cajas_chicas_resp ccr ON ccr.cch_id=cch.cch_id
		WHERE $q_cons
		ORDER BY cch.cch_nombre");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getUltLoteCajaChica($id)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT l.ccl_id, cch.cch_nombre, CONCAT(l.ccl_anio, '-', LPAD(l.ccl_mes, 2, '0'), '-', LPAD(l.ccl_nro, 3, '0'), '/', cch.cch_abrv) ccl_numero,
			m.mon_nom, m.mon_iso, m.mon_simb, m.mon_img, l.ccl_monto_ini, l.ccl_gti, l.ccl_dg_json, l.ccl_cta_bco,
			l.ccl_ape_fec, ru1.usu_nombre, l.ccl_cie_fec, ru2.usu_nombre,
			l.ccl_aprob_fec, ru3.usu_nombre, l.ccl_act_fec, ru4.usu_nombre,
			l.ccl_monto_usado, l.est_id, te.est_nom, rsu.suc_nombre,
			l.ccl_ret, l.ccl_ret_no, l.ccl_det, l.ccl_det_no, l.ccl_gast_asum, l.ccl_pend, l.cch_id, l.mon_id,
			l.ccl_cuadre, l.ccl_banco, COALESCE(l.ccl_desemb_est, 0) ccl_desemb_est
		FROM cajas_chicas_lote l
		INNER JOIN cajas_chicas cch ON cch.cch_id=l.cch_id
		LEFT JOIN monedas m ON m.mon_id=l.mon_id
		LEFT JOIN recursos.usuarios ru1 ON ru1.usu_id=l.ccl_ape_usu
		LEFT JOIN recursos.usuarios ru2 ON ru2.usu_id=l.ccl_cie_usu
		LEFT JOIN recursos.usuarios ru3 ON ru3.usu_id=l.ccl_aprob_usu
		LEFT JOIN recursos.usuarios ru4 ON ru4.usu_id=l.ccl_act_usu
		LEFT JOIN tablas_estados te ON te.tabla_id=3 AND te.est_id=l.est_id
		LEFT JOIN recursos.sucursales rsu ON rsu.suc_id=cch.suc_id
		WHERE l.cch_id=?
		ORDER BY ccl_id DESC
		LIMIT 1");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array(
      $fila[0], $fila[1], $fila[2],
      $fila[3], $fila[4], $fila[5], $fila[6], $fila[7], $fila[8], $fila[9], $fila[10],
      $fila[11], $fila[12], $fila[13], $fila[14],
      $fila[15], $fila[16], $fila[17], $fila[18],
      $fila[19], $fila[20], $fila[21], $fila[22],
      $fila[23], $fila[24], $fila[25], $fila[26], $fila[27], $fila[28], $fila[29], $fila[30],
      $fila[31], $fila[32], $fila[33]
    );
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function nuevoLoteCajaChica($id, $usu_id, $prev = -1)
{
  $arr = getCajasChicasInfo($id);
  if (empty($arr)) {
    return "ERROR: Valor no existe";
  }
  list(
    $cch_id, $cch_nombre, $suc_nombre, $mon_nom, $mon_iso, $mon_img, $cch_monto,
    $cch_abrv, $cch_gti, $cch_dg_json, $cch_cta_bco, $cch_act,
    $suc_id, $mon_id
  ) = $arr;

  $anio = date('Y');
  $mes = date('m');

  include 'datos_abrir_bd.php';

  $mysqli->autocommit(FALSE);

  $query = "SELECT now()";
  $result = $mysqli->query($query) or die($mysqli->error);
  $fila = $result->fetch_array();
  $ahora = $fila[0];

  $stmt = $mysqli->prepare("INSERT INTO cajas_chicas_lote (cch_id, ccl_anio, ccl_mes, ccl_nro, mon_id, ccl_monto_ini, ccl_gti, ccl_dg_json, ccl_cta_bco, est_id,
		ccl_ape_fec, ccl_ape_usu, ccl_act_fec, ccl_act_usu, ccl_monto_usado, ccl_ret, ccl_ret_no, ccl_det, ccl_det_no, ccl_gast_asum, ccl_pend, ccl_cuadre, ccl_banco)
	SELECT ?, ?, ?, max_nro, ?, ?, ?, ?, ?, 1, ?, ?, ?, ?, 0, 0, 0, 0, 0, 0, 0, 0, 0
	FROM (
		SELECT IFNULL(MAX(ccl_nro), 0)+1 AS max_nro FROM cajas_chicas_lote
		WHERE cch_id=? AND ccl_anio=? AND ccl_mes=?) AS sub_tabla") or die($mysqli->error);
  $stmt->bind_param("iiiidisssisiiii", $id, $anio, $mes, $mon_id, $cch_monto, $cch_gti, $cch_dg_json, $cch_cta_bco, $ahora, $usu_id, $ahora, $usu_id, $id, $anio, $mes);
  $stmt->execute() or die($mysqli->error);
  $insertion_id = $mysqli->insert_id;

  $stmt = $mysqli->prepare("INSERT INTO cajas_chicas_lote_act VALUES (?, 1, ?, ?, null)") or die($mysqli->error);
  $stmt->bind_param('iis', $insertion_id, $usu_id, $ahora);
  $stmt->execute() or die($mysqli->error);

  $stmt = $mysqli->prepare("INSERT INTO cajas_chicas_lote_docp (ccl_id, usu_id, cldp_anio, cldp_nro, cldp_reg_fec, cldp_ent_fec, cldp_conc, cldp_monto, est_id,
			cldp_prdc_fec, cldp_prdc_usu, cldp_desc_fec, cldp_desc_usu, cldp_reem_fec, cldp_reem_usu)
		SELECT ?, usu_id, cldp_anio, cldp_nro, cldp_reg_fec, cldp_ent_fec, cldp_conc, cldp_monto, est_id,
			cldp_prdc_fec, cldp_prdc_usu, cldp_desc_fec, cldp_desc_usu, cldp_reem_fec, cldp_reem_usu
		FROM cajas_chicas_lote_docp
		WHERE ccl_id=? AND est_id=1") or die($mysqli->error);
  $stmt->bind_param('ii', $insertion_id, $prev);
  $stmt->execute() or die($mysqli->error);

  $stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die($mysqli->error);
  $desc = "Nuevo lote ($insertion_id) caja chica ($id) hecha por " . getUsuarioNombre($usu_id);
  $ip = $_SERVER['REMOTE_ADDR'];
  $host = gethostbyaddr($ip);
  $stmt->bind_param('issss', $usu_id, $desc, $ahora, $ip, $host);
  $stmt->execute() or die($mysqli->error);

  $stmt->close();

  $mysqli->commit();

  include 'datos_cerrar_bd.php';
}

function getLoteCajaChicaInfo($id)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT l.ccl_id, cch.cch_nombre, CONCAT(l.ccl_anio, '-', LPAD(l.ccl_mes, 2, '0'), '-', LPAD(l.ccl_nro, 3, '0'), '/', cch.cch_abrv) ccl_numero,
			m.mon_nom, m.mon_iso, m.mon_simb, m.mon_img, l.ccl_monto_ini, l.ccl_gti, l.ccl_dg_json, l.ccl_cta_bco,
			l.ccl_ape_fec, ru1.usu_nombre, l.ccl_cie_fec, ru2.usu_nombre,
			l.ccl_aprob_fec, ru3.usu_nombre, l.ccl_act_fec, ru4.usu_nombre,
			l.ccl_monto_usado, l.est_id, te.est_nom, rsu.suc_nombre,
			l.ccl_ret, l.ccl_ret_no, l.ccl_det, l.ccl_det_no, l.ccl_gast_asum, l.ccl_pend, l.cch_id, l.mon_id,
			l.ccl_ape_usu, l.ccl_cie_usu, l.ccl_aprob_usu, l.ccl_act_usu,
			l.ccl_cuadre, l.ccl_banco, l.ccl_aju, l.ccl_desemb, cch.cch_abrv
		FROM cajas_chicas_lote l
		INNER JOIN cajas_chicas cch ON cch.cch_id=l.cch_id
		LEFT JOIN monedas m ON m.mon_id=l.mon_id
		LEFT JOIN recursos.usuarios ru1 ON ru1.usu_id=l.ccl_ape_usu
		LEFT JOIN recursos.usuarios ru2 ON ru2.usu_id=l.ccl_cie_usu
		LEFT JOIN recursos.usuarios ru3 ON ru3.usu_id=l.ccl_aprob_usu
		LEFT JOIN recursos.usuarios ru4 ON ru4.usu_id=l.ccl_act_usu
		LEFT JOIN tablas_estados te ON te.tabla_id=3 AND te.est_id=l.est_id
		LEFT JOIN recursos.sucursales rsu ON rsu.suc_id=cch.suc_id
		WHERE l.ccl_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array(
      $fila[0], $fila[1], $fila[2],
      $fila[3], $fila[4], $fila[5], $fila[6], $fila[7], $fila[8], $fila[9], $fila[10],
      $fila[11], $fila[12], $fila[13], $fila[14],
      $fila[15], $fila[16], $fila[17], $fila[18],
      $fila[19], $fila[20], $fila[21], $fila[22],
      $fila[23], $fila[24], $fila[25], $fila[26], $fila[27], $fila[28], $fila[29], $fila[30],
      $fila[31], $fila[32], $fila[33], $fila[34],
      $fila[35], $fila[36], $fila[37], $fila[38], $fila[39]
    );
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getLoteDetalle($id)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT eld.conc_id, ec.conc_cod, eld.doc_id, eld.ruc_nro, CASE WHEN (eld.doc_id!=10 AND LENGTH(eld.ruc_nro)=8) THEN '' ELSE pr.prov_nom END AS prov_nom,
			IFNULL(pr.ruc_ret,0) ruc_ret, eld.lid_fec, eld.lid_ser, eld.lid_nro, UPPER(eld.lid_glo) lid_glo,
			eld.mon_id, eld.lid_afe, eld.lid_mon_afe, eld.lid_mon_naf, eld.lid_tc,
			eld.lid_retdet_apl, eld.lid_retdet_tip, eld.lid_retdet_mon, eld.lid_gti, eld.lid_dg_json,
			eld.lid_cta_cont, eld.lid_aprob, eld.lid_emp_asume, dt.doc_ruc_req, mo.mon_iso,
			dt.doc_nro, dt.doc_cod, ec.conc_acf, IFNULL(pr.ruc_act,-1) ruc_act, IFNULL(pr.ruc_hab,-1) ruc_hab,
			IFNULL(pr.prov_factura,-1) prov_factura, IFNULL(pr.prov_provincia,'') prov_provincia, dt.doc_tax_code,
			ec.conc_cve, eld.veh_id, eld.veh_km, UPPER(eld.lid_glo2) lid_glo2, ec.conc_fmt_glosa
		FROM cajas_chicas_lote_det eld
		LEFT JOIN ear_conceptos ec ON ec.conc_id=eld.conc_id
		LEFT JOIN proveedores pr ON pr.ruc_nro=eld.ruc_nro
		LEFT JOIN doc_tipos dt ON dt.doc_id=eld.doc_id
		LEFT JOIN monedas mo ON mo.mon_id=eld.mon_id
		WHERE ccl_id=?
		ORDER BY conc_cod, lid_ser, CAST(lid_nro AS UNSIGNED)");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array(
      $fila[0], $fila[1], $fila[2], $fila[3], $fila[4],
      $fila[5], $fila[6], $fila[7], $fila[8], $fila[9],
      $fila[10], $fila[11], $fila[12], $fila[13], $fila[14],
      $fila[15], $fila[16], $fila[17], $fila[18], $fila[19],
      $fila[20], $fila[21], $fila[22], $fila[23], $fila[24],
      $fila[25], $fila[26], $fila[27], $fila[28], $fila[29],
      $fila[30], $fila[31], $fila[32],
      $fila[33], $fila[34], $fila[35], $fila[36], $fila[37]
    );
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getEncargadosCaja($id)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT usu_id
		FROM cajas_chicas_enc
		WHERE cch_id=? AND cce_act=1");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = $fila[0];
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getResponsablesCaja($id)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT usu_id
		FROM cajas_chicas_resp
		WHERE cch_id=? AND ccr_act=1");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = $fila[0];
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getLotesCajaChicaLista($mode_id, $usu_id = null)
{
  $arr = array();

  $q_joins = "";
  switch ($mode_id) {
    case 2:
      // Lista los lotes para ser aprobados por el responsable
      if (getUserGodMode($usu_id) > 0) {
        // god_mode
        // No se hace INNER JOIN
      } else {
        // normal_mode
        $q_joins = "INNER JOIN cajas_chicas_resp ccr ON ccr.cch_id=l.cch_id AND ccr.usu_id=$usu_id AND ccr.ccr_act=1";
      }
      $q_est = "l.est_id=2";
      break;
    case 3:
      // Lista los lotes para ser desembolsados por tesoreria
      $q_est = "l.est_id IN (3,5,6,7) AND COALESCE(l.ccl_desemb_est, 0) = 0";
      break;
    case 4:
      // Lista los lotes para ser consultados por el encargado (readonly)
      if (getUserGodMode($usu_id) > 0) {
        // god_mode
        // No se hace INNER JOIN
      } else {
        // normal_mode
        $q_joins = "INNER JOIN cajas_chicas_enc cce ON cce.cch_id=l.cch_id AND cce.usu_id=$usu_id AND cce.cce_act=1";
      }
      $q_est = "0=0";
      break;
    case 5:
      // Lista los lotes para ser consultados por el responsable (readonly)
      if (getUserGodMode($usu_id) > 0) {
        // god_mode
        // No se hace INNER JOIN
      } else {
        // normal_mode
        $q_joins = "INNER JOIN cajas_chicas_resp ccr ON ccr.cch_id=l.cch_id AND ccr.usu_id=$usu_id AND ccr.ccr_act=1";
      }
      $q_est = "0=0";
      break;
    case 6:
      // Lista los lotes para ser reembolsados por tesoreria (despues de ser revisados por contabilidad)
      $q_est = "l.est_id=5 AND l.ccl_aju>0";
      break;
    case 7:
      // Lista los lotes para ser descontados por compensaciones (despues de ser revisados por contabilidad)
      $q_est = "l.est_id=5 AND l.ccl_aju<0";
      break;
    default:
      $q_joins = "";
      $q_est = "0=0";
  }

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT ccl_id, cch_nombre, ccl_numero,
			mon_nom, mon_iso, mon_simb, mon_img, ccl_monto_ini, ccl_gti, ccl_dg_json, ccl_cta_bco,
			ccl_ape_fec, ru1_usu_nombre, ccl_cie_fec, ru2_usu_nombre,
			ccl_aprob_fec, ru3_usu_nombre, ccl_act_fec, ru4_usu_nombre,
			ccl_monto_usado, est_id, est_nom, suc_nombre,
			ccl_ret, ccl_ret_no, ccl_det, ccl_det_no, ccl_gast_asum, ccl_pend, cch_id,
			ccl_desemb, ccl_aju,
			SUM(pdf_plm) pdf_plm,
			SUM(pdf_docp) pdf_docp,
			ccl_desemb_est, ccl_desemb_fec
		FROM
		(
		SELECT l.ccl_id, cch.cch_nombre, CONCAT(l.ccl_anio, '-', LPAD(l.ccl_mes, 2, '0'), '-', LPAD(l.ccl_nro, 3, '0'), '/', cch.cch_abrv) ccl_numero,
			m.mon_nom, m.mon_iso, m.mon_simb, m.mon_img, l.ccl_monto_ini, l.ccl_gti, l.ccl_dg_json, l.ccl_cta_bco,
			l.ccl_ape_fec, ru1.usu_nombre ru1_usu_nombre, l.ccl_cie_fec, ru2.usu_nombre ru2_usu_nombre,
			l.ccl_aprob_fec, ru3.usu_nombre ru3_usu_nombre, l.ccl_act_fec, ru4.usu_nombre ru4_usu_nombre,
			l.ccl_monto_usado, l.est_id, te.est_nom, rsu.suc_nombre,
			l.ccl_ret, l.ccl_ret_no, l.ccl_det, l.ccl_det_no, l.ccl_gast_asum, l.ccl_pend, l.cch_id,
			l.ccl_desemb, l.ccl_aju,
			CASE WHEN pm.ccl_id IS NOT NULL THEN 1 ELSE 0 END AS pdf_plm,
			0 AS pdf_docp,
			COALESCE(l.ccl_desemb_est, 0) ccl_desemb_est, l.ccl_desemb_fec
		FROM cajas_chicas_lote l
		INNER JOIN cajas_chicas cch ON cch.cch_id=l.cch_id
		LEFT JOIN monedas m ON m.mon_id=l.mon_id
		LEFT JOIN recursos.usuarios ru1 ON ru1.usu_id=l.ccl_ape_usu
		LEFT JOIN recursos.usuarios ru2 ON ru2.usu_id=l.ccl_cie_usu
		LEFT JOIN recursos.usuarios ru3 ON ru3.usu_id=l.ccl_aprob_usu
		LEFT JOIN recursos.usuarios ru4 ON ru4.usu_id=l.ccl_act_usu
		LEFT JOIN tablas_estados te ON te.tabla_id=3 AND te.est_id=l.est_id
		LEFT JOIN recursos.sucursales rsu ON rsu.suc_id=cch.suc_id
		LEFT JOIN pla_mov pm ON pm.ccl_id=l.ccl_id AND pm.est_id=15
		$q_joins
		WHERE $q_est

		UNION ALL

		SELECT l.ccl_id, cch.cch_nombre, CONCAT(l.ccl_anio, '-', LPAD(l.ccl_mes, 2, '0'), '-', LPAD(l.ccl_nro, 3, '0'), '/', cch.cch_abrv) ccl_numero,
			m.mon_nom, m.mon_iso, m.mon_simb, m.mon_img, l.ccl_monto_ini, l.ccl_gti, l.ccl_dg_json, l.ccl_cta_bco,
			l.ccl_ape_fec, ru1.usu_nombre ru1_usu_nombre, l.ccl_cie_fec, ru2.usu_nombre ru2_usu_nombre,
			l.ccl_aprob_fec, ru3.usu_nombre ru3_usu_nombre, l.ccl_act_fec, ru4.usu_nombre ru4_usu_nombre,
			l.ccl_monto_usado, l.est_id, te.est_nom, rsu.suc_nombre,
			l.ccl_ret, l.ccl_ret_no, l.ccl_det, l.ccl_det_no, l.ccl_gast_asum, l.ccl_pend, l.cch_id,
			l.ccl_desemb, l.ccl_aju,
			0 AS pdf_plm,
			CASE WHEN dp.ccl_id IS NOT NULL THEN 1 ELSE 0 END AS pdf_docp,
			COALESCE(l.ccl_desemb_est, 0) ccl_desemb_est, l.ccl_desemb_fec
		FROM cajas_chicas_lote l
		INNER JOIN cajas_chicas cch ON cch.cch_id=l.cch_id
		LEFT JOIN monedas m ON m.mon_id=l.mon_id
		LEFT JOIN recursos.usuarios ru1 ON ru1.usu_id=l.ccl_ape_usu
		LEFT JOIN recursos.usuarios ru2 ON ru2.usu_id=l.ccl_cie_usu
		LEFT JOIN recursos.usuarios ru3 ON ru3.usu_id=l.ccl_aprob_usu
		LEFT JOIN recursos.usuarios ru4 ON ru4.usu_id=l.ccl_act_usu
		LEFT JOIN tablas_estados te ON te.tabla_id=3 AND te.est_id=l.est_id
		LEFT JOIN recursos.sucursales rsu ON rsu.suc_id=cch.suc_id
		LEFT JOIN cajas_chicas_lote_docp dp ON dp.ccl_id=l.ccl_id
		$q_joins
		WHERE $q_est
		) AS A

		GROUP BY ccl_id, cch_nombre, ccl_numero,
			mon_nom, mon_iso, mon_simb, mon_img, ccl_monto_ini, ccl_gti, ccl_dg_json, ccl_cta_bco,
			ccl_ape_fec, ru1_usu_nombre, ccl_cie_fec, ru2_usu_nombre,
			ccl_aprob_fec, ru3_usu_nombre, ccl_act_fec, ru4_usu_nombre,
			ccl_monto_usado, est_id, est_nom, suc_nombre,
			ccl_ret, ccl_ret_no, ccl_det, ccl_det_no, ccl_gast_asum, ccl_pend, cch_id,
			ccl_desemb, ccl_aju, ccl_desemb_est, ccl_desemb_fec");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array(
      $fila[0], $fila[1], $fila[2],
      $fila[3], $fila[4], $fila[5], $fila[6], $fila[7], $fila[8], $fila[9], $fila[10],
      $fila[11], $fila[12], $fila[13], $fila[14],
      $fila[15], $fila[16], $fila[17], $fila[18],
      $fila[19], $fila[20], $fila[21], $fila[22],
      $fila[23], $fila[24], $fila[25], $fila[26], $fila[27], $fila[28], $fila[29],
      $fila[30], $fila[31],
      $fila[32],
      $fila[33],
      $fila[34], $fila[35]
    );
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getLotesCajaChicaContaLista()
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT ccl_id, cch_nombre, ccl_numero, mon_nom, mon_iso, mon_simb, mon_img, ccl_monto_ini, ccl_gti, ccl_dg_json, ccl_cta_bco,
			ccl_ape_fec, ape_usu_nombre, ccl_cie_fec, cie_usu_nombre, ccl_aprob_fec, aprob_usu_nombre, ccl_act_fec, act_usu_nombre,
			ccl_monto_usado, est_id, est_nom, suc_nombre, ccl_ret, ccl_ret_no, ccl_det, ccl_det_no, ccl_gast_asum, ccl_pend, cch_id, ccl_desemb,
			SUM(deta_liq) deta_liq, SUM(deta_ret) deta_ret, SUM(deta_det) deta_det, SUM(deta_acf) deta_acf, SUM(deta_aju) deta_aju
		FROM
		(
		SELECT l.ccl_id, cch.cch_nombre, CONCAT(l.ccl_anio, '-', LPAD(l.ccl_mes, 2, '0'), '-', LPAD(l.ccl_nro, 3, '0'), '/', cch.cch_abrv) ccl_numero,
			m.mon_nom, m.mon_iso, m.mon_simb, m.mon_img, l.ccl_monto_ini, l.ccl_gti, l.ccl_dg_json, l.ccl_cta_bco,
			l.ccl_ape_fec, ru1.usu_nombre ape_usu_nombre, l.ccl_cie_fec, ru2.usu_nombre cie_usu_nombre,
			l.ccl_aprob_fec, ru3.usu_nombre aprob_usu_nombre, l.ccl_act_fec, ru4.usu_nombre act_usu_nombre,
			l.ccl_monto_usado, l.est_id, te.est_nom, rsu.suc_nombre,
			l.ccl_ret, l.ccl_ret_no, l.ccl_det, l.ccl_det_no, l.ccl_gast_asum, l.ccl_pend, l.cch_id,
			l.ccl_desemb,
			CASE WHEN eld.lid_aprob = 1 AND eld.lid_retdet_tip = 0 AND ec.conc_acf = 0 THEN 1 ELSE 0 END as deta_liq,
			CASE WHEN eld.lid_aprob = 1 AND eld.lid_retdet_tip = 2 THEN 1 ELSE 0 END as deta_ret,
			CASE WHEN eld.lid_aprob = 1 AND eld.lid_retdet_tip = 1 THEN 1 ELSE 0 END as deta_det,
			CASE WHEN eld.lid_aprob = 1 AND ec.conc_acf = 1 THEN 1 ELSE 0 END as deta_acf,
			CASE WHEN eld.lid_aprob = 1 AND eld.lid_mon_afe + eld.lid_mon_naf - eld.lid_emp_asume > 0 THEN 1 ELSE 0 END as deta_aju
		FROM cajas_chicas_lote l
		INNER JOIN cajas_chicas cch ON cch.cch_id=l.cch_id
		LEFT JOIN monedas m ON m.mon_id=l.mon_id
		LEFT JOIN recursos.usuarios ru1 ON ru1.usu_id=l.ccl_ape_usu
		LEFT JOIN recursos.usuarios ru2 ON ru2.usu_id=l.ccl_cie_usu
		LEFT JOIN recursos.usuarios ru3 ON ru3.usu_id=l.ccl_aprob_usu
		LEFT JOIN recursos.usuarios ru4 ON ru4.usu_id=l.ccl_act_usu
		LEFT JOIN tablas_estados te ON te.tabla_id=3 AND te.est_id=l.est_id
		LEFT JOIN recursos.sucursales rsu ON rsu.suc_id=cch.suc_id
		LEFT JOIN cajas_chicas_lote_det eld ON eld.ccl_id=l.ccl_id
		LEFT JOIN ear_conceptos ec ON ec.conc_id=eld.conc_id
		WHERE l.est_id IN (3, 4)

		UNION ALL

		SELECT l.ccl_id, cch.cch_nombre, CONCAT(l.ccl_anio, '-', LPAD(l.ccl_mes, 2, '0'), '-', LPAD(l.ccl_nro, 3, '0'), '/', cch.cch_abrv) ccl_numero,
			m.mon_nom, m.mon_iso, m.mon_simb, m.mon_img, l.ccl_monto_ini, l.ccl_gti, l.ccl_dg_json, l.ccl_cta_bco,
			l.ccl_ape_fec, ru1.usu_nombre ape_usu_nombre, l.ccl_cie_fec, ru2.usu_nombre cie_usu_nombre,
			l.ccl_aprob_fec, ru3.usu_nombre aprob_usu_nombre, l.ccl_act_fec, ru4.usu_nombre act_usu_nombre,
			l.ccl_monto_usado, l.est_id, te.est_nom, rsu.suc_nombre,
			l.ccl_ret, l.ccl_ret_no, l.ccl_det, l.ccl_det_no, l.ccl_gast_asum, l.ccl_pend, l.cch_id,
			l.ccl_desemb,
			#CASE WHEN pm.pla_monto > 0 THEN 1 ELSE 0 END AS deta_liq,
			0 AS deta_liq,
			0 AS deta_ret,
			0 AS deta_det,
			0 AS deta_acf,
			0 AS deta_aju
		FROM cajas_chicas_lote l
		INNER JOIN cajas_chicas cch ON cch.cch_id=l.cch_id
		LEFT JOIN monedas m ON m.mon_id=l.mon_id
		LEFT JOIN recursos.usuarios ru1 ON ru1.usu_id=l.ccl_ape_usu
		LEFT JOIN recursos.usuarios ru2 ON ru2.usu_id=l.ccl_cie_usu
		LEFT JOIN recursos.usuarios ru3 ON ru3.usu_id=l.ccl_aprob_usu
		LEFT JOIN recursos.usuarios ru4 ON ru4.usu_id=l.ccl_act_usu
		LEFT JOIN tablas_estados te ON te.tabla_id=3 AND te.est_id=l.est_id
		LEFT JOIN recursos.sucursales rsu ON rsu.suc_id=cch.suc_id
		#JOIN pla_mov pm ON pm.ear_id=e.ear_id
		WHERE l.est_id IN (3, 4) # AND e.pla_id IS NOT NULL
		) AS A

		GROUP BY ccl_id, cch_nombre, ccl_numero, mon_nom, mon_iso, mon_simb, mon_img, ccl_monto_ini, ccl_gti, ccl_dg_json, ccl_cta_bco,
			ccl_ape_fec, ape_usu_nombre, ccl_cie_fec, cie_usu_nombre, ccl_aprob_fec, aprob_usu_nombre, ccl_act_fec, act_usu_nombre,
			ccl_monto_usado, est_id, est_nom, suc_nombre, ccl_ret, ccl_ret_no, ccl_det, ccl_det_no, ccl_gast_asum, ccl_pend, cch_id, ccl_desemb");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array(
      $fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5], $fila[6], $fila[7], $fila[8], $fila[9], $fila[10],
      $fila[11], $fila[12], $fila[13], $fila[14], $fila[15], $fila[16], $fila[17], $fila[18],
      $fila[19], $fila[20], $fila[21], $fila[22], $fila[23], $fila[24], $fila[25], $fila[26], $fila[27], $fila[28], $fila[29], $fila[30],
      $fila[31], $fila[32], $fila[33], $fila[34], $fila[35]
    );
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getUsuMastersJefesIdsLista($slave_id)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT DISTINCT uro.master_usu_id
		FROM usu_reg_otro uro
		INNER JOIN (SELECT DISTINCT usu_jefe FROM usu_detalle) uj ON uj.usu_jefe=uro.slave_usu_id
		WHERE uro.slave_usu_id=? AND uro.uro_act=1");
  $stmt->bind_param("i", $slave_id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = $fila[0];
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getPlanillasMovilidadCCH($god_mode, $mode_id, $usu_id, $otro_jefe_usu_id = null)
{
  $arr = array();
  $q_joins = "";
  $q_cons = "";
  include 'datos_abrir_bd.php';

  // Filtro de acuerdo al tipo de consulta
  if ($god_mode == 0) {
    $q_joins = "LEFT JOIN recursos.usuarios ru ON ru.usu_id=e.ear_act_usu";
    if ($mode_id == 1) {
      if (is_null($otro_jefe_usu_id)) {
        $q_cons = "AND ( ud.usu_jefe=$usu_id )";
      } else {
        $q_cons = "AND ( ud.usu_jefe=$otro_jefe_usu_id )";
      }
    } else if ($mode_id == 2 || $mode_id == 3) {
      $q_joins .= "\n JOIN cajas_chicas_enc cce ON cce.cch_id=cch.cch_id AND cce.usu_id=$usu_id AND cce.cce_act=1";
    }
  }

  if ($mode_id == 1) {
    // 1: Aprobacion de planilla de movilidad por parte de los jefes de los usuarios
    $q_est = "p.est_id=1";
  } else if ($mode_id == 2) {
    // 2: Agrega planilla de movilidad a la caja chica por parte del encargado de caja chica
    $q_est = "p.est_id=4";
  } else if ($mode_id == 3) {
    // 2: Quitar planilla de movilidad a la caja chica por parte del encargado de caja chica
    $q_est = "p.est_id=15 AND ccl.est_id=1";
  }

  $query = "SELECT p.pla_id, CONCAT(p.pla_serie, '-', LPAD(p.pla_nro, 7, '0')) pla_numero, p.pla_monto, te.est_nom, p.pla_reg_fec,
		cch.cch_nombre ear_numero,
		m.mon_nom, m.mon_iso, m.mon_img, p.est_id,
		ru2.usu_nombre,
		CONCAT(ccl.ccl_anio, '-', LPAD(ccl.ccl_mes, 2, '0'), '-', LPAD(ccl.ccl_nro, 3, '0'), '/', cch.cch_abrv) ccl_numero
	FROM pla_mov p
	LEFT JOIN tablas_estados te ON te.tabla_id=2 AND te.est_id=p.est_id
	JOIN tablas_nombres tn ON tn.tabla_id=te.tabla_id AND tabla_nom='pla_mov'
	LEFT JOIN ear_solicitudes e ON e.ear_id=p.ear_id
	LEFT JOIN monedas m ON m.mon_id=p.mon_id
	LEFT JOIN usu_detalle ud ON ud.usu_id=p.usu_id
	LEFT JOIN cajas_chicas cch ON cch.cch_id=p.cch_id
	LEFT JOIN cajas_chicas_lote ccl ON ccl.ccl_id=p.ccl_id
	LEFT JOIN recursos.usuarios ru2 ON ru2.usu_id=p.usu_id
	$q_joins
	WHERE p.ear_id IS NULL AND $q_est $q_cons
	LIMIT 2000";
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($fila = $result->fetch_array()) {
    $arr[] = array(
      "pla_id" => $fila[0],
      "pla_numero" => $fila[1],
      "pla_monto" => $fila[2],
      "est_nom" => $fila[3],
      "pla_reg_fec" => $fila[4],
      "ear_numero" => $fila[5],
      "mon_nom" => $fila[6],
      "mon_iso" => $fila[7],
      "mon_img" => $fila[8],
      "est_id" => $fila[9],
      "usu_nombre" => $fila[10],
      "ccl_numero" => $fila[11]
    );
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function getFilaPlanillaMovilidadCCH($ccl_id, $mon_id, $mode_id, $usu_nombre = "ERROR", $usu_gco_cobj = "ERROR")
{
  // Valores mode_id:
  // 1: cch_lote_reg.php
  // 2: cch_lote_aprob.php

  $arr = getPlanillasMovilidadCCL($ccl_id);

  $html = '';

  if (count($arr) > 0) {
    // Valores de $v
    // 0: pla_numero
    // 1: est_id
    // 2: pla_reg_fec
    // 3: ear_numero
    // 4: tope_maximo
    // 5: usu_id
    // 6: ear_id
    // 7: est_nom
    // 8: pla_monto
    // 9: pla_gti
    // 10: pla_dg_json
    // 11: pla_env_fec
    // 12: pla_exc
    // 13: pla_com1
    // 14: pla_com2
    // 15: pla_com3
    // 16: pla_id
    // 17: usu_nombre

    $i = 0;

    foreach ($arr as $v) {
      // if ($mode_id==1) {
      // $fec_doc = date('d/m/Y');
      // $tc = getTipoCambio(2, date('Y/m/d'));
      // }
      // else {
      $pzas = explode("-", substr($v[11], 0, 10));
      $fec_doc = $pzas[2] . "/" . $pzas[1] . "/" . $pzas[0];
      $tc = getTipoCambio(2, $v[11]);
      // }
      $pzas = explode("-", $v[0]);
      $ser = $pzas[0];
      $nro = $pzas[1];
      $unique_id = "Splamov" . $i;
      $conv_afe_hide = 1;
      $conv_naf_hide = 1;
      $tc_div = "";
      if ($mon_id == 2) {
        $tc_div = $tc;
        $conv_naf_hide = 0;
        $conv = number_format($v[8] / $tc, 2, '.', '');
      } else {
        $conv = $v[8];
      }
      if (is_null($v[9])) {
        $gti_id = 1;
        $dist_gast_json = '[["' . $usu_nombre . '","' . $usu_gco_cobj . '","100.00"]]';
      } else {
        $gti_id = $v[9];
        $dist_gast_json = $v[10];
      }

      $html .= "<tr class='fila_dato'>\n";
      $html .= "\t<td>Movilidad</td>\n";
      $html .= "\t<td>Planilla de Movilidad</td>\n";
      $html .= "\t<td></td>\n";
      $html .= "\t<td></td>\n";
      $html .= "\t<td class='fec_doc_td'>$fec_doc<input type='hidden' value='$fec_doc' size='11' maxlength='10' class='fecha_inp' readonly name='fec_doc[$unique_id]'></td>\n";
      $html .= "\t<td>$ser</td>\n";
      $html .= "\t<td>$nro</td>\n";
      if ($mode_id == 1) {
        $html .= "\t<td><a href='movi_consulta_detalle.php?id=$v[16]&opc=1&close=1' target='_blank'><img src='img/modal.gif' id='abrir_plamov' class='iconos' title='Abrir Planilla de Movilidad en una nueva ventana'></a> $v[17]</td>\n";
      } else {
        $html .= "\t<td>$v[17]</td>\n";
      }
      $html .= "\t<td class='tipo_mon_td'>PEN<input type='hidden' class='tipo_mon' id='pla_tipo_mon' value='1'></td>\n";
      $html .= "\t<td>No</td>\n";
      $html .= "\t<td></td>\n";
      $html .= "\t<td><div id='pla_monto_div'>$v[8]</div></td>\n";
      $html .= "\t<td class='tc_td'><div class='tc_div'>$tc_div</div><input type='hidden' class='tc_inp' name='tc_inp[$unique_id]' id='tc_inp[$unique_id]' value='$tc'></td>\n"; //T/C;
      $html .= "\t<td class='conv_afecto_td'><input type='text' value='0' size='8' maxlength='9' id='conv_afecto_inp[$unique_id]' class='conv_afecto_inp' name='conv_afecto_inp[$unique_id]' readonly" . ($conv_afe_hide == 1 ? " style='display: none;'" : "") . "></td>\n"; //Conversion Afecto
      $html .= "\t<td class='conv_noafecto_td'><input type='text' value='$conv' size='8' maxlength='9' id='conv_noafecto_inp[$unique_id]' class='conv_noafecto_inp' name='conv_noafecto_inp[$unique_id]' readonly" . ($conv_naf_hide == 1 ? " style='display: none;'" : "") . "></td>\n"; //Conversion NoAfecto
      $html .= "\t<td></td>\n";
      $html .= "\t<td>Exonerado</td>\n";
      $html .= getDistGastTemplate($gti_id, $dist_gast_json, "plamov" . $i);
      if ($mode_id == 2) {
        // if ($est_id==3) $html .= "\t<td><div id='pm_estado'>Falta revisar <a href='movi_consulta_detalle.php?id=$id&opc=3' target='_blank'><img src='img/modal.gif' id='abrir_plamov' class='iconos' title='Abrir Planilla de Movilidad en una nueva ventana'></a> <img src='img/reload.gif' id='act_est_plamov' class='iconos' title='Actualizar estado'></div></td>\n";
        // else if ($est_id==4) $html .= "\t<td><div id='pm_estado'>Revisado <a href='movi_consulta_detalle.php?id=$id&opc=3' target='_blank'><img src='img/modal.gif' id='abrir_plamov' class='iconos' title='Abrir Planilla de Movilidad en una nueva ventana'></a> <img src='img/reload.gif' id='act_est_plamov' class='iconos' title='Actualizar estado'></div></td>\n";
        // else $html .= "\t<td><div id='pm_estado'>ERROR: Estado invalido</div></td>\n";
        $html .= "\t<td><div id='pm_estado'>Revisar <a href='movi_consulta_detalle.php?id=$v[16]&opc=5' target='_blank'><img src='img/modal.gif' id='abrir_plamov' class='iconos' title='Abrir Planilla de Movilidad en una nueva ventana'></a></div></td>\n";
        $html .= "\t<td class='gast_asum_td'><input type='text' value='$v[8]' size='8' maxlength='9' class='gast_asum_i' name='gast_asum[$unique_id]' id='gast_asum[$unique_id]' readonly></td>\n";
      }
      // else if ($mode_id==3) {
      // if ($est_id==4) $html .= "\t<td><div id='pm_estado'>Falta revisar <a href='movi_consulta_detalle.php?id=$id&opc=4' target='_blank'><img src='img/modal.gif' id='abrir_plamov' class='iconos' title='Abrir Planilla de Movilidad en una nueva ventana'></a> <img src='img/reload.gif' id='act_est_plamov' class='iconos' title='Actualizar estado'></div></td>\n";
      // else if ($est_id==5) $html .= "\t<td><div id='pm_estado'>Revisado <a href='movi_consulta_detalle.php?id=$id&opc=4' target='_blank'><img src='img/modal.gif' id='abrir_plamov' class='iconos' title='Abrir Planilla de Movilidad en una nueva ventana'></a> <img src='img/reload.gif' id='act_est_plamov' class='iconos' title='Actualizar estado'></div></td>\n";
      // else $html .= "\t<td><div id='pm_estado'>ERROR: Estado invalido</div></td>\n";
      // $html .= "\t<td class='gast_asum_td'><input type='text' value='$pla_monto' size='8' maxlength='9' class='gast_asum_i' name='gast_asum[$unique_id]' id='gast_asum[$unique_id]' readonly><input type='hidden' value='$est_id' name='pm_rev' id='pm_rev'></td>\n";
      // }
      // else if ($mode_id==4) {
      // $html .= "\t<td><div id='pm_estado'>Revisado <a href='movi_consulta_detalle.php?id=$id&opc=5' target='_blank'><img src='img/modal.gif' id='abrir_plamov' class='iconos' title='Abrir Planilla de Movilidad en una nueva ventana'></a> <img src='img/reload.gif' id='act_est_plamov' class='iconos' title='Actualizar estado'></div></td>\n";
      // $html .= "\t<td class='gast_asum_td'><input type='text' value='$pla_monto' size='8' maxlength='9' class='gast_asum_i' name='gast_asum[$unique_id]' id='gast_asum[$unique_id]' readonly><input type='hidden' value='$est_id' name='pm_rev' id='pm_rev'></td>\n";
      // }
      // else if ($mode_id==100) {
      // $html .= "\t<td><div id='pm_estado'>Detalle <a href='movi_consulta_detalle.php?id=$id&opc=1&close=1' target='_blank'><img src='img/modal.gif' id='abrir_plamov' class='iconos' title='Abrir Planilla de Movilidad en una nueva ventana'></a></div></td>\n";
      // $html .= "\t<td class='gast_asum_td'><input type='text' value='$pla_monto' size='8' maxlength='9' class='gast_asum_i' name='gast_asum[$unique_id]' id='gast_asum[$unique_id]' readonly><input type='hidden' value='$est_id' name='pm_rev' id='pm_rev'></td>\n";
      // }
      $html .= "\t<td><input type='hidden' value='$v[16]' name='pm_id[$unique_id]'></td>\n";
      $html .= "</tr>\n";

      $i++;
    }
  }

  return $html;
}

function getPlanillasMovilidadCCL($ccl_id)
{
  $arr = array();
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT CONCAT(LPAD(p.pla_serie, 5, '0'), '-', LPAD(p.pla_nro, 7, '0')) pla_numero, p.est_id, p.pla_reg_fec,
			CONCAT(ccl.ccl_anio, '-', LPAD(ccl.ccl_mes, 2, '0'), '-', LPAD(ccl.ccl_nro, 3, '0'), '/', cch.cch_abrv) ear_numero,
			pla_tope, p.usu_id, p.ear_id, te.est_nom, p.pla_monto, p.pla_gti, p.pla_dg_json,
			p.pla_env_fec, p.pla_exc, p.pla_com1, p.pla_com2, p.pla_com3,
			p.pla_id, ru2.usu_nombre
		FROM pla_mov p
		LEFT JOIN tablas_estados te ON te.tabla_id=2 AND te.est_id=p.est_id
		LEFT JOIN ear_solicitudes e ON e.ear_id=p.ear_id
		LEFT JOIN usu_detalle ud ON ud.usu_id=p.usu_id
		LEFT JOIN cajas_chicas cch ON cch.cch_id=p.cch_id
		LEFT JOIN cajas_chicas_lote ccl ON ccl.ccl_id=p.ccl_id
		LEFT JOIN recursos.usuarios ru2 ON ru2.usu_id=p.usu_id
		WHERE p.ccl_id=? AND p.est_id=15");
  $stmt->bind_param("i", $ccl_id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array(
      $fila[0], $fila[1], $fila[2],
      $fila[3],
      $fila[4], $fila[5], $fila[6], $fila[7], $fila[8], $fila[9], $fila[10],
      $fila[11], $fila[12], $fila[13], $fila[14], $fila[15],
      $fila[16], $fila[17]
    );
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function getFilasPreviasDocPend($ccl_id, $mode_id)
{
  /*
      Valores para $mode_id:
      1: Registro
      2: Revision
     */
  $html = '';
  $arrDP = getDocPend($ccl_id);
  $arrUsu = getUsuLista();
  foreach ($arrDP as $k => $v) {
    if (is_null($v[17])) {
      // Si la fecha de reembolso es nula muestra la fila en el formulario

      $fec_doc = "";
      if (strlen($v[3]) == 10 && !startsWith($v[3], "0000")) {
        $pzas = explode("-", $v[3]);
        $fec_doc = $pzas[2] . "/" . $pzas[1] . "/" . $pzas[0];
      }

      $html .= "<tr class='fila_dato'>\n";
      $html .= "\t<td class='pend_usu_td'>\n";
      $html .= "\t\t<select class='pend_usu_l' id='pend_usu_l[U$k]' name='pend_usu_l[U$k]'>\n";

      $html .= "\t\t\t<option value='$v[0]'>$v[1]</option>\n";
      $html .= "\t\t\t<option disabled>-----</option>\n";

      foreach ($arrUsu as $w) {
        $html .= "\t\t\t<option value='$w[0]'>$w[1]</option>\n";
      }

      $html .= "\t\t</select>\n";
      $html .= "\t</td>\n";
      $html .= "\t<td class='pend_doc_td'>$v[2]</td>\n";
      $html .= "\t<td class='pend_fec_td'><input type='text' value='$fec_doc' size='11' maxlength='10' class='fecha_inp' readonly name='pend_fec[U$k]'></td>\n";
      $html .= "\t<td class='pend_conc_td'><input type='text' value='$v[4]' size='20' maxlength='100' class='pend_conc' name='pend_conc[U$k]'></td>\n";
      $html .= "\t<td class='pend_mont_td'><input type='text' value='$v[5]' size='8' maxlength='9' class='pend_mont' name='pend_mont[U$k]'></td>\n";

      $html .= "\t<td class='pend_acc_td'>";
      if (!is_null($v[13])) {
        $html .= "\t\Devoluci&oacute;n en proceso\n";
        $html .= "\t\t<input type='checkbox' class='pend_cerr' name='pend_cerr[U$k]' style='display:none;'>";
        $html .= "\t\t<input type='checkbox' class='pend_anul' name='pend_anul[U$k]' style='display:none;'>";
        $html .= "\t\t<input type='hidden' value='$v[13]' name='pend_prdc_fec[U$k]'>";
      } else {
        $html .= "\t\t<input type='checkbox' class='pend_cerr' name='pend_cerr[U$k]'" . ($v[6] == 2 ? ' checked' : '') . "> Liquidar&nbsp;";
        $html .= "\t\t<input type='checkbox' class='pend_anul' name='pend_anul[U$k]'" . ($v[6] == 3 ? ' checked' : '') . "> Anular&nbsp;";
      }
      $html .= "\t\t<input type='hidden' value='$v[8]' name='pend_id[U$k]'>";
      $html .= "\t</td>\n";

      $html .= "\t<td class='pend_com1_td'><input type='text' value='$v[7]' size='20' maxlength='100' class='pend_com1' name='pend_com1[U$k]'></td>\n";
      $html .= "\t<td class='pend_docref_td'>\n" . getTipoDocTemplate($v[9], $k, 'pend_') . "\n";
      $html .= "\t\t<input type='text' value='$v[10]' size='6' maxlength='5' class='pend_ser_docref_inp' name='ser_docref[U$k]'>\n";
      $html .= "\t\t<input type='text' value='$v[11]' size='9' maxlength='7' class='pend_nro_docref_inp' name='nro_docref[U$k]'>\n\t</td>\n";
      $html .= "\t<td><a href='cch_lote_dp_pdf.php?id=$v[8]'><img src='img/pdf.gif' title='Imprimir'></a></td>\n";

      $html .= "</tr>\n";
    } else {
      // Si la fecha de reembolso no es nula, primero verifica que el estado sea pendiente para hacer el cambio de estado
      // No se muestra esta fila en el formulario (ya que el documento pendiente ya fue reembolsado)

      if ($v[6] == 1) {
        setDocPendReembolsado($k);
      }
    }
  }

  return $html;
}

function getDocPend($ccl_id)
{
  $arr = array();
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT dp.usu_id, ru2.usu_nombre, CONCAT(LPAD(dp.cldp_anio, 5, '0'), '-', LPAD(dp.cldp_nro, 7, '0')) dp_numero,
			dp.cldp_ent_fec, dp.cldp_conc, dp.cldp_monto, dp.est_id, dp.cldp_com1, dp.cldp_id,
			dp.ref_doc_id, dp.ref_ser, dp.ref_nro, dt.doc_cod,
			dp.cldp_prdc_fec, dp.cldp_prdc_usu, dp.cldp_desc_fec, dp.cldp_desc_usu, dp.cldp_reem_fec, dp.cldp_reem_usu
		FROM cajas_chicas_lote_docp dp
		LEFT JOIN recursos.usuarios ru2 ON ru2.usu_id=dp.usu_id
		LEFT JOIN doc_tipos dt ON dt.doc_id=dp.ref_doc_id
		WHERE dp.ccl_id=?");
  $stmt->bind_param("i", $ccl_id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[$fila[8]] = array(
      $fila[0], $fila[1], $fila[2],
      $fila[3], $fila[4], $fila[5], $fila[6], $fila[7], $fila[8],
      $fila[9], $fila[10], $fila[11], $fila[12],
      $fila[13], $fila[14], $fila[15], $fila[16], $fila[17], $fila[18]
    );
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function getDocPendInfo($id)
{
  $arr = array();
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT dp.usu_id, CONCAT(LPAD(dp.cldp_anio, 5, '0'), '-', LPAD(dp.cldp_nro, 7, '0')) dp_numero,
			dp.cldp_ent_fec, dp.cldp_conc, dp.cldp_monto, dp.est_id, dp.cldp_com1, cch.cch_nombre, dp.cldp_reg_fec, m.mon_nom, m.mon_simb,
			dp.cldp_prdc_fec, dp.cldp_prdc_usu, dp.cldp_desc_fec, dp.cldp_desc_usu, dp.cldp_reem_fec, dp.cldp_reem_usu, ccl.cch_id
		FROM cajas_chicas_lote_docp dp
		LEFT JOIN cajas_chicas_lote ccl ON ccl.ccl_id=dp.ccl_id
		LEFT JOIN cajas_chicas cch ON cch.cch_id=ccl.cch_id
		LEFT JOIN monedas m ON m.mon_id=ccl.mon_id
		WHERE dp.cldp_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array(
      $fila[0], $fila[1],
      $fila[2], $fila[3], $fila[4], $fila[5], $fila[6], $fila[7], $fila[8], $fila[9], $fila[10],
      $fila[11], $fila[12], $fila[13], $fila[14], $fila[15], $fila[16], $fila[17]
    );
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function setDocPendReembolsado($id)
{
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("UPDATE cajas_chicas_lote_docp SET est_id=4 WHERE cldp_id=?") or die($mysqli->error);
  $stmt->bind_param('i', $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->close();

  include 'datos_cerrar_bd.php';
}

function getCuadreNuevoBD($tipo_id, $mon_id)
{
  $arr = array();
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT tipo_id, denominacion
		FROM plantilla_cuadre
		WHERE tipo_id=? AND mon_id=?
		ORDER BY denominacion DESC");
  $stmt->bind_param("ii", $tipo_id, $mon_id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1]);
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function getFilasCuadreNuevo($tipo_id, $mon_id)
{
  $html = '';
  $arr = getCuadreNuevoBD($tipo_id, $mon_id);
  foreach ($arr as $k => $v) {
    $html .= "<tr class='cuadre_tr'>\n";
    $html .= "\t<input type='hidden' value='$v[0]' class='cuadre_tipo' name='cuadre_tipo[S$tipo_id$k]'>\n";
    $html .= "\t<td class='cuadre_deno_td' align='right'>$v[1]<input type='hidden' value='$v[1]' class='cuadre_deno' name='cuadre_deno[S$tipo_id$k]'></td>\n";
    $html .= "\t<td class='cuadre_cant_td'><input type='text' value='0' size='8' maxlength='9' class='cuadre_cant' name='cuadre_cant[S$tipo_id$k]'></td>\n";
    $html .= "\t<td class='cuadre_mont_td' align='right'>0.00</td>\n";
    $html .= "</tr>\n";
  }

  return $html;
}

function getCuadreGrabadoBD($tipo_id, $ccl_id)
{
  $arr = array();
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT tipo_id, cclc_deno, cclc_cant, cclc_id
		FROM cajas_chicas_lote_cuadre
		WHERE tipo_id=? AND ccl_id=?
		ORDER BY cclc_deno DESC");
  $stmt->bind_param("ii", $tipo_id, $ccl_id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3]);
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function getFilasCuadreGrabado($tipo_id, $ccl_id, $mon_id)
{
  $html = '';
  $arr = getCuadreGrabadoBD($tipo_id, $ccl_id);
  if (count($arr) > 0) {
    foreach ($arr as $k => $v) {
      $html .= "<tr class='cuadre_tr'>\n";
      $html .= "\t<input type='hidden' value='$v[0]' class='cuadre_tipo' name='cuadre_tipo[S$tipo_id$k]'><input type='hidden' value='$v[3]' class='cuadre_id' name='cuadre_id[S$tipo_id$k]'>\n";
      $html .= "\t<td class='cuadre_deno_td' align='right'>$v[1]<input type='hidden' value='$v[1]' class='cuadre_deno' name='cuadre_deno[S$tipo_id$k]'></td>\n";
      $html .= "\t<td class='cuadre_cant_td'><input type='text' value='$v[2]' size='8' maxlength='9' class='cuadre_cant' name='cuadre_cant[S$tipo_id$k]'></td>\n";
      $html .= "\t<td class='cuadre_mont_td' align='right'>0.00</td>\n";
      $html .= "</tr>\n";
    }
  } else {
    $html = getFilasCuadreNuevo($tipo_id, $mon_id);
  }

  return $html;
}

function getLoteDetalleHist($ccl_id, $hist_id)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT eld.conc_id, ec.conc_cod, eld.doc_id, eld.ruc_nro, CASE WHEN (eld.doc_id!=10 AND LENGTH(eld.ruc_nro)=8) THEN '' ELSE pr.prov_nom END AS prov_nom,
			IFNULL(pr.ruc_ret,0) ruc_ret, eld.lid_fec, eld.lid_ser, eld.lid_nro, UPPER(eld.lid_glo) lid_glo,
			eld.mon_id, eld.lid_afe, eld.lid_mon_afe, eld.lid_mon_naf, eld.lid_tc,
			eld.lid_retdet_apl, eld.lid_retdet_tip, eld.lid_retdet_mon, eld.lid_gti, eld.lid_dg_json,
			eld.lid_cta_cont, eld.lid_aprob, eld.lid_emp_asume, dt.doc_ruc_req, mo.mon_iso,
			dt.doc_nro, dt.doc_cod, ec.conc_acf, IFNULL(pr.ruc_act,-1) ruc_act, IFNULL(pr.ruc_hab,-1) ruc_hab,
			IFNULL(pr.prov_factura,-1) prov_factura, IFNULL(pr.prov_provincia,'') prov_provincia, dt.doc_tax_code,
			ec.conc_cve, eld.veh_id, eld.veh_km, UPPER(eld.lid_glo2) lid_glo2
		FROM cajas_chicas_lote_det_hist eld
		LEFT JOIN ear_conceptos ec ON ec.conc_id=eld.conc_id
		LEFT JOIN proveedores pr ON pr.ruc_nro=eld.ruc_nro
		LEFT JOIN doc_tipos dt ON dt.doc_id=eld.doc_id
		LEFT JOIN monedas mo ON mo.mon_id=eld.mon_id
		WHERE ccl_id=? AND hist_id=?
		ORDER BY conc_cod, lid_ser, CAST(lid_nro AS UNSIGNED)");
  $stmt->bind_param("ii", $ccl_id, $hist_id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array(
      $fila[0], $fila[1], $fila[2], $fila[3], $fila[4],
      $fila[5], $fila[6], $fila[7], $fila[8], $fila[9],
      $fila[10], $fila[11], $fila[12], $fila[13], $fila[14],
      $fila[15], $fila[16], $fila[17], $fila[18], $fila[19],
      $fila[20], $fila[21], $fila[22], $fila[23], $fila[24],
      $fila[25], $fila[26], $fila[27], $fila[28], $fila[29],
      $fila[30], $fila[31], $fila[32],
      $fila[33], $fila[34], $fila[35], $fila[36]
    );
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getLoteCajaChicaInfoHist($id)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT ccl_monto_usado_hist, ccl_ret_hist, ccl_ret_no_hist, ccl_det_hist, ccl_det_no_hist
		FROM cajas_chicas_lote
		WHERE ccl_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getUserGodMode($id)
{
  $count = getPermisosAdministrativos($id, 'ADMINIST');
  $count += getPermisosAdministrativos($id, 'TI');

  return $count;
}

function getPlanillasId($ccl_id)
{
  // Obtiene todos los Id de planillas de movilidad que pertenezcan a determinado lote de caja chica

  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT pla_id
		FROM pla_mov
		WHERE ccl_id=? AND est_id=15");
  $stmt->bind_param("i", $ccl_id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = $fila[0];
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getDocPendId($ccl_id)
{
  // Obtiene todos los Id de documentos pendientes que pertenezcan a determinado lote de caja chica

  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT cldp_id
		FROM cajas_chicas_lote_docp
		WHERE ccl_id=?");
  $stmt->bind_param("i", $ccl_id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = $fila[0];
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getDocPendLista($mode_id, $usu_id)
{
  $arr = array();

  $q_joins = "";
  switch ($mode_id) {
    case 4:
      // Lista los lotes para ser consultados por el encargado (readonly)
      if (getUserGodMode($usu_id) > 0) {
        // god_mode
        // No se hace INNER JOIN
      } else {
        // normal_mode
        $q_joins = "INNER JOIN cajas_chicas_enc cce ON cce.cch_id=ccl.cch_id AND cce.usu_id=$usu_id AND cce.cce_act=1";
      }
      break;
    case 5:
      // Lista los lotes para ser consultados por el responsable (readonly)
      if (getUserGodMode($usu_id) > 0) {
        // god_mode
        // No se hace INNER JOIN
      } else {
        // normal_mode
        $q_joins = "INNER JOIN cajas_chicas_resp ccr ON ccr.cch_id=ccl.cch_id AND ccr.usu_id=$usu_id AND ccr.ccr_act=1";
      }
      break;
    default:
      $q_joins = "";
  }

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT dp.cldp_id, cch.cch_nombre, CONCAT(LPAD(dp.cldp_anio, 5, '0'), '-', LPAD(dp.cldp_nro, 7, '0')) dp_numero,
			ru2.usu_nombre, m.mon_nom, m.mon_iso, m.mon_simb, m.mon_img, dp.cldp_monto,
			te.est_nom, dp.cldp_reg_fec, dp.cldp_conc, dp.cldp_com1,
			CASE WHEN dp.est_id=1 THEN DATEDIFF(now(), dp.cldp_reg_fec) ELSE null END AS dias_trans,
			dp.cldp_prdc_fec, dp.cldp_prdc_usu, dp.cldp_desc_fec, dp.cldp_desc_usu, dp.cldp_reem_fec, dp.cldp_reem_usu,
			dp.usu_id, ccl.cch_id
		FROM cajas_chicas_lote_docp dp
		INNER JOIN cajas_chicas_lote ccl ON ccl.ccl_id=dp.ccl_id
		INNER JOIN cajas_chicas cch on cch.cch_id=ccl.cch_id
		LEFT JOIN recursos.usuarios ru2 ON ru2.usu_id=dp.usu_id
		LEFT JOIN monedas m ON m.mon_id=ccl.mon_id
		LEFT JOIN tablas_estados te ON te.tabla_id=4 AND te.est_id=dp.est_id
		$q_joins
		WHERE dp.cldp_id IN (
			SELECT MAX(cldp_id)
			FROM cajas_chicas_lote_docp
			GROUP BY cldp_anio, cldp_nro
		)
		ORDER BY dp_numero DESC");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array(
      $fila[0], $fila[1], $fila[2],
      $fila[3], $fila[4], $fila[5], $fila[6], $fila[7], $fila[8],
      $fila[9], $fila[10], $fila[11], $fila[12],
      $fila[13],
      $fila[14], $fila[15], $fila[16], $fila[17], $fila[18], $fila[19],
      $fila[20], $fila[21]
    );
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getValoresSemaforoDP()
{
  $arr = array();
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT est_id, val_min_verde, val_min_ambar, val_min_rojo
		FROM ear_semaforo
		WHERE tabla_id=4 AND est_id=1");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[$fila[0]] = array($fila[1], $fila[2], $fila[3]);
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function getDocPendVencidosLista()
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT dp.cldp_id, cch.cch_nombre, CONCAT(LPAD(dp.cldp_anio, 5, '0'), '-', LPAD(dp.cldp_nro, 7, '0')) dp_numero,
			ru2.usu_nombre, m.mon_nom, m.mon_iso, m.mon_simb, m.mon_img, dp.cldp_monto,
			te.est_nom, dp.cldp_reg_fec, dp.cldp_conc, dp.cldp_com1,
			CASE WHEN dp.est_id=1 THEN DATEDIFF(now(), dp.cldp_reg_fec) ELSE null END AS dias_trans,
			dp.cldp_prdc_fec, dp.cldp_prdc_usu, dp.cldp_desc_fec, dp.cldp_desc_usu, dp.cldp_reem_fec, dp.cldp_reem_usu
		FROM cajas_chicas_lote_docp dp
		INNER JOIN cajas_chicas_lote ccl ON ccl.ccl_id=dp.ccl_id
		INNER JOIN cajas_chicas cch on cch.cch_id=ccl.cch_id
		LEFT JOIN recursos.usuarios ru2 ON ru2.usu_id=dp.usu_id
		LEFT JOIN monedas m ON m.mon_id=ccl.mon_id
		LEFT JOIN tablas_estados te ON te.tabla_id=4 AND te.est_id=dp.est_id
		WHERE dp.cldp_id IN (
			SELECT MAX(cldp_id)
			FROM cajas_chicas_lote_docp
			GROUP BY cldp_anio, cldp_nro
		) AND dp.cldp_prdc_fec IS NOT NULL
		AND dp.cldp_desc_fec IS NULL
		ORDER BY dp_numero DESC");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array(
      $fila[0], $fila[1], $fila[2],
      $fila[3], $fila[4], $fila[5], $fila[6], $fila[7], $fila[8],
      $fila[9], $fila[10], $fila[11], $fila[12],
      $fila[13],
      $fila[14], $fila[15], $fila[16], $fila[17], $fila[18], $fila[19]
    );
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getDocPendDescontadosLista()
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT dp.cldp_id, cch.cch_nombre, CONCAT(LPAD(dp.cldp_anio, 5, '0'), '-', LPAD(dp.cldp_nro, 7, '0')) dp_numero,
			ru2.usu_nombre, m.mon_nom, m.mon_iso, m.mon_simb, m.mon_img, dp.cldp_monto,
			te.est_nom, dp.cldp_reg_fec, dp.cldp_conc, dp.cldp_com1,
			CASE WHEN dp.est_id=1 THEN DATEDIFF(now(), dp.cldp_reg_fec) ELSE null END AS dias_trans,
			dp.cldp_prdc_fec, dp.cldp_prdc_usu, dp.cldp_desc_fec, dp.cldp_desc_usu, dp.cldp_reem_fec, dp.cldp_reem_usu
		FROM cajas_chicas_lote_docp dp
		INNER JOIN cajas_chicas_lote ccl ON ccl.ccl_id=dp.ccl_id
		INNER JOIN cajas_chicas cch on cch.cch_id=ccl.cch_id
		LEFT JOIN recursos.usuarios ru2 ON ru2.usu_id=dp.usu_id
		LEFT JOIN monedas m ON m.mon_id=ccl.mon_id
		LEFT JOIN tablas_estados te ON te.tabla_id=4 AND te.est_id=dp.est_id
		WHERE dp.cldp_id IN (
			SELECT MAX(cldp_id)
			FROM cajas_chicas_lote_docp
			GROUP BY cldp_anio, cldp_nro
		) AND dp.cldp_prdc_fec IS NOT NULL
		AND dp.cldp_desc_fec IS NOT NULL
		AND dp.cldp_reem_fec IS NULL
		ORDER BY dp_numero DESC");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array(
      $fila[0], $fila[1], $fila[2],
      $fila[3], $fila[4], $fila[5], $fila[6], $fila[7], $fila[8],
      $fila[9], $fila[10], $fila[11], $fila[12],
      $fila[13],
      $fila[14], $fila[15], $fila[16], $fila[17], $fila[18], $fila[19]
    );
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getPermisosLista($max)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT pp.perm_id, ru.usu_nombre, pg.grp_nom, pg.grp_desc
		FROM paginas_permisos pp
		INNER JOIN paginas_grupos pg ON pg.grp_id=pp.grp_id
		INNER JOIN recursos.usuarios ru ON ru.usu_id=pp.usu_id
		LIMIT ?");
  $stmt->bind_param("i", $max);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getPermisosGruposLista()
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT grp_id, grp_nom, grp_desc
		FROM paginas_grupos
		ORDER BY 2");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getPermisoInfo($id)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT grp_id, usu_id
		FROM paginas_permisos
		WHERE perm_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array($fila[0], $fila[1]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getPermisosGrupo($usu_id, $grp_nom)
{
  $val = 0;

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT COUNT(*)
		FROM paginas_permisos pp
		INNER JOIN paginas_grupos pg ON pg.grp_id=pp.grp_id
		WHERE pp.usu_id=? AND pg.grp_nom=?");
  $stmt->bind_param("is", $usu_id, $grp_nom);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $val = $fila[0];
  }

  include 'datos_cerrar_bd.php';

  return $val;
}

function getPermisosPagina($usu_id, $pag_nom)
{
  $val = 0;

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SELECT COUNT(*)
		FROM paginas_permisos pp
		INNER JOIN paginas_grupos pg ON pg.grp_id=pp.grp_id
		INNER JOIN paginas_grupos_detalle pgd on pgd.grp_id=pp.grp_id
		INNER JOIN paginas_nombres pn on pn.pag_id=pgd.pag_id
		WHERE pp.usu_id=? AND pn.pag_nom=?");
  $stmt->bind_param("is", $usu_id, $pag_nom);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $val = $fila[0];
  }

  include 'datos_cerrar_bd.php';

  return $val;
}

function getTipoGCO($gco_cobj)
{
  $val = 1;

  if (substr($gco_cobj, 0, 3) == "PE-") {
    $val = 3;
  } elseif (is_numeric($gco_cobj)) {
    $val = 4;
  }

  return $val;
}

//Agregado por Nilton Leon.
function obtenerPlantillaEmailSGI($plantillaId)
{

  $arr = array('', '', '');

  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $stmt = $mysqli->prepare("select id,destinatario,asunto,cuerpo
                                    from " . $baseSGI . ".email_plantilla
                                  where estado=1
                                    and id=?");
  $stmt->bind_param("i", $plantillaId);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array($fila[1], $fila[2], $fila[3]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function obtenerUsuariosIdXPerfil($perfilNombre)
{
  $arr = array();
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $stmt = $mysqli->prepare("select up.usuario_id
                                    from " . $baseSGI . ".usuario_perfil up
                                            inner join " . $baseSGI . ".perfil p on p.id=up.perfil_id
                                            inner join " . $baseSGI . ".usuario u on u.id=up.usuario_id
                                            inner join " . $baseSGI . ".persona pp on pp.id=u.persona_id
                                    where up.estado=1
                                        and p.estado=1
                                        and u.estado=1
                                        and pp.estado=1
                                        and p.nombre = '" . $perfilNombre . "'");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = $fila['usuario_id'];
  }

  include 'datos_cerrar_bd.php';
  return $arr;
}

function obtenerPersonaIdSGI($usuarioId)
{
  $personaId = "";
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  //$stmt = $mysqli->prepare("select persona_id from " . $baseSGI . ".usuario where id=?");
  $stmt = $mysqli->prepare("select id from " . $baseSGI . ".persona where id=?");
  $stmt->bind_param("i", $usuarioId);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $personaId = $fila[0];
  }

  include 'datos_cerrar_bd.php';
  return $personaId;
}

function insertarEmailEnvioSGI($correo, $asunto, $cuerpo, $usuarioId, $rutaArchivo, $nombreArchivo)
{
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  //        $cuerpo='Para: '.$correo.'<br>'.$cuerpo;
  //        $correo="nleon;;";
  $mysqli->set_charset('utf8');
  $stmt = $mysqli->prepare("INSERT INTO " . $baseSGI . ".email_envio
                (destinatario,asunto,cuerpo,estado,usuario_creacion,fecha_envio,archivo_adjunto,nombre_archivo)
                VALUES (?,?,?,1,?,now(),?,?) ") or die($mysqli->error);
  $stmt->bind_param('sssiss', $correo, $asunto, $cuerpo, $usuarioId, $rutaArchivo, $nombreArchivo);
  $stmt->execute() or die($mysqli->error);

  $emailEnvioId = $mysqli->insert_id;

  include 'datos_cerrar_bd.php';
  return $emailEnvioId;
}

function insertarEmailEnvioSGIConeccion($mysqliConeccion, $correo, $asunto, $cuerpo, $usuarioId, $rutaArchivo, $nombreArchivo)
{
  //	include 'datos_abrir_bd.php';
  include 'parametros.php';

  //        $cuerpo='Para: '.$correo.'<br>'.$cuerpo;
  //        $correo="nleon;;";
  $mysqliConeccion->set_charset('utf8');
  $stmt = $mysqliConeccion->prepare("INSERT INTO " . $baseSGI . ".email_envio
                (destinatario,asunto,cuerpo,estado,usuario_creacion,fecha_envio,archivo_adjunto,nombre_archivo)
                VALUES (?,?,?,1,?,now(),?,?) ") or die($mysqliConeccion->error);
  $stmt->bind_param('sssiss', $correo, $asunto, $cuerpo, $usuarioId, $rutaArchivo, $nombreArchivo);
  $stmt->execute() or die($mysqliConeccion->error);

  $emailEnvioId = $mysqliConeccion->insert_id;

  include 'datos_cerrar_bd.php'; //CIERRO LA CONECCION DE $mysql QUE SE ABRIO EN parametros.php
  return $emailEnvioId;
}

function obtenerCorreosPerfilSGI($perfilSGI, $cc)
{
  $dataUsuarios = obtenerUsuariosIdXPerfil($perfilSGI);
  foreach ($dataUsuarios as $index => $usuarioId) {
    $correoUsuario = getCorreoUsuario($usuarioId);

    if (!is_null($correoUsuario) && $correoUsuario != '') {
      array_push($cc, $correoUsuario);
    }
  }

  return $cc;
}

//funciones
function fetchAssocStatement($stmt)
{
  if ($stmt->num_rows > 0) {
    $result = array();
    $md = $stmt->result_metadata();
    $params = array();
    while ($field = $md->fetch_field()) {
      $params[] = &$result[$field->name];
    }
    call_user_func_array(array($stmt, 'bind_result'), $params);
    $res = $stmt->fetch();
    if ($res) {
      $respuesta = array_merge($result, $params);
      return $respuesta;
      //            return $result;
    }
  }

  return null;
}

//para obtener la cadena hasta numero de letras
function obtenerCadenaXNumLetras($cadena, $longitud)
{
  $nuevaCadena = $cadena;
  if (strlen($cadena) > $longitud) {
    $nuevaCadena = substr($cadena, 0, $longitud - 3) . '...';
  }

  return $nuevaCadena;
}

function tablasEstadosObtenerComboXTablaId($tablaId)
{
  $arr = array();
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $stmt = $mysqli->prepare("select est_id,
                                         est_nom
                                  from tablas_estados where tabla_id=" . $tablaId . " and estado=1
                                         and orden is not null order by orden asc;");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    array_push($arr, $fila);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function obtenerDocumentoTipoSgiXDocId($docId)
{
  $documentoTipoId = "";
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $stmt = $mysqli->prepare("select sgi_documento_tipo_id from doc_tipos where doc_id=?");
  $stmt->bind_param("i", $docId);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $documentoTipoId = $fila[0];
  }

  include 'datos_cerrar_bd.php';
  return $documentoTipoId;
}

function obtenerCamposDinamicosSGI($documentoTipoIdSGI, $valores)
{
  include 'parametros.php';
  require_once('../' . $carpetaSGI . '/modeloNegocio/almacen/DocumentoTipoNegocio.php');
  require_once('../' . $carpetaSGI . '/modeloNegocio/almacen/DocumentoNegocio.php');

  $configuracionesDtd = DocumentoTipoNegocio::create()->obtenerDocumentoTipoDatoSimple($documentoTipoIdSGI);

  foreach ($configuracionesDtd as $index => $item) {
    switch ((int) $item["tipo"]) {
      case DocumentoTipoNegocio::DATO_CADENA:
        $valoresCadena = $valores->tipoCadena;

        $posSerie = strpos($item["descripcion"], 'Serie');
        if ($posSerie !== false) {
          $configuracionesDtd[$index]['valor'] = $valoresCadena->serie;
        }
        $posNumero = strpos($item["descripcion"], 'Número');
        if ($posNumero !== false) {
          $configuracionesDtd[$index]['valor'] = $valoresCadena->numero;
        }
        break;
      case DocumentoTipoNegocio::DATO_LISTA:
        $valoresLista = $valores->tipoLista;

        $posMotivo = strpos($item["descripcion"], 'Motivo');
        if ($posMotivo !== false) {
          $motivo = '';
          if ($valoresLista->motivo == 'defecto') {
            $motivo = $item["lista_defecto"];
          }
          $configuracionesDtd[$index]['valor'] = $motivo;
        }
        break;
      case DocumentoTipoNegocio::DATO_FECHA:
        $valoresFecha = $valores->tipoFecha;

        //                $posFechaDep = strpos($item["descripcion"], 'Deposito');
        //                if($posFechaDep!==false){
        $fecha = date_create($valoresFecha->fecha);
        $fecha = date_format($fecha, 'd/m/Y');
        $configuracionesDtd[$index]['valor'] = $fecha;
        //                }
        break;
      case DocumentoTipoNegocio::DATO_PERSONA:
        $configuracionesDtd[$index]['valor'] = $valores->persona;
        break;
      case DocumentoTipoNegocio::DATO_SERIE:
        $serie = $valores->serie;
        if ($valores->serie == 'defecto') {
          $serie = $item["cadena_defecto"];
        }
        $configuracionesDtd[$index]['valor'] = $serie;
        break;
      case DocumentoTipoNegocio::DATO_NUMERO:
        $numero = $valores->numero;
        if ($valores->numero == 'auto') {
          $numero = DocumentoNegocio::create()->obtenerNumeroAutoXDocumentoTipo($documentoTipoIdSGI);
        }
        $configuracionesDtd[$index]['valor'] = $numero;
        break;
      case DocumentoTipoNegocio::DATO_FECHA_EMISION:
        $fecha = date_create($valores->fechaEmision);
        $fecha = date_format($fecha, 'd/m/Y');
        $configuracionesDtd[$index]['valor'] = $fecha;
        break;
      case DocumentoTipoNegocio::DATO_FECHA_VENCIMIENTO:
        $fecha = date_create($valores->fechaVencimiento);
        $fecha = date_format($fecha, 'd/m/Y');
        $configuracionesDtd[$index]['valor'] = $fecha;
        break;
      case DocumentoTipoNegocio::DATO_FECHA_TENTATIVA:
        $fecha = date_create($valores->fechaTentativa);
        $fecha = date_format($fecha, 'd/m/Y');
        $configuracionesDtd[$index]['valor'] = $fecha;
        break;
      case DocumentoTipoNegocio::DATO_IMPORTE_SUB_TOTAL:
        $configuracionesDtd[$index]['valor'] = round($valores->subtotal, 2);
        break;
      case DocumentoTipoNegocio::DATO_IMPORTE_IGV:
        $configuracionesDtd[$index]['valor'] = round($valores->igv, 2);
        break;
      case DocumentoTipoNegocio::DATO_IMPORTE_TOTAL:
        $configuracionesDtd[$index]['valor'] = round($valores->total, 2);
        break;
      case DocumentoTipoNegocio::DATO_PERCEPCION:
        $configuracionesDtd[$index]['valor'] = round($valores->percepcion, 2);
        break;
      case DocumentoTipoNegocio::DATO_CUENTA:
        $cuenta = $valores->cuenta;
        if ($valores->cuenta == 'defecto') {
          $cuenta = $item["numero_defecto"];
        }
        $configuracionesDtd[$index]['valor'] = $cuenta;
        break;
      case DocumentoTipoNegocio::DATO_ACTIVIDAD:
        $actividad = $valores->actividad;
        if ($valores->actividad == 'defecto') {
          $actividad = $item["numero_defecto"];
        }
        $configuracionesDtd[$index]['valor'] = $actividad;
        break;
      case DocumentoTipoNegocio::DATO_DESCRIPCION:
        $configuracionesDtd[$index]['valor'] = $valores->descripcion;
        break;
    }
  }

  return $configuracionesDtd;
}

function personaSgiGuardaObtieneXDocumentoXRazonSocial($documento, $razonSocial)
{
  $personaId = "";
  include 'datos_abrir_bd.php';
  include 'parametros.php';
  require_once('../' . $carpetaSGI . '/modeloNegocio/almacen/PersonaNegocio.php');

  $stmt = $mysqli->prepare("select id from " . $baseSGI . ".persona where codigo_identificacion=? limit 1");
  $stmt->bind_param("s", $documento);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $personaId = $fila[0];
  }

  if ($personaId == '') {
    //inserto la persona en SGI
    $PersonaTipoIdo = 4;
    $codigoIdentificacion = $documento;
    $nombre = $razonSocial;
    $apellidoPaterno = null;
    $apellidoMaterno = null;
    $telefono = null;
    $celular = null;
    $email = null;
    $file = null;
    $estado = 1;
    $usuarioCreacion = $_SESSION['rec_usu_id'];
    $empresa = array(2);
    $clase = array(-1);
    $listaContactoDetalle = null;
    $listaDireccionDetalle = null;
    $codigoSunatId = null;
    $codigoSunatId2 = null;
    $codigoSunatId3 = null;
    $resp = PersonaNegocio::create()->insertPersona($PersonaTipoIdo, $codigoIdentificacion, $nombre, $apellidoPaterno, $apellidoMaterno, $telefono, $celular, $email, $file, $estado, $usuarioCreacion, $empresa, $clase, $listaContactoDetalle, $listaDireccionDetalle, $codigoSunatId, $codigoSunatId2, $codigoSunatId3);

    if ($resp[0]["vout_exito"] == 1) {
      $personaId = $resp[0]["id"];
    }
  }

  include 'datos_cerrar_bd.php';
  return $personaId;
}

function earLiqDetalleActualizarSgiDocumentoIdXEldIdXDocumentoId($eldId, $documentoId)
{
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("update ear_liq_detalle set sgi_documento_id=? where eld_id=?");
  $stmt->bind_param("ii", $documentoId, $eldId);
  $stmt->execute() or die($mysqli->error);

  include 'datos_cerrar_bd.php';
}

function plaMovActualizarSgiDocumentoIdXPlaIdXDocumentoId($plaId, $documentoId)
{
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("update pla_mov set sgi_documento_id=? where pla_id=?");
  $stmt->bind_param("ii", $documentoId, $plaId);
  $stmt->execute() or die($mysqli->error);

  include 'datos_cerrar_bd.php';
}

function earLiqDetalleObtenerXEarId($earId)
{
  $arr = array();
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $stmt = $mysqli->prepare("select ifnull(sgi_documento_id,'') as sgi_documento_id,lid_tc from ear_liq_detalle where ear_id =?");
  $stmt->bind_param("i", $earId);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    array_push($arr, $fila);
  }
  include 'datos_cerrar_bd.php';
  return $arr;
}

function earSolicitudObtenerXEarId($earId)
{
  $arr = array();
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $stmt = $mysqli->prepare("select sgi_documento_id,sgi_documento_id_2 from ear_solicitudes where ear_id=?");
  $stmt->bind_param("i", $earId);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    array_push($arr, $fila);
  }
  include 'datos_cerrar_bd.php';
  return $arr;
}

function plaMovObtenerXEarId($earId)
{
  $arr = array();
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $stmt = $mysqli->prepare("select sgi_documento_id from pla_mov where ear_id=?");
  $stmt->bind_param("i", $earId);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    array_push($arr, $fila);
  }
  include 'datos_cerrar_bd.php';
  return $arr;
}

function sgiDocumentoActualizarTotalXDocumentoId($documentoId, $nuevoTotal)
{
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $stmt = $mysqli->prepare("update " . $baseSGI . ".documento set total='" . $nuevoTotal . "' where id=?");
  $stmt->bind_param('i', $documentoId);
  $stmt->execute() or die($mysqli->error);

  include 'datos_cerrar_bd.php';
}

function earSolicitudActualizarSgiDocumentoId2XEarId($earId, $documentoId)
{
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("update ear_solicitudes set sgi_documento_id_2=? where ear_id=?");
  $stmt->bind_param("ii", $documentoId, $earId);
  $stmt->execute() or die($mysqli->error);

  include 'datos_cerrar_bd.php';
}

function eliminarDocumentoSgiXId($documentoId)
{
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $stmt = $mysqli->prepare("update " . $baseSGI . ".documento set estado=2 where id=?");
  $stmt->bind_param('i', $documentoId);
  $stmt->execute() or die($mysqli->error);

  include 'datos_cerrar_bd.php';
}

function obtenerUsuariosSGI()
{
  $arr = array();
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  //$stmt = $mysqli->prepare("call " . $baseSGI . ".sp_usuario_getAll();");
  $stmt = $mysqli->prepare("call " . $baseSGI . ".sp_persona_obtener_XPersonaClaseId(-2);");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    array_push($arr, $fila);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function obtenerEarSolicitudXSgiDocumentoId($documentoId)
{
  $arr = array();
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $stmt = $mysqli->prepare("select ear_id from ear_solicitudes where sgi_documento_id=? order by ear_id desc");
  $stmt->bind_param("i", $documentoId);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    array_push($arr, $fila);
  }
  include 'datos_cerrar_bd.php';
  return $arr;
}

function actualizarEstadoDesembolsado($earId, $usuarioId)
{
  include 'parametros.php';

  $id = $earId;
  $opcion = 4;
  $motivo = NULL;

  list(
    $ear_tra_nombres, $ear_numero, $zona_nom, $mon_nom, $mon_iso, $mon_simb, $mon_img, $ear_monto, $est_nom, $ear_sol_fec, $ear_liq_fec,
    $ear_sol_motivo, $ear_tra_dni, $ear_tra_cargo, $ear_tra_area, $ear_tra_sucursal, $ear_tra_cta,
    $usu_act, $ear_act_fec, $ear_act_motivo, $mon_id, $zona_id, $est_id, $usu_id,
    $ear_liq_mon, $ear_liq_ret, $ear_liq_ret_no, $ear_liq_det, $ear_liq_det_no, $ear_liq_dcto,
    $ear_liq_gast_asum, $pla_id, $ear_act_obs1, $ear_aprob_usu,
    $master_usu_id
  ) = getSolicitudInfo($id);

  include 'datos_abrir_bd.php';

  $mysqli->autocommit(FALSE);

  $query = "SELECT now()";
  $result = $mysqli->query($query) or die($mysqli->error);
  $fila = $result->fetch_array();
  $ahora = $fila[0];

  $stmt = $mysqli->prepare("UPDATE ear_solicitudes SET est_id=?, ear_act_usu=?, ear_act_fec=?, ear_act_motivo=?, ear_dese_fec=?
	WHERE ear_id=? AND est_id=2") or die($mysqli->error);
  $stmt->bind_param('iisssi', $opcion, $usuarioId, $ahora, $motivo, $ahora, $id);

  $stmt->execute() or die($mysqli->error);
  if ($stmt->affected_rows <> 1) {
    return 'No se actualizó el registro.';
    //        die('No se actualiz&oacute; el registro EAR');
  }

  $stmt = $mysqli->prepare("INSERT INTO ear_actualizaciones VALUES (?, ?, ?, ?, ?)") or die($mysqli->error);
  $stmt->bind_param('iiiss', $id, $opcion, $usuarioId, $ahora, $motivo);
  $stmt->execute() or die($mysqli->error);

  $stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die($mysqli->error);
  $desc = "Actualizacion estado solicitud EAR (" . $id . ") a valor " . $opcion;
  $ip = $_SERVER['REMOTE_ADDR'];
  $host = gethostbyaddr($ip);
  $stmt->bind_param('issss', $usuarioId, $desc, $ahora, $ip, $host);

  $stmt->execute() or die($mysqli->error);
  $stmt->close();

  $mysqli->commit();

  //-------------ENVIO DE CORREO------------------------
  //obteniendo correos
  $dataUsuarios = obtenerUsuariosIdXPerfil($pTESO);
  $to = array();
  $correoTo = '';
  foreach ($dataUsuarios as $index => $usuId) {
    $correoTeso = getCorreoUsuario($usuId);

    if (!is_null($correoTeso)) {
      $correoTo = $correoTo . $correoTeso . ';';
    }
  }

  $cc = array();
  list($usu_id_jefe, $usu_id_gerente) = getUsuJefeYGerenteDirecto($usu_id);
  array_push($cc, getCorreoUsuario($usu_id_jefe));
  if (!is_null($master_usu_id))
    array_push($cc, getCorreoUsuario($master_usu_id));

  $dataUsuariosAdmin = obtenerUsuariosIdXPerfil($pADMINIST);
  foreach ($dataUsuariosAdmin as $index => $usuId) {
    $correoAdmin = getCorreoUsuario($usuId);

    if (!is_null($correoAdmin) && $correoAdmin != '') {
      array_push($cc, $correoAdmin);
    }
  }

  $correo = $correoTo;
  $cc = array_unique($cc);
  foreach ($cc as $index => $valor) {
    $correo = $correo . $valor . ';';
  }
  //QUITAR CORREO DE USUARIO QUE USA EL SISTEMA
  $correoUsuario = getCorreoUsuario($usuarioId);
  $correo = str_replace($correoUsuario, '', $correo);

  //--------------------------------------------
  $subject = "Solicitud Desembolsada de EAR $ear_numero de " . $ear_tra_nombres;
  $body = "Se ha desembolsado la solicitud de EAR $ear_numero de $ear_tra_nombres por el monto de " . number_format($ear_monto, 2, '.', ',') . " $mon_nom.";
  //enviarCorreo($to, $cc, $subject, $body);
  //ConstructorMail::enviarCorreoInfo($to,$cc,$subject,$body,$var_modulo_ident);
  //-------registro email en sgi-------
  list($plantillaDestinatario, $plantillaAsunto, $plantillaCuerpo) = obtenerPlantillaEmailSGI(8);
  $plantillaCuerpo = str_replace("[|asunto|]", 'Solicitud Desembolsada de EAR', $plantillaCuerpo);
  $plantillaCuerpo = str_replace("[|cuerpo|]", $body, $plantillaCuerpo);

  //insertar en email_envio de sgi
  $emailEnvioId = insertarEmailEnvioSGI($correo, $subject, $plantillaCuerpo, $usuarioId, null, null);
  //----------------------------------

  $to = getCorreoUsuario($usu_id);
  $cc = null;
  $subject = "Solicitud Desembolsada de EAR $ear_numero de " . $ear_tra_nombres;
  $body = "Se ha desembolsado la solicitud de EAR $ear_numero de $ear_tra_nombres por el monto de " . number_format($ear_monto, 2, '.', ',') . " $mon_nom.";
  $body .= "\n\nNo se olvide luego de ingresar al modulo de Entregas a Rendir de la web. Y realizar el registro respectivo de la liquidaci&oacute;n para continuar el tr&aacute;mite.";
  //enviarCorreo($to, $cc, $subject, $body);
  //-------registro email en sgi-------
  list($plantillaDestinatario, $plantillaAsunto, $plantillaCuerpo) = obtenerPlantillaEmailSGI(8);
  $plantillaCuerpo = str_replace("[|asunto|]", 'Solicitud Desembolsada de EAR', $plantillaCuerpo);
  $plantillaCuerpo = str_replace("[|cuerpo|]", $body, $plantillaCuerpo);

  //insertar en email_envio de sgi
  $emailEnvioId = insertarEmailEnvioSGI($to, $subject, $plantillaCuerpo, $usuarioId, null, null);
  //----------------------------------

  include 'datos_cerrar_bd.php';

  return $emailEnvioId;
}

function actualizarAlEstadoAprobadoEAR($earId)
{
  //    include 'parametros.php';

  $arr = array();
  include 'datos_abrir_bd.php';

  $mysqli->set_charset('utf8');
  $stmt = $mysqli->prepare("select es.est_id,te.est_nom from ear_solicitudes es
                                            inner join tablas_estados te on te.est_id=es.est_id and te.tabla_id=1
                                    where es.ear_id=?");
  $stmt->bind_param("i", $earId);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    array_push($arr, $fila);
  }
  include 'datos_cerrar_bd.php';

  if ($arr[0]['est_id'] == 4) {

    include 'datos_abrir_bd.php';

    $stmt = $mysqli->prepare("UPDATE ear_solicitudes SET est_id=2, ear_dese_fec=null
                                        WHERE ear_id=? AND est_id=4") or die($mysqli->error);
    $stmt->bind_param('i', $earId);

    $stmt->execute() or die($mysqli->error);
    if ($stmt->affected_rows <> 1) {
      return 'No se actualizó el registro.';
      //        die('No se actualiz&oacute; el registro EAR');
    } else {
      $res->exito = 1;
    }

    include 'datos_cerrar_bd.php';
  } else if ($arr[0]['est_id'] == 2) {
    $res->exito = 1;
  } else {
    $res->exito = 0;
    $res->estadoDescripcion = $arr[0]['est_nom'];
  }

  return $res;
}

function getDua($ear_id)
{

  $arr = array();
  include 'parametros.php';
  include 'datos_abrir_bd.php';
  //$stmt = $mysqli->prepare("select * from bhdt_20170301.documento limit 10");
  $stmt = $mysqli->prepare("select distinct d.id as documento_id,		DATE_FORMAT(d.fecha_emision,'%d/%m/%Y') as fecha_emision,
case ifnull(d.serie,'')			when '' then d.numero            else concat(d.serie,'-',d.numero)		end as serie_numero ,
coalesce((select valor_numero
	from $baseSGI.documento_dato_valor ddv
		inner join $baseSGI.documento_tipo_dato dtd on dtd.id = ddv.documento_tipo_dato_id
        where ddv.documento_id = d.id 		and dtd.tipo = 28),0) as fob ,
(select ddv.valor_cadena from $baseSGI.documento_dato_valor ddv where ddv.documento_id = d.id and ddv.estado = 1 and ddv.documento_tipo_dato_id = 2803) as serie,
(select ddv.valor_cadena from $baseSGI.documento_dato_valor ddv where ddv.documento_id = d.id and ddv.estado = 1 and ddv.documento_tipo_dato_id = 2804) as numero,
    case
        when (d.id in (select dua_id from ear_solicitudes where dua_id is not null and ear_id!=$ear_id)) then 1
        else 0
    end as usado
from $baseSGI.documento d
inner join $baseSGI.documento_tipo dt on d.documento_tipo_id = dt.id
inner join $baseSGI.movimiento m on d.movimiento_id = m.id
where m.movimiento_tipo_id =135
and d.estado in (1)
and dt.estado=1
and m.estado=1
order by serie desc, numero desc");

  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5], $fila[6]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function getDuaXDuaId($dua_id)
{
  if ($dua_id == null) {
    $dua_id = '0';
  }

  $arr = array();
  include 'parametros.php';
  include 'datos_abrir_bd.php';
  //$stmt = $mysqli->prepare("select * from bhdt_20170301.documento limit 10");
  $stmt = $mysqli->prepare("select distinct d.id as documento_id,		DATE_FORMAT(d.fecha_emision,'%d/%m/%Y') as fecha_emision,
                case ifnull(d.serie,'')			when '' then d.numero            else concat(d.serie,'-',d.numero)		end as serie_numero ,
                coalesce((select valor_numero
                        from $baseSGI.documento_dato_valor ddv
                                inner join $baseSGI.documento_tipo_dato dtd on dtd.id = ddv.documento_tipo_dato_id
                        where ddv.documento_id = d.id 		and dtd.tipo = 28),0) as fob ,
                (select ddv.valor_cadena from $baseSGI.documento_dato_valor ddv where ddv.documento_id = d.id and ddv.estado = 1 and ddv.documento_tipo_dato_id = 2803) as serie,
                (select ddv.valor_cadena from $baseSGI.documento_dato_valor ddv where ddv.documento_id = d.id and ddv.estado = 1 and ddv.documento_tipo_dato_id = 2804) as numero
                from $baseSGI.documento d
                    inner join $baseSGI.documento_tipo dt on d.documento_tipo_id = dt.id
                    inner join $baseSGI.movimiento m on d.movimiento_id = m.id
                where m.movimiento_tipo_id =135
                    and d.estado in (1)
                    and dt.estado=1
                    and m.estado=1
                    and d.id=$dua_id
                order by serie desc, numero desc");

  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4], $fila[5], $fila[6]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function actualizarDocumentoValoresTipoCambioMontoNoAfecto($documentoId, $tcLiq, $montoNoAfecto)
{
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $stmt = $mysqli->prepare("update " . $baseSGI . ".documento set cambio_personalizado=?,noafecto=? where id=?");
  $stmt->bind_param('ddi', $tcLiq, $montoNoAfecto, $documentoId);
  $stmt->execute() or die($mysqli->error);

  include 'datos_cerrar_bd.php';
}

function registrarRelacionDuaDocumentoLiquidacion($dua_id, $documentoId, $relacionEar, $usuarioId)
{
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $stmt = $mysqli->prepare("insert into " . $baseSGI . ".documento_relacionado(
						documento_id,
						documento_relacionado_id,
						indicador,
                                                relacion_ear,
                                                usuario_creacion)
                                        values( ?,
                                                ?,
                                                1,
                                                ?,
                                                ?
                                             )");
  $stmt->bind_param('iiii', $dua_id, $documentoId, $relacionEar, $usuarioId);
  $stmt->execute() or die($mysqli->error);

  include 'datos_cerrar_bd.php';
}

function duaObtenerOtrosGastosIGV($duaId)
{

  $arr = array();
  include 'parametros.php';
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SET TIME_ZONE ='-05:00';");
  $stmt->execute() or die($mysqli->error);

  $stmt = $mysqli->prepare("select fecha_emision,
                                        cambio_personalizado as tipo_cambio,
                                        igv*cambio_personalizado as igv_soles
                                    from $baseSGI.documento
                                    where id=$duaId
                                        and documento_tipo_id=256");

  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function duaObtenerOtrosGastosAdvalorem($duaId)
{

  $arr = array();
  include 'parametros.php';
  include 'datos_abrir_bd.php';

  //PARA NUBE: mb.bien_id=1766
  $stmt = $mysqli->prepare("SET TIME_ZONE ='-05:00';");
  $stmt->execute() or die($mysqli->error);

  $stmt = $mysqli->prepare("select d.fecha_emision,
                                        d.cambio_personalizado as tipo_cambio,
                                        sum(ifnull(mb.ad_valorem,0)*d.cambio_personalizado) as importe
                                    from $baseSGI.documento d
                                            inner join $baseSGI.movimiento_bien mb on mb.movimiento_id=d.movimiento_id
                                    where d.id=$duaId
                                        and d.documento_tipo_id=256
                                        and mb.estado=1
                                    group by d.id;");

  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function duaObtenerOtrosGastosPercepcion($duaId)
{

  $arr = array();
  include 'parametros.php';
  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("SET TIME_ZONE ='-05:00';");
  $stmt->execute() or die($mysqli->error);

  $stmt = $mysqli->prepare("select d.fecha_emision,
                                    (select dd.cambio_personalizado from $baseSGI.documento dd where dd.id=$duaId) as tipo_cambio,
                                    d.total as importe
                                from $baseSGI.documento d
                                where d.documento_tipo_id=258
                                        and d.id in (select dr.documento_relacionado_id from $baseSGI.documento_relacionado dr
                                                                        where dr.estado=1 and dr.documento_id=$duaId)
                                    and d.estado=1
                                limit 1");

  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr[] = array($fila[0], $fila[1], $fila[2]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function obtenerPerfilContador($perfilId, $usuarioId)
{
  $contador = 0;
  include 'parametros.php';

  $stmt = $mysqli->prepare("select count(perfil_id) as contador_perfil from " . $baseSGI . ".usuario_perfil
                                    where estado=1 and perfil_id=? and usuario_id=?");
  $stmt->bind_param("ii", $perfilId, $usuarioId);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $contador = $fila[0];
  }

  include 'datos_cerrar_bd.php';
  return $contador;
}

function obtenerPeriodoAbiertoSGI()
{
  $arr = array();
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $stmt = $mysqli->prepare("call " . $baseSGI . ".sp_periodo_obtenerAbiertoXEmpresaId(2);");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    array_push($arr, $fila);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function obtenerPeriodoSGIXId($periodoId)
{
  $arr = array();
  include 'datos_abrir_bd.php';
  include 'parametros.php';

  $stmt = $mysqli->prepare("call " . $baseSGI . ".sp_periodo_obtenerXid(" . $periodoId . ");");
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    array_push($arr, $fila);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function obtenerDocumentoRegistrado($documentoTipoId, $ruc, $serie, $numero, $earId)
{
  $arr = array();

  include 'datos_abrir_bd.php';

  $stmt = $mysqli->prepare("
                select ld.eld_id,
                       CONCAT(e.ear_anio, '-', LPAD(e.ear_mes, 2, '0'), '-', LPAD(e.ear_nro, 3, '0'), '/',
                              fn_obtener_iniciales(concat(ifnull(sp.nombre,''),' ',ifnull(sp.apellido_paterno,''),' ',ifnull(sp.apellido_materno,'')))) ear_numero
                from ear_liq_detalle ld
                inner join ear_solicitudes e on e.ear_id = ld.ear_id
                inner join doc_tipos dt on dt.doc_id = ld.doc_id
                inner join " . $baseSGI . ".usuario su on su.id=e.usu_id
                inner join " . $baseSGI . ".persona sp on sp.id=su.persona_id
                where e.ear_id <> ?
                    and dt.doc_id = ?
                    and ld.ruc_nro = ?
                    and ld.lid_ser = ?
                    and (ld.lid_nro*1 = ? Or ld.lid_nro = ?)");
  $stmt->bind_param("iissss", $earId, $documentoTipoId, $ruc, $serie, $numero, $numero);
  $stmt->execute() or die($mysqli->error);
  $stmt->store_result();
  while ($fila = fetchAssocStatement($stmt)) {
    $arr = array($fila[0], $fila[1]);
  }

  include 'datos_cerrar_bd.php';

  return $arr;
}

function isEmpty($object)
{
  if (!isset($object))
    return true;
  if (is_null($object))
    return true;
  if (is_string($object) && strlen($object) <= 0)
    return true;
  if (is_array($object) && empty($object))
    return true;
  if (is_numeric($object) && is_nan($object))
    return true;

  return false;
}
