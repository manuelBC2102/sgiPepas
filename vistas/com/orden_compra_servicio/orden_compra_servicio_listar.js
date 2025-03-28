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
                if(!isEmpty(response.data)){
                    lstDocumentoArchivos = response.data;
                }
                onResponseListarArchivosDocumento(response.data);
                break;
            case 'cargarArchivosAdjuntos':
                swal({
                    title: "Documentos actualizados",
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
            case 'visualizarDistribucionPagos':
                onResponseListarDistribucionPagos(response.data);
                break;
            case 'obtenerDocumentoAdjuntoXDistribucionPagos':
                if(!isEmpty(response.data)){
                    lstDocumentoArchivos = response.data;
                }
                onResponseListarArchivosDistribucionPago(response.data);
                break;
            case 'cargarArchivosAdjuntosDistribucionPagos':
                swal({
                    title: "Documentos actualizados",
                    text: response.data.mensaje,
                    type: "success",
                    showCancelButton: false,
                    confirmButtonColor: "#33b86c",
                    confirmButtonText: "Aceptar",
                    closeOnConfirm: false,
                    closeOnCancel: false
                });
                lstDocumentoArchivos = response.data.data;
                onResponseListarArchivosDistribucionPago(response.data.data);
                break;
        }
    }else{
        switch (response[PARAM_ACCION_NAME]) {
            case 'cargarArchivosAdjuntos':
                subirArchivosAdjuntos(documentoIdGlobal);
                lstDocumentoArchivos = [];
                break;
            case 'cargarArchivosAdjuntosDistribucionPagos':
                subirArchivosAdjuntosDistribucionPagos(distribucionPagosIdGlobal);
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
                    acciones += "<a href='#' onclick='visualizarDistribucionPagos(" + row.id + ")'><i class='fa fa-eye' style='color:black;' title='Ver detalle distribución pagos'></i></a>&nbsp;";
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

    var estadoId = select2.obtenerValor("cboEstado");

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

    $("#archivoAdjuntoMultiDistribucionPagos").change(function () {
        $("#nombreArchivoMultiDistribucionPagos").html($('#archivoAdjuntoMultiDistribucionPagos').val().slice(12));

        //llenado del popover
        $('#idPopoverMultiDistribucionPagos').attr("data-content", $('#archivoAdjuntoMultiDistribucionPagos').val().slice(12));
        $('[data-toggle="popover"]').popover('show');
        $('.popover-content').css('color', 'black');
        $('[class="popover fade top in"]').css('z-index', '0');

        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = imageIsLoadedMultiDistribucionPagos;
            reader.readAsDataURL(this.files[0]);
        }
    });
}

var documentoIdGlobal = null;
var distribucionPagosIdGlobal = null;

function subirArchivosAdjuntos(id, movimientoId) {
    loaderShow();
    $("#dataList2").empty();
    $("#modalDetalleArchivos").modal('show');
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
        switch(cboTipoArchivo){
            case 1 : //pdf
            case 3 : //pdf factura
                var mimeType = filePath.match(/^data:(.*?);base64/);
                if(mimeType[1] != "application/pdf"){
                    mostrarAdvertencia('El archivo tiene que ser de extensión .pdf o .PDF');
                    loaderClose();
                    return;
                }
            break;
            case 2 : //excel
                var mimeType = filePath.match(/^data:(.*?);base64/);
                if(mimeType[1] != "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"){
                    mostrarAdvertencia('El archivo tiene que ser de extensión .xlsx');
                    loaderClose();
                    return;
                }
            break;
            case 4 : //Xml factura y Cdr factura
            var mimeType = filePath.match(/^data:(.*?);base64/);
                if(mimeType[1] != "text/xml"){
                    mostrarAdvertencia('El archivo tiene que ser de extensión .xlsx o .xls');
                    loaderClose();
                    return;
                }
            break;
        }

        var esUnico = false;
        if (!isEmpty(lstDocumentoArchivos)) {
            for (let i = 0; i < lstDocumentoArchivos.length; i++) {
                if (lstDocumentoArchivos[i].tipo_archivoId == select2.obtenerValor('cboTipoArchivo')) {
                    esUnico = true;
                    break;
                }
            }
        }

        if (esUnico) {
            mostrarAdvertencia('Existe un tipo archivo ' + select2.obtenerText('cboTipoArchivo') + ' ');
            loaderClose();
            return;
        }else{
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
            }
        }

        AgregarActualizar();
    } else {
        $("#msjDocumento").html("Debe adjuntar un archivo primero para agregarlo a la lista");
        $("#msjDocumento").show();
    }

});


$("#btnAgregarDocDistribucionPagos").click(function fileIsLoaded(e) {
    if (!isEmpty($("#archivoAdjuntoMultiDistribucionPagos").val())) {
        var filePath = $("#dataArchivoMultiDistribucionPagos").val();
        var cboTipoArchivo = parseInt(select2.obtenerValor('cboTipoArchivoDistribucionPagos'));
        switch(cboTipoArchivo){
            case 1 : //pdf
            case 3 : //pdf factura
                var mimeType = filePath.match(/^data:(.*?);base64/);
                if(mimeType[1] != "application/pdf"){
                    mostrarAdvertencia('El archivo tiene que ser de extensión .pdf o .PDF');
                    loaderClose();
                    return;
                }
            break;
            case 2 : //excel
                var mimeType = filePath.match(/^data:(.*?);base64/);
                if(mimeType[1] != "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"){
                    mostrarAdvertencia('El archivo tiene que ser de extensión .xlsx');
                    loaderClose();
                    return;
                }
            break;
            case 4 : //Xml factura y Cdr factura
            var mimeType = filePath.match(/^data:(.*?);base64/);
                if(mimeType[1] != "text/xml"){
                    mostrarAdvertencia('El archivo tiene que ser de extensión .xlsx o .xls');
                    loaderClose();
                    return;
                }
            break;
        }

        var esUnico = false;
        if (!isEmpty(lstDocumentoArchivos)) {
            for (let i = 0; i < lstDocumentoArchivos.length; i++) {
                if (lstDocumentoArchivos[i].tipo_archivoId == select2.obtenerValor('cboTipoArchivoDistribucionPagos')) {
                    esUnico = true;
                    break;
                }
            }
        }

        if (esUnico) {
            mostrarAdvertencia('Existe un tipo archivo ' + select2.obtenerText('cboTipoArchivoDistribucionPagos') + ' ');
            loaderClose();
            return;
        }else{
            if ($("#archivoAdjuntoMultiDistribucionPagos").val().slice(12).length > 0) {
                var documento = {};
                documento.data = $("#dataArchivoMultiDistribucionPagos").val();
                documento.archivo = $("#archivoAdjuntoMultiDistribucionPagos").val().slice(12);
                documento.tipo_archivoId = select2.obtenerValor('cboTipoArchivoDistribucionPagos');
                documento.tipo_archivo = select2.obtenerText('cboTipoArchivoDistribucionPagos');
                documento.id = "t" + cont++;
                lstDocumentoArchivos.push(documento);
                onResponseListarArchivosDistribucionPago(lstDocumentoArchivos);
                $("#archivoAdjuntoMultiDistribucionPagos").val("");
                $("#dataArchivoMultiDistribucionPagos").val("");
                $('[data-toggle="popover"]').popover('hide');
                $('#idPopoverMulti').attr("data-content", "");
                $("#msjDocumentoDistribucionPagos").html("");
                $("#msjDocumentoDistribucionPagos").hide();
            } else {
                onResponseListarArchivosDistribucionPago(lstDocumentoArchivos);
            }
        }

        AgregarActualizarDistribucionPagos();
    } else {
        $("#msjDocumentoDistribucionPagos").html("Debe adjuntar un archivo primero para agregarlo a la lista");
        $("#msjDocumentoDistribucionPagos").show();
    }

});

function imageIsLoadedMulti(e) {
    $('#dataArchivoMulti').attr('value', e.target.result);
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
                + "<a href='#' onclick='eliminarDocumento(\"" + item.id + "\")'><i class='fa fa-trash-o' style='color:#cb2a2a;'></i></a>&nbsp;\n";
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
    $("#dataList2").append(html);
    $("#datatable3").DataTable();
}

function AgregarActualizar(){
    loaderShow();
    var documentoId = documentoIdGlobal;
    if(isEmpty(documentoId)){
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

function visualizarDistribucionPagos(documentoId) {
    loaderShow();
    ax.setAccion("visualizarDistribucionPagos");
    ax.addParamTmp("documentoId", documentoId);
    ax.consumir();
}

function onResponseListarDistribucionPagos(data) {
    var cont = 0;

    if (!isEmpty(data)) {
        $("#modalDetalleDistribucionPagos").modal('show');
        $('#dtmodalDetalleDistribucionPagos').dataTable({
            "processing": true,
            "ordering": false,
            "data": data,
            "order": [[1, "desc"]],
            "columns": [
                { "data": "id", "width": "5%", "sClass": "alignCenter" },
                { "data": "fecha_pago", "width": "10%", "sClass": "alignCenter" },
                { "data": "glosa", "width": "25%", "sClass": "alignLeft" },
                { "data": "importe", "width": "10%", "sClass": "alignRight" },
                { "data": "porcentaje", "width": "10%", "sClass": "alignRight" },
                { "data": "estado", "width": "4%", "sClass": "alignCenter" },
                { "data": "documento_id", "width": "4%", "sClass": "alignCenter" },
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
                        return data.substring(0, 10);
                    },
                    "targets": 1
                },
                {
                    "render": function (data, type, row) {
                        return devolverDosDecimales(data);
                    },
                    "targets": [3,4]
                },
                {
                    "render": function (data, type, row) {
                        switch(data){
                            case "1":
                                return "Registrado";
                                break;
                            case "2":
                                return "Programado";
                                break;
                            case "3":
                                return "Pägado";
                                break;
                        }
                    },
                    "targets": 5
                },
                {
                    "render": function (data, type, row) {
                        $("#tituloDistribucionPagos").html("( Por un monto de: " + devolverDosDecimales(row.importe)+" )");
                        var acciones = "<a href='#' onclick='subirArchivosAdjuntosDistribucionPagos(" + row.id + ", " + row.documento_id + ")'><i class='fa fa-cloud-upload' style='color:blue;' title='Subir archivos adjuntos'></i></a>&nbsp;";
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
}

function subirArchivosAdjuntosDistribucionPagos(id, documentoId) {
    loaderShow();
    $("#dataList2DistribucionPagos").empty();
    $("#modalDetalleDistribucionPagos").modal('hide');
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

function AgregarActualizarDistribucionPagos(){
    loaderShow();
    var distribucionPagoId = distribucionPagosIdGlobal;
    var documentoId = documentoIdGlobal;
    if(isEmpty(documentoId)){
        mostrarAdvertencia('Debe seleccionar una Orden de compra o servicio');
        loaderClose();
        return;
    }
    ax.setAccion("cargarArchivosAdjuntosDistribucionPagos");
    ax.addParamTmp("distribucionPagoId", distribucionPagoId);
    ax.addParamTmp("documentoId", documentoId);
    ax.addParamTmp("lstDocumentoArchivos", lstDocumentoArchivos);
    ax.addParamTmp("lstDocEliminado", lstDocEliminado);
    ax.addParamTmp("lstDocRechazado", lstDocRechazado);
    ax.consumir();
}

function imageIsLoadedMultiDistribucionPagos(e) {
    $('#dataArchivoMultiDistribucionPagos').attr('value', e.target.result);
}

function ocultarModalDistribucionPagos(){
    $("#modalDetalleDistribucionPagos").modal('show');
    $("#modalDetalleArchivosDistribucionPagos").modal('hide');
}

function eliminarAdjuntoDistribucionPagos(docId) {
    ordenEdicion = 0;
    lstDocumentoArchivos.some(function (item) {
        if (item.id == docId) {
            lstDocumentoArchivos.splice(ordenEdicion, 1);
            lstDocEliminado.push([{ id: docId, archivo: item.archivo }])
            onResponseListarArchivosDistribucionPago(lstDocumentoArchivos);
            return item.id === docId;
        }
        ordenEdicion++;
    });
    AgregarActualizarDistribucionPagos();
}