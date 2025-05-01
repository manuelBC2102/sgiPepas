var documento_tipo = document.getElementById("documento_tipo").value;

$(document).ready(function () {
    criterioBusquedaDocumentos = [];
    cargarComponetentes();
    ax.setSuccess("onResponseAprobacionConsolidado");
    obtenerConfiguracionInicialListado();
    cambiarAnchoBusquedaDesplegable();
});

function onResponseAprobacionConsolidado(response) {
    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obtenerConfiguracionInicialListado':
                onResponseObtenerConfiguracionInicial(response.data);
                buscarPorCriterios();
                loaderClose();
                break;
            case 'visualizarSolicitudRequerimiento':
                onResponsevisualizarSolicitudRequerimiento(response.data, response.tag);
                if (response.tag == "Por Aprobar") {
                    var btn_aprobar_rechazar = '<button type="button" class="btn btn-danger " data-dismiss="modal" onclick="rechazar()" id="btn_rechazar"><i class="fa fa-ban"></i> Rechazar</button>';
                    btn_aprobar_rechazar += '<button type="button" class="btn btn-primary " data-dismiss="modal" onclick="aprobar()" id="btn_aprobador"><i class="fa fa-check"></i> Aprobar</button>';
                    $("#div_btn_aprobar").html(btn_aprobar_rechazar);
                } else {
                    $("#div_btn_aprobar").html("");
                }
                break;
            case 'visualizarRequerimiento':
                onResponsevisualizarRequerimiento(response.data, response.tag);
                if (response.tag == "Por Aprobar") {
                    var btn_aprobar_rechazar = '<button type="button" class="btn btn-danger " data-dismiss="modal" onclick="rechazar()" id="btn_rechazar"><i class="fa fa-ban"></i> Rechazar</button>';
                    btn_aprobar_rechazar += '<button type="button" class="btn btn-primary " data-dismiss="modal" onclick="aprobar()" id="btn_aprobador"><i class="fa fa-check"></i> Aprobar</button>';
                    $("#div_btn_aprobar").html(btn_aprobar_rechazar);
                } else {
                    $("#div_btn_aprobar").html("");
                }
                break;
            case 'visualizarOrdenCompraServicio':
                onResponseVisualizarOrdenCompraServicio(response.data, response.tag);
                if (response.tag == "Por Aprobar") {
                    var btn_aprobar_rechazar = '<button type="button" class="btn btn-danger " data-dismiss="modal" onclick="rechazar()" id="btn_rechazar"><i class="fa fa-ban"></i> Rechazar</button>';
                    btn_aprobar_rechazar += '<button type="button" class="btn btn-primary " data-dismiss="modal" onclick="aprobar()" id="btn_aprobador"><i class="fa fa-check"></i> Aprobar</button>';
                    $("#div_btn_aprobar").html(btn_aprobar_rechazar);
                } else {
                    $("#div_btn_aprobar").html("");
                }
                visualizarCuadroComparativo(response.data.documentoId);
                break;
            case 'aprobarRequerimiento':
                swal({
                    title: "Aprobación correcta",
                    text: response.data.mensaje,
                    type: "success",
                    confirmButtonColor: "#33b86c",
                    confirmButtonText: "Aceptar",
                    timer: 2500
                });
                var btnEnviar = document.getElementsByClassName('confirm')[0];
                btnEnviar.disabled = false;
                btnEnviar.innerHTML = 'Si!';
                buscarRequerimientos();
                break;
            case 'visualizarConsolidado':
                onResponsevisualizarConsolidado(response.data);
                break;
            case 'aprobarConsolidado':
                swal({
                    title: "Aprobación correcta",
                    text: response.data.mensaje,
                    type: "success",
                    confirmButtonColor: "#33b86c",
                    confirmButtonText: "Aceptar",
                    timer: 2500
                });
                var btnEnviar = document.getElementsByClassName('confirm')[0];
                btnEnviar.disabled = false;
                btnEnviar.innerHTML = 'Si!';
                $("#div_btn_aprobar").html("");
                buscarRequerimientos();
                break;
            case 'aprobarOrdenCompraServicio':
                swal({
                    title: "Aprobación correcta",
                    text: response.data.mensaje,
                    type: "success",
                    showCancelButton: false,
                    confirmButtonColor: "#33b86c",
                    confirmButtonText: "Aceptar",
                    timer: 2500
                });
                var btnEnviar = document.getElementsByClassName('confirm')[0];
                btnEnviar.disabled = false;
                btnEnviar.innerHTML = 'Si!';
                $("#div_btn_aprobar").html("");
                loaderClose();
                buscarRequerimientos();
                break;
            case 'rechazar':
                swal({
                    title: "Rechazo correcto",
                    text: response.data.mensaje,
                    type: "success",
                    showCancelButton: false,
                    confirmButtonColor: "#33b86c",
                    confirmButtonText: "Aceptar",
                    closeOnConfirm: false,
                    closeOnCancel: false
                });
                $('#modalAnulacion').modal('hide');
                $("#div_btn_aprobar").html("");
                loaderClose();
                buscarRequerimientos();
                break;
            case 'obtenerDocumentoAdjuntoXDocumentoId':
                if (!isEmpty(response.data)) {
                    lstDocumentoArchivos = response.data;
                }
                onResponseListarArchivosDocumento(response.data);
                loaderClose();
                break;
            case 'obtenerDocumentoAdjuntoXDistribucionPagos':
                if (!isEmpty(response.data)) {
                    lstDocumentoArchivos = response.data;
                }
                onResponseListarArchivosDistribucionPago(response.data);
                break;
            case 'abrirPdfCuadroComparativoCotizacion':
                loaderClose();
                abrirDocumentoPDF(response.data, 'vistas/com/movimiento/documentos/');
                break;
            case 'validarDocumentoEdicion':
                onResponseValidarDocumentoEdicion(response.data, response[PARAM_TAG]);
                loaderClose();
                break;
        }
    } else {
        switch (response[PARAM_ACCION_NAME]) {
            case 'aprobarConsolidado':
                var btnEnviar = document.getElementsByClassName('confirm')[0];
                btnEnviar.disabled = false;
                buscarRequerimientos();
                swal("Mensaje", response.message, "error");
                break;
            case 'rechazar':
                loaderClose();
                buscarRequerimientos();
                break;
            case 'aprobarOrdenCompraServicio':
                var btnEnviar = document.getElementsByClassName('confirm')[0];
                btnEnviar.disabled = false;
                buscarRequerimientos();
                swal("Mensaje", response.message, "error");
                break;
        }
    }
}

function cargarComponetentes() {
    cargarTitulo("titulo", "");
    select2.iniciar();
    datePiker.iniciarPorClase('fecha');
}

function obtenerConfiguracionInicialListado() {
    ax.setAccion("obtenerConfiguracionInicialListado");
    ax.consumir();
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

function buscarRequerimientos() {
    loaderShow();
    ax.setAccion("obtenerRequerimientos");
    ax.addParamTmp("criterios", criterioBusquedaDocumentos);
    $('#datatableRequermiento').dataTable({
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
        "order": [[3, "desc"]],
        "processing": true,
        "serverSide": true,
        "bFilter": false,
        "ajax": ax.getAjaxDataTable(),
        "scrollX": true,
        "columns": [
            { "data": "serie_numero", "class": "alignCenter" },
            { "data": "tipo_requerimiento", "class": "alignCenter" },
            { "data": "area_descripcion", "class": "alignCenter" },
            { "data": "solicitante_nombre_completo", "class": "alignCenter" },
            { "data": "total", "class": "alignRight" },
            { "data": "fecha_creacion", "class": "alignCenter" },
            { "data": "usuario_creacion", "class": "alignCenter" },
            { "data": "estado_descripcion", "class": "alignCenter" },
            { "data": "progreso", "class": "alignCenter" },
            { "data": "acciones", "class": "alignCenter" }

        ],
        columnDefs: [
            {
                "render": function (data, type, row) {
                    return "<strong>" + row.documento_tipo_descripcion + " </strong>" + (isEmpty(data) ? "" : "<strong> : </strong> " + data + (row.urgencia == "Si" ? " (Urgencia)" : ""));
                },
                "targets": 1
            },
            {
                "render": function (data, type, row) {
                    return (isEmpty(data)) ? '' : data.replace(" 00:00:00", "");
                },
                "targets": 5
            },
            {
                "render": function (data, type, row) {
                    return devolverDosDecimales(data);
                },
                "targets": 4
            },
            {
                "render": function (data, type, row) {
                    var acciones = "";
                    if (documento_tipo == ORDEN_COMPRA) {
                        acciones += "<a href='#' onclick='abrirPdfCuadroComparativoCotizacion(" + row.id + ")'><i class='fa fa-print' style='color:black;' title='Ver pdf de cuadro comparativo'></i></a>&nbsp;";
                    }
                    var tabla = $('#datatableRequermiento').DataTable();
                    if (documento_tipo == ORDEN_COMPRA || documento_tipo == ORDEN_SERVICIO) {
                        setTimeout(function () {
                            tabla.column(2).visible(false);
                        }, 100);
                        $("#th_persona").html("Razón Social");
                    } else {
                        setTimeout(function () {
                            tabla.column(4).visible(false);
                        }, 100);
                    }
                    $('.title').html("Aprobación de " + row.documento_tipo_descripcion);
                    return data + "" + acciones;
                },
                "targets": 9
            },
        ],
        "dom": '<"top">rt<"bottom"<"col-md-3"l><"col-md-9"p><"col-md-12"i>><"clear">',
        destroy: true,
        "drawCallback": function (settings) {
            var api = this.api();
            var dataCount = api.data().count();
            if (dataCount === 0) {
                var tabla = $('#datatableRequermiento').DataTable();
                if (documento_tipo == ORDEN_COMPRA || documento_tipo == ORDEN_SERVICIO) {

                } else {

                }
                setTimeout(function () {
                    tabla.column(4).visible(false);
                }, 100);
            }
        }
    });
    loaderClose();
}

function actualizarBusqueda() {
    buscarPorCriterios();
}

$('.dropdown-menu').click(function (e) {
    if (e.target.id != "btnBusqueda" && e.delegateTarget.id != "listaEmpresa") {
        e.stopPropagation();
    }
});

$('.fecha').datepicker({
    isRTL: false,
    format: 'dd/mm/yyyy',
    autoclose: true,
    language: 'es'
}).on('changeDate', function (ev) {
    setTimeout(function () {
        $("#spanBuscador").addClass('open');
    }, 5);
});

function onResponseObtenerConfiguracionInicial(data) {
    //desplegable de documentos

    $('#fecha_programacion').val(datex.getNow1());
    $('#cboArea').append('<option value=0>Todos</option>');
    if (!isEmpty(data.area)) {
        $.each(data.area, function (index, item) {
            $('#cboArea').append('<option value="' + item['id'] + '">' + item['descripcion'] + '</option>');
        });
    }
    $('#cboTipoRequerimiento').append('<option value=0>Todos</option>');
    if (!isEmpty(data.tipo_requerimiento)) {
        $.each(data.tipo_requerimiento, function (index, item) {
            $('#cboTipoRequerimiento').append('<option value="' + item['descripcion'] + '">' + item['descripcion'] + '</option>');
        });
    } else {
        $('#liarea_tipo').hide();
    }
    select2.asignarValor('cboTipoRequerimiento', 0);
    if (!isEmpty(data.getarea)) {
        select2.asignarValor('cboArea', data.getarea);
        $("#cboArea").attr('disabled', 'disabled');
    } else {
        select2.asignarValor('cboArea', 0);
    }

    fechasActuales();
}

//here
function buscarPorCriterios() {
    var fechaEmision = {
        inicio: $('#inicioFechaEmision').val(),
        fin: $('#finFechaEmision').val()
    };
    var area = select2.obtenerValor('cboArea');
    var requerimiento_tipo = select2.obtenerValor('cboTipoRequerimiento');
    var tipo = select2.obtenerValor('cboTipo');
    var estado = select2.obtenerValor('cboEstado');
    llenarParametrosBusqueda(fechaEmision, documento_tipo, area, requerimiento_tipo, tipo, estado);

    buscarRequerimientos();
}

var criterioBusquedaDocumentos = {};

function llenarParametrosBusqueda(fechaEmision, documento_tipo, area, requerimiento_tipo, tipo, estado) {
    criterioBusquedaDocumentos = {};
    criterioBusquedaDocumentos.fechaEmision = fechaEmision;
    criterioBusquedaDocumentos.documento_tipo = documento_tipo;
    criterioBusquedaDocumentos.area = area;
    criterioBusquedaDocumentos.requerimiento_tipo = requerimiento_tipo;
    criterioBusquedaDocumentos.tipo = tipo;
    criterioBusquedaDocumentos.estado = estado;
}

function cambiarAnchoBusquedaDesplegable() {
    var ancho = $("#divBuscador").width();
    $("#ulBuscadorDesplegable").width((ancho - 5) + "px");
}


function limpiarBuscadores() {
    $('#inicioFechaEmision').val('');
    $('#finFechaEmision').val('');

    criterioBusquedaDocumentos = {};
}

function onResponsevisualizarSolicitudRequerimiento(data, documento_estado) {
    var cont = 0;
    if (documento_estado == "Aprobado" || documento_estado == "Rechazado") {
        $("#div_btn_aprobar").html("");
    }

    cargarDataDocumento(data.dataDocumento);
    $("#tableRequerimiento").show();
    if (!isEmpty(data.detalle)) {
        $("#modalDetalle").modal('show');
        $('#dtmodalDetalleRequerimiento').dataTable({
            "processing": true,
            "ordering": false,
            "data": data.detalle,
            "order": [[0, "asc"]],
            "columns": [
                { "data": "movimiento_bien_id", "width": "5%", "sClass": "alignCenter" },
                { "data": "bien_descripcion", "width": "24%", "sClass": "alignLeft" },
                { "data": "cantidad", "width": "9%", "sClass": "alignCenter" },
                { "data": "unidad_medida_descripcion", "width": "10%", "sClass": "alignCenter" },
                { "data": "centro_costo_descripcion", "width": "10%", "sClass": "alignCenter" },
                { "data": "movimiento_bien_comentario", "width": "20%", "sClass": "alignCenter" },
                { "data": "movimiento_bien_detalle", "width": "10%", "sClass": "alignCenter" },
            ],
            columnDefs: [
                {
                    "render": function (data, type, row) {
                        cont = 1 + cont;
                        return cont;
                    },
                    "targets": 0
                },
                {
                    "render": function (data, type, row) {
                        var bien_descripcion = row.bien_codigo + " | " + row.bien_descripcion;
                        if (!isEmpty(bien_descripcion)) {
                            if (bien_descripcion.length > 60) {
                                bien_descripcion = bien_descripcion.substring(0, 60) + '...';
                            }
                        }
                        return bien_descripcion;
                    },
                    "targets": 1
                },
                {
                    "render": function (data, type, row) {
                        return devolverDosDecimales(data);
                    },
                    "targets": [2]
                },
                {
                    "render": function (data, type, row) {
                        var btn_upload = "";
                        if(!isEmpty(data)){
                            btn_upload = "&nbsp;<a href='#' onclick='verImagenPdf("+ row.movimiento_bien_id +")'><i class='fa fa-cloud-download' style='color:blue;' title='"+ data[0].valor_detalle +"'></i></a> <input type='hidden' id='nombreAdjunto_"+ row.movimiento_bien_id +"' name='nombreAdjunto_"+ row.movimiento_bien_id +"' value='"+ data[0].valor_detalle +"'/>";
                        }
                        return btn_upload;
                    },
                    "targets": 6
                }

            ],
            "dom": '<"top">rt<"bottom"<"col-md-3"l><"col-md-9"p><"col-md-12"i>><"clear">',
            destroy: true
        });
        loaderClose();
    }
}

function onResponsevisualizarRequerimiento(data, documento_estado) {
    var cont = 0;
    if (documento_estado == "Aprobado" || documento_estado == "Rechazado") {
        $("#div_btn_aprobar").html("");
    }

    cargarDataDocumento(data.dataDocumento);
    $("#tableRequerimientoArea").show();
    if (!isEmpty(data.detalle)) {
        $("#modalDetalle").modal('show');
        $('#dtmodalDetalleRequerimientoArea').dataTable({
            "processing": true,
            "ordering": false,
            "data": data.detalle,
            "order": [[0, "asc"]],
            "columns": [
                { "data": "movimiento_bien_id", "width": "5%", "sClass": "alignCenter" },
                { "data": "bien_descripcion", "width": "24%", "sClass": "alignLeft" },
                { "data": "cantidad", "width": "9%", "sClass": "alignCenter" },
                { "data": "cantidad_solicitada", "width": "9%", "sClass": "alignCenter" },
                { "data": "cantidad_solicitada", "width": "9%", "sClass": "alignCenter" },
                { "data": "unidad_medida_descripcion", "width": "10%", "sClass": "alignCenter" },
                { "data": "es_compra", "width": "10%", "sClass": "alignCenter" },
            ],
            columnDefs: [
                {
                    "render": function (data, type, row) {
                        cont = 1 + cont;
                        return cont;
                    },
                    "targets": 0
                },
                {
                    "render": function (data, type, row) {
                        var bien_descripcion = row.bien_codigo + " | " + row.bien_descripcion;
                        if (!isEmpty(bien_descripcion)) {
                            if (bien_descripcion.length > 60) {
                                bien_descripcion = bien_descripcion.substring(0, 60) + '...';
                            }
                        }
                        return bien_descripcion;
                    },
                    "targets": 1
                },
                {
                    "render": function (data, type, row) {
                        return devolverDosDecimales(data - row.cantidad);
                    },
                    "targets": 3
                },
                {
                    "render": function (data, type, row) {
                        return devolverDosDecimales(data);
                    },
                    "targets": [2, 4]
                },
                {
                    "render": function (data, type, row) {
                        return data == 1 ? "Si" : "No";
                    },
                    "targets": 6
                }

            ],
            "dom": '<"top">rt<"bottom"<"col-md-3"l><"col-md-9"p><"col-md-12"i>><"clear">',
            destroy: true
        });
        loaderClose();
    }
}

function onResponsevisualizarConsolidado(data) {
    var cont = 0;

    $("#theadConsolidado").empty();
    $("#tbodyDetalle").empty();

    var htmlTable = "";
    htmlTable += "<tr>" +
        "<th style='text-align:center;' rowspan='2'>#</th>" +
        "<th style='text-align:center;' rowspan='2'>Producto</th>" +
        "<th style='text-align:center;' rowspan='2'>Cantidad</th>" +
        "<th style='text-align:center;' rowspan='2'>U. Medida</th>";
    data.documento_detalle.forEach(function (proveedorID, idx) {
        var textoOriginal = (proveedorID.persona).split("|");
        var textProveedor = textoOriginal[1].length > 30
            ? textoOriginal[1].substring(0, 30) + '…'
            : textoOriginal[1];
        htmlTable += "<th style='text-align:center;' colspan='2'>" + textoOriginal[0] + "<br>" + textProveedor + "</th>";
    });
    htmlTable += "</tr>";

    htmlTable += "<tr>";
    data.documento_detalle.forEach(function (proveedorID, idx) {
        htmlTable += "<th style='text-align:center;' class='postor_precio'>Precio</th>" +
            "<th style='text-align:center;' class='postor_subtotal'>Sub. Total</th>";
    });
    htmlTable += "</tr>";
    $("#theadConsolidado").append(htmlTable);

    var tbodyDetalle = "";
    var totalPostores = [];

    data.detalle.forEach(function (detalleID, idx) {
        tbodyDetalle += "<tr>";

        tbodyDetalle += "<td style='text-align:right;'>" + (idx + 1) + "</td>";
        tbodyDetalle += "<td>" + detalleID.bien_codigo + " | " + detalleID.bien_descripcion + "</td> ";
        tbodyDetalle += "<td style='text-align:right;'>" + devolverDosDecimales(detalleID.cantidad) + "</td>";
        tbodyDetalle += "<td style='text-align:center;'>" + detalleID.simbolo + "</td>";

        data.documento_detalle.forEach(function (proveedorID, idx) {
            var total_ = 0;
            $.each(detalleID.movimiento_bien_detalle, function (indexBD, itemBD) {
                if (itemBD.columna_codigo == 37) {
                    if (proveedorID.persona_id == itemBD.valor_extra) {
                        var color_ganador = "";
                        if (detalleID.postor_ganador_id == proveedorID.persona_id) {
                            color_ganador = "background-color:rgb(0, 254, 127);";
                        }
                        tbodyDetalle += "<td style='text-align:center;" + color_ganador + "'>" + devolverDosDecimales(itemBD.valor_detalle, 2) + "</td>";
                        tbodyDetalle += "<td style='text-align:center;" + color_ganador + "'>" + devolverDosDecimales((detalleID.cantidad * itemBD.valor_detalle), 2) + "</td>";
                        total_ = total_ + (detalleID.cantidad * itemBD.valor_detalle);
                        totalPostores[idx] = (isEmpty(totalPostores[idx]) ? 0 : totalPostores[idx]) + total_;
                    }
                }
            });
        });

        tbodyDetalle += "</tr>";
    });

    $("#tbodyDetalle").append(tbodyDetalle);

    var filaExtratfoot = '';
    $("#dtmodalDetalleConsolidado").css({ "overflow-x": "auto", "display": "block" });
    var tfootDetalle = $('#tfootDetalle');
    tfootDetalle.empty();

    var textsubtotal = "";
    var textigv = "";
    var texttotal = "";
    var monedaText = "";
    var texttotalSoles = "";
    data.documento_detalle.forEach((proveedor, idx) => {
        var monto = totalPostores[idx];
        var tipoCambio = proveedor.moneda_id == 4 ? proveedor.tipo_cambio : 1;
        var esSinIGV = proveedor.igv == 0;
        var subTotal = esSinIGV ? monto : monto / 1.18;
        var total = esSinIGV ? subTotal * 1.18 : monto;
        var igv = total - subTotal;
        var totalSoles = total * tipoCambio;

        textsubtotal += `<th style='text-align:right' colspan='2'> ${devolverDosDecimales(subTotal, 2)}</th>`;
        textigv += `<th style='text-align:right' colspan='2'> ${devolverDosDecimales(igv, 2)}</th>`;
        texttotal += `<th style='text-align:right' colspan='2'> $${proveedor.moneda_id == 2 ? 0 : devolverDosDecimales(total, 2)}</th>`;
        texttotalSoles += `<th style='text-align:right' colspan='2'>S/ ${devolverDosDecimales(totalSoles, 2)}</th>`;
    });

    filaExtratfoot += '<tr >' +
        '<th colspan="4" style="text-align:right">Sub Total:</th>' + textsubtotal;
    filaExtratfoot += '</tr>';
    filaExtratfoot += '<tr>' +
        '<th colspan="4" style="text-align:right">IGV (18%):</th>' + textigv;
    filaExtratfoot += '</tr>';
    filaExtratfoot += '<tr>' +
        '<th colspan="4" style="text-align:right">Totales Dolares:</th>' + texttotal;
    filaExtratfoot += '</tr>';

    filaExtratfoot += '<tr>' +
        '<th colspan="4" style="text-align:right">Totales Soles:</th>' + texttotalSoles;
    filaExtratfoot += '</tr>';

    tfootDetalle.append(filaExtratfoot);

    cargarDataDocumento(data.dataDocumento);
    $("#tableConsolidado").show();
    // $("#tableOrdenCompraServicio").hide();
    if (!isEmpty(data.detalle)) {
        $("#modalDetalle").modal('show');

        loaderClose();
    } else {
        var table = $('#dtmodalDetalleConsolidado').DataTable();
        table.clear().draw();
    }
}

function onResponseVisualizarOrdenCompraServicio(data, documento_estado) {
    var cont = 0;
    if (documento_estado == "Aprobado" || documento_estado == "Rechazado") {
        $("#div_btn_aprobar").html("");
    }

    cargarDataDocumento(data.dataDocumento);
    $("#tableOrdenCompraServicio").show();
    // $("#tableConsolidado").hide();
    if (!isEmpty(data.detalle)) {
        $("#modalDetalle").modal('show');
        $('#dtmodalDetalletableOrdenCompraServicio').dataTable({
            "processing": true,
            "ordering": false,
            "data": data.detalle,
            "order": [[0, "asc"]],
            "columns": [
                { "data": "movimiento_bien_id", "width": "5%", "sClass": "alignCenter" },
                { "data": "bien_descripcion", "width": "24%", "sClass": "alignLeft" },
                { "data": "unidad_medida_descripcion", "width": "10%", "sClass": "alignCenter" },
                { "data": "cantidad", "width": "9%", "sClass": "alignCenter" },
                { "data": "valor_monetario", "width": "9%", "sClass": "alignCenter" },
                { "data": "sub_total", "width": "9%", "sClass": "alignCenter" },
            ],
            columnDefs: [
                {
                    "render": function (data, type, row) {
                        cont = 1 + cont;
                        return cont;
                    },
                    "targets": 0
                },
                {
                    "render": function (data, type, row) {
                        var bien_descripcion = row.bien_codigo + " | " + row.bien_descripcion;
                        if (!isEmpty(bien_descripcion)) {
                            if (bien_descripcion.length > 60) {
                                bien_descripcion = bien_descripcion.substring(0, 60) + '...';
                            }
                        }
                        return bien_descripcion;
                    },
                    "targets": 1
                },
                {
                    "render": function (data, type, row) {
                        return devolverDosDecimales(data);
                    },
                    "targets": [3, 4, 5]
                }

            ],
            "dom": '<"top">rt<"bottom"<"col-md-3"l><"col-md-9"p><"col-md-12"i>><"clear">',
            destroy: true
        });
        loaderClose();
    }
}

var documentoTipoId = null;
var documento_tipo_descripcionText = null;
function visualizar(id, movimientoId, documento_estado_id, documento_estado, documento_tipo_id, documento_tipo_descripcion) {
    $("#visualizarDocumentoId").val(id);
    documento_tipo_descripcionText = documento_tipo_descripcion;
    loaderShow();
    documentoTipoId = documento_tipo_id;
    if (documento_tipo_id == SOLICITUD_REQUERIMIENTO) { //Solicitud Requerimiento
        ax.setAccion("visualizarSolicitudRequerimiento");
        ax.setTag(documento_estado);
    } else if (documento_tipo_id == REQUERIMIENTO_AREA) { //Consolidar Requerimientos Area
        ax.setAccion("visualizarRequerimiento");
        ax.setTag(documento_estado);
    } else if (documento_tipo_id == ORDEN_COMPRA || documento_tipo_id == ORDEN_SERVICIO) { //Orden de Compra o Servicio
        ax.setAccion("visualizarOrdenCompraServicio");
        ax.setTag(documento_estado);
    }
    ax.addParamTmp("id", id);
    ax.addParamTmp("movimientoId", movimientoId);
    ax.consumir();
}

function devolverDosDecimales(num) {
    return redondearNumero(num).toFixed(2);
}

function cargarDataDocumento(data, documento_estado_id) {
    $("#formularioDetalleDocumento").empty();

    if (!isEmpty(data)) {
        $('#nombreDocumentoTipo').html(data[0]['nombre_documento']);
        // Mostraremos la data en filas de dos columnas
        $.each(data, function (index, item) {
            if (item.tipo == 42) {
                $("#tituloOrden").html("Detalle de Orden de " + item.valor);
            }

            appendFormDetalle('</div>');
            appendFormDetalle('<div class="row">');

            var html = '';
            if (!isEmpty(item.valor)) {
                html = '<div class="form-group col-md-12"><div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">' +
                    '<label>' + item.descripcion + '</label>' +
                    '</div><div class="col-lg-8 col-md-8 col-sm-6 col-xs-6">';
            } else {
                if (item.descripcion == 'Postor N° 1') {
                    $("#selectPostor1").prop("disabled", true);
                } else if (item.descripcion == 'Postor N° 2') {
                    $("#selectPostor2").prop("disabled", true);
                } else if (item.descripcion == 'Postor N° 3') {
                    $("#selectPostor3").prop("disabled", true);
                }
            }

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

function appendFormDetalle(html) {
    $("#formularioDetalleDocumento").append(html);
}

function fechaArmada(valor) {
    var fecha = separarFecha(valor);

    return fecha.dia + "/" + fecha.mes + "/" + fecha.anio;
}

function aprobar() {
    id = $("#visualizarDocumentoId").val();
    if (documentoTipoId == SOLICITUD_REQUERIMIENTO || documentoTipoId == ORDEN_SERVICIO || documentoTipoId == REQUERIMIENTO_AREA) { //Requerimiento
        swal({
            title: " ¿Desea continuar?",
            text: "Se procede aprobar " + documento_tipo_descripcionText,
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#33b86c",
            confirmButtonText: "Si!",
            cancelButtonColor: '#d33',
            cancelButtonText: "No!",
            closeOnConfirm: false,
            closeOnCancel: true
        }, function (isConfirm) {
            if (isConfirm) {
                loaderShow();
                var btnEnviar = document.getElementsByClassName('confirm')[0];
                btnEnviar.disabled = true;
                btnEnviar.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Enviando...';
                ax.setAccion("aprobarRequerimiento");
                ax.addParamTmp("id", id);
                ax.consumir();
            } else {
                loaderClose();
            }
        });
    } else if (documentoTipoId == GENERAR_COTIZACION) { // Consolidado RQ
        var checked1 = $('#selectPostor1').is(":checked");
        var checked2 = $('#selectPostor2').is(":checked");
        var checked3 = $('#selectPostor3').is(":checked");
        var cantidad = seleccionados = $('input:checkbox:checked').length;

        if (cantidad == 0) {
            mostrarAdvertencia('Debe seleccionar una cotización');
            return;
        } else if (cantidad > 0 && cantidad <= 1) {
            swal({
                title: " ¿Desea continuar?",
                text: "Se procede aprobar el consolidado de requerimientos",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#33b86c",
                confirmButtonText: "Si!",
                cancelButtonColor: '#d33',
                cancelButtonText: "No!",
                closeOnConfirm: false,
                closeOnCancel: true
            }, function (isConfirm) {
                if (isConfirm) {
                    loaderShow();
                    var btnEnviar = document.getElementsByClassName('confirm')[0];
                    btnEnviar.disabled = true;
                    btnEnviar.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Enviando...';
                    ax.setAccion("aprobarConsolidado");
                    ax.addParamTmp("id", id);
                    ax.addParamTmp("checked1", checked1);
                    ax.addParamTmp("checked2", checked2);
                    ax.addParamTmp("checked3", checked3);
                    ax.consumir();
                } else {
                    loaderClose();
                }
            });
        } else {
            mostrarAdvertencia('Debe seleccionar solo una cotización');
            loaderClose();
            return;
        }
    } else if (documentoTipoId == ORDEN_COMPRA) { // Orden de compra o Servicio
        swal({
            title: " ¿Desea continuar?",
            text: "Se procede aprobar la " + documento_tipo_descripcionText,
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#33b86c",
            confirmButtonText: "Si!",
            cancelButtonColor: '#d33',
            cancelButtonText: "No!",
            closeOnConfirm: false,
            closeOnCancel: true
        }, function (isConfirm) {
            if (isConfirm) {
                loaderShow();
                var btnEnviar = document.getElementsByClassName('confirm')[0];
                btnEnviar.disabled = true;
                btnEnviar.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Enviando...';
                ax.setAccion("aprobarOrdenCompraServicio");
                ax.addParamTmp("id", id);
                ax.consumir();
            } else {
                loaderClose();
            }
        });
    }
}

var documentoId;
function rechazar() {
    id = $("#visualizarDocumentoId").val();
    loaderShow();
    swal({
        title: " ¿Desea continuar?",
        text: "Se va a rechazar el consolidado",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#33b86c",
        confirmButtonText: "Si, rechazar!",
        cancelButtonColor: '#d33',
        cancelButtonText: "No, cancelar !",
    }, function (isConfirm) {
        if (isConfirm) {
            if (documentoTipoId == SOLICITUD_REQUERIMIENTO) {
                $('#tituloModalAnulacion').html("Rechazar Solicitud requerimiento");
            } else if (documentoTipoId == ORDEN_COMPRA) {
                $('#tituloModalAnulacion').html("Rechazar Orden de compra");
            } else if (documentoTipoId == REQUERIMIENTO_AREA) {
                $('#tituloModalAnulacion').html("Rechazar Requerimiento por área");
            }
            $('#txtMotivoRechazo').val('');
            $('#modalAnulacion').modal('show');
            documentoId = id;
        } else {
            loaderClose();
        }
    });
}


function rechazarComentario() {
    var motivoRechazo = $('#txtMotivoRechazo').val();
    motivoRechazo = motivoRechazo.trim();

    if (isEmpty(motivoRechazo)) {
        mostrarAdvertencia('Ingrese motivo de rechazo');
        return;
    } else {
        loaderShow('#modalAnulacion');
        ax.setAccion("rechazar");
        ax.addParamTmp("documentoId", documentoId);
        ax.addParamTmp("motivoRechazo", motivoRechazo);
        ax.consumir();
    }
}

function limpiarFormularioRechazo() {
    $('#txtMotivoRechazo').val("");
    loaderClose();
}

function fechasActuales() {
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

var lstDocumentoArchivos = [];
function archivosAdjuntos(id, movimientoId) {
    loaderShow();
    $("#dataList2").empty();
    $("#modalDetalleArchivos").modal('show');
    $("#btn_agregarActualizar").val(id);

    ax.setAccion("obtenerDocumentoAdjuntoXDocumentoId");
    ax.addParamTmp("documentoId", id);
    ax.consumir();
}

function onResponseListarArchivosDocumento(data) {

    $("#dataList2").empty();
    var cuerpo_total = "";
    var cuerpo = "";
    var cabeza = "<table id='datatable3' class='table table-striped table-bordered'>"
        + "<thead>"
        + "<tr>"
        + "<th style='text-align:center; vertical-align: middle; width:8%'>#</th>"
        + "<th style='text-align:center; vertical-align: middle;'>Tipo Archivo</th>"
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
                + "<td style='text-align:center;'>" + item.tipo_archivo + "</td>"
                + "<td style='text-align:center;'>" + item.archivo + "</td>";

            cuerpo += "<td style='text-align:center;'>"
                + "<a href='" + item.data + "' download='" + item.archivo + "' target='_blank'><i class='fa fa-cloud-download' style='color:#1ca8dd;'></i></a>&nbsp;\n"
            // + "<a href='#' onclick='eliminarDocumento(\"" + item.id + "\")'><i class='fa fa-trash-o' style='color:#cb2a2a;'></i></a>&nbsp;\n";
            cuerpo += "</td>"
                + "</tr>";
            cuerpo_total += cuerpo;
        });
    }
    var pie = '</table>';
    var html = cabeza + cuerpo_total + pie;
    $("#dataList2").append(html);
    $("#datatable3").DataTable();
}

function visualizarCuadroComparativo(documentoId) {
    loaderShow();
    ax.setAccion("visualizarConsolidado");
    ax.addParamTmp("documentoId", documentoId);
    ax.consumir();
}

function subirArchivosAdjuntosDistribucionPagos(id, documentoId) {
    loaderShow();
    $("#dataList2DistribucionPagos").empty();
    $("#modalDetalle").modal('hide');
    $("#modalDetalleArchivosDistribucionPagos").modal('show');
    distribucionPagosIdGlobal = id;
    documentoIdGlobal = documentoId;

    ax.setAccion("obtenerDocumentoAdjuntoXDistribucionPagos");
    ax.addParamTmp("distribucionPagoId", id);
    ax.consumir();
}

function onResponseListarArchivosDistribucionPago(data) {

    $("#dataList2DistribucionPagos").empty();
    var cuerpo_total = "";
    var cuerpo = "";
    var cabeza = "<table id='datatable3DistribucionPagos' class='table table-striped table-bordered'>"
        + "<thead>"
        + "<tr>"
        + "<th style='text-align:center; vertical-align: middle; width:8%'>#</th>"
        + "<th style='text-align:center; vertical-align: middle;'>Tipo Archivo</th>"
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
                + "<td style='text-align:center;'>" + item.tipo_archivo + "</td>"
                + "<td style='text-align:center;'>" + item.archivo + "</td>";

            cuerpo += "<td style='text-align:center;'>"
                + "<a href='" + item.data + "' download='" + item.archivo + "' target='_blank'><i class='fa fa-cloud-download' style='color:#1ca8dd;'></i></a>&nbsp;\n"
            cuerpo += "</td>"
                + "</tr>";
            cuerpo_total += cuerpo;
        });
    }
    var pie = '</table>';
    var html = cabeza + cuerpo_total + pie;
    $("#dataList2DistribucionPagos").append(html);
    $("#datatable3DistribucionPagos").DataTable();
}

function abrirPdfCuadroComparativoCotizacion(documentoId) {
    loaderShow();
    ax.setAccion("abrirPdfCuadroComparativoCotizacion");
    ax.addParamTmp("documentoId", documentoId);
    ax.consumir();
}

function ocultarModalDistribucionPagos() {
    $("#modalDetalleArchivosDistribucionPagos").modal('hide'); // Oculta el modal actual
    // Espera a que el modal termine de cerrarse antes de abrir el siguiente
    $('#modalDetalleArchivosDistribucionPagos').on('hidden.bs.modal', function () {
        $("#modalDetalle").modal('show');
        $(this).off('hidden.bs.modal'); // Evita que se dispare varias veces
    });
}

function abrirDocumentoPDF(data, contenedor) {
    var link = document.createElement("a");
    link.download = data.nombre + '.pdf';
    link.href = contenedor + data.pdf;
    link.click();

    setTimeout(function () {
        //eliminarPDF(data.url);
        eliminarPDF(contenedor + data.pdf);
    }, 3000);
}

function eliminarPDF(url) {
    ax.setAccion("eliminarPDF");
    ax.addParamTmp("url", url);
    ax.consumir();
}

function editarDocumento(documentoId, opcionId) {
    loaderShow();
    ax.setAccion("validarDocumentoEdicion");
    ax.addParamTmp("documentoId", documentoId);
    ax.setTag(documentoId);
    ax.setOpcion(opcionId);
    ax.consumir();
}

function onResponseValidarDocumentoEdicion(data, documentoId) {
    //    console.log(documentoId);
    if (data.exito == 1) {
        loaderShow();
        cargarDivTitulo('#window', 'vistas/com/compraServicio/compra_form_tablas_edit.php?tipoInterfaz=2&documentoId=' + documentoId, "Editar Solicitud de requerimiento");
        active(392,391);
        commonVars.titulo = "Solicitud de requerimiento";
    } else {
        mostrarAdvertencia(data.mensaje);
    }
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