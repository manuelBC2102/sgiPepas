var boton = {
    enviarClase: $('#env i').attr('class'),
    accion: ''
};
var validacion = {
    organizadorExistencia: true
};
var camposDinamicos = [];
var importes = {
    fleteId: null,
    seguroId: null,
    otrosId: null,
    exoneracionId: null,
    totalId: null,
    subTotalId: null,
    igvId: null,
    calculoId: null,
    icbpId: null
};
var cabecera = {
    chkDocumentoRelacion: 1
};
var bandera = {
    primeraCargaDocumentosRelacion: true,
    mostrarDivDocumentoRelacion: false,
    validacionAnticipos: 0
};
var request = {
    documentoRelacion: [] // Ids de documentos a relacionar
};
var variable = {
    documentoIdCopia: null,
    movimientoIdCopia: null
};
var dataTemporal = {
    anticipos: null
}

var tipoInterfaz = document.getElementById("tipoInterfaz").value;
var distribucionObligatoria = 0;
var contenidoArchivoJson = null;
var multiseleccion = 0;
var dataGrupoProducto = [];
$(document).ready(function () {
    datePiker.iniciarPorClase('fecha');
    initSwitch();
    ax.setSuccess("onResponseMovimientoFormTablas");
    ax.addParamTmp("empresaId", commonVars.empresa);
    ax.addParamTmp("documentoId", null);
    ax.setAccion("obtenerConfiguracionesIniciales");
    ax.consumir();

    select2.iniciarElemento("cboUnidadMedida");
    select2.iniciarElemento("cboTipoPago");
    select2.iniciarElemento("cboIgv");

    $('#selectAll').on('change', function () {
        var isChecked = this.checked;
        $('input[name=checkselect]').prop('checked', isChecked);
        var table = $('#dtDocumentoRelacion').DataTable();
        var filasSeleccionadas = table.rows(':has(input[name=checkselect]:checked)').data().toArray();

        console.log(filasSeleccionadas);
    });
    $('#btn_agregar').click(function () {
        var table = $('#dtDocumentoRelacion').DataTable();
        var filasSeleccionadas = table.rows(':has(input[name=checkselect]:checked)').data().toArray();
        var bandera_copia = true;
        if (filasSeleccionadas.length <= 0) {
            $.Notification.autoHideNotify('warning', 'top right', 'Validación', " Seleccione uno o más comprobantes, para realizar el proceso");
            bandera_copia = false;
        }

        if (bandera_copia) {
            multiseleccion = 1;
            reinicializarDataTableDetalle();
            $.each(filasSeleccionadas, function (index, item) {
                loaderShow();
                agregarDocumentoRelacion(item.documento_tipo_id, item.documento_id, item.movimiento_id, item.moneda_id, 1);
                loaderClose();
            });
        }

    });
    $('#android-switchCotizacionTottus').on('change', function () {
        var checkbox = document.getElementById("android-switchCotizacionTottus");
        var botones = document.querySelectorAll('.btn_agrupador');
        var dtdetalle = document.querySelectorAll('.dtdetalle_agrupador');
        if (checkbox.checked) {
            $("#id_3044").show();
            $("#id_3045").show();
            $("#id_3046").show();
            $("#id_3047").show();
            $("#id_3048").show();
            $("#id_3049").show();
            $("#id_3050").show();
            $("#id_3051").show();
            $("#id_3052").show();

            $("#id_3044").prop("required", true);
            $("#id_3045").prop("required", true);
            $("#id_3046").prop("required", true);
            $("#id_3047").prop("required", true);
            $("#id_3048").prop("required", true);
            $("#id_3049").prop("required", true);
            $("#id_3050").prop("required", true);
            $("#id_3051").prop("required", true);
            $("#id_3052").prop("required", true);
            $("#tb_agrupador").removeClass('hidden');
            botones.forEach(function (boton) {
                boton.classList.remove('hidden');
            });
            dtdetalle.forEach(function (boton) {
                boton.classList.remove('hidden');
            });
            $("#datatable").css("width", "1700px");
            $(".dataTables_scrollHeadInner").css("width", "1750px");
            $(".dataTables_scrollHeadInner table").css("width", "1750px");
        } else {
            $("#id_3044").hide();
            $("#id_3045").hide();
            $("#id_3046").hide();
            $("#id_3047").hide();
            $("#id_3048").hide();
            $("#id_3049").hide();
            $("#id_3050").hide();
            $("#id_3051").hide();
            $("#id_3052").hide();

            $("#id_3044").prop("required", false);
            $("#id_3045").prop("required", false);
            $("#id_3046").prop("required", false);
            $("#id_3047").prop("required", false);
            $("#id_3048").prop("required", false);
            $("#id_3049").prop("required", false);
            $("#id_3050").prop("required", false);
            $("#id_3051").prop("required", false);
            $("#id_3052").prop("required", false);
            $("#tb_agrupador").addClass('hidden');
            botones.forEach(function (boton) {
                boton.classList.add('hidden');
            });
            dtdetalle.forEach(function (boton) {
                boton.classList.add('hidden');
            });
            $("#datatable").css("width", "1369px");
            $(".dataTables_scrollHeadInner").css("width", "1369px");
            $(".dataTables_scrollHeadInner table").css("width", "1369px");
        }
    });
});

function onResponseMovimientoFormTablas(response) {
    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obtenerConfiguracionesIniciales':
                onResponseObtenerConfiguracionesIniciales(response.data);
                loaderClose();
                break;
            case 'obtenerUnidadMedida':
                response.data["indice"] = response[PARAM_TAG];
                onResponseObtenerUnidadesMedida(response.data);
                loaderClose();
                break;
            case 'obtenerPrecioUnitario':
                response.data["indice"] = response[PARAM_TAG];
                onResponseObtenerPrecioUnitario(response.data);
                loaderClose();
                break;
            case 'obtenerDocumentoTipoDato':
                onResponseObtenerDocumentoTipoDato(response.data);
                loaderClose();
                break;
            case 'enviar':
                onResponseEnviar(response.data);
                break;
            case 'enviarEImprimir':
                cargarDatosImprimir(response.data, 1);
                break;
            case 'obtenerPersonas':
                onResponseObtenerPersonas(response.data);
                break;
            case 'obtenerStockPorBien':
                if (modalReserva == 1) {
                    onResponseObtenerStockPorBienReserva(response.data, response[PARAM_TAG]);
                } else {
                    onResponseObtenerStockPorBien(response.data, response[PARAM_TAG]);
                }
                break;
            case 'obtenerPrecioPorBien':
                onResponseObtenerPrecioPorBien(response.data, response[PARAM_TAG]);
                break;
            case 'obtenerStockActual':
                response.data[0]["indice"] = response[PARAM_TAG];
                onResponseObtenerStockActual(response.data);
                break;
            case 'obtenerPersonaDireccion':
                onResponseObtenerPersonaDireccion(response.data);
                break;
            case 'obtenerPersonaContacto':
                onResponseobtenerPersonaContacto(response.data);
                break;
            case 'guardarDocumentoGenerado':
                if (boton.accion == 'enviarEImprimir') {
                    cargarDatosImprimir(response.data, 1);
                } else {
                    cargarPantallaListarCompra();
                }
                break;
            case 'obtenerPreciosEquivalentes':
                onResponseObtenerPreciosEquivalentes(response.data);
                loaderClose();
                break;
            case 'obtenerBienPrecio':
                response.data["indice"] = response[PARAM_TAG];
                onResponseObtenerBienPrecio(response.data);
                loaderClose();
                break;
            case 'buscarDocumentoRelacion':
                onResponseBuscarDocumentoRelacion(response.data);
                loaderClose();
                break;
            //FUNCIONES PARA COPIAR DOCUMENTO
            case 'obtenerConfiguracionBuscadorDocumentoRelacion':
                onResponseObtenerConfiguracionBuscadorDocumentoRelacion(response.data);
                buscarDocumentoRelacionPorCriterios();
                loaderClose();
                break;
            case 'obtenerDocumentoRelacion':
                onResponseObtenerDocumentoRelacion(response.data);
                loaderClose();
                break;
            case 'obtenerDocumentoRelacionCabecera':
                onResponseObtenerDocumentoRelacionCabecera(response.data);
                loaderClose();
                break;
            case 'obtenerDocumentoRelacionVisualizar':
                onResponseObtenerDocumentoRelacionVisualizar(response.data);
                loaderClose();
                break;
            case 'obtenerDocumentoRelacionDetalle':
                cargarDetalleDocumentoRelacion(response.data);
                loaderClose();
                break;
            case 'verificarTipoUnidadMedidaParaTramo':
                if (!isEmpty(response.data)) {
                    response.data["indice"] = response[PARAM_TAG];
                }
                onResponseVerificarTipoUnidadMedidaParaTramo(response.data);
                loaderClose();
                break;
            case 'registrarTramoBien':
                onResponseRegistrarTramoBien(response.data);
                loaderClose();
                break;
            case 'obtenerTramoBien':
                onResponseObtenerTramoBien(response.data, response[PARAM_TAG]);
                loaderClose();
                break;
            case 'obtenerPrecioCompra':
                response.data["indice"] = response[PARAM_TAG];
                onResponseObtenerPrecioCompra(response.data);
                loaderClose();
                break;
            case 'modificarDetallePrecios':
                onResponseModificarDetallePrecios(response.data);
                break;
            case 'modificarDetallePreciosXMonedaXOpcion':
                onResponseModificarDetallePreciosXMonedaXOpcion(response.data);
                loaderClose();
                break;
            case 'obtenerTipoCambioXFecha':
                onResponseObtenerTipoCambioXFecha(response.data);
                loaderClose();
                break;
            case 'enviarCorreosMovimiento':
                loaderClose();
                $('#modalCorreos').modal('hide');
                $('.modal-backdrop').hide();
                cargarPantallaListarCompra();
                break;
            case 'eliminarPDF':
                loaderClose();
                if (!banderaSolicitud) {
                    cargarPantallaListarCompra();
                }
                break;
            case 'obtenerNumeroNotaCredito':
                onResponseObtenerNumeroNotaCredito(response.data);
                loaderClose();
                break;

            // pagos
            case 'obtenerDocumentoTipoDatoPago':
                onResponseObtenerDocumentoTipoDatoPago(response.data);
                break;
            case 'guardarDocumentoPago':
                $('#modalNuevoDocumentoPagoConDocumento').modal('hide');
                $('.modal-backdrop').hide();
                onResponseEnviar(response.data);
                break;
            case 'guardarDocumentoAtencionSolicitud':
                $("modalAsignarAtencion").modal('hide');
                $('.modal-backdrop').hide();
                onResponseEnviar(response.data);
            case 'obtenerProductos':
                onResponseObtenerProductos(response.data, response[PARAM_TAG]);
                loaderClose();
                break;
            case 'obtenerStockParaProductosDeCopia':
                onResponseObtenerStockParaProductosDeCopia(response.data);
                loaderClose();
                break;
            case 'obtenerDireccionOrganizador':
                onResponseObtenerDireccionOrganizador(response.data, response[PARAM_TAG]);
                loaderClose();
                break;
            case 'leerDocumentoAdjunto':
                if (!isEmpty(response.data)) {
                    contenidoArchivoJson = response.data;
                    visualizarInformacionDocumentoAdjunto();
                }
                loaderClose();
                break;
            case 'obtenerNumeroAutoXDocumentoTipo':
                $('#txt_' + campoNumeroId).val(response.data);
                loaderClose();
                break;
            case 'obtenerDetalleXAreaId':
                onResponseObtenerDetalle(response.data);
                break;
            case 'obtenerDetalleXGrupoProductoId':
                dataGrupoProducto = response.data;
                onResponseObtenerDetalle(response.data);
                break;
            case 'obtenerCuentaPersona':
                onResponseObtenerCuentaPersona(response.data);
                break;
            case 'obtenerDetalleBienRequerimiento':
                onResponseobtenerDetalleBienRequerimiento(response.data);
                break;
            case 'exportarPdfCotizacion':
                loaderClose();
                abrirDocumentoPDF2(response.data, 'vistas/com/movimiento/documentos/');
                break;
            case 'exportarExcelCotizacion':
                loaderClose();
                location.href = URL_BASE + "util/formatos/SolicitudCotizacion.xlsx";
                break;
        }
    } else {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obtenerConfiguracionesIniciales':
                loaderClose();
                cargarPantallaListarCompra();
                break;
            case 'obtenerUnidadMedida':
                break;
            case 'enviar':
                cerrarModalAnticipo();
                loaderClose();
                habilitarBoton();
                onResponseEnviarError(response['message']);
                break;
            case 'enviarEImprimir':
                loaderClose();
                habilitarBoton();
                break;
            case 'obtenerDocumentoRelacion':
                loaderClose();
                break;
            case 'obtenerDocumentoRelacionVisualizar':
                loaderClose();
                break;
            case 'guardarDocumentoGenerado':
                habilitarBoton();
                loaderClose();
            case 'enviarCorreosMovimiento':
                loaderClose();
                break;
            case 'eliminarPDF':
                loaderClose();
                cargarPantallaListarCompra();
                break;
            case 'leerDocumentoAdjunto':
                $("#nombreArchivo").html('');
                $('#idPopover').attr("data-content", '');
                $('#dataArchivo').attr('value', '');
                $('#archivoAdjunto').val('');
                loaderClose();
                break;
            case 'guardarDocumentoPago':
                loaderClose();
                habilitarBoton();
                onResponseEnviarError(response['message']);
                break;
            case 'obtenerCuentaPersona':
                loaderClose();
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

function onResponseObtenerPersonaDireccion(data) {
    if (personaDireccionId !== 0) {
        onResponseObtenerDataCbo("_" + personaDireccionId, "id", "direccion", data);
    }
    if (textoDireccionId !== 0) {
        onResponseObtenerPersonaDireccionTexto(data);
    }
}

function onResponseobtenerPersonaContacto(data) {
    if (personaContactoResponsableId !== 0) {
        onResponseObtenerDataCbo("_" + personaContactoResponsableId, "id", "persona_nombre_codigo", data);
    }
    if (personaContactoAtencionId !== 0) {
        onResponseObtenerDataCbo("_" + personaContactoAtencionId, "id", "persona_nombre_codigo", data);
    }
    if (personaContactoSupervisorId !== 0) {
        onResponseObtenerDataCbo("_" + personaContactoSupervisorId, "id", "persona_nombre_codigo", data);
    }
    if (textoDireccionId !== 0) {
        onResponseObtenerPersonaDireccionTexto(data);
    }
}

function onResponseObtenerDataCbo(cboId, itemId, itemDes, data) {

    document.getElementById('cbo' + cboId).innerHTML = "";

    select2.asignarValor('cbo' + cboId, "");
    //$('#cbo' + cboId).append('<option value=0>Seleccione la dirección</option>');
    if (!isEmpty(data)) {
        $.each(data, function (index, item) {
            $('#cbo' + cboId).append('<option value="' + item[itemId] + '">' + item[itemDes] + '</option>');
        });
        if (itemDes != "persona_nombre_codigo") {
            select2.asignarValor('cbo' + cboId, data[0]["id"]);
        }
    } else {
        select2.asignarValor('cbo' + cboId, 0);
    }
}

function onResponseObtenerStockActual(data) {
    var stock = parseFloat(data[0]['stock']);
    var cantidadMinima = parseFloat(data[0]['cantidad_minima']);
    var indice = parseInt(data[0]['indice']);
    var saldo = 0;

    var indexTemporal = -1;
    $.each(detalle, function (i, item) {
        if (parseInt(item.index) === parseInt(indice)) {
            indexTemporal = i;
            return false;
        }
    });
    if (indexTemporal != -1) {
        detalle[indexTemporal]["stockBien"] = stock;
    }

    var cantidad = 0;
    if (existeColumnaCodigo(12)) {
        cantidad = parseFloat($('#txtCantidad_' + indice).val());
    }

    if (detalle[indexTemporal]["bienTipoId"] != -1 && indexTemporal != -1) {
        if (movimientoTipoIndicador == 1 || dataCofiguracionInicial.movimientoTipo[0].interfaz == 1) {
            saldo = stock + cantidad;
        } else if (movimientoTipoIndicador == 2) {
            saldo = stock - cantidad;
        } else {
            saldo = stock;
        }
    } else {
        saldo = 0;
    }

    var bienId = null;
    if (existeColumnaCodigo(11)) {
        bienId = select2.obtenerValor("cboBien_" + indice);
    }
    if (bienId == -1) {
        saldo = 0;
    }
    if (!isEmpty(bienId)) {
        if (existeColumnaCodigo(7)) {
            $('#txtStock_' + indice).html(devolverDosDecimales(saldo));
        }
        if (existeColumnaCodigo(8)) {
            $('#txtStockSugerido_' + indice).html(devolverDosDecimales(cantidadMinima));
        }
    }
}

function hallarStockSaldo(indice) {
    //    var indLista = indiceLista.indexOf(parseInt(indice));
    var indexTemporal = -1;
    var bandera = false;
    $.each(detalle, function (i, item) {
        if (parseInt(item.index) === parseInt(indice)) {
            indexTemporal = i;
            return false;
        }
    });

    if (indexTemporal != -1 && !isEmpty(detalle[indexTemporal]["stockBien"])) {
        var stock = detalle[indexTemporal]["stockBien"];
        var bienTipoId = detalle[indexTemporal]["bienTipoId"];
        var cantidad = 0;
        if (existeColumnaCodigo(12)) {
            cantidad = parseFloat($('#txtCantidad_' + indice).val());
        }
        var saldo = 0;

        //breakFunction();
        if (isEmpty(stock))
            stock = 0;
        if (bienTipoId != -1) {
            if (movimientoTipoIndicador == 1 || dataCofiguracionInicial.movimientoTipo[0].interfaz == 1) {
                saldo = stock + cantidad;
                bandera = true;
            } else if (movimientoTipoIndicador == 2) {
                saldo = stock - cantidad;
                bandera = true;
            } else {
                saldo = stock;
                bandera = false;
            }
        } else {
            saldo = 0;
        }

        var bienId = null;
        if (existeColumnaCodigo(11)) {
            bienId = select2.obtenerValor("cboBien_" + indice);
        }
        if (bienId == -1) {
            saldo = 0;
        }
        if (!isEmpty(bienId)) {
            if (existeColumnaCodigo(7) && bandera) {
                $('#txtStock_' + indice).html(devolverDosDecimales(saldo));
            }
        }
    }
}

function obtenerDocumentoTipoDato(documentoTipoId) {
    loaderShow();
    ax.setAccion("obtenerDocumentoTipoDato");
    ax.addParamTmp("documentoTipoId", documentoTipoId);
    ax.consumir();
}

function obtenerUnidadMedida(bienId, indice) {

    var unidadMedidaId = 0;
    if (banderaCopiaDocumento === 1) {
        unidadMedidaId = detalle[indice].unidadMedidaId;
    }

    if (existeColumnaCodigo(13)) {
        $("#cboUnidadMedida_" + indice).empty();
        select2.readonly("cboUnidadMedida_" + indice, true);
    }
    ax.setAccion("obtenerUnidadMedida");
    ax.addParamTmp("bienId", bienId);
    ax.addParamTmp("unidadMedidaId", unidadMedidaId);
    if (existeColumnaCodigo(4)) {
        ax.addParamTmp("precioTipoId", select2.obtenerValor("cboPrecioTipo_" + indice));
    } else {
        ax.addParamTmp("precioTipoId", null);
    }
    ax.addParamTmp("monedaId", select2.obtenerValor("cboMoneda"));
    ax.addParamTmp("fechaEmision", $('#datepicker_' + fechaEmisionId).val());
    //    ax.addParamTmp("incluyeIGV", incluyeIGV);
    ax.setTag(indice);
    ax.consumir();
}
function onResponseObtenerUnidadesMedida(data, index) {
    //INDEX: SI LA DATA VIENE DE LA COPIA
    if (isEmpty(data.indice)) {
        data.indice = index;
    }

    if (!isEmpty(data) && !isEmpty(data.unidad_medida) && existeColumnaCodigo(13)) {
        select2.cargar("cboUnidadMedida_" + data.indice, data.unidad_medida, "id", "simbolo");
        select2.asignarValor("cboUnidadMedida_" + data.indice, data.unidad_medida[0].id);
        select2.readonly("cboUnidadMedida_" + data.indice, false);

        if (banderaCopiaDocumento === 0) {
            setearUnidadMedidaDescripcion(data.indice);
        }
    }


    var operador = obtenerOperador();

    if (existeColumnaCodigo(1)) {
        $("#txtPrecioCompra_" + data.indice).html(devolverDosDecimales(data.precioCompra * operador));
    } else {
        varPrecioCompra = devolverDosDecimales(data.precioCompra * operador);
    }
    //    obtenerStockActual(data.indice);
    if (banderaCopiaDocumento === 1) {
        obtenerUtilidades(data.indice);
        obtenerUtilidadesGenerales();
        if (existeColumnaCodigo(13)) {
            select2.asignarValor("cboUnidadMedida_" + data.indice, detalle[data.indice].unidadMedidaId);
            if (unidadMedidaTxt === 1) {
                setearUnidadMedidaDescripcion(data.indice);
            }
        }

        //        obtenerStockActual(data.indice);
    } else if (banderaCopiaDocumento === 0) {
        obtenerStockActual(data.indice);
        if (existeColumnaCodigo(5)) {
            document.getElementById("txtPrecio_" + data.indice).value = devolverDosDecimales(data.precio * operador);
        }
        hallarSubTotalDetalle(data.indice);
    }

    if (existeColumnaCodigo(13)) {
        $("#cboUnidadMedida_" + data.indice).select2({ width: anchoUnidadMedidaTD + 'px' });
    }
}

function obtenerOperador() {
    var operadorIGV = 1.18;
    if (!isEmpty(importes.subTotalId)) {
        if (!document.getElementById('chkIncluyeIGV').checked) {
            operadorIGV = 1;
        }
    } else if (opcionIGV == 0) {
        operadorIGV = 1;
    }

    return operadorIGV;
}

function onResponseObtenerPreciosEquivalentes(data) {
    if (!isEmpty(data)) {
        var operador = obtenerOperador();

        if (existeColumnaCodigo(1)) {
            $("#txtPrecioCompra_" + data.indice).html(devolverDosDecimales(data.precioCompra * operador));
        } else {
            varPrecioCompra = devolverDosDecimales(data.precioCompra * operador);
        }
        if (existeColumnaCodigo(5)) {
            document.getElementById("txtPrecio_" + data.indice).value = devolverDosDecimales(data.precio * operador);
        }
        hallarSubTotalDetalle(data.indice);
    }
}

function onResponseObtenerBienPrecio(data) {
    if (!isEmpty(data)) {
        var operador = obtenerOperador();

        if (existeColumnaCodigo(5)) {
            document.getElementById("txtPrecio_" + data.indice).value = devolverDosDecimales(data[0].precio * operador);
        }
        hallarSubTotalDetalle(data.indice);
    }
}

function obtenerPrecioUnitario(indice) {
    loaderShow();

    var bienId = select2.obtenerValor("cboBien_" + indice);
    var unidadMedidaId = select2.obtenerValor("cboUnidadMedida_" + indice);

    ax.setAccion("obtenerPrecioUnitario");
    ax.addParamTmp("bienId", bienId);
    ax.addParamTmp("unidadMedidaId", unidadMedidaId);
    ax.setTag(indice);
    ax.consumir();
}

function devolverDosDecimales(num) {
    //    return Math.round(num * 100) / 100;
    //    breakFunction();
    return redondearNumero(num).toFixed(2);
    //      return redondearNumero(num);
}

function onResponseObtenerPrecioUnitario(data) {
    if (!isEmpty(data)) {
        if (existeColumnaCodigo(5)) {
            document.getElementById("txtPrecio_" + data.indice).value = devolverDosDecimales(data.precio);
        }
        hallarSubTotalDetalle(data.indice);
    }
}

var organizadorIdDefectoTM;
var movimientoTipoIndicador;
var dataContOperacionTipo = [];
var dataDocumentoTipo;
var inicialAlturaDetalle;
var documentoTipoTipo;
var monedaSimbolo;

var opcionIGV = null;
//var valorOpcionIGV;
var monedaBase = null;
var monedaBaseId = 0;
var cboDetraccionId = null;
var montoTotalDetraido = 0;
var montoTotalRetencion = 0;
var doc_TipoId = null;
var tipoRequerimientoTemp = null;
var tipoRequerimientoGlobal = null;
var tipoRequerimientoGlobalText = null;
var modalReserva = 0;
function onResponseObtenerConfiguracionesIniciales(data) {
    if (!isEmpty(data.documento_tipo)) {
        dataCofiguracionInicial = data;

        $("#cboDocumentoTipo").select2({
            width: "100%"
        }).on("change", function (e) {

            importes.totalId = null;
            importes.subTotalId = null;
            importes.igvId = null;
            importes.calculoId = null;
            importes.otrosId = null;
            importes.exoneracionId = null;

            percepcionId = 0;
            cboDetraccionId = null;
            montoTotalDetraido = 0;
            montoTotalRetencion = 0;
            nroFilasInicial = parseInt(dataDocumentoTipo[document.getElementById('cboDocumentoTipo').options.selectedIndex]['cantidad_detalle']);
            documentoTipoTipo = parseInt(dataDocumentoTipo[document.getElementById('cboDocumentoTipo').options.selectedIndex]['tipo']);
            opcionIGV = parseInt(dataDocumentoTipo[document.getElementById('cboDocumentoTipo').options.selectedIndex]['opcion_igv']);


            $('#txtComentario').val(dataDocumentoTipo[document.getElementById('cboDocumentoTipo').options.selectedIndex].comentario_defecto);

            $("#contenedorChkIncluyeIGV").hide();
            $("#contenedorTotalDiv").hide();
            $("#contenedorSubTotalDiv").hide();
            $("#contenedorPercepcionDiv").hide();
            $("#contenedorIgvDiv").hide();
            $("#contenedorCambioPersonalizado").hide();

            $("#contenedorFleteDiv").hide();
            $("#contenedorSeguroDiv").hide();

            $("#contenedorExoneracionDiv").hide();
            $("#contenedorOtrosDiv").hide();
            $("#contenedorIcbpDiv").hide();

            //serie y numero
            $("#contenedorSerieDiv").hide();
            $("#contenedorNumeroDiv").hide();

            //tipo pago
            $("#divContenedorTipoPago").hide();

            //Permitir productos duplicados
            $("#contenedorSwitchProductoDuplicado").hide();

            //Permitir tottus
            $("#contenedorSwitchCotizacionTottus").hide();
            obtenerDocumentoTipoDato(e.val);

            if (!isEmpty(dataContOperacionTipo) && documentoTipoTipo != 1) {
                var cbo_documento_tipo_id = parseInt(dataDocumentoTipo[document.getElementById('cboDocumentoTipo').options.selectedIndex]['id']);
                var dataContOperacionTipoFiltrado = dataContOperacionTipo.filter(function (obj) {
                    return obj.documento_tipo_id == cbo_documento_tipo_id;
                });
                $('#tabsDistribucionMostrar').show();
                $('#dgDetalleDistribucion').empty();
                $('#tabDetalle').click();
                select2.cargar("cboOperacionTipo", dataContOperacionTipoFiltrado, "id", "descripcion");
                if (dataContOperacionTipoFiltrado.length === 1) {
                    select2.asignarValor("cboOperacionTipo", dataContOperacionTipoFiltrado[0].id);
                    llenarCabeceraDistribucion();
                    agregarFilaDistribucion(1);
                    select2.readonly("cboOperacionTipo", true);
                } else {
                    select2.asignarValor("cboOperacionTipo", null);
                    select2.readonly("cboOperacionTipo", false);
                }
            }
            doc_TipoId = e.val;
        });

        select2.cargar("cboMoneda", data.moneda, "id", ["descripcion", "simbolo"]);
        select2.cargar("cboPeriodo", data.periodo, "id", ["anio", "mes"]);
        select2.asignarValorQuitarBuscador('cboPeriodo', null);



        $.each(data.moneda, function (i, itemMoneda) {
            if (itemMoneda.base == 1) {
                monedaBase = itemMoneda.id;
                monedaSimbolo = itemMoneda.simbolo;
                monedaBaseId = itemMoneda.id;
            }
        });


        if (isEmpty(monedaBase)) {
            monedaBase = data.moneda[0].id;
            monedaSimbolo = data.moneda[0].simbolo;
        }

        select2.asignarValorQuitarBuscador("cboMoneda", monedaBase);
        $("#cboMoneda").select2({
            width: "100%"
        }).on("change", function (e) {
            visualizarCambioPersonalizado(e.val);

            if (isEmpty(detalle)) {
                modificarSimbolosMoneda(e.val, data.moneda[document.getElementById('cboMoneda').options.selectedIndex]);
            }
            modificarPreciosMoneda(e.val, data.moneda[document.getElementById('cboMoneda').options.selectedIndex])
        });
        //cboIgv
        $("#cboIgv")
            .select2({
                width: "100%",
            })
            .on("change", function (e) {
                asignarImporteDocumento();
            });
        //moneda por defecto de documento tipo
        var monedaDefectoId = data.documento_tipo[0].moneda_id;
        if (!isEmpty(monedaDefectoId)) {
            $.each(data.moneda, function (i, itemMoneda) {
                if (itemMoneda.id == monedaDefectoId) {
                    monedaBase = monedaDefectoId;
                    monedaSimbolo = itemMoneda.simbolo;

                    select2.asignarValorQuitarBuscador("cboMoneda", monedaDefectoId);
                }
            });
        }

        dataDocumentoTipo = data.documento_tipo;
        documentoTipoTipo = dataDocumentoTipo[0]['tipo'];
        opcionIGV = dataDocumentoTipo[0]['opcion_igv'];


        select2.cargar("cboDocumentoTipo", data.documento_tipo, "id", "descripcion");

        if (isEmpty(data.movimientoTipo[0].documento_tipo_defecto_id)) {
            select2.asignarValor("cboDocumentoTipo", data.documento_tipo[0].id);
            doc_TipoId = data.documento_tipo[0].id;
        } else {
            select2.asignarValor("cboDocumentoTipo", data.movimientoTipo[0].documento_tipo_defecto_id);
            doc_TipoId = data.movimientoTipo[0].documento_tipo_defecto_id;
        }

        if (data.documento_tipo.length === 1) {
            select2.readonly("cboDocumentoTipo", true);
        }
        onResponseObtenerDocumentoTipoDato(data.documento_tipo_conf);

        if (select2.obtenerValor("cboDocumentoTipo") != "9") {
            $("#contenedorIgvPorcentajeDiv").hide();
        } else {
            $("#contenedorIgvPorcentajeDiv").show();
        }
        nroFilasReducida = 5;
        inicialAlturaDetalle = $("#contenedorDetalle").height();
        $("#contenedorDetalle").css("height", $("#contenedorDetalle").height() + 38 * nroFilasReducida);
        organizadorIdDefectoTM = data.movimientoTipo[0].organizador_defecto;

        //simbolo moneda
        //        monedaSimbolo=dataCofiguracionInicial.movimientoTipo[0].moneda_simbolo
        nroFilasInicial = parseInt(dataDocumentoTipo[0]['cantidad_detalle']);

        if (nroFilasInicial > 50) {
            $('#divTodasFilas').hide();
        }

        movimientoTipoIndicador = data.movimientoTipo[0].indicador;
        //        if(documentoTipoTipo==1){
        //            $("#contenedorUtilidadesTotales").show();
        //        }
        if (isEmpty(data.organizador)) {
            muestraOrganizador = false;
        }


        $('#txtComentario').val(data.documento_tipo[0].comentario_defecto);

        llenarMonedaSimboloTotales();
        llenarCabeceraDetalle();
        llenarTablaDetalle(data);

        if (doc_TipoId == REQUERIMIENTO_AREA || doc_TipoId == GENERAR_COTIZACION) {
            $("#ver_filas").hide();
        }

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

        //llenar organizador en cabecera
        llenarComboOrganizadorCabecera(data.organizador);

        visualizarCambioPersonalizado(monedaBase);

        var banderaMostrarMoneda = false;
        $.each(dataCofiguracionInicial.movimientoTipoColumna, function (index, objeto) {
            if (objeto.codigo == 5) {
                banderaMostrarMoneda = true;

            }
        });

        if (banderaMostrarMoneda == false) {
            $("#contenedorMoneda").hide();
        }

        obtenerDireccionOrganizador(1);
        onChangePeriodo();

        if (!isEmpty(dataCofiguracionInicial.contOperacionTipo) && documentoTipoTipo != 1) {
            dataContOperacionTipo = dataCofiguracionInicial.contOperacionTipo;
            $("#cboOperacionTipo").select2({
                width: "100%"
            }).on("change", function (e) {
                llenarCabeceraDistribucion();
                $('#dgDetalleDistribucion').empty();
                $('#tabDetalle').click();
                agregarFilaDistribucion(1);
            });

            var cbo_documento_tipo_id = parseInt(dataDocumentoTipo[document.getElementById('cboDocumentoTipo').options.selectedIndex]['id']);

            var dataContOperacionTipoFiltrado = dataContOperacionTipo.filter(function (obj) {
                return obj.documento_tipo_id == cbo_documento_tipo_id;
            });
            select2.cargar("cboOperacionTipo", dataContOperacionTipoFiltrado, "id", "descripcion");
            if (dataContOperacionTipoFiltrado.length === 1) {
                select2.asignarValor("cboOperacionTipo", dataContOperacionTipoFiltrado[0].id);
                select2.readonly("cboOperacionTipo", true);

                llenarCabeceraDistribucion();
                $("#tabsDistribucionMostrar").show();
                $('#tabDetalle').click();
                agregarFilaDistribucion(1);
            } else {
                select2.asignarValor("cboOperacionTipo", null);
                select2.readonly("cboOperacionTipo", false);
            }

        } else {
            $('#tabDetalle').click();
            $("#div_contenido_tab").removeClass("tab-content");
            $("#tabsDistribucionMostrar").hide();
            $("#contenedorCboOperacionTipo").hide();
        }
    }

    //280 = Solicitud requerimiento, oculta o edita descripcion de formulario
    if (doc_TipoId == SOLICITUD_REQUERIMIENTO) {
        $("#cargarBuscadorDocumentoACopiar").hide();
        var dtdTipoClase = obtenerDocumentoTipoDatoXTipoXCodigo(4, "01");
        var dtdTipoTipo = obtenerDocumentoTipoDatoXTipoXCodigo(4, "02");
        var dtdTipoOtros = obtenerDocumentoTipoDatoIdXTipo(2);
        var dtdTipoTipoRequerimiento = obtenerDocumentoTipoDatoIdXTipo(42);
        var dtdTipoUrgencia = obtenerDocumentoTipoDatoXTipoXCodigo(4, "04");
        var dtdTipoCuenta = obtenerDocumentoTipoDatoIdXTipo(52);


        if (!isEmpty(dtdTipoClase)) {
            $("#id_" + dtdTipoClase.id + " label").html("Clase *");
        }
        $("#id_" + dtdTipoOtros).hide();

        if (!isEmpty(dtdTipoClase)) {
            $("#cboTipoRequerimiento_" + dtdTipoTipoRequerimiento).select2({
                width: "100%"
            }).on("change", function (e) {
                var dtdTipoTipoRequerimiento = obtenerDocumentoTipoDatoIdXTipo(42);
                if (doc_TipoId == SOLICITUD_REQUERIMIENTO) {
                    tipoRequerimientoGlobalText = select2.obtenerText("cboTipoRequerimiento_" + dtdTipoTipoRequerimiento);
                    if (e.val == dtdTipoRequerimientoListaServicio) {
                        loaderShow();
                        detalle = [];
                        indiceLista = [];
                        banderaCopiaDocumento = 0;
                        indexDetalle = 0;
                        nroFilasInicial = parseInt(dataDocumentoTipo[0]['cantidad_detalle']);
                        limpiarDetalle();
                        loaderClose();

                        $("#id_" + dtdTipoTipo.id).show();
                        $("#id_" + dtdTipoTipo.id + " label").html("Tipo *");
                        $("#id_" + dtdTipoClase.id).hide();
                        select2.asignarValor("cbo_" + dtdTipoClase.id, "");
                        $("#id_" + dtdTipoUrgencia.id).hide();

                        var dtdTipoCuentaText = select2.obtenerText("cbo_" + dtdTipoCuenta);
                        let arrayCuenta = ["DDH", "EQUIPOS", "PROYECTOS"];
                        if (arrayCuenta.includes(dtdTipoCuentaText)) {
                            var dtdTipoTipoRequerimiento = obtenerDocumentoTipoDatoIdXTipo(42);
                            if (select2.obtenerValor("cboTipoRequerimiento_" + dtdTipoTipoRequerimiento) != 455) {
                                select2.asignarValor("cbo_" + dtdTipoUrgencia.id, 472);
                            } else {
                                select2.asignarValor("cbo_" + dtdTipoUrgencia.id, 473);
                            }
                        }
                        $("#switchProductoDuplicado").btnSwitch("setValue", true);
                        var dtdTipoProveedor = obtenerDocumentoTipoDatoIdXTipo(23);
                        $("#id_" + dtdTipoProveedor).hide();
                        var dtdTipoEntregaDestino = obtenerDocumentoTipoDatoIdXTipo(45);
                        $("#id_" + dtdTipoEntregaDestino).hide();                           
                    } else if(e.val == dtdTipoRequerimientoListaCompra){
                        loaderShow();
                        detalle = [];
                        indiceLista = [];
                        banderaCopiaDocumento = 0;
                        indexDetalle = 0;
                        nroFilasInicial = parseInt(dataDocumentoTipo[0]['cantidad_detalle']);
                        limpiarDetalle();
                        loaderClose();

                        $("#id_" + dtdTipoClase.id).show();
                        $("#id_" + dtdTipoClase.id + " label").html("Clase *");
                        $("#id_" + dtdTipoTipo.id).hide();
                        select2.asignarValor("cbo_" + dtdTipoTipo.id, "");
                        $("#id_" + dtdTipoOtros).hide();
                        $("#id_" + dtdTipoUrgencia.id).show();

                        var dtdTipoCuentaText = select2.obtenerText("cbo_" + dtdTipoCuenta);
                        let arrayCuenta = ["DDH", "EQUIPOS", "PROYECTOS"];
                        if (arrayCuenta.includes(dtdTipoCuentaText)) {
                            var dtdTipoTipoRequerimiento = obtenerDocumentoTipoDatoIdXTipo(42);
                            if (select2.obtenerValor("cboTipoRequerimiento_" + dtdTipoTipoRequerimiento) != 455) {
                                select2.asignarValor("cbo_" + dtdTipoUrgencia.id, 472);
                            } else {
                                select2.asignarValor("cbo_" + dtdTipoUrgencia.id, 473);
                            }
                        }
                        var dtdTipoProveedor = obtenerDocumentoTipoDatoIdXTipo(23);
                        $("#id_" + dtdTipoProveedor).hide();
                        var dtdTipoEntregaDestino = obtenerDocumentoTipoDatoIdXTipo(45);
                        $("#id_" + dtdTipoEntregaDestino).hide();
                    } else if(e.val == dtdTipoRequerimientoListaConsignacion){
                        loaderShow();
                        detalle = [];
                        indiceLista = [];
                        banderaCopiaDocumento = 0;
                        indexDetalle = 0;
                        nroFilasInicial = parseInt(dataDocumentoTipo[0]['cantidad_detalle']);
                        limpiarDetalle();
                        loaderClose();     

                        var dtdTipoProveedor = obtenerDocumentoTipoDatoIdXTipo(23);
                        $("#id_" + dtdTipoProveedor).show();
                        $("#id_" + dtdTipoProveedor + " label").html("Proveedor *");
                        var dtdTipoEntregaDestino = obtenerDocumentoTipoDatoIdXTipo(45);
                        $("#id_" + dtdTipoEntregaDestino).show();
                        $("#id_" + dtdTipoEntregaDestino + " label").html("Entrega en destino *");

                        $("#id_" + dtdTipoClase.id).show();
                        $("#id_" + dtdTipoClase.id + " label").html("Clase *");
                        $("#id_" + dtdTipoTipo.id).hide();
                        select2.asignarValor("cbo_" + dtdTipoTipo.id, "");
                        $("#id_" + dtdTipoOtros).hide();
                        $("#id_" + dtdTipoUrgencia.id).show();

                        var dtdTipoCuentaText = select2.obtenerText("cbo_" + dtdTipoCuenta);
                        let arrayCuenta = ["DDH", "EQUIPOS", "PROYECTOS"];
                        if (arrayCuenta.includes(dtdTipoCuentaText)) {
                            var dtdTipoTipoRequerimiento = obtenerDocumentoTipoDatoIdXTipo(42);
                            if (select2.obtenerValor("cboTipoRequerimiento_" + dtdTipoTipoRequerimiento) != 455) {
                                select2.asignarValor("cbo_" + dtdTipoUrgencia.id, 472);
                            } else {
                                select2.asignarValor("cbo_" + dtdTipoUrgencia.id, 473);
                            }
                        }
                    }
                }
            });
        }

        if (!isEmpty(dtdTipoTipo)) {
            $("#cbo_" + dtdTipoTipo.id).select2({
                width: "100%"
            }).on("change", function (e) {
                if (doc_TipoId == SOLICITUD_REQUERIMIENTO) {
                    if (e.val == 459) {
                        $("#id_" + dtdTipoOtros).show();
                    } else {
                        $("#id_" + dtdTipoOtros).hide();
                        $("#id_" + dtdTipoOtros).val("");
                    }
                }
            });
        }

        //Adjuntar imagen de producto para solicitud de requerimiento
        $("#fileInputAdjunto").change(function () {
            var fileType = this.files[0].type
            if (this.files && this.files[0]) {
                var validImageTypes = ["image/jpeg", "image/png", "image/gif", "image/bmp", "image/webp"];
                // Verificar si el archivo es una imagen válida
                if (validImageTypes.includes(this.files[0].type)) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        var img = new Image();
                        img.src = e.target.result;
                        img.onload = function () {
                            // Redimensionar la imagen si es necesario
                            var canvas = document.createElement("canvas");
                            var ctx = canvas.getContext("2d");
                            // Establecer un tamaño máximo (por ejemplo, 800px de ancho)
                            var maxWidth = 800;
                            var maxHeight = 800;
                            var width = img.width;
                            var height = img.height;

                            // Calcular las nuevas dimensiones manteniendo la relación de aspecto
                            if (width > height) {
                                if (width > maxWidth) {
                                    height = Math.round((height *= maxWidth / width));
                                    width = maxWidth;
                                }
                            } else {
                                if (height > maxHeight) {
                                    width = Math.round((width *= maxHeight / height));
                                    height = maxHeight;
                                }
                            }

                            // Establecer las dimensiones del canvas y dibujar la imagen redimensionada
                            canvas.width = width;
                            canvas.height = height;
                            ctx.drawImage(img, 0, 0, width, height);
                            // Convertir la imagen redimensionada a un formato de imagen (base64)
                            var resizedImage = canvas.toDataURL(fileType);
                            const fileSizeMB = base64ToSize(resizedImage);
                            if (fileSizeMB > 3) {
                                $("#error").html("EL archivo es mayor al permitido");
                                return false;
                            }
                            var indice = $('#indiceImagenAdjuntaBien').val();
                            var indexTemporal = -1;
                            $.each(detalle, function (i, item) {
                                if (parseInt(item.index) === parseInt(indice)) {
                                    indexTemporal = i;
                                    return false;
                                }
                            });

                            if (indexTemporal != -1) {
                                detalle[indexTemporal].imagenAdjuntaBien = resizedImage;
                            }
                            // Establecer la imagen redimensionada en el elemento de vista previa
                            $("#base64archivoAdjunto").val(e.target.result);
                            $("#error").hide();
                        };
                    };
                    reader.readAsDataURL(this.files[0]); // Leer el archivo seleccionado
                    $("#text_archivoAdjunto").html($("#fileInputAdjunto").val().slice(12));
                    $("#nombrearchivoAdjunto").val($("#fileInputAdjunto").val().slice(12));
                } else if (this.files[0].type == "application/pdf") {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        const fileSizeMB = base64ToSize(e.target.result);

                        $("#base64archivoAdjunto").val(e.target.result);
                    }
                    reader.readAsDataURL(this.files[0]);
                    $("#text_archivoAdjunto").html($("#fileInputAdjunto").val().slice(12));
                    $("#nombrearchivoAdjunto").val($("#fileInputAdjunto").val().slice(12));
                } else {
                    // Si no es una imagen válida, mostrar un mensaje de error y ocultar la vista previa
                    $("#error").show();
                }

            }
        });

        $("#cbo_" + dtdTipoCuenta).select2({
            width: "100%"
        }).on("change", function (e) {
            var dtdTipoCuentaText = select2.obtenerText("cbo_" + dtdTipoCuenta);

            let arrayCuenta = ["DDH", "EQUIPOS", "PROYECTOS"];
            if (arrayCuenta.includes(dtdTipoCuentaText)) {
                var dtdTipoTipoRequerimiento = obtenerDocumentoTipoDatoIdXTipo(42);
                if (select2.obtenerValor("cboTipoRequerimiento_" + dtdTipoTipoRequerimiento) != 455) {
                    select2.asignarValor("cbo_" + dtdTipoUrgencia.id, 472);
                } else {
                    select2.asignarValor("cbo_" + dtdTipoUrgencia.id, 473);
                }
                $("#cbo_" + dtdTipoUrgencia.id).prop('disabled', true);
            } else {
                $("#cbo_" + dtdTipoUrgencia.id).prop('disabled', false);
                var dtdTipoTipoRequerimiento = obtenerDocumentoTipoDatoIdXTipo(42);
                if (select2.obtenerValor("cboTipoRequerimiento_" + dtdTipoTipoRequerimiento) != 455) {
                    select2.asignarValor("cbo_" + dtdTipoUrgencia.id, 0);
                }
            }
            var dtdTipoArea = obtenerDocumentoTipoDatoIdXTipo(43);
            var id = select2.obtenerValor("cbo_" + dtdTipoArea);
            var dtdTipoUrgencia = obtenerDocumentoTipoDatoXTipoXCodigo(4, "04");
            if(id == 27){
                $("#cbo_" + dtdTipoUrgencia.id).prop("disabled", true);
                select2.asignarValor("cbo_" + dtdTipoUrgencia.id, 511);
            }
        });
    }

    //Se realiza la busqueda por área de solicitudes de requerimiento
    var dtdTipoArea = obtenerDocumentoTipoDatoIdXTipo(43);
    var dtdTipoTipoRequerimiento = obtenerDocumentoTipoDatoIdXTipo(42);
    if (!isEmpty(dtdTipoArea) && doc_TipoId == REQUERIMIENTO_AREA) {
        $("#cargarBuscadorDocumentoACopiar").hide();
        $("#cbo_" + dtdTipoArea).select2({
            width: "100%"
        }).on("change", function (e) {
            if (isEmpty(select2.obtenerValor("cboTipoRequerimiento_" + dtdTipoTipoRequerimiento))) {
                select2.asignarValor("cboTipoRequerimiento_" + dtdTipoTipoRequerimiento, 0)
                select2.asignarValor("cbo_" + dtdTipoArea, 0)
                mostrarAdvertencia("Seleccionar Tipo de Requerimiento");
                return false;
            }
            loaderShow();
            dataStockReservaOk = [];
            detalle = [];
            indiceLista = [];
            banderaCopiaDocumento = 0;
            indexDetalle = 0;
            ax.setAccion("obtenerDetalleXAreaId");
            ax.addParamTmp("empresaId", commonVars.empresa);
            ax.addParamTmp("areaId", select2.obtenerValor("cbo_" + dtdTipoArea));
            ax.addParamTmp("documentoTipoId", doc_TipoId);
            ax.addParamTmp("tipoRequerimiento", select2.obtenerValor("cboTipoRequerimiento_" + dtdTipoTipoRequerimiento));
            // ax.addParamTmp("urgencia", select2.obtenerText("cbo_" + dtdTipoUrgencia.id));
            ax.addParamTmp("urgencia", "No");
            ax.consumir();


            //Validamos nuevamente y ocultamos columnas
            if (doc_TipoId == REQUERIMIENTO_AREA) {
                if (select2.obtenerValor("cboTipoRequerimiento_" + dtdTipoTipoRequerimiento) == 484) {
                    $("#tb_cantidad_470").hide();
                    $("#tb_cantidad_aceptada_475").hide();
                    $("#tb_stock_473").hide();
                    $("#tb_unidad_medida_471").hide();
                    var dtdetalle_cantidad = document.querySelectorAll('.dtdetalle_cantidad');
                    dtdetalle_cantidad.forEach(function (columna) {
                        columna.classList.add('hidden');
                    });
                    var dtdetalle_cantidad_aceptada = document.querySelectorAll('.dtdetalle_cantidad_aceptada');
                    dtdetalle_cantidad_aceptada.forEach(function (columna) {
                        columna.classList.add('hidden');
                    });
                    var dtdetalle_stock = document.querySelectorAll('.dtdetalle_stock');
                    dtdetalle_stock.forEach(function (columna) {
                        columna.classList.add('hidden');
                    });
                    var dtdetalle_unidad_medida = document.querySelectorAll('.dtdetalle_unidad_medida');
                    dtdetalle_unidad_medida.forEach(function (columna) {
                        columna.classList.add('hidden');
                    });
                    $("#datatable").resize();
                } else {
                    $("#tb_cantidad_470").show();
                    $("#tb_cantidad_aceptada_475").show();
                    $("#tb_stock_473").show();
                    $("#tb_unidad_medida_471").show();
                    var dtdetalle_cantidad = document.querySelectorAll('.dtdetalle_cantidad');
                    dtdetalle_cantidad.forEach(function (columna) {
                        columna.classList.remove('hidden');
                    });
                    var dtdetalle_cantidad_aceptada = document.querySelectorAll('.dtdetalle_cantidad_aceptada');
                    dtdetalle_cantidad_aceptada.forEach(function (columna) {
                        columna.classList.remove('hidden');
                    });
                    var dtdetalle_stock = document.querySelectorAll('.dtdetalle_stock');
                    dtdetalle_stock.forEach(function (columna) {
                        columna.classList.remove('hidden');
                    });
                    var dtdetalle_unidad_medida = document.querySelectorAll('.dtdetalle_unidad_medida');
                    dtdetalle_unidad_medida.forEach(function (columna) {
                        columna.classList.remove('hidden');
                    });
                    $("#datatable").resize();
                }
            }
        });

        //Al cambiar el tipo de requerimiento se oculta columnas //revisar
        $("#cboTipoRequerimiento_" + dtdTipoTipoRequerimiento).select2({
            width: "100%"
        }).on("change", function (e) {
            var dtdTipoTipoRequerimiento = obtenerDocumentoTipoDatoIdXTipo(42);
            tipoRequerimientoGlobal = e.val;
            tipoRequerimientoGlobalText = select2.obtenerText("cboTipoRequerimiento_" + dtdTipoTipoRequerimiento);
            if (e.val == 488) {
                $("#tb_cantidad_470").hide();
                $("#tb_cantidad_aceptada_475").hide();
                $("#tb_stock_473").hide();
                $("#tb_unidad_medida_471").hide();

                loaderShow();
                detalle = [];
                indiceLista = [];
                banderaCopiaDocumento = 0;
                indexDetalle = 0;
                nroFilasInicial = parseInt(dataDocumentoTipo[0]['cantidad_detalle']);
                limpiarDetalle();
                loaderClose();

                var dtdetalle_cantidad = document.querySelectorAll('.dtdetalle_cantidad');
                dtdetalle_cantidad.forEach(function (columna) {
                    columna.classList.add('hidden');
                });
                var dtdetalle_cantidad_aceptada = document.querySelectorAll('.dtdetalle_cantidad_aceptada');
                dtdetalle_cantidad_aceptada.forEach(function (columna) {
                    columna.classList.add('hidden');
                });
                var dtdetalle_stock = document.querySelectorAll('.dtdetalle_stock');
                dtdetalle_stock.forEach(function (columna) {
                    columna.classList.add('hidden');
                });
                var dtdetalle_unidad_medida = document.querySelectorAll('.dtdetalle_unidad_medida');
                dtdetalle_unidad_medida.forEach(function (columna) {
                    columna.classList.add('hidden');
                });

                $("#datatable").resize();
            } else {
                $("#tb_cantidad_470").show();
                $("#tb_cantidad_aceptada_475").show();
                $("#tb_stock_473").show();
                $("#tb_unidad_medida_471").show();

                loaderShow();
                detalle = [];
                indiceLista = [];
                banderaCopiaDocumento = 0;
                indexDetalle = 0;
                nroFilasInicial = parseInt(dataDocumentoTipo[0]['cantidad_detalle']);
                limpiarDetalle();
                loaderClose();

                var dtdetalle_cantidad = document.querySelectorAll('.dtdetalle_cantidad');
                dtdetalle_cantidad.forEach(function (columna) {
                    columna.classList.remove('hidden');
                });
                var dtdetalle_cantidad_aceptada = document.querySelectorAll('.dtdetalle_cantidad_aceptada');
                dtdetalle_cantidad_aceptada.forEach(function (columna) {
                    columna.classList.remove('hidden');
                });
                var dtdetalle_stock = document.querySelectorAll('.dtdetalle_stock');
                dtdetalle_stock.forEach(function (columna) {
                    columna.classList.remove('hidden');
                });
                var dtdetalle_unidad_medida = document.querySelectorAll('.dtdetalle_unidad_medida');
                dtdetalle_unidad_medida.forEach(function (columna) {
                    columna.classList.remove('hidden');
                });

                $("#datatable").resize();
            }
        });
    }


    var dtdTipoGrupo_producto = obtenerDocumentoTipoDatoIdXTipo(44);
    if (!isEmpty(dtdTipoGrupo_producto) && doc_TipoId == GENERAR_COTIZACION) {
        $("#cbo_" + dtdTipoGrupo_producto).select2({
            width: "100%"
        }).on("change", function (e) {
            // if(isEmpty(arrayProveedor)){
            //     mostrarAdvertencia("Debe seleecionar primero un proveedor");
            //     select2.asignarValor("cbo_" + dtdTipoGrupo_producto, 0);
            //     return false;
            //     return;
            // }else{
            if (e.val != 0) {
                var dtdTipoTipoRequerimiento = obtenerDocumentoTipoDatoIdXTipo(42);
                if (isEmpty(select2.obtenerValor("cboTipoRequerimiento_" + dtdTipoTipoRequerimiento))) {
                    select2.asignarValor("cboTipoRequerimiento_" + dtdTipoTipoRequerimiento, 0)
                    select2.asignarValor("cbo_" + dtdTipoArea, 0)
                    mostrarAdvertencia("Seleccionar Tipo de Requerimiento");
                    return false;
                }
                loaderShow();
                dataStockReservaOk = [];
                detalle = [];
                indiceLista = [];
                banderaCopiaDocumento = 0;
                indexDetalle = 0;
                ax.setAccion("obtenerDetalleXGrupoProductoId");
                ax.addParamTmp("grupoProductoId", select2.obtenerValor("cbo_" + dtdTipoGrupo_producto));
                ax.addParamTmp("tipoRequerimiento", select2.obtenerValor("cboTipoRequerimiento_" + dtdTipoTipoRequerimiento));
                ax.addParamTmp("urgencia", "No");
                ax.consumir();
            }
            // }
        });
    }

    // 281 = Generar Cotización
    if (doc_TipoId == GENERAR_COTIZACION) {
        // Usamos delegación de eventos para manejar el evento 'change' de los inputs de archivos
        $(document).on('change', 'input[type="file"]', function () {
            var idx = $(this).attr('id').split('_')[1];  // Extraemos el índice del ID del input (ej. fileInput_0 -> idx = 0)
            // Mostrar el nombre del archivo en el elemento correspondiente
            var archivo = $(this).val().slice(12);
            var nombreReducido = archivo.length > 25 ? archivo.slice(0, 10) + "..." + archivo.slice(-10) : archivo;
            $("#text_archivo_" + idx).html(nombreReducido);

            if (this.files && this.files[0]) {
                var documento = {};
                var reader = new FileReader();

                reader.onload = function (e) {
                    // Cuando se haya cargado el archivo, se procesa
                    documento.data = e.target.result;  // Guardamos el contenido del archivo
                    documento.archivo = archivo;  // Nombre del archivo (sin la ruta completa)
                    documento.id = "t" + idx;  // Un ID único basado en el índice
                    documento.contenido_archivo = arrayProveedor[idx].proveedor_id;  // Identificador dinámico para cada proveedor
                    lstDocumentoArchivos[idx] = documento;  // Agregar documento al array
                };
                reader.readAsDataURL(this.files[0]);  // Iniciar la lectura del archivo
            }
        });

        $("#contenedorProveedor").show();
        llenarTablaDetalleProveedor(data);
        $('#datatableProveedor').DataTable({
            "scrollX": true,
            "paging": false,
            "info": false,
            "filter": false,
            "ordering": false,
            "autoWidth": true,
            "destroy": true
        });
        $("#exportarPdfCotizacion").show();
    }
    if (doc_TipoId == COTIZACION_SERVICIO) {
        var dtdTipoTiempo = obtenerDocumentoTipoDatoXTipoXCodigo(1, "01");
        var dtdTipoDiasPago = obtenerDocumentoTipoDatoXTipoXCodigo(1, "02");
        var dtdTipoTipoTiempoEntrega = obtenerDocumentoTipoDatoIdXTipo(4);
        var dtdTipoTipoCondicionPago = obtenerDocumentoTipoDatoIdXTipo(50);

        $("#cbo_" + dtdTipoTipoTiempoEntrega).select2({
            width: "100%"
        }).on("change", function (e) {
            if(e.val == 509){
                $("#txt_"+ dtdTipoTiempo.id).prop('disabled', false);
                $("#id_" + dtdTipoTiempo.id + " label").html("Tiempo *");
            }else{
                $("#txt_"+ dtdTipoTiempo.id).prop('disabled', true);
                $("#txt_"+ dtdTipoTiempo.id).val("");
                $("#id_" + dtdTipoTiempo.id + " label").html("Tiempo");
            }
        });
        $("#cbo_" + dtdTipoTipoCondicionPago).select2({
            width: "100%"
        }).on("change", function (e) {
            if(e.val == 500){
                $("#txt_"+ dtdTipoDiasPago.id).prop('disabled', false);
                $("#id_" + dtdTipoDiasPago.id + " label").html("Dias de pago *");
            }else{
                $("#txt_"+ dtdTipoDiasPago.id).prop('disabled', true);
                $("#txt_"+ dtdTipoDiasPago.id).val("");
                $("#id_" + dtdTipoDiasPago.id + " label").html("Dias de pago *");
            }
        });
    }
}

function imageIsLoaded(e) {
    $('#dataArchivoMulti').attr('value', e.target.result);
}

function habilitarComboTipoPago() {
    if (documentoTipoTipo == 1 || documentoTipoTipo == 4) {
        $("#divContenedorTipoPago").show();
    } else {
        $("#divContenedorTipoPago").hide();
    }
}

var organizadorIdAntes = null;
function llenarComboOrganizadorCabecera(data) {
    let dataDocumentoTipoSeleccionado = obtenerDocumentoTipoSeleccionado();
    if (muestraOrganizador && dataDocumentoTipoSeleccionado.id != '58') {
        $("#divContenedorOrganizador").show();

        $("#cboOrganizador").select2({
            width: "100%"
        }).on("change", function (e) {
            confirmarCambioOrganizador();
            //PARA OBTENER LA DIRECCION DE ORGANIZADOR
            obtenerDireccionOrganizador(1);
        });

        select2.cargar("cboOrganizador", data, "id", "descripcion");

        if (organizadorIdDefectoTM != 0)
            select2.asignarValor("cboOrganizador", organizadorIdDefectoTM);
        else
            select2.asignarValor("cboOrganizador", data[0].id);

        organizadorIdAntes = select2.obtenerValor("cboOrganizador");
    }
}

function obtenerDireccionOrganizador(origenDestino) {
    //origenDestino => ORIGEN: 1 DESTINO:2
    var organizadorId = null;
    var dtd = null;
    if (origenDestino == 1) {
        organizadorId = select2.obtenerValor('cboOrganizador');
        dtd = obtenerDocumentoTipoDatoXTipoXCodigo(2, 8);
    } else {
        organizadorId = select2.obtenerValor("cbo_" + obtenerDocumentoTipoDatoIdXTipo(17));
        dtd = obtenerDocumentoTipoDatoXTipoXCodigo(2, 9);
    }

    if (!isEmpty(dtd) && !isEmpty(organizadorId)) {
        loaderShow();
        ax.setAccion("obtenerDireccionOrganizador");
        ax.addParamTmp("organizadorId", organizadorId);
        ax.setTag(origenDestino);
        ax.consumir();
    }
}

function onResponseObtenerDireccionOrganizador(data, origenDestino) {
    var dtd = null;
    if (origenDestino == 1) {
        dtd = obtenerDocumentoTipoDatoXTipoXCodigo(2, 8);
    } else {
        dtd = obtenerDocumentoTipoDatoXTipoXCodigo(2, 9);
    }

    if (!isEmpty(dtd)) {
        $('#txt_' + dtd.id).val(data[0].direccion);
    }
}

function confirmarCambioOrganizador() {
    if (existeColumnaCodigo(7)) {
        if (!isEmpty(detalle)) {
            swal({
                title: "Confirmación de cambio de almacén",
                text: "¿Está seguro de cambiar el almacén, se va a actualizar el stock?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#33b86c",
                confirmButtonText: "Si, actualizar!",
                cancelButtonColor: '#d33',
                cancelButtonText: "No, cancelar!",
                closeOnConfirm: true,
                closeOnCancel: true
            }, function (isConfirm) {
                if (isConfirm) {
                    organizadorIdAntes = select2.obtenerValor("cboOrganizador");
                    actualizarStockDetalle();
                } else {
                    select2.asignarValor("cboOrganizador", organizadorIdAntes);
                }
            });
        } else {
            organizadorIdAntes = select2.obtenerValor("cboOrganizador");
        }
    }
}

function actualizarStockDetalle() {
    var varOrganizadorId = select2.obtenerValor("cboOrganizador");

    $.each(detalle, function (indice, item) {
        detalle[indice].organizadorId = varOrganizadorId;
        obtenerStockActual(item.index);
    });
}

function llenarMonedaSimboloTotales() {
    $('#contenedorTotalDiv').append("<median class='text-uppercase' id='simTotal'>" + monedaSimbolo + "</median>");
    $('#percepcionDescripcion').append("<median class='text-uppercase' id='simPercepcion'>" + monedaSimbolo + "</median>");
    $('#contenedorIgvDiv').append("<median class='text-uppercase' id='simIGV'>" + monedaSimbolo + "</median>");
    $('#contenedorSubTotalDiv').append("<median class='text-uppercase' id='simSubTotal'>" + monedaSimbolo + "</median>");
    $('#totalUtilidadDescripcion').append(" " + "<median class='text-uppercase' id='simTotalUtildiad'>" + monedaSimbolo + "</median>");
}

function llenarCabeceraDetalle() {
    var dataColumna = dataCofiguracionInicial.movimientoTipoColumna;

    $("#headDetalleCabecera").empty();
    var fila = "<tr>";

    //columnas dinamicas de acuerdo a documento_tipo_columna
    var anchoDinamicoTabla = 0;//;500;//941-> 1041px

    fila += "<th style='text-align:center; width: 20px;'>#</th>"; // # Item
    anchoDinamicoTabla += parseInt(40);

    if (!isEmpty(dataColumna)) {
        $.each(dataColumna, function (index, item) {
            switch (parseInt(item.codigo)) {
                case 1://Precio de compra
                    fila += "<th style='text-align:center; width: " + item.ancho + "px;vertical-align: middle;'>" + item.descripcion + " <div id='simPC' style='display: inline-table;'>" + monedaSimbolo + "</div></th>";
                    anchoDinamicoTabla += parseInt(item.ancho);
                    break;
                case 2:// Utilidad moneda
                    if (documentoTipoTipo == 1) {
                        fila += "<th style='text-align:center;width: " + item.ancho + "px;'>" + item.descripcion + "  <div id='simUD' style='display: inline-table;'>" + monedaSimbolo + "</div></th>";
                        anchoDinamicoTabla += parseInt(item.ancho);
                    }
                    break;
                case 3:// Utilidad porcentaje
                    if (documentoTipoTipo == 1) {
                        fila += "<th style='text-align:center;width: " + item.ancho + "px;vertical-align: middle;'>" + item.descripcion + "</th>";
                        anchoDinamicoTabla += parseInt(item.ancho);
                    }
                    break;
                case 4:// 87px TIPO PRECIO
                    fila += "<th style='text-align:center; width: " + item.ancho + "px;vertical-align: middle;'>" + item.descripcion + "</th>";
                    anchoDinamicoTabla += parseInt(item.ancho);
                    break;
                case 5:// 69px PRECIO UNITARIO
                    fila += "<th style='text-align:center; width: " + item.ancho + "px;vertical-align: middle;'>" + item.descripcion + "<div id='simPU' style='display: inline-table;'>" + monedaSimbolo + "</div></th> ";
                    anchoDinamicoTabla += parseInt(item.ancho);
                    break;
                case 6:// 47px SUB TOTAL
                    fila += "<th style='text-align:center; width: " + item.ancho + "px;vertical-align: middle;'>" + item.descripcion + "<div id='simST' style='display: inline-table;'>" + monedaSimbolo + "</div></th>";
                    anchoDinamicoTabla += parseInt(item.ancho);
                    break;
                case 7:// 40px STOCK
                    fila += "<th style='text-align:center; width: " + item.ancho + "px;vertical-align: middle;' id='tb_stock_" + item.id + "'>" + item.descripcion + "</th>";
                    anchoDinamicoTabla += parseInt(item.ancho);
                    break;
                case 8:// 86px STOCK MINIMO
                    if (dataCofiguracionInicial.movimientoTipo[0].interfaz == 1) {
                        fila += "<th style='text-align:center; width: " + item.ancho + "px;vertical-align: middle;'>" + item.descripcion + "</th>";
                        anchoDinamicoTabla += parseInt(item.ancho);

                        $('#divTodasFilas').hide();
                    }
                    break;
                case 9:// 59px PRIORIDAD
                    if (dataCofiguracionInicial.movimientoTipo[0].interfaz == 1) {
                        fila += "<th style='text-align:center; width: " + item.ancho + "px;vertical-align: middle;'>" + item.descripcion + "</th>";
                        anchoDinamicoTabla += parseInt(item.ancho);

                        $('#divTodasFilas').hide();
                    }
                    break;
                case 10:// 14px COLUMNA EN BLANCO
                    fila += "<th style='text-align:center; border:0; width: " + item.ancho + "px;vertical-align: middle;' bgcolor='#FFFFFF'></th>";
                    anchoDinamicoTabla += parseInt(item.ancho);
                    break;
                case 11:// 310 PRODUCTO
                    alturaBienTD = parseInt(item.ancho) + (doc_TipoId == GENERAR_COTIZACION ? 0 : 100);
                    fila += "<th style='text-align:center; width: " + alturaBienTD + "px;vertical-align: middle;'>" +
                        // "<a href='#' title='Nuevo producto' onclick='cargarBien()'>&nbsp;&nbsp;<i class='fa fa-plus-circle' style='color:#1ca8dd'></i></a>"+
                        "&nbsp;&nbsp;" + item.descripcion + "</th>";
                    anchoDinamicoTabla += parseInt(item.ancho);
                    break;
                case 12:// 47 CANTIDAD
                    fila += "<th style='text-align:center; width: " + item.ancho + "px;vertical-align: middle;' id='tb_cantidad_" + item.id + "'>" + item.descripcion + "</th>";
                    anchoDinamicoTabla += parseInt(item.ancho);
                    break;
                case 13:// 71 UNIDAD DE MEDIDA
                    fila += "<th style='text-align:center; width: " + item.ancho + "px;vertical-align: middle;' id='tb_unidad_medida_" + item.id + "'>" + item.descripcion + "</th>";
                    anchoDinamicoTabla += parseInt(item.ancho);
                    break;
                case 14:// 40 ACCIONES
                    fila += "<th style='text-align:center; width: " + item.ancho + "px;vertical-align: middle;'>" + item.descripcion + "</th>";
                    anchoDinamicoTabla += parseInt(item.ancho);
                    break;
                case 15:// 47 Organizador
                    if (muestraOrganizador && organizadorIdDefectoTM == 0) {
                        fila += "<th style='text-align:center; width: " + item.ancho + "px;vertical-align: middle;'>" + item.descripcion + "</th>";
                    }
                    anchoDinamicoTabla += parseInt(item.ancho);
                    break;
                case 16://descripcion de producto
                    fila += "<th style='text-align:center; width: " + item.ancho + "px;vertical-align: middle;'>" + item.descripcion + "</th>";
                    anchoDinamicoTabla += parseInt(item.ancho);
                    break;
                case 17://descripcion de unidad de medida
                    fila += "<th style='text-align:center; width: " + item.ancho + "px;vertical-align: middle;'>" + item.descripcion + "</th>";
                    anchoDinamicoTabla += parseInt(item.ancho);
                    break;
                case 18://fecha de vencimiento
                    fila += "<th style='text-align:center; width: " + item.ancho + "px;vertical-align: middle;'>" + item.descripcion + "</th>";
                    anchoDinamicoTabla += parseInt(item.ancho);
                    break;
                case 21://comentario
                    fila += "<th style='text-align:center; width: " + item.ancho + "px;vertical-align: middle;' class='claseMostrarColumnaComentario'>" + item.descripcion + "</th>";
                    anchoDinamicoTabla += parseInt(item.ancho);
                    break;

                case 23://Agencia
                    fila += "<th style='text-align:center; width: " + item.ancho + "px;vertical-align: middle;'>" + item.descripcion + "</th>";
                    anchoDinamicoTabla += parseInt(item.ancho);
                    break;
                case 24://Agrupador
                    fila += "<th class='hidden' id='tb_agrupador' style='text-align:center; width: " + item.ancho + "px;vertical-align: middle;'>" + item.descripcion + "</th>";
                    anchoDinamicoTabla += parseInt(item.ancho);
                    break;
                case 25://Ticket
                    fila += "<th style='text-align:center; width: " + item.ancho + "px;vertical-align: middle;'>" + item.descripcion + "</th>";
                    anchoDinamicoTabla += parseInt(item.ancho);
                    break;
                case 26://CeCo
                    fila += "<th style='text-align:center; width: " + item.ancho + "px;vertical-align: middle;'>" + item.descripcion + "</th>";
                    anchoDinamicoTabla += parseInt(item.ancho);
                    break;
                // case 27://Postor N° 1
                //     fila += "<th style='text-align:center; width: " + item.ancho + "px;' id='tb_postor_"+ item.id +"' "+ colspan +">" + item.descripcion +" &nbsp;<input type='checkbox' id='selectPostor1' class='grupocheckbox' onclick='hallarSubTotalPostorDetalle(1,1,2)'><br>$&nbsp;<input type='checkbox' id='selectPostor1Moneda' class='grupocheckboxMoneda' onclick='hallarSubTotalPostorDetalle(1,1,2)'></th>";
                //     anchoDinamicoTabla += parseInt(item.ancho);
                //     break;
                //  case 28://Postor N° 2
                //     fila += "<th style='text-align:center; width: " + item.ancho + "px;' id='tb_postor_"+ item.id +"' "+ colspan +">" + item.descripcion +" &nbsp;<input type='checkbox' id='selectPostor2' class='grupocheckbox' onclick='hallarSubTotalPostorDetalle(1,2,2)'><br>$&nbsp;<input type='checkbox' id='selectPostor2Moneda' class='grupocheckboxMoneda' onclick='hallarSubTotalPostorDetalle(1,2,2)'></th>";
                //     anchoDinamicoTabla += parseInt(item.ancho);
                //     break;
                // case 29://Postor N° 3
                //     fila += "<th style='text-align:center; width: " + item.ancho + "px;' id='tb_postor_"+ item.id +"' "+ colspan +">" + item.descripcion +" &nbsp;<input type='checkbox' id='selectPostor3' class='grupocheckbox' onclick='hallarSubTotalPostorDetalle(1,3,2)'><br>$&nbsp;<input type='checkbox' id='selectPostor3Moneda' class='grupocheckboxMoneda' onclick='hallarSubTotalPostorDetalle(1,3,2)'></th>";
                //     anchoDinamicoTabla += parseInt(item.ancho);
                //     break;
                case 33://Compras
                    fila += "<th style='text-align:center; width: " + item.ancho + "px;'>" + item.descripcion + "</th>";
                    anchoDinamicoTabla += parseInt(item.ancho);
                    break;
                case 34://Cantidad aceptada
                    fila += "<th style='text-align:center; width: " + item.ancho + "px;' id='tb_cantidad_aceptada_" + item.id + "'>" + item.descripcion + "</th>";
                    anchoDinamicoTabla += parseInt(item.ancho);
                    break;
            }
        });
    }

    fila = fila + "</tr>";

    if (documentoTipoTipo == 1 && existeColumnaCodigo(2) && existeColumnaCodigo(3)) {
        $("#contenedorUtilidadesTotales").show();
    }

    if (!isEmpty(dataColumna)) {
        if (anchoDinamicoTabla > 1195) {
            $("#datatable").width(anchoDinamicoTabla + 2 * dataColumna.length);
        }
    }
    $('#datatable thead').append(fila);
}

var muestraOrganizador = true;
var dataCofiguracionInicial;
var nroFilasInicial;
var nroFilasReducida;
var alturaBienTD;
var anchoUnidadMedidaTD;
var anchoTipoPrecioTD;
var anchoAgenciaTD;
var anchoCeCoTD;
var anchoCompraTD;
function llenarTablaDetalle(data) {
    var nroFilas = nroFilasReducida;
    //NUEVO

    var cuerpo = "";

    //LLENAR TABLA DETALLE
    for (var i = 0; i < nroFilas; i++) {
        //var i=0;
        cuerpo += llenarFilaDetalleTabla(i);
    }

    $('#datatable tbody').append(cuerpo);

    // if (existeColumnaCodigo(11)) {
    //     alturaBienTD = $("#tdBien_" + (nroFilas - 1)).width();
    // }
    if (existeColumnaCodigo(4)) {
        anchoTipoPrecioTD = $("#tdTipoPrecio_" + (nroFilas - 1)).width();
    }
    if (existeColumnaCodigo(13)) {
        anchoUnidadMedidaTD = $("#tdUnidadMedida_" + (nroFilas - 1)).width();
    }

    if (existeColumnaCodigo(23)) {
        anchoAgenciaTD = $("#tdAgencia_" + (nroFilas - 1)).width();
    }

    if (existeColumnaCodigo(26)) {
        anchoCeCoTD = $("#tdCeCo_" + (nroFilas - 1)).width();
    }

    if (existeColumnaCodigo(33)) {
        anchoCompraTD = $("#tdCompra_" + (nroFilas - 1)).width();
    }

    //LLENAR COMBOS
    for (var i = 0; i < nroFilas; i++) {
        cargarOrganizadorDetalleCombo(data.organizador, i);
        cargarUnidadMedidadDetalleCombo(i);
        cargarBienDetalleCombo(data.bien, i);
        cargarPrecioTipoDetalleCombo(data.precioTipo, i);
        cargarAgenciaDetalleCombo(data.dataAgencia, i);
        cargarCeCoDetalleCombo(data.centroCostoRequerimiento, i);
        var compras = [{ "id": 1, "descripcion": "Si" }, { "id": 2, "descripcion": "No" }];
        cargarComprasDetalleCombo(compras, i);
        inicializarFechaVencimiento(i);
    }
    if (existeColumnaCodigo(24)) {
        cargarAgrupadorCombo(data.dataAgrupador);
    }

}


var banderaVerTodasFilas = 0;
function verTodasFilas() {

    $("#contenedorDetalle").css("height", $("#contenedorDetalle").height() + 38 * (nroFilasInicial - nroFilasReducida));

    //NUEVO

    var cuerpo = "";

    //LLENAR TABLA DETALLE
    for (var i = nroFilasReducida; i < nroFilasInicial; i++) {
        //var i=0;
        cuerpo += llenarFilaDetalleTabla(i);
    }

    $('#datatable tbody').append(cuerpo);

    //LLENAR COMBOS
    for (var i = nroFilasReducida; i < nroFilasInicial; i++) {
        cargarOrganizadorDetalleCombo(dataCofiguracionInicial.organizador, i);
        cargarUnidadMedidadDetalleCombo(i);
        cargarBienDetalleCombo(dataCofiguracionInicial.bien, i);
        cargarPrecioTipoDetalleCombo(dataCofiguracionInicial.precioTipo, i);
        cargarCeCoDetalleCombo(dataCofiguracionInicial.centroCostoRequerimiento, i);
        var compras = [{ "id": 1, "descripcion": "Si" }, { "id": 2, "descripcion": "No" }];
        cargarComprasDetalleCombo(compras, i);
        inicializarFechaVencimiento(i);
    }

    nroFilasReducida = nroFilasInicial;
    $('#divTodasFilas').hide();
    if (dataCofiguracionInicial.documento_tipo[0].cantidad_detalle != 0 && !isEmpty(dataCofiguracionInicial.documento_tipo[0].cantidad_detalle)) {
        $('#divAgregarFila').hide();
    }

    banderaVerTodasFilas = 1;
    //    loaderClose();
}

function inicializarFechaVencimiento(indice) {
    $('#txtFechaVencimiento_' + indice).datepicker({
        isRTL: false,
        format: 'dd/mm/yyyy',
        autoclose: true,
        language: 'es'
    }).on('changeDate', function (ev) {
        actualizarTotalesGenerales(parseInt(indice));
    });
}

function obtenerStockActual(indice) {
    var bienId = select2.obtenerValor("cboBien_" + indice);
    if (!isEmpty(bienId)) {
        loaderShow();
        ax.setAccion("obtenerStockActual");
        var organizadorId = null;
        if (organizadorIdDefectoTM == 0) {
            if (!existeColumnaCodigo(15)) {
                if (muestraOrganizador) {
                    organizadorId = select2.obtenerValor('cboOrganizador');
                }
            } else {
                organizadorId = select2.obtenerValor("cboOrganizador_" + indice);
            }
        } else {
            organizadorId = organizadorIdDefectoTM;
        }

        //ALMACEN DE LLEGADA PARA TRANSFERENCIA INTERNA
        var organizadorDestinoId = null;
        if (dataCofiguracionInicial.movimientoTipo[0]["codigo"] == 20) { //Transferencia interna
            var dtdOrganizadorId = obtenerDocumentoTipoDatoIdXTipo(17);
            if (!isEmpty(dtdOrganizadorId)) {
                organizadorDestinoId = select2.obtenerValor('cbo_' + dtdOrganizadorId);
            }
        }

        ax.addParamTmp("organizadorId", organizadorId);
        ax.addParamTmp("unidadMedidaId", select2.obtenerValor("cboUnidadMedida_" + indice));
        ax.addParamTmp("bienId", select2.obtenerValor("cboBien_" + indice));
        ax.addParamTmp("organizadorDestinoId", organizadorDestinoId);
        ax.setTag(indice);
        ax.consumir();
    }
}

function obtenerPreciosEquivalentes(indice) {
    var bienId = select2.obtenerValor("cboBien_" + indice);
    if (!isEmpty(bienId)) {
        loaderShow();
        ax.setAccion("obtenerPreciosEquivalentes");
        ax.addParamTmp("unidadMedidaId", select2.obtenerValor("cboUnidadMedida_" + indice));
        ax.addParamTmp("bienId", select2.obtenerValor("cboBien_" + indice));
        if (existeColumnaCodigo(4)) {
            ax.addParamTmp("precioTipoId", select2.obtenerValor("cboPrecioTipo_" + indice));
        } else {
            ax.addParamTmp("precioTipoId", null);
        }
        //        ax.addParamTmp("monedaId", dataCofiguracionInicial.movimientoTipo[0].moneda_id);
        ax.addParamTmp("monedaId", select2.obtenerValor("cboMoneda"));
        ax.addParamTmp("fechaEmision", $('#datepicker_' + fechaEmisionId).val());
        ax.addParamTmp("indice", indice);
        ax.consumir();
    }
}

function obtenerBienPrecio(indice) {
    var bienId = select2.obtenerValor("cboBien_" + indice);
    if (!isEmpty(bienId)) {
        loaderShow();
        ax.setAccion("obtenerBienPrecio");
        ax.addParamTmp("unidadMedidaId", select2.obtenerValor("cboUnidadMedida_" + indice));
        ax.addParamTmp("bienId", bienId);
        if (existeColumnaCodigo(4)) {
            ax.addParamTmp("precioTipoId", select2.obtenerValor("cboPrecioTipo_" + indice));
        } else {
            ax.addParamTmp("precioTipoId", null);
        }
        ax.addParamTmp("monedaId", select2.obtenerValor("cboMoneda"));
        ax.setTag(indice);
        ax.consumir();
    }
}

function cargarOrganizadorDetalleCombo(data, i) {
    if (!isEmpty(data) && existeColumnaCodigo(15)) {
        $("#cboOrganizador_" + i).select2({
            width: "100%"
        }).on("change", function (e) {
            indiceBien = i;

            obtenerStockActual(indiceBien);
            hallarSubTotalDetalle(indiceBien);
        });
        select2.cargar("cboOrganizador_" + i, data, "id", "descripcion");
        if (organizadorIdDefectoTM != 0)
            select2.asignarValor("cboOrganizador_" + i, organizadorIdDefectoTM);
        else
            select2.asignarValor("cboOrganizador_" + i, data[0].id);

    } else {
        $("#contenedorOrganizador_" + i).hide();
        validacion.organizadorExistencia = false;
    }
}

var indiceBien;
var primeraFechaEmision;
var banderaPrimeraFE = true;
function cargarBienDetalleCombo(data, indice, valorInicial) {
    if (existeColumnaCodigo(16)) {
        $("#txtProductoDescripcion_" + indice).val("");
        $("#txtdescripcion_" + indice).val("");
    }
    if (existeColumnaCodigo(24)) {
        $("#badge_" + indice).html("");
        $("#obscboBien_" + indice).html("");
        $("#obsupcboBien_" + indice).addClass("hidden");
    }
    //loaderShow();

    if (!isEmpty(data)) {

        let selectProducto = $("#cboBien_" + indice);
        if (!selectProducto.length) {
            return;
        }
        //LLENADO COMBO PRODUCTOS
        if (isEmpty(valorInicial)) {
            valorInicial = { id: '', text: '' };
        } else {
            selectProducto.append('<option value="' + valorInicial.id + '">' + valorInicial.text + '</option>');
            selectProducto.val('"' + valorInicial.id + '"');//SE PUEDE COLOCAR AL ULTIMO
        }

        selectProducto.select2({
            placeholder: "Buscar producto",
            allowClear: true,
            //            minimumInputLength: 1,
            data: dataCofiguracionInicial.bien || [],
            width: alturaBienTD + "px",
            initSelection: function (element, callback) {
                var initialData = {
                    id: valorInicial.id,
                    text: valorInicial.text
                };
                callback(initialData);
            },
            ajax: {
                url: URL_BASE + "script/almacen/buscarProducto.php",
                dataType: "json",
                delay: 250,
                data: function (params) {
                    return {
                        q: params,
                        movimiento_tipo_id: dataCofiguracionInicial.movimientoTipo[0].id,
                        empresa: commonVars.empresa,
                        tipoRequerimiento: tipoRequerimientoGlobal,
                        tipoRequerimientoText: tipoRequerimientoGlobalText
                    };
                },
                results: function (data) {
                    dataBienBusquedaOnchange = data || [];
                    if (!isEmpty(data)) {
                        return {
                            results: $.map(data, function (obj) {
                                return {
                                    id: obj.id,
                                    text: obj.text
                                };
                            }),
                        };
                    }
                    return { results: [] };
                },
                cache: true,
            },
        }).on("change", function (e) {
            if (!isEmpty(e) && !isEmpty(e.val) && !isEmpty(dataBienBusquedaOnchange)) {
                var bienFiltradas = dataBienBusquedaOnchange.filter(function (bien) {
                    return bien && bien.id === e.val;
                });

                if (!isEmpty(bienFiltradas) && bienFiltradas.length > 0) {
                    dataCofiguracionInicial.bien.push(bienFiltradas[0]);

                    if (existeColumnaCodigo(16)) {
                        $("#txtProductoDescripcion_" + indice).val(bienFiltradas[0].descripcion);
                        $("#txtdescripcion_" + indice).val(bienFiltradas[0].descripcion);
                        // if (descripcionesModificadas[indice]) {
                        //     delete descripcionesModificadas[indice];
                        // }
                    }
                }
            }
            indiceBien = indice;
            banderaCopiaDocumento = 0;
            bienTramoId = null;

            loaderShow();

            if (documentoTipoTipo == 1 && banderaPrimeraFE) {
                primeraFechaEmision = $('#datepicker_' + fechaEmisionId).val();
                banderaPrimeraFE = false;
            }
            //            alert(primeraFechaEmision);

            if (existeColumnaCodigo(4)) {
                select2.asignarValor("cboPrecioTipo_" + indice, precioTipoPrimero);
                $("#cboPrecioTipo_" + indice).select2({ width: anchoTipoPrecioTD + 'px' });
            }

            if (existeColumnaCodigo(23)) {
                select2.asignarValor("cboAgencia_" + indice, "");
                $("#cboAgencia_" + indice).select2({ width: anchoAgenciaTD + 'px' });
            }

            if (existeColumnaCodigo(26)) {
                select2.asignarValor("cboCeCo_" + indice, "");
                $("#cboCeCo_" + indice).select2({ width: anchoCeCoTD + 'px' });
            }

            obtenerUnidadMedida(e.val, indice);
            //detalle[indice].comentarioBien = "";
            setearDescripcionProducto(indice);

            if (existeColumnaCodigo(21)) {
                $('#txtComentarioDetalle_' + indice).removeAttr("readonly");
            }
        });
        //        select2.cargar("cboBien_" + indice, data, "id", ["codigo_barra", "codigo", "descripcion"]);
        //        select2.asignarValor("cboBien_" + indice, 0);
        if (existeColumnaCodigo(13)) {
            select2.readonly("cboUnidadMedida_" + indice, true);
        }
        //        $("#cboBien_" + indice).select2({width: alturaBienTD + "px"});
    }

}

function cargarUnidadMedidadDetalleCombo(indice) {
    $("#cboUnidadMedida_" + indice).select2({
        width: "100%"
    }).on("change", function (e) {
        indiceBien = indice;

        obtenerPreciosEquivalentes(indice);
        obtenerStockActual(indice);
        setearUnidadMedidaDescripcion(indice);
    });

    $("#cboUnidadMedida_" + indice).select2({ width: anchoUnidadMedidaTD + "px" });
}

var precioTipoPrimero;
function cargarPrecioTipoDetalleCombo(data, indice) {
    if (existeColumnaCodigo(4)) {
        $("#cboPrecioTipo_" + indice).select2({
            width: "100%"
        }).on("change", function (e) {
            obtenerBienPrecio(indice);
        });

        if (!isEmpty(data)) {
            select2.cargar("cboPrecioTipo_" + indice, data, "precio_tipo_id", "precio_tipo_descripcion");
            precioTipoPrimero = data[0]["precio_tipo_id"];

            //        select2.asignarValor("cboPrecioTipo_" + indice, data[0]["precio_tipo_id"]);
        }

        $("#cboPrecioTipo_" + indice).select2({ width: anchoTipoPrecioTD + "px" });
    }
}


function cargarAgenciaDetalleCombo(data, indice) {
    if (existeColumnaCodigo(23)) {
        $("#cboAgencia_" + indice).select2({
            width: anchoAgenciaTD + "px"
        }).on("change", function (e) {
            hallarSubTotalDetalle(indice);
        });

        if (!isEmpty(data)) {
            select2.cargar("cboAgencia_" + indice, data, "id", ["codigo", "descripcion"]);
            select2.asignarValor("cboAgencia_" + indice, "");
        }
        $("#cboAgencia_" + indice).select2({ width: anchoAgenciaTD + "px" });
    }
}

function cargarCeCoDetalleCombo(data, indice) {
    if (existeColumnaCodigo(26)) {
        $("#cboCeCo_" + indice).select2({
            width:"100%"
        }).on("change", function (e) {
            actualizarTotalesGenerales(indice);
        });

        if (!isEmpty(data)) {
            select2.cargar("cboCeCo_" + indice, data, "id", ["codigo", "descripcion"]);
            select2.asignarValor("cboCeCo_" + indice, "");
        }
        // $("#cboCeCo_" + indice).select2({ width: anchoCeCoTD + "px" });
    }
}

function cargarComprasDetalleCombo(data, indice) {
    if (existeColumnaCodigo(33)) {
        $("#cboCompra_" + indice).select2({
            width: anchoCompraTD + "px"
        }).on("change", function (e) {
            var bienId = select2.obtenerValor("cboBien_" + indice);
            var stockBien = 0;
            $.each(detalle, function (index, item) {
                if (item.bienId == bienId) {
                    stockBien = item.stockBien;
                    return false;
                }
            });

            if (e.val == "1") {
                $("#txtCantidadAprobada_" + indice).prop('disabled', false);
                $("#trDetalle_" + indice).css('background-color', '');
            } else if (e.val == "2") {
                if (stockBien < $("#txtCantidad_" + indice).val()) {
                    select2.asignarValor("cboCompra_" + indice, "");
                    $("#cboCompra_" + indice).select2({ width: anchoCompraTD + "px" });
                    mostrarAdvertencia("El stock es menor para poder reservar stock, en la fila " + (indice + 1));
                    return false;
                }
                $("#txtCantidadAprobada_" + indice).val(0);
                $("#txtCantidadAprobada_" + indice).prop('disabled', true);
                var bienId = select2.obtenerValor('cboBien_' + indice);
                dataStockReservaOk = dataStockReservaOk.filter(objeto => objeto.bien_id != bienId);
                $("#trDetalle_" + indice).css('background-color', '');
            }
            actualizarTotalesGenerales(indice);
        });

        if (!isEmpty(data)) {
            select2.cargar("cboCompra_" + indice, data, "id", "descripcion");
            select2.asignarValor("cboCompra_" + indice, "");
        }
        $("#cboCompra_" + indice).select2({ width: anchoCompraTD + "px" });
    }
}

function llenarCentroCosto(item, extra, cbo_id) {
    var cuerpo = "";
    if ($("#" + cbo_id + " option[value='" + item["id"] + "']").length != 0) {
        return cuerpo;
    }
    if (item.hijos * 1 == 0) {
        cuerpo =
            '<option value="' +
            item["id"] +
            '">' +
            extra +
            item["codigo"] +
            " | " +
            item["descripcion"] +
            "</option>";
        return cuerpo;
    }
    cuerpo =
        '<option value="' +
        item["id"] +
        '" disabled>' +
        extra +
        item["codigo"] +
        " | " +
        item["descripcion"] +
        "</option>";
    var dataHijos = dataCuentasContables.filter(
        (cuentaContable) => cuentaContable.plan_contable_padre_id == item.id
    );
    $.each(dataHijos, function (indexHijo, cuentaContableHijo) {
        cuerpo += llenarCuentasContable(
            cuentaContableHijo,
            extra + "&nbsp;&nbsp;&nbsp;&nbsp;",
            cbo_id
        );
    });
    return cuerpo;
}

function cargarAgrupadorCombo(data) {
    if (existeColumnaCodigo(24)) {
        if (!isEmpty(data)) {
            select2.cargar("cboAgrupador", data, "id", ["codigo", "descripcion"]);
            select2.asignarValor("cboAgrupador", "");
        }
        $("#cboAgrupador").select2({ width: "300px" });
    }
}

var KPADINGTD = 1;
function llenarFilaDetalleTabla(indice) {
    numeroItemFinal++;
    var dataColumna = dataCofiguracionInicial.movimientoTipoColumna;

    var fila = "<tr id=\"trDetalle_" + indice + "\">";

    fila = fila + "<td style='border:0; width: 20px; vertical-align: middle; padding-right: 10px;'id=\"txtNumItem_" + indice + "\" name=\"txtNumItem_" + indice + "\" align='right'>" + numeroItemFinal + "</td>";

    //columnas dinamicas de acuerdo a documento_tipo_columna
    if (!isEmpty(dataColumna)) {
        $.each(dataColumna, function (index, item) {
            switch (parseInt(item.codigo)) {
                case 1://Precio de compra
                    fila = fila + "<td style='border:0; width: " + item.ancho + "px; vertical-align: middle; padding: " + KPADINGTD + "px;'id=\"txtPrecioCompra_" + indice + "\" name=\"txtPrecioCompra_" + indice + "\" align='right'></td>";
                    break;
                case 2:// Utilidad moneda
                    if (documentoTipoTipo == 1) {
                        fila += "<td style='border:0; width: " + item.ancho + "px; vertical-align: middle; padding: " + KPADINGTD + "px;' id=\"txtUtilidadSoles_" + indice + "\" name=\"txtUtilidadSoles_" + indice + "\" align='right'></td>";
                    }
                    break;
                case 3:// Utilidad porcentaje
                    if (documentoTipoTipo == 1) {
                        fila += "<td style='border:0; width: " + item.ancho + "px; vertical-align: middle; padding: " + KPADINGTD + "px;' id=\"txtUtilidadPorcentaje_" + indice + "\" name=\"txtUtilidadPorcentaje_" + indice + "\" align='right'></td>";
                    }
                    break;
                case 4:// Tipo de precio
                    fila += "<td style='border:0; width: " + item.ancho + "px; vertical-align: middle; padding: " + KPADINGTD + "px;' id=\"tdTipoPrecio_" + indice + "\">" + agregarPrecioTipoTabla(indice) + "</td>";
                    break;
                case 5://Precio unitario
                    fila += "<td style='border:0; width: " + item.ancho + "px; vertical-align: middle; padding: " + KPADINGTD + "px;'>" + agregarPrecioUnitarioDetalleTabla(indice) + "</td>";
                    break;
                case 6://Sub total detalle
                    fila += "<td style='border:0; width: " + item.ancho + "px; vertical-align: middle; padding: " + KPADINGTD + "px;' id=\"txtSubTotalDetalle_" + indice + "\" name=\"txtSubTotalDetalle_" + indice + "\" align='right'></td>";
                    break;
                case 7://Stock
                    fila += "<td class='dtdetalle_stock' style='border:0; width: " + item.ancho + "px; vertical-align: middle; padding: " + KPADINGTD + "px;' id=\"txtStock_" + indice + "\" name=\"txtStock_" + indice + "\" align='right'></td>";
                    break;
                case 8://Stock minimo
                    if (dataCofiguracionInicial.movimientoTipo[0].interfaz == 1) {
                        fila += "<td style='border:0; width: " + item.ancho + "px; vertical-align: middle; padding: " + KPADINGTD + "px;' id=\"txtStockSugerido_" + indice + "\" name=\"txtStockSugerido_" + indice + "\" align='right'></td>";
                    }
                    break;
                case 9://Prioridad
                    if (dataCofiguracionInicial.movimientoTipo[0].interfaz == 1) {
                        fila += "<td style='border:0; width: " + item.ancho + "px; vertical-align: middle; padding: " + KPADINGTD + "px;' id=\"txtPrioridad_" + indice + "\" name=\"txtPrioridad_" + indice + "\" align='right'></td>";
                    }
                    break;
                case 10://Columna en blanco
                    fila += "<td style='border:0; width: " + item.ancho + "px; vertical-align: middle; padding: " + KPADINGTD + "px;' bgcolor='#FFFFFF'></td>";
                    break;
                case 11://Producto
                    fila += "<td style='border:0; width: " + item.ancho + "px; vertical-align: middle; padding: " + KPADINGTD + "px;' id=\"tdBien_" + indice + "\">" + agregarBienDetalleTabla(indice) + "</td>";
                    break;
                case 12://Cantidad
                    fila += "<td class='dtdetalle_cantidad' style='border:0; width: " + item.ancho + "px; vertical-align: middle; padding: " + KPADINGTD + "px;'>" + agregarCantidadDetalleTabla(indice) + "</td>";
                    break;
                case 13://Unidad de medida
                    fila += "<td class='dtdetalle_unidad_medida' style='border:0; width: " + item.ancho + "px; vertical-align: middle; padding: " + KPADINGTD + "px;' id=\"tdUnidadMedida_" + indice + "\">" + agregarUnidadMedidaDetalleTabla(indice) + "</td>";
                    break;
                case 14://Acciones
                    if (doc_TipoId == GENERAR_COTIZACION && !isEmpty(arrayProveedor)) {
                        arrayProveedor.forEach(element => {
                            fila += "<td style='border:0; width: 150px' class='td_precio_" + indice + "_" + element.indice + "'>" + agregarPrecioUnitarioPDetalleTabla(indice, element.indice) + "</td>";
                            fila += "<td style='border:0; width: 150px' class='td_subTotal_" + indice + "_" + element.indice + "'>" + agregarSubTotalPDetalleTabla(indice, element.indice) + "</td>";
                        });
                    }
                    fila += "<td style='border:0; width: " + item.ancho + "px; text-align:center; vertical-align: middle; padding: " + KPADINGTD + "px;'>" + agregarAccionesDetalleTabla(indice) + "</td>"
                    break;
                case 15://Organizador
                    if (muestraOrganizador && organizadorIdDefectoTM == 0) {
                        fila += "<td style='border:0; width: " + item.ancho + "px; vertical-align: middle; padding: " + KPADINGTD + "px;'>" + agregarOrganizadorDetalleTabla(indice) + "</td>";
                    }
                    break;
                case 16://Producto descripcion
                    fila += "<td style='border:0; width: " + item.ancho + "px; vertical-align: middle; padding: " + KPADINGTD + "px;'>" + agregarProductoDescripcionDetalleTabla(indice, item.longitud) + "</td>";
                    break;
                case 17://Unidad de medida descripcion
                    fila += "<td style='border:0; width: " + item.ancho + "px; vertical-align: middle; padding: " + KPADINGTD + "px;'>" + agregarUnidadMedidaDescripcionDetalleTabla(indice, item.longitud) + "</td>";
                    break;
                case 18://Unidad de medida descripcion
                    fila += "<td style='border:0; width: " + item.ancho + "px; vertical-align: middle; padding: " + KPADINGTD + "px;'>" + agregarFechaVencimientoDetalleTabla(indice) + "</td>";
                    break;
                case 21://Comentario
                    fila += "<td style='border:0; width: " + item.ancho + "px; vertical-align: middle; padding: " + KPADINGTD + "px;' class='claseMostrarColumnaComentario'>" + agregarComentarioDetalleTabla(indice, item.longitud) + "</td>";
                    break;
                case 23://Agencia
                    fila += "<td style='border:0; width: " + item.ancho + "px; vertical-align: middle; padding: " + KPADINGTD + "px;' id=\"tdAgencia_" + indice + "\">" + agregarAgenciaDetalleTabla(indice, item.longitud) + "</td>";
                    break;
                case 24://Agrupador
                    fila += "<td class='dtdetalle_agrupador hidden' style='border:0; width: " + item.ancho + "px; vertical-align: middle; padding: " + KPADINGTD + "px;' id=\"tdAgrupador_" + indice + "\"><span class='badge badge-primary' id='badge_" + indice + "' name='badge_" + indice + "'></span></td>";
                    break;
                case 25://Ticket
                    fila += "<td style='border:0; width: " + item.ancho + "px; vertical-align: middle; padding: " + KPADINGTD + "px;' id=\"tdTicket_" + indice + "\">" + agregarTicketTabla(indice, item.longitud) + "</td>";
                    break;
                case 26://CeCo
                    fila += "<td style='border:0; width: " + item.ancho + "px; vertical-align: middle; padding: " + KPADINGTD + "px;' id=\"tdCeCo_" + indice + "\">" + agregarCeCoDetalleTabla(indice) + "</td>";
                    break;
                case 27://Postor N° 1
                    fila += "<td style='border:0; width: " + item.ancho + "px; vertical-align: middle; padding: " + KPADINGTD + "px;' id=\"tdPostor1_" + indice + "\">" + agregarPrecioUnitarioPDetalleTabla(indice, 1) + "</td>";
                    break;
                case 28://Postor N° 2
                    fila += "<td class='dtdetalle_postor2' style='border:0; width: " + item.ancho + "px; vertical-align: middle; padding: " + KPADINGTD + "px;' id=\"tdPostor2_" + indice + "\">" + agregarPrecioUnitarioPDetalleTabla(indice, 2) + "</td>";
                    break;
                case 29://Postor N° 3
                    fila += "<td class='dtdetalle_postor3' style='border:0; width: " + item.ancho + "px; vertical-align: middle; padding: " + KPADINGTD + "px;' id=\"tdPostor3_" + indice + "\">" + agregarPrecioUnitarioPDetalleTabla(indice, 3) + "</td>";
                    break;
                case 30://Sub total Postor N° 1
                    fila += "<td class='dtdetalle_postor_cantidad1' style='border:0; width: " + item.ancho + "px; vertical-align: middle; padding: " + KPADINGTD + "px;' id=\"tdPostor_cantidad1_" + indice + "\">" + agregarSubTotalPDetalleTabla(indice, 1) + "</td>";
                    break;
                case 31://Sub total Postor N° 2
                    fila += "<td class='dtdetalle_postor_cantidad2' style='border:0; width: " + item.ancho + "px; vertical-align: middle; padding: " + KPADINGTD + "px;' id=\"tdPostor_cantidad2_" + indice + "\">" + agregarSubTotalPDetalleTabla(indice, 2) + "</td>";
                    break;
                case 32://Sub total Postor N° 3
                    fila += "<td class='dtdetalle_postor_cantidad3' style='border:0; width: " + item.ancho + "px; vertical-align: middle; padding: " + KPADINGTD + "px;' id=\"tdPostor_cantidad3_" + indice + "\">" + agregarSubTotalPDetalleTabla(indice, 3) + "</td>";
                    break;
                case 33://Compras
                    fila += "<td style='border:0; width: " + item.ancho + "px; vertical-align: middle; padding: " + KPADINGTD + "px;' id=\"tdCompra_" + indice + "\">" + agregarCompra(indice) + "</td>";
                    break;
                case 34://Cantidad aceptada
                    fila += "<td class='dtdetalle_cantidad_aceptada' style='border:0; width: " + item.ancho + "px; vertical-align: middle; padding: " + KPADINGTD + "px;'>" + agregarCantidadAprobadaDetalleTabla(indice) + "</td>";
                    break;
            }
        });
    }

    fila = fila + "</tr>";

    return fila;
}

function agregarAccionesDetalleTabla(i) {

    let codigo = dataCofiguracionInicial.movimientoTipo[0].codigo;
    let accionComentario = '';
    if (codigo == 7) {
        accionComentario = '&nbsp;<a onclick=\"insertarComentarioBien(' + i + ');\">' +
            '<i class=\"fa fa-comment-o\" style=\"color:#33BEFF;\" title=\"Insertar comentario\"></i></a>';
    }

    var $html = '<div class="btn-toolbar" role="toolbar">' +
        '&nbsp;<a onclick=\"confirmarEliminar(' + i + ');\">' +
        '<i class=\"fa fa-trash-o\" style=\"color:#cb2a2a;\" title=\"Eliminar\"></i></a>' + accionComentario +
        '<div class="btn-group" style="float: right">' +
        '<a  class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false" onclick=\"eliminarOverflowDataTable();\">' +
        '<i class="ion-gear-a"></i>  <span class="caret"></span>' +
        '</a>' +
        '<ul class="dropdown-menu dropdown-menu-right">';
    // '<li>' +
    // '<a onclick=\"verificarTipoUnidadMedida(' + i + ');\">' +
    // '<i class=\"ion-ios7-toggle\"   style=\"color:#1ca8dd;\"   title=\"Registrar tramo\"></i>&nbsp;Registrar tramo</a>' +
    // '</li>' +
    // '<li>' +
    // '<a onclick=\"listarTramosBien(' + i + ');\">' +
    // '<i class=\"fa  fa-tasks\"    style=\"color:#0366b0;\"  title=\"Listar tramo\"></i>&nbsp;Listar tramo</a>' +
    // '</li>' +
    if (doc_TipoId == GENERAR_COTIZACION) {
        $html += '<li>' +
            '<a onclick=\"verificarStockBien(' + i + ');\">' +
            '<i class=\"fa fa-cubes\"    style=\"color:#5cb85c;\" title=\"Verificar stock\"></i>&nbsp;Verificar stock</a>' +
            '</li>';
    }

    // $html += '<li>';
    //         '<a onclick=\"verificarPrecioBien(' + i + ');\">' +
    //         '<i class=\"ion-pricetag\"  title=\"Ver precio\"></i>&nbsp;Precio mínimo</a>'
    //         '</li>';
    if (doc_TipoId == REQUERIMIENTO_AREA) {
        $html += '<li>' +
            '<a onclick=\"reservarStockBien(' + i + ');\">' +
            '<i class=\"ion-paper-airplane\"  title=\"Reservar stock\"></i>&nbsp;Reservar stock</a>'
        '</li>';
    }

    $html += '</ul>' +
        '</div>' +
        '</div>';
    return $html;
}

function eliminarOverflowDataTable() {
}

function agregarOverflowDataTable() {
}

$('#window').click(function (e) {
    var valor = e.target.className;

    if (valor != 'dropdown-toggle' && valor != 'ion-gear-a' && valor != 'caret') {
        agregarOverflowDataTable();
    }

});

function agregarOrganizadorDetalleTabla(i) {
    var $html = "<div id=\"contenedorOrganizador_" + i + "\">" +
        "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\">" +
        "<select name=\"cboOrganizador_" + i + "\" id=\"cboOrganizador_" + i + "\" class=\"select2\">" +
        "</select></div></div>";

    return $html;
}

function agregarCantidadDetalleTabla(i) {
    var disabled = '';
    if (doc_TipoId == REQUERIMIENTO_AREA) {
        disabled = 'disabled';
    }
    var $html = "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\">" +
        "<input type=\"number\" id=\"txtCantidad_" + i + "\" name=\"txtCantidad_" + i + "\" class=\"form-control\" required=\"\" aria-required=\"true\" value=\"0\" style=\"text-align: right;\" onchange=\"hallarSubTotalDetalle(" + i + ");hallarStockSaldo(" + i + ");hallarSubTotalPostorDetalleCantidad(" + i + ");\" onkeyup =\"hallarSubTotalDetalle(" + i + ");hallarStockSaldo(" + i + ");hallarSubTotalPostorDetalleCantidad(" + i + ");\" " + disabled + "/></div><input type=\"hidden\" id=\"txtmovimiento_bien_ids_" + i + "\" name=\"txtmovimiento_bien_ids_" + i + "\" /><input type=\"hidden\" id=\"txtCantidadPorAtender_" + i + "\" name=\"txtCantidadPorAtender_" + i + "\" />";

    return $html;
}

function agregarProductoDescripcionDetalleTabla(i, longitud) {
    var $html = "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\">" +
        "<input type=\"text\" id=\"txtProductoDescripcion_" + i + "\" name=\"txtProductoDescripcion_" + i + "\" maxlength='" + longitud + "' readonly='true' class=\"form-control\" required=\"\" aria-required=\"true\" value=\"\" style=\"text-align: left;\" onchange=\"hallarSubTotalDetalle(" + i + ");\" /></div>";

    return $html;
}

function agregarComentarioDetalleTabla(i, longitud) {
    var $html = "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12 claseMostrarColumnaComentario\">" +
        "<input  type=\"text\" id=\"txtComentarioDetalle_" + i + "\" name=\"txtComentarioDetalle_" + i + "\" maxlength='" + longitud + "' readonly='true'  class=\"form-control claseMostrarColumnaComentario\" required=\"\" aria-required=\"true\" value=\"\" style=\"text-align: left;\" onchange=\"hallarSubTotalDetalle(" + i + ");\" /></div>";

    return $html;
}

function agregarUnidadMedidaDescripcionDetalleTabla(i, longitud) {
    var $html = "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\">" +
        "<input type=\"text\" id=\"txtUnidadMedidaDescripcion_" + i + "\" name=\"txtUnidadMedidaDescripcion_" + i + "\" maxlength='" + longitud + "' readonly='true' class=\"form-control\" required=\"\" aria-required=\"true\" value=\"\" style=\"text-align: left;\" onchange=\"hallarSubTotalDetalle(" + i + ");\" /></div>";

    return $html;
}

function agregarFechaVencimientoDetalleTabla(i) {
    var fecha = obtenerFechaActual();

    var $html = "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\">" +
        '<input type="text" class="form-control fecha" placeholder="dd/mm/yyyy" id="txtFechaVencimiento_' + i + '" value="' + fecha + '">' +
        '<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>'
        + "</div>";

    return $html;
}

function agregarPrecioUnitarioDetalleTabla(i) {
    var $html = "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\">" +
        "<input type=\"number\" id=\"txtPrecio_" + i + "\" name=\"txtPrecio_" + i + "\" class=\"form-control\" required=\"\" aria-required=\"true\" value=\"\" style=\"text-align: right;\" onchange=\"hallarSubTotalDetalle(" + i + ")\" onkeyup =\"hallarSubTotalDetalle(" + i + ")\"/></div>";

    return $html;
}


function agregarAgenciaDetalleTabla(i) {
    var $html = "<div id=\"contenedorAgencia_" + i + "\">" +
        "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\">" +
        "<select name=\"cboAgencia_" + i + "\" id=\"cboAgencia_" + i + "\" class=\"select2\">" +
        "</select></div></div>";

    return $html;
}

function agregarCeCoDetalleTabla(i) {
    var $html = "<div id=\"contenedorCeCo_" + i + "\">" +
        "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\">" +
        "<select name=\"cboCeCo_" + i + "\" id=\"cboCeCo_" + i + "\" class=\"select2\">" +
        "</select></div></div>";

    return $html;
}

function agregarPrecioUnitarioPDetalleTabla(i, numero) {
    var $html = "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\" style=\"display: flex; align-items: center; gap: 8px;\"><input type=\"radio\" name=\"precioGanador_" + i + "\" value=\"" + numero + "\">" +
        "<input type=\"number\" id=\"txtPrecioP" + numero + "_" + i + "\" name=\"txtPrecioP" + numero + "_" + i + "\" class=\"form-control\" required=\"\" aria-required=\"true\" value=\"0\" style=\"text-align: right; width:100px;\" onchange=\"hallarSubTotalPostorDetalle(" + i + ", " + numero + ", 1)\" onkeyup =\"hallarSubTotalPostorDetalle(" + i + ", " + numero + ", 1)\"/></div>";
    return $html;
}

function agregarSubTotalPDetalleTabla(i, numero) {
    var $html = "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\">" +
        "<input type=\"number\" id=\"txtSubtotalP" + numero + "_" + i + "\" name=\"txtSubtotalP" + numero + "_" + i + "\" class=\"form-control\" required=\"\" aria-required=\"true\" value=\"\" style=\"text-align: right;width:100px;\" disabled/></div>";
    return $html;
}

function agregarCantidadAprobadaDetalleTabla(i) {
    var $html = "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\">" +
        "<input type=\"number\" id=\"txtCantidadAprobada_" + i + "\" name=\"txtCantidadAprobada_" + i + "\" class=\"form-control\" required=\"\" aria-required=\"true\" value=\"1\" style=\"text-align: right;\" onchange=\"hallarSubTotalDetalle(" + i + ");hallarStockSaldo(" + i + ");\" onkeyup =\"hallarSubTotalDetalle(" + i + ");hallarStockSaldo(" + i + ");\" /></div><input type=\"hidden\" id=\"txtmovimiento_bien_ids_" + i + "\" name=\"txtmovimiento_bien_ids_" + i + "\" />";

    return $html;
}

function agregarTicketTabla(i, longitud) {
    var $html = "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\">" +
        "<input  type=\"text\" id=\"txtTicket_" + i + "\" name=\"txtTicket_" + i + "\" maxlength='" + longitud + "' class=\"form-control\" aria-required=\"true\" value=\"\" style=\"text-align: left;\" /></div>";

    return $html;
}

function hallarSubTotalDetalle(indice) {
    indice = parseInt(indice);
    //obtenerStockActual(indice)

    //hallar totales genereales y subtotal detalle:
    actualizarTotalesGenerales(indice);

    //hallar utilidades detalles
    obtenerUtilidades(indice);

    //hallar utilidades generales
    obtenerUtilidadesGenerales();

}

function obtenerUtilidadesGenerales() {
    if (documentoTipoTipo == 1 && existeColumnaCodigo(2) && existeColumnaCodigo(3)) {

        var totalUtilidadesSoles = 0;
        var utilidadSoles = 0;

        var totalDocumento = parseFloat($('#' + importes.totalId).val());
        if (isEmpty(totalDocumento)) {
            totalDocumento = calcularImporteDetalle();
        }
        var totalUtilidadesPorcentaje = 0;

        if (totalDocumento != 0) {

            for (var i = 0; i < nroFilasInicial; i++) {
                utilidadSoles = parseFloat($('#txtUtilidadSoles_' + i).html());
                if (isEmpty(utilidadSoles) || !esNumero(utilidadSoles)) {
                    utilidadSoles = 0;
                }

                totalUtilidadesSoles = totalUtilidadesSoles + utilidadSoles;
            }

            totalUtilidadesPorcentaje = (totalUtilidadesSoles / totalDocumento) * 100;
        }


        $('#txtTotalUtilidadSoles').val(devolverDosDecimales(totalUtilidadesSoles));
        $('#txtTotalUtilidadPorcentaje').val(devolverDosDecimales(totalUtilidadesPorcentaje) + " %");

        document.getElementById('txtTotalUtilidadSoles').style.color = '#FF0000';
        document.getElementById('txtTotalUtilidadPorcentaje').style.color = '#FF0000';


        //guardar en array detalle utilidades general en primera fila
        if (!isEmpty(detalle[0])) {
            detalle[0].utilidadTotal = totalUtilidadesSoles;
            detalle[0].utilidadPorcentajeTotal = totalUtilidadesPorcentaje;
        }
        //fin guardar utilidad
    }
}

function obtenerUtilidades(indice) {
    var bienId = select2.obtenerValor("cboBien_" + indice);
    if (documentoTipoTipo == 1 && bienId != -1 && !isEmpty(bienId) &&
        existeColumnaCodigo(1) && existeColumnaCodigo(5) && existeColumnaCodigo(6) && existeColumnaCodigo(12)) {
        if (!valoresFormularioDetalle)
            return;

        var precioVenta = $('#txtPrecio_' + indice).val();
        var precioCompra = $('#txtPrecioCompra_' + indice).html();
        var cantidad = $('#txtCantidad_' + indice).val();

        var utilidadSoles = (precioVenta - precioCompra) * cantidad;

        var subTotal = $('#txtSubTotalDetalle_' + indice).html();
        var utilidadPorcentaje = 0;
        if (subTotal != 0) {
            utilidadPorcentaje = (utilidadSoles / subTotal) * 100;
        }

        if (existeColumnaCodigo(2)) {
            $('#txtUtilidadSoles_' + indice).html(devolverDosDecimales(utilidadSoles));
            document.getElementById('txtUtilidadSoles_' + indice).style.color = '#FF0000';
        }
        if (existeColumnaCodigo(3)) {
            $('#txtUtilidadPorcentaje_' + indice).html(devolverDosDecimales(utilidadPorcentaje) + " %");
            document.getElementById('txtUtilidadPorcentaje_' + indice).style.color = '#FF0000';
        }

        //guardar en array detalle

        var indexTemporal = -1;
        if (existeColumnaCodigo(2) || existeColumnaCodigo(3)) {
            $.each(detalle, function (i, item) {
                if (parseInt(item.index) === parseInt(indice)) {
                    indexTemporal = i;
                    return false;
                }
            });
        }

        if (indexTemporal > -1) {
            if (existeColumnaCodigo(2)) {
                detalle[indexTemporal].utilidad = utilidadSoles;
            }
            if (existeColumnaCodigo(3)) {
                detalle[indexTemporal].utilidadPorcentaje = utilidadPorcentaje;
            }
        }
        //fin guardar utilidad detalle
    }
}


var indiceLista = [];

function actualizarTotalesGenerales(indice) {
    valoresFormularioDetalle = validarFormularioDetalleTablas(indice);
    bienTramoId = null;
    if (!valoresFormularioDetalle)
        return;

    var subTotal = valoresFormularioDetalle.cantidad * valoresFormularioDetalle.precio;

    if (existeColumnaCodigo(6)) {
        $('#txtSubTotalDetalle_' + indice).html(devolverDosDecimales(subTotal));
    }

    valoresFormularioDetalle.subTotal = subTotal;
    valoresFormularioDetalle.index = indice;

    var indexTemporal = -1;
    $.each(detalle, function (i, item) {
        if (parseInt(item.index) === parseInt(indice)) {
            indexTemporal = i;
            return false;
        }
    });

    //ASIGNO EL VALOR DE COMPRA EN CASO NO ESTE CONFIGURADA LA COLUMNA COMPRA
    if (!existeColumnaCodigo(1)) {
        var precioCompraTemp = 0;
        if (indexTemporal > -1) {
            if (!isEmpty(detalle[indexTemporal].precioCompra)) {
                precioCompraTemp = detalle[indexTemporal].precioCompra;
            }
        }

        //        if(varPrecioCompra != 0){
        if (indexTemporal > -1) {
            if (detalle[indexTemporal].bienId != valoresFormularioDetalle.bienId || varPrecioCompra != 0) {
                valoresFormularioDetalle.precioCompra = varPrecioCompra;
            } else {
                valoresFormularioDetalle.precioCompra = precioCompraTemp;
            }
        } else {
            valoresFormularioDetalle.precioCompra = varPrecioCompra;
        }

        varPrecioCompra = 0;
    }

    if (indexTemporal > -1) {
        detalle[indexTemporal] = valoresFormularioDetalle;
    } else {
        detalle[detalle.length] = valoresFormularioDetalle;
    }
    asignarImporteDocumento();
}

function eliminarDetalleFormularioListas(indiceDet) {
    /*var indice = indiceLista.indexOf(indiceDet);
     if (indice > -1) {
     indiceLista.splice(indice, 1);
     detalle.splice(indice, 1);
     }*/
    if (indiceDet > -1) {
        //indiceLista.splice(indiceDet, 1);
        detalle.splice(indiceDet, 1);
    }
}

var varPrecioCompra = 0;

function validarFormularioDetalleTablas(indice) {
    //obtener los datos del detalle dinamico
    var dataColumna = dataCofiguracionInicial.movimientoTipoColumna;
    var validar = true;
    var objDetalle = {};//Objeto para el detalle
    var correcto = true;
    var valor;
    var detDetalle = [];
    var validar_postor = false;

    if (!isEmpty(dataColumna)) {
        $.each(dataColumna, function (index, item) {

            valor = null;
            if (item.opcional == 1) {
                validar = false;
            } else {
                validar = true;
            }
            correcto = true;

            //obtener los datos del detalle
            switch (parseInt(item.codigo)) {
                case 1:
                    //                    valor = document.getElementById("txtPrecioCompra_" + indice).value;
                    valor = $('#txtPrecioCompra_' + indice).html();
                    objDetalle.precioCompra = valor;
                    break;

                //numeros
                case 5:// PRECIO UNITARIO
                    valor = document.getElementById("txtPrecio_" + indice).value;
                    objDetalle.precio = valor;
                    break;
                case 12:// CANTIDAD
                    valor = document.getElementById("txtCantidad_" + indice).value;
                    if (doc_TipoId == REQUERIMIENTO_AREA) {
                        objDetalle.cantidadAceptada = valor;
                    } else {
                        objDetalle.cantidad = valor;
                    }
                    break;

                //combos, seleccion
                case 4:// TIPO PRECIO
                    valor = select2.obtenerValor("cboPrecioTipo_" + indice);
                    objDetalle.precioTipoId = valor;
                    break;
                case 11:// PRODUCTO
                    valor = select2.obtenerValor("cboBien_" + indice);
                    objDetalle.bienId = valor;

                    objDetalle.bienDesc = select2.obtenerText("cboBien_" + indice);

                    var valormovimiento_bien_ids = $("#txtmovimiento_bien_ids_" + indice).val();
                    objDetalle.movimiento_bien_ids = valormovimiento_bien_ids;
                    break;
                case 13:// UNIDAD DE MEDIDA
                    valor = select2.obtenerValor("cboUnidadMedida_" + indice);
                    objDetalle.unidadMedidaId = valor;
                    objDetalle.unidadMedidaDesc = select2.obtenerText("cboUnidadMedida_" + indice);
                    break;
                case 15:// Organizador
                    if (validacion.organizadorExistencia && organizadorIdDefectoTM == 0) {
                        valor = select2.obtenerValor("cboOrganizador_" + indice);
                        objDetalle.organizadorDesc = select2.obtenerText("cboOrganizador_" + indice);
                    } else {
                        valor = organizadorIdDefectoTM;
                    }
                    objDetalle.organizadorId = valor;
                    break;

                case 21://comentario
                    valor = $('#txtComentarioDetalle_' + indice).val();
                    objDetalle.comentarioBien = valor;
                    break;

                //texto
                case 16://descripcion de producto
                    valor = $('#txtProductoDescripcion_' + indice).val();
                    detDetalle.push({ columnaCodigo: 16, valorDet: valor });
                    break;
                case 17://descripcion de unidad de medida
                    valor = $('#txtUnidadMedidaDescripcion_' + indice).val();
                    detDetalle.push({ columnaCodigo: 17, valorDet: valor });
                    break;

                //fechas
                case 18://fecha vencimiento
                    valor = $('#txtFechaVencimiento_' + indice).val();
                    detDetalle.push({ columnaCodigo: 18, valorDet: valor });
                    break;

                case 23://Agencia
                    valor = select2.obtenerValor("cboAgencia_" + indice);
                    objDetalle.agenciaId = valor;
                    objDetalle.agenciaDesc = select2.obtenerText("cboAgencia_" + indice);
                    break;
                case 25://Ticket
                    valor = document.getElementById("txtTicket_" + indice).value;
                    objDetalle.ticket = valor;
                    break;
                case 26://CeCo
                    valor = select2.obtenerValor("cboCeCo_" + indice);
                    objDetalle.CeCoId = valor;
                    break;
                // case 27://Postor N° 1
                //     valor = $("#txtPrecioP1_" + indice).val();
                //     objDetalle.precioPostor1 = valor;
                //     objDetalle.checked1 = $('#selectPostor1').is(":checked");
                //     objDetalle.checked1Moneda = $('#selectPostor1Moneda').is(":checked");
                //     break;
                // case 28://Postor N° 2
                //     valor = $("#txtPrecioP2_" + indice).val();
                //     objDetalle.precioPostor2 = valor;
                //     objDetalle.checked2 = $('#selectPostor2').is(":checked");
                //     objDetalle.checked2Moneda = $('#selectPostor2Moneda').is(":checked");
                //     break;
                // case 29://Postor N° 3
                //     valor = $("#txtPrecioP3_" + indice).val();
                //     objDetalle.precioPostor3 = valor;
                //     objDetalle.checked3 = $('#selectPostor3').is(":checked");
                //     objDetalle.checked3Moneda = $('#selectPostor3Moneda').is(":checked");
                //     break;
                case 33://Compra
                    valor = select2.obtenerValor("cboCompra_" + indice);
                    objDetalle.esCompra = valor;
                    objDetalle.compraDesc = select2.obtenerText("cboCompra_" + indice);
                    break;
                case 34:// CANTIDAD ACEPTADA
                    valor = document.getElementById("txtCantidadAprobada_" + indice).value;
                    objDetalle.cantidad = valor;
                    break;
                case 36:
                    valor = $('#imagenPdfAdjunto_' + indice).val();
                    var nombreArchivo = $("#nombreimagenPdfAdjunto_" + indice).val();
                    detDetalle.push({ columnaCodigo: 36, valorDet: valor, nombreArchivo: nombreArchivo });
                    break;
            }

            //validar los valores del detalle
            let dataDocumentoTipoSeleccionado = obtenerDocumentoTipoSeleccionado();
            switch (parseInt(item.codigo)) {
                //numeros
                case 5:// PRECIO UNITARIO
                    if ((isEmpty(valor) || !esNumero(valor) || valor < 0) && validar) {
                        $('#txtPrecio_' + indice).val(0.00);
                        mostrarValidacionLoaderClose("Debe ingresar: " + item.descripcion + " válida, en fila " + (indice + 1));
                        correcto = false;
                    }
                    break;
                case 12:// CANTIDAD
                    if ((isEmpty(valor) || !esNumero(valor)) && validar) {
                        $('#txtCantidad_' + indice).val("0");
                        mostrarValidacionLoaderClose("Debe ingresar: " + item.descripcion + " válida, en fila " + (indice + 1));
                        correcto = false;
                    }
                    break;


                //combos, seleccion
                case 4:// TIPO PRECIO
                case 11:// PRODUCTO
                case 13:// UNIDAD DE MEDIDA
                case 15:// Organizador
                case 23:// Agencia
                    if (dataDocumentoTipoSeleccionado.id != "270" && dataDocumentoTipoSeleccionado.id != GENERAR_COTIZACION) {
                        if (isEmpty(valor) && validar) {
                            mostrarValidacionLoaderClose("Seleccione: " + item.descripcion + ", en fila " + (indice + 1));
                            correcto = false;
                        }
                    }
                    break;
                //texto
                case 16://descripcion de producto
                case 17://descripcion de unidad de medida
                    if (dataDocumentoTipoSeleccionado.id != "270") {
                        if (isEmpty(valor) && validar) {
                            mostrarValidacionLoaderClose("Ingrese: " + item.descripcion + ", en fila " + (indice + 1));
                            correcto = false;
                        }
                    }
                    break;
                //fecha
                case 18: // fecha vencimiento
                    if (dataDocumentoTipoSeleccionado.id != "270") {
                        if (isEmpty(valor) && validar) {
                            mostrarValidacionLoaderClose("Ingrese: " + item.descripcion + ", en fila " + (indice + 1));
                            correcto = false;
                        }
                    }
                    break;
            }

            if (!correcto) {
                return correcto;
            }
        });
    } else {
        mostrarValidacionLoaderClose("Falta configurar las columnas del detalle");
        return false;
    }

    if (!existeColumnaCodigo(15)) {
        if (muestraOrganizador) {
            objDetalle.organizadorId = select2.obtenerValor('cboOrganizador');
        }
    }

    if (!correcto) {
        return correcto;
    }

    //fin columna dinamica
    var stockBien = 0;
    var bienTipoId = 0;
    var comentarioBien = '';
    var agrupador_id = '';

    var indexTemporal = -1;
    $.each(detalle, function (i, item) {
        if (parseInt(item.index) === parseInt(indice)) {
            indexTemporal = i;
            return false;
        }
    });

    var bTramoId = null;
    if (!isEmpty(detalle) && !isEmpty(detalle[indexTemporal])) {
        stockBien = detalle[indexTemporal].stockBien;
        bienTipoId = detalle[indexTemporal].bienTipoId;
        comentarioBien = detalle[indexTemporal].comentarioBien;
        agrupador_id = detalle[indexTemporal].agrupadorId;

        //logica tramo
        var detalleBienTramoId = detalle[indexTemporal].bienTramoId;
        if (isEmpty(detalleBienTramoId) || (detalleBienTramoId != bienTramoId && bienTramoId != null)) {
            bTramoId = bienTramoId;
        } else {
            bTramoId = detalleBienTramoId;
        }

    } else {
        //        bienTipoId = obtenerBienTipoIdPorBienId(bienId);
        bienTipoId = obtenerBienTipoIdPorBienId(select2.obtenerValor("cboBien_" + indice));
    }

    //validar precios postores
    if (doc_TipoId == GENERAR_COTIZACION) {
        arrayProveedor.forEach(function (proveedorID, idx) {
            var valor = $("#txtPrecioP" + proveedorID.indice + "_" + indice).val();
            if(Number(valor) > 0){
                var postor_ganador = $('input[name="precioGanador_' + indice + '"]:checked').val();
                var postor_ganador_id = null;
                if (!isEmpty(postor_ganador)) {
                    postor_ganador_id = arrayProveedor[postor_ganador].proveedor_id;
                }
                objDetalle.postor_ganador_id = postor_ganador_id;
                detDetalle.push({ columnaCodigo: 37, valorDet: valor, valorExtra: proveedorID.proveedor_id });
            }
        });
        var positivos = detDetalle.filter(item => Number(item.valorDet) > 0);
        if (!isEmpty(positivos) && (isEmpty(boton.accion))) {
            var minimo = positivos.reduce((min, item) => {
                return Number(item.valorDet) < Number(min.valorDet) ? item : min;
            }, positivos[0]); // solo si el array no está vacío
            arrayProveedor.forEach(function (proveedorID, idx) {
                if (proveedorID.proveedor_id == minimo.valorExtra) {
                    $('input[name="precioGanador_' + indice + '"][value="' + proveedorID.indice + '"]').prop('checked', true);
                }
            });
        }

    }

    //otros datos:
    objDetalle.stockBien = stockBien;
    objDetalle.bienTipoId = bienTipoId;
    objDetalle.bienTramoId = bTramoId;
    objDetalle.detalle = detDetalle;
    objDetalle.comentarioBien = comentarioBien;
    objDetalle.agrupadorId = agrupador_id;
    objDetalle.cantidadPorAtender = document.getElementById("txtCantidadPorAtender_" + indice).value;

    return objDetalle;
}

function limpiarFilaDetalleFormulario(indice) {
    //columnas dinamicas de acuerdo a documento_tipo_columna
    if (!isEmpty(dataColumna)) {
        $.each(dataColumna, function (index, item) {
            switch (parseInt(item.tipo)) {
                case 1://Precio de compra
                    $("#txtPrecioCompra_" + indice).html("");
                    break;
                case 2:// Utilidad moneda
                    $("#txtUtilidadSoles_" + indice).html("");
                    break;
                case 3:// Utilidad porcentaje
                    $("#txtUtilidadPorcentaje_" + indice).html("");
                    break;
                case 4:// Tipo de precio
                    $("#cboPrecioTipo_" + indice).select2({ width: anchoTipoPrecioTD + 'px' });
                    break;
                case 5://Precio unitario
                    $("#txtPrecio" + indice).html("");
                    break;
                case 6://Sub total detalle
                    $("#txtSubTotalDetalle_" + indice).html("");
                    break;
                case 7://Stock
                    $("#txtStock_" + indice).html("");
                    break;
                case 11:
                    select2.asignarValor('cboBien_' + indice, '');
                    $("#cboBien_" + indice).select2({ width: alturaBienTD + 'px' });
                    break;
                case 12:
                    document.getElementById("txtCantidad_" + indice).value = '0';
                    break;
                case 13:
                    select2.asignarValor('cboUnidadMedida_' + indice, '');
                    $("#cboUnidadMedida_" + indice).select2({ width: anchoUnidadMedidaTD + 'px' });
                    break;

                case 13:
                    select2.asignarValor('cboAgencia_' + indice, '');
                    $("#cboAgencia_" + indice).select2({ width: anchoAgenciaTD + 'px' });
                    break;
                case 26:
                    select2.asignarValor('cboCeCo_' + indice, '');
                    $("#cboCeCo_" + indice).select2({ width: anchoCeCoTD + 'px' });
                    break;
                case 34:
                    document.getElementById("txtCantidadAprobada_" + indice).value = '0';
                    break;
            }
        });
    }
}


function agregarSubTotalDetalleTabla(i) {
    var $html = "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\">" +
        "<input type=\"text\" id=\"txtSubTotalDetalle_" + i + "\" name=\"txtSubTotalDetalle_" + i + "\" class=\"form-control\" required=\"\" aria-required=\"true\" value=\"\" style=\"text-align: right;\" disabled/></div>";

    return $html;
}

function agregarUnidadMedidaDetalleTabla(i) {
    var disabled = '';
    if (doc_TipoId == GENERAR_COTIZACION || doc_TipoId == REQUERIMIENTO_AREA || doc_TipoId == COTIZACION_SERVICIO) {
        disabled = 'disabled';
    }
    var $html = "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\">" +
        "<select name=\"cboUnidadMedida_" + i + "\" id=\"cboUnidadMedida_" + i + "\" class=\"select2\" onchange=\"\" " + disabled + ">" +
        "</select></div>";

    return $html;
}

function agregarPrecioTipoTabla(i) {
    var $html = "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\">" +
        "<select name=\"cboPrecioTipo_" + i + "\" id=\"cboPrecioTipo_" + i + "\" class=\"select2\" onchange=\"\">" +
        "</select></div>";

    return $html;
}

function agregarCompra(i) {
    var $html = "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\">" +
        "<select name=\"cboCompra_" + i + "\" id=\"cboCompra_" + i + "\" class=\"select2\" onchange=\"\">" +
        "</select></div>";

    return $html;
}

function agregarBienDetalleTabla(i) {
    var disabled = '';
    if (doc_TipoId == GENERAR_COTIZACION || doc_TipoId == REQUERIMIENTO_AREA || doc_TipoId == COTIZACION_SERVICIO) {
        disabled = 'disabled';
    }
    var $html = "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\">" +
        //            "<select name=\"cboBien_" + i + "\" id=\"cboBien_" + i + "\" class=\"select2\"></select>" +
        "<span name=\"obsupcboBien_" + i + "\" id=\"obsupcboBien_" + i + "\" class='select2-chosen hidden'>&nbsp;</span>" +
        "<input name=\"cboBien_" + i + "\" id=\"cboBien_" + i + "\" class=\"select2\" " + disabled + ">" +
        "<BR><span name=\"obscboBien_" + i + "\" id=\"obscboBien_" + i + "\" class='select2-chosen'></span>" +
        "<span class='input-group-btn'>";
    if (doc_TipoId == SOLICITUD_REQUERIMIENTO) {
        $html += "<button type='button' class='btn btn-effect-ripple btn-default' title='Actualizar producto'" +
            "style='padding-bottom: 7px;' onclick='actualizarComboProducto(" + i + ")' " + disabled + "><i class='ion-refresh' style='color: #5CB85C;'></i></button>";
    }

    if (existeColumnaCodigo(23) || existeColumnaCodigo(22)) {
        $html += "<button type='button' class='btn btn-effect-ripple btn-default' title='Ingresar comentario'" +
            "style='padding-bottom: 7px;' onclick='insertarComentarioBien(" + i + ")'><i class='fa fa-comment-o' style='color: #33BEFF;'></i></button>";
    }
    if (existeColumnaCodigo(24)) {
        $html += "<button type='button' class='btn_agrupador btn btn-effect-ripple btn-default hidden' title='Ingresar agrupador'" +
            "style='padding-bottom: 7px;' onclick='insertarAgrupadorBien(" + i + ")'><i class='fa fa-users' style='color: #33BEFF;'></i></button>";
    }
    if (existeColumnaCodigo(36)) {
        $html += "<button type='button' class='btn btn-effect-ripple btn-default' title='Adjuntar archivo'" +
            "style='padding-bottom: 7px;' onclick='adjuntarImagenPdfBien(" + i + ")'><i class='fa fa-cloud-upload' style='color: #33BEFF;'></i></button><input type='hidden' id='imagenPdfAdjunto_" + i + "' name='imagenPdfAdjunto_" + i + "' /><input type='hidden' id='nombreimagenPdfAdjunto_" + i + "' name='nombreimagenPdfAdjunto_" + i + "' />";
    }
    if (doc_TipoId == GENERAR_COTIZACION) {
        $html += "<button type='button' class='btn btn-effect-ripple btn-default' title='Ver detalle Requerimiento'" +
            "style='padding-bottom: 7px;' onclick='verDetalleRequerimiento(" + i + ")'><i class='fa fa-comments-o' style='color: #33BEFF;'></i></button>";
    }
    $html += "</span>" +
        "</div>";

    return $html;
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
    //    console.log(data);
    dataCofiguracionInicial.documento_tipo_conf = data;

    validarCambioFechaEmision = true;
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
                case 42:
                case 49:
                    contadorEspeciales += 1;
                    escribirItem = false;
                    break;
                default:
                    if (contador % 3 == 0) {
                        appendForm('<div class="row">');
                    }
                    contador++;
                    var hidden_4 = "";
                    var btn_upload = "";
                    if (doc_TipoId == SOLICITUD_REQUERIMIENTO) {
                        if (item.descripcion == "Tipo" || item.descripcion == "Otros" || item.descripcion == "Proveedor" || item.descripcion == "Entrega en destino") {
                            hidden_4 = "hidden";
                        }
                    }
                    var html = '<div class="form-group col-md-4" id="id_' + item.id + '" ' + hidden_4 + '>';
                    if (item.tipo != 31) {
                        if (item.codigo != 11) {
                            html += '<label>' + item.descripcion + ' ' + ((item.opcional == 0) ? '*' : '') + '</label>' + btn_upload;
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
                    var disabled = "";
                    if(doc_TipoId == COTIZACION_SERVICIO){
                        disabled = "disabled";
                    }
                    html += '<input type="number" id="txt_' + item.id + '" name="txt_' + item.id + '" ' + readonly + ' class="form-control" value="" maxlength="' + longitudMaxima + '" ' + maxNumero + ' style="text-align: right;" '+ disabled +'/>';
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
                    html += '<input type="text" class="form-control fecha" placeholder="dd/mm/yyyy" id="datepicker_' + item.id + '"  onchange="obtenerTipoCambioDatepicker();" disabled>' +
                        '<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>';
                    break;
                case 3:
                case 10:
                case 11:
                    if (item.codigo != '11' && item.codigo != '01') {
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
                    if (doc_TipoId != GENERAR_COTIZACION) {
                        if (mostrarCampo) { //DT Guia de remision BH
                            html += '<label>' + item.descripcion + ' ' + ((item.opcional == 0) ? '*' : '') + '</label>';
                            html += '<div id ="divContenedorAdjuntoMultiple">';
                            html += '<a class="btn btn-primary btn-sm m-b-5" onclick="adjuntar();"><i class="fa fa-cloud-upload"></i> Adjuntar archivos</a>';
                            iniciarArchivoAdjuntoMultiple();
                        }
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
                case 42:
                    $("#contenedorCboTipoRequerimiento").empty();
                    $("#contenedorCboTipoRequerimiento").show();
                    $("#contenedorCboTipoRequerimiento").append("<h4 class='text-dark text-uppercase'>" +
                        "<select id='cboTipoRequerimiento_" + item.id + "' name='cboTipoRequerimiento_" + item.id + "' class='select2' ></select>" +
                        "</h4>");
                    var dtdTipoTipo = obtenerDocumentoTipoDatoXTipoXCodigo(4, "02");
                    if (!isEmpty(item.data)) {
                        select2.cargar("cboTipoRequerimiento_" + item.id, item.data, "id", "descripcion");
                        select2.asignarValor("cboTipoRequerimiento_" + item.id, item.lista_defecto);
                        tipoRequerimientoGlobal = item.lista_defecto;
                        tipoRequerimientoGlobalText = select2.obtenerText("cboTipoRequerimiento_" + item.id);
                        if (item.lista_defecto == 454) {
                            $("#id_" + dtdTipoTipo.id).hide();
                        }
                        if (doc_TipoId == GENERAR_COTIZACION || doc_TipoId == REQUERIMIENTO_AREA) {
                            $("#cboTipoRequerimiento_" + item.id).attr('disabled', 'disabled');
                        }
                    }
                    break;
                case 43:
                    if (doc_TipoId == REQUERIMIENTO_AREA) {
                        html += '<select name="cbo_' + item.id + '" id="cbo_' + item.id + '" class="select2"></select>';
                    } else {
                        html += '<select name="cbo_' + item.id + '" id="cbo_' + item.id + '" class="select2" disabled></select>';
                    }
                    break;
                case 44:
                    html += '<select name="cbo_' + item.id + '" id="cbo_' + item.id + '" class="select2"></select>';
                    break;
                case 45:
                case 46:
                case 47:
                case 48:
                case 50:
                case 51:
                case 52:
                    html += '<select name="cbo_' + item.id + '" id="cbo_' + item.id + '" class="select2"></select>';
                    break;
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
                        onChangeFechaEmision();
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
                    var dtdTipoArea = obtenerDocumentoTipoDatoIdXTipo(43);
                    var id = select2.obtenerValor("cbo_" + dtdTipoArea);
                    if(id == 27){
                        var dtdTipoUrgencia = obtenerDocumentoTipoDatoXTipoXCodigo(4, "04");
                        select2.asignarValor("cbo_" + dtdTipoUrgencia.id, 511);
                        $("#cbo_" + dtdTipoUrgencia.id).prop('disabled', true);
                    }
                    break;
                case 5:
                    $("#cbo_" + item.id).select2({
                        width: '100%'
                    }).on("change", function (e) {
                        obtenerPersonaDireccion(e.val);
                        obtenerPersonaContacto(e.val);
                        if (doc_TipoId == ORDEN_COMPRA || doc_TipoId == ORDEN_SERVICIO || doc_TipoId == COTIZACIONES || doc_TipoId == COTIZACION_SERVICIO) {
                            obtenerCuentaPersona(e.val);
                        }
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
                        // if (doc_TipoId == ORDEN_COMPRA || doc_TipoId == ORDEN_SERVICIO || doc_TipoId == COTIZACIONES || doc_TipoId == COTIZACION_SERVICIO) {
                        //     obtenerCuentaPersona(e.val);
                        // }
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
                case 43:
                    if(!isEmpty(item.data)){
                        select2.cargar("cbo_" + item.id, item.data, "id", "descripcion");
                    }
                    $("#cbo_" + item.id).select2({
                        width: '100%'
                    }).on("change", function (e) {
                        if (doc_TipoId == SOLICITUD_REQUERIMIENTO) {
                             var dtdTipoUrgencia = obtenerDocumentoTipoDatoXTipoXCodigo(4, "04");
                            if(e.val == 27){
                                select2.asignarValor("cbo_" + dtdTipoUrgencia.id, 511);
                                $("#cbo_" + dtdTipoUrgencia.id).prop('disabled', true);
                            }else{
                                select2.asignarValor("cbo_" + dtdTipoUrgencia.id, 473);
                                $("#cbo_" + dtdTipoUrgencia.id).prop('disabled', false);
                            }
                        }
                    });
                    select2.asignarValor("cbo_" + item.id, 0);
                    if (doc_TipoId != REQUERIMIENTO_AREA) {
                        if (item.listarAreas == 1) {
                            $("#cbo_" + item.id).prop("disabled", false);
                        } else {
                            var id = parseInt(item.lista_defecto);
                            select2.asignarValor("cbo_" + item.id, id);
                        }
                    }
                    break;
                case 44:
                    select2.cargar("cbo_" + item.id, item.data, "id", ["codigo", "descripcion"]);
                    $("#cbo_" + item.id).select2({
                        width: '100%'
                    });
                    select2.asignarValor("cbo_" + item.id, 0);
                    break;
                case 45:
                case 46:
                    select2.cargar("cbo_" + item.id, item.data, "id", ["codigo", "descripcion"]);
                    $("#cbo_" + item.id).select2({
                        width: '100%'
                    });
                    select2.asignarValor("cbo_" + item.id, 0);
                    break;
                case 47:
                    $("#cbo_" + item.id).select2({
                        width: '100%'
                    });
                    break;
                case 48:
                    $("#cbo_" + item.id).select2({
                        width: '100%'
                    });
                    break;
                case 51:
                    select2.cargar("cbo_" + item.id, item.data, "id", ["codigo", "descripcion"]);
                    $("#cbo_" + item.id).select2({
                        width: '100%'
                    });
                    select2.asignarValor("cbo_" + item.id, 0);
                    break;
                case 50:
                    select2.cargar("cbo_" + item.id, item.data, "id", "descripcion");
                    $("#cbo_" + item.id).select2({
                        width: '100%'
                    });
                    select2.asignarValor("cbo_" + item.id, 0);
                    break;
                case 52:
                    select2.cargar("cbo_" + item.id, item.data, "id", "descripcion");
                    $("#cbo_" + item.id).select2({
                        width: '100%'
                    });
                    select2.asignarValor("cbo_" + item.id, 0);
                    if (!isEmpty(item.lista_defecto)) {
                        var id = parseInt(item.lista_defecto);
                        select2.asignarValor("cbo_" + item.id, id);
                        $("#cbo_" + item.id).prop("disabled", true);
                        if (item.bandera_urgencia == "1") {
                            var dtdTipoUrgencia = obtenerDocumentoTipoDatoXTipoXCodigo(4, "04");
                            $("#cbo_" + dtdTipoUrgencia.id).prop("disabled", true);
                            select2.asignarValor("cbo_" + dtdTipoUrgencia.id, 473);
                        }
                    } else {
                        $("#cbo_" + item.id).prop("disabled", false);
                    }
                    break;
                case 54:
                   select2.cargar("cbo_" + item.id, item.data, "id", ["codigo","descripcion"]);
                    $("#cbo_" + item.id).select2({
                        width: '100%'
                    });
                    break;
            }
        });
        $("#id_3044").hide();
        $("#id_3045").hide();
        $("#id_3046").hide();
        $("#id_3047").hide();
        $("#id_3048").hide();
        $("#id_3049").hide();
        $("#id_3050").hide();
        $("#id_3051").hide();
        $("#id_3052").hide();

        $("#id_3044").prop("required", false);
        $("#id_3045").prop("required", false);
        $("#id_3046").prop("required", false);
        $("#id_3047").prop("required", false);
        $("#id_3048").prop("required", false);
        $("#id_3049").prop("required", false);
        $("#id_3050").prop("required", false);
        $("#id_3051").prop("required", false);
        $("#id_3052").prop("required", false);
        modificarDetallePrecios();


        validarImporteLlenar();
        asignarImporteDocumento();
    }
}

var percepcionId = 0;
function calculeTotalMasPercepcion(id) {

    if (calculoTotal <= 0) {
        mostrarValidacionLoaderClose("Total debe ser mayor a cero.");
        return false;
    }

    var percepcion = parseFloat($('#txt_' + id).val());

    if (isEmpty(percepcion) || percepcion < 0) {
        mostrarValidacionLoaderClose("Debe ingresar una percepción válida");
        $('#' + importes.totalId).val(devolverDosDecimales(calculoTotal));
        $('#txt_' + id).val('');
        return false;
    }

    var percepcionMaxima = 0.02 * calculoTotal + 1;

    if (percepcion > percepcionMaxima) {
        mostrarValidacionLoaderClose("Percepción no puede ser mayor a: " + devolverDosDecimales(percepcionMaxima));
        $('#' + importes.totalId).val(devolverDosDecimales(calculoTotal));
        $('#txt_' + id).val('');
        return false;
    }

    var suma = percepcion + calculoTotal;
    $('#' + importes.totalId).val(devolverDosDecimales(suma));

}

function onChangeCheckPercepcion() {
    if (document.getElementById('chkPercepcion').checked) {
        $('#txt_' + percepcionId).removeAttr('disabled');
    } else {
        $('#txt_' + percepcionId).val('');
        $('#' + importes.totalId).val(devolverDosDecimales(calculoTotal));
        $('#txt_' + percepcionId).attr('disabled', 'disabled');
    }
}

function obtenerPersonaDireccion(personaId) {
    //alert(personaId);
    if (personaDireccionId !== 0 || textoDireccionId !== 0) {
        ax.setAccion("obtenerPersonaDireccion");
        ax.addParamTmp("personaId", personaId);
        ax.consumir();
    }

}

function obtenerPersonaContacto(personaId) {
    //alert(personaId);
    if (personaContactoResponsableId !== 0 || personaContactoResponsableId !== 0) {
        ax.setAccion("obtenerPersonaContacto");
        ax.addParamTmp("personaId", personaId);
        ax.consumir();
    }

}

function appendForm(html) {
    $("#formularioDocumentoTipo").append(html);
}

function deshabilitarBoton() {
    $("#env").addClass('disabled');
    $("#envOpciones").addClass('disabled');
    $("#env i").removeClass(boton.enviarClase);
    $("#env i").addClass('fa fa-spinner fa-spin');
}
function habilitarBoton() {
    $("#env").removeClass('disabled');
    $("#envOpciones").removeClass('disabled');
    $("#env i").removeClass('fa-spinner fa-spin');
    $("#env i").addClass(boton.enviarClase);
}
var detalle = [];
var detalleDos = [];
var indexDetalle = 0;
function mostrarValidacionLoaderClose(mensaje) {
    mostrarAdvertencia(mensaje);
    loaderClose();
}

function confirmacionRedirecciona() {
    $('#cargarBuscadorDocumentoACopiar').removeAttr("onclick");
    if (valoresFormularioDetalle.accion === "agregar") {
        agregarConfirmado();
    } else if (valoresFormularioDetalle.accion === "editar") {
        editarConfirmado();
    }
    // asigno el importe
    asignarImporteDocumento();
    loaderClose();
}
var valoresFormularioDetalle;

function agregarConfirmado() {
    //verificar que el producto a relacionar se encuentre en la data de productos que carga el combo.
    var band = false;
    $.each(dataCofiguracionInicial.bien, function (index, itemBien) {
        if (itemBien.id == valoresFormularioDetalle.bienId) {
            band = true;
            return false;
        }
    });
    //fin verificar
    if (band) {
        var subTotal = 0;
        if (existeColumnaCodigo(12) && existeColumnaCodigo(5) && existeColumnaCodigo(6)) {
            subTotal = devolverDosDecimales(valoresFormularioDetalle.cantidad * valoresFormularioDetalle.precio);
        }
        valoresFormularioDetalle.subTotal = subTotal;
        valoresFormularioDetalle.index = indexDetalle;

        detalle.push(valoresFormularioDetalle);


        if (valoresFormularioDetalle.organizadorDesc == null) {
            valoresFormularioDetalle.organizadorDesc = '';
        }

        asignarValoresDetalleFormulario();
        indexDetalle += 1;
    }
}

var banderaCopiaDocumento = 0;
var unidadMedidaTxt = 0;
function asignarValoresDetalleFormulario() {

    banderaCopiaDocumento = 1;
    //COLUMNAS DINAMICAS

    //obtener los datos del detalle dinamico
    var dataColumna = dataCofiguracionInicial.movimientoTipoColumna;
    var valor;

    if (!isEmpty(dataColumna)) {
        $.each(dataColumna, function (index, item) {
            unidadMedidaTxt = 0;
            valor = null;

            //obtener los datos del detalle
            switch (parseInt(item.codigo)) {
                //numeros
                case 5:// PRECIO UNITARIO
                    $('#txtPrecio_' + indexDetalle).val(devolverDosDecimales(valoresFormularioDetalle.precio));
                    break;
                case 6:// SUB TOTAL
                    $('#txtSubTotalDetalle_' + indexDetalle).html(devolverDosDecimales(valoresFormularioDetalle.subTotal));
                    break;
                case 9:// PRIORIDAD
                    if (dataCofiguracionInicial.movimientoTipo[0].interfaz == 1) {
                        $('#txtPrioridad_' + indexDetalle).html(valoresFormularioDetalle.prioridad);
                    }
                case 12:// CANTIDAD
                    $('#txtCantidad_' + indexDetalle).val(devolverDosDecimales(valoresFormularioDetalle.cantidad));
                    $('#txtCantidadAprobada_' + indexDetalle).val(devolverDosDecimales(valoresFormularioDetalle.cantidad));
                    $('#txtCantidadPorAtender_' + indexDetalle).val(devolverDosDecimales(valoresFormularioDetalle.cantidad_total - valoresFormularioDetalle.cantidad_atendida));
                    // if (documentoTipoDescripcionCopia == "Solicitud requerimiento") {
                    //     $('#txtCantidad_' + indexDetalle).attr('disabled', "true");
                    // }
                    break;

                //combos, seleccion
                case 4:// TIPO PRECIO
                    select2.asignarValor("cboPrecioTipo_" + indexDetalle, valoresFormularioDetalle.precioTipoId);
                    $("#cboPrecioTipo_" + indexDetalle).select2({ width: anchoTipoPrecioTD + 'px' });
                    break;
                case 11:// PRODUCTO
                    var valorInicial = { id: valoresFormularioDetalle.bienId, text: valoresFormularioDetalle.bienDesc };
                    cargarBienDetalleCombo(dataCofiguracionInicial.bien, indexDetalle, valorInicial);
                    setearDescripcionProducto(indexDetalle);
                    setearObservacionProducto(indexDetalle);
                    if (doc_TipoId == GENERAR_COTIZACION || doc_TipoId == REQUERIMIENTO_AREA || doc_TipoId == COTIZACION_SERVICIO) {
                        $('#txtmovimiento_bien_ids_' + indexDetalle).val(valoresFormularioDetalle.movimiento_bien_ids);
                    }
                    break;
                //                case 13:// UNIDAD DE MEDIDA
                //                    obtenerUnidadMedida(valoresFormularioDetalle.bienId, indexDetalle);
                //                    break;
                case 15:// Organizador
                    select2.asignarValor("cboOrganizador_" + indexDetalle, valoresFormularioDetalle.organizadorId);
                    break;

                case 21:// COMENTARIO
                    $('#txtComentarioDetalle_' + indexDetalle).removeAttr("readonly");
                    $('#txtComentarioDetalle_' + indexDetalle).val(reducirTexto(valoresFormularioDetalle.comentarioDetalle));
                    break;
                case 22:// COMENTARIO
                    $('#txtComentarioDetalle_' + indexDetalle).removeAttr("readonly");
                    $('#txtComentarioDetalle_' + indexDetalle).val(reducirTexto(valoresFormularioDetalle.comentarioBien));
                    break;

                case 23:// AGENCIA
                    select2.asignarValor("cboAgencia_" + indexDetalle, valoresFormularioDetalle.agenciaId);
                    $("#cboAgencia_" + indexDetalle).select2({
                        width: anchoAgenciaTD + "px"
                    }).on("change", function (e) {
                        hallarSubTotalDetalle(indexDetalle);
                    });
                    break;
                case 24: // AGRUPADOR
                    $("#badge_" + indexDetalle).html(reducirTexto(valoresFormularioDetalle.agrupadorBienDec, 50));

                    if (!isEmpty(valoresFormularioDetalle.agrupadorBienDec)) {
                        var botones = document.querySelectorAll('.btn_agrupador');
                        var dtdetalle = document.querySelectorAll('.dtdetalle_agrupador');
                        $("#switchCotizacionTottus").btnSwitch("setValue", true);
                        var banderaProductoDuplicado = $("#switchCotizacionTottus").btnSwitch("getValue");
                        if (banderaProductoDuplicado) {
                            $("#id_3044").show();
                            $("#id_3045").show();
                            $("#id_3046").show();
                            $("#id_3047").show();
                            $("#id_3048").show();
                            $("#id_3049").show();
                            $("#id_3050").show();
                            $("#id_3051").show();
                            $("#id_3052").show();

                            $("#id_3044").prop("required", true);
                            $("#id_3045").prop("required", true);
                            $("#id_3046").prop("required", true);
                            $("#id_3047").prop("required", true);
                            $("#id_3048").prop("required", true);
                            $("#id_3049").prop("required", true);
                            $("#id_3050").prop("required", true);
                            $("#id_3051").prop("required", true);
                            $("#id_3052").prop("required", true);
                            $("#tb_agrupador").removeClass('hidden');
                            botones.forEach(function (boton) {
                                boton.classList.remove('hidden');
                            });
                            dtdetalle.forEach(function (boton) {
                                boton.classList.remove('hidden');
                            });
                            $("#datatable").css("width", "1700px");
                            $(".dataTables_scrollHeadInner").css("width", "1750px");
                            $(".dataTables_scrollHeadInner table").css("width", "1750px");
                        } else {
                            $("#id_3044").hide();
                            $("#id_3045").hide();
                            $("#id_3046").hide();
                            $("#id_3047").hide();
                            $("#id_3048").hide();
                            $("#id_3049").hide();
                            $("#id_3050").hide();
                            $("#id_3051").hide();
                            $("#id_3052").hide();

                            $("#id_3044").prop("required", false);
                            $("#id_3045").prop("required", false);
                            $("#id_3046").prop("required", false);
                            $("#id_3047").prop("required", false);
                            $("#id_3048").prop("required", false);
                            $("#id_3049").prop("required", false);
                            $("#id_3050").prop("required", false);
                            $("#id_3051").prop("required", false);
                            $("#id_3052").prop("required", false);
                            $("#tb_agrupador").addClass('hidden');
                            botones.forEach(function (boton) {
                                boton.classList.add('hidden');
                            });
                            dtdetalle.forEach(function (boton) {
                                boton.classList.add('hidden');
                            });
                            $("#datatable").css("width", "1369px");
                            $(".dataTables_scrollHeadInner").css("width", "1369px");
                            $(".dataTables_scrollHeadInner table").css("width", "1369px");
                        }
                    }
                    break;
                case 25:// Tikect
                    //$('#txtTicket_' + indexDetalle).removeAttr("readonly");
                    $('#txtTicket_' + indexDetalle).val(valoresFormularioDetalle.ticket);
                    break;
                case 26://CeCo
                    select2.asignarValor("cboCeCo_" + indexDetalle, valoresFormularioDetalle.CeCoId);
                    $("#cboCeCo_" + indexDetalle).select2({
                        width: anchoCeCoTD + "px"
                    });
                    break;
                case 33:
                    $("#cboCompra_" + indexDetalle).select2({
                        width: anchoCompraTD + "px"
                    });
                    select2.asignarValor("cboCompra_" + indexDetalle, 1);
                    break;
                default:
                    //DATOS DE MOVIMIENTO_BIEN_DETALLE
                    if (!isEmpty(valoresFormularioDetalle.detalle)) {
                        $.each(valoresFormularioDetalle.detalle, function (indexBD, itemBD) {
                            if (parseInt(itemBD.columnaCodigo) === parseInt(item.codigo)) {
                                valor = itemBD.valorDet;
                                switch (itemBD.columnaCodigo) {
                                    //texto
                                    case 16://descripcion de producto
                                        $('#txtProductoDescripcion_' + indexDetalle).removeAttr("readonly");
                                        $('#txtProductoDescripcion_' + indexDetalle).val(valor);
                                        break;
                                    case 17://descripcion de unidad de medida
                                        $('#txtUnidadMedidaDescripcion_' + indexDetalle).removeAttr("readonly");
                                        $('#txtUnidadMedidaDescripcion_' + indexDetalle).val(valor);
                                        if (isEmpty(valor)) {
                                            unidadMedidaTxt = 1;
                                        }
                                        break;

                                    //fechas
                                    case 18://fecha vencimiento
                                        if (!isEmpty(valor)) {
                                            valor = formatearFechaBDCadena(valor);
                                            $('#txtFechaVencimiento_' + indexDetalle).val(valor);
                                        }
                                        break;
                                }
                            }
                        });
                    } else {
                        unidadMedidaTxt = 1;
                    }
                    break;
            }
        });

        //CONSIDERANDO QUE SIEMPRE HAY UNIDAD DE MEDIDA
        //DESPUES QUE SE DIBUJO TODO LLAMAMOS A LA FUNCION onResponseObtenerUnidadesMedida , ANTES ERA CON METODO LLAMADO AL AJAX
        onResponseObtenerUnidadesMedida(valoresFormularioDetalle.dataUnidadMedida, indexDetalle);
    } else {
        mostrarValidacionLoaderClose("Falta configurar las columnas del detalle");
        return false;
    }
    //FIN COLUMNAS DINAMICAS
}

var nroFilasEliminados = 0;
function eliminarDetalleFormularioTabla(indice) {
    //$('#cboUnidadMedida_'+indice).attr('disabled', "true");
    var numItemActual = $('#txtNumItem_' + indice).html();

    $('#trDetalle_' + indice).remove();

    //LLENAR TABLA DETALLE
    var fila = llenarFilaDetalleTabla(nroFilasReducida);

    $('#datatable tbody').append(fila);

    //LLENAR COMBOS
    cargarOrganizadorDetalleCombo(dataCofiguracionInicial.organizador, nroFilasReducida);
    cargarUnidadMedidadDetalleCombo(nroFilasReducida);
    cargarBienDetalleCombo(dataCofiguracionInicial.bien, nroFilasReducida);
    cargarPrecioTipoDetalleCombo(dataCofiguracionInicial.precioTipo, nroFilasReducida);
    cargarAgenciaDetalleCombo(dataCofiguracionInicial.dataAgencia, nroFilasReducida);
    cargarCeCoDetalleCombo(dataCofiguracionInicial.centroCostoRequerimiento, nroFilasReducida);
    var compras = [{ "id": 1, "descripcion": "Si" }, { "id": 2, "descripcion": "No" }];
    cargarComprasDetalleCombo(compras, nroFilasReducida);
    inicializarFechaVencimiento(nroFilasReducida);

    nroFilasInicial++;
    nroFilasReducida++;
    nroFilasEliminados++;

    $("#contenedorDetalle").css("height", inicialAlturaDetalle + 38 * (nroFilasReducida - nroFilasEliminados));

    reenumerarFilasDetalle(indice, numItemActual);

    if (doc_TipoId == GENERAR_COTIZACION) {
        if (isEmpty(detalle)) {
            request.documentoRelacion = [];
            var dtdTipoGrupo_producto = obtenerDocumentoTipoDatoIdXTipo(44);
            $("#cbo_" + dtdTipoGrupo_producto).prop('disabled', false);
            select2.asignarValor("cbo_" + dtdTipoGrupo_producto, 0);
        }
    }
    if (doc_TipoId == REQUERIMIENTO_AREA) {
        if (isEmpty(detalle)) {
            var dtdTipoArea = obtenerDocumentoTipoDatoIdXTipo(43);
            select2.asignarValor("cbo_" + dtdTipoArea, 0);
        }
    }
}

function eliminar(index) {
    loaderShow();
    if (isEmpty(detalle) || isEmpty(index)) {
        mostrarValidacionLoaderClose("No se ha encontrado data para eliminar");
        return;
    }
    var indexTemporal = null;
    $.each(detalle, function (i, item) {
        if (parseInt(item.index) === parseInt(index)) {
            indexTemporal = i;
            return false;
        }
    });
    if (isEmpty(indexTemporal)) {
        mostrarValidacionLoaderClose("No se ha encontrado data para eliminar");
        return;
    }
    
    // valoresFormularioDetalle = validarFormularioDetalleTablas(indexTemporal);
    if (indexTemporal > -1) {
        detalle.splice(indexTemporal, 1);
        eliminarDetalleFormularioTabla(index);
        asignarImporteDocumento();
        obtenerUtilidadesGenerales();
        hallarTotales();
    }
    loaderClose();
}
function confirmarEliminar(index) {
    swal({
        title: "¿Está seguro que desea eliminar?",
        text: "Una vez eliminado tendrá que seleccionar nuevamente todo el registro si desea volver agreagarlo",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#33b86c",
        confirmButtonText: "Si, eliminar!",
        cancelButtonColor: '#d33',
        cancelButtonText: "No, cancelar!",
        closeOnConfirm: true,
        closeOnCancel: true
    }, function (isConfirm) {
        if (isConfirm) {
            eliminar(index);
        }
    });
}

function insertarComentarioBien(index) {

    //    $('.summernote').summernote({
    //        height: 200,                 // set editor height
    //        minHeight: null,             // set minimum height of editor
    //        maxHeight: null,             // set maximum height of editor
    //        focus: true                 // set focus to editable area after initializing summernote
    //    });
    //    $("#comentarioBien").code('');
    //    $("#comentarioBien").val('');

    $('#indiceComentarioBien').val(index);

    $('#divComentarioBien').html('<textarea  id="comentarioBien" class="wysihtml5 form-control" rows="9"></textarea>');
    $('.wysihtml5').wysihtml5({
        link: false,
        image: false
    });

    //SETEAR VALOR COMENTARIO
    var indexTemporal = -1;
    $.each(detalle, function (i, item) {
        if (parseInt(item.index) === parseInt(index)) {
            indexTemporal = i;
            return false;
        }
    });
    var bienId = select2.obtenerValor("cboBien_" + indexTemporal);
    if (indexTemporal != -1 && !isEmpty(bienId)) {
        if (!isEmpty(detalle[indexTemporal].comentarioBien)) {
            //            $("#comentarioBien").code(detalle[indexTemporal].comentarioBien);
            $("#comentarioBien").val(reducirTexto(detalle[indexTemporal].comentarioBien));
        } else {
            $("#comentarioBien").val("");
        }
        $('#modalComentarioBien').modal('show');

    } else {
        mostrarAdvertencia('Seleccione un producto');
        return;
    }
}

function insertarAgrupadorBien(index) {

    $('#indiceAgrupadorBien').val(index);

    //$('#divAgrupadorBien').html('<select name="cboAgrupador" id="cboAgrupador" class="select2"></select>');

    //SETEAR VALOR agrupador
    var indexTemporal = -1;
    $.each(detalle, function (i, item) {
        if (parseInt(item.index) === parseInt(index)) {
            indexTemporal = i;
            return false;
        }
    });
    var bienId = select2.obtenerValor("cboBien_" + indexTemporal);
    if (indexTemporal != -1 && !isEmpty(bienId)) {
        if (!isEmpty(detalle[indexTemporal].agrupadorId)) {
            select2.asignarValor("cboAgrupador", detalle[indexTemporal].agrupadorId);
        } else {
            select2.asignarValor("cboAgrupador", "");
        }
        $('#modalAgrupadorBien').modal('show');

    } else {
        mostrarAdvertencia('Seleccione un producto');
        return;
    }
}
function cargarPantallaListarCompra() {
    cargarDiv("#window", "vistas/com/compraServicio/compra_listar.php?tipoInterfaz=" + tipoInterfaz);
}

function enviar(accion) {
    //VALIDO QUE EL PERIODO ESTE SELECCIONADO
    var periodoId = select2.obtenerValor('cboPeriodo');
    if (isEmpty(periodoId)) {
        mostrarAdvertencia('Seleccione un periodo');
        return;
    }

    //VALIDO QUE LA FECHA DE EMISION ESTE EN EL PERIODO SELECCIONADO
    var periodoFechaEm = obtenerPeriodoIdXFechaEmision();
    if (periodoId != periodoFechaEm) {
        swal({
            title: "¿Desea continuar?",
            text: "La fecha de emisión no está en el periodo seleccionado.",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#33b86c",
            confirmButtonText: "Si!",
            cancelButtonColor: '#d33',
            cancelButtonText: "No!",
            closeOnConfirm: true,
            closeOnCancel: true
        }, function (isConfirm) {
            if (isConfirm) {
                guardar(accion);
            }
        });
        return;
    }

    if (bandera.validacionAnticipos == 1) {
        mostrarModalAnticipo();
        return;
    }

    // asignarAtencion();
    boton.accion = accion;

    var dtdTotal = obtenerDocumentoTipoDatoIdXTipo(14);
    if (!isEmpty(dtdTotal)) {
        var existeCero = false;

        $.each(detalle, function (i, item) {
            if (item.precio == 0 || isEmpty(item.precio)) {
                existeCero = true;
                return false;
            }
        });

        if (existeCero) {
            // swal({
            //     title: "¿Desea continuar?",
            //     text: "Existe precios con valor cero en el detalle del documento.",
            //     type: "warning",
            //     showCancelButton: true,
            //     confirmButtonColor: "#33b86c",
            //     confirmButtonText: "Si!",
            //     cancelButtonColor: '#d33',
            //     cancelButtonText: "No!",
            //     closeOnConfirm: true,
            //     closeOnCancel: true
            // }, function (isConfirm) {
            //     if (isConfirm) {
            //         guardar(accion);
            //     }
            // });
            mostrarAdvertencia('Existe precios con valor cero en el detalle del documento.');
            return;
        }
    }

    if (documentoTipoTipo == 1) {
        //validacion cambio de fecha emision en caso de dolares.
        if (select2.obtenerValor("cboMoneda") == 4) {
            var fechaEmision = $('#datepicker_' + fechaEmisionId).val();
            if (primeraFechaEmision != fechaEmision && !isEmpty(fechaEmision) && !isEmpty(primeraFechaEmision)) {

                //                swal("Recálculo!", "La fecha de emisión inicial: " + primeraFechaEmision + ", se cambió a: "+
                //                            fechaEmision + '. Se va a proceder a recalcular el(los) precio(s) de compra y utilidad(es).', "success");

                swal({
                    title: "Recálculo!",
                    text: "La fecha de emisión inicial: " + primeraFechaEmision + ", se cambió a: " +
                        fechaEmision + ". Se va a proceder a recalcular el(los) precio(s) de compra y utilidad(es).",
                    type: "success",
                    showCancelButton: false,
                    confirmButtonColor: "#AEDEF4",
                    confirmButtonText: "OK",
                    closeOnConfirm: true
                }, function (isConfirm) {
                    if (isConfirm) {
                        recalculoPrecioCompraUtilidades();
                        return;
                    }
                });

                return;
            }
        }

        // validacion de tramos longitud
        var unidadMedidaTipo;
        var banderaUM = true;
        $.each(detalle, function (i, item) {
            unidadMedidaTipo = obtenerUnidadMedidaTipoBien(item.bienId);

            if (unidadMedidaTipo.indexOf("Longitud") > -1 && banderaUM == true) {
                banderaUM = false;
                swal({
                    title: "¿Desea continuar sin registrar tramos?",
                    text: "Existe 1 o más detalles que tienen como tipo de unidad de medida Longitud y no se han registrado tramos.",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#33b86c",
                    confirmButtonText: "Si!",
                    cancelButtonColor: '#d33',
                    cancelButtonText: "No!",
                    closeOnConfirm: true,
                    closeOnCancel: true
                }, function (isConfirm) {
                    if (isConfirm) {
                        guardar(accion);
                    }
                });
            }
        });

        if (banderaUM == true) {
            guardar(accion);
        }

    } else {
        guardar(accion);
    }

}

function obtenerUnidadMedidaTipoBien(bienId) {
    var unidadMedidaTipo = 0;

    $.each(dataCofiguracionInicial.bien, function (index, item) {
        if (item.id == bienId) {
            unidadMedidaTipo = item.unida_medida_tipo_descripcion;
            return false;
        }
    });
    return unidadMedidaTipo;
}

function obtenerValoresCamposDinamicos() {

    var isOk = true;
    if (isEmpty(camposDinamicos))
        return false;
    $.each(camposDinamicos, function (index, item) {
        //string
        switch (item.tipo) {
            case 1:
            case 14:
            case 15:
            case 16:
            case 19:
            case 24:
            case 33:
            case 34:
            case 35:
            case 38:
                var numero = document.getElementById("txt_" + item.id).value;
                if (isEmpty(numero)) {

                    if (item.opcional == 0) {
                        mostrarValidacionLoaderClose("Debe ingresar " + item.descripcion);
                        isOk = false;
                        return false;
                    }
                } else {
                    if (!esNumero(numero)) {
                        mostrarValidacionLoaderClose("Debe ingresar un número válido para " + item.descripcion);
                        isOk = false;
                        return false;
                    }
                }
                camposDinamicos[index]["valor"] = numero;

                if(doc_TipoId == COTIZACION_SERVICIO){
                    var dtdTipoTipoTiempoEntrega = obtenerDocumentoTipoDatoIdXTipo(4);
                    var dtdTipoTipoCondicionPago = obtenerDocumentoTipoDatoIdXTipo(50);
                    var TiempoEntrega = select2.obtenerValor("cbo_" + dtdTipoTipoTiempoEntrega);
                    if(item.descripcion == "Tiempo" && TiempoEntrega == 509){
                        if (isEmpty(camposDinamicos[index]["valor"]) ||
                            camposDinamicos[index]["valor"] == 0) {
                            mostrarValidacionLoaderClose("Debe ingresar " + item.descripcion);
                            isOk = false;
                            return false;
                        }
                    }
                    var CondicionPago = select2.obtenerValor("cbo_" + dtdTipoTipoCondicionPago);
                    if(item.descripcion == "Dias de pago" && CondicionPago == 500){
                        if (isEmpty(camposDinamicos[index]["valor"]) ||
                            camposDinamicos[index]["valor"] == 0) {
                            mostrarValidacionLoaderClose("Debe ingresar " + item.descripcion);
                            isOk = false;
                            return false;
                        }
                    }
                }
                break;
            case 2:
            case 6:
            case 7:
            case 8:
            case 12:
            case 13:
                camposDinamicos[index]["valor"] = document.getElementById("txt_" + item.id).value;
                if (item.opcional == 0) {
                    //validamos
                    if (isEmpty(camposDinamicos[index]["valor"])) {
                        mostrarValidacionLoaderClose("Debe ingresar " + item.descripcion);
                        isOk = false;
                        return false;
                    }
                }
                break;
            //fechas
            case 3:
            case 9:
            case 10:
            case 11:
                if (item.codigo != '11') {
                    camposDinamicos[index]["valor"] = document.getElementById("datepicker_" + item.id).value;
                    if (item.opcional == 0) {
                        //validamos
                        if (isEmpty(camposDinamicos[index]["valor"])) {
                            mostrarValidacionLoaderClose("Debe ingresar " + item.descripcion);
                            isOk = false;
                            return false;
                        }
                    }
                } else {
                    camposDinamicos[index]["valor"] = null;
                }
                break;
            case 4:
            case 5:// persona
            case 18:// direccion persona
            case 20:// cuenta
            case 21:// actividad
            case 22:// retencion detraccion
            case 23:// otra persona
            case 26:// vendedor
            case 36:// Detraccion
            case 43:// Area
            case 44:// Grupo productos
            case 45:// Entrega en destino
            case 46:// U.O
            case 47:// Cuenta
            case 48: //Requerimientos
            case 50: //Condicion pago
            case 51: //Unidad Minera     
            case 52: //Cuentas            
                camposDinamicos[index]["valor"] = select2.obtenerValor('cbo_' + item.id);
                if (item.opcional == 0) {
                    //validamos
                    if (isEmpty(camposDinamicos[index]["valor"]) ||
                        camposDinamicos[index]["valor"] == 0) {
                        mostrarValidacionLoaderClose("Debe ingresar " + item.descripcion);
                        isOk = false;
                        return false;
                    }
                }
                break;
            case 17:// organizador
                camposDinamicos[index]["valor"] = select2.obtenerValor('cbo_' + item.id);
                if (item.opcional == 0) {
                    //validamos
                    if (isEmpty(camposDinamicos[index]["valor"]) ||
                        camposDinamicos[index]["valor"] == 0) {
                        mostrarValidacionLoaderClose("Debe ingresar " + item.descripcion);
                        isOk = false;
                        return false;
                    }
                }
                break;
            case 27:// ADJUNTO
                var objArchivo = { nombre: $('#nombreArchivo').html(), data: $('#dataArchivo').val(), contenido_archivo: contenidoArchivoJson };
                camposDinamicos[index]["valor"] = objArchivo;
                if (item.opcional == 0) {
                    //validamos
                    if (isEmpty($('#dataArchivo').val())) {
                        mostrarValidacionLoaderClose("Debe ingresar " + item.descripcion);
                        isOk = false;
                        return false;
                    }
                }
                break;
            case 31:// ADJUNTO MULTIPLE
                var objArchivo = lstDocumentoArchivos;
                camposDinamicos[index]["valor"] = objArchivo;
                if (item.opcional == 0) {
                    //validamos
                    if (isEmpty($('#dataArchivoMulti').val())) {
                        mostrarValidacionLoaderClose("Debe ingresar " + item.descripcion);
                        isOk = false;
                        return false;
                    }
                }
                break;
            case 32: // campos dinámicos
                var banderaProductoDuplicado = $("#switchProductoDuplicado").btnSwitch("getValue"); // switch activado: TRUE, switch activado: FALSE
                if (banderaProductoDuplicado) {
                    camposDinamicos[index]["valor"] = 1;
                } else {
                    camposDinamicos[index]["valor"] = 0;
                }
                break;
            case 39: // es tottus
                var banderaCotizacionTottus = $("#switchCotizacionTottus").btnSwitch("getValue"); // switch activado: TRUE, switch activado: FALSE
                if (banderaCotizacionTottus) {
                    camposDinamicos[index]["valor"] = 1;
                } else {
                    camposDinamicos[index]["valor"] = 0;
                }
                break;
            case 40: // campos tottus
                camposDinamicos[index]["valor"] = select2.obtenerValor('cbo_' + item.id);
                if (item.opcional == 0) {
                    //validamos
                    if (isEmpty(camposDinamicos[index]["valor"]) ||
                        camposDinamicos[index]["valor"] == 0) {
                        mostrarValidacionLoaderClose("Debe ingresar " + item.descripcion);
                        isOk = false;
                        return false;
                    }
                }
                break;
            case 42: // Tipo de requerimiento
                var tipo_requerimiento = select2.obtenerValor('cboTipoRequerimiento_' + item.id);
                camposDinamicos[index]["valor"] = tipo_requerimiento;

                if (doc_TipoId == SOLICITUD_REQUERIMIENTO) {
                    if (tipo_requerimiento == dtdTipoRequerimientoListaCompra) {
                        var dtdTipoClase = obtenerDocumentoTipoDatoXTipoXCodigo(4, "01");
                        var clase = select2.obtenerValor('cbo_' + dtdTipoClase.id);
                        if (isEmpty(clase)) {
                            mostrarValidacionLoaderClose("Debe ingresar " + dtdTipoClase.descripcion);
                            isOk = false;
                            return false;
                        }
                    } else if(doc_TipoId == dtdTipoRequerimientoListaServicio) {
                        var dtdTipoTipo = obtenerDocumentoTipoDatoXTipoXCodigo(4, "02");
                        var tipo = select2.obtenerValor('cbo_' + dtdTipoTipo.id);
                        if (isEmpty(tipo)) {
                            mostrarValidacionLoaderClose("Debe ingresar " + dtdTipoTipo.descripcion);
                            isOk = false;
                            return false;
                        }
                    } else if(doc_TipoId == dtdTipoRequerimientoListaConsignacion){
                        var dtdTipoProveedor = obtenerDocumentoTipoDatoIdXTipo(23);
                        var proveedor = select2.obtenerValor('cbo_' + dtdTipoProveedor);
                        if (isEmpty(proveedor)) {
                            mostrarValidacionLoaderClose("Debe ingresar " + dtdTipoProveedor);
                            isOk = false;
                            return false;
                        }
                        var dtdTipoEntregaDestino = obtenerDocumentoTipoDatoIdXTipo(45);
                        var entregaDestino = select2.obtenerValor('cbo_' + dtdTipoEntregaDestino);
                        if (isEmpty(entregaDestino)) {
                            mostrarValidacionLoaderClose("Debe ingresar " + dtdTipoEntregaDestino);
                            isOk = false;
                            return false;
                        }
                    }

                }
                break;
        }
    });
    return isOk;
}

function enviarEImprimir() {
    if (documentoTipoTipo == 1) {

        var unidadMedidaTipo;
        var bandera = true;
        $.each(detalle, function (i, item) {
            unidadMedidaTipo = obtenerUnidadMedidaTipoBien(item.bienId);

            if (unidadMedidaTipo.indexOf("Longitud") > -1 && bandera == true) {
                bandera = false;
                swal({
                    title: "¿Desea continuar sin registrar tramos?",
                    text: "Existe 1 o más detalles que tienen como tipo de unidad de medida Longitud y no se han registrado tramos.",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#33b86c",
                    confirmButtonText: "Si!",
                    cancelButtonColor: '#d33',
                    cancelButtonText: "No!",
                    closeOnConfirm: true,
                    closeOnCancel: true
                }, function (isConfirm) {
                    if (isConfirm) {
                        guardar("enviarEImprimir");
                    }
                });
            }

        });

        if (bandera == true) {
            guardar("enviarEImprimir");
        }

    } else {
        guardar("enviarEImprimir");
    }
}

// TODO: Inicio Guardar Documento - Percepción
function guardar(accion) {
    loaderShow();
    if (doc_TipoId == GENERAR_COTIZACION) {
        if (isEmpty(arrayProveedor)) {
            mostrarAdvertencia('Debe ingresar un proveedor');
            loaderClose();
            return;
        }
        var precios = [];
        var validar_precios = false;
        $.each(detalle, function (i, item) {
            arrayProveedor.forEach(function (proveedorID, idx) {
                var precioP = $('#txtPrecioP' + idx + '_' + item.index).val();
                precios.push(precioP);
            });
            // if (precios.every(x => x === "0")) {
            //     mostrarAdvertencia('Al menos se debe ingresar un precio para la fila:' + (item.index + 1));
            //     loaderClose();
            //     validar_precios = true;
            //     return;
            // }
        });
        if (validar_precios) {
            loaderClose();
            return;
        }
        var bandera_pagos = false;
        arrayProveedor.forEach(function (proveedorID, idx) {//revisar
            calcularFooterTipoCambio(proveedorID.indice);

            // if (isEmpty(listaPagoProgramacionPostores[idx]) && totalesPostores[idx].total > 0) {
            //     mostrarAdvertencia("Falta realizar la distribución de pagos para: " + select2.obtenerText("cboProveedor_" + idx));
            //     bandera_pagos = true;
            //     return;
            // } else {
            //     var total = 0;
            //     if (totalesPostores[idx].total > 0) {
            //         listaPagoProgramacionPostores[idx].forEach(function (listaPago, index) {//revisar
            //             total = total + parseFloat(listaPago[1]);
            //         });
            //         if (devolverDosDecimales(total) != devolverDosDecimales(totalesPostores[idx].total)) {

            //             mostrarValidacionLoaderClose('Total de pago no coincide con el total del documento, monto total por programar ' + formatearNumero(calculoTotal));
            //             $('#modalProgramacionPagos').modal('show');
            //             onListarPagoProgramacion(listaPagoProgramacionPostores[idx]);
            //             $('#indexProveedor').val(idx);
            //             bandera_pagos = true;
            //             return;
            //         }
            //     }
            // }

            if (isEmpty(lstDocumentoArchivos[idx])) {
                mostrarAdvertencia("Falta registrar pdf Cotización para:" + select2.obtenerText("cboProveedor_" + idx));
                bandera_pagos = true;
                return;
            }
            if(proveedorID.tiempoEntrega == 2 && isEmpty(arrayProveedor[idx].tiempo)){
                mostrarAdvertencia("Si tiempo de entrega es Días, Falta registrar tiempo para:" + select2.obtenerText("cboProveedor_" + idx));
                bandera_pagos = true;
                return;
            }
            if(proveedorID.tiempoEntrega == 2 && arrayProveedor[idx].tiempo <= 0){
                mostrarAdvertencia("Si tiempo de entrega es Días, tiempo tiene que ser mayor que 0 para:" + select2.obtenerText("cboProveedor_" + idx));
                bandera_pagos = true;
                return;
            }
            if(proveedorID.condicionPago == 2 && isEmpty(arrayProveedor[idx].diasPago)){
                mostrarAdvertencia("Si condición de pago es Crédito, Falta registrar días de pago para:" + select2.obtenerText("cboProveedor_" + idx));
                bandera_pagos = true;
                return;
            }
            if(proveedorID.condicionPago == 2 && arrayProveedor[idx].diasPago <= 0){
                mostrarAdvertencia("Si condición de pago es Crédito, días de pago tiene que ser mayo que 0 para:" + select2.obtenerText("cboProveedor_" + idx));
                bandera_pagos = true;
                return;
            }
        });
        if (bandera_pagos) {
            loaderClose();
            return;
        }
    }

    if (doc_TipoId == COTIZACION_SERVICIO) {
        if (isEmpty(request.documentoRelacion)) {
            mostrarAdvertencia("Tiene que relacionar una solicitud de requerimiento");
            loaderClose();
            return false;
        }
        var totalPago = 0;
        listaPagoProgramacion.forEach(function (item) {
            totalPago = totalPago + item[1] * 1;
        });
        if (totalPago != calculoTotal) {
            $('#txtImportePago').val(calculoTotal);
            $('#txtPorcentaje').val(100);

            mostrarValidacionLoaderClose('Total de pago no coincide con el total del documento, monto total por programar ' + formatearNumero(calculoTotal));
            $('#modalProgramacionPagos').modal('show');
            return;
        }
    }
    if (doc_TipoId == REQUERIMIENTO_AREA) {
        var dtdTipoTipoRequerimiento = obtenerDocumentoTipoDatoIdXTipo(42);
        select2.obtenerValor("cboTipoRequerimiento_" + dtdTipoTipoRequerimiento);
        if (select2.obtenerValor("cboTipoRequerimiento_" + dtdTipoTipoRequerimiento) != tipoRequerimientoTemp) {
            mostrarAdvertencia("EL tipo de requerimiento no es el mismo que selecionó al inicio");
            loaderClose();
            return false;
        }
    }



    let datosExtras = {};
    datosExtras.afecto_detraccion_retencion = null;
    datosExtras.afecto_impuesto = null;

    //obtenemos el tipo de documento
    var documentoTipoId = select2.obtenerValor("cboDocumentoTipo");
    if (isEmpty(documentoTipoId)) {
        mostrarValidacionLoaderClose("Debe ingresar tipo de documento");
        return;
    }

    var contOperacionTipoId = select2.obtenerValor("cboOperacionTipo");
    if (isEmpty(contOperacionTipoId) && !isEmpty(dataContOperacionTipo)) {
        mostrarValidacionLoaderClose("Debe seleccionar el tipo de operación");
        return;
    }

    //validamos que el total no sea negativo o cero
    // if(parseFloat($('#'+importes.totalId).val())<=0){
    // mostrarValidacionLoaderClose("Total debe ser positivo.");
    // return;
    // }

    // validamos los importes que esten llenos
    // if (!validarImportesLlenos()) {
    // return;
    // }
    //Validar y obtener valores de los campos dinamicos
    if (!obtenerValoresCamposDinamicos())
        return;
    let dataDocumentoTipoSeleccionado = obtenerDocumentoTipoSeleccionado();
    if (dataDocumentoTipoSeleccionado.id != "270") {
        if (!validarDetalleFormularioLlenos()) {
            return;
        }
    }

    if (isEmpty(detalle)) {
        mostrarAdvertencia("Falta ingresar datos en el detalle.");
        loaderClose();
        return;
    }

    obtenerCheckDocumentoACopiar();

    var banderaProductoDuplicado = $("#switchProductoDuplicado").btnSwitch("getValue"); // switch activado: TRUE, switch activado: FALSE
    if (!banderaProductoDuplicado) {
        if (validarDetalleRepetido()) {
            mostrarValidacionLoaderClose("Detalle repetido, seleccione otro bien, organizador o unidad de medida.");
            return;
        }
    }

    var checkIgv = 0;

    if (!isEmpty(importes.subTotalId)) {
        if (document.getElementById('chkIncluyeIGV').checked) {
            checkIgv = 1;
        }
    } else {
        checkIgv = opcionIGV;
    }

    if ($("#chkIGV").length > 0 && document.getElementById('chkIGV').checked) {
        datosExtras.afecto_impuesto = 1;
    }

    //Calculamos la detracción
    var dtdTipoDetraccion = obtenerDocumentoTipoDatoIdXTipo(36);
    if (!isEmpty(dtdTipoDetraccion) && igvValor > 0) {
        if (select2.obtenerValor("cbo_" + dtdTipoDetraccion) * 1 > 0) {
            let dataDetraccion = dataCofiguracionInicial.dataDetraccion.filter(item => item.id == select2.obtenerValor("cbo_" + dtdTipoDetraccion));
            datosExtras.afecto_detraccion_retencion = 1;
            datosExtras.porcentaje_afecto = dataDetraccion[0]['porcentaje'];
            datosExtras.monto_detraccion_retencion = montoTotalDetraido;
        }
    }

    //Calculamos la retención
    var dtdTipoRetencion = obtenerDocumentoTipoDatoXTipoXCodigo(4, "10");
    if (!isEmpty(dtdTipoRetencion) && igvValor > 0) {
        var valorRetencion = dtdTipoRetencion.data.filter(item => item.id == select2.obtenerValor("cbo_" + dtdTipoRetencion.id));
        if (!isEmpty(valorRetencion) && valorRetencion[0]['valor'] == 1) {
            datosExtras.afecto_detraccion_retencion = 2;
            datosExtras.porcentaje_afecto = dataCofiguracionInicial.dataRetencion.porcentaje;
            datosExtras.monto_detraccion_retencion = montoTotalRetencion;
        }
    }

    if (montoTotalDetraido > 0 && montoTotalRetencion > 0) {
        mostrarValidacionLoaderClose('El documento no puede estar afecto a detracción retención al mismo tiempo.');
        return;
    }

    if ((montoTotalDetraido > 0 || montoTotalRetencion > 0) && igvValor == 0) {
        mostrarValidacionLoaderClose('El documento no esta afecto a IGV, por lo tanto no puede estar afecto a retención o detracción.');
        return;
    }

    var tipoPago = null;

    var dtdTipoPago = obtenerDocumentoTipoDatoIdXTipo(25);
    if (!isEmpty(dtdTipoPago)) {
        tipoPago = select2.obtenerValor("cboTipoPago");

        //validando el total pago = total documento
        if (tipoPago == 2) {
            var totalPago = 0;
            listaPagoProgramacion.forEach(function (item) {
                totalPago = totalPago + item[1] * 1;
            });
            if (totalPago != calculoTotal) {
                mostrarValidacionLoaderClose('Total de pago no coincide con el total del documento, monto total por programar ' + formatearNumero(calculoTotal));
                return;
            }
        }
    }

    if (tipoPago != 2 && !isEmpty(tipoPago)) {
        listaPagoProgramacion = [];
    }

    var dtdOrganizadorId = obtenerDocumentoTipoDatoIdXTipo(17);
    if (!isEmpty(dtdOrganizadorId)) {
        var organizadorOrigen = select2.obtenerValor('cboOrganizador');
        var organizadorDestino = select2.obtenerValor('cbo_' + dtdOrganizadorId);

        if (organizadorOrigen == organizadorDestino) {
            mostrarValidacionLoaderClose('Seleccione un almacén de destino diferente al almacén de origen');
            return;
        }
    }

    var periodoId = select2.obtenerValor('cboPeriodo');

    if (dataCofiguracionInicial.movimientoTipo[0]["codigo"] == 20) { // validar transferencia interna
        validarAlmacenOrigenDestino();

        var bandValidacionTrans = true;
        var dtdMovimientoTipo = obtenerDocumentoTipoDatoXTipoXCodigo(4, 4);
        var codMotivo = dtdMovimientoTipo.data[document.getElementById('cbo_' + id_cboMotivoMov).options.selectedIndex]['valor'];

        // var indMotivo = (select2.obtenerText('cbo_' + id_cboMotivoMov)).indexOf('|');
        // var codMotivo = (select2.obtenerText('cbo_' + id_cboMotivoMov)).substr(0, indMotivo);

        switch (codMotivo * 1) {
            case 1:
                if (origen_destino != 'O') {
                    mostrarValidacionLoaderClose('Debe seleccionar el almacén virtual como almacén de origen o cambie el motivo de movimiento');
                    bandValidacionTrans = false;
                }
                break;
            case 2:
                if (origen_destino != 'D') {
                    mostrarValidacionLoaderClose('Debe seleccionar el almacén virtual como almacén de llegada o cambie el motivo de movimiento');
                    bandValidacionTrans = false;
                }
                break;
            case 3:
                if (origen_destino != null) {
                    mostrarValidacionLoaderClose('El almacén virtual no debe estar seleccionado como origen o llegada');
                    bandValidacionTrans = false;
                }
                break;
        }

        if (!bandValidacionTrans) {
            return;
        }
    }

    let percepcion = null;

    if (document.getElementById('chkPercepcion').checked) {
        const percepcionValue = document.querySelector('#contenedorPercepcion > input').value;
        console.log(typeof percepcionValue)

        if (percepcionValue !== '') {
            percepcion = parseFloat(percepcionValue).toFixed(2);

            if (percepcion === "NaN") {
                mostrarValidacionLoaderClose('La percepcion debe ser un valor numerico');
                return;
            }
        } else {
            mostrarValidacionLoaderClose('Debe ingresar el monto de la percepcion');
            return;
        }
    }

    if (doc_TipoId == GENERAR_COTIZACION) {
        detalle = detalle.filter(item => item.postor_ganador_id != null && item.postor_ganador_id !== "");
    }

    // if (dataCofiguracionInicial.movimientoTipo[0]["codigo"] == 20) {// validar transferencia interna
    //   if (validarDetalleFormularioContenidoEnDocumentoRelacion()) {
    //     mostrarValidacionLoaderClose("El detalle del formulario debe estar contenido en el detalle del documento relacionado");
    //     return;
    //   }
    // }
    var igv_porcentaje = '';
    if (documentoTipoId == "9") {
        igv_porcentaje = select2.obtenerValor("cboIgv");
    }
    obtenerDistribucion();
    deshabilitarBoton();
    ax.setAccion('enviar');
    ax.addParamTmp("documentoTipoId", documentoTipoId);
    ax.addParamTmp("contOperacionTipoId", contOperacionTipoId);
    ax.addParamTmp("camposDinamicos", camposDinamicos);
    ax.addParamTmp("detalle", detalle);
    ax.addParamTmp("documentoARelacionar", request.documentoRelacion);
    ax.addParamTmp("valor_check", cabecera.chkDocumentoRelacion);
    ax.addParamTmp("comentario", $('#txtComentario').val());
    ax.addParamTmp("checkIgv", checkIgv);
    ax.addParamTmp("igv_porcentaje", igv_porcentaje);
    ax.addParamTmp("monedaId", select2.obtenerValor("cboMoneda"));
    ax.addParamTmp("accionEnvio", accion);
    bandera.validacionAnticipos = (bandera.validacionAnticipos > 0) ? 1 : 0;
    ax.addParamTmp("anticiposAAplicar",
        {
            validacion: bandera.validacionAnticipos,
            empresaId: commonVars.empresa,
            data: obtenerAnticiposAAplicar()
        });
    // actividadId: ((bandera.validacionAnticipos == 1)?select2.obtenerValor("cboAnticipoActividad"):null)
    // gclv: agregando el campo de tipo de pago en base al combo de la vista
    ax.addParamTmp("tipoPago", tipoPago);
    ax.addParamTmp("listaPagoProgramacion", listaPagoProgramacion);
    ax.addParamTmp("periodoId", periodoId);
    ax.addParamTmp("origen_destino", origen_destino);
    ax.addParamTmp("detalleDistribucion", dataDistribucion);
    ax.addParamTmp("importeTotalInafectas", importeTotalInafectas);
    ax.addParamTmp("distribucionObligatoria", distribucionObligatoria);
    ax.addParamTmp("datosExtras", datosExtras);
    ax.addParamTmp("percepcion", percepcion);
    ax.addParamTmp("dataStockReservaOk", dataStockReservaOk);
    ax.addParamTmp("dataPostorProveedor", arrayProveedor);
    ax.addParamTmp("listaPagoProgramacionPostores", listaPagoProgramacionPostores);

    ax.consumir();
}
// TODO: Fin Guardar Documento - Percepcion

function validarDetalleFormularioDocumentoRelacionIdentico() {
    //SOLO VALIDA CUANDO SE COPIA CON DETALLE
    var bandera = false;
    var df;
    var dr;

    if (!isEmpty(detalleDocumentoRelacion)) {

        if (detalle.length <= detalleDocumentoRelacion.length) {
            df = detalle;
            dr = detalleDocumentoRelacion;
        } else {
            df = detalleDocumentoRelacion;
            dr = detalle;
        }

        for (var i = 0; i < dr.length; i++) {
            var bandera2 = false;
            for (var j = 0; j < df.length; j++) {
                //FALTA CORREGIR LAS VARIABLES CUANDO INTERCAMBIA DF X DR
                if (dr[i].bien_id == df[j].bienId && formatearCantidad(dr[i].cantidad) == formatearCantidad(df[j].cantidad) && dr[i].unidad_medida_id == df[j].unidadMedidaId) {
                    bandera2 = true;
                    break;
                }
            }
            if (!bandera2) {
                bandera = true;
                break;
            }
        }
    }

    return bandera;
}

function validarDetalleFormularioContenidoEnDocumentoRelacion() {
    //SOLO VALIDA CUANDO SE COPIA CON DETALLE
    var bandera = false;//NO HAY ERRORES
    var df;
    var dr;

    if (!isEmpty(detalleDocumentoRelacion)) {
        df = detalle;
        dr = detalleDocumentoRelacion;

        for (var i = 0; i < df.length; i++) {
            var bandera2 = false;
            for (var j = 0; j < dr.length; j++) {
                if (df[i].bienId == dr[j].bien_id && df[i].cantidad * 1 <= dr[j].cantidad * 1 && df[i].unidadMedidaId == dr[j].unidad_medida_id) {
                    bandera2 = true;
                    break;
                }
            }
            if (!bandera2) {
                bandera = true;
                break;
            }
        }
    }

    return bandera;
}

function validarDetalleFormularioLlenos() {
    var valido = true;
    var validar_postor = false;

    $.each(detalle, function (i, item) {
        actualizarTotalesGenerales(item.index);
        //validamos que este seleccionado el tipo de precio
        if (existeColumnaCodigo(4)) {
            if (isEmpty(item.precioTipoId) || item.precioTipoId == 0) {
                mostrarValidacionLoaderClose("Seleccione el tipo de precio");
                valido = false;
                return false;
            }
        }
        if (existeColumnaCodigo(12)) {
            if (doc_TipoId == REQUERIMIENTO_AREA) {
                if (isEmpty(item.cantidad)) {
                    mostrarValidacionLoaderClose("No se especificó un valor válido para Cantidad");
                    valido = false;
                    return false;
                }
            } else {
                if (isEmpty(item.cantidad) || item.cantidad <= 0) {
                    mostrarValidacionLoaderClose("No se especificó un valor válido para Cantidad");
                    valido = false;
                    return false;
                }
            }
        }

        //        //validamos que este seleccionado alguna agencia
        //        if (existeColumnaCodigo(23)) {
        //            if (isEmpty(item.agenciaId) || item.agenciaId == 0) {
        //                mostrarValidacionLoaderClose("Seleccione el tipo de precio");
        //                valido = false;
        //                return false;
        //            }
        //        }

        if (existeColumnaCodigo(24)) {
            var checkbox = document.getElementById("android-switchCotizacionTottus");
            if (checkbox.checked) {
                if (isEmpty(item.agrupadorId) || item.agrupadorId <= 0) {
                    mostrarValidacionLoaderClose("No se especificó un valor válido para el Agrupador");
                    valido = false;
                    return false;
                }
            }
        }

        if (existeColumnaCodigo(26)) {
            if (isEmpty(item.CeCoId) || item.CeCoId == 0) {
                mostrarValidacionLoaderClose("Seleccione el Centro de Costos");
                valido = false;
                return false;
            }
        }

        if (existeColumnaCodigo(33)) {
            if (isEmpty(item.esCompra) || item.esCompra == 0) {
                mostrarValidacionLoaderClose("Seleccione Si es compras o No, para la fila: " + (item.index + 1));
                valido = false;
                return false;
            }
            var cantidadAceptada_ = parseInt(item.cantidad);
            var cantidad_ = parseInt(item.cantidadAceptada);
            var stockBien_ = parseInt(item.stockBien);

            if (item.esCompra == 1) {
                var dataStockReservaBien = dataStockReservaOk.find(itemReversa => itemReversa.bien_id == item.bienId);
                if (!isEmpty(dataStockReservaBien)) {
                    // if (cantidad_ == cantidadAceptada_) {
                    //     dataStockReservaOk = dataStockReservaOk.filter(objeto => objeto.bien_id != item.bienId);
                    //     valido = false;
                    //     return false;
                    // } else if (cantidad_ < (cantidadAceptada_ - stockBien_)) {
                    //     mostrarValidacionLoaderClose("La catindad aceptada tiene que ser igual o mayor a la resta entre (Cant. solicitada - stock), para la fila: " + (item.index + 1));
                    //     valido = false;
                    //     return false;
                    // } else if ((cantidad_ + (parseInt(dataStockReservaBien.reserva))) > item.stockBien && stockBien_ > 0 && (cantidad_ + (parseInt(dataStockReservaBien.reserva)) != cantidadAceptada_)) {
                    //     dataStockReservaOk = dataStockReservaOk.filter(objeto => objeto.bien_id != item.bienId);
                    //     mostrarValidacionLoaderClose("Necesitas realizar una reserva de stock, para la fila: " + (item.index + 1));
                    //     reservarStockBien(item.index);
                    //     valido = false;
                    //     return false;
                    // } else if ((parseInt(dataStockReservaBien.reserva) + cantidad_) != cantidadAceptada_ && cantidad_ > 0) {
                    //     dataStockReservaOk = dataStockReservaOk.filter(objeto => objeto.bien_id != item.bienId);
                    //     mostrarValidacionLoaderClose("Se ha cambiado la cantidad aceptada, tiene que actualizar el monto reservado, para la fila: " + (item.index + 1));
                    //     reservarStockBien(item.index);
                    //     valido = false;
                    //     return false;
                    // } else if (cantidad_ == 0) {
                    //     dataStockReservaOk = dataStockReservaOk.filter(objeto => objeto.bien_id != item.bienId);
                    //     mostrarValidacionLoaderClose("La cantidad aceptada tiene que ser mayo que 0, tiene que actualizar el monto reservado, para la fila: " + (item.index + 1));
                    //     valido = false;
                    // }
                } else {
                    if (cantidadAceptada_ < 1) {
                        mostrarValidacionLoaderClose("La cantidad aceptada tiene que ser mayor a 1, para la fila: " + (item.index + 1));
                        valido = false;
                        return false;
                    }
                    if (cantidadAceptada_ > cantidad_) {
                        mostrarValidacionLoaderClose("La cantidad aceptada tiene que ser menor o igual a la cantidad solicitada, para la fila: " + (item.index + 1));
                        valido = false;
                        return false;
                    }
                    // if (cantidad_ < (cantidadAceptada_ - stockBien_)) {
                    //     mostrarValidacionLoaderClose("La cantidad aceptada tiene que ser igual o mayor a la resta entre (Cant. solicitada - stock), para la fila: " + (item.index + 1));
                    //     valido = false;
                    //     return false;
                    // } else if (cantidad_ > cantidadAceptada_) {
                    //     mostrarValidacionLoaderClose("La cantidad aceptada tiene que ser igual a la solicitada, para la fila: " + (item.index + 1));
                    //     valido = false;
                    //     return false;
                    // } else if (cantidad_ < cantidadAceptada_ && cantidad_ > 1) {
                    //     mostrarValidacionLoaderClose("Necesitas realizar una reserva de stock, para la fila: " + (item.index + 1));
                    //     reservarStockBien(item.index);
                    //     valido = false;
                    //     return false;
                    // } else if (cantidad_ == 0) {
                    //     mostrarValidacionLoaderClose("Necesitas realizar una reserva de stock, para la fila: " + (item.index + 1));
                    //     valido = false;
                    //     return false;
                    // }
                }

            }

            if (item.esCompra == 2) {
                if (!isEmpty(dataStockReservaOk)) {
                    var dataStockReservaBien = dataStockReservaOk.find(itemReversa => itemReversa.bien_id == item.bienId);
                    if (isEmpty(dataStockReservaBien)) {
                        mostrarValidacionLoaderClose("No se ha realizado la reserva de stock, para la fila: " + (item.index + 1));
                        reservarStockBien(item.index);
                        valido = false;
                        return false;
                    }
                    // if(cantidad_ == 0 && (parseInt(dataStockReservaBien.reserva) + cantidad_)!= cantidadAceptada_){
                    //     mostrarValidacionLoaderClose("No se ha realizado la reserva de stock, para la fila: "+  (item.index + 1));
                    //     reservarStockBien(item.index);
                    //     valido = false;
                    //     return false;
                    // }
                } else {
                    if (cantidad_ == 0) {
                        mostrarValidacionLoaderClose("No se ha realizado la reserva de stock, para la fila: " + (item.index + 1));
                        reservarStockBien(item.index);
                        valido = false;
                        return false;
                    }
                }
                if (cantidad_ != 0) {
                    mostrarValidacionLoaderClose("Cantidad aceptada tiene que ser 0, para la fila: " + (item.index + 1));
                    valido = false;
                    return false;
                }
                if (cantidad_ == cantidadAceptada_ && cantidad_ > stockBien_) {
                    mostrarValidacionLoaderClose("Cantidad aceptada tiene que ser igual a la solicitada y menor al stock actual, para la fila: " + (item.index + 1));
                    valido = false;
                    return false;
                }
            }

        }

    });

    return valido;
}

function validarDetalleRepetido() {
    var detalleRepetido = false;

    for (var i = 0; i < detalle.length; i++) {
        for (var j = i + 1; j < detalle.length; j++) {
            if (detalle[i]["bienId"] === detalle[j]["bienId"] && detalle[i]["organizadorId"] === detalle[j]["organizadorId"] && detalle[i]["unidadMedidaId"] === detalle[j]["unidadMedidaId"]) {
                detalleRepetido = true;
                break;
            }
        }
    }

    return detalleRepetido;
}

function cargarPersona() {
    var rutaAbsoluta = URL_BASE + 'index.php?token=1';
    window.open(rutaAbsoluta, "nuevo", "directories=no, location=no, menubar=no, scrollbars=yes, statusbar=no, tittlebar=no, width=1500, height=900");
    //    var win = window.open(rutaAbsoluta, '_blank');
    //    win.focus();
}

function actualizarCboPersona() {
    obtenerPersonas();
}

function obtenerPersonas() {
    var documneto_tipo = document.getElementById('cboDocumentoTipo').value;
    ax.setAccion("obtenerPersonas");
    ax.addParamTmp("documentoTipoId", documneto_tipo);
    ax.consumir();
}
function onResponseObtenerPersonas(data) {
    $("#div_persona").empty();
    var header = '';
    var string = '';
    var footer = '';
    var html = '';

    $.each(data, function (index, item) {
        switch (parseInt(item.tipo)) {
            case 5:
                header = '<select name="cbo_' + item.id + '" id="cbo_' + item.id + '" class="select2">';
                string = '<option value="' + 0 + '">Seleccione ' + item.descripcion.toLowerCase() + '</option>';
                $.each(item.data, function (indexPersona, itemPersona) {
                    string += '<option value="' + itemPersona.id + '">' + itemPersona.nombre + ' | ' + itemPersona.codigo_identificacion + '</option>';
                });
                footer = '</select>';
                html = header + string + footer;
                $("#div_persona").append(html);
                break;
            case 23:
                $("#div_persona_" + item.id).empty();
                header = '<div id ="div_persona_' + item.id + '" ><select name="cbo_' + item.id + '" id="cbo_' + item.id + '" class="select2">';
                string = '<option value="' + 0 + '">Seleccione ' + item.descripcion.toLowerCase() + '</option>';
                $.each(item.data, function (indexPersona, itemPersona) {
                    string += '<option value="' + itemPersona.id + '">' + itemPersona.nombre + ' | ' + itemPersona.codigo_identificacion + '</option>';
                });
                footer = '</select>';
                html = header + string + footer;
                $("#div_persona_" + item.id).append(html);
                break;
        }

        switch (parseInt(item.tipo)) {
            case 5:
                $("#cbo_" + item.id).select2({
                    width: '100%'
                }).on("change", function (e) {
                    obtenerPersonaDireccion(e.val);
                    obtenerPersonaContacto(e.val);
                });

                if (!isEmpty(personaIdRegistro)) {
                    select2.asignarValor("cbo_" + item.id, personaIdRegistro);
                }
                break;
            case 23:
                $("#cbo_" + item.id).select2({
                    width: '100%'
                }).on("change", function (e) {
                });
                break;
        }
    });

}
function onResponseLoaderBien(data) {
    if (!isEmpty(data)) {
        select2.recargar("cboBien", data, "id", ["codigo", "descripcion"]);
        select2.asignarValor("cboBien", 0);
    }
}
function cargarBien() {
    var rutaAbsoluta = URL_BASE + 'index.php?token=2';
    //    var win = window.open(rutaAbsoluta, '_blank');
    //    win.focus();
    window.open(rutaAbsoluta, "nuevo", "directories=no, location=no, menubar=no, scrollbars=yes, statusbar=no, tittlebar=no, width=1500, height=900");
}

function verificarStockBien(indice) {
    modalReserva = 0;
    var bienId = select2.obtenerValor("cboBien_" + indice);
    if (bienId == null) {
        mostrarAdvertencia("Seleccionar un producto");
    } else {
        obtenerStockPorBien(bienId, indice);
    }
}
function obtenerStockPorBien(bienId, indice) {

    ax.setAccion("obtenerStockPorBien");
    ax.addParamTmp("bienId", bienId);
    ax.setTag(indice);
    ax.consumir();
}
function onResponseObtenerStockPorBien(dataStock, indice) {
    var tituloModal = '<strong>' + select2.obtenerText("cboBien_" + indice) + '</strong>';
    $('.modal-title').empty();
    $('.modal-title').append(tituloModal);

    var data = [];

    if (!isEmpty(dataStock)) {
        $.each(dataStock, function (i, item) {
            if (item.stock != 0) {
                data.push(item);
            }
        });
    }

    if (!isEmptyData(data)) {
        $('#datatableStock').dataTable({
            order: [[0, "desc"]],
            "ordering": false,
            "data": data,
            "columns": [
                { "data": "organizador_descripcion" },
                { "data": "unidad_medida_descripcion" },
                { "data": "stock", "sClass": "alignRight" }
            ],
            columnDefs: [
                {
                    "render": function (data, type, row) {
                        return parseFloat(data).formatMoney(2, '.', ',');
                    },
                    "targets": 2
                }
            ],
            "destroy": true
        });
    } else {
        var table = $('#datatableStock').DataTable();
        table.clear().draw();
    }

    $('#modalStockBien').modal('show');
}

// TODO: Inicio Venta Gratuita
var importeTotalInafectas;
function calcularImporteDetalle() {
    var importe = 0;
    // var importeInafectas=0;

    var importeInafectas = 0;
    if (dataCofiguracionInicial.movimientoTipo[0]["codigo"] == "18") {
        if (!isEmpty(detalle)) {
            $.each(detalle, function (index, item) {
                importe += (parseFloat(item.cantidad) * parseFloat(item.precio));
            });
            if (movimientoTipoIndicador == 5) {
                importeInafectas = importe;
            }
        }
        importe = 0;
    }
    importeTotalInafectas = importeInafectas;
    if (movimientoTipoIndicador != 5) {
        if (!isEmpty(detalle)) {
            $.each(detalle, function (index, item) {
                importe += (parseFloat(item.cantidad) * parseFloat(item.precio));
            });
        }
    }
    // else {
    // if (!isEmpty(detalle)) {
    //   $.each(detalle, function (index, item) {
    //     importeInafectas += (parseFloat(item.cantidad) * parseFloat(item.precio));
    //   });
    // }
    // importeTotalInafectas = importeInafectas;
    // }
    return importe;
}
// TODO: End Venta Gratuita

var igvValor = 0.18;
var calculoTotal = 0;
function asignarImporteDocumento() {
    if (select2.obtenerValor("cboDocumentoTipo") != "9") {
        $("#contenedorIgvPorcentajeDiv").hide();
    } else {
        $("#contenedorIgvPorcentajeDiv").show();
    }
    var idIgv = select2.obtenerValor("cboIgv");
    if (document.getElementById("chkIGV").checked) {
        igvValor = (idIgv == "10" ? 0.10 : 0.18);
    }
    var factorImpuesto = 1;
    var documentoTipo = dataCofiguracionInicial.documento_tipo.filter(item => item.id == $("#cboDocumentoTipo").val());
    if (!isEmpty(documentoTipo) && documentoTipo[0]['identificador_negocio'] == 30) {
        factorImpuesto = -1;
    }

    var calculo, igv;
    validarImporteLlenar();
    if (!isEmpty(importes.calculoId)) {
        calculo = calcularImporteDetalle();
        if (!isEmpty(importes.fleteId) && !isEmpty(importes.seguroId)) {
            var importe_seguro = parseFloat($('#' + importes.seguroId).val());
            var importe_flete = parseFloat($('#' + importes.fleteId).val());
            calculo = calculo + importe_seguro + importe_flete;
        }

        var importe_exoneracion = 0;
        var importe_otro = 0;
        var icbp = 0;
        if (!isEmpty(importes.otrosId)) {
            importe_otro = parseFloat($('#' + importes.otrosId).val());
        }

        if (!isEmpty(importes.exoneracionId)) {
            importe_exoneracion = parseFloat($('#' + importes.exoneracionId).val());
        }

        if (!isEmpty(importes.icbpId)) {
            icbp = parseFloat($('#' + importes.icbpId).val());
        }
        document.getElementById(importes.calculoId).value = devolverDosDecimales(calculo);
        if (importes.calculoId === importes.subTotalId) {
            if (!isEmpty(importes.igvId)) {
                igv = igvValor * calculo;
                document.getElementById(importes.igvId).value = devolverDosDecimales(igv);
            }
            if (!isEmpty(importes.totalId)) {
                document.getElementById(importes.totalId).value = devolverDosDecimales(calculo + (factorImpuesto * igv) + importe_exoneracion + importe_otro + icbp);
            }
        } else if (importes.calculoId === importes.totalId) {
            if (!isEmpty(importes.igvId)) {
                igv = (calculo - calculo / (1 + igvValor));
                document.getElementById(importes.igvId).value = devolverDosDecimales(igv);
            }
            if (!isEmpty(importes.subTotalId)) {
                document.getElementById(importes.subTotalId).value = devolverDosDecimales(calculo - igv);
            }
        }
        calculoTotal = parseFloat($('#' + importes.totalId).val());
        //Calculamos la detracción
        var dtdTipoPago = obtenerDocumentoTipoDatoIdXTipo(36);
        if (!isEmpty(dtdTipoPago)) {
            obtenerMontoDetraccion();
        }

        //Calculamos la retención
        var dtdTipoPagoRetencion = obtenerDocumentoTipoDatoXTipoXCodigo(4, "10");
        if (!isEmpty(dtdTipoPagoRetencion)) {
            obtenerMontoRetencion();
        }
    }

    //recalcular los importes de pago.
    asignarImportePago();
}
function validarImportesLlenos() {
    //    breakFunction();
    validarImporteLlenar();
    if (!isEmpty(importes.calculoId)) {
        var importeFinal = document.getElementById(importes.calculoId).value;
        if (isEmpty(importeFinal)) {
            asignarImporteDocumento();
            importeFinal = document.getElementById(importes.calculoId).value;
        }
        var importeFinalSugerido = calcularImporteDetalle();
        var valorPercepcion = 0;
        if (percepcionId != 0) {
            valorPercepcion = $('#txt_' + percepcionId).val();
            if (isEmpty(valorPercepcion)) {
                valorPercepcion = 0;
            }
        }
        if (Math.abs(parseFloat(importeFinalSugerido) + parseFloat(valorPercepcion) - parseFloat(importeFinal)) > 1) {
            mostrarValidacionLoaderClose("El importe total tiene mucha variación con el cálculado por el sistema. No se puede continuar la operación.");
            return false;
        }
    }
    return true;
}
function onChangeCheckIncluyeIGV() {
    modificarDetallePrecios();
    validarImporteLlenar();
    asignarImporteDocumento();
}
function validarImporteLlenar() {
    if (!isEmpty(importes.subTotalId)) {
        if (document.getElementById('chkIncluyeIGV').checked) {
            importes.calculoId = importes.totalId;
        } else {
            importes.calculoId = importes.subTotalId;
        }
    } else {
        importes.calculoId = importes.totalId;
    }
}

function verificarPrecioBien(indice) {
    indiceVerificarPrecioBien = indice;
    var bienId = select2.obtenerValor("cboBien_" + indice);
    if (bienId == null) {
        $.Notification.autoHideNotify('warning', 'top right', 'Validación', "Seleccionar un producto");
    } else {
        obtenerPrecioPorBien(bienId, indice);
    }
}

function obtenerPrecioPorBien(bienId, indice) {
    var incluyeIGV = 1;
    if (!document.getElementById('chkIncluyeIGV').checked) {
        incluyeIGV = 0;
    }

    ax.setAccion("obtenerPrecioPorBien");
    ax.addParamTmp("bienId", bienId);
    ax.addParamTmp("unidadMedidaId", select2.obtenerValor("cboUnidadMedida_" + indiceVerificarPrecioBien));
    ax.addParamTmp("monedaId", select2.obtenerValor("cboMoneda"));
    ax.addParamTmp("incluyeIGV", incluyeIGV);
    ax.setTag(indice);

    ax.consumir();
}

function cambiarAPrecioMinimo(indice, precioTipoId, precioMinimo) {
    if (existeColumnaCodigo(5)) {
        document.getElementById("txtPrecio_" + indice).value = devolverDosDecimales(precioMinimo);
    }
    if (existeColumnaCodigo(4)) {
        select2.asignarValor("cboPrecioTipo_" + indice, precioTipoId);
        $("#cboPrecioTipo_" + indice).select2({ width: anchoTipoPrecioTD + 'px' });
    }
    $('#modalPrecioBien').modal('hide');

    hallarSubTotalDetalle(indice);
}

function onResponseObtenerPrecioPorBien(data, indice) {
    var tituloModal = '<strong>' + select2.obtenerText("cboBien_" + indice) + '</strong>';
    var dataPrecioBien = [];
    if (!isEmpty(data) && existeColumnaCodigo(12)) {
        var descuentoPorcentaje = 0;
        var precioMinimo = 0;
        var accion = '';

        var operador = obtenerOperador();

        $.each(data, function (index, item) {
            //calculo de utilidad porcentaje
            var precioVenta = item.precio * operador;
            //            var precioCompra = $('#txtPrecioCompra_' + indiceVerificarPrecioBien).html();
            var precioCompra = $('#txtPrecioCompra_' + indice).html();

            //------------------------
            //ASIGNO EL VALOR DE COMPRA EN CASO NO ESTE CONFIGURADA LA COLUMNA COMPRA
            if (!existeColumnaCodigo(1)) {
                var indexTemporal = -1;
                $.each(detalle, function (i, item) {
                    if (parseInt(item.index) === parseInt(indice)) {
                        indexTemporal = i;
                        return false;
                    }
                });

                if (indexTemporal > -1) {
                    if (!isEmpty(detalle[indexTemporal].precioCompra)) {
                        precioCompra = detalle[indexTemporal].precioCompra;
                    }
                }
            }
            //-----------------------


            var cantidad = $('#txtCantidad_' + indice).val();

            var utilidadSoles = (precioVenta - precioCompra) * cantidad;

            var subTotal = precioVenta * cantidad;
            var utilidadPorcentaje = 0;
            if (subTotal != 0) {
                utilidadPorcentaje = (utilidadSoles / subTotal) * 100;
            }

            //$('#txtUtilidadPorcentaje_' + indice).html(devolverDosDecimales(utilidadPorcentaje) + " %");
            // fin calculo

            descuentoPorcentaje = (parseFloat(item.descuento) / 100) * parseFloat(utilidadPorcentaje);
            precioMinimo = parseFloat(precioVenta) - (descuentoPorcentaje / 100) * parseFloat(precioVenta);

            if (precioMinimo < precioCompra) {
                precioMinimo = precioCompra + 0.1;
            }

            accion = "<a onclick=\"cambiarAPrecioMinimo(" + indice + "," + item.precio_tipo_id + "," + precioMinimo + ");\">" +
                "<i class=\"fa fa-arrow-down\"  tooltip-btndata-toggle='tooltip'  style=\"color:#04B404;\" title=\"Seleccionar precio mínimo\"></i></a>";

            dataPrecioBien.push([item.precio_tipo_descripcion,
                precioVenta,
                utilidadPorcentaje,
                descuentoPorcentaje,
                precioMinimo,
                accion]);

        });
    }

    $('#datatablePrecio').dataTable({
        order: [[0, "desc"]],
        "ordering": false,
        "data": dataPrecioBien,
        "columns": [
            { "data": "0" },
            { "data": "1", "sClass": "alignRight" },
            { "data": "2", "sClass": "alignRight" },
            { "data": "3", "sClass": "alignRight" },
            { "data": "4", "sClass": "alignRight" },
            { "data": "5", "sClass": "alignCenter" }
        ],
        columnDefs: [
            {
                "render": function (data, type, row) {
                    return parseFloat(data).formatMoney(2, '.', ',');
                },
                "targets": [1, 2, 3, 4]
            }
        ],
        "destroy": true
    });

    $('.modal-title').empty();
    $('.modal-title').append(tituloModal);

    //    if (!isEmpty(data) && existeColumnaCodigo(1)) {
    if (!isEmpty(data)) {
        $('#modalPrecioBien').modal('show');
    } else {
        $.Notification.autoHideNotify('warning', 'top right', 'Validación', "Ingrese los precios del producto");
    }
}
function onChangeCheckIGV() {
    var documentoTipo = dataCofiguracionInicial.documento_tipo.filter(item => item.id == $("#cboDocumentoTipo").val());
    if (document.getElementById('chkIGV').checked) {
        if (!isEmpty(documentoTipo) && documentoTipo[0]['identificador_negocio'] == 30) {
            igvValor = 0.08;
        } else {
            igvValor = 0.18;
            if (select2.obtenerValor("cboDocumentoTipo") != "9") {
                $("#contenedorIgvPorcentajeDiv").hide();
            } else {
                $("#contenedorIgvPorcentajeDiv").show();
            }
        }
    } else {
        igvValor = 0;
        if (select2.obtenerValor("cboDocumentoTipo") != "9") {
            $("#contenedorIgvPorcentajeDiv").hide();
        } else {
            $("#contenedorIgvPorcentajeDiv").hide();
        }
    }
    asignarImporteDocumento();
}

//Area de Opcion de Copiar Documento

function validarSoloUnDocumentoDeCopia() {
    var bandera = true;//no hay error

    if (!isEmpty(request.documentoRelacion)) {
        $.each(request.documentoRelacion, function (i, item) {
            if (!isEmpty(item.documentoId)) {
                bandera = false;//hay error
            }
        });
    }

    return bandera;
}

function cargarBuscadorDocumentoACopiar() {
    if (dataCofiguracionInicial.movimientoTipo[0]["codigo"] == 20) { //Transferencia interna
        if (!isEmpty(obtenerDocumentoTipoDatoIdXTipo(17))) {
            var orgIdDest = select2.obtenerValor("cbo_" + obtenerDocumentoTipoDatoIdXTipo(17));
            if (isEmpty(orgIdDest)) {
                mostrarAdvertencia('Seleccione almacén de llegada');
                return;
            }
        } else {
            mostrarAdvertencia('Seleccione almacén de llegada');
            return;
        }

        //VALIDAR QUE SOLO SE COPIE UN DOCUMENTO
        //SOLO CUANDO EL MOTIVO DE MOVIMIENTO ES REPOSICION (CODIGO = 1)  ó  CUANDO ALMACEN VIRTUAL ESTA EN ORIGEN
        var dtdMovimientoTipo = obtenerDocumentoTipoDatoXTipoXCodigo(4, 4);

        var codMotivo = null;
        if (!isEmpty(select2.obtenerValor('cbo_' + id_cboMotivoMov))) {
            codMotivo = dtdMovimientoTipo.data[document.getElementById('cbo_' + id_cboMotivoMov).options.selectedIndex]['valor'];
        }

        if ((codMotivo == 1 || origen_destino == 'O') && !validarSoloUnDocumentoDeCopia()) {
            mostrarAdvertencia('Solo debe seleccionar un documento a reponer');
            return;
        }
    }

    var dtdOrganizadorId = obtenerDocumentoTipoDatoIdXTipo(17);
    if (!isEmpty(dtdOrganizadorId)) {
        var organizadorOrigen = select2.obtenerValor('cboOrganizador');
        var organizadorDestino = select2.obtenerValor('cbo_' + dtdOrganizadorId);

        if (organizadorOrigen == organizadorDestino) {
            mostrarValidacionLoaderClose('Seleccione un almacén de destino diferente al almacén de origen');
            return;
        }
    }

    if (bandera.primeraCargaDocumentosRelacion) {
        loaderShow();
        obtenerConfiguracionesInicialesBuscadorCopiaDocumento();
        bandera.primeraCargaDocumentosRelacion = false;
    } else {
        cargarModalCopiarDocumentos();
        actualizarBusquedaDocumentoRelacion();
    }
}

function obtenerConfiguracionesInicialesBuscadorCopiaDocumento() {
    ax.setAccion("obtenerConfiguracionBuscadorDocumentoRelacion");
    ax.addParamTmp("empresa_id", commonVars.empresa);
    ax.consumir();
}

function onResponseObtenerConfiguracionBuscadorDocumentoRelacion(data) {
    $("#cboDocumentoTipoM").select2({
        width: "100%"
    });
    if (!isEmpty(data.documento_tipo)) {
        select2.cargar("cboDocumentoTipoM", data.documento_tipo, "id", "descripcion");
    }
    $("#cboPersonaM").select2({
        width: "100%"
    });
    select2.cargar("cboPersonaM", data.persona, "id", "nombre");

    if (!isEmpty(data.segun)) {
        $('#divSegun').show();
        $("#cbosegunM").select2({
            width: "100%"
        });
        select2.cargar("cbosegunM", data.segun, "id", "descripcion");
    }

    //    var table = $('#dtDocumentoRelacion').DataTable();
    //table.clear().draw();

    cargarModalCopiarDocumentos();
}

function cargarModalCopiarDocumentos() {
    var nombreCeldaPersona = 'Persona';
    if (dataCofiguracionInicial.movimientoTipo[0]["codigo"] == 20) { //Transferencia interna
        validarAlmacenOrigenDestino();
        if (origen_destino == "O") {
            nombreCeldaPersona = 'Guía relacionada';
        }
    }

    $('#nombreCeldaTHDocRelacion').html(nombreCeldaPersona);

    $('#modalDocumentoRelacion').modal('show');
    setTimeout(function () {
        cambiarAnchoBusquedaDesplegable();
    }, 100);
}

function buscarDocumentoRelacionPorCriterios() {
    loaderShow('#dtDocumentoRelacion');
    var cadena;
    //alert('hola');
    cadena = obtenerDatosBusquedaDocumentoACopiar();
    if (!isEmpty(cadena) && cadena !== 0) {
        $('#idPopover').attr("data-content", cadena);
    }
    $('[data-toggle="popover"]').popover('show');

    setTimeout(function () {
        getDataTableDocumentoACopiar()
    }, 500);
}

function obtenerDatosBusquedaDocumentoACopiar() {
    var cadena = "";
    obtenerParametrosBusquedaDocumentoACopiar();

    if (!isEmpty(parametrosBusquedaDocumentoACopiar.documento_tipo_ids)) {
        cadena += negrita("Documento: ");
        cadena += select2.obtenerTextMultiple('cboDocumentoTipoM');
        cadena += "<br>";
    }
    if (!isEmpty(parametrosBusquedaDocumentoACopiar.persona_id)) {
        cadena += negrita("Persona: ");
        cadena += select2.obtenerTextMultiple('cboPersonaM');
        cadena += "<br>";
    }
    if (!isEmpty(parametrosBusquedaDocumentoACopiar.serie)) {
        cadena += negrita("Serie: ");
        cadena += $('#txtSerie').val();
        cadena += "<br>";
    }
    if (!isEmpty(parametrosBusquedaDocumentoACopiar.numero)) {
        cadena += negrita("Numero: ");
        cadena += $('#txtNumero').val();
        cadena += "<br>";
    }
    if (!isEmpty(parametrosBusquedaDocumentoACopiar.fecha_emision_inicio) || !isEmpty(parametrosBusquedaDocumentoACopiar.fecha_emision_fin)) {
        cadena += negrita("Fecha emisión: ");
        cadena += parametrosBusquedaDocumentoACopiar.fecha_emision_inicio + " - " + parametrosBusquedaDocumentoACopiar.fecha_emision_fin;
        cadena += "<br>";
    }
    if (!isEmpty(parametrosBusquedaDocumentoACopiar.fecha_vencimiento_inicio) || !isEmpty(parametrosBusquedaDocumentoACopiar.fecha_vencimiento_fin)) {
        cadena += negrita("Fecha vencimiento: ");
        cadena += parametrosBusquedaDocumentoACopiar.fecha_vencimiento_inicio + " - " + parametrosBusquedaDocumentoACopiar.fecha_vencimiento_fin;
        cadena += "<br>";
    }
    if (!isEmpty(parametrosBusquedaDocumentoACopiar.segun_id)) {
        cadena += negrita("Segun: ");
        cadena += select2.obtenerTextMultiple('cbosegunM');
        cadena += "<br>";
    }
    return cadena;
}

function getDataTableDocumentoACopiar() {
    ax.setAccion("buscarDocumentoRelacionPorCriterio");
    ax.addParamTmp("criterios", parametrosBusquedaDocumentoACopiar);
    ax.addParamTmp("empresa_id", commonVars.empresa);

    $('#dtDocumentoRelacion').dataTable({
        "processing": true,
        "serverSide": true,
        "bFilter": false,
        "ajax": ax.getAjaxDataTable(),
        "scrollX": true,
        "order": [[1, "desc"]],
        "columns": [
            // {
            //     render: function (data, type, row) {
            //     if (type === 'display') {
            //         return '<input type="checkbox" name="checkselect" class="select-checkbox" value="'+row.documento_id+'" >';
            //     }
            //     return data;
            //     },
            //     "orderable": false,
            //     "class": "alignCenter",
            //     "width": "5%"
            // },
            { "data": "fecha_creacion", "width": "9%" },
            { "data": "fecha_emision", "width": "7%" },
            { "data": "documento_tipo", "width": "10%" },
            { "data": "persona", "width": "24%" },
            { "data": "serie_numero", "width": "10%" },
            { "data": "serie_numero_original", "width": "10%" },
            { "data": "fecha_vencimiento", "width": "7%" },
            { "data": "moneda_simbolo", "width": "4%", "sClass": "alignCenter" },
            { "data": "subtotal", "width": "8%", "sClass": "alignRight" },
            { "data": "usuario", "width": "6%", "sClass": "alignCenter" },
            {
                data: "flechas",
                render: function (data, type, row) {
                    if (type === 'display') {
                        var soloRelacionar = '';

                        if (row.documento_tipo_id != "283") {
                            // if (row.relacionar == '1') {
                            //     soloRelacionar = '<a onclick="agregarCabeceraDocumentoRelacion(' + row.documento_tipo_id + ',' + row.documento_id + ',' + row.movimiento_id + ')"><b><i class="fa fa-arrow-down" style = "color:#1ca8dd;" tooltip-btndata-toggle="tooltip" title="Solo relacionar"></i></b></a>';
                            // }

                            return '<a onclick="agregarDocumentoRelacion(' + row.documento_tipo_id + ',' + row.documento_id + ',' + row.movimiento_id + ',' + row.moneda_id + ',' + row.relacionar + ')"><b><i class="fa fa-download" style = "color:#04B404;" tooltip-btndata-toggle="tooltip" title="Copiar">&nbsp&nbsp</i></b></a>' +
                                soloRelacionar
                                ;
                        } else {
                            return "";
                        }
                    }
                    return data;
                },
                "orderable": false,
                "class": "alignCenter",
                "width": "5%"
            }
        ],
        columnDefs: [
            {
                "render": function (data, type, row) {
                    return (isEmpty(data)) ? '' : data.replace(" 00:00:00", "");
                },
                "targets": [1, 6]
            },
            {
                "render": function (data, type, row) {
                    return parseFloat(data).formatMoney(2, '.', ',');
                },
                "targets": 8
            }
        ],
        "fnRowCallback": function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            if (aData['documento_relacionado'] != '0') {
                $('td', nRow).css('background-color', '#FFD0D0');
            }
        },
        "dom": '<"top">rt<"bottom"<"col-md-3"l><"col-md-9"p><"col-md-12"i>><"clear">',
        destroy: true
    });
    cargarModalCopiarDocumentos();
    loaderClose();

}

var parametrosBusquedaDocumentoACopiar = {
    empresa_id: null,
    documento_tipo_ids: null,
    persona_id: null,
    serie: null,
    numero: null,
    fecha_emision_inicio: null,
    fecha_emision_fin: null,
    fecha_vencimiento_inicio: null,
    fecha_vencimiento_fin: null
};

var id_cboDestino = null;
var id_cboMotivoMov = null;
var origen_destino = null;

function validarAlmacenOrigenDestino() {
    if (!isEmpty(obtenerDocumentoTipoDatoIdXTipo(17))) {
        var orgIdDest = select2.obtenerValor("cbo_" + obtenerDocumentoTipoDatoIdXTipo(17));
        if (isEmpty(orgIdDest)) {
            mostrarAdvertencia('Seleccione almacén de llegada');
            return;
        }
    } else {
        mostrarAdvertencia('Seleccione almacén de llegada');
        return;
    }

    var text1 = "";
    var text2 = "";

    text1 = (select2.obtenerText("cbo_" + id_cboDestino)).toLowerCase();
    text2 = (select2.obtenerText("cboOrganizador")).toLowerCase();

    if (text1.indexOf("virtual") == 0 && text2.indexOf("virtual") != 0) {
        parametrosBusquedaDocumentoACopiar.documento_tipo_ids = ["12", "189"];
        parametrosBusquedaDocumentoACopiar.serie = "D"; //destino almacen virtual
        origen_destino = "D";
    } else if (text1.indexOf("virtual") != 0 && text2.indexOf("virtual") == 0) {
        parametrosBusquedaDocumentoACopiar.documento_tipo_ids = ["264"];
        parametrosBusquedaDocumentoACopiar.serie = "O"; //orgien almacen virtual
        origen_destino = "O";
    } else {
        origen_destino = null;
    }
}

function obtenerParametrosBusquedaDocumentoACopiar() {
    parametrosBusquedaDocumentoACopiar = {
        empresa_id: null,
        documento_tipo_ids: null,
        persona_id: null,
        serie: null,
        numero: null,
        fecha_emision_inicio: null,
        fecha_emision_fin: null,
        fecha_vencimiento_inicio: null,
        fecha_vencimiento_fin: null,
        movimiento_tipo_id: isEmpty(dataCofiguracionInicial.movimientoTipo[0].movimiento_tipo_id)?dataCofiguracionInicial.movimientoTipo[0].id : dataCofiguracionInicial.movimientoTipo[0].movimiento_tipo_id
    };

    if (dataCofiguracionInicial.movimientoTipo[0]["codigo"] == 20) { //Transferencia interna
        validarAlmacenOrigenDestino();
    } else {
        parametrosBusquedaDocumentoACopiar.documento_tipo_ids = $('#cboDocumentoTipoM').val();
        parametrosBusquedaDocumentoACopiar.serie = $('#txtSerie').val();
    }

    parametrosBusquedaDocumentoACopiar.empresa_id = commonVars.empresa;


    var personaId = $('#cboPersonaM').val();
    if (!isEmpty(personaId)) {
        parametrosBusquedaDocumentoACopiar.persona_id = personaId[0];
    }
    var segunId = $('#cbosegunM').val();
    if (!isEmpty(segunId)) {
        parametrosBusquedaDocumentoACopiar.segun_id = segunId;
    }
    //    parametrosBusquedaDocumentoACopiar.serie = $('#txtSerie').val();
    parametrosBusquedaDocumentoACopiar.numero = $('#txtNumero').val();
    parametrosBusquedaDocumentoACopiar.fecha_emision_inicio = $('#dpFechaEmisionInicio').val();
    parametrosBusquedaDocumentoACopiar.fecha_emision_fin = $('#dpFechaEmisionFin').val();
    parametrosBusquedaDocumentoACopiar.fecha_vencimiento_inicio = $('#dpFechaVencimientoInicio').val();
    parametrosBusquedaDocumentoACopiar.fecha_vencimiento_fin = $('#dpFechaVencimientoFin').val();
}

var documentoTipoOrigenIdGLobal = null;
function agregarDocumentoRelacion(documentoTipoOrigenId, documentoId, movimientoId, monedaId, relacionar) {
    documentoTipoOrigenIdGLobal = null;
    if(documentoTipoOrigenId == SOLICITUD_REQUERIMIENTO){
        documentoTipoOrigenIdGLobal = documentoTipoOrigenId;
    }
    if (doc_TipoId == GENERAR_COTIZACION || documentoTipoOrigenId == SOLICITUD_REQUERIMIENTO) {
        if (!isEmpty(detalle)) {
            mostrarAdvertencia("No puede copiar el documento al formulario.");
            loaderClose();
            return;
        }
    }

    if (relacionar == 0) {
        $("#chkDocumentoRelacion").prop("checked", "");
        $("#divChkDocumentoRelacion").hide();
    }

    loaderShow("#modalDocumentoRelacion");
    //loaderShow();
    if (validarDocumentoRelacionRepetido(documentoId)) {
        mostrarAdvertencia("Documento a copiar ya a sido agregado");
        loaderClose();
        return;
    }

    if (select2.obtenerValor("cboMoneda") != monedaId && !isEmpty(detalle)) {
        mostrarAdvertencia("Las moneda deben ser iguales, seleccione otro documento, o cambie la moneda.");
        loaderClose();
        return;
    }

    //variable.documentoIdCopia = documentoId;
    //variable.movimientoIdCopia = movimientoId;

    if (dataCofiguracionInicial.documento_tipo[0].identificador_negocio == 5) {
        var documentoTipo = dataDocumentoTipo[0]['id'];
        ax.setAccion("obtenerNumeroNotaCredito");
        ax.addParamTmp("documentoTipoId", documentoTipo);
        ax.addParamTmp("documentoRelacionadoTipo", documentoTipoOrigenId);
        ax.consumir();
    }
    var documentoTipoDestinoId = $('#cboDocumentoTipo').val();

    ax.setAccion("obtenerDocumentoRelacion");
    ax.addParamTmp("documento_id_origen", documentoTipoOrigenId);
    ax.addParamTmp("documento_id_destino", documentoTipoDestinoId);
    ax.addParamTmp("documento_id", documentoId);
    ax.addParamTmp("movimiento_id", movimientoId);
    ax.addParamTmp("documentos_relacinados", request.documentoRelacion);
    ax.consumir();
}

function onResponseObtenerNumeroNotaCredito(data) {
    //colocamos la serie y numero en las cajas de texto
    var idTipoDatoSerie, idTipoDatoNumero;
    idTipoDatoSerie = buscarDocumentoTipoDatoPorTipo(7).id;
    $("#txt_" + idTipoDatoSerie).val(data[0].serie);

    idTipoDatoNumero = buscarDocumentoTipoDatoPorTipo(8).id;
    $("#txt_" + idTipoDatoNumero).val(data[0].numero);

}

function buscarDocumentoTipoDatoPorTipo(tipo) {

    var objDocumentoTipoDato = null;
    if (!isEmpty(dataCofiguracionInicial) && !isEmpty(dataCofiguracionInicial.documento_tipo_conf)) {
        $.each(dataCofiguracionInicial.documento_tipo_conf, function (indexConf, itemConf) {
            if (itemConf.tipo * 1 == tipo) {
                objDocumentoTipoDato = itemConf;
                return false;
            }
        });
    }
    return objDocumentoTipoDato;
}

function agregarCabeceraDocumentoRelacion(documentoTipoOrigenId, documentoId, movimientoId) {
    loaderShow("#modalDocumentoRelacion");
    //loaderShow();
    if (validarDocumentoRelacionRepetido(documentoId)) {
        mostrarAdvertencia("Documento a relacionar ya a sido agregado");
        loaderClose();
        return;
    }

    variable.documentoIdCopia = documentoId;
    variable.movimientoIdCopia = movimientoId;

    var documentoTipoDestinoId = $('#cboDocumentoTipo').val();

    ax.setAccion("obtenerDocumentoRelacionCabecera");
    ax.addParamTmp("documento_id_origen", documentoTipoOrigenId);
    ax.addParamTmp("documento_id_destino", documentoTipoDestinoId);
    ax.addParamTmp("documento_id", documentoId);
    ax.addParamTmp("movimiento_id", movimientoId);
    ax.addParamTmp("documentos_relacinados", request.documentoRelacion);
    ax.consumir();
}

function validarDocumentoRelacionRepetido(documentoACopiarId) {
    var resultado = false;
    $.each(request.documentoRelacion, function (index, item) {
        if (!isEmpty(item.documentoId)) {
            if (item.documentoId === documentoACopiarId) {
                resultado = true;
            }
        }

    });

    return resultado;
}

function reinicializarDataTableDetalle() {
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

var detalleDocumentoRelacion = [];
var documentoTipoDescripcionCopia = null;
var documentoRelacionId = null;
var dataDocumentoCopia = [];
function onResponseObtenerDocumentoRelacion(data) {
    dataDocumentoCopia = data;
    variable.documentoIdCopia = data.datosDocumento.documentoIdCopia;
    variable.movimientoIdCopia = data.datosDocumento.movimientoIdCopia;
    $('#modalDocumentoRelacion').modal('hide');

    if (data.documentoACopiar[0].incluye_igv == 1) {
        $("#chkIncluyeIGV").prop("checked", "checked");
    } else {
        $("#chkIncluyeIGV").prop("checked", "");
    }

    if (data.documentoACopiar[0].documento_tipo_descripcion == "Solicitud requerimiento" && doc_TipoId == GENERAR_COTIZACION) {
        documentoTipoDescripcionCopia = data.documentoACopiar[0].documento_tipo_descripcion;
        var dtdTipoGrupo_producto = obtenerDocumentoTipoDatoIdXTipo(44);
        $("#cbo_" + dtdTipoGrupo_producto).prop('disabled', true);
        documentoRelacionId = variable.documentoIdCopia;
    }

    select2.asignarValorQuitarBuscador("cboMoneda", data.documentoACopiar[0].moneda_id);
    modificarSimbolosMoneda(data.documentoACopiar[0].moneda_id, dataCofiguracionInicial.moneda[document.getElementById('cboMoneda').options.selectedIndex]);

    //detalle = [];
    indiceLista = [];
    banderaCopiaDocumento = 0;

    if (data.detalleDocumento.length > 5) {
        nroFilasReducida = data.detalleDocumento.length;
    } else {
        nroFilasReducida = 5;
    }

    if (doc_TipoId != GENERAR_COTIZACION) {
        if (multiseleccion == 0) {
            indexDetalle = 0;
            $('#datatable').DataTable().clear().destroy();
        }
        limpiarDetalle();
        if (multiseleccion == 0) {
            reinicializarDataTableDetalle();
        }
    }

    detalleDocumentoRelacion = data.detalleDocumento;
    dataCofiguracionInicial.bien = data.dataBien;
    if (doc_TipoId == GENERAR_COTIZACION || doc_TipoId == COTIZACION_SERVICIO) {
        nroFilasReducida = parseInt(data.detalleDocumento.length);
        limpiarDetalle();
    }
    cargarDataDocumentoACopiar(data.documentoACopiar, data.dataDocumentoRelacionada);
    cargarDetalleDocumentoRelacion(data.detalleDocumento);

    cargarDocumentoRelacionadoDeCopia(data.documentosRelacionados);

    //cargar los datos copiados de programacion
    cargarPagoProgramacion(data.dataPagoProgramacion);
}

function cargarDocumentoRelacionadoDeCopia(data) {

    if (!isEmpty(data)) {
        if (data == 1) {
            $("#chkDocumentoRelacion").prop("checked", "");
            $("#divChkDocumentoRelacion").hide();

        } else {

            var detalleEnlace = '';
            $.each(data, function (index, item) {
                if (!validarDocumentoRelacionRepetido(parseInt(item.documento_id))) {
                    request.documentoRelacion.push({
                        documentoId: parseInt(item.documento_id),
                        movimientoId: parseInt(item.movimiento_id),
                        tipo: 2,
                        documentoPadreId: varDocumentoPadreId
                    });

                    detalleEnlace = item.documento_tipo_descripcion + ": " + item.serie_numero;

                    $('#linkDocumentoACopiar').append("<a id='link_" + contadorDocumentoCopiadoAVisualizar + "' onclick='visualizarDocumentoRelacion(" + contadorDocumentoCopiadoAVisualizar + ")' id='linkDocumentoACopiar' style='color:#0000FF'>[" + detalleEnlace + "]</a><br>");
                    //                    $('#linkDocumentoACopiar').append("<a id='cerrarLink_" + contadorDocumentoCopiadoAVisualizar + "' onclick='eliminarDocumentoACopiar(" + contadorDocumentoCopiadoAVisualizar + ")'style='color:red;'>&ensp;X</a>");
                    $("#divDocumentoRelacion").css("height", $("#divDocumentoRelacion").height() + 20);
                    request.documentoRelacion[contadorDocumentoCopiadoAVisualizar].detalleLink = detalleEnlace;
                    request.documentoRelacion[contadorDocumentoCopiadoAVisualizar].posicion = contadorDocumentoCopiadoAVisualizar;
                    contadorDocumentoCopiadoAVisualizar++;
                }
            });

            varDocumentoPadreId = null;
        }

    }
}


function onResponseObtenerDocumentoRelacionCabecera(data) {
    cargarDataDocumentoACopiar(data.dataDocumento, data.dataDocumentoRelacionada);
    if (!isEmpty(variable.documentoIdCopia) && !isEmpty(variable.movimientoIdCopia)) {
        request.documentoRelacion.push({
            documentoId: variable.documentoIdCopia,
            movimientoId: variable.movimientoIdCopia,
            tipo: 1,
            documentoPadreId: null
        });
        varDocumentoPadreId = variable.documentoIdCopia;

        variable.documentoIdCopia = null;
        variable.movimientoIdCopia = null;
    }

    if (!isEmpty(detalleLink)) {
        if (bandera.mostrarDivDocumentoRelacion) {
            $('#divDocumentoRelacion').show();

            if (banderachkDocumentoRelacion === 0) {
                $("#divDocumentoRelacion").css("height", $("#divDocumentoRelacion").height() + 20);
                banderachkDocumentoRelacion = 1;
            }
        }

        $('#linkDocumentoACopiar').append("<a id='link_" + contadorDocumentoCopiadoAVisualizar + "' onclick='visualizarDocumentoRelacion(" + contadorDocumentoCopiadoAVisualizar + ")' id='linkDocumentoACopiar' style='color:#0000FF'>[" + detalleLink + "]</a>");
        $('#linkDocumentoACopiar').append("<a id='cerrarLink_" + contadorDocumentoCopiadoAVisualizar + "' onclick='eliminarDocumentoACopiarSinDetalle(" + contadorDocumentoCopiadoAVisualizar + ")'style='color:red;'>&ensp;X</a><br>");
        $("#divDocumentoRelacion").css("height", $("#divDocumentoRelacion").height() + 20);

        request.documentoRelacion[contadorDocumentoCopiadoAVisualizar].detalleLink = detalleLink;
        request.documentoRelacion[contadorDocumentoCopiadoAVisualizar].posicion = contadorDocumentoCopiadoAVisualizar;
        contadorDocumentoCopiadoAVisualizar++;
        detalleLink = null;
    }

    $('#modalDocumentoRelacion').modal('hide');

    cargarDocumentoRelacionadoDeCopia(data.documentoCopiaRelaciones);
}

var contadorDocumentoCopiadoAVisualizar = 0;
function cargarDataDocumentoACopiar(data, dataDocumentoRelacionada) {
    var documentoTipo = "", serie = "", numero = "";
    if (!bandera.mostrarDivDocumentoRelacion) {
        if (!isEmpty(data)) {

            $.each(data, function (index, item) {
                switch (parseInt(item.tipo)) {
                    case 5:
                    case 23:
                        select2.asignarValor('cbo_' + item.otro_documento_id, item.valor);
                        var indice = select2.obtenerValor('cbo_' + item.otro_documento_id);
                        if (indice == item.valor) {
                            obtenerPersonaDireccion(item.valor);
                            obtenerPersonaContacto(item.valor);
                            // if (doc_TipoId == ORDEN_COMPRA || doc_TipoId == ORDEN_SERVICIO || doc_TipoId == COTIZACIONES || doc_TipoId == COTIZACION_SERVICIO) {
                            //     if (!isEmpty(item.valor)) {
                            //         obtenerCuentaPersona(item.valor);
                            //     }
                            // }
                        }
                        break;
                    case 6:
                        //                    case 7:
                        //                    case 8:
                        $('#txt_' + item.otro_documento_id).val(item.valor);
                        break;
                    //case 9: //fecha emision
                    case 10:
                    case 11:
                        $('#datepicker_' + item.otro_documento_id).val(formatearFechaJS(item.valor));
                        break;
                }
            });
            bandera.mostrarDivDocumentoRelacion = true;

        }

        if (!isEmpty(dataDocumentoRelacionada)) {
            $.each(dataDocumentoRelacionada, function (index, item) {
                if (isEmpty(item.documento_tipo_dato_origen)) {
                    select2.asignarValor('cbo_' + item.documento_tipo_dato_destino, item.valor);
                } else {

                    switch (item.tipo * 1) {
                        case 5:
                            select2.asignarValor('cbo_' + item.documento_tipo_dato_destino, item.valor);
                            var indice = select2.obtenerValor('cbo_' + item.documento_tipo_dato_destino);
                            if (indice == item.valor) {
                                obtenerPersonaDireccion(item.valor);
                                obtenerPersonaContacto(item.valor);
                                // if (doc_TipoId == ORDEN_COMPRA || doc_TipoId == ORDEN_SERVICIO || doc_TipoId == COTIZACIONES || doc_TipoId == COTIZACION_SERVICIO) {
                                //     obtenerCuentaPersona(item.valor);
                                // }
                            }
                            break;
                        case 2:
                            $('#txt_' + item.documento_tipo_dato_destino).val(item.valor);
                            break;
                        case 4:
                            select2.asignarValor("cbo_" + item.documento_tipo_dato_destino, item.valor);
                            break;
                        case 26: // vendedor
                            select2.asignarValor('cbo_' + item.documento_tipo_dato_destino, item.valor);
                            break;
                        case 42: //Tipo requerimiento
                            select2.asignarValor("cboTipoRequerimiento_" + item.documento_tipo_dato_destino, item.valor);
                            tipoRequerimientoTemp = select2.obtenerValor("cboTipoRequerimiento_" + item.documento_tipo_dato_destino);
                            break;
                        case 45: //
                            select2.asignarValor('cbo_' + item.documento_tipo_dato_destino, item.valor);
                            break;
                        // case 46: //
                        //     select2.asignarValor('cbo_' + item.documento_tipo_dato_destino, item.valor);
                        //     break;                              
                        case 47: // 
                            select2.asignarValor('cbo_' + item.documento_tipo_dato_destino, item.valor);
                            break;
                        default:
                            $('#txt_' + item.documento_tipo_dato_destino).val(item.valor);
                            if (isEmpty($('#txt_' + item.documento_tipo_dato_destino).val())) {
                                $('#datepicker_' + item.documento_tipo_dato_destino).val(formatearFechaJS(item.valor));
                            }
                    }
                }
            });
        }
    }

    if (!isEmpty(data)) {
        $.each(data, function (index, item) {

            documentoTipo = item.documento_tipo_descripcion;

            switch (parseInt(item.tipo)) {
                case 7:
                    if (!isEmpty(item.valor)) {
                        serie = item.valor;
                    }

                    break;
                case 8:
                    if (!isEmpty(item.valor)) {
                        numero = item.valor;
                    }
                    break;
            }
        });

        detalleLink = documentoTipo + ": " + serie + " - " + numero;
    }
}

function cargarDetalleDocumentoRelacion(data) {

    var banderaMostrarModal = 0;
    $('#contenedorAsignarStockXOrganizador').empty();
    //$('#dgDetalle').empty();
    dataFaltaAsignarCantidadXOrganizador = null;
    dataFaltaAsignarCantidadXOrganizador = [];

    if (!isEmpty(data)) {
        if (!isEmpty(variable.documentoIdCopia) && !isEmpty(variable.movimientoIdCopia)) {
            request.documentoRelacion.push({
                documentoId: variable.documentoIdCopia,
                movimientoId: variable.movimientoIdCopia,
                tipo: 1,
                documentoPadreId: null
            });

            varDocumentoPadreId = variable.documentoIdCopia;

            variable.documentoIdCopia = null;
            variable.movimientoIdCopia = null;
        }

        if (!isEmpty(detalleLink)) {
            if (bandera.mostrarDivDocumentoRelacion) {
                // $('#divDocumentoRelacion').show(); // se deshabilito

                if (banderachkDocumentoRelacion === 0) {
                    $("#divDocumentoRelacion").css("height", $("#divDocumentoRelacion").height() + 20);
                    banderachkDocumentoRelacion = 1;
                }
            }
            $('#linkDocumentoACopiar').append("<a id='link_" + contadorDocumentoCopiadoAVisualizar + "' onclick='visualizarDocumentoRelacion(" + contadorDocumentoCopiadoAVisualizar + ")' id='linkDocumentoACopiar' style='color:#0000FF'>[" + detalleLink + "]</a>");
            $('#linkDocumentoACopiar').append("<a id='cerrarLink_" + contadorDocumentoCopiadoAVisualizar + "' onclick='eliminarDocumentoACopiar(" + contadorDocumentoCopiadoAVisualizar + ")'style='color:red;'>&ensp;X</a><br>");
            $("#divDocumentoRelacion").css("height", $("#divDocumentoRelacion").height() + 20);
            if (!isEmpty(request.documentoRelacion[contadorDocumentoCopiadoAVisualizar])) {
                if (isEmpty(request.documentoRelacion[contadorDocumentoCopiadoAVisualizar].detalleLink)) {
                    request.documentoRelacion[contadorDocumentoCopiadoAVisualizar].detalleLink = detalleLink;
                    request.documentoRelacion[contadorDocumentoCopiadoAVisualizar].posicion = contadorDocumentoCopiadoAVisualizar;
                    contadorDocumentoCopiadoAVisualizar++;
                    detalleLink = null;
                }
            }
        }
        $.each(data, function (index, item) {
            cargarDataTableDocumentoACopiar(
                cargarFormularioDetalleACopiar(item.organizador_id, item.bien_id, item.cantidad,
                    item.unidad_medida_id, item.valor_monetario, item.organizador_descripcion,
                    ((!isEmpty(item.bien_codigo) ? item.bien_codigo + " | " : "") + item.bien_descripcion), item.unidad_medida_descripcion, item.precio_tipo_id,
                    item.movimiento_bien_detalle, item.dataUnidadMedida, item.movimiento_bien_comentario, item.agencia_id, item.agencia_descripcion, item.agrupador_id, item.agrupador_descripcion, item.ticket, item.ceco_id,
                    item.movimiento_bien_ids, item.precio_postor1, item.precio_postor2, item.precio_postor3, item.postor_ganador_id, item.cantidad_atendida
                )
            );

        });

        let dataDocumentoTipoSeleccionado = obtenerDocumentoTipoSeleccionado();
        if (dataDocumentoTipoSeleccionado.id != "270") {
            //OBTENEMOS EL STOCK DE LOS PRODUCTOS COPIADOS:
            obtnerStockParaProductosDeCopia();
        }
        //FIN OBTENER STOCK
    }

    if (banderaMostrarModal === 1) {
        $('#modalAsignarOrganizador').modal('show');
    }

}

function obtnerStockParaProductosDeCopia() {
    if (!isEmpty(detalle)) {
        //ALMACEN DE LLEGADA PARA TRANSFERENCIA INTERNA
        var organizadorDestinoId = null;
        if (dataCofiguracionInicial.movimientoTipo[0]["codigo"] == 20) { //Transferencia interna
            var dtdOrganizadorId = obtenerDocumentoTipoDatoIdXTipo(17);
            if (!isEmpty(dtdOrganizadorId)) {
                organizadorDestinoId = select2.obtenerValor('cbo_' + dtdOrganizadorId);
            }
        }

        loaderShow();
        ax.setAccion("obtenerStockParaProductosDeCopia");
        ax.addParamTmp("organizadorDefectoId", organizadorIdDefectoTM);
        ax.addParamTmp("detalle", detalle);
        ax.addParamTmp("organizadorDestinoId", organizadorDestinoId);
        ax.consumir();
    }
}

function onResponseObtenerStockParaProductosDeCopia(data) {
    $.each(data, function (index, item) {
        onResponseObtenerStockActual(item);
    });

    $('#modalDocumentoRelacion').modal('hide');
}

var dataFaltaAsignarCantidadXOrganizador = [];
function cargarModalParaAgregarOrganizador(data) {
    var stockOrganizadores;
    dataFaltaAsignarCantidadXOrganizador.push(data);
    var html = '<div>';
    html += '<p id="titulo_' + data.bien_id + '">' + data.bien_descripcion + ' : ' + data.cantidad + ' ' + data.unidad_medida_descripcion + '</p>';
    html += '</div>';

    if (isEmpty(data.stock_organizadores)) {
        html += '<p style="color:red;">No hay stock para este bien.</p>';
    } else {
        html += '<div class="table">';
        html += '<table id="datatableStock_' + data.bien_id + '" class="table table-striped table-bordered">';
        html += '<thead>';
        html += '<tr>';
        html += '<th style="text-align:center;">Organizador</th>';
        html += '<th style="text-align:center;">Disponible</th>';
        html += '<th style="text-align:center;">A usar</th>';
        html += '</tr>';
        html += '</thead>';
        html += '<tbody>';

        stockOrganizadores = obtenerstockPredefinido(data.stock_organizadores, data.cantidad);
        $.each(data.stock_organizadores, function (index, item) {

            html += '<tr>';
            html += '<td >' + item.organizadorDescripcion + '</td>';
            html += '<td >' + formatearCantidad(item.stock) + '</td>';
            html += '<td >';
            html += '<input type="number" min="0" id="txt_' + item.organizadorId + '_' + data.bien_id + '" style="text-align: right;" ';
            html += ' value="' + obtenerStockPredefinidoXOrganizador(item.organizadorId, stockOrganizadores) + '">';
            html += '</td>';
            html += '</tr>';
        });


        html += '</tbody>';
        html += '</table>';
        html += '</div>';
    }


    $('#contenedorAsignarStockXOrganizador').append(html);

    $('#datatableStock_' + data.bien_id).dataTable({
        "columns": [
            { "width": "10px" },
            { "width": "10px", "sClass": "alignRight" },
            { "width": "10px", "sClass": "alignCenter" }
        ],
        "dom": '<"top">rt<"bottom"><"clear">',
        "order": [[1, "desc"]]
    });
}

function obtenerStockPredefinidoXOrganizador(organizadorId, stockOrganizadores) {
    var stock = "";
    $.each(stockOrganizadores, function (index, item) {
        if (item.organizadorId == organizadorId) {
            stock = formatearCantidad(item.asignado);
        }
    });

    return stock;
}
function obtenerstockPredefinido(data, stockDeseado) {
    var array = [];
    //    var organizadores = [];
    $.each(data, function (index, item) {
        array.push({
            organizadorId: item.organizadorId,
            stock: item.stock,
            asignado: 0
        });
    });

    array = ordenacionBurbuja(array);

    $.each(array, function (index, item) {
        if (parseFloat(stockDeseado) > parseFloat(item.stock)) {
            array[index]['asignado'] = item.stock;
            stockDeseado = stockDeseado - item.stock;

        } else {
            array[index]['asignado'] = stockDeseado;
            stockDeseado = 0;
        }
    });

    return array;

}

function ordenacionBurbuja(array) {

    var tamanio = array.length;
    var i, j;
    var aux;
    for (i = 0; i < tamanio; i++) {
        for (j = 0; j < (tamanio - 1); j++) {
            if (array[j].stock < array[j + 1].stock) {
                aux = array[j];
                array[j] = array[j + 1];
                array[j + 1] = aux;
            }
        }

    }

    return array;
}

function asignarStockXOrganizador() {
    var suma = 0;
    var valorStockUnitario;
    var organizadorUsado = [];

    var listaDetalleDocumentoACopiar = [];
    var banderaSalirEach = 0;

    $.each(dataFaltaAsignarCantidadXOrganizador, function (index, itemData) {
        if (banderaSalirEach === 0) {
            if (!isEmpty(itemData.stock_organizadores)) {
                $.each(itemData.stock_organizadores, function (index1, item) {

                    valorStockUnitario = $('#txt_' + item.organizadorId + '_' + itemData.bien_id).val();
                    if (!isEmpty(valorStockUnitario)) {
                        if (valorStockUnitario < 0) {
                            mostrarAdvertencia("El valor a usar es menor que cero para el bien " + itemData.bien_descripcion + " en el organizador " + item.organizadorDescripcion);
                            banderaSalirEach = 1;
                        } else {
                            if (parseFloat(valorStockUnitario) > parseFloat(itemData.cantidad)) {
                                mostrarAdvertencia("El valor a usar es mayor al requerido para el bien " + itemData.bien_descripcion);
                                banderaSalirEach = 1;
                            } else {
                                if (parseFloat(valorStockUnitario) > parseFloat(item.stock)) {
                                    mostrarAdvertencia("El valor a usar es mayor que el stock para el bien " + itemData.bien_descripcion);
                                    banderaSalirEach = 1;
                                } else {
                                    if (valorStockUnitario > 0) {
                                        suma = parseFloat(suma) + parseFloat(valorStockUnitario);
                                        organizadorUsado.push({
                                            organizadorDescripcion: item.organizadorDescripcion,
                                            organizadorId: item.organizadorId,
                                            usado: valorStockUnitario
                                        });
                                    }
                                }
                            }
                        }
                    }

                });
            }
            if (banderaSalirEach === 0) {
                if (parseFloat(suma) > 0 && parseFloat(suma) <= itemData.cantidad) {

                    $.each(organizadorUsado, function (index2, itemOrganizadorUsado) {
                        listaDetalleDocumentoACopiar.push(
                            cargarFormularioDetalleACopiar(itemOrganizadorUsado.organizadorId, itemData.bien_id, itemOrganizadorUsado.usado,
                                itemData.unidad_medida_id, itemData.valor_monetario, itemOrganizadorUsado.organizadorDescripcion,
                                itemData.bien_descripcion, itemData.unidad_medida_descripcion)
                        );
                    });
                } else {
                    if (!isEmpty(itemData.stock_organizadores)) {
                        mostrarAdvertencia("Los valores ingresados no son correctos para el bien " + itemData.bien_descripcion);
                        banderaSalirEach = 1;
                    }

                }
            }

            organizadorUsado = [];
            suma = 0;

            //            }
        }
    });

    if (banderaSalirEach === 0) {
        $.each(listaDetalleDocumentoACopiar, function (index, item) {
            cargarDataTableDocumentoACopiar(item);
        });

        listaDetalleDocumentoACopiar = [];

        if (banderaSalirEach === 0) {
            $('#modalAsignarOrganizador').modal('hide');

        }
    }


}

function cargarFormularioDetalleACopiar(organizadorId, bienId, cantidad, unidadMedidaId, precio,
    organizadorDesc, bienDesc, unidadMedidaDesc, precioTipoId, movimientoBienDetalle, dataUnidadMedida, movimientoBienComentario,
    ageniaId, agenciaDescripcion, agrupadorBien, agrupadorBienDescripcion, ticket, CeCoId, movimiento_bien_ids, precio_postor1, precio_postor2, precio_postor3, postor_ganador_id, cantidad_atendida

) {

    //agregar logica de columnas dinamicas...
    //obtener los datos del detalle dinamico
    var dataColumna = dataCofiguracionInicial.movimientoTipoColumna;
    var objDetalle = {};//Objeto para el detalle
    var detDetalle = [];

    if (!isEmpty(dataColumna)) {
        $.each(dataColumna, function (index, item) {
            //obtener los datos del detalle
            switch (parseInt(item.codigo)) {
                //numeros
                case 5:// PRECIO UNITARIO
                    if (doc_TipoId == COTIZACIONES) {
                        var precioPostor = 0;
                        switch (parseInt(postor_ganador_id)) {
                            case 1:
                                precioPostor = precio_postor1;
                                break;
                            case 2:
                                precioPostor = precio_postor2;
                                break;
                            case 3:
                                precioPostor = precio_postor3;
                                break;
                        }
                        objDetalle.precio = precioPostor;
                    } else {
                        objDetalle.precio = precio;
                    }
                    break;
                case 12:// CANTIDAD
                    if (doc_TipoId == GENERAR_COTIZACION) {
                        objDetalle.cantidad = parseFloat(cantidad);
                    } else {
                        objDetalle.cantidad = cantidad;
                    }
                    break;

                //combos, seleccion
                case 4:// TIPO PRECIO
                    if (isEmpty(precioTipoId)) {
                        precioTipoId = dataCofiguracionInicial.precioTipo[0]['precio_tipo_id'];
                    }
                    objDetalle.precioTipoId = precioTipoId;
                    break;
                case 11:// PRODUCTO
                    objDetalle.bienId = bienId;
                    objDetalle.bienDesc = bienDesc;
                    if (!isEmpty(movimientoBienComentario)) {
                        objDetalle.comentarioBien = movimientoBienComentario;
                    }
                    if (doc_TipoId == GENERAR_COTIZACION || doc_TipoId == REQUERIMIENTO_AREA || doc_TipoId == COTIZACION_SERVICIO || doc_TipoId == SOLICITUD_REQUERIMIENTO) {
                        objDetalle.movimiento_bien_ids = movimiento_bien_ids;
                    }

                    break;
                case 13:// UNIDAD DE MEDIDA
                    objDetalle.unidadMedidaId = unidadMedidaId;
                    objDetalle.unidadMedidaDesc = unidadMedidaDesc;
                    objDetalle.dataUnidadMedida = dataUnidadMedida;
                    break;
                case 15:// Organizador
                    if (organizadorIdDefectoTM == 0) {
                        objDetalle.organizadorId = organizadorId;
                        objDetalle.organizadorDesc = organizadorDesc;
                    } else {
                        objDetalle.organizadorId = organizadorIdDefectoTM;
                    }
                    break;
                case 21:// COMENTARIO
                    objDetalle.comentarioBien = movimientoBienComentario;
                    break;

                case 22:// COMENTARIO
                    objDetalle.comentarioBien = movimientoBienComentario;
                    break;

                case 23:// Agencia
                    objDetalle.agenciaId = ageniaId;
                    objDetalle.agenciaDesc = agenciaDescripcion;

                    break;
                case 24: // Agrupador
                    objDetalle.agrupadorId = agrupadorBien;
                    objDetalle.agrupadorBienDec = agrupadorBienDescripcion;

                    break;
                case 25: // Ticket
                    objDetalle.ticket = ticket;
                    break;
                case 26: // CeCo
                    objDetalle.CeCoId = CeCoId;
                    break;
                case 27://Postor N° 1
                    objDetalle.precioPostor1 = 0;
                    break;
                case 28://Postor N° 2
                    objDetalle.precioPostor2 = 0;
                    break;
                case 29://Postor N° 3
                    objDetalle.precioPostor3 = 0;
                    break;
                case 33://Compra
                    objDetalle.esCompra = 1;
                    break;
                case 34://Cantidad aceptada
                    objDetalle.cantidadAceptada = cantidad;
                    break;
                default:
                    //DATOS DE MOVIMIENTO_BIEN_DETALLE
                    if (!isEmpty(movimientoBienDetalle)) {
                        $.each(movimientoBienDetalle, function (indexBD, itemBD) {
                            if (parseInt(itemBD.columna_codigo) === parseInt(item.codigo)) {
                                detDetalle.push({ columnaCodigo: parseInt(itemBD.columna_codigo), valorDet: itemBD.valor_detalle });
                            }
                        });
                    }
                    break;
            }
        });
    } else {
        mostrarValidacionLoaderClose("Falta configurar las columnas del detalle");
        return false;
    }

    //fin columnas dinamicas

    //en caso el organizador no este en el detalle pero si en la cabecera
    if (!existeColumnaCodigo(15)) {
        if (muestraOrganizador) {
            objDetalle.organizadorId = select2.obtenerValor('cboOrganizador');
        }
    }

    objDetalle.detalle = detDetalle;
    objDetalle.cantidad_atendida = parseFloat(cantidad_atendida);
    objDetalle.cantidad_total = parseFloat(cantidad) + parseFloat(cantidad_atendida);
    return objDetalle;
}

var banderachkDocumentoRelacion = 0;
var varDocumentoPadreId;
function cargarDataTableDocumentoACopiar(data) {
    if (!isEmpty(data)) {
        /*;
        if (!isEmpty(variable.documentoIdCopia) && !isEmpty(variable.movimientoIdCopia))
        {
            request.documentoRelacion.push({
                documentoId: variable.documentoIdCopia,
                movimientoId: variable.movimientoIdCopia,
                tipo: 1,
                documentoPadreId: null
            });

            varDocumentoPadreId = variable.documentoIdCopia;

            variable.documentoIdCopia = null;
            variable.movimientoIdCopia = null;
        }

        if (!isEmpty(detalleLink))
        {
            if (bandera.mostrarDivDocumentoRelacion)
            {
                $('#divDocumentoRelacion').show();

                if (banderachkDocumentoRelacion === 0) {
                    $("#divDocumentoRelacion").css("height", $("#divDocumentoRelacion").height() + 20);
                    banderachkDocumentoRelacion = 1;
                }
            }
            $('#linkDocumentoACopiar').append("<a id='link_" + contadorDocumentoCopiadoAVisualizar + "' onclick='visualizarDocumentoRelacion(" + contadorDocumentoCopiadoAVisualizar + ")' id='linkDocumentoACopiar' style='color:#0000FF'>[" + detalleLink + "]</a>");
            //$('#linkDocumentoACopiar').append("<a id='cerrarLink_" + contadorDocumentoCopiadoAVisualizar + "' onclick='eliminarDocumentoACopiar(" + contadorDocumentoCopiadoAVisualizar + ")'style='color:red;'>&ensp;X</a><br>");
            $("#divDocumentoRelacion").css("height", $("#divDocumentoRelacion").height() + 20);

            if(!isEmpty(request.documentoRelacion[contadorDocumentoCopiadoAVisualizar])){
                if(isEmpty(request.documentoRelacion[contadorDocumentoCopiadoAVisualizar].detalleLink)){
                    request.documentoRelacion[contadorDocumentoCopiadoAVisualizar].detalleLink = detalleLink;
                    request.documentoRelacion[contadorDocumentoCopiadoAVisualizar].posicion = contadorDocumentoCopiadoAVisualizar;
                    contadorDocumentoCopiadoAVisualizar++;
                    detalleLink = null;
                }
            }
        }*/
        valoresFormularioDetalle = data;
        agregarConfirmado();
        asignarImporteDocumento();
    }
}

var banderaEliminarDocumentoRelacion = 0;
var eliminadosArray = new Array();
function eliminarDocumentoACopiar(indice) {
    detalleDocumentoRelacion = [];

    numeroItemFinal = 0;
    var tipoRelacion = request.documentoRelacion[indice].tipo;
    var contRelacion = 1;

    if (tipoRelacion == 1) {
        loaderShow();
        detalle = [];
        indiceLista = [];
        banderaCopiaDocumento = 0;
        indexDetalle = 0;
        asignarImporteDocumento();
        obtenerUtilidadesGenerales();
        nroFilasInicial = parseInt(dataDocumentoTipo[0]['cantidad_detalle']);

        if (banderaVerTodasFilas === 1) {
            nroFilasReducida = nroFilasInicial;
        } else {
            nroFilasReducida = 5;
        }


        mapaEstadoHeaders.set((request.documentoRelacion[indice].documentoId).toString(), false);
        htmlUniqueHeaders.delete(request.documentoRelacion[indice].documentoId);
        mapaHeaders.delete(request.documentoRelacion[indice].detalleLink);


        $.each(request.documentoRelacion, function (index, item) {
            if (item.documentoPadreId == request.documentoRelacion[indice].documentoId) {
                //mapaEstadoHeaders.set((request.documentoRelacion[indice].documentoId).toString(), false);
                request.documentoRelacion[index].documentoId = null;
                request.documentoRelacion[index].movimientoId = null;


                contRelacion++;
            }
        });
    }

    $("#divDocumentoRelacion").css("height", $("#divDocumentoRelacion").height() - 20 * contRelacion);

    banderaEliminarDocumentoRelacion = 1;

    if (tipoRelacion == 1) {
        //        $('#dgDetalle').empty();
        //LIMPIAR DATATABLE
        $('#datatable').DataTable().clear().destroy();
        llenarTablaDetalle(dataCofiguracionInicial);
        //REINICIALIZAAR DATATABLE
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

    var banderaExisteDocumentoRelacionado = 0;
    //$("#contenedorDetalle").css("height", $("#contenedorDetalle").height() - (40 * indexDetalle));

    request.documentoRelacion[indice].documentoId = null;
    request.documentoRelacion[indice].movimientoId = null;
    $('#link_' + indice).remove();
    $('#cerrarLink_' + indice).remove();

    $('#linkDocumentoACopiar').empty();
    $.each(request.documentoRelacion, function (index, item) {

        if (!isEmpty(item.documentoId)) {
            $('#linkDocumentoACopiar').append("<a id='link_" + item.posicion + "' onclick='visualizarDocumentoRelacion(" + item.posicion + ")' id='linkDocumentoACopiar' style='color:#0000FF'>[" + item.detalleLink + "]</a>");

            if (item.tipo == 1) {
                $('#linkDocumentoACopiar').append("<a id='cerrarLink_" + item.posicion + "' onclick='eliminarDocumentoACopiar(" + item.posicion + ")'style='color:red;'>&ensp;X</a>");
            }

            $('#linkDocumentoACopiar').append("<br>");
            banderaExisteDocumentoRelacionado = 1;
        }
    });

    if (banderaExisteDocumentoRelacionado === 0) {
        bandera.mostrarDivDocumentoRelacion = false;
        $('#divDocumentoRelacion').hide();
        $("#divChkDocumentoRelacion").show();
        $("#chkDocumentoRelacion").prop("checked", "checked");
    }

    if (tipoRelacion == 1) {
        loaderShow();
        ax.setAccion("obtenerDocumentoRelacionDetalle");
        ax.addParamTmp("documentos_relacionados", request.documentoRelacion);
        ax.addParamTmp("monedaId", select2.obtenerValor("cboMoneda"));
        ax.addParamTmp("fechaEmision", $('#datepicker_' + fechaEmisionId).val());
        ax.consumir();
    }

}

function eliminarDocumentoACopiarSinDetalle(indice) {
    var contRelacion = 1;
    //eliminar las relaciones hijas
    $.each(request.documentoRelacion, function (index, item) {
        if (item.documentoPadreId == request.documentoRelacion[indice].documentoId) {
            request.documentoRelacion[index].documentoId = null;
            request.documentoRelacion[index].movimientoId = null;

            contRelacion++;
        }
    });


    $("#divDocumentoRelacion").css("height", $("#divDocumentoRelacion").height() - 20 * contRelacion);
    banderaEliminarDocumentoRelacion = 1;

    var banderaExisteDocumentoRelacionado = 0;
    //$("#contenedorDetalle").css("height", $("#contenedorDetalle").height() - (40 * indexDetalle));

    request.documentoRelacion[indice].documentoId = null;
    request.documentoRelacion[indice].movimientoId = null;
    $('#link_' + indice).remove();
    $('#cerrarLink_' + indice).remove();

    $('#linkDocumentoACopiar').empty();
    $.each(request.documentoRelacion, function (index, item) {

        if (!isEmpty(item.documentoId)) {
            $('#linkDocumentoACopiar').append("<a id='link_" + item.posicion + "' onclick='visualizarDocumentoRelacion(" + item.posicion + ")' id='linkDocumentoACopiar' style='color:#0000FF'>[" + item.detalleLink + "]</a>");

            if (item.tipo == 1) {
                $('#linkDocumentoACopiar').append("<a id='cerrarLink_" + item.posicion + "' onclick='eliminarDocumentoACopiarSinDetalle(" + item.posicion + ")'style='color:red;'>&ensp;X</a>");
            }

            $('#linkDocumentoACopiar').append("<br>");

            banderaExisteDocumentoRelacionado = 1;
        }
    });

    if (banderaExisteDocumentoRelacionado === 0) {
        bandera.mostrarDivDocumentoRelacion = false;
        $('#divDocumentoRelacion').hide();
        $("#divChkDocumentoRelacion").show();
        $("#chkDocumentoRelacion").prop("checked", "checked");
    }
}

function visualizarDocumentoRelacion(indice) {
    if (!isEmpty(request.documentoRelacion[indice].documentoId) && !isEmpty(request.documentoRelacion[indice].movimientoId)) {
        ax.setAccion("obtenerDocumentoRelacionVisualizar");
        ax.addParamTmp("documentoId", request.documentoRelacion[indice].documentoId);
        ax.addParamTmp("movimientoId", request.documentoRelacion[indice].movimientoId);
        ax.consumir();
    }
}

function onResponseObtenerDocumentoRelacionVisualizar(data) {
    cargarDataDocumento(data.dataDocumento);
    cargarDetalleDocumento(data.detalleDocumento);
    $('#modalDetalleDocumento').modal('show');
}

function cargarDataDocumento(data) {
    $("#formularioDetalleDocumento").empty();
    //$("#formularioDetalleDocumento").css("height", 75 * data.length);


    if (!isEmpty(data)) {
        $('#nombreDocumentoTipo').html(data[0]['nombre_documento']);
        // Mostraremos la data en filas de dos columnas
        $.each(data, function (index, item) {

            appendFormDetalle('</div>');
            appendFormDetalle('<div class="row">');

            var html = '<div class="form-group col-md-12"><div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">' +
                '<label>' + item.descripcion + '</label>' +
                '</div><div class="col-lg-8 col-md-8 col-sm-6 col-xs-6">';

            var valor = quitarNULL(item.valor);

            if (!isEmpty(valor)) {
                switch (parseInt(item.tipo)) {
                    case 1:
                        valor = formatearCantidad(valor);
                        break;
                    case 3:
                        valor = fechaArmada(valor);
                        break;
                    case 9:
                    case 10:
                    case 11:
                        valor = fechaArmada(valor);
                        break;
                    case 14:
                    case 15:
                    case 16:
                        valor = formatearNumero(valor);
                        break;
                }
            }

            html += '' + valor + '';

            html += '</div></div>';
            appendFormDetalle(html);
        });
        appendFormDetalle('</div>');
    }
}

function cargarDetalleDocumento(data) {

    if (!isEmptyData(data)) {
        $.each(data, function (index, item) {
            data[index]["cantidad"] = formatearCantidad(data[index]["cantidad"]);
            data[index]["precioUnitario"] = formatearNumero(data[index]["precioUnitario"]);
            data[index]["importe"] = formatearNumero(data[index]["importe"]);
        });
        $('#datatable2').dataTable({
            "order": [[0, "desc"]],
            "data": data,
            "columns": [
                { "data": "organizador" },
                { "data": "cantidad", "sClass": "alignRight" },
                { "data": "unidadMedida" },
                { "data": "descripcion" },
                { "data": "precioUnitario", "sClass": "alignRight" },
                { "data": "importe", "sClass": "alignRight" }
            ],
            "destroy": true
        });
    } else {
        var table = $('#datatable2').DataTable();
        table.clear().draw();
    }
}

function obtenerCheckDocumentoACopiar() {
    if ($('#divChkDocumentoRelacion').attr("style") == "display: none;") {
        cabecera.chkDocumentoRelacion = 1;
        return;
    }

    if (document.getElementById('chkDocumentoRelacion').checked) {
        cabecera.chkDocumentoRelacion = 1;
    } else {
        cabecera.chkDocumentoRelacion = 0;
    }
}

function negrita(cadena) {
    return "<b>" + cadena + "</b>";
}


function actualizarBusquedaDocumentoRelacion() {
    buscarDocumentoRelacionPorCriterios();
}

var listaDetalleFaltante;
var listaDetalleOriginal;
function onResponseEnviar(data) {
    cerrarModalAnticipo();
    if (!isEmpty(data.generarDocumentoAdicional)) {
        habilitarBoton();
        loaderClose();
        $('#modalDocumentoGenerado').modal('show');

        $("#dtBodyDocumentoGenerado").empty();

        var dataOrganizador = data.dataOrganizador;
        var dataProveedor = data.dataProveedor;
        var dataDocumentoTipo = data.dataDocumentoTipo;

        listaDetalleOriginal = data.dataDetalle;

        //        var cuerpo = "";
        //LLENAR TABLA DETALLE
        for (var i = 0; i < listaDetalleOriginal.length; i++) {
            //cuerpo += llenarFilaDetalleFaltantesTabla(i, listaDetalleOriginal[i]);
            $('#dtBodyDocumentoGenerado').append(llenarFilaDetalleFaltantesTabla(i, listaDetalleOriginal[i]));
        }
        //        $('#dtBodyDocumentoGenerado').append(cuerpo);

        //LLENAR COMBOS
        for (var i = 0; i < listaDetalleOriginal.length; i++) {
            //cargarOrganizadorDetalleCombo(data.organizador, i);
            //cargarUnidadMedidadDetalleCombo(i);
            cargarTipoDocumentoCombo(dataDocumentoTipo, dataOrganizador, dataProveedor, i);
            cargarOrganizadorProveedorCombo(dataOrganizador, dataProveedor, i);
        }
    } else if (!isEmpty(data.anticipos)) {
        mostrarAnticipos(data);
    } else {
        if (!isEmpty(data.dataPlantilla)) {
            dibujarModalCorreos(data);
        } else if (!isEmpty(data.dataDocumentoPago)) {
            abrirModalPagos(data);

        } else if (!isEmpty(data.dataAtencionSolicitud)) {
            //asignarAtencion();
            abrirModalAtencionSolicitud(data);
        } else if (!isEmpty(data.resEfact) && data.resEfact.esDocElectronico == 1) {
            onResponseRespuestaEfact(data);
        } else if (boton.accion == 'enviarEImprimir') {
            var dataImp = data.dataImprimir;

            if (!isEmpty(dataImp.dataDocumento)) {
                cargarDatosImprimir(dataImp, 1);
            } else if (!isEmpty(dataImp.iReport)) {
                abrirDocumentoPDF(dataImp, URL_BASE + '/reporteJasper/documentos/');
            } else {
                abrirDocumentoPDF(dataImp, 'vistas/com/movimiento/documentos/');
            }
        } else {
            cargarPantallaListarCompra();
            swal({
                title: data.documentoTipoDescripcion + ", generada exitosamente!",
                text: "Con Serie y Número: " + data.serieNumero + "",
                type: "success",
                confirmButtonColor: "#33b86c",
                confirmButtonText: "Aceptar",
                timer: 4000
            });
        }
    }

}

function onResponseRespuestaEfact(data) {
    var dataDocElec = data.resEfact.respDocElectronico;
    //PENDIENTE DE ENVIO
    if (dataDocElec.tipoMensaje == 0) {
        swal({
            title: "Pendiente de registro en SUNAT",
            text: dataDocElec.mensaje + "<br><br> Se registró en el sistema, posteriormente se intentará registrar en SUNAT.",
            type: "warning",
            html: true,
            showCancelButton: false,
            confirmButtonColor: "#33b86c",
            confirmButtonText: "Aceptar",
            closeOnConfirm: true,
            closeOnCancel: true
        }, function (isConfirm) {
            if (isConfirm) {
                if (!isEmpty(dataDocElec.urlPDF)) {
                    //window.open(URL_BASE + 'pdf2.php?url_pdf=' + dataDocElec.urlPDF + '&nombre_pdf=' + dataDocElec.nombrePDF);
                    window.open(dataDocElec.urlPDF);
                }
                cargarPantallaListarCompra();
            }
        });
    }
    //CORRECTO
    if (dataDocElec.tipoMensaje == 1) {
        //        mostrarOk(dataDocElec.mensaje);
        swal({
            title: "Registro correcto",
            text: dataDocElec.mensaje,
            type: "success",
            html: true,
            showCancelButton: false,
            confirmButtonColor: "#33b86c",
            confirmButtonText: "Aceptar",
            closeOnConfirm: true,
            closeOnCancel: true
        }, function (isConfirm) {
            if (isConfirm) {
                if (!isEmpty(dataDocElec.urlPDF)) {
                    //window.open(URL_BASE + 'pdf2.php?url_pdf=' + dataDocElec.urlPDF + '&nombre_pdf=' + dataDocElec.nombrePDF);
                    window.open(dataDocElec.urlPDF);
                }
                cargarPantallaListarCompra();
            }
        });
    }

    //ERROR CONTROLADO SUNAT NO VA A REGISTRAR - WARNING EXCEPTION EN NEGOCIO - PARA NEGAR COMMIT

    //ERROR DESCONOCIDO
    if (dataDocElec.tipoMensaje == 2 || dataDocElec.tipoMensaje == 3 || dataDocElec.tipoMensaje == 4) {
        var mensaje = dataDocElec.mensaje;
        if (dataDocElec.tipoMensaje == 4) {
            mensaje += "<br><br> Se registró en el sistema, pero fue rechazada por SUNAT.";
        } else {
            mensaje += "<br><br> Se registró en el sistema, posteriormente se intentará registrar en SUNAT."
        }
        swal({
            title: "Error desconocido",
            text: mensaje,
            type: "warning",
            html: true,
            showCancelButton: false,
            confirmButtonColor: "#33b86c",
            confirmButtonText: "Aceptar",
            closeOnConfirm: true,
            closeOnCancel: true
        }, function (isConfirm) {
            if (isConfirm) {
                if (!isEmpty(dataDocElec.urlPDF)) {
                    window.open(URL_BASE + 'pdf2.php?url_pdf=' + dataDocElec.urlPDF + '&nombre_pdf=' + dataDocElec.nombrePDF);
                }
                cargarPantallaListarCompra();
            }
        });
    }

}


function abrirDocumentoPDF(data, contenedor) {
    var link = document.createElement("a");
    link.download = data.nombre + '.pdf';
    link.href = contenedor + data.pdf;
    link.click();


    //ax.setAccion("eliminarPDF");
    //ax.addParamTmp("url", data.url);
    //ax.consumir();

    setTimeout(function () {
        //eliminarPDF(data.url);
        eliminarPDF(contenedor + data.pdf);
    }, 3000);
}

function eliminarPDF(url) {
    ax.setAccion("eliminarPDF");
    ax.addParamTmp("url", url);
    ax.consumir();
}

var dataRespuestaCorreo;
function dibujarModalCorreos(data) {

    dataRespuestaCorreo = data;

    $("#tbodyDetalleCorreos").empty();
    var html = '';

    if (!isEmpty(data.dataCorreos)) {
        $.each(data.dataCorreos, function (index, itemh) {
            html += '<tr>' +
                '<td>' +
                '<div class="checkbox" style="margin: 0px;">' +
                '<label class="cr-styled">' +
                '<input onclick="" type="checkbox" name="chekCorreo" id="correo' + index + '" value="' + itemh + '" checked>' +
                '<i class="fa"></i> ' +
                itemh +
                '</label>' +
                ' </div>' +
                '</td>' +
                '</tr>'
                ;
        });

        $("#tbodyDetalleCorreos").append(html);
    } else {
        $("#rowDataTableCorreo").hide();
    }

    $('#modalCorreos').modal('show');
}

function enviarCorreosMovimiento() {
    var txtCorreo = $('#txtCorreo').val();
    var correosSeleccionados = new Array();

    if (!isEmpty(dataRespuestaCorreo.dataCorreos)) {
        var chekCorreo = document.getElementsByName('chekCorreo');

        $.each(chekCorreo, function (index, item) {
            if (item.checked == true) {
                correosSeleccionados.push(item.value);
            }
        });
    }

    if (isEmpty(txtCorreo) && isEmpty(correosSeleccionados)) {
        $.Notification.autoHideNotify('warning', 'top right', 'Validación', "Seleccione o ingrese un correo, para enviar email.");
        return;
    }

    loaderShow('#modalCorreos');
    ax.setAccion("enviarCorreosMovimiento");
    ax.addParamTmp("txtCorreo", txtCorreo);
    ax.addParamTmp("correosSeleccionados", correosSeleccionados);
    ax.addParamTmp("respuestaCorreo", dataRespuestaCorreo);
    ax.addParamTmp("comentario", $('#txtComentario').val());
    ax.consumir();
}

function cancelarEnvioCorreos() {
    $('#modalCorreos').modal('hide');
    $('.modal-backdrop').hide();
    cargarPantallaListarCompra();
}

function llenarFilaDetalleFaltantesTabla(indice, dataDetalle) {
    var fila = "<tr id=\"trDetalleFaltante_" + indice + "\">"
        + "<td style='border:0; width: 40%; vertical-align: middle;'>" + agregarBienDetalleFaltanteTabla(indice, dataDetalle['bienDesc']) + "</td>"
        + "<td style='border:0; width: 10%; vertical-align: middle; '>" + agregarCantidadDetalleFaltanteTabla(indice, dataDetalle['cantidad']) + "</td>"
        + "<td style='border:0; width: 20%; vertical-align: middle; '>" + agregarTipoDocumento(indice) + "</td>"
        + "<td style='border:0; width: 30%; vertical-align: middle; '>" + agregarComboOrganizadorProveedor(indice) + "</td>"
        + "</tr>";

    return fila;

}

function agregarBienDetalleFaltanteTabla(i, valor) {
    var $html = "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\">" +
        "<input type=\"text\" id=\"txtBien_" + i + "\" name=\"txtBien_" + i + "\" class=\"form-control\" required=\"\" aria-required=\"true\" value=\"" + valor + "\" style=\"text-align: left;\" readonly=true /></div>";

    return $html;
}

function agregarCantidadDetalleFaltanteTabla(i, valor) {
    var $html = "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\">" +
        "<input type=\"text\" id=\"txtCantidadFaltante_" + i + "\" name=\"txtCantidadFaltante_" + i + "\" class=\"form-control\" required=\"\" aria-required=\"true\" value=\"" + valor + "\" style=\"text-align: right;\" readonly=true /></div>";

    return $html;
}

function agregarTipoDocumento(i) {
    var $html = "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\">" +
        "<select name=\"cboTipoDocumento_" + i + "\" id=\"cboTipoDocumento_" + i + "\" class=\"select2\">" +
        "</select></div>";

    return $html;
}

function agregarComboOrganizadorProveedor(i) {
    var $html = "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\">" +
        "<select name=\"cboOrganizadorProveedor_" + i + "\" id=\"cboOrganizadorProveedor_" + i + "\" class=\"select2\">" +
        "</select></div>";

    return $html;
}

function cargarTipoDocumentoCombo(dataDocumentoTipo, dataOrganizador, dataProveedor, indice) {
    //loaderShow();

    // tipo entrada salida
    //    $('#cboTipoDocumento_' + indice).append('<option value="1">Guía</option>');
    //    $('#cboTipoDocumento_' + indice).append('<option value="2">Solicitud de pedido</option>');

    if (!isEmpty(dataOrganizador[indice])) {
        select2.cargar("cboTipoDocumento_" + indice, dataDocumentoTipo, "documento_tipo_id", "descripcion");
        select2.asignarValor("cboTipoDocumento_" + indice, dataDocumentoTipo[0]['documento_tipo_id']);
    } else {
        $('#cboTipoDocumento_' + indice).append('<option value="-1">Solicitud de compra</option>');
        select2.asignarValor("cboTipoDocumento_" + indice, -1);
    }

    $("#cboTipoDocumento_" + indice).select2({
        width: '100%'
    }).on("change", function (e) {

        //loaderShow();
        //        asignarOrganizadorProveedor(e.val, indice, dataOrganizador, dataProveedor);
        //obtenerStockActual(indice);
    });

}

function asignarOrganizadorProveedor(tipoDocumentoId, indice, dataOrganizador, dataProveedor) {
    if (tipoDocumentoId != -1) {
        if (!isEmpty(dataOrganizador[indice])) {
            select2.cargar("cboOrganizadorProveedor_" + indice, dataOrganizador[indice], "organizadorId", "descripcion");
            select2.asignarValor("cboOrganizadorProveedor_" + indice, dataOrganizador[indice][0]["organizadorId"]);
        }
    } else {
        if (!isEmpty(dataProveedor[indice])) {
            select2.cargar("cboOrganizadorProveedor_" + indice, dataProveedor[indice], "persona_id", "persona_nombre");
            select2.asignarValor("cboOrganizadorProveedor_" + indice, dataProveedor[indice][0]["persona_id"]);
        }
    }

    //alert(tipoDocumentoId)    ;
}

function cargarOrganizadorProveedorCombo(dataOrganizador, dataProveedor, indice) {

    $("#cboOrganizadorProveedor_" + indice).select2({
        width: '100%'
    });

    if (!isEmpty(dataOrganizador[indice])) {
        select2.cargar("cboOrganizadorProveedor_" + indice, dataOrganizador[indice], "organizadorId", "descripcion");
        select2.asignarValor("cboOrganizadorProveedor_" + indice, dataOrganizador[indice][0]["organizadorId"]);
    } else {
        if (!isEmpty(dataProveedor[indice])) {
            // solo proveedor
            //            select2.asignarValor("cboTipoDocumento_" + indice, 2);
            $("#cboTipoDocumento_" + indice).attr('disabled', 'disabled');

            select2.cargar("cboOrganizadorProveedor_" + indice, dataProveedor[indice], "persona_id", "persona_nombre");
            select2.asignarValor("cboOrganizadorProveedor_" + indice, dataProveedor[indice][0]["persona_id"]);
        }
    }
}

var listaDetallePedidos;
var listaDetalleGuia;
var listaDetalleVenta;

function guardarDocumentoGenerado() {

    listaDetallePedidos = [];
    listaDetalleGuia = [];
    listaDetalleVenta = [];
    var proveedor = null;

    //obtenemos el tipo de documento
    var documentoTipoId = select2.obtenerValor("cboDocumentoTipo");

    var indGuia = 0;
    var indPed = 0;


    var totalGuia = 0;
    var totalProv = 0;
    for (var i = 0; i < listaDetalleOriginal.length; i++) {
        var tipoDocumentoId = select2.obtenerValor("cboTipoDocumento_" + i);
        var organizadorProveedorId = select2.obtenerValor("cboOrganizadorProveedor_" + i);
        var organizadorProveedorText = select2.obtenerText("cboOrganizadorProveedor_" + i);

        if (tipoDocumentoId != -1) {
            listaDetalleGuia.push(listaDetalleOriginal[i]);
            listaDetalleGuia[indGuia]["tipoDocumentoId"] = tipoDocumentoId;
            listaDetalleGuia[indGuia]["organizadorId"] = organizadorProveedorId;
            listaDetalleGuia[indGuia]["organizadorDesc"] = organizadorProveedorText;

            totalGuia = totalGuia + listaDetalleGuia[indGuia]["cantidad"] * listaDetalleGuia[indGuia]["precio"];

            indGuia++;
        } else {
            proveedor = select2.obtenerValor("cboOrganizadorProveedor_" + i);

            listaDetallePedidos.push(listaDetalleOriginal[i]);
            //            listaDetallePedidos[indPed]["organizadorId"] = organizadorIdDefectoTM;
            listaDetallePedidos[indPed]["proveedorId"] = proveedor;

            indPed++;
        }
    }

    var checkIgv = 0;

    if (!isEmpty(importes.subTotalId)) {
        if (document.getElementById('chkIncluyeIGV').checked) {
            checkIgv = 1;
        }
    } else {
        checkIgv = opcionIGV;
    }

    $('#modalDocumentoGenerado').modal('hide');
    loaderShow();
    deshabilitarBoton();
    ax.setAccion("guardarDocumentoGenerado");
    //guardar guia y nota
    ax.addParamTmp("detalleGuia", listaDetalleGuia);
    ax.addParamTmp("detallePedido", listaDetallePedidos);
    //ax.addParamTmp("proveedorId", proveedor);
    ax.addParamTmp("totalGuia", totalGuia);
    //ax.addParamTmp("totalProv", totalProv);

    //guardar venta
    ax.addParamTmp("empresaId", commonVars.empresa);
    ax.addParamTmp("documentoTipoId", documentoTipoId);
    ax.addParamTmp("camposDinamicos", camposDinamicos);
    ax.addParamTmp("detalleVenta", listaDetalleOriginal);
    ax.addParamTmp("documentoARelacionar", request.documentoRelacion);
    ax.addParamTmp("valor_check", cabecera.chkDocumentoRelacion);
    ax.addParamTmp("comentario", $('#txtComentario').val());
    ax.addParamTmp("checkIgv", checkIgv);
    ax.addParamTmp("monedaId", select2.obtenerValor("cboMoneda"));
    ax.addParamTmp("accionEnvio", boton.accion);
    ax.consumir();
}

function obtenerBienTipoIdPorBienId(bienId) {
    var bienTipoId = 0;

    $.each(dataCofiguracionInicial.bien, function (index, item) {
        if (item.id == bienId) {
            bienTipoId = item.bien_tipo_id;
            return false;
        }
    });
    return bienTipoId;
}

function limpiarDetalle() {
    //detalle = [];
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

var numeroItemFinal = 0;
function agregarFila() {
    if (nroFilasInicial > nroFilasReducida || parseInt(dataDocumentoTipo[0]['cantidad_detalle']) == 0) {
        //LLENAR TABLA DETALLE
        var fila = llenarFilaDetalleTabla(nroFilasReducida);

        $('#datatable tbody').append(fila);

        //LLENAR COMBOS
        cargarOrganizadorDetalleCombo(dataCofiguracionInicial.organizador, nroFilasReducida);
        cargarUnidadMedidadDetalleCombo(nroFilasReducida);
        cargarBienDetalleCombo(dataCofiguracionInicial.bien, nroFilasReducida);
        cargarPrecioTipoDetalleCombo(dataCofiguracionInicial.precioTipo, nroFilasReducida);
        cargarAgenciaDetalleCombo(dataCofiguracionInicial.dataAgencia, nroFilasReducida);
        cargarCeCoDetalleCombo(dataCofiguracionInicial.centroCostoRequerimiento, nroFilasReducida);
        var compras = [{ "id": 1, "descripcion": "Si" }, { "id": 2, "descripcion": "No" }];
        cargarComprasDetalleCombo(compras, nroFilasReducida);
        // nroFilasInicial++;
        nroFilasReducida++;

        $("#contenedorDetalle").css("height", inicialAlturaDetalle + 38 * (nroFilasReducida - nroFilasEliminados));
    } else {
        $('#divTodasFilas').hide();
        $('#divAgregarFila').hide();
    }

}


// funcionalidad de tramos registro

function verificarTipoUnidadMedida(indice) {
    var bienId = select2.obtenerValor("cboBien_" + indice);
    var unidadMedidaId = select2.obtenerValor("cboUnidadMedida_" + indice);
    if (isEmpty(bienId)) {
        $.Notification.autoHideNotify('warning', 'top right', 'Validación', "Seleccionar un producto");
    } else if (isEmpty(unidadMedidaId)) {
        $.Notification.autoHideNotify('warning', 'top right', 'Validación', "Seleccionar unidad de medida");
    } else {
        //verificar el tipo de unidad de medida (longitud)
        loaderShow();
        ax.setAccion("verificarTipoUnidadMedidaParaTramo");
        ax.addParamTmp("unidadMedidaId", unidadMedidaId);
        ax.setTag(indice);
        ax.consumir();

    }
}

function onResponseVerificarTipoUnidadMedidaParaTramo(data) {
    if (isEmpty(data)) {
        mostrarAdvertencia("Seleccione una unidad de medida de tipo longitud");
    } else {

        var tituloModal = '<strong>PRODUCTO: ' + select2.obtenerText("cboBien_" + data.indice) + '</strong>';

        limpiarMensajesTramo();
        $('#txtCantidadTramo').val('');
        $('#indiceTramo').val(data.indice);

        // unidad medida (metros)
        $('#cboUnidadMedidaTramo').empty();
        $('#cboUnidadMedidaTramo').append('<option value="157">Metro(s)</option>');

        $("#cboUnidadMedidaTramo").select2({
            width: '100%'
        });

        $('#bienTramoRegistro').empty();
        $('#bienTramoRegistro').append(tituloModal);
        $('.modal-title').empty();
        $('.modal-title').append("<strong>REGISTRAR TRAMO</strong>");
        $('#modalTramoBienRegistro').modal('show');

    }
}

function registrarTramoBien() {
    var unidadMedidaId = select2.obtenerValor("cboUnidadMedidaTramo");
    var cantidadTramo = $('#txtCantidadTramo').val();
    var indiceTramo = $('#indiceTramo').val();

    if (validarFormularioModalTramo(unidadMedidaId, cantidadTramo)) {
        var bienId = select2.obtenerValor("cboBien_" + indiceTramo);

        loaderShow();
        ax.setAccion("registrarTramoBien");
        ax.addParamTmp("unidadMedidaId", unidadMedidaId);
        ax.addParamTmp("cantidadTramo", cantidadTramo);
        ax.addParamTmp("bienId", bienId);
        ax.consumir();
    }
}

function validarFormularioModalTramo(unidadMedidaId, cantidadTramo) {
    var bandera = true;
    var espacio = /^\s+$/;
    limpiarMensajesTramo();

    if (unidadMedidaId === "" || unidadMedidaId === null || espacio.test(unidadMedidaId) || unidadMedidaId.length === 0) {
        $("#msjTipoUnidadMedidaTramo").text("La unidad de medida es obligatorio").show();
        bandera = false;
    }

    if (cantidadTramo === "" || cantidadTramo === null || espacio.test(cantidadTramo) || cantidadTramo.length === 0) {
        $("#msjCantidadTramo").text("Cantidad es obligatorio").show();
        bandera = false;
    } else if (cantidadTramo <= 0) {
        $("#msjCantidadTramo").text("Cantidad tiene que se positivo").show();
        bandera = false;
    }

    return bandera;
}

function limpiarMensajesTramo() {
    $("#msjTipoUnidadMedidaTramo").hide();
    $("#msjCantidadTramo").hide();
}

function onResponseRegistrarTramoBien(data) {
    if (data[0]["vout_exito"] == 0) {
        mostrarAdvertencia(data[0]["vout_mensaje"]);
    } else {
        mostrarOk(data[0]["vout_mensaje"]);
        $('#modalTramoBienRegistro').modal('hide');
    }
}

// funcionalidad de tramos busqueda

function listarTramosBien(indice) {
    var bienId = select2.obtenerValor("cboBien_" + indice);
    if (bienId == null) {
        $.Notification.autoHideNotify('warning', 'top right', 'Validación', "Seleccionar un producto");
    } else {
        loaderShow();
        ax.setAccion("obtenerTramoBien");
        ax.addParamTmp("bienId", bienId);
        ax.setTag(indice);
        ax.consumir();
    }
}

function onResponseObtenerTramoBien(data, indice) {


    var tituloModal = '<strong>PRODUCTO: ' + select2.obtenerText("cboBien_" + indice) + '</strong>';
    //    Unidad medida	Cantidad  Accion

    var dataTramoBien = [];
    if (!isEmpty(data)) {
        var accion = '';

        $.each(data, function (index, item) {
            accion = "<a onclick=\"cambiarACantidadTramo(" + indice + "," + item.unidad_medida_id + "," + item.cantidad + "," + item.bien_tramo_id + ");\">" +
                "<i class=\"fa fa-arrow-down\"  tooltip-btndata-toggle='tooltip'  style=\"color:#04B404;\" title=\"Seleccionar tramo\"></i></a>";

            dataTramoBien.push([item.unidad_medida_descripcion,
            item.cantidad,
                accion]);
        });
    }

    $('#datatableTramoBien').dataTable({
        order: [[1, "asc"]],
        "ordering": false,
        "data": dataTramoBien,
        "columns": [
            { "data": "0" },
            { "data": "1", "sClass": "alignRight" },
            { "data": "2", "sClass": "alignCenter" }
        ],
        columnDefs: [
            {
                "render": function (data, type, row) {
                    return parseFloat(data).formatMoney(2, '.', ',');
                },
                "targets": 1
            }
        ],
        "destroy": true
    });


    $('#bienTramoBusqueda').empty();
    $('#bienTramoBusqueda').append(tituloModal);
    $('.modal-title').empty();
    $('.modal-title').append("<strong>SELECCIONAR TRAMO</strong>");
    $('#modalTramoBienBusqueda').modal('show');
}

var bienTramoId = null;
function cambiarACantidadTramo(indice, unidadMedidaId, cantidad, tramoId) {
    bienTramoId = tramoId;

    if (existeColumnaCodigo(12)) {
        document.getElementById("txtCantidad_" + indice).value = devolverDosDecimales(cantidad);
    }
    select2.asignarValor("cboUnidadMedida_" + indice, unidadMedidaId);
    $("#cboUnidadMedida_" + indice).select2({ width: anchoUnidadMedidaTD + 'px' });

    $('#modalTramoBienBusqueda').modal('hide');
    obtenerStockActual(indice);
    hallarSubTotalDetalle(indice);
}

// recalculo de precio de compra y utilidades
function recalculoPrecioCompraUtilidades() {
    //    alert('recalculoPrecioCompraUtilidades');

    primeraFechaEmision = $('#datepicker_' + fechaEmisionId).val();
    //    banderaPrimeraFE=true;

    $.each(detalle, function (indice, item) {
        obtenerPrecioCompra(item.index, item.unidadMedidaId, item.bienId);
    });

}

function obtenerPrecioCompra(indice, unidadMedidaId, bienId) {
    loaderShow();
    ax.setAccion("obtenerPrecioCompra");
    ax.addParamTmp("unidadMedidaId", unidadMedidaId);
    ax.addParamTmp("bienId", bienId);
    ax.addParamTmp("monedaId", select2.obtenerValor("cboMoneda"));
    ax.addParamTmp("fechaEmision", $('#datepicker_' + fechaEmisionId).val());
    ax.setTag(indice);
    ax.consumir();
}

function onResponseObtenerPrecioCompra(data) {
    if (!isEmpty(data)) {
        var operador = obtenerOperador();

        if (existeColumnaCodigo(1)) {
            $("#txtPrecioCompra_" + data.indice).html(devolverDosDecimales(data.precioCompra * operador));
        } else {
            varPrecioCompra = devolverDosDecimales(data.precioCompra * operador);
        }
        hallarSubTotalDetalle(data.indice);
    }
}

// recalculo de precio de compra y utilidades
function modificarPrecioCompra() {

    $.each(detalle, function (indice, item) {
        obtenerPrecioCompra(item.index, item.unidadMedidaId, item.bienId);
    });

}

function onResponseObtenerPersonaDireccionTexto(data) {
    if (isEmpty(data)) {
        $('#txt_' + textoDireccionId).val('');
    } else {
        $('#txt_' + textoDireccionId).val(data[0]['direccion']);
    }

}

function modificarDetallePrecios() {
    if (!isEmpty(detalle)) {
        loaderShow();
        var operador = obtenerOperador();

        ax.setAccion("modificarDetallePrecios");
        ax.addParamTmp("detalle", detalle);
        ax.addParamTmp("monedaId", select2.obtenerValor("cboMoneda"));
        ax.addParamTmp("operador", operador);
        ax.addParamTmp("fechaEmision", $('#datepicker_' + fechaEmisionId).val());
        ax.consumir();
    }
}

function onResponseModificarDetallePrecios(data) {
    $.each(data, function (indice, item) {
        if (existeColumnaCodigo(5)) {
            $("#txtPrecio_" + item.index).val(devolverDosDecimales(item.precio));
        }
        if (existeColumnaCodigo(1)) {
            $("#txtPrecioCompra_" + item.index).html(devolverDosDecimales(item.precioCompra));
        } else {
            varPrecioCompra = devolverDosDecimales(item.precioCompra);
        }
        hallarSubTotalDetalle(item.index);
    });
}

function modificarSimbolosMoneda(monedaId, moneda) {
    //    alert(simbolo);
    monedaSimbolo = moneda.simbolo;
    monedaBase = monedaId;

    $('#simTotal').html(monedaSimbolo);
    $('#simPercepcion').html(monedaSimbolo);
    $('#simIGV').html(monedaSimbolo);
    $('#simSubTotal').html(monedaSimbolo);
    $('#simTotalUtildiad').html(monedaSimbolo);
    $('#simPU').html(monedaSimbolo);
    $('#simST').html(monedaSimbolo);
    $('#simPC').html(monedaSimbolo);
    $('#simUD').html(monedaSimbolo);
}

function modificarPreciosMoneda(monedaId, moneda) {
    if (!isEmpty(detalle) && existeColumnaCodigo(5)) {
        swal({
            title: " ¿Desea continuar?",
            text: "Se va a modificar los precios a " + moneda.descripcion,
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#33b86c",
            confirmButtonText: "Si!",
            cancelButtonColor: '#d33',
            cancelButtonText: "No!",
            closeOnConfirm: false,
            closeOnCancel: true
        }, function (isConfirm) {
            if (isConfirm) {
                modificarSimbolosMoneda(monedaId, moneda);
                alertaModificarPrecioMoneda(monedaId, moneda);

            } else {
                select2.asignarValorQuitarBuscador("cboMoneda", monedaBase);
            }
        });
    }
}

function alertaModificarPrecioMoneda(monedaId, moneda) {
    swal({
        title: "Escoja una opción",
        text: "1: Convertir el precio con el tipo de cambio de la fecha de emisión.\n\
                   2: Modificar con el precio registrado previamente en el sistema.",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#33b86c",
        confirmButtonText: "2",
        cancelButtonColor: '#d33',
        cancelButtonText: "1",
        closeOnConfirm: true,
        closeOnCancel: true
    }, function (isConfirm) {
        if (isConfirm) {
            modificarDetallePreciosXMonedaXOpcion(2);
        } else {
            modificarDetallePreciosXMonedaXOpcion(1);
        }
    });
}

function modificarDetallePreciosXMonedaXOpcion(opcion) {
    loaderShow();
    var operador = obtenerOperador();

    ax.setAccion("modificarDetallePreciosXMonedaXOpcion");
    ax.addParamTmp("detalle", detalle);
    ax.addParamTmp("monedaId", select2.obtenerValor("cboMoneda"));
    ax.addParamTmp("operador", operador);
    ax.addParamTmp("fechaEmision", $('#datepicker_' + fechaEmisionId).val());
    ax.addParamTmp("opcion", opcion);
    ax.consumir();
}

function onResponseModificarDetallePreciosXMonedaXOpcion(data) {
    $.each(data, function (indice, item) {
        if (existeColumnaCodigo(5)) {
            $("#txtPrecio_" + item.index).val(devolverDosDecimales(item.precio));
        }
        if (existeColumnaCodigo(1)) {
            $("#txtPrecioCompra_" + item.index).html(devolverDosDecimales(item.precioCompra));
        } else {
            varPrecioCompra = devolverDosDecimales(item.precioCompra);
        }
        hallarSubTotalDetalle(item.index);
    });
}

function dibujarBotonesDeEnvio(data) {

    var html = '<a href="#" class="btn btn-danger" onclick="cargarPantallaListarCompra()"><i class="fa fa-close"></i> Cancelar</a>';
    var accion = '';
    var estilo = '';
    $('#divAccionesEnvio').empty();

    var htmlPredet = '';
    if (!isEmpty(data.accionEnvioPredeterminado)) {
        accion = data.accionEnvioPredeterminado[0];
        if (!isEmpty(accion.color)) {
            //            estilo='style="color: '+accion.color+'"';
            estilo = '';
        }
        htmlPredet = '&nbsp;&nbsp;<button type="button" class="btn btn-success" onclick="enviar(\'' + accion.funcion + '\')" name="env" id="env"><i class="' + accion.icono + '" ' + estilo + '></i> ' + accion.descripcion + '</button>';
    }

    if (!isEmpty(data.accionesEnvio)) {
        accion = data.accionesEnvio;

        html += '&nbsp;&nbsp;<div class="btn-group dropup">' +
            htmlPredet +
            '<button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-expanded="false" name="envOpciones" id="envOpciones"><span class="caret"></span></button>' +
            '<ul class="dropdown-menu dropdown-menu-right" role="menu">';

        $.each(accion, function (index, item) {
            estilo = '';
            if (doc_TipoId == GENERAR_COTIZACION && item.funcion != "generar") {
                if (!isEmpty(item.color)) {
                    estilo = 'style="color: ' + item.color + '"';
                }

                html += '<li><a href="#" onclick="enviar(\'' + item.funcion + '\')"><i class="' + item.icono + '" ' + estilo + '></i>&nbsp;&nbsp; ' + item.descripcion + '</a></li>';
            }
        });

        html += '</ul></div>';

    }

    $("#divAccionesEnvio").append(html);

}


//here
$('.dropdown-menu').click(function (e) {
    if (e.target.id != "btnBusqueda" && e.delegateTarget.id != "ulBuscadorDesplegable2" && e.delegateTarget.id != "listaEmpresa") {
        e.stopPropagation();
    }
});

function buscarDocumentoRelacion() {
    ax.setAccion("buscarDocumentoRelacion");
    ax.addParamTmp("busqueda", $('#txtBuscar').val());
    ax.addParamTmp("empresa_id", commonVars.empresa);
    ax.consumir();
}

function onResponseBuscarDocumentoRelacion(data) {
    var dataPersona = data.dataPersona;
    var dataDocumentoTipo = data.dataDocumentoTipo;
    var dataSerieNumero = data.dataSerieNumero;

    var html = '';
    $('#ulBuscadorDesplegable2').empty();
    if (!isEmpty(dataPersona)) {
        $.each(dataPersona, function (index, item) {
            html += '<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">';
            html += '<a onclick="busquedaPorTexto(5,' + item.id + ',' + null + ')" >';
            html += '<span class="col-md-1"><i class="ion-person"></i></span>';
            html += '<span class="col-md-2">';
            html += '<label style="color: #141719;">' + item.codigo_identificacion + '</label>';
            html += '</span>';
            html += '<span class="col-md-9">';
            html += '<label style="color: #141719;">' + item.nombre + '</label>';
            html += '</span></a>';
            html += '</div>';

        });
    }

    if (!isEmpty(dataDocumentoTipo)) {
        $.each(dataDocumentoTipo, function (index, item) {
            html += '<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">';
            html += '<a onclick="busquedaPorTexto(5,' + null + ',' + item.id + ')" >';
            html += '<span class="col-md-1"><i class="fa fa-files-o"></i></span>';
            html += '<span class="col-md-11">';
            html += '<label style="color: #141719;">' + item.descripcion + '</label>';
            html += '</span></a>';
            html += '</div>';

        });
    }


    if (!isEmpty(dataSerieNumero)) {
        $.each(dataSerieNumero, function (index, item) {
            html += '<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">';
            html += '<a onclick="busquedaPorSerieNumero(\'' + item.serie + '\',\'' + item.numero + '\')" >';
            html += '<span class="col-md-1"><i class="ion-document"></i></span>';
            html += '<span class="col-md-2">';
            html += '<label style="color: #141719;">' + item.serie_numero + '</label>';
            html += '</span>';
            html += '<span class="col-md-9">';
            html += '<label style="color: #141719;">' + item.documento_tipo_descripcion + '</label>';
            html += '</span></a>';
            html += '</div>';

        });
    }
    $("#ulBuscadorDesplegable2").append(html);
}


function busquedaPorTexto(tipo, texto, tipoDocumento) {

    var tipoDocumentoIds = [];
    if (!isEmpty(tipoDocumento)) {
        tipoDocumentoIds.push(tipoDocumento);
    }

    if (tipo == 5) {
        llenarParametrosBusqueda(texto, tipoDocumentoIds, null, null, null, null);
    }

}

function busquedaPorSerieNumero(serie, numero) {
    llenarParametrosBusqueda(null, null, serie, numero, null, null);
}

function llenarParametrosBusqueda(personaId, tipoDocumentoIds, serie, numero, fechaEmision) {
    obtenerParametrosBusquedaDocumentoACopiar()

    parametrosBusquedaDocumentoACopiar.serie = serie;
    parametrosBusquedaDocumentoACopiar.numero = numero;
    parametrosBusquedaDocumentoACopiar.persona_id = personaId
    parametrosBusquedaDocumentoACopiar.fecha_emision_inicio = fechaEmision;
    parametrosBusquedaDocumentoACopiar.documento_tipo_ids = tipoDocumentoIds;
    loaderShow();

    getDataTableDocumentoACopiar();
}

$('#txtBuscar').keyup(function (e) {
    var bAbierto = $(this).attr("aria-expanded");

    if (!eval(bAbierto)) {
        $(this).dropdown('toggle');
    }

});

function appendFormDetalle(html) {
    $("#formularioDetalleDocumento").append(html);
}

function fechaArmada(valor) {
    var fecha = separarFecha(valor);

    return fecha.dia + "/" + fecha.mes + "/" + fecha.anio;
}

var personaIdRegistro = null;
function setearPersonaRegistro(personaId) {
    personaIdRegistro = personaId;
    obtenerPersonas();
    obtenerPersonaDireccion(personaIdRegistro);
    obtenerPersonaContacto(personaIdRegistro);
}

function visualizarCambioPersonalizado(monedaId) {
    if (cambioPersonalizadoId != 0) {
        if (monedaId != monedaBaseId) {
            fc = ""
            $('#txt_' + cambioPersonalizadoId).val('');
            $("#contenedorCambioPersonalizado").show();

            obtenerTipoCambioDatepicker();
        } else {
            $('#txt_' + cambioPersonalizadoId).val('');
            $("#contenedorCambioPersonalizado").hide();
        }
    }
}

function obtenerTipoCambioDatepicker() {
    if (fechaEmisionId != 0 /*&& cambioPersonalizadoId != 0*/) {
        var fecha = $("#datepicker_" + fechaEmisionId).val();
        obtenerTipoCambioXFecha(fecha);
    }
}

var fc = "";
function obtenerTipoCambioXFecha(fecha) {
    //var fecha = obtenerFechaActual();
    if (fc !== fecha) {
        ax.setAccion("obtenerTipoCambioXFecha");
        ax.addParam("fecha", fecha);
        ax.addParamTmp("documentoId", null);
        ax.consumir();
        fc = fecha;
    }
}

function onResponseObtenerTipoCambioXFecha(data) {
    if (!isEmptyData(data)) {
        $('#txt_' + cambioPersonalizadoId).val(data[0]['equivalencia_venta']);
        $('#tipoCambio').val('');
        $('#tipoCambio').val(data[0]['equivalencia_venta']);
        dataCofiguracionInicial.dataTipoCambio = data;
    } else {
        $('#txt_' + cambioPersonalizadoId).val('');
    }
}
function quitarEtiquetasHTML(textoConHTML) {
    // Utilizar una expresión regular para quitar las etiquetas HTML
    return textoConHTML.replace(/<[^>]*>/g, '');
}
function setearDescripcionProducto(indice) {
    if (existeColumnaCodigo(16)) {
        var descripcion = '';
        if (!isEmpty(detalle)) {
            if (isEmpty(detalle[indice].comentarioBien)) {
                descripcion = select2.obtenerText("cboBien_" + indice);
                descripcion = descripcion.split("|");
                descripcion = descripcion[descripcion.length - 1].trim();
            } else {
                descripcion = reducirTexto(detalle[indice].comentarioBien);
            }
        }

        $('#txtProductoDescripcion_' + indice).val(quitarEtiquetasHTML(descripcion));
        $('#txtProductoDescripcion_' + indice).removeAttr("readonly");
    }
    if (existeColumnaCodigo(24)) {
        $("#badge_" + indice).html("");
        $("#obscboBien_" + indice).html("");
        $("#obsupcboBien_" + indice).addClass("hidden");
    }
}

function setearObservacionProducto(indice) {
    if (existeColumnaCodigo(21)) {
        if (!isEmpty(detalle[indice].comentarioBien)) {
            $("#obsupcboBien_" + indice).removeClass("hidden");
            $("#obscboBien_" + indice).html(reducirTexto(detalle[indice].comentarioBien, 50));
            $("#obscboBien_" + indice).removeClass("hidden");

        } else {
            $("#obsupcboBien_" + indice).addClass("hidden");
            $("#obscboBien_" + indice).html(reducirTexto(detalle[indice].comentarioBien, 50));
            $("#obscboBien_" + indice).addClass("hidden");
        }
    }

}
function setearUnidadMedidaDescripcion(indice) {
    if (existeColumnaCodigo(17)) {
        var descripcion = select2.obtenerText("cboUnidadMedida_" + indice);

        $('#txtUnidadMedidaDescripcion_' + indice).val(descripcion);
        $('#txtUnidadMedidaDescripcion_' + indice).removeAttr("readonly");
    }
}

function existeColumnaCodigo(codigo) {
    var dataColumna = dataCofiguracionInicial.movimientoTipoColumna;

    var existe = false;
    if (!isEmpty(dataColumna)) {
        $.each(dataColumna, function (index, item) {
            if (parseInt(item.codigo) === parseInt(codigo)) {
                existe = true;
                return false;
            }
        });
    }

    return existe;
}

function obtenerFechaActual() {
    var hoy = new Date();
    var dd = hoy.getDate();
    var mm = hoy.getMonth() + 1; //hoy es 0!
    var yyyy = hoy.getFullYear();

    if (dd < 10) {
        dd = '0' + dd;
    }

    if (mm < 10) {
        mm = '0' + mm;
    }

    hoy = dd + '/' + mm + '/' + yyyy;

    return hoy;
}

var fechaEmisionAnterior;
function onChangeFechaEmision() {
    if (documentoTipoTipo == 1) {
        if (!validarCambioFechaEmision) {
            if (!isEmpty(detalle)) {
                swal({
                    title: "Confirmación de actualización de precio promedio",
                    text: "¿Está seguro de actualizar los precios promedios a la fecha de emisión " + $('#datepicker_' + fechaEmisionId).val() + '?',
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#33b86c",
                    confirmButtonText: "Si!",
                    cancelButtonColor: '#d33',
                    cancelButtonText: "No!",
                    closeOnConfirm: true,
                    closeOnCancel: true
                }, function (isConfirm) {
                    if (isConfirm) {
                        modificarDetallePrecios();
                    } else {
                        validarCambioFechaEmision = true;
                        $('#datepicker_' + fechaEmisionId).datepicker('setDate', fechaEmisionAnterior);
                    }
                    fechaEmisionAnterior = $('#datepicker_' + fechaEmisionId).val();
                });
            }
        }
        validarCambioFechaEmision = false;
    }
}

function onChangeTipoPago() {
    var tipoPagoId = select2.obtenerValor('cboTipoPago');


    if (tipoPagoId == 2) {
        if (calculoTotal > 0) {
            mostrarModalProgramacionPago();

        } else {
            $.Notification.autoHideNotify('warning', 'top right', 'Validación', "Total debe ser positivo.");
            select2.asignarValor('cboTipoPago', 1);
        }
    } else {
        $('#aMostrarModalProgramacion').hide();
        $('#idFormaPagoContado').show();
    }
}

function cancelarProgramacion() {
    $('#modalProgramacionPagos').modal('hide');

    if (!isEmpty(listaPagoProgramacion)) {
        swal({
            title: "Confirmación de cancelación de distrinución de condición de pagos",
            text: "¿Está seguro de cancelación de distrinución de condición de pagos? Al confirmar se limpiará la distrinución de condición de pagos registrada.",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#33b86c",
            confirmButtonText: "Si!",
            cancelButtonColor: '#d33',
            cancelButtonText: "No!",
            closeOnConfirm: true,
            closeOnCancel: true
        }, function (isConfirm) {
            if (isConfirm) {
                modalPagoAbierto = false;

                listaPagoProgramacion = [];
                arrayFechaPago = [];
                arrayImportePago = [];
                arrayDias = [];
                arrayPorcentaje = [];
                arrayGlosa = [];
                arrayPagoProgramacionId = [];
                pagoProgramacionTotalImporte = 0;

                //                onListarPagoProgramacion(listaPagoProgramacion);

                $('#aMostrarModalProgramacion').show();
                $('#tipoPagoDescripcion').html('Forma de pago: Crédito');
                $('#tipoPagoDescripcion').css({ 'color': '#cb2a2a' });
                $('#idFormaPagoContado').hide();
                if (doc_TipoId == GENERAR_COTIZACION) {
                    listaPagoProgramacionPostores[$("#indexProveedor").val()] = [];
                }
            } else {
                aceptarProgramacion(false);
            }
        });
    } else {
        $('#aMostrarModalProgramacion').show();
        $('#tipoPagoDescripcion').html('Forma de pago: Crédito');
        $('#tipoPagoDescripcion').css({ 'color': '#cb2a2a' });
        $('#idFormaPagoContado').hide();
    }

}

function aceptarProgramacion(muestraMensaje) {
    if (!isEmpty(listaPagoProgramacion) || !isEmpty(listaPagoProgramacionPostores)) {

        var programacionTexto = '';
        var totalPago = 0;
        listaPagoProgramacion.forEach(function (item) {
            //listaPagoProgramacion.push([ fechaPago, importePago, dias, porcentaje,glosa,pagoProgramacionId]);
            totalPago = totalPago + item[1] * 1;

            var sep = ' | ';
            if (programacionTexto == '') {
                sep = '';
            }

            programacionTexto += sep + item[0] + ': ' + item[1];
        });

        if (programacionTexto.length > 55) {
            programacionTexto = programacionTexto.substring(0, 52) + '...';
        }

        $('#modalProgramacionPagos').modal('hide');
        $('#aMostrarModalProgramacion').show();
        $('#tipoPagoDescripcion').html('(' + programacionTexto + ')');
        if (doc_TipoId == GENERAR_COTIZACION) {
            pagoProgramacionTotalImporte = 0;
            listaPagoProgramacion = [];

            arrayFechaPago = [];
            arrayImportePago = [];
            arrayDias = [];
            arrayPorcentaje = [];
            arrayGlosa = [];
            arrayPagoProgramacionId = [];
            calculoTotal = totalesPostores[$("#indexProveedor").val()].total;
        }

        if (devolverDosDecimales(totalPago) != devolverDosDecimales(calculoTotal)) {
            if (muestraMensaje) {
                mensajeValidacion('Total de pago no coincide con el total del documento');
                return;
            }
            $('#tipoPagoDescripcion').css({ 'color': '#cb2a2a' });
        } else {
            $('#tipoPagoDescripcion').css({ 'color': '#1ca8dd' });
        }

        $('#idFormaPagoContado').hide();

    } else {
        mensajeValidacion('Registre programación de pago.');
    }
}

var modalPagoAbierto = false;
function mostrarModalProgramacionPago() {
    var dtdFechaVencimientoId = obtenerDocumentoTipoDatoIdXTipo(10);

    if (!isEmpty(dtdFechaVencimientoId)) {
        if (!modalPagoAbierto) {
            $('#fechaPago').datepicker({
                isRTL: false,
                format: 'dd/mm/yyyy',
                autoclose: true,
                language: 'es'
            }).on('changeDate', function (ev) {
                actualizarNumeroDias();
            });

            //            var fechaVencimiento=$('#datepicker_' + dtdFechaVencimientoId).val();
            var fechaEmision = $('#datepicker_' + fechaEmisionId).val();
            $('#fechaPago').datepicker('setDate', sumaFecha(1, fechaEmision));

            $('#txtImportePago').val(devolverDosDecimales(calculoTotal));

            actualizarPorcentajePago();
            onChangeRdFechaPago();
            onChangeRdImportePago();

            modalPagoAbierto = true;
        }

        $('#modalProgramacionPagos').modal('show');

        setTimeout(function () {
            onListarPagoProgramacion(listaPagoProgramacion);
        }, 500);

        $('#labelTotalDocumento').html('(Total: ' + monedaSimbolo + ' ' + parseFloat(calculoTotal).formatMoney(2, '.', ',') + ')');

    } else {
        $.Notification.autoHideNotify('warning', 'top right', 'Validación', "Falta configurar la fecha de vencimiento.");
        select2.asignarValor('cboTipoPago', 1);
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

function obtenerDocumentoTipoSeleccionado() {
    let dataDocumentoTipo = dataCofiguracionInicial.documento_tipo.filter(item => item.id == select2.obtenerValor("cboDocumentoTipo"));
    if (!isEmpty(dataDocumentoTipo)) {
        return dataDocumentoTipo[0];
    }
    return dataDocumentoTipo;
}

function actualizarPorcentajePago() {
    var importePago = $('#txtImportePago').val();
    if (doc_TipoId == GENERAR_COTIZACION) {
        calculoTotal = totalesPostores[$("#indexProveedor").val()].total;
    }
    if (importePago > calculoTotal) {
        $('#txtImportePago').val(calculoTotal);
        mensajeValidacion('Importe de pago no puede ser mayor al total');
        calculoPorcentajePago();
        return;
    }

    if (importePago <= 0) {
        $('#txtImportePago').val(0);
        mensajeValidacion('El importe de pago debe ser positivo.');
        calculoPorcentajePago();
        return;
    }

    calculoPorcentajePago();
}

function calculoPorcentajePago() {
    //    if (document.getElementById("rdImportePago").checked) {
    var importePago = $('#txtImportePago').val();
    var porcentaje = (importePago / calculoTotal) * 100;
    $('#txtPorcentaje').val(devolverDosDecimales(porcentaje));
    //    }
}

function mensajeValidacion(mensaje) {
    $.Notification.autoHideNotify('warning', 'top right', 'Validación', mensaje);
}

function actualizarImportePago() {
    var porcentaje = $('#txtPorcentaje').val();
    if (porcentaje > 100) {
        $('#txtPorcentaje').val(100);
        mensajeValidacion('Porcentaje máximo 100.');
        calculoImportePago();
        return;
    }

    if (porcentaje <= 0) {
        $('#txtPorcentaje').val(0);
        mensajeValidacion('Porcentaje de pago debe ser positivo.');
        calculoImportePago();
        return;
    }

    calculoImportePago();

}

function calculoImportePago() {
    var porcentaje = $('#txtPorcentaje').val();
    var importePago = 0;
    if(doc_TipoId == GENERAR_COTIZACION){
        importePago = (totalesPostores[$("#indexProveedor").val()].total * porcentaje) / 100;
    }else{
        importePago = (calculoTotal * porcentaje) / 100; 
    }
    $('#txtImportePago').val(importePago);
}

function restarFechas(f1, f2) {
    var aFecha1 = f1.split('/');
    var aFecha2 = f2.split('/');
    var fFecha1 = Date.UTC(aFecha1[2], aFecha1[1] - 1, aFecha1[0]);
    var fFecha2 = Date.UTC(aFecha2[2], aFecha2[1] - 1, aFecha2[0]);
    var dif = fFecha2 - fFecha1;
    var dias = Math.floor(dif / (1000 * 60 * 60 * 24));
    return dias;
}

function actualizarNumeroDias() {
    var dtdFechaVencimientoId = obtenerDocumentoTipoDatoIdXTipo(10);
    var fechaVencimiento = $('#datepicker_' + dtdFechaVencimientoId).val();
    var fechaEmision = $('#datepicker_' + fechaEmisionId).val();
    var fechaPago = $('#fechaPago').val();

    if (restarFechas(fechaEmision, fechaPago) < 0) {
        mensajeValidacion('La fecha de pago no puede ser menor a la fecha de emisión.');
        $('#fechaPago').datepicker('setDate', sumaFecha(1, fechaEmision));
        return;
    }
    if (fechaEmision == fechaPago) {
        mensajeValidacion('La fecha de pago no puede igual a la fecha de emisión.');
        $('#fechaPago').datepicker('setDate', sumaFecha(1, fechaEmision));
        return;
    }

    if (restarFechas(fechaPago, fechaVencimiento) < 0) {
        //        mensajeValidacion('La fecha de pago no puede ser mayor a la fecha de vencimiento.');
        //        $('#fechaPago').datepicker('setDate', fechaVencimiento);

        $('#modalProgramacionPagos').modal('hide');

        swal({
            title: "La fecha de pago es mayor a la fecha de vencimiento",
            text: "¿Desea actualizar la fecha de vencimiento a la fecha de pago: " + fechaPago + "?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#33b86c",
            confirmButtonText: "Si, actualizar!",
            cancelButtonColor: '#d33',
            cancelButtonText: "No, cancelar!",
            closeOnConfirm: true,
            closeOnCancel: true
        }, function (isConfirm) {
            if (isConfirm) {
                $('#datepicker_' + dtdFechaVencimientoId).datepicker('setDate', fechaPago);
                calculoNumeroDias();
                $('#modalProgramacionPagos').modal('show');
            } else {
                $('#fechaPago').datepicker('setDate', fechaVencimiento);
                $('#modalProgramacionPagos').modal('show');
            }
        });

        return;
    }

    calculoNumeroDias();
}

function calculoNumeroDias() {
    var fechaEmision = $('#datepicker_' + fechaEmisionId).val();
    var fechaPago = $('#fechaPago').val();

    $('#txtDias').val(restarFechas(fechaEmision, fechaPago));
}

function actualizarFechaPago() {
    var fechaEmision = $('#datepicker_' + fechaEmisionId).val();
    var dias = $('#txtDias').val();

    if (!isEmpty(dias)) {
        $('#fechaPago').datepicker('setDate', sumaFecha(dias, fechaEmision));
    }

}

function sumaFecha(d, fecha) {
    var Fecha = new Date();
    var sFecha = fecha || (Fecha.getDate() + "/" + (Fecha.getMonth() + 1) + "/" + Fecha.getFullYear());
    var sep = sFecha.indexOf('/') != -1 ? '/' : '-';
    var aFecha = sFecha.split(sep);
    var fecha = aFecha[2] + '/' + aFecha[1] + '/' + aFecha[0];
    fecha = new Date(fecha);
    fecha.setDate(fecha.getDate() + parseInt(d));
    var anno = fecha.getFullYear();
    var mes = fecha.getMonth() + 1;
    var dia = fecha.getDate();
    mes = (mes < 10) ? ("0" + mes) : mes;
    dia = (dia < 10) ? ("0" + dia) : dia;
    var fechaFinal = dia + sep + mes + sep + anno;
    return (fechaFinal);
}

//GUARDAR EN VARIABLE ARRAY PROGRAMACION PAGO
var listaPagoProgramacion = [];
var listaPagoProgramacionPostores = [];

var arrayFechaPago = [];
var arrayImportePago = [];
var arrayDias = [];
var arrayPorcentaje = [];
var arrayGlosa = [];
var arrayPagoProgramacionId = [];

function agregarPagoProgramacion() {
    //alert("Hola");

    //    var fechaPago = $('#cboFechaPago').val();

    var fechaPago = $('#fechaPago').val();
    var importePago = $('#txtImportePago').val();
    var dias = $('#txtDias').val();
    var porcentaje = $('#txtPorcentaje').val();
    var glosa = $('#txtGlosa').val();
    var idPagoProgramacion = $('#idPagoProgramacion').val();
    var index = $("#indexProveedor").val();

    // ids tablas
    var pagoProgramacionId = null;
    //alert(idPagoProgramacion);

    if (validarFormularioPagoProgramacion(fechaPago, importePago, dias, porcentaje)) {
        if (validarPagoProgramacionRepetido(fechaPago, importePago, dias, porcentaje)) {

            if (idPagoProgramacion != '') {
                //alert('igual');

                arrayFechaPago[idPagoProgramacion] = fechaPago;
                arrayImportePago[idPagoProgramacion] = importePago;
                arrayDias[idPagoProgramacion] = dias;
                arrayPorcentaje[idPagoProgramacion] = porcentaje;
                arrayGlosa[idPagoProgramacion] = glosa;

                // ids de tablas relacionadas
                pagoProgramacionId = arrayPagoProgramacionId[idPagoProgramacion];

                listaPagoProgramacion[idPagoProgramacion] = [fechaPago, importePago, dias, porcentaje, glosa, pagoProgramacionId];
            } else {
                //alert('diferente');

                arrayFechaPago.push(fechaPago);
                arrayImportePago.push(importePago);
                arrayDias.push(dias);
                arrayPorcentaje.push(porcentaje);
                arrayGlosa.push(glosa);

                listaPagoProgramacion.push([fechaPago, importePago, dias, porcentaje, glosa, pagoProgramacionId]);
            }

            if (doc_TipoId == GENERAR_COTIZACION) {
                listaPagoProgramacionPostores[index] = listaPagoProgramacion;
            }
            onListarPagoProgramacion(listaPagoProgramacion);
            limpiarCamposPagoProgramacion();

        }
        $('#txtImportePago').val("");
        $('#txtDias').val(0);
        $('#txtPorcentaje').val(0);
        $('#txtGlosa').val("");
        $('#fechaPago').val("");
    }

}

function validarFormularioPagoProgramacion(fechaPago, importePago, dias, porcentaje) {
    var bandera = true;

    if (fechaPago === '' || fechaPago === null) {
        mensajeValidacion('Fecha de pago es obligatorio');
        bandera = false;
    }

    if (importePago === '' || importePago === null) {
        mensajeValidacion('Importe de pago es obligatorio');
        bandera = false;
    }

    if (importePago <= 0) {
        mensajeValidacion('Importe de pago debe ser positivo');
        bandera = false;
    }

    if (dias === '' || dias === null) {
        mensajeValidacion('Número de días es obligatorio');
        bandera = false;
    }

    if (porcentaje === '' || porcentaje === null) {
        mensajeValidacion('Porcentaje es obligatorio');
        bandera = false;
    }

    if (porcentaje <= 0) {
        mensajeValidacion('Porcentaje de pago debe ser positivo');
        bandera = false;
    }

    if (doc_TipoId == GENERAR_COTIZACION) {
        if (pagoProgramacionTotalImporte != 0) {
            var provedorIndex = $("#indexProveedor").val();
            var pagoRestante = totalesPostores[provedorIndex].total - pagoProgramacionTotalImporte;

            if (pagoRestante - importePago < 0) {
                mensajeValidacion('Total de pago excedido. Importe de pago restante: ' + devolverDosDecimales(pagoRestante));
                bandera = false;
            }
        }
    } else {
        if (pagoProgramacionTotalImporte != 0) {
            var pagoRestante = calculoTotal - pagoProgramacionTotalImporte;

            if (pagoRestante - importePago < 0) {
                mensajeValidacion('Total de pago excedido. Importe de pago restante: ' + devolverDosDecimales(pagoRestante));
                bandera = false;
            }
        }
    }

    return bandera;
}

function validarPagoProgramacionRepetido(fechaPago, importePago, dias, porcentaje) {
    var valido = true;

    var idPagoProgramacion = $('#idPagoProgramacion').val();

    if (idPagoProgramacion != '') {
        var indice = buscarPagoProgramacion(fechaPago, importePago, dias, porcentaje);
        if (indice != idPagoProgramacion && indice != -1) {
            $.Notification.autoHideNotify('warning', 'top right', 'Validación', "La fecha de pago ya ha sido agregado");
            valido = false;
        }
    } else {
        var indice = buscarPagoProgramacion(fechaPago, importePago, dias, porcentaje);
        if (indice > -1) {
            $.Notification.autoHideNotify('warning', 'top right', 'Validación', "La fecha de pago ya ha sido agregado");
            valido = false;
        }
    }

    return valido;
}

var pagoProgramacionTotalImporte = 0;
function onListarPagoProgramacion(data) {
    breakFunction();
    var ind = 0;
    var totalImporte = 0;
    var totalPorcentaje = 0;
    var dataTb = [];

    if (!isEmpty(data)) {
        data.forEach(function (item) {
            dataTb[ind] = [item[0], item[1], item[2], item[3], item[4], item[5]];

            totalImporte += item['1'] * 1;
            totalPorcentaje += item['3'] * 1;

            var eliminar = "<a href='#' onclick = 'eliminarPagoProgramacion(\""
                + item['0'] + "\", \"" + item['1'] + "\", \"" + item['2'] + "\", \"" + item['3'] + "\")' >"
                + "<i id='e" + ind + "' class='fa fa-trash-o' style='color:#cb2a2a;'></i></a>";

            var editar = "<a href='#' onclick = 'editarPagoProgramacion(\"" + ind + "\")' >"
                + "<i id='e" + ind + "' class='fa fa-edit' style='color:#E8BA2F;'></i></a>&nbsp;&nbsp;&nbsp;";

            dataTb[ind][6] = editar + eliminar;

            ind++;

            arrayFechaPago.push(item[0]);
            arrayImportePago.push(item[1]);
            arrayDias.push(item[2]);
            arrayPorcentaje.push(item[3]);
            arrayGlosa.push(item[4]);
        });

        $('#dataTablePagoProgramacion').DataTable({
            "scrollX": false,
            "paging": false,
            "info": false,
            "filter": false,
            "ordering": true,
            "order": [[1, 'asc']],
            "data": dataTb,
            "columns": [
                { "data": 0, "sClass": "alignCenter" },
                { "data": 2, "sClass": "alignRight" },
                { "data": 1, "sClass": "alignRight" },
                { "data": 3, "sClass": "alignRight" },
                { "data": 4 },
                { "data": 6, "sClass": "alignCenter" }
            ],
            columnDefs: [
                {
                    "render": function (data, type, row) {
                        return parseFloat(data).formatMoney(3, '.', ',');
                    },
                    "targets": [2, 3]
                }
            ],
            "dom": '<"top">rt<"bottom"<"col-md-3"l><"col-md-9"p><"col-md-12"i>><"clear">',
            destroy: true,
            footerCallback: function (row, data, start, end, display) {
                var api = this.api(), data;
                $(api.column(2).footer()).html(parseFloat(totalImporte).formatMoney(2, '.', ','));
                $(api.column(3).footer()).html(parseFloat(totalPorcentaje).formatMoney(2, '.', ','));
            }
        });

        pagoProgramacionTotalImporte = totalImporte;
    } else {
        var table = $('#dataTablePagoProgramacion').DataTable();
        table.clear().draw();
        // Limpia el contenido del footer
        $('#dataTablePagoProgramacion tfoot th, #dataTablePagoProgramacion tfoot td').each(function () {
            $(this).html('');
        });
    }
}

function limpiarCamposPagoProgramacion() {
    $('#txtGlosa').val('');
    $('#idPagoProgramacion').val('');
    $('#txtImportePago').val("");
    $('#txtDias').val(0);
    $('#txtPorcentaje').val(0);
    $('#fechaPago').val("");
}

function buscarPagoProgramacion(fechaPago, importePago, dias, porcentaje) {
    if (doc_TipoId == GENERAR_COTIZACION) {
        arrayFechaPago = [];
        var proveedor_index = $("#indexProveedor").val();
        if (listaPagoProgramacionPostores.length > 0) {
            listaPagoProgramacionPostores.forEach(function (programacionPostores, idx) {
                if (proveedor_index == idx) {
                    programacionPostores.forEach(function (programacion, idx) {
                        arrayFechaPago.push(programacion[0]);
                        arrayImportePago.push(programacion[1]);
                        arrayDias.push(programacion[2]);
                        arrayPorcentaje.push(programacion[3]);
                        arrayGlosa.push(programacion[4]);
                    });
                }
            });
        }
    }

    var tam = arrayFechaPago.length;
    var ind = -1;
    for (var i = 0; i < tam; i++) {
        if (arrayFechaPago[i] === fechaPago /*&& arrayImportePago[i] === importePago && arrayDias[i] === dias && arrayPorcentaje[i] === porcentaje*/) {
            ind = i;
            break;
        }
    }
    return ind;
}

function editarPagoProgramacion(indice) {
    if (isEmpty(arrayProveedor)) {
        $('#fechaPago').datepicker('setDate', arrayFechaPago[indice]);
        $('#txtImportePago').val(arrayImportePago[indice]);
        $('#txtPorcentaje').val(arrayPorcentaje[indice]);
        $('#txtGlosa').val(arrayGlosa[indice]);

        pagoProgramacionTotalImporte = pagoProgramacionTotalImporte - arrayImportePago[indice] * 1;

        $('#idPagoProgramacion').val(indice);
    } else {
        var indice_proveedor = $("#indexProveedor").val();
        pagoProgramacionTotalImporte = totalesPostores[indice_proveedor].total - devolverDosDecimales(listaPagoProgramacionPostores[indice_proveedor][indice][1]);

        $('#fechaPago').datepicker('setDate', listaPagoProgramacionPostores[indice_proveedor][indice][0]);
        $('#txtPorcentaje').val(listaPagoProgramacionPostores[indice_proveedor][indice][3]);
        $('#txtImportePago').val(devolverDosDecimales(listaPagoProgramacionPostores[indice_proveedor][indice][1])).change();
        $('#txtGlosa').val(listaPagoProgramacionPostores[indice_proveedor][indice][4]);
        $('#txtDias').val(listaPagoProgramacionPostores[indice_proveedor][indice][2]);
        if(!isEmpty($('#txtDias').val()) || $('#txtDias').val() != 0){
            $('input[name="rdTiempoPago"][value="rdDias"]').prop('checked', true);
            $('#txtDias').prop("disabled", false);
            $('#fechaPago').prop("disabled", true);
        }else{
            $('input[name="rdTiempoPago"][value="rdFechaPago"]').prop('checked', true);
            $('#txtDias').prop("disabled", true);
            $('#fechaPago').prop("disabled", false);
        }
        $('#idPagoProgramacion').val(indice);
    }
}

var listaPagoProgramacionEliminado = [];

function eliminarPagoProgramacion(fechaPago, importePago, dias, porcentaje) {
    var indice = buscarPagoProgramacion(fechaPago, importePago, dias, porcentaje);
    if (indice > -1) {
        arrayFechaPago.splice(indice, 1);
        arrayImportePago.splice(indice, 1);
        arrayDias.splice(indice, 1);
        arrayPorcentaje.splice(indice, 1);
        arrayGlosa.splice(indice, 1);
    }


    listaPagoProgramacion = [];
    var tam = arrayFechaPago.length;
    for (var i = 0; i < tam; i++) {
        listaPagoProgramacion.push([arrayFechaPago[i], arrayImportePago[i], arrayDias[i], arrayPorcentaje[i], arrayGlosa[i], arrayPagoProgramacionId[i]]);
    }
    if (doc_TipoId == GENERAR_COTIZACION) {
        listaPagoProgramacionPostores[$("#indexProveedor").val()] = listaPagoProgramacion;
    }
    onListarPagoProgramacion(listaPagoProgramacion);
}

function onChangeRdFechaPago() {
    if (document.getElementById("rdFechaPago").checked) {
        $("#fechaPago").removeAttr("disabled");
        $("#txtDias").attr('disabled', 'disabled');
    }
}

function onChangeRdDias() {
    if (document.getElementById("rdDias").checked) {
        $("#txtDias").removeAttr("disabled");
        $("#fechaPago").attr('disabled', 'disabled');
    }
}

function onChangeRdImportePago() {
    if (document.getElementById("rdImportePago").checked) {
        $("#txtImportePago").removeAttr("disabled");
        $("#txtPorcentaje").attr('disabled', 'disabled');
    }
}

function onChangeRdPorcentaje() {
    if (document.getElementById("rdPorcentaje").checked) {
        $("#txtPorcentaje").removeAttr("disabled");
        $("#txtImportePago").attr('disabled', 'disabled');
    }
}

function cargarPagoProgramacion(data) {
    var dtdTipoPago = obtenerDocumentoTipoDatoIdXTipo(25);

    if (!isEmpty(data) && isEmpty(listaPagoProgramacion) && !isEmpty(dtdTipoPago)) {
        var fechaPago;
        var importePago;
        var dias;
        var porcentaje;
        var glosa;
        var pagoProgramacionId = null;
        $.each(data, function (index, item) {
            fechaPago = formatearFechaBDCadena(item.fecha_pago);
            importePago = devolverDosDecimales(item.importe);
            porcentaje = devolverDosDecimales(item.porcentaje);
            dias = item.dias;
            glosa = item.glosa;

            arrayFechaPago.push(fechaPago);
            arrayImportePago.push(importePago);
            arrayDias.push(dias);
            arrayPorcentaje.push(porcentaje);
            arrayGlosa.push(glosa);

            listaPagoProgramacion.push([fechaPago, importePago, dias, porcentaje, glosa, pagoProgramacionId]);
        });

        select2.asignarValor('cboTipoPago', 2);
        aceptarProgramacion('false');
    }
}

function asignarImportePago() {
    if (doc_TipoId == GENERAR_COTIZACION) {
        if (!isEmpty(listaPagoProgramacionPostores)) {
            var porcentaje;
            var importePago;
            arrayProveedor.forEach(function (proveedorID, idx) {
                $.each(listaPagoProgramacionPostores[proveedorID.indice], function (index, item) {
                    porcentaje = item[3];
                    importePago = (totalesPostores[proveedorID.indice].total * porcentaje) / 100;
                    listaPagoProgramacionPostores[proveedorID.indice][index][1] = importePago;
                });
            });
            aceptarProgramacion(false);
        }
    } else {
        var dtdTipoPago = obtenerDocumentoTipoDatoIdXTipo(25);
        if (!isEmpty(dtdTipoPago) && !isEmpty(listaPagoProgramacion)) {
            var porcentaje;
            var importePago;
            $.each(listaPagoProgramacion, function (index, item) {
                porcentaje = item[3];
                importePago = (calculoTotal * porcentaje) / 100;
                listaPagoProgramacion[index][1] = importePago;
            });

            aceptarProgramacion(false);
        }
    }

}

//seccion PAGOS
function abrirModalPagos(data) {
    habilitarBoton();
    var dataDP = data.dataDocumentoPago;

    if ($('#cboMoneda').val() == 4) {
        $("#contenedorTipoCambioDiv").show();
    } else {
        $("#contenedorTipoCambioDiv").hide();
    }

    $("#contenedorEfectivo").hide();
    if (!isEmpty(dataDP.documento_tipo)) {
        $("#cboDocumentoTipoNuevoPagoConDocumento").select2({
            width: "100%"
        }).on("change", function (e) {
            $("#contenedorEfectivo").hide();
            loaderShow("#modalNuevoDocumentoPagoConDocumento");
            if (e.val == 0) {
                obtenerFormularioEfectivo();
            } else {
                obtenerDocumentoTipoDatoPago(e.val);
            }
        });
        select2.cargar("cboDocumentoTipoNuevoPagoConDocumento", dataDP.documento_tipo, "id", "descripcion");
        $('#cboDocumentoTipoNuevoPagoConDocumento').append('<option value="0">Efectivo</option>');
        select2.asignarValor("cboDocumentoTipoNuevoPagoConDocumento", dataDP.documento_tipo[0].id);
        if (dataDP.documento_tipo.length === 1) {
            select2.readonly("cboDocumentoTipoNuevoPagoConDocumento", true);
        }
        onResponseObtenerDocumentoTipoDatoPago(dataDP.documento_tipo_conf);
    }

    //llenado de la actividad
    select2.cargar('cboActividadEfectivo', data.actividad, 'id', ['codigo', 'descripcion']);
    select2.asignarValor('cboActividadEfectivo', data.actividad[0].actividad_defecto);
}

function obtenerFormularioEfectivo() {
    $("#formNuevoDocumentoPagoConDocumento").empty();
    $("#contenedorDocumentoTipoNuevo").css("height", 0);

    $("#contenedorEfectivo").show();
    loaderClose();
    $("#txtMontoAPagar").val($('#' + importes.totalId).val());
}

$("#tipoCambio").prop("disabled", true);
$("#checkBP").click(function () {
    var checked = $(this).is(":checked");
    if (!checked) {
        $("#tipoCambio").prop("disabled", true);
        return true;
    }
    obtenerTipoCambioDatepicker();
    $("#tipoCambio").prop("disabled", false);
    return true;
});

$('#txtPagaCon').keyup(function () {
    var monto = parseFloat($('#txtMontoAPagar').val());
    var pago = parseFloat($('#txtPagaCon').val());
    var vuelto = pago - monto;
    $('#txtVuelto').val(vuelto.toFixed(2));
});
$('#txtMontoAPagar').keyup(function () {
    var monto = parseFloat($('#txtMontoAPagar').val());
    var pago = parseFloat($('#txtPagaCon').val());
    var vuelto = pago - monto;
    $('#txtVuelto').val(vuelto.toFixed(2));
    //    $('#txtVuelto').val(formatearNumero(vuelto));
});

function obtenerDocumentoTipoDatoPago(documentoTipoId) {
    ax.setAccion("obtenerDocumentoTipoDatoPago");
    ax.addParamTmp("documentoTipoId", documentoTipoId);
    ax.consumir();
}

var camposDinamicosPago = [];
var personaNuevoId;
var totalPago;
function onResponseObtenerDocumentoTipoDatoPago(data) {
    camposDinamicosPago = [];
    personaNuevoId = 0;
    $("#formNuevoDocumentoPagoConDocumento").empty();
    if (!isEmpty(data)) {
        $("#contenedorDocumentoTipoNuevo").css("height", 75 * data.length);
        // Mostraremos la data en filas de dos columnas
        $.each(data, function (index, item) {
            appendFormNuevo('<div class="row">');
            var html = '<div class="form-group col-md-12">' +
                '<label>' + item.descripcion + ' ' + ((item.opcional == 0) ? '*' : '') + '</label>';
            if (item.tipo == 5) {
                html += '<span class="divider"></span> <a onclick="cargarPersona();"><i class="ion-person-add" tooltip-btndata-toggle="tooltip" title="Agregar proveedor"></i></a>';
            }
            html += '<div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">';
            camposDinamicosPago.push({
                id: item.id,
                tipo: parseInt(item.tipo),
                opcional: item.opcional,
                descripcion: item.descripcion
            });
            switch (parseInt(item.tipo)) {
                case 1:
                case 14:
                    totalPago = 'txtnd_' + item.id;
                    html += '<input type="number" id="txtnd_' + item.id + '" name="txtnd_' + item.id + '" class="form-control" value="' + $('#' + importes.totalId).val() + '" maxlength="45" style="text-align:right; "/>';
                    break;
                case 2:
                case 6:
                case 7:
                case 8:
                case 12:
                case 13:

                    var readonly = (parseInt(item.editable) === 0) ? 'readonly="true"' : '';
                    var value = '';
                    // Número autoincrementable
                    if (parseInt(item.tipo) === 8 && !isEmpty(item.data)) {
                        value = item.data;
                    } else if (!isEmpty(item.cadena_defecto)) {
                        value = item.cadena_defecto;
                    }
                    html += '<input type="text" id="txtnd_' + item.id + '" name="txtnd_' + item.id + '" ' + readonly + '" class="form-control" value="' + value + '" maxlength="45"/>';
                    break;
                case 3:
                case 10:
                case 11:
                    html += '<input type="text" class="form-control fecha" placeholder="dd/mm/yyyy" id="datepickernd_' + item.id + '" value = "' + item.data + '">' +
                        '<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>';
                    break;
                //FECHA DE EMISION DESHABILITADO Y FECHA DE EMISION DEL DOCUMENTO
                case 9:
                    var fechaEmision = item.data;
                    var dtdFechaEmision = obtenerDocumentoTipoDatoIdXTipo(9);
                    if (!isEmpty(dtdFechaEmision)) {
                        fechaEmision = $('#datepicker_' + dtdFechaEmision).val();
                    }
                    html += '<input type="text" class="form-control fecha" placeholder="dd/mm/yyyy" id="datepickernd_' + item.id + '" value = "' + fechaEmision + '" disabled>' +
                        '<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>';
                    break;
                case 4:
                    html += '<select name="cbond_' + item.id + '" id="cbond_' + item.id + '" class="select2"></select>';
                    break;
                case 5:
                    html += '<div id ="div_proveedor" ><select name="cbond_' + item.id + '" id="cbond_' + item.id + '" class="select2">';
                    html += '<option value="' + 0 + '">Seleccione la persona</option>';
                    $.each(item.data, function (indexPersona, itemPersona) {
                        html += '<option value="' + itemPersona.id + '">' + itemPersona.nombre + ' | ' + itemPersona.codigo_identificacion + '</option>';
                    });
                    html += '</select>';
                    personaNuevoId = item.id;
                    break;
                case 20:
                    html += '<div id ="div_cuenta" ><select name="cbond_' + item.id + '" id="cbond_' + item.id + '" class="select2">';
                    html += '<option value="' + 0 + '">Seleccione la cuenta</option>';
                    $.each(item.data, function (indexCuenta, itemCuenta) {
                        html += '<option value="' + itemCuenta.id + '">' + itemCuenta.descripcion_numero + '</option>';
                    });
                    html += '</select>';
                    break;
                case 21:
                    html += '<div id ="div_actividad" ><select name="cbond_' + item.id + '" id="cbond_' + item.id + '" class="select2">';
                    html += '<option value="' + 0 + '">Seleccione la actividad</option>';
                    $.each(item.data, function (indexActividad, itemActividad) {
                        html += '<option value="' + itemActividad.id + '">' + itemActividad.codigo + ' | ' + itemActividad.descripcion + '</option>';
                    });
                    html += '</select>';
                    break;
            }
            html += '</div></div>';
            appendFormNuevo(html);
            appendFormNuevo('</div>');
            switch (item.tipo) {
                case 4, "4":
                    select2.cargar("cbond_" + item.id, item.data, "id", "descripcion");
                    $("#cbond_" + item.id).select2({
                        width: '100%'
                    });
                    break;
                case 5, "5":
                    $("#cbond_" + item.id).select2({
                        width: '100%'
                    });
                    break;
                case 20, "20":
                case 21, "21":
                    $("#cbond_" + item.id).select2({
                        width: '100%'
                    });

                    if (!isEmpty(item.numero_defecto)) {
                        var id = parseInt(item.numero_defecto);
                        select2.asignarValor("cbond_" + item.id, id);
                    }

                    if (item.editable == 0) {
                        $("#cbond_" + item.id).attr('disabled', 'disabled');
                    }
                    break;
            }
        });
        var clienteId = select2.obtenerValor('cbo_' + obtenerDocumentoTipoDatoIdXTipo(5));
        if (personaNuevoId > 0 && clienteId > 0) {
            select2.asignarValor('cbond_' + personaNuevoId, clienteId);
        }
        $('.fecha').datepicker({
            isRTL: false,
            format: 'dd/mm/yyyy',
            autoclose: true,
            language: 'es'
        });
        var fecha_ = $('#datepicker_fechaPago').val();
    }
    //habilitarBotonGeneral("btnNuevoC")
    $('#modalNuevoDocumentoPagoConDocumento').modal('show');
    loaderClose("#modalNuevoDocumentoPagoConDocumento");

}

function appendFormNuevo(html) {
    $("#formNuevoDocumentoPagoConDocumento").append(html);
}

function obtenerValoresCamposDinamicosPago() {
    var isOk = true;
    if (isEmpty(camposDinamicosPago))
        return false;
    $.each(camposDinamicosPago, function (index, item) {
        //string
        switch (item.tipo) {
            case 1:
            case 2:
            case 6:
            case 7:
            case 8:
            case 12:
            case 13:
            case 14:
                camposDinamicosPago[index]["valor"] = document.getElementById("txtnd_" + item.id).value;
                if (item.opcional == 0) {
                    //validamos
                    if (isEmpty(camposDinamicosPago[index]["valor"])) {
                        mostrarValidacionLoaderClose("Debe ingresar " + item.descripcion);
                        loaderClose();
                        habilitarBoton();
                        isOk = false;
                        return false;
                    }
                }
                break;
            //fechas
            case 3:
            case 9:
            case 10:
            case 11:
                camposDinamicosPago[index]["valor"] = document.getElementById("datepickernd_" + item.id).value;
                if (item.opcional == 0) {
                    //validamos
                    if (isEmpty(camposDinamicosPago[index]["valor"])) {
                        mostrarValidacionLoaderClose("Debe ingresar" + item.descripcion);
                        loaderClose();
                        habilitarBoton();
                        isOk = false;
                        return false;
                    }
                }
                break;
            case 4:
            case 5:// persona
            case 20:// cuenta
            case 21:// actividad
                camposDinamicosPago[index]["valor"] = select2.obtenerValor('cbond_' + item.id);
                if (item.opcional == 0) {
                    //validamos
                    if (isEmpty(camposDinamicosPago[index]["valor"]) ||
                        camposDinamicosPago[index]["valor"] == 0) {
                        mostrarValidacionLoaderClose("Debe ingresar " + item.descripcion);
                        loaderClose();
                        habilitarBoton();
                        isOk = false;
                        return false;
                    }
                }
                break;
            case 36:// detraccion
                camposDinamicos[index]["valor"] = select2.obtenerValor('cbo_' + item.id);
                if (item.opcional == 0) {
                    //validamos
                    if (isEmpty(camposDinamicos[index]["valor"]) ||
                        camposDinamicos[index]["valor"] == 0) {
                        mostrarValidacionLoaderClose("Debe ingresar " + item.descripcion);
                        isOk = false;
                        return false;
                    }
                }
                break;
        }
    });
    return isOk;
}


function validarSumaCantidades() {
    var state = true;
    var a = $("#tbodyProductosDetalles tr");
    $.each(a, function (index, value) {
        var TDS = $(this).find('td');
        var maxQty = $(this).find("td:nth-child(2)").text();
        var currentQty = 0;
        $.each(TDS, function (indice, valor) {
            //var cajitas =  $("input[type='number']");
            var cajitas = $(this).find("input[type='number']");
            $.each(cajitas, function (i, o) {
                currentQty += parseInt($(this).val());
            });

        });

        if (currentQty > parseInt(maxQty)) {
            state = false;
        }
    });
    return state;
}
//guardar el documento y ATENCION DE SOLICITUDES
function guardarDocumentoAtencionSolicitud() {
    if (validarSumaCantidades()) {
        //parte documento operacion
        loaderShow("#modalAsignarAtencion");

        var documentoTipoId = select2.obtenerValor("cboDocumentoTipo");
        if (isEmpty(documentoTipoId)) {
            mostrarValidacionLoaderClose("Debe ingresar tipo de documento");
            return;
        }

        obtenerCheckDocumentoACopiar();

        var checkIgv = 0;

        if (!isEmpty(importes.subTotalId)) {
            if (document.getElementById('chkIncluyeIGV').checked) {
                checkIgv = 1;
            }
        } else {
            checkIgv = opcionIGV;
        }

        var tipoPago = null;

        var dtdTipoPago = obtenerDocumentoTipoDatoIdXTipo(25);
        if (!isEmpty(dtdTipoPago)) {
            tipoPago = select2.obtenerValor("cboTipoPago");
        }

        if (tipoPago != 2) {
            listaPagoProgramacion = [];
        }
        //fin documento operacion

        ax.setAccion("guardarDocumentoAtencionSolicitud");
        //documento operacion
        ax.addParamTmp("documentoTipoId", documentoTipoId);
        ax.addParamTmp("camposDinamicos", camposDinamicos);
        // ax.addParamTmp("detalle", detalle);
        ax.addParamTmp("detalle", detalleDos);
        ax.addParamTmp("documentoARelacionar", request.documentoRelacion);
        ax.addParamTmp("valor_check", cabecera.chkDocumentoRelacion);
        ax.addParamTmp("comentario", $('#txtComentario').val());
        ax.addParamTmp("checkIgv", checkIgv);
        ax.addParamTmp("monedaId", select2.obtenerValor("cboMoneda"));
        ax.addParamTmp("accionEnvio", boton.accion);
        ax.addParamTmp("tipoPago", tipoPago);
        ax.addParamTmp("listaPagoProgramacion", listaPagoProgramacion);
        //------------------------
        //documento atencion solicitud
        ax.addParamTmp("atencionSolicitudes", nArray);
        ax.consumir();
    } else {
        mostrarAdvertencia("Las cantidades ingresadas superan a las cantidades disponibles en la orden.");
    }

}

//guardar el documento y pago
function guardarDocumentoPago() {
    //deshabilitarBoton();
    //parte documento operacion
    loaderShow("#modalNuevoDocumentoPagoConDocumento");

    let datosExtras = {};
    datosExtras.afecto_detraccion_retencion = null;
    datosExtras.afecto_impuesto = null;

    var documentoTipoId = select2.obtenerValor("cboDocumentoTipo");
    if (isEmpty(documentoTipoId)) {
        mostrarValidacionLoaderClose("Debe ingresar tipo de documento");
        return;
    }

    var contOperacionTipoId = select2.obtenerValor("cboOperacionTipo");
    if (isEmpty(contOperacionTipoId) && !isEmpty(dataContOperacionTipo)) {
        mostrarValidacionLoaderClose("Debe seleccionar el tipo de operación");
        return;
    }

    obtenerCheckDocumentoACopiar();

    var checkIgv = 0;

    if (!isEmpty(importes.subTotalId)) {
        if (document.getElementById('chkIncluyeIGV').checked) {
            checkIgv = 1;
        }
    } else {
        checkIgv = opcionIGV;
    }

    var tipoPago = null;

    var dtdTipoPago = obtenerDocumentoTipoDatoIdXTipo(25);
    if (!isEmpty(dtdTipoPago)) {
        tipoPago = select2.obtenerValor("cboTipoPago");
    }

    if (tipoPago != 2) {
        listaPagoProgramacion = [];
    }

    //fin documento operacion

    //parte documento pago
    //obtenemos el tipo de documento
    var documentoTipoIdPago = select2.obtenerValor("cboDocumentoTipoNuevoPagoConDocumento");
    if (isEmpty(documentoTipoIdPago)) {
        mostrarValidacionLoaderClose("Debe ingresar tipo de documento");
        return;
    }

    //Validar y obtener valores de los campos dinamicos
    if (documentoTipoIdPago != 0) {
        if (!obtenerValoresCamposDinamicosPago())
            return;
    } else {
        camposDinamicosPago = null;
    }
    //fin documento pago

    //datos de registro de pago
    var cliente = select2.obtenerValor('cbo_' + obtenerDocumentoTipoDatoIdXTipo(5));
    var tipoCambio = $('#tipoCambio').val();
    var fecha = document.getElementById("datepicker_" + fechaEmisionId).value;
    var retencion = null;

    // efectivo a pagar
    var montoAPagar = $('#txtMontoAPagar').val();
    if (isEmpty(montoAPagar)) {
        montoAPagar = 0;
    }

    if (montoAPagar == 0 && documentoTipoIdPago == 0) {
        mostrarValidacionLoaderClose("Debe ingresar monto a pagar en efectivo");
        return;
    }

    var pagarCon = $('#txtPagaCon').val();
    var vuelto = $('#txtVuelto').val();

    var actividadEfectivo = $('#cboActividadEfectivo').val();

    if ($("#chkIGV").length > 0 && document.getElementById('chkIGV').checked) {
        datosExtras.afecto_impuesto = 1;
    }

    //Calculamos la detracción
    var dtdTipoDetraccion = obtenerDocumentoTipoDatoIdXTipo(36);
    if (!isEmpty(dtdTipoDetraccion) && igvValor > 0) {
        if (select2.obtenerValor("cbo_" + dtdTipoDetraccion) * 1 > 0) {
            let dataDetraccion = dataCofiguracionInicial.dataDetraccion.filter(item => item.id == select2.obtenerValor("cbo_" + dtdTipoDetraccion));
            datosExtras.afecto_detraccion_retencion = 1;
            datosExtras.porcentaje_afecto = dataDetraccion[0]['porcentaje'];
            datosExtras.monto_detraccion_retencion = montoTotalDetraido;
        }
    }

    //Calculamos la retención
    var dtdTipoRetencion = obtenerDocumentoTipoDatoXTipoXCodigo(4, "10");
    if (!isEmpty(dtdTipoRetencion) && igvValor > 0) {
        var valorRetencion = dtdTipoRetencion.data.filter(item => item.id == select2.obtenerValor("cbo_" + dtdTipoRetencion.id));
        if (!isEmpty(valorRetencion) && valorRetencion[0]['valor'] == 1) {
            datosExtras.afecto_detraccion_retencion = 2;
            datosExtras.porcentaje_afecto = dataCofiguracionInicial.dataRetencion.porcentaje;
            datosExtras.monto_detraccion_retencion = montoTotalRetencion;
        }
    }

    if (montoTotalDetraido > 0 && montoTotalRetencion > 0) {
        mostrarValidacionLoaderClose('El documento no puede estar afecto a detracción retención al mismo tiempo.');
        return;
    }


    if ((montoTotalDetraido > 0 || montoTotalRetencion > 0) && igvValor == 0) {
        mostrarValidacionLoaderClose('El documento no esta afecto a IGV, por lo tanto no puede estar afecto a retención o detracción.');
        return;
    }

    //validar total de documento de pago = total de documento a pagar.
    var banderaTotalPago = true;
    if (documentoTipoIdPago == 0) {
        if (parseFloat(montoAPagar) != parseFloat(calculoTotal)) {
            banderaTotalPago = false;
        }
    } else {
        if (parseFloat($('#' + totalPago).val()) != parseFloat(calculoTotal)) {
            banderaTotalPago = false;
        }
    }

    if (!banderaTotalPago) {
        mostrarValidacionLoaderClose("El monto de pago debe ser igual al monto total del documento.");
        return;
    }

    var periodoId = select2.obtenerValor('cboPeriodo');

    obtenerDistribucion();
    //        deshabilitarBoton();
    ax.setAccion("guardarDocumentoPago");
    //documento operacion
    ax.addParamTmp("documentoTipoId", documentoTipoId);
    ax.addParamTmp("contOperacionTipoId", contOperacionTipoId);
    ax.addParamTmp("camposDinamicos", camposDinamicos);
    ax.addParamTmp("detalle", detalle);
    ax.addParamTmp("documentoARelacionar", request.documentoRelacion);
    ax.addParamTmp("valor_check", cabecera.chkDocumentoRelacion);
    ax.addParamTmp("comentario", $('#txtComentario').val());
    ax.addParamTmp("checkIgv", checkIgv);
    ax.addParamTmp("monedaId", select2.obtenerValor("cboMoneda"));
    ax.addParamTmp("accionEnvio", boton.accion);
    ax.addParamTmp("tipoPago", tipoPago);
    ax.addParamTmp("listaPagoProgramacion", listaPagoProgramacion);
    //------------------------

    //documento pago
    ax.addParamTmp("documentoTipoIdPago", documentoTipoIdPago);
    ax.addParamTmp("camposDinamicosPago", camposDinamicosPago);

    //registro de pago
    ax.addParamTmp("cliente", cliente);
    ax.addParamTmp("montoAPagar", montoAPagar);
    ax.addParamTmp("tipoCambio", tipoCambio);
    ax.addParamTmp("fecha", fecha);
    ax.addParamTmp("retencion", retencion);
    ax.addParamTmp("actividadEfectivo", actividadEfectivo);
    ax.addParamTmp("empresaId", commonVars.empresa);

    //totales
    ax.addParamTmp("totalDocumento", $('#' + importes.totalId).val());
    ax.addParamTmp("totalPago", $('#' + totalPago).val());

    ax.addParamTmp("periodoId", periodoId);
    ax.addParamTmp("detalleDistribucion", dataDistribucion);
    ax.addParamTmp("distribucionObligatoria", distribucionObligatoria);
    ax.addParamTmp("datosExtras", datosExtras);
    ax.consumir();
}

function reenumerarFilasDetalle(indice, numItemActual) {
    var numItem = numItemActual;
    for (var i = (indice + 1); i < nroFilasReducida; i++) {
        if ($('#txtNumItem_' + i).length > 0) {
            $('#txtNumItem_' + i).html(numItem);
            numItem++;
        }
    }

    numeroItemFinal--;
}

function dibujarAtencionLeftSide() {
    $("#theadProductosDetalles").append("<th>Producto</th><th>Cantidad</th>");
    var html = '';
    for (var i = 0; i < detalle.length; i++) {
        html += '<tr>';
        html += '<td>' + detalle[i].bienDesc + '</td>';
        html += '<td style="text-align: right;">' + (detalle[i].cantidad / 1) + '</td>';
        html += '<td style="display:none;">' + detalle[i].bienId + '</td>';
        html += '</tr>';
    }
    $('#tbodyProductosDetalles').append(html);
}


// God helps your poor soul understand the code you are about to see :D
var count = 0;
var headersNoRepetir = new Set();
var htmlUniqueHeaders = new Set();
var onClickHeaderAtencion;

var dataMapaHeaders = new Map();
var mapaHeaders = new Map();

var dataMapaEstadoHeaders = new Map();
var mapaEstadoHeaders = new Map();

var dataMapaCantidadesValidacion, mapaCantidadesValidacion = new Map();
var dataMapaCantidadesAsignacion, mapaCantidadesAsignacion = new Map();
var showModal = true;
var arrayBienIdCorrectos = [];
var globalAS;
var sub = 0;

function cancelarModalAsignacion() {
    detalle = detalleDos;
}



var arrayCantidades = new Array();
var mapaBienId = new Map();
var nArray;


function asignar() {

    mapaBienId.clear();
    arrayCantidades = [];

    var allInputIds = $('#tbodyProductosDetalles input').map(function (index, dom) {
        return dom.id
    });
    for (var i = 0; i < allInputIds.length; i++) {
        var splat = (allInputIds[i].replace('txt', "")).split("_");
        var idSolic = splat[0];
        var result = globalAS.filter(function (obj) {
            return obj.documentoId == idSolic;
        });
        if (!isEmpty(result)) {
            var search = "#" + idSolic + "_" + splat[1] + " input";
            var inputGroup = $(search);
            for (var o = 0; o < result[0].detalleBien.length; o++) {

                if (parseInt(inputGroup.last().attr('value')) == parseInt(result[0].detalleBien[o].bien_id)) {

                    if (mapaBienId.has(parseInt(result[0].detalleBien[o].bien_id))) {
                        var localArray = mapaBienId.get(parseInt(result[0].detalleBien[o].bien_id));
                        localArray.push({
                            'cantidad': inputGroup.first().val(),
                            'mov_bien_ant_id': result[0].detalleBien[o].movimiento_bien_id
                        });
                        mapaBienId.set(parseInt(result[0].detalleBien[o].bien_id), localArray);
                    } else {
                        arrayCantidades.push({
                            'cantidad': inputGroup.first().val(),
                            'mov_bien_ant_id': result[0].detalleBien[o].movimiento_bien_id
                        });
                        mapaBienId.set(parseInt(result[0].detalleBien[o].bien_id), arrayCantidades);
                        arrayCantidades = [];
                    }

                }
            }
        } else {
            //do nothing xdxd
        }
    }
    nArray = Array.from(mapaBienId);

    guardarDocumentoAtencionSolicitud();
}

//OPCION PARA REFRESCAR PRODUCTO
function cargarBien() {
    var rutaAbsoluta = URL_BASE + 'index.php?token=2';
    window.open(rutaAbsoluta, "nuevo", "directories=no, location=no, menubar=no, scrollbars=yes, statusbar=no, tittlebar=no, width=1500, height=900");
}

function actualizarComboProducto(indice) {
    loaderShow();
    ax.setAccion("obtenerProductos");
    ax.addParamTmp("empresaId", commonVars.empresa);
    ax.setTag(indice);
    ax.consumir();
}

function onResponseObtenerProductos(data, indice) {
    cargarBienDetalleCombo(data, indice);
}

function iniciarArchivoAdjunto() {
    $("#archivoAdjunto").change(function () {
        let nombre_archivo = $('#archivoAdjunto').val().slice(12);
        let dataDocumentoTipoSeleccionado = obtenerDocumentoTipoSeleccionado();
        if (dataDocumentoTipoSeleccionado.identificador_negocio == 1) {
            if (nombre_archivo.split('.').pop() != 'xls') {
                $("#nombreArchivo").html('');
                $('#idPopover').attr("data-content", '');
                $('#dataArchivo').attr('value', '');
                $('#archivoAdjunto').val('');
                $.Notification.autoHideNotify('warning', 'top right', 'Validación', "El archivo tiene debe tener la extensión .xls");
                return;
            }
        }


        $("#nombreArchivo").html(nombre_archivo);
        //llenado del popover
        $('#idPopover').attr("data-content", nombre_archivo);
        $('[data-toggle="popover"]').popover('show');
        $('.popover-content').css('color', 'black');
        $('[class="popover fade top in"]').css('z-index', '0');

        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = imageIsLoaded;
            reader.readAsDataURL(this.files[0]);
        }
    });
}

var lstDocumentoArchivos = [];
var lstDocEliminado = [];
var cont = 0;
var ordenEdicion = 0;
function eliminarDocumento(docId) {
    ordenEdicion = 0;
    lstDocumentoArchivos.some(function (item) {
        if (item.id == docId) {
            lstDocumentoArchivos.splice(ordenEdicion, 1);
            lstDocEliminado.push([{ id: docId, archivo: item.archivo }])
            onResponseListarArchivosDocumento(lstDocumentoArchivos);
            return item.id === docId;
        }
        ordenEdicion++;
    });
}

$("#btnAgregarDoc").click(function fileIsLoaded(e) {
    var documento = {};
    if (!isEmpty($("#archivoAdjuntoMulti").val())) {
        if ($("#archivoAdjuntoMulti").val().slice(12).length > 0) {
            documento.data = $("#dataArchivoMulti").val();
            documento.archivo = $("#archivoAdjuntoMulti").val().slice(12);
            documento.id = "t" + cont++;
            lstDocumentoArchivos.push(documento);
            onResponseListarArchivosDocumento(lstDocumentoArchivos);
            $("#archivoAdjuntoMulti").val("");
            $("#dataArchivoMulti").val("");
            $('[data-toggle="popover"]').popover('hide');
            $('#idPopoverMulti').attr("data-content", "");
            $("#msjDocumento").html("");
            $("#msjDocumento").hide();
        } else {
            onResponseListarArchivosDocumento(lstDocumentoArchivos);
        }

    } else {
        $("#msjDocumento").html("Debe adjuntar un archivo primero para agregarlo a la lista");
        $("#msjDocumento").show();
    }

});

function onResponseListarArchivosDocumento(data) {

    $("#dataList2").empty();
    var cuerpo_total = "";
    var cuerpo = "";
    var cabeza = "<table id='datatable3' class='table table-striped table-bordered'>"
        + "<thead>"
        + "<tr>"
        + "<th style='text-align:center; vertical-align: middle; width:20%'>#</th>"
        + "<th style='text-align:center; vertical-align: middle;'>Nombre</th>"
        + "<th style='text-align:center; vertical-align: middle; width:15%'>Acciones</th>"
        + "</tr>"
        + "</thead>";
    if (!isEmpty(data)) {

        $.each(data, function (index, item) {
            if (!item.id.match(/t/g)) {
                lstDocumentoArchivos[index]["data"] = "util/uploads/documentoAdjunto/" + item.nombre;
            }

            cuerpo = "<tr>"
                + "<td style='text-align:center;'>" + (index + 1) + "</td>"
                + "<td style='text-align:center;'>" + item.archivo + "</td>";

            cuerpo += "<td style='text-align:center;'>"
                + "<a href='" + item.data + "' download='" + item.archivo + "' target='_blank'><i class='fa fa-cloud-download' style='color:#1ca8dd;'></i></a>&nbsp;\n"
                + "<a href='#' onclick='eliminarDocumento(\"" + item.id + "\")'><i class='fa fa-trash-o' style='color:#cb2a2a;'></i></a>&nbsp;\n"
                + "</td>"
                + "</tr>";
            cuerpo_total += cuerpo;
        });
    }
    var pie = '</table>';
    var html = cabeza + cuerpo_total + pie;
    $("#dataList2").append(html);
    $("#datatable3").DataTable();
}

function imageIsLoaded(e) {
    $('#dataArchivo').attr('value', e.target.result);
    let dataDocumentoTipoSeleccionado = obtenerDocumentoTipoSeleccionado();
    if (dataDocumentoTipoSeleccionado.identificador_negocio == 1) {
        loaderShow();
        ax.setAccion("leerDocumentoAdjunto");
        ax.addParamTmp("data", e.target.result);
        ax.consumir();

    }
}

function adjuntar() {
    onResponseListarArchivosDocumento(lstDocumentoArchivos);
    $('#tituloVisualizarModalArchivos').html("Adjuntar archivos");
    $('#modalVisualizarArcvhivos').modal('show');
}

function iniciarArchivoAdjuntoMultiple() {
    $("#archivoAdjuntoMulti").change(function () {
        $("#nombreArchivoMulti").html($('#archivoAdjuntoMulti').val().slice(12));

        //llenado del popover
        $('#idPopoverMulti').attr("data-content", $('#archivoAdjuntoMulti').val().slice(12));
        $('[data-toggle="popover"]').popover('show');
        $('.popover-content').css('color', 'black');
        $('[class="popover fade top in"]').css('z-index', '0');

        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = imageIsLoadedMulti;
            reader.readAsDataURL(this.files[0]);
        }
    });
}

function imageIsLoadedMulti(e) {
    $('#dataArchivoMulti').attr('value', e.target.result);
}

// Anticipos
function mostrarAnticipos(data) {
    var anticipos = data.anticipos;
    var actividades = data.actividades;

    bandera.validacionAnticipos = 1;
    mostrarModalAnticipo();
    $("#dtAnticipos").dataTable({
        order: [[1, "desc"]],
        "ordering": false,
        "data": anticipos,
        "columns": [
            { "data": "documento_id", sClass: "columnAlignCenter" },
            { "data": "serie" },
            { "data": "fecha_emision" },
            { "data": "descripcion" },
            { "data": "pendiente", "sClass": "alignRight" }
        ],
        columnDefs: [
            {
                "render": function (data, type, row) {
                    return '<label class="cr-styled"><input type="checkbox" id="chkAnticipo_' + data + '"><i class="fa"></i></label>';
                },
                "targets": 0
            },
            {
                "render": function (data, type, row) {
                    return formatearFechaBDCadena(data);
                },
                "targets": 2
            },
            {
                "render": function (data, type, row) {
                    return row.simbolo + ' ' + parseFloat(data).formatMoney(2, '.', ',');
                },
                "targets": 4
            }
        ],
        "dom": '<"top">rt<"bottom"><"clear">',
        "destroy": true
    });
    dataTemporal.anticipos = anticipos;
    //    select2.iniciarElemento("cboAnticipoActividad");
    //    select2.cargar("cboAnticipoActividad", actividades, "id", "descripcion");
}

function limpiarAnticipos() {
    dataTemporal.anticipos = null;
    bandera.validacionAnticipos = 2;
    enviar(boton.accion);
}
function aplicarAnticipos() {
    // Validamos
    // Si se ha seleccionado algun anticipo, debe de haberse seleccionado una cuenta
    var validaActividad = false;
    $.each(dataTemporal.anticipos, function (indexAnticipo, itemAnticipo) {
        if (document.getElementById('chkAnticipo_' + itemAnticipo.documento_id).checked) {
            validaActividad = true;
            return false;
        }
    });
    if (!validaActividad) {
        mostrarAdvertencia("No ha seleccionado ningún anticipo");
        return;
    }
    bandera.validacionAnticipos = 3;
    // Enviamos a guardar
    enviar(boton.accion);
}
function deshabilitaBotonesAnticipos() {
    $('#btnLimpiaAnticipos').prop('disabled', true);
    $('#btnAplicaAnticipos').prop('disabled', true);
}
function obtenerAnticiposAAplicar() {
    if (!isEmpty(dataTemporal.anticipos)) {
        var anticiposAplicar = [];
        $.each(dataTemporal.anticipos, function (indexAnticipo, itemAnticipo) {
            if (document.getElementById('chkAnticipo_' + itemAnticipo.documento_id).checked) {
                anticiposAplicar.push({ documentoId: itemAnticipo.documento_id, pendiente: itemAnticipo.pendiente });
            }
        });
        return anticiposAplicar;
    } else {
        return null;
    }
}
function cerrarModalAnticipo() {
    if (bandera.validacionAnticipos == 1) {
        $('#modalAnticipos').modal('hide');
        $('.modal-backdrop').hide();
    }
}
function mostrarModalAnticipo() {
    $('#modalAnticipos').modal({ backdrop: 'static', keyboard: false });
    $('#modalAnticipos').modal('show');
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
    //    console.log(fechaArray,periodoId);
    return periodoId;
}

function obtenerDocumentoTipoDatoXTipoXCodigo(tipo, codigo) {
    var dataConfig = dataCofiguracionInicial.documento_tipo_conf;

    var dtd = null;
    if (!isEmpty(dataConfig)) {
        $.each(dataConfig, function (index, item) {
            if (parseInt(item.tipo) === parseInt(tipo) && item.codigo == codigo) {
                dtd = item;
                return false;
            }
        });
    }

    return dtd;
}

function cambiarAnchoBusquedaDesplegable() {
    var ancho = $("#divBuscador").width();
    $("#ulBuscadorDesplegable").width((ancho - 5) + "px");
    $("#ulBuscadorDesplegable2").width((ancho - 5) + "px");
}

function onChangeOrganizadorDestino() {
    if (!isEmpty(obtenerDocumentoTipoDatoIdXTipo(17))) {
        obtenerDireccionOrganizador(2);
    }
}

function registrarComentarioBien() {
    var indice = $('#indiceComentarioBien').val();

    var indexTemporal = -1;
    $.each(detalle, function (i, item) {
        if (parseInt(item.index) === parseInt(indice)) {
            indexTemporal = i;
            return false;
        }
    });

    if (indexTemporal != -1) {
        //        detalle[indexTemporal].comentarioBien = $("#comentarioBien").code();
        detalle[indexTemporal].comentarioBien = $("#comentarioBien").val();
        if (!isEmpty($("#comentarioBien").val())) {
            $("#obsupcboBien_" + indexTemporal).removeClass("hidden");
            $("#obscboBien_" + indexTemporal).html(reducirTexto($("#comentarioBien").val(), 50));
            $("#obscboBien_" + indexTemporal).removeClass("hidden");

        } else {
            $("#obsupcboBien_" + indexTemporal).addClass("hidden");
            $("#obscboBien_" + indexTemporal).html(reducirTexto($("#comentarioBien").val(), 50));
            $("#obscboBien_" + indexTemporal).addClass("hidden");
        }
        $('#modalComentarioBien').modal('hide');
    } else {
        mostrarAdvertencia('Seleccione un producto');
        return;
    }


}

function registrarAgrupadorBien() {
    var indice = $('#indiceAgrupadorBien').val();

    var indexTemporal = -1;
    $.each(detalle, function (i, item) {
        if (parseInt(item.index) === parseInt(indice)) {
            indexTemporal = i;
            return false;
        }
    });

    if (indexTemporal != -1) {
        var valor = select2.obtenerText("cboAgrupador");
        var valor_ = select2.obtenerValor("cboAgrupador");

        detalle[indexTemporal].agrupadorId = valor_;
        if (!isEmpty(valor)) {
            //$("#obsupcboBien_"+indexTemporal).removeClass("hidden");
            $("#badge_" + indexTemporal).html(reducirTexto(valor, 50));
            $("#obscboBien_" + indexTemporal).removeClass("hidden");

        } else {
            //$("#obsupcboBien_"+indexTemporal).addClass("hidden");
            $("#badge_" + indexTemporal).html(reducirTexto(valor, 50));
            //$("#obscboBien_"+indexTemporal).addClass("hidden");
        }
        $('#modalAgrupadorBien').modal('hide');
    } else {
        mostrarAdvertencia('Seleccione un producto');
        return;
    }


}

function initSwitch() {
    $('#switchProductoDuplicado').btnSwitch({
        Theme: 'Android',
    });

    setTimeout(function () {
        $("#switchProductoDuplicado").btnSwitch("setValue", false);
    }, 2000);

    $('#switchCotizacionTottus').btnSwitch({
        Theme: 'Android',
    });

    setTimeout(function () {
        $("#switchCotizacionTottus").btnSwitch("setValue", false);
    }, 2000);
}

function obtenerMontoRetencion() {
    let dataRetencion = obtenerDocumentoTipoDatoXTipoXCodigo("4", "10");
    if (!isEmpty(dataRetencion) && !isEmpty(dataCofiguracionInicial.dataRetencion)) {
        var retencion = dataCofiguracionInicial.dataRetencion;
        var montoTotal = $("#" + importes.totalId).val();
        if (!isEmpty(retencion) && !isEmpty(montoTotal) && (montoTotal * 1) > 0 && igvValor > 0) {
            var valorRetencion = dataRetencion.data.filter(item => item.id == select2.obtenerValor("cbo_" + dataRetencion.id));
            if (!isEmpty(valorRetencion) && valorRetencion[0]['valor'] == 1) {
                var monto_minimo = retencion.monto_minimo * 1;
                montoTotal = montoTotal * 1;
                var monedaBase = dataCofiguracionInicial.moneda.filter(item => item.base == 1)[0];

                var monedaSeleccionada = select2.obtenerValor('cboMoneda');

                var tipoCambio = ($("#tipoCambio").length > 0 && !isEmpty($('#tipoCambio').val()) ? ($('#tipoCambio').val()) * 1 : 0);
                if (monedaBase.id != monedaSeleccionada) {
                    monto_minimo = (monto_minimo / tipoCambio);
                }

                if (montoTotal > monto_minimo) {
                    var montoRetenido = (montoTotal) * ((retencion.porcentaje) / 100);
                    $('#txt_' + dataRetencion.id).removeAttr('style').attr('style', 'color:black;font-style: normal;');
                    $('#txt_' + dataRetencion.id).html('<b>Aplica la retención de ' + monedaSimbolo + ' ' + formatearNumero(montoRetenido) + '</b>');
                    $('#txt_' + dataRetencion.id).show();
                    montoTotalRetencion = montoRetenido;
                } else if (montoTotal <= monto_minimo) {
                    $('#txt_' + dataRetencion.id).removeAttr('style').attr('style', 'color:red;font-style: normal;');
                    $('#txt_' + dataRetencion.id).html('<b>El monto mínimo es ' + monedaSimbolo + ' ' + formatearNumero(monto_minimo) + '</b>');
                    $('#txt_' + dataRetencion.id).show();
                    montoTotalRetencion = 0;
                }
            } else {
                $('#txt_' + dataRetencion.id).hide();
                montoTotalRetencion = 0;
            }
        } else {
            $('#txt_' + dataRetencion.id).hide();
            montoTotalRetencion = 0;
        }
    } else {
        montoTotalRetencion = 0;
    }

    if (!isEmpty(importes.calculoId)) {
        calculoTotal = parseFloat(devolverDosDecimales($('#' + importes.totalId).val() - montoTotalDetraido - montoTotalRetencion));
    }
}


function obtenerMontoDetraccion() {
    if ($('#cbo_' + cboDetraccionId).length != 0 && ($('#cbo_' + cboDetraccionId).val() * 1) > 0) {
        var detraccion = dataCofiguracionInicial.dataDetraccion.filter(item => item.id == select2.obtenerValor('cbo_' + cboDetraccionId));
        var montoTotal = $("#" + importes.totalId).val();
        if (!isEmpty(detraccion) && !isEmpty(montoTotal) && (montoTotal * 1) > 0) {
            var monto_minimo = detraccion[0].monto_minimo * 1;
            montoTotal = montoTotal * 1;
            var monedaBase = dataCofiguracionInicial.moneda.filter(item => item.base == 1)[0];

            var monedaSeleccionada = select2.obtenerValor('cboMoneda');

            var tipoCambio = ($("#tipoCambio").length > 0 && !isEmpty($('#tipoCambio').val()) ? ($('#tipoCambio').val()) * 1 : 0);
            if (monedaBase.id != monedaSeleccionada) {
                monto_minimo = (monto_minimo / tipoCambio);
            }
            if (igvValor == 0) {
                $('#txt_' + cboDetraccionId).removeAttr('style').attr('style', 'color:red;font-style: normal;');
                $('#txt_' + cboDetraccionId).html('<b>El monto mínimo es ' + monedaSimbolo + ' ' + formatearNumero(monto_minimo) + '</b>');
                $('#txt_' + cboDetraccionId).show();
                montoTotalDetraido = 0;
            } else if (montoTotal > monto_minimo) {
                var textoConversion = '';
                var montoDetraido = (montoTotal) * ((detraccion[0].porcentaje) / 100);
                if (monedaBase.id == monedaSeleccionada) {
                    montoDetraido = Math.round(montoDetraido);
                } else {
                    textoConversion = '(' + monedaBase.simbolo + ' ' + formatearNumero(Math.round(montoDetraido * tipoCambio)) + ')';
                }

                $('#txt_' + cboDetraccionId).removeAttr('style').attr('style', 'color:black;font-style: normal;');
                $('#txt_' + cboDetraccionId).html('<b>Aplica la detracción de ' + monedaSimbolo + ' ' + formatearNumero(montoDetraido) + ' ' + textoConversion + '</b>');
                $('#txt_' + cboDetraccionId).show();
                montoTotalDetraido = montoDetraido;
            } else if (montoTotal <= monto_minimo) {
                $('#txt_' + cboDetraccionId).removeAttr('style').attr('style', 'color:red;font-style: normal;');
                $('#txt_' + cboDetraccionId).html('<b>El monto mínimo es ' + monedaSimbolo + ' ' + formatearNumero(monto_minimo) + '</b>');
                $('#txt_' + cboDetraccionId).show();
                montoTotalDetraido = 0;
            }
        } else {
            $('#txt_' + cboDetraccionId).hide();
            montoTotalDetraido = 0;
        }
    } else {
        $('#txt_' + cboDetraccionId).hide();
        montoTotalDetraido = 0;
    }

    if (!isEmpty(importes.calculoId)) {
        calculoTotal = parseFloat(devolverDosDecimales($('#' + importes.totalId).val() - montoTotalDetraido - montoTotalRetencion));
    }
}


function visualizarInformacionDocumentoAdjunto() {
    //Limpiar contedido
    $("#divPresupuestoIdModal").empty();
    $("#divSubPresupuestoIdModal").empty();
    $("#divClienteIdModal").empty();
    $("#divFechaIdModal").empty();
    $("#divLugarIdModal").empty();

    $("#dataTablePartidasModal tbody").empty();
    $("#dataTablePartidasModal tfoot").empty();

    let contenidoArchivo = JSON.parse(contenidoArchivoJson);
    if (!isEmpty(contenidoArchivo)) {
        $("#divPresupuestoIdModal").html("<b>Presupuesto :&nbsp;&nbsp;</b>" + contenidoArchivo.presupuesto.codigo + " | " + contenidoArchivo.presupuesto.descripcion);
        $("#divSubPresupuestoIdModal").html("<b>Subpresupuesto :&nbsp;&nbsp;</b>" + contenidoArchivo.subpresupuesto.codigo + " | " + contenidoArchivo.subpresupuesto.descripcion);
        $("#divClienteIdModal").html("<b>Cliente :&nbsp;&nbsp;</b>" + contenidoArchivo.cliente);
        $("#divFechaIdModal").html("<b>Costo al :&nbsp;&nbsp;</b>" + contenidoArchivo.fecha_costo);
        $("#divLugarIdModal").html("<b>Lugar :&nbsp;&nbsp;</b>" + contenidoArchivo.lugar);
        let dataPartidas = contenidoArchivo.partidas;
        if (!isEmpty(dataPartidas)) {
            let tablaBodyHtml = "";
            $.each(dataPartidas, function (index, item) {
                let labelBInicio = "<b>";
                let labelBFin = "</b>";
                if (item.es_padre != 1) {
                    labelBInicio = "";
                    labelBFin = "";
                }

                let metrado = (!isEmpty(item.metrado) ? formatearNumeroPorCantidadDecimales(item.metrado, 2) : "");
                let precio = (!isEmpty(item.precio) ? formatearNumeroPorCantidadDecimales(item.precio, 2) : "");
                let parcial = (!isEmpty(item.parcial) ? formatearNumeroPorCantidadDecimales(item.parcial, 2) : "");

                tablaBodyHtml += "<tr>"
                    + "<td style='text-align:left;'>" + labelBInicio + item.codigo + labelBFin + "</td>"
                    + "<td style='text-align:left;'>" + labelBInicio + item.descripcion + labelBFin + "</td>"
                    + "<td style='text-align:center;'>" + item.unidad_medida + "</td>"
                    + "<td style='text-align:right;'>" + metrado + "</td>"
                    + "<td style='text-align:right;'>" + precio + "</td>"
                    + "<td style='text-align:right;'>" + parcial + "</td>"
                    + "<tr>";
            });
            $("#dataTablePartidasModal tbody").html(tablaBodyHtml);
        }
        let dataTotalizados = contenidoArchivo.totalizados;
        if (!isEmpty(dataTotalizados)) {
            let tablaFootHtml = '';
            if (!isEmpty(dataTotalizados.costo_directo)) {
                tablaFootHtml += "<tr>"
                    + "<td style='text-align:right;' colspan='5'><b>" + dataTotalizados.costo_directo.nombre + "</b></td>"
                    + "<td style='text-align:right;'><b>" + formatearNumeroPorCantidadDecimales(dataTotalizados.costo_directo.monto, 2) + "</b></td>"
                    + "<tr>";
            }

            if (!isEmpty(dataTotalizados.adicionales)) {
                $.each(dataTotalizados.adicionales, function (index, item) {
                    tablaFootHtml += "<tr>"
                        + "<td style='text-align:right;' colspan='5'><b>" + item.nombre + "</b></td>"
                        + "<td style='text-align:right;'><b>" + formatearNumeroPorCantidadDecimales(item.monto, 2) + "</b></td>"
                        + "<tr>";
                });
            }

            if (!isEmpty(dataTotalizados.subtotal)) {
                tablaFootHtml += "<tr>"
                    + "<td style='text-align:right;' colspan='5'><b>" + dataTotalizados.subtotal.nombre + "</b></td>"
                    + "<td style='text-align:right;'><b>" + formatearNumeroPorCantidadDecimales(dataTotalizados.subtotal.monto, 2) + "</b></td>"
                    + "<tr>";
            }

            if (!isEmpty(dataTotalizados.igv)) {
                tablaFootHtml += "<tr>"
                    + "<td style='text-align:right;' colspan='5'><b>" + dataTotalizados.igv.nombre + "</b></td>"
                    + "<td style='text-align:right;'><b>" + formatearNumeroPorCantidadDecimales(dataTotalizados.igv.monto, 2) + "</b></td>"
                    + "<tr>";
            }

            if (!isEmpty(dataTotalizados.total)) {
                tablaFootHtml += "<tr>"
                    + "<td style='text-align:right;' colspan='5'><b>" + dataTotalizados.total.nombre + "</b></td>"
                    + "<td style='text-align:right;'><b>" + formatearNumeroPorCantidadDecimales(dataTotalizados.total.monto, 2) + "</b></td>"
                    + "<tr>";
            }
            $("#dataTablePartidasModal tfoot").html(tablaFootHtml);
        }
    }

    $("#modalContenidoArchivo").modal("show");


}
var campoNumeroId = null;
function onChangePeriodo() {
    if (dataCofiguracionInicial.movimientoTipo[0]['codigo'] == '7') {
        let dataPeriodo = dataCofiguracionInicial.periodo.filter(item => item.id == select2.obtenerValor("cboPeriodo"));

        let serie = dataPeriodo[0]['anio'].substring(2) + dataPeriodo[0]['mes'];

        let documentoTipoDato = dataCofiguracionInicial.documento_tipo_conf.filter(item => item.tipo == 7);

        $("#txt_" + documentoTipoDato[0]['id']).val(serie);

        let documentoTipoDatoNumero = dataCofiguracionInicial.documento_tipo_conf.filter(item => item.tipo == 8);
        campoNumeroId = documentoTipoDatoNumero[0]['id'];

        loaderShow();
        ax.setAccion("obtenerNumeroAutoXDocumentoTipo");
        ax.addParamTmp("dotumentoTipoId", select2.obtenerValor("cboDocumentoTipo"));
        ax.addParamTmp("serie", serie);
        ax.consumir();
    }
}

//CONTABILIDAD
/****************************************************************************************************************************************/
$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    var target = $(e.target).attr("href");
    if ((target == '#distribucion')) {
        var monto = obtenerSubTotal_Total_Distribucion();
        if (!isEmpty(importes.subTotalId) && monto * 1 < 0) {
            $('#tabDetalle').click();
            mostrarAdvertencia('Primero debe ingresar el sub total para iniciar con la distribución contable.');
        } else if (monto * 1 < 0) {
            $('#tabDetalle').click();
            mostrarAdvertencia('Primero debe ingresar el monto total para iniciar con la distribución contable.');
        } else if (isEmpty(select2.obtenerValor('cboOperacionTipo'))) {
            $('#tabDetalle').click();
            mostrarAdvertencia('Primero seleccione el tipo de operación.');
        }
    }
});

var nroFilasDistribucion = 0;

function llenarCabeceraDistribucion() {
    $('#headDetalleCabeceraDistribucion').empty();
    var operacionTipo = dataContOperacionTipo.filter(function (obj) {
        return obj.id == select2.obtenerValor('cboOperacionTipo');
    })[0];

    //    var operacionTipo = dataContOperacionTipo[document.getElementById('cboOperacionTipo').options.selectedIndex];
    var fila = ' <tr role="row">';
    fila += '<th style="text-align: center; width: 5%;" class="sorting_disabled" rowspan="1" colspan="1">#</th>';
    fila += '<th style="text-align: center; width: 30%;" class="sorting_disabled" rowspan="1" colspan="1">Cuenta Contable</th>';
    if (operacionTipo.requiere_centro_costo == 1 || operacionTipo.requiere_centro_costo == 2) {
        fila += '<th style="text-align: center; width: 30%;" class="sorting_disabled" rowspan="1" colspan="1">Centro Costo</th>';
    }
    fila += '<th style="text-align:center; width: 15%;" class="sorting_disabled" rowspan="1" colspan="1">Procentaje(%)</th>';
    fila += '<th style="text-align:center; width: 15%;" class="sorting_disabled" rowspan="1" colspan="1">Monto</th>';
    fila += '<th style="text-align: center; width: 5%;" class="sorting_disabled" rowspan="1" colspan="1">Acciones</th>';
    $('#headDetalleCabeceraDistribucion').append(fila);
}

function agregarFilaDistribucion(opcion) {

    if (obtenerAcumuladosPorcentajes_Montos(2) >= obtenerSubTotal_Total_Distribucion() && opcion != 1) {
        if (!isEmpty(importes.subTotalId))
            mostrarAdvertencia('El monto no puede exceder al sub total.');
        else
            mostrarAdvertencia('El monto no puede exceder al total.');
        return;
    }
    var operacionTipoId = select2.obtenerValor('cboOperacionTipo');
    if (isEmpty(operacionTipoId)) {
        mostrarAdvertencia('Primero seleccione el tipo de operación.');
        return;
    }

    var operacionTipo = dataContOperacionTipo.filter(item => item.id == select2.obtenerValor('cboOperacionTipo'))[0];
    var indice = nroFilasDistribucion;

    let addOnchageCboCuenta = (operacionTipo.requiere_centro_costo == 2 ? " onchange = 'onChangeCuentaContable(" + indice + ",this.value);' " : "");

    var fila = "<tr id=\"trDetalleDistribucion_" + indice + "\">";
    fila += "<td style='border:0; vertical-align: middle; padding-right: 10px;' id='txtNumItemDistribucion_" + indice + "' name='txtNumItemDistribucion_" + indice + "' align='center'></td>";
    fila += "<td style='border:0; vertical-align: middle;'>" +
        "<div class='input-group col-lg-12 col-md-12 col-sm-12 col-xs-12'>" +
        "<select name='cboCuentaContable_" + indice + "' id='cboCuentaContable_" + indice + "' class='select2' " + addOnchageCboCuenta + " >" +
        "</select></div></td>";

    if (operacionTipo.requiere_centro_costo == 1 || operacionTipo.requiere_centro_costo == 2) {
        fila += "<td style='border:0; vertical-align: middle;'>" +
            "<div class='input-group col-lg-12 col-md-12 col-sm-12 col-xs-12'>" +
            "<select name='cboCentroCosto_" + indice + "' id='cboCentroCosto_" + indice + "' class='select2'>" +
            "</select></div></td>";
    }

    fila += "<td style='border:0; vertical-align: middle;'><div class='input-group col-lg-12 col-md-12 col-sm-12 col-xs-12' >" +
        "<input type='number'   id='txtPorcentajeDistribucion_" + indice + "' name='txtPorcentajeDistribucion_" + indice + "' class='form-control' required='' aria-required='true' style='text-align: right;' " +
        "onkeyup='if (this.value.length > 13) {this.value = this.value.substring(0, 13)}; actualizarMontoDistribucion(" + indice + ");'  /><span class='input-group-addon'>%</span></div></td>";

    fila += "<td style='border:0; vertical-align: middle;'><div class='input-group col-lg-12 col-md-12 col-sm-12 col-xs-12'>" +
        "<div class='input-group col-lg-12 col-md-12 col-sm-12 col-xs-12'>" +
        "<input type='number' id='txtMontoDistribucion_" + indice + "' name='txtMontoDistribucion_" + indice + "' class='form-control' required='' aria-required='true' value='' style='text-align: right;' " +
        "onkeyup='if (this.value.length > 13) {this.value = this.value.substring(0, 13)}; calculoPorcentajePagoDistribucion(" + indice + ");'  /></div></td>";

    fila += "<td style='border:0; align='center' vertical-align: middle;'>&nbsp;<a onclick='confirmarEliminarDistribucion(" + indice + ");'>" +
        "<i class='fa fa-trash-o' style='color:#cb2a2a;' title='Eliminar'></i></a></td>";


    $('#datatableDistribucion tbody').append(fila);
    nroFilasDistribucion++;
    reenumerarFilasDetalleDistribucion();
    if (operacionTipo.requiere_centro_costo == 1 || operacionTipo.requiere_centro_costo == 2) {
        if (!isEmpty(dataCofiguracionInicial.centroCosto)) {
            $.each(dataCofiguracionInicial.centroCosto, function (indexPadre, centroCostoPadre) {
                if (isEmpty(centroCostoPadre.centro_costo_padre_id)) {
                    var html = '<optgroup id="' + centroCostoPadre.id + '" label="' + centroCostoPadre['codigo'] + ' | ' + centroCostoPadre['descripcion'] + '">';
                    // centroCostoPadre['codigo'] + " | " + centroCostoPadre['descripcion']
                    var dataHijos = dataCofiguracionInicial.centroCosto.filter(centroCosto => centroCosto.centro_costo_padre_id == centroCostoPadre.id);
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
            select2.asignarValor("cboCentroCosto_" + indice, "");
        }
    }
    var array_cuentas_relaciondas = [];

    if (!isEmpty(dataCofiguracionInicial.cuentaContable)) {
        if (isEmpty(operacionTipo.cuentas_relacionadas)) {
            array_cuentas_relaciondas = dataCofiguracionInicial.cuentaContable;
        } else {
            var cuentas_relacionadas = operacionTipo.cuentas_relacionadas;
            cuentas_relacionadas = cuentas_relacionadas.split(',');
            if (!isEmpty(cuentas_relacionadas)) {
                $.each(dataCofiguracionInicial.cuentaContable, function (indexPadre, cuenta) {
                    $.each(cuentas_relacionadas, function (indexPadre, item) {
                        var busquedad = new RegExp('^' + item + '.*$');
                        if (!isEmpty(cuenta.codigo) && cuenta.codigo.match(busquedad)) {
                            array_cuentas_relaciondas.push(cuenta);
                        }
                    });
                });
            }
        }
    }

    $.each(array_cuentas_relaciondas, function (indexPadre, cuentaContablePadre) {
        var html = llenarCuentasContable(cuentaContablePadre, '', 'cboCuentaContable_' + indice);
        $('#cboCuentaContable_' + indice).append(html);
    });

    select2.asignarValor("cboCuentaContable_" + indice, "");
}


function onChangeCuentaContable(indice, valor) {
    let cuenta = dataCofiguracionInicial.cuentaContable.filter(item => item.id == valor);
    if (!isEmpty(valor) && cuenta[0]['codigo'].substr(0, 1) == '6' && cuenta[0]['codigo'].substr(0, 2) != '60' && cuenta[0]['codigo'].substr(0, 2) != '61' && cuenta[0]['codigo'].substr(0, 2) != '69') {
        $("#cboCentroCosto_" + indice).prop('disabled', false);
    } else {
        select2.asignarValor("cboCentroCosto_" + indice, "");
        $("#cboCentroCosto_" + indice).prop('disabled', true);
    }
}

function llenarCuentasContable(item, extra, cbo_id) {
    var cuerpo = '';
    if ($("#" + cbo_id + " option[value='" + item['id'] + "']").length != 0) {
        return cuerpo;
    }
    if (item.hijos * 1 == 0) {
        cuerpo = '<option value="' + item['id'] + '">' + extra + item['codigo'] + " | " + item['descripcion'] + '</option>';
        return cuerpo;
    }
    cuerpo = '<option value="' + item['id'] + '" disabled>' + extra + item['codigo'] + " | " + item['descripcion'] + '</option>';
    var dataHijos = dataCofiguracionInicial.cuentaContable.filter(cuentaContable => cuentaContable.plan_contable_padre_id == item.id);
    //    cuerpo = '<optgroup label="' + extra + item['codigo'] + ' | ' + item['descripcion'] + '">';
    $.each(dataHijos, function (indexHijo, cuentaContableHijo) {
        //            cuerpo += '<option value="' + cuentaContableHijo['id'] + '">' + cuentaContableHijo['codigo'] + " | " + cuentaContableHijo['descripcion'] + '</option>';
        cuerpo += llenarCuentasContable(cuentaContableHijo, extra + '&nbsp;&nbsp;&nbsp;&nbsp;', cbo_id);
    });
    //    cuerpo += ' </optgroup>';
    return cuerpo;
}

function confirmarEliminarDistribucion(index) {
    swal({
        title: "¿Está seguro que desea eliminar?",
        text: "Una vez eliminado tendrá que seleccionar nuevamente todo el registro si desea volver agregarlo",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#33b86c",
        confirmButtonText: "Si, eliminar!",
        cancelButtonColor: '#d33',
        cancelButtonText: "No, cancelar!",
        closeOnConfirm: true,
        closeOnCancel: true
    }, function (isConfirm) {
        if (isConfirm) {
            eliminarDistribucion(index);
        }
    });
}

function eliminarDistribucion(indice) {
    $('#trDetalleDistribucion_' + indice).remove();
    reenumerarFilasDetalleDistribucion();
}

function calculoPorcentajePagoDistribucion(indice) {
    var importeAcumulado = obtenerAcumuladosPorcentajes_Montos(2);
    var importePago = $('#txtMontoDistribucion_' + indice).val() * 1;
    if (importeAcumulado > obtenerSubTotal_Total_Distribucion()) {
        var nuevo_importe = redondearNumerDecimales(obtenerSubTotal_Total_Distribucion() - importeAcumulado + importePago, 6);
        var porcentaje = redondearNumerDecimales((nuevo_importe / obtenerSubTotal_Total_Distribucion()) * 100, 6);
        $('#txtMontoDistribucion_' + indice).val(redondearNumerDecimales(nuevo_importe, 2));
        $('#txtPorcentajeDistribucion_' + indice).val(redondearNumerDecimales(porcentaje, 4));

        if (!isEmpty(importes.subTotalId))
            mensajeValidacion('El monto no puede exceder al sub total.');
        else
            mensajeValidacion('El monto no puede exceder al total.');
        return;
    }

    if (importePago <= 0) {
        $('#txtMontoDistribucion_' + indice).val(devolverDosDecimales(0));
        $('#txtPorcentajeDistribucion_' + indice).val(devolverDosDecimales(0));
        mensajeValidacion('El monto debe ser positivo.');
        return;
    }

    var porcentaje = (importePago / obtenerSubTotal_Total_Distribucion()) * 100;
    $('#txtPorcentajeDistribucion_' + indice).val(devolverDosDecimales(porcentaje));
}

function redondearNumerDecimales(monto, decimales) {
    if (isEmpty(decimales)) {
        decimales = 2;
    }
    return Math.round(monto * Math.pow(10, decimales)) / Math.pow(10, decimales);
}

function actualizarMontoDistribucion(indice) {

    var porcentajeAcumulado = obtenerAcumuladosPorcentajes_Montos(1);
    var porcentaje = $('#txtPorcentajeDistribucion_' + indice).val() * 1;

    if (porcentajeAcumulado > 100) {
        var nuevo_porcentaje = redondearNumerDecimales(100 - porcentajeAcumulado, 6);
        $('#txtPorcentajeDistribucion_' + indice).val(redondearNumerDecimales(porcentaje, 4));
        $('#txtMontoDistribucion_' + indice).val(devolverDosDecimales(obtenerSubTotal_Total_Distribucion() * nuevo_porcentaje / 100));
        mensajeValidacion('Porcentaje máximo 100.');
        return;
    }

    if (porcentaje < 0) {
        $('#txtPorcentajeDistribucion_' + indice).val(0);
        $('#txtMontoDistribucion_' + indice).val(devolverDosDecimales(0));
        mensajeValidacion('Porcentaje de pago debe ser positivo.');
        return;
    }

    var monto = (obtenerSubTotal_Total_Distribucion() * porcentaje) / 100;
    $('#txtMontoDistribucion_' + indice).val(devolverDosDecimales(monto));
}

function obtenerAcumuladosPorcentajes_Montos(tipo) {
    var montoAcumulado = 0;
    for (var i = 0; i < nroFilasDistribucion; i++) {
        if (tipo == 1 && $('#txtPorcentajeDistribucion_' + i).length != 0) {
            montoAcumulado = redondearNumerDecimales($('#txtPorcentajeDistribucion_' + i).val() * 1 + montoAcumulado, 6);
        } else if (tipo == 2 && $('#txtMontoDistribucion_' + i).length != 0) {
            montoAcumulado = redondearNumerDecimales($('#txtMontoDistribucion_' + i).val() * 1 + montoAcumulado, 6);
            //            montoAcumulado += $('#txtMontoDistribucion_' + i).val() * 1;
        }
    }
    return isEmpty(montoAcumulado) ? 0 : montoAcumulado;
}

function obtenerSubTotal_Total_Distribucion() {
    var monto = 0;
    var checkIGV = false;

    if ($("#chkIncluyeIGV").length > 0 && $('#chkIncluyeIGV').is(':checked')) {
        checkIGV = true;
    }
    if (importes.subTotalId == importes.calculoId || (checkIGV && !isEmpty(importes.subTotalId))) {

        if ($('#' + importes.subTotalId).length != 0 && !isEmpty($('#' + importes.subTotalId).val())) {
            monto += $('#' + importes.subTotalId).val() * 1;
        }

        if ($('#' + importes.otrosId).length != 0 && !isEmpty($('#' + importes.otrosId).val())) {
            monto += $('#' + importes.otrosId).val() * 1;
        }

        if ($('#' + importes.exoneracionId).length != 0 && !isEmpty($('#' + importes.exoneracionId).val())) {
            monto += $('#' + importes.exoneracionId).val() * 1;
        }

        if ($('#' + importes.igvId).length != 0 && !isEmpty($('#' + importes.igvId).val()) && select2.obtenerValor("cboOperacionTipo") == "30") {
            monto += $('#' + importes.igvId).val() * 1;
        }

    } else if (importes.totalId == importes.calculoId && $('#' + importes.totalId).length != 0 && !isEmpty($('#' + importes.totalId).val())) {
        monto = $('#' + importes.totalId).val() * 1;
    }
    return monto;
}

function reenumerarFilasDetalleDistribucion() {
    var numItem = 1;
    for (var i = 0; i < nroFilasDistribucion; i++) {
        if ($('#txtNumItemDistribucion_' + i).length > 0) {
            $('#txtNumItemDistribucion_' + i).html(numItem);
            numItem++;
        }
    }
}


var dataDistribucion = [];
function obtenerDistribucion() {
    dataDistribucion = [];
    var operacionTipo = dataContOperacionTipo.filter(function (obj) {
        return obj.id == select2.obtenerValor('cboOperacionTipo');
    })[0];
    for (var i = 0; i < nroFilasDistribucion; i++) {
        if ($('#txtNumItemDistribucion_' + i).length > 0) {
            var item = {};
            item.linea = $('#txtNumItemDistribucion_' + i).html();

            if (!isEmpty(select2.obtenerValor("cboCuentaContable_" + i))) {
                item.plan_contable_id = select2.obtenerValor("cboCuentaContable_" + i);
            }

            if (!isEmpty(select2.obtenerValor("cboCentroCosto_" + i)) && (operacionTipo.requiere_centro_costo == '1' || operacionTipo.requiere_centro_costo == '2')) {
                item.centro_costo_id = select2.obtenerValor("cboCentroCosto_" + i);
            }

            if ($('#txtMontoDistribucion_' + i).val() * 1 >= 0) {
                item.monto = $('#txtMontoDistribucion_' + i).val() * 1;
            }

            if ($('#txtPorcentajeDistribucion_' + i).val() * 1 > 0) {
                item.porcentaje = $('#txtPorcentajeDistribucion_' + i).val() * 1;
            }

            if (!isEmpty(item.plan_contable_id) || !isEmpty(item.porcentaje)) {
                dataDistribucion.push(item);
            }
        }
    }
}

function validarDistribucion() {
    dataDistribucion = [];
    var operacionTipo = dataContOperacionTipo.filter(function (obj) {
        return obj.id == select2.obtenerValor('cboOperacionTipo');
    })[0];
    var porcentaje = 0;
    var monto_total = 0;
    for (var i = 0; i < nroFilasDistribucion; i++) {
        if ($('#txtNumItemDistribucion_' + i).length > 0) {
            var item = {};
            item.linea = $('#txtNumItemDistribucion_' + i).html();

            if (isEmpty(select2.obtenerValor("cboCuentaContable_" + i))) {
                mostrarValidacionLoaderClose('Debe seleccionar la cuenta contable en la fila ' + item.linea + '.');
                return false;
            } else {
                item.plan_contable_id = select2.obtenerValor("cboCuentaContable_" + i);
            }

            if (isEmpty(select2.obtenerValor("cboCentroCosto_" + i)) && operacionTipo.requiere_centro_costo == '1') {
                mostrarValidacionLoaderClose('Debe seleccionar el centro de costo en la fila ' + item.linea + '.');
                return false;
            } else if (operacionTipo.requiere_centro_costo == '1') {
                item.centro_costo_id = select2.obtenerValor("cboCentroCosto_" + i);
            }

            if ($('#txtMontoDistribucion_' + i).val() * 1 <= 0) {
                mostrarValidacionLoaderClose('El monto en la fila ' + item.linea + ' debe ser mayor que cero.');
                return false;
            } else if ($('#txtMontoDistribucion_' + i).val() * 1 > obtenerSubTotal_Total_Distribucion()) {
                mostrarValidacionLoaderClose('El monto en la fila ' + item.linea + ' sobre pasa el monto máximo ' + obtenerSubTotal_Total_Distribucion() + '.');
                return false;
            } else {
                item.monto = $('#txtMontoDistribucion_' + i).val() * 1;
                monto_total += $('#txtMontoDistribucion_' + i).val() * 1;
            }

            if ($('#txtPorcentajeDistribucion_' + i).val() * 1 <= 0) {
                mostrarValidacionLoaderClose('El porcentaje en la fila ' + item.linea + ' debe ser mayor que cero.');
                return false;
            } else if ($('#txtPorcentajeDistribucion_' + i).val() * 1 > 100) {
                mostrarValidacionLoaderClose('El porcentaje en la fila ' + item.linea + ' no debe ser mayor de lo permitido 100%.');
                return false;
            } else {
                item.porcentaje = $('#txtPorcentajeDistribucion_' + i).val() * 1;
                porcentaje += $('#txtPorcentajeDistribucion_' + i).val() * 1;
            }

            dataDistribucion.push(item);
        }
    }

    if (devolverDosDecimales(monto_total) != obtenerSubTotal_Total_Distribucion()) {
        mostrarValidacionLoaderClose('La suma de los montos debe ser ' + obtenerSubTotal_Total_Distribucion() + '.');
        return false;
    }

    if (devolverDosDecimales(porcentaje) != 100) {
        mostrarValidacionLoaderClose('La suma de porcentajes debe ser  100.00%.');
        return false;
    }

    return true;
}

function onResponseObtenerDetalle(data) {
    if (!isEmpty(data.detalleRequerimientos)) {
        request.documentoRelacion = [];

        dataCofiguracionInicial.bien = data.dataBien;
        cargarDataDocumentoACopiar(null, data.dataDocumentoRelacionada);
        nroFilasReducida = parseInt(data.detalleRequerimientos.length);
        limpiarDetalle();

        var DocumentoIds = [];

        data.detalleRequerimientos.forEach(itemRequerimientos => {
            var documentoIdsArray = itemRequerimientos.documento_id.split(","); // Separar los IDs por coma
            documentoIdsArray.forEach(id => {
                // Agregar solo si no está ya en el array
                if (!DocumentoIds.includes(id)) {
                    DocumentoIds.push(id);
                }
            });
        });
        DocumentoIds.forEach(itemDocumentoIds => {
            request.documentoRelacion.push({
                documentoId: itemDocumentoIds,
                movimientoId: null,
                tipo: 1,
                documentoPadreId: null
            });
        });

        $.each(data.detalleRequerimientos, function (index, item) {
            cargarDataTableDocumentoACopiar(
                cargarFormularioDetalleACopiar(item.organizador_id, item.bien_id, item.cantidad,
                    item.unidad_medida_id, item.valor_monetario, item.organizador_descripcion,
                    ((!isEmpty(item.bien_codigo) ? item.bien_codigo + " | " : "") + item.bien_descripcion), item.unidad_medida_descripcion, item.precio_tipo_id,
                    item.movimiento_bien_detalle, item.dataUnidadMedida, item.movimiento_bien_comentario, item.agencia_id, item.agencia_descripcion, item.agrupador_id, item.agrupador_descripcion, item.ticket, item.centro_costo_id, item.movimiento_bien_ids,
                    null, null, null, null, item.cantidad_atendida
                )
            );

        });
        obtnerStockParaProductosDeCopia();
    } else {
        dataStockReservaOk = [];
        detalle = [];
        indiceLista = [];
        banderaCopiaDocumento = 0;
        indexDetalle = 0;
        nroFilasReducida = parseInt(5);
        limpiarDetalle();
        mostrarAdvertencia("No se encontrtaron datos");
    }
}

var totalesPostores = [];
function hallarSubTotalPostorDetalle(indice, numero, bandera) {
    if (!isEmpty(dataCofiguracionInicial.bien[0]['id'])) {
        if (bandera == 1) {
            valoresFormularioDetalle = validarFormularioDetalleTablas(indice);
            valoresFormularioDetalle.index = indice;

            var indexTemporal = -1;
            $.each(detalle, function (i, item) {
                if (parseInt(item.index) === parseInt(indice)) {
                    indexTemporal = i;
                    return false;
                }
            });
            if (indexTemporal > -1) {
                detalle[indexTemporal] = valoresFormularioDetalle;
            } else {
                detalle[detalle.length] = valoresFormularioDetalle;
            }

            var precio = $('#txtPrecioP' + numero + '_' + indice).val();
            var subTotal = valoresFormularioDetalle.cantidad * precio;
            $('#txtSubtotalP' + numero + '_' + indice).val(subTotal.toFixed(4));
            if (isEmpty(precio)) {
                $('#txtPrecioP' + numero + '_' + indice).val("0");
                $('#txtSubtotalP' + numero + '_' + indice).val("0.00");
                return false;
            }
        }

        arrayProveedor.forEach(function (proveedorID, idx) {
            let valorTotal = 0;
            var tipo_cambio = 1;
            $.each(detalle, function (i, item) {
                let val = $('#txtSubtotalP' + idx + '_' + item.index).val();
                let subtotal = val === "" ? 0 : parseFloat(val);
                valorTotal += subtotal;
            });

            var igv = $('#selectIGV_' + idx).is(":checked");
            var tipoCambio = proveedorID.monedaId == 4 ? proveedorID.tipoCambio : 1;

            var subTotal = igv ? valorTotal / 1.18 : valorTotal;
            var total = igv ? valorTotal : subTotal * 1.18;
            var IGV = total - subTotal;
            var totalSoles = total * tipoCambio;
            // Actualizar el footer
            $('#tfootpostorSolesSubTotal' + proveedorID.indice).html(devolverDosDecimales(subTotal));
            $('#tfootpostorSolesIgv' + proveedorID.indice).html(devolverDosDecimales(IGV));
            $('#tfootpostorSoles' + proveedorID.indice).html(devolverDosDecimales(totalSoles));
            $('#tfootpostorDolares' + proveedorID.indice).html("$ " + devolverDosDecimales(proveedorID.monedaId == 2 ? 0 : devolverDosDecimales(total, 2)));

            totalesPostores[idx] = { indice: proveedorID.indice, total: proveedorID.monedaId == 2 ? totalSoles : total };
        });

        asignarImportePago();
    }
}

function obtenerCuentaPersona(personaId) {
    loaderShow();
    ax.setAccion("obtenerCuentaPersona");
    ax.addParamTmp("personaId", personaId);
    ax.consumir();
}

function onResponseObtenerCuentaPersona(data) {
    var dtdTipoCuentaPersona = obtenerDocumentoTipoDatoIdXTipo(47);
    if (!isEmpty(data)) {
        select2.cargar("cbo_" + dtdTipoCuentaPersona, data, "id", ["descripcion_cuenta", "numero"]);
    } else {
        select2.asignarValor('cbo' + dtdTipoCuentaPersona, "");
        mostrarValidacionLoaderClose('El proveedor seleccionado no tiene cuentas de banco registradas');
        return false;
    }
}

function reservarStockBien(indice) {
    modalReserva = 1;
    var bienId = select2.obtenerValor("cboBien_" + indice);
    if (bienId == null) {
        mostrarAdvertencia("Seleccionar un producto");
    } else {
        var cantidad_solicitada = parseInt($("#txtCantidad_" + indice).val());
        var cantidad_aceptada = parseInt($("#txtCantidadAprobada_" + indice).val());
        var compras = select2.obtenerValor('cboCompra_' + indice);

        if (cantidad_solicitada == cantidad_aceptada && compras == "1") {
            mostrarAdvertencia("La cantidad aceptada tiene que ser diferente a la solicitada para poder reservar, en la fila " + (indice + 1));
            return false;
        }
        if (isEmpty(compras)) {
            mostrarAdvertencia("Seleccionar opción de compras, en la fila " + (indice + 1));
            return false;
        }
        obtenerStockPorBien(bienId, indice);
    }
}

var dataStockReserva = null;
function onResponseObtenerStockPorBienReserva(dataStock, indice) {
    $("#btn_reserva").val(indice);
    dataStockReserva = dataStock;
    var tituloModal = '<strong>Reservar strock</strong><br><strong>' + select2.obtenerText("cboBien_" + indice) + '</strong>';
    $('.modal-title').empty();
    $('.modal-title').append(tituloModal);

    var data = [];

    if (!isEmpty(dataStock)) {
        $.each(dataStock, function (i, item) {
            if (item.stock != 0) {
                data.push(item);
            }
        });
    }
    var i = 0;

    if (!isEmptyData(data)) {
        $('#datatableReservaStock').dataTable({
            order: [[0, "desc"]],
            "ordering": false,
            "data": data,
            "columns": [
                { "data": "organizador_descripcion" },
                { "data": "unidad_medida_descripcion" },
                { "data": "stock", "sClass": "alignRight" },
                { "data": "stock", "sClass": "alignRight" }
            ],
            columnDefs: [
                {
                    "render": function (data, type, row) {
                        return parseFloat(data).formatMoney(2, '.', ',');
                    },
                    "targets": 2
                },
                {
                    "render": function (data, type, row) {
                        var html = "";
                        var cantidad_solicitada = parseInt($("#txtCantidad_" + indice).val());
                        var cantidad_aceptada = parseInt($("#txtCantidadAprobada_" + indice).val());
                        var compras = select2.obtenerValor('cboCompra_' + indice);

                        var cantidad_reserva = 0;

                        if (cantidad_aceptada == 0 && compras == 2 && row.organizador_id == 64) {
                            cantidad_reserva = cantidad_solicitada;
                        } else if (compras == 1 && row.organizador_id == 64) {
                            cantidad_reserva = cantidad_solicitada - cantidad_aceptada;
                        }
                        html = "<div class=\"input-group col-lg-6 col-md-6 col-sm-6 col-xs-6\">" +
                            "<input type=\"number\" id=\"txtCantidadReserva_" + row.organizador_id + "\" name=\"txtCantidadReserva_" + row.organizador_id + "\" class=\"form-control\" required=\"\" aria-required=\"true\" style=\"text-align: right;\" value='" + cantidad_reserva + "' /></div><input type=\"hidden\" id=\"txtorganizadorReserva_" + row.organizador_id + "\" name=\"txtorganizadorReserva_" + row.organizador_id + "\" />";
                        i++;
                        return html;
                    },
                    "targets": 3
                },
            ],
            "destroy": true
        });
    } else {
        var table = $('#datatableReservaStock').DataTable();
        table.clear().draw();
    }

    $('#modalReservaStockBien').modal('show');
}
var dataStockReservar = {};
var dataStockReservaOk = [];
function generarReserva() {
    dataStockReservar = {};
    var indice = $("#btn_reserva").val();
    var compras = select2.obtenerValor('cboCompra_' + indice);
    var bandera_modalReserva = true;

    if (isEmpty(compras)) {
        mostrarAdvertencia("Seleccionar opción de compras, en la fila " + (indice + 1));
        return false;
    } else {
        var reserva_validacion = [];
        $.each(dataStockReserva, function (i, item) {
            var reserva = parseInt($("#txtCantidadReserva_" + item.organizador_id).val());
            if (reserva > 0 && !isEmpty(reserva)) {
                reserva_validacion.push({ "reserva": reserva, "organizador_id": item.organizador_id });
            }
        });

        if (reserva_validacion.length > 1) {
            mostrarAdvertencia("Solo se puede realizar la reserva de un solo almacen");
            return false;
        }
        if (reserva_validacion.length == 0) {
            mostrarAdvertencia("No se puede guardar la reserva, porque no se ha registrado un valor a reservar");
            return false;
        }
        $.each(dataStockReserva, function (i, item) {
            if (item.organizador_id == reserva_validacion[0].organizador_id) {
                var reserva = parseInt($("#txtCantidadReserva_" + item.organizador_id).val());
                var cantidad_ = parseInt($("#txtCantidad_" + indice).val());
                var cantidadAprobada_ = parseInt($("#txtCantidadAprobada_" + indice).val());
                if (compras == 1) {
                    if ((cantidadAprobada_ + reserva) != cantidad_) {
                        bandera_modalReserva = false;
                        mostrarAdvertencia("La suma de la cantidad aceptada más la reserva no es igual a la solicitada, en la fila " + (parseInt(indice) + 1));
                        return false;
                    }
                } else {
                    if (reserva != cantidad_) {
                        bandera_modalReserva = false;
                        mostrarAdvertencia("La suma de la cantidad aceptada más la reserva no es igual a la solicitada, en la fila " + (parseInt(indice) + 1));
                        return false;
                    }
                }

                dataStockReservar.reserva = reserva;
                dataStockReservar.bien_id = item.bien_id;
                dataStockReservar.bien_descripcion = item.bien_descripcion;
                dataStockReservar.organizador_id = item.organizador_id;
                dataStockReservar.unidad_medida_id = item.unidad_medida_id;
            }
        });
        if (bandera_modalReserva) {
            dataStockReservaOk.push(dataStockReservar);
        }
    }

    if (bandera_modalReserva) {
        $('#modalReservaStockBien').modal('hide');
        $("#trDetalle_" + indice).css('background-color', 'mediumspringgreen');
    }
}

function adjuntarImagenPdfBien(index) {
    $('#indiceImagenAdjuntaBien').val(index);
    //SETEAR VALOR COMENTARIO
    var indexTemporal = -1;
    $.each(detalle, function (i, item) {
        if (parseInt(item.index) === parseInt(index)) {
            indexTemporal = i;
            return false;
        }
    });
    var bienId = select2.obtenerValor("cboBien_" + indexTemporal);
    if (indexTemporal != -1 && !isEmpty(bienId)) {
        if (!isEmpty(detalle[indexTemporal].detalle)) {
            $("#text_archivoAdjunto").html($("#nombreimagenPdfAdjunto_" + indexTemporal).val());
            $("#nombrearchivoAdjunto").val($("#nombreimagenPdfAdjunto_" + indexTemporal).val());
            $("#base64archivoAdjunto").val($("#imagenPdfAdjunto_" + indexTemporal).val());
        } else {
            $("#comentarioBien").val("");
        }
        $('#modalImagenPdfAdjuntaBien').modal('show');

    } else {
        mostrarAdvertencia('Seleccione un producto');
        return;
    }
}

function verImagenPdf() {
    var filePath = $("#base64archivoAdjunto").val();
    var nombreAdjunto = $("#nombrearchivoAdjunto").val();
    var partesnombreAdjunto = nombreAdjunto.split(".");

    var newWindow = window.open();

    if (partesnombreAdjunto[1] == "pdf") {
        newWindow.document.write('<html><body>');
        newWindow.document.write('<embed width="100%" height="100%" src="' + filePath + '" type="application/pdf">');
        newWindow.document.write('</body></html>');
        newWindow.document.close();
    } else {
        newWindow.document.write('<html><body>');
        newWindow.document.write('<img src="' + filePath + '">');
        newWindow.document.write('</body></html>');
        newWindow.document.close();
    }
}

function registrarImagenPdfBien() {
    var indice = $('#indiceImagenAdjuntaBien').val();
    var indexTemporal = -1;
    $.each(detalle, function (i, item) {
        if (parseInt(item.index) === parseInt(indice)) {
            indexTemporal = i;
            return false;
        }
    });

    if (indexTemporal != -1) {
        $('#modalImagenPdfAdjuntaBien').modal('hide');
        $("#imagenPdfAdjunto_" + indexTemporal).val($("#base64archivoAdjunto").val());
        $("#nombreimagenPdfAdjunto_" + indexTemporal).val($("#fileInputAdjunto").val().slice(12));

        $("#fileInputAdjunto").val();
    } else {
        mostrarAdvertencia('Seleccione un producto');
        return;
    }

}

function base64ToSize(base64) {
    // Eliminar el encabezado de la cadena base64, si está presente
    const base64Data = base64.split(',')[1] || base64;
    // El tamaño en bytes de la cadena base64
    const byteSize = (base64Data.length * 3) / 4 - (base64Data.endsWith('==') ? 2 : base64Data.endsWith('=') ? 1 : 0);
    // Convertir el tamaño a MB
    const sizeInMB = byteSize / (1024 * 1024); // 1 MB = 1024 * 1024 bytes
    return sizeInMB;
}

function verDetalleRequerimiento(indice) {
    var indexTemporal = -1;
    $.each(detalle, function (i, item) {
        if (parseInt(item.index) === parseInt(indice)) {
            indexTemporal = i;
            return false;
        }
    });

    if (indexTemporal != -1) {
        loaderShow();
        ax.setAccion("obtenerDetalleBienRequerimiento");
        ax.addParamTmp("movimientoBienId", $("#txtmovimiento_bien_ids_" + indexTemporal).val());
        ax.addParamTmp("documentoTipoOrigenId", documentoTipoOrigenIdGLobal);
        ax.consumir();
        $('#modalDetalleRequerimiento').modal('show');
    } else {
        mostrarAdvertencia('Seleccione un producto');
        return;
    }
}

function onResponseobtenerDetalleBienRequerimiento(data) {
    if (!isEmptyData(data)) {
        $('#datatableDetalleReserva').dataTable({
            order: [[0, "desc"]],
            "ordering": false,
            "data": data,
            "columns": [
                { "data": "bien_codigo_descripcion" },
                { "data": "cantidad_requerimiento" },
                { "data": "comentario" },
                { "data": "archivo_adjunto", "sClass": "alignRight" },
            ],
            columnDefs: [
                {
                    "render": function (data, type, row) {
                        if (!isEmpty(data)) {
                            var archivo = data.split(".");
                            if (archivo[1] == "pdf" || archivo[1] == "PDF") {
                                return "<a href='" + URL_BASE + "util/uploads/documentoAdjunto/" + data + "' target='_blank' style='color:blue;'>" + data + "</a>";
                            } else {
                                return "<a href='" + URL_BASE + "util/uploads/imagenAdjunto/" + data + "' target='_blank' style='color:blue;'>" + data + "</a>";
                            }
                        } else {
                            return "";
                        }
                    },
                    "targets": 3
                },
                {
                    "render": function (data, type, row) {
                        return devolverDosDecimales(data);
                    },
                    "targets": 1
                },
            ],
            "destroy": true
        });
    } else {
        var table = $('#datatableDetalleReserva').DataTable();
        table.clear().draw();
    }
}

function habilitarModalCuotasPagos() {
    $('#modalProgramacionPagos').modal('show');
    actualizarPorcentajePago();
}

function limpiarBuscadores_movimiento_form_tablas() {
    $('#txtSerie').val('');
    $('#txtNumero').val('');

    select2.asignarValor('cboDocumentoTipoM', -1);
    select2.asignarValor('cboPersonaM', -1);
}

var widthProveedor = "500";
var widthMoneda = "100";
var widthUO = "150";
var widtIGV = "50";
var widtTiempo = "150";
var arrayProveedor = [];
var theadOriginal = "";
function cargarProveedorDetalleCombo(data, indice) {
    $("#cboProveedor_" + indice).select2({
        width: widthProveedor + "px",
        allowClear: true,
        placeholder: "Buscar proveedor",
    }).on("change", function (e) {
        // Agregar nuevo proveedor al array
        var textoOriginal = (select2.obtenerText("cboProveedor_" + indice)).split("|");
        var textProveedor = textoOriginal[1].length > 30
            ? textoOriginal[1].substring(0, 30) + '…'
            : textoOriginal[1];

        var proveedorExistenteIndex = arrayProveedor.findIndex(item => item.indice == indice);
        var proveedorExistente = arrayProveedor.findIndex(item => item.proveedor_id === e.val);

        if (proveedorExistenteIndex == -1 && proveedorExistente == -1) {
            $("#i_sumilla_" + indice).attr('style', 'color: black;');
            $("#a_sumilla_" + indice).attr('onclick', 'mostrarModalSumilla('+ indice +');');
            $("#i_pdfCotizacion_" + indice).attr('style', 'color: blue;');
            $("#a_pdfCotizacion_" + indice).attr('onclick', '$("#fileInput_' + indice + '").click();');
            // $("#i_distribucionPagosCotizacion_" + indice).attr('style', 'color: green;');           
            // $("#a_distribucionPagosCotizacion_" + indice).attr('onclick', 'mostrarModalDistribucionPago('+ indice +');');

            $("#cboMonedaP_" + indice).prop('disabled', false);
            $("#txtTipoCambio_" + indice).prop('disabled', false);
            $("#selectIGV_" + indice).prop('disabled', false);
            $("#cboUO_" + indice).prop('disabled', false);
            $("#cboTiempoEntrega_" + indice).prop('disabled', false);
            $("#cboCondicionPago_" + indice).prop('disabled', false);
            $("#txtReferencia_" + indice).prop('disabled', false);    

            $("#btn_EliminarProveedor_" + indice).show();
            var sumilla = (isEmpty(arrayProveedor[indice])) ? null : arrayProveedor[indice].sumilla;
            arrayProveedor.push({
                "indice": indice,
                "proveedor_id": e.val,
                "proveedorText": select2.obtenerText("cboProveedor_" + indice),
                "monedaId": select2.obtenerValor("cboMonedaP_" + indice),
                "uoId": select2.obtenerValor("cboUO_" + indice),
                "tipoCambio": $("#txtTipoCambio_" + indice).val(),
                "igv": ($('#selectIGV_').is(":checked") == true ? 1 : 0),
                "tiempoEntrega": $("#cboTiempoEntrega_" + indice).val(),
                "tiempo": $("#txtTiempoEntrega_" + indice).val(),
                "condicionPago": select2.obtenerValor("cboCondicionPago_" + indice),
                "referencia": $("#txtReferencia_" + indice).val(),
                "diasPago": $("#txtDiasPago_" + indice).val(),
                "sumilla": sumilla
            });

            $('#datatable').DataTable().destroy();
            // Asegurar que los encabezados tengan rowspan="2"
            if ($('#datatable thead #tr_proveedor').length === 0) {
                $('#datatable thead tr:first th').attr('rowspan', '2');
                var dtdTipoGrupo_producto = obtenerDocumentoTipoDatoIdXTipo(44);
            }

            theadOriginal = $('#datatable thead').html(); // Guardar copia original
            $('#datatable thead').html(theadOriginal);
            // Y cuando quieras resetear:
            // Agregar nueva columna antes de la última en la fila principal
            $('#datatable thead tr:first').each(function () {
                var $ths = $(this).find('th');
                $(`<th colspan="2" style="font-size: 10px; text-align: center;width: 300px;" class='th_proveedor_${indice}' id='th_proveedor_${indice}'>
                    ${textoOriginal[0]} <br> ${textProveedor}
                </th>`).insertBefore($ths.last());
            });

            // Verificar si la fila con id='tr_proveedor' ya existe
            if ($('#datatable thead #tr_proveedor').length === 0) {
                // Si no existe, agregarla solo la primera vez
                var filaExtra = `
                    <tr id='tr_proveedor'>
                        <th style='text-align:center;width: 150px;' class='th_precio_${indice}'>Precio</th>
                        <th style='text-align:center;width: 150px;' class='th_subTotal_${indice}'>Sub. Total</th>
                    </tr>`;
                $('#datatable thead').append(filaExtra);
            } else {
                $('#datatable thead #tr_proveedor').append(`
                    <th style='text-align:center;width: 100px;' class='th_precio_${indice}'>Precio</th>
                    <th style='text-align:center;width: 100px;' class='th_subTotal_${indice}'>Sub. Total</th>
                `);
            }
            // Agregar celdas en cada fila del tbody antes de la última celda
            $('#datatable tbody tr').each(function (index) {
                var $tds = $(this).find('td');
                $(`<td class='td_precio_${index}_${indice}'>${agregarPrecioUnitarioPDetalleTabla(index, indice)}</td>`).insertBefore($tds.last());
                $(`<td class='td_subTotal_${index}_${indice}'>${agregarSubTotalPDetalleTabla(index, indice)}</td>`).insertBefore($tds.last());
            });

            $("#datatable").css({ "overflow-x": "auto", "display": "block" });
            //footer
            if ($('#datatable tfoot #tfoot_proveedor').length === 0) {
                // Si no existe, agregarla solo la primera vez
                var filaExtratfoot = `
                    <tfoot>
                        <tr id='tfoot_proveedorSubTotal'>
                            <th colspan='4' style='text-align:right'>Sub Total:</th>
                            <th style='text-align:right' colspan='2' class='tfootpostorSubtTotal${indice}_class' id='tfootpostorSolesSubTotal${indice}'>0.00</th>
                        </tr>
                        <tr id='tfoot_proveedorIgv'>
                            <th colspan='4' style='text-align:right'>IGV (18%):</th>
                            <th style='text-align:right' colspan='2' class='tfootpostorIgv${indice}_class' id='tfootpostorSolesIgv${indice}'>0.00</th>
                        </tr>
                        <tr id='tfoot_proveedorDolares'>
                            <th colspan='4' style='text-align:right'>Totales Dolares:</th>
                            <th style='text-align:right' colspan='2' class='tfootpostorDolares${indice}_class' id='tfootpostorDolares${indice}'>0.00</th>
                        </tr>                        
                        <tr id='tfoot_proveedor'>
                            <th colspan='4' style='text-align:right'>Totales Soles:</th>
                            <th style='text-align:right' colspan='2' class='tfootpostor${indice}_class' id='tfootpostorSoles${indice}'>0.00</th>
                        </tr>
                    </tfoot>`;
                $('#datatable').append(filaExtratfoot);
                if (!isEmpty(dataDocumentoCopia)) {
                    detalle = [];
                    request.documentoRelacion = [];
                    onResponseObtenerDocumentoRelacion(dataDocumentoCopia);
                }
                if (!isEmpty(dataGrupoProducto)) {
                    detalle = [];
                    request.documentoRelacion = [];
                    onResponseObtenerDetalle(dataGrupoProducto);
                }
            } else {
                $('#datatable tfoot #tfoot_proveedorSubTotal').append(`
                    <th style='text-align:right' colspan='2' class='tfootpostorSubtTotal${indice}_class' id='tfootpostorSolesSubTotal${indice}'>0.00</th>
                `);
                $('#datatable tfoot #tfoot_proveedorIgv').append(`
                    <th style='text-align:right' colspan='2' class='tfootpostorIgv${indice}_class' id='tfootpostorSolesIgv${indice}'>0.00</th>
                `);
                $('#datatable tfoot #tfoot_proveedorDolares').append(`
                    <th style='text-align:right' colspan='2' class='tfootpostorDolares${indice}_class' id='tfootpostorDolares${indice}'>0.00</th>
                `);
                $('#datatable tfoot #tfoot_proveedor').append(`
                    <th style='text-align:right' colspan='2' class='tfootpostor${indice}_class' id='tfootpostorSoles${indice}'>0.00</th>
                `);
            }
        } else {
            var proveedorExistente = arrayProveedor.findIndex(item => item.proveedor_id === e.val);
            if (proveedorExistente != indice && proveedorExistente != -1) {
                mostrarAdvertencia("Proveedor ya ingresado");
                if (!isEmpty(arrayProveedor[indice])) {
                    select2.asignarValor("cboProveedor_" + indice, arrayProveedor[indice].proveedor_id);
                } else {
                    select2.asignarValor("cboProveedor_" + indice, 0);
                }
                return;
            } else {
                var sumilla = (isEmpty(arrayProveedor[indice].sumilla)) ? null : arrayProveedor[indice].sumilla;
                arrayProveedor[proveedorExistenteIndex] = {
                    "indice": indice,
                    "proveedor_id": e.val,
                    "proveedorText": select2.obtenerText("cboProveedor_" + indice),
                    "monedaId": select2.obtenerValor("cboMonedaP_" + indice),
                    "uoId": select2.obtenerValor("cboUO_" + indice),
                    "tipoCambio": $("#txtTipoCambio_" + indice).val(),
                    "tiempoEntrega": $("#cboTiempoEntrega_" + indice).val(),
                    "tiempo": $("#txtTiempoEntrega_" + indice).val(),
                    "condicionPago": select2.obtenerValor("cboCondicionPago_" + indice),
                    "diasPago": $("#txtDiasPago_" + indice).val(),
                    "referencia": $("#txtReferencia_" + indice).val(),
                    "sumilla": sumilla
                };
                $('#th_proveedor_' + indice).html(textoOriginal[0] + " <br>" + textProveedor);
            }
        }
    });

    if (!isEmpty(data)) {
        select2.cargar("cboProveedor_" + indice, data, "id", ["codigo_identificacion", "persona_nombre"]);
        select2.asignarValor("cboProveedor_" + indice, "");
    }
    $("#cboProveedor_" + indice).select2({ width: widthProveedor + "px" });
}

function cargarMonedaDetalleCombo(data, indice) {
    $("#cboMonedaP_" + indice).select2({
        width: widthMoneda + "px",
        allowClear: true
    }).on("change", function (e) {
        if (!isEmpty(arrayProveedor[indice])) {
            calcularFooterTipoCambio(indice);
        }
    });

    if (!isEmpty(data)) {
        select2.cargar("cboMonedaP_" + indice, data, "id", "descripcion");
        select2.asignarValor("cboMonedaP_" + indice, 2);
    }
    $("#cboMonedaP_" + indice).select2({ width: widthMoneda + "px" });
}

function cargarUODetalleCombo(data, indice) {
    $("#cboUO_" + indice).select2({
        width: widthUO + "px"
    }).on("change", function (e) {
        if (!isEmpty(arrayProveedor[indice])) {
            calcularFooterTipoCambio(indice);
        }
    });

    if (!isEmpty(data)) {
        select2.cargar("cboUO_" + indice, data, "id", "descripcion");
    }
    $("#cboUO_" + indice).select2({ width: widthUO + "px" });
}

function cargarTiempoEntrega(indice) {
    $("#cboTiempoEntrega_" + indice).select2({
        width: widtTiempo + "px"
    }).on("change", function (e) {
        if (e.val == 2) {
            $("#txtTiempoEntrega_" + indice).prop('disabled', false);
        } else {
            $("#txtTiempoEntrega_" + indice).prop('disabled', true);
        }
        if (!isEmpty(arrayProveedor[indice])) {
            calcularFooterTipoCambio(indice);
        }
    });
}

function cargarCondicionpago(indice) {
    $("#cboCondicionPago_" + indice).select2({
        width: widtTiempo + "px"
    }).on("change", function (e) {
        if (e.val == 2) {
            $("#txtDiasPago_" + indice).prop('disabled', false);
        } else {
            $("#txtDiasPago_" + indice).prop('disabled', true);
            $("#txtDiasPago_" + indice).val("");
        }
        if (!isEmpty(arrayProveedor[indice])) {
            calcularFooterTipoCambio(indice);
        }
    });
}

function agregarRazonSocialProveedor(i) {
    var $html = "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\">" +
        "<select name=\"cboProveedor_" + i + "\" id=\"cboProveedor_" + i + "\" class=\"select2\" onchange=\"\"></select>" +
        "</div>";
    return $html;
}

function agregarMoneda(i) {
    var $html = "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\">" +
        "<select name=\"cboMonedaP_" + i + "\" id=\"cboMonedaP_" + i + "\" class=\"select2\" onchange=\"\" disabled>" +
        "</select></div>";
    return $html;
}

function agregarTipoCambio(i) {
    var tipo_cambio = dataCofiguracionInicial.dataTipoCambio[0].equivalencia_venta;
    var $html = "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\" style=\"display: flex; align-items: center; gap: 8px;\">" +
        "<input type=\"number\" id=\"txtTipoCambio_" + i + "\" name=\"txtTipoCambio_" + i + "\" class=\"form-control\" required=\"\" aria-required=\"true\" value=\"" + devolverDosDecimales(tipo_cambio) + "\" style=\"text-align: right; width:100px;\" onchange=\"calcularFooterTipoCambio(" + i + ")\" onkeyup=\"calcularFooterTipoCambio(" + i + ")\" disabled/></div>";
    return $html;
}

function agregarChkIGV(i) {
    var $html = "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\" style=\"text-align:center;\">" +
        "<input type='checkbox' id=\"selectIGV_" + i + "\" onchange=\"calcularFooterTipoCambio(" + i + ")\" disabled>" +
        "</div>";
    return $html;
}

function agregarUO(i) {
    var $html = "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\">" +
        "<select name=\"cboUO_" + i + "\" id=\"cboUO_" + i + "\" class=\"select2\" disabled>" +
        "</select></div>";
    return $html;
}

function agregarTiempoEntrega(i) {
    var $html = "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\">" +
        "<select name=\"cboTiempoEntrega_" + i + "\" id=\"cboTiempoEntrega_" + i + "\" class=\"select2\" disabled>" +
        "<option value='1'>Inmediato</option>" +
        "<option value='2'>Días</option>" +
        "<option value='3'>Contraentrega</option>" +
        "</select></div>";
    return $html;
}

function agregarTiempo(i) {
    var $html = "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\" style=\"text-align:center;\">" +
        "<input type=\"number\" id=\"txtTiempoEntrega_" + i + "\" name=\"txtTiempoEntrega_" + i + "\" class=\"form-control\" required=\"\" aria-required=\"true\" value=\"\" style=\"text-align: right; width:" + widtTiempo + "px;\" disabled/></div>";
    return $html;
}

function agregarSumilla(i) {
    var $html = "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\" style=\"text-align:center;\">" +
      "<a href='#' id='a_sumilla_"+ i +"'><i id='i_sumilla_" + i + "' class='fa fa-comment' style='color:gray; opacity:0.5; cursor:not-allowed;' title='Subir pdf cotización'></i></a></div>";
    return $html;
}

function agregarCondicionPago(i) {
    var $html = "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\">" +
        "<select name=\"cboCondicionPago_" + i + "\" id=\"cboCondicionPago_" + i + "\" class=\"select2\" onchange=\"calcularFooterTipoCambio(" + i + ")\" disabled>" +
        "<option value='1'>Contado</option>" +
        "<option value='2'>Crédito</option>" +
        "</select></div>";
    return $html;
}

function agregarDiasPago(i) {
    var $html = "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\" style=\"text-align:center;\">" +
        "<input type=\"number\" id=\"txtDiasPago_" + i + "\" name=\"txtDiasPago_" + i + "\" class=\"form-control\" required=\"\" aria-required=\"true\" value=\"\" style=\"text-align: right; width:" + widtTiempo + "px;\" disabled/></div>";
    return $html;
}

function agregarReferencia(i) {
    var $html = "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\" style=\"text-align:center;\">" +
      "<input type=\"text\" id=\"txtReferencia_" + i + "\" name=\"txtReferencia_" + i + "\" class=\"form-control\" required=\"\" aria-required=\"true\" value=\"\" style=\"text-align: right; width:" + widtTiempo + "px;\" disabled/></div>";
    return $html;
}

function agregarPdfCotizacion(i) {
    var $html = "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\" style=\"text-align:center;\">";

    var btn_upload = "&nbsp;<a href='#' id='a_pdfCotizacion_" + i + "' ><i id='i_pdfCotizacion_" + i + "' class='fa fa-cloud-upload' style='color:gray; opacity:0.5; cursor:not-allowed;' title='Adjuntar pdf'></i></a>";
    btn_upload += "<input type='file' id='fileInput_" + i + "' style='display:none;'>";
    btn_upload += "&nbsp;<i id='text_archivo_" + i + "' style='font-size:10px'></i>";

    $html += btn_upload + "</div>";
    return $html;
}

function agregarDistribucionPagosCotizacion(i) {
    var $html = "<div class=\"input-group col-lg-12 col-md-12 col-sm-12 col-xs-12\" style=\"text-align:center;\">" +
        "<a href='#' id='a_distribucionPagosCotizacion_" + i + "'><i id='i_distribucionPagosCotizacion_" + i + "' class='fa fa-calendar' style='color:gray; opacity:0.5; cursor:not-allowed;' title='Distribución pagos' disabled></i></a></div>";
    return $html;
}

function llenarFilaDetalleTablaProveedor(indice) {
    document.getElementById("th_Nro").style.width = "50px";
    var boton_eliminarProveedor = "";
    if (indice != 0) {
        boton_eliminarProveedor = "&nbsp;&nbsp;<a id='btn_EliminarProveedor_" + indice + "' onclick='confirmarEliminarProveedor(" + indice + ");' hidden><i class='fa fa-trash-o' style='color:#cb2a2a;' title='Eliminar'></i></a>";
    }
    var fila = "<tr id=\"trDetalleProveedor_" + indice + "\">";
    fila = fila + "<td style='border:0; width: 200px; vertical-align: middle; padding-right: 10px;' align='right'><div class'input-group col-lg-12 col-md-12 col-sm-12 col-xs-12' style='text-align: center;'>" + (indice + 1) + boton_eliminarProveedor + "</div></td>";
    //Razón social
    fila += "<td style='border:0; width: " + widthProveedor + "px; vertical-align: middle; padding: " + KPADINGTD + "px;' id=\"tdProveedor_" + indice + "\">" + agregarRazonSocialProveedor(indice) + "</td>";
    //Moneda
    fila += "<td style='border:0; width: " + widthMoneda + "px; vertical-align: middle; padding: " + KPADINGTD + "px;' id=\"tdMoneda_" + indice + "\">" + agregarMoneda(indice) + "</td>";
    //Tipo cambio
    fila += "<td style='border:0; width: " + widthMoneda + "px; vertical-align: middle; padding: " + KPADINGTD + "px;' id=\"tdMoneda_" + indice + "\">" + agregarTipoCambio(indice) + "</td>";
    //IGV
    fila += "<td style='border:0; width: " + widtIGV + "px; vertical-align: middle; padding: " + KPADINGTD + "px;' id=\"tdIgv_" + indice + "\">" + agregarChkIGV(indice) + "</td>";
    //U.O
    fila += "<td style='border:0; width: " + widthUO + "px; vertical-align: middle; padding: " + KPADINGTD + "px;' id=\"tdTiempoEntrega_" + indice + "\">" + agregarUO(indice) + "</td>";
    //Tiempo entrega
    fila += "<td style='border:0; width: " + widtTiempo + "px; vertical-align: middle; padding: " + KPADINGTD + "px;' id=\"tdTiempoEntrega_" + indice + "\">" + agregarTiempoEntrega(indice) + "</td>";
    //Tiempo
    fila += "<td style='border:0; width: " + widtTiempo + "px; vertical-align: middle; padding: " + KPADINGTD + "px;' id=\"tdTiempoEntrega_" + indice + "\">" + agregarTiempo(indice) + "</td>";
    //Condicion pago
    fila += "<td style='border:0; width: " + widtTiempo + "px; vertical-align: middle; padding: " + KPADINGTD + "px;' id=\"tdCondicionPago_" + indice + "\">" + agregarCondicionPago(indice) + "</td>";
    //Días de pago
    fila += "<td style='border:0; width: " + widtTiempo + "px; vertical-align: middle; padding: " + KPADINGTD + "px;' id=\"tdDiasPago_" + indice + "\">" + agregarDiasPago(indice) + "</td>";
    //Referencia
    fila += "<td style='border:0; width: " + widtTiempo + "px; vertical-align: middle; padding: " + KPADINGTD + "px;' id=\"tdDetraccion_" + indice + "\">" + agregarReferencia(indice) + "</td>";    
    //Sumilla
    fila += "<td style='border:0; width: " + widtIGV + "px; vertical-align: middle; padding: " + KPADINGTD + "px;' id=\"tdSumilla_" + indice + "\">" + agregarSumilla(indice) + "</td>";
    //pdf cotización
    fila += "<td style='border:0; width: " + widtIGV + "px; vertical-align: middle; padding: " + KPADINGTD + "px;' id=\"tdTiempoEntrega_" + indice + "\">" + agregarPdfCotizacion(indice) + "</td>";
    //distribución pagos
    // fila += "<td style='border:0; width: " + widtIGV + "px; vertical-align: middle; padding: " + KPADINGTD + "px;' id=\"tdTiempoEntrega_" + indice + "\">" + agregarDistribucionPagosCotizacion(indice) + "</td>";

    fila = fila + "</tr>";
    return fila;
}

function llenarTablaDetalleProveedor(data) {
    var cuerpo = "";
    var nroFilas = 5;
    //LLENAR TABLA DETALLE
    for (var i = 0; i < nroFilas; i++) {
        cuerpo += llenarFilaDetalleTablaProveedor(i);
    }

    $('#datatableProveedor tbody').append(cuerpo);
    //LLENAR COMBOS
    for (var i = 0; i < nroFilas; i++) {
        cargarProveedorDetalleCombo(data.postores, i);
        cargarMonedaDetalleCombo(data.moneda, i);
        cargarUODetalleCombo(data.uo, i);
        cargarCondicionpago(i);
        cargarTiempoEntrega(i);
    }
}

function calcularFooterTipoCambio(indice) {
    if (!isEmpty(arrayProveedor[indice])) {
        var sumilla = (isEmpty(arrayProveedor[indice].sumilla)) ? null : arrayProveedor[indice].sumilla;
        arrayProveedor[indice] = {
            "indice": indice,
            "proveedor_id": select2.obtenerValor("cboProveedor_" + indice),
            "proveedorText": select2.obtenerText("cboProveedor_" + indice),
            "monedaId": select2.obtenerValor("cboMonedaP_" + indice),
            "uoId": select2.obtenerValor("cboUO_" + indice),
            "tipoCambio": $("#txtTipoCambio_" + indice).val(),
            "igv": ($('#selectIGV_'+ indice).is(":checked") == true ? 1 : 0),
            "tiempoEntrega": $("#cboTiempoEntrega_" + indice).val(),
            "tiempo": $("#txtTiempoEntrega_" + indice).val(),
            "condicionPago": select2.obtenerValor("cboCondicionPago_" + indice),
            "diasPago": $("#txtDiasPago_" + indice).val(),
            "referencia": $("#txtReferencia_" + indice).val(),
            "sumilla": sumilla
        };
        $('#datatable tbody tr').each(function (index) {
            var valorBien = select2.obtenerValor("cboBien_" + index);
            if(!isEmpty(valorBien)){
                hallarSubTotalPostorDetalle(index, indice, 1);
            }
        });
    }
}

function mostrarModalSumilla(indice) {
    $('#indiceSumilla').val(indice);

    $('#divSumilla').html('<textarea  id="proveedorSumilla" class="wysihtml5 form-control" rows="9" maxlength="500" onkeyup="actualizarContador()"></textarea><div id="contador">Caracteres: 0 / 500</div>');
    $('.wysihtml5').wysihtml5({
        link: false,
        image: false
    });

    var proveedorId = select2.obtenerValor("cboProveedor_" + indice);
    if (!isEmpty(proveedorId)) {
        if (!isEmpty(arrayProveedor[indice].sumilla)) {
            $("#proveedorSumilla").val(reducirTexto(arrayProveedor[indice].sumilla));
        } else {
            $("#proveedorSumilla").val("");
        }
        $('#modalSumilla').modal('show');
    } else {
        mostrarAdvertencia('Seleccione un proveedor');
        return;
    }
}

function registrarSumilla() {
    var indice = $('#indiceSumilla').val();

    var cantidadCaracteres = ($("#proveedorSumilla").val()).length;
    if(cantidadCaracteres >= 500){
        $("#contador").html("Caracteres: "+ cantidadCaracteres +" / 500");
        mostrarAdvertencia('Sumilla tiene que tener maximo 500 caracteres');
        return;
    }
    if (indice != -1) {
        arrayProveedor[indice].sumilla = $("#proveedorSumilla").val();
        $('#modalSumilla').modal('hide');
    } else {
        mostrarAdvertencia('Seleccione un producto');
        return;
    }
}

function exportarPdfCotizacion() {
    if (!isEmpty(detalle)) {
        var dtdTipoGrupo_producto = obtenerDocumentoTipoDatoIdXTipo(44);
        var dtdTipoTipoRequerimiento = obtenerDocumentoTipoDatoIdXTipo(42);
        loaderShow();
        ax.setAccion("exportarPdfCotizacion");
        ax.addParamTmp("grupoProductoId", select2.obtenerValor("cbo_" + dtdTipoGrupo_producto));
        ax.addParamTmp("tipoRequerimiento", select2.obtenerValor("cboTipoRequerimiento_" + dtdTipoTipoRequerimiento));
        ax.addParamTmp("urgencia", "No");
        ax.addParamTmp("documentoId", documentoRelacionId);
        ax.consumir();
    } else {
        mostrarAdvertencia("No hay registros para exportar");
        loaderClose();
        return;
    }
}

var banderaSolicitud = false;
function abrirDocumentoPDF2(data, contenedor) {
    const link = document.createElement('a');
    link.href = data.url;
    link.target = '_blank';
    link.click();
    banderaSolicitud = true;

    setTimeout(function () {
        eliminarPDF2(contenedor + data.pdf);
    }, 4000);
}

function mostrarModalDistribucionPago(indice) {
    var proveedorId = select2.obtenerValor("cboProveedor_" + indice);
    if (!isEmpty(proveedorId)) {
        if (isEmpty(totalesPostores[indice])) {
            mostrarAdvertencia("No se ha ingresados precios en el detalle");
            return;
        }
        $('#modalProgramacionPagos').modal('show');
        $("#indexProveedor").val(indice);

        if (!isEmpty(listaPagoProgramacionPostores[indice])) {
            limpiarCamposPagoProgramacion();
            listaPagoProgramacion = listaPagoProgramacionPostores[indice];
            onListarPagoProgramacion(listaPagoProgramacionPostores[indice]);
        } else {
            $("#txtImportePago").val(devolverDosDecimales(totalesPostores[indice].total));
            $("#txtPorcentaje").val(100);
            onListarPagoProgramacion(null);
        }
    } else {
        mostrarAdvertencia('Seleccione un proveedor');
        return;
    }
}

function exportarExcelCotizacion() {
    if (!isEmpty(detalle)) {
        var dtdTipoGrupo_producto = obtenerDocumentoTipoDatoIdXTipo(44);
        var dtdTipoTipoRequerimiento = obtenerDocumentoTipoDatoIdXTipo(42);
        var grupoProducto = select2.obtenerValor("cbo_" + dtdTipoGrupo_producto);
        if (!isEmpty(dataGrupoProducto) && (isEmpty(grupoProducto) || grupoProducto == 0)) {
            mostrarAdvertencia("Seleccionar grupo de productos");
            loaderClose();
            return;
        }

        loaderShow();
        ax.setAccion("exportarExcelCotizacion");
        ax.addParamTmp("grupoProductoId", select2.obtenerValor("cbo_" + dtdTipoGrupo_producto));
        ax.addParamTmp("tipoRequerimiento", select2.obtenerValor("cboTipoRequerimiento_" + dtdTipoTipoRequerimiento));
        ax.addParamTmp("urgencia", "No");
        ax.addParamTmp("documentoId", documentoRelacionId);
        ax.consumir();
    } else {
        mostrarAdvertencia("No hay registros para exportar");
        loaderClose();
        return;
    }
}

function confirmarEliminarProveedor(indice) {
    swal({
        title: "¿Desea continuar?",
        text: "Se eliminaran los registros del proveedor seleccionado.",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#33b86c",
        confirmButtonText: "Si!",
        cancelButtonColor: '#d33',
        cancelButtonText: "No!",
        closeOnConfirm: true,
        closeOnCancel: true
    }, function (isConfirm) {
        if (isConfirm) {
            eliminarEncabezador(indice);
        }
    });
    return;
}

function eliminarEncabezador(indice) {
    $("#i_sumilla_" + indice).attr('style', 'ccolor:gray; opacity:0.5; cursor:not-allowed;');
    $("#a_sumilla_" + indice).attr('onclick', '');
    $("#i_pdfCotizacion_" + indice).attr('style', 'color:gray; opacity:0.5; cursor:not-allowed;');
    $("#a_pdfCotizacion_" + indice).attr('onclick', '');
    // $("#i_distribucionPagosCotizacion_" + indice).attr('style', 'color:gray; opacity:0.5; cursor:not-allowed;');           
    // $("#a_distribucionPagosCotizacion_" + indice).attr('onclick', '');

    $("#cboMonedaP_" + indice).prop('disabled', true);
    $("#txtTipoCambio_" + indice).prop('disabled', true);
    $("#selectIGV_" + indice).prop('disabled', true);
    $("#cboUO_" + indice).prop('disabled', true);
    $("#cboTiempoEntrega_" + indice).prop('disabled', true);
    $("#cboCondicionPago_" + indice).prop('disabled', true);
    $("#txtReferencia_" + indice).prop('disabled', true);    

    // Eliminar encabezado principal
    $("#btn_EliminarProveedor_" + indice).hide();
    select2.asignarValor("cboProveedor_" + indice, 0);

    $(`#th_proveedor_${indice}`).remove();
    // Eliminar encabezados de precio y subtotal en la fila adicional
    $(`#tr_proveedor .th_precio_${indice}`).remove();
    $(`#tr_proveedor .th_subTotal_${indice}`).remove();

    // Eliminar las celdas del tbody asociadas a este índice
    $('#datatable tbody tr').each(function (index) {
        $(this).find(`.td_precio_${index}_${indice}`).remove();
        $(this).find(`.td_subTotal_${index}_${indice}`).remove();
    });

    $('#tfootpostorSolesSubTotal' + indice).remove();
    $('#tfootpostorSolesIgv' + indice).remove();
    $('#tfootpostorDolares' + indice).remove();
    $('#tfootpostorSoles' + indice).remove();

    arrayProveedor.splice(indice, 1);
    lstDocumentoArchivos.splice(indice, 1);
    listaPagoProgramacionPostores.splice(indice, 1);
    totalesPostores.splice(indice, 1);
    $("#text_archivo_" + indice).html("");

    if (indice == 0) {
        $('#headDetalleCabecera #th_proveedor_' + indice).remove();
        $('#headDetalleCabecera #tr_proveedor').remove();
        $('#datatable thead tr:first th').attr('rowspan', '1');
    }
    theadOriginal = $('#datatable thead').html(); // Guardar copia original

    return false;
}

function eliminarPDF2(url) {
    ax.setAccion("eliminarPDF2");
    ax.addParamTmp("url", url);
    ax.consumir();
}

function hallarSubTotalPostorDetalleCantidad(indice) {
    if (!isEmpty(dataCofiguracionInicial.bien[0]['id']) && doc_TipoId == GENERAR_COTIZACION) {
        valoresFormularioDetalle = validarFormularioDetalleTablas(indice);
        valoresFormularioDetalle.index = indice;

        var indexTemporal = -1;
        $.each(detalle, function (i, item) {
            if (parseInt(item.index) === parseInt(indice)) {
                indexTemporal = i;
                return false;
            }
        });
        if (indexTemporal > -1) {
            detalle[indexTemporal] = valoresFormularioDetalle;
        } else {
            detalle[detalle.length] = valoresFormularioDetalle;
        }
        var a = parseFloat(valoresFormularioDetalle.cantidad);
        if (!Number.isInteger(a) && valoresFormularioDetalle.unidadMedidaDesc == "UN") {
            $("#txtCantidad_"+indice).val(valoresFormularioDetalle.cantidadPorAtender);
            mostrarAdvertencia("La cantidad tiene que ser entero, para:" + valoresFormularioDetalle.bienDesc);
            return false;
        }
        if(parseFloat(valoresFormularioDetalle.cantidad) > parseFloat(valoresFormularioDetalle.cantidadPorAtender)){
            $("#txtCantidad_"+indice).val(valoresFormularioDetalle.cantidadPorAtender);
            arrayProveedor.forEach(function (proveedorID, idx) {
                var precio = $('#txtPrecioP' + idx + '_' + indice).val();
                var subTotal = valoresFormularioDetalle.cantidadPorAtender * precio;
                $('#txtSubtotalP' + idx + '_' + indice).val(subTotal.toFixed(4));
                if (isEmpty(precio)) {
                    $('#txtPrecioP' + idx + '_' + indice).val("0");
                    $('#txtSubtotalP' + idx + '_' + indice).val("0.00");
                    return false;
                }

                let valorTotal = 0;
                var tipo_cambio = 1;
                $.each(detalle, function (i, item) {
                    let val = $('#txtSubtotalP' + idx + '_' + item.index).val();
                    let subtotal = val === "" ? 0 : parseFloat(val);
                    valorTotal += subtotal;
                });

                var igv = $('#selectIGV_' + idx).is(":checked");
                var tipoCambio = proveedorID.monedaId == 4 ? proveedorID.tipoCambio : 1;

                var subTotal = igv ? valorTotal / 1.18 : valorTotal;
                var total = igv ? valorTotal : subTotal * 1.18;
                var IGV = total - subTotal;
                var totalSoles = total * tipoCambio;
                // Actualizar el footer
                $('#tfootpostorSolesSubTotal' + proveedorID.indice).html(devolverDosDecimales(subTotal));
                $('#tfootpostorSolesIgv' + proveedorID.indice).html(devolverDosDecimales(IGV));
                $('#tfootpostorSoles' + proveedorID.indice).html(devolverDosDecimales(totalSoles));
                $('#tfootpostorDolares' + proveedorID.indice).html("$ " + devolverDosDecimales(proveedorID.monedaId == 2 ? 0 : devolverDosDecimales(total, 2)));

                totalesPostores[idx] = { indice: proveedorID.indice, total: proveedorID.monedaId == 2 ? totalSoles : total };
            });
            mostrarAdvertencia("La cantidad no tiene que ser mayor a la pendiente de atender, para:" + valoresFormularioDetalle.bienDesc);
            return false;
        }
        arrayProveedor.forEach(function (proveedorID, idx) {
            var precio = $('#txtPrecioP' + idx + '_' + indice).val();
            var subTotal = valoresFormularioDetalle.cantidad * precio;
            $('#txtSubtotalP' + idx + '_' + indice).val(subTotal.toFixed(4));
            if (isEmpty(precio)) {
                $('#txtPrecioP' + idx + '_' + indice).val("0");
                $('#txtSubtotalP' + idx + '_' + indice).val("0.00");
                return false;
            }

            let valorTotal = 0;
            var tipo_cambio = 1;
            $.each(detalle, function (i, item) {
                let val = $('#txtSubtotalP' + idx + '_' + item.index).val();
                let subtotal = val === "" ? 0 : parseFloat(val);
                valorTotal += subtotal;
            });

            var igv = $('#selectIGV_' + idx).is(":checked");
            var tipoCambio = proveedorID.monedaId == 4 ? proveedorID.tipoCambio : 1;

            var subTotal = igv ? valorTotal / 1.18 : valorTotal;
            var total = igv ? valorTotal : subTotal * 1.18;
            var IGV = total - subTotal;
            var totalSoles = total * tipoCambio;
            // Actualizar el footer
            $('#tfootpostorSolesSubTotal' + proveedorID.indice).html(devolverDosDecimales(subTotal));
            $('#tfootpostorSolesIgv' + proveedorID.indice).html(devolverDosDecimales(IGV));
            $('#tfootpostorSoles' + proveedorID.indice).html(devolverDosDecimales(totalSoles));
            $('#tfootpostorDolares' + proveedorID.indice).html("$ " + devolverDosDecimales(proveedorID.monedaId == 2 ? 0 : devolverDosDecimales(total, 2)));

            totalesPostores[idx] = { indice: proveedorID.indice, total: proveedorID.monedaId == 2 ? totalSoles : total };
        });

        asignarImportePago();
    }
}

function hallarTotales(){
    $('#datatable tbody tr').each(function (index) {
      var indexTemporal = -1;
      $.each(detalle, function (i, item) {
        if (parseInt(item.index) === parseInt(index)) {
          indexTemporal = i;
          return false;
        }
      });
        var valorBien = select2.obtenerValor("cboBien_" + indexTemporal);
        if(!isEmpty(valorBien)){
            hallarSubTotalPostorDetalleCantidad(indexTemporal);
        }
    });
}