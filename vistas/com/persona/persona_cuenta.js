var c = $('#env i').attr('class');
function iniciarControlador() {
    ax.setSuccess("successPersona");
    listarPersonaClase();

}

function listarPersonaClase() {
    ax.setAccion("getDataGridPersonaCuenta");
    ax.consumir();
}

function successPersona(response) {
    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'getDataGridPersonaCuenta':
                onResponseGetDataGridPersonaCuenta(response.data);
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
                    }
                });
                break;
            case 'getAllBancos':
                onResponsegetAllBancos(response.data);
                if (!isEmpty(VALOR_ID_USUARIO)) {
                    llenarFormPersonaCuenta();
                }
                loaderClose();

                break;
            case 'insertPersonaCuenta':
                onResponseSavePersonaCuenta(response.data);
                break;
            case 'updatePersonaCuenta':
                onResponseSavePersonaCuenta(response.data);
                break;
            case 'deletePersonaClase':
                var error = response.data[0]['vout_exito'];
                if (error == 1) {
                    swal("Eliminado!", "Clase de persona eliminada correctamente", "success");
                } else {
                    swal("Cancelado", "No se pudo eliminar " + response.data[0]['vout_mensaje'], "error");
                }
                bandera_eliminar = true;
                listarPersonaClase();
                break;
            case 'cambiarEstadoPersonaClase':
                onResponseCambiarEstadoPersonaClase(response.data);
                listarPersonaClase();
                break;
        }
    }
}

function onResponseGetDataGridPersonaCuenta(data) {
    $("#datatable2").empty();
    var cuerpo_total = '';
    var cuerpo = '';
    var cabeza = '<table id="datatable" class="table table-striped table-bordered"><thead>' +
        " <tr>" +
        "<th style='text-align:center;'>Descripcion</th>" +
        "<th style='text-align:center;'>Tipo</th>" +
        "<th style='text-align:center;'>Numero</th>" +
        "<th style='text-align:center;'>Cci</th>" +
        "<th style='text-align:center;'>Tipo cuenta</th>" +
        "<th style='text-align:center;'width=100px>Acciones</th>" +
        "</tr>" +
        "</thead>";
    if (!isEmpty(data)) {
        $.each(data, function (index, item) {

            if (isEmpty(item.persona_tipo_descripcion)) {
                item.persona_tipo_descripcion = "";
            }
            cuerpo = '<tr>' +
                '<td style="text-align:center;">' + item.descripcion + '</td>' +
                '<td style="text-align:center;">' + item.tipo + '</td>' +
                '<td style="text-align:center;">' + item.numero + '</td>' +
                '<td style="text-align:center;">' + item.cci + '</td>' +
                '<td style="text-align:center;">' + item.tipo_cuenta_descripcion + '</td>' +
                '<td style="text-align:center;">' +
                '<a onclick="editarPersonaCuenta(' + item.id + ', ' + item.numero + ', \'' + item.cci + '\',\'' + item.principal + '\',' + item.cuenta_id + ',\'' + item.tipo_cuenta + '\')"><b><i class="fa fa-edit" style="color:#E8BA2F;"></i><b></a>&nbsp;\n' +
                // '<a onclick="confirmarDeletePersonaClase(' + item.persona_clase_id + ',\'' + item.persona_clase_descripcion + '\')"><b><i class="fa fa-trash-o" style="color:#cb2a2a;"></i><b></a>' +
                '</td>' +
                '</tr>';
            cuerpo_total = cuerpo_total + cuerpo;
        });
    }

    var pie = '</table>';
    var html = cabeza + cuerpo_total + pie;
    $("#datatable2").append(html);
    loaderClose();
}

function getAllBancos() {
    ax.setAccion("getAllBancos");
    ax.consumir();
}

function cargarSelect2() {
    $(".select2").select2({
        width: '100%'
    });
}

//function onResponsegetAllPersonaTipo(data)
//{
//    $.each(data, function (index, value) {
//        $('#listaPersonaTipo').append('<li><a onclick="cargarForm(\''+value.ruta+'\')">'+value.descripcion+'</a></li>');
//    });
//}

function cargarForm(ruta) {
    cargarDiv('#window', ruta);
}

function nuevaPersonaCuenta() {
    VALOR_ID_USUARIO = null;

    cargarFormPersonaCuenta("Nueva");
    cargarComponentesFormPersonacUENTA();
}

function cargarFormPersonaCuenta(nombre) {
    cargarDiv('#window', 'vistas/com/persona/persona_cuenta_form.php', nombre + " " + obtenerTitulo());
}

function cargarComponentesFormPersonacUENTA() {

    getAllBancos();
}

function onResponsegetAllBancos(data) {

    if (!isEmpty(data)) {
        $('#cboBanco').append('<option value="0" selected>Seleccionar</option>');
        $.each(data, function (index, value) {
            $('#cboBanco').append('<option value="' + value.id + '">' + value.descripcion + '</option>');
        });
    }
}

function guardarPersonaCuenta() {
    var numero = trim(document.getElementById('txtNumero').value);
    var cci = trim(document.getElementById('txtCci').value);
    var bancoId = document.getElementById('cboBanco').value;
    var tipo = document.getElementById('cboTipo').value;
    var tipo_cuenta = document.getElementById('cboTipoCuenta').value;

    savePersonaCuenta(numero, cci, bancoId, tipo, tipo_cuenta);
}

function savePersonaCuenta(numero, cci, bancoId, tipo, tipo_cuenta) {
    if (validarFormPersonaCuenta(numero, cci, bancoId, tipo, tipo_cuenta)) {
        loaderClose();
        deshabilitarBoton();

        if (isEmpty(VALOR_ID_USUARIO)) {
            ax.setAccion("insertPersonaCuenta");
        } else {

            ax.setAccion("updatePersonaCuenta");
            ax.addParamTmp("id", VALOR_ID_USUARIO);
        }
        ax.addParamTmp("numero", numero);
        ax.addParamTmp("cci", cci);
        ax.addParamTmp("bancoId", bancoId);
        ax.addParamTmp("tipo", tipo);
        ax.addParamTmp("tipo_cuenta", tipo_cuenta);
        ax.consumir();
    }
}

function validarFormPersonaCuenta(numero, cci, bancoId, tipo, tipo_cuenta) {
    $("#msjNumero").hide();
    $("#msjBanco").hide();
    $("#msjTipo").hide();
    $("#msjTipoCuenta").hide();
    $("#msjCci").hide();

    var bandera = true;
    let arrayCuentas = ["14", "15"];
    if (!arrayCuentas.includes(bancoId)) {

    }

    if (isEmpty(numero) || numero.length > 13 && tipo == 1) {
        $("#msjNumero").text("EL numero de cuenta debe tener 13 dígitos").show();
        bandera = false;
    }
    if (bancoId == 0) {
        $("#msjBanco").text("Seleccionar un banco").show();
        bandera = false;
    } 
    if (tipo == 0) {
        $("#msjTipo").text("Seleccionar un tipo").show();
        bandera = false;
    }

    if (tipo == 1) {
        if (tipo_cuenta == 0) {
            $("#msjTipoCuenta").text("Seleccionar un tipo cuenta").show();
            bandera = false;
        }
        if (isEmpty(cci) || cci == "-") {
            $("#msjCci").text("ingresar un cci").show();
            bandera = false;
        }        
    }

    return bandera;
}

function deshabilitarBoton() {
    $("#env").addClass('disabled');
    $("#env i").removeClass(c);
    $("#env i").addClass('fa fa-spinner fa-spin');
}
function habilitarBoton() {
    $("#env").removeClass('disabled');
    $("#env i").removeClass('fa-spinner fa-spin');
    $("#env i").addClass(c);
}

$('#txtDescripcion').keypress(function () {
    $('#msjDescripcion').hide();
});

function onResponseSavePersonaCuenta(data) {
    if (data[0]["vout_exito"] == 0) {
        habilitarBoton();
        loaderClose();
        $.Notification.autoHideNotify('warning', 'top right', 'Validación', data[0]["vout_mensaje"]);
    }
    else {
        habilitarBoton();
        loaderClose();
        $.Notification.autoHideNotify('success', 'top-right', '&Eacute;xito', data[0]["vout_mensaje"]);
        cargarListarPersonaCuenta();
    }
}

function onResponseCambiarEstadoPersonaClase(data) {
    if (data[0]["vout_exito"] == 1) {
        $.Notification.autoHideNotify('success', 'top-right', '&Eacute;xito', data[0]["vout_mensaje"]);
    }
    else {
        $.Notification.autoHideNotify('warning', 'top right', 'Validacion', data[0]["vout_mensaje"] + ", no se puede cambiar de estado");
    }
}

function editarPersonaCuenta(id, numero, cci, tipo, bancoId, tipo_cuenta) {
    VALOR_ID_USUARIO = id;
    VARLOR_NUMERO = numero;
    VARLOR_CCI = cci;
    VALOR_TIPO = tipo;
    VALOR_BANCO = bancoId;
    VALOR_TIPO_CUENTA = tipo_cuenta;
    cargarFormPersonaCuenta("Editar");
    cargarComponentesFormPersonacUENTA();
}

function llenarFormPersonaCuenta() {
    $('#txtNumero').val(VARLOR_NUMERO);
    $('#txtCci').val(VARLOR_CCI);
    $('#txtCci').val(VARLOR_CCI);
    asignarValorSelect2('cboTipo', VALOR_TIPO);
    asignarValorSelect2('cboBanco', VALOR_BANCO);
    asignarValorSelect2('cboTipoCuenta', VALOR_TIPO_CUENTA);
    if (VALOR_BANCO == 10) {
        $("#cboBanco").prop("disabled", true);
        $("#cboTipoCuenta").prop("disabled", true);
        $("#txtCci").prop("disabled", true);
        $("#txtCci").val();
    } else {
        $("#cboBanco").prop("disabled", false);
        $("#cboTipoCuenta").prop("disabled", false);
    }
}

function asignarValorSelect2(id, valor) {
    $("#" + id).select2().select2("val", valor);
    $("#" + id).select2({ width: '100%' });
}

function confirmarDeletePersonaClase(id, descripcion) {
    BANDERA_ELIMINAR = false;
    swal({
        title: "Est\xe1s seguro?",
        text: "Eliminarás " + descripcion + "",
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
            deletePersonaClase(id);
        } else {
            if (BANDERA_ELIMINAR == false) {
                swal("Cancelado", "La eliminaci\xf3n fue cancelada", "error");
            }
        }
    });
}

function deletePersonaClase(id) {
    ax.setAccion("deletePersonaClase");
    ax.addParamTmp("id", id);
    ax.consumir();
}

function cambiarEstadoPersonaClase(id) {
    ax.setAccion("cambiarEstadoPersonaClase");
    ax.addParamTmp("id", id);
    ax.consumir();
}

function obtenerTitulo() {
    tituloGlobal = $("#titulo").text();
    var titulo = tituloGlobal;
    $("#window").empty();

    if (!isEmpty(titulo)) {
        titulo = titulo.toLowerCase();
    }
    return titulo;
}

function cargarListarPersonaCuenta() {
    loaderShow(null);
    cargarDivTitulo('#window', 'vistas/com/persona/persona_cuenta_listar.php', tituloGlobal);
}

function cambiarTextoTipoCuenta() {
    let arrayCuentas = ["14", "15"];
    var cuentaId = select2.obtenerValor('cboBanco');
    if (arrayCuentas.includes(cuentaId)) {
        $("#lblTipoCuenta").html("Tipo Cuenta *");
        $("#lblCci").html("Cci *");
    } else {
        $("#lblTipoCuenta").html("Tipo Cuenta");
        $("#lblCci").html("Cci");
    }
}

function seleccionarBanco() {
    var tipo = select2.obtenerValor('cboTipo');
    if (tipo == "1") {
        $("#lblCci").html("Cci *");
        select2.asignarValor("cboBanco", 0)
        $("#cboBanco").prop("disabled", false);
        $("#cboTipoCuenta").prop("disabled", false);
        $("#txtCci").prop("disabled", false);
    } else {
        $("#lblCci").html("Cci");
        select2.asignarValor("cboBanco", 10)
        $("#cboBanco").prop("disabled", true);
        select2.asignarValor("cboTipoCuenta", 0)
        $("#cboTipoCuenta").prop("disabled", true);
        $("#txtCci").prop("disabled", true);
        $("#txtCci").val("");
        $("#lblTipoCuenta").html("Tipo Cuenta *");
    }
}