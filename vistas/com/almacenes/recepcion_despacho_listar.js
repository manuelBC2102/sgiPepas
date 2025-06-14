var recepcion = document.getElementById("recepcion").value;

$(document).ready(function () {
    criterioBusquedaDocumentos = [];
    cargarComponetentes();
    ax.setSuccess("onResponseAlmacenar");
    obtenerConfiguracionInicialListadoDocumentos();
    cambiarAnchoBusquedaDesplegable();
    select2.iniciarElemento("cboAlmacen");

    $('#selectAll').on('change', function () {
        var isChecked = this.checked;
        $('input[name=checkselect]').prop('checked', isChecked);
    });

    var dataFilasSeleccionadas = [];
    $('#btn_guardar').click(function () {
        var table = $('#datatableRecepcionDespacho').DataTable();
        var filasSeleccionadas = table.rows(':has(input[name=checkselect]:checked)').data().toArray();
        var bandera_generar = true;
        if (filasSeleccionadas.length <= 0) {
            $.Notification.autoHideNotify('warning', 'top right', 'Validación', " Seleccione uno o más paquetes, para realizar el proceso");
            bandera_generar = false;
            loaderClose();
            return false;
        }
        var documentoArray = [];

        filasSeleccionadas.forEach(function (detalleItem, idx) {
            documentoArray.push(detalleItem.documento_id);
            filasSeleccionadas[idx]['cantidad_recepcion'] = detalleItem.cantidad;
            filasSeleccionadas[idx]['unidad_medida_id'] = 15;
        });

        if (bandera_generar) {
            swal(
                {
                    title: "¿Desea continuar?",
                    text: "Al generar al recepción no se podrá revertir.",
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
                        var unicos = [...new Set(documentoArray)];
                        generarRecepcionDespacho(filasSeleccionadas, unicos[0]);
                    } else {
                        loaderClose();
                    }
                }
            );
        }
    });
});
var dataOrganizadoresHijos = [];

function onResponseAlmacenar(response) {
    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obtenerConfiguracionInicialListadoDocumentos':
                onResponseObtenerConfiguracionInicialListadoPaqueteAlmacenar(response.data);
                buscarPorCriterios();
                loaderClose();
                break;
            case 'generarDistribucionQR':
                onResponsegenerarDistribucionQR(response.data);
                $("#modalDetalleRecepcionado").modal('hide');
                buscarPorCriterios();
                break;
            case 'getDataOrganizadoresHijos':
                dataOrganizadoresHijos = response.data;
                break;
            case 'generarRecepcionDespacho':
                // $("#modalDetalleRecepcionDespacho").modal('hide');
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
    ax.addParamTmp("recepcion", recepcion);
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
        obtenerOrganizadoresHijos(e.val);
    });
}

//here
function buscarPorCriterios() {
    var fechaEmision = {
        inicio: $('#inicioFechaEmisionAlmacenado').val(),
        fin: $('#finFechaEmisionAlmacenado').val()
    };

    var almacen = select2.obtenerValor("cboAlmacen");
    var serie = $("#txtSerieDespacho").val();
    var numero = $("#txtNumeroDespacho").val();

    llenarParametrosBusqueda(fechaEmision, almacen, serie, numero);

    buscarRecepcionDespacho();
}

var criterioBusquedaDocumentos = {};

function llenarParametrosBusqueda(fechaEmision, almacen, serie, numero) {
    criterioBusquedaDocumentos = {};
    criterioBusquedaDocumentos.fechaEmision = fechaEmision;
    criterioBusquedaDocumentos.almacen = almacen;
    criterioBusquedaDocumentos.serie = serie;
    criterioBusquedaDocumentos.numero = numero;
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

function buscarRecepcionDespacho() {
    loaderShow();
    ax.setAccion("obtenerPaqueteRecepcionDespacho");
    ax.addParamTmp("criterios", criterioBusquedaDocumentos);
    $('#datatableRecepcionDespacho').dataTable({
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
            { "data": "serie_numero", "class": "alignCenter" },
            { "data": "paquete_id", "class": "alignCenter", "render": function (data, type, row) { return '<strong>' + data + '</strong>'; } },
            { "data": "bien_codigo_descripcion", "class": "aligalignCenternLeft" },
            { "data": "cantidad", "width": "10%", "sClass": "alignRight" },
            {
                "data": "paquete_detalle_id",
                render: function (data, type, row) {
                    if (type === 'display') {
                        return '<input type="checkbox" name="checkselect" class="select-checkbox" value="' + row.movimiento_bien_id + '">';
                    }
                    return "";
                },
                "orderable": false,
                "class": "alignCenter",
                "width": "5%"
            },
        ],
        columnDefs: [
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
    $('#datatableRecepcionDespacho').on('draw.dt', function () {
        loaderClose();
    });
}

var cuerpo = '';
function onResponseObtenerPaqueteDetalleXPaqueteId(data, idx) {
    $('#dtmodalDetallePaquete tbody').empty();
    var cont = 0;
    if (!isEmpty(data)) {
        cuerpo += "<tr id='tr_'" + idx + ">";
        cuerpo += "<td align='center' style='align-content: center;'><strong>" + data.paquete_id + "</strong></td>";
        cuerpo += "<td style='align-content: center;'>" + data.bien_codigo_descripcion + "</td>";
        cuerpo += "<td style='align-content: center;'>" + data.organizador_actual_descripcion + "</td>";
        cuerpo += "<td align='center' style='align-content: center;'>" + devolverDosDecimales(data.cantidad) + "</td>";
        cuerpo += "</tr>";
        $('#dtmodalDetallePaquete tbody').append(cuerpo);
        loaderClose();
    }
}

function obtenerOrganizadoresHijos(id) {
    ax.setAccion("getDataOrganizadoresHijos");
    ax.addParamTmp("almacenId", id);
    ax.consumir();
}

function generarRecepcionDespacho(filasSeleccionadas, documentoId) {
    ax.setAccion("generarRecepcionDespacho");
    ax.addParamTmp("documentoId", documentoId);
    ax.addParamTmp("almacenId", $("#cboAlmacen").val());
    ax.addParamTmp("dataFilasSeleccionadas", filasSeleccionadas);
    ax.addParamTmp("empresaId", commonVars.empresa);
    ax.consumir();
}