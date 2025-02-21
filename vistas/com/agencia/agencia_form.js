$(document).ready(function () {
    ax.setSuccess("successAgenciaForm");
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

$('#txt_codigo').keypress(function () {
    $('#msj_codigo').hide();
});
$('#txt_descripcion').keypress(function () {
    $('#msj_descripcion').hide();
});

$('#txt_direccion').keypress(function () {
    $('#msj_direccion').hide();
});

$('#cbo_division').on('change', function () {
    $('#msj_division').hide();
});

$('#cbo_ubigeo').on('change', function () {
    $('#msj_ubigeo').hide();
});

$('#cbo_ubicacion_geografica').on('change', function () {
    $('#msj_ubicacion_geografica').hide();
});

$('#cbo_modelo_local').on('change', function () {
    $('#msj_modelo_local').hide();
});

function successAgenciaForm(response) {
    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obterConfiguracionInicialForm':
                onResponseObtenerConfiguracionInicial(response.data);
                loaderClose();
                break;
            case 'guardarAgencia':
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
    select2.cargar("cbo_division", data.dataDivision, "id", "descripcion");
    select2.cargar("cbo_modelo_local", data.dataModeloLocal, "id", "descripcion");
    select2.cargar("cbo_ubicacion_geografica", data.dataUbicacionGeografica, "id", "descripcion");
    select2.cargar("cbo_ubigeo", data.dataUbigeo, "id", ["ubigeo_dep", "ubigeo_prov", "ubigeo_dist"]);
    select2.iniciar();

    limpiarImput();

    if (!isEmpty(data.dataAgencia)) {
        let agencia = data.dataAgencia[0];

        $("#txt_codigo").val(agencia.codigo);
        $("#txt_descripcion").val(agencia.descripcion);         
        $("#txt_direccion").val(agencia.direccion);
        
        select2.asignarValor("cbo_estado", agencia.estado);
        select2.asignarValor("cbo_division", agencia.division_id);
        select2.asignarValor("cbo_modelo_local", agencia.modelo_local_id);
        select2.asignarValor("cbo_ubicacion_geografica", agencia.ubicacion_geografica_id);
        select2.asignarValor("cbo_ubigeo", agencia.ubigeo_id);
    }
}
function limpiarImput() {
    select2.asignarValor("cbo_division", "");
    select2.asignarValor("cbo_modelo_local", "");
    select2.asignarValor("cbo_ubicacion_geografica", "");
    select2.asignarValor("cbo_ubigeo", "");
}
function guardarAgencia() {
    let agencia = {};
    agencia.id = $("#id").val();
    agencia.empresa_id = commonVars.empresa;
    agencia.codigo = $("#txt_codigo").val();
    agencia.descripcion = $("#txt_descripcion").val();
    agencia.direccion = $("#txt_direccion").val();
    agencia.estado = select2.obtenerValor("cbo_estado");
    agencia.division_id = select2.obtenerValor("cbo_division");
    agencia.modelo_local_id = select2.obtenerValor("cbo_modelo_local");
    agencia.ubicacion_geografica_id = select2.obtenerValor("cbo_ubicacion_geografica");
    agencia.ubigeo_id = select2.obtenerValor("cbo_ubigeo");

    if (validarAgenciaForm(agencia)) {
        deshabilitarBoton();
        ax.setAccion("guardarAgencia");
        ax.addParamTmp("agencia", agencia);
        ax.consumir();
    }
}
function validarAgenciaForm(agencia) {
    let bandera = true;
    let espacio = /^\s+$/;

    if (isEmpty(agencia.descripcion) || espacio.test(agencia.descripcion) || agencia.descripcion.length == 0)
    {
        $("#msj_descripcion").removeProp(".hidden");
        $("#msj_descripcion").text("Ingresar una descripción").show();
        bandera = false;
    }
    if (isEmpty(agencia.codigo) || espacio.test(agencia.codigo) || agencia.codigo.length == 0)
    {
        $("#msj_codigo").removeProp(".hidden");
        $("#msj_codigo").text("Ingresar el centro costo valido").show();
        bandera = false;
    }

    if (isEmpty(agencia.direccion) || espacio.test(agencia.direccion) || agencia.direccion.length == 0)
    {
        $("#msj_direccion").removeProp(".hidden");
        $("#msj_direccion").text("Ingresar una dirección").show();
        bandera = false;
    }


    if (isEmpty(agencia.division_id))
    {
        $("#msj_division").removeProp(".hidden");
        $("#msj_division").text("Seleccione una división").show();
        bandera = false;
    }

//    if (isEmpty(agencia.modelo_local_id))
//    {
//        $("#msj_modelo_local").removeProp(".hidden");
//        $("#msj_modelo_local").text("Seleccione un modelo del local").show();
//        bandera = false;
//    }

    if (isEmpty(agencia.ubicacion_geografica_id))
    {
        $("#msj_ubicacion_geografica").removeProp(".hidden");
        $("#msj_ubicacion_geografica").text("Seleccione una ubicación geográfica").show();
        bandera = false;
    }

//    if (isEmpty(agencia.ubigeo_id))
//    {
//        $("#msj_ubigeo").removeProp(".hidden");
//        $("#msj_ubigeo").text("Seleccione una ubigeo.").show();
//        bandera = false;
//    }

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
        cargarDivIndex('#window', 'vistas/com/agencia/agencia_listar.php', 324, '')
    }
}