$(document).ready(function () {
    ax.setSuccess("successZonaForm");
    obtenerConfiguracionInicial();
});

var c = $('#env i').attr('class');
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
// Campo nombre 
$('#txt_nombre').keypress(function () {
    $('#msj_nombre').hide();
});


$('#txt_descripcion').keypress(function () {
    $('#msj_descripcion').hide();
});


function successZonaForm(response) {
    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obterConfiguracionInicialForm':
                onResponseObtenerConfiguracionInicial(response.data);
                loaderClose();
                break;
            case 'guardarzona':
                exitoInsert(response.data);
                break;
            
                case 'actualizarEstadoZona':
                    exitoInsert(response.data);
                    break;
        }
    }
}
function obtenerConfiguracionInicial() {
    ax.setAccion("obterConfiguracionInicialForm");
    ax.addParamTmp("id", $("#id").val());
    ax.consumir();
}
function onResponseObtenerConfiguracionInicial(data) {
    ;
    select2.iniciar();

    limpiarImput();

    if (!isEmpty(data.dataZona)) {
        let zona = data.dataZona[0];

        $("#txt_nombre").val(zona.nombre);
        $("#txt_descripcion").val(zona.descripcion);         
        select2.asignarValor("cbo_estado", zona.estado);
    }
}
function limpiarImput() {
}
function guardarzona() {
    
    ;
    id = $("#id").val();
    nombre = $("#txt_nombre").val();
    descripcion = $("#txt_descripcion").val();
    estado = select2.obtenerValor("cbo_estado");

    // if (validarzonaForm(zona)) {
        deshabilitarBoton();
        if(id=='0'){
        ax.setAccion("guardarzona"); }
        else{
        ax.setAccion("actualizarEstadoZona"); 
        ax.addParamTmp("id", id);   
        }
        ax.addParamTmp("nombre", nombre);
        ax.addParamTmp("descripcion", descripcion);
        ax.addParamTmp("estado", estado);
        ax.consumir();
    // }
}
function validarzonaForm(zona) {
    let bandera = true;
    let espacio = /^\s+$/;
    if (isEmpty(zona.nombre) || espacio.test(zona.nombre) || zona.nombre.length == 0)
        {
            $("#msj_nombre").removeProp(".hidden");
            $("#msj_nombre").text("Ingresar un nombre").show();
            bandera = false;
    }
    if (isEmpty(zona.descripcion) || espacio.test(zona.descripcion) || zona.descripcion.length == 0)
    {
        $("#msj_descripcion").removeProp(".hidden");
        $("#msj_descripcion").text("Ingresar una descripción").show();
        bandera = false;
    }
  
    if (isEmpty(zona.estado) )
    {
        $("#msj_estado").removeProp(".hidden");
        $("#msj_estado").text("Seleccione el estado").show();
        bandera = false;
    }


    return bandera;
}
function exitoInsert(data) {
    if (data[0]["vout_exito"] == 0)
    {
        habilitarBoton();
        $.Notification.autoHideNotify('warning', 'top right', 'Validación', data[0]["vout_mensaje"]);
    } else
    {
        habilitarBoton();
        $.Notification.autoHideNotify('success', 'top-right', '&Eacute;xito', data[0]["vout_mensaje"]);
        cargarDivIndex('#window', 'vistas/com/zona/zona_listar.php', 350, '')
    }
}