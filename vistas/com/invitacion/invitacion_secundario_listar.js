var c = $('#env i').attr('class');
var buscar = false;

var personaTipoVentana=0;
$(document).ready(function () {
    $('[data-toggle="popover"]').popover({html: true}).popover();
    ax.setSuccess("successPersonaListar");
    select2.iniciar();
    personaTipoVentana = getParameterByName('personaTipo');
    if(personaTipoVentana==2){
        cargarFormularioPersona(2,'PN','vistas/com/persona/persona_natural_form.php');
    }else{    
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
function configuracionesIniciales()
{
    ax.setAccion("configuracionesInicialesPersonaListar");
    ax.consumir();
}

function onresponseConfiguraciones(data)
{
//    console.log(data);
    $('#listaPersonaTipo').empty();
    var perJuridica;
    var perNatural;
    var html;
    if (!isEmpty(data.persona_tipo))
    {
        $('#cboTipoPersonaBusqueda').append('<option value="-1">Seleccionar tipo de persona</option>');
        $.each(data.persona_tipo, function (index, value) {
            /*
             * Para el buscador
             */
            $('#cboTipoPersonaBusqueda').append('<option value="' + value.id + '">' + value.descripcion + '</option>');
            
            //Para boton nuevo           
//            $('#listaPersonaTipo').append('<li><a data-toggle="modal" data-target="#accordion-modal" onclick="cargarFormularioPersona(' + value.id + ',\'' + value.descripcion + '\',\'' + value.ruta + '\')">' + value.descripcion + '</a></li>');
            
            if(value.id==2){
                perNatural=value;
            }else{
                perJuridica=value;
            }
        });
        
        html='<div class="input-group-btn dropdown">'+
                '<button type="button" class="btn btn-info"  onclick="cargarFormularioPersona()"><i class=" fa fa-plus-square-o"></i>&nbsp; Nueva invitación </button>'+
                // '<button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="caret"></i></button>'+
            '</div>';
        
         $('#listaPersonaTipo').append(html);
    }

    /*
     * Cargar dato en el select multiple de busqueda clase de persona
     */
    if (!isEmpty(data.persona_tipo))
    {
        $.each(data.persona_clase, function (index, value) {
            $('#cboClasePersonaBusqueda').append('<option value="' + value.id + '">' + value.descripcion + '</option>');
        });
    }
    listarPersona();
}

var personaTipoIdG;
var valorPersonaTipoG;
var rutaG;

function cargarFormularioPersona()
{   
    commonVars.invitacionId = 0;
    cargarDiv('#window', 'vistas/com/invitacion/invitacion_secundario_form.php', "Nueva ");
}

function onResponseObtenerPersonaClaseAsociada(data){
    if(isEmpty(data)){
        $.Notification.autoHideNotify('warning', 'top right', 'Validación', 'No tiene permisos para crear el tipo de persona.');        
    }else{
        cargarFormulario(personaTipoIdG, valorPersonaTipoG, rutaG);
    }
}



var nombres;
var codigo;
var tipoPersona;
var clasePersona;
var id;


function listarPersona(id) {
    ;
//    var nombres = $("#txtNombresBusqueda").val();
//    var codigo = $("#txtCodigoBusqueda").val();
//    var tipoPersona = $("#cboTipoPersonaBusqueda").val();
//    var clasePersona = $("#cboClasePersonaBusqueda").val();
    ax.setAccion("getDataGridInvitacionSecundario");
     if(id==null){

     }else{
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
            {"data": "fecha_creacion"},
            {"data": "RUC"},
            {"data": "REINFO"},
            {"data": "ubigeo"},
            {"data": "direccion"},
            {"data": "nivel"},
            {"data": "usuario"},
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
            {data: "id",
                render: function (data, type, row) {
                    if (type === 'display') {
                        if(row.nivel_codigo==0){
                        return '<a onclick="editarInvitacion(' + row.id + ')"><b><i class="fa fa-edit" style="color:#088a08;"></i><b></a>\n\
                                   <a onclick="confirmarDeleteSolicitud(' + row.id + ')"><b><i class="fa fa-trash-o" style="color:#cb2a2a;"></i><b></a>\n\
                                   <a target="_blank" href="envio_notificacion7.php?token='+row.token+'"><b><i class="fa fa-file-pdf-o" style="color:#0366b0;"></i><b></a>\n\
                                   <a onclick="openModal(' + row.id + ', \'' + row.token + '\')"><b><i class="fa fa-link" style="color:#337ab7;"></i><b></a>';}
                                  
                                   else { return '';}
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

    
    nombres=null;
    codigo=null;
    tipoPersona=null;
    clasePersona=null;
    id=null;
    loaderClose();
}



function copiarHTML() {
    // Selecciona el contenido del elemento con el ID generatedLink
    var contenido = document.getElementById("generatedLink").innerHTML;

    // Usa la API del portapapeles para copiar el contenido
    navigator.clipboard.writeText(contenido).then(function() {
        // Muestra un mensaje de confirmación (opcional)
        // alert("HTML copiado al portapapeles!");
    }, function(err) {
        console.error("No se pudo copiar el texto: ", err);
    });
}

function openModal(id,token) {
    // Obtener el dominio actual
    var dominio = window.location.origin;
    var archivo = "/sgiLaVictoria/envio_notificacion7.php"; // Cambia esto por el nombre del archivo que necesites
    var token = token; // Reemplaza esto con el token generado

    // Concatenar dominio, archivo y token
    var urlCompleta = dominio + archivo + "?token=" + token;

    // Mostrar el enlace en el modal
    document.getElementById("generatedLink").innerText = urlCompleta;
    document.getElementById("generatedLink").href = urlCompleta;

    // Abrir el modal
    $('#linkModal').modal('show');
}
function confirmarDeleteSolicitud(id)
{  
    ;
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
                listarPersona();
                break;
            case 'deleteSolicitudPrincipal':
                loaderClose();
                var error = response.data[0]['vout_exito'];
                if (error == 1) {
                    swal("Eliminado!", "Invitación eliminada correctamente", "success");
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
            case 'buscarCriteriosBusquedaSolicitudPrincipal':
                onResponseBuscarCriteriosBusquedaSolicitud(response.data);
                loaderClose();
                break;
            case 'obtenerPersonaClaseAsociada':
                onResponseObtenerPersonaClaseAsociada(response.data);
                loaderClose();
                break;
        }
    } else
    {
        switch (response[PARAM_ACCION_NAME]) {
            case 'deleteSolicitudPrincipal':
                loaderClose();
                swal("Cancelado", "No se pudo eliminar, esta en uso", "error");
        }
    }
}

function deleteSolicitud(id)
{    
    loaderShow();
    ax.setAccion("deleteSolicitudPrincipal");
    ax.addParamTmp("id", id);
    ax.consumir();
}

function cambiarEstadoPersona(id)
{
    ax.setAccion("cambiarEstadoPersona");
    ax.addParamTmp("id", id);
    ax.consumir();
}
function onResponseCambiarEstadoPersona(data)
{
    if (data[0]["vout_exito"] == 1)
    {
        $.Notification.autoHideNotify('success', 'top-right', '&Eacute;xito', data[0]["vout_mensaje"]);
    }
    else
    {
        $.Notification.autoHideNotify('warning', 'top right', 'Validación', data[0]["vout_mensaje"] + "No se puede cambiar de estado.");
    }
}
function obtenerTitulo(dependiente)
{
    tituloGlobal = $("#titulo").text();
    var titulo = tituloGlobal;
    if (dependiente == 0)
    {
        $("#window").empty();
    }
    if (!isEmpty(titulo))
    {
        titulo = titulo.toLowerCase();
    }
    return titulo;
}



function buscarPersona(colapsa)
{   ;
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
function obtenerDatosBusqueda()
{
    ;
    var cadena = "";
    var nombres = $("#txtNombresBusqueda").val();
    var codigo = $("#txtCodigoBusqueda").val();
    var tipoPersona = $("#cboTipoPersonaBusqueda").val();
    var clasePersona = $("#cboClasePersonaBusqueda").val();

    
    if (!isEmpty(codigo))
    {
        cadena += StringNegrita("Cód. Id.: ");

        cadena += codigo;
        cadena += "<br>";
    }
    if (!isEmpty(nombres))
    {
        cadena += StringNegrita("Nombre: ");

        cadena += nombres;
        cadena += "<br>";
    }
    if (tipoPersona != -1)
    {
        cadena += StringNegrita("Tipo de persona: ");

        cadena += select2.obtenerText('cboTipoPersonaBusqueda');
        cadena += "<br>";
    }
    if (!isEmpty(clasePersona))
    {
        cadena += StringNegrita("Clase de persona: ");
        cadena += select2.obtenerTextMultiple('cboClasePersonaBusqueda');
        cadena += "<br>";
    }
    return cadena;
}
function editarInvitacion(id) {
    ;
    loaderShow(null);
    commonVars.invitacionId = id;
    cargarDiv("#window", "vistas/com/invitacion/invitacion_secundario_form.php", "Editar Invitación ");
}
function actualizarBusquedaPersona()
{
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
function getAllEmpresaImport()
{
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

function importar()
{
    var file = document.getElementById('secretFile').value;   
    var empresa = $('#cboEmpresa').val();

    if (isEmpty(empresa))
    {
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

    if (!isEmpty(data))
    {
        $('#cboEmpresa').empty();
        $.each(data, function (index, value) {
            $('#cboEmpresa').append('<option value="' + value.id + '">' + value.razon_social + '</option>');
        });
    }
    acciones.getEmpresa = true;
}

function asignarValorSelect2(id, valor)
{
    $("#" + id).select2().select2("val", valor);
    $("#" + id).select2({width: '100%'});
}
    
$('.dropdown-menu').click(function(e) {
        if(e.target.id != "btnBusqueda" && e.delegateTarget.id!="ulBuscadorDesplegable2" && e.delegateTarget.id!="listaEmpresa") {
                e.stopPropagation();
        }        
    });

$('#txtBuscar').keyup(function(e) {   
    var bAbierto=$('#txtBuscar').attr("aria-expanded");
    
    if(!eval(bAbierto)){        
        $('#txtBuscar').dropdown('toggle');
    }     

});

function cambiarAnchoBusquedaDesplegable(){
    var ancho = $("#divBuscador").width();    
    $("#ulBuscadorDesplegable").width((ancho-5)+"px");    
    $("#ulBuscadorDesplegable2").width((ancho-5)+"px");    
}

function obtenerParametrosBusqueda(){    
    nombres = $("#txtNombresBusqueda").val();
    codigo = $("#txtCodigoBusqueda").val();
    tipoPersona = $("#cboTipoPersonaBusqueda").val();
    clasePersona = $("#cboClasePersonaBusqueda").val();
}

function llenarParametrosBusqueda(nombresTxt,codigoTxt,idTxt,clasePersonaTxt){         
    ;
    var clasePersonaIds=[];
    if(!isEmpty(clasePersonaTxt)){
        clasePersonaIds.push(clasePersonaTxt);
    }
    
    if(!isEmpty(codigoTxt)){
        nombresTxt=null;
    }
    
    nombres = nombresTxt;
    codigo = codigoTxt;
    id = idTxt;
    clasePersona = clasePersonaIds;
    
    loaderShow();
    listarPersona(id); 
}

function buscarCriteriosBusquedaSolicitud(){    
//    loaderShow();
    ax.setAccion("buscarCriteriosBusquedaSolicitudPrincipal");
    ax.addParamTmp("busqueda", $('#txtBuscar').val());
    ax.consumir();
}

function onResponseBuscarCriteriosBusquedaSolicitud(data){
    ;
    var dataPersona=data.dataPersona;
    var dataPersonaClase=data.dataPersonaClase;  
    
    var html = '';
    $('#ulBuscadorDesplegable2').empty();
    if (!isEmpty(dataPersona)) {        
        $.each(dataPersona, function (index, item) {
            html += '<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">';
            html += '<a onclick="llenarParametrosBusqueda(\''+item.nombre+'\',\'' + item.codigo_identificacion + '\','+item.id+','+null+')" >';
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

function limpiarBuscadores(){
    $('#txtCodigoBusqueda').val('');
    $('#txtNombresBusqueda').val('');
    
    select2.asignarValor('cboClasePersonaBusqueda',-1);
    select2.asignarValor('cboTipoPersonaBusqueda',-1);
}