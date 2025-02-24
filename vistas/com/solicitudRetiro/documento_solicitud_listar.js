
$(document).ready(function () {
    loaderClose();
    ax.setSuccess("successZona");
    
    listarSolicitudesDocumentario();
});

function successZona(response) {
    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'listarSolicitudesDocumentario':
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
    ax.setAccion("listarSolicitudesDocumentario");
    ax.consumir();
}

function onResponseAjaxpGetDataGridSolicitud(data) {
    ;
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
            "<th style='text-align:center;'>Factura Transporte</th>" +
            "<th style='text-align:center;'>Guia Remisión Transp.</th>" +
            "<th style='text-align:center;'>Guia Remisión Remite.</th>" +
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
                    "<td style='text-align:center;'>" + generarCeldaArchivo(item.archivo_factura, item.id, 'factura_transporte') + "</td>" +
                    "<td style='text-align:center;'>" + generarCeldaArchivo(item.archivo_guia_transportista, item.id, 'guia_remision_transp') + "</td>" +
                    "<td style='text-align:center;'>" + generarCeldaArchivo(item.archivo_guia, item.id, 'guia_remision_remite') + "</td>" +
                    "</tr>";
            cuerpo_total = cuerpo_total + cuerpo;
        });
    }
    var pie = '</table>';
    var html = cabeza + cuerpo_total + pie;
    $("#dataList").append(html);
}

function generarCeldaArchivo(archivo, id, tipo) {
    ;
    var inputId = 'file_' + id + '_' + tipo;
    if (archivo) {
        var archivoRuta='/sgiLaVictoria/vistas/com/solicitudRetiro/documento/'+archivo;
        return "<div>" +
            "<button onclick='visualizarArchivo(\"" + archivoRuta + "\")'><i class='fa fa-eye' style='color:#5cb85c;'></i> Visualizar</button>" +
            "<button onclick='eliminarArchivo(" + id + ", \"" + tipo + "\",\"" + archivo + "\");'><i class='fa fa-trash-o' style='color:#cb2a2a;'></i> Eliminar</button>" +
            "</div>";
    } else {
        return "<div class='input-group col-lg-12 col-md-12 col-sm-12 col-xs-12'>" +
            "<br> <br>   <div class='fileUpload btn w-lg m-b-5' id='multi' style='border-radius: 0px;background-color: #337Ab7;color: #fff;cursor:default;'>" +
            "<div id='edi'><i class='ion-upload m-r-15' style='font-size: 16px;'></i>Subir imagen</div>" +
            "<input name='file' id='" + inputId + "' type='file' accept='image/*' class='upload' onchange='subirArchivo(" + id + ", \"" + tipo + "\", \"" + inputId + "\");'>" +
            "</div>" +
            "&nbsp; &nbsp; <b class='' id='upload-file-info-" + inputId + "'>Ninguna imagen seleccionada</b>" +
            "</div>";
    }
}


function visualizarArchivo(archivo) {
    window.open(archivo, '_blank');
}

function eliminarArchivo(id, tipo,archivo) {
    loaderShow();
    ;
    ax.setAccion("eliminarArchivo");
    ax.addParamTmp("id", id);
    ax.addParamTmp("archivo", archivo);
    ax.addParamTmp("tipo", tipo);
    ax.consumir();
}


function subirArchivo(id, tipo, inputId) {
    loaderShow();
    ;
    var inputFile = document.getElementById(inputId);
    if (inputFile && inputFile.files.length > 0) {
        var file = inputFile.files[0]; // Obtener el archivo del input

        // Crear un objeto FileReader para leer el archivo como una cadena base64
        var reader = new FileReader();
        reader.onload = function(event) {
            var base64String = event.target.result; // Obtener la cadena base64 del archivo

            // Aquí puedes enviar la cadena base64 al controlador mediante Axios (ax)
            // Por ahora, simplemente imprimiremos la cadena base64 en la consola
            console.log(base64String);

            // Enviar la cadena base64 al controlador utilizando tu función guardarSolicitud
            guardarSolicitud(base64String, id, tipo);
        };

        // Leer el archivo como una cadena base64
        reader.readAsDataURL(file);

    } else {
        alert('Selecciona un archivo antes de subir.');
    }
}

function guardarSolicitud(base64String, id, tipo) {
    ;
    ax.setAccion("subirArchivo");
    ax.addParamTmp("id", id);
    ax.addParamTmp("file", base64String);
    ax.addParamTmp("tipo", tipo);
    ax.consumir();
  
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