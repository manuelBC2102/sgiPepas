var c = $('#env i').attr('class');
var buscar = false;

var personaTipoVentana = 0;
$(document).ready(function () {
    $('[data-toggle="popover"]').popover({ html: true }).popover();
    ax.setSuccess("successPersonaListar");
    select2.iniciar();
    personaTipoVentana = getParameterByName('personaTipo');
    if (personaTipoVentana == 2) {
        cargarFormularioPersona(2, 'PN', 'vistas/com/persona/persona_natural_form.php');
    } else {
        configuracionesIniciales();
        colapsarBuscadorPersona();
        cambiarAnchoBusquedaDesplegable();
    }
    modificarAnchoTabla('datatable');
});

function exportarReporteExcel(colapsa) {
    loaderShow();
    ax.setAccion("ExportarPersonaExcel");
    ax.consumir();
}

function imprimirDocumentoTicket(id) {
    window.open(URL_BASE + 'vistas/com/actaRetiro/formato.php?id=' + id);
}
function configuracionesIniciales() {
    ax.setAccion("configuracionesInicialesPersonaListar");
    ax.consumir();
}

function onresponseConfiguraciones(data) {
    //    console.log(data);
    $('#listaPersonaTipo').empty();
    var perJuridica;
    var perNatural;
    var html;
    if (!isEmpty(data.persona_tipo)) {
        $('#cboTipoPersonaBusqueda').append('<option value="-1">Seleccionar tipo de persona</option>');
        $.each(data.persona_tipo, function (index, value) {
            /*
             * Para el buscador
             */
            $('#cboTipoPersonaBusqueda').append('<option value="' + value.id + '">' + value.descripcion + '</option>');

            //Para boton nuevo           
            //            $('#listaPersonaTipo').append('<li><a data-toggle="modal" data-target="#accordion-modal" onclick="cargarFormularioPersona(' + value.id + ',\'' + value.descripcion + '\',\'' + value.ruta + '\')">' + value.descripcion + '</a></li>');

            if (value.id == 2) {
                perNatural = value;
            } else {
                perJuridica = value;
            }
        });



        $('#listaPersonaTipo').append(html);
    }

    /*
     * Cargar dato en el select multiple de busqueda clase de persona
     */
    if (!isEmpty(data.persona_tipo)) {
        $.each(data.persona_clase, function (index, value) {
            $('#cboClasePersonaBusqueda').append('<option value="' + value.id + '">' + value.descripcion + '</option>');
        });
    }
    listarPersona();
}

var personaTipoIdG;
var valorPersonaTipoG;
var rutaG;

function cargarFormularioPersona(personaTipoId, valor_persona_tipo, ruta) {
    commonVars.personaId = 0;
    cargarDiv('#window', 'vistas/com/actaRetiro/acta_retiro_form.php', "Nueva ");
}

function onResponseObtenerPersonaClaseAsociada(data) {
    if (isEmpty(data)) {
        $.Notification.autoHideNotify('warning', 'top right', 'Validación', 'No tiene permisos para crear el tipo de persona.');
    } else {
        cargarFormulario(personaTipoIdG, valorPersonaTipoG, rutaG);
    }
}

function cargarFormulario(personaTipoId, valor_persona_tipo, ruta) {
    loaderShow(null);
    var dependiente = document.getElementById('hddIsDependiente').value;
    //    obtenerTitulo(dependiente);
    if (!isEmpty(valor_persona_tipo)) {
        valor_persona_tipo = valor_persona_tipo.toLowerCase();
    }
    if (dependiente == 0) {
        commonVars.personaId = 0;
        cargarDiv('#window', 'vistas/com/persona/persona_form.php', "Nueva Persona");
    } else {
        cargarDivModal('#respuesta', ruta, "Nueva " + valor_persona_tipo);
    }
}

var nombres;
var codigo;
var tipoPersona;
var clasePersona;
var id;


function listarPersona() {
   
    ax.setAccion("obtenerSolicitudesRetiroEntregaResultados");
    ax.consumir();
   
}

function ingresarRegistrarPesajes(id) {
    ;
    loaderShow(null);
    commonVars.personaId = id;
    cargarDiv("#window", "vistas/com/pesajePlanta/pesaje_planta_form.php", "Registrar Pesajes ");
}
function showModalActualizarPesajes(id) {
    loaderShow();
    llenarcombosolicitudes(id);
    // Abre el modal
    $('#modalActualizarPesajes').modal('show');
    // Guarda el ID de la fila para usarlo más tarde
    $('#hiddenRowId').val(id);
    loaderClose();
}

function actualizarPesajes() {

    var solicitudId = $('#cboTipoArchivo').val();
    commonVars.personaId = solicitudId;
    cargarDiv("#window", "vistas/com/pesajePlanta/pesaje_actualizar_planta.php", "Actualizar Pesajes ");
}


function llenarcombosolicitudes(id) {
    ax.setAccion("obtenerDataActa");
    ax.addParamTmp("acta", id);
    ax.consumir();
}


function confirmarDeleteSolicitud(id) {
    BANDERA_ELIMINAR = false;
    swal({
        title: "Est\xe1s seguro?",
        text: "Eliminarás el acta de retiro",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#33b86c",
        confirmButtonText: "Si,eliminar!",
        cancelButtonColor: '#d33',
        cancelButtonText: "No,cancelar !",
        closeOnConfirm: false,
        closeOnCancel: false
    }, function (isConfirm) {

        if (isConfirm) {
            loaderShow();
            deleteSolicitud(id);
        } else {
            if (BANDERA_ELIMINAR == false) {
                swal("Cancelado", "La eliminaci\xf3n fue cancelada", "error");
            }
        }
    });
}

function successPersonaListar(response) {
    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {

            case 'cambiarEstadoPersona':
                onResponseCambiarEstadoPersona(response.data);
                listarPersona();
                break;
            case 'deleteSolicitud':
                loaderClose();
                var error = response.data[0]['vout_exito'];
                if (error == 1) {
                    swal("Eliminado!", "Solicitud eliminada correctamente", "success");
                } else {
                    swal("Cancelado", response.data[0]['vout_mensaje'] + "No se pudo eliminar", "error");
                }
                bandera_eliminar = true;
                listarPersona();
                break;
            case 'configuracionesInicialesPersonaListar':
                onresponseConfiguraciones(response.data);
                break;
            case 'getAllEmpresa':
                onResponseGetAllEmpresas(response.data);
                break;
                break;
            case 'importPersona':
                $('#fileInfo').html('');
                $('#resultado').append(response.data);
                break;
            case 'ExportarPersonaExcel':
                loaderClose();
                location.href = URL_BASE + "util/formatos/lista_de_personas.xlsx";
                break;
            case 'buscarCriteriosBusquedaSolicitud':
                onResponseBuscarCriteriosBusquedaSolicitud(response.data);
                loaderClose();
                break;
            case 'obtenerDataActa':
                onResponsellenarcomboTipoArchivo(response.data);
                break;
            case 'obtenerPersonaClaseAsociada':
                onResponseObtenerPersonaClaseAsociada(response.data);
                loaderClose();
                break;
            case 'obtenerSolicitudesRetiroEntregaResultados':
                onResponseAjaxpGetDataGridSolicitudes(response.data);
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
        }
    } else {
        switch (response[PARAM_ACCION_NAME]) {
            case 'deleteSolicitud':
                loaderClose();
                swal("Cancelado", "No se pudo eliminar, esta en uso", "error");
            case 'obtenerDataActa':
                loaderClose();
                swal("Cancelado", "No se pudo obtener los datos de las solicitudes", "error");
                break;
        }
    }
}


function onResponseAjaxpGetDataGridSolicitudes(data) {
    $("#dataList").empty();
    var cuerpo_total = '';
    var cuerpo = '';
    var cabeza = '<table id="datatable" class="table table-responsive table-striped table-bordered"><thead>' +
        " <tr>" +
        "<th style='text-align:start;'>Fecha Entrega</th>" +
        "<th style='text-align:start;'>Usuario Solicitante</th>" +
        "<th style='text-align:start;'>Zona</th>" +
        "<th style='text-align:center;'>Vehículo</th>" +
        "<th style='text-align:center;'>Conductor</th>" +
        "<th style='text-align:center;'>Planta</th>" +
        "<th style='text-align:center;'>Estado</th>" +
        "<th style='text-align:center;' width=100px>Acciones</th>" +
        "</tr>" +
        "</thead>";
        ;
        if (!isEmpty(data)) {
            let iconoEstado = [{estado_actualizar: 1, color: "#cb2a2a", icono: "ion-flash-off"}, {estado_actualizar: 0, color: "#5cb85c", icono: "ion-checkmark-circled"}];
        
            $.each(data, function (index, item) {
 
                if (item.nivel == 1)
                    {
                        $accion= '<a onclick="aprobarSolicitudPlanta(' + item.id + ')"><b><i class="fa fa-check " style="color:blue;"></i><b></a>' ;
                    } else {
                        $accion=  '<a onclick="aprobarSolicitud(' + item.id + ')"><b><i class="fa fa-check " style="color:blue;"></i><b></a>' ;
                    }

                  cuerpo = "<tr>" +
                    "<td style='text-align:start;'>" + item.fecha_entrega + "</td>" +
        
                    "<td style='text-align:start;'>" + item.REINFO + "</td>" +
                    "<td style='text-align:start;'>" + item.zona + "</td>" +
                    "<td style='text-align:start;'>" + item.vehiculo + "</td>" +
                    "<td style='text-align:start;'>" + item.conductor + "</td>" +
                    "<td style='text-align:start;'>" + item.planta + "</td>" +
                    "<td style='text-align:start;'>" + item.estado + "</td>" +
                    "<td style='text-align:center;'>" +

                   '<a onclick="formularioResultados(' + item.id + ')"><b><i class="fa fa-list " style="color:blue;"></i><b></a>' +
                   
                    "</td>" +
                    "</tr>";
                  cuerpo_total = cuerpo_total + cuerpo;
    
            });
          }
    var pie = '</table>';
    var html = cabeza + cuerpo_total + pie;
    $("#dataList").append(html);
}


function formularioResultados(id) {
    loaderShow(null);
    commonVars.personaId = id;
    cargarDiv("#window", "vistas/com/entregaResultados/entrega_resultados_form.php", "Entrega resultados");
}

function onResponsellenarcomboTipoArchivo(data) {
    arraycomboTipoArchivo= data;
     
    $('#cboTipoArchivo').empty();
    
    $.each(data, function(index, item) {
        $('#cboTipoArchivo').append(new Option(item.id_estado, item.id));
 
    });
    $('#cboTipoArchivo').trigger('change'); // Actualizar select2
   
}

function deleteSolicitud(id) {
    loaderShow();
    ax.setAccion("deleteSolicitud");
    ax.addParamTmp("id", id);
    ax.consumir();
}

function cambiarEstadoPersona(id) {
    ax.setAccion("cambiarEstadoPersona");
    ax.addParamTmp("id", id);
    ax.consumir();
}
function onResponseCambiarEstadoPersona(data) {
    if (data[0]["vout_exito"] == 1) {
        $.Notification.autoHideNotify('success', 'top-right', '&Eacute;xito', data[0]["vout_mensaje"]);
    }
    else {
        $.Notification.autoHideNotify('warning', 'top right', 'Validación', data[0]["vout_mensaje"] + "No se puede cambiar de estado.");
    }
}
function obtenerTitulo(dependiente) {
    tituloGlobal = $("#titulo").text();
    var titulo = tituloGlobal;
    if (dependiente == 0) {
        $("#window").empty();
    }
    if (!isEmpty(titulo)) {
        titulo = titulo.toLowerCase();
    }
    return titulo;
}

function cargarListarPersonaCancelar() {
    loaderShow(null);
    cargarDiv('#window', 'vistas/com/persona/persona_listar.php', tituloGlobal);
}

function buscarPersona(colapsa) {
    ;
    buscar = true;
    var cadena;
    cadena = obtenerDatosBusqueda();
    cadena = (!isEmpty(cadena) && cadena !== 0) ? cadena : "Todos";
    $('#idPopover').attr("data-content", cadena);
    $('[data-toggle="popover"]').popover('show');
    obtenerParametrosBusqueda();
    listarPersona();
    if (colapsa === 1)
        colapsarBuscadorPersona();
}

var actualizandoBusquedaPersona = false;

function colapsarBuscadorPersona() {
    ;
    if (actualizandoBusquedaPersona) {
        actualizandoBusquedaPersona = false;
        return;
    }
    if ($('#bg-info').hasClass('in')) {
        $('#bg-info').attr('aria-expanded', "false");
        $('#bg-info').attr('height', "0px");
        $('#bg-info').removeClass('in');
    } else {
        $('#bg-info').attr('aria-expanded', "false");
        $('#bg-info').attr('height', "0px");
        $('#bg-info').addClass('in');
    }
}
function obtenerDatosBusqueda() {
    ;
    var cadena = "";
    var nombres = $("#txtNombresBusqueda").val();
    var codigo = $("#txtCodigoBusqueda").val();
    var tipoPersona = $("#cboTipoPersonaBusqueda").val();
    var clasePersona = $("#cboClasePersonaBusqueda").val();


    if (!isEmpty(codigo)) {
        cadena += StringNegrita("Cód. Id.: ");

        cadena += codigo;
        cadena += "<br>";
    }
    if (!isEmpty(nombres)) {
        cadena += StringNegrita("Nombre: ");

        cadena += nombres;
        cadena += "<br>";
    }
    if (tipoPersona != -1) {
        cadena += StringNegrita("Tipo de persona: ");

        cadena += select2.obtenerText('cboTipoPersonaBusqueda');
        cadena += "<br>";
    }
    if (!isEmpty(clasePersona)) {
        cadena += StringNegrita("Clase de persona: ");
        cadena += select2.obtenerTextMultiple('cboClasePersonaBusqueda');
        cadena += "<br>";
    }
    return cadena;
}
function editarPersona(id) {
    ;
    loaderShow(null);
    commonVars.personaId = id;
    cargarDiv("#window", "vistas/com/solicitudRetiro/solicitud_retiro_form.php", "Editar Solicitud ");
}
function actualizarBusquedaPersona() {
    actualizandoBusquedaPersona = true;
    //    var estadobuscador = $('#bg-info').attr("aria-expanded");
    //    if (estadobuscador == "false")
    //    {
    buscarPersona(0);
    //    }
}
/*IMPORTAR EXCEL*/
$(function () {
    $(":file").change(function () {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = imageIsLoaded;
            reader.readAsDataURL(this.files[0]);
            //            $fileupload = $('#file');
            //            $fileupload.replaceWith($fileupload.clone(true));
        }
    });
});

function imageIsLoaded(e) {
    $('#secretFile').attr('value', e.target.result);
    importPersona();
}

function validarFormularioCarga(documento) {
    var bandera = true;
    var espacio = /^\s+$/;

    if (documento === "" || documento === null || espacio.test(documento) || documento.length === 0) {
        $("#lblDoc").text("Documento es obligatorio").show();
        bandera = false;
    }
    return bandera;
}
function getAllEmpresaImport() {
    ax.setAccion("getAllEmpresa");
    ax.consumir();
}
/*FIN IMPORTAR EXCEL*/

function importPersona() {
    getAllEmpresaImport();
    $('#resultado').empty();
    $('#btnImportar').show();
    $('#btnSalirModal').empty();
    $('#btnSalirModal').append("<i class='fa ion-android-close'>&nbsp;&nbsp;</i>Cancelar");
    $("#cboEmpresa").attr("disabled", false);
    asignarValorSelect2('cboEmpresa', "");
    $('#modalPersona').modal('show');
}

function importar() {
    var file = document.getElementById('secretFile').value;
    var empresa = $('#cboEmpresa').val();

    if (isEmpty(empresa)) {
        $("msj_empresa").removeProp(".hidden");
        $("#msj_empresa").text("Seleccionar una empresa").show();
        return;
    }

    $('#resultado').empty();
    $('#btnImportar').hide();
    $('#btnSalirModal').empty();
    $('#btnSalirModal').append("<i class='fa ion-android-close'>&nbsp;&nbsp;</i>Salir");
    $("#cboEmpresa").attr("disabled", true);

    loaderShow(".modal-content");
    ax.setAccion("importPersona");
    ax.addParam("file", file);
    ax.addParam("empresa_id", empresa);
    ax.consumir();
}
function onResponseGetAllEmpresas(data) {

    if (!isEmpty(data)) {
        $('#cboEmpresa').empty();
        $.each(data, function (index, value) {
            $('#cboEmpresa').append('<option value="' + value.id + '">' + value.razon_social + '</option>');
        });
    }
    acciones.getEmpresa = true;
}

function asignarValorSelect2(id, valor) {
    $("#" + id).select2().select2("val", valor);
    $("#" + id).select2({ width: '100%' });
}

$('.dropdown-menu').click(function (e) {
    if (e.target.id != "btnBusqueda" && e.delegateTarget.id != "ulBuscadorDesplegable2" && e.delegateTarget.id != "listaEmpresa") {
        e.stopPropagation();
    }
});

$('#txtBuscar').keyup(function (e) {
    var bAbierto = $('#txtBuscar').attr("aria-expanded");

    if (!eval(bAbierto)) {
        $('#txtBuscar').dropdown('toggle');
    }

});

function cambiarAnchoBusquedaDesplegable() {
    var ancho = $("#divBuscador").width();
    $("#ulBuscadorDesplegable").width((ancho - 5) + "px");
    $("#ulBuscadorDesplegable2").width((ancho - 5) + "px");
}

function obtenerParametrosBusqueda() {
    nombres = $("#txtNombresBusqueda").val();
    codigo = $("#txtCodigoBusqueda").val();
    tipoPersona = $("#cboTipoPersonaBusqueda").val();
    clasePersona = $("#cboClasePersonaBusqueda").val();
}

function llenarParametrosBusqueda(nombresTxt, codigoTxt, idTxt, clasePersonaTxt) {
    ;
    var clasePersonaIds = [];
    if (!isEmpty(clasePersonaTxt)) {
        clasePersonaIds.push(clasePersonaTxt);
    }

    if (!isEmpty(codigoTxt)) {
        nombresTxt = null;
    }

    nombres = nombresTxt;
    codigo = codigoTxt;
    id = idTxt;
    clasePersona = clasePersonaIds;

    loaderShow();
    listarPersona();
}

function buscarCriteriosBusquedaSolicitud() {
    //    loaderShow();
    ax.setAccion("buscarCriteriosBusquedaSolicitud");
    ax.addParamTmp("busqueda", $('#txtBuscar').val());
    ax.consumir();
}

function onResponseBuscarCriteriosBusquedaSolicitud(data) {
    ;
    var dataPersona = data.dataPersona;
    var dataPersonaClase = data.dataPersonaClase;

    var html = '';
    $('#ulBuscadorDesplegable2').empty();
    if (!isEmpty(dataPersona)) {
        $.each(dataPersona, function (index, item) {
            html += '<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">';
            html += '<a onclick="llenarParametrosBusqueda(\'' + item.nombre + '\',\'' + item.codigo_identificacion + '\',' + item.id + ',' + null + ')" >';
            html += '<span class="col-md-1"><i class="ion-person"></i></span>';
            html += '<span class="col-md-2">';
            html += '<label style="color: #141719;">' + item.codigo_identificacion + '</label>';
            html += '</span>';
            html += '<span class="col-md-9">';
            html += '<label style="color: #141719;">' + item.nombre + '</label>';
            html += '</span></a>';
            html += '</div>';

        });
    }



    $("#ulBuscadorDesplegable2").append(html);

    //    console.log(dataPersona);
}

function limpiarBuscadores() {
    $('#txtCodigoBusqueda').val('');
    $('#txtNombresBusqueda').val('');

    select2.asignarValor('cboClasePersonaBusqueda', -1);
    select2.asignarValor('cboTipoPersonaBusqueda', -1);
}