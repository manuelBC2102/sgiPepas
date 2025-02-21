var bandera_getMenu = false;
var bandera_insertaropciones = false;
var bandera_actualizar = false;
var bandera_getCombo = false;
var bandera_eliminar = false;
var c = $('#env i').attr('class');
var dataGetPerfil = '';
var id = null;
var acciones = {
    getMenu: false,
//      tipoMovimiento:false,
    getPerfil: false
};

$("#estado").change(function () {
    $('#msj_estado').hide();
});

function clickOpcion()
{
    $('#msj_opcion').hide();
}
function clickPantallaPrincipal()
{
    $('#msj_opcion').hide();
}
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
function cambiarEstado(id_estado)
{
    ax.setAccion("cambiarEstado");
    ax.addParamTmp("id_estado", id_estado);
    ax.consumir();
}
function cambiarIconoEstado(data)
{
    document.getElementById(data[0].id_estado).className = data[0].icono;
    document.getElementById(data[0].id_estado).style.color = data[0].color;
    $.Notification.autoHideNotify('success', 'top-right', '&Eacute;xito', 'Estado actualizado');
}

$('#codigo').keypress(function () {
    $('#msj_codigo').hide();
});

$('#descripcion').keypress(function () {
    $('#msj_desc').hide();
});
function cargarComponentes()
{
    cargarSelect2();
}
function cargarSelect2()
{
    $(".select2").select2({
        width: '100%'
    });
}
function asignarValorSelect2(id, valor)
{
    $("#" + id).select2().select2("val", valor);
    $("#" + id).select2({width: '100%'});
}
function validar_perfil_form()
{
    var bandera = true;
    var descripcion = document.getElementById("descripcion").value;
    var estado = document.getElementById('estado').value;
    //validar el menu 
    var opcion = document.getElementsByName('opcion');
    var tamO = opcion.length;
    var opcionT = new Array();
    var opcionM = new Array();
    var m = 0;
    for (var l = 0; l < tamO; l++)
    {
        opcionT[l] = opcion[l].value;
        if (opcion[l].checked == true)
        {
            var id_opcion = opcion[l].value;
            opcionM[m] = id_opcion;
            m++;
        }
    }
    ///// fin de captura de datos del menu
    //para la pantalla principal
    var rdpan_principal = document.getElementsByName("rdpantalla");
    var pant_principal = null;
    var tamanio = rdpan_principal.length;
    for (var i = 0; i < tamanio; i++)
    {
        var id_opcion = rdpan_principal[i].value;
        if (rdpan_principal[i].checked)
        {
            pant_principal = id_opcion;
        }
    }
    ////fin de datos para la pantalla principal


    var espacio = /^\s+$/;
    //validar pantalla principal
//    if (pant_principal == null || pant_principal == '') {
//        $("#msj_opcion").removeProp("hidden");
//        $("#msj_opcion").text("Seleccionar acceso a opciones pantalla principal").show();
//        bandera = false;
//    }
    if (opcionM.length == 0) {
        $("#msj_opcion").removeProp("hidden");
        $("#msj_opcion").text("Seleccionar acceso a opciones del menu").show();
        bandera = false;
    }
    if (descripcion == "" || espacio.test(descripcion) || descripcion.lenght == 0 || descripcion == null)
    {
        $("msj_desc").removeProp(".hidden");
        $("#msj_desc").text("Ingresar una descripción").show();
        bandera = false;
    }
    if (estado == "" || espacio.test(estado) || estado.lenght == 0 || estado == null)
    {
        $("msj_estado").removeProp(".hidden");
        $("#msj_estado").text("Seleccionar un estado").show();
        bandera = false;
    }   
    
    
    var personaClase = $('#cboClasePersona').val();
    if (isEmpty(personaClase))
    {
        $("#msjClasePersona").removeProp(".hidden");
        $("#msjClasePersona").text("Seleccionar una clase de persona").show();
        bandera = false;
    }
    
    return bandera;
}

function listar_perfil() {
    $("span.help-block").hide();
//    breakFunction();
    ax.setSuccess("successPerfil");
    ax.setAccion("getDataGridPerfil");
    ax.consumir();
}

function guardarPerfil(tipo)
{
    var id_perfil = document.getElementById("id_perfil").value;
    var nombre = document.getElementById("descripcion").value;
    var descripcion = document.getElementById("comentario").value;
    var estado = document.getElementById("estado").value;
    var usuario = document.getElementById("usuario").value;
//    var dashboard = document.getElementById("chk_dashboard").checked;
    var dashboard = false;
//    var email = document.getElementById("chk_email").checked;
    var email =false;
//    var monetaria = document.getElementById("chk_monetaria").checked;
    var monetaria = false;

    var opcion = document.getElementsByName('opcion');

    var tamO = opcion.length;
    var opcionT = new Array();
    var opcionM = new Array();
    var m = 0;
    for (var l = 0; l < tamO; l++)
    {
        opcionT[l] = opcion[l].value;
        if (opcion[l].checked == true)
        {
            var id_opcion = opcion[l].value;
            opcionM[m] = id_opcion;
            m++;
        }
    }

    var opcionTipoMovimiento = document.getElementsByName('opcionMovimiento');
    var tamOpcionM = opcionTipoMovimiento.length;
    var totalOpcionesMovimiento = new Array();
    var totalMovimientoSeleccionado = new Array();
    var mm = 0;
    for (var ll = 0; ll < tamOpcionM; ll++)
    {
        totalOpcionesMovimiento[ll] = opcionTipoMovimiento[ll].value;
        if (opcionTipoMovimiento[ll].checked == true)
        {
            var id_opcionTM = opcionTipoMovimiento[ll].value;
            totalMovimientoSeleccionado[mm] = id_opcionTM;
            mm++;
        }
    }
    if (dashboard)
    {
        dashboard = "1";
    } else
    {
        dashboard = "0";
    }
    if (email)
    {
        email = "1";
    } else
    {
        email = "0";
    }
    if (monetaria)
    {
        monetaria = "1";
    } else
    {
        monetaria = "0";
    }

    var rdpan_principal = document.getElementsByName("rdpantalla");
    var pant_principal = null;
    var tamanio = rdpan_principal.length;
    for (var i = 0; i < tamanio; i++)
    {
        var id_opcion = rdpan_principal[i].value;
        if (rdpan_principal[i].checked)
        {
            pant_principal = id_opcion;
        }
    }    
    
    var personaClase = $('#cboClasePersona').val();
    
    if (tipo == 1)
    {
        updatePerfil(id_perfil, nombre, descripcion, estado, dashboard, email, monetaria, pant_principal, totalOpcionesMovimiento, 
            totalMovimientoSeleccionado,personaClase);
        updateDetOpcPerfil(id_perfil);
    } else {
        insertPerfil(nombre, descripcion, estado, usuario, dashboard, email, monetaria, pant_principal, opcionT, 
            opcionM, totalOpcionesMovimiento, totalMovimientoSeleccionado,personaClase);
    }
}
function successPerfil(response) {
    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'getDataGridPerfil':
                onResponseAjaxpGetDataGridPerfiles(response.data);
                $('#datatable').dataTable({
                    "scrollX": true,
                    "autoWidth": true,
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
                break;
            case 'insertPerfil':
                exitoInsert(response.data);
                break;
            case 'getPerfil':
                acciones.getPerfil = true;
                dataGetPerfil = response.data;
                verificarCargarComplemento();
//                llenarFormularioEditar(response.data);
                break;
            case 'getMenu':
                acciones.getMenu = true;
//                if (bandera_getMenu == false)
//                {
                onResponseAjaxpGetOpcionesMenu(response.data);
                verificarCargarComplemento();
//                    bandera_getMenu = true;
//                    if (response.data[0].id_perfil != null)
//                    {
//                        getPerfil(response.data[0].id_perfil);
//                    }
//                }
//                loaderClose();
                break;
            case 'obterTipoMovimiento':
                acciones.tipoMovimiento = true;
                onResponseAjaxpGetTipoMovimiento(response.data);
                verificarCargarComplemento();
//                    if (response.data[0].id_perfil != null)
//                    {
//                        getPerfil(response.data[0].id_perfil);
//                    }
//                loaderClose();
                break;
            case 'updatePerfil':
                exitoUpdate(response.data);
                break;
            case 'deletePerfil':
                var error = response.data['0'].vout_exito;
                if (error > 0) {
                    swal("Eliminado!", " " + response.data['0'].nombre + ".", "success");
                } else {
                    swal("Cancelado", "El perfil " + response.data['0'].nombre + " " + response.data['0'].vout_mensaje, "error");
                }
                bandera_eliminar = true;
                break;
            case 'cambiarEstado':
                cambiarIconoEstado(response.data);
                break;
            case 'updateDetOpcPerfil':
                loaderClose();
                break;
            case 'obtenerPersonaClase':
                onResponseObtenerPersonaClase(response.data);
                break;
        }
    }
}

function verificarCargarComplemento()
{
//    console.log(dataGetPerfil,acciones);
    
    if (acciones.getMenu && acciones.getPerfil)
    {
        if (dataGetPerfil == '' || dataGetPerfil == null || dataGetPerfil.length == 0)
        {
            loaderClose();
        } else
        {
            llenarFormularioEditar(dataGetPerfil);
//            loaderClose();
//            $("#txtDescripcion").val(dataObtenerDocumentoTipoDatoLista['0']['descripcion']);
//            $("#txtValor").val(dataObtenerDocumentoTipoDatoLista['0']['valor']);
//            asignarValorSelect2("cboDocumentoTipoDato", dataObtenerDocumentoTipoDatoLista['0']['documento_tipo_dato_id']);
//            asignarValorSelect2("cboEstado", dataObtenerDocumentoTipoDatoLista['0']['estado']);
//            loaderClose();
        }
    }
}
function exitoUpdate(data)
{
    if (data[0]["vout_exito"] == 0)
    {
        habilitarBoton();
        loaderClose();
        $.Notification.autoHideNotify('warning', 'top right', 'Validación', data[0]["vout_mensaje"]);
    }
    else
    {
        habilitarBoton();
        loaderClose();
        $.Notification.autoHideNotify('success', 'top-right', '&Eacute;xito', data[0]["vout_mensaje"]);
        cargarPantallaListar();
    }
}

function exitoInsert(data)
{
    if (data[0]["vout_exito"] == 0)
    {
        habilitarBoton();
        loaderClose();
        $.Notification.autoHideNotify('warning', 'top right', 'Validación', data[0]["vout_mensaje"]);
    }
    else
    {
        habilitarBoton();
        loaderClose();
        $.Notification.autoHideNotify('success', 'top-right', '&Eacute;xito', data[0]["vout_mensaje"]);
        cargarPantallaListar();
    }
}
function onResponseAjaxpGetDataGridPerfiles(data) {
    $("#datatable2").empty();
    var cuerpo_total = '';
    var cuerpo = '';

    var cabeza = '<table id="datatable" class="table table-striped table-bordered"><thead>' +
            " <tr>" +
            "<th style='text-align:center;'>Nombre</th>" +
            "<th style='text-align:center;'>Clases a visualizar</th>" +
            "<th style='text-align:center;' width=140px>Estado</th>" +
            "<th style='text-align:center;' width=140px>Acciones</th>" +
            "</tr>" +
            "</thead>";
    $.each(data, function (index, item) {

        cuerpo = "<tr>" +
                "<td style='text-align:left;'>" + item.nombre + "</td>" +
                "<td style='text-align:left;'>" + item.persona_clase_descripcion + "</td>" +
                "<td style='text-align:center;'><a href='#' onclick = 'cambiarEstado(" + item.id + ")' ><b><i id='" + item.id + "' class='" + item.icono + "' style='color:" + item.color + ";'></i><b></a></td>" +
                "<td style='text-align:center;'>" +
                "<a data-toggle='reload' href='#' onclick='cargarDivGetPerfil(" + item.id + ")'><b><i class='fa fa-edit'  style='color:#E8BA2F;cursor:hand;'></i><b></a>&nbsp;\n" +
                "<a href='#' onclick='confirmarDeletePerfil(" + item.id + ", \"" + item.nombre + "\")'><i class='fa fa-trash-o' style='color:#cb2a2a;'></i></a>" +
                "</td>" +
                "</tr>";
        cuerpo_total = cuerpo_total + cuerpo;
    });
    var pie = '</table>';
    var html = cabeza + cuerpo_total + pie;
    $("#datatable2").append(html);
    
    modificarAnchoTabla('datatable');
}

function insertPerfil(nombre, descripcion, estado, usuario, dashboard, email, monetaria, pant_principal, opcionT, opcionM,
        totalOpcionesMovimiento, totalMovimientoSeleccionado,personaClase){
            
    if (validar_perfil_form()) {
        loaderShow(null);
        deshabilitarBoton();
        ax.setAccion("insertPerfil");
        ax.addParamTmp("descripcion", nombre);
        ax.addParamTmp("comentario", descripcion);
        ax.addParamTmp("estado", estado);
        ax.addParamTmp("usuario", usuario);
        ax.addParamTmp("dashboard", dashboard);
        ax.addParamTmp("email", email);
        ax.addParamTmp("monetaria", monetaria);
        ax.addParamTmp("pant_principal", pant_principal);
        ax.addParamTmp("opcionT", opcionT);
        ax.addParamTmp("opcionM", opcionM);
        ax.addParamTmp("totalOpcionesMovimiento", totalOpcionesMovimiento);
        ax.addParamTmp("totalMovimientoSeleccionado", totalMovimientoSeleccionado);
        ax.addParamTmp("personaClase", personaClase);
        ax.consumir();
    }
    
}
function updatePerfil(id_perfil, nombre, descripcion, estado, dashboard, email, monetaria, pant_principal, totalOpcionesMovimiento, 
    totalMovimientoSeleccionado,personaClase)
{
    if (validar_perfil_form()) {
        loaderShow(null);
        deshabilitarBoton();
        ax.setAccion("updatePerfil");
        ax.addParamTmp("id_perfil", id_perfil);
        ax.addParamTmp("descripcion", nombre);
        ax.addParamTmp("comentario", descripcion);
        ax.addParamTmp("estado", estado);
        ax.addParamTmp("dashboard", dashboard);
        ax.addParamTmp("email", email);
        ax.addParamTmp("monetaria", monetaria);
        ax.addParamTmp("pant_principal", pant_principal);
        ax.addParamTmp("totalOpcionesMovimiento", totalOpcionesMovimiento);
        ax.addParamTmp("totalMovimientoSeleccionado", totalMovimientoSeleccionado);
        ax.addParamTmp("personaClase", personaClase);
        ax.consumir();
    }
}

function getPerfil()
{
    var id_perfil = document.getElementById('id_perfil').value;
    if (!isEmpty(id_perfil)) {
        loaderShow();
        ax.setAccion("getPerfil");
        ax.addParamTmp("id_perfil", id_perfil);
        ax.consumir();
    }
}
function getIdUltimoPerfil(data)
{
    return data[0].id;
}
function llenarFormularioEditar(data)
{
//    document.getElementById('codigo').value = data[0].codigo;
    document.getElementById('descripcion').value = data[0].nombre;
    document.getElementById('comentario').value = data[0].comentario;
    asignarValorSelect2('estado', data[0].pestado);
//    document.getElementById('estado').value = data[0].pestado;
//    if (data[0].bandera_monetaria == 1)
//        document.getElementById("chk_monetaria").checked = true;
//    else
//        document.getElementById("chk_monetaria").checked = false;

//    if (data[0].bandera_email == 1)
//        document.getElementById("chk_email").checked = true;
//    else
//        document.getElementById("chk_email").checked = false;
//
//    if (data[0].bandera_dashboard == 1)
//        document.getElementById("chk_dashboard").checked = true;
//    else
//        document.getElementById("chk_dashboard").checked = false;
    $.each(data, function (index, item) {
        if (item.opcion_id == item.pantalla_principal)
        {
            var id_rd = "#r" + item.opcion_id.toString();
            $(id_rd).prop("checked", true);
        }
        if (item.estado == 1)
        {
            var id = "#" + item.opcion_id.toString();
            $(id).prop("checked", true);
        }
    });


    var tipoMovimiento = data[0].tipo_movimiento_id; //Original Text

    var dataTipoMovimiento = "";
    if (!isEmpty(tipoMovimiento))
    {
        dataTipoMovimiento = tipoMovimiento.split(';');
    }


    for (var i = 0; dataTipoMovimiento.length > i; i++)
    {
        var id_rdm = "#tm" + dataTipoMovimiento[i].toString();

        $(id_rdm).prop("checked", true);
    }

    if (data[0]['id'] == data[0]['id_per_sesion'])
    {
//        document.getElementById('estado').disabled = true;
        $('#estado').select2('disable', true);
    }
        
    if (!isEmpty(data[0].persona_clase_ids))
    {
        select2.asignarValor('cboClasePersona', data[0].persona_clase_ids.split(";"));
    }
    
    loaderClose();
}

function deletePerfil(id_perfil, nom)
{
    ax.setAccion("deletePerfil");
    ax.addParamTmp("id_perfil", id_perfil);
    ax.addParamTmp("nom", nom);
    ax.consumir();
    cargarPantallaListar();
}

function confirmarDeletePerfil(id, nom) {
    bandera_eliminar = false;
    swal({
        title: "Est\xe1s seguro?",
        text: "Eliminarás " + nom + "!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#33b86c",
        confirmButtonText: "Si,eliminar!",
        cancelButtonColor: '#d33',
        cancelButtonText: "No,cancelar!",
        closeOnConfirm: false,
        closeOnCancel: false
    }, function (isConfirm) {
        if (isConfirm) {
            deletePerfil(id, nom);
        } else {
            if (bandera_eliminar == false)
            {
                swal("Cancelado", "La eliminaci\xf3n fue cancelada", "error");
            }
        }
    });
}

function cargarMenu(id_perfil) {
    ax.setAccion("getMenu");
    ax.addParamTmp("id_perfil", id_perfil);
    ax.consumir();
}
function cargarTipoMovimientoMenu() {
    ax.setAccion("obterTipoMovimiento");
    ax.consumir();
}

function onResponseAjaxpGetOpcionesMenu(data) {
//    console.log(data);
    
    $("#opcionp").empty();
    var cuerpo_total = '';
//    var cuerpoh;
    var cuerpop = '';
    var cuerpop_total = '';

    var cabeza = '<table class="table table-striped table-bordered">';
    var pie = '</table>';

    $.each(data, function (index, item) {
        var cuerpoh_total = '';
        var cuerpoh = '';
        var cuerpoh_total = '';
        cuerpop =
                ' <tr>' +
                '<th width=270>' + item.nombre + '</th>' +
                '<th width=270>Pantalla Prinicipal</th>' +
                '</tr>';
        if (!isEmpty(item.hijo)) {
            $.each(item.hijo, function (indexh, itemh) {
                cuerpoh = '<tbody>' +
                        '<tr>' +
                        '<td width=270><div class="checkbox">' +
                        '<label class="cr-styled">' +
                        '<input onclick="clickOpcion();" type="checkbox" name="opcion" id="' + itemh.id + '" value="' + itemh.id + '">' +
                        '<i class="fa"></i> ' +
                        itemh.nombre +
                        '</label>' +
                        ' </div></td>' +
                        '<td width=270>' +
                        ' <div class="radio">' +
                        '<label class="cr-styled">' +
                        '<input onclick="clickPantallaPrincipal();" type="radio" id="r' + itemh.id + '" name="rdpantalla" value="' + itemh.id + '"> ' +
                        '<i class="fa"></i> ' +
                        '</label>' +
                        '</div>' +
                        '</td>' +
                        '</tr>' +
                        '</tbody>';
                cuerpoh_total = cuerpoh_total + cuerpoh;
            });
        }
        cuerpop_total = cuerpop_total + cuerpop + cuerpoh_total;
    });
    cuerpo_total = cabeza + cuerpop_total + pie;
    $("#opcionp").append(cuerpo_total);
//    cargarTipoMovimientoMenu();
}


function onResponseAjaxpGetTipoMovimiento(data) {
    $("#opcionp").empty();
    var cuerpo_total = '';
//    var cuerpoh;
    var cuerpop = '';
    var cuerpop_total = '';

    var cabeza = '<table class="table table-striped table-bordered">';
    var pie = '</table>';

    var cuerpoh_total = '';
    var cuerpoh = '';
    var cuerpoh_total = '';
    cuerpop =
            ' <tr>' +
            '<th width=270>' + "Tipo de movimiento" + '</th>' +
//                '<th width=270>Pantalla Prinicipal</th>' +
            '</tr>';
    $.each(data, function (index, item) {
        cuerpoh = '<tbody>' +
                '<tr>' +
                '<td width=270><div class="checkbox">' +
                '<label class="cr-styled">' +
                '<input type="checkbox" name="opcionMovimiento" id="tm' + item.id + '" value="' + item.id + '">' +
                '<i class="fa"></i> ' +
                item.descripcion +
                '</label>' +
                ' </div></td>' +
                '</tr>' +
                '</tbody>';
        cuerpoh_total = cuerpoh_total + cuerpoh;
    });

    cuerpop_total = cuerpop_total + cuerpop + cuerpoh_total;

    cuerpo_total = cabeza + cuerpop_total + pie;

    $("#opcionp").append(cuerpo_total);
}


function cancelarAsignarOpciones(id)
{
    $("#opcionp").empty();
    cargarMenu(id);
//    cargarTipoMovimientoMenu();
//    obterTipoMovimiento(id);
    bandera_getMenu = false;
}

var opcionEdicion=[];
function updateDetOpcPerfil(id_per)
{
    if (validar_perfil_form()) {
        deshabilitarBoton();
        var opcion = document.getElementsByName('opcion');        
        var opcionDetalle=null;

        for (var i = 0; i < opcion.length; i++)
        {
            var estado = 0;
            var id_opcion = opcion[i].value;
            if (opcion[i].checked)
            {
                estado = "1";
            }       
            opcionDetalle={opcionId: id_opcion,estado: estado};
            
            opcionEdicion.push(opcionDetalle);            
        }   
        
        loaderShow();
        ax.setAccion("updateDetOpcPerfil");
        ax.addParamTmp("id_per", id_per);
        ax.addParamTmp("opcionEdicion", opcionEdicion);
//        ax.addParamTmp("estado", estado);
        ax.consumir();
    }
}

function cargarDivGetPerfil(id) {

    cargarDivTitulo("#window", "vistas/com/perfil/perfil_form.php?id=" + id + "&" + "tipo=" + 1, "Editar " + obtenerTitulo());
}

function nuevo()
{
    cargarDivTitulo('#window', 'vistas/com/perfil/perfil_form.php', "Nuevo " + obtenerTitulo());
}

function obtenerTitulo()
{
    tituloGlobal = $("#titulo").text();
    var titulo = tituloGlobal;
    $("#window").empty();

    if (!isEmpty(titulo))
    {
        titulo = titulo.toLowerCase();
    }
    return titulo;
}

function cargarPantallaListar()
{
    loaderShow(null);
    cargarDivTitulo("#window", "vistas/com/perfil/perfil_listar.php", tituloGlobal);
}

// persona clase
function obtenerPersonaClase(){
    ax.setAccion("obtenerPersonaClase");
    ax.consumir();
}

function onResponseObtenerPersonaClase(data){
    select2.cargar("cboClasePersona", data, "id", "descripcion");
}