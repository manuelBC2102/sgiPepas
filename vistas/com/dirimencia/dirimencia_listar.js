
$(document).ready(function () {
    loaderClose();
    ax.setSuccess("successZona");
    
    listarSolicitudesDocumentario();
});

function successZona(response) {
    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obtenerLotesDirimencia':
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
        }
    }else
    {
        switch (response[PARAM_ACCION_NAME]) {
            case 'guardarActualizacionDirimencia':
                swal("Cancelado", "No se pudo registrar los resultados finales", "error");
                loaderClose();
                break;
        }
    }
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
    ax.setAccion("obtenerLotesDirimencia");
    ax.consumir();
}

function onResponseAjaxpGetDataGridSolicitud(data) {
    ;

    $("#dataList").empty();
    var cuerpo_total = '';
    var cuerpo = '';
    var cabeza = '<table id="datatable" class="table table-responsive table-striped table-bordered"><thead>' +
        " <tr>" +
        "<th style='text-align:center;'>Fecha Dirimencia</th>" +
        "<th style='text-align:center;'>Planta</th>" +
        "<th style='text-align:center;'>Ley planta</th>" +
        "<th style='text-align:center;'>Representante</th>" +
        "<th style='text-align:center;'>Ley</th>" +
        "<th style='text-align:center;'>Análisis</th>" +
        "<th style='text-align:center;'>Estado</th>" +
        "<th style='text-align:center;'>Ley Final</th>" +
        "<th style='text-align:center;'>Análisis Final</th>" +
        "<th style='text-align:center;' width=100px>Acciones</th>" +
        "</tr>" +
        "</thead>";

    if (!isEmpty(data)) {
        let iconoEstado = [
            { estado_actualizar: 1, color: "#cb2a2a", icono: "ion-flash-off" },
            { estado_actualizar: 0, color: "#5cb85c", icono: "ion-checkmark-circled" }
        ];

        $.each(data, function (index, item) {
            ;

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
                    "<td style='text-align:center;'>" + item.fecha_entrega + "</td>" +
                    "<td style='text-align:center;'>" + item.planta + "</td>" +
                    "<td style='text-align:center;'>" + item.ley_antigua + "</td>" +
                    "<td style='text-align:center;'>" + item.sociedad + "</td>" +
                    "<td style='text-align:center;'>" + item.ley_nueva + "</td>" +
                    "<td style='text-align:center;'>" + archivoIcono + "</td>" + // Aquí está el ícono de archivo
                    "<td style='text-align:center;'>" + item.estado + "</td>" +
                    "<td style='text-align:center;'>" + ley_final + "</td>" +
                    "<td style='text-align:center;'>" + archivoIcono2 + "</td>" +
                    "<td style='text-align:center;'>" + accion + "</td>" +
                    "</tr>";

            cuerpo_total += cuerpo;
        });
    }

    var pie = '</table>';
    var html = cabeza + cuerpo_total + pie;
    $("#dataList").append(html);
}


function abrirModalDirimencia(id,solicitud_retiro_detalle_id) {
    ;
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
    ;
    loaderShow();
    $('#registroModal').modal('hide');
    var file = $('#secretImg').val();
    var ley = $('#txtLey').val();
    var monto = $('#txtMonto').val();
    var id = $('#txtaprobacion').val();
    var lote = $('#txtloteId').val();
    ax.setAccion("guardarActualizacionDirimencia");
    ax.addParamTmp("id", id);
    ax.addParamTmp("file", file);
    ax.addParamTmp("ley", ley);
    ax.addParamTmp("monto", monto);
    ax.addParamTmp("lote", lote);
    ax.consumir();
}

