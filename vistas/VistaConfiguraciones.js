
// **********************************************************************************************************
// - CONFIGURACION A CAMBIAR EN CADA SITIO WEB.
// **********************************************************************************************************
// **********************************************************************************************************

var URL_EXECUTECONTROLLER = URL_BASE + "controlador/core/Itec.php";
var URL_UPLOADHANDLER = URL_BASE + "util/uploadHandler.php";

var PARAM_ACCION_NAME = "action_name";
var PARAM_TAG = "tag";
var TYPE_FORZAR_LOGOUT = '0';
var FORMAT_JSON_EASYUI = "json_easyui";

var PAGE_NUMBER = "pageNumber";
var PAGE_SIZE = "pageSize";

var PARAM_CONTEXT_FUNCTIONS = "contextFunctions";

var PARAM_GET_ACCIONES_SEGURIDAD = 'get_acciones_seguridad'; // parámetro que se le pasa al controlador para que me devuelva las acciones por el rol de usuario
var RESPONSE_ACCIONES_SEGURIDAD = 'acciones_seguridad';

var PARAM_TIME_ZONE = 'param_time_zone';

var PARAM_OPCION_ID = 'param_opcion_id'; //parametro que se le pasa al controlador para saber que componente esta llamando a los tabs comunes en Task y HelpDesk
var RESPONSE_MENSAJE_EMERGENTE = 'response_mensaje_emergente'; // parametro del array de respuesta que da el controlador en caso todo haya ocurrido satisfactoriamente.

var COOKIE_NAME_SID = "pepas_sid";
var PARAM_SID = "param_sid";
var PARAM_USU = "param_usu";

var UPLOAD_NAME = "upload_file";
var PARAM_FLAG_DATATABLE = "param_flag_datatable";

//PARA NUBE 622
var persona_sunat_id=622;

  //Documento tipo ids
  var COTIZACIONES = 104;
  var SOLICITUD_REQUERIMIENTO = 280;
  var GENERAR_COTIZACION = 281;
  var ORDEN_COMPRA = 282; 
  var REQUERIMIENTO_AREA = 283;
  var ORDEN_SERVICIO = 284;
  var COTIZACION_SERVICIO = 285;
  var INGRESO_RESERVA_STOCK = 286;
  var SALIDA_RESERVA_STOCK = 287;
  var GENERAR_COTIZACION_SERVICIO = 288;
  var RECEPCION = 265;
  var DESPACHO = 289;
  var SOLICITUD_ENTREGA = 290;

  var AREA_LOGISTICA = 10;

  var dtdTipoRequerimientoListaCompra = 454;
  var dtdTipoRequerimientoListaServicio = 455;
  var dtdTipoRequerimientoListaConsignacion = 512;