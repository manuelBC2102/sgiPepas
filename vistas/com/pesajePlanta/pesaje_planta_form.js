var c = $('#env i').attr('class');
var anchoComboSunat2;
var dataPersonaGlobal;
$(document).ready(function () {
  
    select2.iniciar();
    anchoComboSunat2 = $("#divCboCodigoSunat2").width();

    ax.setSuccess("successPersona");
    ax.setAccion("obtenerConfiguracionesSolicitudRetiro");
    ax.addParamTmp("solicitudId", commonVars.personaId);
    ax.consumir();
    
    setearInputActa();

});

$("#cboClasePersona").on("change", function (e) {
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
        if (item == -2) {
            bandera_tabla_centro_costo = true;
        }
    });
    if (bandera_tabla_centro_costo) {
        cargarCentroCostoPersona(dataCentroCostoPersona);
    } else {
        $('#divCentroCostoPersona').hide();
    }
});


$('#txtFechaEntrega').keypress(function () {
    $('#msjFechaEntrega').hide();
});
$('#txtCapacidad').keypress(function () {
    $('#msjCapacidad').hide();
});
$('#txtConstancia').keypress(function () {
    $('#msjConstancia').hide();
});

$('#cboPlanta').keypress(function () {
    $('#msjPlanta').hide();
});
$('#cboZona').keypress(function () {
    $('#msjZona').hide();
});
$('#cboTransportista').keypress(function () {
    $('#msjTransportista').hide();
});
$('#cboConductor').keypress(function () {
    $('#msjConductor').hide();
});
$('#cboVehiculo').keypress(function () {
    $('#msjVehiculo').hide();
});

$('#divCentroCostoPersona').hide();
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
            case 'obtenerLotes':
                renderLotes(response.data);
                loaderHide2();
                break; 
            case 'guardarLotes':
                obtenerLote(response.data);
                loaderHide2();
                break; 
            case 'eliminarLotes':
                obtenerLote(response.data);
                loaderHide2();
                break;    
            case 'obtenerConfiguracionesSolicitudRetiro':
                onresponseConfiguracionesPersona(response.data);
                dataPersonaGlobal = response.data.personaNatural;
                //console.log(dataPersonaGlobal);
                break;
            case 'insertSolicitud':
                mostrarOk("Solicitud Registrada");
                loaderHide2();
                habilitarBoton();
                cargarListarPersonaCancelar();
                break;
            case 'guardarActaRetiro':
                ;
                mostrarOk("Acta Retiro Registrada");
                loaderClose();
                cargarListarPersonaCancelar();
                break;
            case 'updateSolicitud':
                mostrarOk(response.data['0'].vout_mensaje);
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
            case 'obtenerDataActa':
                listarDataSolicitudes(response.data);
                loaderClose();
                break;
            case 'guardarPesajesActaRetiro':
                    mostrarOk("Fecha Recepción Actualizada");
                    loaderClose();
                    habilitarBoton();
                    cargarListarActaCancelar();
                    break;
        }
    } else
    {
        switch (response[PARAM_ACCION_NAME]) {
            case 'insertSolicitud':
                loaderClose();
                habilitarBoton();
                break;

            case 'guardarActaRetiro':
                loaderClose();
                habilitarBoton();
                break;
            case 'updateSolicitud':
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
            case 'obtenerDataActa':
                loaderClose();
                habilitarBoton();
                break;
            case 'guardarPesajesActaRetiro':
                mostrarMensajeError("No se pudo Actualizar Fecha Recepción del Acta");
                    loaderClose();
                    habilitarBoton();
                    break;
        }
    }
}

function cargarListarPersonaCancelar()
{
    loaderShow(null);
    cargarDiv('#window', 'vistas/com/pesajePlanta/pesaje_planta_listar.php');
}
var direccionTipoFiscal;
var convenioSunatId0 = null;
function onresponseConfiguracionesPersona(data)
{



    loaderClose();
}

function cargarListarActaCancelar()
{
    loaderShow(null);
    cargarDiv('#window', 'vistas/com/pesajePlanta/pesaje_planta_listar.php');
}

function mostrarMensajeError(nombre)
{
    $('#msj' + nombre).hide();
}

function habilitarDivContactoTipoTexto() {
    $("#contenedorContactoTipoDivCombo").hide();
    $("#contenedorContactoTipoDivTexto").show();
}

function habilitarDivContactoTipoCombo() {
    $("#contenedorContactoTipoDivTexto").hide();
    $("#contenedorContactoTipoDivCombo").show();
}


var personaIdRegistro = null;
function setearPersonaRegistro(personaId) {
//    console.log(personaId);
    personaIdRegistro = personaId;
    obtenerPersonasNaturales();
}

function asignarValorSelect2(id, valor)
{
    $("#" + id).select2().select2("val", valor);
    $("#" + id).select2({width: '100%'});
}

function setearInputActa() {
    loaderShow();
    ax.setAccion("obtenerDataActa");
    ax.addParamTmp("acta", commonVars.personaId);
    ax.consumir();
}
function getSelectedItems() {
    var selected = [];
    $(".select_item:checked").each(function() {
        selected.push($(this).data('id'));
    });
    return selected;
}

function listarDataSolicitudes(data) {
    $("#datatable2").empty();
    var cuerpo_total = '';
    var cuerpo = '';
    var url = '/sgiLaVictoria/vistas/com/solicitudRetiro/documento/';
    var cabeza = '<div class="table-responsive">' + // Agregar clase table-responsive aquí
        '<table id="datatable" class="table table-striped table-bordered">' +
        "<thead>" +
        "<tr>" +
        "<th style='text-align:center; display: none;'><input type='checkbox' id='select_all' checked disabled /></th>" + // Ocultar la cabecera del checkbox
        "<th style='text-align:center;'>Codigo</th>" +
        "<th style='text-align:center;'>Fecha Entrega</th>" +
        "<th style='text-align:center;'>Zona</th>" +
        "<th style='text-align:center;'>Vehiculo</th>" +
        "<th style='text-align:center;'>Conductor</th>" +
        "<th style='text-align:center;'>REINFO</th>" +
        "<th style='text-align:center;'>Planta</th>" +

        "<th style='text-align:center;'>Acción</th>" +
        "</tr>" +
        "</thead><tbody>";
    $.each(data, function (index, item) {
        cuerpo = "<tr>" +
            "<td style='text-align:center; display: none;'><input type='checkbox' class='select_item' data-id='" + item.id + "' checked disabled /></td>" + // Ocultar cada checkbox y deshabilitarlo
            "<td style='text-align:left;'>" + item.id + "</td>" +
            "<td style='text-align:left;'>" + item.fecha_entrega + "</td>" +
            "<td style='text-align:left;'>" + item.zona + "</td>" +
            "<td style='text-align:left;'>" + item.vehiculo + "</td>" +
            "<td style='text-align:left;'>" + item.conductor + "</td>" +
            "<td style='text-align:left;'>" + item.sociedad + "</td>" +
            "<td style='text-align:left;'>" + item.planta + "</td>" +
           
            "<td style='text-align:center;'><button class='btn btn-success' onclick='openLoteModal(" + item.id + ")'><i class='fa fa-plus'></i>Agregar Lote</button></td>" +
            "</tr>";
        cuerpo_total = cuerpo_total + cuerpo;
    });
    var pie = '</tbody></table></div>'; // Cerrar el div table-responsive aquí
    var html = cabeza + cuerpo_total + pie;
    $("#datatable2").append(html);

    // No se necesita el evento de selección/deselección de todos los checkboxes porque están deshabilitados
}


var lotes = {};

function openLoteModal(solicitudId) {
    $('#loteModal').modal('show');
    $('#saveLote').data('solicitudId', solicitudId);
    obtenerLote(solicitudId);
}

function renderLotes(data) {
    var loteTableBody = $('#loteTable tbody');
    loteTableBody.empty();
    
    if (data) {
        data.forEach(function(lote) {
            var aprobacion;
            if (lote.aprobacion == null) {
                aprobacion = 'Pendiente Aprobación';
            } else if (lote.aprobacion == 1) {
                aprobacion = 'Aprobado';
            } else {
                aprobacion = 'Rechazado';
            }

            var rutaArchivo = '/sgiLaVictoria/vistas/com/solicitudRetiro/lotes/' + lote.archivo;
            var row = '<tr>' +
                '<td>' + lote.lote + '</td>' +
                '<td>' + lote.ticket + '</td>' +
                // '<td>' + lote.ticket2 + '</td>' +
                '<td>' + lote.peso_bruto + '</td>' +
                '<td>' + lote.peso_tara + '</td>' +
                '<td>' + lote.peso_neto + '</td>' +
                '<td>' + (lote.archivo ? "<a href='" + rutaArchivo + "' target='_blank'>Ver archivo</a>" : "No disponible") + '</td>' +
                '<td>' + aprobacion + '</td>';
            
            // Añadir el botón de eliminar solo si aprobacion no es 1 (Aprobado)
            if (lote.aprobacion != 1) {
                row += '<td><i class="fa fa-trash" style="cursor:pointer; color:red;" onclick="deleteLote(' + lote.id + ', \'' + lote.archivo + '\')"></i></td>';
            } else {
                row += '<td></td>';
            }

            row += '</tr>';
            loteTableBody.append(row);
        });
    }
}   function calculatePesoNeto() {
    const pesoBruto = parseFloat(document.getElementById('peso_bruto').value) || 0;
    const pesoTara = parseFloat(document.getElementById('peso_tara').value) || 0;
    const pesoNeto = pesoBruto - pesoTara;
    document.getElementById('peso_neto').value = pesoNeto.toFixed(2);
}

function saveLote() {
    ;
    var solicitudId = $('#saveLote').data('solicitudId');
    var ticket1 = $('#ticket1').val();
    var ticket2 = $('#ticket1').val();
    var peso_bruto = $('#peso_bruto').val();
    var peso_tara = $('#peso_tara').val();
    var peso_neto = $('#peso_neto').val();
    var nombre_lote = $('#nombre_lote').val();
    var archivo_lote = $('#secretImg').val();
    loaderShow2();
    ax.setAccion("guardarLotes");
    ax.addParamTmp("solicitudId", solicitudId);
    ax.addParamTmp("ticket1", ticket1);
    ax.addParamTmp("ticket2", ticket2);
    ax.addParamTmp("peso_bruto", peso_bruto);
    ax.addParamTmp("peso_tara", peso_tara);
    ax.addParamTmp("peso_neto", peso_neto);
    ax.addParamTmp("nombre_lote", nombre_lote);
    ax.addParamTmp("archivo_lote", archivo_lote);
    ax.consumir();
    $('#loteForm')[0].reset();
    limpiarCampos();
}

function limpiarCampos() {
    $('#file').val('');
    $('#upload-file-info').html('Ninguna archivo seleccionada');
    $('#myImg').attr('src', 'vistas/com/persona/imagen/none.jpg');
    $('#secretImg').val('');
}

function editLote(solicitudId, index) {
    var lote = lotes[solicitudId][index];
    $('#ticket1').val(lote.ticket1);
    $('#ticket2').val(lote.ticket1);
    $('#pesaje').val(lote.pesaje);
    $('#nombre_lote').val(lote.nombre_lote);
    $('#archivo_lote').val(lote.archivo_lote);

    $('#saveLote').off('click').on('click', function() {
        lotes[solicitudId][index] = {
            ticket1: $('#ticket1').val(),
            ticket2: $('#ticket1').val(),
            pesaje: $('#pesaje').val(),
            nombre_lote: $('#nombre_lote').val(),
            archivo_lote: $('#archivo_lote').val()
        };
        renderLotes(solicitudId);
        $('#loteForm')[0].reset();
    });
}

function loaderShow2() {
    document.getElementById('loader').style.display = 'flex';
}

function loaderHide2() {
    document.getElementById('loader').style.display = 'none';
}
function deleteLote(id, archivo) {
    ;
    loaderShow2();
    var solicitudId = $('#saveLote').data('solicitudId');
    ax.setAccion("eliminarLotes");
    ax.addParamTmp("id", id);
    ax.addParamTmp("archivo", archivo);
    ax.addParamTmp("solicitudId", solicitudId);
    ax.consumir();
}

function obtenerLote(solicitudId) {
    ;
    var solicitudId = $('#saveLote').data('solicitudId');
    console.log('Mostrando loader');
    loaderShow2();
    ax.setAccion("obtenerLotes");
    ax.addParamTmp("id", solicitudId);
    ax.consumir();
}

$(document).ready(function() {
    $('#saveLote').click(saveLote);
});



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

function guardarSolicitud() {
    ;
    var selectedItems = getSelectedItems();
    loaderShow();
    ax.setAccion("guardarPesajesActaRetiro");
    ax.addParamTmp("selectedItems", selectedItems);
    ax.addParamTmp("acta_id", commonVars.personaId);
    ax.consumir();

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

var claseConductor="22";
function mostrarLicenciaConducir() {
    var dataClase = $('#cboClasePersona').val();

    if (!isEmpty(dataClase)) {
        var index = dataClase.indexOf(claseConductor);
        if (index != -1) {
            $("#contenedorLicenciaConducir").show();
        } else {
            $("#contenedorLicenciaConducir").hide();
        }
    } else {
        $("#contenedorLicenciaConducir").hide();
    }
}