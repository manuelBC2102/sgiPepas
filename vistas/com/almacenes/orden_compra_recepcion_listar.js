var documento_tipo = document.getElementById("documento_tipo").value;
var documento_tipo1 = document.getElementById("documento_tipo1").value;

var tabActivo = 1;
var documentoTipoActivo = null;
var lstDocumentoArchivos = [];

$(document).ready(function () {
    criterioBusquedaDocumentos = [];
    cargarComponetentes();
    ax.setSuccess("onResponseAprobacionOrdenCompraServicio");
    obtenerConfiguracionInicialListadoDocumentos();
    cambiarAnchoBusquedaDesplegable();
    select2.iniciarElemento("cboAlmacen");

    $('#selectAll').on('change', function () {
        var isChecked = this.checked;
        $('input[name=checkselect]').prop('checked', isChecked);
    });

    $('#btn_guardar').click(function () {
        loaderShow('#modalDetalle');
        var table = $('#dtmodalDetalle').DataTable();
        var filasSeleccionadas = table.rows(':has(input[name=checkselect]:checked)').data().toArray();
        var bandera_copia = true;
        if (filasSeleccionadas.length <= 0) {
            $.Notification.autoHideNotify('warning', 'top right', 'Validación', " Seleccione uno o más comprobantes, para realizar el proceso");
            bandera_copia = false;
            loaderClose();
        }

        filasSeleccionadas.forEach(function (detalleItem, idx) {
            var cantidadRecepcion = devolverDosDecimales($("#cantidad_recepcion_" + detalleItem.movimiento_bien_id).val());
            if (parseFloat(devolverDosDecimales(cantidadRecepcion)) > parseFloat(devolverDosDecimales(detalleItem.cantidad_por_recepcionar))) {
                mostrarAdvertencia("Cantidad de recepción tiene que ser menor a la cantidad de la orden de compra para el item: " + detalleItem.bien_descripcion);
                bandera_copia = false;
                loaderClose();
                return false;
            }
            if (devolverDosDecimales($("#cantidad_recepcion_" + detalleItem.movimiento_bien_id).val()) == 0) {
                mostrarAdvertencia("Cantidad a recepcionar tiene que ser mayor a 0, para el item: " + (idx + 1));
                bandera_copia = false;
                loaderClose();
                return false;
            }
            filasSeleccionadas[idx]['cantidad_recepcion'] = cantidadRecepcion;
        });

        if (bandera_copia) {
            ax.setAccion("guardarDetalleRecepcion");
            ax.addParamTmp("filasSeleccionadas", filasSeleccionadas);
            ax.setTag(filasSeleccionadas[0].movimiento_id);
            ax.consumir();
        }
    });

    var dataFilasSeleccionadas = [];
    $('#btn_generar').click(function () {
        loaderShow('#modalDetalle');
        var table = $('#dtmodalDetalle').DataTable();
        var filasSeleccionadas = table.rows(':has(input[name=checkselect]:checked)').data().toArray();
        var bandera_copia = true;
        if (filasSeleccionadas.length <= 0) {
            $.Notification.autoHideNotify('warning', 'top right', 'Validación', " Seleccione uno o más comprobantes, para realizar el proceso");
            bandera_copia = false;
            loaderClose();
        }

        filasSeleccionadas.forEach(function (detalleItem, idx) {
            var cantidadRecepcion = detalleItem.doc_tipo_id == DESPACHO ? detalleItem.cantidad : devolverDosDecimales($("#cantidad_recepcion_" + detalleItem.movimiento_bien_id).val());
            if (parseFloat(devolverDosDecimales(cantidadRecepcion)) > parseFloat(devolverDosDecimales(detalleItem.cantidad_por_recepcionar))) {
                mostrarAdvertencia("Cantidad de recepción tiene que ser menor a la cantidad de la orden de compra para el item: " + (idx + 1));
                bandera_copia = false;
                loaderClose();
                return false;
            }
            filasSeleccionadas[idx]['cantidad_recepcion'] = cantidadRecepcion;
        });

        dataFilasSeleccionadas = filasSeleccionadas;

        if (bandera_copia) {
            var cuerpo = '';
            $("#modalDetalle").modal('hide');
            $('#dtmodalDetalleRecepcionado tbody').empty();
            $("#modalDetalleRecepcionado").modal('show');
            if (!isEmpty(filasSeleccionadas)) {
                filasSeleccionadas.forEach(function (detalleItem, idx) {
                    var cantidadFila = Array.isArray(detalleItem.distribucion_unidad_minera)
                        ? detalleItem.distribucion_unidad_minera.length
                        : 1;
                    cuerpo += "<tr> <input type='hidden' id='count_fila_" + idx + "' name='count_fila_" + idx + "' value='" + idx + "'>";
                    cuerpo += "<td align='center' rowspan='" + cantidadFila + "'>" + (idx + 1) + "</td>";
                    cuerpo += "<td align='left' style='align-content: center;' rowspan='" + cantidadFila + "'>" + detalleItem.bien_descripcion + "</td>";
                    cuerpo += "<td align='right' style='align-content: center;' rowspan='" + cantidadFila + "'>" + devolverDosDecimales(detalleItem.cantidad_recepcion) + "</td>";

                    detalleItem.distribucion_unidad_minera.forEach(function (detalleItemUnidadMinera, idxDistribucion) {
                        detalleItemUnidadMinera.movimiento_bien_id = detalleItem.movimiento_bien_ids;
                        // dataArrayUnidadMinera.push(detalleItemUnidadMinera);
                        if (idxDistribucion > 0) cuerpo += "<tr><input type='hidden' id='count_fila_distribucion_" + idxDistribucion + "' name='count_fila_distribucion_" + idxDistribucion + "' value='" + idxDistribucion + "'>";

                        cuerpo += "<td><strong>" + detalleItemUnidadMinera.unidad_minera + ": </strong>" + devolverDosDecimales(detalleItemUnidadMinera.cantidad) + "</td>";
                        cuerpo += "<td>";
                        cuerpo += "<select id='cbo_organizador_destino_" + idx + "_" + idxDistribucion + "_" + detalleItemUnidadMinera.unidad_minera_id + "' name='cbo_organizador_destino_" + idx + "_" + idxDistribucion + "_" + detalleItemUnidadMinera.unidad_minera_id + "' class='select2'>";
                        cuerpo += "</select>";
                        cuerpo += "</td>";

                        cuerpo += "<td>";
                        cuerpo += "<select id='cbo_tipo_" + idx + "_" + idxDistribucion + "_" + detalleItemUnidadMinera.unidad_minera_id + "' name='cbo_tipo_" + idx + "_" + idxDistribucion + "_" + detalleItemUnidadMinera.unidad_minera_id + "' class='select2'>";
                        cuerpo += "</select>";
                        cuerpo += "</td>";

                        cuerpo += "<td id='td_" + idx + "_" + idxDistribucion + "_" + detalleItemUnidadMinera.unidad_minera_id + "'>";
                        var cuerpo_distribucion = agregardistribucion(detalleItemUnidadMinera.unidad_minera_id, 0, idx, idxDistribucion);
                        cuerpo += cuerpo_distribucion;
                        cuerpo += "</td>";

                        if (idxDistribucion > 0) cuerpo += "</tr>";
                    });
                    cuerpo += "</tr>";
                });
            }
            $('#dtmodalDetalleRecepcionado tbody').append(cuerpo);
            filasSeleccionadas.forEach(function (detalleItem, idx) {
                detalleItem.distribucion_unidad_minera.forEach(function (detalleItemUnidadMinera, idxDistribucion) {
                    select2.cargar("cbo_tipo_" + idx + "_" + idxDistribucion + "_" + detalleItemUnidadMinera.unidad_minera_id, [{ id: 1, descripcion: "Unidades" }, { id: 2, descripcion: "Paquetes" }], "id", "descripcion");
                    $("#cbo_tipo_" + idx + "_" + idxDistribucion + "_" + detalleItemUnidadMinera.unidad_minera_id).select2({ width: '100%' }).on("change", function (e) {
                        if (e.val == 1) {
                            $("#paq_" + idx + "_" + idxDistribucion + "_0_" + detalleItemUnidadMinera.unidad_minera_id).addClass('hidden');
                            $("#cantidad_recepcion_a_" + idx + "_" + idxDistribucion + "_0_" + detalleItemUnidadMinera.unidad_minera_id).prop('disabled', true);
                            $("#cantidad_recepcion_a_" + idx + "_" + idxDistribucion + "_0_" + detalleItemUnidadMinera.unidad_minera_id).addClass('hidden');
                            var a = (parseInt($("#count_" + idx + "_" + idxDistribucion + "_" + detalleItemUnidadMinera.unidad_minera_id).val()) + 1);
                            for (let index = 1; index < a; index++) {
                                eliminardistribucion(idx, idxDistribucion, index, detalleItemUnidadMinera.unidad_minera_id);
                            }
                            $("#chk_" + idx + "_" + idxDistribucion + "_0_" + detalleItemUnidadMinera.unidad_minera_id).attr('style', 'color:gray; opacity:0.5; cursor:not-allowed;display:none;');
                            $("#chk_" + idx + "_" + idxDistribucion + "_0_" + detalleItemUnidadMinera.unidad_minera_id).attr('onclick', '');
                            $("#chk_" + idx + "_" + idxDistribucion + "_0_" + detalleItemUnidadMinera.unidad_minera_id).hide();

                            $("#cantidad_recepcion_a_" + idx + "_" + idxDistribucion + "_0_" + detalleItemUnidadMinera.unidad_minera_id).val(1);
                        } else {
                            var count_ = parseInt($("#count_" + idx + "_" + idxDistribucion + "_" + detalleItemUnidadMinera.unidad_minera_id).val()) + 1;
                            $("#paq_" + idx + "_" + idxDistribucion + "_0_" + detalleItemUnidadMinera.unidad_minera_id).removeClass('hidden');
                            $("#cantidad_recepcion_a_" + idx + "_" + idxDistribucion + "_0_" + detalleItemUnidadMinera.unidad_minera_id).prop('disabled', false);
                            $("#cantidad_recepcion_a_" + idx + "_" + idxDistribucion + "_0_" + detalleItemUnidadMinera.unidad_minera_id).removeClass('hidden');
                            $("#chk_" + idx + "_" + idxDistribucion + "_0_" + detalleItemUnidadMinera.unidad_minera_id).attr('style', 'color: blue;');
                            $("#chk_" + idx + "_" + idxDistribucion + "_0_" + detalleItemUnidadMinera.unidad_minera_id).show();
                            $("#chk_" + idx + "_" + idxDistribucion + "_0_" + detalleItemUnidadMinera.unidad_minera_id).attr('onclick', 'agregardistribucion(' + detalleItemUnidadMinera.unidad_minera_id + ", " + count_ + "," + idx + "," + idxDistribucion + ')');
                        }
                    });
                    var unidad_minera_id = detalleItemUnidadMinera.unidad_minera_id;
                    var dataFiltrada = dataOrganizadorXUnidadMinera.filter(
                        (item) => item.unidad_minera_id == unidad_minera_id
                    );
                    select2.cargar("cbo_organizador_destino_" + idx + "_" + idxDistribucion + "_" + detalleItemUnidadMinera.unidad_minera_id, dataFiltrada, "id", ["codigo", "descripcion"]);
                    $("#cbo_organizador_destino_" + idx + "_" + idxDistribucion + "_" + detalleItemUnidadMinera.unidad_minera_id).select2({ width: '100%' })

                });
            });
        }
    });

    $('#btn_generarDistribucionQR').click(function () {
        loaderClose('#modalDetalle');
        loaderShow();
        var bandera_generar = true;
        var arrayDatoFila = [];
        var almacen = select2.obtenerValor("cboAlmacen");

        if (almacen == 71) {
            if (isEmpty($("#serie_numeroGuia").val())) {
                mostrarAdvertencia("Debe ingresar serie y número de Guía");
                bandera_generar = false;
                loaderClose();
                return false;
            }
            if (isEmpty($("#peso").val())) {
                mostrarAdvertencia("Debe ingresar peso de Guía");
                bandera_generar = false;
                loaderClose();
                return false;
            }
            if (isEmpty($("#volumen").val())) {
                mostrarAdvertencia("Debe ingresar volumen de Guía");
                bandera_generar = false;
                loaderClose();
                return false;
            }
            if (isEmpty($("#inputFilePdfGuia").val())) {
                mostrarAdvertencia("Debe adjuntar pdf de Guía");
                bandera_generar = false;
                loaderClose();
                return false;
            }
        }

        dataFilasSeleccionadas.forEach(function (detalleItem, idx) {
            var arrayDistribucionQR = [];
            var cantidad_total = 0;
            detalleItem.distribucion_unidad_minera.forEach(function (detalleItemUnidadMinera, idxDistribucion) {
                var cantidad_a_b = 0;
                var count = $("#count_" + idx + "_" + idxDistribucion + "_" + detalleItemUnidadMinera.unidad_minera_id).val();
                var distribucionArray = [];

                for (let index1 = 0; index1 < (parseInt(count) + 1); index1++) {
                    var unidad_a = ($("#cantidad_recepcion_a_" + idx + "_" + idxDistribucion + "_" + index1 + "_" + detalleItemUnidadMinera.unidad_minera_id).val());
                    var unidad_b = ($("#cantidad_recepcion_b_" + idx + "_" + idxDistribucion + "_" + index1 + "_" + detalleItemUnidadMinera.unidad_minera_id).val());
                    cantidad_a_b += (unidad_a * unidad_b);
                    if (cantidad_a_b == 0) {
                        mostrarAdvertencia("Ingrese cantidad en distribución");
                        bandera_generar = false;
                        loaderClose();
                        return false;
                    }

                    var tipo = select2.obtenerValor("cbo_tipo_" + idx + "_" + idxDistribucion + "_" + detalleItemUnidadMinera.unidad_minera_id);
                    var organizador_destino_id = select2.obtenerValor("cbo_organizador_destino_" + idx + "_" + idxDistribucion + "_" + detalleItemUnidadMinera.unidad_minera_id);
                    distribucionArray.push({ "unidad_minera_id": detalleItemUnidadMinera.unidad_minera_id, "indice1": unidad_a, "indice2": unidad_b, "tipo": tipo, "organizador_id": organizador_destino_id, "organizador_destino_id": organizador_destino_id });
                }

                if (cantidad_a_b > detalleItemUnidadMinera.cantidad) {
                    mostrarAdvertencia("La suma de la distribución es mayor a la asiganda para la unidad minera");
                    bandera_generar = false;
                    loaderClose();
                    return false;
                }
                arrayDistribucionQR.push({ "unidad_minera_id": detalleItemUnidadMinera.unidad_minera_id, "distribucion": distribucionArray, "cantidad": detalleItemUnidadMinera.cantidad });
                cantidad_total += cantidad_a_b;
            });
            arrayDatoFila.push({ "bien_id": detalleItem.bien_id, "movimiento_bien_id": detalleItem.movimiento_bien_id, "cantidad": detalleItem.cantidad_recepcion, "arrayDistribucionQR": arrayDistribucionQR });

            if (cantidad_total != detalleItem.cantidad_recepcion) {
                mostrarAdvertencia("La suma de la distribución no es igual a la cantidad recepcionada ");
                bandera_generar = false;
                loaderClose();
                return false;
            }
        });

        if (bandera_generar) {
            swal(
                {
                    title: "¿Desea continuar?",
                    text: "Al generar la distribución de los QR no se podrá revertir.",
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
                        generarDistribucionQR(arrayDatoFila, dataFilasSeleccionadas);
                    } else {
                        loaderClose();
                    }
                }
            );
        }
    });


    $('#inputFilePdfGuia').change(function () {
        lstDocumentoArchivos = [];
        var archivo = $(this).val().split('\\').pop();  // Obtener solo el nombre del archivo
        var nombreReducido = archivo.length > 25 ? archivo.slice(0, 10) + "..." + archivo.slice(-10) : archivo;

        $("#text_archivo").html(nombreReducido);

        if (this.files && this.files[0]) {
            var documento = {};
            var reader = new FileReader();

            reader.onload = function (e) {
                documento.data = e.target.result;
                documento.archivo = archivo;
                documento.id = "t0";
                documento.contenido_archivo = "";
                lstDocumentoArchivos = [documento];
            };

            reader.readAsDataURL(this.files[0]);
        }
    });
});

function onResponseAprobacionOrdenCompraServicio(response) {
    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obtenerConfiguracionInicialListadoDocumentos':
                onResponseObtenerConfiguracionInicialListadoDocumentos(response.data);
                buscarPorCriterios();
                loaderClose();
                break;
            case 'visualizarDetalle':
                onResponseVisualizarOrdenCompra(response.data);
                break;
            case 'guardarDetalleRecepcion':
                onResponseguardarDetalleRecepcion(response.data, response.tag);
                break;
            case 'generarDistribucionQR':
                onResponsegenerarDistribucionQR(response.data);
                $("#modalDetalleRecepcionado").modal('hide');
                buscarPorCriterios();
                break;
            case 'anular':
                onResponseAnular(response.data);
                loaderClose();
                break;
        }
    } else {
        switch (response[PARAM_ACCION_NAME]) {
            case 'guardarDetalleRecepcion':
                loaderClose();
                break;
            case 'generarDistribucionQR':
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

function buscarOrdenCompra() {
    loaderShow();
    ax.setAccion("obtenerOrdenCompra");
    ax.addParamTmp("criterios", criterioBusquedaDocumentos);
    $('#datatableOrdenCompra').dataTable({
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
            { "data": "documento_tipo_descripcion", "class": "alignCenter" },
            { "data": "proveedor_nombre_completo", "class": "alignLeft" },
            { "data": "fecha_creacion", "class": "alignCenter" },
            { "data": "usuario_creacion", "class": "alignCenter" },
            { "data": "estado_descripcion", "class": "alignCenter" },
            { "data": "id", "class": "alignCenter", "orderable": false }

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
                    acciones += "<a href='#' onclick='visualizarOrdenCompra(" + row.id + ", " + row.movimiento_id + ")'><i class='fa fa-random' style='color:green;' title='Recepcionar'></i></a>&nbsp;&nbsp;";
                    acciones += "<a href='#' onclick='imprimirOrdenCompra(" + row.id + ", " + row.documento_tipo_id + ")'><i class='fa fa-print' style='color:blue;' title='Imprimir pdf OC'></i></a>&nbsp;";
                    return acciones;
                },
                "targets": 6
            }
        ],
        "dom": '<"top">rt<"bottom"<"col-md-3"l><"col-md-9"p><"col-md-12"i>><"clear">',
        destroy: true
    });
    loaderClose();
    // Aquí se engancha el evento draw
    $('#datatableOrdenCompra').on('draw.dt', function () {
        loaderClose();
    });
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

function onResponseObtenerConfiguracionInicialListadoDocumentos(data) {
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
    var arrayDocumentoTipo = documento_tipo;
    var serie = $("#txtSerieRecepcionOC").val();
    var numero = $("#txtNumeroRecepcionOC").val();

    llenarParametrosBusqueda(fechaEmision, 3, arrayDocumentoTipo, almacen, serie, numero);

    buscarOrdenCompra();
}

var criterioBusquedaDocumentos = {};

function llenarParametrosBusqueda(fechaEmision, estadoId, tipoId, almacen, serie, numero) {
    criterioBusquedaDocumentos = {};
    criterioBusquedaDocumentos.fechaEmision = fechaEmision;
    criterioBusquedaDocumentos.estadoId = estadoId;
    criterioBusquedaDocumentos.tipoId = tipoId;
    criterioBusquedaDocumentos.almacen = almacen;
    criterioBusquedaDocumentos.serie = serie;
    criterioBusquedaDocumentos.numero = numero;
}

function cambiarAnchoBusquedaDesplegable() {
    var ancho = $("#divBuscador").width();
    $("#ulBuscadorDesplegable").width((ancho - 5) + "px");

    var ancho = $("#divBuscador").width();
    $("#ulBuscadorDesplegableRecepcion").width((ancho - 5) + "px");
}


function limpiarBuscadores() {
    $('#inicioFechaEmision').val('');
    $('#finFechaEmision').val('');

    criterioBusquedaDocumentos = {};
}

function visualizarOrdenCompra(id, movimientoId) {
    loaderShow();
    documentoTipoActivo = documento_tipo;
    $("#documentoId").val(id);
    ax.setAccion("visualizarDetalle");
    ax.addParamTmp("id", id);
    ax.addParamTmp("movimientoId", movimientoId);
    ax.consumir();
}

var dataOrganizadorXUnidadMinera = [];
var dataDocumentoAdjunto = [];
function onResponseVisualizarOrdenCompra(data) {
    var cont = 0;
    dataOrganizadorXUnidadMinera = data.dataOrganizadorXUnidadMinera;
    dataDocumentoAdjunto = data.dataDocumentoAdjunto;

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
                { "data": "cantidad", "width": "9%", "sClass": "alignCenter" },
                { "data": "cantidad_recepcionada", "width": "5%", "sClass": "alignCenter" },
                { "data": "cantidad_por_recepcionar", "width": "5%", "sClass": "alignCenter" },
                { "data": "movimiento_bien_ids", "width": "5%", "sClass": "alignCenter" },
                {
                    "data": "movimiento_bien_id",
                    render: function (data, type, row) {
                        var checked = row.bandera_recepcion == 2 ? "checked" : "";
                        if (row.cantidad_por_recepcionar > 0) {
                            if (type === 'display') {
                                return '<input type="checkbox" name="checkselect" class="select-checkbox" value="' + row.movimiento_bien_id + '" ' + checked + '>';
                            }
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
                    "targets": [3, 4]
                },
                {
                    "render": function (data, type, row) {
                        var tabla = $('#dtmodalDetalle').DataTable();
                        if (row.doc_tipo_id == DESPACHO) {
                            setTimeout(function () {
                                tabla.column(6).visible(false);
                            }, 100);
                        } else {
                            if (documentoTipoActivo == RECEPCION) {
                                setTimeout(function () {
                                    tabla.column(4).visible(false);
                                    tabla.column(5).visible(false);
                                    tabla.column(6).visible(false);
                                    tabla.column(7).visible(false);
                                }, 100);
                                $("#btn_guardar").hide();
                                $("#btn_generar").hide();
                            } else {
                                setTimeout(function () {
                                    tabla.column(4).visible(true);
                                    tabla.column(5).visible(true);
                                    tabla.column(6).visible(true);
                                    tabla.column(7).visible(true);
                                }, 100);
                                $("#btn_guardar").show();
                                $("#btn_generar").show();
                            }
                        }

                        if (row.cantidad_por_recepcionar > 0) {
                            return "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\" style=\"display: flex; align-items: center; gap: 8px;\">" +
                                "<input type=\"number\" id=\"cantidad_recepcion_" + row.movimiento_bien_id + "\" name=\"cantidad_recepcion_" + row.movimiento_bien_id + "\" class=\"form-control\" style=\"text-align: right;\"  value='" + devolverDosDecimales(row.cantidad_recepcion) + "' /></div>";
                        } else {
                            return devolverDosDecimales(row.cantidad_por_recepcionar);
                        }
                    },
                    "targets": 6
                },
            ],
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
        // data.sort((a, b) => a.tipo - b.tipo);
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
                    case 23:
                    case 10:
                    case 11:
                    case 41:
                    case 45:
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
                    case 17:
                        valor = "";
                        break;
                }
            }

            html += '' + valor + '';

            html += '</div></div>';
            appendFormDetalle(html);
        });
        var html_ = '';
        if (!isEmpty(dataDocumentoAdjunto)) {
            html_ = '<div class="form-group col-md-12"><div class="col-lg-4 col-md-4 col-sm-6 col-xs-6"><label>Pdf Guía</label></div><div class="col-lg-8 col-md-8 col-sm-6 col-xs-6">' + dataDocumentoAdjunto[0]['archivo'] + '&nbsp;&nbsp;<a href="util/uploads/documentoAdjunto/' + dataDocumentoAdjunto[0]['nombre'] + '" download="' + dataDocumentoAdjunto[0]['archivo'] + '" target="_blank"><i class="fa fa-cloud-download" style="color:#1ca8dd;"></i></a>' + '</div></div>';
        }
        appendFormDetalle(html_ + '</div>');
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
    $('#finFechaEmisionRecepcion').val(fechaFormateada);

    // Calcular la fecha de hace un mes
    fechaActual.setMonth(fechaActual.getMonth() - 1);
    var diaInicio = ('0' + fechaActual.getDate()).slice(-2);
    var mesInicio = ('0' + (fechaActual.getMonth() + 1)).slice(-2);
    var anioInicio = fechaActual.getFullYear();
    var fechaInicioFormateada = diaInicio + '/' + mesInicio + '/' + anioInicio;

    // Colocar la fecha de hace un mes en el campo "inicioFechaEmision"
    $('#inicioFechaEmision').val(fechaInicioFormateada);
    $('#inicioFechaEmisionRecepcion').val(fechaInicioFormateada);
}

function imprimirOrdenCompra(documentoId, documentoTipoId) {
    const link = document.createElement('a');
    if (documentoTipoId == DESPACHO) {
        link.href = URL_BASE + "vistas/com/almacenes/despacho_pdf.php?id=" + documentoId;
    } else {
        link.href = URL_BASE + "vistas/com/compraServicio/compra_servicio_pdf.php?id=" + documentoId + "&documentoTipoId=" + documentoTipoId;
    }
    link.target = '_blank';
    link.click();
}


function onResponseguardarDetalleRecepcion(data, movimiento_id) {
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
    if (data.tipo_mensaje == 1) {
        visualizarOrdenCompra($("#documentoId").val(), movimiento_id);
    }
}

function actualizarTabActivo(tab) {
    tabActivo = tab;
    actualizarBusqueda();
}

function buscarPorCriterios2() {
    var fechaEmision = {
        inicio: $('#inicioFechaEmisionRecepcion').val(),
        fin: $('#finFechaEmisionRecepcion').val()
    };

    var almacen = select2.obtenerValor("cboAlmacen");
    var estadoId = select2.obtenerValor("cboEstado");
    var serie = $("#txtSerieRecepcion").val();
    var numero = $("#txtNumeroRecepcion").val();

    llenarParametrosBusquedaRecepcion(fechaEmision, estadoId, documento_tipo1, almacen, serie, numero);

    buscarRecepcion();
}

criterioBusquedaDocumentosRecepcion = {};
function llenarParametrosBusquedaRecepcion(fechaEmision, estadoId, tipoId, almacen, serie, numero) {
    criterioBusquedaDocumentosRecepcion = {};
    criterioBusquedaDocumentosRecepcion.fechaEmision = fechaEmision;
    criterioBusquedaDocumentosRecepcion.estadoId = estadoId;
    criterioBusquedaDocumentosRecepcion.tipoId = tipoId;
    criterioBusquedaDocumentosRecepcion.almacen = almacen;
    criterioBusquedaDocumentosRecepcion.serie = serie;
    criterioBusquedaDocumentosRecepcion.numero = numero;
}

function buscarRecepcion() {
    loaderShow();
    ax.setAccion("obtenerRecepcion");
    ax.addParamTmp("criterios", criterioBusquedaDocumentosRecepcion);
    $('#datatableRecepcion').dataTable({
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
            { "data": "id", "class": "alignCenter", "orderable": false }

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
                    acciones += "<a href='#' onclick='visualizarRecepcion(" + row.id + ", " + row.movimiento_id + ")'><i class='fa fa-eye' style='color:green;' title='Ver detalle programación'></i></a>&nbsp;&nbsp;";
                    if (row.documento_estado_id != 2) {
                        acciones += "<a href='#' onclick='imprimirPdfRecepción(" + row.id + ", " + row.documento_tipo_id + ")'><i class='fa fa-print' style='color:blue;' title='Imprimir Qr de paquetes'></i></a>&nbsp;&nbsp;";
                        if (row.relacion_despacho == 0) {
                            acciones += "<a href='#' onclick='imprimirPdfQR(" + row.id + ", " + row.documento_tipo_id + ")'><i class='fa fa-qrcode' style='color:orange;' title='Imprimir Qr de paquetes'></i></a>&nbsp;&nbsp;";
                        }
                        acciones += "<a href='#' onclick='anularDocumento(" + row.id + ", " + row.documento_tipo_id + ")'><i class='fa fa-ban' style='color:#cb2a2a' title='Imprimir Qr de paquetes'></i></a>&nbsp;&nbsp;";
                    }
                    return acciones;
                },
                "targets": 6
            }
        ],
        "dom": '<"top">rt<"bottom"<"col-md-3"l><"col-md-9"p><"col-md-12"i>><"clear">',
        destroy: true
    });
    loaderClose();
    $('#datatableRecepcion').on('draw.dt', function () {
        loaderClose();
    });
}

function visualizarRecepcion(id, movimientoId) {
    loaderShow();
    documentoTipoActivo = documento_tipo1;
    $("#documentoId").val(id);
    ax.setAccion("visualizarDetalle");
    ax.addParamTmp("id", id);
    ax.addParamTmp("movimientoId", movimientoId);
    ax.consumir();
}

function imprimirPdfRecepción(id, movimientoId) {
    const link = document.createElement('a');
    link.href = URL_BASE + "vistas/com/almacenes/recepcion_pdf.php?id=" + id;
    link.target = '_blank';
    link.click();
}

function agregardistribucion(unidad_minera_id, inicial, count_fila, count_distribucion) {
    var indice = inicial == 0 ? 0 : parseInt($("#count_" + count_fila + "_" + count_distribucion + "_" + unidad_minera_id).val()) + 1;
    var cuerpo = inicial == 0 ? "" : "<br id='br_" + count_fila + "_" + count_distribucion + "_" + indice + "_" + unidad_minera_id + "'>";
    var disabled_ = inicial == 0 ? "disabled" : "";
    var tipo = select2.obtenerValor("cbo_tipo_" + count_fila + "_" + count_distribucion + "_" + unidad_minera_id);
    var class_ = isEmpty(tipo) ? "hidden" : (tipo == 1 ? "hidden" : "");

    cuerpo += "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\" style=\"display: flex; align-items: center; gap: 8px;\" id='div_" + count_fila + "_" + count_distribucion + "_" + indice + "_" + unidad_minera_id + "'>";

    cuerpo += "<input type=\"text\" oninput=\"this.value = this.value.replace(/[^0-9/,]/g, '')\"  id=\"cantidad_recepcion_a_" + count_fila + "_" + count_distribucion + "_" + indice + "_" + unidad_minera_id + "\" name=\"cantidad_recepcion_b_" + count_fila + "_" + count_distribucion + "_" + indice + "_" + unidad_minera_id + "\" class=\"form-control " + class_ + "\" style=\"text-align: right;\" value='1' " + disabled_ + "/> X";
    cuerpo += "<label id='paq_" + count_fila + "_" + count_distribucion + "_" + indice + "_" + unidad_minera_id + "' class='" + class_ + "'>paq.</label><input type=\"text\" oninput=\"this.value = this.value.replace(/[^0-9/,]/g, '')\"  id=\"cantidad_recepcion_b_" + count_fila + "_" + count_distribucion + "_" + indice + "_" + unidad_minera_id + "\" name=\"cantidad_recepcion_b_" + count_fila + "_" + count_distribucion + "_" + indice + "_" + unidad_minera_id + "\" class=\"form-control\" style=\"text-align: right;\" />";
    if (inicial == 0) {
        cuerpo += "<input type='hidden' id='count_" + count_fila + "_" + count_distribucion + "_" + unidad_minera_id + "' value='0' />";
        cuerpo += "<a href='#' onclick='return false;' style='pointer-events: none; color: gray; text-decoration: none;display:none;' id=\"chk_" + count_fila + "_" + count_distribucion + "_0_" + unidad_minera_id + "\" name=\"chk_" + count_fila + "_" + count_distribucion + "_0_" + unidad_minera_id + "\"><i class='fa fa-plus' style='color:blue;' title='Agregar distribución'></i></a>&nbsp;";
    } else {
        cuerpo += "<a href='#' onclick='eliminardistribucion(" + count_fila + "," + count_distribucion + "," + indice + "," + unidad_minera_id + ")'><i class='fa fa-trash' style='color:red;' title='ver traking'></i></a>&nbsp;";
    }
    cuerpo += "</div>";
    $("#count_" + count_fila + "_" + count_distribucion + "_" + unidad_minera_id).val(indice);
    if (inicial == 0) {
        return cuerpo;
    } else {
        $("#td_" + count_fila + "_" + count_distribucion + "_" + unidad_minera_id).append(cuerpo);
    }
}

function eliminardistribucion(count_fila, count_distribucion, indice, unidad_mineraId) {
    $("#div_" + count_fila + "_" + count_distribucion + "_" + indice + "_" + unidad_mineraId).remove();
    $("#br_" + count_fila + "_" + count_distribucion + "_" + indice + "_" + unidad_mineraId).remove();
    $("#count_" + count_fila + "_" + count_distribucion + "_" + unidad_mineraId).val((parseInt($("#count_" + count_fila + "_" + count_distribucion + "_" + unidad_mineraId).val()) - 1));
}

function generarDistribucionQR(arrayDatoFila, dataFilasSeleccionadas) {
    var arrayDatosGuia = [{ "serie_numeroGuia": $("#serie_numeroGuia").val(), "peso": $("#peso").val(), "volumen": $("#volumen").val(), "pdfGuia": lstDocumentoArchivos }]
    ax.setAccion("generarDistribucionQR");
    ax.addParamTmp("arrayDatoFila", arrayDatoFila);
    ax.addParamTmp("documentoId", $("#documentoId").val());
    ax.addParamTmp("almacenId", $("#cboAlmacen").val());
    ax.addParamTmp("dataFilasSeleccionadas", dataFilasSeleccionadas);
    ax.addParamTmp("empresaId", commonVars.empresa);
    ax.addParamTmp("datosGuia", arrayDatosGuia);
    ax.consumir();
}

function onResponsegenerarDistribucionQR(data) {
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
    $("#modalDetalle").modal('hide');
    $("#serie_numeroGuia").val("");
    $("#peso").val("");
    $("#volumen").val("");
    $("#inputFilePdfGuia").val("");
}

function imprimirPdfQR(id) {
    const link = document.createElement('a');
    link.href = URL_BASE + "vistas/com/almacenes/generar_Qr_recepcion_pdf.php?id=" + id;
    link.target = '_blank';
    link.click();
}

function anularDocumento(id, documentoTipoId) {
    confirmarAnularMovimiento(id, documentoTipoId);
}

function confirmarAnularMovimiento(id, documentoTipoId) {
    bandera_eliminar = false;
    swal({
        title: "Est\xe1s seguro?",
        text: "Anulara un documento, esta anulación no podra revertirse.",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#33b86c",
        confirmButtonText: "Si, anular!",
        cancelButtonColor: '#d33',
        cancelButtonText: "No, cancelar !",
        closeOnConfirm: true,
        closeOnCancel: false
    }, function (isConfirm) {

        if (isConfirm) {
            anular(id, documentoTipoId);
        } else {
            if (bandera_eliminar == false) {
                swal("Cancelado", "La anulaci\xf3n fue cancelada", "error");
            }
        }
    });
}

function anular(id, documentoTipoId) {
    loaderShow();
    ax.setAccion("anular");
    ax.addParamTmp("id", id);
    ax.addParamTmp("documentoTipoId", documentoTipoId);
    ax.consumir();
}
function onResponseAnular(data) {
    swal("Anulado!", "Documento anulado correctamente.", "success");
    bandera_eliminar = true;
    buscarRecepcion();
}
