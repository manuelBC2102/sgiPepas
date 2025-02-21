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
        configuracionesInicialesFiltros();
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

function configuracionesIniciales()
{
    ax.setAccion("configuracionesInicialesPersonaListar");
    ax.consumir();
}


function configuracionesInicialesFiltros()
{
    ax.setAccion("obtenerConfiguracionesFiltros");
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
                // '<button type="button" class="btn btn-info"  onclick="cargarFormularioPersona(' + perJuridica.id + ',\'' + perJuridica.descripcion + '\',\'' + perJuridica.ruta + '\')"><i class=" fa fa-plus-square-o"></i>&nbsp; Nueva Solicitud </button>'+
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

function cargarFormularioPersona(personaTipoId, valor_persona_tipo, ruta)
{    commonVars.personaId = 0;
    cargarDiv('#window', 'vistas/com/solicitudRetiro/solicitud_retiro_form.php', "Nueva ");
}

function onResponseObtenerPersonaClaseAsociada(data){
    if(isEmpty(data)){
        $.Notification.autoHideNotify('warning', 'top right', 'Validación', 'No tiene permisos para crear el tipo de persona.');        
    }else{
        cargarFormulario(personaTipoIdG, valorPersonaTipoG, rutaG);
    }
}

function cargarFormulario(personaTipoId, valor_persona_tipo, ruta)
{
    loaderShow(null);
    var dependiente = document.getElementById('hddIsDependiente').value;
//    obtenerTitulo(dependiente);
    if (!isEmpty(valor_persona_tipo))
    {
        valor_persona_tipo = valor_persona_tipo.toLowerCase();
    }
    if (dependiente == 0)
    {
        commonVars.personaId = 0;
        cargarDiv('#window', 'vistas/com/persona/persona_form.php', "Nueva Persona");
    } else
    {
        cargarDivModal('#respuesta', ruta, "Nueva " + valor_persona_tipo);
    }
}

var nombres;
var codigo;
var tipoPersona;
var clasePersona;
var id;

function listarPersona() {
    debugger;

    var planta = $("#cboPlantaF").val();
    var zona = $("#cboZonaF").val();
    var vehiculo = $("#cboVehiculo").val();
    var transportista = $("#cboTransportista").val();
    ax.setAccion("getDataGridSolicitudes");
     if(id==null){

     }else{
    ax.addParamTmp("id", id);
     }
     ax.addParamTmp("planta", planta);
     ax.addParamTmp("zona", zona);
     ax.addParamTmp("vehiculo", vehiculo);
     ax.addParamTmp("transportista", transportista);
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
            {"data": "id"},
            {"data": "fecha_entrega"},
            {"data": "zona"},
            {"data": "vehiculo"},
            {"data": "conductor"},
            {"data": "REINFO"},
            {"data": "planta"},
            {"data": "estado"},
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
    "data": "id",
    "render": function (data, type, row) {
        if (type === 'display') {
            if (row.estado == 'Registrado') {
                return ' <a onclick="confirmarDeleteSolicitud(' + row.id + ')"><b><i class="fa fa-trash-o" style="color:#cb2a2a;"></i><b></a>\n\
                // // <a onclick="editarPersona(' + row.id + ')"><b><i class="fa fa-edit" style="color:#E8BA2F;"></i><b></a>\n\
                        <a onclick="requerimiento(' + row.id + ')"><b><i class="fa fa-file" style="color:blue;"></i><b></a>';
                          
            }
            if(row.imagen1_base64!=null && row.imagen2_base64!=null && row.imagen3_base64){
                 return '<a onclick="confirmarDeleteSolicitud(' + row.id + ')"><b><i class="fa fa-trash-o" style="color:#cb2a2a;"></i><b></a>\n\
                 <a onclick="generarPDF(' + row.id + ', \'' + row.imagen1_base64 + '\', \'' + row.imagen2_base64 + '\', \'' + row.imagen3_base64 + '\')"><i class="fa fa-file" style="color:green;"></i></a>\n\
                 <a onclick="solicitudRetiro(' + row.id + ')"><b><i class="fa fa-file" style="color:blue;"></i><b></a>';
                 }
        }
        return null;
    },
    "orderable": true,
    "class": "alignCenter"
}
,
        ],
        columnDefs: [
          
        ],
//        "dom": '<"top">rt<"bottom"<"col-md-3"l><"col-md-9"p><"col-md-12"i>><"clear">',
        "order": [[1, "desc"]],
        destroy: true
    });

    
    planta=null;
    zona=null;
    vehiculo=null;
    transportista=null;
    id=null;
    loaderClose();
}

function generarPDF(id, imagen1_nombre, imagen2_nombre, imagen3_nombre) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
debugger;
    // Función para construir la ruta completa a la imagen
    function obtenerRutaImagen(nombreImagen) {
        if (!nombreImagen) return ''; // Si no hay nombre de imagen, retornamos vacío

        // Ruta base de las imágenes
        const rutaBase = 'vistas/com/solicitudRetiro/validaciones/';
        
        // Devuelvo la ruta completa
        return rutaBase + nombreImagen;
    }

    // Construir las rutas completas de las imágenes
    var imagen1_ruta = obtenerRutaImagen(imagen1_nombre);
    var imagen2_ruta = obtenerRutaImagen(imagen2_nombre);
    var imagen3_ruta = obtenerRutaImagen(imagen3_nombre);

    // Agregar la primera imagen en el PDF
    doc.addImage(imagen1_ruta, 'PNG', 10, 10, 200, 220);  // Ajusta el tamaño y las posiciones

    // Agregar una nueva página para la segunda imagen
    doc.addPage();
    doc.addImage(imagen2_ruta, 'PNG', 10, 10, 200, 220);

    // Agregar una nueva página para la tercera imagen
    doc.addPage();
    doc.addImage(imagen3_ruta, 'PNG', 10, 10, 180, 160);

    // Guardar el archivo PDF
    doc.save("validaciones_solicitudRetiro_" + id + ".pdf");
}
function confirmarDeleteSolicitud(id)
{
    BANDERA_ELIMINAR = false;
    swal({
        title: "Est\xe1s seguro?",
        text: "Eliminarás la solicitud de retiro",
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
            case 'obtenerConfiguracionesFiltros':
                onResponseObtenerdatosInicialesModal2(response.data);
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
            case 'obtenerPersonaClaseAsociada':
                onResponseObtenerPersonaClaseAsociada(response.data);
                loaderClose();
                break;
        }
    } else
    {
        switch (response[PARAM_ACCION_NAME]) {
            case 'deleteSolicitud':
                swal("Cancelado", "No se pudo eliminar, esta en uso", "error");
        }
    }
}

function onResponseObtenerdatosInicialesModal2(data) {
    // Cargar los combos con los datos proporcionados
    select2.cargar("cboPlantaF", data.plantas, "id", ["codigo_identificacion", "nombre_completo"]);
    select2.cargar("cboZonaF", data.zonas, "id", "nombre");
    select2.cargar("cboVehiculo", data.vehiculos, "id", "placa");
    select2.cargar("cboTransportista", data.transportistas, "id", ["codigo_identificacion", "nombre_completo"]);

    // Después de cargar los datos, limpiar la selección y agregar opción vacía
    $("#cboPlantaF").val("").trigger('change');
    $("#cboZonaF").val("").trigger('change');
    $("#cboVehiculo").val("").trigger('change');
    $("#cboTransportista").val("").trigger('change');

    loaderClose();
}

function limpiarFiltros() {
 
    $("#cboPlantaF").val(null).trigger('change.select2');
    $("#cboZonaF").val(null).trigger('change.select2');
    $("#cboVehiculo").val(null).trigger('change.select2');
    $("#cboTransportista").val(null).trigger('change.select2');
    loaderClose();

 
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
function deleteSolicitud(id)
{
    ax.setAccion("deleteSolicitud");
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

function cargarListarPersonaCancelar()
{
    loaderShow(null);
    cargarDiv('#window', 'vistas/com/persona/persona_listar.php', tituloGlobal);
}

function buscarPersona(colapsa)
{   debugger;
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
function obtenerDatosBusqueda()
{
    debugger;
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
function editarPersona(id) {
    debugger;
    loaderShow(null);
    commonVars.personaId = id;
    cargarDiv("#window", "vistas/com/solicitudRetiro/solicitud_retiro_form.php", "Editar Solicitud ");
}



function requerimiento(id) {
   
// Abrir la ventana con los parámetros adicionales
window.open(URL_BASE + 'vistas/com/solicitudRetiro/PDF.php?token=' + id);
}


function solicitudRetiro(id) {
   
    // Abrir la ventana con los parámetros adicionales
    window.open(URL_BASE + 'vistas/com/solicitudRetiro/solicitud_retiro_pdf.php?id=' + id);
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
    debugger;
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
    listarPersona(); 
}

function buscarCriteriosBusquedaSolicitud(){    
//    loaderShow();
    ax.setAccion("buscarCriteriosBusquedaSolicitud");
    ax.addParamTmp("busqueda", $('#txtBuscar').val());
    ax.consumir();
}

function onResponseBuscarCriteriosBusquedaSolicitud(data){
    debugger;
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
   
    
    select2.asignarValor('cboPlantaF',-1);
    select2.asignarValor('cboZonaF',-1);
    select2.asignarValor('cboVehiculo',-1);
    select2.asignarValor('cboTransportista',-1);

  
}