
$(document).ready(function () {
    loaderClose();
    ax.setSuccess("successAgencia");
    listarAgencias();
});

function successAgencia(response) {
    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'listarAgencia':
                onResponseAjaxpGetDataGridAgencia(response.data);
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

            case 'actualizarEstadoAgencia':
                $.Notification.autoHideNotify('success', 'top-right', '&Eacute;xito', 'Estado actualizado');
                listarAgencias();
                break;

        }
    }
}


function cambiarEstado(id, estado)
{
    ax.setAccion("actualizarEstadoAgencia");
    ax.addParamTmp("id", id);
    ax.addParamTmp("estado", estado);
    ax.consumir();
}
function cargarDivGetAgencia(id) {
    cargarDivIndex("#window", "vistas/com/agencia/agencia_form.php?id=" + id + "&" + "tipo=" + 1, 324, "");
}

function listarAgencias() {
    ax.setAccion("listarAgencia");
    ax.addParamTmp("empresa_id", commonVars.empresa);
    ax.consumir();
}

function onResponseAjaxpGetDataGridAgencia(data) {
    $("#dataList").empty();
    var cuerpo_total = '';
    var cuerpo = '';
    var cabeza = '<table id="datatable" class="table table-responsive table-striped table-bordered"><thead>' +
            " <tr>" +
            "<th style='text-align:center;'>#</th>" +
            "<th style='text-align:center;'>División</th>" +
            "<th style='text-align:center;'>Centro costo</th>" +
            "<th style='text-align:center;'>Agencia</th>" +
            "<th style='text-align:center;'>Modelo de local</th>" +
            "<th style='text-align:center;'>Departamento</th>" +
            "<th style='text-align:center;'>Provincia</th>" +
            "<th style='text-align:center;'>Distrito</th>" +
            "<th style='text-align:center;'>Dirección</th>" +
            "<th style='text-align:center;'>Ubicación geográfica</th>" +
            "<th style='text-align:center;' width=100px>Estado</th>" +
            "<th style='text-align:center;' width=100px>Acciones</th>" +
            "</tr>" +
            "</thead>";
    if (!isEmpty(data)) {
        let iconoEstado = [{estado_actualizar: 1, color: "#cb2a2a", icono: "ion-flash-off"}, {estado_actualizar: 0, color: "#5cb85c", icono: "ion-checkmark-circled"}];

        $.each(data, function (index, item) {

            cuerpo = "<tr>" +
                    "<td style='text-align:center;'>" + (index + 1) + "</td>" +
                    "<td style='text-align:center;'>" + item.division_descripcion + "</td>" +
                    "<td style='text-align:center;'>" + item.codigo + "</td>" +
                    "<td style='text-align:center;'>" + item.descripcion + "</td>" +
                    "<td style='text-align:center;'>" + item.modelo_local_descripcion + "</td>" +
                    "<td style='text-align:center;'>" + item.departamento + "</td>" +
                    "<td style='text-align:center;'>" + item.provincia + "</td>" +
                    "<td style='text-align:center;'>" + item.distrito + "</td>" +
                    "<td style='text-align:center;'>" + item.direccion + "</td>" +
                    "<td style='text-align:center;'>" + item.ubicacion_geografica_descripcion + "</td>" +
                    "<td style='text-align:center;'><a href='#' onclick = 'cambiarEstado(" + item.id + "," + iconoEstado[item.estado * 1]['estado_actualizar'] + ")' ><b><i id='" + item.id + "' class='" + iconoEstado[item.estado * 1]['icono'] + "' style='color:" + iconoEstado[item.estado * 1]['color'] + ";'></i><b></a></td>" +
                    "<td style='text-align:center;'>" +
                    "<a href='#' onclick='cargarDivGetAgencia(" + item.id + ")'><b><i class='fa fa-edit' style='color:#E8BA2F;'></i><b></a>&nbsp;\n" +
                    "<a href='#' onclick='confirmarDelete(" + item.id + ", \"" + (item.codigo + " | " + item.descripcion) + "\")'><b><i class='fa fa-trash-o' style='color:#cb2a2a;'></i><b></a>" +
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

        if (isConfirm) {
            cambiarEstado(id, 2);
        } else {
            swal("Cancelado", "La eliminaci\xf3n fue cancelada", "error");
        }
    });
}

 