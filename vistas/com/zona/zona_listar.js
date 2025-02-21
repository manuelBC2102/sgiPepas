
$(document).ready(function () {
    loaderClose();
    ax.setSuccess("successZona");
    listarZonas();
});

function successZona(response) {
    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'listarZona':
                onResponseAjaxpGetDataGridZona(response.data);
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

            case 'actualizarBotonEstadoZona':
                // $.Notification.autoHideNotify('success', 'top-right', '&Eacute;xito', '<i class="fa fa-map-marker">  Estado actualizado </i> ');
                
                if(response.data['0'].vout_exito == 1){
                    listarZonas();
                    mostrarOk(response.data['0'].vout_mensaje);
                }else{
                    mostrarAdvertencia(response.data['0'].vout_mensaje);
                }
                break;

        }
    }
}


function cambiarEstado(id, estado) {
    debugger
    ax.setAccion("actualizarBotonEstadoZona");
    ax.addParamTmp("id", id);
    ax.addParamTmp("estado", estado);
    ax.consumir();
}
function cargarDivGetZona(id) {
    cargarDivIndex("#window", "vistas/com/zona/zona_form.php?id=" + id + "&" + "tipo=" + 1, 354, "");
}

function listarZonas() {
    ax.setAccion("listarZona");
    ax.consumir();
}

function onResponseAjaxpGetDataGridZona(data) {
    $("#dataList").empty();
    var cuerpo_total = '';
    var cuerpo = '';
    var cabeza = '<table id="datatable" class="table table-responsive table-striped table-bordered"><thead>' +
        " <tr>" +
        "<th style='text-align:start;'>Código</th>" +
        "<th style='text-align:start;'>Nombre</th>" +
        "<th style='text-align:center;'>Estado</th>" +

        "<th style='text-align:center;' width=100px>Acciones</th>" +
        "</tr>" +
        "</thead>";
        debugger;
        if (!isEmpty(data)) {
            let iconoEstado = [{estado_actualizar: 1, color: "#cb2a2a", icono: "ion-flash-off"}, {estado_actualizar: 0, color: "#5cb85c", icono: "ion-checkmark-circled"}];
        
            $.each(data, function (index, item) {
              if (typeof item.estado !== 'undefined') {
                const estado = parseInt(item.estado) || 0; // Convierte a entero
                if (iconoEstado.length > estado) { // Verifica longitud del arreglo
                  cuerpo = "<tr>" +
                    "<td style='text-align:start;'>" + item.codigo + "</td>" +
        
                    "<td style='text-align:start;'>" + item.nombre + "</td>" +
                    "<td style='text-align:center;'><a href='#' onclick = 'cambiarEstado(" + item.id + "," + iconoEstado[estado]['estado_actualizar'] + ")' ><b><i id='" + item.id + "' class='" + iconoEstado[estado]['icono'] + "' style='color:" + iconoEstado[estado]['color'] + ";'></i><b></a></td>" +
                    "</td>" +
                    "<td style='text-align:center;'>" +
                    "<a href='#' onclick='cargarDivGetZona(" + item.id + ")'><b><i class='fa fa-edit' style='color:#E8BA2F;'></i><b></a>&nbsp;\n" +
                    "<a href='#' onclick='confirmarDelete(" + item.id + ", \"" + (item.nombre + " | " + item.codigo) + "\")'><b><i class='fa fa-trash-o' style='color:#cb2a2a;'></i><b></a>" +
                    "</td>" +
                    "</tr>";
                  cuerpo_total = cuerpo_total + cuerpo;
                } else {
                }
              } else {
                console.error("Error: Propiedad 'estado' no encontrada en el objeto", item); // Mensaje de error
              }
            });
          }
    var pie = '</table>';
    var html = cabeza + cuerpo_total + pie;
    $("#dataList").append(html);
}

function confirmarDelete(id, codigo) {
    swal({
        title: "Est\xe1s seguro?",
        text: "Eliminarás la zona " +codigo  + "",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#33b86c",
        confirmButtonText: "Si,eliminar!",
        cancelButtonColor: '#d33',
        cancelButtonText: "No,cancelar !",
        closeOnCancel: false
    }, function (isConfirm) {

        if (isConfirm) {
            cambiarEstado(id, 2);
        } else {
            swal("Cancelado", "La eliminaci\xf3n fue cancelada", "error");
        }
    });
}