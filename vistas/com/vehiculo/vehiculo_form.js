$(document).ready(function () {
    ax.setSuccess("successVehiculoForm");
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
$('#txt_placa').keypress(function () {
    $('#msj_placa').hide();
});


$('#txt_marca').keypress(function () {
    $('#msj_marca').hide();
});
$('#txt_modelo').keypress(function () {
    $('#msj_modelo').hide();
});


$('#txt_capacidad').keypress(function () {
    $('#msj_capacidad').hide();
});
$('#cbo_tipo').keypress(function () {
    $('#msj_tipo').hide();
});

function successVehiculoForm(response) {
    debugger;
    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obterConfiguracionInicialForm':
                onResponseObtenerConfiguracionInicial(response.data);
                loaderClose();
                break;
            case 'validarPlacaEndPoint':
                debugger;
                onresponseObtenerConsultaPlaca(response.data);
                loaderClose();
                break;
            case 'guardarVehiculo':
                exitoInsert(response.data);
                break;
            
            // case 'actualizarVehiculo':
            //     exitoInsert(response.data);
            //     break;
        }
    }
}

function buscarConsultaPlaca(){
    placa = $("#txt_placa").val();
    // Expresión regular para el formato "LLLNNN" (3 letras seguidas de 3 números)
    var regex = /^[A-Z0-9]{6,7}$/i;
    
    // Verificar si la placa coincide con el formato esperado
    if (!regex.test(placa)) {
        $.Notification.autoHideNotify('warning', 'top right', 'Validación', "Ingrese una placa válida, formato: LLLNNN.");
        return false;
    }
    loaderShow();
    ax.setAccion("validarPlacaEndPoint");
    ax.addParamTmp("placa", placa);
    ax.consumir();
}

function onresponseObtenerConsultaPlaca(data) {
    debugger;
    // dataImage= data[0]['captura'];
    //console.log(data);    
    $('#txt_capacidad').val('');
    $('#txt_nro_constancia').val('');
    $('#img_captura').hide();
    $('#img_contenedor').hide();
    $('#download_img').hide();
    $('#img_64').hide();

    if (!isEmpty(data)) {
        $('#txt_capacidad').val(data[0]['carga_util']).prop('disabled', true);
        $('#txt_nro_constancia').val(data[0]['nro_constancia']).prop('disabled', true);
        $('#img_contenedor').show();
        $('#constancia_contenedor').show();
        $('#img_captura').attr('src', 'data:image/png;base64,' + data[0]['captura']).show();
        $('#download_img').attr('href', 'data:image/png;base64,' + data[0]['captura']).show();
        $('#img_link').attr('href', 'data:image/png;base64,' + data[0]['captura']);
        $('#img_64').val( data[0]['captura']);
    }

}
// var dataImage = dataImage;

function obtenerConfiguracionInicial() {
    ax.setAccion("obterConfiguracionInicialForm");
    ax.addParamTmp("id", $("#id").val());
    ax.consumir();
}
function onResponseObtenerConfiguracionInicial(data) {
    debugger;
    select2.iniciar();

  

    if (!isEmpty(data.dataVehiculo)) {
        let vehiculo = data.dataVehiculo[0];

        $("#txt_placa").val(vehiculo.placa);
        $("#txt_marca").val(vehiculo.marca);         
        $("#txt_modelo").val(vehiculo.modelo);         
        $("#txt_capacidad").val(vehiculo.capacidad);         
        select2.asignarValor("cbo_tipo", vehiculo.tipo);
        $("#txt_nro_constancia").val(vehiculo.nro_constancia);         
        // Agregar y mostrar la imagen guardada
        if (vehiculo.captura) {
            let rutaBase = 'vistas/com/vehiculo/capturas/';
            let rutaCompletaImagen = rutaBase + vehiculo.captura;
            $('#img_captura').attr('src', rutaCompletaImagen).show();
            $('#download_img').attr('href', rutaCompletaImagen).show();
            $('#img_link').attr('href', rutaCompletaImagen);   
            $('#img_64').val( vehiculo.captura).hide();   
        }  
       
    }
}
// guardar sin validacion !
// function guardarVehiculo() {
//     debugger;
//     let espacio = /^\s+$/;
//     id = $("#id").val();
//     // si es nuevo vehiculo que lo valide los campos que ingrese pero para al actualizar no
//     if(id == '0'){
//         placa = $("#txt_placa").val();
//         if (isEmpty(placa) || espacio.test(placa) || placa.length == 0)
//         {   
//             $("#msj_placa").hide();
//             $("#msj_placa").removeProp(".hidden");
//             $("#msj_placa").text("Ingrese una placa ").show();
//         }
//         capacidad = $("#txt_capacidad").val();
//         if (isEmpty(capacidad) || espacio.test(capacidad) || placa.length == 0)
//             {
//                 $("#msj_capacidad").hide();
//                 $("#msj_capacidad").removeProp(".hidden");
//                 $("#msj_capacidad").text("Ingresar una capacidad").show();
//         }
//         nro_constancia = $("#txt_nro_constancia").val();
//         if (isEmpty(nro_constancia) || espacio.test(nro_constancia) || nro_constancia.length == 0)
//             {
//                 $("#msj_nro_constancia").hide();
//                 $("#msj_nro_constancia").removeProp(".hidden");
//                 $("#msj_nro_constancia").text("Ingresar su numero de constancia").show();
//         }
//         var inputFile = $("#img_64").val();
//         $('#cbo_tipo').select2(); 
//         var tipo = $('#cbo_tipo').val();

//         marca = $("#txt_marca").val();
//         if (isEmpty(marca) || espacio.test(marca) || marca.length == 0)
//             {
//                 $("#msj_marca").hide();
                
//                 $("#msj_marca").removeProp(".hidden");
//                 $("#msj_marca").text("Ingrese la marca del vehiculo").show();
//         }
//         modelo = $("#txt_modelo").val();
//         if (isEmpty(modelo) || espacio.test(modelo) || modelo.length == 0)
//             {
//                 $("#msj_modelo").hide();
                
//                 $("#msj_modelo").removeProp(".hidden");
//                 $("#msj_modelo").text("Ingrese su modelo del vehiculo").show();
//         }
//     }
//     else{
//         placa = $("#txt_placa").val();
//         capacidad = $("#txt_capacidad").val();
//         nro_constancia = $("#txt_nro_constancia").val();
//         var inputFile = $("#img_64").val();
//         $('#cbo_tipo').select2(); 
//         var tipo = $('#cbo_tipo').val();
//         marca = $("#txt_marca").val();
//         modelo = $("#txt_modelo").val();
//     }
    
//     deshabilitarBoton();
//     ax.setAccion("guardarVehiculo"); 
//     ax.addParamTmp("id", id);   
//     ax.addParamTmp("placa", placa);
//     ax.addParamTmp("marca", marca);
//     ax.addParamTmp("modelo", modelo);
//     ax.addParamTmp("capacidad", capacidad);
//     ax.addParamTmp("tipo", tipo);
//     ax.addParamTmp("nro_constancia", nro_constancia);
//     ax.addParamTmp("inputFile", inputFile);
//     ax.consumir();
// }






function guardarVehiculo() {
    debugger;
    const espacio = /^\s+$/;
    const id = $("#id").val();

    function validarCampo(campo, mensaje, idMensaje) {
        if (isEmpty(campo) || espacio.test(campo) || campo.length === 0) {
            $(idMensaje).text(mensaje).removeAttr('hidden');
            return false;
        } else {
            $(idMensaje).attr('hidden', true);
            return true;
        }
    }

    let validacionCorrecta = true;

    if (id === '0') {
        const placa = $("#txt_placa").val();
        const capacidad = $("#txt_capacidad").val();
        const nro_constancia = $("#txt_nro_constancia").val();
        const marca = $("#txt_marca").val();
        const modelo = $("#txt_modelo").val();

        validacionCorrecta &= validarCampo(placa, "Ingrese una placa", "#msj_placa");
        validacionCorrecta &= validarCampo(capacidad, "Ingresar una capacidad", "#msj_capacidad");
        validacionCorrecta &= validarCampo(nro_constancia, "Ingresar su número de constancia", "#msj_nro_constancia");
        validacionCorrecta &= validarCampo(marca, "Ingrese la marca del vehículo", "#msj_marca");
        validacionCorrecta &= validarCampo(modelo, "Ingrese el modelo del vehículo", "#msj_modelo");

        // si una de estas falla no seguir
        if (!validacionCorrecta) {
            return;
        }
    }

    const placa = $("#txt_placa").val();
    const capacidad = $("#txt_capacidad").val();
    const nro_constancia = $("#txt_nro_constancia").val();
    const inputFile = $("#img_64").val();
    $('#cbo_tipo').select2(); 
    const tipo = $('#cbo_tipo').val();
    const marca = $("#txt_marca").val();
    const modelo = $("#txt_modelo").val();

    deshabilitarBoton();

    ax.setAccion("guardarVehiculo"); 
    ax.addParamTmp("id", id);   
    ax.addParamTmp("placa", placa);
    ax.addParamTmp("marca", marca);
    ax.addParamTmp("modelo", modelo);
    ax.addParamTmp("capacidad", capacidad);
    ax.addParamTmp("tipo", tipo);
    ax.addParamTmp("nro_constancia", nro_constancia);
    ax.addParamTmp("inputFile", inputFile);
    ax.consumir();
}

function isEmpty(data) {
    return (data === null || data === undefined || data.length === 0);
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
        cargarDivIndex('#window', 'vistas/com/vehiculo/vehiculo_listar.php', 355, '')
    }
}

