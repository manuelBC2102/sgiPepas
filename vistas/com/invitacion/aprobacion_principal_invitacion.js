
$(document).ready(function () {
    loaderClose();
    ax.setSuccess("successZona");
     listarInvitaciones();
      modificarAnchoTabla('datatable');
});

function successZona(response) {
    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'listarInvitacionAsociativa':
                onResponseAjaxpGetDataGridZona(response.data);
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

            case 'actualizarBotonEstadoZona':
                // $.Notification.autoHideNotify('success', 'top-right', '&Eacute;xito', '<i class="fa fa-map-marker">  Estado actualizado </i> ');
                
                if(response.data['0'].vout_exito == 1){
                    listarInvitaciones();
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

function listarInvitaciones() {
    ax.setAccion("listarInvitacionAsociativa");
    ax.consumir();
}

function onResponseAjaxpGetDataGridZona(data) {
    $("#dataList").empty();
    var cuerpo_total = '';
    var cuerpo = '';
    var cabeza = '<table id="datatable" class="table table-striped table-bordered"><thead>' +
        " <tr>" +
        "<th style='text-align:start;'>Fecha registro</th>" +
        "<th style='text-align:start;'>RUC</th>" +
        "<th style='text-align:center;'>REINFO</th>" +
        "<th style='text-align:center;'>Ubigeo</th>" +
      
        "<th style='text-align:center;'>Ubicación</th>" +
        "<th style='text-align:center;'>Nivel</th>" +
        "<th style='text-align:center;'>Usuario</th>" +
        "<th style='text-align:center;' >Acciones</th>" +
        "</tr>" +
        "</thead>";
        debugger;
        if (!isEmpty(data)) {
            let iconoEstado = [{estado_actualizar: 1, color: "#cb2a2a", icono: "ion-flash-off"}, {estado_actualizar: 0, color: "#5cb85c", icono: "ion-checkmark-circled"}];
        
            $.each(data, function (index, item) {
 
           

                  cuerpo = "<tr>" +
                    "<td style='text-align:start;'>" + item.fecha_creacion + "</td>" +
        
                    "<td style='text-align:start;'>" + item.RUC + "</td>" +
                    "<td style='text-align:start;'>" + item.REINFO + "</td>" +
                    "<td style='text-align:start;'>" + item.codigo + "</td>" +
                
                    "<td style='text-align:start;'>" + item.ubicacion + "</td>" +
                    "<td style='text-align:start;'>" + item.nivel + "</td>" +
                    "<td style='text-align:start;'>" + item.usuario + "</td>" +
                    "<td style='text-align:center;'>" +
                  
                    "<a  href='envio_notificacion8.php?token="+item.token+"'><b><i class='fa fa-file-pdf-o' style='color:#0366b0;'></i><b></a>\n" +
                   
                    "</td>" +
                    "</tr>";
                  cuerpo_total = cuerpo_total + cuerpo;
    
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

window.addEventListener('pageshow', function(event) {
    if (sessionStorage.getItem('needsReload')) {
        sessionStorage.removeItem('needsReload'); // Remover el indicador
        window.location.reload(); // Recargar la página
    }
});
