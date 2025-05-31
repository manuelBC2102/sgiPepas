//import JsonViewer from '../../libs/imagina/assets/json/json-viewer-js.js'

var dataDocumentoTipoDato;
var banderaBuscar = 0;
var estadoTolltip = 0;
var bandera_eliminar = false;
var bandera_aprobar = false;
var tipoInterfaz = document.getElementById("tipoInterfaz").value;
var currentUserEmail;
var bandera = {
    primeraCargaDocumentosRelacion: true
};

$('.gang-name-1').click(function () {
    if ($(this).hasClass("collapsed")) {
        $(this).nextUntil('tr.gang-name-1')
                .find('td')
                .parent()
                .find('td > div')
                .slideDown("fast", function () {
                    var $set = $(this);
                    $set.replaceWith($set.contents());
                });
        $(this).removeClass("collapsed");
    } else {
        $(this).nextUntil('tr.gang-name-1')
                .find('td')
                .wrapInner('<div style="display: block;" />')
                .parent()
                .find('td > div')
                .slideUp("fast");
        $(this).addClass("collapsed");
    }
});
$('.gang-name-2').click(function () {
    if ($(this).hasClass("collapsed")) {
        $(this).nextUntil('tr.gang-name-2')
                .find('td')
                .parent()
                .find('td > div')
                .slideDown("fast", function () {
                    var $set = $(this);
                    $set.replaceWith($set.contents());
                });
        $(this).removeClass("collapsed");
    } else {
        $(this).nextUntil('tr.gang-name-2')
                .find('td')
                .wrapInner('<div style="display: block;" />')
                .parent()
                .find('td > div')
                .slideUp("fast");
        $(this).addClass("collapsed");
    }
});


$(document).ready(function () {
    dataDocumentoTipoDato = [];
    $('[data-toggle="popover"]').popover({html: true}).popover();
    cargarTitulo("titulo", "");
    select2.iniciar();
    ax.setSuccess("onResponseMovimientoListar");
//    obtenerMovimientoTipoColumnaLista();
    obtenerDocumentoTipo();
    iniciarDataPicker();
    cambiarAnchoBusquedaDesplegable();

    if (commonVars.titulo == "Recepción" || commonVars.titulo == "Cotizaciones de servicio") {
        $("#btnNuevo").hide();
    }
});

/**
 *
 * @param response
 * @param response.data
 * @param response.data.columna
 */
var dataConfiguracionInicial;
function onResponseMovimientoListar(response) {
    //breakFunction();
    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obtenerDocumentoTipo':
                dataConfiguracionInicial = response.data;
                if (response.data.documento_tipo[0]['id'] === '190') {
                    $('#liNumeroOrdenCompra').show();
                }
                onResponseObtenerDocumentoTipoDesplegable(response.data);
                onResponseObtenerMovimientoTipoColumnaLista(response.data.columna);
                buscarDesplegable();
                dibujarLeyendaAcciones(response.data.acciones);
                loaderClose();
                break;
            case 'imprimir':
                loaderClose();
                if (!isEmpty(response.data.dataDocumento)) {
                    cargarDatosImprimir(response.data);
                } else if (!isEmpty(response.data.iReport)) {
                    abrirDocumentoPDF(response.data, URL_BASE + '/reporteJasper/documentos/');
                } else {
                    abrirDocumentoPDF(response.data, 'vistas/com/movimiento/documentos/');
                }
                break;
            case 'generarXml':
                loaderClose();
                if (!isEmpty(response.data)) {
                    window.open(response.data);
                }
                break;
            case 'anular':
//                loaderClose();
////                habilitarBotonSweetGeneral();
//                swal("Anulado!", "Documento anulado correctamente.", "success");
//                bandera_eliminar = true;
//                buscar();
                onResponseAnular(response.data);
                loaderClose();
                break;
            case 'obtenerDocumentoRelacionVisualizar':
                onResponseObtenerDocumentoRelacionVisualizar(response.data);
                loaderClose();
                break;
            case 'obtenerDocumentoArchivosVisualizar':
                onResponseObtenerDocumentoArchivosVisualizar(response.data);
                loaderClose();
                break;
            case 'guardarArchivosXDocumentoID':
                onResponseGuardarArchivosXDocumentoID(response.data);
//                exitoCrear(response.data);
                loaderClose();
                break;
            case 'aprobar':
                loaderClose();
                swal("Aprobado!", "Documento aprobado correctamente.", "success");
                bandera_aprobar = true;
                buscar();
                break;
            case 'exportarReporteExcel':
                loaderClose();
                location.href = URL_BASE + "util/formatos/Reporte_Movimientos.xlsx";
                break;
            case 'descargarFormato':
                loaderClose();
                location.href = URL_BASE + "util/formatos/formato_movimientos.xlsx";
                break;
            case 'importarExcelMovimiento':
                loaderClose();
                descargarExcel(response.data);
                //location.href = URL_BASE + "util/formatos/formato_movimientos.xlsx";                
                break;
            case 'obtenerDocumentosRelacionados':
                onResponseObtenerDocumentosRelacionados(response.data);
                loaderClose();
                break;
            case 'enviarCorreoDetalleDocumento':
                $('#modalDetalleDocumento').modal('hide');
                $('.modal-backdrop').hide();
                loaderClose();
                break;
            case 'editarComentarioDocumento':
                exitoCrear(response.data);
                loaderClose();
                break;
            case 'buscarCriteriosBusqueda':
                onResponseBuscarCriteriosBusqueda(response.data);
                loaderClose();
                break;
            case 'guardarEdicionDocumento':
                exitoCrear(response.data);
                $('#modalDetalleDocumento').modal('hide');
                $('.modal-backdrop').hide();
                buscar();
                loaderClose();
                break;
            case 'obtenerPersonaDireccion':
                if (personaDireccionId !== 0) {
                    onResponseObtenerDataCbo("_" + personaDireccionId, "id", "direccion", response.data);
                }
                if (textoDireccionId !== 0) {
                    onResponseObtenerPersonaDireccionTexto(response.data);
                }
                break;
            case 'obtenerPersonaContacto':
                onResponseobtenerPersonaContacto(response.data);
                break;
            case 'enviarMovimientoEmailPDF':
                if (!isEmpty(response.data[0]['id'])) {
                    $('#modalDetalleDocumento').modal('hide');
                    $('.modal-backdrop').hide();
                }
                loaderClose();
                break;
                //COLUMNAS DEL LISTADO DINAMICO
            case 'obtenerMovimientoTipoColumnaLista':
                onResponseObtenerMovimientoTipoColumnaLista(response.data);
                loaderClose();
                break;
            case 'obtenerEmailsXAccion':
                onResponseObtenerEmailsXAccion(response.data);
                loaderClose();
                break;
            case 'enviarCorreoXAccion':
                $('#modalDetalleDocumento').modal('hide');
                $('.modal-backdrop').hide();
                loaderClose();
                break;
            case 'getUserEmailByUserId':
                onResponseGetUserEmailByUserId(response.data);
                loaderClose();
                break;
            case 'obtenerReporteDocumentosAsignaciones':
                onResponseObtenerReporteDocumentosAsignaciones(response.data);
                loaderClose();
                break;
            case 'generarBienUnicoXDocumentoId':
                onResponseGenerarBienUnicoXDocumentoId(response.data);
                loaderClose();
                break;
            case 'anularBienUnicoXDocumentoId':
                loaderClose();
                if (response.data[0].vout_exito == 1)
                {
                    swal("Anulado!", response.data[0].vout_mensaje, "success");
                    buscar();
                } else
                {
                    swal("Cancelado!", response.data[0].vout_mensaje, "error");
                }
                break;
            case 'obtenerBienUnicoConfiguracionInicial':
                onResponseObtenerBienUnicoConfiguracionInicial(response.data);
                loaderClose();
                break;
            case 'guardarBienUnicoDetalle'  :
                onResponseGuardarBienUnicoDetalle(response.data);
                loaderClose();
                break;

                //FUNCIONES PARA COPIAR DOCUMENTO
            case 'obtenerConfiguracionBuscadorDocumentoRelacion':
                onResponseObtenerConfiguracionBuscadorDocumentoRelacion(response.data);
                buscarDocumentoRelacionPorCriterios();
                loaderClose();
                break;
            case 'buscarDocumentoRelacion':
                onResponseBuscarDocumentoRelacion(response.data);
                loaderClose();
                break;
            case 'relacionarDocumento':
                mostrarOk('Se registró la relación.');
                if (modalReferenciaGlobalId == "modalDetalleDocumento") {
                    preVisualizarTab = "dataDocumentoRelacion";
                    visualizarDocumento(docId, movId);
                    actualizarBusqueda();
                } else {
                    obtenerDocumentosRelacionados(documentoIdOrigen);
                }

                loaderClose();
                break;

            case 'eliminarRelacionDocumento':
                mostrarOk('Se eliminó correctamente la relación.');
                preVisualizarTab = "dataDocumentoRelacion";
                visualizarDocumento(docId, movId);
                actualizarBusqueda();
                break;

            case 'anularDocumentoMensaje':
                onResponseAnularDocumentoMensaje(response.data);
                loaderClose();
                break;
            case 'validarDocumentoEdicion':
                onResponseValidarDocumentoEdicionServicio(response.data, response[PARAM_TAG]);
                loaderClose();
                break;
            case 'consultarEstadoSunat':
                //respuestaSunat(response.data);
                loaderClose();
                break;
            case 'insertarListaComprobacion':
                cargarDataListaComprobacionDocumento(response.data);
                loaderClose();
                break;
            case 'editarEstadoListaComprobacion':
                cargarDataListaComprobacionDocumento(response.data);
                loaderClose();
                break;
            case 'ordenarArribaEstadoListaComprobacion':
                cargarDataListaComprobacionDocumento(response.data);
                loaderClose();
                break;
            case 'generarExcelLiquidacion':
                loaderClose();
                location.href = URL_BASE + "util/formatos/Liquidacion.xlsx";
                break;
            case 'generarExcelCotizacion':
                loaderClose();
                location.href = URL_BASE + "util/formatos/Cotizacion.xlsx";
                break;

            case 'obtenerHistorialDocumento':
                if (!isEmpty(response.data)) {
                    let dataDocumento = JSON.parse(response.data[0]['valor']);
                    onResponseObtenerDocumentoRelacionVisualizar(dataDocumento, 1);
                }
                loaderClose();
                break;

            case 'reenviarComprobante':
                onResponseReenviarComprobante(response.data);
                loaderClose();
                break;
                
            case 'aprobarCotizacion':
                onResponseAprobarCotizacion(response.data);
                loaderClose();
                break;
            case 'obtenerPdfOrdenCompra':
                loaderClose();
                if (!isEmpty(response.data.dataDocumento)) {
                    cargarDatosImprimir(response.data);
                } else if (!isEmpty(response.data.iReport)) {
                    abrirDocumentoPDF(response.data, URL_BASE + '/reporteJasper/documentos/');
                } else {
                    abrirDocumentoPDF(response.data, 'vistas/com/movimiento/documentos/');
                }
                break;
            case 'obtenerPdfOrdenServicio':
                loaderClose();
                if (!isEmpty(response.data.dataDocumento)) {
                    cargarDatosImprimir(response.data);
                } else if (!isEmpty(response.data.iReport)) {
                    abrirDocumentoPDF(response.data, URL_BASE + '/reporteJasper/documentos/');
                } else {
                    abrirDocumentoPDF(response.data, 'vistas/com/movimiento/documentos/');
                }
                break;   
            case 'imprimirDocumentoAdjunto':
                    const link = document.createElement('a');
                    link.href = URL_BASE+"util/uploads/documentoAdjunto/"+ response.data[0]['nombre'];
                    link.target = '_blank';
                    link.click();
                    setTimeout(function () {
                        eliminarPDF(contenedor + data.pdf);
                    }, 4000);
                break;               
        }
    } else {
        switch (response[PARAM_ACCION_NAME]) {
            case 'imprimir':
                loaderClose();
                break;
            case 'generarXml':
                loaderClose();
                break;
            case 'anular':
                loaderClose();
                swal({title: "Cancelado", text: response.message, type: "error", html: true});
                break;
            case 'aprobar':
                loaderClose();
                break;
            case 'obtenerDocumentoRelacionVisualizar':
                loaderClose();
                break;
            case 'importarExcelMovimiento':
                loaderClose();
                descargarExcel(response.data);
                //location.href = URL_BASE + "util/formatos/formato_movimientos.xlsx";
                break;
            case 'descargarFormato':
                loaderClose();
                break;
            case 'obtenerDocumentosRelacionados':
                loaderClose();
                break;
            case 'enviarCorreoDetalleDocumento':
                loaderClose();
                break;
            case 'enviarMovimientoEmailPDF':
                loaderClose();
                break;
            case 'generarBienUnicoXDocumentoId':
                loaderClose();
                swal("Cancelado", response.message, "error");
                break;
            case 'guardarBienUnicoDetalle'  :
                loaderClose();
                swalMostrarSoloConfirmacion('error', 'Cancelado!', response.message, 'mostrarModalAsignacionCU()');
                break;
            case 'relacionarDocumento':
                cargarModalCopiarDocumentos();
                loaderClose();
                break;
            case 'eliminarRelacionDocumento':
                cargarModalDetalleDocumento();
                break;
            case 'anularDocumentoMensaje':
                loaderClose();
                break;
            case 'consultarEstadoSunat':
                loaderClose();
                break;
            case 'ordenarArribaEstadoListaComprobacion':
                loaderClose();
                break;
            case 'imprimirDocumentoAdjunto':
                loaderClose();
                break;
        }
    }
}

function mostrarModalAsignacionCU() {
    $('#modalAsignarCodigoUnico').modal('show')
}

function swalMostrarSoloConfirmacion(tipo, titulo, mensaje, funcion) {
    swal({
        title: titulo,
        text: mensaje,
        type: tipo,
        showCancelButton: false,
        confirmButtonColor: "#33b86c",
        confirmButtonText: "Ok",
        cancelButtonColor: '#d33',
        cancelButtonText: "No, cancelar !",
        closeOnConfirm: true,
        closeOnCancel: true
    }, function (isConfirm) {
        if (isConfirm) {
            eval(funcion);
        }
    });
}

function onResponseGetUserEmailByUserId(data)
{
    appendCurrentUserEmail(data);
}

/**
 *
 * @param data
 * @param data.vout_exito
 */
function descargarExcel(data)
{
    if (data.vout_exito == '0') {
        location.href = URL_BASE + "util/formatos/formato_movimientos.xlsx";
    }
    $("#fileInfo").text("").show();
    buscar();
}

function obtenerDocumentoTipo()
{
    ax.setAccion("obtenerDocumentoTipo");
    ax.addParamTmp("empresaId", commonVars.empresa);
    ax.consumir();
}
/**
 *
 * @param data
 * @param data.documento_tipo
 * @param data.documento_tipo_dato
 * @param data.documento_tipo_dato_lista
 * @param data.persona_activa
 */
function onResponseObtenerDocumentoTipo(data) {
    if (!isEmpty(data.documento_tipo)) {
        select2.cargar("cboDocumentoTipo", data.documento_tipo, "id", "descripcion");
        onResponseObtenerDocumentoTipoDato(data.documento_tipo_dato, data.persona_activa);
        onResponseCargarDocumentotipoDatoLista(data.documento_tipo_dato_lista);
    }
}

function onResponseCargarDocumentotipoDatoLista(dataValor)
{
    if (!isEmpty(dataValor))
    {
        $.each(dataValor, function (index, item) {
            select2.cargar("cbo_" + item.id, item.data, "id", "descripcion");
        });
    }
}

function onResponseObtenerDocumentoTipoDato(data, personaActiva) {
    dataDocumentoTipoDato = data;
    $("#formularioDocumentoTipo").empty();
    if (!isEmpty(data)) {
        // Mostraremos la data en filas de dos columnas

        var columna = 1;
        $.each(data, function (index, item) {
            switch (columna) {
                case 1:
                    if (index > 0) {
                        appendForm('</div>');
                    }
                    appendForm('<div class="row">');
                    columna = 2;
                    break;
                case 2:
                    columna = 3;
                    break;
                case 3:
                    columna = 1;
                    break;
            }

            var html = '<div class="form-group col-md-4">' +
                    '<label>' + item.descripcion + '</label>' +
                    '<div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">';
            switch (parseInt(item.tipo)) {
                case 1:
                case 14:
                    html += '<input type="text" id="txt_' + item.id + '" name="txt_' + item.id + '" class="form-control" value="" maxlength="8"/>';
                    break;
                case 2:
                case 6:
                case 7:
                case 8:
                case 12:
                case 13:
                    html += '<input type="text" id="txt_' + item.id + '" name="txt_' + item.id + '" class="form-control" value="" maxlength="45"/>';
                    break;
                case 3:
                case 9:
                case 10:
                case 11:
                    html += '<div class="row">' +
                            '<div class="form-group col-md-6">' +
                            '<div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">' +
                            '<input type="text" class="form-control fecha" placeholder="dd/mm/yyyy" id="datepicker_inicio_' + item.id + '">' +
                            '<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>' +
                            '</div></div>' +
                            '<div class="form-group col-md-6">' +
                            '<div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">' +
                            '<input type="text" class="form-control fecha" placeholder="dd/mm/yyyy" id="datepicker_fin_' + item.id + '">' +
                            '<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>' +
                            '</div></div></div>';
                    break;
                case 4:
                    html += '<select name="cbo_' + item.id + '" id="cbo_' + item.id + '" class="select2"></select>';
                    break;
                case 5:
                    html += '<div id="div_persona"><select name="cbo_' + item.id + '" id="cbo_' + item.id + '" class="select2">';
                    html += '<option value="' + 0 + '">Seleccione la persona</option>';
                    if (!isEmpty(personaActiva))
                    {
                        $.each(personaActiva, function (indexPersona, itemPersona) {
                            html += '<option value="' + itemPersona.id + '">' + itemPersona.nombre + ' | ' + itemPersona.codigo_identificacion + '</option>';
                        });
                    }
                    html += '</select></div>';
                    break;
            }
            html += '</div></div>';
            appendForm(html);
            switch (item.tipo) {
                case 4, "4":
                    $("#cbo_" + item.id).select2({
                        width: '100%'
                    });
                    break;
                case 5, "5":
                    $("#cbo_" + item.id).select2({
                        width: '100%'
                    });
                    break;
            }
        });
        appendForm('</div>');
        $('.fecha').datepicker({
            isRTL: false,
            format: 'dd/mm/yyyy',
            autoclose: true,
            language: 'es'
        });
    }
}

function appendForm(html) {
    $("#formularioDocumentoTipo").append(html);
}

function cargarDatoDeBusqueda()
{
    $.each(dataDocumentoTipoDato, function (index, item) {
        switch (parseInt(item.tipo)) {
            case 1:
            case 14:
                item["valor"] = $('#txt_' + item.id).val();
                break;
            case 2:
            case 6:
            case 7:
            case 8:
            case 12:
            case 13:
                item["valor"] = $('#txt_' + item.id).val();
                break;
            case 3:
            case 9:
            case 10:
            case 11:
                var f = {inicio: $('#datepicker_inicio_' + item.id).val(),
                    fin: $('#datepicker_fin_' + item.id).val()};
                item["valor"] = f;
                break;
            case 4:
            case 5:
                item["valor"] = $('#cbo_' + item.id).val();
                break;

        }
    });
}

function obtenerDatosBusqueda()
{
    var valorPersona;

    tipoDocumento = $('#cboDocumentoTipo').val();
    var cadena = "";
//    if (!isEmpty(tipoDocumento))
//    {

    cargarDatoDeBusqueda();
    var valorTipoDocumento = obtenerValorTipoDocumento();
    cadena += (!isEmpty(valorTipoDocumento)) ? valorTipoDocumento + "<br>" : "";
    $.each(dataDocumentoTipoDato, function (index, item) {


        switch (parseInt(item.tipo)) {
            case 1:
            case 2:
            case 6:
            case 7:
            case 8:
            case 12:
            case 13:
            case 14:
                if (!isEmpty(item.valor))
                {
                    cadena += negrita(item.descripcion) + ": ";
                    cadena += item.valor + " ";
                    cadena += "<br>";
                }
                break;
            case 3:
            case 9:
            case 10:
            case 11:
                if (!isEmpty(item.valor.inicio) || !isEmpty(item.valor.fin))
                {
                    cadena += negrita(item.descripcion) + ": ";
                    cadena += item.valor.inicio + " - " + item.valor.fin + " ";
                    cadena += "<br>";
                }
                break;
            case 4:

                if (!isEmpty(item.valor))
                {
                    if (select2.obtenerText('cbo_' + item.id) !== null)
                    {
                        cadena += negrita(item.descripcion) + ": ";
                        cadena += select2.obtenerText('cbo_' + item.id) + " ";
                        cadena += "<br>";
                    }
                }
                break;
            case 5:
                if (item.valor != 0)
                {
                    cadena += negrita(item.descripcion) + ": ";
                    valorPersona = select2.obtenerText('cbo_' + item.id);
                    cadena += valorPersona;
                    cadena += "<br>";
                }
                break;
        }
    });
    dataDocumentoTipoDato[0]['tipoDocumento'] = tipoDocumento;
    return cadena;
//    }
//    return 0;
}

function cerrarPopover()
{
    if (banderaBuscar == 1)

    {
        if (estadoTolltip == 1)
        {
            $('[data-toggle="popover"]').popover('hide');
        } else
        {
            $('[data-toggle="popover"]').popover('show');
        }
    } else
    {
        $('[data-toggle="popover"]').popover('hide');
    }


    estadoTolltip = (estadoTolltip == 0) ? 1 : 0;
}

function obtenerValorTipoDocumento()
{
    var valorTipoDocumento = select2.obtenerTextMultiple('cboDocumentoTipo');
    if (valorTipoDocumento !== null)
    {
        var cadena = negrita("Tipo de documento: ") + valorTipoDocumento;
        return cadena;
    }
    return "";
}

function exportarReporteExcel(colapsa) {
    loaderShow();
//    var cadena;
//    cadena = obtenerDatosBusqueda();
//    cadena = (!isEmpty(cadena) && cadena !== 0) ? cadena : "Todos";
//
//    $('#idPopover').attr("data-content", cadena);
//    $('[data-toggle="popover"]').popover('show');
//    banderaBuscar = 1;
    //getDataTable();
    ax.setAccion("exportarReporteExcel");
    ax.addParamTmp("criterios", dataDocumentoTipoDato);
    //ax.addParamTmp("anio", 2015);
    //ax.addParamTmp("mes", 10);
    ax.consumir();

//    if (colapsa === 1)
//        colapsarBuscador();
}

function descargarFormato(colapsa) {
    loaderShow();
//    var cadena;
//    cadena = obtenerDatosBusqueda();
//    cadena = (!isEmpty(cadena) && cadena !== 0) ? cadena : "Todos";
//
//    $('#idPopover').attr("data-content", cadena);
//    $('[data-toggle="popover"]').popover('show');
    banderaBuscar = 1;
    //getDataTable();
    ax.setAccion("descargarFormato");
    ax.addParamTmp("criterios", dataDocumentoTipoDato);
    //ax.addParamTmp("anio", 2015);
    //ax.addParamTmp("mes", 10);
    ax.consumir();

//    if (colapsa === 1)
//        colapsarBuscador();
}

function buscar(colapsa)
{
    loaderShow();
    getDataTable();
}

function obtenerFechaActualBD() {
    var hoy = new Date();
    var dia = hoy.getDate();
    dia = (dia < 10) ? ('0' + dia) : dia;
    var mes = hoy.getMonth() + 1;
    mes = (mes < 10) ? ('0' + mes) : mes;
    var anio = hoy.getFullYear();

    return anio + "-" + mes + "-" + dia;
}

function getDataTable() {


    ax.setAccion("obtenerDocumentos");
    ax.addParamTmp("criterios", dataDocumentoTipoDato);

    $('#datatable').dataTable({
        "language": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "Ning\xfAn dato disponible en esta tabla",
            "sInfo": "Mostrando del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix": "",
            "sSearch": "Buscar:",
            "sUrl": "",
            "sInfoThousands": ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Último",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        },
        "order": [[indexFechaCreacion, "desc"]],
        "processing": true,
        "serverSide": true,
        "bFilter": false,
        "ajax": ax.getAjaxDataTable(),
        "scrollX": true,
        "columns": detalleColumna,
        "columnDefs": detalleColumnaDefs,
        fnCreatedRow: function (nRow, aData, iDataIndex) {
            if (aData.efact_ws_estado == 3) {
                $(nRow).attr('style', 'background-color: #ffc4c4;');
            }
        },
        /*"fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull){
         
         console.log(iDisplayIndex);
         // var $th = $td.closest('table').find('th').eq($td.index());
         $('td:eq(7)', nRow).html( "<a href='#' onclick='abrirModalReporteAtenciones()' >"+aData['documento_estado_negocio_descripcion']+"</a>" );
         },*/
        "dom": '<"top">rt<"bottom"<"col-md-3"l><"col-md-9"p><"col-md-12"i>><"clear">',
        //"aoColumnDefs":[
        //    {
        //        "aTargets":[2],
        //        "mRender": function(data, type, full){
        //            return data;
        //            //return '<a href="google.com/'+data+">'";
        //        }
        //    }
        //],
        destroy: true
    });
    loaderClose();
}

function nuevoFormServicio()
{
    ;
    loaderShow();
    VALOR_ID_USUARIO = null;

    if (tipoInterfaz == 2) {
        cargarDiv('#window', 'vistas/com/compraServicio/servicio_form_tablas.php?tipoInterfaz=' + tipoInterfaz);
    } else if (tipoInterfaz == 3) {
        cargarDiv('#window', 'vistas/com/compraServicio/servicio_form_tablas_atencion.php?tipoInterfaz=' + tipoInterfaz);
    } else {
        cargarDiv('#window', 'vistas/com/compraServicio/servicio_form.php');
    }
}

function negrita(cadena)
{
    return "<b>" + cadena + "</b>";
}
var actualizandoBusqueda = false;
function actualizarBusqueda()
{
    buscar(0);
}
function actualizarBusquedaExcel()
{
    exportarReporteExcel(0);
}
function colapsarBuscador() {
    if (actualizandoBusqueda) {
        actualizandoBusqueda = false;
        return;
    }
    var bgInfo = $('#bg-info');
    if (bgInfo.hasClass('in')) {
        bgInfo.attr('aria-expanded', "false");
        bgInfo.attr('height', "0px");
        bgInfo.removeClass('in');
    } else {
        bgInfo.attr('aria-expanded', "false");
        bgInfo.removeAttr('height', "0px");
        bgInfo.addClass('in');
    }
}
function imprimirDocumento(id, documentoTipo)
{
    if(documentoTipo == ORDEN_COMPRA || documentoTipo == ORDEN_SERVICIO){
            const link = document.createElement('a');
            link.href = URL_BASE + "vistas/com/compraServicio/compra_servicio_pdf.php?id=" + id + "&documentoTipoId=" + documentoTipo;
            link.target = '_blank';
            link.click();
    }else{
        loaderShow();
        ax.setAccion("imprimir");
        ax.addParamTmp("id", id);
        ax.addParamTmp("documento_tipo_id", documentoTipo);
        ax.consumir();
    }
}
function imprimirDocumentoAdjunto(id, documentoTipo)
{
    loaderShow();
    ax.setAccion("imprimirDocumentoAdjunto");
    ax.addParamTmp("id", id);
    ax.addParamTmp("documento_tipo_id", documentoTipo);
    ax.consumir();
}
function generarXml(id, documentoTipo)
{
    loaderShow();
    ax.setAccion("generarXml");
    ax.addParamTmp("id", id);
    ax.addParamTmp("tipo", 1);
    ax.addParamTmp("documento_tipo_id", documentoTipo);
    ax.consumir();
}
function generarCdr(id, documentoTipo)
{
    loaderShow();
    ax.setAccion("generarXml");
    ax.addParamTmp("id", id);
    ax.addParamTmp("tipo", 2);
    ax.addParamTmp("documento_tipo_id", documentoTipo);
    ax.consumir();
}
function anularDocumento(id)
{
    confirmarAnularMovimiento(id);
}

function anular(id)
{
    loaderShow();
    ax.setAccion("anular");
    ax.addParamTmp("id", id);
    ax.addParamTmp("documentoTipoId", docTipo);
    ax.consumir();
}

var docId;
var movId;
function visualizarDocumento(documentoId, movimientoId)
{
    docId = documentoId;
    documentoIdOrigen = documentoId;
    movId = movimientoId;

    $('#txtCorreo').val('');
    loaderShow();
    ax.setAccion("obtenerDocumentoRelacionVisualizar");
    ax.addParamTmp("documentoId", documentoId);
    ax.addParamTmp("movimientoId", movimientoId);
    ax.consumir();
    deleteChild();
}

function visualizarArchivos(documentoId, movimientoId)
{
    docId = documentoId;

    loaderShow();
    ax.setAccion("obtenerDocumentoArchivosVisualizar");
    ax.addParamTmp("documentoId", documentoId);
    ax.addParamTmp("movimientoId", movimientoId);
    ax.consumir();
}

function guardarDocumentosAdjuntos() {
    loaderShow('#modalVisualizarArcvhivos');

    ax.setAccion("guardarArchivosXDocumentoID");
    ax.addParamTmp("documentoId", docId);
    ax.addParamTmp("lstDocumento", lstDocumentoArchivos);
    ax.addParamTmp("lstDocEliminado", lstDocEliminado);
    ax.consumir();
}

var dataVisualizarDocumento;
var datalistaComprobacion;
/**
 * @param data          Trae la data Para visualizacion del documento.
 * @param data.dataDocumento   Info.
 * @param data.configuracionEditable Info.
 * @param data.comentarioDocumento Info.
 * @param data.detalleDocumento Info.
 * @param data.dataMovimientoTipoColumna Info.
 * @param data.dataAccionEnvio Info.
 */
function onResponseObtenerDocumentoRelacionVisualizar(data, banderaDesactivarAccion = 0)
{
    resultadoObtenerEmails = null;
    dataVisualizarDocumento = data;
    $('[data-toggle="popover"]').popover('hide');
    cargarDataDocumento(data.dataDocumento, data.configuracionEditable, data.dataDocumentoAdjunto);
    cargarDataComentarioDocumento(data.comentarioDocumento);
    datalistaComprobacion = data.listaComprobacion;
    $("#liDataArchivoAdjuntos").hide();
    cargarDataArchivoAdjuntos(data.dataDocumentoAdjunto);

    if (!isEmpty(data.detalleDocumento)) {
        cargarDetalleDocumento(data.detalleDocumento, data.dataMovimientoTipoColumna);

    } else {
        $('#datatable2').hide();
    }

    if (!isEmpty(data.listaComprobacion)) {
        cargarDataListaComprobacionDocumento(datalistaComprobacion);
    }

    $('#liDataHistorial').hide();    
    if (!isEmpty(data.historialDocumento)) {
        $('#liDataHistorial').show();
        cargarHistorialDocumento(data.historialDocumento);
    } else {
        $('#theadHistorial').empty();
        $('#tbodyHistorial').empty();
    }
    
    $('#liDataDistribucion').hide();
    if (!isEmpty(data.dataDistribucionContable)) {
        $('#liDataDistribucion').show();
        cargarDistribucionDocumento(data.dataDistribucionContable);
    } else {
        $('#theadDetalleCabeceraDistribucion').empty();
        $('#tbodyDetalleDistribucion').empty();
    }
    
    $('#liDataVoucher').hide();
    if (!isEmpty(data.dataVoucherContable)) {
        $('#liDataVoucher').show();
        cargaVoucherContable(data.dataVoucherContable);
    } else {
        $('#theadDetalleCabeceraVocuher').empty();
        $('#tbodyDetalleVocuher').empty();
    }

    $('#linkDocumentoRelacionadoVisualizar').empty();
    if (!isEmptyData(data.dataDocumentoRelacion)) {
        let htmlDocumentoRelacion = '';

        let dataDocumentosRelacionados = data.dataDocumentoRelacion.filter(itemFiltrado => itemFiltrado.es_ear == 0);
        let dataDocumentosRelacionadosEAR = data.dataDocumentoRelacion.filter(itemFiltrado => itemFiltrado.es_ear == 1);
        if (!isEmpty(dataDocumentosRelacionados)) {

            htmlDocumentoRelacion += "<label style='color:#0000FF'>Documentos relacionados desde el sistema</label><br>";
            $.each(dataDocumentosRelacionados, function (index, item) {
                let accionRelacion = '';
                if (banderaDesactivarAccion == 0) {
                    accionRelacion = "visualizarDocumentoRelacion(" + item.documento_relacionado_id + "," + item.movimiento_id + ")";
                }
                htmlDocumentoRelacion += "<a onclick='" + accionRelacion + "' style='color:#0000FF'>[" + item.documento_tipo + ": " + item.serie_numero + "]";
                if (item.identificador_negocio == 27) {
                    htmlDocumentoRelacion += (!isEmpty(item.comentario) ? "&nbsp;&nbsp;&nbsp;&nbsp;" + item.comentario : "");
                }
                htmlDocumentoRelacion += "</a>";
                if (!(item.identificador_negocio_documento == 1 && item.identificador_negocio == 27) && !isEmpty(item.relacion_id) && banderaDesactivarAccion == 0) {
                    htmlDocumentoRelacion += '<a onclick="confirmarEliminarDocumentoARelacionar(' + item.documento_relacionado_id + ',\'' + item.documento_tipo + '\',\'' + item.serie_numero + '\')"> &nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-trash-o" style = "color:#cb2a2a;" title="Eliminar relación"></i></a>';
                }
                htmlDocumentoRelacion += '&nbsp;&nbsp;<a onclick="imprimirDocumento(' + item.documento_relacionado_id + ',' + item.documento_tipo_id + ')" title="Imprimir"><b><i class="fa fa-print" style="color:#088A08"></i></b></a>';
                htmlDocumentoRelacion += "<br>";

            });
        }
        if (!isEmpty(dataDocumentosRelacionadosEAR)) {
            htmlDocumentoRelacion += "<label style='color:#0000FF'>Documentos EAR</label><br>";
            $.each(dataDocumentosRelacionadosEAR, function (index, item) {
                let accionRelacion = '';
                if (banderaDesactivarAccion == 0) {
                    accionRelacion = "visualizarDocumentoRelacion(" + item.documento_relacionado_id + "," + item.movimiento_id + ")";
                }
                htmlDocumentoRelacion += "<a onclick='" + accionRelacion + "' style='color:#0000FF'>[" + item.documento_tipo + ": " + item.serie_numero + "]</a><br>";
                htmlDocumentoRelacion += '&nbsp;&nbsp;<a onclick="imprimirDocumento(' + item.documento_relacionado_id + ',' + item.documento_tipo_id + ')" title="Imprimir"><b><i class="fa fa-print" style="color:#088A08"></i></b></a>';

            });
        }
        $('#linkDocumentoRelacionadoVisualizar').append(htmlDocumentoRelacion);
    }

    //#region Partidas
     $("#liDataPartida").hide();
    // let contenidoArchivoJson = null;
    // if (!isEmpty(data.dataDocumentoAdjunto)) {
    //     $.each(data.dataDocumentoAdjunto, function (index, item) {
    //         if (!isEmpty(item.contenido_archivo)) {
    //             contenidoArchivoJson = item.contenido_archivo;
    //         }
    //     });
    // }

    // let contenidoArchivo = JSON.parse(contenidoArchivoJson);
    // if (!isEmpty(contenidoArchivo)) {
    //     $("#liDataPartida").show();
    //     $("#divPresupuestoIdModal").html("<b>Presupuesto :&nbsp;&nbsp;</b>" + contenidoArchivo.presupuesto.codigo + " | " + contenidoArchivo.presupuesto.descripcion);
    //     $("#divSubPresupuestoIdModal").html("<b>Subpresupuesto :&nbsp;&nbsp;</b>" + contenidoArchivo.subpresupuesto.codigo + " | " + contenidoArchivo.subpresupuesto.descripcion);
    //     $("#divClienteIdModal").html("<b>Cliente :&nbsp;&nbsp;</b>" + contenidoArchivo.cliente);
    //     $("#divFechaIdModal").html("<b>Costo al :&nbsp;&nbsp;</b>" + contenidoArchivo.fecha_costo);
    //     $("#divLugarIdModal").html("<b>Lugar :&nbsp;&nbsp;</b>" + contenidoArchivo.lugar);
    //     let dataPartidas = contenidoArchivo.partidas;
    //     if (!isEmpty(dataPartidas)) {
    //         let tablaBodyHtml = "";
    //         $.each(dataPartidas, function (index, item) {
    //             let labelBInicio = "<b>";
    //             let labelBFin = "</b>";
    //             if (item.es_padre != 1) {
    //                 labelBInicio = "";
    //                 labelBFin = "";
    //             }
    //             let metrado = (!isEmpty(item.metrado) ? formatearNumeroPorCantidadDecimales(item.metrado, 2) : "");
    //             let precio = (!isEmpty(item.precio) ? formatearNumeroPorCantidadDecimales(item.precio, 2) : "");
    //             let parcial = (!isEmpty(item.parcial) ? formatearNumeroPorCantidadDecimales(item.parcial, 2) : "");

    //             tablaBodyHtml += "<tr>"
    //                     + "<td style='text-align:left;'>" + labelBInicio + item.codigo + labelBFin + "</td>"
    //                     + "<td style='text-align:left;'>" + labelBInicio + item.descripcion + labelBFin + "</td>"
    //                     + "<td style='text-align:center;'>" + item.unidad_medida + "</td>"
    //                     + "<td style='text-align:right;'>" + metrado + "</td>"
    //                     + "<td style='text-align:right;'>" + precio + "</td>"
    //                     + "<td style='text-align:right;'>" + parcial + "</td>"
    //                     + "<tr>";
    //         });
    //         $("#dataTablePartidasModal tbody").html(tablaBodyHtml);
    //     }
    //     let dataTotalizados = contenidoArchivo.totalizados;
    //     if (!isEmpty(dataTotalizados)) {
    //         let tablaFootHtml = '';
    //         if (!isEmpty(dataTotalizados.costo_directo)) {
    //             tablaFootHtml += "<tr>"
    //                     + "<td style='text-align:right;' colspan='5'><b>" + dataTotalizados.costo_directo.nombre + "</b></td>"
    //                     + "<td style='text-align:right;'><b>" + formatearNumeroPorCantidadDecimales(dataTotalizados.costo_directo.monto, 2) + "</b></td>"
    //                     + "<tr>";
    //         }

    //         if (!isEmpty(dataTotalizados.adicionales)) {
    //             $.each(dataTotalizados.adicionales, function (index, item) {
    //                 tablaFootHtml += "<tr>"
    //                         + "<td style='text-align:right;' colspan='5'><b>" + item.nombre + "</b></td>"
    //                         + "<td style='text-align:right;'><b>" + formatearNumeroPorCantidadDecimales(item.monto, 2) + "</b></td>"
    //                         + "<tr>";
    //             });
    //         }

    //         if (!isEmpty(dataTotalizados.subtotal)) {
    //             tablaFootHtml += "<tr>"
    //                     + "<td style='text-align:right;' colspan='5'><b>" + dataTotalizados.subtotal.nombre + "</b></td>"
    //                     + "<td style='text-align:right;'><b>" + formatearNumeroPorCantidadDecimales(dataTotalizados.subtotal.monto, 2) + "</b></td>"
    //                     + "<tr>";
    //         }

    //         if (!isEmpty(dataTotalizados.igv)) {
    //             tablaFootHtml += "<tr>"
    //                     + "<td style='text-align:right;' colspan='5'><b>" + dataTotalizados.igv.nombre + "</b></td>"
    //                     + "<td style='text-align:right;'><b>" + formatearNumeroPorCantidadDecimales(dataTotalizados.igv.monto, 2) + "</b></td>"
    //                     + "<tr>";
    //         }

    //         if (!isEmpty(dataTotalizados.total)) {
    //             tablaFootHtml += "<tr>"
    //                     + "<td style='text-align:right;' colspan='5'><b>" + dataTotalizados.total.nombre + "</b></td>"
    //                     + "<td style='text-align:right;'><b>" + formatearNumeroPorCantidadDecimales(dataTotalizados.total.monto, 2) + "</b></td>"
    //                     + "<tr>";
    //         }
    //         $("#dataTablePartidasModal tfoot").html(tablaFootHtml);
    //     }
    // }
    //#endregion Partidas


    dibujarTipoEnvioEmail(data.dataAccionEnvio);
    //Valores fijos de busquedad
    if (dataConfiguracionInicial.movimientoTipo[0]['id'] == 141) {
        datosEstaticosBusquedadACopiar.persona_id = data.dataDocumentoGeneral[0]['persona_id'];
        datosEstaticosBusquedadACopiar.estado_id = 1;
    }

    $("#btnAgregarRelacionDocumentoModal").show();
    $("#btnGuardarEdicionModal").show();
    if (banderaDesactivarAccion == 1) {
        $("#btnAgregarRelacionDocumentoModal").hide();
        $("#btnGuardarEdicionModal").hide();
    }

    setTimeout(function () {
        $('a[href="#' + preVisualizarTab + '"]').click();

        preVisualizarTab = "dataGeneral";
        $('#modalDetalleDocumento').modal('show');
    }, 200);

}

var lstDocumentoArchivos = [];
var lstDocEliminado = [];
var cont = 0;
var ordenEdicion = 0;
function eliminarDocumento(docId) {
    ordenEdicion = 0;
    lstDocumentoArchivos.some(function (item) {
        if (item.id == docId) {
            lstDocumentoArchivos.splice(ordenEdicion, 1);
            lstDocEliminado.push([{id: docId, archivo: item.archivo}])
            onResponseListarArchivosDocumento(lstDocumentoArchivos);
            return item.id === docId;
        }
        ordenEdicion++;
    });
}

function onResponseObtenerDocumentoArchivosVisualizar(data)
{
    var titulo = "";
    lstDocEliminado = [];
    $("#msjDocumento").html("");
    $("#msjDocumento").hide();
    lstDocumentoArchivos = data.dataDocumentoAdjunto === null ? [] : data.dataDocumentoAdjunto;
    onResponseListarArchivosDocumento(lstDocumentoArchivos);

    if (!isEmpty(data.dataDocumento)) {
        $.each(data.dataDocumento, function (index, item) {
            if (item.tipo == 7) {
                titulo += item.valor + "-";
            } else if (item.tipo == 8) {
                titulo += item.valor;
            }
        });
    }
    $('#tituloVisualizarModalArchivos').html("Administrar archivos - " + data.dataDocumento[0]['nombre_documento'] + " " + titulo);
    $('#modalVisualizarArcvhivos').modal('show');
}


function fileIsLoaded(e) {
    $('#dataArchivo').val(e.target.result);
}

$("#archivoAdjunto").change(function () {
    $("#nombreArchivo").html($('#archivoAdjunto').val().slice(12));

    //llenado del popover
    $('#idPopover').attr("data-content", $('#archivoAdjunto').val().slice(12));
    $('[data-toggle="popover"]').popover('show');
    $('.popover-content').css('color', 'black');
    $('[class="popover fade top in"]').css('z-index', '0');

    if (isEmpty(this.files[0])) {
    } else if (this.files[0].size > 1000000) {
        $("#msjDocumento").text("El archivo no debe superar el tamaño: 1024 KB").show();
    } else if (this.files[0].size > 1) {
        $("#msjDocumento").hide();
        var reader = new FileReader();
        reader.onload = fileIsLoaded;
        reader.readAsDataURL(this.files[0]);
    }
});

$("#btnAgregarDoc").click(function fileIsLoaded(e) {
    var documento = {};
    if (!isEmpty($("#archivoAdjunto").val())) {
        if ($("#archivoAdjunto").val().slice(12).length > 0) {
            documento.data = $("#dataArchivo").val();
            documento.archivo = $("#archivoAdjunto").val().slice(12);
            documento.id = "t" + cont++;
            lstDocumentoArchivos.push(documento);
            onResponseListarArchivosDocumento(lstDocumentoArchivos);
            $("#archivoAdjunto").val("");
            $("#dataArchivo").val("");
            $('[data-toggle="popover"]').popover('hide');
            $("#msjDocumento").html("");
            $("#msjDocumento").hide();
        } else {
            onResponseListarArchivosDocumento(lstDocumentoArchivos);
        }

    } else {
        $("#msjDocumento").html("Debe adjuntar un archivo primero para agregarlo a la lista");
        $("#msjDocumento").show();
    }

});

function onResponseListarArchivosDocumento(data) {

    $("#dataList2").empty();
    var cuerpo_total = "";
    var cuerpo = "";
    var cabeza = "<table id='datatable3' class='table table-striped table-bordered'>"
            + "<thead>"
            + "<tr>"
            + "<th style='text-align:center; vertical-align: middle; width:20%'>#</th>"
            + "<th style='text-align:center; vertical-align: middle;'>Nombre</th>"
            + "<th style='text-align:center; vertical-align: middle; width:15%'>Acciones</th>"
            + "</tr>"
            + "</thead>";
    if (!isEmpty(data)) {

        $.each(data, function (index, item) {
            if (!item.id.match(/t/g)) {
                lstDocumentoArchivos[index]["data"] = "util/uploads/documentoAdjunto/" + item.nombre;
            }

            cuerpo = "<tr>"
                    + "<td style='text-align:center;'>" + (index + 1) + "</td>"
                    + "<td style='text-align:center;'>" + item.archivo + "</td>";

            cuerpo += "<td style='text-align:center;'>"
                    + "<a href='" + item.data + "' download='" + item.archivo + "' target='_blank'><i class='fa fa-cloud-download' style='color:#1ca8dd;'></i></a>&nbsp;\n"
                    + "<a href='#' onclick='eliminarDocumento(\"" + item.id + "\")'><i class='fa fa-trash-o' style='color:#cb2a2a;'></i></a>&nbsp;\n"
                    + "</td>"
                    + "</tr>";
            cuerpo_total += cuerpo;
        });
    }
    var pie = '</table>';
    var html = cabeza + cuerpo_total + pie;
    $("#dataList2").append(html);
    $("#datatable3").DataTable();
//    onResponseVacio('datatable3', [[0, "asc"]]);
}

function cargarDataComentarioDocumento(data) {
    $('#txtComentario').val(data[0]['comentario_documemto']);
}

function appendFormDetalle(html) {
    $("#formularioDetalleDocumento").append(html);
}


var camposDinamicos = [];
var textoDireccionId = 0;
var personaDireccionId = 0;
var guardarEdicionDocumento = false;
/**
 * @param data          Trae la data del documento.
 * @param data.edicion_habilitar   Information about the object's members.
 * @param data.documento_tipo_dato_id Info.
 * @param data.item.documento_tipo_id Info.
 * @param data.numero_defecto Info.
 * @param data.codigo_identificacion Info.
 * @param data.descripcion_numero Info.
 * @param data.valor_id Info.
 * @param data.codigo Info.
 * @param data. Info.
 */
function cargarDataDocumento(data, configuracionEditable = [], dataDocumentoAdjunto)
{

    textoDireccionId = 0;
    personaDireccionId = 0;
    camposDinamicos = [];

    guardarEdicionDocumento = false;
    if (!isEmpty(configuracionEditable)) {
        guardarEdicionDocumento = true;
    }

//    if(!isEmpty(configuracionEditable)){
//        $("#botonEdicion").show();
//    }

    $("#formularioDetalleDocumento").empty();
    //$("#formularioDetalleDocumento").css("height", 75 * data.length);
    var contador = 0;

    if (!isEmpty(data)) {
//        $('#nombreDocumentoTipo').html(data[0]['nombre_documento']);
        $('#tituloVisualizacionModal').html(data[0]['nombre_documento']);
        // Mostraremos la data en filas de dos columnas
        $.each(data, function (index, item) {
            if (item.tipo != 31 && item.tipo != 32) {

                if (contador % 3 == 0) {
                    appendFormDetalle('<div class="row">');
                    appendFormDetalle('</div>');
                }
                contador++;


//            appendFormDetalle('<div class="row">');

                var html = '<div class="form-group col-md-4"><div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">' +
                        '<label>' + item.descripcion + '</label>' +
                        '</div><div class="col-lg-8 col-md-8 col-sm-6 col-xs-6">';

                var valor = '';
                if (item.edicion_habilitar == 0) {
                    valor = quitarNULL(item.valor);

                    if (!isEmpty(valor))
                    {
                        switch (parseInt(item.tipo)) {
                            case 1:
                                valor = formatearCantidad(valor);
                                break;
//                    case 2:
                            case 3:
                                valor = fechaArmada(valor);
                                break;
//                    case 4:
//                    case 5:
//                    case 6:
//                    case 7:
//                    case 8:
                            case 9:
                            case 10:
                            case 11:
                                valor = fechaArmada(valor);
                                break;
//                    case 12:
//                    case 13:
                            case 14:
                            case 15:
                            case 16:
                            case 19:
                            case 33:
                            case 34:
                            case 35:
                            case 38:   
                                valor = formatearNumero(valor);
                                break;
                            case 27:
                                if (!isEmpty(dataDocumentoAdjunto)) {
                                    valor = '<a style="color: blue;" href="util/uploads/documentoAdjunto/' + dataDocumentoAdjunto[0]['nombre'] + '" download="' + dataDocumentoAdjunto[0]['archivo'] + '">' + dataDocumentoAdjunto[0]['archivo'] + '</a>';
                                }
                                break;
                        }
                    }
                } else {
                    $.each(configuracionEditable, function (index, itemEditable) {
                        if (itemEditable.documento_tipo_dato_id == item.documento_tipo_id) {

                            camposDinamicos.push({
                                id: item.documento_tipo_id,
                                tipo: parseInt(itemEditable.tipo),
                                opcional: itemEditable.opcional,
                                descripcion: itemEditable.descripcion
                            });

                            var longitudMaxima = itemEditable.longitud;
                            if (isEmpty(longitudMaxima)) {
                                longitudMaxima = 45;
                            }

                            switch (parseInt(item.tipo)) {
                                case 1:
                                case 14:
                                case 15:
                                case 16:
                                case 19:
                                    valor += '<input type="number" id="txt_' + item.documento_tipo_id + '" name="txt_' + item.documento_tipo_id + '" class="form-control" value="" maxlength="45" style="text-align: right;" />';
                                    break;

                                case 7:
                                case 8:
                                    valor += '<input type="text" id="txt_' + item.documento_tipo_id + '" name="txt_' + item.documento_tipo_id + '" class="form-control" value="" maxlength="45" style="text-align: right;"/>';
                                    break;

                                case 2:
                                case 6:
                                case 12:
                                case 13:

                                    if (parseInt(itemEditable.numero_defecto) === 1) {
                                        textoDireccionId = itemEditable.documento_tipo_dato_id;
                                    }
                                    valor += '<input type="text" id="txt_' + item.documento_tipo_id + '" name="txt_' + item.documento_tipo_id + '" class="form-control" value="" maxlength="' + longitudMaxima + '"/>';
                                    break;
                                case 9:
                                case 3:
                                case 10:
                                case 11:
                                    valor += '<div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">' +
                                            '<input type="text" class="form-control fecha" placeholder="dd/mm/yyyy" id="datepicker_' + item.documento_tipo_id + '">' +
                                            '<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span></div>';
                                    break;
                                case 4:
                                case 40:
                                    if(!isEmpty(item.valor_id)){
                                        valor += '<select name="cbo_' + item.documento_tipo_id + '" id="cbo_' + item.documento_tipo_id + '" class="select2"></select>';
                                    }
                                    break;
                                case 5:
                                    valor += '<div id ="div_persona" ><select name="cbo_' + item.documento_tipo_id + '" id="cbo_' + item.documento_tipo_id + '" class="select2">';
                                    valor += '<option value="' + 0 + '">Seleccione ' + item.descripcion.toLowerCase() + '</option>';
                                    $.each(itemEditable.data, function (indexPersona, itemPersona) {
                                        valor += '<option value="' + itemPersona.id + '">' + itemPersona.nombre + ' | ' + itemPersona.codigo_identificacion + '</option>';
                                    });
                                    valor += '</select>';
                                    break;
                                case 17:
                                    valor += '<div id ="div_organizador_destino" ><select name="cbo_' + item.documento_tipo_id + '" id="cbo_' + item.documento_tipo_id + '" class="select2">';
                                    valor += '<option value="' + 0 + '">Seleccione organizador</option>';
                                    $.each(itemEditable.data, function (indexOrganizador, itemOrganizador) {
                                        valor += '<option value="' + itemOrganizador.id + '">' + itemOrganizador.descripcion + '</option>';
                                    });
                                    valor += '</select>';
                                    break;
                                case 18:
                                    personaDireccionId = item.documento_tipo_id;
                                    valor += '<div id ="div_direccion" ><select name="cbo_' + item.documento_tipo_id + '" id="cbo_' + item.documento_tipo_id + '" class="select2">';
                                    valor += '</select>';
                                    break;
                                case 20:
                                    valor += '<div id ="div_cuenta" ><select name="cbo_' + item.documento_tipo_id + '" id="cbo_' + item.documento_tipo_id + '" class="select2">';
                                    valor += '<option value="' + 0 + '">Seleccione la cuenta</option>';
                                    $.each(itemEditable.data, function (indexCuenta, itemCuenta) {
                                        valor += '<option value="' + itemCuenta.id + '">' + itemCuenta.descripcion_numero + '</option>';
                                    });
                                    valor += '</select>';
                                    break;
                                case 21:
                                    valor += '<div id ="div_actividad" ><select name="cbo_' + item.documento_tipo_id + '" id="cbo_' + item.documento_tipo_id + '" class="select2">';
                                    valor += '<option value="' + 0 + '">Seleccione la actividad</option>';
                                    $.each(itemEditable.data, function (indexActividad, itemActividad) {
                                        valor += '<option value="' + itemActividad.id + '">' + itemActividad.codigo + ' | ' + itemActividad.descripcion + '</option>';
                                    });
                                    valor += '</select>';
                                    break;
                                case 22:
                                    valor += '<div id ="div_retencion_detraccion" ><select name="cbo_' + item.documento_tipo_id + '" id="cbo_' + item.documento_tipo_id + '" class="select2">';
                                    valor += '<option value="' + 0 + '">Seleccione retención o detracción</option>';
                                    $.each(itemEditable.data, function (indexRetencionDetraccion, itemRetencionDetraccion) {
                                        valor += '<option value="' + itemRetencionDetraccion.id + '">' + itemRetencionDetraccion.descripcion + '</option>';
                                    });
                                    valor += '</select>';
                                    break;
                                case 23:
                                    valor += '<div id ="div_persona_' + item.documento_tipo_id + '" ><select name="cbo_' + item.documento_tipo_id + '" id="cbo_' + item.documento_tipo_id + '" class="select2">';
                                    valor += '<option value="' + 0 + '">Seleccione ' + item.descripcion.toLowerCase() + '</option>';
                                    $.each(itemEditable.data, function (indexPersona, itemPersona) {
                                        valor += '<option value="' + itemPersona.id + '">' + itemPersona.nombre + ' | ' + itemPersona.codigo_identificacion + '</option>';
                                    });
                                    valor += '</select>';
                                    break;
                                case 26:
                                    valor += '<div id ="div_persona_' + item.documento_tipo_id + '" ><select name="cbo_' + item.documento_tipo_id + '" id="cbo_' + item.documento_tipo_id + '" class="select2">';
                                    valor += '<option value="' + 0 + '">Seleccione ' + item.descripcion.toLowerCase() + '</option>';
                                    $.each(itemEditable.data, function (indexPersona, itemPersona) {
                                        valor += '<option value="' + itemPersona.id + '">' + itemPersona.nombre + ' | ' + itemPersona.codigo_identificacion + '</option>';
                                    });
                                    valor += '</select>';
                                    break;
                                case 27:
                                    if (!isEmpty(dataDocumentoAdjunto)) {
                                        valor = '<a style="color: blue;" href="util/uploads/documentoAdjunto/' + dataDocumentoAdjunto[0]['nombre'] + '" download="' + dataDocumentoAdjunto[0]['archivo'] + '">' + dataDocumentoAdjunto[0]['archivo'] + '</a>';
                                    }
                                    break;
                            }
                        }
                    });
                }

                html += '' + valor + '';
                html += '</div></div>';
                appendFormDetalle(html);

                if (item.edicion_habilitar == 1) {
                    $.each(configuracionEditable, function (index, itemEditable) {
                        if (itemEditable.documento_tipo_dato_id == item.documento_tipo_id) {
                            switch (parseInt(item.tipo)) {
                                case 3:
                                case 9:
                                case 10:
                                case 11:
                                    $('#datepicker_' + item.documento_tipo_id).datepicker({
                                        isRTL: false,
                                        format: 'dd/mm/yyyy',
                                        autoclose: true,
                                        language: 'es'
                                    });

                                    if (isEmpty(itemEditable.valor_id)) {
                                        $('#datepicker_' + item.documento_tipo_id).datepicker('setDate', itemEditable.data);
                                    } else {
                                        $('#datepicker_' + item.documento_tipo_id).datepicker('setDate', formatearFechaJS(itemEditable.valor_id));
                                    }


                                    break;
                                case 4:
                                    if(!isEmpty(item.valor_id)){
                                        select2.cargar("cbo_" + item.documento_tipo_id, itemEditable.data, "id", "descripcion");
                                        $("#cbo_" + item.documento_tipo_id).select2({
                                            width: '100%'
                                        });
                                        select2.asignarValor("cbo_" + item.documento_tipo_id, itemEditable.valor_id);
                                    }
                                    break;
                                case 5:
                                    $("#cbo_" + item.documento_tipo_id).select2({
                                        width: '100%'
                                    }).on("change", function (e) {
                                        obtenerPersonaDireccion(e.val);
                                        obtenerPersonaContacto(e.val);
//                                    obtenerBienesConStockMenorACantidadMinima(e.val);
                                    });

                                    select2.asignarValor("cbo_" + item.documento_tipo_id, itemEditable.valor_id);
                                    break;
                                case 17:
                                    $("#cbo_" + item.documento_tipo_id).select2({
                                        width: '100%'
                                    });

                                    select2.asignarValor("cbo_" + item.documento_tipo_id, itemEditable.valor_id);
                                    break;
                                case 18:
                                    $("#cbo_" + item.documento_tipo_id).select2({
                                        width: '100%'
                                    });

                                    select2.asignarValor("cbo_" + item.documento_tipo_id, itemEditable.valor_id);
                                    break;
                                case 20:
                                case 21:
                                    $("#cbo_" + item.documento_tipo_id).select2({
                                        width: '100%'
                                    });

                                    select2.asignarValor("cbo_" + item.documento_tipo_id, itemEditable.valor_id);
                                    break;
                                case 22:
                                    $("#cbo_" + item.documento_tipo_id).select2({
                                        width: '100%'
                                    });

                                    select2.asignarValor("cbo_" + item.documento_tipo_id, itemEditable.valor_id);
                                    break;
                                case 23:
                                    $("#cbo_" + item.documento_tipo_id).select2({
                                        width: '100%'
                                    });

                                    select2.asignarValor("cbo_" + item.documento_tipo_id, itemEditable.valor_id);
                                    break;
                                case 26:
                                    $("#cbo_" + item.documento_tipo_id).select2({
                                        width: '100%'
                                    });

                                    select2.asignarValor("cbo_" + item.documento_tipo_id, itemEditable.valor_id);
                                    break;
                                    //input numero    
                                case 1:
                                case 14:
                                case 15:
                                case 16:
                                case 19:
                                    $('#txt_' + item.documento_tipo_id).val(formatearNumero(itemEditable.valor_id));
                                    break;

                                    //input texto
                                case 7:
                                case 8:
                                case 2:
                                case 6:
                                case 12:
                                case 13:
                                    $('#txt_' + item.documento_tipo_id).val(itemEditable.valor_id);
                                    break;
                                case 40:
                                case 50:
                                    select2.cargar("cbo_" + item.documento_tipo_id, itemEditable.data, "id", "descripcion");
                                    $("#cbo_" + item.documento_tipo_id).select2({
                                            width: '100%'
                                    });
                                    select2.asignarValor("cbo_" + item.documento_tipo_id, itemEditable.valor_id);
                                    break;
                            }
                        }
                    });

                }
            }
        });
        appendFormDetalle('</div>');
}
}

function obtenerPersonaDireccion(personaId) {
//    alert(personaId);    
    if (personaDireccionId !== 0 || textoDireccionId !== 0) {
        ax.setAccion("obtenerPersonaDireccion");
        ax.addParamTmp("personaId", personaId);
        ax.consumir();
    }
}
function obtenerPersonaContacto(personaId) {
    //alert(personaId);
    if (personaContactoResponsableId !== 0 || personaContactoResponsableId !== 0) {
        ax.setAccion("obtenerPersonaContacto");
        ax.addParamTmp("personaId", personaId);
        ax.consumir();
    }

}

function existeColumnaCodigo(codigo) {
    var dataColumna = movimientoTipoColumna;

    var existe = false;
    if (!isEmpty(dataColumna)) {
        $.each(dataColumna, function (index, item) {
            if (parseInt(item.codigo) === parseInt(codigo)) {
                existe = true;
                return false;
            }
        });
    }

    return existe;
}

/**
 *
 * @param data
 * @param dataMovimientoTipoColumna
 * @param data.unidadMedida
 */

var movimientoTipoColumna;
function cargarDetalleDocumento(data, dataMovimientoTipoColumna) {
//    $('#datatable4').hide();
    $('#datatable2').show();
    $('#formularioDetalleDocumento').show();
    $('#lblComentario').show();
    $('#checklistInsertado').show();
    //  $('#lista_comprobacion').show();

    //$('#modalDetalleDocumento').show();

    movimientoTipoColumna = dataMovimientoTipoColumna;

    if (!isEmptyData(data))
    {
        $.each(data, function (index, item) {
            data[index]["cantidad"] = formatearCantidad(data[index]["cantidad"]);
            data[index]["precioUnitario"] = formatearNumero(data[index]["precioUnitario"]);
            data[index]["importe"] = formatearNumero(data[index]["importe"]);
        });

        //CABECERA DETALLE
        var tHeadDetalle = $('#theadDetalle');
        tHeadDetalle.empty();

        var html = '';
        html += "<tr>";
        var rowspan = '';
        if (dataVisualizarDocumento.dataDocumentoGeneral[0]['documento_tipo_id'] == GENERAR_COTIZACION) {
            rowspan = "rowspan='2'";
        }
//        if(existeColumnaCodigo(15)){
        if (!isEmpty(dataVisualizarDocumento.organizador)) {
            html += "<th style='text-align:center;'"+ rowspan +">Organizador</th>";
        }
        if (existeColumnaCodigo(33)) {
            html += "<th style='text-align:center;'>Cantidad compras</th>";
            html += "<th style='text-align:center;'>Cantidad en reserva</th>";
        }else{
            html += "<th style='text-align:center;' "+ rowspan +">Cantidad</th>";
        }
        if (existeColumnaCodigo(34)) {
            html += "<th style='text-align:center;'>Cantidad solicitada</th>";
        } 
        html += "<th style='text-align:center;' "+ rowspan +">Unidad de medida</th>";
        html += "<th style='text-align:center;' "+ rowspan +">Producto</th> ";
        if (existeColumnaCodigo(5)) {
            html += "<th style='text-align:center;'>Precio Unitario</th>";
            html += "<th style='text-align:center;'>Total</th>";
        }
        if (existeColumnaCodigo(21) || existeColumnaCodigo(22)) {
            html += "<th style='text-align:center;'>Comentario</th>";
        }
        if (existeColumnaCodigo(26)) {
            html += "<th style='text-align:center;'>CeCo</th>";
        }
        if (existeColumnaCodigo(33)) {
            html += "<th style='text-align:center;'>Compras</th>";
        }          
        if (existeColumnaCodigo(36)) {
            html += "<th style='text-align:center;'>Adjunto</th>";
            html += "<th style='text-align:center;'>Reserva</th>";
        }    
        if (dataVisualizarDocumento.dataDocumentoGeneral[0]['documento_tipo_id'] == GENERAR_COTIZACION) {
            dataVisualizarDocumento.dataPostores.forEach(function (proveedorID, idx) {
                var textoOriginal = (proveedorID.persona).split("|");
                var textProveedor = textoOriginal[1].length > 30 
                    ? textoOriginal[1].substring(0, 30) + '…' 
                    : textoOriginal[1];
                html += "<th style='text-align:center;font-size:10px' colspan='2'>"+ textoOriginal[0] + "<br>" + textProveedor +"</th>";

            });
        }            
        html += "</tr>";
        if (dataVisualizarDocumento.dataDocumentoGeneral[0]['documento_tipo_id'] == GENERAR_COTIZACION) {
            html += "<tr>";
            dataVisualizarDocumento.dataPostores.forEach(function (proveedorID, idx) {
                html += "<th style='text-align:center;' >Precio</th>"+
                "<th style='text-align:center;' >Sub. Total</th>";
            });
            html += "</tr>";
        }
        
        tHeadDetalle.append(html);

        //CUERPO DETALLE
        var tBodyDetalle = $('#tbodyDetalle');
        tBodyDetalle.empty();

        var totalPostores = [];
        html = '';
        $.each(data, function (index, item) {
            html += "<tr>";
//            if(existeColumnaCodigo(15)){
            if (!isEmpty(dataVisualizarDocumento.organizador)) {
                html += "<td>" + item.organizador + "</td>";
            }
            html += "<td style='text-align:right;'>" + item.cantidad + "</td>";
            if (existeColumnaCodigo(34)) {
                html += "<td style='text-align:right;'>" + redondearNumerDecimales((item.cantidad_solicitada - item.cantidad), 2) + "</td>";
                html += "<td style='text-align:right;'>" + redondearNumerDecimales(item.cantidad_solicitada, 2) + "</td>";
            }  
            html += "<td>" + item.unidadMedida + "</td>";
            html += "<td>" + item.bien_codigo + " | " + item.descripcion + "</td> ";
            if (existeColumnaCodigo(5)) {
                html += "<td style='text-align:right;'>" + item.precioUnitario + "</td>";
                html += "<td style='text-align:right;'>" + item.importe + "</td>";
            }
            if (existeColumnaCodigo(21) || existeColumnaCodigo(22)) {
                html += "<td style='text-align:left;'>" + item.movimientoBienComentario + "</td>";
            }
            if (existeColumnaCodigo(26)) {
                html += "<td style='text-align:left;'>" + item.centro_costo_descripcion + "</td>";
            }
            if (existeColumnaCodigo(33)) {
                var esCompra = item.es_compra == 1? "Si":"No";
                html += "<td style='text-align:center;'>" + esCompra + "</td>";
            }            
            if (existeColumnaCodigo(36)) {
                var adjuntoBien = item.movimiento_bien_detalle;
                var btn_upload = "";
                if(!isEmpty(adjuntoBien)){
                    btn_upload = "&nbsp;<a href='#' onclick='verImagenPdf("+ index +")'><i class='fa fa-cloud-download' style='color:blue;' title='"+ adjuntoBien[0].valor_detalle +"'></i></a> <input type='hidden' id='nombreAdjunto_"+ index +"' name='nombreAdjunto_"+ index +"' value='"+ adjuntoBien[0].valor_detalle +"'/>";
                }
                html += "<td style='text-align:center;'>" + btn_upload + "</td>";
                if(!isEmpty(item.estadoReserva)){
                    html += "<td style='text-align:center;'>Si</td>";
                }else{
                    html += "<td style='text-align:center;'>No</td>";
                }
            }

            if (dataVisualizarDocumento.dataDocumentoGeneral[0]['documento_tipo_id'] == GENERAR_COTIZACION) {
                dataVisualizarDocumento.dataPostores.forEach(function (proveedorID, idx) {
                    var total_ = 0;
                    $.each(item.movimiento_bien_detalle, function (indexBD, itemBD) {
                        if(itemBD.columna_codigo == 37){
                            if (proveedorID.persona_id == itemBD.valor_extra) {
                                var color_ganador = "";
                                if(item.postor_ganador_id == proveedorID.persona_id){
                                    color_ganador = "background-color:rgb(0, 254, 127);";
                                }
                                html += "<td style='text-align:center;"+ color_ganador +"'>" + devolverDosDecimales(itemBD.valor_detalle, 2) + "</td>";
                                html += "<td style='text-align:center;"+ color_ganador +"'>"+ devolverDosDecimales((item.cantidad * itemBD.valor_detalle), 2) +"</td>";
                                total_ = total_ + (item.cantidad * itemBD.valor_detalle);
                                totalPostores[idx] = (isEmpty(totalPostores[idx])?0:totalPostores[idx]) + total_;
                            }
                        }
                    });
                });
            }
            html += "</tr>";
        });

        tBodyDetalle.append(html);

        var tfootDetalle = $('#tfootDetalle');
        tfootDetalle.empty();


        var filaExtratfoot = '';
        if (dataVisualizarDocumento.dataDocumentoGeneral[0]['documento_tipo_id'] == GENERAR_COTIZACION) {
            $("#datatable2").css({ "overflow-x": "auto", "display": "block" });

            var textsubtotal = "";
            var textigv = "";
            var texttotal = "";
            var monedaText = "";
            var texttotalSoles = "";
            dataVisualizarDocumento.dataPostores.forEach((proveedor, idx) => {
                var monto = totalPostores[idx];
                var tipoCambio = proveedor.moneda_id == 4 ? proveedor.tipo_cambio : 1;
                var esSinIGV = proveedor.igv == 0;
                var subTotal = esSinIGV ? monto:monto / 1.18;
                var total = esSinIGV ? subTotal * 1.18:monto;
                var igv = total - subTotal;
                var totalSoles = total * tipoCambio;
            
                textsubtotal += `<th style='text-align:right' colspan='2'> ${devolverDosDecimales(subTotal, 2)}</th>`;
                textigv += `<th style='text-align:right' colspan='2'> ${devolverDosDecimales(igv, 2)}</th>`;
                texttotal += `<th style='text-align:right' colspan='2'> $${proveedor.moneda_id == 2 ? 0 : devolverDosDecimales(total, 2)}</th>`;
                texttotalSoles += `<th style='text-align:right' colspan='2'>S/ ${devolverDosDecimales(totalSoles, 2)}</th>`;
            });
            
            filaExtratfoot += '<tr >'+
                    '<th colspan="4" style="text-align:right">Sub Total dolares:</th>' + textsubtotal;
            filaExtratfoot += '</tr>';
            filaExtratfoot += '<tr>'+
                    '<th colspan="4" style="text-align:right">IGV (18%):</th>' + textigv;
            filaExtratfoot += '</tr>';
            filaExtratfoot += '<tr>'+                   
                    '<th colspan="4" style="text-align:right">Totales Dolares:</th>' + texttotal;
            filaExtratfoot += '</tr>';

            filaExtratfoot += '<tr>'+                   
            '<th colspan="4" style="text-align:right">Totales Soles:</th>' + texttotalSoles;
            filaExtratfoot += '</tr>';
            
            tfootDetalle.append(filaExtratfoot);
        }
    } else
    {
        var table = $('#datatable2').DataTable();
        table.clear().draw();
    }
}

function fechaArmada(valor)
{
    var fecha = separarFecha(valor);

    return fecha.dia + "/" + fecha.mes + "/" + fecha.anio;
}

function redondearNumerDecimales(monto, decimales) {
    if (isEmpty(decimales)) {
        decimales = 2;
    }
    return Math.round(monto * Math.pow(10, decimales)) / Math.pow(10, decimales);
}

function confirmarAnularMovimiento(id) {
    bandera_eliminar = false;
    swal({
        title: "Est\xe1s seguro?",
        text: "Anulara un documento, esta anulación no podra revertirse.",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#33b86c",
        confirmButtonText: "Si, anular!",
        cancelButtonColor: '#d33',
        cancelButtonText: "No, cancelar !",
        closeOnConfirm: true,
        closeOnCancel: false
    }, function (isConfirm) {

        if (isConfirm) {
            anular(id);
        } else {
            if (bandera_eliminar == false)
            {
                swal("Cancelado", "La anulaci\xf3n fue cancelada", "error");
            }
        }
    });
}

function buscarDocumentoTipoDatoPorId(tipo) {

    var objDocumentoTipoDato = null;
    if (!isEmpty(dataCofiguracionInicial) && !isEmpty(dataCofiguracionInicial.documento_tipo_conf)) {
        $.each(dataCofiguracionInicial.documento_tipo_conf, function (indexConf, itemConf) {
            if (itemConf.tipo * 1 == tipo) {
                objDocumentoTipoDato = itemConf;
                return false;
            }
        });
    }
    return objDocumentoTipoDato;
}

function aprobar(documentoId)
{
    ax.setAccion("aprobar");
    ax.addParamTmp("id", documentoId);
    ax.consumir();
}

function confirmarAprobarMovimiento(id) {
    bandera_aprobar = false;
    swal({
        title: "Está seguro?",
        text: "Aprobar un documento, esta aprobación no podra revertirse.",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#33b86c",
        confirmButtonText: "Si, aprobar!",
        cancelButtonColor: '#d33',
        cancelButtonText: "No, cancelar !",
        closeOnConfirm: false,
        closeOnCancel: false
    }, function (isConfirm) {

        if (isConfirm) {
            aprobar(id);
        } else {
            if (bandera_aprobar == false)
            {
                swal("Cancelado", "La aprobación fue cancelada", "error");
            }
        }
    });
}
function aprobarDocumento(id)
{
    confirmarAprobarMovimiento(id);
}

/*IMPORTAR EXCEL*/
$(function () {
    $("#file").change(function () {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = imageIsLoaded;
            reader.readAsDataURL(this.files[0]);
//            $fileupload = $('#file');
//            $fileupload.replaceWith($fileupload.clone(true));
        }
    });
});

function imageIsLoaded(e) {
    $('#secretFile').attr('value', e.target.result);
    enviarExcel();
}

function enviarExcel() {
    loaderShow();

    var documento = document.getElementById('secretFile').value;
    var docNombre = document.getElementById('file').value.slice(12);
    if (documento === '') {
        documento = null;
        docNombre = null;
    }

    //alert(documento);
    //alert(docNombre);
    importarExcelMovimiento(documento, docNombre);
}

function importarExcelMovimiento(documento, docNombre) {
    if (validarFormularioCarga(documento)) {
        //alert('Importar');
        ax.setAccion("importarExcelMovimiento");
        ax.addParamTmp("documento", documento);
        ax.addParamTmp("docNombre", docNombre);
        ax.consumir();
    } else {
        loaderClose();
    }
}

function validarFormularioCarga(documento) {
    var bandera = true;
    var espacio = /^\s+$/;

    if (documento === "" || documento === null || espacio.test(documento) || documento.length === 0) {
        $("#lblDoc").text("Documento es obligatorio").show();
        bandera = false;
    }
    return bandera;
}
/*FIN IMPORTAR EXCEL*/

var documentoIdOrigen;
function obtenerDocumentosRelacionados(documentoId)
{
    documentoIdOrigen = documentoId;

    loaderShow();
    ax.setAccion("obtenerDocumentosRelacionados");
    ax.addParamTmp("documento_id", documentoId);
    ax.consumir();
}

/**
 *
 * @param data
 * @param data.documento_relacionado_id
 * @param data.movimiento_id
 */
function onResponseObtenerDocumentosRelacionados(data)
{
    $('#linkDocumentoRelacionado').empty();
    if (!isEmptyData(data))
    {
        $('[data-toggle="popover"]').popover('hide');
        let dataDocumentosRelacionados = data.filter(itemFiltrado => itemFiltrado.es_ear == 0);
        let dataDocumentosRelacionadosEAR = data.filter(itemFiltrado => itemFiltrado.es_ear == 1);
        if (!isEmpty(dataDocumentosRelacionados)) {
            $('#linkDocumentoRelacionado').append("<label style='color:#0000FF'>Documentos relacionados desde el sistema</label><br>");
            $.each(dataDocumentosRelacionados, function (index, item) {
                $('#linkDocumentoRelacionado').append("<a onclick='visualizarDocumentoRelacion(" + item.documento_relacionado_id + "," + item.movimiento_id + ")' style='color:#0000FF'>[" + item.documento_tipo + ": " + item.serie_numero + "]</a>");
                if(item.documento_tipo_id == COTIZACION_SERVICIO){
                    $('#linkDocumentoRelacionado').append('&nbsp;&nbsp;<a onclick="imprimirDocumentoAdjunto(' + item.documento_relacionado_id + ',' + item.documento_tipo_id + ')" title="Imprimir"><b><i class="fa fa-print" style="color:#088A08"></i></b></a><br>');
                }else{
                    $('#linkDocumentoRelacionado').append('&nbsp;&nbsp;<a onclick="imprimirDocumento(' + item.documento_relacionado_id + ',' + item.documento_tipo_id + ')" title="Imprimir"><b><i class="fa fa-print" style="color:#088A08"></i></b></a><br>');
                }
            });

        }

        if (!isEmpty(dataDocumentosRelacionadosEAR)) {
            $('#linkDocumentoRelacionado').append("<label style='color:#0000FF'>Documentos EAR</label><br>");
            $.each(dataDocumentosRelacionadosEAR, function (index, item) {
                $('#linkDocumentoRelacionado').append("<a onclick='visualizarDocumentoRelacion(" + item.documento_relacionado_id + "," + item.movimiento_id + ")' style='color:#0000FF'>[" + item.documento_tipo + ": " + item.serie_numero + "]</a>");
                $('#linkDocumentoRelacionado').append('&nbsp;&nbsp;<a onclick="imprimirDocumento(' + item.documento_relacionado_id + ',' + item.documento_tipo_id + ')" title="Imprimir"><b><i class="fa fa-print" style="color:#088A08"></i></b></a><br>');

            });
        }
    } else {
        mostrarAdvertencia("No se encontró ningun documento relacionado con el actual.");
    }
    $('#modalDocumentoRelacionado').modal('show');
}



function visualizarDocumentoRelacion(documentoId, movimientoId)
{
    $('#modalDetalleDocumento').modal('hide');
    $('#modalDocumentoRelacionado').modal('hide');
    visualizarDocumento(documentoId, movimientoId);

}

function enviarCorreoDetalleDocumento() {

    var correo = $('#txtCorreo').val();
    correo = fixCorreo(correo);
    $('#txtCorreo').val(correo);
    var arr = correo.split(";");
    var valid = true;
    var boo = false;
    for (var i = 0; i < arr.length - 1; i++)
    {
        boo = validarEmail(arr[i]);
        if (boo)
        {
            valid = valid && boo;
        } else {
            valid = valid && boo;
        }
    }


    if (!isEmpty(correo) && valid) {
        loaderShow('#modalDetalleDocumento');
        ax.setAccion("enviarCorreoDetalleDocumento");
        ax.addParamTmp("correo", correo);
        ax.addParamTmp("documentoId", docId);
        ax.addParamTmp("comentarioDocumento", $('#txtComentario').val());
        ax.consumir();

    } else
    {
        mostrarAdvertencia("Ingrese email válido.");
        return;
    }



}

function editarComentarioDocumento() {
    loaderShow('#modalDetalleDocumento');
    //Validar y obtener valores de los campos dinamicos

    if (guardarEdicionDocumento) {
        if (!obtenerValoresCamposDinamicos())
            return;
    }


    ax.setAccion("guardarEdicionDocumento");
//    ax.setAccion("editarComentarioDocumento");
    ax.addParamTmp("comentario", $('#txtComentario').val());
    ax.addParamTmp("documentoId", docId);
    ax.addParamTmp("camposDinamicos", camposDinamicos);
    ax.addParamTmp("documentoTipoId", docTipo);
    ax.consumir();
}

function exitoCrear(data) {
    if (!isEmpty(data)) {
        if (data[0]["vout_exito"] == 0) {
            $.Notification.autoHideNotify('warning', 'top right', 'Validación', data[0]["vout_mensaje"]);
        } else {
            $.Notification.autoHideNotify('success', 'top-right', 'Éxito', data[0]["vout_mensaje"]);
            //        cargarPantallaListar();
        }
    } else {
        $.Notification.autoHideNotify('info', 'top-right', 'Validación', "No hay cambios para actualizar los datos.");
    }

}

function validarCorreo() {
    //$('#txtCorreo').val('');
    var correo = $('#txtCorreo').val();
    var pattern = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/igm;


    if (!isEmpty(correo) && pattern.test(correo)) {
        loaderShow('#modalDetalleDocumento');
        ax.setAccion("enviarMovimientoEmailPDF");
        ax.addParamTmp("correo", correo);
        ax.addParamTmp("documentoId", docId);
        ax.addParamTmp("comentario", $('#txtComentario').val());
        ax.consumir();
    } else {
        mostrarAdvertencia("Ingrese email");
        return;
    }

}

function validarCorreoMasPDF() {
    var correo = $('#txtCorreo').val();
    correo = fixCorreo(correo);
    $('#txtCorreo').val(correo);
    var arr = correo.split(";");
    var valid = true;
    var boo = false;
    for (var i = 0; i < arr.length - 1; i++)
    {
        boo = validarEmail(arr[i]);
        if (boo)
        {
            valid = valid && boo;
        } else {
            valid = valid && boo;
        }
    }

    if (!isEmpty(correo) && valid) {
        loaderShow('#modalDetalleDocumento');
        ax.setAccion("enviarMovimientoEmailCorreoMasPDF");
        ax.addParamTmp("correo", correo);
        ax.addParamTmp("documentoId", docId);
        ax.addParamTmp("comentario", $('#txtComentario').val());
        ax.consumir();
    } else {
        mostrarAdvertencia("Ingrese email");
        return;

    }

}


$('.dropdown-menu').click(function (e) {
    if (e.target.id != "btnBusqueda" && e.delegateTarget.id != "ulBuscadorDesplegable2" && e.delegateTarget.id != "ulBuscadorDesplegableCopia2" && e.delegateTarget.id != "listaEmpresa" && e.delegateTarget.id != "ulObtenerEmail") {
        e.stopPropagation();
    }
});

var contadorClick = 1;
$('#txtBuscar').keyup(function (e) {
    var bAbierto = $('#txtBuscar').attr("aria-expanded");

    if (!eval(bAbierto)) {
        $('#txtBuscar').dropdown('toggle');
    }

});


function iniciarDataPicker()
{
    $('.fecha').datepicker({
        isRTL: false,
        format: 'dd/mm/yyyy',
        autoclose: true,
        language: 'es'
    });
}
/**
 *
 * @param data
 * @param data.documento_tipo
 * @param data.personasMayorMovimientos
 * @param data.persona_activa
 * @param data.moneda
 */
var docTipo;
function onResponseObtenerDocumentoTipoDesplegable(data) {
    if (!isEmpty(data.documento_tipo)) {
        docTipo = data.documento_tipo[0]['id'];
        dibujarTiposDocumentos(data.documento_tipo);
        dibujarPersonasMayorMovimiento(data.personasMayorMovimientos);

        select2.cargar("cboDocumentoTipo", data.documento_tipo, "id", "descripcion");
        select2.cargar("cboPersona", data.persona_activa, "id", ["nombre", "codigo_identificacion"]);
        select2.cargar("cboMoneda", data.moneda, "id", ["descripcion", "simbolo"]);
        select2.cargar("cboEstadoNegocio", data.estadoNegocioPago, "codigo", ["codigo", "descripcion"]);
        select2.cargar("cboAgencia", data.dataAgencia, "id", ["codigo", "descripcion", "provincia"]);
    }

    if (data.documento_tipo[0]['id'] === '190') {
        $('#liNumeroOrdenCompra').show();
    } else if (data.documento_tipo[0]['id'] === '269') {
        $('#liResponsable').show();
        $('#liProgreso').show();
        $('#liPrioridad').show();
        $('#lista_comprobacion').show();
        $('#lblListaComprobacion').show();
        select2.cargar("cboProgreso", data.progreso, "id", ["descripcion"]);
        select2.cargar("cboPrioridad", data.prioridad, "id", ["descripcion"]);
        select2.cargar("cboResponsable", data.responsable, "id", ["persona_nombre"]);
    } else if (data.documento_tipo[0]['id'] === '23') {
        $('#liAgencia').show();
    }

    if (data.documento_tipo[0]['id'] === '280') {
        $('#liArea').show();
        $('#liTipoRequerimiento').show();
        select2.cargar("cboArea", data.area, "id", ["descripcion"]);
        select2.cargar("cboTipoRequerimiento", data.tipo_requerimiento, "descripcion", ["descripcion"]);
        if(!isEmpty(dataConfiguracionInicial.getarea)){
            select2.asignarValor('cboArea', dataConfiguracionInicial.getarea);
            $("#cboArea").attr('disabled', 'disabled');
        }
    }
    if (data.documento_tipo[0]['id'] == COTIZACION_SERVICIO) {
        $('#liEstadoCotizacion').show();
        select2.asignarValor('cboEstadoCotizacion', ["3", "16"]);
    }
    fechasActuales();
}

//here
function buscarDesplegable()
{
    dataDocumentoTipoDato = [];

    var personaId = select2.obtenerValor('cboPersona');
    var tipoDocumentoIds = $('#cboDocumentoTipo').val();
    var serie = $('#txtSerie').val();
    var numero = $('#txtNumero').val();
    var serieCompra = $('#txtSerieOrden').val();
    var numeroCompra = $('#txtNumeroOrden').val();
    var proyecto = $('#txtProyecto').val();
    var fechaEmision = {inicio: $('#inicioFechaEmision').val(),
        fin: $('#finFechaEmision').val()};
    var monedaId = select2.obtenerValor('cboMoneda');
    var estadoNegocio = select2.obtenerValor('cboEstadoNegocio');
    var responsable = select2.obtenerValor('cboResponsable');
    var progreso = select2.obtenerValor('cboProgreso');
    var prioridad = select2.obtenerValor('cboPrioridad');
    var agencia = $('#cboAgencia').val();
    var area = select2.obtenerValor('cboArea');
    var requerimiento_tipo = select2.obtenerValor('cboTipoRequerimiento');
    var estado_cotizacion = $('#cboEstadoCotizacion').val();

    llenarParametrosBusqueda(personaId, tipoDocumentoIds, serie, numero, fechaEmision, monedaId, estadoNegocio, proyecto, serieCompra, numeroCompra, responsable, progreso, prioridad, agencia, area, requerimiento_tipo, estado_cotizacion);

}

function llenarParametrosBusqueda(personaId, tipoDocumentoIds, serie, numero, fechaEmision, monedaId, estadoNegocio, proyecto, serieCompra, numeroCompra, responsable, progreso, prioridad, agencia, area, requerimiento_tipo, estado_cotizacion) {
    dataDocumentoTipoDato = [];

    if (isEmpty(personaId) && isEmpty(tipoDocumentoIds) && isEmpty(serie) && isEmpty(numero) && isEmpty(fechaEmision)) {
        dataDocumentoTipoDato = [];
    } else {
        dataDocumentoTipoDato.push({descripcion: "Persona", tipo: "5", valor: personaId, tipoDocumento: tipoDocumentoIds, monedaId: monedaId, estadoNegocio: estadoNegocio,
            proyecto: proyecto, serieCompra: serieCompra, numeroCompra: numeroCompra, responsable: responsable, progreso: progreso, prioridad: prioridad, agencia: agencia, area: area, requerimiento_tipo: requerimiento_tipo, estado_cotizacion: estado_cotizacion});
        dataDocumentoTipoDato.push({descripcion: "Serie", tipo: "7", valor: serie});
        dataDocumentoTipoDato.push({descripcion: "Numero", tipo: "8", valor: numero});
        dataDocumentoTipoDato.push({descripcion: "Fecha de emision", tipo: "9", valor: fechaEmision});
    }

    loaderShow();
    getDataTable();
}

function cambiarAnchoBusquedaDesplegable() {
    var ancho = $("#divBuscador").width();
    $("#ulBuscadorDesplegable").width((ancho - 5) + "px");
    $("#ulBuscadorDesplegable2").width((ancho - 5) + "px");
}

function limpiarBuscadores() {
    $('#txtSerie').val('');
    $('#txtNumero').val('');
    $('#inicioFechaEmision').val('');
    $('#finFechaEmision').val('');
    $('#txtProyecto').val('');
    $('#txtSerieOrden').val('');
    $('#txtNumeroOrden').val('');

    select2.asignarValor('cboDocumentoTipo', -1);
    select2.asignarValor('cboPersona', -1);
    select2.asignarValor('cboEstadoNegocio', -1);
    select2.asignarValor('cboProgreso', -1);
    select2.asignarValor('cboPrioridad', -1);
    select2.asignarValor('cboResponsable', -1);
    select2.asignarValor('cboAgencia', -1);
}

//here
function buscarCriteriosBusqueda() {
//    loaderShow();
    ax.setAccion("buscarCriteriosBusqueda");
    ax.addParamTmp("busqueda", $('#txtBuscar').val());
    ax.consumir();
}
/**
 *
 * @param data
 * @param data.dataPersona
 * @param data.dataDocumentoTipo
 * @param data.dataSerieNumero
 * @param data.documento_tipo_descripcion
 */
function onResponseBuscarCriteriosBusqueda(data) {
    var dataPersona = data.dataPersona;
    var dataDocumentoTipo = data.dataDocumentoTipo;
    var dataSerieNumero = data.dataSerieNumero;

    var html = '';
    var ulBuscadorDesplegable2 = $('#ulBuscadorDesplegable2');
    ulBuscadorDesplegable2.empty();
    if (!isEmpty(dataPersona)) {
        $.each(dataPersona, function (index, item) {
            html += '<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">';
            html += '<a onclick="busquedaPorTexto(5,' + item.id + ',' + null + ')" >';
            html += '<span class="col-md-1"><i class="ion-person"></i></span>';
            html += '<span class="col-md-2">';
            html += '<label style="color: #141719;">' + item.codigo_identificacion + '</label>';
            html += '</span>';
            html += '<span class="col-md-9">';
            html += '<label style="color: #141719;">' + item.nombre + '</label>';
            html += '</span></a>';
            html += '</div>';

        });
    }

    if (!isEmpty(dataDocumentoTipo)) {
        $.each(dataDocumentoTipo, function (index, item) {
            html += '<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">';
            html += '<a onclick="busquedaPorTexto(5,' + null + ',' + item.id + ')" >';
            html += '<span class="col-md-1"><i class="fa fa-files-o"></i></span>';
            html += '<span class="col-md-11">';
            html += '<label style="color: #141719;">' + item.descripcion + '</label>';
            html += '</span></a>';
            html += '</div>';

        });
    }


    if (!isEmpty(dataSerieNumero)) {
        $.each(dataSerieNumero, function (index, item) {
            html += '<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">';
            html += '<a onclick="busquedaPorSerieNumero(\'' + item.serie + '\',\'' + item.numero + '\')" >';
            html += '<span class="col-md-1"><i class="ion-document"></i></span>';
            html += '<span class="col-md-2">';
            html += '<label style="color: #141719;">' + item.serie_numero + '</label>';
            html += '</span>';
            html += '<span class="col-md-9">';
            html += '<label style="color: #141719;">' + item.documento_tipo_descripcion + '</label>';
            html += '</span></a>';
            html += '</div>';

        });
    }

    ulBuscadorDesplegable2.append(html);


}

function busquedaPorTexto(tipo, texto, tipoDocumento) {

    var tipoDocumentoIds = [];
    if (!isEmpty(tipoDocumento)) {
        tipoDocumentoIds.push(tipoDocumento);
    }

    if (tipo == 5) {
        llenarParametrosBusqueda(texto, tipoDocumentoIds, null, null, null, null, null);
    }

}

function busquedaPorSerieNumero(serie, numero) {
    llenarParametrosBusqueda(null, null, serie, numero, null, null, null);
}

function dibujarTiposDocumentos(documentoTipo) {

    var html = '';
    html += '<a href="#" onclick="busquedaPorTexto(5,' + null + ',' + null + ')" class="list-group-item">';
    html += '<span class="fa fa-circle text-pink pull-right" style="color: #D8D8D8;"></span>Todos';
    html += '</a>';
    var divDocumentoTipos = $('#divDocumentoTipos');
    divDocumentoTipos.empty();
    $.each(documentoTipo, function (index, item) {
        html += '<a href="#" onclick="busquedaPorTexto(5,' + null + ',' + item.id + ')" class="list-group-item">';
        html += '<span class="' + item.icono + '"></span>' + item.descripcion;
        html += '</a>';
    });

    divDocumentoTipos.append(html);
}

/**
 *
 * @param personas
 * @param personas.veces
 */
function dibujarPersonasMayorMovimiento(personas) {
    var html = '';
    var divPersonasMayorMovimientos = $('#divPersonasMayorMovimientos');
    divPersonasMayorMovimientos.empty();
    if (!isEmpty(personas)) {
        $.each(personas, function (index, item) {
            html += '<a href="#" class="list-group-item" onclick="busquedaPorTexto(5,' + item.id + ',' + null + ')" >';
            html += '<span class="badge bg-info">' + item.veces + '</span>' + item.nombre;
            html += '</a>';
        });
    }

    divPersonasMayorMovimientos.append(html);
}

function guardarEdicion() {
    loaderShow('#modalDetalleDocumento');

    //Validar y obtener valores de los campos dinamicos
    if (!obtenerValoresCamposDinamicos())
        return;


    ax.setAccion("guardarEdicionDocumento");
    ax.addParamTmp("documentoId", docId);
    ax.addParamTmp("camposDinamicos", camposDinamicos);
    ax.addParamTmp("documentoTipoId", docTipo);
    ax.consumir();
}

function obtenerValoresCamposDinamicos() {
    var isOk = true;
    if (isEmpty(camposDinamicos))
        return false;
    $.each(camposDinamicos, function (index, item) {
        //string
        switch (item.tipo) {
            case 1:
            case 14:
            case 15:
            case 16:
            case 19:
                var numero = document.getElementById("txt_" + item.id).value;
                if (isEmpty(numero)) {
                    if (item.opcional == 0) {
                        mostrarValidacionLoaderClose("Debe ingresar " + item.descripcion);
                        isOk = false;
                        return false;
                    }
                } else {
                    if (!esNumero(numero)) {
                        mostrarValidacionLoaderClose("Debe ingresar un número válido para " + item.descripcion);
                        isOk = false;
                        return false;
                    }
                }
                camposDinamicos[index]["valor"] = numero;
                break;
            case 2:
            case 6:
            case 7:
            case 8:
            case 12:
            case 13:
                camposDinamicos[index]["valor"] = document.getElementById("txt_" + item.id).value;
                if (item.opcional == 0) {
                    //validamos
                    if (isEmpty(camposDinamicos[index]["valor"])) {
                        mostrarValidacionLoaderClose("Debe ingresar " + item.descripcion);
                        isOk = false;
                        return false;
                    }
                }
                break;
                //fechas
            case 3:
            case 9:
            case 10:
            case 11:
                camposDinamicos[index]["valor"] = document.getElementById("datepicker_" + item.id).value;
                if (item.opcional == 0) {
                    //validamos
                    if (isEmpty(camposDinamicos[index]["valor"])) {
                        mostrarValidacionLoaderClose("Debe ingresar " + item.descripcion);
                        isOk = false;
                        return false;
                    }
                }
                break;
            case 4:
            case 5:// persona
            case 18:// direccion persona
            case 20:// cuenta
            case 21:// actividad
            case 22:// retencion detraccion
            case 23:// otra persona
                camposDinamicos[index]["valor"] = select2.obtenerValor('cbo_' + item.id);
                if (item.opcional == 0) {
                    //validamos
                    if (isEmpty(camposDinamicos[index]["valor"]) ||
                            camposDinamicos[index]["valor"] == 0) {
                        mostrarValidacionLoaderClose("Debe ingresar " + item.descripcion);
                        isOk = false;
                        return false;
                    }
                }
                break;
            case 17:// organizador
                camposDinamicos[index]["valor"] = select2.obtenerValor('cbo_' + item.id);
                if (item.opcional == 0) {
                    //validamos
                    if (isEmpty(camposDinamicos[index]["valor"]) ||
                            camposDinamicos[index]["valor"] == 0) {
                        mostrarValidacionLoaderClose("Debe ingresar " + item.descripcion);
                        isOk = false;
                        return false;
                    }
                }
                break;
            case 26:// vendedor - responsable    
                camposDinamicos[index]["valor"] = select2.obtenerValor('cbo_' + item.id);
                if (item.opcional == 0) {
                    //validamos
                    if (isEmpty(camposDinamicos[index]["valor"]) ||
                            camposDinamicos[index]["valor"] == 0) {
                        mostrarValidacionLoaderClose("Debe ingresar " + item.descripcion);
                        isOk = false;
                        return false;
                    }
                }
                break;
        }
    });
    return isOk;
}

function onResponseObtenerDataCbo(cboId, itemId, itemDes, data) {

    document.getElementById('cbo' + cboId).innerHTML = "";

    select2.asignarValor('cbo' + cboId, "");
    //$('#cbo' + cboId).append('<option value=0>Seleccione la dirección</option>');
    if (!isEmpty(data)) {
        $.each(data, function (index, item) {
            $('#cbo' + cboId).append('<option value="' + item[itemId] + '">' + item[itemDes] + '</option>');
        });

        select2.asignarValor('cbo' + cboId, data[0]["id"]);

//        $("#cbo" + cboId).select2({
//            width: '100%'
//        });
    } else {
        select2.asignarValor('cbo' + cboId, 0);
    }
}

function onResponseObtenerPersonaDireccionTexto(data) {
    if (isEmpty(data)) {
        $('#txt_' + textoDireccionId).val('');
    } else {
        $('#txt_' + textoDireccionId).val(data[0]['direccion']);
    }

}

/**
 *
 * @param data
 * @param data.pdf
 * @param data.nombre
 * @param data.url
 */
function abrirDocumentoPDF(data, contenedor) {
    if (isEmpty(data.pdfSunat)) {
        // window.open(data.url, '_blank');
        const link = document.createElement('a');
        link.href = data.url;
        link.target = '_blank';
        link.click();
        setTimeout(function () {
            eliminarPDF(contenedor + data.pdf);
        }, 4000);


    } else {
        if(data.descargar = 0){
            window.open(URL_BASE + 'pdf2.php?url_pdf=' + data.url + '&nombre_pdf=' + data.nombre);
        }else{
            window.open(data.url);
        }
    }
}

function eliminarPDF(url) {
    ax.setAccion("eliminarPDF");
    ax.addParamTmp("url", url);
    ax.consumir();
}


function obtenerMovimientoTipoColumnaLista() {
    ax.setAccion("obtenerMovimientoTipoColumnaLista");
    ax.consumir();
}

var detalleColumna = [];
var detalleColumnaDefs = [];
var indexFechaCreacion = 0;

/**
 *
 * @param data
 * @param data.ancho
 * @param data.alias
 * @param data.alineacion
 * @param data.documento_columna_consulta_id
 * @param data.fecha_creacion
 * @param data.documento_tipo_dato_tipo
 * @param data.documento_tipo_icono
 * @param data.documento_id
 */
function onResponseObtenerMovimientoTipoColumnaLista(data) {
    var dataColumna = data;

    $("#theadListado").empty();
    var fila = "<tr>";

    //columnas dinamicas de acuerdo a documento_tipo_columna
    var anchoDinamicoTabla = 0;
    var objDetalle = {};
    var objColumnaDefs = {};
    var alinear = '';

    if (!isEmpty(dataColumna)) {
        $.each(dataColumna, function (index, item) {
            //PARA LA CABECERA
            fila += "<th style='text-align:center; width: " + item.ancho + "px;'>" + item.nombre + "</th>";
            anchoDinamicoTabla += parseInt(item.ancho);

            //PARA LAS COLUMNAS DEL DETALLE
            //limpiar
            objDetalle = {};
            alinear = '';

            //construir
            objDetalle.data = item.alias;
            if (item.alias === 'documento_estado_negocio_descripcion') {
                //objDetalle.mRender = '<a href="google.com/'+data+'">';
            }
            objDetalle.width = item.ancho + "px";

            if (!isEmpty(item.alineacion)) {
                switch (item.alineacion) {
                    case 'D':
                        alinear = "alignRight";
                        break;
                    case 'I':
                        alinear = "alignLeft";
                        break;
                    case 'C':
                        alinear = "alignCenter";
                        break;
                }

                objDetalle.sClass = alinear;
            }

            //guardo en array
            detalleColumna.push(objDetalle);
            //PARA LAS COLUMNAS DEFS DEL DETALLE
            objColumnaDefs = {};
            if (!isEmpty(item.documento_columna_consulta_id)) {
                switch (parseInt(item.documento_columna_consulta_id)) {
                    case 9: //Usuario
                        objColumnaDefs.render = function (data, type, row) {
                            return '<p title="'+row.persona_nombre+'">'+ data +'</p>';
                        };
                        objColumnaDefs.targets = index;
                        break;
                    case 8: //fecha creacion
                        objColumnaDefs.render = function (data, type, row) {
                            var fecha = row.fecha_creacion;
                            var muestraFecha = '';

                            if (obtenerFechaActualBD() == data.substring(0, 10)) {
                                muestraFecha = fecha.substring(12, fecha.length);
                            } else {
                                muestraFecha = fecha.substring(0, 10);
                            }
                            return muestraFecha;
                        };
                        objColumnaDefs.targets = index;
                        indexFechaCreacion = index;
                        break;
                    case 10: //Estado
                        if(dataConfiguracionInicial.movimientoTipo[0]['id'] == 144 || dataConfiguracionInicial.movimientoTipo[0]['id'] == 145 || dataConfiguracionInicial.movimientoTipo[0]['id'] == 146 || dataConfiguracionInicial.movimientoTipo[0]['id'] == 147 || dataConfiguracionInicial.movimientoTipo[0]['id'] == 148){
                            objColumnaDefs.render = function (data, type, row) {
                                return data + ' &nbsp<i class="fa fa-info-circle" aria-hidden="true" title="'+data+" por "+row.usuario_estado+'"></i>';
                            };
                            objColumnaDefs.targets = index;
                        }else{
                            objColumnaDefs.render = function (data, type, row) {
                                return data;
                            };
                            objColumnaDefs.targets = index;
                        }
                        break;
                    case 18: //ESTADO NEGOCIO
                        objColumnaDefs.render = function (data, type, row) {
                            var rowTitle = row.documento_tipo_descripcion + " " + row.serie_numero;
                            return '<a href="#" onclick="obtenerReporteDocumentosAsignaciones(' + row.documento_id + ',\'' + rowTitle + '\')">' + data + '</a>';
                        };
                        objColumnaDefs.targets = index;
                        break;
                        //FORMATO FECHAS
                    case 6://fecha emision
                    case 7://fecha vencimiento
                        objColumnaDefs.render = function (data, type, row) {
                            var fecha = '';
                            if (!isEmpty(data)) {
                                fecha = formatearFechaBDCadena(data);
                            }
                            return fecha;
                        };
                        objColumnaDefs.targets = index;
                        break;

                    case 1: //nombre persona
                        objColumnaDefs.render = function (data) {
                            if (!isEmpty(data)) {
                                if (data.length > 28) {
                                    data = data.substring(0, 25) + '...';
                                }
                            }
                            return data;
                        };
                        objColumnaDefs.targets = index;
                        break;
                    case 2: //documento descripcion, para mostrar el icono en ves de la descripcion.
                        objColumnaDefs.render = function (data, type, row) {
                            return '<i class="' + row.documento_tipo_icono + '"></i>';
                        };
                        objColumnaDefs.targets = index;
                        break;
                    case 19: //BIEN UNICO
                        objColumnaDefs.render = function (data, type, row) {
//                            console.log(data,row);
//                            data=row.bien_unico_contad;
                            var titulo = row.documento_tipo_descripcion + " " + row.serie_numero;
                            var html = '';
                            if (isEmpty(data)) {
                                html = '<button class="btn btn-success btn-xs m-b-5" onclick="confirmarGenerarBienUnico(' + row.documento_id + ',\'' + titulo + '\')" style="margin-bottom: 1px;"><i class="ion-plus"></i>&nbsp Generar</button>';
                            } else if (data == 1) {
                                html = '<button class="btn btn-danger btn-xs m-b-5" onclick="confirmarAnularBienUnico(' + row.documento_id + ',\'' + titulo + '\')" style="margin-bottom: 1px;"><i class="ion-close-round"></i>&nbsp Anular</button>'
                            }

                            if (row.estado_documento != 1) {
                                html = '';
                            }
                            return html;
                        };
                        objColumnaDefs.targets = index;
                        break;
                    case 20:
                        $('#liEstadoNegocio').show();
                        // $('#liNumeroOrdenCompra').show();
                        break;
                    case 31:

                        $('#liProyecto').show();
                        break;

                        //FORMATO NUMERO DECIMAL
                    case 5: //total
                    case 25:  //subtotal  
                    case 26: //igv
                        objColumnaDefs.render = function (data, type, row) {
                            return parseFloat(data).formatMoney(2, '.', ',');
                        };
                        objColumnaDefs.targets = index;
                        break;
                }
            } else {
                switch (parseInt(item.documento_tipo_dato_tipo)) {
                    //FORMATO NUMERO DECIMAL
                    case 1:
                        objColumnaDefs.render = function (data, type, row) {
                            return parseFloat(data).formatMoney(2, '.', ',');
                        };
                        objColumnaDefs.targets = index;
                        break;

                        //FORMATO FECHAS
                    case 3:
                        objColumnaDefs.render = function (data, type, row) {
                            var fecha = '';
                            if (!isEmpty(data)) {
                                fecha = formatearFechaBDCadena(data);
                            }
                            return fecha;
                        };
                        objColumnaDefs.targets = index;
                        break;
                }

            }
            //guardar en array columna defs
            if (!isEmpty(objColumnaDefs)) {
                detalleColumnaDefs.push(objColumnaDefs);
            }
        });
    }

    //acciones
    fila += "<th style='text-align:center; width:115px;'>Acciones</th>";
//    fila += "<th style='text-align:center; width:95px;'>Acciones</th>";
//    fila += "<th style='text-align:center; width:95px;'>Acciones</th>";
    anchoDinamicoTabla += 115;
    detalleColumna.push({data: "acciones", width: "115px"});

    fila += "</tr>";

    $("#datatable").width(anchoDinamicoTabla);
    $('#datatable thead').append(fila);


}

function obtenerEmail(accion, descripcion, icono) {
    $('#idDescripcionBoton').show();
    $('#idDescripcionBoton').html('<i class="' + icono + '"></i>&nbsp;&nbsp; ' + descripcion);

    ax.setAccion("obtenerEmailsXAccion");
    ax.addParamTmp("accion", accion);
    ax.addParamTmp("documentoId", docId);
    ax.consumir();
}

var resultadoObtenerEmails;
/**
 *
 * @param data
 * @param data.correo
 */
function onResponseObtenerEmailsXAccion(data) {
    resultadoObtenerEmails = data;

    $('#txtCorreo').val(data.correo);
}



function enviarCorreoXAccion() {
    var correo = $('#txtCorreo').val();
    //var pattern = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/igm;

    correo = fixCorreo(correo);
    $('#txtCorreo').val(correo);
    var arr = correo.split(";");
    var valid = true;
    var boo = false;
    for (var i = 0; i < arr.length - 1; i++)
    {
        boo = validarEmail(arr[i]);
        if (boo)
        {
            valid = valid && boo;
        } else {
            valid = valid && boo;
        }
    }

    if (isEmpty(resultadoObtenerEmails)) {
        mostrarAdvertencia("Seleccion tipo de envío de correo.");
        return;
    }

    if (!isEmpty(correo) && valid) {
        loaderShow('#modalDetalleDocumento');
        ax.setAccion("enviarCorreoXAccion");
        ax.addParamTmp("correo", correo);
        ax.addParamTmp("documentoId", docId);
        ax.addParamTmp("dataRespuestaEmail", resultadoObtenerEmails);
        ax.addParamTmp("comentario", $('#txtComentario').val());
        ax.consumir();

    } else {
        mostrarAdvertencia("Ingrese email válido.");
        return;
    }
}

/**
 *
 * @param data
 * @param data.funcion
 * @param data.icono
 */
function dibujarTipoEnvioEmail(data) {

    $("#idDescripcionBoton").hide();
    var ulObtenerEmail = $("#ulObtenerEmail");
    ulObtenerEmail.empty();
    var html = '';
    var estilo = '';
    if (!isEmpty(data)) {
        $.each(data, function (index, item) {
            estilo = '';
            if (!isEmpty(item.color)) {
                estilo = 'style="color: ' + item.color + '"';
            }

            html += '<li><a href="#" onclick="obtenerEmail(\'' + item.funcion + '\',\'' + item.descripcion + '\',\'' + item.icono + '\')"><i class="' + item.icono + '" ' + estilo + '></i>&nbsp;&nbsp; ' + item.descripcion + '</a></li>';
        });
    } else {
        $('#alertEmail').hide();
    }

    ulObtenerEmail.append(html);
}

function getUserEmailByUserId()
{
    ax.setAccion("getUserEmailByUserId");
    ax.consumir();

}

function appendCurrentUserEmail(data)
{
    var fetchEmail;
    var arr;
    var correo;
    var newCorreo = "";


    if (!isEmpty(data))
    {
        fetchEmail = data[0]['email'];
    }

    if ($("#checkIncluirSelf").is(':checked'))
    {
        $('#txtCorreo').val(function (i, val) {
            return val + fetchEmail + (val ? ';' : '');
        });


        correo = $('#txtCorreo').val();
        correo = fixCorreo(correo);
        $('#txtCorreo').val(correo);

    } else {

        correo = $('#txtCorreo').val();
        arr = correo.split(";");

        for (var i = 0; i < arr.length; i++)
        {
            if (arr[i] === fetchEmail)
            {
                //alert("adsa: "+arr[i]);
                arr.splice(i, 1);
            } else {
                newCorreo += arr[i] + ";";

            }
        }
        newCorreo = fixCorreo(newCorreo);
        $('#txtCorreo').val(newCorreo);
    }
}

function cerrarModalReporteAtenciones()
{
    $('#dataReporteAtenciones').empty();
    $('#modalReporteAtenciones').modal('hide');
}

function obtenerReporteDocumentosAsignaciones(documentoId, rowTitle)
{
    loaderShow();
    var tituloTablaReportes = $('#modalReporteAtencionesTitulo');
    tituloTablaReportes.text("Atencion de " + rowTitle);
    ax.setAccion("obtenerReporteDocumentosAsignaciones");
    ax.addParamTmp("documento_id", documentoId);
    ax.consumir();
}

/**
 *
 * @param data
 * @param data.serie_numero
 */
function onResponseObtenerReporteDocumentosAsignaciones(data)
{
    var htmlTablaReporteAtenciones = "";
    var tablaReportes = $('#tableReporteAtenciones');

    tablaReportes.empty();
    if (!isEmpty(data)) {
        $.each(data, function (index, item) {
            htmlTablaReporteAtenciones += '<tr class="gang-name-1">' +
                    '<td colspan="2">' +
                    item.documento_tipo + " - " + item.serie_numero +
                    '</td>' +
                    '</tr>';
        });
        tablaReportes.append(htmlTablaReporteAtenciones);
    } else {
        tablaReportes.append("<h2>Aún no se registran atenciones para este documento.</h2>")
    }
    $('#modalReporteAtenciones').modal('show');

}

function confirmarGenerarBienUnico(documentoId, titulo) {
    swal({
        title: "Est\xe1s seguro?",
        text: "Generará los códigos únicos de los productos correspondientes del documento: <br>" + titulo,
        type: "warning",
        html: true,
        showCancelButton: true,
        confirmButtonColor: "#33b86c",
        confirmButtonText: "Si, generar !",
        cancelButtonColor: '#d33',
        cancelButtonText: "No, cancelar !",
        closeOnConfirm: true,
        closeOnCancel: false
    }, function (isConfirm) {
        if (isConfirm) {
            generarBienUnico(documentoId);
        } else {
            swal("Cancelado", "La generación fue cancelada", "error");
        }
    });
}

function generarBienUnico(documentoId) {
    loaderShow();
    ax.setAccion("generarBienUnicoXDocumentoId");
    ax.addParamTmp("documentoId", documentoId);
    ax.consumir();
}

function onResponseGenerarBienUnicoXDocumentoId(data) {
    exitoCrear(data);
    buscar();
}

function confirmarAnularBienUnico(documentoId, titulo) {
    swal({
        title: "Est\xe1s seguro?",
        text: "Anulará los códigos únicos de los productos correspondientes del documento: <br>" + titulo,
        type: "warning",
        html: true,
        showCancelButton: true,
        confirmButtonColor: "#33b86c",
        confirmButtonText: "Si, anular!",
        cancelButtonColor: '#d33',
        cancelButtonText: "No, cancelar !",
        closeOnConfirm: true,
        closeOnCancel: false
    }, function (isConfirm) {
        if (isConfirm) {
            anularBienUnico(documentoId);
        } else {
            swal("Cancelado", "La anulaci\xf3n fue cancelada", "error");
        }
    });
}

function anularBienUnico(documentoId) {
    loaderShow();
    ax.setAccion("anularBienUnicoXDocumentoId");
    ax.addParamTmp("documentoId", documentoId);
    ax.consumir();
}

function asignarCodigoUnico(documentoId, movimientoId) {
    loaderShow();
    ax.setAccion("obtenerBienUnicoConfiguracionInicial");
    ax.addParamTmp("documentoId", documentoId);
    ax.consumir();
}

var dataAsignarBU = null;
function onResponseObtenerBienUnicoConfiguracionInicial(data) {
    dataAsignarBU = data;
    var dataBienUnicoDisponible = data.dataBienUnicoDisponible;
    var dataDocumento = data.dataDocumento;
    var dataMovimientoBienUnico = data.dataMovimientoBienUnico;
    var banderaBU = dataDocumento[0]['estado_qr'];
    var titulo = "";

//    if (!isEmpty(dataMovimientoBienUnico)) {
//        listaBienUnicoDetalle = dataMovimientoBienUnico;
//        banderaBU = validarCantidadesExactasBUD();
//    }

    if (banderaBU == 2) {
        titulo = '<b>Códigos únicos asignados: </b>' + dataDocumento[0]['documento_tipo_descripcion'] + ' ' + dataDocumento[0]['serie_numero'];
        $('#tituloModalAsignarCodigoUnico').html(titulo);

        $('#divAgregarBU').hide();
        $('#divLeyendaBU').hide();
        $('#idGuardarBienUnico').hide();
        $('#idEnviarBienUnico').hide();

        listaBienUnicoDetalle = dataMovimientoBienUnico;
        onListarBienUnicoDetalle(listaBienUnicoDetalle, 0);
//        return;
    } else {
        if (isEmpty(dataBienUnicoDisponible)) {
            mostrarAdvertencia('No hay productos únicos disponibles para asignar.');
            return;
        }

        titulo = '<b>Asignar códigos únicos: </b>' + dataDocumento[0]['documento_tipo_descripcion'] + ' ' + dataDocumento[0]['serie_numero'];
        $('#tituloModalAsignarCodigoUnico').html(titulo);

        $('#divAgregarBU').show();
        $('#divLeyendaBU').show();
        $('#idGuardarBienUnico').show();
        $('#idEnviarBienUnico').show();

        select2.cargar('cboBienUnico', dataBienUnicoDisponible, 'bien_unico_id', ['codigo_unico', 'bien_descripcion']);
        select2.asignarValor('cboBienUnico', dataBienUnicoDisponible[0].bien_unico_id);

        document.getElementById("chkHasta").checked = false;
        onClickCheckHasta();
        limpiarBienUnicoDetalle();

        if (!isEmpty(dataMovimientoBienUnico)) {
            listaBienUnicoDetalle = dataMovimientoBienUnico;
            onListarBienUnicoDetalle(listaBienUnicoDetalle, 1);
        }
    }

    $('#modalAsignarCodigoUnico').modal('show');
}

function onClickCheckHasta() {
//        document.getElementById("chkHasta").checked = true;
    if (document.getElementById("chkHasta").checked) {
        var buPartes = obtenerBienUnicoPartes();

        $('#txtBienUnicoDescripcion').val(buPartes.parte1);
        $('#txtBienUnicoNumero').val(buPartes.parte2);
    } else {
        $('#txtBienUnicoDescripcion').val('');
        $('#txtBienUnicoNumero').val('');
    }
}

function onChangeComboBienUnico() {
    onClickCheckHasta();
}

function obtenerBienUnicoPartes() {
    var dataBienUnicoDisponible = dataAsignarBU.dataBienUnicoDisponible;

    var indiceBU = document.getElementById('cboBienUnico').options.selectedIndex;
    var codigoBU = dataBienUnicoDisponible[indiceBU].codigo_unico;

    var parte1 = codigoBU.substring(0, codigoBU.length - 5);
    var parte2 = codigoBU.substring(codigoBU.length - 5, codigoBU.length) * 1;

    var parteUltima = parte2;
    $.each(dataBienUnicoDisponible, function (i, item) {
        var codigoBUItem = item.codigo_unico;
        var parteItem1 = codigoBUItem.substring(0, codigoBUItem.length - 5);
        var parteItem2 = codigoBUItem.substring(codigoBUItem.length - 5, codigoBUItem.length) * 1;

        if (parte1 === parteItem1) {
            parteUltima = parteItem2;
        }
    });

    return {parte1: parte1, parte2: parteUltima};
}

//AGREGAR BIEN UNICO DETALLE

var listaBienUnicoDetalle = [];


function agregarBienUnico() {
    if (!document.getElementById("chkHasta").checked) {
        //solo un producto
        var dataBienUnicoDisponible = dataAsignarBU.dataBienUnicoDisponible;
        var indiceBU = document.getElementById('cboBienUnico').options.selectedIndex;
        var bienUnico = dataBienUnicoDisponible[indiceBU];

        agregarBienUnicoDetalle(bienUnico);

    } else {
        //array
        var parteUltima = $('#txtBienUnicoNumero').val();
        var dataBienUnicoDisponible = dataAsignarBU.dataBienUnicoDisponible;

        var indiceBU = document.getElementById('cboBienUnico').options.selectedIndex;
        var codigoBU = dataBienUnicoDisponible[indiceBU].codigo_unico;

        var parte1 = codigoBU.substring(0, codigoBU.length - 5);
        var parte2 = codigoBU.substring(codigoBU.length - 5, codigoBU.length) * 1;

        for (var i = parte2; i <= parteUltima; i++) {
            var codigoGenerado = parte1 + pad(i, 5);

            var itemBienUnico = null;
            $.each(dataBienUnicoDisponible, function (i, item) {
                if (item.codigo_unico == codigoGenerado) {
                    itemBienUnico = item;
                    return;
                }
            });

            if (!isEmpty(itemBienUnico)) {
                agregarBienUnicoDetalle(itemBienUnico);
            }
        }
    }
}

function pad(str, max) {
    str = str.toString();
    return str.length < max ? pad("0" + str, max) : str;
}

function agregarBienUnicoDetalle(objDetalle) {
    if (validarBienUnicoDetalle(objDetalle)) {
        objDetalle.movimiento_bien_unico_id = null;

        var bienId = objDetalle.bien_id;
        var dataMovimientoBien = dataAsignarBU.dataMovimientoBien;

        var movimientoBienId = 0;
        $.each(dataMovimientoBien, function (i, item) {
            if (item.bien_id == bienId) {
                movimientoBienId = item.movimiento_bien_id;
                return;
            }
        });

        objDetalle.movimiento_bien_id = movimientoBienId;

        listaBienUnicoDetalle.push(objDetalle);

        onListarBienUnicoDetalle(listaBienUnicoDetalle, 1);
    }
}

function validarBienUnicoDetalle(objDetalle) {
    var check = document.getElementById("chkHasta").checked;

    var valido = true;

    //REPETIDO
    var indice = buscarBienUnicoDetalle(objDetalle.bien_unico_id);
    if (indice > -1) {
//        if(!check){
        mostrarAdvertencia("Producto único ya ha sido agregado: " + objDetalle.codigo_unico);
//        }
        valido = false;
    }

    if (!valido) {
        return valido;
    }

    //CANTIDAD <    
    var bienId = objDetalle.bien_id;
    var dataMovimientoBien = dataAsignarBU.dataMovimientoBien;

    var cantidadTotal = 0;
    $.each(dataMovimientoBien, function (i, item) {
        if (item.bien_id == bienId) {
            cantidadTotal = item.cantidad * 1;
            return;
        }
    });

    var cont = 0;
    $.each(listaBienUnicoDetalle, function (i, item) {
        if (item.bien_id == bienId) {
            cont++;
        }
    });

    if (cont >= cantidadTotal) {
        if (!check) {
            mostrarAdvertencia(" Ya se completó la cantidad requerida (" + cantidadTotal + ") del producto " + objDetalle.bien_descripcion)
        }
        valido = false;
    }

    return valido;
}

function buscarBienUnicoDetalle(bienUnicoId) {
    var ind = -1;

    if (!isEmpty(listaBienUnicoDetalle)) {
        $.each(listaBienUnicoDetalle, function (i, item) {
            if (item.bien_unico_id == bienUnicoId) {
                ind = i;
            }
        });
    }

    return ind;
}

function onListarBienUnicoDetalle(data, opcion) {
//    $('#dataTableBienUnicoDetalle tbody tr').remove();
//    $('#dataTableBienUnicoDetalle thead tr').remove();
    $("#dataList").empty();

    //dibujando la cabecera
    var cabeza = "<table id='dataTableBienUnicoDetalle' class='table table-striped table-bordered'>" +
            "<thead>" +
            '<tr>' +
            '<th style="text-align:center">N°</th>' +
            '<th style="text-align:center">Prod. Único</th>' +
            '<th style="text-align:center">Producto</th>' +
            '<th style="text-align:center">Estado</th>';
    if (opcion == 1) {
        cabeza += '<th style="text-align:center">Opciones</th>';
    }
    cabeza += '</tr>' +
            "</thead>";


    var cuerpo = "";
    var ind = 0;
    if (!isEmpty(data)) {
        data.forEach(function (item) {
            if (opcion == 1) {
                var eliminar = "<a href='#' onclick = 'eliminarBienUnicoDetalle(\"" + ind + "\")' >"
                        + "<i id='e" + ind + "' class='fa fa-trash-o' style='color:#cb2a2a;'></i></a>";
            }
            var estadoBU = calcularEstadoBU(ind);

            cuerpo += "<tr>"
                    + "<td style='text-align:right;'>" + (ind + 1) + "</td>"
                    + "<td style='text-align:left;'>" + item.codigo_unico + "</td>"
                    + "<td style='text-align:left;'>" + item.bien_descripcion + "</td>"
                    + "<td style='text-align:center;'>" + estadoBU + "</td>";
            if (opcion == 1) {
                cuerpo += "<td style='text-align:center;'>" + eliminar + "</td>";
            }
            cuerpo += "</tr>";

            ind++;
        });

//        $('#dataTableBienUnicoDetalle tbody').append(cuerpo);
    }

    var pie = '</table>';
    var html = cabeza + cuerpo + pie;
    $("#dataList").append(html);
    onResponseVacio();
}

function onResponseVacio() {
    $('#dataTableBienUnicoDetalle').dataTable({
        "order": [[0, 'asc']],
        "language": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "Ning\xfAn dato disponible en esta tabla",
            "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix": "",
            "sSearch": "Buscar:",
            "sUrl": "",
            "sInfoThousands": ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Último",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        }
    });
}

function calcularEstadoBU(indice) {
    var bienId = listaBienUnicoDetalle[indice].bien_id;
    var dataMovimientoBien = dataAsignarBU.dataMovimientoBien;

    var cantidadTotal = 0;
    $.each(dataMovimientoBien, function (i, item) {
        if (item.bien_id == bienId) {
            cantidadTotal = item.cantidad * 1;
            return;
        }
    });

    var cont = 0;
    for (var i = 0; i <= indice; i++) {
        if (listaBienUnicoDetalle[i].bien_id == bienId) {
            cont++;
        }
    }

    return cont + '/' + cantidadTotal;
}


var listaBienUnicoDetalleEliminado = [];

function eliminarBienUnicoDetalle(indice) {
    if (!isEmpty(listaBienUnicoDetalle[indice].movimiento_bien_unico_id)) {
        listaBienUnicoDetalleEliminado.push(listaBienUnicoDetalle[indice].movimiento_bien_unico_id);
    }

    mostrarOk('Producto único eliminado: ' + listaBienUnicoDetalle[indice].codigo_unico);
    listaBienUnicoDetalle.splice(indice, 1);

    var detalleCopia = listaBienUnicoDetalle.slice();
    listaBienUnicoDetalle = [];

    if (!isEmpty(detalleCopia)) {
        $.each(detalleCopia, function (i, item) {
            listaBienUnicoDetalle.push(item);
        });
    }

    onListarBienUnicoDetalle(listaBienUnicoDetalle, 1);
}

function limpiarBienUnicoDetalle() {
//    $('#dataTableBienUnicoDetalle tbody tr').remove();
    $("#dataList").empty();
    listaBienUnicoDetalle = [];
    listaBienUnicoDetalleEliminado = [];
}

function guardarBienUnicoDetalle(estadoQR) {
    if (isEmpty(listaBienUnicoDetalle)) {
        mostrarAdvertencia('Seleccione productos únicos.');
        return;
    }
    $('#modalAsignarCodigoUnico').modal('hide');
    loaderShow();
    ax.setAccion("guardarBienUnicoDetalle");
    ax.addParamTmp("listaBienUnicoDetalle", listaBienUnicoDetalle);
    ax.addParamTmp("listaBienUnicoDetalleEliminado", listaBienUnicoDetalleEliminado);
    ax.addParamTmp("estadoQR", estadoQR);
    ax.consumir();
}

function onResponseGuardarBienUnicoDetalle(data) {
    if (data.respuesta[0]["vout_exito"] == 0) {
        $.Notification.autoHideNotify('warning', 'top right', 'Validación', "Error.");
    } else {
        $.Notification.autoHideNotify('success', 'top-right', 'Éxito', 'Se guardó correctamente la asignación de productos únicos.');

        $('#modalAsignarCodigoUnico').modal('hide');
        $('.modal-backdrop').hide();

        if (data.indicador == 1) {
            buscar();
        }
    }
}

function enviarBienUnicoDetalle() {
    //valido cantidades exactas
    var bandera = validarCantidadesExactasBUD();
    if (bandera) {
        $('#modalAsignarCodigoUnico').modal('hide');
        swal({
            title: "Estás seguro?",
            text: "Si finaliza la asignación de códigos únicos ya no podrá modificar la asignación posteriormente.",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#33b86c",
            confirmButtonText: "Si, finalizar!",
            cancelButtonColor: '#d33',
            cancelButtonText: "No, cancelar !",
            closeOnConfirm: true,
            closeOnCancel: true
        }, function (isConfirm) {
            if (isConfirm) {
//                $('#modalAsignarCodigoUnico').modal('show');  
                guardarBienUnicoDetalle(2);
            } else {
                $('#modalAsignarCodigoUnico').modal('show');
            }
        });


    } else {
        mostrarAdvertencia('Falta ingresar productos únicos. Cantidad por detalle del documento incompleto.')
    }
}

function validarCantidadesExactasBUD() {
    var listaBUDetalleCantidades = [];

    var dataMovimientoBien = dataAsignarBU.dataMovimientoBien;

    $.each(dataMovimientoBien, function (i, itemMB) {
        var cont = 0;
        $.each(listaBienUnicoDetalle, function (j, itemBUD) {
            if (itemMB.bien_id == itemBUD.bien_id) {
                cont++;
            }
        });

        listaBUDetalleCantidades.push({bienId: itemMB.bien_id, contador: cont});
    });

    var bandera = true;
    $.each(dataMovimientoBien, function (i, itemMB) {
        $.each(listaBUDetalleCantidades, function (j, itemBUDC) {
            if (itemMB.bien_id == itemBUDC.bienId) {
                if (itemMB.cantidad * 1 != itemBUDC.contador * 1) {
                    bandera = false;
                    return;
                }
            }
        });
    });

    if (dataMovimientoBien.length != listaBUDetalleCantidades.length) {
        bandera = false;
    }

    return bandera;
}

function dibujarLeyendaAcciones(data) {
    var html = '<br><b>Leyenda:</b>&nbsp;&nbsp;';
    if (!isEmpty(data)) {
        $.each(data, function (i, item) {
            if (item.mostrarAccion == 1) {
                html += "<i class='" + item.icono + "' style='color:" + item.color + ";'></i>&nbsp;" + item.descripcion + " &nbsp;&nbsp;&nbsp;";
            }
        });
    }

    $('#divLeyenda').html(html);
}

function imprimirDocumentoQR(documentoId, movimientoId) {
    $('#documentoIdHidden').val(documentoId);
    document.formDocumentoQR.submit();
}

var modalReferenciaGlobalId;
var preVisualizarTab = "dataGeneral";
//AREA DE OPCION DE RELACIONAR DOCUMENTO
function prepararModalDocumentoACopiar(modalReferenciaId) {
    modalReferenciaId = (isEmpty(modalReferenciaId) ? 'modalDocumentoRelacionado' : modalReferenciaId);
    $('#' + modalReferenciaId).modal('hide');

    modalReferenciaGlobalId = modalReferenciaId;
    setTimeout(function () {
        cargarBuscadorDocumentoACopiar();
    }, 500);
}

function cargarBuscadorDocumentoACopiar() {
    if (bandera.primeraCargaDocumentosRelacion) {
        loaderShow();
        obtenerConfiguracionesInicialesBuscadorCopiaDocumento();
        bandera.primeraCargaDocumentosRelacion = false;
    } else {
        cargarModalCopiarDocumentos();
        actualizarBusquedaDocumentoRelacion();
    }
}

function cerrarModalCopia() {
    $('#modalDocumentoRelacion').modal('hide');

    $('#' + modalReferenciaGlobalId).modal('show');
}

function actualizarBusquedaDocumentoRelacion() {
    buscarDocumentoRelacionPorCriterios();
}

function obtenerConfiguracionesInicialesBuscadorCopiaDocumento()
{
    ax.setAccion("obtenerConfiguracionBuscadorDocumentoRelacion");
    ax.addParamTmp("empresa_id", commonVars.empresa);
    ax.consumir();
}
var datosEstaticosBusquedadACopiar = {};
function onResponseObtenerConfiguracionBuscadorDocumentoRelacion(data)
{
    $("#cboDocumentoTipoM").select2({
        width: "100%"
    });
    select2.cargar("cboDocumentoTipoM", data.documento_tipo, "id", "descripcion");
    $("#cboPersonaM").select2({
        width: "100%"
    });
    select2.cargar("cboPersonaM", data.persona, "id", "nombre");

    $("#cboEstadoM").select2({
        width: "100%"
    });
    select2.cargar("cboEstadoM", data.estado, "id", "descripcion");
    if (!isEmpty(datosEstaticosBusquedadACopiar)) {
        if (!isEmpty(datosEstaticosBusquedadACopiar.persona_id)) {
            select2.asignarValor("cboPersonaM", datosEstaticosBusquedadACopiar.persona_id);
        }

        if (!isEmpty(datosEstaticosBusquedadACopiar.estado_id)) {
            select2.asignarValor("cboEstadoM", datosEstaticosBusquedadACopiar.estado_id);
        }
        datosEstaticosBusquedadACopiar = {};
    }

//    var table = $('#dtDocumentoRelacion').DataTable();
    //table.clear().draw();

    cargarModalCopiarDocumentos();
}

function cargarModalCopiarDocumentos() {
    $('#modalDocumentoRelacion').modal('show');
}

function cargarModalDetalleDocumento() {
    $('#modalDetalleDocumento').modal('show');
}

function buscarDocumentoRelacionPorCriterios() {
    loaderShow('#dtDocumentoRelacion');
    var cadena;
    //alert('hola');
    obtenerParametrosBusquedaDocumentoACopiar();

    setTimeout(function () {
        getDataTableDocumentoACopiar();
    }, 500);
}

function getDataTableDocumentoACopiar() {
    console.log('hola');
    ax.setAccion("buscarDocumentoRelacionPorCriterio");
    ax.addParamTmp("criterios", parametrosBusquedaDocumentoACopiar);
    ax.addParamTmp("empresa_id", commonVars.empresa);

    $('#dtDocumentoRelacion').dataTable({
        "processing": true,
        "serverSide": true,
        "bFilter": false,
        "ajax": ax.getAjaxDataTable(),
        "scrollX": true,
        "order": [[0, "desc"]],
        "columns": [
            {"data": "fecha_creacion", "width": "9%"},
            {"data": "fecha_emision", "width": "7%"},
            {"data": "documento_tipo", "width": "10%"},
            {"data": "persona", "width": "24%"},
            {"data": "serie_numero", "width": "10%"},
            {"data": "serie_numero_original", "width": "10%"},
            {"data": "fecha_vencimiento", "width": "7%"},
            {"data": "moneda_simbolo", "width": "4%", "sClass": "alignCenter"},
            {"data": "total", "width": "8%", "sClass": "alignRight"},
            {"data": "usuario", "width": "6%", "sClass": "alignCenter"},
            {data: "flechas",
                render: function (data, type, row) {
                    if (type === 'display') {
                        var soloRelacionar = '<a onclick="confirmarAgregarDocumentoARelacionar(' + row.documento_id + ',\'' + row.documento_tipo + '\',\'' + row.serie_numero + '\')"><b><i class="fa fa-arrow-down" style = "color:#1ca8dd;" tooltip-btndata-toggle="tooltip" title="Solo relacionar"></i></b></a>';
                        return soloRelacionar;
                    }
                    return data;
                },
                "orderable": false,
                "class": "alignCenter",
                "width": "5%"
            }
        ],
        columnDefs: [
            {
                "render": function (data, type, row) {
                    return (isEmpty(data)) ? '' : data.replace(" 00:00:00", "");
                },
                "targets": [1, 6]
            },
            {
                "render": function (data, type, row) {
                    return parseFloat(data).formatMoney(2, '.', ',');
                },
                "targets": 8
            }
        ],
        "fnRowCallback": function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            if (aData['documento_relacionado'] != '0')
            {
                $('td', nRow).css('background-color', '#FFD0D0');
            }
        },
        "dom": '<"top">rt<"bottom"<"col-md-3"l><"col-md-9"p><"col-md-12"i>><"clear">',
        destroy: true
    });
    cargarModalCopiarDocumentos();
    loaderClose();

}

var parametrosBusquedaDocumentoACopiar = {
    empresa_id: null,
    documento_tipo_ids: null,
    persona_id: null,
    estado_id: null,
    serie: null,
    numero: null,
    fecha_emision_inicio: null,
    fecha_emision_fin: null,
    fecha_vencimiento_inicio: null,
    fecha_vencimiento_fin: null
};

function obtenerParametrosBusquedaDocumentoACopiar() {
    parametrosBusquedaDocumentoACopiar = {
        empresa_id: null,
        documento_tipo_ids: null,
        persona_id: null,
        estado_id: null,
        serie: null,
        numero: null,
        fecha_emision_inicio: null,
        fecha_emision_fin: null,
        fecha_vencimiento_inicio: null,
        fecha_vencimiento_fin: null,
        movimiento_tipo_id: dataConfiguracionInicial.movimientoTipo[0].id
    };

    parametrosBusquedaDocumentoACopiar.empresa_id = commonVars.empresa;
    parametrosBusquedaDocumentoACopiar.documento_tipo_ids = $('#cboDocumentoTipoM').val();
    parametrosBusquedaDocumentoACopiar.estado_id = $('#cboEstadoM').val();
    var personaId = $('#cboPersonaM').val();
    if (!isEmpty(personaId))
    {
        parametrosBusquedaDocumentoACopiar.persona_id = personaId[0];
    }

    parametrosBusquedaDocumentoACopiar.serie = $('#txtSerie').val();
    parametrosBusquedaDocumentoACopiar.numero = $('#txtNumero').val();
    parametrosBusquedaDocumentoACopiar.fecha_emision_inicio = $('#dpFechaEmisionInicio').val();
    parametrosBusquedaDocumentoACopiar.fecha_emision_fin = $('#dpFechaEmisionFin').val();
    parametrosBusquedaDocumentoACopiar.fecha_vencimiento_inicio = $('#dpFechaVencimientoInicio').val();
    parametrosBusquedaDocumentoACopiar.fecha_vencimiento_fin = $('#dpFechaVencimientoFin').val();
}

function buscarDocumentoRelacion() {
    ax.setAccion("buscarDocumentoRelacion");
    ax.addParamTmp("busqueda", $('#txtBuscarCopia').val());
    ax.addParamTmp("empresa_id", commonVars.empresa);
    ax.consumir();
}

function onResponseBuscarDocumentoRelacion(data) {
    var dataPersona = data.dataPersona;
    var dataDocumentoTipo = data.dataDocumentoTipo;
    var dataSerieNumero = data.dataSerieNumero;

    var html = '';
    $('#ulBuscadorDesplegableCopia2').empty();
    if (!isEmpty(dataPersona)) {
        $.each(dataPersona, function (index, item) {
            html += '<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">';
            html += '<a onclick="busquedaPorTextoCopia(5,' + item.id + ',' + null + ')" >';
            html += '<span class="col-md-1"><i class="ion-person"></i></span>';
            html += '<span class="col-md-2">';
            html += '<label style="color: #141719;">' + item.codigo_identificacion + '</label>';
            html += '</span>';
            html += '<span class="col-md-9">';
            html += '<label style="color: #141719;">' + item.nombre + '</label>';
            html += '</span></a>';
            html += '</div>';

        });
    }

    if (!isEmpty(dataDocumentoTipo)) {
        $.each(dataDocumentoTipo, function (index, item) {
            html += '<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">';
            html += '<a onclick="busquedaPorTextoCopia(5,' + null + ',' + item.id + ')" >';
            html += '<span class="col-md-1"><i class="fa fa-files-o"></i></span>';
            html += '<span class="col-md-11">';
            html += '<label style="color: #141719;">' + item.descripcion + '</label>';
            html += '</span></a>';
            html += '</div>';

        });
    }


    if (!isEmpty(dataSerieNumero)) {
        $.each(dataSerieNumero, function (index, item) {
            html += '<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">';
            html += '<a onclick="busquedaPorSerieNumeroCopia(\'' + item.serie + '\',\'' + item.numero + '\')" >';
            html += '<span class="col-md-1"><i class="ion-document"></i></span>';
            html += '<span class="col-md-2">';
            html += '<label style="color: #141719;">' + item.serie_numero + '</label>';
            html += '</span>';
            html += '<span class="col-md-9">';
            html += '<label style="color: #141719;">' + item.documento_tipo_descripcion + '</label>';
            html += '</span></a>';
            html += '</div>';

        });
    }
    $("#ulBuscadorDesplegableCopia2").append(html);
}


function busquedaPorTextoCopia(tipo, texto, tipoDocumento) {

    var tipoDocumentoIds = [];
    if (!isEmpty(tipoDocumento)) {
        tipoDocumentoIds.push(tipoDocumento);
    }

    if (tipo == 5) {
        llenarParametrosBusquedaCopia(texto, tipoDocumentoIds, null, null, null, null);
    }

}

function busquedaPorSerieNumeroCopia(serie, numero) {
    llenarParametrosBusquedaCopia(null, null, serie, numero, null, null);
}

function llenarParametrosBusquedaCopia(personaId, tipoDocumentoIds, serie, numero, fechaEmision) {
    obtenerParametrosBusquedaDocumentoACopiar();

    parametrosBusquedaDocumentoACopiar.serie = serie;
    parametrosBusquedaDocumentoACopiar.numero = numero;
    parametrosBusquedaDocumentoACopiar.persona_id = personaId;
    parametrosBusquedaDocumentoACopiar.fecha_emision_inicio = fechaEmision;
    parametrosBusquedaDocumentoACopiar.documento_tipo_ids = tipoDocumentoIds;
    loaderShow();

    getDataTableDocumentoACopiar();
}

$('#txtBuscarCopia').keyup(function (e) {
    var bAbierto = $(this).attr("aria-expanded");

    if (!eval(bAbierto)) {
        $(this).dropdown('toggle');
    }

});

function confirmarAgregarDocumentoARelacionar(documentoId, documentoTipoDesc, serieNumero) {
    $('#modalDocumentoRelacion').modal('hide');
    swal({
        title: "Estás seguro?",
        text: "Relacionará el documento: " + documentoTipoDesc + " " + serieNumero,
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#33b86c",
        confirmButtonText: "Si, relacionar!",
        cancelButtonColor: '#d33',
        cancelButtonText: "No, cancelar !",
        closeOnConfirm: true,
        closeOnCancel: true
    }, function (isConfirm) {
        if (isConfirm) {
            relacionarDocumento(documentoId);
        } else {
            cargarModalCopiarDocumentos();
        }
    });
}

function confirmarEliminarDocumentoARelacionar(documentoId, documentoTipoDesc, serieNumero) {
    $('#modalDetalleDocumento').modal('hide');
    swal({
        title: "Estás seguro?",
        text: "Eliminará la relación del documento: " + documentoTipoDesc + " " + serieNumero,
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#33b86c",
        confirmButtonText: "Si, eliminar!",
        cancelButtonColor: '#d33',
        cancelButtonText: "No, cancelar !",
        closeOnConfirm: true,
        closeOnCancel: true
    }, function (isConfirm) {
        if (isConfirm) {
            eliminarRelacionacionDocumento(documentoIdOrigen, documentoId);
        } else {
            $('#modalDetalleDocumento').modal('show');
        }
    });
}

function eliminarRelacionacionDocumento(documentoId, documentoRelacionId) {
    ax.setAccion("eliminarRelacionDocumento");
    ax.addParamTmp("documentoId", documentoId);
    ax.addParamTmp("documentoRelacionId", documentoRelacionId);
    ax.consumir();
}

function relacionarDocumento(documentoId) {
    ax.setAccion("relacionarDocumento");
    ax.addParamTmp("documentoIdOrigen", documentoIdOrigen);
    ax.addParamTmp("documentoIdARelacionar", documentoId);
    ax.setTag("docId", documentoIdOrigen);
    ax.consumir();
}

function onResponseGuardarArchivosXDocumentoID(data) {
    if (!isEmpty(data)) {
        if (data[0]["vout_exito"] == 0) {
            $.Notification.autoHideNotify('warning', 'top right', 'Validación', data[0]["vout_mensaje"]);
        } else {
            $.Notification.autoHideNotify('success', 'top-right', 'Éxito', data[0]["vout_mensaje"]);
            $('#modalVisualizarArcvhivos').modal('hide');
        }
    } else {
        $.Notification.autoHideNotify('info', 'top-right', 'Validación', "No hay cambios para actualizar los datos.");
    }
}

function editarDocumentoServicio(documentoId, movimientoId) {
    loaderShow();
    ax.setAccion("validarDocumentoEdicion");
    ax.addParamTmp("documentoId", documentoId);
    ax.setTag(documentoId);
    ax.consumir();
}

function onResponseValidarDocumentoEdicionServicio(data, documentoId) {
//    console.log(documentoId);
    if (data.exito == 1) {
        loaderShow();
        cargarDiv('#window', 'vistas/com/compraServicio/servicio_form_tablas_edit.php?tipoInterfaz=2&documentoId=' + documentoId);
    } else {
        mostrarAdvertencia(data.mensaje);
    }
}

var documentoId;
function onResponseAnular(data) {
    //SE ANULÓ
    if (!isEmpty(data) && !isEmpty(data.motivoAnulacion)) {
        var dataDocumento = data.documento;
        documentoId = dataDocumento[0]['documento_id'];
        $('#tituloModalAnulacion').html("Anular documento: " + dataDocumento[0]['documento_tipo_descripcion'] + " " + dataDocumento[0]['serie'] + " - " + dataDocumento[0]['numero']);
        $('#txtMotivoAnulacion').val('');
        $('#modalAnulacion').modal('show');
    } else {
        swal("Anulado!", "Documento anulado correctamente.", "success");
        bandera_eliminar = true;
        buscar();
    }
}

function anularDocumentoMensaje() {
    var motivoAnulacion = $('#txtMotivoAnulacion').val();
    motivoAnulacion = motivoAnulacion.trim();

    if (isEmpty(motivoAnulacion)) {
        mostrarAdvertencia('Ingrese motivo de anulación');
    } else {
        loaderShow('#modalAnulacion');
        ax.setAccion("anularDocumentoMensaje");
        ax.addParamTmp("documentoId", documentoId);
        ax.addParamTmp("motivoAnulacion", motivoAnulacion);
        ax.consumir();
    }
}

function onResponseAnularDocumentoMensaje(data) {
    $('#modalAnulacion').modal('hide');
    swal("Anulado!", "Documento anulado correctamente.", "success");
    buscar();
}

function consultarEstadoSunat(documentoId, movimientoId) {
    loaderShow();
    ax.setAccion("consultarEstadoSunat");
    ax.addParamTmp("documentoId", documentoId);
    ax.consumir();
}

function respuestaSunat(text) {
    swal({
        title: 'Consulta SUNAT',
        text: text,
        showConfirmButton: true
    });
}

var incrementoID = 0;
var estadoCheck = 0;
var check = document.getElementById('checklistNuevo');
var checkInsertado = document.getElementById('checklistInsertado');

function crearCheckList() {
    var txtComprobacion = document.getElementById("txtComprobacion");
    var elementoCheckbox = "";
    var elementoLabel = "";
    var elementoIcon = "";

    if (txtComprobacion.value !== "") {

        incrementoID++;
        /*  elementoCheckbox = document.createElement('input');
         elementoCheckbox.setAttribute("type", "checkbox");
         elementoCheckbox.setAttribute("class", "form-check-input");
         elementoCheckbox.setAttribute("style", "margin: 5px");
         elementoCheckbox.id = 'check' + incrementoID;
         elementoCheckbox.setAttribute("onclick", "editarEstadoCheckbox(" + elementoCheckbox.id + ")");
         
         elementoLabel = document.createElement('label');
         //    elementoLabel.setAttribute("type", "text");
         elementoLabel.setAttribute("class", "form-check-label");
         elementoLabel.setAttribute("style", "width: 570px; text-align: left");
         elementoLabel.id = 'label' + incrementoID;
         
         elementoButton = document.createElement('a');
         elementoButton.setAttribute("onclick", "anularEstadoCheckbox()");
         elementoButton.id = 'a' + incrementoID;
         
         //  var button = document.getElementById('a' + incrementoID);
         
         elementoIcon = document.createElement('i');
         elementoIcon.setAttribute("class", "fa fa-close");
         elementoIcon.setAttribute("style", "margin: 5px");
         elementoIcon.id = 'icon' + incrementoID;
         
         check.appendChild(elementoCheckbox);
         check.appendChild(elementoLabel);
         //        button.appendChild(elementoIcon);
         //        check.appendChild(elementoButton);
         check.appendChild(elementoButton);
         
         var button = document.getElementById(elementoButton.id);
         button.appendChild(elementoIcon);
         
         label_incremento = document.getElementById("label" + incrementoID);
         
         label_incremento.innerHTML = txtComprobacion.value;
         
         var br = document.createElement('br');
         check.appendChild(br);*/

        estadoCheck = 0;
        //  ingresarListaComprobacion(txtComprobacion.value, incrementoID, estadoCheck);

        ax.setAccion("insertarListaComprobacion");
        ax.addParamTmp("documento_id", docId);
        ax.addParamTmp("descripcion", txtComprobacion.value);
        // ax.addParamTmp("orden", incrementoID);
        ax.addParamTmp("estado", estadoCheck);
        ax.consumir();

        deleteChild();
    }
    txtComprobacion.value = "";
}

function ingresarListaComprobacion(descripcion, orden, estado) {

    //visualizarDocumento(docId, movId);

    //cargarDataListaComprobacionDocumento();
}

var elementoLabel = "";
var label_incremento = "";

function cargarDataListaComprobacionDocumento(data) {

    if (!isEmpty(data)) {
        for (let i = 0; i < data.length; i++)
        {
            elementoCheckbox = document.createElement('input');
            elementoCheckbox.setAttribute("type", "checkbox");
            elementoCheckbox.setAttribute("class", "form-check-input");
            elementoCheckbox.setAttribute("style", "margin: 5px");
            elementoCheckbox.id = 'check' + data[i]['id'];
            elementoCheckbox.setAttribute("onclick", "editarEstadoCheckbox(" + elementoCheckbox.id + "," + data[i]['id'] + ")");

            if (data[i]['estado'] == 1) {
                elementoCheckbox.checked = true;
            }

            elementoLabel = document.createElement('label');
            elementoLabel.setAttribute("class", "form-check-label");
            elementoLabel.setAttribute("style", "width: 750px; text-align: left");
            elementoLabel.id = 'labelvisualizar' + data[i]['id'];

            elementoButtonAnular = document.createElement('a');
            elementoButtonAnular.setAttribute("onclick", "anularEstadoCheckbox(" + data[i]['id'] + ")");
            elementoButtonAnular.id = 'btnAnular' + data[i]['id'];

            if (i !== 0) {
                elementoButtonArriba = document.createElement('a');
                elementoButtonArriba.setAttribute("onclick", "ordenarArribaEstadoCheckbox(" + data[i]['id'] + "," + data[i - 1]['id'] + "," + data[i]['orden'] + "," + data[i - 1]['orden'] + ")");
                elementoButtonArriba.id = 'btnArriba' + data[i]['id'];
            } else
            {
                elementoButtonArriba = document.createElement('a');
                elementoButtonArriba.setAttribute("style", "visibility:hidden");
                //  elementoButtonArriba.setAttribute("onclick", "ordenarArribaEstadoCheckbox(" + data[i]['id'] + ","+data[i-1]['id'] + ")");
                elementoButtonArriba.id = 'btnArriba' + data[i]['id'];
            }

            if (i !== (data.length - 1)) {
                elementoButtonAbajo = document.createElement('a');
                elementoButtonAbajo.setAttribute("onclick", "ordenarAbajoEstadoCheckbox(" + data[i]['id'] + "," + data[i + 1]['id'] + "," + data[i]['orden'] + "," + data[i + 1]['orden'] + ")");
                elementoButtonAbajo.id = 'btnAbajo' + data[i]['id'];
            } else
            {
                elementoButtonAbajo = document.createElement('a');
                elementoButtonAbajo.setAttribute("style", "visibility:hidden");
                //    elementoButtonAbajo.setAttribute("onclick", "ordenarAbajoEstadoCheckbox(" + data[i]['id'] + "," + data[i]['orden'] + ")");
                elementoButtonAbajo.id = 'btnAbajo' + data[i]['id'];
            }
            elementoIconAnular = document.createElement('i');
            elementoIconAnular.setAttribute("class", "fa fa-close");
            elementoIconAnular.setAttribute("style", "margin: 5px");
            elementoIconAnular.id = 'iconAnular' + data[i]['id'];


            elementoIconArriba = document.createElement('i');
            elementoIconArriba.setAttribute("class", "fa fa-arrow-up");
            elementoIconArriba.setAttribute("style", "margin: 5px");
            elementoIconArriba.id = 'iconArriba' + data[i]['id'];

            elementoIconAbajo = document.createElement('i');
            elementoIconAbajo.setAttribute("class", "fa fa-arrow-down");
            elementoIconAbajo.setAttribute("style", "margin: 5px");
            elementoIconAbajo.id = 'iconAbajo' + data[i]['id'];

            checkInsertado.appendChild(elementoCheckbox);
            checkInsertado.appendChild(elementoLabel);
            checkInsertado.appendChild(elementoButtonArriba);
            checkInsertado.appendChild(elementoButtonAbajo);
            checkInsertado.appendChild(elementoButtonAnular);

            var buttonAnular = document.getElementById('btnAnular' + data[i]['id']);
            buttonAnular.appendChild(elementoIconAnular);

            var br = document.createElement('br');
            br.id = 'br' + data[i]['id'];


            var buttonArriba = document.getElementById('btnArriba' + data[i]['id']);
            buttonArriba.appendChild(elementoIconArriba);

            var buttonAbajo = document.getElementById('btnAbajo' + data[i]['id']);
            buttonAbajo.appendChild(elementoIconAbajo);

            checkInsertado.appendChild(br);
            label_incremento = document.getElementById("labelvisualizar" + data[i]['id']);
            label_incremento.innerHTML = data[i]['descripcion'];

        }
    }

}

function editarEstadoCheckbox(idCheckbox, idLista) {
    if (idCheckbox.checked === true) {
        estado = 1;
    } else {
        estado = 0;
    }

    ax.setAccion("editarEstadoListaComprobacion");
    ax.addParamTmp("documento_id", docId);
    ax.addParamTmp("documento_lista_id", idLista);
    ax.addParamTmp("estado", estado);
    ax.consumir();

    deleteChild();

}

function ordenarArribaEstadoCheckbox(idactual, idsiguiente, ordenactual, ordensiguiente) {
    var label1 = document.getElementById('labelvisualizar' + idactual);
    label1.id = 'labelvisualizar' + idsiguiente;
    var label2 = document.getElementById('labelvisualizar' + idsiguiente);
    label2.id = 'labelvisualizar' + idactual;
    var tlabel1 = label1.cloneNode(true);
    var tlabel2 = label2.cloneNode(true);

    if (!isEmpty(label2)) {

        document.getElementById('checklistInsertado').replaceChild(tlabel2, label1);
        document.getElementById('checklistInsertado').replaceChild(tlabel1, label2);
    }

    var temp_ord;
    var temp_id;

//    temp_id = idactual;
//    idactual = idsiguiente;
//    idsiguiente = temp_id;

    temp_ord = ordenactual;
    ordenactual = ordensiguiente;
    ordensiguiente = temp_ord;

    // var temp;



    ax.setAccion("ordenarArribaEstadoListaComprobacion");
    ax.addParamTmp("documento_id", docId);
    ax.addParamTmp("documento_listaIdActual", idactual);
    ax.addParamTmp("documento_listaIdSiguiente", idsiguiente);
    ax.addParamTmp("ordenActual", ordenactual);
    ax.addParamTmp("ordenSiguiente", ordensiguiente);
    ax.consumir();

    deleteChild();
    // visualizarDocumento(docId, movId);

    // cargarDataListaComprobacionDocumento(datalistaComprobacion);
}

function ordenarAbajoEstadoCheckbox(idactual, idanterior, ordenactual, ordenanterior) {


    var label2 = document.getElementById('labelvisualizar' + idanterior);
    label2.id = 'labelvisualizar' + idactual;

    var label1 = document.getElementById('labelvisualizar' + idactual);
    label1.id = 'labelvisualizar' + idanterior;

    var tlabel1 = label1.cloneNode(true);
    var tlabel2 = label2.cloneNode(true);

    if (!isEmpty(label1)) {

        document.getElementById('checklistInsertado').replaceChild(tlabel2, label1);
        document.getElementById('checklistInsertado').replaceChild(tlabel1, label2);
    }

    var temp_ord;
    var temp_id;

//    temp_id = idactual;
//    idactual = idsiguiente;
//    idsiguiente = temp_id;

    temp_ord = ordenactual;
    ordenactual = ordenanterior;
    ordenanterior = temp_ord;

    // var temp;

//
    ax.setAccion("ordenarArribaEstadoListaComprobacion");
    ax.addParamTmp("documento_id", docId);
    ax.addParamTmp("documento_listaIdActual", idactual);
    ax.addParamTmp("documento_listaIdSiguiente", idanterior);
    ax.addParamTmp("ordenActual", ordenactual);
    ax.addParamTmp("ordenSiguiente", ordenanterior);
    ax.consumir();

    deleteChild();
    //  visualizarDocumento(docId, movId);

}

function anularEstadoCheckbox(idLista) {

    $("#check" + idLista).remove();
    $("#labelvisualizar" + idLista).remove();
    $("#btnAnular" + idLista).remove();
    $("#btnArriba" + idLista).remove();
    $("#btnAbajo" + idLista).remove();
    $("#iconAnular" + idLista).remove();
    $("#iconArriba" + idLista).remove();
    $("#iconAbajo" + idLista).remove();
    $("#br" + idLista).remove();



    ax.setAccion("editarEstadoListaComprobacion");
    ax.addParamTmp("documento_id", docId);
    ax.addParamTmp("documento_lista_id", idLista);
    ax.addParamTmp("estado", 2);
    ax.consumir();

    deleteChild();
    // visualizarDocumento(docId, movId);

}

function deleteChild() {
    var e = document.getElementById("checklistInsertado");
    e.innerHTML = "";
    var f = document.getElementById("checklistNuevo");
    f.innerHTML = "";
}

function redireccionarOT() {
    $('#formularioDetalleDocumento').show();
    $('#datatable2').show();
    $('#lblComentario').show();
    $('#checklistInsertado').show();
    $('#lista_comprobacion').show();
    $('#lblListaComprobacion').show();
    $('#datatable4').hide();
}

function redireccionarHistorial() {


    $('#formularioDetalleDocumento').hide();
    $('#datatable2').hide();
    $('#lblComentario').hide();
    $('#checklistInsertado').hide();
    $('#lista_comprobacion').hide();
    $('#lblListaComprobacion').hide();
    $('#datatable4').show();

}




//function redireccionarVisualizarRelacion() {
//
//   $('#formularioDetalleDocumento').hide();
//    $('#datatable2').hide();
//    $('#lblComentario').hide();
//    $('#checklistInsertado').hide();
//    $('#lista_comprobacion').hide();
//    $('#lblListaComprobacion').hide();
//    $('#datatable4').show();
//
//}

function cargarHistorialDocumento(data) {

    //CABECERA DETALLE

    if (!isEmptyData(data))
    {
        // $('#datatable4').show();
        var tHeadHistorial = $('#theadHistorial');
        tHeadHistorial.empty();

        var html = '';
        html += "<tr>";
        html += "<th style='text-align:center;'>Versión</th>";
        html += "<th style='text-align:center;'>Fecha</th>";
        html += "<th style='text-align:center;'>Usuario</th>";
        html += "<th style='text-align:center;'>Acciones</th> ";
        html += "<th style='text-align:center;'>Valor</th>";
        html += "</tr>";
        tHeadHistorial.append(html);

        //CUERPO DETALLE
        var tBodyHistorial = $('#tbodyHistorial');
        tBodyHistorial.empty();

        $.each(data, function (index, item) {
            html = "";
            html += "<tr>";
            html += "<td style='text-align:center;'>" + (!isEmpty(item.codigo_version) ? item.codigo_version : "") + "</td>";
            html += "<td style='text-align:center;'>" + item.fecha_creacion + "</td>";
            html += "<td >" + item.usuario + "</td>";
            html += "<td>" + item.descripcion + "</td> ";
            html += "<td style='text-align:center;'>";
            if (item.tipo == 1) {
                html += "<div id='vHistorial_" + item.id + "'></div>";
            } else {
                html += '<a onclick="visualizarInformacionHistorial(' + item.id + ')" title="Visualizar"><b><i class="fa fa-eye" style="color:#1ca8dd"></i></a>'
            }
            html += "</td>";
            html += "</tr>";
            tBodyHistorial.append(html);
            if (item.tipo == 1) {
                new JsonEditor('#vHistorial_' + item.id, JSON.parse(item.valor), {defaultCollapsed: true});
            }
        });

    } else
    {
        var table = $('#datatable4').DataTable();
        table.clear().draw();
    }

}

function visualizarInformacionHistorial(id) {
    loaderShow();
    ax.setAccion("obtenerHistorialDocumento");
    ax.addParamTmp("id", id);
    ax.consumir();
}


function generarExcelLiquidacion(documentoId, movimientoId) {
    loaderShow();
    ax.setAccion("generarExcelLiquidacion");
    ax.addParamTmp("documento_id", documentoId);
    ax.consumir();
}

function generarExcelCotizacion(documentoId, movimientoId) {
    loaderShow();
    ax.setAccion("generarExcelCotizacion");
    ax.addParamTmp("documento_id", documentoId);
    ax.consumir();
}


function reenviar(id, documentoTipo) {
    swal({
        title: "Estás seguro?",
        text: "Se reenviará el documento a SUNAT.",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#33b86c",
        confirmButtonText: "Si, enviar!",
        cancelButtonColor: '#d33',
        cancelButtonText: "No, cancelar !",
//        closeOnConfirm: false
    }, function (isConfirm) {
        if (isConfirm) {
            loaderShow();
            ax.setAccion("reenviarComprobante");
            ax.addParamTmp("id", id);
            ax.addParamTmp("documento_tipo_id", documentoTipo);
            ax.addParamTmp("estado", 2);
            ax.consumir();
        }
    });

}
function onResponseReenviarComprobante(data) {
    var dataDocElec = data.respDocElectronico;
    var titulo = '';
    //CORRECTO
    if (dataDocElec.tipoMensaje == 1 || dataDocElec.tipoMensaje == 0) {
        mostrarOk(dataDocElec.mensaje);
        if (isEmpty(dataDocElec.titulo)) {
            titulo = '';
        } else {
            titulo = dataDocElec.titulo;
        }
        swal({
            title: "Reenvio correcto" + titulo,
            text: dataDocElec.mensaje,
            type: "success",
            html: true,
            showCancelButton: false,
            confirmButtonColor: "#33b86c",
            confirmButtonText: "Aceptar",
            closeOnConfirm: true,
            closeOnCancel: true
        }, function (isConfirm) {
            if (isConfirm) {
                buscarDesplegable();
                if (!isEmpty(dataDocElec.urlPDF)) {
                    //window.open(URL_BASE + 'pdf2.php?url_pdf=' + dataDocElec.urlPDF + '&nombre_pdf=' + dataDocElec.nombrePDF);
                    window.open(dataDocElec.urlPDF);
                }
            }
        });
    }
    //ERROR CONTROLADO SUNAT NO VA A REGISTRAR - WARNING EXCEPTION EN NEGOCIO - PARA NEGAR COMMIT
    //ERROR DESCONOCIDO
    if (dataDocElec.tipoMensaje == 2 || dataDocElec.tipoMensaje == 3 || dataDocElec.tipoMensaje == 4) {
        var mensaje = dataDocElec.mensaje;
        if (dataDocElec.tipoMensaje == 4) {
            mensaje += "<br><br> Se registró en el sistema, pero fue rechazada por SUNAT.";
        } else {
            mensaje += "<br><br> Se registró en el sistema, posteriormente se intentará registrar en SUNAT."
        }
        swal({
            title: "Error desconocido",
            text: mensaje,
            type: "warning",
            html: true,
            showCancelButton: false,
            confirmButtonColor: "#33b86c",
            confirmButtonText: "Aceptar",
            closeOnConfirm: true,
            closeOnCancel: true
        }, function (isConfirm) {
            if (isConfirm) {
                buscarDesplegable();
                if (!isEmpty(dataDocElec.urlPDF)) {
                    window.open(URL_BASE + 'pdf2.php?url_pdf=' + dataDocElec.urlPDF + '&nombre_pdf=' + dataDocElec.nombrePDF);
                }
            }
        });
    }
}

function aprobarCotizacion(id, documentoTipo) {
    swal({
        title: "Estás seguro?",
        text: "Se actualizará el estado de este documento.",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#33b86c",
        confirmButtonText: "Si, enviar!",
        cancelButtonColor: '#d33',
        cancelButtonText: "No, cancelar !",
//        closeOnConfirm: false
    }, function (isConfirm) {
        if (isConfirm) {
            loaderShow();
            ax.setAccion("aprobarCotizacion");
            ax.addParamTmp("id", id);
            ax.consumir();
        }
    });

}

function onResponseAprobarCotizacion(data) {
    let tituloSwal = "";
    let mensajeSwal = "";
    let tipoSwal = "warning";

    if (!isEmpty(data)) {
        if (data[0]['vout_exito'] == 1) {
            tituloSwal = "Documento aprobado";
            tipoSwal = "success";
        } else {
            tituloSwal = "Error";
            tipoSwal = "warning";
        }
        mensajeSwal = data[0]['vout_mensaje'];
    } else {
        tituloSwal = "Error";
        mensajeSwal = "No se obtuvo una respuesta del servidor";
    }

    swal({
        title: tituloSwal,
        text: mensajeSwal,
        type: tipoSwal,
        html: true,
        showCancelButton: false,
        confirmButtonColor: "#33b86c",
        confirmButtonText: "Aceptar",
        closeOnConfirm: true,
        closeOnCancel: true
    }, function (isConfirm) {
        if (isConfirm) {
            buscarDesplegable();
        }
    });
}


/************************************************************************ DISTIRIBUCIÓN CONTABLE *****************************************************/


function cargarDistribucionDocumento(data) {
    $('#datatableDistribucion2').show();

    if (!isEmptyData(data))
    {
        //CABECERA DETALLE
        var tHeadDetalle = $('#theadDetalleCabeceraDistribucion');
        tHeadDetalle.empty();

        var html = '';
        html += "<tr>";
        html += "<th style='text-align:center;'>#</th>";
        html += "<th style='text-align:center;'>Cuenta Contable</th>";
        if (!isEmpty(data[0]['centro_costo_id'])) {
            html += "<th style='text-align:center;'>Centro Costo</th>";
        }
        html += "<th style='text-align:center;'>Porcentaje(%)</th>";
        html += "<th style='text-align:center;'>Monto</th> ";

        html += "</tr>";
        tHeadDetalle.append(html);


        //CUERPO DETALLE
        var tBodyDetalle = $('#tbodyDetalleDistribucion');
        tBodyDetalle.empty();

        html = '';
        $.each(data, function (index, item) {
            let cuenta_descripcion = (!isEmpty(item.plan_contable_descripcion) ? item.plan_contable_descripcion : "");
            html += "<tr>";
            html += "<td style='text-align:center;'>" + item.linea + "</td>";
            html += "<td style='text-align:center;'>" + cuenta_descripcion + "</td>";
            if (!isEmpty(item.centro_costo_descripcion)) {
                html += "<td style='text-align:center;'>" + item.centro_costo_descripcion + "</td>";
            }
            html += "<td style='text-align:right;'>" + formatearNumero(item.porcentaje) + "</td> ";
            html += "<td style='text-align:right;'>" + formatearNumero(item.monto) + "</td> ";
            html += "</tr>";
        });

        tBodyDetalle.append(html);
    } else
    {
        var table = $('#datatableDistribucion2').DataTable();
        table.clear().draw();
    }
}



function cargaVoucherContable(data) {
//    
    $('#datatableVocuher').show();

    if (!isEmptyData(data)) {
        //CABECERA DETALLE
        var tHeadDetalle = $('#theadDetalleCabeceraVocuher');
        tHeadDetalle.empty();

        var html = '';
        html += "<tr>";
        html += "<th style='text-align:center;'>#</th>";
        html += "<th style='text-align:center;'>Cuenta contable</th>";
        html += "<th style='text-align:center;'>Fecha contable</th>";
        html += "<th style='text-align:center;'>Monto dólares</th>";
        html += "<th style='text-align:center;'>Deber</th>";
        html += "<th style='text-align:center;'>Haber</th> ";
        html += "</tr>";
        tHeadDetalle.append(html);


        //CUERPO DETALLE
        var tBodyDetalle = $('#tbodyDetalleVocuher');
        tBodyDetalle.empty();

        html = '';
        var sumaDeber = 0;
        var sumaHaber = 0;
        $.each(data, function (index, item) {
            let monto_dolares = 0;
            if (item.moneda_id == 4) {
                monto_dolares = (item.debe_dolares * 1) + (item.haber_dolares * 1);
            }
            html += "<tr>";
            html += "<td style='text-align:center;'>" + (index + 1) + "</td>";
            html += "<td style='text-align:left;'>" + item.plan_contable_codigo + " | " + item.plan_contable_descripcion + "</td>";
            html += "<td style='text-align:center;'>" + datex.formatoImaginaDG(item.fecha_contabilizacion) + "</td>";
            html += "<td style='text-align:right;'>" + formatearNumero(monto_dolares) + "</td> ";
            html += "<td style='text-align:right;'>" + formatearNumero(item.debe_soles) + "</td> ";
            html += "<td style='text-align:right;'>" + formatearNumero(item.haber_soles) + "</td> ";
            html += "</tr>";

            sumaDeber += item.debe_soles * 1;
            sumaHaber += item.haber_soles * 1;
        });

        html += "<tr><td style='text-align:right;' colspan='4'><b>Suma de montos</b></td>\n\
                    <td style='text-align:right;'><b>" + formatearNumero(sumaDeber) + "</b></td>\n\
                    <td style='text-align:right;'><b>" + formatearNumero(sumaHaber) + "</b></td>\n\
                </tr>";

        tBodyDetalle.append(html);
    } else {
        var table = $('#datatableVocuher').DataTable();
        table.clear().draw();
    }
}


function fechasActuales(){
    var fechaActual = new Date();
    
    // Formatear la fecha en formato dd/mm/yyyy
    var dia = ('0' + fechaActual.getDate()).slice(-2);
    var mes = ('0' + (fechaActual.getMonth() + 1)).slice(-2);
    var anio = fechaActual.getFullYear();
    var fechaFormateada = dia + '/' + mes + '/' + anio;

    // Colocar la fecha actual en el campo "finFechaEmision"
    $('#finFechaEmision').val(fechaFormateada);

    // Calcular la fecha de hace un mes
    fechaActual.setMonth(fechaActual.getMonth() - 1);
    var diaInicio = ('0' + fechaActual.getDate()).slice(-2);
    var mesInicio = ('0' + (fechaActual.getMonth() + 1)).slice(-2);
    var anioInicio = fechaActual.getFullYear();
    var fechaInicioFormateada = diaInicio + '/' + mesInicio + '/' + anioInicio;

    // Colocar la fecha de hace un mes en el campo "inicioFechaEmision"
    $('#inicioFechaEmision').val(fechaInicioFormateada);
}

function verImagenPdf(index){
    var nombreAdjunto = $("#nombreAdjunto_" + index).val();
    var partesNombreAdjunto = nombreAdjunto.split('.');
    var newWindow = window.open();

    if(partesNombreAdjunto[1] == "pdf"){
        newWindow.document.write('<html><body>');
        newWindow.document.write('<embed width="100%" height="100%" src="' + URL_BASE + "util/uploads/documentoAdjunto/" + nombreAdjunto + '" type="application/pdf">');
        newWindow.document.write('</body></html>');
        newWindow.document.close();
    }else{
        newWindow.document.write('<html><body>');
        newWindow.document.write('<img src="' + URL_BASE + "util/uploads/imagenAdjunto/" + nombreAdjunto + '">'); 
        newWindow.document.write('</body></html>');
        newWindow.document.close();
    }
}

function imprimirOrdenCompra(documentoId, documentoTipo){
    const link = document.createElement('a');
    link.href = URL_BASE + "vistas/com/compraServicio/compra_servicio_pdf.php?id=" + documentoId;
    link.target = '_blank';
    link.click();
}

function cargarDataArchivoAdjuntos(data) {
    $("#dataListArchivosAdjuntos").empty();
    var cuerpo_total = "";
    var cuerpo = "";
    var cabeza = "<table id='datatableArchivosAdjuntos' class='table table-striped table-bordered'>"
            + "<thead>"
            + "<tr>"
            + "<th style='text-align:center; vertical-align: middle; width:20%'>#</th>"
            + "<th style='text-align:center; vertical-align: middle;'>Nombre Archivo</th>"
            + "<th style='text-align:center; vertical-align: middle; width:15%'>Acciones</th>"
            + "</tr>"
            + "</thead>";
    if (!isEmpty(data)) {
        $("#liDataArchivoAdjuntos").show();
        $.each(data, function (index, item) {
            // if (!item.id.match(/t/g)) {
            //     lstDocumentoArchivos[index]["data"] = "util/uploads/documentoAdjunto/" + item.nombre;
            // }


            cuerpo = "<tr>"
                    + "<td style='text-align:center;'>" + (index + 1) + "</td>"
                    + "<td style='text-align:center;'>" + item.archivo + "</td>";

            cuerpo += "<td style='text-align:center;'>"
                    + "<a href='" + "util/uploads/documentoAdjunto/"+ item.nombre + "' download='" + item.archivo + "' target='_blank'><i class='fa fa-cloud-download' style='color:#1ca8dd;'></i></a>&nbsp;\n"
                    + "</td>"
                    + "</tr>";
            cuerpo_total += cuerpo;
        });
    }
    var pie = '</table>';
    var html = cabeza + cuerpo_total + pie;
    $("#dataListArchivosAdjuntos").append(html);
    $("#datatableArchivosAdjuntos").DataTable();
}

function imprimirOrdenServicio(documentoId, documentoTipo){
    const link = document.createElement('a');
    link.href = URL_BASE + "vistas/com/compraServicio/compra_servicio_pdf.php?id=" + documentoId;
    link.target = '_blank';
    link.click();
}

function devolverDosDecimales(num) {
        return redondearNumero(num).toFixed(2);
}