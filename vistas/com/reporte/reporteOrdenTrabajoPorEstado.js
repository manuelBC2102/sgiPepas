var total = 0;
var importe_pagado = 0;
var importe_pendiente = 0;
let valoresBusquedaReporteOrdenTrabajoPorEstado = [{persona: "", serie: "", numero: "", progreso: "", fechaEmision: "", fechaVencimiento: "", bandera: "0"}];//bandera 0 es balance

$(document).ready(function () {
//    loaderShow();
    $('[data-toggle="popover"]').popover({html: true}).popover();
    cargarTitulo("titulo", "");
//    select2.iniciar();

    iniciarDataPicker();
    modificarAnchoTabla('datatableReporteOrdenTrabajo');

    ax.setSuccess("onResponseReporteOrdenTrabajo");
    obtenerConfiguracionesInicialesOrdenTrabajo();
    buscarReporteOrdenTrabajoPorEstado(1);

});

function onResponseReporteOrdenTrabajo(response) {

    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obtenerConfiguracionesInicialesOrdenTrabajo':
                onResponseObtenerConfiguracionesInicialesOrdenTrabajo(response.data);
                break;
            case 'obtenerDataReporteOrdenTrabajoPorEstado':
                onResponseObtenerDataReporteOrdenTrabajoPorEstado(response.data);
                loaderClose();
                break;
            case 'verDetallePorOrdenTrabajoPorEstado':
                onResponseVerDetallePorOrdenTrabajoPorEstado(response.data);
                loaderClose();
                break;
            case 'exportarReporteOrdenTrabajo':
                onResponseExportarReporteOrdenTrabajo(response.data);
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
function onResponseVerDetallePorOrdenTrabajoPorEstado(data){
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
        $.each(data, function (index, item) {
            appendFormDetalle('</div>');
            appendFormDetalle('<div class="row">');

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
function exportarReporteOrdenTrabajo() {
    loaderShow();
    var cadena;
    cadena = obtenerDatosBusquedaOrdenTrabajoPorEstado();

    if (!isEmpty(cadena) && cadena !== 0)
    {
        $('#idPopover').attr("data-content", cadena);
    }
    $('[data-toggle="popover"]').popover('show');
    banderaBuscar = 1;

    ax.setAccion("exportarReporteOrdenTrabajo");
    ax.addParamTmp("criterios", valoresBusquedaReporteOrdenTrabajoPorEstado);
    ax.consumir();

    if (colapsa === 1)
        colapsarBuscador();
}


function obtenerConfiguracionesInicialesOrdenTrabajo()
{
    ax.setAccion("obtenerConfiguracionesInicialesOrdenTrabajo");
    ax.consumir();
}

function onResponseExportarReporteOrdenTrabajo(data) {
    window.open(URL_BASE + data);
}

function onResponseObtenerConfiguracionesInicialesOrdenTrabajo(data) {
    var string = '<option selected value="-1">Seleccionar una persona</option>';
    if (!isEmpty(data.persona)) {

        $.each(data.persona, function (indexPersona, itemPersona) {
            string += '<option value="' + itemPersona.id + '">' + itemPersona.nombre + ' | ' + itemPersona.codigo_identificacion + '</option>';
        });
        $('#cboPersona').append(string);
        select2.asignarValor('cboPersona', "-1");
    }
    var stringProgreso = '<option selected value="-1">Seleccionar una progreso</option>';

    if (!isEmpty(data.progreso)) {

        $.each(data.progreso, function (indexProgreso, itemProgreso) {
            stringProgreso += '<option value="' + itemProgreso.id + '">' + itemProgreso.descripcion + '</option>';
        });
        $('#cboProgreso').append(stringProgreso);
        select2.asignarValor('cboProgreso', "-1");
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


function cargarDatosBusquedaReporteOrdenTrabajo()
{
    var personaId = select2.obtenerValor('cboPersona');
    var serie = $('#txtSerie').val();
    var numero = $('#txtNumero').val();
    var progresoId = select2.obtenerValor('cboProgreso');
    var fechaEmisionInicio = $('#inicioFechaEmision').val();
    var fechaEmisionFin = $('#finFechaEmision').val();

    valoresBusquedaReporteOrdenTrabajoPorEstado[0].persona = personaId;
    valoresBusquedaReporteOrdenTrabajoPorEstado[0].serie = serie;
    valoresBusquedaReporteOrdenTrabajoPorEstado[0].numero = numero;
    valoresBusquedaReporteOrdenTrabajoPorEstado[0].progreso = progresoId;
    valoresBusquedaReporteOrdenTrabajoPorEstado[0].fechaEmisionDesde = fechaEmisionInicio;
    valoresBusquedaReporteOrdenTrabajoPorEstado[0].fechaEmisionHasta = fechaEmisionFin;
//    getDataTableReporteOperaciones();
}

function obtenerDatosBusquedaOrdenTrabajoPorEstado()
{
    var cadena = "";
    cargarDatosBusquedaReporteOrdenTrabajo();

    if (!isEmpty(valoresBusquedaReporteOrdenTrabajoPorEstado[0].persona))
    {
        cadena += StringNegrita("Persona: ");
        cadena += select2.obtenerText('cboPersona');
        cadena += "<br>";
    }

    if (!isEmpty(valoresBusquedaReporteOrdenTrabajoPorEstado[0].fechaEmisionDesde) || !isEmpty(valoresBusquedaReporteOrdenTrabajoPorEstado[0].fechaEmisionHasta))
    {
        cadena += StringNegrita("Fecha emisión: ");
        cadena += valoresBusquedaReporteOrdenTrabajoPorEstado[0].fechaEmisionDesde + " - " + valoresBusquedaReporteOrdenTrabajoPorEstado[0].fechaEmisionHasta;
        cadena += "<br>";
    }

    if (!isEmpty(valoresBusquedaReporteOrdenTrabajoPorEstado[0].progreso))
    {
        cadena += StringNegrita("Progreso: ");
        cadena += select2.obtenerText('cboProgreso');
        cadena += "<br>";
    }
    return cadena;
}

function buscarReporteOrdenTrabajoPorEstado(colapsa)
{
    loaderShow();
    var cadena;
    cadena = obtenerDatosBusquedaOrdenTrabajoPorEstado();

    if (!isEmpty(cadena) && cadena !== 0)
    {
        $('#idPopover').attr("data-content", cadena);
    }
    $('[data-toggle="popover"]').popover('show');
    banderaBuscar = 1;

    obtenerDataBusquedaOrdenTrabajoPorEstado();

    if (colapsa === 1)
        colapsarBuscador();
}

function obtenerDataBusquedaOrdenTrabajoPorEstado()
{
    ax.setAccion("obtenerDataReporteOrdenTrabajoPorEstado");
    ax.addParamTmp("criterios", valoresBusquedaReporteOrdenTrabajoPorEstado);
    ax.consumir();

}

function onResponseObtenerDataReporteOrdenTrabajoPorEstado(data)
{
    if (!isEmptyData(data))
    {
        $('#datatableReporteOrdenTrabajo').dataTable({
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
                {"data": "progreso", "class": "alignCenter"},
                {"data": "porcentaje", "class": "alignCenter"},
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
                        return '<div class="progress" title="'+formatearNumeroPorCantidadDecimales(row['porcentaje'],2)+'"><div class="progress-bar" role="progressbar" style="width: '+formatearNumeroPorCantidadDecimales(row['porcentaje'],2)+'%;" aria-valuenow="'+row['porcentaje']+'" aria-valuemin="0" aria-valuemax="100">'+formatearNumeroPorCantidadDecimales(row['porcentaje'],2)+'%</div></div>';
                    },
                    "targets": [6]
                },
                {
                    "render": function (data, type, row) {
                        return '<a onclick="verDetallePorOrdenTrabajoPorEstado(' + row['documento_id'] + ', ' + row['movimiento_id'] + ')"><b><i class="fa fa-eye"" style="color:b"></i><b></a>';
                    },
                    "targets": [7]
                }
            ],
            "destroy": true
        });
    } else
    {
        var table = $('#datatableReporteOrdenTrabajo').DataTable();
        table.clear().draw();
    }
}

// ver detalle en modal
function verDetallePorOrdenTrabajoPorEstado(documentoId, movimientoId)
{
    loaderShow();
    ax.setAccion("verDetallePorOrdenTrabajoPorEstado");
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
        buscarReporteOrdenTrabajoPorEstado();
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