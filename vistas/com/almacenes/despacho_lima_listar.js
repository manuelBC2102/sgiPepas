var documento_tipo = document.getElementById("documento_tipo").value;

var tabActivo = 1;
var documentoTipoActivo = null;

$(document).ready(function () {
    criterioBusquedaDespacho = {};
    cargarComponetentes();
    ax.setSuccess("onResponseAprobacionOrdenCompraServicio");
    obtenerConfiguracionInicialListadoDespacho();
    cambiarAnchoBusquedaDesplegable();
    select2.iniciarElemento("cboAlmacen");
});

function onResponseAprobacionOrdenCompraServicio(response) {
    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obtenerConfiguracionInicialListadoDocumentos':
                onResponseObtenerConfiguracionInicialListadoDespacho(response.data);
                buscarPorCriterios();
                loaderClose();
                break;
            case 'visualizarDetalleDespacho':
                onResponseVisualizarDespacho(response.data);
                break;
            case 'obtenerMovimientoPaqueteTraking':
                onResponseObtenerMovimientoPaqueteTraking(response.data);
                break;
        }
    } else {
        switch (response[PARAM_ACCION_NAME]) {
            case 'visualizarDetalleDespacho':
                loaderClose();
                break;
        }
    }
}

function cargarComponetentes() {
    cargarTitulo("titulo", "");
    select2.iniciar();
    datePiker.iniciarPorClase('fecha');
}

function obtenerConfiguracionInicialListadoDespacho() {
    ax.setAccion("obtenerConfiguracionInicialListadoDocumentos");
    ax.addParamTmp("documento_tipo", documento_tipo);
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

function buscarDespacho() {
    loaderShow();
    ax.setAccion("obtenerDespacho");
    ax.addParamTmp("criterios", criterioBusquedaDespacho);
    $('#datatableDespacho').dataTable({
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
            { "data": "almacen_destino", "class": "alignCenter" },
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
                "targets": 4
            },
            {
                "render": function (data, type, row) {
                    var acciones = "";
                    acciones += "<a href='#' onclick='visualizarDespacho(" + row.id + ", " + row.movimiento_id + ")'><i class='fa fa-eye' style='color:green;' title='Ver detalle programación'></i></a>&nbsp;&nbsp;";
                    acciones += "<a href='#' onclick='imprimirPdfDespacho(" + row.id + ", " + row.documento_tipo_id + ")'><i class='fa fa-print' style='color:blue;' title='Imprimir Qr de paquetes'></i></a>&nbsp;";
                    return acciones;
                },
                "targets": 7
            }
        ],
        "dom": '<"top">rt<"bottom"<"col-md-3"l><"col-md-9"p><"col-md-12"i>><"clear">',
        destroy: true
    });
    loaderClose();
    $('#datatableDespacho').on('draw.dt', function () {
        loaderClose();
    });
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

function onResponseObtenerConfiguracionInicialListadoDespacho(data) {
    //desplegable de documentos
    fechasActuales();
    select2.cargar("cboAlmacen", data.almacenes, "id", ["codigo", "descripcion"]);
    select2.asignarValor("cboAlmacen", data.almacenes[0]['id']);

    $("#cboAlmacen").select2({
        width: "100%"
    }).on("change", function (e) {
        loaderShow();
        actualizarBusqueda();
    });
}

//here
function buscarPorCriterios() {
    var fechaEmision = {
        inicio: $('#inicioFechaEmision').val(),
        fin: $('#finFechaEmision').val()
    };

    var almacen = select2.obtenerValor("cboAlmacen");
    var estadoId = select2.obtenerValor("cboEstadoDespacho");
    var serie = $("#txtSerieDespacho").val();
    var numero = $("#txtNumeroDespacho").val();

    llenarParametrosBusquedaDespacho(fechaEmision, estadoId, documento_tipo, almacen, serie, numero);

    buscarDespacho();
}

var criterioBusquedaDespacho = {};

function llenarParametrosBusquedaDespacho(fechaEmision, estadoId, tipoId, almacen, serie, numero) {
    criterioBusquedaDespacho = {};
    criterioBusquedaDespacho.fechaEmision = fechaEmision;
    criterioBusquedaDespacho.estadoId = estadoId;
    criterioBusquedaDespacho.tipoId = tipoId;
    criterioBusquedaDespacho.almacen = almacen;
    criterioBusquedaDespacho.serie = serie;
    criterioBusquedaDespacho.numero = numero;
}

function cambiarAnchoBusquedaDesplegable() {
    var ancho = $("#divBuscador").width();
    $("#ulBuscadorDesplegable").width((ancho - 5) + "px");
}


function limpiarBuscadores() {
    $('#inicioFechaEmision').val('');
    $('#finFechaEmision').val('');

    criterioBusquedaDespacho = {};
}

function visualizarDespacho(id, movimientoId) {
    loaderShow();
    ax.setAccion("visualizarDetalleDespacho");
    ax.addParamTmp("id", id);
    ax.addParamTmp("movimientoId", movimientoId);
    ax.consumir();
}

function onResponseVisualizarDespacho(data) {
    var cont = 0;

    cargarDataDocumento(data.dataDocumento);

    if (!isEmpty(data.detalle)) {
        $('input[type=checkbox]').prop('checked', false);
        $("#modalDetalle").modal('show');
        $('#dtmodalDetalle').dataTable({
            "processing": true,
            "ordering": false,
            "data": data.detalle,
            "order": [[0, "asc"]],
            "columns": [
                { "data": "movimiento_bien_id", "width": "5%", "sClass": "alignCenter" },
                { "data": "bien_descripcion", "width": "24%", "sClass": "alignLeft" },
                { "data": "unidad_medida_descripcion", "width": "10%", "sClass": "alignCenter" },
                { "data": "cantidadDespacho", "width": "9%", "sClass": "alignCenter" },
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
                    "targets": 3
                },
            ],
            "dom": '<"top">rt<"bottom"<"col-md-3"l><"col-md-9"p><"col-md-12"i>><"clear">',
            destroy: true
        });
        loaderClose();
    }

}

function devolverDosDecimales(num) {
    return redondearNumero(num).toFixed(2);
}

function cargarDataDocumento(data) {
    $("#formularioDetalleDocumento").empty();

    if (!isEmpty(data)) {
        $('#nombreDocumentoTipo').html(data[0]['nombre_documento']);
        // Mostraremos la data en filas de dos columnas
        $.each(data, function (index, item) {

            appendFormDetalle('</div>');
            appendFormDetalle('<div class="row">');

            var html = '';
            var descripcion = "";
            if (!isEmpty(item.valor)) {
                switch (parseInt(item.tipo)) {
                    case 1:
                    case 2:
                    case 3:
                    case 5:
                    case 7:
                    case 8:
                    case 9:
                    case 10:
                    case 11:
                    case 17:
                    case 23:
                    case 45:
                    case 41:
                    case 53:
                    case 54:
                        descripcion = item.descripcion;
                        break;
                }
                if (!isEmpty(descripcion)) {
                    html = '<div class="form-group col-md-12"><div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">' +
                        '<label>' + descripcion + '</label>' +
                        '</div><div class="col-lg-8 col-md-8 col-sm-6 col-xs-6">';
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
                    case 47:
                        valor = "";
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

function imprimirPdfDespacho(id, movimientoId) {
    const link = document.createElement('a');
    link.href = URL_BASE + "vistas/com/almacenes/despacho_pdf.php?id=" + id;
    link.target = '_blank';
    link.click();
}

var tabActivo = 1;
function actualizarTabActivo(tab) {
    tabActivo = tab;
    actualizarBusqueda();
}

function actualizarBusqueda() {
    if (tabActivo == 1) {
        buscarPorCriterios();
    } else {
        buscarPorCriterios2();
    }
}

function buscarPorCriterios2() {
    var fechaEmision = {
        inicio: $('#inicioFechaEmisionDespachos').val(),
        fin: $('#finFechaEmisionDespachos').val()
    };

    var almacen = select2.obtenerValor("cboAlmacen");
    var estadoId = select2.obtenerValor("cboEstado"); //Registrado

    llenarParametrosBusquedaDespachos(fechaEmision, estadoId, documento_tipo, almacen);

    buscarDespachos();
}

var criterioBusquedaDocumentosDespachos = {};
function llenarParametrosBusquedaDespachos(fechaEmision, estadoId, tipoId, almacen) {
    criterioBusquedaDocumentosDespachos = {};
    criterioBusquedaDocumentosDespachos.fechaEmision = fechaEmision;
    criterioBusquedaDocumentosDespachos.estadoId = estadoId;
    criterioBusquedaDocumentosDespachos.tipoId = tipoId;
    criterioBusquedaDocumentosDespachos.almacen = almacen;
}

function buscarDespachos() {
    loaderShow();
    ax.setAccion("obtenerPaqueteDespachos");
    ax.addParamTmp("criterios", criterioBusquedaDocumentosDespachos);
    $('#datatableDespachos').dataTable({
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
            { "data": "paquete_id", "class": "alignCenter", "render": function (data, type, row) { return '<strong>' + data + '</strong>'; } },
            { "data": "organizador_descripcion", "class": "alignCenter" },
            { "data": "bien_codigo_descripcion", "class": "aligalignCenternLeft" },
            { "data": "cantidad", "width": "10%", "sClass": "alignRight" },
            { "data": "organizador_destino_descripcion", "width": "10%", "sClass": "alignCenter" },
            { "data": "fecha_creacion", "class": "alignCenter" },
            { "data": "usuario_creacion", "class": "alignCenter" },
            { "data": "paquete_detalle_id", "class": "alignCenter", "orderable": false },
        ],
        columnDefs: [
            {
                "render": function (data, type, row) {
                    return devolverDosDecimales(data);
                },
                "targets": 3
            },
            {
                "render": function (data, type, row) {
                    var acciones = "";
                    acciones += "<a href='#' onclick='verMovimientoPaquete(" + row.paquete_id + ")'><i class='fa fa-cube' style='color:orange;' title='ver traking'></i></a>&nbsp;";
                    return acciones;
                },
                "targets": 7
            },
            {
                "render": function (data, type, row) {
                    return '<p title="' + row.persona_nombre_completo + '">' + data + '</p>';
                },
                "targets": 6
            },
        ],
        "dom": '<"top">rt<"bottom"<"col-md-3"l><"col-md-9"p><"col-md-12"i>><"clear">',
        destroy: true
    });
    loaderClose();
    $('#datatableDespachos').on('draw.dt', function () {
        loaderClose();
    });
}

function verMovimientoPaquete(id) {
    loaderShow("#modalDetalleAlmacenado");
    $("#modalDetalleAlmacenado").modal('show');
    ax.setAccion("obtenerMovimientoPaqueteTraking");
    ax.addParamTmp("id", id);
    ax.consumir();
}

function onResponseObtenerMovimientoPaqueteTraking(data) {
    $('#dtmodalDetalleTraking tbody').empty();
    var cont = 0;
    $('.modal-title-almacenado').html("Detalle Traking: <strong>" + data[0].bien_codigo_descripcion) + "</strong>";
    if (!isEmpty(data)) {
        $('#dtmodalDetalleTraking').dataTable({
            "processing": true,
            "ordering": false,
            "data": data,
            "order": [[0, "asc"]],
            "columns": [
                { "data": "paquete_detalle_id", "width": "5%", "sClass": "alignCenter" },
                { "data": "paquete_id", "width": "5%", "sClass": "alignCenter", "render": function (data, type, row) { return '<strong>' + data + '</strong>'; } },
                { "data": "tipo_almacenaje", "width": "10%", "sClass": "aligalignCenternLeft" },
                { "data": "almacen", "width": "20%", "sClass": "alignLeft" },
                { "data": "organizador_codigo_descripcion", "width": "20%", "sClass": "alignLeft" },
                { "data": "cantidad", "width": "10%", "sClass": "alignRight" },
                { "data": "serie_numero", "width": "9%", "sClass": "alignCenter" },
                { "data": "documento_tipo_descripcion", "width": "15%", "sClass": "alignCenter" },
                { "data": "usuario_creacion", "width": "10%", "sClass": "alignCenter" },
                { "data": "fecha_creacion", "width": "10%", "sClass": "alignCenter" },
                { "data": "estado_traking", "width": "10%", "sClass": "alignCenter" },
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
                        return devolverDosDecimales(data);
                    },
                    "targets": 5
                },
                {
                    "render": function (data, type, row) {
                        return "<p title='" + row.persona_nombre_completo + "'>" + data + "</p>";
                    },
                    "targets": 6
                },
                {
                    "render": function (data, type, row) {
                        return isEmpty(data) ? "" : "<strong>" + data + "</strong>";
                    },
                    "targets": 10
                },
            ],
            "dom": '<"top">rt<"bottom"<"col-md-3"l><"col-md-9"p><"col-md-12"i>><"clear">',
            destroy: true
        });
        loaderClose();
    }
}

function nuevoFormDespacho() {
    cargarDiv('#window', 'vistas/com/almacenes/despacho_lima_form_tablas.php?almacenId=' + select2.obtenerValor("cboAlmacen"));
}