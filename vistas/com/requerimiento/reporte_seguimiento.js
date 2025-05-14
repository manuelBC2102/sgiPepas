$(document).ready(function () {
    $('[data-toggle="popover"]').popover({ html: true }).popover();
    cargarTitulo("titulo", "");
    select2.iniciarElemento("cboTipoBien");
    cargarComponetentes();
    ax.setSuccess("onResponseReporteBalance");
    obtenerConfiguracionesInicialesSeguimientoRequerimiento();

    $('#btn_mostrarColumnas').click(function () {
        var allValues = [];
        $('#miSelect option').each(function () {
            allValues.push($(this).val());
        });
        $('#miSelect').val(allValues).trigger('change');
    });
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
            case 'obtenerReporteSeguimiento':
                loaderClose();
                location.href = URL_BASE + "util/formatos/reporte.xlsx";
                break;
        }
    } else {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obtenerReporteSeguimiento':
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

    // Calcular la fecha de hace una semana (7 días)
    var fechaInicio = new Date();
    fechaInicio.setDate(fechaInicio.getDate() - 7);
    var diaInicio = ('0' + fechaInicio.getDate()).slice(-2);
    var mesInicio = ('0' + (fechaInicio.getMonth() + 1)).slice(-2);
    var anioInicio = fechaInicio.getFullYear();
    var fechaInicioFormateada = diaInicio + '/' + mesInicio + '/' + anioInicio;

    // Colocar la fecha de hace una semana en el campo "inicioFechaEmision"
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
            { "data": "bien_tipo_descripcion" },
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

    
    var tabla = $('#datatableSeguimiento').DataTable();
    select2.iniciarElemento("miSelect");
    var headers = [];
    var headers_ = [];
    $('#datatableSeguimiento thead th').each(function(index) {
        if(index <=9){
            headers.push($(this).text().trim());
        }
    });

    var $select = $('#miSelect');
    $select.empty(); // Limpiar opciones existentes si es necesario

    headers.forEach(function(header, index) {
        $select.append($('<option>', {
            value: index, // o usar `header` si prefieres el texto como valor
            text: header
        }));
        headers_.push({"index":index, "text": header});
    });
    var allValues = [];

    $('#miSelect option').each(function () {
        allValues.push($(this).val());
    });
    
    $('#miSelect').val(allValues).trigger('change');
    $select.on('change', function () {
        var selected = ($(this).val() || []).map(Number);
        selected.forEach(element => {
            tabla.column(element).visible(true);
        });
        var noSeleccionados = headers_.filter(function(header, index) {
            return !selected.includes(header.index);
        });
        noSeleccionados.forEach(function(element, index) {
            tabla.column(element.index).visible(false);
        });
    });
}

function loaderBuscarDeuda() {
    loaderShow();
    var estadobuscador = $('#bg-info').attr("aria-expanded");
    if (estadobuscador == "false") {
        buscarSeguimiento();
    }
    loaderClose();
}

var actualizandoBusqueda = false;
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

function exportarReporteSeguimiento(){
    loaderShow();
    cargarDatosBusqueda();
    ax.setAccion("obtenerReporteSeguimiento");
    ax.addParamTmp("criterios", valoresBusquedaKardex);
    ax.addParamTmp("tipo", 1);
    ax.consumir();
}