var documento_tipo = document.getElementById("documento_tipo").value;

$(document).ready(function () {
    criterioBusquedaDocumentos = [];
    cargarComponetentes();
    ax.setSuccess("onResponseAlmacenar");
    obtenerConfiguracionInicialListadoDocumentos();
    cambiarAnchoBusquedaDesplegable();
    select2.iniciarElemento("cboAlmacen");

});

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
            case 'visualizarDetalleEntrega':
                onResponseVisualizarEntrega(response.data);
                break;
            case 'obtenerStockPorBien':
                onResponseObtenerStockPorBienReserva(response.data, response[PARAM_TAG]);
                break;
            case 'generarSalidaSolicitud':
                dataStockOk = [];
                onResponseGenerarSalidaSolicitud(response.data);
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
    });
}

//here
function buscarPorCriterios() {
    var fechaEmision = {
        inicio: $('#inicioFechaEmisionAlmacenado').val(),
        fin: $('#finFechaEmisionAlmacenado').val()
    };

    var almacen = select2.obtenerValor("cboAlmacen");
    var serie = $("#txtSerie").val();
    var numero = $("#txtNumero").val();

    llenarParametrosBusqueda(fechaEmision, documento_tipo, almacen, serie, numero);

    buscarEntregas();
}

var criterioBusquedaDocumentos = {};

function llenarParametrosBusqueda(fechaEmision, tipoId, almacen, serie, numero) {
    criterioBusquedaDocumentos = {};
    criterioBusquedaDocumentos.fechaEmision = fechaEmision;
    criterioBusquedaDocumentos.almacen = almacen;
    criterioBusquedaDocumentos.tipoId = tipoId;
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

function buscarEntregas() {
    loaderShow();
    ax.setAccion("obtenerEntrega");
    ax.addParamTmp("criterios", criterioBusquedaDocumentos);
    $('#datatableEntregas').dataTable({
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
            { "data": "nombre_responsable", "class": "alignCenter" },
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
                    if(row.estado_descripcion != "Atendido"){
                        acciones += "<a href='#' onclick='visualizarEntrega(" + row.id + ", " + row.movimiento_id + ")'><i class='fa fa-ticket' style='color:green;' title='Realizar entrega'></i></a>&nbsp;&nbsp;";
                    }
                    acciones += "<a href='#' onclick='imprimirPdfDespacho(" + row.id + ", " + row.documento_tipo_id + ")'><i class='fa fa-print' style='color:blue;' title='Imprimir pdf'></i></a>&nbsp;";
                    return acciones;
                },
                "targets": 6
            }
        ],
        "dom": '<"top">rt<"bottom"<"col-md-3"l><"col-md-9"p><"col-md-12"i>><"clear">',
        destroy: true
    });
    loaderClose();
    $('#datatableEntregas').on('draw.dt', function () {
        loaderClose();
    });
}


function visualizarEntrega(id, movimientoId) {
    loaderShow();
    $("#documentoId").val(id);
    ax.setAccion("visualizarDetalleEntrega");
    ax.addParamTmp("id", id);
    ax.addParamTmp("movimientoId", movimientoId);
    ax.consumir();
}

var dataDetalle = [];
function onResponseVisualizarEntrega(data) {
    var cont = 0;
    var cont_ = 0;

    cargarDataDocumento(data.dataDocumento);

    if (!isEmpty(data.detalle)) {
        dataDetalle = data.detalle;
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
                { "data": "cantidad", "width": "9%", "sClass": "alignCenter" },
                { "data": "movimiento_bien_id", "width": "5%", "sClass": "alignCenter" },
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
                        var cantidad_entrega = row.cantidad_entrega;
                        return devolverDosDecimales((data - cantidad_entrega));
                    },
                    "targets": 3
                },
                {
                    "render": function (data, type, row) {
                        var acciones  = "";
                        if(row.bandera_entrega != 3){
                            acciones = "<a href='#' onclick='registrarEntrega(" + row.bien_id + ", " + cont_ + "," + row.unidad_medida_id + ")'><i class='fa fa-random' style='color:green;' title='Visualizar stock'></i></a>&nbsp;&nbsp;";
                        }
                        cont_++;
                        return acciones;
                    },
                    "targets": 4
                },
            ],
            "dom": '<"top">rt<"bottom"<"col-md-3"l><"col-md-9"p><"col-md-12"i>><"clear">',
            destroy: true
        });
        loaderClose();
    }
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


function registrarEntrega(bienId, indice, unidadMedidaId) {
    $('#modalDetalle').modal('hide');
    ax.setAccion("obtenerStockPorBien");
    ax.addParamTmp("bienId", bienId);
    ax.addParamTmp("indice", indice);
    ax.addParamTmp("almacenId", select2.obtenerValor("cboAlmacen"));
    ax.addParamTmp("unidadMedidaId", unidadMedidaId);
    ax.setTag(indice);
    ax.consumir();
}

var dataStockReserva = null;
function onResponseObtenerStockPorBienReserva(dataStock, indice) {
    $("#txtFila").val(indice);
    dataStockReserva = dataStock;
    var dataFiltrado = dataDetalle.filter(function (obj) {
        return obj.bien_id == dataStock[0].bien_id;
    });
    $("#txtCantidad").val(dataFiltrado[0].cantidad);
    $("#txtMovimientoBienId").val(dataFiltrado[0].movimiento_bien_id);
    $("#txtMovimientoId").val(dataFiltrado[0].movimiento_id);


    if (!isEmpty(dataStockOk)) {
        var dataFiltradoStockModal = dataStockOk.filter(function (obj) {
            return obj.bien_id == dataFiltrado[0].bien_id;
        });
        var dataFiltradoStock = dataStockOk.filter(function (obj) {
            return obj.bien_id != dataFiltrado[0].bien_id;
        });
        dataStockOk = dataFiltradoStock;
    }

    var tituloModal = '<strong>Strock</strong><br><strong>' + dataFiltrado[0].bien_descripcion + '</strong>';
    $('.modal-title-stock').empty();
    $('.modal-title-stock').append(tituloModal);

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
                { "data": "stock", "sClass": "alignRight" }
            ],
            columnDefs: [
                {
                    "render": function (data, type, row) {
                        return parseFloat(data).formatMoney(2, '.', ',');
                    },
                    "targets": 2
                },
                {
                    "render": function (data, type, row) {
                        var html = "";

                        var cantidad = '';
                        if (!isEmpty(dataFiltradoStockModal)) {
                            dataFiltradoStockModal.forEach(element => {
                                if (element.bien_id == row.bien_id && element.organizador_id == row.organizador_id) {
                                    cantidad = element.reserva;
                                }
                            });
                        }
                        html = "<div class=\"input-group col-lg-6 col-md-6 col-sm-6 col-xs-6\">" +
                            "<input type=\"number\" id=\"txtCantidadReserva_" + row.organizador_id + "\" name=\"txtCantidadReserva_" + row.organizador_id + "\" class=\"form-control\" required=\"\" aria-required=\"true\" style=\"text-align: right;\" value='" + cantidad + "' /></div><input type=\"hidden\" id=\"txtorganizadorReserva_" + row.organizador_id + "\" name=\"txtorganizadorReserva_" + row.organizador_id + "\" />";
                        i++;
                        return html;
                    },
                    "targets": 3
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

var dataStockModal = {};
var dataStockOk = [];
function generarReserva() {
    var bandera_modalReserva = true;
    var indice = $("#txtFila").val();
    var reserva_validacion = [];
    $.each(dataStockReserva, function (i, item) {
        var reserva = parseInt($("#txtCantidadReserva_" + item.organizador_id).val());
        if (reserva > 0 && !isEmpty(reserva)) {
            reserva_validacion.push(item.organizador_id);
        }
    });

    if (reserva_validacion.length == 0) {
        mostrarAdvertencia("No se puede guardar la distribucion, porque no se ha registrado un valor");
        return false;
    }
    var total_reserva = 0;
    $.each(dataStockReserva, function (i, item) {
        dataStockModal = {};
        if (reserva_validacion.includes(item.organizador_id)) {
            var reserva = parseInt($("#txtCantidadReserva_" + item.organizador_id).val());
            total_reserva += reserva;

            dataStockModal.reserva = reserva;
            dataStockModal.bien_id = item.bien_id;
            dataStockModal.bien_descripcion = item.bien_descripcion;
            dataStockModal.organizador_id = item.organizador_id;
            dataStockModal.unidad_medida_id = item.unidad_medida_id;
            dataStockModal.movimiento_bien_id = $("#txtMovimientoBienId").val();
            dataStockModal.movimiento_id = $("#txtMovimientoId").val();
            dataStockModal.documento_id = $("#documentoId").val();
            dataStockOk.push(dataStockModal);
        }
    });
    var cantidad_ = parseInt($("#txtCantidad").val());

    if (total_reserva > cantidad_) {
        bandera_modalReserva = false;
        mostrarAdvertencia("La suma de la cantidad a entregar no es igual a la solicitada");
        return false;
    }

    if (bandera_modalReserva) {
        $('#modalReservaStockBien').modal('hide');
        $('#modalDetalle').modal('show');
        var tabla = $('#dtmodalDetalle').DataTable();
        var fila = tabla.row(indice).node();
        $(fila).css('background-color', 'mediumspringgreen');
    }
}

function generarSalidaSolicitud() {
    ax.setAccion("generarSalidaSolicitud");
    ax.addParamTmp("dataStockOk", dataStockOk);
    ax.consumir();
}

function cerrarReserva() {
    swal(
        {
            title: "¿Desea continuar?",
            text: "Al cancelar se perderan los datos a entregar.",
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
                $('#modalReservaStockBien').modal('hide');
                $('#modalDetalle').modal('show');
                var indice = $("#txtFila").val();
                var tabla = $('#dtmodalDetalle').DataTable();
                var fila = tabla.row(indice).node();
                $(fila).css('background-color', '');
            } else {
                loaderClose();
            }
        }
    );
}

function onResponseGenerarSalidaSolicitud(data) {
    swal({
        title: data.tipo_mensaje == 1 ? "Confirmación" : "Advertencia",
        text: data.mensaje,
        type: data.tipo_mensaje == 1 ? "success" : "warning",
        html: true,
        showCancelButton: false,
        confirmButtonColor: "#33b86c",
        confirmButtonText: "Aceptar",
        closeOnConfirm: true,
        closeOnCancel: true,
        timer: 2000
    });
     $('#modalDetalle').modal('hide');
    buscarPorCriterios();
}
