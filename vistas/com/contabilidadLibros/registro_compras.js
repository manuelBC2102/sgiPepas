var estadoTolltipMP = 0;
var banderaBuscarMP = 0;
var total;
var total_cantidad;
$(document).ready(function () {
    $('[data-toggle="popover"]').popover({ html: true }).popover();
    cargarTitulo("titulo", "");
    select2.iniciar();
    $('.fecha').datepicker({
        isRTL: false,
        format: 'dd/mm/yyyy',
        autoclose: true,
        language: 'es'
    });
    ax.setSuccess("onResponseReporteCompras");
    obtenerConfiguracionesInicialesReporteCompras();
    modificarAnchoTabla('dataTableReporteCompras');
});

function onResponseReporteCompras(response) {
    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obtenerConfiguracionInicial':
                onResponseObtenerConfiguracionesInicialesReporteCompras(response.data);
                break;
            case 'listarRegistroComprasXCriterios':
                onResponseListarRegistroCompras(response.data);
                break;
            case 'obtenerDocumentoRelacionVisualizar':
                onResponseObtenerDocumentoRelacionVisualizar(response.data);
                loaderClose();
                break;
            case 'exportarRegistroComprasSire':
                loaderClose();
                var link = document.createElement("a");
                link.download = response.data;
                link.href = URL_BASE + "util/uploads/" + response.data;
                link.click();
                break;
            case 'exportarRegistroCompras':
                loaderClose();
                if (response.tag == 'excel') {
                    location.href = URL_BASE + "util/formatos/registroCompras.xlsx";
                } else if (response.tag == 'txt' && !isEmpty(response.data)) {
                    var link = document.createElement("a");
                    link.download = response.data;
                    link.href = URL_BASE + "util/uploads/" + response.data;
                    link.click();
                } else {
                    $.Notification.autoHideNotify('warning', 'top right', 'Validación', "No se obtuvo la información del registro de compras.");
                }
                break;
            case 'obtenerReporteComprasProducto':
                loaderClose();
                location.href = URL_BASE + "util/formatos/reporte.xlsx";
                break;

            case 'obtenerDocumento':
                onResponseObtenerDocumento(response.data);
                loaderClose();
                break;
            case 'obtenerDocumentosRelacionados':
                onResponseObtenerDocumentosRelacionados(response.data);
                loaderClose();
                break;

            case 'validarComprobanteSUNAT':
                onResponseValidacionSunat(response.data);
                break;

            case 'validarDocumentoSistema':
                onResponseValidarComprobanteSistema(response.data);
                break;

            case 'validarComprobanteSUNATXDocumentoId':
                loaderClose();
                onResponsevalidarComprobanteSUNAT(response.data);
                break;
        }
    } else {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obtenerReporteReporteComprasExcel':
                loaderClose();
                break;
            case 'obtenerReporteComprasProducto':
                loaderClose();
                break;

            case 'validarComprobanteSUNATXDocumentoId':
                loaderClose();
                swal("Validación!", response.message, "warning");
                break;

        }
    }
}

function exportarRegistroComprasSire(tipo) {
    //    actualizandoBusqueda = true;
    loaderShow();
    cargarDatosBusquedaRegistroCompras();
    ax.setAccion("exportarRegistroComprasSire");
    ax.addParamTmp("criterios", valoresBusquedaReporteCompras);
    ax.consumir();
}

function obtenerConfiguracionesInicialesReporteCompras() {
    ax.setAccion("obtenerConfiguracionInicial");
    ax.addParamTmp("id_empresa", commonVars.empresa);
    ax.consumir();
}
var dataLibro = null;

function onResponseObtenerConfiguracionesInicialesReporteCompras(data) {

    dataLibro = data.dataLibro;
    select2.cargar("cboDocumentoTipo", data.dataTipoDocumento, "id", "descripcion");
    onResponseObtenerDataCbo('PersonaProveedor', 'id', ['codigo_identificacion', ' | ', 'nombre'], data.dataPersonaActiva);
    onResponseObtenerDataCbo('Libro', 'id', ['codigo', ' | ', 'descripcion'], data.dataLibro);
    onResponseObtenerDataCbo('Periodo', 'id', ['anio', ' - ', 'mes'], data.dataPeriodo, data.dataPeriodoActual[0]['id']);
    //    onResponseObtenerDataCbo('PeriodoFin', 'id', ['anio', ' - ', 'mes'], data.dataPeriodo, data.dataPeriodoActual[0]['id']);
    onResponseListarRegistroCompras(data.dataRegistroCompras);
}
function onResponseObtenerDataCbo(cboId, itemId, itemDes, data, valor) {
    document.getElementById('cbo' + cboId).innerHTML = "";

    select2.asignarValor('cbo' + cboId, "");
    $('#cbo' + cboId).append('<option value="">Seleccione</option>');
    if (!isEmpty(data)) {
        $.each(data, function (index, item) {
            $('#cbo' + cboId).append('<option value="' + item[itemId] + '">' + (isArray(itemDes) ? item[itemDes[0]] + itemDes[1] + item[itemDes[2]] : item[itemDes]) + '</option>');
        });
        if (!isEmpty(valor)) {
            select2.asignarValor('cbo' + cboId, valor);
        } else {
            select2.asignarValor('cbo' + cboId, "");
        }
    }
}

var valoresBusquedaReporteCompras = [{ persona: null }];//bandera 0 es balance

function cargarDatosBusquedaRegistroCompras() {
    var personaId = $('#cboPersonaProveedor').val();
    var libro = $('#cboLibro').val();
    var periodoInicio = $('#cboPeriodo').val();
    //    var periodoFin = $('#cboPeriodoFin').val();
    var fechaEmisionInicio = $('#inicioFechaEmisionMP').val();
    var fechaEmisionFin = $('#finFechaEmisionMP').val();

    valoresBusquedaReporteCompras[0].persona = personaId;
    valoresBusquedaReporteCompras[0].libro = libro;
    valoresBusquedaReporteCompras[0].empresa = commonVars.empresa;
    valoresBusquedaReporteCompras[0].periodoInicio = periodoInicio;
    //    valoresBusquedaReporteCompras[0].periodoFin = periodoFin;
    valoresBusquedaReporteCompras[0].fechaEmisionDesde = fechaEmisionInicio;
    valoresBusquedaReporteCompras[0].fechaEmisionHasta = fechaEmisionFin;
    //    getDataTableReporteCompras();

}
function buscarReporteCompras(colapsa) {
    loaderShow();
    var cadena;
    cadena = obtenerDatosBusqueda();
    obtenerReporteDeCompras();
    if (!isEmpty(cadena) && cadena !== 0) {
        $('#idPopover').attr("data-content", cadena);
    }
    $('[data-toggle="popover"]').popover('show');
    banderaBuscarMP = 1;

    if (colapsa === 1)
        colapsarBuscador();

}

function validarDocumentoSunat() {
    loaderShow();
    cargarDatosBusquedaRegistroCompras();
    ax.setAccion("validarComprobanteSUNAT");
    ax.addParamTmp("criterios", valoresBusquedaReporteCompras);
    ax.consumir();
}
function obtenerDatosBusqueda() {
    var cadena = "";
    cargarDatosBusquedaRegistroCompras();

    if (!isEmpty(select2.obtenerValor('cboPersonaProveedor'))) {
        cadena += StringNegrita("Proveedor: ");

        cadena += select2.obtenerText('cboPersonaProveedor');
        cadena += "<br>";
    }

    if (!isEmpty(select2.obtenerValor('cboPeriodo'))) {
        cadena += StringNegrita("Periodo: ");

        cadena += select2.obtenerText('cboPeriodo');
        cadena += "<br>";
    }

    if (!isEmpty(select2.obtenerValor('cboLibro'))) {
        cadena += StringNegrita("Tipo de Compra: ");

        cadena += select2.obtenerText('cboLibro');
        cadena += "<br>";
    }


    if (!isEmpty(valoresBusquedaReporteCompras[0].fechaEmisionDesde) || !isEmpty(valoresBusquedaReporteCompras[0].fechaEmisionHasta)) {
        cadena += StringNegrita("Fecha emisión: ");
        cadena += valoresBusquedaReporteCompras[0].fechaEmisionDesde + " - " + valoresBusquedaReporteCompras[0].fechaEmisionHasta;
        cadena += "<br>";
    }
    return cadena;
}

function onResponseListarRegistroCompras(data) {
    $("#dataList").empty();
    var cuerpo_total = '';
    var cabeza = "<table id='datatable' class='table table-striped table-bordered'>"
        + "<thead>"
        + "<tr>"
        + "<th style='text-align:center;'>Fecha Emsión</th>"
        + "<th style='text-align:center;'>N° Registro</th>"
        + "<th style='text-align:center;'>Tipo</th>"
        + "<th style='text-align:center;'>Serie - Número</th>"
        + "<th style='text-align:center;'>R.U.C.</th>"
        + "<th style='text-align:center;'>Nombre</th>"
        + "<th style='text-align:center;'>Tipo Cambio</th>"
        + "<th style='text-align:center;'>Monto USD $</th>"
        + "<th style='text-align:center;'>Sub-Total</th>"
        + "<th style='text-align:center;'>IGV</th>"
        + "<th style='text-align:center;'>Total</th>"
        + "<th style='text-align:center;'>Acc.</th>"
        + "</tr>"
        + "</thead>";

    if (!isEmpty(dataLibro) && !isEmpty(data)) {
        var monto_total_dolares = 0;
        var monto_sub_total = 0;
        var monto_igv = 0;
        var monto_total = 0;
        $.each(dataLibro, function (indexLibro, libro) {
            var html = '';
            var arrayFiltradoLibro = data.filter(function (obj) {
                return obj.cont_libro_id == libro.id;
            });
            if (!isEmpty(arrayFiltradoLibro)) {
                var monto_total_dolares_tipo = 0;
                var monto_sub_total_tipo = 0;
                var monto_igv_tipo = 0;
                var monto_total_tipo = 0;
                $.each(arrayFiltradoLibro, function (index, item) {
                    html += '<tr>';
                    html += '<td align="center">' + formatearFechaBDCadena(item.fecha_emision) + '</td>';
                    html += '<td align="center">' + (item.cuo) + '</td>';
                    html += '<td align="center">' + (item.tipo_documento) + '</td>';
                    html += '<td align="center">' + (item.serie_numero) + '</td>';
                    html += '<td align="center">' + (item.codigo_identificacion_persona) + '</td>';
                    let persona_nombre = (!isEmpty(item.nombre_persona) ? item.nombre_persona : '');
                    if (!isEmpty(persona_nombre) && persona_nombre.length > 34) {
                        persona_nombre = persona_nombre.substr(0, 30) + '...';
                    }
                    html += '<td align="left">' + (persona_nombre) + '</td>';
                    html += '<td align="right">' + formatearNumeroPorCantidadDecimales(item.tipo_cambio, 4) + '</td>';

                    monto_total_dolares_tipo += item.total_dolares * 1;
                    html += '<td align="right">' + formatearNumeroPorCantidadDecimales(item.total_dolares, 2) + '</td>';

                    monto_sub_total_tipo += item.sub_total * 1;
                    html += '<td align="right">' + formatearNumeroPorCantidadDecimales(item.sub_total, 2) + '</td>';

                    monto_igv_tipo += item.igv * 1;
                    html += '<td align="right">' + formatearNumeroPorCantidadDecimales(item.igv, 2) + '</td>';

                    monto_total_tipo += item.total * 1;
                    html += '<td align="right">' + formatearNumeroPorCantidadDecimales(item.total, 2) + '</td>';
                    html += '<td align="center"> <a onclick="visualizarDocumento(' + item.id + ', ' + item.movimiento_id + ')" title="Visualizar"><b><i class="fa fa-eye" style="color:#1ca8dd"></i></b></a>';
                    html += ' <a onclick="validarComprobanteSUNATXDocumentoId(' + item.id + ')" title="Validar SUNAT"><b><i class="ion-android-search" style="color:#001aff"></i></b></a>';
                    html += '</td></tr>';
                });

                html += '<tr>';
                html += '<td align="left" colspan="7" ><b>Sub-total tipo ' + libro.codigo.substr(3, 1) + '</b></td>';
                html += '<td align="right"><b>' + formatearNumeroPorCantidadDecimales(monto_total_dolares_tipo, 2) + '</b></td>';
                html += '<td align="right"><b>' + formatearNumeroPorCantidadDecimales(monto_sub_total_tipo, 2) + '</b></td>';
                html += '<td align="right"><b>' + formatearNumeroPorCantidadDecimales(monto_igv_tipo, 2) + '</b></td>';
                html += '<td align="right"><b>' + formatearNumeroPorCantidadDecimales(monto_total_tipo, 2) + '</b></td>';
                html += '<td align="center"></td>';
                html += '</tr>';

                monto_total_dolares += monto_total_dolares_tipo;
                monto_sub_total += monto_sub_total_tipo;
                monto_igv += monto_igv_tipo;
                monto_total += monto_total_tipo;

            }
            cuerpo_total += html;
        });
        cuerpo_total += '<tr>';
        cuerpo_total += '<td align="left" colspan="7" ><b>Total</b></td>';
        cuerpo_total += '<td align="right"><b>' + formatearNumeroPorCantidadDecimales(monto_total_dolares, 2) + '</b></td>';
        cuerpo_total += '<td align="right"><b>' + formatearNumeroPorCantidadDecimales(monto_sub_total, 2) + '</b></td>';
        cuerpo_total += '<td align="right"><b>' + formatearNumeroPorCantidadDecimales(monto_igv, 2) + '</b></td>';
        cuerpo_total += '<td align="right"><b>' + formatearNumeroPorCantidadDecimales(monto_total, 2) + '</b></td>';
        cuerpo_total += '<td align="center"></td>';
        cuerpo_total += '</tr>';
    }


    var pie = "</table>";
    var html = cabeza + cuerpo_total + pie;
    $("#dataList").append(html);
    loaderClose();
}



var color;

function getDataTableReporteCompras() {
    color = '';
    ax.setAccion("obtenerDataReporteCompras");
    ax.addParamTmp("criterios", valoresBusquedaReporteCompras);
    $('#dataTableReporteCompras').dataTable({
        "processing": true,
        "serverSide": true,
        "ajax": ax.getAjaxDataTable(),
        "scrollX": true,
        "autoWidth": true,
        "order": [[0, "desc"]],
        //         "lengthMenu": [[1, 2, 3], [1, 2, 3]],
        "columns": [
            //F. Creación	F. Emisión	Vendedor	Tipo documento	Persona	Serie	Número	Origen Total acciones
            { "data": "fecha_creacion" },
            { "data": "fecha_emision" },
            { "data": "usuario_nombre" },
            { "data": "documento_tipo_descripcion" },
            { "data": "persona_nombre_completo" },
            { "data": "serie" },
            { "data": "numero" },
            { "data": "origen" },
            { "data": "total", "class": "alignRight" },
            { "data": "acciones", "class": "alignCenter" }
        ],
        columnDefs: [
            {
                "render": function (data, type, row) {
                    return parseFloat(data).formatMoney(2, '.', ',');
                },
                "targets": 8
            },
            {
                "render": function (data, type, row) {
                    return (isEmpty(data)) ? '' : data.replace(" 00:00:00", "");
                },
                "targets": 0
            },
            {
                "render": function (data, type, row) {
                    return (isEmpty(data)) ? '' : data.replace(" 00:00:00", "");
                },
                "targets": 1
            }
        ],
        destroy: true,
        footerCallback: function (row, data, start, end, display) {
            var api = this.api(), data;
            $(api.column(8).footer()).html(
                'S/. ' + (formatearNumero(total))
            );
        }
    });
    loaderClose();
}

var actualizandoBusqueda = false;
function loaderBuscarVentas() {
    actualizandoBusqueda = true;
    var estadobuscador = $('#bg-info').attr("aria-expanded");
    if (estadobuscador == "false") {
        buscarReporteCompras();
    }
}

function cerrarPopover() {
    if (banderaBuscarMP == 1) {
        if (estadoTolltipMP == 1) {
            $('[data-toggle="popover"]').popover('hide');
        } else {
            $('[data-toggle="popover"]').popover('show');
        }
    } else {
        $('[data-toggle="popover"]').popover('hide');
    }


    estadoTolltipMP = (estadoTolltipMP == 0) ? 1 : 0;
}

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



function obtenerReporteDeCompras() {
    ax.setAccion("listarRegistroComprasXCriterios");
    ax.addParamTmp("criterios", valoresBusquedaReporteCompras);
    ax.consumir();
}

function exportarRegistroCompras(tipo) {
    loaderShow();
    cargarDatosBusquedaRegistroCompras();
    ax.setAccion("exportarRegistroCompras");
    ax.addParamTmp("criterios", valoresBusquedaReporteCompras);
    ax.addParamTmp("tipo", tipo);
    ax.setTag(tipo);
    ax.consumir();
}

function exportarReporteReporteCompras() {
    actualizandoBusqueda = true;
    loaderShow();
    cargarDatosBusquedaRegistroCompras();
    ax.setAccion("obtenerReporteReporteComprasExcel");
    ax.addParamTmp("criterios", valoresBusquedaReporteCompras);
    ax.consumir();
}
function exportarReporteComprasProducto() {
    actualizandoBusqueda = true;
    loaderShow();
    cargarDatosBusquedaRegistroCompras();
    ax.setAccion("obtenerReporteComprasProducto");
    ax.addParamTmp("criterios", valoresBusquedaReporteCompras);
    ax.consumir();
}


//function exportarRegistroCompras(tipo) {
//    loaderShow();
//    cargarDatosBusquedaRegistroCompras();    
//    ax.setAccion("exportarRegistroCompras");
//    ax.addParamTmp("criterios", valoresBusquedaReporteCompras);
//    ax.addParamTmp("tipo", tipo);
//    ax.setTag(tipo);
//    ax.consumir();
//}

//function imprimir(muestra)
//{
//    var ficha = document.getElementById(muestra);
//    var ventimp = window.open(' ', 'popimpr');
//    ventimp.document.write(ficha.innerHTML);
//    ventimp.document.close();
//    ventimp.print();
//    ventimp.close();
//}


function obtenerDocumento(documentoId) {
    loaderShow();
    ax.setAccion("obtenerDocumento");
    ax.addParamTmp("documentoId", documentoId);
    ax.consumir();
}

function onResponseObtenerDocumento(data) {
    $('[data-toggle="popover"]').popover('hide');
    cargarDataDocumento(data.dataDocumento);
    cargarDataComentarioDocumento(data.comentarioDocumento);

    if (!isEmpty(data.detalleDocumento)) {
        cargarDetalleDocumento(data.detalleDocumento, data.dataMovimientoTipoColumna, data.organizador);
    } else {
        $('#formularioCopiaDetalle').hide();
    }

    $('#modalDetalleDocumento').modal('show');
}
function cargarDataDocumento(data) {
    $("#formularioDetalleDocumento").empty();
    //$("#formularioDetalleDocumento").css("height", 75 * data.length);

    if (!isEmpty(data)) {
        $('#nombreDocumentoTipo').html(data[0]['nombre_documento']);
        // Mostraremos la data en filas de dos columnas
        $.each(data, function (index, item) {

            appendFormDetalle('</div>');
            appendFormDetalle('<div class="row">');

            var html = '<div class="form-group col-md-12"><div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">' +
                '<label>' + item.descripcion + '</label>' +
                '</div><div class="col-lg-8 col-md-8 col-sm-6 col-xs-6">';

            var valor = quitarNULL(item.valor);

            if (!isEmpty(valor)) {
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
function fechaArmada(valor) {
    var fecha = separarFecha(valor);
    return fecha.dia + "/" + fecha.mes + "/" + fecha.anio;
}
function appendFormDetalle(html) {
    $("#formularioDetalleDocumento").append(html);
}

function cargarDataComentarioDocumento(data) {
    $('#txtComentario').val(data[0]['comentario_documemto']);
    $('#txtDescripcion').val(data[0]['descripcion_documemto']);
}

var movimientoTipoColumna;
function cargarDetalleDocumento(data, dataMovimientoTipoColumna) {
    $('#datatable2').show();
    movimientoTipoColumna = dataMovimientoTipoColumna;

    if (!isEmptyData(data)) {
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
        //        if(existeColumnaCodigo(15)){
        if (!isEmpty(dataVisualizarDocumento.organizador)) {
            html += "<th style='text-align:center;'>Organizador</th>";
        }
        html += "<th style='text-align:center;'>Cantidad</th>";
        html += "<th style='text-align:center;'>Unidad de medida</th>";
        html += "<th style='text-align:center;'>Producto</th> ";
        if (existeColumnaCodigo(5)) {
            html += "<th style='text-align:center;'>Precio Unitario</th>";
            html += "<th style='text-align:center;'>Total</th>";
        }
        html += "</tr>";
        tHeadDetalle.append(html);


        //CUERPO DETALLE
        var tBodyDetalle = $('#tbodyDetalle');
        tBodyDetalle.empty();

        html = '';
        $.each(data, function (index, item) {
            html += "<tr>";
            //            if(existeColumnaCodigo(15)){
            if (!isEmpty(dataVisualizarDocumento.organizador)) {
                html += "<td>" + item.organizador + "</td>";
            }
            html += "<td style='text-align:right;'>" + item.cantidad + "</td>";
            html += "<td>" + item.unidadMedida + "</td>";
            html += "<td>" + item.bien_codigo + " | " + item.descripcion + "</td> ";
            if (existeColumnaCodigo(5)) {
                html += "<td style='text-align:right;'>" + item.precioUnitario + "</td>";
                html += "<td style='text-align:right;'>" + item.importe + "</td>";
            }
            html += "</tr>";
        });

        tBodyDetalle.append(html);
    } else {
        var table = $('#datatable2').DataTable();
        table.clear().draw();
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


function obtenerDocumentosRelacionados(documentoId) {
    loaderShow();
    ax.setAccion("obtenerDocumentosRelacionados");
    ax.addParamTmp("documentoId", documentoId);
    ax.consumir();
}
function obtenerDocumentoRelacion(documentoId) {
    $('#modalDocumentoRelacionado').modal('hide');
    obtenerDocumento(documentoId);
}
function onResponseObtenerDocumentosRelacionados(data) {
    $('#linkDocumentoRelacionado').empty();

    if (!isEmptyData(data)) {
        $('[data-toggle="popover"]').popover('hide');
        $.each(data, function (index, item) {
            $('#linkDocumentoRelacionado').append("<a onclick='obtenerDocumentoRelacion(" + item.documento_relacionado_id + ")' style='color:#0000FF'>[" + item.documento_tipo + ": " + item.serie_numero + "]</a><br>");
        });
        $('#modalDocumentoRelacionado').modal('show');
    } else {
        mostrarAdvertencia("No se encontró ningún documento relacionado con el actual.");
    }
}

var docId;
function visualizarDocumento(documentoId, movimientoId) {
    docId = documentoId;
    loaderShow();
    ax.setAccion("obtenerDocumentoRelacionVisualizar");
    ax.addParamTmp("documentoId", documentoId);
    ax.addParamTmp("movimientoId", movimientoId);
    ax.consumir();

    //    
}

function onResponseObtenerDocumentoRelacionVisualizar(data, documentoId) {
    resultadoObtenerEmails = null;
    dataVisualizarDocumento = data;
    $('[data-toggle="popover"]').popover('hide');
    cargarDataDocumento(data.dataDocumento, data.configuracionEditable, data.dataDocumentoAdjunto, (!isEmpty(data.dataVoucherContable) ? data.dataVoucherContable[0]['cuo'] : ''));
    cargarDataComentarioDocumento(data.comentarioDocumento);
    $('#tabDistribucion').show();
    $('#li_detalle').show();
    $('#li_distribucion').show();
    $('#li_voucher').show();
    //    $('#tabsDistribucionMostrar').show();

    if (!$("#div_contenido_tab").hasClass("tab-content")) {
        $("#div_contenido_tab").addClass("tab-content");
    }


    if (!isEmpty(data.detalleDocumento) && !isEmpty(data.dataDistribucionContable) && !isEmpty(data.dataVoucherContable)) {
        $('a[href="#detalle"]').click();
        cargarDetalleDocumento(data.detalleDocumento, data.dataMovimientoTipoColumna);
        cargarDistribucionDocumento(data.dataDistribucionContable);
        cargaVoucherContable(data.dataVoucherContable);
    } else if (isEmpty(data.detalleDocumento) && isEmpty(data.dataDistribucionContable) && isEmpty(data.dataVoucherContable)) {
        $('#tabDistribucion').hide();
    } else {
        if (!isEmpty(data.detalleDocumento)) {
            $('a[href="#detalle"]').click();
            cargarDetalleDocumento(data.detalleDocumento, data.dataMovimientoTipoColumna);
        } else {
            $('#li_detalle').hide();
            $('#datatable2').hide();
        }

        if (!isEmpty(data.dataDistribucionContable)) {
            $('a[href="#distribucion"]').click();
            cargarDistribucionDocumento(data.dataDistribucionContable);
        } else {
            $('#li_distribucion').hide();
            $('#datatableDistribucion2').hide();
        }

        if (!isEmpty(data.dataVoucherContable)) {
            $('a[href="#voucher"]').click();
            cargaVoucherContable(data.dataVoucherContable);
        } else {
            $('#li_voucher').hide();
            $('#datatableVocuher').hide();
        }
    }
    $('#modalDetalleDocumento').modal('show');
}



function cargarDataDocumento(data, configuracionEditable, dataDocumentoAdjunto) {
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
            if (item.tipo != 31) {

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

                    if (!isEmpty(valor)) {
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
                            case 32:
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
                                    valor += '<select name="cbo_' + item.documento_tipo_id + '" id="cbo_' + item.documento_tipo_id + '" class="select2"></select>';
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
                                case 32:
                                case 33:
                                case 34:
                                case 35:
                                    valor = formatearNumero(item.valor);
                                    break;
                                case 36:
                                    valor = (item.valor);
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
                                    select2.cargar("cbo_" + item.documento_tipo_id, itemEditable.data, "id", "descripcion");
                                    $("#cbo_" + item.documento_tipo_id).select2({
                                        width: '100%'
                                    });
                                    select2.asignarValor("cbo_" + item.documento_tipo_id, itemEditable.valor_id);
                                    break;
                                case 5:
                                    $("#cbo_" + item.documento_tipo_id).select2({
                                        width: '100%'
                                    }).on("change", function (e) {
                                        obtenerPersonaDireccion(e.val);
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
                            }
                        }
                    });

                }
            }
        });
        appendFormDetalle('</div>');
    }
}


function cargarDataComentarioDocumento(data) {
    $('#txtComentario').val(data[0]['comentario_documemto']);
}

function appendFormDetalle(html) {
    $("#formularioDetalleDocumento").append(html);
}
function cargarDistribucionDocumento(data) {
    $('#datatableDistribucion2').show();

    if (!isEmptyData(data)) {
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
    } else {
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



function onResponseValidacionSunat(data) {
    $('#tableValidacionSunat').empty();

    if (!isEmptyData(data)) {
        let htmlTable = '<table id="dataTableValidaSUNAT" class="table table-striped table-bordered">';
        htmlTable = htmlTable + '<thead><tr><th>#</th><th>Tipo documento</th><th>Serie - número</th><th>Proveedor</th><th>Respuesta SUNAT</th></tr></thead>';
        htmlTable = htmlTable + '<tbody>';
        $.each(data, function (index, item) {

            htmlTable = htmlTable + "<tr>";
            htmlTable = htmlTable + "<td>" + (index + 1) + "</td>";
            htmlTable = htmlTable + "<td>" + item.documento_tipo_descripcion + "</td>";
            htmlTable = htmlTable + "<td>" + item.serie_numero + "</td>";
            htmlTable = htmlTable + "<td>" + item.codigo_identificacion_persona + " | " + item.nombre_persona + "</td>";
            htmlTable = htmlTable + "<td>" + item.vout_respuesta + "</td>";
            htmlTable = htmlTable + "</tr>";
            //documento_tipo_descripcion
        });
        htmlTable = htmlTable + '</tbody>';
        $('#tableValidacionSunat').html(htmlTable);
        $('#modalValidacionSunat').modal('show');

        $('#dataTableValidaSUNAT').dataTable({
            destroy: true,
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

    } else {
        mostrarAdvertencia("No se encontró ningun documento por validar.");
    }
}

$(function () {
    $(":file").change(function () {
        //validar que la extension sea .xls
        var nombreArchivo = $(this).val().slice(12);
        var extension = nombreArchivo.substring(nombreArchivo.lastIndexOf('.') + 1).toLowerCase();

        if (extension != "xls" && extension != "txt") {
            $.Notification.autoHideNotify('warning', 'top-right', 'Validación', 'La extensión del excel tiene que ser .xls o .txt');
            return;
        }

        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $("#lblImportarArchivo").text(nombreArchivo);
                $('#secret').attr('value', e.target.result);
            };
            reader.readAsDataURL(this.files[0]);
            $fileupload = $('#file');
            $fileupload.replaceWith($fileupload.clone(true));
        }
    });
});

function cargarModalValidacionSistema() {
    $('#modalImportarArchivoValidacion').modal('show');
}

function validarDocumentoSistema() {
    let documentoTipoId = select2.obtenerValor("cboDocumentoTipo");
    let file = document.getElementById('secret').value;
    let documentoNombre = $("#lblImportarArchivo").text();
    if (isEmpty($('#secret').attr('value'))) {
        mostrarAdvertencia("No se especificó un archivo correcto!");
        return;
    }

    if (isEmpty(documentoTipoId)) {
        mostrarAdvertencia("No se seleccionó el tipo de documento!");
        return;
    }

    loaderShow('#modalImportarArchivoValidacion');
    ax.setAccion("validarDocumentoSistema");
    ax.addParam("file", file);
    ax.addParam("documentoTipoId", documentoTipoId);
    ax.addParam("documentoNombre", documentoNombre);
    ax.consumir();
}

function onResponseValidarComprobanteSistema(data) {
    $('#tableValidacionComprobanteSgi').empty();
    let documentoTipoDescripcion = select2.obtenerText("cboDocumentoTipo");
    if (!isEmptyData(data)) {
        let htmlTable = '<table id="datatableValidacion" class="table table-striped table-bordered">';
        htmlTable = htmlTable + '<thead><tr><th>#</th><th>Tipo documento</th><th>Serie - número</th><th>Proveedor</th><th>Registrado SGI</th><th>Respuesta SUNAT</th></tr></thead>';
        $.each(data, function (index, item) {

            htmlTable = htmlTable + "<tr>";
            htmlTable = htmlTable + "<td>" + (index + 1) + "</td>";
            htmlTable = htmlTable + "<td>" + documentoTipoDescripcion + "</td>";
            htmlTable = htmlTable + "<td>" + item.serie_numero + "</td>";
            htmlTable = htmlTable + "<td>" + item.ruc + " | " + item.persona_descripcion + "</td>";
            htmlTable = htmlTable + "<td>" + (item.vout_existe_documento == 1 ? "SI" : "NO") + "</td>";
            htmlTable = htmlTable + "<td>" + item.vout_respuesta + "</td>";
            htmlTable = htmlTable + "</tr>";
            //documento_tipo_descripcion
        });
        $('#tableValidacionComprobanteSgi').html(htmlTable);

        $('#datatableValidacion').dataTable({
            destroy: true,
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
    } else {
        mostrarAdvertencia("No se encontró ningun documento por validar.");
    }

}


function validarComprobanteSUNATXDocumentoId(documentoId) {
    loaderShow();
    ax.setAccion("validarComprobanteSUNATXDocumentoId");
    ax.addParamTmp("documentoId", documentoId);
    ax.consumir();
}


function onResponsevalidarComprobanteSUNAT(data) {
    if (data[0]['vout_exito'] == 1) {
        swal("Validación!", data[0]['vout_mensaje'], "success");

    } else {
        swal("Validación!", data[0]['vout_mensaje'], "warning");
    }
}
