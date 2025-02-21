var total = 0;
var importe_pagado = 0;
var importe_pendiente = 0;
var valoresBusquedaReporteCotizacion = [{persona: "", serie: "", numero: "", estado: "", fechaEmision: "", fechaVencimiento: "", bandera: "0"}];//bandera 0 es balance

$(document).ready(function () {
//    loaderShow();
    $('[data-toggle="popover"]').popover({html: true}).popover();
    cargarTitulo("titulo", "");
//    select2.iniciar();

    iniciarDataPicker();
    modificarAnchoTabla('datatableReporteCotizacion');

    ax.setSuccess("onResponseReporteCotizacion");
    obtenerConfiguracionesInicialesCotizacion();

    select2.iniciarElemento("cboEstado");
    buscarReporteCotizacion(1);
});

function onResponseReporteCotizacion(response) {

    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obtenerConfiguracionesInicialesCotizacion':
                onResponseObtenerConfiguracionesInicialesCotizacion(response.data);
                break;
            case 'obtenerDataReporteCotizacion':
                onResponseObtenerDataReporteOrdenTrabajo(response.data);
                loaderClose();
                break;
            case 'verDetallePorCotizacion':
                onResponseVerDetallePorCotizacion(response.data);
                loaderClose();
                break;
            case 'exportarReporteCotizacion':
                onResponseExportarReporteCotizacion(response.data);
                loaderClose();
        }
    } else {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obtenerReporteBalanceExcel':
                loaderClose();
                break;
        }
    }
}

function cargarDetalleDocumento(data) {
    
    if (!isEmptyData(data))
    {
        $.each(data, function (index, item) {
            data[index]["cantidad"] = formatearCantidad(data[index]["cantidad"]);
            data[index]["precioUnitario"] = formatearNumero(data[index]["precioUnitario"]);
            data[index]["importe"] = formatearNumero(data[index]["importe"]);
        });
        $('#datatable2').dataTable({
//            "scrollX": true,
            "order": [[0, "desc"]],
            "data": data,
            "columns": [
                {"data": "cantidad", "sClass": "alignRight"},
                {"data": "unidadMedida"},
                {"data": "descripcion"},
                {"data": "precioUnitario", "sClass": "alignRight"},
                {"data": "importe", "sClass": "alignRight"}
            ],
            "destroy": true
        });
    }
    else
    {
        var table = $('#datatable2').DataTable();
        table.clear().draw();
    }
}
function onResponseVerDetallePorCotizacion(data){
    $('[data-toggle="popover"]').popover('hide');
    cargarDataDocumento(data.dataDocumento);
    cargarDataComentarioDocumento(data.comentarioDocumento);
    cargarDetalleDocumento(data.detalleDocumento);
    $('#modalDetalleDocumento').modal('show');
}
function cargarDataComentarioDocumento(data) {
    $('#txtComentario').val(data[0]['comentario_documemto']);
    $('#txtDescripcion').val(data[0]['descripcion_documemto']);
}
function appendFormDetalle(html) {
    $("#formularioDetalleDocumento").append(html);
}
function cargarDataDocumento(data){
    $("#formularioDetalleDocumento").empty();
    //$("#formularioDetalleDocumento").css("height", 75 * data.length);


    if (!isEmpty(data)) {
        let encontradoUno = false;
        $('#nombreDocumentoTipo').html(data[0]['nombre_documento']);
        // Mostraremos la data en filas de dos columnas
        var estottus =  data[23]['valor'];
        $.each(data, function (index, item) {
            appendFormDetalle('</div>');
            appendFormDetalle('<div class="row">');
            if (item.documento_tipo_id == 3043 && estottus == 0) {
                encontradoUno = true;  // Marcar que se ha encontrado un 1
                return true;  // Continuar con la siguiente iteración sin mostrar el 1
            }
            if (encontradoUno) {
                return false;  // Terminar el bucle al encontrar un 1 y no mostrar más
            }
            var html = '<div class="form-group col-md-12"><div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">' +
                    '<label>' + item.descripcion + '</label>' +
                    '</div><div class="col-lg-8 col-md-8 col-sm-6 col-xs-6">';

            var valor = quitarNULL(item.valor);

            if (!isEmpty(valor))
            {
                switch (parseInt(item.tipo)) {
                    case 1:
                        valor = formatearCantidad(valor);
                        break;
                    case 3:
                        valor = fechaArmada(valor);
                        break;
                    case 9:
                    case 10:
                    case 11:
                        valor = fechaArmada(valor);
                        break;
                    case 14:
                    case 15:
                    case 16:
                    case 19:
                        valor = formatearNumero(valor);
                        break;
                }
            }

            html += '' + valor + '';

            html += '</div></div>';
            appendFormDetalle(html);
        });
        appendFormDetalle('</div>');
    }
}
function exportarReporteCotizacion() {
    loaderShow();
    var cadena;
    cadena = obtenerDatosBusqueda();

    if (!isEmpty(cadena) && cadena !== 0)
    {
        $('#idPopover').attr("data-content", cadena);
    }
    $('[data-toggle="popover"]').popover('show');
    banderaBuscar = 1;

    ax.setAccion("exportarReporteCotizacion");
    ax.addParamTmp("criterios", valoresBusquedaReporteCotizacion);
    ax.consumir();

    if (colapsa === 1)
        colapsarBuscador();
}


function obtenerConfiguracionesInicialesCotizacion()
{
    ax.setAccion("obtenerConfiguracionesInicialesCotizacion");
    ax.consumir();
}

function onResponseExportarReporteCotizacion(data) {
    window.open(URL_BASE + data);
}

function onResponseObtenerConfiguracionesInicialesCotizacion(data) {

    var string = '<option selected value="-1">Seleccionar una persona</option>';
    if (!isEmpty(data.persona)) {

        $.each(data.persona, function (indexPersona, itemPersona) {
            string += '<option value="' + itemPersona.id + '">' + itemPersona.nombre + ' | ' + itemPersona.codigo_identificacion + '</option>';
        });
        $('#cboPersona').append(string);
        select2.asignarValor('cboPersona', "-1");
        select2.asignarValor('cboEstado', "-1");

    }

    if (!isEmpty(data.fecha_primer_documento[0]['primera_fecha']))
    {
        $('#inicioFechaEmision').val(formatearFechaBDCadena(data.fecha_primer_documento[0]['primera_fecha']));
        if (!isEmpty(data.fecha_primer_documento[0]['fecha_actual']))
        {
            $('#finFechaEmision').val(formatearFechaBDCadena(data.fecha_primer_documento[0]['fecha_actual']));
        }
    }

    loaderClose();
}

function cargarDatosBusquedaReporteCotizacion()
{
    var personaId = select2.obtenerValor('cboPersona');
    var serie = $('#txtSerie').val();
    var numero = $('#txtNumero').val();
    var estadoId = select2.obtenerValor('cboEstado');
    var fechaEmisionInicio = $('#inicioFechaEmision').val();
    var fechaEmisionFin = $('#finFechaEmision').val();

    valoresBusquedaReporteCotizacion[0].persona = personaId;
    valoresBusquedaReporteCotizacion[0].serie = serie;
    valoresBusquedaReporteCotizacion[0].numero = numero;
    valoresBusquedaReporteCotizacion[0].estado = estadoId;
    valoresBusquedaReporteCotizacion[0].fechaEmisionDesde = fechaEmisionInicio;
    valoresBusquedaReporteCotizacion[0].fechaEmisionHasta = fechaEmisionFin;
//    getDataTableReporteOperaciones();
}

function obtenerDatosBusqueda()
{
    var cadena = "";
    cargarDatosBusquedaReporteCotizacion();

    if (!isEmpty(valoresBusquedaReporteCotizacion[0].persona))
    {
        cadena += StringNegrita("Persona: ");
        cadena += select2.obtenerText('cboPersona');
        cadena += "<br>";
    }

    if (!isEmpty(valoresBusquedaReporteCotizacion[0].fechaEmisionDesde) || !isEmpty(valoresBusquedaReporteCotizacion[0].fechaEmisionHasta))
    {
        cadena += StringNegrita("Fecha emisión: ");
        cadena += valoresBusquedaReporteCotizacion[0].fechaEmisionDesde + " - " + valoresBusquedaReporteCotizacion[0].fechaEmisionHasta;
        cadena += "<br>";
    }

    if (!isEmpty(valoresBusquedaReporteCotizacion[0].estado))
    {
        cadena += StringNegrita("Estado: ");
        cadena += select2.obtenerText('cboEstado');
        cadena += "<br>";
    }
    return cadena;
}

function buscarReporteCotizacion(colapsa)
{
    loaderShow();
    var cadena;
    cadena = obtenerDatosBusqueda();

    if (!isEmpty(cadena) && cadena !== 0)
    {
        $('#idPopover').attr("data-content", cadena);
    }
    $('[data-toggle="popover"]').popover('show');
    banderaBuscar = 1;

    obtenerDataBusquedaCotizacion();

    if (colapsa === 1)
        colapsarBuscador();
}

function obtenerDataBusquedaCotizacion()
{
    ax.setAccion("obtenerDataReporteCotizacion");
    ax.addParamTmp("criterios", valoresBusquedaReporteCotizacion);
    ax.consumir();

}

function onResponseObtenerDataReporteOrdenTrabajo(data)
{
    if (!isEmptyData(data))
    {
        $('#datatableReporteCotizacion').dataTable({
//            "processing": true,
//            "serverSide": true,
            "scrollX": true,
            "order": [[0, "desc"]],
            "data": data,
            "autoWidth": true,
            "columns": [
                {"data": "fecha_emision", "class": "alignCenter", "width": "50px"}, //, width: "15%"
                {"data": "persona_nombre", "width": "255px"},
                {"data": "serie_numero", "class": "alignCenter"},
                {"data": "moneda", "class": "alignCenter"},
                {"data": "total", "class": "alignRight"},
                {"data": "estado", "class": "alignCenter"},
                {"data": "documento_id", "class": "alignCenter"}
            ],

            columnDefs: [
                {
                    "render": function (data, type, row) {
                        if (parseFloat(data).formatMoney(2, '.', ',') == '0.00') {
                            return '-';
                        } else {
                            return parseFloat(data).formatMoney(2, '.', ',');
                        }
                    },
                    "targets": [4]
                },
                {
                    "render": function (data, type, row) {
                        return (isEmpty(data)) ? '' : data.replace(" 00:00:00", "");
                    },
                    "targets": [0]
                },
                {
                    "render": function (data, type, row) {
                        return '<a onclick="verDetallePorCotizacion(' + row['documento_id'] + ', ' + row['movimiento_id'] + ')"><b><i class="fa fa-eye"" style="color:b"></i><b></a>';
                    },
                    "targets": [6]
                }
            ],
            "destroy": true
        });
    } else
    {
        var table = $('#datatableReporteCotizacion').DataTable();
        table.clear().draw();
    }
}

// ver detalle en modal
function verDetallePorCotizacion(documentoId, movimientoId)
{
    loaderShow();
    ax.setAccion("verDetallePorCotizacion");
    ax.addParamTmp("documento_id", documentoId);
    ax.addParamTmp("movimiento_id", movimientoId);
    ax.consumir();
}


function imprimir(muestra)
{
    var ficha = document.getElementById(muestra);
    var ventimp = window.open(' ', 'popimpr');
    ventimp.document.write(ficha.innerHTML);
    ventimp.document.close();
    ventimp.print();
    ventimp.close();
}

function loaderBuscar()
{
    actualizandoBusqueda = true;
    loaderShow();
    var estadobuscador = $('#bg-info').attr("aria-expanded");
    if (estadobuscador == "false")
    {
        buscarReporteCotizacion();
    }
    loaderClose();
}


var actualizandoBusqueda = false;

function colapsarBuscador() {
    if (actualizandoBusqueda) {
        actualizandoBusqueda = false;
        return;
    }
    if ($('#bg-info').hasClass('in')) {
        $('#bg-info').attr('aria-expanded', "false");
        $('#bg-info').attr('height', "0px");
        $('#bg-info').removeClass('in');
    } else {
        $('#bg-info').attr('aria-expanded', "false");
        $('#bg-info').removeAttr('height', "0px");
        $('#bg-info').addClass('in');
    }
}
function fechaArmada(valor)
{
    var fecha = separarFecha(valor);

    return fecha.dia + "/" + fecha.mes + "/" + fecha.anio;
}