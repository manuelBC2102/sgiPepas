$(document).ready(function () {
    criterioBusquedaDocumentos = [];
    cargarComponetentes();
    ax.setSuccess("onResponseProgramacionPagoListar");
    obtenerConfiguracionInicialListado();
    cambiarAnchoBusquedaDesplegable();

    $('#selectAll').on('change', function () {
        var isChecked = this.checked;
        $('input[name=checkselect]').prop('checked', isChecked);
    });
    $('#btn_agregar').click(function () {
        loaderShow('#modalDocumentos');
        var table = $('#dtDocumentos').DataTable();
        var filasSeleccionadas = table.rows(':has(input[name=checkselect]:checked)').data().toArray();
        var bandera_copia = true;
        if (filasSeleccionadas.length <= 0) {
            $.Notification.autoHideNotify('warning', 'top right', 'Validación', " Seleccione uno o más comprobantes, para realizar el proceso");
            bandera_copia = false;
            loaderClose();
        }

        if (bandera_copia) {
            ax.setAccion("registrarProgramacionPagos");
            ax.addParamTmp("filasSeleccionadas", filasSeleccionadas);
            ax.addParamTmp("tipo", select2.obtenerValor("cboTipo_operacion"));
            ax.addParamTmp("moneda", select2.obtenerValor("cboMoneda2"));
            ax.addParamTmp("fecha_programación", $('#fecha_programacion').val());
            ax.consumir();
        }

    });

    $('#cboTipo_operacion').change(function () {
        loaderShow();
        var tipo_operacion = select2.obtenerValor("cboTipo_operacion");
        if (tipo_operacion == 2) {
            $("#div_cboMoneda2").addClass("hidden");
            select2.asignarValor("cboMoneda2", '');
        } else {
            $("#div_cboMoneda2").removeClass("hidden");
            select2.asignarValor("cboMoneda2", 2);
        }
        obtenerDocumentosPagos();
    });
    $('#cboMoneda2').change(function () {
        loaderShow();
        obtenerDocumentosPagos();
    });
    $('#cboPersonaM').change(function () {
        loaderShow();
        obtenerDocumentosPagos();
    });
});

function onResponseProgramacionPagoListar(response) {
    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obtenerConfiguracionInicialListado':
                onResponseObtenerConfiguracionInicial(response.data);
                buscarDocumentos();
                loaderClose();
                break;
            case 'registrarProgramacionPagos':
                onResponseRegistrarProgramacionPagos(response.data);
                loaderClose();
                break;
            case 'visualizarProgramacion':
                onResponseVisualizarProgramacion(response.data);
                break;
            case 'generarTXTPagos':
                onResponseGenerarTXTPagos(response.data);
                break;
            case 'generarTXTPagosDetraccion':
                onResponseGenerarTXTPagos(response.data);
                break;
            case 'anularProgramacion':
                onResponseAnularProgramacion(response.data);
                break;
            case 'subirAdjunto':
                loaderClose();
                $("#modalAdjunto").modal('hide');
                $("#text_archivoAdjunto").val();
                $("#base64archivoAdjunto").val();
                swal({
                    title: "Documento pago actualizado",
                    text: response.data.mensaje,
                    type: "success",
                    showCancelButton: false,
                    confirmButtonColor: "#33b86c",
                    confirmButtonText: "Aceptar",
                    closeOnConfirm: false,
                    closeOnCancel: false
                });
                buscarDocumentos();
                break;                
        }
    }
}

function cargarComponetentes() {
    cargarTitulo("titulo", "");
    select2.iniciar();
    datePiker.iniciarPorClase('fecha');
}

function obtenerConfiguracionInicialListado() {
    ax.setAccion("obtenerConfiguracionInicialListado");
    ax.consumir();
}

function obtenerFechaActualBD() {
    var hoy = new Date();
    var dia = hoy.getDate();
    dia = (dia < 10) ? ('0' + dia) : dia;
    var mes = hoy.getMonth() + 1;
    mes = (mes < 10) ? ('0' + mes) : mes;
    var anio = hoy.getFullYear();

    return dia+ "/" + mes + "/" + anio ;
}

function buscarDocumentos() {
    loaderShow();
    ax.setAccion("obtenerPPagos");
    ax.addParamTmp("criterios", criterioBusquedaDocumentos);

    $('#datatable').dataTable({
        "language": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "Ning\xfAn dato disponible en esta tabla",
            "sInfo": "Mostrando del _START_ al _END_ de un total de _TOTAL_ registros",
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
        },
        "order": [[4, "desc"]],
        "processing": true,
        "serverSide": true,
        "bFilter": false,
        "ajax": ax.getAjaxDataTable(),
        "scrollX": true,
        "columns": [
            { "data": "tipo_operacion_descripcion", "class": "alignCenter" },
            { "data": "fecha_programacion", "class": "alignCenter" },
            { "data": "descripcion_moneda", "class": "alignCenter" },
            { "data": "monto_total", "class": "alignRight" },
            { "data": "fecha_creacion", "class": "alignCenter" },
            { "data": "estado", "class": "alignCenter" },
            { "data": "usuario", "class": "alignCenter" },
            { "data": "id", "class": "alignCenter" }

        ],
        columnDefs: [
            {
                "render": function (data, type, row) {
                    if (parseFloat(data).formatMoney(2, '.', ',') == '0.00') {
                        return '-';
                    } else {
                        return row.moneda_simbolo + ' ' + parseFloat(data).formatMoney(2, '.', ',');
                    }
                },
                "targets": 3
            },
            {
                "render": function (data, type, row) {
                    return (isEmpty(data)) ? '' : data.replace(" 00:00:00", "");
                },
                "targets": 1
            },
            {
                "render": function (data, type, row) {
                    if (obtenerFechaActualBD() == data.substring(0, 10)) {
                        muestraFecha = data.substring(12, data.length);
                    } else {
                        muestraFecha = data;
                    }
                    return muestraFecha;                
                },
                "targets": 4
            },
            {
                "render": function (data, type, row) {
                    return data == 1 ? "Registrado" : data == 2 ?"Pagado": "Anulado";
                },
                "targets": 5
            },
            {
                "render": function (data, type, row) {
                    var acciones = "";
                    acciones += "<a href='#' onclick='visualizarProgramacion(" + row.id + ")'><i class='fa fa-eye' style='color:blue;' title='Ver detalle programación'></i></a>&nbsp;";
                    if ((row.estado == 1 || row.estado == 2) && !isEmpty(row.url_pdf)) {
                        acciones += "<a href='#' onclick='anularProgramacion(" + row.id + ")'><i class='fa fa-ban' style='color:red;' title='Anular'></i></a>&nbsp;";
                        if (row.tipo_operacion == 1) {
                            acciones += "<a href='#' onclick='generarTXTPagos(" + row.id + ")'><i class='fa fa-file-text-o' style='color:green;' title='Generar txt Interbank'></i></a>&nbsp;";
                        } else {
                            acciones += "<a href='#' onclick='generarTXTPagosDetraccion(" + row.id + ")'><i class='fa fa-file-text-o' style='color:green;' title='Generar txt Detracciones'></i></a>&nbsp;";
                        }
                        acciones += "<a href='#' onclick='verPdf(\"" + row.url_pdf + "\")'><i class='fa fa-cloud-download' style='color:blue;' title='Descargar pago'></i></a>&nbsp;";
                    }else if (row.estado == 1 && isEmpty(row.url_pdf)) {
                        acciones += "<a href='#' onclick='subirArchivosAdjuntos(" + row.id + ")'><i class='fa fa-cloud-upload' style='color:blue;' title='Subir pago'></i></a>&nbsp;";
                        acciones += "<a href='#' onclick='anularProgramacion(" + row.id + ")'><i class='fa fa-ban' style='color:red;' title='Anular'></i></a>&nbsp;";
                        if (row.tipo_operacion == 1) {
                            acciones += "<a href='#' onclick='generarTXTPagos(" + row.id + ")'><i class='fa fa-file-text-o' style='color:green;' title='Generar txt Interbank'></i></a>&nbsp;";
                        } else {
                            acciones += "<a href='#' onclick='generarTXTPagosDetraccion(" + row.id + ")'><i class='fa fa-file-text-o' style='color:green;' title='Generar txt Detracciones'></i></a>&nbsp;";
                        }
                    }
                    return acciones;
                },
                "targets": 7
            }
        ],
        "dom": '<"top">rt<"bottom"<"col-md-3"l><"col-md-9"p><"col-md-12"i>><"clear">',
        destroy: true
    });
    loaderClose();
}

function actualizarBusqueda() {
    buscarDocumentos();
}

$('.dropdown-menu').click(function (e) {
    if (e.target.id != "btnBusqueda" && e.delegateTarget.id != "listaEmpresa") {
        e.stopPropagation();
    }
});

$('.fecha').datepicker({
    isRTL: false,
    format: 'dd/mm/yyyy',
    autoclose: true,
    language: 'es'
}).on('changeDate', function (ev) {
    setTimeout(function () {
        $("#spanBuscador").addClass('open');
    }, 5);
});

function onResponseObtenerConfiguracionInicial(data) {
    //desplegable de documentos
    select2.cargar("cboPersona", data.persona_activa, "id", ["nombre", "codigo_identificacion"]);
    select2.cargar("cboMoneda", data.moneda, "id", ["descripcion", "simbolo"]);

    //desplegable de ppago detalle
    select2.cargar("cboPersonaM", data.persona_activa, "id", ["nombre", "codigo_identificacion"]);
    select2.cargar("cboMoneda2", data.moneda, "id", ["descripcion", "simbolo"]);
    select2.asignarValor('cboMoneda2', 2);

    $('#fecha_programacion').val(datex.getNow1());
}

//here
function buscarPorCriterios() {
    var tipo_operacionPP = select2.obtenerValor('cboTipo_operacionPP');
    var fechaEmision = {
        inicio: $('#inicioFechaEmision').val(),
        fin: $('#finFechaEmision').val()
    };

    var monedaId = select2.obtenerValor('cboMoneda');
    llenarParametrosBusqueda(tipo_operacionPP, fechaEmision, monedaId);

    buscarDocumentos();
}


var criterioBusquedaDocumentos = {};

function llenarParametrosBusqueda(tipo_operacionPP, fechaEmision, monedaId) {
    criterioBusquedaDocumentos = {};
    criterioBusquedaDocumentos.tipo_operacionPP = tipo_operacionPP;
    criterioBusquedaDocumentos.fechaEmision = fechaEmision;
    criterioBusquedaDocumentos.monedaId = monedaId;
}

function cambiarAnchoBusquedaDesplegable() {
    var ancho = $("#divBuscador").width();
    $("#ulBuscadorDesplegable").width((ancho - 5) + "px");
}


function limpiarBuscadores() {
    $('#inicioFechaEmision').val('');
    $('#finFechaEmision').val('');

    select2.asignarValor('cboTipo_operacionPP', 0);
    select2.asignarValor('cboPersona', -1);
    select2.asignarValor('cboMoneda', -1);

    criterioBusquedaDocumentos = {};
}

function abrirModalDocumentos() {
    loaderShow('#dtDocumentoRelacion');
    //reinicializarDataTableDetalle();
    $('input[type=checkbox]').prop('checked', false);
    $("#modalDocumentos").modal('show');

    obtenerDocumentosPagos();
}

function obtenerDocumentosPagos() {
    ax.setAccion("obtenerDocumentosPagos");
    ax.addParamTmp("tipo_operacion", select2.obtenerValor("cboTipo_operacion"));
    ax.addParamTmp("persona_id", select2.obtenerValor("cboPersonaM"));
    ax.addParamTmp("moneda_id", select2.obtenerValor("cboMoneda2"));
    $("#dtDocumentos").DataTable().clear().destroy();
    $('input[type=checkbox]').prop('checked', false);

    $('#dtDocumentos').dataTable({
        "processing": true,
        "serverSide": true,
        "bFilter": false,
        "ajax": ax.getAjaxDataTable(),
        "autoWidth": true,
        "order": [[1, "desc"]],
        "columns": [
            {
                "data": "facturacion_proveedor_id",
                render: function (data, type, row) {
                    if (type === 'display') {
                        return '<input type="checkbox" name="checkselect" class="select-checkbox" value="' + row.persona_proveedor_id + '" >';
                    }
                    return data;
                },
                "orderable": false,
                "class": "alignCenter",
                "width": "5%"
            },
            { "data": "fecha_creacion", "width": "9%", "sClass": "alignCenter" },
            { "data": "persona", "width": "24%" },
            { "data": "serie_numero", "width": "10%", "sClass": "alignCenter" },
            { "data": "moneda_simbolo", "width": "4%", "sClass": "alignCenter" },
            { "data": "total", "width": "8%", "sClass": "alignRight" },
            { "data": "usuario", "width": "6%", "sClass": "alignCenter" },
            
            { "data": "persona_proveedor_id", "width": "6%", "sClass": "hidden" },
            { "data": "facturacion_proveedor_id", "width": "6%", "sClass": "hidden" },
            {
                data: "facturacion_proveedor_id",
                render: function (data, type, row) {
                    if (type === 'display') {
                       
                        return '<a onclick="imprimirDocumentoTicket2(' + row.facturacion_proveedor_id + ')"><b><i class="fa fa-folder-open" style="color:#black;"></i></b></a>';
                    }
                    return data;
                },
                orderable: false,
                class: "alignCenter",
                "width": "5%"
            },
        ],
        columnDefs: [
            {
                "render": function (data, type, row) {
                    return (isEmpty(data)) ? '' : data.replace(" 00:00:00", "");
                },
                "targets": [1]
            },
            {
                "render": function (data, type, row) {
                    return parseFloat(data).formatMoney(2, '.', ',');
                },
                "targets": 5
            },
            {
                "render": function (data, type, row) {
                    if (!isEmpty(data)) {
                        if (data.length > 60) {
                            data = data.substring(0, 60) + '...';
                        }
                    }
                    return data;
                },
                "targets": 2
            },
            {
                "render": function (data, type, row) {
                    if(select2.obtenerValor("cboTipo_operacion") == 1){
                        return data;
                    }else{
                        return "S/";
                    }
                },
                "targets": 4
            },            
        ],
        "dom": '<"top">rt<"bottom"<"col-md-3"l><"col-md-9"p><"col-md-12"i>><"clear">',
        destroy: true
    });
    loaderClose();

}
function reinicializarDataTableDetalle() {
    $('#dtDocumentos').DataTable({
        "scrollX": true,
        "paging": false,
        "info": false,
        "filter": false,
        "ordering": false,
        "autoWidth": true,
        "destroy": true
    });
}
function imprimirDocumentoTicket2(id) {
    window.open(URL_BASE + 'vistas/com/valorizacion/valorizacion2_pdf.php?id=' + id);
}

function onResponseRegistrarProgramacionPagos(data) {
    swal({
        title: data.tipo_mensaje == 1 ? "Confirmación" : "Advertencia",
        text: data.mensaje,
        type: data.tipo_mensaje == 1 ? "success" : "warning",
        html: true,
        showCancelButton: false,
        confirmButtonColor: "#33b86c",
        confirmButtonText: "Aceptar",
        closeOnConfirm: true,
        closeOnCancel: true
    });
    if (data.tipo_mensaje == 1) {
        $("#modalDocumentos").modal('hide');
        buscarDocumentos();
        select2.asignarValor('cboTipo_operacion', 1);
        select2.asignarValor('cboPersonaM', 0);
        select2.asignarValor('cboMoneda2', 2);
        obtenerDocumentosPagos();
    }
}

function onResponseVisualizarProgramacion(data) {
    if (!isEmpty(data)) {
        $("#modalProgramacionPagos").modal('show');
        $('#dtProgramacionPagos').dataTable({
            "processing": true,
            "data": data,
            "order": [[1, "desc"]],
            "columns": [
                { "data": "persona_nombre", "width": "24%", "sClass": "alignCenter" },
                { "data": "serie", "width": "9%", "sClass": "alignCenter" },
                { "data": "moneda_id", "width": "10%", "sClass": "alignCenter" },
                { "data": "monto_pagado", "width": "4%", "sClass": "alignRight" },
            ],
            columnDefs: [
                {
                    "render": function (data, type, row) {
                        var nombre_persona = row.ruc + " | " + row.persona_nombre;
                        if (!isEmpty(nombre_persona)) {
                            if (nombre_persona.length > 60) {
                                nombre_persona = nombre_persona.substring(0, 60) + '...';
                            }
                        }
                        return nombre_persona;
                    },
                    "targets": 0
                },
                {
                    "render": function (data, type, row) {
                        return row.serie + "-"+ row.correlativo;
                    },
                    "targets": 1
                },
                {
                    "render": function (data, type, row) {
                        if(data == 2){
                            return "S/";
                        }else{
                            return "$";
                        }
                    },
                    "targets": 2
                },
            ],
            "dom": '<"top">rt<"bottom"<"col-md-3"l><"col-md-9"p><"col-md-12"i>><"clear">',
            destroy: true
        });
        loaderClose();
    }
}
function generarTXTPagos(id) {
    loaderShow();
    ax.setAccion("generarTXTPagos");
    ax.addParamTmp("id", id);
    ax.consumir();
}

function generarTXTPagosDetraccion(id) {
    loaderShow();
    ax.setAccion("generarTXTPagosDetraccion");
    ax.addParamTmp("id", id);
    ax.consumir();
}

function onResponseGenerarTXTPagos(data) {
    var link = document.createElement("a");
    link.download = data.nombreArchivo + '.txt';
    link.href = data.archivo;
    link.click();
    loaderClose();
}

function anularProgramacion(id) {
    swal({
        title: "¿Está seguro que desea anular la programación?",
        text: "Una vez anulado, no se podrá revertir el registro!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#33b86c",
        confirmButtonText: "Si, anular!",
        cancelButtonColor: '#d33',
        cancelButtonText: "No, cancelar!",
        closeOnConfirm: true,
        closeOnCancel: true
    }, function (isConfirm) {
        if (isConfirm) {
            loaderShow();
            ax.setAccion("anularProgramacion");
            ax.addParamTmp("id", id);
            ax.consumir();
        }
    });
}
function onResponseAnularProgramacion(data) {
    swal({
        title: data.tipo_mensaje == 1 ? "Confirmación" : "Advertencia",
        text: data.mensaje,
        type: data.tipo_mensaje == 1 ? "success" : "warning",
        html: true,
        showCancelButton: false,
        confirmButtonColor: "#33b86c",
        confirmButtonText: "Aceptar",
        closeOnConfirm: true,
        closeOnCancel: true
    });
    if (data.tipo_mensaje == 1) {
        $("#modalDocumentos").modal('hide');
        buscarDocumentos();
        select2.asignarValor('cboTipo_operacionPP', 1);
        select2.asignarValor('cboPersonaM', 0);
        select2.asignarValor('cboMoneda2', 2);
        obtenerDocumentosPagos();
    }
}

function visualizarProgramacion(id) {
    loaderShow();
    ax.setAccion("visualizarProgramacion");
    ax.addParamTmp("id", id);
    ax.consumir();
}


function subirArchivosAdjuntos(id, movimientoId) {
    $("#modalAdjunto").modal('show');
    $("#indiceImagenAdjuntaBien").val(id);
}



//Adjuntar imagen de producto para solicitud de requerimiento
$("#fileInputAdjunto").change(function () {
    var fileType = this.files[0].type
    if (this.files && this.files[0]) {
        var validImageTypes = ["image/jpeg", "image/png", "image/gif", "image/bmp", "image/webp"];
        // Verificar si el archivo es una imagen válida
        if (validImageTypes.includes(this.files[0].type)) {
            var reader = new FileReader();
            reader.onload = function (e) {
                var img = new Image();
                img.src = e.target.result;
                img.onload = function () {
                    // Redimensionar la imagen si es necesario
                    var canvas = document.createElement("canvas");
                    var ctx = canvas.getContext("2d");
                    // Establecer un tamaño máximo (por ejemplo, 800px de ancho)
                    var maxWidth = 800;
                    var maxHeight = 800;
                    var width = img.width;
                    var height = img.height;

                    // Calcular las nuevas dimensiones manteniendo la relación de aspecto
                    if (width > height) {
                        if (width > maxWidth) {
                            height = Math.round((height *= maxWidth / width));
                            width = maxWidth;
                        }
                    } else {
                        if (height > maxHeight) {
                            width = Math.round((width *= maxHeight / height));
                            height = maxHeight;
                        }
                    }

                    // Establecer las dimensiones del canvas y dibujar la imagen redimensionada
                    canvas.width = width;
                    canvas.height = height;
                    ctx.drawImage(img, 0, 0, width, height);
                    // Convertir la imagen redimensionada a un formato de imagen (base64)
                    var resizedImage = canvas.toDataURL(fileType);
                    const fileSizeMB = base64ToSize(resizedImage);
                    if (fileSizeMB > 3) {
                        $("#error").html("EL archivo es mayor al permitido");
                        return false;
                    }
                    // var indice = $('#indiceImagenAdjuntaBien').val();
                    // var indexTemporal = -1;
                    // $.each(detalle, function (i, item) {
                    //     if (parseInt(item.index) === parseInt(indice)) {
                    //         indexTemporal = i;
                    //         return false;
                    //     }
                    // });

                    // if (indexTemporal != -1) {
                    //     detalle[indexTemporal].imagenAdjuntaBien = resizedImage;
                    // }
                    // Establecer la imagen redimensionada en el elemento de vista previa
                    $("#base64archivoAdjunto").val(resizedImage);
                    $("#error").hide();
                };
            };
            reader.readAsDataURL(this.files[0]); // Leer el archivo seleccionado
            $("#text_archivoAdjunto").html($("#fileInputAdjunto").val().slice(12));
            $("#nombrearchivoAdjunto").val($("#fileInputAdjunto").val().slice(12));
        } else if (this.files[0].type == "application/pdf") {
            var reader = new FileReader();
            reader.onload = function (e) {
                const fileSizeMB = base64ToSize(e.target.result);

                $("#base64archivoAdjunto").val(e.target.result);
            }
            reader.readAsDataURL(this.files[0]);
            $("#text_archivoAdjunto").html($("#fileInputAdjunto").val().slice(12));
            $("#nombrearchivoAdjunto").val($("#fileInputAdjunto").val().slice(12));
            $("#error").hide();
        } else {
            // Si no es una imagen válida, mostrar un mensaje de error y ocultar la vista previa
            $("#error").show();
        }

    }
});

function registrarImagenPdfBien() {
    if (!isEmpty($("#base64archivoAdjunto").val())) {
        loaderShow("#modalAdjunto");
        ax.setAccion("subirAdjunto");
        ax.addParamTmp("programacionId", $("#indiceImagenAdjuntaBien").val());
        ax.addParamTmp("base64archivoAdjunto", $("#base64archivoAdjunto").val());
        ax.consumir();
    } else {
        mostrarAdvertencia('Seleccione un producto');
        return;
    }

}

function base64ToSize(base64) {
    // Eliminar el encabezado de la cadena base64, si está presente
    const base64Data = base64.split(',')[1] || base64;
    // El tamaño en bytes de la cadena base64
    const byteSize = (base64Data.length * 3) / 4 - (base64Data.endsWith('==') ? 2 : base64Data.endsWith('=') ? 1 : 0);
    // Convertir el tamaño a MB
    const sizeInMB = byteSize / (1024 * 1024); // 1 MB = 1024 * 1024 bytes
    return sizeInMB;
}

function verPdf(nombreAdjunto){
    var partesNombreAdjunto = nombreAdjunto.split('.');
    var newWindow = window.open();

    if(partesNombreAdjunto[1] == "pdf"){
        newWindow.document.write('<html><body>');
        newWindow.document.write('<embed width="100%" height="100%" src="' + URL_BASE + "vistas/com/programacionPagos/documentoAdjunto/" + nombreAdjunto + '" type="application/pdf">');
        newWindow.document.write('</body></html>');
        newWindow.document.close();
    }else{
        newWindow.document.write('<html><body>');
        newWindow.document.write('<img src="' + URL_BASE + "vistas/com/programacionPagos/imagenAdjunto/" + nombreAdjunto + '">'); 
        newWindow.document.write('</body></html>');
        newWindow.document.close();
    }
}