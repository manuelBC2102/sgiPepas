$(document).ready(function () {
    criterioBusquedaDocumentos = [];
    cargarComponetentes();
    ax.setSuccess("onResponseAlmacenar");
    obtenerConfiguracionInicialListadoDocumentos();
    cambiarAnchoBusquedaDesplegable();
    select2.iniciarElemento("cboAlmacen");
});
var tabActivo = 1;

function onResponseAlmacenar(response) {
    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obtenerConfiguracionInicialListadoDocumentos':
                onResponseObtenerConfiguracionInicialListadoPaqueteAlmacenar(response.data);
                buscarPorCriterios();
                loaderClose();
                break;
            case 'obtenerPaqueteTrakingDetalleXBienId':
                onResponseObtenerPaqueteTrakingDetalleXBienId(response.data);
                break;
            case 'obtenerMovimientoPaqueteTraking':
                onResponseObtenerMovimientoPaqueteTraking(response.data);
                break;
            case 'generarDistribucionQR':
                onResponsegenerarDistribucionQR(response.data);
                $("#modalDetalleRecepcionado").modal('hide');
                buscarPorCriterios();
                break;
        }
    } else {
        switch (response[PARAM_ACCION_NAME]) {
            case 'guardarDetalleRecepcion':
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

function actualizarBusqueda() {
    if (tabActivo == 1) {
        buscarPorCriterios();
    } else {
        buscarPorCriterios2();
    }
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

function onResponseObtenerConfiguracionInicialListadoPaqueteAlmacenar(data) {
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
        inicio: $('#inicioFechaEmisionAlmacenado').val(),
        fin: $('#finFechaEmisionAlmacenado').val()
    };

    var almacen = select2.obtenerValor("cboAlmacen");

    llenarParametrosBusqueda(fechaEmision, almacen);

    // buscarRecepcionado();
    buscarAlmacenado();
}

var criterioBusquedaDocumentos = {};

function llenarParametrosBusqueda(fechaEmision, almacen) {
    criterioBusquedaDocumentos = {};
    criterioBusquedaDocumentos.fechaEmision = fechaEmision;
    criterioBusquedaDocumentos.almacen = almacen;
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

function devolverDosDecimales(num) {
    return redondearNumero(num).toFixed(2);
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
    $('#finFechaEmisionAlmacenado').val(fechaFormateada);

    // Calcular la fecha de hace un mes
    fechaActual.setMonth(fechaActual.getMonth() - 1);
    var diaInicio = ('0' + fechaActual.getDate()).slice(-2);
    var mesInicio = ('0' + (fechaActual.getMonth() + 1)).slice(-2);
    var anioInicio = fechaActual.getFullYear();
    var fechaInicioFormateada = diaInicio + '/' + mesInicio + '/' + anioInicio;

    // Colocar la fecha de hace un mes en el campo "inicioFechaEmision"
    $('#inicioFechaEmision').val(fechaInicioFormateada);
    $('#inicioFechaEmisionAlmacenado').val(fechaInicioFormateada);
}


function actualizarTabActivo(tab) {
    tabActivo = tab;
    actualizarBusqueda();
}

function buscarPorCriterios2() {
    var fechaEmision = {
        inicio: $('#inicioFechaEmisionAlmacenado').val(),
        fin: $('#finFechaEmisionAlmacenado').val()
    };
    var almacen = select2.obtenerValor("cboAlmacen");

    llenarParametrosBusquedaAlmacenado(fechaEmision, almacen);

    // buscarAlmacenado(); //cambiar
}

criterioBusquedaDocumentosAlmacenado = {};
function llenarParametrosBusquedaAlmacenado(fechaEmision, almacen) {
    criterioBusquedaDocumentosAlmacenado = {};
    criterioBusquedaDocumentosAlmacenado.fechaEmision = fechaEmision;
    criterioBusquedaDocumentosAlmacenado.almacen = almacen;
}

function buscarAlmacenado() {
    loaderShow();
    ax.setAccion("obtenerPaqueteAlmacenado");
    ax.addParamTmp("criterios", criterioBusquedaDocumentos);
    $('#datatableAlmacenado').dataTable({
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
        "order": [[0, "desc"]],
        "processing": true,
        "serverSide": true,
        "bFilter": false,
        "ajax": ax.getAjaxDataTable(),
        "scrollX": true,
        "columns": [
            { "data": "bien_codigo_descripcion", "class": "aligalignCenternLeft" },
            { "data": "cantidad", "width": "10%", "sClass": "alignRight" },
            { "data": "paquete_detalle_id", "class": "alignCenter", "orderable": false },
        ],
        columnDefs: [
            {
                "render": function (data, type, row) {
                    return devolverDosDecimales(data);
                },
                "targets": 1
            },
            {
                "render": function (data, type, row) {
                    var acciones = "";
                    acciones += "<a href='#' onclick='visualizarDetalleProducto(" + row.bien_id + ")'><i class='fa fa-eye' style='color:green;' title='Ver detalle'></i></a>&nbsp;&nbsp;";
                    return acciones;
                },
                "targets": 2
            }
        ],
        "dom": '<"top">rt<"bottom"<"col-md-3"l><"col-md-9"p><"col-md-12"i>><"clear">',
        destroy: true
    });
    loaderClose();
    $('#datatableAlmacenado').on('draw.dt', function () {
        loaderClose();
    });
}


function visualizarDetalleProducto(bienId) {
    loaderShow("#modalDetalleAlmacenado");
    $("#modalDetalleAlmacenado").modal('show');
    ax.setAccion("obtenerPaqueteTrakingDetalleXBienId");
    ax.addParamTmp("bienId", bienId);
    ax.addParamTmp("almacen", select2.obtenerValor("cboAlmacen"));
    ax.consumir();
}

function onResponseObtenerPaqueteTrakingDetalleXBienId(data) {
    $('#dtmodalDetallePaquete tbody').empty();
    $('#dtmodalDetalleTraking tbody').empty();
    var cont = 0;
    if (!isEmpty(data)) {
        $('.modal-title-almacenado').html("Detalle Traking: <strong>" + data[0].bien_codigo_descripcion) + "</strong>";
        $('#dtmodalDetallePaquete').dataTable({
            "processing": true,
            "ordering": false,
            "data": data,
            "order": [[0, "asc"]],
            "columns": [
                { "data": "paquete_detalle_id", "width": "5%", "sClass": "alignCenter" },
                { "data": "paquete_id", "width": "5%", "sClass": "alignCenter", "render": function (data, type, row) { return '<strong>' + data + '</strong>'; } },
                { "data": "organizador_codigo_descripcion", "width": "20%", "sClass": "alignLeft" },
                { "data": "cantidad", "width": "10%", "sClass": "alignRight" },
                { "data": "usuario_creacion", "width": "10%", "sClass": "alignCenter" },
                { "data": "fecha_creacion", "width": "10%", "sClass": "alignCenter" },
                { "data": "movimiento_bien_id", "width": "10%", "sClass": "alignCenter" },
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
                    "targets": 3
                },
                {
                    "render": function (data, type, row) {
                        return "<p title='" + row.persona_nombre_completo + "'>" + data + "</p>";
                    },
                    "targets": 4
                },
                {
                    "render": function (data, type, row) {
                        var acciones = "";
                        acciones += "<a href='#' onclick='verMovimientoPaquete(" + row.paquete_id + ")'><i class='fa fa-cube' style='color:orange;' title='ver traking'></i></a>&nbsp;";
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

function verMovimientoPaquete(id) {
    loaderShow("#modalDetalleAlmacenado");
    ax.setAccion("obtenerMovimientoPaqueteTraking");
    ax.addParamTmp("id", id);
    ax.consumir();
}

function onResponseObtenerMovimientoPaqueteTraking(data) {
    var cont = 0;
    var datos = data;
    $("#div_detalleTraking").show();
    $('.modal-title-almacenado').html("<strong>" + data[0].bien_codigo_descripcion) + "</strong>";
    if (!isEmpty(data)) {
        $('#dtmodalDetalleTraking').dataTable({
            "processing": true,
            "ordering": false,
            "data": data,
            "order": [[0, "asc"]],
            "columns": [
                { "data": "paquete_detalle_id", "width": "5%", "sClass": "alignCenter" },
                { "data": "paquete_id", "width": "5%", "sClass": "alignCenter", "render": function (data, type, row) { return row.tipo_almacenaje == "Almacenaje" ? "-" : '<strong>' + data + '</strong>'; } },
                { "data": "tipo_almacenaje", "width": "10%", "sClass": "aligalignCenternLeft" },
                { "data": "almacen", "width": "20%", "sClass": "alignLeft" },
                { "data": "organizador_codigo_descripcion", "width": "20%", "sClass": "alignLeft" },
                { "data": "organizador_destino_codigo_descripcion", "width": "20%", "sClass": "alignLeft" },
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
                        if (row.tipo_almacenaje != "Almacenaje") {
                            cont = 1 + cont;
                            return cont;
                        } else {
                            return "<strong>" + datos[0].paquete_id + "</strong>";
                        }

                    },
                    "targets": 0
                },
                {
                    "render": function (data, type, row) {
                        return devolverDosDecimales(data);
                    },
                    "targets": 6
                },
                {
                    "render": function (data, type, row) {
                        return "<p title='" + row.persona_nombre_completo + "'>" + data + "</p>";
                    },
                    "targets": 9
                },
                {
                    "render": function (data, type, row) {
                        return isEmpty(data) ? "" : "<strong>" + data + "</strong>";
                    },
                    "targets": 11
                },
            ],
            "dom": '<"top">rt<"bottom"<"col-md-3"l><"col-md-9"p><"col-md-12"i>><"clear">',
            destroy: true,
            drawCallback: function (settings) {
                $('#dtmodalDetalleTraking tbody tr').each(function () {
                    var $fila = $(this);
                    var tipo = $fila.find('td:eq(2)').text();

                    if (tipo === 'Almacenaje') {
                        var correlativo = $fila.find('td:eq(0)').text();

                        // Establecer colspan y combinar contenido
                        $fila.find('td:eq(0)').attr('colspan', 2).html("<strong>" + correlativo + "</strong>");
                        $fila.find('td:eq(1)').remove(); // Quitar la celda extra para que colspan funcione
                    }
                });
            }
        });
    }
    loaderClose();
}

