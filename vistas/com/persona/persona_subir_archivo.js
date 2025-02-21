var c = $('#env i').attr('class');
var anchoComboSunat2;
var dataPersonaGlobal;
$(document).ready(function () {
    
    select2.iniciar();
    anchoComboSunat2 = $("#divCboCodigoSunat2").width();

  

    ax.setSuccess("successPersona");
    llenarTablaArchivos();
    llenarcomboTipoArchivo();

});

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

            case 'obtenerArchivos':
                renderArchivo(response.data);
                break; 
            case 'obtenerTipoDocumento23':
                onResponsellenarcomboTipoArchivo(response.data);
                break;
            case'insertArchivo':
                if(response.data['0'].vout_exito == 1){
                    llenarTablaArchivos();
                    loaderClose();
                }else{
                    mostrarAdvertencia(response.data['0'].vout_mensaje);
                }
                
                break; 

            case 'eliminarArchivos':
                debugger;
                if(response.data['0'].vout_exito == 1){
                    llenarTablaArchivos();
                    loaderClose();
                }else{
                    mostrarAdvertencia(response.data['0'].vout_mensaje);
                }
                break; 
            
        }
    } else
    {
        switch (response[PARAM_ACCION_NAME]) {
            
        }
    }
}




function cargarListarPersonaCancelar()
{
    loaderShow(null);
    cargarDiv('#window', 'vistas/com/persona/persona_listar.php', 'Listar persona');
}

function mostrarMensajeError(nombre)
{
    $('#msj' + nombre).hide();
}

function validarToken(personaId) {
    if (token == 1) {
        window.opener.setearPersonaRegistro(personaId);
        setTimeout("self.close();", 700)
    }
}

// LLENAR COMBO TIPOS DE ARCHIVOS DESDE EL CONTROLADOR
function llenarcomboTipoArchivo() {
    ax.setAccion("obtenerTipoDocumento23");
 
    ax.consumir();
}

// LLENAR EL COMBO EN EL SELECT
function onResponsellenarcomboTipoArchivo(data) {
    return select2.cargar("cboTipoArchivo", data, "id", "nombre");
}

function renderArchivo(data) {
    debugger;
    var archivoTableBody = $('#archivoTable tbody');
    archivoTableBody.empty();
    
    if (data) {
        data.forEach(function(archi, index) {
            var rutaArchivo='/sgiLaVictoria/vistas/com/persona/documentos/'+archi.archivo;
            var row = '<tr>' +
                '<td>' + archi.nombre + '</td>' +
                '<td>' + archi.archivo + '</td>' +
                '<td>' + archi.fecha_creacion + '</td>' +
                '<td>' + (archi.archivo ? "<a href='" + rutaArchivo + "' target='_blank'>Ver archivo</a>" : "No disponible") + '</td>' +
                '<td><button class="btn btn-danger" onclick="deleteArchivo(' + archi.id + ', \'' + archi.archivo + '\')"><i class="fa fa-trash-o"></i>Eliminar</button></td>' +
                '</tr>';
            archivoTableBody.append(row);
        });
    }
}

// INSERTAR DOCUMENTOS A LA PERSONA

function insertDocumentoDetalle() {
    debugger;
    var personaId = commonVars.personaId;
    var personaTipoArchivo = select2.obtenerValor('cboTipoArchivo');   // arroja el id del tipo de archivo
    var inputFile = $('#secretImg').val();
    loaderShow();
    ax.setAccion('insertArchivo');
    ax.addParamTmp("personaId", personaId);
    ax.addParamTmp("personaTipoArchivo", personaTipoArchivo);
    ax.addParamTmp("inputFile", inputFile);
    ax.consumir();

    limpiarCampos();
}

// eliminar archivo 
function deleteArchivo(id, archivo) {
    debugger;
    loaderShow();
    ax.setAccion("eliminarArchivos");
    ax.addParamTmp("id", id);
    ax.addParamTmp("archivo", archivo);
    ax.consumir();
}
function limpiarCampos() {
    $('#file').val('');
    $('#cboTipoArchivo').val('');
    $('#myImg').attr('src', 'vistas/com/persona/imagen/none.jpg');
    $('#secretImg').val('');
}


function asignarValorSelect2(id, valor)
{
    $("#" + id).select2().select2("val", valor);
    $("#" + id).select2({width: '100%'});
}

