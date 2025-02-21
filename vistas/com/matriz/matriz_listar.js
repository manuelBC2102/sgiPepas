var c = $('#env i').attr('class');
var buscar = false;
var actualizandoBusqueda = true;
var personaTipoVentana = 0;
$(document).ready(function () {
    $('[data-toggle="popover"]').popover({ html: true }).popover();
    ax.setSuccess("successPersonaListar");
    select2.iniciar();
    personaTipoVentana = getParameterByName('personaTipo');
    if (personaTipoVentana == 2) {
        cargarFormularioPersona(2, 'PN', 'vistas/com/persona/persona_natural_form.php');
    } else {

        colapsarBuscadorPersona();
        cambiarAnchoBusquedaDesplegable();
    }
    modificarAnchoTabla('datatable');
    listarMatriz();


});

function exportarReporteExcel(colapsa) {
    loaderShow();
    ax.setAccion("ExportarPersonaExcel");
    ax.consumir();
}

function imprimirDocumentoTicket(id) {
    window.open(URL_BASE + 'vistas/com/actaRetiro/formato.php?id=' + id);
}
// function configuracionesIniciales()
// {
//     ax.setAccion("configuracionesInicialesPersonaListar");
//     ax.consumir();
// }



var personaTipoIdG;
var valorPersonaTipoG;
var rutaG;

function cargarFormularioPersona(personaTipoId, valor_persona_tipo, ruta) {
    commonVars.personaId = 0;
    cargarDiv('#window', 'vistas/com/invitacion/invitacion_form.php', "Nueva ");
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


function listarMatriz() {
    debugger;
    var documentoF = select2.obtenerValor('cboTipoDocumentoF');
    var usuarioF = select2.obtenerValor('cboUsuarioF');
    var zonaF = select2.obtenerValor('cboZonaF');
    var plantaF = select2.obtenerValor('cboPlantaF');
    ax.setAccion("getDataGridMatriz");
    ax.addParamTmp("documentoF", documentoF);
    ax.addParamTmp("usuarioF", usuarioF);
    ax.addParamTmp("plantaF", plantaF);
    ax.addParamTmp("zonaF", zonaF);
    if (id == null) {

    } else {
        ax.addParamTmp("id", id);
    }
    $('#datatable').dataTable({
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
        },
        "processing": true,
        "serverSide": true,
        "bFilter": false,
        "ajax": ax.getAjaxDataTable(),
        "scrollX": true,
        "autoWidth": true,
        "columns": [
            { "data": "persona_planta_nombre" },
            { "data": "nombre_zona" },
            { "data": "documento_tipo" },
            { "data": "nivel_codigo", "className": "text-center" },
            { "data": "persona_aprobador_nombre" },
            { "data": "fecha_creacion", "className": "text-center" },
            { "data": "usuario_creacion" },
            {
                "data": "estado",
                "className": "text-center",
                "render": function (data, type, row) {
                    if (type === 'display') {
                        if (data == 1) {
                            return '<i class="fa fa-check" style="color:#5cb85c;"></i>';
                        } else {
                            return '<i class="fa fa-times" style="color:#cb2a2a;"></i>';
                        }
                    }
                    return data;
                }
            },
            //            {data: "estado", "width": "20",
            //                render: function (data, type, row) {
            //                    if (type === 'display') {
            //                        if (row.estado == 1)
            //                        {
            //                            return '<a onclick ="cambiarEstadoPersona(' + row.id + ')" ><b><i class="ion-checkmark-circled" style="color:#5cb85c"></i><b></a>';
            //                        } else {
            //                            return '<a onclick ="cambiarEstadoPersona(' + row.id + ')"><b><i class="ion-flash-off" style="color:#cb2a2a"></i><b></a>';
            //                        }
            //                    }
            //                    return data;
            //                },
            //                "orderable": true,
            //                "class": "alignCenter"
            //            },
            {
                data: "id",
                render: function (data, type, row) {
                    if (type === 'display') {

                        return '<a onclick="abrirModalMatriz(' + row.id + ')"><b><i class="fa fa-edit" style="color:#088a08;"></i><b></a>\n\
                                   <a onclick="confirmarDeleteSolicitud(' + row.id + ')"><b><i class="fa fa-trash-o" style="color:#cb2a2a;"></i><b></a>';


                    }
                    return data;
                },
                "orderable": true,
                "class": "alignCenter"
            },
        ],
        columnDefs: [

        ],
        //        "dom": '<"top">rt<"bottom"<"col-md-3"l><"col-md-9"p><"col-md-12"i>><"clear">',
        "order": [[1, "desc"]],
        destroy: true
    });


    nombres = null;
    codigo = null;
    tipoPersona = null;
    clasePersona = null;
    id = null;
    loaderClose();

    datosInicialesFiltros();
}

function openModal(id, token) {
    // Obtener el dominio actual
    var dominio = window.location.origin;
    var archivo = "/sgiLaVictoria/envio_notificacion.php"; // Cambia esto por el nombre del archivo que necesites
    var token = token; // Reemplaza esto con el token generado

    // Concatenar dominio, archivo y token
    var urlCompleta = dominio + archivo + "?token=" + token;

    // Mostrar el enlace en el modal
    document.getElementById("generatedLink").innerText = urlCompleta;
    document.getElementById("generatedLink").href = urlCompleta;

    // Abrir el modal
    $('#linkModal').modal('show');
}
function confirmarDeleteSolicitud(id) {
    debugger;
    BANDERA_ELIMINAR = false;
    swal({
        title: "Est\xe1s seguro?",
        text: "Eliminarás la invitación",
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
                listarMatriz();
                break;
            case 'deleteSolicitud':
                loaderClose();
                var error = response.data[0]['vout_exito'];
                if (error == 1) {
                    swal("Eliminado!", "Aprobador eliminado correctamente", "success");
                } else {
                    swal("Cancelado", response.data[0]['vout_mensaje'] + "No se pudo eliminar", "error");
                }
                bandera_eliminar = true;
                listarMatriz();
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
            case 'obtenerSelectPersonas':
                onResponseObtenerPersonaActivoXStringBusqueda(response.data, response.tag);
                break;
            case 'buscarCriteriosBusquedaSolicitud':
                onResponseBuscarCriteriosBusquedaSolicitud(response.data);
                loaderClose();
                break;
            case 'obtenerPersonaClaseAsociada':
                onResponseObtenerPersonaClaseAsociada(response.data);
                loaderClose();
                break;
            case 'datosInicialesModal':
                onResponseObtenerdatosInicialesModal(response.data);
                break;
                case 'datosInicialesFiltros':
                    onResponseObtenerdatosInicialesModal2(response.data);
                    break;

            case 'guardarAprobador':
                $('#registroModal').modal('hide');
                btnEnviar = document.getElementById('btnEnviar');
                btnEnviar.innerHTML = ' Guardar';
                btnEnviar.disabled = false;
                listarMatriz();
                loaderClose();
                break;
            case 'actualizarAprobador':
                $('#registroModal').modal('hide');
                btnEnviar = document.getElementById('btnEnviar');
                btnEnviar.innerHTML = ' Guardar';
                btnEnviar.disabled = false;
                listarMatriz();
                loaderClose();
                break;
        }
    } else {
        switch (response[PARAM_ACCION_NAME]) {
            case 'guardarAprobador':
                $('#registroModal').modal('hide');
                btnEnviar = document.getElementById('btnEnviar');
                btnEnviar.innerHTML = ' Guardar';
                btnEnviar.disabled = false;
                listarMatriz();
                loaderClose();
                break;
                case 'actualizarAprobador':
                    $('#registroModal').modal('hide');
                    btnEnviar = document.getElementById('btnEnviar');
                    btnEnviar.innerHTML = ' Guardar';
                    btnEnviar.disabled = false;
                    listarMatriz();
                    loaderClose();
                    break;
            case 'deleteSolicitud':
                loaderClose();
                swal("Cancelado", "No se pudo eliminar, esta en uso", "error");
        }
    }
}

function onResponseObtenerdatosInicialesModal(data) {
    debugger;

    let defaultOption = new Option("Todos", "0", true, true);
    $('#cboPlanta').append(defaultOption).trigger('change');
    $('#cboZona').append(defaultOption).trigger('change');


    select2.cargar("cboPlanta", data.plantas, "id", ["codigo_identificacion", "nombre_completo"]);
    select2.cargar("cboZona", data.zonas, "id", "nombre");
    select2.cargar("cboTipoDocumento", data.documentos, "id", "descripcion");
    select2.cargar("cboUsuario", data.usuarios, "id", ["nombre", "usuario"]);

    if (!isEmpty(data.matriz)) {
        llenarFormularioEditar(data.matriz);
    }

    $('#registroModal').modal('show');
    loaderClose();
}

function onResponseObtenerdatosInicialesModal2(data) {
    debugger;

    let defaultOption = new Option("Todos", "0", true, true);
    $('#cboPlanta').append(defaultOption).trigger('change');
    $('#cboZona').append(defaultOption).trigger('change');

    select2.cargar("cboPlantaF", data.plantas, "id", ["codigo_identificacion", "nombre_completo"]);
    select2.cargar("cboZonaF", data.zonas, "id", "nombre");
    select2.cargar("cboTipoDocumentoF", data.documentos, "id", "descripcion");
    select2.cargar("cboUsuarioF", data.usuarios, "id", ["nombre", "usuario"]);
    if (!isEmpty(data.matriz)) {
        llenarFormularioEditar(data.matriz);
    }

    loaderClose();
}
function llenarFormularioEditar(data) {
    // Obtener todas las opciones para Zona y Planta
    let opcionesZona = obtenerOpciones('cboZona');
    let opcionesPlanta = obtenerOpciones('cboPlanta');

    // Crear la opción por defecto "Todos"
    let defaultOption = new Option("Todos", "0", true, true);

    // Limpiar y agregar la opción por defecto a ambos selectores
    $('#cboZona').empty().append(opcionesZona).append(defaultOption).trigger('change');
    $('#cboPlanta').empty().append(opcionesPlanta).append(defaultOption.cloneNode(true)).trigger('change');

    // Asignar valor si no es nulo
    if (data[0]['zona_id'] != null) {
        select2.asignarValor('cboZona', data[0]['zona_id']);
    }

    if (data[0]['persona_planta_id'] != null) {
        select2.asignarValor('cboPlanta', data[0]['persona_planta_id']);
    }

    // Asignar otros valores directamente
    $("#txtComentario").val(data[0].comentario);
    $("#txtaprobacion").val(data[0].id);
    select2.asignarValor('cboTipoDocumento', data[0]['documento_tipo_id']);
    select2.asignarValor('cboUsuario', data[0]['usuario_aprobador_id']);
    select2.asignarValor('cboNivel', data[0]['nivel']);
}

function obtenerOpciones(selectorId) {
    // Obtener todas las opciones del selector original
    let opciones = $('#' + selectorId + ' option').clone();
    return opciones;
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



function buscarPersona(colapsa) {
    debugger;
    buscar = true;
    var cadena;
    cadena = obtenerDatosBusqueda();
    cadena = (!isEmpty(cadena) && cadena !== 0) ? cadena : "Todos";
    $('#idPopover').attr("data-content", cadena);
    $('[data-toggle="popover"]').popover('show');
    obtenerParametrosBusqueda();
    listarMatriz();
    if (colapsa === 1)
        colapsarBuscadorPersona();
}

var actualizandoBusquedaPersona = false;

function colapsarBuscadorPersona() {
    debugger;
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
    debugger;
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
function editarInvitacion(id) {
    debugger;
    loaderShow(null);
    commonVars.invitacionId = id;
    cargarDiv("#window", "vistas/com/invitacion/invitacion_form.php", "Editar Invitación ");
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
    debugger;
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
    listarMatriz();
}

function buscarCriteriosBusquedaSolicitud() {
    //    loaderShow();
    ax.setAccion("buscarCriteriosBusquedaSolicitud");
    ax.addParamTmp("busqueda", $('#txtBuscar').val());
    ax.consumir();
}

function onResponseBuscarCriteriosBusquedaSolicitud(data) {
    debugger;
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
var actualizandoBusqueda = true;
function colapsarBuscador() {
    if (actualizandoBusqueda) {
        actualizandoBusqueda = false;
        return;
    }
    if ($('#bg-info').hasClass('in')) {
        $('#bg-info').attr('aria-expanded', "false");
        $('#bg-info').attr('height', "0px");
        $('#bg-info').removeClass('in');
    } else {
        $('#bg-info').attr('aria-expanded', "false");
        $('#bg-info').removeAttr('height', "0px");
        $('#bg-info').addClass('in');
    }
}

function datosInicialesModal(id) {
    loaderShow();
    ax.setAccion("datosInicialesModal");
    ax.addParamTmp("idMatriz", id);
    ax.consumir();
}

function datosInicialesFiltros(id) {
    loaderShow();
    ax.setAccion("datosInicialesFiltros");
    ax.addParamTmp("idMatriz", id);
    ax.consumir();
}



function abrirModalMatriz(id) {
    debugger;
    // Limpiar los campos select2
    $('#cboTipoDocumento').val(null).trigger('change');
    $('#cboUsuario').val(null).trigger('change');
    $('#cboNivel').val(null).trigger('change');
    $('#cboZona').val(null).trigger('change');
    $('#cboPlanta').val(null).trigger('change');

    // Limpiar los campos de texto e imagen
    $('#secretImg').val('');
    $('#txtComentario').val('');
    $('#txtaprobacion').val('');
    $('#file').val('');
    $('#upload-file-info').val('');
    $("#upload-file-info").html('Ninguna imagen seleccionada');
    
    datosInicialesModal(id);
   

}

setTimeout(function () {
    $($("#cboClienteListado").data("select2").search).on('keyup', function (e) {

        if (e.keyCode === 13) {
            obtenerDataCombo('ClienteListado');
        }
    });

}, 1000);

function obtenerDataCombo(id) {
    let texto = $($("#cbo" + id).data("select2").search).val();


    if (isEmpty(texto)) {
        //        $.Notification.autoHideNotify('warning', 'top right', 'Validación', "Ingrese un texto para buscar");
        return;
    }

    $('#cbo' + id).select2('close');

    loaderShow();
    ax.setAccion("obtenerSelectPersonas");
    ax.addParamTmp("querySelect", texto);
    ax.setTag({ id: id, valor: texto });
    ax.consumir();
}


function onResponseObtenerPersonaActivoXStringBusqueda(data, dataCombo) {
    select2.cargarSeleccione("cbo" + dataCombo.id, data, "id", ["nombre", "codigo_identificacion"], "Todos");
    loaderClose();

    $('#cbo' + dataCombo.id).select2('open');
    $($("#cbo" + dataCombo.id).data("select2").search).val(dataCombo.valor);
    $($("#cbo" + dataCombo.id).data("select2").search).trigger('input');

    setTimeout(function () {
        $('.select2-results__option').trigger("mouseup");

        $($("#cbo" + dataCombo.id).data("select2").search).on('keyup', function (e) {
            if (e.keyCode === 13) {
                obtenerDataCombo(dataCombo.id);
            }
        });
    }, 500);
}


$("#file").change(function () {
    debugger;
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
    debugger;
    $('#secretImg').attr('value', e.target.result);
}

function guardarAprobador() {
    debugger;
    let btnEnviar = document.getElementById('btnEnviar');

    if (btnEnviar) {
        btnEnviar.disabled = true;
        btnEnviar.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Enviando...';
    }

    btnEnviar.disabled = true;
    btnEnviar.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Enviando...';
    var documento = select2.obtenerValor('cboTipoDocumento');
    var usuario = select2.obtenerValor('cboUsuario');
    var nivel = select2.obtenerValor('cboNivel');
    var zona = select2.obtenerValor('cboZona');
    var planta = select2.obtenerValor('cboPlanta');
    var file = $('#secretImg').val();
    var comentario = $('#txtComentario').val();
    var id = $('#txtaprobacion').val();
    loaderShow();
    if (id == '') {
        ax.setAccion("guardarAprobador");
    }
    else {
        ax.setAccion("actualizarAprobador");
        ax.addParamTmp("id", id);
    }
    ax.addParamTmp("documento", documento);
    ax.addParamTmp("usuario", usuario);
    ax.addParamTmp("zona", zona);
    ax.addParamTmp("planta", planta);
    ax.addParamTmp("file", file);
    ax.addParamTmp("nivel", nivel);
    ax.addParamTmp("comentario", comentario);
    ax.consumir();
}

