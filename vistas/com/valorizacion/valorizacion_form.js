
$(document).ready(function () {
    loaderClose();
    ax.setSuccess("successZona");
    
    listarSolicitudesDocumentario();
});

function successZona(response) {
    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obtenerLotesConfirmados':
                onResponseAjaxpGetDataGridSolicitud(response.data);
                $('#datatable').dataTable({
                    "scrollX": true,
                    "autoWidth": true,
                    "order": [[0, "desc"]],
                    "language": {
                        "sProcessing": "Procesando...",
                        "sLengthMenu": "Mostrar _MENU_ registros",
                        "sZeroRecords": "No se encontraron resultados",
                        "sEmptyTable": "Ning\xfAn dato disponible en esta tabla",
                        "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
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
                    }
                });
                break;
            case  'subirArchivo':
                loaderClose();
                $.Notification.autoHideNotify('success', 'top-right', '&Eacute;xito', 'Archivo Cargado');
                listarSolicitudesDocumentario();
                break;
            case  'eliminarArchivo':
                    loaderClose();
                    $.Notification.autoHideNotify('success', 'top-right', '&Eacute;xito', 'Archivo Eliminado');
                    listarSolicitudesDocumentario();
                    break;
            case 'actualizarEstadoZona':
                $.Notification.autoHideNotify('success', 'top-right', '&Eacute;xito', 'Zona actualizada');
                listarSolicitudesDocumentario();
                break;
                case 'guardarActualizacionDirimencia':
                $.Notification.autoHideNotify('success', 'top-right', '&Eacute;xito', 'Resultados actualizados');
                listarSolicitudesDocumentario();
                loaderClose();
                break;
                case 'guardarFactura':
                $.Notification.autoHideNotify('success', 'top-right', '&Eacute;xito', 'Valorización generada');
                loaderClose();
                cargarListarPersonaCancelar();
                break;
        }
    }else
    {
        switch (response[PARAM_ACCION_NAME]) {
            case 'guardarActualizacionDirimencia':
                swal("Cancelado", "No se pudo registrar los resultados finales", "error");
                loaderClose();
                break;
                case 'guardarFactura':
                    swal("Cancelado", "No se pudo registrar la valorización", "error");
                    loaderClose();
                    break;
        }
    }
}

function cargarListarPersonaCancelar()
{
    loaderShow(null);
    cargarDiv('#window', 'vistas/com/valorizacion/valorizacion_listar.php');
}
function cambiarEstado(id, estado)
{
    ax.setAccion("actualizarEstadoZona");
    ax.addParamTmp("id", id);
    ax.addParamTmp("estado", estado);
    ax.consumir();
}
function cargarDivGetZona(id) {
    cargarDivIndex("#window", "vistas/com/zona/zona_form.php?id=" + id + "&" + "tipo=" + 1, 350, "");
}

function listarSolicitudesDocumentario() {
    ax.setAccion("obtenerLotesConfirmados");
    ax.consumir();
}

function onResponseAjaxpGetDataGridSolicitud(data) {
    debugger;

    $("#dataList").empty();
    var cuerpo_total = '';
    var cuerpo = '';
    var cabeza = '<table id="datatable" class="table table-responsive table-striped table-bordered"><thead>' +
        " <tr>" +
        "<th style='text-align:center;'></th>" +
        "<th style='text-align:center;'>Minero</th>" +
        "<th style='text-align:center;'>Lote</th>" +
        "<th style='text-align:center;'>Fecha Recepción</th>" +
        "<th style='text-align:center;'>TMH</th>" +
        "<th style='text-align:center;'>% Hum</th>" +
        "<th style='text-align:center;'>TMS</th>" +
        "<th style='text-align:center;'>PIO</th>" +
        "<th style='text-align:center;'>Ley oz/tc</th>" +
        "<th style='text-align:center;'>% Recp</th>" +
        "<th style='text-align:center;'>Maquila</th>" +
        "<th style='text-align:center;'>R/C</th>" +
        "<th style='text-align:center;'>C/A</th>" +
        "<th style='text-align:center;'>US$ Precio TM</th>" +
        "<th style='text-align:center;'>Importe</th>" +
        "<th style='display:none;'></th>" +
        "</tr>" +
        "</thead>";

    if (!isEmpty(data)) {
        let iconoEstado = [
            { estado_actualizar: 1, color: "#cb2a2a", icono: "ion-flash-off" },
            { estado_actualizar: 0, color: "#5cb85c", icono: "ion-checkmark-circled" }
        ];

        $.each(data, function (index, item) {
            debugger;

            // Declarar las variables fuera del bloque condicional
            let archivoRutaFinal = '';
            let archivoIcono2 = '';
            let ley_final = '';

            // Asignar valores a las variables según las condiciones
            if (item.archivo_resultados_final == null) {
                archivoRutaFinal = '';
                archivoIcono2 = '';
                ley_final = 'Pendiente';  // Por defecto si ley_final es nulo
            } else {
                archivoRutaFinal = "vistas/com/dirimencia/resultados/" + item.archivo_resultados_final;
                archivoIcono2 = "<a href='" + archivoRutaFinal + "' target='_blank'>" +
                                "<i class='ion-document' style='font-size: 20px; color: green;'></i>" +
                                "</a>";
                ley_final = item.ley_final || 'Pendiente'; // Asigna ley_final si existe, si no, 'Pendiente'
            }

            let archivoRuta = "vistas/com/dirimencia/resultados/" + item.archivo_resultados;
            let archivoIcono = "<a href='" + archivoRuta + "' target='_blank'>" +
                                "<i class='ion-document' style='font-size: 20px; color: #007bff;'></i>" +
                               "</a>";

            let accion = '';
            if (item.aprobacion_dirimencia == null) {
                accion = '<a onclick="abrirModalDirimencia(' + item.id + ', \'' + item.solicitud_retiro_detalle_id + '\')"><b><i class="fa fa-check-square-o" style="font-size: 17px; color:blue;"></i><b></a>';
            }

            cuerpo = "<tr>" +
            "<td style='text-align:center;'>" + 
                "<input type='checkbox' class='loteCheckbox' data-id='" + item.id + "'>" +
            "</td>" +
            "<td style='text-align:center;'>" + item.sociedad + "</td>" +
            "<td style='text-align:center;'>" + item.lote + "</td>" +
            "<td style='text-align:center;'>" + item.fecha_recepcion + "</td>" +
            "<td style='text-align:center;'>" + item.tmh + "</td>" +
            "<td style='text-align:center;'>" + item.porcentagua + "</td>" +
            "<td style='text-align:center;'>" + item.tms + "</td>" +
            "<td style='text-align:center;'>" + item.precio_internacional + "</td>" +
            "<td style='text-align:center;'>" + item.ley + "</td>" +
            "<td style='text-align:center;'>" + item.porcentaje_recuperacion + "</td>" +
            "<td style='text-align:center;'>" + item.maquila + "</td>" +
            "<td style='text-align:center;'>" + item.descuento_internacional + "</td>" +
            "<td style='text-align:center;'>" + item.penalidad + "</td>" +
            "<td style='text-align:center;'>" + (Math.round(item.total / item.tms * 1000) / 1000).toFixed(3) + "</td>" +
            "<td style='text-align:center;'>" + (Math.round(item.total * 1000) / 1000).toFixed(3) + "</td>" +
            "<td style='display:none;'>"+ item.sociedad_id + "</td>" +
        "</tr>";

            cuerpo_total += cuerpo;
        });
    }

    var pie = '</table>';
    var html = cabeza + cuerpo_total + pie;
    $("#dataList").append(html);
}


function abrirModalDirimencia(id,solicitud_retiro_detalle_id) {
    debugger;
    // Limpiar los campos select2
    $('#cboTipoDocumento').val(null).trigger('change');
    $('#cboUsuario').val(null).trigger('change');
    $('#cboNivel').val(null).trigger('change');
    $('#cboZona').val(null).trigger('change');
    $('#cboPlanta').val(null).trigger('change');

    // Limpiar los campos de texto e imagen
    $('#secretImg').val('');
    $('#txtComentario').val('');
    $('#file').val('');
    $('#upload-file-info').val('');
    $("#upload-file-info").html('Ningún resultado seleccionada');
    $("#txtaprobacion").val(id);
    $("#txtloteId").val(solicitud_retiro_detalle_id);
    $('#registroModal').modal('show');
   

}

function guardarAprobador() {
    debugger;
    loaderShow();
    $('#registroModal').modal('hide');
    var file = $('#secretImg').val();
    var ley = $('#txtLey').val();
    var id = $('#txtaprobacion').val();
    var lote = $('#txtloteId').val();
    ax.setAccion("guardarActualizacionDirimencia");
    ax.addParamTmp("id", id);
    ax.addParamTmp("file", file);
    ax.addParamTmp("ley", ley);
    ax.addParamTmp("lote", lote);
    ax.consumir();
}

$(document).on('change', '.loteCheckbox', function() {
    var checkboxesSeleccionados = $('.loteCheckbox:checked');
    
    // Verificar si al menos un checkbox está seleccionado y si todos pertenecen a la misma sociedad
    var sociedadUnica = null;
    var lotesValidos = true;

    checkboxesSeleccionados.each(function() {
        var row = $(this).closest('tr');
        var sociedad = row.find('td:eq(1)').text();

        if (sociedadUnica === null) {
            sociedadUnica = sociedad;
        } else if (sociedadUnica !== sociedad) {
            lotesValidos = false;
        }
    });

    if (lotesValidos && checkboxesSeleccionados.length > 0) {
        $('#btnAbrirModal').show();  // Mostrar el botón si los lotes son válidos
    } else {
        $('#btnAbrirModal').hide();  // Ocultar el botón si los lotes no son válidos

        if (checkboxesSeleccionados.length > 0) {
            // Notificar al usuario que no puede seleccionar lotes de diferentes sociedades
            swal("Error", "No se pueden seleccionar lotes de diferentes sociedades para una misma factura.", "error");
        }
    }
});


function abrirModalSeleccionados() {
    var totalSeleccionado = 0;
    var detalles = [];
    var sociedadUnica = null; // Para verificar si todos los lotes pertenecen a la misma sociedad

    // Obtener los lotes seleccionados
    $('.loteCheckbox:checked').each(function() {
        var loteId = $(this).data('id');
        var row = $(this).closest('tr');
        
        // Acceder a las celdas correspondientes de cada fila
        var sociedad = row.find('td:eq(1)').text(); // La celda de la sociedad
        var lote = row.find('td:eq(2)').text(); // La celda del lote
        var totalLote = parseFloat(row.find('td:eq(14)').text()); // La celda del total
        var sociedad_id = row.find('td:eq(15)').text(); 
        // Verificar si es la primera sociedad seleccionada
        if (sociedadUnica === null) {
            sociedadUnica = sociedad;
        } else if (sociedadUnica !== sociedad) {
            // Si hay sociedades diferentes, mostramos un mensaje y desmarcamos todos los checkboxes
            swal("Error", "No se pueden seleccionar lotes de diferentes sociedades.", "error");
            $('.loteCheckbox').prop('checked', false); // Desmarcar todos los checkboxes
            return; // Salir de la función
        }

        debugger;
        // Añadir los datos de este lote al array de detalles
        detalles.push({
            id: loteId,
            sociedad: sociedad,
            lote: lote,
            totalLote: totalLote
        });

        totalSeleccionado += totalLote;
        sociedad_id=sociedad_id;
        $('#minero').val(sociedad_id);
    });

    // Llenar la tabla de detalles en el modal
    var detalleHTML = '';
    detalles.forEach(function(detalle) {
        detalleHTML += "<tr><td>" + detalle.sociedad + "</td><td>" + detalle.lote + "</td><td>" + detalle.totalLote.toFixed(3) + "</td></tr>";
    });

    // Calcular el subtotal, IGV, detracción y total
    var subtotal = totalSeleccionado;
    var igv = subtotal * 0.18; // IGV es 18%
    var totalFactura = subtotal * 1.18; // Total factura con IGV
    var detraccion = totalFactura * 0.1; // Detracción 10%
    var netoPagar = totalFactura - detraccion; // Neto a pagar después de detracción
    
    // Mostrar los cálculos en el modal
    $('#detalleLotes').html(detalleHTML);
    $('#subtotal').val(subtotal.toFixed(3));
    $('#igv').val(igv.toFixed(3));
    $('#totalFactura').val(totalFactura.toFixed(3));
    $('#detraccion').val(detraccion.toFixed(3));
    $('#netoPago').val(netoPagar.toFixed(3));
   
    // Abrir el modal
    $('#modalFactura').modal('show');
}


function guardarFactura() {
    loaderShow();
    var serie = $('#serieFactura').val();
    var correlativo = $('#correlativoFactura').val();

    var subtotal = $('#subtotal').val();
    var igv = $('#igv').val();
    var totalFactura = $('#totalFactura').val();
    var detraccion = $('#detraccion').val();
    var netoPago = $('#netoPago').val();
    var minero = $('#minero').val();
    var lotesSeleccionados = [];
    
    $('.loteCheckbox:checked').each(function() {
        debugger;
        
        var loteId = $(this).data('id');
        lotesSeleccionados.push(loteId);
    });

    // Enviar al backend
    ax.setAccion("guardarFactura");
    ax.addParamTmp("serie", serie);
    ax.addParamTmp("correlativo", correlativo);
    ax.addParamTmp("subtotal", subtotal);
    ax.addParamTmp("igv", igv);
    ax.addParamTmp("totalFactura", totalFactura);
    ax.addParamTmp("detraccion", detraccion);
    ax.addParamTmp("netoPago", netoPago);
    ax.addParamTmp("lotes", JSON.stringify(lotesSeleccionados));
    ax.addParamTmp("minero", minero);
    ax.consumir();
    
    $('#modalFactura').modal('hide');
}
