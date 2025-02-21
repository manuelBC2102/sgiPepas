var c = $('#env i').attr('class');
var anchoComboSunat2;
var dataPersonaGlobal;
$(document).ready(function () {
    controlarDomXTipoPersona();
    select2.iniciar();
    anchoComboSunat2 = $("#divCboCodigoSunat2").width();
loaderShow();
    ax.setSuccess("successPersona");
    ax.setAccion("obtenerConfiguracionesRetenciones");
    ax.addParamTmp("solicitudId", commonVars.personaId);
    ax.consumir();

//    tokenPersona = getParameterByName('token');
//    console.log(tokenPersona);

// llenarcomboZonas();
llenarcomboCarreta();
});


function togglePesaje(show) {
    const pesajeContainer = document.getElementById('pesajeContainer');
    pesajeContainer.style.display = show ? 'block' : 'none';
    if (show==false) {
        document.getElementById('txtPesaje').value = '';
        document.getElementById('txtPesajeFinal').value = '';
        document.getElementById('fechaPesajeInicial').value = '';
        document.getElementById('txtPesajeFinal').value = '';
        
    }
}

function traerPesaje(variable) {
    debugger;
    loaderShow();
    ax.setAccion("obtenerPesajeSuminco");
    ax.addParamTmp("variable", variable);
    ax.consumir();
}

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

function deshabilitarBoton2()
{
    document.getElementById("buscar").disabled = true;
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
            case 'obtenerConfiguracionesRetenciones':
                onresponseConfiguracionesPersona(response.data);
                dataPersonaGlobal = response.data.personaNatural;
                //console.log(dataPersonaGlobal);
                break;
            case 'insertRetencionFacturas':
                mostrarOk("Retención Registrada");
                loaderClose();
                habilitarBoton();
                cargarListarPersonaCancelar();
                break;
            case 'guardarActaRetiro':
                debugger;
                mostrarOk("Acta Retiro Registrada");
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
            case 'obtenerDataProveedor':
                onresponseConfiguracionesProveedor(response.data);
                loaderClose();
                break;
            case 'obtenerZonas':
                    onResponsellenarcomboTipoArchivo(response.data);
                    break;

                    case 'obtenerCarretas':
                        onResponsellenarcomboCarreta(response.data);
                        break;

                    
            case 'obtenerPesajeSuminco':
                        onResponseSuminco(response.data);
                        break;
        }
    } else
    {
        switch (response[PARAM_ACCION_NAME]) {
            case 'insertRetencionFacturas':
                loaderClose();
                habilitarBoton();
                break;

            case 'guardarActaRetiro':
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
            case 'obtenerDataProveedor':
                loaderClose();
                habilitarBoton();
                break;

            case 'obtenerZonas':
                mostrarMensajeError("Usuario no tiene zonas asignadas");
                deshabilitarBoton2;
                    break;

                    case 'obtenerCarretas':
                        mostrarMensajeError("No hay carretas asignadas");
                        deshabilitarBoton2;
                            break;
        }
    }
}


function onResponsellenarcomboTipoArchivo(data) {
    arraycomboTipoArchivo= data;
     
    $('#cboTipoArchivo').empty();
    
    $.each(data, function(index, item) {
        $('#cboTipoArchivo').append(new Option(item.nombre, item.id));
 
    });
    $('#cboTipoArchivo').trigger('change'); // Actualizar select2
   
}

function onResponsellenarcomboCarreta(data) {
    // Limpiar el select antes de agregar nuevas opciones
    $('#cboCarreta').empty();
    
    // Agregar la opción nula al principio
    $('#cboCarreta').append(new Option("Seleccione una placa", "", true, true)); // Esta opción será seleccionada por defecto

    // Llenar el select2 con las placas obtenidas en "data"
    $.each(data, function(index, item) {
        $('#cboCarreta').append(new Option(item.placa, item.id)); // Usamos "item.placa" como texto y "item.id" como valor
    });

    // Actualizar el select2 para reflejar los cambios
    $('#cboCarreta').trigger('change');
}

let pesajeInicial = null;
let pesajeFinal = null;
function onResponseSuminco(data, tipo) {
    if (data) {
        var pesaje = data.pesaje;
        var variable = data.variable;
        var fechaHora = new Date(); // Fecha y hora actual

        loaderClose();
        swal("Validación Exitosa", "Pudimos obtener el pesaje de SUMINCO: " + pesaje + " KG", "success");

        if (variable == 'inicial') {
            document.getElementById('txtPesaje').value = pesaje;
            pesajeInicial=pesaje;
            // Mostrar fecha y hora del pesaje inicial
            document.getElementById('fechaPesajeInicial').innerHTML = `Pesaje obtenido el: <span>${fechaHora.toLocaleString()}</span>`;
        } else {
            document.getElementById('txtPesajeFinal').value = pesaje;
            pesajeFinal=pesaje;
            // Mostrar fecha y hora del pesaje final
            document.getElementById('fechaPesajeFinal').innerHTML = `Pesaje obtenido el: <span>${fechaHora.toLocaleString()}</span>`;
        }
    } else {
        loaderClose();
        document.getElementById('txtPesaje').value = ''; 
        document.getElementById('txtPesajeFinal').value = ''; 
        swal("Error Conexión", "No pudimos conectar con la balanza de SUMINCO", "error");
    }
}

function llenarcomboZonas() {
    ax.setAccion("obtenerZonas");
 
    ax.consumir();
}

function llenarcomboCarreta() {
    ax.setAccion("obtenerCarretas");
 
    ax.consumir();
}

function cargarListarPersonaCancelar()
{
    loaderShow(null);
    cargarDiv('#window', 'vistas/com/retenciones/retenciones.php');
}
var direccionTipoFiscal;
var convenioSunatId0 = null;
function onresponseConfiguracionesPersona(data)
{
loaderShow();

debugger;
       $("#txtPersonaE").val(data.persona[0]['nombre']);
       $("#txtTipoC").val(data.tipoCambio);
       $("#txtFecha").val(data.fecha);
      
  


    loaderClose();
}

function onresponseConfiguracionesProveedor(data)
{
loaderShow();

debugger;
       $("#txtRazonSocial").val(data.razonSocial);
       $("#txtUbigeo").val(data.ubigeoCodigo);
       $("#txtDepartamento").val(data.departamento);
       $("#txtProvincia").val(data.provincia);
       $("#txtDistrito").val(data.distrito);
       $("#txtDireccion").val(data.domicilio_fiscal);
      
  


    loaderClose();
}



function cargarListarActaCancelar() {
    

    // Si hay datos de pesaje sin guardar, mostrar la advertencia
    if (pesajeInicial || pesajeFinal) {
        var resultado = confirm("¿Estás seguro que deseas salir de esta página?");

if (resultado) {
    // Si el usuario selecciona "Aceptar", permanece en la página actual
    alert("Te quedas en la página.");
    loaderClose();
} 
    } else {
        // Si no hay datos sin guardar, simplemente realizamos la acción sin mostrar la advertencia
        cargarDiv('#window', 'vistas/com/retenciones/retenciones.php', 'Retenciones');
    }
}


function cerrarModal() {
    var cancelButton = document.querySelector('.cancel');
    if (cancelButton) {
        cancelButton.click();  // Simula el clic en el botón de "Cancelar"
    }
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
    var tipoCambio = trim(document.getElementById('txtTipoC').value);
    var ruc = trim(document.getElementById('txtRUC').value);
    var razonSocial = trim(document.getElementById('txtRazonSocial').value);
   
    var ubigeo = trim(document.getElementById('txtUbigeo').value);
    var departamento = trim(document.getElementById('txtDepartamento').value);
    var provincia = trim(document.getElementById('txtProvincia').value);
    var distrito = trim(document.getElementById('txtDistrito').value);
    var direccion = trim(document.getElementById('txtDireccion').value);
    
    var factura = trim(document.getElementById('txtFactura').value);
    var fechaFactura = trim(document.getElementById('txtFechaFactura').value);
    var montoFactura = trim(document.getElementById('txtMontoFactura').value);
    var porcentajeRetencion = trim(document.getElementById('txtRetencion').value);
    var fechaPago = trim(document.getElementById('txtFechaPago').value);
    

    var moneda = select2.obtenerValor('cboMoneda');
 

    loaderShow(null);
    deshabilitarBoton();
    ax.setAccion("insertRetencionFacturas");
    ax.addParamTmp("tipoCambio", tipoCambio);
    ax.addParamTmp("ruc", ruc);
    ax.addParamTmp("razonSocial", razonSocial);
    ax.addParamTmp("ubigeo", ubigeo);
    ax.addParamTmp("departamento", departamento);
    ax.addParamTmp("provincia", provincia);
    ax.addParamTmp("distrito", distrito);
    ax.addParamTmp("direccion", direccion);
    ax.addParamTmp("factura", factura);
    ax.addParamTmp("fechaFactura", fechaFactura);
    ax.addParamTmp("montoFactura", montoFactura);
    ax.addParamTmp("porcentajeRetencion", porcentajeRetencion);
    ax.addParamTmp("fechaPago", fechaPago);
    ax.addParamTmp("moneda", moneda);
    ax.consumir();
}

// function insertarSolicitud(fechaEntrega,capacidad,constancia,transportista,conductor,vehiculo,zona,planta)
// {
//     loaderShow(null);
//     deshabilitarBoton();
//     ax.setAccion("insertSolicitud");
//     ax.addParamTmp("fechaEntrega", fechaEntrega);
//     ax.addParamTmp("capacidad", capacidad);
//     ax.addParamTmp("constancia", constancia);
//     ax.addParamTmp("transportista", transportista);
//     ax.addParamTmp("conductor", conductor);
//     ax.addParamTmp("vehiculo", vehiculo);
//     ax.addParamTmp("zona", zona);
//     ax.addParamTmp("planta", planta);

//     ax.consumir();
// }

// function actualizarPersona(solicitudId,fechaEntrega,capacidad,constancia,transportista,conductor,vehiculo,zona,planta)
// {
//     loaderShow(null);
//     deshabilitarBoton();
//     ax.setAccion("updateSolicitud");
//     ax.addParamTmp("id", solicitudId)
//     ax.addParamTmp("fechaEntrega", fechaEntrega);
//     ax.addParamTmp("capacidad", capacidad);
//     ax.addParamTmp("constancia", constancia);
//     ax.addParamTmp("transportista", transportista);
//     ax.addParamTmp("conductor", conductor);
//     ax.addParamTmp("vehiculo", vehiculo);
//     ax.addParamTmp("zona", zona);
//     ax.addParamTmp("planta", planta);
//     ax.consumir();
// }

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
function validarSolicitud(fechaEntrega,capacidad,constancia,transportista,conductor,vehiculo,zona,planta) {

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

function setearInputPlaca() {
    var ruc = trim(document.getElementById('txtRUC').value);
    loaderShow();
    ax.setAccion("obtenerDataProveedor");
    ax.addParamTmp("ruc", ruc);
    ax.consumir();
}
function getSelectedItems() {
    var selected = [];
    $(".select_item:checked").each(function() {
        selected.push($(this).data('id'));
    });
    return selected;
}

// function guardarSolicitud() {
//     debugger;
//     var selectedItems = getSelectedItems();
//     var file = $('#secretImg').val();
//     var placaV = trim(document.getElementById('txtPlacaV').value);
//     var comentario = trim(document.getElementById('txtComentario').value);
//     var pesaje = trim(document.getElementById('txtPesaje').value);
//     var pesajeFinal = trim(document.getElementById('txtPesajeFinal').value);
//     // var fechaInicio = trim(document.getElementById('fechaPesajeInicial').value);
//     var fechaInicio = document.getElementById('fechaPesajeInicial').querySelector('span').textContent;
//     var fechaFinal = document.getElementById('fechaPesajeFinal').querySelector('span').textContent;
//     // var fechaFinal = trim(document.getElementById('fechaPesajeFinal').value);
//     var carreta = $('#cboCarreta').val();
//     var zona = $('#cboTipoArchivo').val();
//     loaderShow();
//     ax.setAccion("guardarActaRetiro");
//     ax.addParamTmp("placa", placaV);
//     ax.addParamTmp("file", file);
//     ax.addParamTmp("selectedItems", selectedItems);
//     ax.addParamTmp("comentario", comentario);
//     ax.addParamTmp("pesaje", pesaje);
//     ax.addParamTmp("zona", zona);
//     ax.addParamTmp("pesajeFinal", pesajeFinal);
//     ax.addParamTmp("fechaInicio", fechaInicio);
//     ax.addParamTmp("fechaFinal", fechaFinal);
//     ax.addParamTmp("carreta", carreta);
//     ax.consumir();

// }

function toggleCarreta(hasCarreta) {
    const carretaSelectContainer = document.getElementById('carretaSelectContainer');
    
    // Mostrar u ocultar el select2 según la respuesta
    if (hasCarreta) {
        carretaSelectContainer.style.display = 'block';
        loadPlacasCarreta(); // Cargar las placas disponibles si se tiene carreta
    } else {
        carretaSelectContainer.style.display = 'none';
    }
}

function listarDataSolicitudes(data) {
    $("#datatable2").empty();
    var cuerpo_total = '';
    var cuerpo = '';
    var cabeza = '<table id="datatable" class="table table-striped table-bordered"><thead>' +
        "<tr>" +
        "<th style='text-align:center;'><input type='checkbox' id='select_all' /></th>" +
        "<th style='text-align:center;'>Fecha Entrega</th>" +
        "<th style='text-align:center;'>Zona</th>" +
        "<th style='text-align:center;'>Vehiculo</th>" +
        "<th style='text-align:center;'>Conductor</th>" +
        "<th style='text-align:center;'>REINFO</th>" +
        "<th style='text-align:center;'>Transportista</th>" +
        "<th style='text-align:center;'>Planta</th>" +
        "<th style='text-align:center;'>Estado</th>" +
        "</tr>" +
        "</thead><tbody>";
    $.each(data, function (index, item) {
        cuerpo = "<tr>" +
            "<td style='text-align:center;'><input type='checkbox' class='select_item' data-id='" + item.id + "' /></td>" +
            "<td style='text-align:left;'>" + item.fecha_entrega + "</td>" +
            "<td style='text-align:left;'>" + item.zona + "</td>" +
            "<td style='text-align:left;'>" + item.vehiculo + "</td>" +
            "<td style='text-align:left;'>" + item.conductor + "</td>" +
            "<td style='text-align:left;'>" + item.sociedad + "</td>" +
            "<td style='text-align:left;'>" + item.transportista + "</td>" +
            "<td style='text-align:left;'>" + item.planta + "</td>" +
            "<td style='text-align:left;'>" + item.estado + "</td>" +
            "</tr>";
        cuerpo_total = cuerpo_total + cuerpo;
    });
    var pie = '</tbody></table>';
    var html = cabeza + cuerpo_total + pie;
    $("#datatable2").append(html);

    // Agregar evento de selección/deselección de todos los checkboxes
    $("#select_all").click(function() {
        $(".select_item").prop('checked', this.checked);
    });
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

window.addEventListener('beforeunload', function (e) {
    // Verificamos si los datos de pesaje están presentes
    if (pesajeInicial || pesajeFinal) {
        // Este mensaje aparece en algunos navegadores y sirve como advertencia
        var confirmationMessage = 'Tienes datos no guardados. Si sales de la página, perderás la información del pesaje.';
        
        // Para Firefox y algunos otros navegadores
        (e || window.event).returnValue = confirmationMessage; // Standard for most browsers
        return confirmationMessage; // For some browsers
    }
});