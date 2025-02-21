var total = 0;
var importe_pagado = 0;
var importe_pendiente = 0;
$(document).ready(function () {
//    loaderShow();
    $('[data-toggle="popover"]').popover({html: true}).popover();
    cargarTitulo("titulo", "");
//    select2.iniciar();

    iniciarDataPicker();
    modificarAnchoTabla('datatableReporteOrdenTrabajo');
    modificarAnchoTabla('datatableFacturacionOrdenTrabajo');
    modificarAnchoTabla('datatableSolicitadoOrdenTrabajo');
    modificarAnchoTabla('datatableEAROrdenTrabajo');
    ax.setSuccess("onResponseReporteOrdenTrabajo");
    obtenerConfiguracionesInicialesReporteOrdenTrabajo();

});

function onResponseReporteOrdenTrabajo(response) {

    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obtenerConfiguracionesInicialesReporteOrdenTrabajo':
                onResponseObtenerConfiguracionesInicialesReporteOrdenTrabajo(response.data);
                break;
            case 'obtenerDataReporteOrdenTrabajo':
                onResponseObtenerDataReporteOrdenTrabajo(response.data);
                loaderClose();
                break;
            case 'verDetallePorOrdenTrabajo':
                onResponseVerDetallePorOrdenTrabajo(response.data);
                loaderClose();
                break;

            case 'obtenerReporteIngresosVSGastosExcel':
                loaderClose();
                location.href = URL_BASE + "util/formatos/reporteIngresosVSGastos.xlsx";
                break;
        }
    } else {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obtenerReporteIngresosVSGastosExcel':
                loaderClose();
                break;
        }
    }
}

function obtenerConfiguracionesInicialesReporteOrdenTrabajo()
{
    ax.setAccion("obtenerConfiguracionesInicialesReporteOrdenTrabajo");
    ax.consumir();
}

function onResponseObtenerConfiguracionesInicialesReporteOrdenTrabajo(data) {

    var string = '<option selected value="-1">Seleccionar una persona</option>';
    if (!isEmpty(data.persona)) {

        $.each(data.persona, function (indexPersona, itemPersona) {
            string += '<option value="' + itemPersona.id + '">' + itemPersona.nombre + ' | ' + itemPersona.codigo_identificacion + '</option>';
        });
        $('#cboPersona').append(string);
        select2.asignarValor('cboPersona', "-1");
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

var valoresBusquedaReporteOrdenTrabajo = [{persona: "", serie: "", numero: "", fechaEmision: "", fechaVencimiento: "", bandera: "0"}];//bandera 0 es balance

function cargarDatosBusquedaReporteOrdenTrabajo()
{
    var personaId = select2.obtenerValor('cboPersona');
    var serie = $('#txtSerie').val();
    var numero = $('#txtNumero').val();
    var fechaEmisionInicio = $('#inicioFechaEmision').val();
    var fechaEmisionFin = $('#finFechaEmision').val();

    valoresBusquedaReporteOrdenTrabajo[0].persona = personaId;
    valoresBusquedaReporteOrdenTrabajo[0].serie = serie;
    valoresBusquedaReporteOrdenTrabajo[0].numero = numero;
    valoresBusquedaReporteOrdenTrabajo[0].fechaEmisionDesde = fechaEmisionInicio;
    valoresBusquedaReporteOrdenTrabajo[0].fechaEmisionHasta = fechaEmisionFin;
//    getDataTableReporteOperaciones();
}

function obtenerDatosBusqueda()
{
    var cadena = "";
    cargarDatosBusquedaReporteOrdenTrabajo();

    if (!isEmpty(valoresBusquedaReporteOrdenTrabajo[0].persona))
    {
        cadena += StringNegrita("Persona: ");
        cadena += select2.obtenerText('cboPersona');
        cadena += "<br>";
    }

    if (!isEmpty(valoresBusquedaReporteOrdenTrabajo[0].fechaEmisionDesde) || !isEmpty(valoresBusquedaReporteOrdenTrabajo[0].fechaEmisionHasta))
    {
        cadena += StringNegrita("Fecha emisión: ");
        cadena += valoresBusquedaReporteOrdenTrabajo[0].fechaEmisionDesde + " - " + valoresBusquedaReporteOrdenTrabajo[0].fechaEmisionHasta;
        cadena += "<br>";
    }
    return cadena;
}

function buscarReporteOrdenTrabajo(colapsa)
{
    loaderShow();
    var cadena;
    cadena = obtenerDatosBusqueda();

//    obtenerCantidadesTotalesReporteOrdenTrabajo();
    if (!isEmpty(cadena) && cadena !== 0)
    {
        $('#idPopover').attr("data-content", cadena);
    }
    $('[data-toggle="popover"]').popover('show');
    banderaBuscar = 1;

    obtenerDataBusquedaOrdenTrabajo();

    if (colapsa === 1)
        colapsarBuscador();
}

function obtenerDataBusquedaOrdenTrabajo()
{
    ax.setAccion("obtenerDataReporteOrdenTrabajo");
    ax.addParamTmp("criterios", valoresBusquedaReporteOrdenTrabajo);
    ax.consumir();

}

function onResponseObtenerDataReporteOrdenTrabajo(data)
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
                {"data": "monto_facturado", "class": "alignRight"},
                {"data": "monto_rendido", "class": "alignRight"},
                {"data": "monto_rendido_sgi", "class": "alignRight"},
                {"data": "utilidad_bruta", "class": "alignRight"},
                {"data": "monto_solicitado", "class": "alignRight"},
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
                    "targets": [4, 5, 6, 7]
                },
                {
                    "render": function (data, type, row) {
                        let html = ' / <span class="label label-primary">' + parseFloat(row.monto_solicitado).formatMoney(2, '.', ',') + '</span>';

                        if (row.monto_solicitado * 1 > row.monto_rendido_total * 1) {
                            html = '<span class="label label-warning">' + parseFloat(row.monto_rendido_total).formatMoney(2, '.', ',') + '</span>' + html;
                        } else {
                            html = '<span class="label label-success">' + parseFloat(row.monto_rendido_total).formatMoney(2, '.', ',') + '</span>' + html;
                        }

                        return html;
                    },
                    "targets": [8]
                },
                {
                    "render": function (data, type, row) {
                        return (isEmpty(data)) ? '' : data.replace(" 00:00:00", "");
                    },
                    "targets": [0]
                },
                {
                    "render": function (data, type, row) {
                        return '<a onclick="verDetallePorOrdenTrabajo(' + row['documento_id'] + ', ' + row['movimiento_id'] + ')"><b><i class="fa fa-eye"" style="color:b"></i><b></a>';

                    },
                    "targets": [9]
                }
            ],
//            
            "destroy": true
        });
    } else
    {
        var table = $('#datatableReporteOrdenTrabajo').DataTable();
        table.clear().draw();
    }
}

// ver detalle en modal
function verDetallePorOrdenTrabajo(documentoId)
{
    loaderShow();
    ax.setAccion("verDetallePorOrdenTrabajo");
    ax.addParamTmp("documento_id", documentoId);
//    ax.addParamTmp("movimiento_id", movimientoId);
    ax.consumir();
}

function onResponseVerDetallePorOrdenTrabajo(data)
{
    subtotalFacturado = 0;
    subtotalSolicitado = 0;
    subtotalRendido = 0;
    utilidadBruta = 0;
    $('#resumenSubtotal').remove();
    $('#resumenIGV').remove();
    $('#resumenTotal').remove();

    if (!isEmpty(data)) {
        $('[data-toggle="popover"]').popover('hide');
        $('#modalDetalleOrdenTrabajo').modal('show');
        cargarDataDocumento(data.cabecera);

        subtotalFacturado = data.detalleFacturacion.subtotalDetalle;
        subtotalSolicitado = data.detalleSolicitado.totalDetalle;
        subtotalRendido = data.detalleEAR.subtotalDetalle;
        subtotalRendidoOtros = data.detalleOtros.subtotalDetalle;

        subtotalUtilidad = Math.round((subtotalFacturado - subtotalRendido - subtotalRendidoOtros) * 100) / 100;

        igvFacturado = data.detalleFacturacion.igvDetalle;
        igvSolicitado = 0;
        igvRendido = data.detalleEAR.igvDetalle;
        igvRendidoOtros = data.detalleOtros.igvDetalle;

        igvUtilidad = Math.round((igvFacturado - igvRendido - igvRendidoOtros) * 100) / 100;

        totalFacturado = data.detalleFacturacion.totalDetalle;
        totalSolicitado = data.detalleSolicitado.totalDetalle;
        totalRendido = data.detalleEAR.totalDetalle;
        totalRendidoOtros = data.detalleOtros.totalDetalle;

        totalUtilidad = Math.round((totalFacturado - totalRendido - totalRendidoOtros) * 100) / 100;


        setTimeout(function () {
            cargarDetalleFacturacionOrdenTrabajo(data.detalleFacturacion.datosDetalle, subtotalFacturado, data.detalleFacturacion.igvDetalle, data.detalleFacturacion.totalDetalle)
        }, 500);
        setTimeout(function () {
            cargarDetalleSolicitadoOrdenTrabajo(data.detalleSolicitado.datosDetalle, subtotalSolicitado, data.detalleSolicitado.igvDetalle, data.detalleSolicitado.totalDetalle)
        }, 500);
        setTimeout(function () {
            cargarDetalleEAROrdenTrabajo(data.detalleEAR.datosDetalle, subtotalRendido, data.detalleEAR.igvDetalle, data.detalleEAR.totalDetalle)
        }, 500);

        setTimeout(function () {
            cargarDetalleCostosAdicionalesOrdenTrabajo(data.detalleOtros.datosDetalle, subtotalRendidoOtros, data.detalleOtros.igvDetalle, data.detalleOtros.totalDetalle)
        }, 500);

        setTimeout(function () {
            cargarDetalleRHOrdenTrabjo(data.detalleRH.datosDetalle, data.detalleRH.subtotalDetalle, data.detalleRH.igvDetalle, data.detalleRH.totalDetalle)
        }, 500);

        var columnaTablaResumen =
                '<tr id="resumenSubtotal">' +
                '<td align="right"><strong>Monto Facturado (Venta):</strong></td>' +
                '<td align="right"><strong>' + formatearNumero(subtotalFacturado) + '&nbsp;</strong></td>' +
                '<td align="right"><strong>' + formatearNumero(igvFacturado) + '&nbsp;</strong></td>' +
                '<td align="right"><strong>' + formatearNumero(totalFacturado) + '&nbsp;</strong></td>' +
                '</tr>' +
                '<tr id="resumenIGV">' +
                '<td align="right" style="color:#cb2a2a;"><strong>EAR Rendido a la Fecha (Solicitado ' + formatearNumero(totalSolicitado) + ')</strong></td>' +
                '<td align="right" style="color:#cb2a2a;"><strong>(' + formatearNumero(subtotalRendido) + ')</strong></td>' +
                '<td align="right" style="color:#cb2a2a;"><strong>(' + formatearNumero(igvRendido) + ')</strong></td>' +
                '<td align="right" style="color:#cb2a2a;"><strong>(' + formatearNumero(totalRendido) + ')</strong></td>' +
                '</tr>' +
                '<tr id="resumenTotal">' +
                '<td align="right" style="color:#cb2a2a;"><strong>Costos Adicionales</strong></td>' +
                '<td align="right" style="color:#cb2a2a;"><strong>(' + formatearNumero(subtotalRendidoOtros) + ')</strong></td>' +
                '<td align="right" style="color:#cb2a2a;"><strong>(' + formatearNumero(igvRendidoOtros) + ')</strong></td>' +
                '<td align="right" style="color:#cb2a2a;"><strong>(' + formatearNumero(totalRendidoOtros) + ')</strong></td>' +
                '</tr>' +
                '<tr id="resumenUtilidad">' +
                '<td align="right" style="color:blue;"><strong>Utilidad Bruta:</strong></td>' +
                '<td align="right" style="color:blue;"><strong>' + formatearNumero(subtotalUtilidad) + '&nbsp;</strong></td>' +
                '<td align="right" style="color:blue;"><strong>' + formatearNumero(igvUtilidad) + '&nbsp;</strong></td>' +
                '<td align="right" style="color:blue;"><strong>' + formatearNumero(totalUtilidad) + '&nbsp;</strong></td>' +
                '</tr>';
        $('#tablaResumen').html(columnaTablaResumen);
    } else {
        $.Notification.autoHideNotify('warning', 'top right', 'Validación', "No hay detalle");
    }
}

function cargarDataDocumento(data)
{
    var stringTitulo = '<div class="row"> ' +
            '<div class="col-lg-8 col-md-9 col-sm-8 col-xs-8"> ' +
            '<strong>Detalle de ' + data[0]['documento_tipo'] + ' [' + data[0]['serie_numero'] + ']</strong>' +
            '</div>' +
            '<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">' +
//            ' <h3 style="margin-top: 0px;margin-bottom: 0px;">S/. 50.00</h3>'+
            '<strong>Monto: ' + data[0]['moneda'] + ' ' + formatearNumero(data[0]['total']) + '</strong>' +
            '</div>' +
            '</div>';
    $('.modal-title').empty();
    $('.modal-title').append(stringTitulo);
}

function cargarDetalleFacturacionOrdenTrabajo(data, subtotal, igv, total)
{
//    modificarAnchoTabla('datatableFacturacionOrdenTrabajo');
//    if (!isEmptyData(data))
//    {
    $('#datatableFacturacionOrdenTrabajo').dataTable({
//            "scrollX":datatable2 true,
        "order": [[0, "desc"]],
        "data": !isEmpty(data) ? data : [],
        "scrollX": true,
        "autoWidth": true,
        "columns": [
            {"data": "documento_tipo", "sClass": "alignCenter"},
            {"data": "fecha_emision", "sClass": "alignCenter"},
            {"data": "serie_numero", "sClass": "alignCenter"},
            {"data": "estado", "sClass": "alignCenter"},
            {"data": "subtotal", "sClass": "alignRight"},
            {"data": "igv", "sClass": "alignRight"},
            {"data": "total", "sClass": "alignRight"}
        ],
        columnDefs: [
            {
                "render": function (data, type, row) {
                    return parseFloat(data).formatMoney(2, '.', ',');
                },
                "targets": [4, 5, 6]
            },
            {
                "render": function (data, type, row) {
                    return (isEmpty(data)) ? '' : datex.parserFecha(data.replace(" 00:00:00", ""));
                },
                "targets": [1]
            }
        ],
        "destroy": true,
        footerCallback: function (row, data, start, end, display) {
            var api = this.api(), data;
            $(api.column(4).footer()).html((formatearNumero(subtotal)));
            $(api.column(5).footer()).html((formatearNumero(igv)));
            $(api.column(6).footer()).html((formatearNumero(total)));
        }
    });
//    } else
//    {
//        var table = $('#datatableFacturacionOrdenTrabajo').DataTable();
//        table.clear().draw();
//    }
}
function cargarDetalleSolicitadoOrdenTrabajo(data, subtotal, igv, total)
{

//    if (!isEmptyData(data))
//    {
    $('#datatableSolicitadoOrdenTrabajo').dataTable({
//            "scrollX":datatable2 true,
        "order": [[0, "desc"]],
        "data": !isEmpty(data) ? data : [],
        "scrollX": true,
        "autoWidth": true,
        "columns": [
            {"data": "fecha_ear_sol", "sClass": "alignCenter"},
            {"data": "ear_numero", "sClass": "alignCenter"},
            {"data": "estado_descripcion", "sClass": "alignCenter"},
            {"data": "descripcion_ear", "sClass": "alignCenter"},
            {"data": "total", "sClass": "alignRight"}
        ],
        columnDefs: [
            {
                "render": function (data, type, row) {
                    return parseFloat(data).formatMoney(2, '.', ',');
                },
                "targets": [4]
            },
            {
                "render": function (data, type, row) {
                    return (isEmpty(data)) ? '' : datex.parserFecha(data.replace(" 00:00:00", ""));
                },
                "targets": [0]
            }
        ],
        "destroy": true,
        footerCallback: function (row, data, start, end, display) {
            var api = this.api(), data;
            $(api.column(4).footer()).html((formatearNumero(total)));
        }
    });
//    } else
//    {
//        var table = $('#datatableSolicitadoOrdenTrabajo').DataTable();
//        table.clear().draw();
//    }
}

function cargarDetalleEAROrdenTrabajo(data, subtotal, igv, total)
{
//    if (!isEmptyData(data))
//    {
    $('#datatableEAROrdenTrabajo').dataTable({
//            "scrollX":datatable2 true,
        "order": [[0, "desc"]],
        "data": !isEmpty(data) ? data : [],
        "scrollX": true,
        "columns": [
            {"data": "documento_tipo_descripcion", "sClass": "alignCenter"},
            {"data": "fecha_emision_ord", "sClass": "alignCenter"},
            {"data": "persona_nombre"},
            {"data": "serie_numero", "sClass": "alignCenter"},
            {"data": "num_ear", "sClass": "alignCenter"},
            {"data": "subtotal", "sClass": "alignRight"},
            {"data": "igv", "sClass": "alignRight"},
            {"data": "total", "sClass": "alignRight"}
        ],
        columnDefs: [
            {
                "render": function (data, type, row) {
                    return parseFloat(data).formatMoney(2, '.', ',');
                },
                "targets": [5, 6, 7]
            },
            {
                "render": function (data, type, row) {
                    return (isEmpty(data)) ? '' : datex.parserFecha(data.replace(" 00:00:00", ""));
                },
                "targets": [1]
            }
        ],
        "destroy": true,
        footerCallback: function (row, data, start, end, display) {
            var api = this.api(), data;
            $(api.column(5).footer()).html((formatearNumero(subtotal)));
            $(api.column(6).footer()).html((formatearNumero(igv)));
            $(api.column(7).footer()).html((formatearNumero(total)));
        }
    });
//    } else
//    {
//        var table = $('#datatableEAROrdenTrabajo').DataTable();
//        table.clear().draw();
//    }
}


function cargarDetalleRHOrdenTrabjo(data, subtotal, igv, total)
{

    $('#datatableRH').dataTable({
//            "scrollX":datatable2 true,
        "order": [[0, "desc"]],
        "data": !isEmpty(data) ? data : [],
        "scrollX": true,
        "columns": [
            {"data": "documento_tipo_descripcion", "sClass": "alignCenter"},
            {"data": "fecha_emision_ord", "sClass": "alignCenter"},
            {"data": "persona_nombre"},
            {"data": "serie_numero", "sClass": "alignCenter"},
            {"data": "num_ear", "sClass": "alignCenter"},
            {"data": "subtotal", "sClass": "alignRight"},
            {"data": "igv", "sClass": "alignRight"},
            {"data": "total", "sClass": "alignRight"}
        ],
        columnDefs: [
            {
                "render": function (data, type, row) {
                    return parseFloat(data).formatMoney(2, '.', ',');
                },
                "targets": [5, 6, 7]
            },
            {
                "render": function (data, type, row) {
                    return (isEmpty(data)) ? '' : datex.parserFecha(data.replace(" 00:00:00", ""));
                },
                "targets": [1]
            }
        ],
        "destroy": true,
        footerCallback: function (row, data, start, end, display) {
            var api = this.api(), data;
            $(api.column(5).footer()).html((formatearNumero(subtotal)));
            $(api.column(6).footer()).html((formatearNumero(igv)));
            $(api.column(7).footer()).html((formatearNumero(total)));
        }
    });

}

function cargarDetalleCostosAdicionalesOrdenTrabajo(data, subtotal, igv, total)
{
//    if (!isEmptyData(data))
//    {
    $('#datatableCostosAdicionalesOrdenTrabajo').dataTable({
//            "scrollX":datatable2 true,
        "order": [[0, "desc"]],
        "data": !isEmpty(data) ? data : [],
        "scrollX": true,
        "columns": [
            {"data": "documento_tipo_descripcion", "sClass": "alignCenter"},
            {"data": "fecha_emision_ord", "sClass": "alignCenter"},
            {"data": "persona_nombre"},
            {"data": "serie_numero", "sClass": "alignCenter"},
//                {"data": "num_ear", "sClass": "alignCenter"},
            {"data": "subtotal", "sClass": "alignRight"},
            {"data": "igv", "sClass": "alignRight"},
            {"data": "total", "sClass": "alignRight"}
        ],
        columnDefs: [
            {
                "render": function (data, type, row) {
                    return parseFloat(data).formatMoney(2, '.', ',');
                },
                "targets": [4, 5, 6]
            },
            {
                "render": function (data, type, row) {
                    return (isEmpty(data)) ? '' : datex.parserFecha(data.replace(" 00:00:00", ""));
                },
                "targets": [1]
            }
        ],
        "destroy": true,
        footerCallback: function (row, data, start, end, display) {
            var api = this.api(), data;
            $(api.column(4).footer()).html((formatearNumero(subtotal)));
            $(api.column(5).footer()).html((formatearNumero(igv)));
            $(api.column(6).footer()).html((formatearNumero(total)));
        }
    });
//    } else
//    {
//        var table = $('#datatableCostosAdicionalesOrdenTrabajo').DataTable();
//        table.clear().draw();
//    }
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
        buscarReporteOrdenTrabajo();
    }
    loaderClose();
}

function exportarReporteBalanceExcel()
{
    loaderShow();
    cargarDatosBusquedaReporteOrdenTrabajo();
    ax.setAccion("obtenerReporteIngresosVSGastosExcel");
    ax.addParamTmp("criterios", valoresBusquedaReporteOrdenTrabajo);
    ax.consumir();
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