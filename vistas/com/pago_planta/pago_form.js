
$(document).ready(function () {
    loaderClose();
    ax.setSuccess("successZona");
    
    listarSolicitudesDocumentario();
});

function successZona(response) {
    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obtenerValorizacionesGeneradas':
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
                case 'guardarPago':
                $.Notification.autoHideNotify('success', 'top-right', '&Eacute;xito', 'Pago generada');
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
                case 'guardarPago':
                    swal("Cancelado", "No se pudo registrar el pago", "error");
                    loaderClose();
                    break;
        }
    }
}

function cargarListarPersonaCancelar()
{
    loaderShow(null);
    cargarDiv('#window', 'vistas/com/pago_planta/pago_listar.php');
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
    ax.setAccion("obtenerValorizacionesGeneradas");
    ax.addParamTmp("tipoPago", commonVars.personaTipoId);
    ax.consumir();
}

function onResponseAjaxpGetDataGridSolicitud(data) {
    ;
    if(commonVars.personaTipoId==2){
    var titulo = document.getElementById('titulo');

    // Modificar el contenido del <h3>
    titulo.innerHTML = '<b>Valoraciones pendientes de pago detracción</b>';}
    else{
        var titulo = document.getElementById('titulo');

        // Modificar el contenido del <h3>
        titulo.innerHTML = '<b>Valoraciones pendientes de pago neto</b>';
    }
    $("#dataList").empty();
    var cuerpo_total = '';
    var cuerpo = '';
    var cabeza = '<table id="datatable" class="table table-responsive table-striped table-bordered"><thead>' +
        " <tr>" +
        "<th style='text-align:center;'></th>" +
        "<th style='text-align:center;'>Representante Legal</th>" +
        "<th style='text-align:center;'>Usuario</th>" +
        "<th style='text-align:center;'>Serie</th>" +
        "<th style='text-align:center;'>Correlativo</th>" +
        "<th style='text-align:center;'>Monto</th>" +
        "<th style='text-align:center;'>Tipo</th>" +
        "<th style='display:none;'></th>" +
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
            "<td style='text-align:center;'>" + 
                "<input type='checkbox' class='loteCheckbox' data-id='" + item.id + "'>" +
            "</td>" +
            "<td style='text-align:center;'>" + item.comunidad + "</td>" +
            "<td style='text-align:center;'>" + item.minero + "</td>" +
            "<td style='text-align:center;'>" + item.serie + "</td>" +
            "<td style='text-align:center;'>" + item.correlativo + "</td>" +
            "<td style='text-align:center;'>" + item.monto + "</td>" +
            "<td style='text-align:center;'>" + item.tipo + "</td>" +
            "<td style='display:none;'>"+ item.padre_id + "</td>" +
          
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
    ;
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
        var serie = row.find('td:eq(3)').text(); // La celda del lote
        var correlativo = row.find('td:eq(4)').text(); // La celda del lote
        var totalLote = parseFloat(row.find('td:eq(5)').text()); // La celda del total
        var sociedad_id = row.find('td:eq(7)').text(); 
        // Verificar si es la primera sociedad seleccionada
        if (sociedadUnica === null) {
            sociedadUnica = sociedad;
        } else if (sociedadUnica !== sociedad) {
            // Si hay sociedades diferentes, mostramos un mensaje y desmarcamos todos los checkboxes
            swal("Error", "No se pueden seleccionar lotes de diferentes sociedades.", "error");
            $('.loteCheckbox').prop('checked', false); // Desmarcar todos los checkboxes
            return; // Salir de la función
        }

        ;
        // Añadir los datos de este lote al array de detalles
        detalles.push({
            id: loteId,
            sociedad: sociedad,
            serie: serie,
            correlativo: correlativo,
            totalLote: totalLote
        });

        totalSeleccionado += totalLote;
        sociedad_id=sociedad_id;
        $('#minero').val(sociedad_id);
    });

    // Llenar la tabla de detalles en el modal
    var detalleHTML = '';
    detalles.forEach(function(detalle) {
        detalleHTML += "<tr><td>" + detalle.sociedad + "</td><td>" + detalle.serie +'-'+ detalle.correlativo+ "</td><td>" + detalle.totalLote.toFixed(3) + "</td></tr>";
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
    // $('#igv').val(igv.toFixed(3));
    // $('#totalFactura').val(totalFactura.toFixed(3));
    // $('#detraccion').val(detraccion.toFixed(3));
    // $('#netoPago').val(netoPagar.toFixed(3));
   
    // Abrir el modal
    $('#modalFactura').modal('show');
}

function handleFileSelect(event) {
    const file = event.target.files[0]; // Obtenemos el archivo seleccionado

    if (file) {
        const reader = new FileReader(); // Creamos un lector de archivos

        // Evento cuando se ha leído el archivo
        reader.onload = function(e) {
            // Obtener la base64 del archivo
            const base64 = e.target.result;

            // Obtener el nombre y extensión del archivo
            const fileName = file.name;
            const fileExtension = fileName.split('.').pop(); // Extraer la extensión del archivo

            // Asignar la base64, nombre y extensión a los campos ocultos
            document.getElementById('fileBase64').value = base64;
            document.getElementById('fileName').value = fileName;
            document.getElementById('fileExtension').value = fileExtension;

            // Limpiar cualquier contenido anterior en el visualizador
            document.getElementById('fileViewer').innerHTML = '';

            // Si el archivo es un PDF
            if (fileExtension === 'pdf') {
                document.getElementById('fileViewer').innerHTML = `<embed src="${base64}" width="100%" height="500px" type="application/pdf" />`;
            } else if (fileExtension === 'jpg' || fileExtension === 'jpeg' || fileExtension === 'png') {
                // Si es una imagen, mostrarla
                document.getElementById('fileViewer').innerHTML = `<img src="${base64}" alt="Imagen de factura" width="100%" />`;
            } else {
                document.getElementById('fileViewer').innerHTML = `<p>Formato de archivo no soportado</p>`;
            }
        };

        // Leemos el archivo como base64
        reader.readAsDataURL(file);
    }
}


function guardarFactura() {
    loaderShow();


    var subtotal = $('#subtotal').val();
    var minero = $('#minero').val();
    var numeroOperacion = $('#numeroOperacion').val();
    var lotesSeleccionados = [];
    const fileBase64 = document.getElementById('fileBase64').value;
    const fileName = document.getElementById('fileName').value;
    const fileExtension = document.getElementById('fileExtension').value;
    
    $('.loteCheckbox:checked').each(function() {
        ;
        
        var loteId = $(this).data('id');
        lotesSeleccionados.push(loteId);
    });

    // Enviar al backend
    ax.setAccion("guardarPago");
    ax.addParamTmp("tipo", commonVars.personaTipoId);
    ax.addParamTmp("subtotal", subtotal);
    ax.addParamTmp("fileBase64", fileBase64);
    ax.addParamTmp("fileExtension", fileExtension);
    ax.addParamTmp("lotes", JSON.stringify(lotesSeleccionados));
    ax.addParamTmp("minero", minero);
    ax.addParamTmp("numeroOperacion", numeroOperacion);
    ax.consumir();
    
    $('#modalFactura').modal('hide');
}
