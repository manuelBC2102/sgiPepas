function commonsOnResponseAjaxp(data_response) {
    // Implementación de la función
    console.log('commonsOnResponseAjaxp llamada con:', data_response);
}
var ax = new Ajaxp(URL_EXECUTECONTROLLER, 'POST', 'JSON');
var usuarioEdicionProveedor = 'OFVIVVh5QXlxc1ZOMkl2N1VhSHlDZz09';

$(document).ready(function () {
   
    ax.setOpcion(357);
    ax.setSuccess("getResponseRegistroProveedorForm");
//    $("#tituloRegistroProveedores").text("Ficha para el registro de proveedores");
    obtenerParametrosIniciales();
//    cambiarURL();
});

function getResponseRegistroProveedorForm(response) {
    if (response.status === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obtenerDataInicialPreForm':
                onResponseObtenerPreDataForm(response.data);
                break;

            case 'obtenerDataInicialForm':
                onResponseObtenerDataForm(response.data);
                break;

            case 'obtenerParametrosIniciales':
                ;
                if(response.data==0){mensajeCulminado(); loaderClose();}
                else{
                onResponseObtenerParametrosIniciales(response.data);
                obtenerDocumentosAdministracion();
                obtenerCoordenadas();
                 onResponseAjaxpGetDataGridSolicitud();
                 $('#datatable32').dataTable({
                    "scrollX": true,
                    "autoWidth": true,
                    "order": [[0, "asc"]],
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
                 }
                break;

            case 'obtenerDocumentosPlanta':
                ;
                onResponseAjaxpGetDataGridSolicitud(response.data);
                $('#datatable32').dataTable({
                    "scrollX": true,
                    "autoWidth": true,
                    "order": [[0, "asc"]],
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
            
            case 'obtenerDocumentosAdministracion':
                ;
                onResponseAjaxpGetDataGridSolicitud2(response.data);
                $('#datatable').dataTable({
                    "scrollX": true,
                    "autoWidth": true,
                    "order": [[0, "asc"]],
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

                case 'obtenerCoordenadas':
                    ;
                    buildTable(response.data);
                    $('#coordinatesTable').dataTable({
                        "scrollX": true,
                        "autoWidth": true,
                        "order": [[0, "asc"]],
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
                 var planta = $('#cboPlantas').val();
                 $.Notification.autoHideNotify('success', 'top-right', '&Eacute;xito', 'Archivo Cargado');
                 onChangeDocumentoTipoAdjunto(planta);
                 obtenerDocumentosAdministracion();
               break;
            
            case  'eliminarArchivo':
                var planta = $('#cboPlantas').val();
                $.Notification.autoHideNotify('success', 'top-right', '&Eacute;xito', 'Archivo Eliminado');
                onChangeDocumentoTipoAdjunto(planta);
                obtenerDocumentosAdministracion();
              break;

              case  'subirArchivo2':
                var planta = $('#cboPlantas').val();
                $.Notification.autoHideNotify('success', 'top-right', '&Eacute;xito', 'Archivo Cargado');
                obtenerDocumentosAdministracion();
                onChangeDocumentoTipoAdjunto(planta);
              break;
           
           case  'eliminarArchivo2':
               var planta = $('#cboPlantas').val();
               $.Notification.autoHideNotify('success', 'top-right', '&Eacute;xito', 'Archivo Eliminado');
               obtenerDocumentosAdministracion();
               onChangeDocumentoTipoAdjunto(planta);
             break;

            case 'obtenerPlantasXPersona':
                ;
                listarDataSolicitudes(response.data);
                break;

            case 'guardarInvitacionConformidad':
                ;
                loaderClose();
                $('#modalDocumento').modal('hide');
                mensajeCulminado();
                break;

            case 'actualizarSolicitudProveedor':
                onResponseActualizarProveedor(response.data);
                break;

            case 'aprobarRechazarSolicitudProveedor':
                onResponseAprobarRechazarSolicitud(response.data);
                break;


        }
    } else {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obtenerDataInicialForm':
                mensajeErrorInscripcion();
                break;

            case 'obtenerDataInicialPreForm':
                mensajeErrorInscripcion();
                break;
            case 'obtenerParametrosIniciales':
                ;
                mensajeErrorTiempo();
                break;
            case 'guardarInvitacionConformidad':
                ;
                loaderClose();
                $("button[onclick='guardarInvitacion();']").prop("disabled", false);
                btnEnviar.innerHTML = '<i ></i> Enviar';
                // mensajeErrorInscripcion();
                break;
                case 'obtenerPlantasXPersona':
                    ;
                    loaderClose();
                    break;
            default:
                recargarPagina(response.message);
                exitoCrear([{ vout_exito: 0, vout_mensaje: response.message }], false);
                break;
        }
    }
}

function onResponseAjaxpGetDataGridSolicitud3(data) {
    ;
    $("#dataList3").empty();
    var cuerpo_total = '';
    var cuerpo = '';
    var cabeza = '<table id="datatable" class="table table-responsive table-striped table-bordered"><thead>' +
            " <tr style='background-color:#244061; color:white'>" +
            "<th style='text-align:center; width: 10%;'>#</th>" +
            "<th style='text-align:center; width: 30%;'>X</th>" +
            "<th style='text-align:center; width: 30%;'>Y</th>" +
            "<th style='text-align:center;  width: 20%;'>Acciones</th>" +
            "</tr>" +
            "</thead>";
    if (!isEmpty(data)) {
     
        $numero=1;
        $.each(data.plantas, function (index, item) {
           
     
            cuerpo = "<tr>" +
                    "<td style='text-align:center; width: 10%;'>" + $numero + "</td>" +
                    "<td style='text-align:left; width: 30%;' >" + item.nombre + "</td>" +
                    "<td style='text-align:left; width: 30%;'>" + item.fileName + "</td>" +
                    "<td style='text-align:center; width: 20%;'>" + generarCeldaArchivo2(item.archivo, item.id, 'factura_transporte',persona,planta,item.persona_archivo_id) + "</td>" +

                    "</tr>";
            cuerpo_total = cuerpo_total + cuerpo;
            $numero=$numero+1;
        });
    }
    var pie = '</table>';
    var html = cabeza + cuerpo_total + pie;
    $("#dataList3").append(html);
    // onChangeDocumentoTipoAdjunto2();
    loaderClose();
}

function buildTable(data) {
    ;
    const tableContainer = document.getElementById('dataList3');
    let tableHTML = `
        <table id="coordinatesTable" class="table table-bordered">
            <thead>
                <tr style="background-color:#244061; color:white;">
                    <th style="text-align:center; width: 15%;">#</th>
                    <th style="text-align:center; width: 35%;">X</th>
                    <th style="text-align:center; width: 35%;">Y</th>
                    <th style="text-align:center; width: 25%;">Acciones</th>
                </tr>
            </thead>
            <tbody id="coordinatesBody">
    `;
    const defaultData = data && Array.isArray(data) ? data : Array.from({ length: 3 }, () => ({ x: '', y: '' }));

    
    defaultData.forEach((item, index) => {
        tableHTML += `
            <tr>
                <td style="text-align:center;">${index+1 }</td>
                <td style="text-align:left;"><input type="number" class="form-control" placeholder="X" required /></td>
                <td style="text-align:left;"><input type="number" class="form-control" placeholder="Y" required /></td>
                <td style="text-align:center;"><button type="button" class="btn btn-danger btn-sm" onclick="removeSpecificRow(this)">Eliminar</button></td>
            </tr>
        `;
       
    });

    tableHTML += `
            </tbody>
        </table>
    `;

    tableContainer.innerHTML = tableHTML;
    reindexRows(); 
}

function onResponseAjaxpGetDataGridSolicitud2(data) {
    ;
    var persona = trim(document.getElementById('secretPersona').value);
    var planta = $('#cboPlantas').val();
    ;
    $("#dataList2").empty();
    var cuerpo_total = '';
    var cuerpo = '';
    var cabeza = '<table id="datatable" class="table table-responsive table-striped table-bordered"><thead>' +
            " <tr style='background-color:#244061; color:white'>" +
            "<th style='text-align:center; width: 10%;'>#</th>" +
            "<th style='text-align:center; width: 30%;'>Documento</th>" +
            "<th style='text-align:center; width: 30%;'>Archivo</th>" +
            "<th style='text-align:center;  width: 10%;'>Cargado</th>" +
            "<th style='text-align:center;  width: 20%;'>Acciones</th>" +
            "</tr>" +
            "</thead>";
    if (!isEmpty(data)) {
        let iconoEstado = [{estado_actualizar: 1, color: "#cb2a2a", icono: "ion-flash-off"}, {estado_actualizar: 0, color: "#5cb85c", icono: "ion-checkmark-circled"}];
        $numero=1;
        $.each(data.plantas, function (index, item) {
            var iconoAsignado = item.asignado === 'Sí' 
            ? "<i class='fa fa-check' style='color:green;'></i>" 
            : "<i class='fa fa-times' style='color:red;'></i>";

            if(item.fileName==null){
                item.fileName='';
            }
            cuerpo = "<tr>" +
                    "<td style='text-align:center; width: 10%;'>" + $numero + "</td>" +
                    "<td style='text-align:left; width: 30%;' >" + item.nombre + "</td>" +
                    "<td style='text-align:left; width: 30%;'>" + item.fileName + "</td>" +
                    "<td style='text-align:center; width: 10%;'>" + iconoAsignado + "</td>" +
                    "<td style='text-align:center; width: 20%;'>" + generarCeldaArchivo2(item.archivo, item.id, 'factura_transporte',persona,planta,item.persona_archivo_id) + "</td>" +

                    "</tr>";
            cuerpo_total = cuerpo_total + cuerpo;
            $numero=$numero+1;
        });
    }
    var pie = '</table>';
    var html = cabeza + cuerpo_total + pie;
    $("#dataList2").append(html);
    // onChangeDocumentoTipoAdjunto2();
    loaderClose();
}

function onResponseAjaxpGetDataGridSolicitud(data) {
    ;
    var persona = trim(document.getElementById('secretPersona').value);
    var planta = $('#cboPlantas').val();
    ;
    $("#dataList").empty();
    var cuerpo_total = '';
    var cuerpo = '';
    var cabeza = '<table id="datatable32" class="table table-responsive table-striped table-bordered"><thead>' +
            " <tr style='background-color:#244061; color:white'>" +
            "<th style='text-align:center; width: 10%;'>#</th>" +
            "<th style='text-align:center; width: 25%;'>Documento</th>" +
            "<th style='text-align:center; width: 10%;'>Formato</th>" +
            "<th style='text-align:center; width: 25%;'>Archivo</th>" +
            "<th style='text-align:center;  width: 10%;'>Cargado</th>" +
            "<th style='text-align:center;  width: 20%;'>Acciones</th>" +
            "</tr>" +
            "</thead>";
    if (!isEmpty(data)) {
        let iconoEstado = [{estado_actualizar: 1, color: "#cb2a2a", icono: "ion-flash-off"}, {estado_actualizar: 0, color: "#5cb85c", icono: "ion-checkmark-circled"}];
        $numero=1;
        $.each(data.plantas, function (index, item) {
            var iconoAsignado = item.asignado === 'Sí' 
            ? "<i class='fa fa-check' style='color:green;'></i>" 
            : "<i class='fa fa-times' style='color:red;'></i>";

            if(item.fileName==null){
                item.fileName='';
            }
            var archivoRuta='/sgiLaVictoria/vistas/com/persona/documentosPlanta/'+item.formato;
            var linkFormato = "<a href='" + archivoRuta + "' target='_blank' style='color: blue; text-decoration: underline;'>Ver Formato</a>";
            cuerpo = "<tr>" +
                    "<td style='text-align:center; width: 10%;'>" + $numero + "</td>" +
                    "<td style='text-align:left; width: 25%;' >" + item.nombre + "</td>" +
                    "<td style='text-align:center; width: 1.0%;' >" + linkFormato + "</td>" +
                    "<td style='text-align:left; width: 25%;'>" + item.fileName + "</td>" +
                    "<td style='text-align:center; width: 10%;'>" + iconoAsignado + "</td>" +
                    "<td style='text-align:center; width: 20%;'>" + generarCeldaArchivo(item.archivo, item.id, 'factura_transporte',persona,planta,item.persona_archivo_id) + "</td>" +

                    "</tr>";
            cuerpo_total = cuerpo_total + cuerpo;
            $numero=$numero+1;
        });
    }
    var pie = '</table>';
    var html = cabeza + cuerpo_total + pie;
    $("#dataList").append(html);
    // obtenerDocumentosAdministracion();
    loaderClose();
}

function generarCeldaArchivo2(archivo, id, tipo,persona,planta,persona_archivo_id) {
    ;
    loaderClose();
    var inputId = 'file_' + id + '_' + tipo;
    if (archivo) {
        var archivoRuta='/sgiLaVictoria/vistas/com/persona/documentos/'+archivo;
        return "<div>" +
            "<button onclick='visualizarArchivo2(\"" + archivoRuta + "\")'><i class='fa fa-eye' style='color:#5cb85c;'></i> Visualizar</button>" +
            "<button onclick='eliminarArchivo2(" + id + ", \"" + tipo + "\",\"" + archivo + "\", " + persona_archivo_id + ", " + planta + ");'><i class='fa fa-trash-o' style='color:#cb2a2a;'></i> Eliminar</button>" +
            "</div>";
    } else {
        return "<div class='input-group col-lg-6 col-md-6 col-sm-6 col-xs-6'>" +
            " <div class='fileUpload btn w-lg m-b-2' id='multi' style='border-radius: 0px;background-color: #337Ab7;color: #fff;cursor:default;'>" +
            "<div id='edi'><i class='ion-upload m-r-8' style='font-size: 12px;'></i>Subir documento</div>" +
            "<input name='file' id='" + inputId + "' type='file' accept='*' class='upload' onchange='subirArchivo2(" + id + ", \"" + tipo + "\", \"" + inputId + "\", " + persona + ", " + planta + ");'>" +
            "</div>" +
            "&nbsp; &nbsp; <b class='' id='upload-file-info-" + inputId + "'>Ningún documento seleccionada</b>" +
            "</div>";
    }
    
}

function visualizarArchivo2(archivo) {
    window.open(archivo, '_blank');
}

function eliminarArchivo2(id, tipo,archivo,persona_archivo_id,planta) {
    loaderShow();
    ;
    ax.setAccion("eliminarArchivo2");
    ax.addParamTmp("id", id);
    ax.addParamTmp("archivo", archivo);
    ax.addParamTmp("tipo", tipo);
    ax.addParamTmp("persona", persona_archivo_id);
    ax.addParamTmp("planta", planta);
    ax.consumir();
}


function subirArchivo2(id, tipo, inputId,persona,planta) {
    loaderShow();
    ;
    var inputFile = document.getElementById(inputId);
    var inputFileName = inputFile.files[0].name;
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
            guardarSolicitud2(base64String, id, tipo,inputFileName,persona,planta);
        };

        // Leer el archivo como una cadena base64
        reader.readAsDataURL(file);

    } else {
        alert('Selecciona un archivo antes de subir.');
    }
}

function guardarSolicitud2(base64String, id, tipo,inputFileName,persona,planta) {
    ;
    ax.setAccion("subirArchivo2");
    ax.addParamTmp("id", id);
    ax.addParamTmp("file", base64String);
    ax.addParamTmp("tipo", tipo);
    ax.addParamTmp("name", inputFileName);
    ax.addParamTmp("persona", persona);
    ax.addParamTmp("planta", planta);
    ax.consumir();
  
}

function generarCeldaArchivo(archivo, id, tipo,persona,planta,persona_archivo_id) {
    ;
    loaderClose();
    var inputId = 'fileid_' + id + '_' + tipo;
    if (archivo) {
        var archivoRuta='/sgiLaVictoria/vistas/com/persona/documentos/'+archivo;
        return "<div>" +
            "<button onclick='visualizarArchivo(\"" + archivoRuta + "\")'><i class='fa fa-eye' style='color:#5cb85c;'></i> Visualizar</button>" +
            "<button onclick='eliminarArchivo(" + id + ", \"" + tipo + "\",\"" + archivo + "\", " + persona_archivo_id + ", " + planta + ");'><i class='fa fa-trash-o' style='color:#cb2a2a;'></i> Eliminar</button>" +
            "</div>";
    } else {
        return "<div class='input-group col-lg-6 col-md-6 col-sm-6 col-xs-6'>" +
            " <div class='fileUpload btn w-lg m-b-2' id='multi' style='border-radius: 0px;background-color: #337Ab7;color: #fff;cursor:default;'>" +
            "<div id='edi'><i class='ion-upload m-r-8' style='font-size: 12px;'></i>Subir documento</div>" +
            "<input name='file' id='" + inputId + "' type='file' accept='*' class='upload' onchange='subirArchivo(" + id + ", \"" + tipo + "\", \"" + inputId + "\", " + persona + ", " + planta + ");'>" +
            "</div>" +
            "&nbsp; &nbsp; <b class='' id='upload-file-info-" + inputId + "'>Ningún documento seleccionada</b>" +
            "</div>";
    }
    
}


function visualizarArchivo(archivo) {
    window.open(archivo, '_blank');
}

function eliminarArchivo(id, tipo,archivo,persona_archivo_id,planta) {
    loaderShow();
    ;
    ax.setAccion("eliminarArchivo");
    ax.addParamTmp("id", id);
    ax.addParamTmp("archivo", archivo);
    ax.addParamTmp("tipo", tipo);
    ax.addParamTmp("persona", persona_archivo_id);
    ax.addParamTmp("planta", planta);
    ax.consumir();
}


function subirArchivo(id, tipo, inputId,persona,planta) {
    loaderShow();
    ;
    var inputFile = document.getElementById(inputId);
    var inputFileName = inputFile.files[0].name;
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
            guardarSolicitud(base64String, id, tipo,inputFileName,persona,planta);
        };

        // Leer el archivo como una cadena base64
        reader.readAsDataURL(file);

    } else {
        alert('Selecciona un archivo antes de subir.');
    }
}

function guardarSolicitud(base64String, id, tipo,inputFileName,persona,planta) {
    ;
    ax.setAccion("subirArchivo");
    ax.addParamTmp("id", id);
    ax.addParamTmp("file", base64String);
    ax.addParamTmp("tipo", tipo);
    ax.addParamTmp("name", inputFileName);
    ax.addParamTmp("persona", persona);
    ax.addParamTmp("planta", planta);
    ax.consumir();
  
}

function obtenerParametrosIniciales() {
    ;
    loaderShow();
    let parametros = obtenecionCadenaEncriptada();
    if (isEmpty(parametros)) {
        mensajeErrorInscripcion();
        return;
    }

    ax.setAccion("obtenerParametrosIniciales");
    ax.addParam("parametros", parametros);
    if (typeof usuarioEdicionProveedor !== 'undefined') {
        ax.addParam("usuariox", usuarioEdicionProveedor);
        ax.addParam("param_workflow", 1);
    }
    ax.consumir();
}

// function nuevoDocumento() {
//     // Utilizando jQuery para mostrar el modal
//     $('#modalDocumento').modal('show');
    
// }

var tamanioArchivo = 0;
$("#file").change(function () {
    ;
    $('#idPopover').attr("data-content", !isEmpty($('#file').val().slice(12)) ? $('#file').val().slice(12) : "No se eligió archivo");
    $('#idPopover').popover('show');
    $('.popover-content').css('color', 'black');
    $('[class="popover fade top in"]').css('z-index', '0');
    $("#msjDocumento").empty();
    $('#msjDocumento').hide();
    if (this.files && this.files[0]) {
        tamanioArchivo = this.files[0].size;
        $("#secretName").val(this.files[0].name);
        var reader = new FileReader();
        reader.onload = imageIsLoaded;
        reader.readAsDataURL(this.files[0]);
    }
});

function imageIsLoaded(e) {
    ;
    $('#secretFile').attr('value', e.target.result);
}

$("#fileFirma").change(function () {
    ;
    if (this.files && this.files[0]) {
        $("#secretNameFirma").val(this.files[0].name);
        var reader = new FileReader();
        reader.onload = imageFirmaIsLoaded;
        reader.readAsDataURL(this.files[0]);
    } else {
        $("#secretFileFirma").val("");
        $("#PiePagFirma").html("<img src='" + URL_BASE + "vistas/images/nofirma.jpg" + "' onerror=\"this.src='vistas/images/nodata.jpg'\"  class='img-responsive' style='width: 170px; height: 68px'>");
    }
});

function imageFirmaIsLoaded(e) {
    $('#secretFileFirma').attr('value', e.target.result);
    $("#PiePagFirma").html("<img src='" + e.target.result + "' onerror=\"this.src='vistas/images/nofirma.jpg'\" class='img-responsive' style='width: 170px; height: 68px'>");
}



function agregarDocumento() {
    ;
    var documento = {};
    documento.id = "t" + moment().valueOf();
    documento.value = $('#secretFile').val();
    documento.name = $('#secretName').val();
    documento.comentario = $('#txtComentario').val();

    // Validar si se ha cargado un archivo
    if (isEmpty(documento.value)) {
        $("#msjDocumento").html("El documento es obligatorio").show();
        return;
    }
   
}





function onResponseObtenerParametrosIniciales(data) {
    ;
    $("#txtCodigoIdentificacion").val(data.datos[0].codigo_identificacion);
    $("#txtNombreCompleto").val(data.datos[0].nombre);
    $("#txtRazonSocial").val(data.datos[0].nombre);
    $("#txtSector").val(data.datos[0].sector);
    $("#txtUbicacion").val(data.datos[0].ubicacion);
    $("#txtCodigo").val(data.datos[0].codigo);
    $("#txtTelef1").val(data.datos[0].telefono);
    $("#txtCorreo").val(data.datos[0].email);
    $("#secretPersona").val(data.datos[0].persona_id);
    $("#secretInvitacion").val(data.datos[0].id);
    if(data.datos[0].edit!=null){
        document.getElementById('firmaReinfo').style.display = 'none';
    }
    document.getElementById('txtnombresolicitante').innerText = data.datos[0].nombre;
    // select2.cargar("cboPlantas", data.plantas, "id", ["codigo_identificacion", "nombre_completo"], "Seleccione Planta");
    select2.cargarSeleccione("cboPlantas", data.plantas, "id", ["codigo_identificacion", "nombre_completo"], "Seleccione Planta");

    var ruc = data.datos[0].codigo_identificacion;
    let td = document.getElementById('tipoPersona');

            if (ruc.startsWith('10')) {
                td.textContent = 'Persona Natural';
            } else if (ruc.startsWith('20')) {
                td.textContent = 'Persona Jurídica';
            } else {
                td.textContent = 'Tipo de Persona Desconocido';
            }

}

function onChangeDocumentoTipoAdjunto(id){
    ;
    loaderShow();
    var persona = trim(document.getElementById('secretPersona').value);
    ax.setAccion("obtenerDocumentosPlanta");
    ax.addParam("planta", id);
    ax.addParam("persona", persona);
   
    if (typeof usuarioEdicionProveedor !== 'undefined') {
        ax.addParam("usuariox", usuarioEdicionProveedor);
        ax.addParam("param_workflow", 1);
    }
    ax.consumir();
}


function onChangeDocumentoTipoAdjunto2(){
    ;
    loaderShow();
    var planta = $('#cboPlantas').val();
    var persona = trim(document.getElementById('secretPersona').value);
    ax.setAccion("obtenerDocumentosPlanta");
    ax.addParam("planta", planta);
    ax.addParam("persona", persona);
   
    if (typeof usuarioEdicionProveedor !== 'undefined') {
        ax.addParam("usuariox", usuarioEdicionProveedor);
        ax.addParam("param_workflow", 1);
    }
    ax.consumir();
}


function obtenerDocumentosAdministracion(){
    ;
    var persona = trim(document.getElementById('secretPersona').value);
    ax.setAccion("obtenerDocumentosAdministracion");
    ax.addParam("persona", persona);
   
    if (typeof usuarioEdicionProveedor !== 'undefined') {
        ax.addParam("usuariox", usuarioEdicionProveedor);
        ax.addParam("param_workflow", 1);
    }
    ax.consumir();
}


function obtenerCoordenadas(){
    ;
    var persona = trim(document.getElementById('secretPersona').value);
    ax.setAccion("obtenerCoordenadas");
    ax.addParam("persona", persona);
   
    if (typeof usuarioEdicionProveedor !== 'undefined') {
        ax.addParam("usuariox", usuarioEdicionProveedor);
        ax.addParam("param_workflow", 1);
    }
    ax.consumir();
}

function mensajeCulminado() {
    ;
    swal({
        title: "Invitación registrada",
        type: "warning",
        text: "La invitación ya fue registrada y enviada para aprobación",
        confirmButtonColor: "#5cb85c",
        confirmButtonText: "Aceptar",
        closeOnConfirm: false
    }, function (isConfirm) {
        if (isConfirm) {
;
            window.close();
        }

    });
}

function mensajeErrorInscripcion() {
    swal({
        title: "Aviso",
        type: "warning",
        text: "Error al obtener información",
        confirmButtonColor: "#5cb85c",
        confirmButtonText: "Aceptar",
        closeOnConfirm: false
    }, function (isConfirm) {
        if (isConfirm) {
            window.close();
        }

    });
}

function mensajeErrorTiempo() {
    swal({
        title: "Aviso",
        type: "warning",
        text: "Ya expiró la fecha limite para registrar datos de la invitación.",
        confirmButtonColor: "#5cb85c",
        confirmButtonText: "Aceptar",
        closeOnConfirm: false
    }, function (isConfirm) {
        if (isConfirm) {
            window.close();
        }

    });
}

function obtenecionCadenaEncriptada() {
    let cadena = "";
    if (!isEmpty(parametrosUrl)) {
        cadena = parametrosUrl.substring(parametrosUrl.indexOf('"search":"') + 10);
        return cadena.substring(0, cadena.indexOf('","'));
    }
    return cadena;
}

function cambiarURL() {
    history.pushState(null, "", "/ecosacB2B/inscripcion");
}

function recargarPagina(mensaje) {
    if (!isEmpty(mensaje) && (mensaje.match(/Su sesión ha caducado/g) || mensaje.match(/Error en la sesión/g))) {
        setTimeout(function () {
            window.close();
        }, 1000);
    }
}

function validarFormulario(arrInput, arrMsjId, arrMsj) {
    var bandera = true;
    var espacio = /^\s+$/;
    limpiarMensajes(arrMsjId);

    for (var i = 0; i < arrInput.length; i++) {
        if (trim(arrInput[i]) === "" || trim(arrInput[i]) === null || espacio.test(arrInput[i]) || trim(arrInput[i]).length === 0) {
            $("#" + arrMsjId[i]).text(arrMsj[i]).show();
            bandera = false;
        } else {
            $("#" + arrMsjId[i]).hide();
        }
    }
    return bandera;
}

function limpiarMensajes(arrMsjId) {
    for (var i = 0; i < arrMsjId.length; i++) {
        $("#" + arrMsjId[i]).hide();
    }
}

function validarCorreo(valor) {
    var mensaje = "";

    var emailRegex = /^[-\w.%+]{1,64}@(?:[A-Z0-9-]{1,63}\.){1,125}[A-Z]{2,63}$/i;
    //Se muestra un texto a modo de ejemplo, luego va a ser un icono
    if (!emailRegex.test(valor)) {
        mensaje = "Ingrese un correo válido";
    }

    return mensaje;
}

function formatearInputDecimal(claseNumeroDecimal, decimales) {
    if (isEmpty(decimales)) {
        decimales = 2;
    }
    $(claseNumeroDecimal).inputmask({
        'alias': 'decimal',
        digits: decimales,
        digitsOptional: false,
        placeholder: "0",
        rightAlign: true,
        'groupSeparator': '.',
        'autoGroup': true,
        allowMinus: false
    });
}

function filerArrayOfElement(data, text, id, flag, operator) {

    if (isEmpty(data) || isEmpty(id) || isEmpty(text)) {
        return [];
    }

    let symbol = ` == `;
    if (flag === 0) {
        symbol = ` != `;
    }

    if (isEmpty(operator)) {
        operator = ` && `;
    }

    let string = ``;
    if (isArray(text)) {
        string = ` item.` + text[0] + symbol + `'` + id[0] + `'`;
        for (let index = 1; index < text.length; index++) {
            string += ` ` + operator + ` item.` + text[index] + symbol + `'` + id[index] + `'`;
        }
    } else {
        string = `item.` + text + symbol + `'` + id + `'`;
    }

    let dataFilter = [];
    string = `dataFilter = data.filter(item => ` + string + `)`;
    eval(string);
    return dataFilter;
}

function getArrayOfElementById(data, id, text) {
    if (isEmpty(text)) {
        text = "id";
    }

    let element = data.filter(item => item[text] == id);
    if (!isEmpty(element)) {
        return element[0];
    } else {
        return {};
    }
}

function getFechaFormatDataBase(id) {
    let dateObject = $("#" + id).datepicker("getDate"); // get the date object
    if (!isEmpty(dateObject)) {
        return dateObject.getFullYear() + '-' + (dateObject.getMonth() + 1).toString().padStart(2, '0') + '-' + dateObject.getDate().toString().padStart(2, '0');
    } else {
        return null;
    }
}

function asignarValorSelect2(id, valor) {
    $("#" + id).select2().select2("val", valor);
    $("#" + id).select2({width: '100%'});
}

function cargarSelect2() {
    $("select.select2-hidden-accessible").select2("destroy").select2({width: '100%'});
//    $(".select2").select2("destroy").select2({width: '100%'});
}

function obtenerDataInicial() {
    loaderShow_esperar("buscarDatos");
    ax.setAccion("obtenerDataInicialForm");
    ax.addParam("empresaId", empresaId);
     ax.addParam("solicitudId", solicitud);

    if (typeof usuarioEdicionProveedor !== 'undefined') {
        ax.addParam("usuariox", usuarioEdicionProveedor);
        ax.addParam("param_workflow", 1);
    }

    ax.consumir();
}

function enviarInvitacion(){
    loaderShow();
    var persona = trim(document.getElementById('secretPersona').value);
    ax.setAccion("obtenerPlantasXPersona");
    ax.addParam("persona", persona);
    if (typeof usuarioEdicionProveedor !== 'undefined') {
        ax.addParam("usuariox", usuarioEdicionProveedor);
        ax.addParam("param_workflow", 1);
    }
    ax.consumir();
    
}

function listarDataSolicitudes(data) {
    ;
    $('#modalDocumento').modal('show');
    $("#datatable2").empty();
    if(data.length!=0){
    var cuerpo_total = '';
    var cuerpo = '';
    var cabeza = '<table id="datatable" class="table table-striped table-bordered"><thead>' +
        "<tr>" +
        "<th style='text-align:center;'><input type='checkbox' id='select_all' /></th>" +
        "<th style='text-align:center;'>RUC</th>" +
        "<th style='text-align:center;'>Nombre</th>" +
        "</tr>" +
        "</thead><tbody>";
        if(data.length==1){
    $.each(data, function (index, item) {
        cuerpo = "<tr>" +
            "<td style='text-align:center;'><input type='checkbox' checked  class='select_item' data-id='" + item.id + "' /></td>" +
            "<td style='text-align:left;'>" + item.codigo_identificacion + "</td>" +
            "<td style='text-align:left;'>" + item.nombre_completo + "</td>" +
            "</tr>";
        cuerpo_total = cuerpo_total + cuerpo;
    });  }
    else {
        $.each(data, function (index, item) {
            cuerpo = "<tr>" +
                "<td style='text-align:center;'><input type='checkbox'   class='select_item' data-id='" + item.id + "' /></td>" +
                "<td style='text-align:left;'>" + item.codigo_identificacion + "</td>" +
                "<td style='text-align:left;'>" + item.nombre_completo + "</td>" +
                "</tr>";
            cuerpo_total = cuerpo_total + cuerpo;
        });   
    }
    var pie = '</tbody></table>';
    var html = cabeza + cuerpo_total + pie;
    $("#datatable2").append(html);
    loaderClose();
    // Agregar evento de selección/deselección de todos los checkboxes
    $("#select_all").click(function() {
        $(".select_item").prop('checked', this.checked);
    });  
    $("button[onclick='guardarInvitacion();']").prop("disabled", false);
    }
    else{
        var html ="<span style='color:red;'>No tienes los documentos suficientes para postular a una planta, intenta registrar los documentos solicitados y prueba nuevamente</span>";
        $("#datatable2").append(html);
        $("button[onclick='guardarInvitacion();']").prop("disabled", true);
    } 
}

function getSelectedItems() {
    var selected = [];
    $(".select_item:checked").each(function() {
        selected.push($(this).data('id'));
    });
    return selected;
}

function guardarInvitacion() {
    ;
    loaderShow();
    let btnEnviar = document.getElementById('btnEnviar');

    if (btnEnviar) {
        btnEnviar.disabled = true;
        btnEnviar.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Enviando...';
    }

    btnEnviar.disabled = true;
    btnEnviar.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Enviando...';
    ;
    const rows = document.querySelectorAll('#coordinatesBody tr');
    const coordinates = [];
    
    rows.forEach(row => {
        const inputs = row.querySelectorAll('input');
        const x = inputs[0].value;
        const y = inputs[1].value;
        if (x !== '' && y !== '') {
            coordinates.push({ x, y });
        }
    });
    var selectedItems = getSelectedItems();
    var file = $('#secretFileFirma').val();
    var name = $('#secretNameFirma').val();
    var persona = trim(document.getElementById('secretPersona').value);
    let parametros = obtenecionCadenaEncriptada();
    ax.setAccion("guardarInvitacionConformidad");
    ax.addParam("persona", persona);
    ax.addParam("selectedItems", selectedItems);
    ax.addParam("file", file);
    ax.addParam("name", name);
    ax.addParam("parametros", parametros);
    ax.addParam("coordenadas", coordinates);
    if (typeof usuarioEdicionProveedor !== 'undefined') {
        ax.addParam("usuariox", usuarioEdicionProveedor);
        ax.addParam("param_workflow", 1);
    }
    ax.consumir();

}


        $(document).ready(function() {
            $('#cboPlantas').select2({
                placeholder: "Seleccione tipo de documento",
                allowClear: true
            });
        });
 

        function addRow() {
            if (document.querySelectorAll('#coordinatesBody tr').length < 8) {
                const tbody = document.getElementById('coordinatesBody');
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td style="text-align:center;"></td>
                    <td style="text-align:left;"><input type="number" class="form-control" placeholder="X" required /></td>
                    <td style="text-align:left;"><input type="number" class="form-control" placeholder="Y" required /></td>
                    <td style="text-align:center;"><button type="button" class="btn btn-danger btn-sm" onclick="removeSpecificRow(this)">Eliminar</button></td>
                `;
                tbody.appendChild(row);
                reindexRows(); // Reindexar después de agregar una fila
            } else {
                alert('Puedes agregar un máximo de 8 coordenadas.');
            }
        }

        function removeRow() {
            const rows = document.querySelectorAll('#coordinatesBody tr');
            if (rows.length > 3) {
                const tbody = document.getElementById('coordinatesBody');
                tbody.removeChild(tbody.lastChild);
                reindexRows(); // Reindexar después de eliminar una fila
            } else {
                alert('Debes ingresar al menos 3 coordenadas.');
            }
        }

        function removeSpecificRow(button) {
            const rows = document.querySelectorAll('#coordinatesBody tr');
            if (rows.length > 3) {
                const row = button.closest('tr');
                row.parentNode.removeChild(row);
                reindexRows(); // Reindexar después de eliminar una fila
            } else {
                alert('Debes ingresar al menos 3 coordenadas.');
            }
        }

        function reindexRows() {
            const rows = document.querySelectorAll('#coordinatesBody tr');
            rows.forEach((row, index) => {
                row.querySelector('td').textContent = index + 1;
            });
        }
      


        function saveData() {
            const rows = document.querySelectorAll('#coordinatesBody tr');
            const coordinates = [];
            rows.forEach(row => {
                const inputs = row.querySelectorAll('input');
                const x = inputs[0].value;
                const y = inputs[1].value;
                if (x && y) {
                    coordinates.push({ x, y });
                }
            });
            console.log(coordinates); // Enviar este array al back-end

            // Ejemplo de envío (reemplazar con la lógica real)
            // fetch('/tu-endpoint', {
            //     method: 'POST',
            //     headers: {
            //         'Content-Type': 'application/json'
            //     },
            //     body: JSON.stringify(coordinates)
            // }).then(response => response.json())
            //   .then(data => console.log(data))
            //   .catch(error => console.error('Error:', error));
        }

        
        let map;
        let polygon;
  
        // Inicializar el mapa
        function initMap(coordinates) {
          // Asegurarse de que hay al menos una coordenada válida
          if (coordinates.length === 0) {
            console.error("No hay coordenadas para inicializar el mapa.");
            return;
          }
  
          // Usar la primera coordenada para centrar el mapa
          const mapaCentro = { lat: coordinates[0].lat, lng: coordinates[0].lng };
  
          // Crear el mapa centrado en el primer punto de las coordenadas
          map = new google.maps.Map(document.getElementById('dataList43'), {
            zoom: 15,
            center: mapaCentro
          });
          document.getElementById('dataList43').style.display = 'block';
  
          console.log("Mapa inicializado correctamente.");
        }
  
        // Función para capturar las coordenadas y dibujar el polígono
        function dibujarCoordenada() {
            ;
          // Obtener las coordenadas de la tabla
          const rows = document.querySelectorAll('#coordinatesBody tr');
          const coordinates = [];
  
          rows.forEach(row => {
            const inputs = row.querySelectorAll('input');
            const lat = parseFloat(inputs[0].value);
            const lng = parseFloat(inputs[1].value);
            if (!isNaN(lat) && !isNaN(lng)) {
              coordinates.push({ lat: lat, lng: lng });
            }
          });
  
          // Verificar si ya existe un mapa, si no, inicializarlo
          if (!map && coordinates.length > 0) {
            initMap(coordinates); // Inicializa el mapa solo si no existe
          }
  
          // Si ya existe un polígono, eliminarlo antes de dibujar uno nuevo
          if (polygon) {
            polygon.setMap(null);
          }
  
          // Dibujar el polígono con las coordenadas capturadas
          if (coordinates.length > 0) {
            polygon = new google.maps.Polygon({
              paths: coordinates,
              strokeColor: '#FF0000',
              strokeOpacity: 0.8,
              strokeWeight: 2,
              fillColor: '#FF0000',
              fillOpacity: 0.35
            });
  
            // Mostrar el polígono en el mapa
            polygon.setMap(map);
  
            // Centrar el mapa en la primera coordenada ingresada
            map.setCenter(coordinates[0]);  // Centrar el mapa en la primera coordenada
          } else {
            console.warn("No se encontraron coordenadas válidas para dibujar.");
          }
        }
  

  

        // Inicializar la tabla con 3 coordenadas
        document.addEventListener('DOMContentLoaded', () => {
            for (let i = 1; i <= 3; i++) {
                addRow();
            }
        });
       

