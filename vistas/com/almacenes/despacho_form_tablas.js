var boton = {
    enviarClase: $('#env i').attr('class'),
    accion: ''
};
var validacion = {
    organizadorExistencia: true
};
var camposDinamicos = [];

var tipoInterfaz = document.getElementById("tipoInterfaz").value;
var distribucionObligatoria = 0;
var contenidoArchivoJson = null;
var multiseleccion = 0;
var dataDocumentoTipo;
var documentoTipoTipo;

$(document).ready(function () {
    datePiker.iniciarPorClase('fecha');
    ax.setSuccess("onResponseMovimientoFormTablas");
    ax.addParamTmp("empresaId", commonVars.empresa);
    ax.addParamTmp("documentoId", null);
    ax.setAccion("obtenerConfiguracionesIniciales");
    ax.consumir();

    select2.iniciarElemento("cboUnidadMedida");
    select2.iniciarElemento("cboTipoPago");
    select2.iniciarElemento("cboIgv");

});

function onResponseMovimientoFormTablas(response) {
    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obtenerConfiguracionesIniciales':
                onResponseObtenerConfiguracionesIniciales(response.data);
                loaderClose();
                break;
            case 'obtenerDocumentoTipoDato':
                onResponseObtenerDocumentoTipoDato(response.data);
                loaderClose();
                break;
            case 'obtenerPlacaVehiculo':
                onResponseObtenerPlacaVehiculo(response.data);
                break;
            case 'obtenerPaqueteXAlmacenId':
                var arrayData = response.data;
                arrayData.push({ id: 0, text: "Seleccionar" });
                for (let index = 0; index < nroFilasInicial; index++) {
                    cargarBienDetalleCombo(arrayData, index);
                }
                break;
            case 'generarDespacho':
                onResponseGenerarDespacho(response.data);
                break;
        }
    } else {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obtenerConfiguracionesIniciales':
                break;
            case 'obtenerUnidadMedida':
                break;
            case 'enviar':
                cerrarModalAnticipo();
                loaderClose();
                habilitarBoton();
                onResponseEnviarError(response['message']);
                break;
        }
    }
}

function onResponseEnviarError(mensaje) {
    //ERROR CONTROLADO SUNAT
    if (mensaje.indexOf("[Cod: IMA02]") != -1) {
        swal("Error controlado", mensaje, "error");
    }
}

function onResponseObtenerConfiguracionesIniciales(data) {
    if (!isEmpty(data.documento_tipo)) {
        dataCofiguracionInicial = data;

        //llenar organizador en cabecera
        llenarComboOrganizadorCabecera(data.organizador);
        select2.asignarValor("cboOrganizador", $("#almacenId").val());
        cargarPaquete($("#almacenId").val());
        $("#cboDocumentoTipo").select2({
            width: "100%"
        }).on("change", function (e) {
            $('#txtComentario').val(dataDocumentoTipo[document.getElementById('cboDocumentoTipo').options.selectedIndex].comentario_defecto);

            //serie y numero
            $("#contenedorSerieDiv").hide();
            $("#contenedorNumeroDiv").hide();

            obtenerDocumentoTipoDato(e.val);
        });

        select2.cargar("cboPeriodo", data.periodo, "id", ["anio", "mes"]);
        select2.asignarValorQuitarBuscador('cboPeriodo', null);

        dataDocumentoTipo = data.documento_tipo;
        documentoTipoTipo = dataDocumentoTipo[0]['tipo'];

        select2.cargar("cboDocumentoTipo", data.documento_tipo, "id", "descripcion");

        if (isEmpty(data.movimientoTipo[0].documento_tipo_defecto_id)) {
            select2.asignarValor("cboDocumentoTipo", data.documento_tipo[0].id);
        } else {
            select2.asignarValor("cboDocumentoTipo", data.movimientoTipo[0].documento_tipo_defecto_id);
        }

        if (data.documento_tipo.length === 1) {
            select2.readonly("cboDocumentoTipo", true);
        }
        onResponseObtenerDocumentoTipoDato(data.documento_tipo_conf);

        nroFilasReducida = 5;
        inicialAlturaDetalle = $("#contenedorDetalle").height();
        $("#contenedorDetalle").css("height", $("#contenedorDetalle").height() + 38 * nroFilasReducida);
        organizadorIdDefectoTM = data.movimientoTipo[0].organizador_defecto;

        nroFilasInicial = parseInt(dataDocumentoTipo[0]['cantidad_detalle']);

        if (nroFilasInicial > 50) {
            $('#divTodasFilas').hide();
        }

        movimientoTipoIndicador = data.movimientoTipo[0].indicador;
        if (isEmpty(data.organizador)) {
            muestraOrganizador = false;
        }


        $('#txtComentario').val(data.documento_tipo[0].comentario_defecto);

        llenarTablaDetalle(data);

        if (!isEmpty(dataCofiguracionInicial.movimientoTipoColumna)) {
            $('#datatable').DataTable({
                "scrollX": true,
                "paging": false,
                "info": false,
                "filter": false,
                "ordering": false,
                "autoWidth": true,
                "destroy": true
            });
        }

        dibujarBotonesDeEnvio(data);
    }

    doc_TipoId = $("#cboDocumentoTipo").val();
}

function obtenerDocumentoTipoDato(documentoTipoId) {
    loaderShow();
    ax.setAccion("obtenerDocumentoTipoDato");
    ax.addParamTmp("documentoTipoId", documentoTipoId);
    ax.consumir();
}

function appendForm(html) {
    $("#formularioDocumentoTipo").append(html);
}

function obtenerDocumentoTipoSeleccionado() {
    let dataDocumentoTipo = dataCofiguracionInicial.documento_tipo.filter(item => item.id == select2.obtenerValor("cboDocumentoTipo"));
    if (!isEmpty(dataDocumentoTipo)) {
        return dataDocumentoTipo[0];
    }
    return dataDocumentoTipo;
}

function cambiarPeriodo() {
    var periodoId = obtenerPeriodoIdXFechaEmision();
    select2.asignarValorQuitarBuscador('cboPeriodo', periodoId);
}

function obtenerPeriodoIdXFechaEmision() {
    var periodoId = null;
    var dtdFechaEmision = obtenerDocumentoTipoDatoIdXTipo(9);
    if (!isEmpty(dtdFechaEmision)) {
        var fechaEmision = $('#datepicker_' + dtdFechaEmision).val();

        var fechaArray = fechaEmision.split('/');
        var d = parseInt(fechaArray[0], 10);
        var m = parseInt(fechaArray[1], 10);
        var y = parseInt(fechaArray[2], 10);

        $.each(dataCofiguracionInicial.periodo, function (index, item) {
            if (item.anio == y && item.mes == m) {
                periodoId = item.id;
            }
        });
    }
    return periodoId;
}

var personaDireccionId = 0;
var personaContactoResponsableId = 0;
var personaContactoAtencionId = 0;
var personaContactoSupervisorId = 0;
var fechaEmisionId = 0;
var textoDireccionId = 0;
var textoContactoId = 0;
var cambioPersonalizadoId = 0;
var validarCambioFechaEmision = false;
function onResponseObtenerDocumentoTipoDato(data) {
    dataCofiguracionInicial.documento_tipo_conf = data;

    camposDinamicos = [];
    personaDireccionId = 0;
    personaContactoResponsableId = 0;
    personaContactoAtencionId = 0;
    personaContactoSupervisorId = 0;
    var contador = 0;
    var mostrarCampo = true;

    $.each(dataCofiguracionInicial.documento_tipo, function (index, dt) {
        if (dt.identificador_negocio == 6 && dt.id == $("#cboDocumentoTipo").val()) {
            mostrarCampo = false;
        }
    });

    var documentoTipo = dataCofiguracionInicial.documento_tipo.filter(item => item.id == $("#cboDocumentoTipo").val());
    $("#formularioDocumentoTipo").empty();
    if (!isEmpty(data)) {
        var escribirItem;
        var contadorEspeciales = 0;
        $.each(data, function (index, item) {
            switch (parseInt(item.tipo)) {
                case 7:
                case 8:
                case 14:
                case 15:
                case 16:
                case 17:
                case 19:
                case 24:
                case 25:
                case 27:
                case 32:
                case 33:
                case 34:
                case 35:
                case 39:
                case 38:
                case 53:
                    contadorEspeciales += 1;
                    escribirItem = false;
                    break;
                default:
                    if (contador % 3 == 0) {
                        appendForm('<div class="row">');
                    }
                    contador++;

                    var html = '<div class="form-group col-md-4" id="id_' + item.id + '">';
                    if (item.tipo != 31) {
                        if (item.codigo != 11) {
                            html += '<label>' + item.descripcion + ' ' + ((item.opcional == 0) ? '*' : '') + '</label>';
                        }
                    }

                    //                    if (item.tipo == 5)
                    //                    {
                    //                        html += '<span class="divider"></span> <a onclick="cargarPersona();"><i class="ion-person-add" tooltip-btndata-toggle="tooltip" title="Agregar proveedor"></i></a>';
                    //                    }
                    switch (parseInt(item.tipo)) {
                        case 1:
                        case 7:
                        case 8:
                        case 14:
                        case 15:
                        case 16:
                        case 19:
                        case 2:
                        case 6:
                        case 12:
                        case 13:
                        case 3:
                        case 9:
                        case 10:
                        case 11:
                        case 24:
                        case 26:
                        case 36:
                            html += '<div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">';
                            break;
                        case 5:
                            html += '<span class="divider"></span> <a onclick="cargarPersona();"><i class="ion-person-add" tooltip-btndata-toggle="tooltip" title="Agregar ' + item.descripcion.toLowerCase() + '" style="color: #CB932A;"></i></a>' +
                                '<span class="divider"></span> <a onclick="actualizarCboPersona()"><i class="ion-refresh" tooltip-btndata-toggle="tooltip" title="Actualizar" style="color: #5CB85C;"></i></a>';
                        case 4:
                        //                        case 17:
                        case 18:
                            html += '<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding: 0;">';
                            break;
                    }

                    escribirItem = true;
                    break;
            }
            camposDinamicos.push({
                id: item.id,
                tipo: parseInt(item.tipo),
                opcional: item.opcional,
                descripcion: item.descripcion,
                codigo: item.codigo
            });
            var readonly = (parseInt(item.editable) === 0) ? 'readonly="true"' : '';
            var longitudMaxima = item.longitud;
            if (isEmpty(longitudMaxima)) {
                longitudMaxima = 45;
            }

            var maxNumero = 'onkeyup="if(this.value.length>' + longitudMaxima + '){this.value=this.value.substring(0,' + longitudMaxima + ')}"';

            switch (parseInt(item.tipo)) {
                case 1:
                    html += '<input type="number" id="txt_' + item.id + '" name="txt_' + item.id + '" ' + readonly + ' class="form-control" value="" maxlength="' + longitudMaxima + '" ' + maxNumero + ' style="text-align: right;" />';
                    break;

                case 7:
                    var value = '';
                    // Número autoincrementable
                    if (parseInt(item.tipo) === 8 && !isEmpty(item.data)) {
                        value = item.data;
                    } else if (!isEmpty(item.cadena_defecto)) {
                        value = item.cadena_defecto;
                    }

                    $("#contenedorSerieDiv").show();
                    $("#contenedorSerie").html('<input type="text" id="txt_' + item.id + '" name="txt_' + item.id + '" class="form-control" value="' + value + '" maxlength="' + longitudMaxima + '" placeholder="Serie"  style="text-align: right;" disabled/>');
                    break;

                case 8:
                    var value = '';
                    // Número autoincrementable
                    if (parseInt(item.tipo) === 8 && !isEmpty(item.data)) {
                        value = item.data;
                    } else if (!isEmpty(item.cadena_defecto)) {
                        value = item.cadena_defecto;
                    }

                    $("#contenedorNumeroDiv").show();
                    $("#contenedorNumero").html('<input type="text" id="txt_' + item.id + '" name="txt_' + item.id + '" class="form-control" value="' + value + '" maxlength="' + longitudMaxima + '" placeholder="Número"  style="text-align: right;" disabled/>');
                    break;

                case 14:
                    importes.totalId = 'txt_' + item.id;
                    $("#contenedorTotalDiv").show();
                    $("#contenedorTotal").html('<input type="number" id="txt_' + item.id + '" name="txt_' + item.id + '" ' + readonly + ' class="form-control" value="" maxlength="' + longitudMaxima + '"  ' + maxNumero + ' style="text-align: center;" />');
                    break;
                case 15:
                    importes.igvId = 'txt_' + item.id;
                    $("#contenedorIgvDiv").show();
                    $("#txtDescripcionIGV").html(item.descripcion);
                    $("#contenedorIgv").html('<input type="number" id="txt_' + item.id + '" name="txt_' + item.id + '" ' + readonly + ' class="form-control" value="" maxlength="' + longitudMaxima + '"  ' + maxNumero + ' style="text-align: center;" />');
                    $("#txtDescripcionIGV").css("font-weigh", "");
                    break;
                case 16:
                    importes.subTotalId = 'txt_' + item.id;
                    if (!isEmpty(documentoTipo) && documentoTipo[0]['identificador_negocio'] == 30) {
                        $("#chkIGV").prop("checked", false);
                        igvValor = 0;
                    } else {
                        // si existe un subtotal, Mostramos el checkbox de cálculo de precios con / sin igv
                        $("#contenedorChkIncluyeIGV").show();
                        //                    $("#chkIncluyeIGV").prop("checked", "checked");
                        $("#chkIncluyeIGV").prop("checked", "");
                        $("#chkIGV").prop("checked", true);
                        igvValor = 0.18;
                    }

                    $("#contenedorSubTotalDiv").show();
                    $("#contenedorSubTotal").html('<input type="number" id="txt_' + item.id + '" name="txt_' + item.id + '" ' + readonly + ' class="form-control" value="" maxlength="' + longitudMaxima + '"  ' + maxNumero + ' style="text-align: center;" />');
                    break;
                case 19:
                    percepcionId = item.id;
                    $("#contenedorPercepcionDiv").show();
                    $("#contenedorPercepcion").html('<input type="number" id="txt_' + item.id + '" name="txt_' + item.id + '" ' + readonly + ' class="form-control" value="" maxlength="' + longitudMaxima + '"  ' + maxNumero + ' style="text-align: center;" onchange="calculeTotalMasPercepcion(' + item.id + ')"  disabled/>');
                    break;
                case 2:
                case 6:
                case 12:
                case 13:
                    var value = '';
                    if (item.descripcion == "Utilidad") {
                        if (!isEmpty(item.cadena_defecto)) {
                            value = item.cadena_defecto;
                        }
                        html += '<input type="text" id="txt_' + item.id + '" name="txt_' + item.id + '" class="form-control" value="' + value + '" maxlength="' + longitudMaxima + '" oninput="handleDecimalInput(event)" />';
                    } else {
                        // Número autoincrementable
                        if (parseInt(item.tipo) === 8 && !isEmpty(item.data)) {
                            value = item.data;
                        } else if (!isEmpty(item.cadena_defecto)) {
                            value = item.cadena_defecto;
                        }

                        if (parseInt(item.numero_defecto) === 1) {
                            textoDireccionId = item.id;
                        }

                        if (parseInt(item.numero_defecto) === 2) {
                            value = dataCofiguracionInicial.dataEmpresa[0]['direccion'];
                        }

                        html += '<input type="text" id="txt_' + item.id + '" name="txt_' + item.id + '" class="form-control" value="' + value + '" maxlength="' + longitudMaxima + '"/>';
                    }

                    break;
                case 9:
                    fechaEmisionId = item.id;
                    html += '<input type="text" class="form-control fecha" placeholder="dd/mm/yyyy" id="datepicker_' + item.id + '" >' +
                        '<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>';
                    break;
                case 3:
                case 10:
                case 11:
                    if (item.codigo != '11') {
                        html += '<input type="text" class="form-control fecha" placeholder="dd/mm/yyyy" id="datepicker_' + item.id + '">' +
                            '<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>';
                    }
                    break;
                case 4:
                    html += '<select name="cbo_' + item.id + '" id="cbo_' + item.id + '" class="select2"></select>';
                    if (item.codigo == "10") {
                        html += '<span name="txt_' + item.id + '" id="txt_' + item.id + '" class="control-label" style="font-style: normal;" hidden=""></span>';
                    }
                    id_cboMotivoMov = (dataCofiguracionInicial.movimientoTipo[0]["codigo"] == 20) ? item.id : null;
                    break;
                case 5:
                    html += '<div id ="div_persona" ><select name="cbo_' + item.id + '" id="cbo_' + item.id + '" class="select2">';
                    html += '<option value="' + 0 + '">Seleccione ' + item.descripcion.toLowerCase() + '</option>';
                    if (!isEmpty(item.data)) {
                        $.each(item.data, function (indexPersona, itemPersona) {
                            html += '<option value="' + itemPersona.id + '">' + itemPersona.nombre + ' | ' + itemPersona.codigo_identificacion + '</option>';
                        });
                    }
                    html += '</select>';
                    //                    html += '<div class="form-group col-lg-4"><a href="#" class="btn btn-info w-sm m-b-5" id="id" style="border-radius: 0px;" onclick="cargarPersona();" ><i class="ion-plus"></i></div></div>';
                    break;
                case 17:
                    var htmlOrg = '';
                    htmlOrg += '<div id ="div_organizador_destino" ><select name="cbo_' + item.id + '" id="cbo_' + item.id + '" class="select2" placeholder="Seleccione almacén de llegada" onchange="onChangeOrganizadorDestino()">';

                    id_cboDestino = (dataCofiguracionInicial.movimientoTipo[0]["codigo"] == 20) ? item.id : null;
                    htmlOrg += '<option></option>';
                    $.each(item.data, function (indexOrganizador, itemOrganizador) {
                        htmlOrg += '<option value="' + itemOrganizador.id + '">' + itemOrganizador.descripcion + '</option>';
                    });
                    htmlOrg += '</select>';

                    $("#h4OrganizadorDestino").append(htmlOrg);
                    $("#divContenedorOrganizadorDestino").show();
                    break;
                case 18:
                    personaDireccionId = item.id;
                    html += '<div id ="div_direccion" ><select name="cbo_' + item.id + '" id="cbo_' + item.id + '" class="select2">';
                    //html += '<option value="' + 0 + '">Seleccione la dirección</option>';
                    html += '</select>';
                    //                    html += '<div class="form-group col-lg-4"><a href="#" class="btn btn-info w-sm m-b-5" id="id" style="border-radius: 0px;" onclick="cargarPersona();" ><i class="ion-plus"></i></div></div>';
                    break;
                case 20:
                    html += '<div id ="div_cuenta" ><select name="cbo_' + item.id + '" id="cbo_' + item.id + '" class="select2">';
                    html += '<option value="' + 0 + '">Seleccione la cuenta</option>';
                    $.each(item.data, function (indexCuenta, itemCuenta) {
                        html += '<option value="' + itemCuenta.id + '">' + itemCuenta.descripcion_numero + '</option>';
                    });
                    html += '</select>';
                    break;
                case 21:
                    html += '<div id ="div_actividad" ><select name="cbo_' + item.id + '" id="cbo_' + item.id + '" class="select2">';
                    html += '<option value="' + 0 + '">Seleccione la actividad</option>';
                    $.each(item.data, function (indexActividad, itemActividad) {
                        html += '<option value="' + itemActividad.id + '">' + itemActividad.codigo + ' | ' + itemActividad.descripcion + '</option>';
                    });
                    html += '</select>';
                    break;
                case 22:
                    html += '<div id ="div_retencion_detraccion" ><select name="cbo_' + item.id + '" id="cbo_' + item.id + '" class="select2">';
                    html += '<option value="' + 0 + '">Seleccione retención o detracción</option>';
                    $.each(item.data, function (indexRetencionDetraccion, itemRetencionDetraccion) {
                        html += '<option value="' + itemRetencionDetraccion.id + '">' + itemRetencionDetraccion.descripcion + '</option>';
                    });
                    html += '</select>';
                    break;
                case 23:
                    html += '<div id ="div_persona_' + item.id + '" ><select name="cbo_' + item.id + '" id="cbo_' + item.id + '" class="select2">';
                    if (dataCofiguracionInicial.documento_tipo[0]["identificador_negocio"] == 1) {
                        html += '<option value="' + 0 + '">Seleccione a quién va dirigido</option>';
                    } else {
                        html += '<option value="' + 0 + '">Seleccione ' + item.descripcion.toLowerCase() + '</option>';
                    }
                    $.each(item.data, function (indexPersona, itemPersona) {
                        html += '<option value="' + itemPersona.id + '">' + itemPersona.nombre + ' | ' + itemPersona.codigo_identificacion + '</option>';
                    });
                    html += '</select>';
                    //                    html += '<div class="form-group col-lg-4"><a href="#" class="btn btn-info w-sm m-b-5" id="id" style="border-radius: 0px;" onclick="cargarPersona();" ><i class="ion-plus"></i></div></div>';
                    break;
                case 24:
                    cambioPersonalizadoId = item.id;
                    //                    $("#contenedorCambioPersonalizado").show();
                    $("#cambioPersonalizado").html('<input type="number" id="txt_' + item.id + '" name="txt_' + item.id + '" ' + readonly + ' class="form-control" value="" maxlength="' + longitudMaxima + '"  ' + maxNumero + ' style="text-align: right;" placeholder="' + item.descripcion + '"/>');
                    break;
                case 25:
                    $("#divContenedorTipoPago").show();
                    break;
                case 26:
                    html += '<div id ="div_vendedor" ><select name="cbo_' + item.id + '" id="cbo_' + item.id + '" class="select2">';
                    html += '<option value="' + 0 + '">Seleccione ' + item.descripcion.toLowerCase() + '</option>';
                    if (!isEmpty(item.data)) {
                        $.each(item.data, function (indexPersona, itemPersona) {
                            html += '<option value="' + itemPersona.id + '">' + itemPersona.persona_nombre + ' | ' + itemPersona.codigo_identificacion + '</option>';
                        });
                    }
                    html += '</select>';
                    break;
                case 27:
                    $("#divContenedorAdjunto").show();
                    let dataDocumentoTipoSeleccionado = obtenerDocumentoTipoSeleccionado();
                    if (dataDocumentoTipoSeleccionado.identificador_negocio == 1) {
                        $("#btnVisualizarInformacionAdjunto").show();
                    }
                    iniciarArchivoAdjunto();
                    break;
                case 31:
                    if (mostrarCampo) { //DT Guia de remision BH
                        html += '<label>' + item.descripcion + ' ' + ((item.opcional == 0) ? '*' : '') + '</label>';
                        html += '<div id ="divContenedorAdjuntoMultiple">';
                        html += '<a class="btn btn-primary btn-sm m-b-5" onclick="adjuntar();"><i class="fa fa-cloud-upload"></i> Adjuntar archivos</a>';
                        iniciarArchivoAdjuntoMultiple();
                    }

                    break;
                case 32:
                    $("#contenedorSwitchProductoDuplicado").show();
                    break;
                case 33:
                    importes.seguroId = 'txt_' + item.id;
                    $("#contenedorSeguroDiv").show();
                    $("#contenedorSeguro").html('<input type="number" id="txt_' + item.id + '" name="txt_' + item.id + '" ' + readonly + ' class="form-control" value="' + devolverDosDecimales(item.numero_defecto) + '" maxlength="' + longitudMaxima + '"  ' + maxNumero + '  onchange="asignarImporteDocumento()"  style="text-align: center;" />');
                    break;
                case 34:
                    importes.otrosId = 'txt_' + item.id;
                    $("#contenedorOtrosDiv").show();
                    $("#contenedorOtros").html('<input type="number" id="txt_' + item.id + '" name="txt_' + item.id + '" ' + readonly + ' class="form-control" value="' + devolverDosDecimales(item.numero_defecto) + '" maxlength="' + longitudMaxima + '"  ' + maxNumero + '  onchange="asignarImporteDocumento()"  style="text-align: center;" />');
                    break;
                case 35:
                    importes.exoneracionId = 'txt_' + item.id;
                    $("#contenedorExoneracionDiv").show();
                    $("#contenedorExoneracion").html('<input type="number" id="txt_' + item.id + '" name="txt_' + item.id + '" ' + readonly + ' class="form-control" value="' + devolverDosDecimales(item.numero_defecto) + '" maxlength="' + longitudMaxima + '"  ' + maxNumero + ' onchange="asignarImporteDocumento()"  style="text-align: center;" />');
                    break;

                case 36:
                    cboDetraccionId = item.id;
                    html += '<div id ="div_detraccion" ><select name="cbo_' + item.id + '" id="cbo_' + item.id + '" class="select2">';
                    html += '<option value="' + 0 + '">Seleccione ' + item.descripcion.toLowerCase() + '</option>';
                    if (!isEmpty(dataCofiguracionInicial.dataDetraccion)) {
                        $.each(dataCofiguracionInicial.dataDetraccion, function (indexDetraccion, itemDetraccion) {
                            html += '<option value="' + itemDetraccion.id + '">' + itemDetraccion.descripcion + '</option>';
                        });
                    }
                    html += '</select>';
                    html += '<span name="txt_' + item.id + '" id="txt_' + item.id + '" class="control-label" style="font-style: normal;" hidden=""></span>';

                    break;
                case 38:
                    importes.icbpId = 'txt_' + item.id;
                    $("#contenedorIcbpDiv").show();
                    $("#contenedorIcbp").html('<input type="number" id="txt_' + item.id + '" name="txt_' + item.id + '" ' + readonly + ' class="form-control" value="' + devolverDosDecimales(item.numero_defecto) + '" maxlength="' + longitudMaxima + '"  ' + maxNumero + ' onchange="asignarImporteDocumento()"  style="text-align: center;" />');
                    break;
                case 39:
                    $("#contenedorSwitchCotizacionTottus").show();
                    break;
                case 40:
                    if (parseInt(item.numero_defecto) === 1) {
                        textoContactoId = item.id;
                    }
                    if (item.descripcion == "Responsable ticket") {
                        personaContactoResponsableId = item.id;
                    } else if (item.descripcion == "Atención a") {
                        personaContactoAtencionId = item.id;
                    } else if (item.descripcion == "Supervisor") {
                        personaContactoSupervisorId = item.id;
                    }
                    html += '<div id ="div_contacto" ><select name="cbo_' + item.id + '" id="cbo_' + item.id + '" class="select2">';
                    html += '</select>';
                    break;
                case 54:
                case 55:
                    html += '<select name="cbo_' + item.id + '" id="cbo_' + item.id + '" class="select2"></select>';
                    break
            }
            if (escribirItem) {
                html += '</div></div>';
                appendForm(html);
                if (contador % 3 == 0) {
                    appendForm('</div>');
                }
            }
            switch (parseInt(item.tipo)) {
                case 3:
                //                case 9:
                case 10:
                case 11:
                    $('#datepicker_' + item.id).datepicker({
                        isRTL: false,
                        format: 'dd/mm/yyyy',
                        autoclose: true,
                        language: 'es'
                    });
                    $('#datepicker_' + item.id).datepicker('setDate', item.data);
                    break;
                case 9:
                    $('#datepicker_' + item.id).datepicker({
                        isRTL: false,
                        format: 'dd/mm/yyyy',
                        autoclose: true,
                        language: 'es'
                    }).on('changeDate', function (ev) {
                        let dataDocumentoTipoSeleccionado = obtenerDocumentoTipoSeleccionado();
                        if (dataDocumentoTipoSeleccionado.tipo != "4") {
                            cambiarPeriodo();
                        }
                    });
                    $('#datepicker_' + item.id).datepicker('setDate', item.data);
                    cambiarPeriodo();
                    fechaEmisionAnterior = item.data;
                    break;
                case 4:
                    select2.cargar("cbo_" + item.id, item.data, "id", "descripcion");
                    $("#cbo_" + item.id).select2({
                        width: '100%'
                    }).on("change", function (e) {
                        if (item.codigo == "10") {
                            obtenerMontoRetencion();
                        }
                    });

                    if (!isEmpty(item.lista_defecto)) {
                        var id = parseInt(item.lista_defecto);
                        select2.asignarValor("cbo_" + item.id, id);
                    } else {
                        select2.asignarValor("cbo_" + item.id, null);
                    }

                    if (dataCofiguracionInicial.movimientoTipo[0]["codigo"] == 20) {
                        select2.asignarValor("cbo_" + item.id, null);
                    }

                    break;
                case 5:
                    $("#cbo_" + item.id).select2({
                        width: '100%'
                    }).on("change", function (e) {

                    });
                    break;
                case 17:
                    $("#cbo_" + item.id).select2({
                        width: '100%'
                    });
                    break;
                case 18:
                    $("#cbo_" + item.id).select2({
                        width: '100%'
                    });
                    break;
                case 20:
                case 21:
                case 26:
                    $("#cbo_" + item.id).select2({
                        width: '100%'
                    });

                    if (!isEmpty(item.numero_defecto)) {
                        var id = parseInt(item.numero_defecto);
                        select2.asignarValor("cbo_" + item.id, id);
                    }

                    if (item.editable == 0) {
                        $("#cbo_" + item.id).attr('disabled', 'disabled');
                    }
                    break;
                case 22:
                    $("#cbo_" + item.id).select2({
                        width: '100%'
                    });
                    break;
                case 23:
                    $("#cbo_" + item.id).select2({
                        width: '100%'
                    }).on("change", function (e) {
                        obtenerPlacasVehiculo(e.val);
                    });
                    break;
                case 36:
                    $("#cbo_" + item.id).select2({
                        width: '100%'
                    }).on("change", function (e) {
                        obtenerMontoDetraccion(e.val, item.id);
                    });
                    break;
                case 40:
                    $("#cbo_" + item.id).select2({
                        width: '100%'
                    });
                    break;
                case 54:
                    var almacenOrigen = select2.obtenerValor("cboOrganizador");
                    var organizadoreFiltrados = item.data.filter(function (obj) {
                        return obj.id != almacenOrigen;
                    });
                    select2.cargar("cbo_" + item.id, organizadoreFiltrados, "id", "descripcion");
                    $("#cbo_" + item.id).select2({
                        width: '100%'
                    });
                    select2.asignarValor("cbo_" + item.id, 0);
                    break;
                case 55:
                    $("#cbo_" + item.id).select2({
                        width: '100%'
                    });
                    break;
            }
        });

    }
}


function obtenerDocumentoTipoDatoIdXTipo(tipo) {
    var dataConfig = dataCofiguracionInicial.documento_tipo_conf;

    var id = null;
    if (!isEmpty(dataConfig)) {
        $.each(dataConfig, function (index, item) {
            if (parseInt(item.tipo) === parseInt(tipo)) {
                id = item.id;
                return false;
            }
        });
    }

    return id;
}

function dibujarBotonesDeEnvio(data) {
    var html = '<a href="#" class="btn btn-danger" onclick="cargarPantallaListaDespacho()"><i class="fa fa-close"></i> Cancelar</a>';
    html += '&nbsp;&nbsp;<button type="button" class="btn btn-success" onclick="enviarDespacho()" name="env" id="env"><i class="fa fa-save" ></i> Generar</button>';
    $("#divAccionesEnvio").append(html);
}

var organizadorIdDefectoTM;
function llenarComboOrganizadorCabecera(data) {
    let dataDocumentoTipoSeleccionado = obtenerDocumentoTipoSeleccionado();
    $("#divContenedorOrganizador").show();

    $("#cboOrganizador").select2({
        width: "100%"
    }).on("change", function (e) {
        loaderShow();
        cargarPaquete(e.val);
    });

    select2.cargar("cboOrganizador", data, "id", "descripcion");

    if (organizadorIdDefectoTM != 0)
        select2.asignarValor("cboOrganizador", organizadorIdDefectoTM);
    else
        select2.asignarValor("cboOrganizador", data[0].id);
}

function obtenerPlacasVehiculo(id) {
    ax.setAccion("obtenerPlacaVehiculo");
    ax.addParamTmp("id", id);
    ax.consumir();
}

function onResponseObtenerPlacaVehiculo(data) {
    var dtdId = obtenerDocumentoTipoDatoIdXTipo(55);
    select2.cargar("cbo_" + dtdId, data, "id", "placa");
}

function llenarTablaDetalle(data) {
    var cuerpo = "";
    var nroFilas = 5;
    //LLENAR TABLA DETALLE
    for (var i = 0; i < nroFilas; i++) {
        cuerpo += llenarFilaDetalleTabla(i);
    }

    $('#datatable tbody').append(cuerpo);
}

var KPADINGTD = 1;
function llenarFilaDetalleTabla(indice) {
    document.getElementById("th_Nro").style.width = "10px";
    var boton_eliminar = "";
    if (indice != 0) {
        boton_eliminar = "&nbsp;&nbsp;<a id='btn_Eliminar_" + indice + "' onclick='confirmarEliminar(" + indice + ");' hidden><i class='fa fa-trash-o' style='color:#cb2a2a;' title='Eliminar'></i></a>";
    }
    var fila = "<tr id=\"trDetalle_" + indice + "\">";
    fila = fila + "<td style='border:0; width: 10; vertical-align: middle; padding-right: 10px;' align='right'><div class'input-group col-lg-12 col-md-12 col-sm-12 col-xs-12' style='text-align: center;'>" + (indice + 1) + boton_eliminar + "</div></td>";
    //producto
    fila += "<td style='border:0; width: 400px; vertical-align: middle; padding: " + KPADINGTD + "px;' id=\"td_" + indice + "\">" + agregarBienDetalleTabla(indice) + "</td>";
    //Cantidad
    fila += "<td class='dtdetalle_cantidad text-center' style='border:0; width: 20px; vertical-align: middle; padding: " + KPADINGTD + "px;'><label id='lbl_" + indice + "'> </label></td>";
    fila = fila + "</tr>";
    return fila;
}



function agregarBienDetalleTabla(i) {
    var $html = "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\">" +
        "<span name=\"obsupcboBien_" + i + "\" id=\"obsupcboBien_" + i + "\" class='select2-chosen hidden'>&nbsp;</span>" +
        "<select name=\"cboBien_" + i + "\" id=\"cboBien_" + i + "\" class=\"select2\" ></select>" +
        "<BR><span name=\"obscboBien_" + i + "\" id=\"obscboBien_" + i + "\" class='select2-chosen'></span>" +
        "<span class='input-group-btn'>";

    $html += "</span>" +
        "</div>";

    return $html;
}

var indiceBien;
var dataPaquete = [];
var dataDespachoDetalle = [];
function cargarBienDetalleCombo(data, indice) {
    if (!isEmpty(data)) {
        dataPaquete = data;
        select2.cargar("cboBien_" + indice, data, "id", "text");
        $("#cboBien_" + indice).select2({
            width: "100%",
        }).on("change", function (e) {
            if (!isEmpty(e.val)) {
                indiceBien = indice;
                dataDespachoDetalle.splice(indice, 1);
                if (!isEmpty(dataDespachoDetalle)) {
                    var verificarPaquete = dataDespachoDetalle.filter(item => item.id == e.val);
                    if (!isEmpty(verificarPaquete) || verificarPaquete.length > 0) {
                        select2.asignarValor("cboBien_" + indice, 0);
                        mostrarAdvertencia("El paquete ya fue ingresado, en la fila " + (indice + 1));
                        return false;
                    }
                }

                var datosPaquete = dataPaquete.filter(item => item.id == e.val);
                dataDespachoDetalle.push(datosPaquete[0]);
                $("#lbl_" + indiceBien).html(devolverDosDecimales(datosPaquete[0].cantidad));
            } else {
                dataDespachoDetalle.splice(indice, 1);
            }

        });
        select2.asignarValor("cboBien_" + indice, 0);
    }
}

function cargarPaquete(id) {
    ax.setAccion("obtenerPaqueteXAlmacenId");
    ax.addParamTmp("id", id);
    ax.consumir();
}

function limpiarDetalle() {
    indiceLista = [];
    banderaCopiaDocumento = 0;
    nroFilasEliminados = 0;
    numeroItemFinal = 0;
    if (multiseleccion == 0) {
        indexDetalle = 0;
        $('#dgDetalle').empty();
    }
    $("#contenedorDetalle").css("height", inicialAlturaDetalle + 38 * nroFilasReducida);
    if (multiseleccion == 0) {
        llenarTablaDetalle(dataCofiguracionInicial);
    }
}

function agregarFila() {
    if (nroFilasInicial > nroFilasReducida || parseInt(dataDocumentoTipo[0]['cantidad_detalle']) == 0) {
        //LLENAR TABLA DETALLE
        var fila = llenarFilaDetalleTabla(nroFilasReducida);

        $('#datatable tbody').append(fila);
        //LLENAR COMBOS
        cargarBienDetalleCombo(dataPaquete, nroFilasReducida);
        // nroFilasInicial++;
        nroFilasReducida++;
    } else {
        $('#divTodasFilas').hide();
        $('#divAgregarFila').hide();
    }

}

function verTodasFilas() {
    var cuerpo = "";
    //LLENAR TABLA DETALLE
    for (var i = nroFilasReducida; i < nroFilasInicial; i++) {
        //var i=0;
        cuerpo += llenarFilaDetalleTabla(i);
    }

    $('#datatable tbody').append(cuerpo);
    //LLENAR COMBOS
    for (var i = nroFilasReducida; i < nroFilasInicial; i++) {
        cargarBienDetalleCombo(dataPaquete, i);
    }

    nroFilasReducida = nroFilasInicial;
    $('#divTodasFilas').hide();
    if (dataCofiguracionInicial.documento_tipo[0].cantidad_detalle != 0 && !isEmpty(dataCofiguracionInicial.documento_tipo[0].cantidad_detalle)) {
        $('#divAgregarFila').hide();
    }

    banderaVerTodasFilas = 1;
}

function enviarDespacho() {
    loaderShow();
    var almacenDestino = select2.obtenerValor("cbo_" + obtenerDocumentoTipoDatoIdXTipo(54));
    var vehiculoId = select2.obtenerValor("cbo_" + obtenerDocumentoTipoDatoIdXTipo(55));
    var pesaje = $("#txt_" + obtenerDocumentoTipoDatoIdXTipo(2)).val();

    ax.setAccion('generarDespacho');
    ax.addParamTmp("almacenDestino", almacenDestino);
    ax.addParamTmp("vehiculoId", vehiculoId);
    ax.addParamTmp("pesaje", pesaje);
    ax.addParamTmp("detalle", dataDespachoDetalle);
    ax.consumir();
}

function onResponseGenerarDespacho(data) {
    swal({
        title: data.tipo_mensaje == 1 ? "Confirmación" : "Advertencia",
        text: data.mensaje,
        type: data.tipo_mensaje == 1 ? "success" : "warning",
        html: true,
        showCancelButton: false,
        confirmButtonColor: "#33b86c",
        confirmButtonText: "Aceptar",
        closeOnConfirm: true,
        closeOnCancel: true,
        timer: 2000
    });
    cargarPantallaListaDespacho();
}

function cargarPantallaListaDespacho() {
    cargarDiv("#window", "vistas/com/almacenes/despacho_listar.php?documento_tipo=289");
}