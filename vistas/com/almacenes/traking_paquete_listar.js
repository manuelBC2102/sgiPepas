$(document).ready(function () {
    criterioBusquedaDocumentos = [];
    cargarComponetentes();
    ax.setSuccess("onResponseAlmacenar");
    obtenerConfiguracionInicialListadoDocumentos();
    cambiarAnchoBusquedaDesplegable();
    select2.iniciarElemento("cboAlmacen");

    $('#btn_generarDistribucionQR').click(function () {
        loaderClose('#modalDetalle');
        loaderShow();
        var bandera_generar = true;
        var arrayDatoFila = [];

        arrayDataFiltradaPaquete.forEach(function (detalleItem, idx) {
            var arrayDistribucionQR = [];
            var cantidad_total = 0;
            var cantidad_a_b = 0;
            var count = $("#count_" + idx + "_" + detalleItem.unidad_minera_id).val();
            var distribucionArray = [];

            for (let index1 = 0; index1 < (parseInt(count) + 1); index1++) {
                var unidad_a = ($("#cantidad_recepcion_a_" + idx + "_" + index1 + "_" + detalleItem.unidad_minera_id).val());
                var unidad_b = ($("#cantidad_recepcion_b_" + idx + "_" + index1 + "_" + detalleItem.unidad_minera_id).val());
                cantidad_a_b += (unidad_a * unidad_b);
                if (cantidad_a_b == 0) {
                    mostrarAdvertencia("Ingrese cantidad en distribución");
                    bandera_generar = false;
                    loaderClose();
                    return false;
                }

                var tipo = select2.obtenerValor("cbo_tipo_" + idx + "_" + detalleItem.unidad_minera_id);
                distribucionArray.push({ "unidad_minera_id": detalleItem.unidad_minera_id, "indice1": unidad_a, "indice2": unidad_b, "tipo": tipo, "organizador_id": detalleItem.organizador_destino_id, "organizador_destino_id": detalleItem.organizador_destino_id });
            }

            if (cantidad_a_b > detalleItem.cantidad) {
                mostrarAdvertencia("La suma de la distribución es mayor a la asiganda");
                bandera_generar = false;
                loaderClose();
                return false;
            }
            arrayDistribucionQR.push({ "unidad_minera_id": detalleItem.unidad_minera_id, "distribucion": distribucionArray, "cantidad": detalleItem.cantidad });
            cantidad_total += cantidad_a_b;
            arrayDatoFila.push({ "paquete_id": detalleItem.paquete_id, "bien_id": detalleItem.bien_id, "organizador_destino_id": detalleItem.organizador_destino_id, "organizador_id": detalleItem.organizador_id, "movimiento_bien_id": detalleItem.movimiento_bien_id, "cantidad": detalleItem.cantidad_recepcion, "arrayDistribucionQR": arrayDistribucionQR });

            if (cantidad_total != detalleItem.cantidad) {
                mostrarAdvertencia("La suma de la distribución no es igual a la cantidad");
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
                        editarDistribucionQR(arrayDatoFila);
                    } else {
                        loaderClose();
                    }
                }
            );
        }
    });
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
            case 'editarDistribucionQR':
                onResponseEditarDistribucionQR(response.data);
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
    $("#div_paqueteEdit").hide();
    $("#div_detalleTraking").hide();
    loaderShow("#modalDetalleAlmacenado");
    $("#modalDetalleAlmacenado").modal('show');
    ax.setAccion("obtenerPaqueteTrakingDetalleXBienId");
    ax.addParamTmp("bienId", bienId);
    ax.addParamTmp("almacen", select2.obtenerValor("cboAlmacen"));
    ax.consumir();
}

var arrayDataPaquete = [];
function onResponseObtenerPaqueteTrakingDetalleXBienId(data) {
    $('#dtmodalDetallePaquete tbody').empty();
    $('#dtmodalDetalleTraking tbody').empty();
    var cont = 0;
    if (!isEmpty(data)) {
        arrayDataPaquete = data;
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
                        acciones += "<a href='#' onclick='verMovimientoPaquete(" + row.paquete_id + ")'><i class='fa fa-cube' style='color:orange;' title='Ver traking'></i></a>&nbsp;&nbsp;";
                        if (row.organizador_id != 83) {
                            acciones += "<a href='#' onclick='editarMovimientoPaquete(" + row.paquete_id + ")'><i class='fa fa-edit' style='color:blue;' title='Editar paquete'></i></a>&nbsp;";
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
    }
}

function verMovimientoPaquete(id) {
    $("#btn_generarDistribucionQR").addClass("hidden");
    loaderShow("#modalDetalleAlmacenado");
    ax.setAccion("obtenerMovimientoPaqueteTraking");
    ax.addParamTmp("id", id);
    ax.consumir();
}

function onResponseObtenerMovimientoPaqueteTraking(data) {
    $("#div_paqueteEdit").hide();
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

var arrayDataFiltradaPaquete = [];
function editarMovimientoPaquete(id) {
    $("#div_detalleTraking").hide();
    var cuerpo = "";
    loaderShow("#modalDetalleAlmacenado");
    $("#div_paqueteEdit").show();
    var dataFiltrada = arrayDataPaquete.filter(
        (item) => item.paquete_id == id
    );
    arrayDataFiltradaPaquete = dataFiltrada;
    $('#dtmodalPaquete tbody').empty();

    if (!isEmpty(dataFiltrada)) {
        $("#btn_generarDistribucionQR").removeClass("hidden");
        dataFiltrada.forEach(function (detalleItem, idx) {
            var cantidadFila = Array.isArray(detalleItem.distribucion_unidad_minera)
                ? detalleItem.distribucion_unidad_minera.length
                : 1;
            cuerpo += "<tr> <input type='hidden' id='count_fila_" + idx + "' name='count_fila_" + idx + "' value='" + idx + "'>";
            cuerpo += "<td align='center' rowspan='" + cantidadFila + "'>" + (idx + 1) + "</td>";
            cuerpo += "<td align='left' style='align-content: center;' rowspan='" + cantidadFila + "'>" + detalleItem.bien_codigo_descripcion + "</td>";
            cuerpo += "<td align='right' style='align-content: center;' rowspan='" + cantidadFila + "'>" + devolverDosDecimales(detalleItem.cantidad) + "</td>";

            cuerpo += "<td style='align-content: center;'>";
            cuerpo += "<select id='cbo_tipo_" + idx + "_" + detalleItem.unidad_minera_id + "' name='cbo_tipo_" + idx + "_" + detalleItem.unidad_minera_id + "' class='select2' disabled>";
            cuerpo += "</select>";
            cuerpo += "</td>";

            cuerpo += "<td id='td_" + idx + "_" + detalleItem.unidad_minera_id + "'>";
            var cuerpo_distribucion = agregardistribucion(detalleItem.unidad_minera_id, 0, idx);
            cuerpo += cuerpo_distribucion;
            cuerpo += "</td>";

            // cuerpo += "</tr>";
            // });
            cuerpo += "</tr>";
        });
    }
    $('#dtmodalPaquete tbody').append(cuerpo);
    dataFiltrada.forEach(function (detalleItem, idx) {
        select2.cargar("cbo_tipo_" + idx + "_" + detalleItem.unidad_minera_id, [{ id: 2, descripcion: "Paquetes" }], "id", "descripcion");
        $("#cbo_tipo_" + idx + "_" + detalleItem.unidad_minera_id).select2({ width: '100%' }).on("change", function (e) {
            if (e.val == 1) {
                $("#paq_" + idx + "_0_" + detalleItem.unidad_minera_id).addClass('hidden');
                $("#cantidad_recepcion_a_" + idx + "_0_" + detalleItem.unidad_minera_id).prop('disabled', true);
                $("#cantidad_recepcion_a_" + idx + "_0_" + detalleItem.unidad_minera_id).addClass('hidden');
                var a = (parseInt($("#count_" + idx + "_" + detalleItem.unidad_minera_id).val()) + 1);
                for (let index = 1; index < a; index++) {
                    eliminardistribucion(idx, index, detalleItem.unidad_minera_id);
                }
                $("#chk_" + idx + "_0_" + detalleItem.unidad_minera_id).attr('style', 'color:gray; opacity:0.5; cursor:not-allowed;display:none;');
                $("#chk_" + idx + "_0_" + detalleItem.unidad_minera_id).attr('onclick', '');
                $("#chk_" + idx + "_0_" + detalleItem.unidad_minera_id).hide();

                $("#cantidad_recepcion_a_" + "_0_" + detalleItem.unidad_minera_id).val(1);
            } else {
                var count_ = parseInt($("#count_" + idx + "_" + detalleItem.unidad_minera_id).val()) + 1;
                $("#paq_" + idx + "_0_" + detalleItem.unidad_minera_id).removeClass('hidden');
                $("#cantidad_recepcion_a_" + idx + "_0_" + detalleItem.unidad_minera_id).prop('disabled', false);
                $("#cantidad_recepcion_a_" + idx + "_0_" + detalleItem.unidad_minera_id).removeClass('hidden');
                $("#chk_" + idx + "_0_" + detalleItem.unidad_minera_id).attr('style', 'color: blue;');
                $("#chk_" + idx + "_0_" + detalleItem.unidad_minera_id).show();
                $("#chk_" + idx + "_0_" + detalleItem.unidad_minera_id).attr('onclick', 'agregardistribucion(' + detalleItem.unidad_minera_id + ", " + count_ + "," + idx + ')');
            }
        });
    });
    loaderClose();
}

function agregardistribucion(unidad_minera_id, inicial, count_fila) {
    var indice = inicial == 0 ? 0 : parseInt($("#count_" + count_fila + "_" + unidad_minera_id).val()) + 1;
    var cuerpo = inicial == 0 ? "" : "<br id='br_" + count_fila + "_" + indice + "_" + unidad_minera_id + "'>";

    cuerpo += "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\" style=\"display: flex; align-items: center; gap: 8px;\" id='div_" + count_fila + "_" + indice + "_" + unidad_minera_id + "'>";

    cuerpo += "<input type=\"text\" oninput=\"this.value = this.value.replace(/[^0-9/,]/g, '')\"  id=\"cantidad_recepcion_a_" + count_fila + "_" + indice + "_" + unidad_minera_id + "\" name=\"cantidad_recepcion_b_" + count_fila + "_" + indice + "_" + unidad_minera_id + "\" class=\"form-control\" style=\"text-align: right;\" value='1' /> X";
    cuerpo += "<label id='paq_" + count_fila + "_" + indice + "_" + unidad_minera_id + "'>paq.</label><input type=\"text\" oninput=\"this.value = this.value.replace(/[^0-9/,]/g, '')\"  id=\"cantidad_recepcion_b_" + count_fila + "_" + indice + "_" + unidad_minera_id + "\" name=\"cantidad_recepcion_b_" + count_fila + "_" + indice + "_" + unidad_minera_id + "\" class=\"form-control\" style=\"text-align: right;\" />";
    if (inicial == 0) {
        cuerpo += "<input type='hidden' id='count_" + count_fila + "_" + unidad_minera_id + "' value='0' />";
        cuerpo += "<a href='#' onclick='agregardistribucion(" + unidad_minera_id + "," + (indice + 1) + "," + count_fila + ");' id=\"chk_" + count_fila + "_0_" + unidad_minera_id + "\" name=\"chk_" + count_fila + "_0_" + unidad_minera_id + "\"><i class='fa fa-plus' style='color:blue;' title='Agregar distribución'></i></a>&nbsp;";
    } else {
        cuerpo += "<a href='#' onclick='eliminardistribucion(" + count_fila + "," + indice + "," + unidad_minera_id + ")'><i class='fa fa-trash' style='color:red;' title='ver traking'></i></a>&nbsp;";
    }
    cuerpo += "</div>";
    $("#count_" + count_fila + "_" + unidad_minera_id).val(indice);
    if (inicial == 0) {
        return cuerpo;
    } else {
        $("#td_" + count_fila + "_" + unidad_minera_id).append(cuerpo);
    }
}

function eliminardistribucion(count_fila, indice, unidad_mineraId) {
    $("#div_" + count_fila + "_" + indice + "_" + unidad_mineraId).remove();
    $("#br_" + count_fila + "_" + indice + "_" + unidad_mineraId).remove();
    $("#count_" + count_fila + "_" + unidad_mineraId).val((parseInt($("#count_" + count_fila + "_" + unidad_mineraId).val()) - 1));
}

function editarDistribucionQR(arrayDatoFila) {
    ax.setAccion("editarDistribucionQR");
    ax.addParamTmp("arrayDatoFila", arrayDatoFila);
    ax.consumir();
}

function onResponseEditarDistribucionQR(data) {
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
    $("#btn_generarDistribucionQR").addClass("hidden");
    visualizarDetalleProducto(data.bien_id);
}