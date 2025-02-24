var c = $('#env i').attr('class');
var anchoComboSunat2;
 
var dataPersonaGlobal;
$(document).ready(function () {
    ;
    controlarDomXTipoPersona();
    select2.iniciar();
    anchoComboSunat2 = $("#divCboCodigoSunat2").width();
    // $("#liPersonaDocumentos").hide();
    // $("#litPersonaPlantaDocumento").hide();


    ax.setSuccess("successPersona");
    ax.setAccion("obtenerConfiguracionesPersona");
   
    // $("#tabDocumentos").hide();
    ax.addParamTmp("personaId", commonVars.personaId);
    ax.addParamTmp("personaTipoId", commonVars.personaTipoId);
    ax.addParamTmp("empresaId", commonVars.empresa);
    ax.consumir();
    llenarTablaArchivos();
    llenarTablaArchivosPlanta();
    llenarcomboTipoArchivo();
    // Mostrar u ocultar documentos basados en personaTipoId y la selección en el combo
    controlarVisibilidadDocumentos();
    $('#contenedorGuardarNuevoDocumento').hide();

//    tokenPersona = getParameterByName('token');
//    console.log(tokenPersona);



});

$("#cboClasePersona").on("change", function (e) {
    ;
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
        // if (item == -2) {
        //     bandera_tabla_centro_costo = true;
        // }
    });
    if (bandera_tabla_centro_costo) {
        cargarCentroCostoPersona(dataCentroCostoPersona);
    } else {
        $('#divCentroCostoPersona').hide();
    }
});

$('#txtCodigoIdentificacion').keypress(function () {
    $('#msjCodigoIdentificacion').hide();
});
$('#txtNombre').keypress(function () {
    $('#msjNombre').hide();
});
$('#txtRazonSocial').keypress(function () {
    $('#msjRazonSocial').hide();
});
$('#txtApellidoPaterno').keypress(function () {
    $('#msjApellidoPaterno').hide();
});
$('#txtApellidoMaterno').keypress(function () {
    $('#msjApellidoMaterno').hide();
});
$('#txtTelefono').keypress(function () {
    $('#msjTelefono').hide();
});
$('#txtCelular').keypress(function () {
    $('#msjCelular').hide();
});
$('#txtEmail').keypress(function () {
    $('#msjEmail').hide();
});
$('#txtRUC').keypress(function () {
    $('#msjRUC').hide();
});
$('#txtRazonSocial').keypress(function () {
    $('#msjRazonSocial').hide();
});
$('#txtDireccion').keypress(function () {
    $('#msjDireccion').hide();
});
$('#divCentroCostoPersona').hide();
// subir documento
function llenarTablaArchivos(){
     
    ax.setAccion("obtenerArchivos");
    ax.addParamTmp("personaId", commonVars.personaId);
    ax.consumir();

    ax.setAccion("obtenerConfiguracionesPersona");
    ax.addParamTmp("personaId", commonVars.personaId);
    ax.addParamTmp("personaTipoId", commonVars.personaTipoId);
    ax.addParamTmp("empresaId", commonVars.empresa);
    ax.consumir();
}
function llenarTablaArchivosPlanta(){
    ax.setAccion("obtenerArchivosPlanta");
    ax.addParamTmp("personaId", commonVars.personaId);
    ax.consumir();

}

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
            case 'obtenerConfiguracionesPersona':
                onresponseConfiguracionesPersona(response.data);
                dataPersonaGlobal = response.data.personaNatural;
                //console.log(dataPersonaGlobal);
                break;
            // subir documento
            case 'obtenerTipoDocumento23':
                onResponsellenarcomboTipoArchivo(response.data);
                break;
            case'insertArchivo':
                if(response.data['0'].vout_exito == 1){
                    llenarTablaArchivos();
                    loaderClose();
                    mostrarOk(response.data['0'].vout_mensaje);
                }else{
                    mostrarAdvertencia(response.data['0'].vout_mensaje);
                }
                break; 
            case 'obtenerArchivos':
                renderArchivo(response.data);
                break; 
            case 'insertPersona':
                mostrarOk(response.data['0'].vout_mensaje);
                validarToken(response.data['0'].id);
                loaderClose();
                habilitarBoton();
                cargarListarPersonaCancelar();
                break;
            case 'eliminarArchivos':
                 
                if(response.data['0'].vout_exito == 1){
                    mostrarOk(response.data['0'].vout_mensaje);
                    llenarTablaArchivos();
                    loaderClose();
                }else{
                    mostrarAdvertencia(response.data['0'].vout_mensaje);
                }
                break; 
            case 'obtenerArchivosPlanta':
                renderArchivoPlanta(response.data);
                break; 

            case 'insertTipoDocumentoPlanta':
                loaderClose();
                mostrarOk(response.data['0'].vout_mensaje);
                llenarcomboTipoArchivo();
                
                break;
            case 'insertTipoDocumentoPLantaXPersona':
                loaderClose();
                mostrarOk(response.data['0'].vout_mensaje);
                llenarTablaArchivosPlanta();
                break; 
            case'insertTipoDocumentoPLantaXPersona':
                if(response.data['0'].vout_exito == 1){
                    mostrarOk(response.data['0'].vout_mensaje);
                }else{
                    mostrarAdvertencia(response.data['0'].vout_mensaje);
                }
                break; 
            case 'eliminarTipoDocumentoPLantaXPersona':
                 
                if(response.data['0'].vout_exito == 1){
                    mostrarOk(response.data['0'].vout_mensaje);
                    llenarTablaArchivosPlanta();
                    loaderClose();
                }else{
                    mostrarAdvertencia(response.data['0'].vout_mensaje);
                }
                break; 
                
                

            case 'updatePersona':
                mostrarOk(response.data['0'].vout_mensaje);
                validarToken(response.data['0'].persona_id);
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
        }
    } else
    {
        switch (response[PARAM_ACCION_NAME]) {
            case 'insertPersona':
                loaderClose();
                habilitarBoton();
                break;
            case 'updatePersona':
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
        }
    }
}
// LLENAR COMBO TIPOS DE ARCHIVOS DESDE EL CONTROLADOR
function llenarcomboTipoArchivo() {
    ax.setAccion("obtenerTipoDocumento23");
 
    ax.consumir();
}
// LLENAR EL COMBO EN EL SELECT
var arraycomboTipoArchivo = [];
function onResponsellenarcomboTipoArchivo(data) {
    arraycomboTipoArchivo= data;
     
    $('#cboTipoArchivo').empty();
    $('#cboTipoArchivo2').empty();
    $.each(data, function(index, item) {
        $('#cboTipoArchivo').append(new Option(item.nombre, item.id));
        $('#cboTipoArchivo2').append(new Option(item.nombre, item.id));
    });
    $('#cboTipoArchivo').trigger('change'); // Actualizar select2
    $('#cboTipoArchivo2').trigger('change'); // Actualizar select2
}

var direccionTipoFiscal;
var convenioSunatId0 = null;

function onresponseConfiguracionesPersona(data)
{
   
    //console.log(data.personaContacto);
    var empresaIds = '';

    $.each(data.empresa, function (i, item) {
        empresaIds = empresaIds + ";" + item["id"];
    });

    if (commonVars.personaId > 0) {
        // mostrar pestaña Documentos
         
        
        // mostrarDocumentosClaseReinfo();
        select2.cargar("cboEmpresa", data.empresa, "id", "razon_social");
        select2.cargar("cboClasePersona", data.persona_clase, "id", "descripcion");
        controlarVisibilidadDocumentos();

    } else {
         
        // ocultarDocumentosClaseReinfo();
        // ocultarDocumentoClasePlanta();
        select2.cargarAsignaUnico("cboEmpresa", data.empresa, "id", "razon_social");
        select2.cargarAsignaUnico("cboClasePersona", data.persona_clase, "id", "descripcion");
    }

    //cargar combos sunat    
    $("#cboCodigoSunat2").select2({width: anchoComboSunat2 + "px"});

    select2.cargar("cboCodigoSunat", data.dataSunatDetalle, "id", ["codigo", "descripcion"]);
    select2.cargar("cboCodigoSunat2", data.dataSunatDetalle2, "id", ["codigo", "descripcion"]);
    select2.cargar("cboCodigoSunat3", data.dataSunatDetalle3, "id", ["codigo", "descripcion"]);
    select2.cargarSeleccione("cboCuentaContable", data.cuentaContable, "id", ["codigo", "descripcion"],'Seleccione');

    convenioSunatId0 = data.dataSunatDetalle3[0]["id"];

    select2.asignarValor('cboEmpresa', empresaIds.split(";"));

    if (personaTipoVentana == 2) {
        select2.asignarValor('cboClasePersona', ["-3"]);
    }

    if (commonVars.personaTipoId != 2) {
        //validamos si tiene permiso para visualizar la clase de persona "Contacto"
        var tipoContacto = false;
        $.each(data.personaClaseXUsuario, function (i, item) {
            if (parseInt(item.id) == -3) {
                tipoContacto = true;
                return false;
            }
        });

        if (tipoContacto) {
            $("#liPersonaContactos").show();

            if (!isEmpty(data.personaNatural)) {
                select2.cargar("cboContacto", data.personaNatural, "id", ["persona_nombre", "codigo_identificacion"]);
            }

            if (!isEmpty(data.contactoTipo)) {
                select2.cargar("cboContactoTipo", data.contactoTipo, "id", "descripcion");
            }
        } else {
            $("#liPersonaContactos").hide();
        }
    }

    if (!isEmpty(data.direccionTipo)) {
        //obtengo la descripcion de la direccion fiscal.
        $.each(data.direccionTipo, function (i, item) {
            if (parseInt(item.id) == -1) {
                direccionTipoFiscal = item.descripcion;
                return false;
            }
        });

        select2.cargar("cboDireccionTipo", data.direccionTipo, "id", "descripcion");
    }

    if (!isEmpty(data.dataUbigeo)) {
        select2.cargar("cboUbigeo", data.dataUbigeo, "id", ["ubigeo_codigo", "ubigeo_dep", "ubigeo_prov", "ubigeo_dist"]);
    }

    if (!isEmpty(data.dataCentroCosto)) {
        dataCentroCosto = data.dataCentroCosto;
    }

    if (!isEmpty(data.dataCentroCostoPersona)) {
        dataCentroCostoPersona = data.dataCentroCostoPersona;
    }

    if (!isEmpty(data.persona)) {
        llenarFormularioEditar(data.persona);
    }

    if (!isEmpty(data.personaDireccion)) {
        llenarFormularioPersonaDireccion(data.personaDireccion);
    }

    if (!isEmpty(data.personaContacto)) {
        llenarFormularioPersonaContacto(data);
    }

    loaderClose();
}

function llenarFormularioPersonaDireccion(dataPersonaDireccion) {
    // llenado de los array de Persona contacto

//    console.log(dataPersonaDireccion);
    $(dataPersonaDireccion).each(function (index) {
        //ids
        var ubigeo = dataPersonaDireccion[index].ubigeo_id;
        var direccionTipo = dataPersonaDireccion[index].direccion_tipo_id;

        //texto
        var ubigeoText = dataPersonaDireccion[index].ubigeo_descripcion;
        var direccionTipoText = dataPersonaDireccion[index].direccion_tipo_descripcion;
        direccionTipoText = direccionTipoText.trim();
        var direccionText = dataPersonaDireccion[index].direccion;


        // ids de tablas relacionadas
        var personaDireccionId = dataPersonaDireccion[index].id;

        arrayDireccionTipo.push(direccionTipo);
        arrayDireccionTipoText.push(direccionTipoText);
        arrayUbigeo.push(ubigeo);
        arrayUbigeoText.push(ubigeoText);
        arrayDireccionText.push(direccionText);
        // array ids
        arrayPersonaDireccionId.push(personaDireccionId);

        listaDireccionDetalle.push([direccionTipo, direccionTipoText, ubigeo, ubigeoText, direccionText, personaDireccionId]);
        onListarDireccionDetalle(listaDireccionDetalle);

    });
    // fin
}

function llenarFormularioPersonaContacto(data) {
    // llenado de los array de Persona contacto

    var dataPersonaContacto = data.personaContacto;
    var dataPersonaNatural = data.personaNatural;
    //console.log(dataPersonaContacto);
    $(dataPersonaContacto).each(function (index) {

        var contacto = dataPersonaContacto[index].persona_id;
        var contactoTipo = dataPersonaContacto[index].contacto_tipo_id;

        //texto
        var contactoTipoText = dataPersonaContacto[index].contacto_tipo_descripcion;
        var contactoText = dataPersonaContacto[index].persona_nombre_codigo;
        var contactoTelefonoText = dataPersonaContacto[index].celular !== null ? dataPersonaContacto[index].celular : "Sin celular.";
        contactoTelefonoText += dataPersonaContacto[index].telefono !== null ? dataPersonaContacto[index].telefono : " / Sin teléfono.";
        var contactoEmailText = dataPersonaContacto[index].email !== null ? dataPersonaContacto[index].email : "Sin email.";

        // ids de tablas relacionadas
        var personaContactoId = dataPersonaContacto[index].id;

        arrayContactoTipo.push(contactoTipo);
        arrayContactoTipoText.push(contactoTipoText);
        arrayContacto.push(contacto);
        arrayContactoText.push(contactoText);
        arrayContactoTelefonoText.push(contactoTelefonoText);
        arrayContactoEmailText.push(contactoEmailText);

        // array ids
        arrayPersonaContactoId.push(personaContactoId);

        listaContactoDetalle.push([contactoTipo, contactoTipoText, contacto, contactoText, personaContactoId, contactoTelefonoText, contactoEmailText]);
        onListarContactoDetalle(listaContactoDetalle);

    });
    // fin
}

function cargarListarPersonaCancelar()
{
    loaderShow(null);
    cargarDiv('#window', 'vistas/com/persona/persona_listar.php', 'Listar persona');
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

         var htmlTablaSimilitudes = "<div style='max-height: 200px; overflow-y: auto;'>";

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
        htmlTablaSimilitudes += "</div>";
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

function guardarPersona() {
    var codigoIdentificacion = trim(document.getElementById('txtCodigoIdentificacion').value);
   
    
    if (commonVars.personaTipoId == 2)
    {
        var nombre = trim(document.getElementById('txtNombre').value);
    } else
    {
        var nombre = trim(document.getElementById('txtRazonSocial').value);
        
    }

    var apellido_paterno = trim(document.getElementById('txtApellidoPaterno').value);
    var apellido_materno = trim(document.getElementById('txtApellidoMaterno').value);
    var telefono = trim(document.getElementById('txtTelefono').value);
    var celular = trim(document.getElementById('txtCelular').value);
    var email = trim(document.getElementById('txtEmail').value);

    var numero_cuenta_bcp = trim(document.getElementById('txtCuentaBCP').value);
    var cci = trim(document.getElementById('txtCCI').value);

    var estado = document.getElementById('cboEstado').value;
    var clase = $('#cboClasePersona').val();
    var empresa = $('#cboEmpresa').val();

    var file = $('#secretImg').val();

    var codigoSunatId = select2.obtenerValor('cboCodigoSunat');
    var codigoSunatId2 = select2.obtenerValor('cboCodigoSunat2');
    var codigoSunatId3 = select2.obtenerValor('cboCodigoSunat3');
    var plan_contable_id = select2.obtenerValor('cboCuentaContable');

    var nombreBCP = trim(document.getElementById('txtNombreBCP').value);
    var arrayCentroCostoPersona = [];

    //LICENCIA DE CONDUCIR
    var licenciaAuto = $('#txtLicenciaAuto').val();
    var licenciaMoto = $('#txtLicenciaMoto').val();

    if (validarPersona(codigoIdentificacion, nombre, apellido_paterno, apellido_materno, telefono, celular, email, empresa, clase/*, direccion*/)) {
        // if (clase.find(idClase => idClase == -2) !== undefined) {
        //     // arrayCentroCostoPersona = obtenerCentroCostoPersona();
        //     if (isEmpty(arrayCentroCostoPersona)) {
        //         return;
        //     }
        // }

        if (commonVars.personaId > 0) {
            actualizarPersona(commonVars.personaId, commonVars.personaTipoId, codigoIdentificacion, nombre,
                    apellido_paterno, apellido_materno, telefono, celular, email, file, estado, empresa, clase,
                    codigoSunatId, codigoSunatId2, codigoSunatId3, nombreBCP, numero_cuenta_bcp, cci, arrayCentroCostoPersona,plan_contable_id, licenciaAuto,licenciaMoto,);
        } else {
            insertarPersona(commonVars.personaTipoId, codigoIdentificacion, nombre, apellido_paterno,
                    apellido_materno, telefono, celular, email, file, estado, empresa, clase,
                    codigoSunatId, codigoSunatId2, codigoSunatId3, nombreBCP, numero_cuenta_bcp, cci, arrayCentroCostoPersona,plan_contable_id, licenciaAuto,licenciaMoto,);
        }

    } else {
        loaderClose();
    }
}

function insertarPersona(personaTipoId, codigoIdentificacion, nombre, apellido_paterno, apellido_materno,
        telefono, celular, email, file, estado, empresa, clase, codigoSunatId, codigoSunatId2, codigoSunatId3, nombreBCP,
        numero_cuenta_bcp, cci, arrayCentroCostoPersona,plan_contable_id,licenciaAuto,licenciaMoto)
{
    loaderShow(null);
    deshabilitarBoton();
    ax.setAccion("insertPersona");
    ax.addParamTmp("PersonaTipoId", personaTipoId);
    ax.addParamTmp("codigoIdentificacion", codigoIdentificacion);
    ax.addParamTmp("nombre", nombre);
    ax.addParamTmp("apellido_paterno", apellido_paterno);
    ax.addParamTmp("apellido_materno", apellido_materno);
    ax.addParamTmp("telefono", telefono);
    ax.addParamTmp("celular", celular);
    ax.addParamTmp("email", email);
    ax.addParamTmp("file", file);
    ax.addParamTmp("estado", estado);
    ax.addParamTmp("empresa", empresa);
    ax.addParamTmp("clase", clase);
    ax.addParamTmp("listaContactoDetalle", listaContactoDetalle);
    ax.addParamTmp("listaDireccionDetalle", listaDireccionDetalle);
    ax.addParamTmp("codigoSunatId", codigoSunatId);
    ax.addParamTmp("codigoSunatId2", codigoSunatId2);
    ax.addParamTmp("codigoSunatId3", codigoSunatId3);
    ax.addParamTmp("nombreBCP", nombreBCP);
    ax.addParamTmp("numero_cuenta_bcp", numero_cuenta_bcp);
    ax.addParamTmp("cci", cci);
    ax.addParamTmp("plan_contable_id", plan_contable_id);
    ax.addParamTmp("listaCentroCostoPersona", arrayCentroCostoPersona);
    ax.addParamTmp("licenciaAuto", licenciaAuto);
    ax.addParamTmp("licenciaMoto", licenciaMoto);
    ax.consumir();
}

function actualizarPersona(personaId, personaTipoId, codigoIdentificacion, nombre, apellido_paterno,
        apellido_materno, telefono, celular, email, file, estado, empresa, clase, codigoSunatId, codigoSunatId2, codigoSunatId3, nombreBCP,
        numero_cuenta_bcp, cci, arrayCentroCostoPersona,plan_contable_id,licenciaAuto,licenciaMoto)
{
    loaderShow(null);
    deshabilitarBoton();
    ax.setAccion("updatePersona");
    ax.addParamTmp("id", personaId)
    ax.addParamTmp("PersonaTipoId", personaTipoId);
    ax.addParamTmp("codigoIdentificacion", codigoIdentificacion);
    ax.addParamTmp("nombre", nombre);
    ax.addParamTmp("apellido_paterno", apellido_paterno);
    ax.addParamTmp("apellido_materno", apellido_materno);
    ax.addParamTmp("telefono", telefono);
    ax.addParamTmp("celular", celular);
    ax.addParamTmp("email", email);
    ax.addParamTmp("file", file);
    ax.addParamTmp("estado", estado);
    ax.addParamTmp("empresa", empresa);
    ax.addParamTmp("clase", clase);
    ax.addParamTmp("listaContactoDetalle", listaContactoDetalle);
    ax.addParamTmp("listaPersonaContactoEliminado", listaPersonaContactoEliminado);
    ax.addParamTmp("listaDireccionDetalle", listaDireccionDetalle);
    ax.addParamTmp("listaPersonaDireccionEliminado", listaPersonaDireccionEliminado);
    ax.addParamTmp("codigoSunatId", codigoSunatId);
    ax.addParamTmp("codigoSunatId2", codigoSunatId2);
    ax.addParamTmp("codigoSunatId3", codigoSunatId3);
    ax.addParamTmp("nombreBCP", nombreBCP);
    ax.addParamTmp("numero_cuenta_bcp", numero_cuenta_bcp);
    ax.addParamTmp("cci", cci);
    ax.addParamTmp("plan_contable_id", plan_contable_id);
    ax.addParamTmp("listaCentroCostoPersona", arrayCentroCostoPersona);
    ax.addParamTmp("licenciaAuto", licenciaAuto);
    ax.addParamTmp("licenciaMoto", licenciaMoto);
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
function validarPersona(codigoIdentificacion, nombre, apellido_paterno, apellido_materno, telefono, celular, email, empresa, clase/*, direccion*/) {

    //expresiones de validacion 
    var expresion_email = /^[a-zA-Z0-9\._-]+@[a-zA-Z0-9-]{2,}[.][a-zA-Z]{2,4}$/;


    //var requiereDNI = DNIObligatorio()==-3?false:true; //Si contacto esta seleccionado
    var bandera = true;

    if (commonVars.personaTipoId == 2)
    {
        if ((isEmpty(codigoIdentificacion) || (isNaN(codigoIdentificacion) || codigoIdentificacion.length !== 8)) && DNIObligatorio(-3))
        {
            $("#msjCodigoIdentificacion").removeProp(".hidden");
            $("#msjCodigoIdentificacion").text("Ingresar un DNI/RUC").show();
            bandera = false;
        }
    } else
    {
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

        if (proveedorInter) {
            if (isEmpty(codigoIdentificacion)) {
                $("#msjCodigoIdentificacion").removeProp(".hidden");
                $("#msjCodigoIdentificacion").text("Ingresar un código de identificación").show();
                bandera = false;
            }
        }
//        else if (isEmpty(codigoIdentificacion) || (isNaN(codigoIdentificacion) || codigoIdentificacion.length !== 11))
//        {
//            $("#msjCodigoIdentificacion").removeProp(".hidden");
//            $("#msjCodigoIdentificacion").text("Ingresar un DNI/RUC").show();
//            bandera = false;
//        }
    }


    if (isEmpty(nombre) || nombre.length > 250)
    {
//        console.log(nombre);
        $("#msjNombre").removeProp(".hidden");
        $("#msjNombre").text("Ingresar un nombre").show();
        $("#msjRazonSocial").removeProp(".hidden");
        $("#msjRazonSocial").text("Ingresar razón social").show();
        bandera = false;
    }

    if (commonVars.personaTipoId == 2)
    {
        if (isEmpty(apellido_paterno) || apellido_paterno.length > 45)
        {
            $("#msjApellidoPaterno").removeProp(".hidden");
            $("#msjApellidoPaterno").text("Ingresar un apellido paterno").show();
            bandera = false;
        }

//        if (isEmpty(apellido_materno) || apellido_materno.length > 45)
//        {
//            $("#msjApellidoMaterno").removeProp(".hidden");
//            $("#msjApellidoMaterno").text("Ingresar un apellido materno").show();
//            bandera = false;
//        }
    }


//    if (commonVars.personaTipoId != 2 && isEmpty(direccion))
//    {
//        $("#msjDireccion").removeProp(".hidden");
//        $("#msjDireccion").text("Ingrese dirección").show();
//        bandera = false;
//    }

    if (isEmpty(empresa))
    {
        $("#msjEmpresa").removeProp(".hidden");
        $("#msjEmpresa").text("Seleccionar una empresa").show();
        bandera = false;
    }

    if (isEmpty(clase))
    {
        $("#msjClasePersona").removeProp(".hidden");
        $("#msjClasePersona").text("Seleccionar una clase de persona").show();
        bandera = false;
    }

    //arrayDireccionTipoText
//    if (commonVars.personaTipoId != 2) {
//        var indiceFiscal = buscarDireccionTipoTexto(direccionTipoFiscal);
//        if (indiceFiscal == -1) {
//            $.Notification.autoHideNotify('warning', 'top right', 'Validación', "Ingrese dirección fiscal.");
//            bandera = false;
//        }
//    }

    return bandera;
}
function mostrarMensajeError(nombre)
{
    $('#msj' + nombre).hide();
}
function llenarFormularioEditar(data)
{

    //console.log(data);
    $("#txtCodigoIdentificacion").val(data[0].codigo_identificacion);

    $("#txtNombre").val(data[0].nombre);
    $("#txtApellidoPaterno").val(data[0].apellido_paterno);
    $("#txtApellidoMaterno").val(data[0].apellido_materno);

    $("#txtTelefono").val(data[0].telefono);
    $("#txtCelular").val(data[0].celular);
    $("#txtEmail").val(data[0].email);
//    $("#txtDireccion").val(data[0].direccion_1);
//    $("#txtReferenciaDireccion").val(data[0].direccion_2);
//    $("#txtDireccion3").val(data[0].direccion_3);
//    $("#txtDireccion4").val(data[0].direccion_4);
    $("#txtRazonSocial").val(data[0].nombre);

    $("#txtCuentaBCP").val(data[0].numero_cuenta);
    $("#txtCCI").val(data[0].cci);

    if (!isEmpty(data[0].persona_clase_id))
    {
        select2.asignarValor('cboClasePersona', data[0].persona_clase_id.split(";"));
        // if (data[0].persona_clase_id.split(";").find(clasePersona => clasePersona == -2) !== undefined) {
        //     cargarCentroCostoPersona(dataCentroCostoPersona);
        // }
    }

    if (!isEmpty(data[0].empresa_id))
    {
        select2.asignarValor('cboEmpresa', data[0].empresa_id.split(";"));
    }
    if (!isEmpty(data[0].estado))
    {
        select2.asignarValor('cboEstado', data[0].estado);
    }
    if (data[0].imagen == null || data[0].imagen == "" || data[0].imagen == "null")
    {
        data[0].imagen = "none.jpg";
    }

    //tablas sunat    
    select2.asignarValor('cboCodigoSunat', data[0]['sunat_tabla_detalle_id']);
    select2.asignarValor('cboCodigoSunat2', data[0]['sunat_tabla_detalle_id2']);
    select2.asignarValor('cboCodigoSunat3', data[0]['sunat_tabla_detalle_id3']);
    select2.asignarValor('cboCuentaContable', data[0]['plan_contable_id']);
    

    $("#txtNombreBCP").val(data[0].nombre_bcp);

    $("#cboCodigoSunat2").select2({width: anchoComboSunat2 + "px"});

    var dir = URL_BASE + "/vistas/com/persona/imagen/" + data[0].imagen;
    document.getElementById("myImg").src = dir;

    modificarFormularioProveedorInternacional();
    mostrarLicenciaConducir();
    // if(commonVars.personaId === 0){
    //     ocultarDocumentosClaseReinfo();
    //     ocultarDocumentoClasePlanta();

    // }else   {
    //     mostrarDocumentosClaseReinfo();
    //     mostrarDocumentoClasePlanta();
    // }
    controlarVisibilidadDocumentos();
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


var clasetransportista="23";

function mostrarLicenciaConducir() {
     
    var dataClase = $('#cboClasePersona').val();

    if (!isEmpty(dataClase)) {
        var index = dataClase.indexOf(clasetransportista);
        if (index != -1) {
            $("#contenedorLicenciaConducir").show();
        } else {
            $("#contenedorLicenciaConducir").hide();
        }
    } else {
        $("#contenedorLicenciaConducir").hide();
    }
}


var claseReinfo="26";
var clasePlanta="25";



function controlarVisibilidadDocumentos() {
     
    var dataClase = $('#cboClasePersona').val();

    // Verificar si commonVars.personaId es 0
    if (commonVars.personaId == 0) {
        $("#liPersonaDocumentos").hide();
        $("#litPersonaPlantaDocumento").hide();
        return; 
    }
    if (commonVars.personaId > 0){
        // Mostrar u ocultar documentos según la selección en el combo
        if (dataClase && dataClase.includes(claseReinfo)) {
            $("#liPersonaDocumentos").show();
        } else {
            $("#liPersonaDocumentos").hide();
        }

        if (dataClase && dataClase.includes(clasePlanta)) {
            $("#litPersonaPlantaDocumento").show();
        } else {
            $("#litPersonaPlantaDocumento").hide();
        }
    }

    
}



// ////////// SUBIR DOCUMENTOS ///////////////////////
function insertDocumentoDetalle() {
    ;
    var personaId = commonVars.personaId;
    var personaTipoArchivo = select2.obtenerValor('cboTipoArchivo');   // arroja el id del tipo de archivo
    var inputFile = $('#secretImg2').val();

    var fullPath = $('#file2').val(); // obtiene el nombre de la img con la ruta
    
 

    // Obtener solo el nombre del archivo
    var startIndex = (fullPath.indexOf('\\') >= 0 ? fullPath.lastIndexOf('\\') : fullPath.lastIndexOf('/'));
    var fileName = fullPath.substring(startIndex);

    // Si se encontró una barra invertida (\) o barra (/), eliminarla
    if (fileName.indexOf('\\') === 0 || fileName.indexOf('/') === 0) {
        fileName = fileName.substring(1);
    }

    loaderShow();
    ax.setAccion('insertArchivo');
    ax.addParamTmp("personaId", personaId);
    ax.addParamTmp("fileName", fileName);
    ax.addParamTmp("personaTipoArchivo", personaTipoArchivo);
    ax.addParamTmp("inputFile", inputFile);
    ax.consumir();

    limpiarCampos();
}

// eliminar archivo 
function deleteArchivo(id, archivo) {
     
    loaderShow();
    ax.setAccion("eliminarArchivos");
    ax.addParamTmp("id", id);
    ax.addParamTmp("archivo", archivo);
    ax.consumir();
}
function limpiarCampos() {
    $('#file2').val('');
    $('#cboTipoArchivo').val('');
    $('#myImg2').attr('src', 'vistas/com/persona/imagen/none.jpg');
    $('#secretImg2').val('');
}

function renderArchivo(data) {
     
    var archivoTableBody = $('#archivoTable tbody');
    archivoTableBody.empty();
    
    if (data) {
        data.forEach(function(archi, index) { 
             
            var estadoDocumento= archi.estado;
            var claseFila = ''; // Declarar claseFila dentro del bucle
            if(estadoDocumento == '1'){
                estadoDocumento = 'Activo';
                claseFila = ''; 
            }if(estadoDocumento == '2'){
                estadoDocumento = 'Inactivo';
                claseFila = 'inactivo'; 
            }
            var rutaArchivo='/sgiLaVictoria/vistas/com/persona/documentos/'+archi.archivo;
            var row = '<tr class="'+claseFila+'">' +
                '<td>' + archi.nombre + '</td>' +
                '<td><a href=' + rutaArchivo + ' target="_blank">' + archi.fileName + '</a></td>' +
                
                '<td>' + archi.fecha_creacion + '</td>' +
                '<td style="text-align:center;">' + estadoDocumento + '</td>' +
                // '<td style="text-align:center;">' + (archi.archivo ? "<a href='" + rutaArchivo + "' target='_blank'><i style='color:green;' class='fa fa-eye'></i> </a>" : "No disponible") + '</td>' +
                '<td style="text-align:center;"> <a class="" onclick="deleteArchivo(' + archi.id + ', \'' + archi.archivo + '\')"><i style="color:#cb2a2a;" class="fa fa-trash-o"></i></a></td>' +
                '</tr>';
            archivoTableBody.append(row);
        });
    }
}

// PLANTA PERSONA
// PLANTA 
//  
function renderArchivoPlanta(data) {
     
    var archivoTableBody = $('#archivoPlantaTable tbody');
    archivoTableBody.empty();
    
    if (data) {
        data.forEach(function(archi, index) { 
             
            var estadoDocumento= archi.estado;
            var claseFila = ''; // Declarar claseFila dentro del bucle
            if(estadoDocumento == '1'){
                estadoDocumento = 'Activo';
                claseFila = ''; 
            }if(estadoDocumento == '2'){
                estadoDocumento = 'Inactivo';
                claseFila = 'inactivo2'; 
            }
            var rutaArchivo='/sgiLaVictoria/vistas/com/persona/documentosPlanta/'+archi.formato;
            var row = '<tr class="'+claseFila+'">' +
                '<td>' + archi.nombre + '</td>' +
                // '<td>' + archi.formato + '</td>' +
                // '<td>' + archi.formato + '</td>' +
                '<td>' + archi.estado + '</td>' +
                '<td><a href=' + rutaArchivo + ' target="_blank">' + archi.nombredoc + '</a></td>' +
                '<td>' + archi.fecha_creacion + '</td>' +
                '<td style="text-align:center;">' + estadoDocumento + '</td>' +
                '<td style="text-align:center;"> <a class="" onclick="deleteDocumentoPersonaPlanta(' + archi.id + ', \'' + archi.archivo + '\')"><i style="color:#cb2a2a;" class="fa fa-trash-o"></i></a></td>' +
                '</tr>';
            archivoTableBody.append(row);
        });
    }
}


function MostrarGuardarDocumento() {
    $('#contenedorGuardarNuevoDocumento').toggle();
}

function validarYGuardar() {
    ;
    var nuevoDocumentoPLanta = $('#txtNombreDocumento').val();
  
    if (isEmpty(nuevoDocumentoPLanta) || nuevoDocumentoPLanta.trim() === '') {
        // Oculta el mensaje, luego muestra con el nuevo texto
        $('#msjPlanta').hide();
        $('#msjPlanta').removeAttr('hidden');
        $('#msjPlanta').text('Ingrese un documento!').show();
    } else {
        // Oculta el mensaje si el campo tiene contenido válido
        $('#msjPlanta').hide();
        // Llama a la función para guardar el documento
        nuevoTipoDocumento();
    }
        
    

   
}

function nuevoTipoDocumento(){
    ;
    
    var nombreDocumento = $('#txtNombreDocumento').val();
    loaderShow();
    ax.setAccion('insertTipoDocumentoPlanta');
    ax.addParamTmp("nombreDocumento", nombreDocumento);
    ax.consumir();

    $('#txtNombreDocumento').val('');
}

function mostrarFormato() {
    var uploadField = $('#uploadField');
    if ($('#checkSubida').is(':checked')) {
        uploadField.show();
    } else {
        uploadField.hide();
    }
}

function insertDocumentoPersonaPlanta(){
    ;
    var tipoDocumentoPlanta = $('#cboTipoArchivo2').val();
    var personaId = commonVars.personaId;

    var inputFile = $('#secretImg3').val();
    var fullPath = $('#file3').val();
    
    

    // Obtener solo el nombre del archivo
    var startIndex = (fullPath.indexOf('\\') >= 0 ? fullPath.lastIndexOf('\\') : fullPath.lastIndexOf('/'));
    var fileName = fullPath.substring(startIndex);

    // Si se encontró una barra invertida (\) o barra (/), eliminarla
    if (fileName.indexOf('\\') === 0 || fileName.indexOf('/') === 0) {
        fileName = fileName.substring(1);
    }




    ax.setAccion('insertTipoDocumentoPLantaXPersona');
    ax.addParamTmp("tipoDocumentoPlanta", tipoDocumentoPlanta);
    ax.addParamTmp("personaId", personaId);
    ax.addParamTmp("inputFile", inputFile);
    ax.addParamTmp("fileName", fileName);
    ax.consumir();

}
// eliminar archivo 
function deleteDocumentoPersonaPlanta(id, archivo) {
     
    loaderShow();
    ax.setAccion("eliminarTipoDocumentoPLantaXPersona");
    ax.addParamTmp("id", id);
    ax.consumir();
}