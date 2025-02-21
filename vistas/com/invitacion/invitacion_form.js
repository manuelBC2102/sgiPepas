var c = $('#env i').attr('class');
var anchoComboSunat2;
var dataPersonaGlobal;
$(document).ready(function () {
    controlarDomXTipoPersona();
    select2.iniciar();
    anchoComboSunat2 = $("#divCboCodigoSunat2").width();
    

    ax.setSuccess("successPersona");
    ax.setAccion("obtenerConfiguracionesInvitacion");
    ax.addParamTmp("invitacionId", commonVars.invitacionId);
    ax.consumir();

//    tokenPersona = getParameterByName('token');
//    console.log(tokenPersona);

llenarcomboZonas();

});

$("#cboClasePersona").on("change", function (e) {
    a = e.val;
    //console.log(e.val)
    var bandera_tabla_centro_costo = false;
    $.each(e.val, function (index, item) {
        if (item == -3 && (e.val).length == 1)
        {
            $("#lblCodigoIdentificacion").html("DNI ");
        } else {
            $("#lblCodigoIdentificacion").html("DNI *");
        }
        if (item == -2) {
            bandera_tabla_centro_costo = true;
        }
    });
    if (bandera_tabla_centro_costo) {
        cargarCentroCostoPersona(dataCentroCostoPersona);
    } else {
        $('#divCentroCostoPersona').hide();
    }
});


$('#txtFechaEntrega').keypress(function () {
    $('#msjFechaEntrega').hide();
});
$('#txtCapacidad').keypress(function () {
    $('#msjCapacidad').hide();
});
$('#txtConstancia').keypress(function () {
    $('#msjConstancia').hide();
});

$('#cboPlanta').keypress(function () {
    $('#msjPlanta').hide();
});
$('#cboZona').keypress(function () {
    $('#msjZona').hide();
});
$('#cboTipoArchivo').keypress(function () {
    $('#msjTipoArchivo').hide();
});
$('#txtCorreo').keypress(function () {
    $('#msjCorreo').hide();
});
$('#txtTelefono').keypress(function () {
    $('#msjTelefono').hide();
});

$('#divCentroCostoPersona').hide();
function deshabilitarBoton()
{
    $("#env").addClass('disabled');
    $("#env i").removeClass(c);
    $("#env i").addClass('fa fa-spinner fa-spin');
}
function habilitarBoton()
{
    $("#env").removeClass('disabled');
    $("#env i").removeClass('fa-spinner fa-spin');
    $("#env i").addClass(c);
}


function successPersona(response) {
    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obtenerPersona':
                editarPersona(response.data);
                break;
            case 'obtenerConfiguracionesInvitacion':
                onresponseConfiguracionesPersona(response.data);
                dataPersonaGlobal = response.data.personaNatural;
                //console.log(dataPersonaGlobal);
                break;
            case 'insertSolicitud':
                mostrarOk("Solicitud Registrada");
                loaderClose();
                habilitarBoton();
                cargarListarPersonaCancelar();
                break;
            case 'guardarInvitacion':
                debugger;
                mostrarOk("Invitación registrada");
                loaderClose();
                cargarListarPersonaCancelar();
                break;
            case 'actualizarInvitacion':
                debugger;
                mostrarOk("Invitación actualizada");
                loaderClose();
                cargarListarPersonaCancelar();
                break;            
            case 'updateSolicitud':
                mostrarOk(response.data['0'].vout_mensaje);
                loaderClose();
                habilitarBoton();
                cargarListarPersonaCancelar();
                break;
            case 'obtenerConsultaRUC':
                onresponseObtenerConsultaRUC(response.data);
                loaderClose();
                break;
            case 'obtenerPersonasNaturales':
                onResponseObtenerPersonasNaturales(response.data);
                break;
            case 'obtenerDataConvenioSunat':
                onResponseObtenerDataConvenioSunat(response.data);
                loaderClose();
                break;
            case 'validarSimilitud':
                onResponseValidarSimilitudes(response.data);
                loaderClose();
                break;
            case 'obtenerDataREINFO':
                listarDataSolicitudes(response.data);
                loaderClose();
                break;
            case 'obtenerDataDNI':
                listarDataSolicitudesDNI(response.data);
                loaderClose2();
                    break;
                
                case 'obtenerZonas':
                    onResponsellenarcomboTipoArchivo(response.data);
                    break;
        }
    } else
    {
        switch (response[PARAM_ACCION_NAME]) {
            case 'insertSolicitud':
                loaderClose();
                habilitarBoton();
                break;

            case 'guardarInvitacion':
                loaderClose();
                habilitarBoton();
                break;
            case 'actualizarInvitacion':
                loaderClose();
                habilitarBoton();
                break;
            case 'updateSolicitud':
                loaderClose();
                habilitarBoton();
                break;
            case 'obtenerConsultaRUC':
                loaderClose();
                break;
            case 'validarSimilitud':
                loaderClose();
                habilitarBoton();
                break;
            case 'obtenerDataPlaca':
                mostrarMensajeError("No hay solicitudes con esta placa");
                loaderClose();
                habilitarBoton();
                break;
                case 'obtenerZonas':
                    mostrarMensajeError("No existen zonas");
                    deshabilitarBoton2;
                        break;
        }
    }
}

function cargarListarPersonaCancelar()
{
    loaderShow(null);
    cargarDiv('#window', 'vistas/com/invitacion/invitacion_listar.php');
}
var direccionTipoFiscal;
var convenioSunatId0 = null;
var ubigeoT;
function onresponseConfiguracionesPersona(data)
{


    $('#txtNombre').val(data.invitacion[0].nombre).prop('readonly', true);
    $('#txtSector').val(data.invitacion[0].sector).prop('readonly', true);
    $('#txtEstado').val(data.invitacion[0].estado).prop('readonly', true);
    $('#txtReinfo').val(data.invitacion[0].codigo_identificacion);
    $('#txtCodigo').val(data.invitacion[0].codigo);
    
    $('#txtCodigoUnico').val(data.invitacion[0].codigo).prop('readonly', true);
    $('#txtDepartamento').val(data.invitacion[0].departamento);
    $('#txtDistrito').val(data.invitacion[0].distrito);
    $('#txtProvincia').val(data.invitacion[0].provincia);
    $('#txtDireccion').val(data.invitacion[0].direccion);
    $('#txtTelefono').val(data.invitacion[0].telefono);
    $('#txtCorreo').val(data.invitacion[0].email);
    debugger;
    ubigeoT=data.invitacion[0].ubigeo_id;
    loaderClose();
}

function llenarcomboZonas() {
    ax.setAccion("obtenerZonas");
 
    ax.consumir();
}
function onResponsellenarcomboTipoArchivo(data) {
    debugger;
    
    arraycomboTipoArchivo= data.zona;
    $('#cboTipoArchivo').empty();
    
    $.each(data.zona, function(index, item) {
        $('#cboTipoArchivo').append(new Option(item.nombre, item.id));
 
    });
    $('#cboTipoArchivo').trigger('change'); // Actualizar select2

    
        select2.cargar("cboUbigeo", data.dataUbigeo, "id", ["ubigeo_codigo", "ubigeo_dep", "ubigeo_prov", "ubigeo_dist"]);
        select2.asignarValor('cboUbigeo', ubigeoT); 
   
}


function cargarListarActaCancelar()
{
    loaderShow(null);
    cargarDiv('#window', 'vistas/com/invitacion/invitacion_listar.php', 'Registro invitación');
}
function controlarDomXTipoPersona() {

    $("#lblCodigoIdentificacion").empty();
    $("#lblNombre").empty();
    if (commonVars.personaTipoId == 2) {
        $("#contenedorNombres").show();
        $("#contenedorApellidoPaterno").show();
        $("#contenedorApellidoMaterno").show();
        $("#lblCodigoIdentificacion").append("DNI *");
        $("#lblNombre").append("Nombres *");
    } else {
        $("#contenedorBuscarRUC").show();
        $("#contenedorRazonSocial").show();
        $("#lblCodigoIdentificacion").append("RUC *")
        $("#lblNombre").append("Razón social *");
    }

    $("#liPersonaContactos").hide();
}

function validarSimilitud()
{

    if (commonVars.personaTipoId == 2) {
        var nombre = trim(document.getElementById('txtNombre').value);
        var apellido_paterno = trim(document.getElementById('txtApellidoPaterno').value);

        ax.setAccion("validarSimilitud");
        ax.addParamTmp("personaId", commonVars.invitacionId);
        ax.addParamTmp("nombre", nombre);
        ax.addParamTmp("apellidoPaterno", apellido_paterno);
        ax.consumir();
    } else {
        guardarPersona();
    }
}
function onResponseValidarSimilitudes(data)
{
    if (!isEmpty(data)) {
        /*var htmlTablaSimilitudes = "<table>";
         $.each(data, function (indice, valor) {
         htmlTablaSimilitudes += "<tr>";
         htmlTablaSimilitudes += "<td>" + valor['nombre'] + "</td>";
         htmlTablaSimilitudes += "<td>" + valor['apellido_paterno'] + "</td>";
         htmlTablaSimilitudes += "<td>" + valor['apellido_materno']==null?"":valor['apellido_materno']==null + "</td>";
         htmlTablaSimilitudes += "</tr>";
         });
         
         htmlTablaSimilitudes += "</table>";*/

        var htmlTablaSimilitudes = "<ul class='list-group'>";
        $.each(data, function (indice, valor) {
            var aMaterno = (isEmpty(valor['apellido_materno'])) ? " " : valor['apellido_materno'];
            var aPaterno = (isEmpty(valor['apellido_paterno'])) ? " " : valor['apellido_paterno'];
            var Nombre = (isEmpty(valor['nombre'])) ? " " : valor['nombre'];
            htmlTablaSimilitudes += "<li class='list-group-item'>";
            htmlTablaSimilitudes += " " + Nombre;
            htmlTablaSimilitudes += " " + aPaterno;
            htmlTablaSimilitudes += " " + aMaterno;
            htmlTablaSimilitudes += "</li>";
        });

        var textoSwal = "<h4>¿ Desea completar el registro de todos modos ?</h4>";
        htmlTablaSimilitudes += "</ul>";
        htmlTablaSimilitudes += textoSwal;

        swal({
            title: "¡Se encontraron nombres similares!",
            text: htmlTablaSimilitudes,
            html: true,
            type: "info",
            showCancelButton: true,
            confirmButtonColor: "#33b86c",
            confirmButtonText: "Si, registrar!",
            cancelButtonColor: '#d33',
            cancelButtonText: "No, cancelar registro !",
            closeOnConfirm: true,
            closeOnCancel: false
        }, function (isConfirm) {
            if (isConfirm) {
                guardarPersona();
            } else {
                swal("Cancelado", "El registro fue cancelado", "error");
            }
        });

    } else {
        guardarPersona();
    }

}

// function guardarSolicitud() {
//     var fechaEntrega = trim(document.getElementById('txtFechaEntrega').value);
//     var capacidad = trim(document.getElementById('txtCapacidad').value);
//     var constancia = trim(document.getElementById('txtConstancia').value);
   


//     var transportista = select2.obtenerValor('cboTransportista');
//     var conductor = select2.obtenerValor('cboConductor');
//     var vehiculo = select2.obtenerValor('cboVehiculo');
//     var zona = select2.obtenerValor('cboZona');
//     var planta = select2.obtenerValor('cboPlanta');



//     if (validarSolicitud(fechaEntrega,capacidad,constancia,transportista,conductor,vehiculo,zona,planta)) {

// debugger;
//         if (commonVars.invitacionId > 0) {
//             actualizarPersona(commonVars.invitacionId,fechaEntrega,capacidad,constancia,transportista,conductor,vehiculo,zona,planta);
//         } else {
//             insertarSolicitud(fechaEntrega,capacidad,constancia,transportista,conductor,vehiculo,zona,planta);
//         }

//     } else {
//         loaderClose();
//     }
// }

function insertarSolicitud(fechaEntrega,capacidad,constancia,transportista,conductor,vehiculo,zona,planta)
{
    loaderShow(null);
    deshabilitarBoton();
    ax.setAccion("insertSolicitud");
    ax.addParamTmp("fechaEntrega", fechaEntrega);
    ax.addParamTmp("capacidad", capacidad);
    ax.addParamTmp("constancia", constancia);
    ax.addParamTmp("transportista", transportista);
    ax.addParamTmp("conductor", conductor);
    ax.addParamTmp("vehiculo", vehiculo);
    ax.addParamTmp("zona", zona);
    ax.addParamTmp("planta", planta);

    ax.consumir();
}

function actualizarPersona(solicitudId,fechaEntrega,capacidad,constancia,transportista,conductor,vehiculo,zona,planta)
{
    loaderShow(null);
    deshabilitarBoton();
    ax.setAccion("updateSolicitud");
    ax.addParamTmp("id", solicitudId)
    ax.addParamTmp("fechaEntrega", fechaEntrega);
    ax.addParamTmp("capacidad", capacidad);
    ax.addParamTmp("constancia", constancia);
    ax.addParamTmp("transportista", transportista);
    ax.addParamTmp("conductor", conductor);
    ax.addParamTmp("vehiculo", vehiculo);
    ax.addParamTmp("zona", zona);
    ax.addParamTmp("planta", planta);
    ax.consumir();
}



function validarSolicitud(telefono,correo,zona) {

    //expresiones de validacion 
    var expresion_email = /^[a-zA-Z0-9\._-]+@[a-zA-Z0-9-]{2,}[.][a-zA-Z]{2,4}$/;


    //var requiereDNI = DNIObligatorio()==-3?false:true; //Si contacto esta seleccionado
    var bandera = true;

    
    if (isEmpty(zona))
    {
        $("#msjTipoArchivo").removeProp(".hidden");
        $("#msjTipoArchivo").text("Seleccione la Zona").show();
        bandera = false;
    }

        
    if (isEmpty(telefono))
    {
        $("#msjTelefono").removeProp(".hidden");
        $("#msjTelefono").text("Registrar telefono").show();
        bandera = false;
    }


    if (isEmpty(correo))
        {
            $("#msjCorreo").removeProp(".hidden");
            $("#msjCorreo").text("Registrar correo").show();
            bandera = false;
        }

    


    return bandera;
}
function mostrarMensajeError(nombre)
{
    $('#msj' + nombre).hide();
}
function llenarFormularioEditar(data)
{
debugger;
    //console.log(data);
    $("#txtFechaEntrega").val(data[0].fecha_entrega);
    $("#txtCapacidad").val(data[0].capacidad);
    $("#txtConstancia").val(data[0].constancia);


    //tablas sunat    
    select2.asignarValor('cboTransportista', data[0]['persona_transportista_id']);
    select2.asignarValor('cboConductor', data[0]['persona_conductor_id']);
    select2.asignarValor('cboVehiculo', data[0]['vehiculo_id']);
    select2.asignarValor('cboZona', data[0]['zona_id']);
    select2.asignarValor('cboPlanta', data[0]['persona_planta_id']);
    
}

function buscarConsultaRUC() {
    var codigoIdentificacion = trim(document.getElementById('txtCodigoIdentificacion').value);

    if (isEmpty(codigoIdentificacion)) {
        $.Notification.autoHideNotify('warning', 'top right', 'Validación', "Ingrese RUC.");
        return;
    }
    if (isNaN(codigoIdentificacion) || codigoIdentificacion.length !== 11) {
        $.Notification.autoHideNotify('warning', 'top right', 'Validación', "Ingrese RUC, 11 dígitos numéricos .");
        return;
    }

    loaderShow();
    ax.setAccion("obtenerConsultaRUC");
    ax.addParamTmp("codigoIdentificacion", codigoIdentificacion);
    ax.consumir();

}

function onresponseObtenerConsultaRUC(data) {

    //console.log(data);    
    $('#txtRazonSocial').val('');
    $('#txtDireccion').val('');

    if (!isEmpty(data)) {
        $('#txtRazonSocial').val(data['razonSocial']);
        $('#txtDireccion').val(data['DireccióndelDomicilioFiscal']);
        habilitarDivDireccionTipoCombo();
        select2.asignarValor("cboDireccionTipo", -1);
    }

}

function validarToken(personaId) {
    if (token == 1) {
        window.opener.setearPersonaRegistro(personaId);
        setTimeout("self.close();", 700)
    }
}

function habilitarDivContactoTipoTexto() {
    $("#contenedorContactoTipoDivCombo").hide();
    $("#contenedorContactoTipoDivTexto").show();
}

function habilitarDivContactoTipoCombo() {
    $("#contenedorContactoTipoDivTexto").hide();
    $("#contenedorContactoTipoDivCombo").show();
}

function nuevoContactoPersona()
{
    var rutaAbsoluta = URL_BASE + 'index.php?token=1&personaTipo=2';
    window.open(rutaAbsoluta, "nuevo", "directories=no, location=no, menubar=no, scrollbars=yes, statusbar=no, tittlebar=no, width=1500, height=900");
}

var personaIdRegistro = null;
function setearPersonaRegistro(personaId) {
//    console.log(personaId);
    personaIdRegistro = personaId;
    obtenerPersonasNaturales();
}

function obtenerPersonasNaturales() {
    ax.setAccion("obtenerPersonasNaturales");
    ax.consumir();
}

function onResponseObtenerPersonasNaturales(data) {
//    console.log(data);
    if (!isEmpty(data)) {
        select2.cargar("cboContacto", data, "id", ["persona_nombre", "codigo_identificacion"]);
    }

    if (!isEmpty(personaIdRegistro)) {
        select2.asignarValor("cboContacto", personaIdRegistro);
    }
}

// PESTAÑA CONTACTO
var listaContactoDetalle = [];

var arrayContacto = [];
var arrayContactoText = [];
var arrayContactoTipo = [];
var arrayContactoTipoText = [];
var arrayContactoTelefonoText = [];
var arrayContactoEmailText = [];
var arrayPersonaContactoId = [];

function findContactoById(index)
{
    for (var i = 0; i < dataPersonaGlobal.length; i++)
    {
        if (dataPersonaGlobal[i].id === index)
        {
            return dataPersonaGlobal[i];
        }
        //console.log("id"+dataPersonaGlobal[i].id + "Nombre: " + dataPersonaGlobal[i].persona_nombre + "Celular: " + dataPersonaGlobal[i].celular);
    }
    //console.log("SADASDASD");
    //alert("asdasdasd");
}

function agregarContactoDetalle() {
    //ids
    var contacto = select2.obtenerValor('cboContacto');
    var contactoTipo = select2.obtenerValor('cboContactoTipo');

    //texto
    var contactoTipoText = document.getElementById('txtContactoTipo').value;
    contactoTipoText = contactoTipoText.trim();
    if (isEmpty(contactoTipoText)) {
        contactoTipoText = select2.obtenerText('cboContactoTipo');
    }
    var contactoText = select2.obtenerText('cboContacto');


    var contactoTelefonoText = findContactoById(contacto).celular !== null ? findContactoById(contacto).celular : "Sin celular";
    var contactoEmailText = findContactoById(contacto).email !== null ? findContactoById(contacto).email : "Sin email.";

    var idContactoDetalle = $('#idContactoDetalle').val();

    // ids tablas
    var personaContactoId = null;
    //alert(idContactoDetalle);

    if (validarFormularioContactoDetalle(contactoTipoText, contacto)) {
        if (validarContactoDetalleRepetido(contactoTipoText, contacto)) {

            if (idContactoDetalle != '') {

                arrayContactoTipo[idContactoDetalle] = contactoTipo;
                arrayContactoTipoText[idContactoDetalle] = contactoTipoText;
                arrayContacto[idContactoDetalle] = contacto;
                arrayContactoText[idContactoDetalle] = contactoText;
                arrayContactoTelefonoText[idContactoDetalle] = contactoTelefonoText;
                arrayContactoEmailText[idContactoDetalle] = contactoEmailText;

                // ids de tablas relacionadas
                personaContactoId = arrayPersonaContactoId[idContactoDetalle];

                listaContactoDetalle[idContactoDetalle] = [contactoTipo, contactoTipoText, contacto, contactoText, personaContactoId, contactoTelefonoText, contactoEmailText];
            } else {
                //alert('diferente');

                arrayContactoTipo.push(contactoTipo);
                arrayContactoTipoText.push(contactoTipoText);
                arrayContacto.push(contacto);
                arrayContactoText.push(contactoText);
                arrayContactoTelefonoText.push(contactoTelefonoText);
                arrayContactoEmailText.push(contactoEmailText);

                listaContactoDetalle.push([contactoTipo, contactoTipoText, contacto, contactoText, personaContactoId, contactoTelefonoText, contactoEmailText]);
            }

//            console.log(listaContactoDetalle);
//            console.log(listaPersonaContactoEliminado);
            onListarContactoDetalle(listaContactoDetalle);
            limpiarCamposContactoDetalle();
            limpiarMensajesContactoDetalle();

        }
    }
}

function validarFormularioContactoDetalle(contactoTipo, contacto) {
    var bandera = true;
    limpiarMensajesContactoDetalle();

    if (contactoTipo === '' || contactoTipo === null) {
        $("#msjContactoTipo").removeProp(".hidden");
        $("#msjContactoTipo").text("Tipo de contacto es obligatorio").show();
        bandera = false;
    }

    if (contacto === '' || contacto === null) {
        $("#msjContacto").removeProp(".hidden");
        $("#msjContacto").text("Contacto es obligatorio").show();
        bandera = false;
    }

    return bandera;
}

function limpiarMensajesContactoDetalle() {
    $("#msjContactoTipo").hide();
    $("#msjContacto").hide();
    $("#msjContactoDetalle").hide();

}

function validarContactoDetalleRepetido(contactoTipo, contacto) {
    var valido = true;

    var idContactoDetalle = $('#idContactoDetalle').val();

    //alert(idContactoDetalle + ' : '+ indiceContactoTipo);

    if (idContactoDetalle != '') {
        //alert('igual');
        var indice = buscarContactoDetalle(contactoTipo, contacto);
//        console.log(indice,idContactoDetalle);
        if (indice != idContactoDetalle && indice != -1) {
            $.Notification.autoHideNotify('warning', 'top right', 'Validación', "El contacto ya ha sido agregado");
            valido = false;
        } else {
            valido = true;
        }
    } else {
        //alert('diferente');
        var indice = buscarContactoDetalle(contactoTipo, contacto);
        if (indice > -1) {
            $.Notification.autoHideNotify('warning', 'top right', 'Validación', "El contacto ya ha sido agregado");
            valido = false;
        }
    }

    return valido;
}

function onListarContactoDetalle(data) {
    $('#dataTableContacto tbody tr').remove();
    var cuerpo = "";
    var ind = 0;
    //console.log(data);
    if (!isEmpty(data)) {
        data.forEach(function (item) {

            //listaContactoDetalle.push([ contactoTipo, contactoTipoText, contacto, contactoText,personaContactoId]);


            var eliminar = "<a href='#' onclick = 'eliminarContactoDetalle(\""
                    + item['1'] + "\", \"" + item['2'] + "\")' >"
                    + "<i id='e" + ind + "' class='fa fa-trash-o' style='color:#cb2a2a;'></i></a>";

            var editar = "<a href='#' onclick = 'editarContactoDetalle("
                    + item['0'] + ", \"" + item['1'] + "\", \"" + item['2'] + "\", \"" + ind + "\")' >"
                    + "<i id='e" + ind + "' class='fa fa-edit' style='color:#E8BA2F;'></i></a>&nbsp;&nbsp;&nbsp;";

            cuerpo += "<tr>"
                    + "<td style='text-align:left;'>" + item['3'] + "</td>"
                    + "<td style='text-align:left;'>" + item['1'] + "</td>"
                    + "<td style='text-align:left;'>" + item['5'] + "</td>"
                    + "<td style='text-align:left;'>" + item['6'] + "</td>"
                    + "<td style='text-align:center;'>" + editar + eliminar
                    + "</td>"
                    + "</tr>";

            ind++;
        });

        $('#dataTableContacto tbody').append(cuerpo);
    }
}

function limpiarCamposContactoDetalle() {
    asignarValorSelect2('cboContactoTipo', null);
    asignarValorSelect2('cboContacto', null);
    $('#txtContactoTipo').val('');
    $('#idContactoDetalle').val('');
}

function buscarContactoDetalle(contactoTipoText, contacto) {
    var tam = arrayContactoTipoText.length;
    var ind = -1;
    for (var i = 0; i < tam; i++) {
        if (arrayContactoTipoText[i] === contactoTipoText && arrayContacto[i] === contacto) {
            ind = i;
            break;
        }
    }
    return ind;
}

function editarContactoDetalle(contactoTipo, contactoTipoText, contacto, ind) {
    var indice = ind;

    asignarValorSelect2('cboContacto', arrayContacto[indice]);

    if (isEmpty(contactoTipo)) {
        habilitarDivContactoTipoTexto();
        $('#txtContactoTipo').val(contactoTipoText);
    } else {
        habilitarDivContactoTipoCombo();
        asignarValorSelect2('cboContactoTipo', arrayContactoTipo[indice]);
    }

    $('#idContactoDetalle').val(ind);

}

var listaPersonaContactoEliminado = [];

function eliminarContactoDetalle(contactoTipoText, contacto) {
    var indice = buscarContactoDetalle(contactoTipoText, contacto);
    if (indice > -1) {
        arrayContactoTipo.splice(indice, 1);
        arrayContactoTipoText.splice(indice, 1);
        arrayContacto.splice(indice, 1);
        arrayContactoText.splice(indice, 1);
    }

    if (!isEmpty(arrayPersonaContactoId[indice])) {
        var personaContactoId = arrayPersonaContactoId[indice];
        arrayPersonaContactoId.splice(indice, 1);
        listaPersonaContactoEliminado.push([personaContactoId]);
    }

    listaContactoDetalle = [];
    var tam = arrayContactoTipo.length;
    for (var i = 0; i < tam; i++) {
        listaContactoDetalle.push([arrayContactoTipo[i], arrayContactoTipoText[i], arrayContacto[i], arrayContactoText[i], arrayPersonaContactoId[i]]);
    }

//    console.log(listaContactoDetalle);
    onListarContactoDetalle(listaContactoDetalle);
}

function habilitarDivDireccionTipoTexto() {
    asignarValorSelect2('cboDireccionTipo', null);
    $("#contenedorDireccionTipoDivCombo").hide();
    $("#contenedorDireccionTipoDivTexto").show();
}

function habilitarDivDireccionTipoCombo() {
    $('#txtDireccionTipo').val('');
    $("#contenedorDireccionTipoDivTexto").hide();
    $("#contenedorDireccionTipoDivCombo").show();
}

// PESTAÑA DIRECCION
var listaDireccionDetalle = [];

var arrayUbigeo = [];
var arrayUbigeoText = [];
var arrayDireccionTipo = [];
var arrayDireccionTipoText = [];
var arrayDireccionText = [];
var arrayPersonaDireccionId = [];

function agregarDireccionDetalle() {
    //ids
    var ubigeo = select2.obtenerValor('cboUbigeo');
    var direccionTipo = select2.obtenerValor('cboDireccionTipo');

    //texto
    var ubigeoText = select2.obtenerText('cboUbigeo');
    var direccionTipoText = document.getElementById('txtDireccionTipo').value;
    direccionTipoText = direccionTipoText.trim();
    if (isEmpty(direccionTipoText)) {
        direccionTipoText = select2.obtenerText('cboDireccionTipo');
    }
    var direccionText = document.getElementById('txtDireccion').value;

    var idDireccionDetalle = $('#idDireccionDetalle').val();

    // ids tablas
    var personaDireccionId = null;
    //alert(idDireccionDetalle);

    if (validarFormularioDireccionDetalle(direccionTipoText, ubigeo, direccionText)) {
        if (validarDireccionDetalleRepetido(direccionTipoText, ubigeo, direccionText)) {

            if (idDireccionDetalle != '') {

                arrayDireccionTipo[idDireccionDetalle] = direccionTipo;
                arrayDireccionTipoText[idDireccionDetalle] = direccionTipoText;
                arrayUbigeo[idDireccionDetalle] = ubigeo;
                arrayUbigeoText[idDireccionDetalle] = ubigeoText;
                arrayDireccionText[idDireccionDetalle] = direccionText;

                // ids de tablas relacionadas
                personaDireccionId = arrayPersonaDireccionId[idDireccionDetalle];

                listaDireccionDetalle[idDireccionDetalle] = [direccionTipo, direccionTipoText, ubigeo, ubigeoText, direccionText, personaDireccionId];
            } else {
                //alert('diferente');

                arrayDireccionTipo.push(direccionTipo);
                arrayDireccionTipoText.push(direccionTipoText);
                arrayUbigeo.push(ubigeo);
                arrayUbigeoText.push(ubigeoText);
                arrayDireccionText.push(direccionText);

                listaDireccionDetalle.push([direccionTipo, direccionTipoText, ubigeo, ubigeoText, direccionText, personaDireccionId]);
            }

//            console.log(listaDireccionDetalle);
//            console.log(listaPersonaDireccionEliminado);
            onListarDireccionDetalle(listaDireccionDetalle);
            limpiarCamposDireccionDetalle();
            limpiarMensajesDireccionDetalle();

        }
    }
}

function validarFormularioDireccionDetalle(direccionTipoText, ubigeo, direccionText) {
    var bandera = true;
    limpiarMensajesDireccionDetalle();

    if (direccionTipoText === '' || direccionTipoText === null) {
        $("#msjDireccionTipo").removeProp(".hidden");
        $("#msjDireccionTipo").text("Tipo de direccion es obligatorio").show();
        bandera = false;
    }

    //validar proveedor internacional
    var dataClase = $('#cboClasePersona').val();
    var proveedorInter = false;
    if (!isEmpty(dataClase)) {
        var index = dataClase.indexOf(claseProveedorInternacionalId);
        var indexClienteExt = dataClase.indexOf(claseClienteInternacionalId);
        if (index != -1 || indexClienteExt != -1) {
            proveedorInter = true;
        }
    }

    if (!proveedorInter) {
        if (ubigeo === '' || ubigeo === null) {
            $("#msjUbigeo").removeProp(".hidden");
            $("#msjUbigeo").text("Ubigeo es obligatorio").show();
            bandera = false;
        }
    }

    if (direccionText === '' || direccionText === null) {
        $("#msjDireccion").removeProp(".hidden");
        $("#msjDireccion").text("Direccion es obligatorio").show();
        bandera = false;
    }

    return bandera;
}

function limpiarMensajesDireccionDetalle() {
    $("#msjDireccionTipo").hide();
    $("#msjUbigeo").hide();
    $("#msjDireccion").hide();
    $("#msjDireccionDetalle").hide();

}

function validarDireccionDetalleRepetido(direccionTipoText, ubigeo, direccionText) {
    var valido = true;

    var idDireccionDetalle = $('#idDireccionDetalle').val();

    //alert(idDireccionDetalle + ' : '+ indiceDireccionTipo);

    if (idDireccionDetalle != '') {
        //alert('igual');
        var indice = buscarDireccionDetalle(direccionTipoText, ubigeo, direccionText);
//        console.log(indice,idDireccionDetalle);
        if (indice != idDireccionDetalle && indice != -1) {
            $.Notification.autoHideNotify('warning', 'top right', 'Validación', "La dirección ya ha sido agregado");
            valido = false;
        } else {
            valido = true;
        }

        //validando que solo haya una direccion fiscal.
        if (direccionTipoFiscal == direccionTipoText) {
            var indiceFiscal = buscarDireccionTipoTexto(direccionTipoText);
            if (indiceFiscal != idDireccionDetalle && indiceFiscal != -1) {
                $.Notification.autoHideNotify('warning', 'top right', 'Validación', "Solo se puede registrar una dirección fiscal.");
                valido = false;
            }
        }
    } else {
        //alert('diferente');
        var indice = buscarDireccionDetalle(direccionTipoText, ubigeo, direccionText);
        if (indice > -1) {
            $.Notification.autoHideNotify('warning', 'top right', 'Validación', "La dirección ya ha sido agregado");
            valido = false;
        }

        //validando que solo haya una direccion fiscal.
        if (direccionTipoFiscal == direccionTipoText) {
            var indiceFiscal = buscarDireccionTipoTexto(direccionTipoText);
            if (indiceFiscal > -1) {
                $.Notification.autoHideNotify('warning', 'top right', 'Validación', "Solo se puede registrar una dirección fiscal.");
                valido = false;
            }
        }
    }

    return valido;
}

function buscarDireccionTipoTexto(direccionTipoText) {
    var tam = arrayDireccionTipoText.length;
    var ind = -1;
    for (var i = 0; i < tam; i++) {
        if (arrayDireccionTipoText[i] === direccionTipoText) {
            ind = i;
            break;
        }
    }
    return ind;
}

function onListarDireccionDetalle(data) {
    $('#dataTableDireccion tbody tr').remove();
    var cuerpo = "";
    var ind = 0;
    var ubigeoDescripcion;
    if (!isEmpty(data)) {
        data.forEach(function (item) {
            ubigeoDescripcion = item['3'];

            if (isEmpty(ubigeoDescripcion)) {
                ubigeoDescripcion = '';
            }

            //listaDireccionDetalle.push([direccionTipo,direccionTipoText,ubigeo,ubigeoText,direccionText,personaDireccionId]);

            var eliminar = "<a href='#' onclick = 'eliminarDireccionDetalle(\""
                    + item['1'] + "\", \"" + item['2'] + "\", \"" + item['4'] + "\")' >"
                    + "<i id='e" + ind + "' class='fa fa-trash-o' style='color:#cb2a2a;'></i></a>";

            var editar = "<a href='#' onclick = 'editarDireccionDetalle("
                    + item['0'] + ", \"" + item['1'] + "\", \"" + ind + "\")' >"
                    + "<i id='e" + ind + "' class='fa fa-edit' style='color:#E8BA2F;'></i></a>&nbsp;&nbsp;&nbsp;";

            cuerpo += "<tr>"
                    + "<td style='text-align:left;'>" + item['1'] + "</td>"
                    + "<td style='text-align:left;'>" + ubigeoDescripcion + "</td>"
                    + "<td style='text-align:left;'>" + item['4'] + "</td>"
                    + "<td style='text-align:center;'>" + editar + eliminar
                    + "</td>"
                    + "</tr>";

            ind++;
        });

        $('#dataTableDireccion tbody').append(cuerpo);
    }
}

function limpiarCamposDireccionDetalle() {
    asignarValorSelect2('cboDireccionTipo', null);
    asignarValorSelect2('cboUbigeo', null);
    $('#txtDireccionTipo').val('');
    $('#txtDireccion').val('');
    $('#idDireccionDetalle').val('');
}

function buscarDireccionDetalle(direccionTipoText, ubigeo, direccionText) {
    var tam = arrayDireccionTipoText.length;
    var ind = -1;
    for (var i = 0; i < tam; i++) {
        if (arrayDireccionTipoText[i] === direccionTipoText && arrayUbigeo[i] === ubigeo && arrayDireccionText[i] === direccionText) {
            ind = i;
            break;
        }
    }
    return ind;
}

function editarDireccionDetalle(direccionTipo, direccionTipoText, ind) {
    var indice = ind;

    asignarValorSelect2('cboUbigeo', arrayUbigeo[indice]);

    if (isEmpty(direccionTipo)) {
        habilitarDivDireccionTipoTexto();
        $('#txtDireccionTipo').val(direccionTipoText);
    } else {
        habilitarDivDireccionTipoCombo();
        asignarValorSelect2('cboDireccionTipo', arrayDireccionTipo[indice]);
    }

    $('#txtDireccion').val(arrayDireccionText[indice]);

    $('#idDireccionDetalle').val(ind);

}

var listaPersonaDireccionEliminado = [];

function eliminarDireccionDetalle(direccionTipoText, ubigeo, direccionText) {
    var indice = buscarDireccionDetalle(direccionTipoText, ubigeo, direccionText);
    if (indice > -1) {
        arrayDireccionTipo.splice(indice, 1);
        arrayDireccionTipoText.splice(indice, 1);
        arrayUbigeo.splice(indice, 1);
        arrayUbigeoText.splice(indice, 1);
        arrayDireccionText.splice(indice, 1);
    }

    if (!isEmpty(arrayPersonaDireccionId[indice])) {
        var personaDireccionId = arrayPersonaDireccionId[indice];
        arrayPersonaDireccionId.splice(indice, 1);
        listaPersonaDireccionEliminado.push([personaDireccionId]);
    }

    listaDireccionDetalle = [];
    var tam = arrayDireccionTipo.length;
    for (var i = 0; i < tam; i++) {
        listaDireccionDetalle.push([arrayDireccionTipo[i], arrayDireccionTipoText[i], arrayUbigeo[i], arrayUbigeoText[i], arrayDireccionText[i], arrayPersonaDireccionId[i]]);
    }

//    console.log(listaDireccionDetalle);
    onListarDireccionDetalle(listaDireccionDetalle);
}

function asignarValorSelect2(id, valor)
{
    $("#" + id).select2().select2("val", valor);
    $("#" + id).select2({width: '100%'});
}

function setearComboConvenioSunat() {
    var codigoSunatId = select2.obtenerValor('cboCodigoSunat');

    loaderShow();
    ax.setAccion("obtenerDataConvenioSunat");
    ax.addParamTmp("codigoSunatId", codigoSunatId);
    ax.consumir();
}

function setearInputREINFO() {
    var ruc = trim(document.getElementById('txtReinfo').value);
    var codigo = trim(document.getElementById('txtCodigo').value);

    loaderShow();
    ax.setAccion("obtenerDataREINFO");
    ax.addParamTmp("ruc", ruc);
    ax.addParamTmp("codigo", codigo);
    ax.consumir();
}

function buscarDNI() {
    // Obtener los valores de los campos
    const dni = document.getElementById('txtDNI').value.trim();
    const tipoDNI = document.getElementById('cboTipoDNI').value;

    // Validar si los campos están llenos
    if (!dni) {
        $("#msjtxtDNI").removeProp(".hidden");
        $("#msjtxtDNI").text("Escribe DNI responsable").show();
        bandera = false;
    }

    if (!tipoDNI) {
        $("#msjcboTipoDNI").removeProp(".hidden");
        $("#msjcboTipoDNI").text("Seleccione tipo DNI").show();
        bandera = false;
    }

    document.getElementById('overlay').style.display = 'flex';

    let countdown = 10; // Tiempo en segundos para la cuenta regresiva
    let progress = 0; // Progreso de la barra (si fuera necesario)

    // Actualizar el contador
    const interval = setInterval(() => {
        document.getElementById('contador').innerText = `Tiempo restante: ${countdown--} segundos`;

        if (countdown < 0) {
            clearInterval(interval);
            document.getElementById('contador').innerText = "Proceso completado!";
            loaderClose(); // Cierra el loader si termina antes
        }
    }, 2500);

    ax.setAccion("obtenerDataDNI");
    ax.addParamTmp("dni", dni);
    ax.addParamTmp("tipoDNI", tipoDNI);
    ax.consumir();

    
}

function loaderClose2() {
    document.getElementById('overlay').style.display = 'none'; // Ocultar el overlay
}

function getSelectedItems() {
    var selected = [];
    $(".select_item:checked").each(function() {
        selected.push($(this).data('id'));
    });
    return selected;
}

function guardarSolicitud() {
    
    debugger;
    var ruc = trim(document.getElementById('txtReinfo').value);
    var codigo = trim(document.getElementById('txtCodigoUnico').value);
    var nombre = trim(document.getElementById('txtNombre').value);
    var sector = trim(document.getElementById('txtSector').value);
    var estado = trim(document.getElementById('txtEstado').value);
    var departamento = trim(document.getElementById('txtDepartamento').value);
    var provincia = trim(document.getElementById('txtProvincia').value);
    var distrito = trim(document.getElementById('txtDistrito').value);
    var ubigeo = $('#cboUbigeo').val();
    var direccion = trim(document.getElementById('txtDireccion').value);
    var telefono = trim(document.getElementById('txtTelefono').value);
    var correo = trim(document.getElementById('txtCorreo').value);
    var invitacionId=commonVars.invitacionId;
    var zona = $('#cboTipoArchivo').val();
    var organizacion = trim(document.getElementById('txtOrganizacion').value);
    if (validarSolicitud(telefono,correo,zona)) {
    
    loaderShow();
    if(invitacionId>0){
        ax.setAccion("actualizarInvitacion");
        ax.addParamTmp("invitacionId", invitacionId);
        ax.addParamTmp("ruc", ruc);
        ax.addParamTmp("codigo", codigo);
        ax.addParamTmp("nombre", nombre);
        ax.addParamTmp("sector", sector);
        ax.addParamTmp("estado", estado);
        ax.addParamTmp("departamento", departamento);
        ax.addParamTmp("provincia", provincia);
        ax.addParamTmp("distrito", distrito);
        ax.addParamTmp("ubigeo", ubigeo);
        ax.addParamTmp("direccion", direccion);
        ax.addParamTmp("telefono", telefono);
        ax.addParamTmp("correo", correo);
        ax.addParamTmp("zona", zona);
        ax.addParamTmp("organizacion", organizacion);
        ax.consumir();
    }else{
    ax.setAccion("guardarInvitacion");
    ax.addParamTmp("ruc", ruc);
    ax.addParamTmp("codigo", codigo);
    ax.addParamTmp("nombre", nombre);
    ax.addParamTmp("sector", sector);
    ax.addParamTmp("estado", estado);
    ax.addParamTmp("departamento", departamento);
    ax.addParamTmp("provincia", provincia);
    ax.addParamTmp("distrito", distrito);
    ax.addParamTmp("ubigeo", ubigeo);
    ax.addParamTmp("direccion", direccion);
    ax.addParamTmp("telefono", telefono);
    ax.addParamTmp("correo", correo);
    ax.addParamTmp("zona", zona);
    ax.addParamTmp("organizacion", organizacion);
    ax.consumir();
    }  }
}

function listarDataSolicitudes(data) {
    debugger;
    if(data==false){
        swal.fire({
            title: "Error",
            text: "No se encontró datos para la búsqueda",
            icon: "error",
        });
        $('#txtCodigoUnico').val('');
        $('#txtNombre').val('');
        $('#txtSector').val('');
        $('#txtEstado').val('');
        $('#txtUbicacion').val('');
        document.getElementById('env').disabled = true;
        loaderClose();
    }
    if (data[0].estado === 'VIGENTE') {
        // swal({
        //     title: "Validación Exitosa",
        //     text: "El minero en vías de formalización está 'VIGENTE'.",
        //     icon: "success",
        //     confirmButtonColor: "#5cb85c",
        //     confirmButtonText: "Aceptar",
        // });
        swal("Validación Exitosa", "El minero en vías de formalización está 'VIGENTE'.", "success");
        // swal({
        //     title: "Validación Exitosa",
        //     type: "succes",
        //     text: "El minero en vías de formalización está 'VIGENTE'.",
        //     confirmButtonColor: "#5cb85c",
        //     confirmButtonText: "Aceptar",
        //     closeOnConfirm: false
        // }, function (isConfirm) {
        //     if (isConfirm) {
        //         window.close();
        //     }
    
        // });
        // Llenar los campos con los datos del primer objeto
        $('#txtCodigoUnico').val(data[0].codigo).prop('readonly', true);
        $('#txtNombre').val(data[0].minero).prop('readonly', true);
        $('#txtSector').val(data[0].derecho).prop('readonly', true);
        $('#txtEstado').val(data[0].estado).prop('readonly', true);
        var ubicacion = data[0].departamento + '-' + data[0].provincia + '-' + data[0].distrito;
        $('#txtDepartamento').val(data[0].departamento).prop('readonly', true);
        $('#txtProvincia').val(data[0].provincia).prop('readonly', true);
        $('#txtDistrito').val(data[0].distrito).prop('readonly', true);
        document.getElementById('env').disabled = false;
        loaderClose();
    } else {
        // swal({
        //     title: "Error",
        //     text: "Minero no se encuentra Vigente.",
        //     icon: "error",
        // });
        swal("Error validación", "Minero no se encuentra Vigente.", "error");
        $('#txtCodigoUnico').val('');
        $('#txtNombre').val('');
        $('#txtSector').val('');
        $('#txtEstado').val('');
        $('#txtUbicacion').val('');
        document.getElementById('env').disabled = true;
        loaderClose();
    }
}

function listarDataSolicitudesDNI(data) {
    debugger;
    const adversoBase64 = data.anverso; // Base64 del adverso
    const reversoBase64 = data.reverso; 
    const foto = data.foto_base64;// Base64 del reverso

    const codigo_secreto = data.codigo_secreto;
    const nombre = data.nombre;
    const lugar = data["departamento nacimiento"];
    const madre = data["nombre madre"];
    const padre = data["nombre padre"];
    const restriccion = data["restricción"];
    const fechaN = data["fecha de nacimiento"];
    const direccionA = data["dirección"];
    const estadoC = data["estado civil"];
    const hijos = data["hijos"];
    const estatura = data["estatura"];
    const sexo = data["sexo"];
    

    // Mostrar las imágenes en el HTML
    document.getElementById('imgAdverso').src = adversoBase64;
    document.getElementById('imgReverso').src = reversoBase64;
    document.getElementById('imgFoto').src = foto;

    document.getElementById('txtCodigoS').value = codigo_secreto;
    document.getElementById('txtResponsableNombre').value = nombre;
    document.getElementById('txtLugarN').value = lugar;
    document.getElementById('txtfechaN').value = fechaN;
    document.getElementById('txtDireccionA').value = direccionA;
    document.getElementById('txtEstadoC').value = estadoC;
    document.getElementById('txtHijos').value = hijos;
    document.getElementById('txtEstatura').value = estatura;

    document.getElementById('txtMadre').value = madre;
    document.getElementById('txtPadre').value = padre;
    document.getElementById('txtRestriccion').value = restriccion;
    document.getElementById('txtSexo').value = sexo;
      // Asignar los valores base64 a los campos textarea
      document.getElementById('base64Adverso').value = adversoBase64;
      document.getElementById('base64Reverso').value = reversoBase64;
      document.getElementById('base64Foto').value = foto;
    // Mostrar la sección de imágenes
    document.getElementById('imagenesDNI').style.display = 'block';
    document.getElementById('imagenesDNI2').style.display = 'block';
    ocultarCamposBase64();
}

function ocultarCamposBase64() {
    // Ocultar los campos de texto (textarea) que contienen los valores base64
    document.getElementById('base64Adverso').style.display = 'none'; // Ocultar el base64 del Anverso
    document.getElementById('base64Reverso').style.display = 'none'; // Ocultar el base64 del Reverso
    document.getElementById('base64Foto').style.display = 'none';
}

function onResponseObtenerDataConvenioSunat(data) {
    if (!isEmpty(data)) {
        select2.asignarValor('cboCodigoSunat3', data[0]['sunat_tabla_detalle_id2']);
    } else {
        select2.asignarValor('cboCodigoSunat3', convenioSunatId0);
    }
}

var claseProveedorInternacionalId = "17";
var claseClienteInternacionalId = "18";
function modificarFormularioProveedorInternacional() {

    $("#msjCodigoIdentificacion").hide();
    $("#msjRazonSocial").hide();
    $("#msjClasePersona").hide();

    if (commonVars.personaTipoId == 4) {
        $("#labelUbigeo").html("Ubigeo *");
        var dataClase = $('#cboClasePersona').val();

        if (!isEmpty(dataClase)) {
            var index = dataClase.indexOf(claseProveedorInternacionalId);
            var indexClienteExt = dataClase.indexOf(claseClienteInternacionalId);
            if (index != -1 || indexClienteExt != -1) {
                $("#lblCodigoIdentificacion").html("Código identificación *");
                $("#labelUbigeo").html("Ubigeo");
                $("#contenedorBuscarRUC").hide();
            } else {
                $("#lblCodigoIdentificacion").html("RUC *");
                $("#contenedorBuscarRUC").show();
            }
        } else {
            $("#lblCodigoIdentificacion").html("RUC *");
            $("#contenedorBuscarRUC").show();
        }
    }
}

var contadorCentroCosto = 1;
var dataCentroCosto = [];
var dataCentroCostoPersona = [];

function cargarCentroCostoPersona(data) {
    $('#divCentroCostoPersona').show();
    $('#dataTableCentroCostoPersona tbody tr').remove();
    if (!isEmpty(data)) {
        $.each(data, function (index, item) {
            agregarCentroCostoPersona(item.centro_costo_id, item.porcentaje);
        });
    } else {
        agregarCentroCostoPersona();
    }
}

function agregarCentroCostoPersona(centroCosto, porcentaje) {
    let indice = contadorCentroCosto;

    let eliminar = "<a  onclick='eliminarCentroCostoPersonaDetalle(" + indice + ");'><i class='fa fa-trash-o' style='color:#cb2a2a;'></i></a>";
    let cuerpo = "<tr id='trCentroCostoPersona_" + indice + "'>";
    cuerpo += "<td style='border:0; vertical-align: middle;'>" +
            "<div class='input-group col-lg-12 col-md-12 col-sm-12 col-xs-12'>" +
            "<select name='cboCentroCosto_" + indice + "' id='cboCentroCosto_" + indice + "' class='select2'>" +
            "</select></div></td>";

    cuerpo += "<td style='border:0; vertical-align: middle;'><div class='input-group col-lg-12 col-md-12 col-sm-12 col-xs-12'>" +
            "<div class='input-group col-lg-12 col-md-12 col-sm-12 col-xs-12'>" +
            "<input type='number' id='txtPorcentaje_" + indice + "' name='txtPorcentaje_" + indice + "' class='form-control' required='' aria-required='true' value='' min='1' max='100' style='text-align: right;'/></div></td>" +
            "<td style='text-align:center;'>" + eliminar + "</td>" +
            "</tr>";
    $('#dataTableCentroCostoPersona tbody').append(cuerpo);
    if (!isEmpty(dataCentroCosto)) {
        $.each(dataCentroCosto, function (indexPadre, centroCostoPadre) {
            if (isEmpty(centroCostoPadre.centro_costo_padre_id)) {
                let html = '<optgroup id="' + centroCostoPadre.id + '" label="' + centroCostoPadre['codigo'] + ' | ' + centroCostoPadre['descripcion'] + '">';
                let dataHijos = dataCentroCosto.filter(centroCosto => centroCosto.centro_costo_padre_id == centroCostoPadre.id);
                $.each(dataHijos, function (indexHijo, centroCostoHijo) {
                    html += '<option value="' + centroCostoHijo['id'] + '">' + centroCostoHijo['codigo'] + " | " + centroCostoHijo['descripcion'] + '</option>';
                });
                html += ' </optgroup>';
                $('#cboCentroCosto_' + indice).append(html);
            }
        });

        $("#cboCentroCosto_" + indice).select2({
            width: "100%"
        });

        select2.asignarValor("cboCentroCosto_" + indice, "-1");
        if (!isEmpty(centroCosto)) {
            select2.asignarValor("cboCentroCosto_" + indice, centroCosto);
        }
    }

    if (!isEmpty(porcentaje)) {
        $("#txtPorcentaje_" + indice).val(redondearNumero(porcentaje));
    }

    contadorCentroCosto++;
}

function eliminarCentroCostoPersonaDetalle(indice) {
    $('#trCentroCostoPersona_' + indice).remove();
}

var claseConductor="22";
function mostrarLicenciaConducir() {
    var dataClase = $('#cboClasePersona').val();

    if (!isEmpty(dataClase)) {
        var index = dataClase.indexOf(claseConductor);
        if (index != -1) {
            $("#contenedorLicenciaConducir").show();
        } else {
            $("#contenedorLicenciaConducir").hide();
        }
    } else {
        $("#contenedorLicenciaConducir").hide();
    }
}


