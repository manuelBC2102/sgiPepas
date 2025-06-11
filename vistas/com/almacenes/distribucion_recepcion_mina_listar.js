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
        var bandera_copia = true;
        if (filasSeleccionadas.length <= 0) {
            $.Notification.autoHideNotify('warning', 'top right', 'Validación', " Seleccione uno o más paquetes, para realizar el proceso");
            bandera_copia = false;
            loaderClose();
            return false;
        }
        cuerpo = '';
        loaderShow("#modalDetalleRecepcionDespacho");
        $("#modalDetalleRecepcionDespacho").modal('show');
        filasSeleccionadas.forEach(function (detalleItem, idx) {
            onResponseObtenerPaqueteDetalleXPaqueteId(detalleItem, idx);
        });

        filasSeleccionadas.forEach(function (detalleItem, idx) {
            select2.cargar("cboOrganizador_" + idx + "_0", dataOrganizadoresHijos, "id", ["codigo", "descripcion"]);
            $("#cboOrganizador_" + idx + "_0").select2({ width: '100%' });
        });
        dataFilasSeleccionadas = filasSeleccionadas;
    });

    $('#btn_generarDistribucionDespacho').click(function () {
        loaderClose('#modalDetalleRecepcionDespacho');
        loaderShow();
        var bandera_generar = true;
        var distribucionArray = [];
        var documentoArray = [];

        dataFilasSeleccionadas.forEach(function (detalleItem, idx) {
            var cantidad_total = 0;
            var count = $("#count_fila_distribucion_" + idx).val();

            for (let index1 = 0; index1 < (parseInt(count) + 1); index1++) {
                var unidad_b = ($("#cantidad_recepcion_b_" + idx + "_" + index1).val());
                cantidad_total += parseFloat(unidad_b);

                if (unidad_b == 0) {
                    mostrarAdvertencia("Ingrese cantidad en distribución");
                    bandera_generar = false;
                    loaderClose();
                    return false;
                }

                var organizador_destino_id = select2.obtenerValor("cboOrganizador_" + idx + "_" + index1);
                distribucionArray.push({ "paquete_id": detalleItem.paquete_id, "organizador_id": organizador_destino_id, "bien_id": detalleItem.bien_id, "cantidad_recepcion": unidad_b, "movimiento_bien_id": detalleItem.bien_id, "doc_tipo_id": detalleItem.doc_tipo_id, "unidad_medida_id": 15 });
            }

            if (cantidad_total != detalleItem.cantidad) {
                mostrarAdvertencia("La suma de la distribución no es igual a la cantidad para el correlativo:"+ detalleItem.paquete_id );
                $("#tdCantidad_" + idx).attr('style', 'background: #ff3f5b;color: white;');
                bandera_generar = false;
                loaderClose();
                return false;
            }
            $("#tdCantidad_" + idx).attr('style', 'background: white;color: #797979;');
            documentoArray.push(detalleItem.documento_id);
            dataFilasSeleccionadas[idx]['cantidad_recepcion'] = detalleItem.cantidad;
            dataFilasSeleccionadas[idx]['unidad_medida_id'] = 15;
        });

        if (bandera_generar) {
            swal(
                {
                    title: "¿Desea continuar?",
                    text: "Al generar la distribución no se podrá revertir.",
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
                        generarDistribucionRecepcionMina([distribucionArray, dataFilasSeleccionadas], unicos[0]);
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
            case 'generarDistribucionRecepcionMina':
                $("#modalDetalleRecepcionDespacho").modal('hide');
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
    obtenerOrganizadoresHijos(data.almacenes[0]['id']);

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
    var serie = $("#txtSerie").val();
    var numero = $("#txtNumero").val();

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
    ax.setAccion("obtenerPaqueteRecepcionMina");
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
        cuerpo += "<td align='center' style='align-content: center;'>" + data.bien_codigo_descripcion + "</td>";
        cuerpo += "<td style='align-content: center;'>" + data.organizador_actual_descripcion + "</td>";
        cuerpo += "<td id='tdCantidad_" + idx + "' align='center' style='align-content: center;'>" + devolverDosDecimales(data.cantidad) + "</td>";
        cuerpo += "<td id='td_" + idx + "' ><input type='hidden' id='count_fila_distribucion_" + idx + "' name='count_fila_distribucion_" + idx + "' value='0' />";
        var cuerpo_distribucion = agregardistribucion(0, data.cantidad, idx);
        cuerpo += cuerpo_distribucion;
        cuerpo += "</td>";
        cuerpo += "</tr>";
        $('#dtmodalDetallePaquete tbody').append(cuerpo);
        loaderClose();
    }
}

function agregardistribucion(inicial, cantidad, count_fila) {
    var indice = inicial == 0 ? 0 : parseInt($("#count_fila_distribucion_" + count_fila).val()) + 1;
    var cuerpoDistribucion = indice == 0 ? "" : "<br id='br_" + count_fila + "_" + indice + "'>";
    // var disabled_ = inicial == 0 ? "disabled" : "";
    // var tipo = select2.obtenerValor("cbo_tipo_" + count_fila + "_" + count_distribucion + "_" + unidad_minera_id);
    // var class_ = isEmpty(tipo) ? "hidden" : (tipo == 1 ? "hidden" : "");

    cuerpoDistribucion += "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\" style=\"display: flex; align-items: center; gap: 8px;\" id='div_" + count_fila + "_" + indice + "'>";

    cuerpoDistribucion += "<select id='cboOrganizador_" + count_fila + "_" + indice + "' name='cboOrganizador_" + count_fila + "_" + indice + "' class='select2'></select>";
    cuerpoDistribucion += "<input type=\"text\" oninput=\"this.value = this.value.replace(/[^0-9/,]/g, '')\"  id=\"cantidad_recepcion_b_" + count_fila + "_" + indice + "\" name=\"cantidad_recepcion_b_" + count_fila + "_" + indice + "\" class=\"form-control\" style=\"text-align: right;\" />";
    if (inicial == 0) {
        var style_ = parseInt(cantidad) == 0 ? "pointer-events: none; color: gray; text-decoration: none;display:none;" : "";
        cuerpoDistribucion += "<a href='#' onclick='agregardistribucion(" + (indice + 1) + ",0 ," + count_fila + ");' style='" + style_ + "' id=\"chk_" + count_fila + "_" + indice + "\" name=\"chk_" + count_fila + "_" + indice + "\"><i class='fa fa-plus' style='color:blue;' title='Agregar distribución'></i></a>&nbsp;";
    } else {
        cuerpoDistribucion += "<a href='#' onclick='eliminardistribucion(" + count_fila + "," + indice + ")' id=\"eliminar_" + count_fila + "_" + indice + "\" name=\"eliminar_" + count_fila + "_" + indice + "\"><i class='fa fa-trash' style='color:red;' id=title='Eliminar'></i></a>&nbsp;";
    }
    cuerpoDistribucion += "</div>";
    $("#count_fila_distribucion_" + count_fila).val(indice);
    if (inicial == 0) {
        return cuerpoDistribucion;
    } else {
        $("#td_" + count_fila).append(cuerpoDistribucion);
        select2.cargar("cboOrganizador_" + count_fila + "_" + indice, dataOrganizadoresHijos, "id", ["codigo", "descripcion"]);
        $("#cboOrganizador_" + count_fila + "_" + indice).select2({ width: '100%' });
        $("#eliminar_" + count_fila + "_" + (indice - 1)).attr('style', 'display:none;');
    }

}

function obtenerOrganizadoresHijos(id) {
    ax.setAccion("getDataOrganizadoresHijos");
    ax.addParamTmp("almacenId", id);
    ax.consumir();
}

function eliminardistribucion(count_fila, indice) {
    $("#div_" + count_fila + "_" + indice).remove();
    $("#br_" + count_fila + "_" + indice).remove();
    $("#count_fila_distribucion_" + count_fila).val((parseInt($("#count_fila_distribucion_" + count_fila).val()) - 1));
    $("#eliminar_" + count_fila + "_" + (indice - 1)).attr('style', '');
}

function generarDistribucionRecepcionMina(distribucionArray, documentoId) {
    ax.setAccion("generarDistribucionRecepcionMina");
    ax.addParamTmp("documentoId", documentoId);
    ax.addParamTmp("dataFilasSeleccionadas", distribucionArray);
    ax.consumir();
}