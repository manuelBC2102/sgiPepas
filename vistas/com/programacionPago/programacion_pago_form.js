
$(document).ready(function () {
    ax.setSuccess("exitoProgramacionPago");
    var documentoId = document.getElementById("documentoId").value;
    obtenerConfiguracionesIniciales(documentoId);
    cargarSelect2();
    datePiker.iniciarPorClase('fecha');
    habilitarContenedorTiempoProgramado();
    habilitarContenedorImporteProgramado();
});

function cargarSelect2() {
    $(".select2").select2({
        width: '100%'
    });
}

function cargarPantallaListar() {
    var url = URL_BASE + "vistas/com/programacionPago/programacion_pago_listar.php";
    cargarDiv("#window", url);
}

function exitoProgramacionPago(response) {
    if (response.status === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obtenerConfiguracionesIniciales':
                onResponseObtenerConfiguracionesIniciales(response.data);
                loaderClose();
                break;
            case 'guardarProgramacionPago':
                onResponseGuardarProgramacionPago(response.data);
                loaderClose();
                break;
            case 'obtenerDocumento':
                onResponseObtenerDocumento(response.data);
                loaderClose();
                break;
        }
    }
}

function obtenerConfiguracionesIniciales(documentoId) {
    loaderShow();
    ax.setAccion("obtenerConfiguracionesIniciales");
    ax.addParam("documentoId", documentoId);
    ax.consumir();
}

var documentoTotal;
var personaId;
function onResponseObtenerConfiguracionesIniciales(data) {
//    console.log(data);
    var documentoId = document.getElementById('documentoId').value;
    var dataDocumento = data.dataDocumento[0];
    var htmlVerDoc = "<a href='#' onclick='obtenerDocumento(" + documentoId + ")' style='color: #0000ff;' title='Visualizar documento'>[" + dataDocumento.serie_numero + "]</a>";
    $('#pDocDescripcion').html(dataDocumento.documento_tipo_descripcion + "  " + htmlVerDoc + " | " + dataDocumento.moneda_simbolo + ' ' + formatearNumero(dataDocumento.total));

    var dataProveedor = [{persona_id: dataDocumento.persona_id, nombre: dataDocumento.persona_nombre, codigo_identificacion: dataDocumento.codigo_identificacion}];
    select2.cargarAsignaUnico('cboProveedor', dataProveedor, 'persona_id', ['nombre', 'codigo_identificacion']);

    personaId = dataDocumento.persona_id;
//    if(!isEmpty(dataDocumento.importe_programado) && dataDocumento.importe_programado!=0){        
//        $('#pTotalDesc').html('<h3 style="margin-top: 0px;margin-bottom: 0px;" >' + dataDocumento.moneda_simbolo + ' ' + formatearNumero(dataDocumento.importe_programado) + '</h3>');
//        documentoTotal = dataDocumento.importe_programado * 1;
//    }else 
    if ((dataDocumento.identificador_negocio == 17 || dataDocumento.identificador_negocio == 18) && !isEmpty(dataDocumento.movimiento_id)) {
        var totalTemp = dataDocumento.total_aprobado_restante * 1+dataDocumento.importe_programado * 1;
        if (totalTemp > dataDocumento.total * 1) {
            totalTemp = dataDocumento.total * 1;
        }

        $('#pTotalDesc').html('<h3 style="margin-top: 0px;margin-bottom: 0px;" >' + dataDocumento.moneda_simbolo + ' ' + formatearNumero(totalTemp) + '</h3>');
        documentoTotal = totalTemp;
    } else {
        $('#pTotalDesc').html('<h3 style="margin-top: 0px;margin-bottom: 0px;" >' + dataDocumento.moneda_simbolo + ' ' + formatearNumero(dataDocumento.total) + '</h3>');
        documentoTotal = dataDocumento.total * 1;
    }

    $('#pFechaEmision').html(formatearFechaBDCadena(dataDocumento.fecha_emision));

    var fechaT;
    if (!isEmpty(data.dataPPDetalle)) {
        if (!isEmpty(data.dataPPDetalle[0].ppago_id)) {
            fechaT = data.dataPPDetalle[0].fecha_calculo;
        }
    }

    if (isEmpty(fechaT)) {
        fechaT = dataDocumento.fecha_bl;
    }

    $('#pFechaTentativa').html(formatearFechaBDCadena(fechaT));

    //modal detalle
    select2.cargar('cboIndicador', data.dataIndicador, 'id', 'descripcion');
    select2.asignarValor('cboIndicador', null);

//    if (!isEmpty(data.dataProgramacionPago)) {
//        llenarFormularioEditar(data);
//    }

    //llenar el detalle
    if (!isEmpty(data.dataPPDetalle)) {
        var totalTempDet = 0;
        $.each(data.dataPPDetalle, function (i, itemDetalle) {
            var objDetalle = {};

            if (isEmpty(data.dataPPDetalle[0].ppago_id) && totalTempDet <= documentoTotal) {
                totalTempDet += itemDetalle.importe * 1;
                objDetalle.indicadorId = itemDetalle.indicador_id;
                objDetalle.dias = itemDetalle.dias;
                objDetalle.fechaProgramada = (!isEmpty(itemDetalle.fecha_programada) ? formatearFechaBDCadena(itemDetalle.fecha_programada) : '');
                objDetalle.indicadorDesc = itemDetalle.indicador_desc;
                objDetalle.estadoId = (!isEmpty(itemDetalle.ppago_detalle_estado_logico) ? itemDetalle.ppago_detalle_estado_logico : itemDetalle.estado_pp);
                objDetalle.estadoDesc = itemDetalle.estado_pp_desc;
                objDetalle.programacionPagoDetalleId = itemDetalle.id;

                if (totalTempDet <= documentoTotal) {
                    objDetalle.importe = redondearNumero(itemDetalle.importe).toFixed(2);
                } else {
                    var totalLista=0;
                    $.each(listaProgramacionPagoDetalle, function (i, item) {
                        totalLista+=item.importe;
                    });
                    objDetalle.importe = documentoTotal-totalLista;
                }

                listaProgramacionPagoDetalle.push(objDetalle);
            } else {
                objDetalle.indicadorId = itemDetalle.indicador_id;
                objDetalle.dias = itemDetalle.dias;
                objDetalle.fechaProgramada = (!isEmpty(itemDetalle.fecha_programada) ? formatearFechaBDCadena(itemDetalle.fecha_programada) : '');
                objDetalle.importe = redondearNumero(itemDetalle.importe).toFixed(2);
                objDetalle.indicadorDesc = itemDetalle.indicador_desc;
                objDetalle.estadoId = (!isEmpty(itemDetalle.ppago_detalle_estado_logico) ? itemDetalle.ppago_detalle_estado_logico : itemDetalle.estado_pp);
                objDetalle.estadoDesc = itemDetalle.estado_pp_desc;
                objDetalle.programacionPagoDetalleId = itemDetalle.id;

                listaProgramacionPagoDetalle.push(objDetalle);
            }
        });

        onListarProgramacionPagoDetalle(listaProgramacionPagoDetalle);
    }
}

//function llenarFormularioEditar(data) {
//    var dataProgramacionPago = data.dataProgramacionPago[0];
//
//    $('#txtDescripcion').val(dataProgramacionPago.descripcion);
//    $('#txtComentario').val(dataProgramacionPago.comentario);
//    select2.asignarValor('cboProveedor', dataProgramacionPago.persona_id);
//    select2.asignarValor('cboGrupoProducto', dataProgramacionPago.bien_tipo_ids.split(";"));
//
//}

function abrirModalNuevoDetalle() {
    $('#modalProgramacionPagoDetalle').modal('show');
}

//AGREGAR PROGRAMACION PAGO DETALLE

var listaProgramacionPagoDetalle = [];

function agregarProgramacionPagoDetalle() {
    var indicadorId = select2.obtenerValor('cboIndicador');
    var dias = $('#txtDias').val();
    var fechaProgramada = $('#txtFechaProgamada').val();
    var importe = $('#txtImporte').val();
    var detalleIndice = $('#detalleIndice').val(); //el indice de edicion        
    var objDetalle = {};//Objeto para el detalle  

    if (validarFormularioProgramacionPagoDetalle(indicadorId, dias, fechaProgramada, importe)) {
        if (validarProgramacionPagoDetalleRepetido(indicadorId, dias, fechaProgramada)) {
            var indicadorDesc = select2.obtenerText('cboIndicador');
            objDetalle.indicadorId = indicadorId;
            objDetalle.dias = dias;
            objDetalle.fechaProgramada = fechaProgramada;
            objDetalle.importe = importe;
            objDetalle.indicadorDesc = indicadorDesc;

            if (detalleIndice != '') {
                objDetalle.estadoId = (!isEmpty(listaProgramacionPagoDetalle[detalleIndice].estadoId) ? listaProgramacionPagoDetalle[detalleIndice].estadoId : 1);
                objDetalle.estadoDesc = (!isEmpty(listaProgramacionPagoDetalle[detalleIndice].estadoDesc) ? listaProgramacionPagoDetalle[detalleIndice].estadoDesc : 'Por liberar');

            } else {
                objDetalle.estadoId = 1;
                objDetalle.estadoDesc = 'Por liberar';
            }

            if (detalleIndice != '') {// validamos si es edicion                                
                objDetalle.programacionPagoDetalleId = listaProgramacionPagoDetalle[detalleIndice].programacionPagoDetalleId;
                listaProgramacionPagoDetalle[detalleIndice] = objDetalle;
            } else {
                objDetalle.programacionPagoDetalleId = null;
                listaProgramacionPagoDetalle.push(objDetalle);
            }

//          console.log(listaProgramacionPagoDetalle);
//          console.log(listaProgramacionPagoDetalleEliminado);
            onListarProgramacionPagoDetalle(listaProgramacionPagoDetalle);
            limpiarCamposProgramacionPagoDetalle();
            limpiarMensajesProgramacionPagoDetalle();

            $('#modalProgramacionPagoDetalle').modal('hide');
        }
    }
}

function validarFormularioProgramacionPagoDetalle(indicadorId, dias, fechaProgramada, importe) {
    var bandera = true;
    limpiarMensajesProgramacionPagoDetalle();

    if (isEmpty(indicadorId)) {
        $("#msjIndicador").removeProp(".hidden");
        $("#msjIndicador").text("Indicador es obligatorio").show();
        bandera = false;
    }

    if (importe <= 0 || isEmpty(importe)) {
        $("#msjImporte").removeProp(".hidden");
        $("#msjImporte").text("Importe tiene que ser positivo").show();
        bandera = false;
    }

    return bandera;
}

function limpiarMensajesProgramacionPagoDetalle() {
    $("#msjIndicador").hide();
    $("#msjImporte").hide();
}

function limpiarCamposProgramacionPagoDetalle() {
    select2.asignarValor('cboIndicador', null);
    $('#txtDias').val('');
    $('#txtFechaProgamada').datepicker('setDate', null);
    $('#txtImporte').val('');
    $('#txtPorcentaje').val('');
    $('#detalleIndice').val('');
}

function buscarProgramacionPagoDetalleXIndicador(indicadorId) {
    var ind = -1;

    if (!isEmpty(listaProgramacionPagoDetalle)) {
        $.each(listaProgramacionPagoDetalle, function (i, item) {
            if (item.indicadorId == indicadorId) {
                ind = i;
            }
        });
    }

    return ind;
}

function buscarProgramacionPagoDetalle(indicadorId, dias, fechaProgramada) {
    var ind = -1;

    if (!isEmpty(listaProgramacionPagoDetalle)) {
        $.each(listaProgramacionPagoDetalle, function (i, item) {
            if (item.indicadorId == indicadorId && item.dias == dias && item.fechaProgramada == fechaProgramada) {
                ind = i;
            }
        });
    }

    return ind;
}

function validarProgramacionPagoDetalleRepetido(indicadorId, dias, fechaProgramada) {
    var valido = true;
    //PUEDE REPETIRSE: Después de B/L y Fecha de emisión
    if (indicadorId != 63 && indicadorId != 62) {
        var detalleIndice = $('#detalleIndice').val();

        if (detalleIndice != '') {
            //alert('igual');
            var indice = buscarProgramacionPagoDetalleXIndicador(indicadorId);
            if (indice != detalleIndice && indice != -1) {
                mostrarAdvertencia("El indicador ya ha sido agregado");
                valido = false;
            } else {
                valido = true;
            }
        } else {
            //alert('diferente');
            var indice = buscarProgramacionPagoDetalleXIndicador(indicadorId);
            if (indice > -1) {
                mostrarAdvertencia("El indicador ya ha sido agregado");
                valido = false;
            }
        }
    } else {
        var detalleIndice = $('#detalleIndice').val();

        if (detalleIndice != '') {
            //alert('igual');
            var indice = buscarProgramacionPagoDetalle(indicadorId, dias, fechaProgramada);
            if (indice != detalleIndice && indice != -1) {
                mostrarAdvertencia("El detalle ya ha sido agregado");
                valido = false;
            } else {
                valido = true;
            }
        } else {
            //alert('diferente');
            var indice = buscarProgramacionPagoDetalle(indicadorId, dias, fechaProgramada);
            if (indice > -1) {
                mostrarAdvertencia("El detalle ya ha sido agregado");
                valido = false;
            }
        }
    }

    return valido;
}

function onListarProgramacionPagoDetalle(data) {
    $('#dataTableProgramacionPagoDetalle tbody tr').remove();
    var cuerpo = "";
    var ind = 0;
    //Indicador	Días	Fecha programada	Importe	Estado	Acciones
    //background: #c2ffae

    var ppagoDetalleId = document.getElementById("ppagoDetalleId").value;
    var colorFila = '';

    if (!isEmpty(data)) {
        data.forEach(function (item) {
            colorFila = '';
            if (item.programacionPagoDetalleId == ppagoDetalleId) {
                colorFila = " style='background: #c2ffae;' ";
            }
            var eliminar = "<a href='#' onclick = 'eliminarProgramacionPagoDetalle(\"" + ind + "\")' >"
                    + "<i id='e" + ind + "' class='fa fa-trash-o' style='color:#cb2a2a;'></i></a>";

            var editar = "<a href='#' onclick = 'editarProgramacionPagoDetalle(\"" + ind + "\")' >"
                    + "<i id='e" + ind + "' class='fa fa-edit' style='color:#E8BA2F;'></i></a>&nbsp;&nbsp;&nbsp;";

            var actualizarEstado = '';
            // El estado
            switch (item.estadoId * 1) {
                case 1:
                    actualizarEstado = "<a href='#' onclick='actualizarEstadoPPagoDetalle(" + ind + ",3)'><i class='fa fa-lock' style='color:red;' title='Actualizar a liberado'></i></a>&nbsp;";
                    break;
                case 3:
                    actualizarEstado = "<a href='#' onclick='actualizarEstadoPPagoDetalle(" + ind + ",1)'><i class='fa fa-unlock' style='color:green;' title='Actualizar a por liberar'></i></a>&nbsp;";
                    break;
                case 4:
                    actualizarEstado = "<i class='fa fa-money' style='color:orange;' title='Pagado parcialmente'></i>&nbsp;";
                    eliminar = '';
                    editar = '';
                    break;
                case 5:
                    actualizarEstado = "<i class='fa fa-money' style='color:green;' title='Pagado'></i>&nbsp;";
                    eliminar = '';
                    editar = '';
                    break;
                default:
                    actualizarEstado = "<i class='ion-close-circled' style='color:red;' title='Eliminado'></i>";
            }

            var fechaProg = item.fechaProgramada;
            if (isEmpty(fechaProg)) {
                switch (parseInt(item.indicadorId)) {
                    case 62:
                        fechaProg = sumaFecha(item.dias, $('#pFechaEmision').html());
                        break;
                    default:
                        fechaProg = sumaFecha(item.dias, $('#pFechaTentativa').html());
                        break;
                }
            } else {
                fechaProg = '<b>' + item.fechaProgramada + '</b>';
            }

            cuerpo += "<tr " + colorFila + ">"
                    + "<td style='text-align:left;'>" + item.indicadorDesc + "</td>"
                    + "<td style='text-align:right;'>" + formatearCantidad(item.dias) + "</td>"
                    + "<td style='text-align:center;'>" + fechaProg + "</td>"
                    + "<td style='text-align:right;'>" + formatearNumero(item.importe) + "</td>"
                    + "<td style='text-align:right;'>" + formatearNumero(item.importe * 100 / documentoTotal) + "</td>"
                    + "<td style='text-align:center;'>" + actualizarEstado + "</td>"
                    + "<td style='text-align:center;'>" + editar + eliminar
                    + "</td>"
                    + "</tr>";

            ind++;
        });

        $('#dataTableProgramacionPagoDetalle tbody').append(cuerpo);
    }
}

function editarProgramacionPagoDetalle(indice) {
    $('#detalleIndice').val(indice);

    abrirModalNuevoDetalle();
    var objDetalle = listaProgramacionPagoDetalle[indice];
    select2.asignarValor('cboIndicador', objDetalle.indicadorId);
//    onChangeIndicador();
    $('#txtDias').val(formatearCantidad(objDetalle.dias));
    if (!isEmpty(objDetalle.fechaProgramada)) {
        $('#txtFechaProgamada').datepicker('setDate', objDetalle.fechaProgramada);
        $("#rdFechaProg").prop("checked", "checked");
    } else {
        $('#txtFechaProgamada').val('');
        $("#rdDias").prop("checked", "checked");
    }
    $('#txtImporte').val(objDetalle.importe);
    actualizarPorcentaje();
    habilitarContenedorTiempoProgramado();

    $("#rdImporte").prop("checked", "checked");
    habilitarContenedorImporteProgramado();
}

var listaProgramacionPagoDetalleEliminado = [];

function eliminarProgramacionPagoDetalle(indice) {
    if (!isEmpty(listaProgramacionPagoDetalle[indice].programacionPagoDetalleId)) {
        listaProgramacionPagoDetalleEliminado.push(listaProgramacionPagoDetalle[indice].programacionPagoDetalleId);
    }

    listaProgramacionPagoDetalle.splice(indice, 1);

    var detalleCopia = listaProgramacionPagoDetalle.slice();
    listaProgramacionPagoDetalle = [];

    if (!isEmpty(detalleCopia)) {
        $.each(detalleCopia, function (i, item) {
            listaProgramacionPagoDetalle.push(item);
        });
    }

    onListarProgramacionPagoDetalle(listaProgramacionPagoDetalle);
}

//------------ FIN DETALLE -----------------

function onChangeIndicador() {
    var indicador = select2.obtenerValor('cboIndicador');

    if (indicador == 62) {
        $('#txtFechaProgamada').datepicker('setDate', $('#pFechaEmision').html());
    } else {
        $('#txtFechaProgamada').datepicker('setDate', $('#pFechaTentativa').html());
    }

    $("#rdFechaProg").prop("checked", "checked");
    habilitarContenedorTiempoProgramado();
}

function guardarProgramacionPago() {
    if (validarFormulario()) {
        var documentoId = document.getElementById('documentoId').value;
        var fechaTentativa = $('#pFechaTentativa').html();
        crearProgramacionPago(documentoId, fechaTentativa);
    }
}

function validarFormulario() {
    var bandera = true;
    var espacio = /^\s+$/;
    limpiarMensajes();

    if (isEmpty(listaProgramacionPagoDetalle)) {
        $("#msjDetalle").text("Ingrese el detalle de la programación de pagos").show();
        bandera = false;
    } else {
        var importeTotal = 0;
        $.each(listaProgramacionPagoDetalle, function (i, item) {
            importeTotal = importeTotal + item.importe * 1;
        });

        if (importeTotal.toFixed(2) != documentoTotal.toFixed(2)) {
            mostrarAdvertencia('El importe total debe ser igual a ' + formatearNumero(documentoTotal));
            bandera = false;
        }

    }


    return bandera;
}

function limpiarMensajes() {
    $("#msjDetalle").hide();
}

function crearProgramacionPago(documentoId, fechaTentativa) {
    loaderShow();
    ax.setAccion("guardarProgramacionPago");
    ax.addParamTmp("documentoId", documentoId);
    ax.addParamTmp("fechaTentativa", fechaTentativa);
    ax.addParamTmp("personaId", personaId);
    ax.addParamTmp("listaProgramacionPagoDetalle", listaProgramacionPagoDetalle);
    ax.addParamTmp("listaProgramacionPagoDetalleEliminado", listaProgramacionPagoDetalleEliminado);
    ax.consumir();
}

function onResponseGuardarProgramacionPago(data) {
    if (data[0]['vout_exito'] == 1) {
        mostrarOk(data[0]['vout_mensaje']);
        cargarPantallaListar();
    } else {
        mostrarAdvertencia((data[0]['vout_mensaje']));
    }
}

//----------------------- VISUALIZAR DOCUMENTO -------------------------------
function obtenerDocumento(documentoId) {
    loaderShow();
    ax.setAccion("obtenerDocumento");
    ax.addParamTmp("documentoId", documentoId);
    ax.consumir();
}

function onResponseObtenerDocumento(data) {
    $('[data-toggle="popover"]').popover('hide');
    cargarDataDocumento(data.dataDocumento);
    cargarDataComentarioDocumento(data.comentarioDocumento);

    if (!isEmpty(data.detalleDocumento)) {
        cargarDetalleDocumento(data.detalleDocumento, data.dataMovimientoTipoColumna, data.organizador);
    } else {
        $('#formularioCopiaDetalle').hide();
    }

    $('#modalDetalleDocumento').modal('show');
}

function cargarDataComentarioDocumento(data) {
    $('#txtComentario').val(data[0]['comentario_documemto']);
    $('#txtDescripcion').val(data[0]['descripcion_documemto']);
}

function appendFormDetalle(html) {
    $("#formularioDetalleDocumento").append(html);
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

            if (!isEmpty(valor))
            {
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
                    case 19:
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

function fechaArmada(valor) {
    var fecha = separarFecha(valor);
    return fecha.dia + "/" + fecha.mes + "/" + fecha.anio;
}

var movimientoTipoColumna;
function cargarDetalleDocumento(data, dataMovimientoTipoColumna, dataOrganizador) {
    movimientoTipoColumna = dataMovimientoTipoColumna;
    if (!isEmptyData(data)) {
        $('#formularioCopiaDetalle').show();

        $.each(data, function (index, item) {
            data[index]["importe"] = formatearNumero(data[index]["cantidad"] * data[index]["valor_monetario"]);
            data[index]["cantidad"] = formatearCantidad(data[index]["cantidad"]);
            data[index]["valor_monetario"] = formatearNumero(data[index]["valor_monetario"]);
        });

        //CABECERA DETALLE
        var tHeadDetalle = $('#theadDetalle');
        tHeadDetalle.empty();

        var html = '';
        html += "<tr>";
//        if(existeColumnaCodigo(15)){
        if (!isEmpty(dataOrganizador)) {
            html += "<th style='text-align:center;'>Organizador</th>";
        }
        html += "<th style='text-align:center;'>Cantidad</th>";
        html += "<th style='text-align:center;'>Unidad de medida</th>";
        html += "<th style='text-align:center;'>Producto</th> ";
        if (existeColumnaCodigo(5)) {
            html += "<th style='text-align:center;'>Precio Unitario</th>";
            html += "<th style='text-align:center;'>Total</th>";
        }
        html += "</tr>";
        tHeadDetalle.append(html);


        //CUERPO DETALLE
        var tBodyDetalle = $('#tbodyDetalle');
        tBodyDetalle.empty();

        html = '';
        $.each(data, function (index, item) {
            html += "<tr>";
//            if(existeColumnaCodigo(15)){
            if (!isEmpty(dataOrganizador)) {
                html += "<td>" + item.organizador_descripcion + "</td>";
            }
            html += "<td style='text-align:right;'>" + item.cantidad + "</td>";
            html += "<td>" + item.unidad_medida_descripcion + "</td>";
            html += "<td>" + item.bien_descripcion + "</td> ";
            if (existeColumnaCodigo(5)) {
                html += "<td style='text-align:right;'>" + item.valor_monetario + "</td>";
                html += "<td style='text-align:right;'>" + item.importe + "</td>";
            }
            html += "</tr>";
        });

        tBodyDetalle.append(html);
    } else {
        var table = $('#datatable2').DataTable();
        table.clear().draw();
    }
}

function existeColumnaCodigo(codigo) {
    var dataColumna = movimientoTipoColumna;

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
//-------------------- FIN VISUALIZAR DOCUMENTO ----------------------------

function actualizarEstadoPPagoDetalle(indice, nuevoEstado) {
    listaProgramacionPagoDetalle[indice].estadoId = nuevoEstado;
    var nuevoEstadoDesc = '';
    if (nuevoEstado == 1) {
        nuevoEstadoDesc = 'Por liberar';
    } else {
        nuevoEstadoDesc = 'Liberado';
    }
    ;
    listaProgramacionPagoDetalle[indice].estadoDesc = nuevoEstadoDesc;

    onListarProgramacionPagoDetalle(listaProgramacionPagoDetalle);
}

function habilitarContenedorFechaTentativa() {
    $('#txtFechaTentativa').datepicker('setDate', $('#pFechaTentativa').html());
    $('#contenedorFechaTentativa').show();
    $('#pFechaTentativa').hide();
}

function actualizarFechaProgramada() {
    $('#pFechaTentativa').html($('#txtFechaTentativa').val());
    habilitarFechaTentativa();

    onListarProgramacionPagoDetalle(listaProgramacionPagoDetalle);
}

function habilitarFechaTentativa() {
    $('#pFechaTentativa').show();
    $('#contenedorFechaTentativa').hide();
}

function limpiarFechaProgramada() {
//    $('#txtFechaProgamada').datepicker('setDate', '');    
    $('#txtFechaProgamada').val('');
}

$('#txtFechaProgamada').datepicker({
    isRTL: false,
    format: 'dd/mm/yyyy',
    autoclose: true,
    language: 'es'
}).on('changeDate', function (ev) {
    $('#txtDias').val('');
});

function habilitarContenedorTiempoProgramado() {
    if (document.getElementById("rdDias").checked) {
        $('#contenedorDias').show();
        $('#contenedorFechaProgramada').hide();
    } else {
        $('#contenedorDias').hide();
        $('#contenedorFechaProgramada').show();
    }
}

function habilitarContenedorImporteProgramado() {
    if (document.getElementById("rdImporte").checked) {
        $('#txtPorcentaje').attr("readonly", "true");
        $('#txtImporte').removeAttr("readonly");
    } else {
        $('#txtImporte').attr("readonly", "true");
        $('#txtPorcentaje').removeAttr("readonly");
    }
}

function actualizarImporte() {
    var porcentaje = $('#txtPorcentaje').val();
    $('#txtImporte').val((porcentaje * documentoTotal / 100).toFixed(2));
}

function actualizarPorcentaje() {
    var importe = $('#txtImporte').val();
    $('#txtPorcentaje').val((importe * 100 / documentoTotal).toFixed(2));
}