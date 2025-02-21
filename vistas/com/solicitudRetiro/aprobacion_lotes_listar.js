
$(document).ready(function () {
    loaderClose();
    ax.setSuccess("successZona");
    
    listarSolicitudesDocumentario();
});

function successZona(response) {
    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'listarSolicitudesPorAprobacionPesaje':
                onResponseAjaxpGetDataGridSolicitud(response.data);
                $('#datatable').dataTable({
                    destroy: true,
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
    ax.setAccion("listarSolicitudesPorAprobacionPesaje");
    ax.consumir();
}

function onResponseAjaxpGetDataGridSolicitud(data) {
    debugger;
    $("#dataList").empty();
    var cuerpo_total = '';
    var cuerpo = '';
    var cabeza = '<table id="datatable" class="table table-responsive table-striped table-bordered"><thead>' +
            " <tr>" +
            "<th style='text-align:center;'>#</th>" +
            "<th style='text-align:center;'>Fecha Entrega</th>" +
            "<th style='text-align:center;'>Zona</th>" +
            "<th style='text-align:center;'>Vehiculo</th>" +
            "<th style='text-align:center;'>REINFO</th>" +
            "<th style='text-align:center;'>Planta</th>" +
            "<th style='text-align:center;'>REINFO</th>" +
            "</tr>" +
            "</thead>";
    if (!isEmpty(data)) {
        let iconoEstado = [{estado_actualizar: 1, color: "#cb2a2a", icono: "ion-flash-off"}, {estado_actualizar: 0, color: "#5cb85c", icono: "ion-checkmark-circled"}];

        $.each(data, function (index, item) {
            if(item.estado==1){
                estado='Activo';
            }
            else{
                estado='Inactivo';
            }
            cuerpo = "<tr>" +
                    "<td style='text-align:center;'>" + item.id + "</td>" +
                    "<td style='text-align:center;'>" + item.fecha_entrega + "</td>" +
                    "<td style='text-align:center;'>" + item.zona + "</td>" +
                    "<td style='text-align:center;'>" + item.vehiculo + "</td>" +
                    "<td style='text-align:center;'>" + item.sociedad + "</td>" +
                    "<td style='text-align:center;'>" + item.planta + "</td>" +
                    "<td style='text-align:center;'>" + item.reinfo + "</td>" +
                    "</tr>";
            cuerpo_total = cuerpo_total + cuerpo;
        });
    }
    var pie = '</table>';
    var html = cabeza + cuerpo_total + pie;
    $("#dataList").append(html);
}

