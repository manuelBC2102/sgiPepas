var total = 0;
var importe_pagado = 0;
var importe_pendiente = 0;
let valoresBusquedaReporteConsolidadoCotizacion = [{persona: "", serie: "", numero: "", segun: "", fechaEmision: "", fechaVencimiento: "", bandera: "0"}];//bandera 0 es balance

$(document).ready(function () {
//    loaderShow();
    $('[data-toggle="popover"]').popover({html: true}).popover();
    cargarTitulo("titulo", "");
//    select2.iniciar();

    iniciarDataPicker();
    modificarAnchoTabla('datatableReporteConsolidadoCotizacion');

    ax.setSuccess("onResponseReporteConsolidadoCotizacion");
    obtenerConfiguracionesInicialesConsolidadoCotizacion();
    buscarReporteConsolidadoCotizacion(1);

});

function onResponseReporteConsolidadoCotizacion(response) {

    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obtenerConfiguracionesInicialesConsolidadoCotizacion':
                onResponseObtenerConfiguracionesInicialesConsolidadoCotizacion(response.data);
                break;
            case 'obtenerDataReporteConsolidadoCotizacion':
                onResponseObtenerDataReporteConsolidadoCotizacion(response.data);
                loaderClose();
                break;
            case 'exportarReporteConsolidadoCotizacion':
                onResponseExportarReporteConsolidadoCotizacion(response.data);
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

function exportarReporteConsolidadoCotizacion() {
    loaderShow();
    var cadena;
    cadena = obtenerDatosBusquedaConsolidadoCotizacion();

    if (!isEmpty(cadena) && cadena !== 0)
    {
        $('#idPopover').attr("data-content", cadena);
    }
    $('[data-toggle="popover"]').popover('show');
    banderaBuscar = 1;

    ax.setAccion("exportarReporteConsolidadoCotizacion");
    ax.addParamTmp("criterios", valoresBusquedaReporteConsolidadoCotizacion);
    ax.consumir();

    if (colapsa === 1)
        colapsarBuscador();
}


function obtenerConfiguracionesInicialesConsolidadoCotizacion()
{
    ax.setAccion("obtenerConfiguracionesInicialesConsolidadoCotizacion");
    ax.consumir();
}

function onResponseExportarReporteConsolidadoCotizacion(data) {
    window.open(URL_BASE + data);
}

function onResponseObtenerConfiguracionesInicialesConsolidadoCotizacion(data) {
    var string = '<option selected value="-1">Seleccionar una persona</option>';
    if (!isEmpty(data.persona)) {

        $.each(data.persona, function (indexPersona, itemPersona) {
            string += '<option value="' + itemPersona.id + '">' + itemPersona.nombre + ' | ' + itemPersona.codigo_identificacion + '</option>';
        });
        $('#cboPersona').append(string);
        select2.asignarValor('cboPersona', "-1");
    }
    var stringSegun = '<option selected value="-1">Seleccionar Segun</option>';

    if (!isEmpty(data.segun)) {

        $.each(data.segun, function (indexSegun, itemSegun) {
            stringSegun += '<option value="' + itemSegun.id + '">' + itemSegun.descripcion + '</option>';
        });
        $('#cboSegun').append(stringSegun);
        select2.asignarValor('cboSegun', "-1");
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


function cargarDatosBusquedaReporteConsolidadoCotizacion()
{
    var personaId = select2.obtenerValor('cboPersona');
    var serie = $('#txtSerie').val();
    var numero = $('#txtNumero').val();
    var segunId = select2.obtenerValor('cboSegun');
    var fechaEmisionInicio = $('#inicioFechaEmision').val();
    var fechaEmisionFin = $('#finFechaEmision').val();

    valoresBusquedaReporteConsolidadoCotizacion[0].persona = personaId;
    valoresBusquedaReporteConsolidadoCotizacion[0].serie = serie;
    valoresBusquedaReporteConsolidadoCotizacion[0].numero = numero;
    valoresBusquedaReporteConsolidadoCotizacion[0].segun = segunId;
    valoresBusquedaReporteConsolidadoCotizacion[0].fechaEmisionDesde = fechaEmisionInicio;
    valoresBusquedaReporteConsolidadoCotizacion[0].fechaEmisionHasta = fechaEmisionFin;
//    getDataTableReporteOperaciones();
}

function obtenerDatosBusquedaConsolidadoCotizacion()
{
    var cadena = "";
    cargarDatosBusquedaReporteConsolidadoCotizacion();

    if (!isEmpty(valoresBusquedaReporteConsolidadoCotizacion[0].persona))
    {
        cadena += StringNegrita("Persona: ");
        cadena += select2.obtenerText('cboPersona');
        cadena += "<br>";
    }

    if (!isEmpty(valoresBusquedaReporteConsolidadoCotizacion[0].fechaEmisionDesde) || !isEmpty(valoresBusquedaReporteConsolidadoCotizacion[0].fechaEmisionHasta))
    {
        cadena += StringNegrita("Fecha emisión: ");
        cadena += valoresBusquedaReporteConsolidadoCotizacion[0].fechaEmisionDesde + " - " + valoresBusquedaReporteConsolidadoCotizacion[0].fechaEmisionHasta;
        cadena += "<br>";
    }

    if (!isEmpty(valoresBusquedaReporteConsolidadoCotizacion[0].segun))
    {
        cadena += StringNegrita("Progreso: ");
        cadena += select2.obtenerText('cboSegun');
        cadena += "<br>";
    }
    return cadena;
}

function buscarReporteConsolidadoCotizacion(colapsa)
{
    loaderShow();
    var cadena;
    cadena = obtenerDatosBusquedaConsolidadoCotizacion();

    if (!isEmpty(cadena) && cadena !== 0)
    {
        $('#idPopover').attr("data-content", cadena);
    }
    $('[data-toggle="popover"]').popover('show');
    banderaBuscar = 1;

    obtenerDataBusquedaConsolidadoCotizacion();

    if (colapsa === 1)
        colapsarBuscador();
}

function obtenerDataBusquedaConsolidadoCotizacion()
{
    ax.setAccion("obtenerDataReporteConsolidadoCotizacion");
    ax.addParamTmp("criterios", valoresBusquedaReporteConsolidadoCotizacion);
    ax.consumir();

}

function onResponseObtenerDataReporteConsolidadoCotizacion(data)
{
    if (!isEmptyData(data))
    {
        $('#datatableReporteConsolidadoCotizacion').dataTable({
//            "processing": true,
//            "serverSide": true,
            "scrollX": true,
            "order": [[2, "asc"]],
            "data": data,
            "autoWidth": true,
            "columns": [
                {"data": "fecha_emision", "class": "alignCenter", "width": "50px"}, //, width: "15%"
                {"data": "persona_nombre", "width": "205px"},
                {"data": "serie_numero", "class": "alignCenter"},
                {"data": "ticket", "class": "alignCenter"},
                {"data": "agencia", "class": "alignCenter"},
                {"data": "agencia_zona", "class": "alignCenter"},
                {"data": "categoria", "class": "alignCenter"},
                {"data": "detalle", "class": "alignCenter"},
                {"data": "fuente", "class": "alignCenter"},
                {"data": "tipo", "class": "alignCenter"},
                {"data": "moneda", "class": "alignCenter"},
                {"data": "costo_directo", "class": "alignRight"},
                {"data": "igv", "class": "alignRight"},
                {"data": "total", "class": "alignRight"},
            ],

            columnDefs: [
                {
                    "render": function (data, type, row) {
                        return ((isEmpty(row['detalle'])) ? '' : row['detalle']) + ' ' + row['detalle_producto'];
                    },
                    "targets": [7]
                },
                {
                    "render": function (data, type, row) {
                        if (parseFloat(data).formatMoney(2, '.', ',') == '0.00') {
                            return '-';
                        } else {
                            return parseFloat(data).formatMoney(2, '.', ',');
                        }
                    },
                    "targets": [11,12,13]
                },
                {
                    "render": function (data, type, row) {
                        return (isEmpty(data)) ? '' : data.replace(" 00:00:00", "");
                    },
                    "targets": [0]
                }
            ],
            "destroy": true
        });
    } else
    {
        var table = $('#datatableReporteConsolidadoCotizacion').DataTable();
        table.clear().draw();
    }
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
        buscarReporteConsolidadoCotizacion();
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