var c = $('#env i').attr('class');
var buscar = false;

var personaTipoVentana = 0;
$(document).ready(function () {
    $('[data-toggle="popover"]').popover({ html: true }).popover();
    ax.setSuccess("successPersonaListar");
    select2.iniciar();
    personaTipoVentana = getParameterByName('personaTipo');
    if (personaTipoVentana == 2) {
        cargarFormularioPersona(2, 'PN', 'vistas/com/persona/persona_natural_form.php');
    } else {
        configuracionesIniciales();
        colapsarBuscadorPersona();
        cambiarAnchoBusquedaDesplegable();
    }
    modificarAnchoTabla('datatable');
});

function exportarReporteExcel(colapsa) {
    loaderShow();
    ax.setAccion("ExportarPersonaExcel");
    ax.consumir();
}

function imprimirDocumentoTicket(id) {
    window.open(URL_BASE + 'vistas/com/actaRetiro/formato.php?id=' + id);
}
function configuracionesIniciales() {
    ax.setAccion("configuracionesInicialesPersonaListar");
    ax.consumir();
}

function onresponseConfiguraciones(data) {
    //    console.log(data);
    $('#listaPersonaTipo').empty();
    var perJuridica;
    var perNatural;
    var html;
    if (!isEmpty(data.persona_tipo)) {
        $('#cboTipoPersonaBusqueda').append('<option value="-1">Seleccionar tipo de persona</option>');
        $.each(data.persona_tipo, function (index, value) {
            /*
             * Para el buscador
             */
            $('#cboTipoPersonaBusqueda').append('<option value="' + value.id + '">' + value.descripcion + '</option>');

            //Para boton nuevo           
            //            $('#listaPersonaTipo').append('<li><a data-toggle="modal" data-target="#accordion-modal" onclick="cargarFormularioPersona(' + value.id + ',\'' + value.descripcion + '\',\'' + value.ruta + '\')">' + value.descripcion + '</a></li>');

            if (value.id == 2) {
                perNatural = value;
            } else {
                perJuridica = value;
            }
        });



        $('#listaPersonaTipo').append(html);
    }

    /*
     * Cargar dato en el select multiple de busqueda clase de persona
     */
    if (!isEmpty(data.persona_tipo)) {
        $.each(data.persona_clase, function (index, value) {
            $('#cboClasePersonaBusqueda').append('<option value="' + value.id + '">' + value.descripcion + '</option>');
        });
    }
    listarPersona();
}

var personaTipoIdG;
var valorPersonaTipoG;
var rutaG;

function cargarFormularioPersona(personaTipoId, valor_persona_tipo, ruta) {
    commonVars.personaId = 0;
    cargarDiv('#window', 'vistas/com/actaRetiro/acta_retiro_form.php', "Nueva ");
}

function onResponseObtenerPersonaClaseAsociada(data) {
    if (isEmpty(data)) {
        $.Notification.autoHideNotify('warning', 'top right', 'Validación', 'No tiene permisos para crear el tipo de persona.');
    } else {
        cargarFormulario(personaTipoIdG, valorPersonaTipoG, rutaG);
    }
}

function cargarFormulario(personaTipoId, valor_persona_tipo, ruta) {
    loaderShow(null);
    var dependiente = document.getElementById('hddIsDependiente').value;
    //    obtenerTitulo(dependiente);
    if (!isEmpty(valor_persona_tipo)) {
        valor_persona_tipo = valor_persona_tipo.toLowerCase();
    }
    if (dependiente == 0) {
        commonVars.personaId = 0;
        cargarDiv('#window', 'vistas/com/persona/persona_form.php', "Nueva Persona");
    } else {
        cargarDivModal('#respuesta', ruta, "Nueva " + valor_persona_tipo);
    }
}

var nombres;
var codigo;
var tipoPersona;
var clasePersona;
var id;


function listarPersona() {
   loaderShow();
    ax.setAccion("obtenerLotes");
    ax.addParamTmp("id", commonVars.personaId);
    ax.consumir();
   
}

function ingresarRegistrarPesajes(id) {
    debugger;
    loaderShow(null);
    commonVars.personaId = id;
    cargarDiv("#window", "vistas/com/pesajePlanta/pesaje_planta_form.php", "Registrar Pesajes ");
}
function showModalActualizarPesajes(id) {
    loaderShow();
    llenarcombosolicitudes(id);
    // Abre el modal
    $('#modalActualizarPesajes').modal('show');
    // Guarda el ID de la fila para usarlo más tarde
    $('#hiddenRowId').val(id);
    loaderClose();
}

function actualizarPesajes() {

    var solicitudId = $('#cboTipoArchivo').val();
    commonVars.personaId = solicitudId;
    cargarDiv("#window", "vistas/com/pesajePlanta/pesaje_actualizar_planta.php", "Actualizar Pesajes ");
}


function llenarcombosolicitudes(id) {
    ax.setAccion("obtenerDataActa");
    ax.addParamTmp("acta", id);
    ax.consumir();
}


function confirmarDeleteSolicitud(id) {
    BANDERA_ELIMINAR = false;
    swal({
        title: "Est\xe1s seguro?",
        text: "Eliminarás el acta de retiro",
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
            loaderShow();
            deleteSolicitud(id);
        } else {
            if (BANDERA_ELIMINAR == false) {
                swal("Cancelado", "La eliminaci\xf3n fue cancelada", "error");
            }
        }
    });
}

function successPersonaListar(response) {
    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {

            case 'cambiarEstadoPersona':
                onResponseCambiarEstadoPersona(response.data);
                listarPersona();
                break;
            case 'deleteSolicitud':
                loaderClose();
                var error = response.data[0]['vout_exito'];
                if (error == 1) {
                    swal("Eliminado!", "Solicitud eliminada correctamente", "success");
                } else {
                    swal("Cancelado", response.data[0]['vout_mensaje'] + "No se pudo eliminar", "error");
                }
                bandera_eliminar = true;
                listarPersona();
                break;
            case 'configuracionesInicialesPersonaListar':
                onresponseConfiguraciones(response.data);
                break;
            case 'getAllEmpresa':
                onResponseGetAllEmpresas(response.data);
                break;
                break;
            case 'importPersona':
                $('#fileInfo').html('');
                $('#resultado').append(response.data);
                break;
            case 'ExportarPersonaExcel':
                loaderClose();
                location.href = URL_BASE + "util/formatos/lista_de_personas.xlsx";
                break;
            case 'buscarCriteriosBusquedaSolicitud':
                onResponseBuscarCriteriosBusquedaSolicitud(response.data);
                loaderClose();
                break;
            case 'obtenerDataActa':
                onResponsellenarcomboTipoArchivo(response.data);
                break;
            case 'obtenerPersonaClaseAsociada':
                onResponseObtenerPersonaClaseAsociada(response.data);
                loaderClose();
                break;
            case 'obtenerLotes':
                onResponseAjaxpGetDataGridSolicitudes(response.data);
                   break;

            case 'registrarResultadosLote':
                mostrarOk("Resultados registrados");
                loaderClose();
                cargarListarActaCancelar();
                break;
        }
    } else {
        switch (response[PARAM_ACCION_NAME]) {
            case 'deleteSolicitud':
                loaderClose();
                swal("Cancelado", "No se pudo eliminar, esta en uso", "error");
            case 'obtenerDataActa':
                loaderClose();
                swal("Cancelado", "No se pudo obtener los datos de las solicitudes", "error");
                break;
                case 'registrarResultadosLote':
                    loaderClose();
                    swal("Cancelado", "No se pudo registrar los resultados", "error");
                    break;
        }
    }
}


function onResponseAjaxpGetDataGridSolicitudes(data) {
    debugger;
    $("#dataList").empty(); // Limpiar contenido anterior

    if (Array.isArray(data) && data.length > 0) {
        var html = '';

        $.each(data, function(index, loteData) {
            html += `
                <div class="panel panel-1" id="lote_${index}">
                    <div class="panel-hdr">
                        <h2>Lote ${index + 1}</h2>
                        <div class="panel-buttons">
                            <button type="button" class="btn-success add-item" hidden><i class="fa fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="panel-container">
                        <div class="form-group">
                            <div class="row">
                                <input type="hidden" id="solicitudId_${index}" name="lote[${index}][solicitudId]" value="${loteData.solicitud_retiro_id}">
                                <input type="hidden" id="id_${index}" name="lote[${index}][id]" value="${loteData.id}">
                                <div class="col-md-4">
                                    <label for="lote_${index}">Lote</label>
                                    <input type="text" id="lote_${index}" name="lote[${index}][lote]" class="form-control" required readonly value="${loteData.lote}">
                                </div>
                                <div class="col-md-4">
                                    <label for="tmh_${index}">TMH</label>
                                    <input type="number" id="tmh_${index}" name="lote[${index}][tmh]" class="form-control" step="any" required readonly value="${loteData.peso_neto}">
                                </div>
                                <div class="col-md-4">
                                    <label for="porcentagua_${index}">Porcentaje Agua</label>
                                    <input type="number" id="porcentagua_${index}" name="lote[${index}][porcentagua]" class="form-control" step="any" onchange="calcularTms(this)">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="merma_${index}">Merma</label>
                                    <input type="number" id="merma_${index}" name="lote[${index}][merma]" class="form-control" step="any" onchange="calcularTms(this)">
                                </div>
                                <div class="col-md-4">
                                    <label for="tms_${index}">TMS</label>
                                    <input type="number" id="tms_${index}" name="lote[${index}][tms]" class="form-control" step="any" required onchange="calcularTotalMineral(this)">
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <div class="input-group">
                                        <br>
                                            <label for="file_${index}" class="file-upload btn w-lg m-b-5" style="border-radius: 0px;background-color: #337Ab7;color: #fff;cursor:pointer;">
                                                <i class="ion-upload m-r-15" style="font-size: 13px;"></i>Subir certificado de ley
                                            </label>
                                            <input name="file" id="file_${index}" type="file" accept="*" class="upload" style="display:none;">
                                            &nbsp; &nbsp; <b class='' id="upload-file-info_${index}">Ningún archivo seleccionado</b>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
 <button type="button" class="btn btn-outline-success " onclick="agregarMineral(${index})">
                <i class="fa fa-plus"></i> Agregar Mineral
            </button>
                        <!-- Agregar la sección de minerales -->
                        <div class="panel panel-1 panelMineral" id="mineralPanel_${index}">
                            <div class="panel-hdr">
                                <h2>Mineral ${index+1}</h2>
                                <div class="panel-buttons">
                                     
            <!-- Botón de Eliminar Mineral -->
            <div class="col-row">
            <button type="button" class="btn btn-outline-danger remove-mineral" onclick="eliminarMineral(${index})" style="display: none;">
                <i class="fa fa-minus"></i> Eliminar Mineral
            </button>
                              </div>   </div>
                            </div>
                            <div class="panel-container">
                                <div class="form-group">
                                    <div class="row">
                                        <div class="form-group col-md-4">
                                            <label>Tipo mineral *</label>
                                            <div class="input-group">
                                                <select style="width: 320px;" name="cboTipoArchivo_${index}" id="cboTipoArchivo_${index}">
                                                    <option value="Oro">Oro</option>
                                                    <option value="Plata">Plata</option>
                                                </select>
                                            </div>
                                            <span id="msjContacto_${index}" class="control-label" style="color:red;font-style: normal;" hidden></span>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="ley_${index}">Ley</label>
                                            <input type="number" id="ley_${index}" name="lote[${index}][ley]" class="form-control" step="any" onchange="calcularTotalMineral(this)">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="unidad_${index}">Unidad</label>
                                            <select style="width: 320px;" id="unidad_${index}" name="lote[${index}][unidad]" onchange="calcularTotalMineral(this)">
                                                <option value="oz">oz</option>
                                                <option value="gr">gr</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label for="recuperacion_${index}">Recuperación</label>
                                            <input type="number" id="recuperacion_${index}" name="lote[${index}][recuperacion]" class="form-control" step="any" onchange="calcularTotalMineral(this)">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="precio_internacional_${index}">Precio Internacional</label>
                                            <input type="number" id="precio_internacional_${index}" name="lote[${index}][precio_internacional]" class="form-control" step="any" onchange="calcularTotalMineral(this)">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="descuento_internacional_${index}">Descuento Internacional</label>
                                            <input type="number" id="descuento_internacional_${index}" name="lote[${index}][descuento_internacional]" class="form-control" step="any" onchange="calcularTotalMineral(this)">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label for="maquila_${index}">Maquila</label>
                                            <input type="number" id="maquila_${index}" name="lote[${index}][maquila]" class="form-control" step="any" onchange="calcularTotalMineral(this)">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="penalidad_${index}">Penalidad</label>
                                            <input type="number" id="penalidad_${index}" name="lote[${index}][penalidad]" class="form-control" step="any" onchange="calcularTotalMineral(this)">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="flete_${index}">Flete</label>
                                            <input type="number" id="flete_${index}" name="lote[${index}][flete]" class="form-control" step="any" onchange="calcularTotalMineral(this)">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4" style="display:none;>
                                            <label for="total_calculado_${index}">Total Calculado</label>
                                            <input type="number" id="totalCalculado_${index}" name="lote[${index}][total_calculado]" class="form-control" step="any" disabled>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="total_mineral_${index}">Total Mineral</label>
                                            <input type="number" id="totalMineral_${index}" name="lote[${index}][total_mineral]" onchange="validarResultados(this)" class="form-control totalMineral" step="any">
                                        </div>
                                    </div>
                                    <input type="hidden" id="secretImg_${index}" name="lote[${index}][secretImg]" value="">
                                    <input type="hidden" id="secretNameFirma_${index}" name="lote[${index}][secretNameFirma]" value="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        $("#dataList").append(html);
        $('.select2').select2();

        // Script para cargar archivos y convertir a base64
        $.each(data, function(index, loteData) {
            debugger;
            $(`#file_${index}`).change(function() {
                debugger;
                var input = this;
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $(`#secretImg_${index}`).val(e.target.result);
                        $(`#secretNameFirma_${index}`).val(input.files[0].name);
                        $(`#upload-file-info_${index}`).html(input.files[0].name);
                    };
                    reader.readAsDataURL(input.files[0]);
                } else {
                    $(`#secretImg_${index}`).val('');
                    $(`#secretNameFirma_${index}`).val('');
                    $(`#upload-file-info_${index}`).html('Ningún archivo seleccionado');
                }
            });
        });
    } else {
        $("#dataList").html('<p>No se encontraron datos.</p>');
    }
    loaderClose();
}


function agregarMineral(index) {
    // Verificamos cuántos minerales tenemos en el lote
    var mineralCount = $(`#lote_${index} .panelMineral`).length;

    // Si hemos alcanzado el máximo de minerales (2), deshabilitamos el botón de agregar
    if (mineralCount >= 2) {
        Swal.fire({
            title: '¡Atención!',
            text: `Solo puedes tener 2 tipos de mineral para este servicio.`,
            icon: 'warning',
            confirmButtonText: 'Aceptar'
        });
    } else {
        var nuevoIndex =  1; // Usamos el número de minerales como índice único

        // Obtener el contenido del panel original
        var panelMineral = $(`#mineralPanel_${index}`).clone(true); // Clonamos el panel con todos los eventos y elementos

        // Actualizamos los ID y el índice de los elementos para que sean únicos
        panelMineral.attr('id', `mineralPanel_${nuevoIndex}`);
        panelMineral.find('.panel-hdr h2').text(`Mineral ${nuevoIndex+1}`);

        // Actualizar los IDs de los campos dentro del panel duplicado
        panelMineral.find('input, select').each(function() {
            var name = $(this).attr('name');
            var id = $(this).attr('id');
            if (name) $(this).attr('name', name.replace(index, nuevoIndex));
            if (id) $(this).attr('id', id.replace(index, nuevoIndex));
        });

        // Agregar el botón de eliminar al panel duplicado
        panelMineral.find('.panel-hdr').append(`
            <button type="button" class="btn-danger remove-mineral" onclick="eliminarMineral(${nuevoIndex})">
                <i class="fa fa-minus"></i> Eliminar Mineral
            </button>
        `);

        // Añadir el nuevo panel al lote correspondiente
        $(`#lote_${index}`).append(panelMineral);

        // Volver a inicializar los select2 solo en los nuevos combos
        panelMineral.find('select').select2({ width: '100%' });

        // Asociar el evento 'onchange' a los campos de los nuevos minerales
        panelMineral.find('input, select').on('change', function() {
            calcularTotalMineral(this); // Ejecutar el cálculo cuando cualquiera de los campos cambie
        });

        // Asegurarse de que el cálculo se ejecute en el segundo mineral de inmediato (si es necesario)
        calcularTotalMineral(panelMineral.find('input, select')[0]); // Esto desencadenará el cálculo al agregar el segundo mineral
    }
}




function eliminarMineral(index) {
  debugger;
    // Eliminar el panel de mineral correspondiente
    var mineralCount = $(`#lote_${1} .panelMineral`).length;
    if (mineralCount == 0) {
        $(`#lote_${1} .add-mineral`).prop('disabled', false); // Deshabilitar el botón de agregar mineral
        $(`#lote_${1} .add-mineral`).show(); // También ocultamos el botón
    }
    $(`#mineralPanel_${index}`).remove();

    // Verificamos cuántos minerales tenemos en el lote
    var mineralCount = $(`#lote_${1} .panelMineral`).length;

    // Si solo hay 1 mineral, ocultamos el botón de eliminar
    if (mineralCount == 1) {
        $(`#lote_${index} .remove-mineral`).hide(); // Ocultar el botón de eliminar si solo hay 1 mineral
    }

    // Mostrar el botón de agregar mineral solo si hay espacio para agregar más
    // if (mineralCount < 2) {
    //     // Volver a mostrar y habilitar el botón de agregar mineral
    //     $(`#lote_${index} .add-mineral`).prop('disabled', false); // Habilitar el botón de agregar mineral
    //     $(`#lote_${index} .add-mineral`).show(); // Mostrar el botón de agregar mineral
    // }
}

// Función para inicializar los paneles de minerales
function inicializarMinerales() {
    // Para cada lote, aseguramos que haya al menos 1 mineral
    $('.panelMineral').each(function() {
        var index = $(this).attr('id').split('_')[1]; // Obtener el índice del lote
        var mineralCount = $(`#lote_${index} .panelMineral`).length;

        // Si solo hay 1 mineral, ocultar el botón de eliminar
        if (mineralCount == 1) {
            $(`#lote_${index} .remove-mineral`).hide(); // Ocultar el botón de eliminar si solo hay 1 mineral
        }

        // Si solo hay 1 mineral, asegurarse de que el botón de agregar esté visible
        if (mineralCount == 1) {
            $(`#lote_${index} .add-mineral`).prop('disabled', false); // Asegurar que el botón de agregar esté habilitado
            $(`#lote_${index} .add-mineral`).show(); // Asegurarse de que esté visible
        }
    });
}

// Llamar a la función para inicializar los minerales cuando se cargue la página o después de agregar o eliminar minerales
$(document).ready(function() {
    inicializarMinerales();
});




// Event listener para el primer panel (lote inicial)
$(document).on('click', '.add-mineral-btn', function() {
    var index = $(this).data('index'); // Usamos un atributo 'data-index' para pasar el índice correcto
    agregarMineral(index);
});

function cargarListarActaCancelar()
{
    loaderShow(null);
    cargarDiv('#window', 'vistas/com/entregaResultados/entrega_resultados_listar.php', 'Entrega resultados');
}

function calcularTms(element) {
    // Obtener el índice del lote desde el ID del elemento
    var id = $(element).attr('id');
    var index = id.split('_')[1];

    // Obtener los valores de los campos relacionados
    var tmh = parseFloat($(`#tmh_${index}`).val()) || 0;
    var porcentagua = parseFloat($(`#porcentagua_${index}`).val()) || 0;
    var merma = parseFloat($(`#merma_${index}`).val()) || 0;

    // Realizar el cálculo del TMS
    var tms = tmh * (1-((porcentagua)/100)) * (1-((merma)/100));

    // Escribir el valor del TMS en el input correspondiente
    $(`#tms_${index}`).val(tms.toFixed(3));


}

function validarResultados(element) {
    debugger;
    var id = $(element).attr('id');
    var index = id.split('_')[1];

    var total_calculado = parseFloat($(`#totalCalculado_${index}`).val()) || 0;
    if(total_calculado==0){
        document.getElementById("guardarDatos").disabled = true;
    }

    var total_mineral = parseFloat($(`#totalMineral_${index}`).val());

    if (total_mineral < 0) {
        document.getElementById("guardarDatos").disabled = true;
        // Mostrar alerta con SweetAlert
        Swal.fire({
            title: '¡Atención!',
            text: `Ingrea un valor que no sea negativo.`,
            icon: 'warning',
            confirmButtonText: 'Aceptar'
        });
    }
    // Calcular la diferencia
    var diferencia =Math.round(total_calculado - total_mineral,2);
    

    // Verificar si la diferencia es mayor o igual a 5
    if (diferencia > 1) {
        document.getElementById("guardarDatos").disabled = true;
        // Mostrar alerta con SweetAlert
        Swal.fire({
            title: '¡Atención!',
            text: `Total calculado excede en: ${diferencia}. al registrado por planta`,
            icon: 'warning',
            confirmButtonText: 'Aceptar'
        });
    }
    else {
        document.getElementById("guardarDatos").disabled = false;
    }
}


function calcularTotalMineral(element) {
    debugger;
    // Obtener el índice del lote desde el ID del elemento
    var id = $(element).attr('id');
    var index = id.split('_')[1];

    // Obtener los valores de los campos relacionados
    var ley = parseFloat($(`#ley_${index}`).val()) || 0;
    var recuperacion = parseFloat($(`#recuperacion_${index}`).val()) || 0;
    var precio_internacional = parseFloat($(`#precio_internacional_${index}`).val()) || 0;
    var descuento_internacional = parseFloat($(`#descuento_internacional_${index}`).val()) || 0;

    var maquila = parseFloat($(`#maquila_${index}`).val()) || 0;
    var penalidad = parseFloat($(`#penalidad_${index}`).val()) || 0;
    var flete = parseFloat($(`#flete_${index}`).val()) || 0;
    
    var merma = parseFloat($(`#merma_${0}`).val()) || 0;
    var tms = parseFloat($(`#tms_${0}`).val()) || 0;
    

    // PAGABLES
     var tempOnzas=tms*ley*(recuperacion/100);
     var totalOnzas=Math.round(tempOnzas*1000)/1000;
     var cotizacion=precio_internacional-descuento_internacional;

     var totalPagable=totalOnzas*cotizacion;

     //DEDUCIBLES
     var maquila = (maquila) * (tms);
     var penalidad = (penalidad) * (tms);
     var transporte = (flete) * (tms);

     var totalDeducible = (maquila) + (penalidad) + (transporte);

     var totalConDescuento = (totalPagable) - (totalDeducible);

     var result = 0;
     result = (totalConDescuento)* 1.1023;
     var totalCalculado= Math.round((result) * 1000) / 1000;

    $(`#totalCalculado_${index}`).val(totalCalculado.toFixed(3));


}

function guardarLotes() {
    loaderShow();
    debugger;
    let allLotesData = [];

    // Recorre todos los lotes
    document.querySelectorAll('.panel.panel-1').forEach((panel) => {
        let loteData = {};

        // Obtén el id del lote y el índice
        let panelId = panel.id;
        if (panelId && panelId.startsWith('lote_')) {
            debugger;
            let loteIndex = panelId.split('_')[1];
            console.log(`Procesando lote con índice ${loteIndex}`);

            // Recolecta los datos generales del lote
            loteData.file = panel.querySelector(`#secretImg_${loteIndex}`)?.value || '';
            loteData.name = panel.querySelector(`#secretNameFirma_${loteIndex}`)?.value || '';
            loteData.id = panel.querySelector(`#id_${loteIndex}`)?.value || '';
            loteData.solicitud_id = panel.querySelector(`#solicitudId_${loteIndex}`)?.value || '';
            loteData.lote = panel.querySelector(`#lote_${loteIndex}`)?.value || '';
            loteData.tmh = panel.querySelector(`#tmh_${loteIndex}`)?.value || '';
            loteData.porcentagua = panel.querySelector(`#porcentagua_${loteIndex}`)?.value || '';
            loteData.merma = panel.querySelector(`#merma_${loteIndex}`)?.value || '';
            loteData.tms = panel.querySelector(`#tms_${loteIndex}`)?.value || '';
            loteData.total = panel.querySelector(`#total_${loteIndex}`)?.value || '';

            // Recolecta los datos de los minerales asociados al lote
            let mineralPanels = panel.querySelectorAll('.panelMineral'); // Seleccionamos todos los paneles de mineral
            let mineralesData = [];

            mineralPanels.forEach((mineralPanel, mineralIndex) => {
                debugger;
                let mineralData = {
                    tipo_mineral2: mineralPanel.querySelector(`#cboTipoArchivo_${2}`)?.value || '',
                    tipo_mineral: mineralPanel.querySelector(`#cboTipoArchivo_${mineralIndex}`)?.value || '',
                    ley: mineralPanel.querySelector(`#ley_${mineralIndex}`)?.value || '',
                    unidad: mineralPanel.querySelector(`#unidad_${mineralIndex}`)?.value || '',
                    recuperacion: mineralPanel.querySelector(`#recuperacion_${mineralIndex}`)?.value || '',
                    precio_internacional: mineralPanel.querySelector(`#precio_internacional_${mineralIndex}`)?.value || '',
                    descuento_internacional: mineralPanel.querySelector(`#descuento_internacional_${mineralIndex}`)?.value || '',
                    maquila: mineralPanel.querySelector(`#maquila_${mineralIndex}`)?.value || '',
                    penalidad: mineralPanel.querySelector(`#penalidad_${mineralIndex}`)?.value || '',
                    flete: mineralPanel.querySelector(`#flete_${mineralIndex}`)?.value || '',
                    total_mineral: mineralPanel.querySelector(`#totalMineral_${mineralIndex}`)?.value || '',
                    total_mineral_calculado: mineralPanel.querySelector(`#totalCalculado_${mineralIndex}`)?.value || ''
                };
                
                mineralesData.push(mineralData); // Agregar el mineral a la lista
            });

            loteData.minerales = mineralesData; // Guardamos todos los minerales para este lote

            allLotesData.push(loteData); // Agregamos los datos del lote a la lista de todos los lotes
        }
    });

    // Aquí puedes verificar los datos antes de enviarlos
    console.log('Datos a enviar:', allLotesData);

    // Convertir la lista a JSON y enviarla
    ax.setAccion("registrarResultadosLote");
    ax.addParamTmp("data", allLotesData);
    ax.consumir();
}



// function guardarLotes(){
//     debugger;
//     var formData = $("form").serialize(); 
//     ax.setAccion("deleteSolicitud");
// }

function formularioResultados(id) {
    loaderShow(null);
    commonVars.personaId = id;
    cargarDiv("#window", "vistas/com/entregaResultados/entrega_resultados_form.php", "Entrega resultados");
}

function onResponsellenarcomboTipoArchivo(data) {
    arraycomboTipoArchivo= data;
     
    $('#cboTipoArchivo').empty();
    
    $.each(data, function(index, item) {
        $('#cboTipoArchivo').append(new Option(item.id_estado, item.id));
 
    });
    $('#cboTipoArchivo').trigger('change'); // Actualizar select2
   
}

function deleteSolicitud(id) {
    loaderShow();
    ax.setAccion("deleteSolicitud");
    ax.addParamTmp("id", id);
    ax.consumir();
}

function cambiarEstadoPersona(id) {
    ax.setAccion("cambiarEstadoPersona");
    ax.addParamTmp("id", id);
    ax.consumir();
}
function onResponseCambiarEstadoPersona(data) {
    if (data[0]["vout_exito"] == 1) {
        $.Notification.autoHideNotify('success', 'top-right', '&Eacute;xito', data[0]["vout_mensaje"]);
    }
    else {
        $.Notification.autoHideNotify('warning', 'top right', 'Validación', data[0]["vout_mensaje"] + "No se puede cambiar de estado.");
    }
}
function obtenerTitulo(dependiente) {
    tituloGlobal = $("#titulo").text();
    var titulo = tituloGlobal;
    if (dependiente == 0) {
        $("#window").empty();
    }
    if (!isEmpty(titulo)) {
        titulo = titulo.toLowerCase();
    }
    return titulo;
}

function cargarListarPersonaCancelar() {
    loaderShow(null);
    cargarDiv('#window', 'vistas/com/persona/persona_listar.php', tituloGlobal);
}

function buscarPersona(colapsa) {
    debugger;
    buscar = true;
    var cadena;
    cadena = obtenerDatosBusqueda();
    cadena = (!isEmpty(cadena) && cadena !== 0) ? cadena : "Todos";
    $('#idPopover').attr("data-content", cadena);
    $('[data-toggle="popover"]').popover('show');
    obtenerParametrosBusqueda();
    listarPersona();
    if (colapsa === 1)
        colapsarBuscadorPersona();
}

var actualizandoBusquedaPersona = false;

function colapsarBuscadorPersona() {
    debugger;
    if (actualizandoBusquedaPersona) {
        actualizandoBusquedaPersona = false;
        return;
    }
    if ($('#bg-info').hasClass('in')) {
        $('#bg-info').attr('aria-expanded', "false");
        $('#bg-info').attr('height', "0px");
        $('#bg-info').removeClass('in');
    } else {
        $('#bg-info').attr('aria-expanded', "false");
        $('#bg-info').attr('height', "0px");
        $('#bg-info').addClass('in');
    }
}
function obtenerDatosBusqueda() {
    debugger;
    var cadena = "";
    var nombres = $("#txtNombresBusqueda").val();
    var codigo = $("#txtCodigoBusqueda").val();
    var tipoPersona = $("#cboTipoPersonaBusqueda").val();
    var clasePersona = $("#cboClasePersonaBusqueda").val();


    if (!isEmpty(codigo)) {
        cadena += StringNegrita("Cód. Id.: ");

        cadena += codigo;
        cadena += "<br>";
    }
    if (!isEmpty(nombres)) {
        cadena += StringNegrita("Nombre: ");

        cadena += nombres;
        cadena += "<br>";
    }
    if (tipoPersona != -1) {
        cadena += StringNegrita("Tipo de persona: ");

        cadena += select2.obtenerText('cboTipoPersonaBusqueda');
        cadena += "<br>";
    }
    if (!isEmpty(clasePersona)) {
        cadena += StringNegrita("Clase de persona: ");
        cadena += select2.obtenerTextMultiple('cboClasePersonaBusqueda');
        cadena += "<br>";
    }
    return cadena;
}
function editarPersona(id) {
    debugger;
    loaderShow(null);
    commonVars.personaId = id;
    cargarDiv("#window", "vistas/com/solicitudRetiro/solicitud_retiro_form.php", "Editar Solicitud ");
}
function actualizarBusquedaPersona() {
    actualizandoBusquedaPersona = true;
    //    var estadobuscador = $('#bg-info').attr("aria-expanded");
    //    if (estadobuscador == "false")
    //    {
    buscarPersona(0);
    //    }
}
/*IMPORTAR EXCEL*/
$(function () {
    $(":file").change(function () {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = imageIsLoaded;
            reader.readAsDataURL(this.files[0]);
            //            $fileupload = $('#file');
            //            $fileupload.replaceWith($fileupload.clone(true));
        }
    });
});

function imageIsLoaded(e) {
    $('#secretFile').attr('value', e.target.result);
    importPersona();
}

function validarFormularioCarga(documento) {
    var bandera = true;
    var espacio = /^\s+$/;

    if (documento === "" || documento === null || espacio.test(documento) || documento.length === 0) {
        $("#lblDoc").text("Documento es obligatorio").show();
        bandera = false;
    }
    return bandera;
}
function getAllEmpresaImport() {
    ax.setAccion("getAllEmpresa");
    ax.consumir();
}
/*FIN IMPORTAR EXCEL*/

function importPersona() {
    getAllEmpresaImport();
    $('#resultado').empty();
    $('#btnImportar').show();
    $('#btnSalirModal').empty();
    $('#btnSalirModal').append("<i class='fa ion-android-close'>&nbsp;&nbsp;</i>Cancelar");
    $("#cboEmpresa").attr("disabled", false);
    asignarValorSelect2('cboEmpresa', "");
    $('#modalPersona').modal('show');
}

function importar() {
    var file = document.getElementById('secretFile').value;
    var empresa = $('#cboEmpresa').val();

    if (isEmpty(empresa)) {
        $("msj_empresa").removeProp(".hidden");
        $("#msj_empresa").text("Seleccionar una empresa").show();
        return;
    }

    $('#resultado').empty();
    $('#btnImportar').hide();
    $('#btnSalirModal').empty();
    $('#btnSalirModal').append("<i class='fa ion-android-close'>&nbsp;&nbsp;</i>Salir");
    $("#cboEmpresa").attr("disabled", true);

    loaderShow(".modal-content");
    ax.setAccion("importPersona");
    ax.addParam("file", file);
    ax.addParam("empresa_id", empresa);
    ax.consumir();
}
function onResponseGetAllEmpresas(data) {

    if (!isEmpty(data)) {
        $('#cboEmpresa').empty();
        $.each(data, function (index, value) {
            $('#cboEmpresa').append('<option value="' + value.id + '">' + value.razon_social + '</option>');
        });
    }
    acciones.getEmpresa = true;
}

function asignarValorSelect2(id, valor) {
    $("#" + id).select2().select2("val", valor);
    $("#" + id).select2({ width: '100%' });
}

$('.dropdown-menu').click(function (e) {
    if (e.target.id != "btnBusqueda" && e.delegateTarget.id != "ulBuscadorDesplegable2" && e.delegateTarget.id != "listaEmpresa") {
        e.stopPropagation();
    }
});

$('#txtBuscar').keyup(function (e) {
    var bAbierto = $('#txtBuscar').attr("aria-expanded");

    if (!eval(bAbierto)) {
        $('#txtBuscar').dropdown('toggle');
    }

});

function cambiarAnchoBusquedaDesplegable() {
    var ancho = $("#divBuscador").width();
    $("#ulBuscadorDesplegable").width((ancho - 5) + "px");
    $("#ulBuscadorDesplegable2").width((ancho - 5) + "px");
}

function obtenerParametrosBusqueda() {
    nombres = $("#txtNombresBusqueda").val();
    codigo = $("#txtCodigoBusqueda").val();
    tipoPersona = $("#cboTipoPersonaBusqueda").val();
    clasePersona = $("#cboClasePersonaBusqueda").val();
}

function llenarParametrosBusqueda(nombresTxt, codigoTxt, idTxt, clasePersonaTxt) {
    debugger;
    var clasePersonaIds = [];
    if (!isEmpty(clasePersonaTxt)) {
        clasePersonaIds.push(clasePersonaTxt);
    }

    if (!isEmpty(codigoTxt)) {
        nombresTxt = null;
    }

    nombres = nombresTxt;
    codigo = codigoTxt;
    id = idTxt;
    clasePersona = clasePersonaIds;

    loaderShow();
    listarPersona();
}

function buscarCriteriosBusquedaSolicitud() {
    //    loaderShow();
    ax.setAccion("buscarCriteriosBusquedaSolicitud");
    ax.addParamTmp("busqueda", $('#txtBuscar').val());
    ax.consumir();
}

function onResponseBuscarCriteriosBusquedaSolicitud(data) {
    debugger;
    var dataPersona = data.dataPersona;
    var dataPersonaClase = data.dataPersonaClase;

    var html = '';
    $('#ulBuscadorDesplegable2').empty();
    if (!isEmpty(dataPersona)) {
        $.each(dataPersona, function (index, item) {
            html += '<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">';
            html += '<a onclick="llenarParametrosBusqueda(\'' + item.nombre + '\',\'' + item.codigo_identificacion + '\',' + item.id + ',' + null + ')" >';
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



    $("#ulBuscadorDesplegable2").append(html);

    //    console.log(dataPersona);
}

function limpiarBuscadores() {
    $('#txtCodigoBusqueda').val('');
    $('#txtNombresBusqueda').val('');

    select2.asignarValor('cboClasePersonaBusqueda', -1);
    select2.asignarValor('cboTipoPersonaBusqueda', -1);
}