var estadoTolltipMP = 0;
var banderaBuscarMP = 0;
var total;
var total_cantidad;
$(document).ready(function () {
    $('[data-toggle="popover"]').popover({ html: true }).popover();
    select2.iniciar();

    ax.setSuccess("onResponseReporteSumasAnual");
    obtenerDataInicial();
});

function onResponseReporteSumasAnual(response) {
    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obtenerDataInicialReporteSumasAnual':
                onResponseObtenerDataInicial(response.data);
                break;
            case 'obtenerReporteSumasAnualXCriterios':
                onResponseReporteSumaMensual(response.data);
                break;
            case 'obtenerReporteComprobacionExcel':
                loaderClose();
                location.href = URL_BASE + response.data;
                break;

        }
    } else {
        switch (response[PARAM_ACCION_NAME]) {
            default:
                loaderClose();
                break;

        }
    }
}

function obtenerDataInicial() {
    loaderShow();
    ax.setAccion("obtenerDataInicialReporteSumasAnual");
    ax.addParamTmp("empresaId", commonVars.empresa);
    ax.consumir();
}

function exportarExcel() {
    loaderShow();
    ax.setAccion("obtenerReporteComprobacionExcel");
    ax.addParamTmp("empresaId", commonVars.empresa);
    ax.addParamTmp("valorBusquedad", select2.obtenerValor("cboPeriodo"));
    ax.addParamTmp("tipo", 2);
    ax.consumir();
}

function buscarPorCriterios(colapsa) {
    $('[data-toggle="popover"]').popover('show');
    banderaBuscarMP = 1;

    if (colapsa === 1)
        colapsarBuscador();

    loaderShow();
    ax.setAccion("obtenerReporteSumasAnualXCriterios");
    ax.addParamTmp("empresaId", commonVars.empresa);
    ax.addParamTmp("anio", select2.obtenerValor("cboPeriodo"));
    ax.consumir();
}


var dataCuentasContablesTitulo = [];
function onResponseObtenerDataInicial(data) {
    dataCuentasContablesTitulo = data.dataCuentaContableTitulo;
    select2.cargar('cboPeriodo', data.dataAnio, 'anio', 'anio');
    select2.asignarValor('cboPeriodo', data.dataAnioActual);
    onResponseReporteSumaMensual(data.dataReporte);
}

function onResponseReporteSumaMensual(data) {
    $("#dataList").empty();

    var trVacio = '';
    trVacio += '<tr>';
    trVacio += '<td></td>';
    trVacio += '<td></td>';
    trVacio += '<td></td>';
    trVacio += '<td></td>';
    trVacio += '<td></td>';
    trVacio += '<td></td>';
    trVacio += '<td></td>';
    trVacio += '<td></td>';
    trVacio += '</tr>';

    var cuerpo_total = '';

    cuerpo_total += '<table id="datatable" class="table table-striped table-bordered">';
    cuerpo_total += '<thead>';
    cuerpo_total += '<tr>';
    cuerpo_total += '<th style="text-align:center;" colspan="2">Cuenta y subcuenta contable</th>';
    cuerpo_total += '<th style="text-align:center;" colspan="2">Saldos iniciales</th>';
    cuerpo_total += '<th style="text-align:center;" colspan="2">Movimiento</th>';
    cuerpo_total += '<th style="text-align:center;" colspan="2">Saldos Finales</th>';
    cuerpo_total += '<th style="text-align:center;" colspan="2">S. F. ESTADO DE SITUACION FINANCIERA</th>';
    cuerpo_total += '<th style="text-align:center;" colspan="2">S. F.(FUNCION) ESTADO DE RESULTADOS INTEGRALES</th>';
    cuerpo_total += '<th style="text-align:center;" colspan="2">S. F.(NATUR.) ESTADO DE RESULTADOS INTEGRALES</th>';
    cuerpo_total += '</tr>';
    cuerpo_total += '<tr>';
    cuerpo_total += '<th style="text-align:center;">Cuenta</th>';
    cuerpo_total += '<th style="text-align:center;">Nombre</th>';
    cuerpo_total += '<th style="text-align:center;">Deudo</th>';
    cuerpo_total += '<th style="text-align:center;">Acreedor</th>';
    cuerpo_total += '<th style="text-align:center;">Deudo</th>';
    cuerpo_total += '<th style="text-align:center;">Acreedor</th>';
    cuerpo_total += '<th style="text-align:center;">Deudo</th>';
    cuerpo_total += '<th style="text-align:center;">Acreedor</th>';
    cuerpo_total += '<th style="text-align:center;">Activo</th>';
    cuerpo_total += '<th style="text-align:center;">Pasivo Patrimonio</th>';
    cuerpo_total += '<th style="text-align:center;">Perdidas</th>';
    cuerpo_total += '<th style="text-align:center;">Ganancias</th>';
    cuerpo_total += '<th style="text-align:center;">Perdidas</th>';
    cuerpo_total += '<th style="text-align:center;">Ganancias</th>';
    cuerpo_total += '</tr>';
    cuerpo_total += '</thead>';
    cuerpo_total += '<tbody>';
    let saldos_iniciales_deudo = 0.00;
    let saldos_iniciales_acreedor = 0.00;
    let movimiento_deudo = 0.00;
    let movimiento_acreedor = 0.00;
    let saldos_finales_deudo = 0.00;
    let saldos_finales_acreedor = 0.00;
    let suma_SF_ESTADO_activo = 0.00;
    let suma_SF_ESTADO_pasivo_patrimonio = 0.00;
    let suma_SF_FUNCION_debe = 0.00;
    let suma_SF_FUNCION_haber = 0.00;
    let suma_SFNATUR_debe = 0.00;
    let suma_SFNATUR_haber = 0.00;

    if (!isEmpty(dataCuentasContablesTitulo) && !isEmpty(data)) {
        $.each(dataCuentasContablesTitulo, function (index, item) {
            let dataCuenta = data.filter(elemento => elemento['plan_contable_codigo'].substring(0, 2) === item.codigo);
            if (isEmpty(dataCuenta)) {
                return;
            }
            $.each(dataCuenta, function (indexCuenta, cuenta) {
                let diferencia = redondearNumero(redondearNumero(cuenta.debe_inicial) + redondearNumero(cuenta.debe) - redondearNumero(cuenta.haber_inicial) - redondearNumero(cuenta.haber));
                let debe_saldo = (diferencia > 0 ? redondearNumero(diferencia) : 0);
                let haber_saldo = (diferencia < 0 ? redondearNumero(Math.abs(diferencia)) : 0);
                cuerpo_total += '<tr>';
                cuerpo_total += '<td align="left">' + cuenta.plan_contable_codigo + '</td>';
                cuerpo_total += '<td align="left">' + cuenta.plan_contable_descripcion + '</td>';
                cuerpo_total += '<td align="right">' + formatearNumeroPorCantidadDecimales(cuenta.debe_inicial, 2) + '</td>';
                cuerpo_total += '<td align="right">' + formatearNumeroPorCantidadDecimales(cuenta.haber_inicial, 2) + '</td>';
                cuerpo_total += '<td align="right">' + formatearNumeroPorCantidadDecimales(cuenta.debe, 2) + '</td>';
                cuerpo_total += '<td align="right">' + formatearNumeroPorCantidadDecimales(cuenta.haber, 2) + '</td>';
                cuerpo_total += '<td align="right">' + formatearNumeroPorCantidadDecimales(debe_saldo, 2) + '</td>';
                cuerpo_total += '<td align="right">' + formatearNumeroPorCantidadDecimales(haber_saldo, 2) + '</td>';
                // debugger;
                saldos_iniciales_deudo = saldos_iniciales_deudo + parseFloat(cuenta.debe_inicial);
                saldos_iniciales_acreedor = saldos_iniciales_acreedor + parseFloat(cuenta.haber_inicial);
                movimiento_deudo = movimiento_deudo + parseFloat(cuenta.debe);
                movimiento_acreedor = movimiento_acreedor + parseFloat(cuenta.haber);
                saldos_finales_deudo = saldos_finales_deudo + debe_saldo;
                saldos_finales_acreedor = saldos_finales_acreedor + haber_saldo;
                let codigoTitulo = parseInt(item.codigo);
                switch (true) {
                    case 10 <= codigoTitulo && codigoTitulo <= 59:
                        cuerpo_total += '<td align="right">' + formatearNumeroPorCantidadDecimales(debe_saldo, 2) + '</td>';
                        cuerpo_total += '<td align="right">' + formatearNumeroPorCantidadDecimales(haber_saldo, 2) + '</td>';
                        cuerpo_total += '<td align="right">0.00</td>';
                        cuerpo_total += '<td align="right">0.00</td>';
                        cuerpo_total += '<td align="right">0.00</td>';
                        cuerpo_total += '<td align="right">0.00</td>';
                        suma_SF_ESTADO_activo = suma_SF_ESTADO_activo + debe_saldo;
                        suma_SF_ESTADO_pasivo_patrimonio = suma_SF_ESTADO_pasivo_patrimonio + haber_saldo;
                        break;

                    case (60 <= codigoTitulo && codigoTitulo <= 65) || (90 <= codigoTitulo && codigoTitulo <= 97) || codigoTitulo == 68:
                        cuerpo_total += '<td align="right">0.00</td>';
                        cuerpo_total += '<td align="right">0.00</td>';
                        cuerpo_total += '<td align="right">0.00</td>';
                        cuerpo_total += '<td align="right">0.00</td>';
                        cuerpo_total += '<td align="right">' + formatearNumeroPorCantidadDecimales(debe_saldo, 2) + '</td>';
                        cuerpo_total += '<td align="right">' + formatearNumeroPorCantidadDecimales(haber_saldo, 2) + '</td>';
                        suma_SFNATUR_debe = suma_SFNATUR_debe + debe_saldo;
                        suma_SFNATUR_haber = suma_SFNATUR_haber + haber_saldo;
                        break;
                    case (69 <= codigoTitulo && codigoTitulo <= 79) || codigoTitulo == 67:
                        cuerpo_total += '<td align="right">0.00</td>';
                        cuerpo_total += '<td align="right">0.00</td>';
                        cuerpo_total += '<td align="right">' + formatearNumeroPorCantidadDecimales(debe_saldo, 2) + '</td>';
                        cuerpo_total += '<td align="right">' + formatearNumeroPorCantidadDecimales(haber_saldo, 2) + '</td>';
                        cuerpo_total += '<td align="right">' + formatearNumeroPorCantidadDecimales(debe_saldo, 2) + '</td>';
                        cuerpo_total += '<td align="right">' + formatearNumeroPorCantidadDecimales(haber_saldo, 2) + '</td>';
                        suma_SF_FUNCION_debe = suma_SF_FUNCION_debe + debe_saldo;
                        suma_SF_FUNCION_haber = suma_SF_FUNCION_haber  + haber_saldo;
                        suma_SFNATUR_debe = suma_SFNATUR_debe + debe_saldo;
                        suma_SFNATUR_haber = suma_SFNATUR_haber + haber_saldo;
                        break;
                    default:
                        cuerpo_total += '<td></td>';
                        cuerpo_total += '<td></td>';
                        cuerpo_total += '<td></td>';
                        cuerpo_total += '<td></td>';
                        cuerpo_total += '<td></td>';
                        cuerpo_total += '<td></td>';
                        break;
                }



                cuerpo_total += '</tr>';
            });



        });
    }

    cuerpo_total += '</tbody>';
    cuerpo_total += '<tfoot>';
    cuerpo_total += '<tr>';
    cuerpo_total += '<th colspan="2" style="text-align:right">TOTALES:</th>';
    cuerpo_total += '<th style="text-align:right">'+formatearNumeroPorCantidadDecimales(saldos_iniciales_deudo)+'</th>';
    cuerpo_total += '<th style="text-align:right">'+formatearNumeroPorCantidadDecimales(saldos_iniciales_acreedor)+'</th>';
    cuerpo_total += '<th style="text-align:right">'+formatearNumeroPorCantidadDecimales(movimiento_deudo)+'</th>';
    cuerpo_total += '<th style="text-align:right">'+formatearNumeroPorCantidadDecimales(movimiento_acreedor)+'</th>';
    cuerpo_total += '<th style="text-align:right">'+formatearNumeroPorCantidadDecimales(saldos_finales_deudo)+'</th>';
    cuerpo_total += '<th style="text-align:right">'+formatearNumeroPorCantidadDecimales(saldos_finales_acreedor)+'</th>';
    cuerpo_total += '<th style="text-align:right">'+formatearNumeroPorCantidadDecimales(suma_SF_ESTADO_activo)+'</th>';
    cuerpo_total += '<th style="text-align:right">'+formatearNumeroPorCantidadDecimales(suma_SF_ESTADO_pasivo_patrimonio)+'</th>';
    cuerpo_total += '<th style="text-align:right">'+formatearNumeroPorCantidadDecimales(suma_SF_FUNCION_debe)+'</th>';
    cuerpo_total += '<th style="text-align:right">'+formatearNumeroPorCantidadDecimales(suma_SF_FUNCION_haber)+'</th>';
    cuerpo_total += '<th style="text-align:right">'+formatearNumeroPorCantidadDecimales(suma_SFNATUR_debe)+'</th>';
    cuerpo_total += '<th style="text-align:right">'+formatearNumeroPorCantidadDecimales(suma_SFNATUR_haber)+'</th>';
    cuerpo_total += '</tr>';

    let suma_ejercicio_periodoSF_ESTADO1 = suma_SF_ESTADO_pasivo_patrimonio > suma_SF_ESTADO_activo ? (suma_SF_ESTADO_pasivo_patrimonio - suma_SF_ESTADO_activo): 0.00;
    let suma_ejercicio_periodoSF_ESTADO2 = suma_SF_ESTADO_activo > suma_SF_ESTADO_pasivo_patrimonio ? (suma_SF_ESTADO_activo - suma_SF_ESTADO_pasivo_patrimonio): 0.00;
    let suma_ejercicio_periodoSF_FUNCION1 = suma_SF_FUNCION_haber > suma_SF_FUNCION_debe ? (suma_SF_FUNCION_haber - suma_SF_FUNCION_debe): 0.00;
    let suma_ejercicio_periodoSF_FUNCION2 = suma_SF_FUNCION_debe > suma_SF_FUNCION_haber? (suma_SF_FUNCION_debe - suma_SF_FUNCION_haber): 0.00;
    let suma_ejercicio_periodoSFNATUR1 = suma_SFNATUR_haber > suma_SFNATUR_debe ? (suma_SFNATUR_haber - suma_SFNATUR_debe): 0.00;
    let suma_ejercicio_periodoSFNATUR2 = suma_SFNATUR_debe > suma_SFNATUR_haber ? (suma_SFNATUR_debe - suma_SFNATUR_haber): 0.00;

    cuerpo_total += '<tr>';
    cuerpo_total += '<th colspan="8" style="text-align:right">RESULTADO DEL EJERCICIO O PERIODO:</th>';
    cuerpo_total += '<th style="text-align:right">'+ formatearNumeroPorCantidadDecimales(suma_ejercicio_periodoSF_ESTADO1)+'</th>';
    cuerpo_total += '<th style="text-align:right">'+ formatearNumeroPorCantidadDecimales(suma_ejercicio_periodoSF_ESTADO2)+'</th>';
    cuerpo_total += '<th style="text-align:right">'+ formatearNumeroPorCantidadDecimales(suma_ejercicio_periodoSF_FUNCION1)+'</th>';
    cuerpo_total += '<th style="text-align:right">'+ formatearNumeroPorCantidadDecimales(suma_ejercicio_periodoSF_FUNCION2)+'</th>';
    cuerpo_total += '<th style="text-align:right">'+ formatearNumeroPorCantidadDecimales(suma_ejercicio_periodoSFNATUR1)+'</th>';
    cuerpo_total += '<th style="text-align:right">'+ formatearNumeroPorCantidadDecimales(suma_ejercicio_periodoSFNATUR2)+'</th>';
    cuerpo_total += '</tr>';

    cuerpo_total += '<tr>';
    cuerpo_total += '<th colspan="8" style="text-align:right">SUMAS TOTALES:</th>';
    cuerpo_total += '<th style="text-align:right">'+ formatearNumeroPorCantidadDecimales(suma_ejercicio_periodoSF_ESTADO1 + suma_SF_ESTADO_activo)+'</th>';
    cuerpo_total += '<th style="text-align:right">'+ formatearNumeroPorCantidadDecimales(suma_ejercicio_periodoSF_ESTADO2 + suma_SF_ESTADO_pasivo_patrimonio)+'</th>';
    cuerpo_total += '<th style="text-align:right">'+ formatearNumeroPorCantidadDecimales(suma_ejercicio_periodoSF_FUNCION1 + suma_SF_FUNCION_debe)+'</th>';
    cuerpo_total += '<th style="text-align:right">'+ formatearNumeroPorCantidadDecimales(suma_ejercicio_periodoSF_FUNCION2 + suma_SF_FUNCION_haber)+'</th>';
    cuerpo_total += '<th style="text-align:right">'+ formatearNumeroPorCantidadDecimales(suma_ejercicio_periodoSFNATUR1 + suma_SFNATUR_debe)+'</th>';
    cuerpo_total += '<th style="text-align:right">'+ formatearNumeroPorCantidadDecimales(suma_ejercicio_periodoSFNATUR2 + suma_SFNATUR_haber)+'</th>';
    cuerpo_total += '</tr>';
    cuerpo_total += '</tfoot></table>';
    $("#dataList").append(cuerpo_total);

    $('#datatable').dataTable({
        "lengthMenu": [[-1], ["Todo"]],
        "order": [],
        "destroy": true,
        scrollX: true,
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

    loaderClose();
}

var actualizandoBusqueda = false;

function colapsarBuscador() {
    if (actualizandoBusqueda) {
        actualizandoBusqueda = false;
        return;
    }
    if ($('#bg-info').hasClass('in')) {
        $('#bg-info').attr('aria-expanded', "false");
        $('#bg-info').attr('height', "0px");
        $('#bg-info').removeClass('in');
    } else {
        $('#bg-info').attr('aria-expanded', "false");
        $('#bg-info').removeAttr('height', "0px");
        $('#bg-info').addClass('in');
    }
}