var c = $('#env i').attr('class');
var anchoComboSunat2;
var dataPersonaGlobal;
$(document).ready(function () {
    controlarDomXTipoPersona();
    select2.iniciar();
    anchoComboSunat2 = $("#divCboCodigoSunat2").width();

    ax.setSuccess("successPersona");
    ax.setAccion("obtenerConfiguracionesSolicitudRetiro");
    ax.addParamTmp("solicitudId", commonVars.personaId);
    ax.consumir();

//    tokenPersona = getParameterByName('token');
//    console.log(tokenPersona);



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
$('#txtLotes').keypress(function () {
    $('#msjLotes').hide();
});
$('#cboPlanta').keypress(function () {
    $('#msjPlanta').hide();
});

$('#cboReinfo').keypress(function () {
    $('#msjReinfo').hide();
});
$('#cboZona').keypress(function () {
    $('#msjZona').hide();
});
$('#cboTransportista').keypress(function () {
    $('#msjTransportista').hide();
});
$('#cboConductor').keypress(function () {
    $('#msjConductor').hide();
});
$('#cboVehiculo').keypress(function () {
    $('#msjVehiculo').hide();
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
            case 'obtenerConfiguracionesSolicitudRetiro':
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
            case 'obtenerValidacionTransportista':
                iconoTransportista(response.data);
                loaderClose();
                break;
            case 'obtenerValidacionConductor':
                debugger;
                    iconoConductor(response.data);
                    loaderClose();
                    break;
            case 'obtenerValidacionVehiculo':
                debugger;
                        iconoVehiculo(response.data);
                        loaderClose();
                        break;
            
                    
                
            case 'validarSimilitud':
                onResponseValidarSimilitudes(response.data);
                loaderClose();
                break;
        }
    } else
    {
        switch (response[PARAM_ACCION_NAME]) {
            case 'insertSolicitud':
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
            case 'obtenerValidacionTransportista':
                debugger;
                    mensajeAlertaTransportista(response.message);
                    loaderClose();
                    break;
            case 'obtenerValidacionConductor':
                        debugger;
                            mensajeAlertaConductor(response.message);
                            loaderClose();
                            break;
                            case 'obtenerValidacionVehiculo':
                                debugger;
                                    mensajeAlertaVehiculo(response.message);
                                    loaderClose();
                                    break;


                            
        }
    }
}

var direccionTipoFiscal;
var convenioSunatId0 = null;
function onresponseConfiguracionesPersona(data)
{


debugger;
        select2.cargar("cboTransportista", data.transportistas, "id", ["codigo_identificacion", "nombre_completo"]);
        select2.cargar("cboConductor", data.conductores, "id", ["codigo_identificacion", "nombre_completo"]);
        select2.cargar("cboPlanta", data.plantas, "id", ["codigo_identificacion", "nombre_completo"]);
        select2.cargar("cboReinfo", data.reinfo, "id", ["codigo_identificacion", "nombre_completo"]);
        if(!isEmpty(data.vehiculos)){
            select2.cargar("cboVehiculo", data.vehiculos, "id", "placa");
            }
        if (!isEmpty(data.solicitud)) {
            llenarFormularioEditar(data.solicitud);
        }
        select2.cargar("cboZona", data.zonas, "id", "nombre");

        
     
    loaderClose();
}

function iconoVehiculo(data){
    var capacidad=data[0]['carga_util'];
    var constancia=data[0]['nro_constancia'];

    const inputElement = document.getElementById("txtCapacidad");
    inputElement.value = capacidad;

    const inputElement2 = document.getElementById("txtConstancia");
    inputElement2.value = constancia;
    
    const icono = document.getElementById("iconoVehiculo");
    icono.className = "glyphicon glyphicon-ok"; // Icono de error
    icono.style.color = "green";
    icono.style.display = "inline";

}

function mensajeAlertaVehiculo(mensaje){
    debugger;
    const icono = document.getElementById("iconoVehiculo");
    icono.className = "fa fa-close"; // Icono de error
    icono.style.color = "red";
    icono.style.display = "inline";
    Swal.fire({
        title: 'Validación incorrecta',
        text: mensaje,
        icon: 'error',
        confirmButtonText: 'OK'
    });
}

function iconoConductor(data){
    debugger;
    var licencia=data[5][1];
    if(licencia!='VIGENTE'){
        mensajeAlertaConductor(licencia);
    }
    else{
    const icono = document.getElementById("iconoConductor");
    icono.className = "glyphicon glyphicon-ok"; // Icono de error
    icono.style.color = "green";
    icono.style.display = "inline";
    }
}

function mensajeAlertaConductor(mensaje){
    debugger;
    const icono = document.getElementById("iconoConductor");
    icono.className = "fa fa-close"; // Icono de error
    icono.style.color = "red";
    icono.style.display = "inline";
    Swal.fire({
        title: 'Validación incorrecta',
        text: mensaje,
        icon: 'error',
        confirmButtonText: 'OK'
    });
}


function iconoTransportista(){
    const icono = document.getElementById("iconoTransportista");
    icono.className = "glyphicon glyphicon-ok"; // Icono de error
    icono.style.color = "green";
    icono.style.display = "inline";

}

function mensajeAlertaTransportista(mensaje){
    debugger;
    const icono = document.getElementById("iconoTransportista");
    icono.className = "fa fa-close"; // Icono de error
    icono.style.color = "red";
    icono.style.display = "inline";
    Swal.fire({
        title: 'Validación incorrecta',
        text: mensaje,
        icon: 'error',
        confirmButtonText: 'OK'
    });
}

function cargarListarPersonaCancelar()
{
    loaderShow(null);
    cargarDiv('#window', 'vistas/com/solicitudRetiro/solicitud_retiro_listar.php', 'Listar Solicitudes Retiro');
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
        ax.addParamTmp("personaId", commonVars.personaId);
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

function guardarSolicitud() {
    var fechaEntrega = trim(document.getElementById('txtFechaEntrega').value);
    var capacidad = trim(document.getElementById('txtCapacidad').value);
    var constancia = trim(document.getElementById('txtConstancia').value);
    var lotes = trim(document.getElementById('txtLotes').value);


    var transportista = select2.obtenerValor('cboTransportista');
    var conductor = select2.obtenerValor('cboConductor');
    var vehiculo = select2.obtenerValor('cboVehiculo');
    var zona = select2.obtenerValor('cboZona');
    var planta = select2.obtenerValor('cboPlanta');
    var reinfo = select2.obtenerValor('cboReinfo');



    if (validarSolicitud(fechaEntrega,capacidad,constancia,transportista,conductor,vehiculo,zona,planta,reinfo,lotes)) {

debugger;
        if (commonVars.personaId > 0) {
            actualizarPersona(commonVars.personaId,fechaEntrega,capacidad,constancia,transportista,conductor,vehiculo,zona,planta,reinfo,lotes);
        } else {
            insertarSolicitud(fechaEntrega,capacidad,constancia,transportista,conductor,vehiculo,zona,planta,reinfo,lotes);
        }

    } else {
        loaderClose();
    }
}

function insertarSolicitud(fechaEntrega,capacidad,constancia,transportista,conductor,vehiculo,zona,planta,reinfo,lotes)
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
    ax.addParamTmp("reinfo", reinfo);
    ax.addParamTmp("lotes", lotes);
    ax.consumir();
}

function actualizarPersona(solicitudId,fechaEntrega,capacidad,constancia,transportista,conductor,vehiculo,zona,planta,reinfo,lotes)
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
    ax.addParamTmp("reinfo", reinfo);
    ax.addParamTmp("lotes", lotes);
    ax.consumir();
}

function obtenerCentroCostoPersona() {
    let arrayCentroCostoPersona = [];
    let banderaValidacion = false;
    let totalPorcentaje = 0;
    for (var i = 1; i <= contadorCentroCosto; i++) {
        if ($('#cboCentroCosto_' + i).length > 0) {
            let centro_costo_id = select2.obtenerValor('cboCentroCosto_' + i);
            let porcentaje = $('#txtPorcentaje_' + i).val();

            if (isEmpty(centro_costo_id) || isEmpty(porcentaje)) {
                $.Notification.autoHideNotify('warning', 'top right', 'Validación', "Debe seleccionar los centro de costo o el porcentaje.");
                banderaValidacion = true;
                break;
            } else {
                totalPorcentaje += porcentaje * 1;
                arrayCentroCostoPersona.push({centro_costo_id: centro_costo_id, porcentaje: porcentaje});
            }
        }
    }

    if (banderaValidacion) {
        return [];
    }

    if (totalPorcentaje != 100) {
        $.Notification.autoHideNotify('warning', 'top right', 'Validación', "El total de porcentaje debe ser 100%.");
        return [];
    }

    return arrayCentroCostoPersona;
}

function DNIObligatorio(idClasePersona) {
    var required = true;
    var a = $('#cboClasePersona').val();
    //Si esta vacio el combo, retorna validación. || Como el resto exigen DNI, si el tamaño del array es > 1 , contacto no es el unico y retorna validacion
    if (isEmpty(a) || a.length > 1) {
        return true;
    }


    $.each(a, function (i, e) {
        if (a[i] == idClasePersona)
            required = false
    });
    return required;
}
function validarSolicitud(fechaEntrega,capacidad,constancia,transportista,conductor,vehiculo,zona,planta,reinfo,lotes) {

    //expresiones de validacion 
    var expresion_email = /^[a-zA-Z0-9\._-]+@[a-zA-Z0-9-]{2,}[.][a-zA-Z]{2,4}$/;


    //var requiereDNI = DNIObligatorio()==-3?false:true; //Si contacto esta seleccionado
    var bandera = true;



    if (isEmpty(fechaEntrega))
    {
//        console.log(nombre);
        $("#msjFechaEntrega").removeProp(".hidden");
        $("#msjFechaEntrega").text("Ingresar una fecha").show();
        bandera = false;
    }


    if (isEmpty(capacidad))
    {
        $("#msjCapacidad").removeProp(".hidden");
        $("#msjCapacidad").text("Ingresa la Capacidad").show();
        bandera = false;
    }

    if (isEmpty(constancia))
    {
        $("#msjConstancia").removeProp(".hidden");
        $("#msjConstancia").text("Ingresa Constancia").show();
        bandera = false;
    }

    if (isEmpty(lotes))
        {
            $("#msjLotes").removeProp(".hidden");
            $("#msjLotes").text("Ingresa Lotes").show();
            bandera = false;
        }

    
    if (isEmpty(transportista))
    {
        $("#msjTransportista").removeProp(".hidden");
        $("#msjTransportista").text("Seleccione al Transportista").show();
        bandera = false;
    }

        
    if (isEmpty(conductor))
    {
        $("#msjConductor").removeProp(".hidden");
        $("#msjConductor").text("Seleccione al Conductor").show();
        bandera = false;
    }

    if (isEmpty(vehiculo))
    {
        $("#msjVehiculo").removeProp(".hidden");
        $("#msjVehiculo").text("Seleccione al Vehiculo").show();
        bandera = false;
    }


    
    if (isEmpty(zona))
    {
        $("#msjZona").removeProp(".hidden");
        $("#msjZona").text("Seleccione la Zona").show();
        bandera = false;
    }

        
    if (isEmpty(planta))
    {
        $("#msjPlanta").removeProp(".hidden");
        $("#msjPlanta").text("Seleccione la Planta").show();
        bandera = false;
    }
    if (isEmpty(reinfo))
        {
            $("#msjReinfo").removeProp(".hidden");
            $("#msjReinfo").text("Seleccione el REINFO").show();
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
    $("#txtLotes").val(data[0].cantidad_lotes);


    //tablas sunat    
    select2.asignarValor('cboTransportista', data[0]['persona_transportista_id']);
    select2.asignarValor('cboConductor', data[0]['persona_conductor_id']);
    select2.asignarValor('cboVehiculo', data[0]['vehiculo_id']);
    select2.asignarValor('cboZona', data[0]['zona_id']);
    select2.asignarValor('cboPlanta', data[0]['persona_planta_id']);
    select2.asignarValor('cboReinfo', data[0]['persona_reinfo_id']);
    
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

function validarTransportista(){
    var transportista = select2.obtenerValor('cboTransportista');
    loaderShow();
    ax.setAccion("obtenerValidacionTransportista");
    ax.addParamTmp("transportista_id", transportista);
    ax.consumir(); 
}


function validarConductor(){
    var conductor = select2.obtenerValor('cboConductor');
    loaderShow();
    ax.setAccion("obtenerValidacionConductor");
    ax.addParamTmp("conductor_id", conductor);
    ax.consumir(); 
}

function validarVehiculo(){
    var vehiculo = select2.obtenerValor('cboVehiculo');
    loaderShow();
    ax.setAccion("obtenerValidacionVehiculo");
    ax.addParamTmp("vehiculo_id", vehiculo);
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