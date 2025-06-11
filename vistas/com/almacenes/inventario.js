$(document).ready(function () {
    //    loaderShow();
    $('[data-toggle="popover"]').popover({ html: true }).popover();
    cargarTitulo("titulo", "");
    //select2.iniciar();
    select2.iniciarElemento("cboAlmacen");
    select2.iniciarElemento("cboTipoBien");
    iniciarDataPicker();
    ax.setSuccess("onResponseReporteBalance");
    obtenerConfiguracionesInicialesInventario();
    modificarAnchoTabla('datatable');
});

function onResponseReporteBalance(response) {
    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obtenerConfiguracionesInicialesInventario':
                onResponseObtenerConfiguracionesIniciales(response.data);
                //                loaderClose();
                buscarKardex();
                break;
            case 'obtenerStockPorBien':
                onResponseDetalleInventario(response.data);
                loaderClose();
                break;
            case 'obtenerReporteStockExcel':
                loaderClose();
                location.href = URL_BASE + "util/formatos/reporteStock.xlsx";
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

function obtenerConfiguracionesInicialesInventario() {
    ax.setAccion("obtenerConfiguracionesInicialesInventario");
    ax.consumir();
}

var arraydataBien = [];
function onResponseObtenerConfiguracionesIniciales(data) {
    if (!isEmpty(data.bien)) {
        select2.cargar("cboAlmacen", data.almacenes, "id", ["codigo", "descripcion"]);
        select2.asignarValor("cboAlmacen", data.almacenes[0]['id']);
        cargarComboBien(data.bien);
        arraydataBien = data.bien;
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
    loaderClose();
}

var valoresBusquedaInventario = [{ bien: "", bienTipo: "", fechaEmision: "" }];//bandera 0 es balance

function cargarDatosBusqueda() {
    var almacen = select2.obtenerValor("cboAlmacen");
    var bien = $('#cboBien').val();
    bien = bien == "" ? "" : bien.split(",");

    var bienTipo = $('#cboTipoBien').val();

    var fechaEmision = {
        inicio: $('#inicioFechaEmision').val(),
        fin: $('#finFechaEmision').val()
    };
    valoresBusquedaInventario[0].almacen = almacen;
    valoresBusquedaInventario[0].bien = bien;
    valoresBusquedaInventario[0].bienTipo = bienTipo;
    valoresBusquedaInventario[0].fechaEmision = fechaEmision;
}

function obtenerDatosBusqueda() {
    var cadena = "";
    cargarDatosBusqueda();

    if (!isEmpty(valoresBusquedaInventario[0].bien)) {
        cadena += negrita("Producto: ");
        cadena += select2.obtenerTextMultiple('cboBien');
        cadena += "<br>";
    }
    if (!isEmpty(valoresBusquedaInventario[0].bienTipo)) {
        cadena += negrita("Grupo de Producto: ");
        cadena += select2.obtenerTextMultiple('cboTipoBien');
        cadena += "<br>";
    }
    if (!isEmpty(valoresBusquedaInventario[0].fechaEmision.inicio) || !isEmpty(valoresBusquedaInventario[0].fechaEmision.fin)) {
        cadena += negrita("Fecha emisión: ");
        cadena += valoresBusquedaInventario[0].fechaEmision.inicio + " - " + valoresBusquedaInventario[0].fechaEmision.fin;
        cadena += "<br>";
    }
    return cadena;
}

function buscarKardex(colapsa) {
    loaderShow();
    var cadena;
    cadena = obtenerDatosBusqueda();
    if (!isEmpty(cadena) && cadena !== 0) {
        $('#idPopover').attr("data-content", cadena);
    }
    $('[data-toggle="popover"]').popover('show');
    banderaBuscar = 1;

    buscarInventario(cadena);

    if (colapsa === 1)
        colapsarBuscador();
}

function buscarInventario() {
    loaderShow();
    ax.setAccion("obtenerDataInventario");
    ax.addParamTmp("criterios", valoresBusquedaInventario);
    $('#datatableInvenario').dataTable({
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
            { "data": "codigo_descripcion", "class": "alignLeft" },
            { "data": "codigo_descripcion_tipo", "class": "alignLeft" },
            { "data": "unidad_medida_descripcion", "class": "alignCenter" },
            { "data": "stock", "class": "alignRight" },
            { "data": "id", "class": "alignCenter", "orderable": false }

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
                    acciones += "<a href='#' onclick='verDetalleInventario(" + row.id + ", " + row.unidad_medida_id + ")'><i class='fa fa-eye' style='color:green;' title='Recepcionar'></i></a>&nbsp;&nbsp;";
                    return acciones;
                },
                "targets": 4
            }
        ],
        "dom": '<"top">rt<"bottom"<"col-md-3"l><"col-md-9"p><"col-md-12"i>><"clear">',
        destroy: true
    });
    loaderClose();
    // Aquí se engancha el evento draw
    $('#datatableInvenario').on('draw.dt', function () {
        loaderClose();
    });
}

function loaderBuscarDeuda() {
    loaderShow();
    var estadobuscador = $('#bg-info').attr("aria-expanded");
    if (estadobuscador == "false") {
        buscarKardex();
    }
    loaderClose();
}

function verDetalleInventario(bienId, unidadMedidaId, indice) {
    loaderShow();
    ax.setAccion("obtenerStockPorBien");
    ax.addParamTmp("bienId", bienId);
    ax.addParamTmp("unidadMedidaId", unidadMedidaId);
    ax.addParamTmp("indice", indice);
    ax.addParamTmp("almacenId", select2.obtenerValor("cboAlmacen"));
    ax.consumir();
}

function onResponseDetalleInventario(dataStock) {
    // var bien_id = dataStock[0]['bien_id'];
    // var dataFiltrada = arraydataBien.filter(
    //     (item) => item.bien_id == bien_id
    // );
    // var tituloModal = '<strong>Strock</strong><br><strong>' + dataFiltrada[0]['codigo_descripcion']+ '</strong>';
    // $('.modal-title').empty();
    // $('.modal-title').append(tituloModal);

    var data = [];

    if (!isEmpty(dataStock)) {
        $.each(dataStock, function (i, item) {
            if (item.stock != 0) {
                data.push(item);
            }
        });
    }
    var i = 0;

    if (!isEmptyData(data)) {
        $('#datatableReservaStock').dataTable({
            order: [[0, "desc"]],
            "ordering": false,
            "data": data,
            "columns": [
                { "data": "organizador_descripcion" },
                { "data": "unidad_medida_descripcion" },
                { "data": "stock", "sClass": "alignRight" },
            ],
            columnDefs: [
                {
                    "render": function (data, type, row) {
                        return parseFloat(data).formatMoney(2, '.', ',');
                    },
                    "targets": 2
                },
            ],
            "destroy": true
        });
    } else {
        var table = $('#datatableReservaStock').DataTable();
        table.clear().draw();
    }

    $('#modalReservaStockBien').modal('show');
}

var actualizandoBusqueda = false;
function exportarReporteInventarioExcel() {
    loaderShow();
    cargarDatosBusqueda();
    ax.setAccion("obtenerReporteStockExcel");
    ax.addParamTmp("criterios", valoresBusquedaInventario);
    ax.consumir();
}

function loaderBuscar() {
    actualizandoBusqueda = true;
    loaderShow();
    var estadobuscador = $('#bg-info').attr("aria-expanded");
    if (estadobuscador == "false") {
        buscarKardex();
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

function iniciarDataPicker() {
    $('.fecha').datepicker({
        isRTL: false,
        format: 'dd/mm/yyyy',
        autoclose: true,
        language: 'es'
    });
}

function negrita(cadena) {
    return "<b>" + cadena + "</b>";
}

function devolverDosDecimales(num) {
    return redondearNumero(num).toFixed(2);
}