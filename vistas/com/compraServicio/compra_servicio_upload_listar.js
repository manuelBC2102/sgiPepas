var documento_tipo = document.getElementById("documento_tipo").value;

$(document).ready(function () {
    criterioBusquedaDocumentos = [];
    cargarComponetentes();
    ax.setSuccess("onResponseAprobacionOrdenCompraServicio");
    obtenerConfiguracionInicialListadoDocumentos();
    cambiarAnchoBusquedaDesplegable();
    iniciarArchivoAdjuntoMultiple();
});

function onResponseAprobacionOrdenCompraServicio(response) {
    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obtenerConfiguracionInicialListadoDocumentos':
                onResponseObtenerConfiguracionInicialListadoDocumentos(response.data);
                buscarPorCriterios();
                loaderClose();
                break;
            case 'visualizarOrdenCompraServicio':
                onResponsevisualizarOrdenCompraServicio(response.data, response.tag);
                break;
            case 'obtenerDocumentoAdjuntoXDocumentoId':
                if (!isEmpty(response.data)) {
                    lstDocumentoArchivos = response.data;
                }
                onResponseListarArchivosDocumento(response.data);
                break;
            case 'cargarArchivosAdjuntos':
                swal({
                    title: response.data.bandera_respuesta == 1 ? "Respuesta" : "Documentos actualizados",
                    text: response.data.mensaje,
                    html: true,
                    type: response.data.bandera_respuesta == 1 ? "warning" : "success",
                    showCancelButton: false,
                    confirmButtonColor: "#33b86c",
                    confirmButtonText: "Aceptar",
                    closeOnConfirm: false,
                    closeOnCancel: false
                });
                lstDocumentoArchivos = response.data.data;
                onResponseListarArchivosDocumento(response.data.data);
                break;
            case 'aprobarRechazar':
                $("#modalRechazarDocumento").modal('hide');
                swal({
                    title: "Documentos " + response.data.accion + " correctamente",
                    text: response.data.mensaje,
                    type: "success",
                    showCancelButton: false,
                    confirmButtonColor: "#33b86c",
                    confirmButtonText: "Aceptar",
                    closeOnConfirm: false,
                    closeOnCancel: false
                });
                lstDocumentoArchivos = response.data.data;
                onResponseListarArchivosDocumento(response.data.data);
                break;
        }
    } else {
        switch (response[PARAM_ACCION_NAME]) {
            case 'cargarArchivosAdjuntos':
                subirArchivosAdjuntos(documentoIdGlobal, monto_totalGlobal, moneda_descripcionGlobal);
                lstDocumentoArchivos = [];
                break;

        }
    }
}

function cargarComponetentes() {
    cargarTitulo("titulo", "");
    select2.iniciar();
    datePiker.iniciarPorClase('fecha');
}

function obtenerConfiguracionInicialListadoDocumentos() {
    ax.setAccion("obtenerConfiguracionInicialListadoDocumentos");
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

function buscarOrdenCompraServicio() {
    loaderShow();
    ax.setAccion("obtenerOrdenCompraServicio");
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
            { "data": "documento_tipo_descripcion", "class": "alignCenter" },
            { "data": "proveedor_nombre_completo", "class": "alignCenter" },
            { "data": "fecha_creacion", "class": "alignCenter" },
            { "data": "usuario_creacion", "class": "alignCenter" },
            { "data": "estado_descripcion", "class": "alignCenter" },
            { "data": "id", "class": "alignCenter" }

        ],
        columnDefs: [
            {
                "render": function (data, type, row) {
                    return (isEmpty(data)) ? '' : data.replace(" 00:00:00", "");
                },
                "targets": 3
            },
            {
                "render": function (data, type, row) {
                    var acciones = "";
                    acciones += "<a href='#' onclick='visualizarOrdenCompraServicio(" + row.id + ", " + row.movimiento_id + ", " + row.documento_estado_id + ")'><i class='fa fa-eye' style='color:green;' title='Ver detalle programación'></i></a>&nbsp;&nbsp;";
                    acciones += "<a href='#' onclick='subirArchivosAdjuntos(" + row.id + ", \"" + row.total + "\", \"" + row.descripcion_moneda + "\")'><i class='fa fa-cloud-upload' style='color:blue;' title='Subir archivos adjuntos'></i></a>&nbsp;";
                    return acciones;
                },
                "targets": 6
            }
        ],
        "dom": '<"top">rt<"bottom"<"col-md-3"l><"col-md-9"p><"col-md-12"i>><"clear">',
        destroy: true
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

function onResponseObtenerConfiguracionInicialListadoDocumentos(data) {
    //desplegable de documentos
    fechasActuales();
}

//here
function buscarPorCriterios() {
    var fechaEmision = {
        inicio: $('#inicioFechaEmision').val(),
        fin: $('#finFechaEmision').val()
    };

    // var estadoId = select2.obtenerValor("cboEstado");
    var estadoId = 3;

    llenarParametrosBusqueda(fechaEmision, estadoId, documento_tipo);

    buscarOrdenCompraServicio();
}

var criterioBusquedaDocumentos = {};

function llenarParametrosBusqueda(fechaEmision, estadoId, tipoId) {
    criterioBusquedaDocumentos = {};
    criterioBusquedaDocumentos.fechaEmision = fechaEmision;
    criterioBusquedaDocumentos.estadoId = estadoId;
    criterioBusquedaDocumentos.tipoId = tipoId;
}

function cambiarAnchoBusquedaDesplegable() {
    var ancho = $("#divBuscador").width();
    $("#ulBuscadorDesplegable").width((ancho - 5) + "px");
}


function limpiarBuscadores() {
    $('#inicioFechaEmision').val('');
    $('#finFechaEmision').val('');

    select2.asignarValor("cboEstado", 0);

    criterioBusquedaDocumentos = {};
}

function onResponsevisualizarOrdenCompraServicio(data, documento_estado_id) {
    var cont = 0;

    cargarDataDocumento(data.dataDocumento);

    if (!isEmpty(data.detalle)) {
        $("#modalDetalle").modal('show');
        $('#dtmodalDetalle').dataTable({
            "processing": true,
            "ordering": false,
            "data": data.detalle,
            "order": [[0, "asc"]],
            "columns": [
                { "data": "movimiento_bien_id", "width": "5%", "sClass": "alignCenter" },
                { "data": "bien_descripcion", "width": "24%", "sClass": "alignLeft" },
                { "data": "cantidad", "width": "9%", "sClass": "alignCenter" },
                { "data": "unidad_medida_descripcion", "width": "10%", "sClass": "alignCenter" },
                { "data": "valor_monetario", "width": "4%", "sClass": "alignRight" },
                { "data": "sub_total", "width": "4%", "sClass": "alignRight" },
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
                    "targets": [2, 4, 5]
                }

            ],
            "dom": '<"top">rt<"bottom"<"col-md-3"l><"col-md-9"p><"col-md-12"i>><"clear">',
            destroy: true
        });
        loaderClose();
    }
}

function visualizarOrdenCompraServicio(id, movimientoId, documento_estado_id) {
    loaderShow();
    ax.setAccion("visualizarOrdenCompraServicio");
    ax.addParamTmp("id", id);
    ax.addParamTmp("movimientoId", movimientoId);
    ax.setTag(documento_estado_id);
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

function iniciarArchivoAdjuntoMultiple() {
    $("#archivoAdjuntoMulti").change(function () {
        $("#nombreArchivoMulti").html($('#archivoAdjuntoMulti').val().slice(12));

        //llenado del popover
        $('#idPopoverMulti').attr("data-content", $('#archivoAdjuntoMulti').val().slice(12));
        $('[data-toggle="popover"]').popover('show');
        $('.popover-content').css('color', 'black');
        $('[class="popover fade top in"]').css('z-index', '0');

        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = imageIsLoadedMulti;
            reader.readAsDataURL(this.files[0]);
        }
    });
}

var documentoIdGlobal = null;
var distribucionPagosIdGlobal = null;
var monto_totalGlobal = 0;
var moneda_descripcionGlobal = 0;
function subirArchivosAdjuntos(id, total, descripcion_moneda) {
    loaderShow();
    monto_totalGlobal = total;
    moneda_descripcionGlobal = descripcion_moneda;
    $("#dataList2").empty();
    $("#modalDetalleArchivos").modal('show');
    $(".modal-title-upload").empty();
    $(".modal-title-upload").append("Detalle documento, con un total de <strong>" + formatearNumero(total) + " " + descripcion_moneda + "</strong>");
    documentoIdGlobal = id;

    ax.setAccion("obtenerDocumentoAdjuntoXDocumentoId");
    ax.addParamTmp("documentoId", id);
    ax.consumir();
}

var lstDocumentoArchivos = [];
var lstDocEliminado = [];
var lstDocRechazado = [];
var cont = 0;
var ordenEdicion = 0;

function eliminarDocumento(docId) {
    ordenEdicion = 0;
    lstDocumentoArchivos.some(function (item) {
        if (item.id == docId) {
            lstDocumentoArchivos.splice(ordenEdicion, 1);
            lstDocEliminado.push([{ id: docId, archivo: item.archivo }])
            onResponseListarArchivosDocumento(lstDocumentoArchivos);
            return item.id === docId;
        }
        ordenEdicion++;
    });
    AgregarActualizar();
}

$("#btnAgregarDoc").click(function fileIsLoaded(e) {
    if (!isEmpty($("#archivoAdjuntoMulti").val())) {
        var filePath = $("#dataArchivoMulti").val();
        var cboTipoArchivo = parseInt(select2.obtenerValor('cboTipoArchivo'));
        switch (cboTipoArchivo) {
            case 1: //pdf
            case 3: //pdf factura
                var mimeType = filePath.match(/^data:(.*?);base64/);
                if (mimeType[1] != "application/pdf") {
                    mostrarAdvertencia('El archivo tiene que ser de extensión .pdf o .PDF');
                    loaderClose();
                    return;
                }
                break;
            case 2: //excel
                var mimeType = filePath.match(/^data:(.*?);base64/);
                if (mimeType[1] != "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") {
                    mostrarAdvertencia('El archivo tiene que ser de extensión .xlsx');
                    loaderClose();
                    return;
                }
                break;
            case 4: //Xml factura y Cdr factura
                var mimeType = filePath.match(/^data:(.*?);base64/);
                if (mimeType[1] != "text/xml") {
                    mostrarAdvertencia('El archivo tiene que ser de extensión .xml');
                    loaderClose();
                    return;
                }
                break;
        }

        var esUnico = false;
        if (!isEmpty(lstDocumentoArchivos)) {
            for (let i = 0; i < lstDocumentoArchivos.length; i++) {
                if (lstDocumentoArchivos[i].tipo_archivoId != 4) {
                    if (lstDocumentoArchivos[i].tipo_archivoId == select2.obtenerValor('cboTipoArchivo')) {
                        esUnico = true;
                        break;
                    }
                }
            }
        }

        if (esUnico) {
            mostrarAdvertencia('Existe un tipo archivo ' + select2.obtenerText('cboTipoArchivo') + ' ');
            loaderClose();
            return;
        } else {
            if ($("#archivoAdjuntoMulti").val().slice(12).length > 0) {
                var documento = {};
                documento.data = $("#dataArchivoMulti").val();
                documento.archivo = $("#archivoAdjuntoMulti").val().slice(12);
                documento.tipo_archivoId = select2.obtenerValor('cboTipoArchivo');
                documento.tipo_archivo = select2.obtenerText('cboTipoArchivo');
                documento.id = "t" + cont++;
                lstDocumentoArchivos.push(documento);
                onResponseListarArchivosDocumento(lstDocumentoArchivos);
                $("#archivoAdjuntoMulti").val("");
                $("#dataArchivoMulti").val("");
                $('[data-toggle="popover"]').popover('hide');
                $('#idPopoverMulti').attr("data-content", "");
                $("#msjDocumento").html("");
                $("#msjDocumento").hide();
            } else {
                onResponseListarArchivosDocumento(lstDocumentoArchivos);
                $("#archivoAdjuntoMulti").val("");
                $("#dataArchivoMulti").val("");
            }
        }

        AgregarActualizar();
    } else {
        $("#msjDocumento").html("Debe adjuntar un archivo primero para agregarlo a la lista");
        $("#msjDocumento").show();
    }

});

function imageIsLoadedMulti(e) {
    $('#dataArchivoMulti').attr('value', e.target.result);
}

function onResponseListarArchivosDocumento(data) {
    $(".modal-title-upload").empty();
    $(".modal-title-upload").append("Detalle documento, con un total de <strong>" + formatearNumero(monto_totalGlobal) + " " + moneda_descripcionGlobal + "</strong>");
    $("#dataList2").empty();
    var cuerpo_total = "";
    var cuerpo = "";
    var cabeza = "<table id='datatable3' class='table table-striped table-bordered'>"
        + "<thead>"
        + "<tr>"
        + "<th style='text-align:center; vertical-align: middle; width:8%'>#</th>"
        + "<th style='text-align:center; vertical-align: middle;'>Tipo Archivo</th>"
        + "<th style='text-align:center; vertical-align: middle;'>Nombre</th>"
        + "<th style='text-align:center; vertical-align: middle;'>Total factura</th>"
        + "<th style='text-align:center; vertical-align: middle;'>Estado</th>"
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
                + "<td style='text-align:center;'>" + item.archivo + "</td>"
                + "<td style='text-align:center;'>" + (item.contenido_archivo == null ? "" : item.contenido_archivo) + "</td>";
            if (item.estado_descripcion == "Rechazado") {
                cuerpo += "<td style='text-align:center;" + (item.estado_descripcion == "Rechazado" ? "color:red;" : "") + "' title='" + item.estado_descripcion + ", por : " + item.nombre_completo + "'>" + item.estado_descripcion + "</td>";
            } else {
                cuerpo += "<td style='text-align:center;' title='" + item.estado_descripcion + ", por : " + item.nombre_completo_reg + "'>" + item.estado_descripcion + "</td>";
            }

            cuerpo += "<td style='text-align:center;'>"
                + "<a href='" + item.data + "' download='" + item.archivo + "' target='_blank'><i class='fa fa-cloud-download' style='color:#1ca8dd;'></i></a>&nbsp;\n";
            if (item.estado == 1 && item.tipo_archivoId == 4) {
                cuerpo += "<a href='#' onclick='eliminarDocumento(\"" + item.id + "\")'><i class='fa fa-trash-o' style='color:#cb2a2a;'></i></a>&nbsp;\n";
                // if (!item.id.match(/t/g)) {
                cuerpo += "<a href='#' onclick='rechazarDocumento(\"" + item.id + "\")'><i class='fa fa-times' style='color:red;'></i></a>&nbsp;\n";
                cuerpo += "<a href='#' onclick='aprobarDocumento(\"" + item.id + "\")'><i class='fa fa-check' style='color:blue;'></i></a>&nbsp;\n";
                // }
            }
            if (item.estado_descripcion == "Rechazado") {
                cuerpo += "<a href='#' onclick='visualizarRechazo(\"" + item.comentario + "\")'><i class='fa fa-eye' style='color:black;'></i></a>&nbsp;\n";
            }
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

function AgregarActualizar() {
    loaderShow();
    var documentoId = documentoIdGlobal;
    if (isEmpty(documentoId)) {
        mostrarAdvertencia('Debe seleccionar una Orden de compra o servicio');
        loaderClose();
        return;
    }
    ax.setAccion("cargarArchivosAdjuntos");
    ax.addParamTmp("documentoId", documentoId);
    ax.addParamTmp("lstDocumentoArchivos", lstDocumentoArchivos);
    ax.addParamTmp("lstDocEliminado", lstDocEliminado);
    ax.addParamTmp("lstDocRechazado", lstDocRechazado);
    ax.consumir();
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

function subirArchivosAdjuntosDistribucionPagos(id, documentoId) {
    loaderShow();
    $("#dataList2DistribucionPagos").empty();
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
                + "<a href='#' onclick='eliminarAdjuntoDistribucionPagos(\"" + item.id + "\")'><i class='fa fa-trash-o' style='color:#cb2a2a;'></i></a>&nbsp;\n";
            // if (!item.id.match(/t/g)) {
            //     cuerpo += "<a href='#' onclick='rechazarDocumento(\"" + item.id + "\")'><i class='fa fa-times' style='color:red;'></i></a>&nbsp;\n";
            // }
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

function rechazarDocumento(id) {
    $("#modalRechazarDocumento").modal("show");
    $("#documentoAdjuntoId").val(id);
}

function confirmarRechazo() {
    var inputValue = $("#txtMotivoRechazo").val();
    var documentoAdjuntoId = $("#documentoAdjuntoId").val();
    if (inputValue === "" || isEmpty(inputValue.trim())) {
        $("#txtMotivoRechazo").html('Debe ingresar un motivo').show();
        return;
    } else {
        aprobarRechazarVistoBueno('RE', documentoAdjuntoId, inputValue);
    }
}

function aprobarRechazarVistoBueno(accion, documentoAdjuntoId, razonRechazo) {
    loaderShow('#modalRechazarDocumento');
    var documentoId = documentoIdGlobal;
    ax.setAccion("aprobarRechazar");
    ax.addParamTmp("accion", accion);
    ax.addParamTmp("documentoAdjuntoId", documentoAdjuntoId);
    ax.addParamTmp("razonRechazo", razonRechazo);
    ax.addParamTmp("documentoId", documentoId);
    ax.consumir();
}

function aprobarDocumento(documentoAdjuntoId) {
    swal(
        {
            title: "¿Desea continuar?",
            text: "Al aprobar el documento no se podrá revertir.",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#33b86c",
            confirmButtonText: "Si!",
            cancelButtonColor: "#d33",
            cancelButtonText: "No!",
            closeOnConfirm: true,
            closeOnCancel: true,
        },
        function (isConfirm) {
            if (isConfirm) {
                aprobarRechazarVistoBueno('AP', documentoAdjuntoId, null);
            } else {
                loaderClose();
            }
        }
    );
}

function visualizarRechazo(comentario) {
    swal({
        title: "El motivo de rechazo es:",
        text: comentario,
        type: "warning",
        showCancelButton: false,
        confirmButtonColor: "#33b86c",
        confirmButtonText: "Aceptar",
        closeOnConfirm: false,
        closeOnCancel: false
    });
}