var documento_tipo = document.getElementById("documento_tipo").value;

$(document).ready(function () {
    criterioBusquedaDocumentos = [];
    cargarComponetentes();
    ax.setSuccess("onResponseAprobacionConsolidado");
    obtenerConfiguracionInicialListado();
    cambiarAnchoBusquedaDesplegable();
});

function onResponseAprobacionConsolidado(response) {
    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obtenerConfiguracionInicialListado':
                onResponseObtenerConfiguracionInicial(response.data);
                buscarPorCriterios();
                loaderClose();
                break;
            case 'visualizarSolicitudRequerimiento':
                onResponsevisualizarSolicitudRequerimiento(response.data, response.tag);
                if(response.tag == "Por Aprobar"){
                    var btn_aprobar_rechazar = '<button type="button" class="btn btn-danger " data-dismiss="modal" onclick="rechazar()" id="btn_rechazar"><i class="fa fa-ban"></i> Rechazar</button>';
                    btn_aprobar_rechazar += '<button type="button" class="btn btn-primary " data-dismiss="modal" onclick="aprobar()" id="btn_aprobador"><i class="fa fa-check"></i> Aprobar</button>';
                    $("#div_btn_aprobar").html(btn_aprobar_rechazar);                        
                }else{
                    $("#div_btn_aprobar").html("");                        
                }
                break;
            case 'visualizarRequerimiento':
                onResponsevisualizarRequerimiento(response.data, response.tag);
                if(response.tag == "Por Aprobar"){
                    var btn_aprobar_rechazar = '<button type="button" class="btn btn-danger " data-dismiss="modal" onclick="rechazar()" id="btn_rechazar"><i class="fa fa-ban"></i> Rechazar</button>';
                    btn_aprobar_rechazar += '<button type="button" class="btn btn-primary " data-dismiss="modal" onclick="aprobar()" id="btn_aprobador"><i class="fa fa-check"></i> Aprobar</button>';
                    $("#div_btn_aprobar").html(btn_aprobar_rechazar);                        
                }else{
                    $("#div_btn_aprobar").html("");                        
                }
                break;
            case 'visualizarOrdenCompraServicio':
                onResponseVisualizarOrdenCompraServicio(response.data, response.tag);
                if(response.tag == "Por Aprobar"){
                    var btn_aprobar_rechazar = '<button type="button" class="btn btn-danger " data-dismiss="modal" onclick="rechazar()" id="btn_rechazar"><i class="fa fa-ban"></i> Rechazar</button>';
                    btn_aprobar_rechazar += '<button type="button" class="btn btn-primary " data-dismiss="modal" onclick="aprobar()" id="btn_aprobador"><i class="fa fa-check"></i> Aprobar</button>';
                    $("#div_btn_aprobar").html(btn_aprobar_rechazar);                        
                }else{
                    $("#div_btn_aprobar").html("");                        
                }
                break;                               
            case 'aprobarRequerimiento':
                swal({
                    title: "Aprobación correcta",
                    text: response.data.mensaje,
                    type: "success",
                    confirmButtonColor: "#33b86c",
                    confirmButtonText: "Aceptar",
                    timer: 2500
                });
                var btnEnviar = document.getElementsByClassName('confirm')[0];
                btnEnviar.disabled = false;
                btnEnviar.innerHTML = 'Si!';                
                buscarRequerimientos();
                break;
            case 'visualizarConsolidado':
                onResponsevisualizarConsolidado(response.data, response.tag);
                break;
            case 'aprobarConsolidado':
                swal({
                    title: "Aprobación correcta",
                    text: response.data.mensaje,
                    type: "success",
                    confirmButtonColor: "#33b86c",
                    confirmButtonText: "Aceptar",
                    timer: 2500
                });
                var btnEnviar = document.getElementsByClassName('confirm')[0];
                btnEnviar.disabled = false;
                btnEnviar.innerHTML = 'Si!';  
                $("#div_btn_aprobar").html(""); 
                buscarRequerimientos();
                break;
            case 'aprobarOrdenCompraServicio':
                swal({
                    title: "Aprobación correcta",
                    text: response.data.mensaje,
                    type: "success",
                    showCancelButton: false,
                    confirmButtonColor: "#33b86c",
                    confirmButtonText: "Aceptar",
                    timer: 2500
                });
                var btnEnviar = document.getElementsByClassName('confirm')[0];
                btnEnviar.disabled = false;
                btnEnviar.innerHTML = 'Si!';
                $("#div_btn_aprobar").html("");
                loaderClose();
                buscarRequerimientos();
                break;                
            case 'rechazar':
                swal({
                    title: "Rechazo correcto",
                    text: response.data.mensaje,
                    type: "success",
                    showCancelButton: false,
                    confirmButtonColor: "#33b86c",
                    confirmButtonText: "Aceptar",
                    closeOnConfirm: false,
                    closeOnCancel: false
                });
                $('#modalAnulacion').modal('hide');
                $("#div_btn_aprobar").html("");
                loaderClose();
                buscarRequerimientos();
                break;
            case 'obtenerDocumentoAdjuntoXDocumentoId':
                if(!isEmpty(response.data)){
                    lstDocumentoArchivos = response.data;
                }
                onResponseListarArchivosDocumento(response.data);
                loaderClose();
                break;                
        }
    }else{
        switch (response[PARAM_ACCION_NAME]) {
            case 'aprobarConsolidado':
                var btnEnviar = document.getElementsByClassName('confirm')[0];
                btnEnviar.disabled = false;
                buscarRequerimientos();
                swal("Mensaje", response.message, "error");
                break;
            case 'rechazar':
                loaderClose();
                buscarRequerimientos();
                break;
            case 'aprobarOrdenCompraServicio':
                var btnEnviar = document.getElementsByClassName('confirm')[0];
                btnEnviar.disabled = false;
                buscarRequerimientos();
                swal("Mensaje", response.message, "error");
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

    return anio + "-" + mes + "-" + dia;
}

function buscarRequerimientos() {
    loaderShow();
    ax.setAccion("obtenerRequerimientos");
    ax.addParamTmp("criterios", criterioBusquedaDocumentos);
    $('#datatableRequermiento').dataTable({
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
        "order": [[3, "desc"]],
        "processing": true,
        "serverSide": true,
        "bFilter": false,
        "ajax": ax.getAjaxDataTable(),
        "scrollX": true,
        "columns": [
            { "data": "serie_numero", "class": "alignCenter" },
            { "data": "tipo_requerimiento", "class": "alignCenter" },
            { "data": "area_descripcion", "class": "alignCenter" },
            { "data": "solicitante_nombre_completo", "class": "alignCenter" },
            { "data": "fecha_creacion", "class": "alignCenter" },
            { "data": "usuario_creacion", "class": "alignCenter" },
            { "data": "estado_descripcion", "class": "alignCenter" },
            { "data": "progreso", "class": "alignCenter" },
            { "data": "acciones", "class": "alignCenter" }

        ],
        columnDefs: [
            {
                "render": function (data, type, row) {
                    return "<strong>"+row.documento_tipo_descripcion +" </strong>"+ (isEmpty(data)? "": "<strong> : </strong> "+ data + (row.urgencia == "Si"? " (Urgencia)":"") );
                },
                "targets": 1
            },
            {
                "render": function (data, type, row) {
                    return (isEmpty(data)) ? '' : data.replace(" 00:00:00", "");
                },
                "targets": 3
            },
        ],
        "dom": '<"top">rt<"bottom"<"col-md-3"l><"col-md-9"p><"col-md-12"i>><"clear">',
        destroy: true
    });
    loaderClose();
}

function actualizarBusqueda() {
    buscarPorCriterios();
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

    $('#fecha_programacion').val(datex.getNow1());
    $('#cboArea').append('<option value=0>Todos</option>');
    if (!isEmpty(data.area)) {
        $.each(data.area, function (index, item) {
            $('#cboArea').append('<option value="' + item['id'] + '">' + item['descripcion'] + '</option>');
        });
    }
    $('#cboTipoRequerimiento').append('<option value=0>Todos</option>');
    if (!isEmpty(data.tipo_requerimiento)) {
        $.each(data.tipo_requerimiento, function (index, item) {
            $('#cboTipoRequerimiento').append('<option value="' + item['id'] + '">' + item['descripcion'] + '</option>');
        });
    }else{
        $('#liarea_tipo').hide();
    }
    select2.asignarValor('cboTipoRequerimiento', 0);
    if(!isEmpty(data.getarea)){
        select2.asignarValor('cboArea', data.getarea);
        $("#cboArea").attr('disabled', 'disabled');
    }else{
        select2.asignarValor('cboArea', 0);
    }

    if(documento_tipo == '282,284'){
        $("#liTipo").show();
    }
    fechasActuales();
}

//here
function buscarPorCriterios() {
    var fechaEmision = {
        inicio: $('#inicioFechaEmision').val(),
        fin: $('#finFechaEmision').val()
    };
    var area = select2.obtenerValor('cboArea');
    var requerimiento_tipo = select2.obtenerValor('cboTipoRequerimiento');
    var tipo = select2.obtenerValor('cboTipo');
    llenarParametrosBusqueda(fechaEmision, documento_tipo, area, requerimiento_tipo, tipo);

    buscarRequerimientos();
}

var criterioBusquedaDocumentos = {};

function llenarParametrosBusqueda(fechaEmision, documento_tipo, area, requerimiento_tipo, tipo) {
    criterioBusquedaDocumentos = {};
    criterioBusquedaDocumentos.fechaEmision = fechaEmision;
    criterioBusquedaDocumentos.documento_tipo = documento_tipo;
    criterioBusquedaDocumentos.area = area;
    criterioBusquedaDocumentos.requerimiento_tipo = requerimiento_tipo;
    criterioBusquedaDocumentos.tipo = tipo;
}

function cambiarAnchoBusquedaDesplegable() {
    var ancho = $("#divBuscador").width();
    $("#ulBuscadorDesplegable").width((ancho - 5) + "px");
}


function limpiarBuscadores() {
    $('#inicioFechaEmision').val('');
    $('#finFechaEmision').val('');

    criterioBusquedaDocumentos = {};
}

function onResponsevisualizarSolicitudRequerimiento(data, documento_estado) {
    var cont = 0;
    if(documento_estado == "Aprobado" || documento_estado == "Rechazado"){
        $("#div_btn_aprobar").html("");                        
    }

    cargarDataDocumento(data.dataDocumento);
    $("#tableRequerimiento").show();
    if (!isEmpty(data.detalle)) {
        $("#modalDetalle").modal('show');
        $('#dtmodalDetalleRequerimiento').dataTable({
            "processing": true,
            "ordering": false,
            "data": data.detalle,
            "order": [[0, "asc"]],
            "columns": [
                { "data": "movimiento_bien_id", "width": "5%", "sClass": "alignCenter" },
                { "data": "bien_descripcion", "width": "24%", "sClass": "alignLeft" },
                { "data": "cantidad", "width": "9%", "sClass": "alignCenter" },
                { "data": "unidad_medida_descripcion", "width": "10%", "sClass": "alignCenter" },
                { "data": "centro_costo_descripcion", "width": "10%", "sClass": "alignCenter" },
            ],
            columnDefs: [
                {
                    "render": function (data, type, row) {
                        cont = 1 + cont;
                        return cont;
                    },
                    "targets": 0
                },
                {
                    "render": function (data, type, row) {
                        var bien_descripcion = row.bien_codigo + " | " + row.bien_descripcion;
                        if (!isEmpty(bien_descripcion)) {
                            if (bien_descripcion.length > 60) {
                                bien_descripcion = bien_descripcion.substring(0, 60) + '...';
                            }
                        }
                        return bien_descripcion;
                    },
                    "targets": 1
                },
                {
                    "render": function (data, type, row) {
                        return devolverDosDecimales(data);
                    },
                    "targets": [2]
                }

            ],
            "dom": '<"top">rt<"bottom"<"col-md-3"l><"col-md-9"p><"col-md-12"i>><"clear">',
            destroy: true
        });
        loaderClose();
    }
}

function onResponsevisualizarRequerimiento(data, documento_estado) {
    var cont = 0;
    if(documento_estado == "Aprobado" || documento_estado == "Rechazado"){
        $("#div_btn_aprobar").html("");                        
    }

    cargarDataDocumento(data.dataDocumento);
    $("#tableRequerimientoArea").show();
    if (!isEmpty(data.detalle)) {
        $("#modalDetalle").modal('show');
        $('#dtmodalDetalleRequerimientoArea').dataTable({
            "processing": true,
            "ordering": false,
            "data": data.detalle,
            "order": [[0, "asc"]],
            "columns": [
                { "data": "movimiento_bien_id", "width": "5%", "sClass": "alignCenter" },
                { "data": "bien_descripcion", "width": "24%", "sClass": "alignLeft" },
                { "data": "cantidad", "width": "9%", "sClass": "alignCenter" },
                { "data": "cantidad_solicitada", "width": "9%", "sClass": "alignCenter" },
                { "data": "cantidad_solicitada", "width": "9%", "sClass": "alignCenter" },
                { "data": "unidad_medida_descripcion", "width": "10%", "sClass": "alignCenter" },
                { "data": "es_compra", "width": "10%", "sClass": "alignCenter" },
            ],
            columnDefs: [
                {
                    "render": function (data, type, row) {
                        cont = 1 + cont;
                        return cont;
                    },
                    "targets": 0
                },
                {
                    "render": function (data, type, row) {
                        var bien_descripcion = row.bien_codigo + " | " + row.bien_descripcion;
                        if (!isEmpty(bien_descripcion)) {
                            if (bien_descripcion.length > 60) {
                                bien_descripcion = bien_descripcion.substring(0, 60) + '...';
                            }
                        }
                        return bien_descripcion;
                    },
                    "targets": 1
                },
                {
                    "render": function (data, type, row) {
                        return devolverDosDecimales(data - row.cantidad);
                    },
                    "targets": 3
                },
                {
                    "render": function (data, type, row) {
                        return devolverDosDecimales(data);
                    },
                    "targets": [2,4]
                },
                {
                    "render": function (data, type, row) {
                        return data == 1?"Si":"No";
                    },
                    "targets": 6
                }

            ],
            "dom": '<"top">rt<"bottom"<"col-md-3"l><"col-md-9"p><"col-md-12"i>><"clear">',
            destroy: true
        });
        loaderClose();
    }
}

function onResponsevisualizarConsolidado(data, documento_estado) {
    var cont = 0;

    $('#selectPostor1').prop('checked', false);
    $('#selectPostor2').prop('checked', false);
    $('#selectPostor3').prop('checked', false);

    if(documento_estado == "Rechazado" || documento_estado == "Registrado" || documento_estado == "Aprobado"){
        $("#div_btn_aprobar").html("");
        $("#selectPostor1").prop("disabled", true);
        $("#selectPostor2").prop("disabled", true);
        $("#selectPostor3").prop("disabled", true);
    }else{
        $("#selectPostor1").prop("disabled", false);
        $("#selectPostor2").prop("disabled", false);
        $("#selectPostor3").prop("disabled", false);
    }
    cargarDataDocumento(data.dataDocumento);
    $("#tableConsolidado").show();
    if (!isEmpty(data.detalle)) {
        $("#modalDetalle").modal('show');
        $('#dtmodalDetalleConsolidado').dataTable({
            "processing": true,
            "ordering": false,
            "data": data.detalle,
            "order": [[0, "asc"]],
            "columns": [
                { "data": "movimiento_bien_id", "width": "5%", "sClass": "alignCenter" },
                { "data": "bien_descripcion", "width": "24%", "sClass": "alignLeft" },
                { "data": "cantidad", "width": "9%", "sClass": "alignCenter" },
                { "data": "unidad_medida_descripcion", "width": "10%", "sClass": "alignCenter" },
                { "data": "precio_postor1", "width": "4%", "sClass": "alignRight" },
                { "data": "subTotal_precio_postor1", "width": "4%", "sClass": "alignRight" },
                { "data": "precio_postor2", "width": "4%", "sClass": "alignRight" },
                { "data": "subTotal_precio_postor2", "width": "4%", "sClass": "alignRight" },
                { "data": "precio_postor3", "width": "4%", "sClass": "alignRight" },
                { "data": "subTotal_precio_postor3", "width": "4%", "sClass": "alignRight" },
            ],
            columnDefs: [
                {
                    "render": function (data, type, row) {
                        cont = 1 + cont;

                        switch (parseInt(row.postor_ganador_id)) {
                            case 1:
                                $('#selectPostor1').prop('checked', true);
                                break;
                            case 2:
                                $('#selectPostor2').prop('checked', true);
                                break;
                            case 3:
                                $('#selectPostor3').prop('checked', true);
                                break;
                        }
                        return cont;
                    },
                    "targets": 0
                },
                {
                    "render": function (data, type, row) {
                        var bien_descripcion = row.bien_codigo + " | " + row.bien_descripcion;
                        if (!isEmpty(bien_descripcion)) {
                            if (bien_descripcion.length > 60) {
                                bien_descripcion = bien_descripcion.substring(0, 60) + '...';
                            }
                        }
                        return bien_descripcion;
                    },
                    "targets": 1
                },
                {
                    "render": function (data, type, row) {
                        return devolverDosDecimales(data);
                    },
                    "targets": [2, 4, 5, 6, 7, 8, 9]
                }

            ],
            "dom": '<"top">rt<"bottom"<"col-md-3"l><"col-md-9"p><"col-md-12"i>><"clear">',
            destroy: true,
            footerCallback: function (row, data, start, end, display) {
                if (!isEmpty(data)) {
                    var api = this.api(), data;

                    var total1 = api.column(5).data().reduce(function (a, b) {
                        return parseFloat(a) + parseFloat(b);
                    }, 0);
                    $(api.column(5).footer()).html(total1.toFixed(2));

                    var total2 = api.column(7).data().reduce(function (a, b) {
                        return parseFloat(a) + parseFloat(b);
                    }, 0);
                    $(api.column(7).footer()).html(total2.toFixed(2));

                    var total3 = api.column(9).data().reduce(function (a, b) {
                        return parseFloat(a) + parseFloat(b);
                    }, 0);
                    $(api.column(9).footer()).html(total3.toFixed(2));
                }
            }
        });
        loaderClose();
    }
}

function onResponseVisualizarOrdenCompraServicio(data, documento_estado) {
    var cont = 0;
    if(documento_estado == "Aprobado" || documento_estado == "Rechazado"){
        $("#div_btn_aprobar").html("");                        
    }

    cargarDataDocumento(data.dataDocumento);
    $("#tableOrdenCompraServicio").show();
    if (!isEmpty(data.detalle)) {
        $("#modalDetalle").modal('show');
        $('#dtmodalDetalletableOrdenCompraServicio').dataTable({
            "processing": true,
            "ordering": false,
            "data": data.detalle,
            "order": [[0, "asc"]],
            "columns": [
                { "data": "movimiento_bien_id", "width": "5%", "sClass": "alignCenter" },
                { "data": "bien_descripcion", "width": "24%", "sClass": "alignLeft" },
                { "data": "unidad_medida_descripcion", "width": "10%", "sClass": "alignCenter" },
                { "data": "cantidad", "width": "9%", "sClass": "alignCenter" },
                { "data": "valor_monetario", "width": "9%", "sClass": "alignCenter" },
                { "data": "sub_total", "width": "9%", "sClass": "alignCenter" },
            ],
            columnDefs: [
                {
                    "render": function (data, type, row) {
                        cont = 1 + cont;
                        return cont;
                    },
                    "targets": 0
                },
                {
                    "render": function (data, type, row) {
                        var bien_descripcion = row.bien_codigo + " | " + row.bien_descripcion;
                        if (!isEmpty(bien_descripcion)) {
                            if (bien_descripcion.length > 60) {
                                bien_descripcion = bien_descripcion.substring(0, 60) + '...';
                            }
                        }
                        return bien_descripcion;
                    },
                    "targets": 1
                },
                {
                    "render": function (data, type, row) {
                        return devolverDosDecimales(data);
                    },
                    "targets": [3,4,5]
                }

            ],
            "dom": '<"top">rt<"bottom"<"col-md-3"l><"col-md-9"p><"col-md-12"i>><"clear">',
            destroy: true
        });
        loaderClose();
    }
}

var documentoTipoId = null;
var documento_tipo_descripcionText = null;
function visualizar(id, movimientoId, documento_estado_id, documento_estado, documento_tipo_id, documento_tipo_descripcion) {
    $("#visualizarDocumentoId").val(id);
    $("#btn_rechazar").val(id);
    documento_tipo_descripcionText = documento_tipo_descripcion;
    loaderShow();
    documentoTipoId = documento_tipo_id;
    if(documento_tipo_id == 280){ //Solicitud Requerimiento
        ax.setAccion("visualizarSolicitudRequerimiento");
        ax.setTag(documento_estado);
    }else if(documento_tipo_id == 283){ //Consolidar Requerimientos Area
        ax.setAccion("visualizarRequerimiento");
        ax.setTag(documento_estado);
    }else if(documento_tipo_id == 281){ //Consolidar Requerimientos
        ax.setAccion("visualizarConsolidado");
        ax.setTag(documento_estado);
    }else if(documento_tipo_id == 282 || documento_tipo_id == 284){ //Orden de Compra o Servicio
        ax.setAccion("visualizarOrdenCompraServicio");
        ax.setTag(documento_estado);        
    }
    ax.addParamTmp("id", id);
    ax.addParamTmp("movimientoId", movimientoId);
    ax.consumir();
}

function devolverDosDecimales(num) {
    return redondearNumero(num).toFixed(2);
}

function cargarDataDocumento(data, documento_estado_id) {
    $("#formularioDetalleDocumento").empty();

    if (!isEmpty(data)) {
        $('#nombreDocumentoTipo').html(data[0]['nombre_documento']);
        // Mostraremos la data en filas de dos columnas
        $.each(data, function (index, item) {

            appendFormDetalle('</div>');
            appendFormDetalle('<div class="row">');

            var html = '';
            if (!isEmpty(item.valor)) {
                html = '<div class="form-group col-md-12"><div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">' +
                    '<label>' + item.descripcion + '</label>' +
                    '</div><div class="col-lg-8 col-md-8 col-sm-6 col-xs-6">';
            } else {
                if (item.descripcion == 'Postor N° 1') {
                    $("#selectPostor1").prop("disabled", true);
                } else if (item.descripcion == 'Postor N° 2') {
                    $("#selectPostor2").prop("disabled", true);
                } else if (item.descripcion == 'Postor N° 3') {
                    $("#selectPostor3").prop("disabled", true);
                }
            }

            var valor = quitarNULL(item.valor);

            if (!isEmpty(valor)) {
                switch (parseInt(item.tipo)) {
                    case 1:
                        valor = formatearCantidad(valor);
                        break;
                    case 3:
                        valor = fechaArmada(valor);
                        break;
                    case 9:
                    case 10:
                    case 11:
                        valor = fechaArmada(valor);
                        break;
                    case 14:
                    case 15:
                    case 16:
                        valor = formatearNumero(valor);
                        break;
                }
            }

            html += '' + valor + '';

            html += '</div></div>';
            appendFormDetalle(html);
        });
        appendFormDetalle('</div>');
    }
}

function appendFormDetalle(html) {
    $("#formularioDetalleDocumento").append(html);
}

function fechaArmada(valor) {
    var fecha = separarFecha(valor);

    return fecha.dia + "/" + fecha.mes + "/" + fecha.anio;
}

function aprobar() {
    id = $("#visualizarDocumentoId").val();
    if(documentoTipoId == 280 || documentoTipoId == 284 || documentoTipoId == 283){ //Requerimiento
        swal({
            title: " ¿Desea continuar?",
            text: "Se procede aprobar "+ documento_tipo_descripcionText,
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#33b86c",
            confirmButtonText: "Si!",
            cancelButtonColor: '#d33',
            cancelButtonText: "No!",
            closeOnConfirm: false,
            closeOnCancel: true
        }, function (isConfirm) {
            if (isConfirm) {
                loaderShow();
                var btnEnviar = document.getElementsByClassName('confirm')[0];
                btnEnviar.disabled = true;
                btnEnviar.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Enviando...';
                ax.setAccion("aprobarRequerimiento");
                ax.addParamTmp("id", id);
                ax.consumir();
            } else {
                loaderClose();
            }
        });
    }else if(documentoTipoId == 281){ // Consolidado RQ
        var checked1 = $('#selectPostor1').is(":checked");
        var checked2 = $('#selectPostor2').is(":checked");
        var checked3 = $('#selectPostor3').is(":checked");
        var cantidad = seleccionados = $('input:checkbox:checked').length;
    
        if (cantidad == 0) {
            mostrarAdvertencia('Debe seleccionar una cotización');
            return;
        } else if (cantidad > 0 && cantidad <= 1) {
            swal({
                title: " ¿Desea continuar?",
                text: "Se procede aprobar el consolidado de requerimientos",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#33b86c",
                confirmButtonText: "Si!",
                cancelButtonColor: '#d33',
                cancelButtonText: "No!",
                closeOnConfirm: false,
                closeOnCancel: true
            }, function (isConfirm) {
                if (isConfirm) {
                    loaderShow();
                    var btnEnviar = document.getElementsByClassName('confirm')[0];
                    btnEnviar.disabled = true;
                    btnEnviar.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Enviando...';
                    ax.setAccion("aprobarConsolidado");
                    ax.addParamTmp("id", id);
                    ax.addParamTmp("checked1", checked1);
                    ax.addParamTmp("checked2", checked2);
                    ax.addParamTmp("checked3", checked3);
                    ax.consumir();
                } else {
                    loaderClose();
                }
            });
        } else {
            mostrarAdvertencia('Debe seleccionar solo una cotización');
            loaderClose();
            return;
        }
    }else if(documentoTipoId == 282){ // Orden de compra o Servicio
        swal({
            title: " ¿Desea continuar?",
            text: "Se procede aprobar la " + documento_tipo_descripcionText,
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#33b86c",
            confirmButtonText: "Si!",
            cancelButtonColor: '#d33',
            cancelButtonText: "No!",
            closeOnConfirm: false,
            closeOnCancel: true
        }, function (isConfirm) {
            if (isConfirm) {
                loaderShow();
                var btnEnviar = document.getElementsByClassName('confirm')[0];
                btnEnviar.disabled = true;
                btnEnviar.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Enviando...';
                ax.setAccion("aprobarOrdenCompraServicio");
                ax.addParamTmp("id", id);
                ax.consumir();
            } else {
                loaderClose();
            }
        });
    }
}

var documentoId;
function rechazar() {
    id = $("#btn_rechazar").val();
    loaderShow();
    swal({
        title: " ¿Desea continuar?",
        text: "Se va a rechazar el consolidado",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#33b86c",
        confirmButtonText: "Si, rechazar!",
        cancelButtonColor: '#d33',
        cancelButtonText: "No, cancelar !",
    }, function (isConfirm) {
        if (isConfirm) {
            $('#tituloModalAnulacion').html("Rechazar Consolidado ");
            $('#txtMotivoRechazo').val('');
            $('#modalAnulacion').modal('show');
            documentoId = id;
        } else {
            loaderClose();
        }
    });
}


function rechazarComentario() {
    var motivoRechazo = $('#txtMotivoRechazo').val();
    motivoRechazo = motivoRechazo.trim();

    if (isEmpty(motivoRechazo)) {
        mostrarAdvertencia('Ingrese motivo de rechazo');
        return;
    } else {
        loaderShow('#modalAnulacion');
        ax.setAccion("rechazar");
        ax.addParamTmp("documentoId", documentoId);
        ax.addParamTmp("motivoRechazo", motivoRechazo);
        ax.consumir();
    }
}

function limpiarFormularioRechazo(){
    $('#txtMotivoRechazo').val("");
    loaderClose();
}

function fechasActuales(){
    var fechaActual = new Date();
    
    // Formatear la fecha en formato dd/mm/yyyy
    var dia = ('0' + fechaActual.getDate()).slice(-2);
    var mes = ('0' + (fechaActual.getMonth() + 1)).slice(-2);
    var anio = fechaActual.getFullYear();
    var fechaFormateada = dia + '/' + mes + '/' + anio;

    // Colocar la fecha actual en el campo "finFechaEmision"
    $('#finFechaEmision').val(fechaFormateada);

    // Calcular la fecha de hace un mes
    fechaActual.setMonth(fechaActual.getMonth() - 1);
    var diaInicio = ('0' + fechaActual.getDate()).slice(-2);
    var mesInicio = ('0' + (fechaActual.getMonth() + 1)).slice(-2);
    var anioInicio = fechaActual.getFullYear();
    var fechaInicioFormateada = diaInicio + '/' + mesInicio + '/' + anioInicio;

    // Colocar la fecha de hace un mes en el campo "inicioFechaEmision"
    $('#inicioFechaEmision').val(fechaInicioFormateada);
}

var lstDocumentoArchivos = [];
function archivosAdjuntos(id, movimientoId) {
    loaderShow();
    $("#dataList2").empty();
    $("#modalDetalleArchivos").modal('show');
    $("#btn_agregarActualizar").val(id);
    
    ax.setAccion("obtenerDocumentoAdjuntoXDocumentoId");
    ax.addParamTmp("documentoId", id);
    ax.consumir();
}

function onResponseListarArchivosDocumento(data) {

    $("#dataList2").empty();
    var cuerpo_total = "";
    var cuerpo = "";
    var cabeza = "<table id='datatable3' class='table table-striped table-bordered'>"
        + "<thead>"
        + "<tr>"
        + "<th style='text-align:center; vertical-align: middle; width:8%'>#</th>"
        + "<th style='text-align:center; vertical-align: middle;'>Tipo Archivo</th>"
        + "<th style='text-align:center; vertical-align: middle;'>Nombre</th>"
        + "<th style='text-align:center; vertical-align: middle; width:15%'>Acciones</th>"
        + "</tr>"
        + "</thead>";
    if (!isEmpty(data)) {
        $.each(data, function (index, item) {
            if (!item.id.match(/t/g)) {
                lstDocumentoArchivos[index]["data"] = "util/uploads/documentoAdjunto/" + item.nombre;
            }

            cuerpo = "<tr>"
                + "<td style='text-align:center;'>" + (index + 1) + "</td>"
                + "<td style='text-align:center;'>" + item.tipo_archivo + "</td>"
                + "<td style='text-align:center;'>" + item.archivo + "</td>";

            cuerpo += "<td style='text-align:center;'>"
                + "<a href='" + item.data + "' download='" + item.archivo + "' target='_blank'><i class='fa fa-cloud-download' style='color:#1ca8dd;'></i></a>&nbsp;\n"
                // + "<a href='#' onclick='eliminarDocumento(\"" + item.id + "\")'><i class='fa fa-trash-o' style='color:#cb2a2a;'></i></a>&nbsp;\n";
            cuerpo += "</td>"
                + "</tr>";
            cuerpo_total += cuerpo;
        });
    }
    var pie = '</table>';
    var html = cabeza + cuerpo_total + pie;
    $("#dataList2").append(html);
    $("#datatable3").DataTable();
}