
$(document).ready(function () {
    loaderClose();
    ax.setSuccess("successVehiculo");
    listarVehiculo();
    ;
});

function successVehiculo(response) {
    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'listarVehiculo':
                onResponseAjaxpGetDataGridVehiculo(response.data);
                ;
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

            case 'actualizarEstadoVehiculo':
                $.Notification.autoHideNotify('success', 'top-right', '&Eacute;xito!', '<i class="fa fa-car"></i> Vehículo Eliminado');
                 listarVehiculo();
                break;

        }
    }
}


function cambiarEstado(id, estado)
{
    ax.setAccion("actualizarEstadoVehiculo");
    ax.addParamTmp("id", id);
    ax.addParamTmp("estado", estado);
    ax.consumir();
}
function cargarDivGetVehiculo(id) {
    cargarDivIndex("#window", "vistas/com/vehiculo/vehiculo_form.php?id=" + id + "&" + "tipo=" + 1, 355, "");
}

function  listarVehiculo() {
    ax.setAccion("listarVehiculo");
    ax.consumir();
}

function onResponseAjaxpGetDataGridVehiculo(data) {
    $("#dataList").empty();
    var cuerpo_total = '';
    var cuerpo = '';
    ;
    var cabeza = '<table id="datatable" class="table table-responsive table-striped table-bordered"><thead>' +
            " <tr>" +
            // "<th style='text-align:end;'>#</th>" +
            "<th style='text-align:start;'>Placa</th>" +
            "<th style='text-align:start;'>Marca</th>" +
            "<th style='text-align:start;'>Modelo</th>" +
            "<th style='text-align:start;'>N° Constancia</th>" +
            "<th style='text-align:start;'>Tipo</th>" +
            "<th style='text-align:end;'>Capacidad</th>" +

 
            "<th style='text-align:center;' width=100px>Acciones</th>" +
            "</tr>" +
            "</thead>";
    if (!isEmpty(data)) {
        // let iconoEstado = [{estado_actualizar: 1, color: "#cb2a2a", icono: "ion-flash-off"}, {estado_actualizar: 0, color: "#5cb85c", icono: "ion-checkmark-circled"}];
        // Define the mapping function
       
        var tipoMap = {
            1: { nombre: 'Vehículo', icono: '<i class="fa fa-car"></i>' },
            2: { nombre: 'Carreta', icono: '<i class="fa fa-truck"></i>' },
        };
        $.each(data, function (index, item) {
            ;
            var tipo = tipoMap[item.tipo] || { nombre: 'Desconocido', icono: '' };
            var tipoNombreIcono = tipo.icono + ' ' + tipo.nombre;
            if(item.estado==1){
                estado='Activo';
            }
            else{
                estado='Inactivo';
            }
            cuerpo = "<tr>" +
                    // "<td style='text-align:end;'>" + (index + 1) + "</td>" +
                    "<td style='text-align:start;'>" + item.placa + "</td>" +
                    "<td style='text-align:start;'>" + item.marca + "</td>" +
                    "<td style='text-align:start;'>" + item.modelo + "</td>" +
                    "<td style='text-align:start;'>" + item.nro_constancia + "</td>" +
                    "<td style='text-align:start;'>" + tipoNombreIcono  + "</td>" +
                    "<td style='text-align:end;'>" + item.capacidad + "</td>" +

                    "<td style='text-align:center;'>" +
                    "<a href='#' onclick='cargarDivGetVehiculo(" + item.id + ")'><b><i class='fa fa-edit' style='color:#E8BA2F;'></i><b></a>&nbsp;\n" +
                    "<a href='#' onclick='confirmarDelete(" + item.id + ", \"" + (item.marca + " | " + item.modelo) + "\")'><b><i class='fa fa-trash-o' style='color:#cb2a2a;'></i><b></a>" +
                    "</td>" +
                    "</tr>";
            cuerpo_total = cuerpo_total + cuerpo;
        });
    }
    var pie = '</table>';
    var html = cabeza + cuerpo_total + pie;
    $("#dataList").append(html);
}

function confirmarDelete(id, descripcion) {
    ;
    swal({
        title: "Est\xe1s seguro?",
        text: "Eliminarás " + descripcion + "",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#33b86c",
        confirmButtonText: "Si,eliminar!",
        cancelButtonColor: '#d33',
        cancelButtonText: "No,cancelar !",
         closeOnCancel: false
    }, function (isConfirm) {
        ;
        if (isConfirm) {
            cambiarEstado(id, 2);
        } else {
            swal("Cancelado", "La eliminaci\xf3n fue cancelada", "error");
        }
    });
}