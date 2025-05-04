$(document).ready(function () {
    $('[data-toggle="popover"]').popover({ html: true }).popover();
    cargarTitulo("titulo", "");
    select2.iniciarElemento("cboTipoBien");
    cargarComponetentes();
    ax.setSuccess("onResponseReporteBalance");
    obtenerConfiguracionesInicialesSeguimientoRequerimiento();
});

function onResponseReporteBalance(response) {
    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obtenerConfiguracionesInicialesSeguimientoRequerimiento':
                onResponseObtenerConfiguracionesInicialesSeguimientoRequerimiento(response.data);
                buscarSeguimiento();
                break;
            case 'obtenerDataSeguimientoRequerimiento':
                loaderClose();
                break;
            case 'obtenerDetalleKardex':
                onResponseDetalleKardex(response.data);
                loaderClose();
                break;
            case 'obtenerReporteKardexExcel':
                loaderClose();
                location.href = URL_BASE + "util/formatos/reporte.xlsx";
                break;
        }
    } else {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obtenerReporteKardexExcel':
                loaderClose();
                break;
        }
    }
}

function cargarComponetentes() {
    cargarTitulo("titulo", "");
    datePiker.iniciarPorClase('fecha');
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

function obtenerConfiguracionesInicialesSeguimientoRequerimiento() {
    ax.setAccion("obtenerConfiguracionesInicialesSeguimientoRequerimiento");
    ax.addParamTmp("id_empresa", commonVars.empresa);
    ax.consumir();
}

function onResponseObtenerConfiguracionesInicialesSeguimientoRequerimiento(data) {
    if (!isEmpty(data.bien)) {
        cargarComboBien(data.bien);
        if (!isEmpty(data.bien_tipo)) {
            select2.cargar("cboTipoBien", data.bien_tipo, "id", ["codigo", "descripcion"]);
            if (!isEmpty(data.fecha_primer_documento)) {

                if (!isEmpty(data.fecha_primer_documento[0]['primera_fecha'])) {
                    $('#inicioFechaEmision').val(formatearFechaBDCadena(data.fecha_primer_documento[0]['primera_fecha']));
                    if (!isEmpty(data.fecha_primer_documento[0]['fecha_actual'])) {
                        $('#finFechaEmision').val(formatearFechaBDCadena(data.fecha_primer_documento[0]['fecha_actual']));
                    }
                }

            }
        }

    }
    fechasActuales();
    loaderClose();
}

var valoresBusquedaSeguimiento = [{bienIds: "", bienTipoIds: "", fechaInicio: "", fechaFin: "", serie: "", numero: "" }];//bandera 0 es balance

function cargarDatosBusqueda() {

    var bien = $('#cboBien').val();
    bien = bien == "" ? "" : bien.split(",");

    var bienTipo = $('#cboTipoBien').val();

    var fechaInicio = $('#inicioFechaEmision').val();
    var fechaFin = $('#finFechaEmision').val();
    var serie = $('#serie').val();
    var numero = $('#numero').val();


    valoresBusquedaSeguimiento[0].bienIds = bien;
    valoresBusquedaSeguimiento[0].bienTipoIds = bienTipo;
    valoresBusquedaSeguimiento[0].fechaInicio = fechaInicio;
    valoresBusquedaSeguimiento[0].fechaFin = fechaFin;
    valoresBusquedaSeguimiento[0].serie = serie;
    valoresBusquedaSeguimiento[0].numero = numero;
}

function obtenerDatosBusqueda() {
    var cadena = "";
    cargarDatosBusqueda();

    if (!isEmpty(valoresBusquedaSeguimiento[0].bienIds)) {
        cadena += negrita("Producto: ");
        cadena += select2.obtenerTextMultiple('cboBien');
        cadena += "<br>";
    }
    if (!isEmpty(valoresBusquedaSeguimiento[0].bienTipoIds)) {
        cadena += negrita("Producto tipo: ");
        cadena += select2.obtenerTextMultiple('cboTipoBien');
        cadena += "<br>";
    }
    if (!isEmpty(valoresBusquedaSeguimiento[0].fechaInicio) || !isEmpty(valoresBusquedaSeguimiento[0].fechaFin)) {
        cadena += negrita("Fecha emisión: ");
        cadena += valoresBusquedaSeguimiento[0].fechaInicio + " - " + valoresBusquedaSeguimiento[0].fechaFin;
        cadena += "<br>";
    }
    return cadena;
}

function buscarSeguimiento(colapsa) {
    loaderShow();
    var cadena;
    cadena = obtenerDatosBusqueda();
    if (!isEmpty(cadena) && cadena !== 0) {
        $('#idPopover').attr("data-content", cadena);
    }
    $('[data-toggle="popover"]').popover('show');
    banderaBuscar = 1;

    obtenerDataBusquedaSeguimiento(cadena);

    if (colapsa === 1)
        colapsarBuscador();

    loaderClose();
}

function obtenerDataBusquedaSeguimiento() {
    var cont = 0;
    loaderShow();
    ax.setAccion("obtenerDataSeguimientoRequerimiento");
    ax.addParamTmp("criterios", valoresBusquedaSeguimiento);
    $('#datatableSeguimiento').dataTable({
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
        "order": [[0, "asc"]],
        "processing": true,
        "bFilter": true,
        "ajax": ax.getAjaxDataTable(),
        "scrollX": true,
        "columns": [
            { "data": "documento_id" },
            { "data": "bien_codigo" },
            { "data": "bien_descripcion", "width": "150px" },
            { "data": "unidad_medida_descripcion" },
            { "data": "cantidad", "sClass": "alignRight" },
            { "data": "generador" },
            { "data": "serie_numero_requerimiento" },
            { "data": "tipo_requerimiento" },
            { "data": "fecha_creacion", "sClass": "alignCenter" },
            { "data": "area_descripcion", "sClass": "alignCenter" },
            { "data": "aprobacionRQ", "sClass": "alignCenter" },
            { "data": "aprobacionRQFecha", "sClass": "alignCenter" },
            { "data": "usuarioGenerador", "sClass": "alignCenter" },
            { "data": "usuarioGeneradorEstado", "sClass": "alignCenter" },
            { "data": "usuarioGeneradorFecha", "sClass": "alignCenter" },
            { "data": "OC", "sClass": "alignCenter" },
            { "data": "OCEstado", "sClass": "alignCenter" },
            { "data": "OCFecha", "sClass": "alignCenter" },
        ],
        columnDefs: [
            {
                "render": function (data, type, row) {
                    cont = 1 + cont;
                    return cont;
                },
                "targets": 0
            },
        ],
        "dom": '<"top">rt<"bottom"<"col-md-3"l><"col-md-9"p><"col-md-12"i>><"clear">',
        destroy: true,
    });
    loaderClose();
}

// function onResponseGetDataGridSeguimiento(data) {
//     var cont = 0;
//     if (!isEmptyData(data)) {
//         $('#datatable').dataTable({
//             "order": [[3, "desc"]],
//             "processing": true,
//             "serverSide": true,
//             "bFilter": false,
//             "ajax": ax.getAjaxDataTable(),
//             "scrollX": true,
//             "columns": [
//                 { "data": "documento_id" },
//                 { "data": "bien_codigo" },
//                 { "data": "bien_descripcion", "width": "50px "},
//                 { "data": "unidad_medida_descripcion" },
//                 { "data": "cantidad", "sClass": "alignRight"},
//                 { "data": "generador" },
//                 { "data": "serie_numero_requerimiento" },
//                 { "data": "fecha_creacion", "sClass": "alignCenter"},
//                 { "data": "area_descripcion", "sClass": "alignCenter" },
//                 { "data": "aprobacionRQ", "sClass": "alignCenter" },
//                 { "data": "aprobacionRQFecha", "sClass": "alignCenter" },
//             ],
//             columnDefs: [
//                 {
//                     "render": function (data, type, row) {
//                         cont = 1 + cont;
//                         return cont;
//                     },
//                     "targets": 0
//                 },
//             ],

//             "destroy": true
//         });
//     }
//     else {
//         var table = $('#datatable').DataTable();
//         table.clear().draw();
//     }
// }

function loaderBuscarDeuda() {
    loaderShow();
    var estadobuscador = $('#bg-info').attr("aria-expanded");
    if (estadobuscador == "false") {
        buscarSeguimiento();
    }
    loaderClose();
}

function verDetalleKardex(bienId, organizadorId, fechaInicio, fechaFin) {
    loaderShow();
    ax.setAccion("obtenerDetalleKardex");
    ax.addParamTmp("id_bien", bienId);
    ax.addParamTmp("id_organizador", organizadorId);
    ax.addParamTmp("fecha_inicio", fechaInicio);
    ax.addParamTmp("fecha_fin", fechaFin);
    ax.consumir();
}

function onResponseDetalleKardex(data) {
    if (!isEmptyData(data)) {
        $('[data-toggle="popover"]').popover('hide');
        var stringTituloStock = '<strong> ' + data[0]['organizador_descripcion'] + ' - ' + data[0]['bien_descripcion'] + '</strong>';

        $('#datatableStock').dataTable({
            order: [[0, "desc"]],
            "ordering": false,
            "data": data,
            "columns": [
                { "data": "unidad_medida_descripcion" },
                { "data": "cantidad", "sClass": "alignRight" }
            ],
            "destroy": true
        });
        $('.modal-title').empty();
        $('.modal-title').append(stringTituloStock);
        $('#modal-detalle-kardex').modal('show');
    }
    else {
        var table = $('#datatableStock').DataTable();
        table.clear().draw();
        mostrarAdvertencia("No se encontro detalles de este bien.")
    }
}

var actualizandoBusqueda = false;
function exportarReporteKardexExcel() {
    loaderShow();
    cargarDatosBusqueda();
    ax.setAccion("obtenerReporteKardexExcel");
    ax.addParamTmp("criterios", valoresBusquedaSeguimiento);
    ax.addParamTmp("tipo", 1);
    ax.consumir();
}

function loaderBuscar() {
    actualizandoBusqueda = true;
    loaderShow();
    var estadobuscador = $('#bg-info').attr("aria-expanded");
    if (estadobuscador == "false") {
        buscarSeguimiento();
    }
    loaderClose();
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
function cargarComboBien(dataBien) {
    $("#cboBien").select2({
        placeholder: "Buscar producto",
        allowClear: true,
        //            minimumInputLength: 1,
        data: dataBien,
        width: "100%",
        initSelection: function (element, callback) {
            var initialData = {
                id: "",
                text: ""
            };
            callback(initialData);
        },

        // NOT NEEDED: These are just css for the demo data
        dropdownCssClass: 'capitalize',
        containerCssClass: 'capitalize',
        // configure as multiple select
        multiple: true,
        // NOT NEEDED: text for loading more results
        formatLoadMore: 'Cargando más...',
        // query with pagination
        query: function (q) {
            var pageSize,
                results;
            pageSize = 20; // or whatever pagesize
            results = [];
            if (q.term && q.term !== "") {
                // HEADS UP; for the _.filter function i use underscore (actually lo-dash) here
                results = dataBien.filter(itemProducto => itemProducto.text.toUpperCase().indexOf(q.term.toUpperCase()) >= 0);
            } else if (q.term === "") {
                results = this.data;
            }
            q.callback({
                results: results.slice((q.page - 1) * pageSize, q.page * pageSize),
                more: results.length >= q.page * pageSize
            });
        }
    });
}

function negrita(cadena) {
    return "<b>" + cadena + "</b>";
}
