var c = $('#env i').attr('class');
var unidad_controlID = 0;
var bandera_eliminar = false;
var bandera_getCombo = false;
var acciones = {
    getTipoBien: false,
    getEmpresa: false,
    getTipoUnidad: false
};

$(document).ready(function () {
    datePiker.iniciarPorClase('fecha');

    controlarDomXTipoBien();
    ax.setAccion("obtenerConfiguracionesIniciales");
    ax.addParamTmp("bienId", commonVars.bienId);
    ax.addParamTmp("bienTipoId", commonVars.bienTipoId);
    ax.addParamTmp("empresaId", commonVars.empresa);
    ax.consumir();

    listarComboProveedores();
    cargarSelect2();
});

function controlarDomXTipoBien() {
    if (commonVars.bienTipoId != -1) {
        $("#contenedorCantidadMinima").show();
        $("#contenedorBienTipo").show();
        $("#contenedorUnidadTipo").show();
        $("#contenedorUnidadControl").show();
//        $("#contenedorCodigoFabricante").show();
//        $("#contenedorCodigoBarras").show();
        $("#contenedorMarca").show();
        $("#contenedorMaquinaria").show();
        $("#contenedorCodigoSunat").show();
        $("#contenedorCuentaContable").show();
        $("#contenedorCostoInicial").show();
    } else {
        $("#contenedorBienTipo").show();
        $('#btnProveedor').hide();
    }
}

$("#cboEstado").change(function () {
    $('#msj_estado').hide();
});

function deshabilitarBoton()
{
    $("#env").addClass('disabled');
}
function habilitarBoton()
{
    $("#env").removeClass('disabled');
}
function cambiarEstado(id_estado)
{
    ax.setAccion("cambiarEstado");
    ax.addParamTmp("id_estado", id_estado);
    ax.consumir();
}
function cambiarIconoEstado(data)
{
    $.Notification.autoHideNotify('success', 'top-right', '&Eacute;xito', 'Estado actualizado');
}

function onchangeEmpresa()
{
    $('#msj_empresa').hide();
}
function onchangeTipoBien(valor)
{
    $('#msj_tipo').hide();
    if (valor == -2) {
        $('a[href="#tabActivoFijo"]').show();
    } else {
        $('a[href="#tabGeneral"]').click();
        $('a[href="#tabActivoFijo"]').hide();
    }

}

function onchangeUnidadTipo()
{
    $('#msj_UnidadTipo').hide();
//    loaderShow();
    ax.setAccion("obtenerUnidadControl");
    //ax.addParamTmp("id_unidad_medida_tipo", document.getElementById('cboUnidadTipo').value);
    ax.addParamTmp("id_unidad_medida_tipo", $('#cboUnidadTipo').val());
    ax.consumir();

    //var unidad_tipo = $('#cboUnidadTipo').val();
    //alert(document.getElementById('cboUnidadTipo').value);
}

$('#txt_codigo').keypress(function () {
    $('#msj_codigo').hide();
});
$('#txt_descripcion').keypress(function () {
    $('#msj_descripcion').hide();
});
$('#txt_precio_venta').keypress(function () {
    $('#msj_precio_venta').hide();
});

function limpiarFormulario()
{
    document.getElementById("frm_bien").reset();
}

function validarFormulario() {
    var bandera = true;
    // valido modal de proveedores repetido
    bandera = validarModalProveedoresRepetidos();
    if (bandera == false) {
        $.Notification.autoHideNotify('warning', 'top right', 'Validación', "Seleccione proveedores en orden de prioridad");
    }

    var espacio = /^\s+$/;

    if (commonVars.bienTipoId != 1) {
        var descripcion = document.getElementById('txt_descripcion').value;
        var cant_minima = null;
        // var cant_minima = document.getElementById('txt_cant_minima').value;
        var codigo = document.getElementById('txt_codigo').value;
        var tipo = document.getElementById('cboBienTipo').value;
        var empresa = document.getElementById("cboEmpresa").value;
        var estado = document.getElementById('cboEstado').value;
        var unidad_tipo = document.getElementById('cboUnidadTipo').value;
        var codigoFabricante = null;
        // var codigoFabricante = document.getElementById('txtCodigoFabricante').value;
        var unidad_control_id = document.getElementById('cboUnidadControl').value;
//        var agregado_precio_venta = document.getElementById('txt_precio_venta').value;        
//        var precioCompra = document.getElementById('txt_precio_compra').value;
        var marca = document.getElementById('txtMarca').value;

        var modelo = document.getElementById('txt_modelo').value;
        var serie_numero = document.getElementById('txt_serie_numero').value;

        var depreciacion_metodo = select2.obtenerValor('cboMetodoDepreciacion');

        var depreciacion_pocentaje = select2.obtenerValor('cboDepreciacion');

        var cuenta_contable_gasto = select2.obtenerValor('cboCuentaContableGasto');

        var cuenta_contable_depreciacion = select2.obtenerValor('cboCuentaContableDepreciacion');

        var cuenta_contable_venta = select2.obtenerValor('cboCuentaContableVenta');

        var fecha_adquisicion = document.getElementById('txtFechaAdquicion').value;
        var fecha_inicio_uso = document.getElementById('txtFechaInicioUso').value;

//        if (modelo == "" || modelo == null || espacio.test(modelo) || modelo.length == 0)
//        {
//            $("#msj_modelo").removeProp(".hidden");
//            $("#msj_modelo").text("Ingrese el modelo").show();
//            bandera = false;
//        }

        if ((serie_numero == "" || serie_numero == null || espacio.test(serie_numero) || serie_numero.length == 0) && tipo == -2)
        {
            $("msj_serie_numero").removeProp(".hidden");
            $("#msj_serie_numero").text("Ingrese la serie y numero.").show();
            bandera = false;
        }

        if ((depreciacion_metodo == "" || depreciacion_metodo == null || espacio.test(depreciacion_metodo) || depreciacion_metodo.length == 0) && tipo == -2)
        {
            $("#msjMetodoDepreciacion").removeProp(".hidden");
            $("#msjMetodoDepreciacion").text("Ingrese el método de depreaciación.").show();
            bandera = false;
        }

        if (isEmpty(cuenta_contable_depreciacion) && tipo == -2)
        {
            $("#msjCuentaContableDepreciacion").removeProp(".hidden");
            $("#msjCuentaContableDepreciacion").text("Seleccione la cuenta contable de depreciación.").show();
            bandera = false;
        }

        if (isEmpty(cuenta_contable_gasto) && tipo == -2)
        {
            $("#msjCuentaContableGasto").removeProp(".hidden");
            $("#msjCuentaContableGasto").text("Seleccione la cuenta contable de gasto.").show();
            bandera = false;
        }

        if (isEmpty(cuenta_contable_venta) && tipo == -2)
        {
            $("#msjCuentaContableVenta").removeProp(".hidden");
            $("#msjCuentaContableVenta").text("Seleccione la cuenta contable de venta.").show();
            bandera = false;
        }



        if ((depreciacion_pocentaje == "" || depreciacion_pocentaje == null || espacio.test(depreciacion_pocentaje) || depreciacion_pocentaje.length == 0) && tipo == -2)
        {
            $("#msjPorcentajeDepreciacion").removeProp(".hidden");
            $("#msjPorcentajeDepreciacion").text("Ingrese el porcentaje de depreaciación.").show();
            bandera = false;
        }

        if ((fecha_adquisicion == "" || fecha_adquisicion == null || espacio.test(fecha_adquisicion) || fecha_adquisicion.length == 0) && tipo == -2)
        {
            $("#msjFechaAdquicion").removeProp(".hidden");
            $("#msjFechaAdquicion").text("Ingrese el porcentaje de depreaciación.").show();
            bandera = false;
        }

        if ((fecha_inicio_uso == "" || fecha_inicio_uso == null || espacio.test(fecha_inicio_uso) || fecha_inicio_uso.length == 0) && tipo == -2)
        {
            $("#msjFechaInicioUso").removeProp(".hidden");
            $("#msjFechaInicioUso").text("Ingrese el porcentaje de depreaciación.").show();
            bandera = false;
        }

        if (descripcion == "" || descripcion == null || espacio.test(descripcion) || descripcion.length == 0)
        {
            $("msj_descripcion").removeProp(".hidden");
            $("#msj_descripcion").text("Ingresar una descripción").show();
            bandera = false;
        }
        /*if (marca == "" || marca == null || espacio.test(marca) || marca.length == 0)
         {
         $("msjMarca").removeProp(".hidden");
         $("#msjMarca").text("Ingresar una marca").show();
         bandera = false;
         }*/
        if (codigo == "" || codigo == null || espacio.test(codigo) || codigo.length == 0)
        {
            $("msj_codigo").removeProp(".hidden");
            $("#msj_codigo").text("Ingresar un código").show();
            bandera = false;
        }
        if (tipo == "" || tipo == null || espacio.test(tipo) || tipo.length == 0)
        {
            $("msj_tipo").removeProp(".hidden");
            $("#msj_tipo").text("Ingresar un tipo de bien").show();
            bandera = false;
        }

//        if (isEmpty(agregado_precio_venta) || isNaN(agregado_precio_venta))
//        {
//            $("msj_precio_venta").removeProp(".hidden");
//            $("#msj_precio_venta").text("Ingresar precio de venta.").show();
//            bandera = false;
//        }
//
//        if (isEmpty(precioCompra) || isNaN(precioCompra))
//        {
//            $("msj_precio_compra").removeProp(".hidden");
//            $("#msj_precio_compra").text("Ingresar precio de compra.").show();
//            bandera = false;
//        }

        if (empresa == "" || espacio.test(empresa) || empresa.lenght == 0 || empresa == null)
        {
            $("msj_empresa").removeProp(".hidden");
            $("#msj_empresa").text("Seleccionar una empresa").show();
            bandera = false;
        }
        if (estado == "" || espacio.test(estado) || estado.lenght == 0 || estado == null)
        {
            $("msj_estado").removeProp(".hidden");
            $("#msj_estado").text("Seleccionar un estado").show();
            bandera = false;
        }

        if (unidad_tipo == "" || espacio.test(unidad_tipo) || unidad_tipo.lenght == 0 || unidad_tipo == null)
        {
            $("msj_UnidadTipo").removeProp(".hidden");
            $("#msj_UnidadTipo").text("Seleccionar un tipo de unidad").show();
            bandera = false;
        }

        if (unidad_control_id == "" || unidad_control_id == null || espacio.test(unidad_control_id) || unidad_control_id.length == 0)
        {
            $("msj_tipo").removeProp(".hidden");
            $("#msj_unidad_control").text("Seleccionar una unidad de control").show();
            bandera = false;
        }
        /*if (codigoFabricante == "" || codigoFabricante == null || espacio.test(codigoFabricante) || codigoFabricante.length == 0)
         {
         $("msjCodigoFabricante").removeProp(".hidden");
         $("#msjCodigoFabricante").text("Ingresar un código de fabricante").show();
         bandera = false;
         }*/

//        if (listaPrecioDetalle.length === 0) {
//            $("#msjPrecioDetalle").removeProp(".hidden");
//            $("#msjPrecioDetalle").text("Agregue al menos un precio").show();
//            bandera = false;
//        }

    } else {
        var descripcion = document.getElementById('txt_descripcion').value;
        var codigo = document.getElementById('txt_codigo').value;
        var empresa = document.getElementById("cboEmpresa").value;
        var estado = document.getElementById('cboEstado').value;

//        var agregado_precio_venta = document.getElementById('txt_precio_venta').value;   
//        var precioCompra = document.getElementById('txt_precio_compra').value;

//        if (isEmpty(precioCompra) || isNaN(precioCompra))
//        {
//            $("msj_precio_compra").removeProp(".hidden");
//            $("#msj_precio_compra").text("Ingresar precio de compra.").show();
//            bandera = false;
//        }

        if (descripcion == "" || descripcion == null || espacio.test(descripcion) || descripcion.length == 0)
        {
            $("msj_descripcion").removeProp(".hidden");
            $("#msj_descripcion").text("Ingresar una descripción").show();
            bandera = false;
        }
        if (codigo == "" || codigo == null || espacio.test(codigo) || codigo.length == 0)
        {
            $("msj_codigo").removeProp(".hidden");
            $("#msj_codigo").text("Ingresar un código").show();
            bandera = false;
        }

//        if (isEmpty(agregado_precio_venta) || isNaN(agregado_precio_venta))
//        {
//            $("msj_precio_venta").removeProp(".hidden");
//            $("#msj_precio_venta").text("Ingresar un agregado al precio de venta.").show();
//            bandera = false;
//        }

        if (empresa == "" || espacio.test(empresa) || empresa.lenght == 0 || empresa == null)
        {
            $("msj_empresa").removeProp(".hidden");
            $("#msj_empresa").text("Seleccionar una empresa").show();
            bandera = false;
        }
        if (estado == "" || espacio.test(estado) || estado.lenght == 0 || estado == null)
        {
            $("msj_estado").removeProp(".hidden");
            $("#msj_estado").text("Seleccionar un estado").show();
            bandera = false;
        }
    }

    return bandera;
}
function successBien(response) {
    if (response['status'] === 'ok') {
        switch (response[PARAM_ACCION_NAME]) {
            case 'obtenerConfiguracionesIniciales':
                onResponseObtenerConfiguracionesIniciales(response.data);
                loaderClose();
                break;
            case 'obtenerUnidadControl':
                obtenerUnidadControlCombo(response.data);
                loaderClose();
                break;
            case 'insertBien':
                loaderClose();
                exitoInsert(response.data);
                break;
            case 'getBien':
                llenarFormularioEditar(response.data);
                break;
            case 'updateBien':
                loaderClose();
                exitoUpdate(response.data);
                break;
            case 'cambiarEstado':
                cambiarIconoEstado(response.data);
                listarBIEN();
                break;
            case 'deleteBien':
                var error = response.data['0'].vout_exito;
                if (error > 0) {
                    swal("Eliminado!", "El tipo de bien " + response.data['0'].nombre + ".", "success");
                    listarBIEN();
                } else {
                    swal("Cancelado", "El tipo de bien " + response.data['0'].nombre + " " + response.data['0'].vout_mensaje, "error");
                }
                bandera_eliminar = true;
                break;
            case 'generarCodigoBarra':
                break;

            case 'importBien':
                loaderClose();
                $('#resultado').append(response.data);
                listarBIEN();
                break;

            case 'ExportarBienExcel':
                loaderClose();
                location.href = "http://" + location.host + "/almacen/util/formatos/lista_de_bienes.xlsx";
                break;
            case 'getAllEmpresa':
                onResponseGetAllEmpresas(response.data);
                break;
            case 'getAllUnidadMedidaTipoCombo':
                onResponsegetAllUnidadMedidaTipoCombo(response.data);
                break;
            case 'getAllBienTipo':
                onResponseGetAllBienTipo(response.data);

//                if (!isEmpty(VALOR_ID_USUARIO))
//                {
//                    getBien(VALOR_ID_USUARIO);
//                }
//               loaderClose();
                verificarCargaDeComplemento();
                break;
            case 'getAllEmpresaImport':
                onResponseGetAllEmpresas(response.data);
                break;
            case 'obtenerComboProveedores':
                onResponseObtenerComboProveedores(response.data);
                loaderClose();
                break;
        }
    } else
    {
        switch (response[PARAM_ACCION_NAME]) {
            case 'insertBien':
                habilitarBoton();
                loaderClose();
                break;
        }
    }
}

var dataPrecioTipo;
var anchoCuentaContable;
function onResponseObtenerConfiguracionesIniciales(data) {
//    console.log(data.dataSunatDetalle);
//    console.log(data.cuentaContable);
    //SETEAR ANCHO PARA COMBO CUENTA CONTABLE    
    anchoCuentaContable = $("#divCuentaContable").width();

    dataPrecioTipo = data.precioTipo;
    dataCentroCosto = data.dataCentroCosto;
    $('a[href="#tabActivoFijo"]').hide();
    $('#txtDescuento').val('0.00');
    // cargamos los combos

    if (commonVars.bienId > 0) {
        select2.cargar("cboEmpresa", data.empresa, "id", "razon_social");
        if (commonVars.bienTipoId == -2) {
            $('a[href="#tabActivoFijo"]').show();
        }
        if (commonVars.bienTipoId != 1) {
            select2.cargar("cboUnidadTipo", data.unidadMedidaTipo, "id", "descripcion");
            select2.cargar("cboBienTipo", data.bienTipo, "id", ["codigo", "descripcion"]);
            select2.cargar("cboPrecioTipo", data.precioTipo, "id", "descripcion");
            select2.cargar("cboMoneda", data.moneda, "id", ["descripcion", "simbolo"]);
            select2.cargar("cboMarca", data.marcas, "id", "descripcion");
            if (!isEmpty(data.maquinarias)) {
                select2.cargar("cboMaquinaria", data.maquinarias, "id", "descripcion");
            }
            select2.cargar("cboCodigoSunat", data.dataSunatDetalle, "id", ["codigo", "descripcion"]);

            select2.cargarAsignaUnico("cboCuentaContable", data.cuentaContable, "codigo", ["codigo", "descripcion"]);
            select2.cargarAsignaUnico("cboCuentaContableGasto", data.cuentaContable, "codigo", ["codigo", "descripcion"]);
            select2.cargarAsignaUnico("cboCuentaContableDepreciacion", data.cuentaContable, "codigo", ["codigo", "descripcion"]);
            select2.cargarAsignaUnico("cboCuentaContableVenta", data.cuentaContable, "codigo", ["codigo", "descripcion"]);

            $("#cboCuentaContable").select2({width: anchoCuentaContable + 'px'});


        } else {
            select2.cargar("cboUnidadTipo", data.unidadMedidaTipo, "id", "descripcion");
            select2.cargar("cboPrecioTipo", data.precioTipo, "id", "descripcion");
            select2.cargar("cboMoneda", data.moneda, "id", ["descripcion", "simbolo"]);
            select2.cargarAsignaUnico("cboUnidadMedida", data.unidadMedidaUnidades, "id", "descripcion");
            select2.cargar("cboBienTipo", data.bienTipo, "id", ["codigo", "descripcion"]);
        }

        if (commonVars.bienTipoId == 1) {
            $("#contenedorModelo").hide();
            $("#contenedorSeriNumero").hide();
            $("#contenedorMarca").hide();
            $("#codigoProductoText").html("Código de Servicio *")
            $('a[href="#tabPrecios"]').hide();
        }
        //select2.cargar("cboUnidadControl", data.bienTipo, "id", "descripcion");

        // llenado de los array de los precios
        var bienPrecio = data.bienPrecio;
        //console.log(bienPrecio);
        $(bienPrecio).each(function (index) {

            var precioTipo = bienPrecio[index].precio_tipo_id;
            var unidadMedida = bienPrecio[index].unidad_medida_id;
            var moneda = bienPrecio[index].moneda_id;
            var precio = bienPrecio[index].precio;
            var descuento = bienPrecio[index].descuento;
            var incluyeIGV = bienPrecio[index].incluye_igv;
            var checkIGV = bienPrecio[index].check_igv;

            var precioTipoText = bienPrecio[index].precio_tipo_descripcion;
            var unidadMedidaText = bienPrecio[index].unidad_medida_descripcion;
            var monedaText = bienPrecio[index].moneda_descripcion;

            // ids de tablas relacionadas
            var bienPrecioId = bienPrecio[index].bien_precio_id;


            arrayPrecioTipo.push(precioTipo);
            arrayPrecioTipoText.push(precioTipoText);
            arrayUnidadMedida.push(unidadMedida);
            arrayUnidadMedidaText.push(unidadMedidaText);
            arrayMoneda.push(moneda);
            arrayMonedaText.push(monedaText);
            arrayPrecio.push(precio);
            arrayDescuento.push(descuento);
            arrayIncluyeIGV.push(incluyeIGV);
            arrayCheckIGV.push(checkIGV);

            // array ids
            arrayBienPrecioId.push(bienPrecioId);

            listaPrecioDetalle.push([precioTipo, precioTipoText, unidadMedida, unidadMedidaText, moneda, monedaText, precio, descuento, bienPrecioId, incluyeIGV, checkIGV]);
            onListarPrecioDetalle(listaPrecioDetalle);

        });
        // fin

    } else {
        select2.cargarAsignaUnico("cboEmpresa", data.empresa, "id", "razon_social");
        select2.cargarAsignaUnico("cboUnidadTipo", data.unidadMedidaTipo, "id", "descripcion");

        if (commonVars.bienTipoId != 1) {
            if (data.unidadMedidaTipo.length == 1) {
                onchangeUnidadTipo();
            }
        }
        if (commonVars.bienTipoId == 1) {
            $("#contenedorModelo").hide();
            $("#contenedorSeriNumero").hide();
            $("#codigoProductoText").html("Código de Servicio *")
            $('a[href="#tabPrecios"]').hide();
            $("#contenedorMarca").hide();
        }

        select2.cargar("cboBienTipo", data.bienTipo, "id", ["codigo", "descripcion"]);
        select2.cargarAsignaUnico("cboBienTipo", data.bienTipo, "id", ["codigo", "descripcion"]);
        select2.cargarAsignaUnico("cboPrecioTipo", data.precioTipo, "id", "descripcion");
        select2.cargarAsignaUnico("cboMoneda", data.moneda, "id", ["descripcion", "simbolo"]);
        select2.asignarValor('cboMoneda', 2);
        select2.cargarAsignaUnico("cboUnidadMedida", data.unidadMedidaUnidades, "id", "descripcion");
        select2.cargarAsignaUnico("cboMarca", data.marcas, "id", "descripcion");
        if (!isEmpty(data.maquinarias)) {
            select2.cargarAsignaUnico("cboMaquinaria", data.maquinarias, "id", "descripcion");
        }
        select2.cargar("cboCodigoSunat", data.dataSunatDetalle, "id", ["codigo", "descripcion"]);
        select2.cargarAsignaUnico("cboCuentaContable", data.cuentaContable, "codigo", ["codigo", "descripcion"]);
        select2.cargarAsignaUnico("cboCuentaContableGasto", data.cuentaContable, "codigo", ["codigo", "descripcion"]);
        select2.cargarAsignaUnico("cboCuentaContableDepreciacion", data.cuentaContable, "codigo", ["codigo", "descripcion"]);
        select2.cargarAsignaUnico("cboCuentaContableVenta", data.cuentaContable, "codigo", ["codigo", "descripcion"]);

        $('#cboCuentaContable').select2('val', '20111');
        $("#cboCuentaContable").select2({width: anchoCuentaContable + 'px'});
        //select2.cargarAsignaUnico("cboUnidadControl", data.bienTipo, "id", "descripcion");

        //preseleccion de todas las empresas    
        var empresaIds = '';

        $.each(data.empresa, function (i, item) {
            empresaIds = empresaIds + ";" + item["id"];
        });

        select2.asignarValor('cboEmpresa', empresaIds.split(";"));

        // fin preseleccion

    }

    select2.cargar("cboMetodoDepreciacion", data.dataDepreciacionMetodo, "id", "descripcion");
    select2.cargar("cboDepreciacion", data.dataDepreciacion, "id", "descripcion");
    // cargamos el formulario
    if (!isEmpty(data.bien)) {
        unidad_controlID = data.bien[0].unidad_control_id;
        llenarFormularioEditar(data.bien);
        cargarCentroCostoBien(data.dataDistribucion);
        if (commonVars.bienTipoId != 1) {
            onchangeUnidadTipo();
        }
        //llenarcboUnidadControlEditar(data.bien);
    }

    // cargamos el modal proveedor
    if (!isEmpty(data.bienPersona)) {
        dataBienPersona = data.bienPersona;
        //llenarModalProveedores(data.bienPersona);
    }

    if (commonVars.bienTipoId == 1) {
        onchangeUnidadTipo();
    }
    loaderClose();
}

var dataBienPersona = [];

function obtenerUnidadControlCombo(data) {
    if (commonVars.bienId > 0) {
        select2.limpiar("cboUnidadControl");
        select2.cargarAsignaUnico("cboUnidadControl", data.unidadMedida, "id", "descripcion");
        select2.cargarAsignaUnico("cboUnidadMedida", data.unidadMedida, "id", "descripcion");
        select2.asignarValor('cboUnidadControl', unidad_controlID);
    } else {
        select2.cargarAsignaUnico("cboUnidadControl", data.unidadMedida, "id", "descripcion");
        select2.cargarAsignaUnico("cboUnidadMedida", data.unidadMedida, "id", "descripcion");
    }
    loaderClose();
}

function exitoInsert(data)
{
    if (data[0]["vout_exito"] == 0)
    {
        habilitarBoton();
        loaderClose();
        $.Notification.autoHideNotify('warning', 'top right', 'Validación', data[0]["vout_mensaje"]);
    } else
    {
        habilitarBoton();
        loaderClose();
        $.Notification.autoHideNotify('success', 'top-right', '&Eacute;xito', data[0]["vout_mensaje"]);
        validarToken();
        cargarPantallaListar();
    }
}
function exitoUpdate(data)
{
    if (data[0]["vout_exito"] == 0)
    {
        habilitarBoton();
        loaderClose();
        $.Notification.autoHideNotify('warning', 'top right', 'Validación', data[0]["vout_mensaje"]);
    } else
    {
        habilitarBoton();
        loaderClose();
        $.Notification.autoHideNotify('success', 'top-right', '&Eacute;xito', data[0]["vout_mensaje"]);
        validarToken();
        cargarPantallaListar();
    }
}

function guardarBien(tipo_accion)
{
    var usu_creacion = document.getElementById('usuario').value;
    var descripcion = document.getElementById('txt_descripcion').value;
    var cant_minima = null;
    // var cant_minima = document.getElementById('txt_cant_minima').value;
    var codigo = document.getElementById('txt_codigo').value;
    var tipo_bien = document.getElementById('cboBienTipo').value;
    var estado = document.getElementById('cboEstado').value;
    var comentario = document.getElementById('txt_comentario').value;

    var modelo = document.getElementById('txt_modelo').value;
    var serie_numero = document.getElementById('txt_serie_numero').value;

    var depreciacion_metodo = select2.obtenerValor('cboMetodoDepreciacion');
    var depreciacion_pocentaje = select2.obtenerValor('cboDepreciacion');

    var fecha_adquisicion = datex.parserControlador(document.getElementById('txtFechaAdquicion').value);
    var fecha_inicio_uso = datex.parserControlador(document.getElementById('txtFechaInicioUso').value);

//    var agregado_precio_venta = document.getElementById('txt_precio_venta').value;
//    var precioCompra = document.getElementById('txt_precio_compra').value;
    //var agregado_precio_venta_tipo = document.getElementById('cboPrecioVentaTipo').value;
    var agregado_precio_venta_tipo = 1;

    var unidad_control_id = document.getElementById('cboUnidadControl').value;

    var empresa = $('#cboEmpresa').val();
    var unidad_tipo = $('#cboUnidadTipo').val();

    var codigoFabricante = null;
    // var codigoFabricante = document.getElementById('txtCodigoFabricante').value;
    var marca = document.getElementById('txtMarca').value;

    if (isEmpty(marca)) {
        marca = select2.obtenerText('cboMarca');
    }

    //maquinaria    
    var maquinaria = null;
    // var maquinaria = document.getElementById('txtMaquinaria').value;

    // if (isEmpty(maquinaria)) {
    //     maquinaria = select2.obtenerText('cboMaquinaria');
    // }

    var codigoBarras = null;
    // var codigoBarras = document.getElementById('txtCodigoBarras').value;

    var file = document.getElementById('secretImg').value;
    if (file == '')
    {
        file = null;
    }

    var codigoSunatId = select2.obtenerValor('cboCodigoSunat');

    var cuentaContableId = select2.obtenerValor('cboCuentaContable');
    var cuenta_contable_gasto = select2.obtenerValor('cboCuentaContableGasto');
    var cuenta_contable_depreciacion = select2.obtenerValor('cboCuentaContableDepreciacion');
    var cuenta_contable_venta = select2.obtenerValor('cboCuentaContableVenta');

    var costoInical = $('#txtCostoInicial').val();
    var codigoCuenta = $('#txtCodigoCuenta').val();

    if (costoInical * 1 < 0) {
        mostrarAdvertencia('Costo inicial no puede ser negativo.');
        return;
    }

    var codigoInternacional = $('#txtCodigoInternacional').val();

    var arrayCentroCostoBien = [];
    if (tipo_bien == -2) {
        arrayCentroCostoBien = obtenerCentroCostoBien();
        if (isEmpty(arrayCentroCostoBien)) {
            return;
        }
    }

    //VARIABLE PARA GUARDAR OTROS CAMPOS DE BIEN
    var objCamposBien = {};
    objCamposBien.cuentaContableId = cuentaContableId;
    objCamposBien.cuentaContableGasto = cuenta_contable_gasto;
    objCamposBien.cuentaContableDepreciacion = cuenta_contable_depreciacion;
    objCamposBien.cuentaContableVenta = cuenta_contable_venta;

    objCamposBien.costoInical = costoInical;
    objCamposBien.codigoCuenta = codigoCuenta;
    objCamposBien.codigoInternacional = codigoInternacional;
    objCamposBien.modelo = modelo;
    objCamposBien.serieNumero = serie_numero;
    objCamposBien.depreciacionMetodo = depreciacion_metodo;
    objCamposBien.depreciacionPorcentaje = depreciacion_pocentaje;
    objCamposBien.distribucionContable = arrayCentroCostoBien;
    objCamposBien.fechaAdquisicion = fecha_adquisicion;
    objCamposBien.fechaInicioUso = fecha_inicio_uso;

    if (commonVars.bienId > 0)
    {
        updateBien(commonVars.bienId, descripcion, codigo, cant_minima, tipo_bien, estado, comentario, empresa, file, unidad_tipo,
                agregado_precio_venta_tipo, unidad_control_id, codigoFabricante, codigoBarras, marca, maquinaria, codigoSunatId, objCamposBien);
    } else {
        insertBien(descripcion, codigo, tipo_bien, cant_minima, estado, usu_creacion, comentario, empresa, file, unidad_tipo,
                agregado_precio_venta_tipo, unidad_control_id, codigoFabricante, codigoBarras, marca, maquinaria, codigoSunatId, objCamposBien);
    }
}

function insertBien(descripcion, codigo, tipo_bien, cant_minima, estado, usu_creacion, comentario, empresa, file, unidad_tipo,
        agregado_precio_venta_tipo, unidad_control_id, codigoFabricante, codigoBarras, marca, maquinaria, codigoSunatId, objCamposBien)
{
    if (validarFormulario()) {
        loaderShow();
        //deshabilitarBoton();
        ax.setAccion("insertBien");
        ax.addParamTmp("descripcion", descripcion);
        ax.addParamTmp("codigo", codigo);
        ax.addParamTmp("tipo", tipo_bien);
        ax.addParamTmp("cant_minima", cant_minima);
        ax.addParamTmp("estado", estado);
        ax.addParamTmp("usu_creacion", usu_creacion);
        ax.addParamTmp("comentario", comentario);
        ax.addParamTmp("empresa", empresa);
        ax.addParamTmp("file", file);
        ax.addParamTmp("unidad_tipo", unidad_tipo);
//        ax.addParamTmp("agregado_precio_venta", agregado_precio_venta);
        ax.addParamTmp("agregado_precio_venta_tipo", agregado_precio_venta_tipo);
        ax.addParamTmp("unidad_control_id", unidad_control_id);
        ax.addParamTmp("listaProveedorId", listaProveedorId);
        ax.addParamTmp("listaPrioridad", listaPrioridad);
        ax.addParamTmp("listaPrecioDetalle", listaPrecioDetalle);
        ax.addParamTmp("codigoFabricante", codigoFabricante);
        ax.addParamTmp("codigoBarras", codigoBarras);
        ax.addParamTmp("marca", marca);
//        ax.addParamTmp("precioCompra", precioCompra);
        ax.addParamTmp("maquinaria", maquinaria);
        ax.addParamTmp("codigoSunatId", codigoSunatId);
        ax.addParamTmp("objCamposBien", objCamposBien);
        ax.consumir();
    } else
    {
        loaderClose();
    }
}

function llenarFormularioEditar(data)
{
    var dir = URL_BASE + "vistas/com/bien/imagen/" + data[0].imagen;
    $('#myImg').empty();
    document.getElementById('myImg').src = dir;
    document.getElementById('txt_descripcion').value = data[0].descripcion;
    document.getElementById('txt_codigo').value = data[0].codigo;
    // document.getElementById('txt_cant_minima').value = data[0].cantidad_minima;
    document.getElementById('txt_comentario').value = data[0].comentario;
    // document.getElementById('txtCodigoFabricante').value = data[0].codigo_fabricante;
    // document.getElementById('txtCodigoBarras').value = data[0].codigo_barra;

    document.getElementById('txt_serie_numero').value = data[0].serie_numero;
    document.getElementById('txt_modelo').value = data[0].modelo;
//    document.getElementById('cboMetodoDepreciacion').value = data[0].metodo_depreciacion_id;
//    document.getElementById('cboDepreciacion').value = data[0].depreciacion_id;
    document.getElementById('txtFechaAdquicion').value = datex.formatoImaginaDG(data[0].fecha_adquisicion);
    document.getElementById('txtFechaInicioUso').value = datex.formatoImaginaDG(data[0].fecha_inicio_uso);

    //document.getElementById('txt_precio_venta').value = data[0]agregado_precio_venta;
//    document.getElementById('txt_precio_venta').value = data[0].precio_venta;
//    document.getElementById('txt_precio_compra').value = data[0].precio_compra;
    document.getElementById('txtMarca').value = '';

    select2.asignarValor('cboPrecioVentaTipo', data[0].agregado_precio_venta_tipo);

    select2.asignarValor('cboEstado', data[0].estado);

    select2.asignarValor('cboBienTipo', data[0].bien_tipo_id);
    select2.asignarValor('cboMarca', data[0].marca_id);
    select2.asignarValor('cboMaquinaria', data[0].maquinaria_id);
    select2.asignarValor('cboCodigoSunat', data[0]['sunat_tabla_detalle_id']);
    select2.asignarValor('cboMetodoDepreciacion', data[0].metodo_depreciacion_id);
    select2.asignarValor('cboDepreciacion', data[0].depreciacion_id);

    select2.asignarValor('cboCuentaContable', data[0]['plan_contable_codigo']);
    $("#cboCuentaContable").select2({width: anchoCuentaContable + 'px'});

    select2.asignarValor('cboCuentaContableGasto', data[0].plan_contable_gasto);
    select2.asignarValor('cboCuentaContableDepreciacion', data[0].plan_contable_depreciacion);
    select2.asignarValor('cboCuentaContableVenta', data[0].plan_contable_venta);

    document.getElementById('txtCostoInicial').value = data[0].costo_inicial;
    // document.getElementById('txtCodigoCuenta').value = data[0].codigo_contable;

    $('#txtCodigoInternacional').val(data[0].codigo_internacional);


    if (!isEmpty(data[0]['empresas_id']))
    {
        select2.asignarValor("cboEmpresa", data[0]['empresas_id'].split(";"));
    }
    if (!isEmpty(data[0]['unidad_medida_tipo_id']))
    {
        select2.asignarValor("cboUnidadTipo", data[0]['unidad_medida_tipo_id'].split(";"));
    }
    loaderClose();
}

function llenarcboUnidadControlEditar(data)
{
    select2.asignarValor('cboUnidadControl', data[0].unidad_control_id);
    loaderClose();
}

function updateBien(id, descripcion, codigo, cant_minima, tipo, estado, comentario, empresa, file, unidad_tipo,
        agregado_precio_venta_tipo, unidad_control_id, codigoFabricante, codigoBarras, marca, maquinaria, codigoSunatId, objCamposBien)
{
    if (validarFormulario()) {
        loaderShow();
        //deshabilitarBoton();
        ax.setAccion("updateBien");
        ax.addParamTmp("id_bien", id);
        ax.addParamTmp("descripcion", descripcion);
        ax.addParamTmp("codigo", codigo);
        ax.addParamTmp("tipo", tipo);
        ax.addParamTmp("cant_minima", cant_minima);
        ax.addParamTmp("estado", estado);
        ax.addParamTmp("comentario", comentario);
        ax.addParamTmp("empresa", empresa);
        ax.addParamTmp("file", file);
        ax.addParamTmp("unidad_tipo", unidad_tipo);
//        ax.addParamTmp("agregado_precio_venta", agregado_precio_venta);
        ax.addParamTmp("agregado_precio_venta_tipo", agregado_precio_venta_tipo);
        ax.addParamTmp("unidad_control_id", unidad_control_id);
        ax.addParamTmp("listaProveedorId", listaProveedorId);
        ax.addParamTmp("listaPrioridad", listaPrioridad);
        ax.addParamTmp("listaPrecioDetalle", listaPrecioDetalle);
        ax.addParamTmp("listaBienPrecioEliminado", listaBienPrecioEliminado);
        ax.addParamTmp("codigoFabricante", codigoFabricante);
        ax.addParamTmp("codigoBarras", codigoBarras);
        ax.addParamTmp("marca", marca);
        ax.addParamTmp("maquinaria", maquinaria);
        ax.addParamTmp("codigoSunatId", codigoSunatId);
        ax.addParamTmp("objCamposBien", objCamposBien);
//        ax.addParamTmp("precioCompra", precioCompra);
        ax.consumir();
    } else
    {
        loaderClose();
    }
}

function generarCodigoBarra()
{
    var codigo = document.getElementById("txt_codigo").value;
    $("#bcTarget").barcode("11111111", "ean8", {barWidth: 5, barHeight: 30});
}

function cargarSelect2()
{
    $(".select2").select2({
        width: '100%'
    });
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
    cargarDiv("#window", "vistas/com/bien/bien_listar.php", tituloGlobal);
}

//MODAL PROVEEDORES
function abrirModalProveedor() {
    loaderShow();
    listarComboProveedores();
    $('#modalProveedores').modal('show');
}

function listarComboProveedores() {
    ax.setAccion("obtenerComboProveedores");
    ax.consumir();
}


var dataComboProveedor;
var banderaComboProveedor = 0;

function onResponseObtenerComboProveedores(data) {
    if (banderaComboProveedor === 0) {
        dataComboProveedor = data;
        llenarCombo("cboProveedor1", "id", "persona_nombre", "codigo_identificacion", data);
        llenarCombo("cboProveedor2", "id", "persona_nombre", "codigo_identificacion", data);
        llenarCombo("cboProveedor3", "id", "persona_nombre", "codigo_identificacion", data);
        llenarCombo("cboProveedor4", "id", "persona_nombre", "codigo_identificacion", data);
        banderaComboProveedor = 1;

    }

    if (!isEmpty(dataBienPersona)) {
        llenarModalProveedores(dataBienPersona);
    }
}

function llenarCombo(cboId, itemId, itemDes, itemDoc, data) {
    //document.getElementById(cboId).innerHTML = "";
    //asignarValorSelect2(cboId, "");
    if (!isEmpty(data)) {
        $('#' + cboId).append('<option value="-1">Ninguno</option>');
        $.each(data, function (index, item) {
            $('#' + cboId).append('<option value="' + item[itemId] + '">' + item[itemDes] + ' | ' + item[itemDoc] + '</option>');
        });
    }
}

var listaProveedorId = [];
var listaComboProveedor = [];
var listaPrioridad = [];
function agregarProveedor(cboProveedor, prioridad) {
    var proveedorId = document.getElementById(cboProveedor).value;
    //alert(proveedorId);

    eliminarProveedorDeLista(cboProveedor);

    if (validarProveedorRepetido(listaProveedorId, proveedorId, cboProveedor)) {
        if (validarPrioridad(listaPrioridad, prioridad, cboProveedor, proveedorId)) {

            if (proveedorId != -1) {
                listaProveedorId.push(proveedorId);
                listaComboProveedor.push(cboProveedor);
                listaPrioridad.push(prioridad);
            }

            //var indice = listaPrioridad.indexOf(prioridad-1);
            //alert(indice);
        }
    }

//    console.log(listaProveedorId);
//    console.log(listaPrioridad);
//    console.log(listaComboProveedor);
}

function validarPrioridad(listaPrioridad, prioridad, cboProveedor, proveedorId) {
    var valido = true;
    if (proveedorId != -1) {
        var indice = listaPrioridad.indexOf(prioridad - 1);
        //console.log(indice+2,prioridad);
        //alert("prioridad: "+(prioridad-1)+ " indice: "+ indice);
        if ((indice + 2) != prioridad) {
            $.Notification.autoHideNotify('warning', 'top right', 'Validación', "Seleccione proveedor en orden de prioridad");

            //recarga el combo
            limpiaCombo(cboProveedor);
            //llenarCombo(cboProveedor, "id", "persona_nombre", dataComboProveedor);
            //eliminarProveedorDeLista(cboProveedor);

            valido = false;
        }
    }
    return valido;
}

function validarProveedorRepetido(listaProveedorId, proveedorId, cboProveedor) {

    var valido = true;

    if (proveedorId != -1) {
        var indice = listaProveedorId.indexOf(proveedorId);
        if (indice > -1) {
            $.Notification.autoHideNotify('warning', 'top right', 'Validación', "El proveedor ya ha sido seleccionado");

            //recarga el combo
            limpiaCombo(cboProveedor);
            llenarCombo(cboProveedor, "id", "persona_nombre", dataComboProveedor);
//            console.log(dataComboProveedor);
            //eliminarProveedorDeLista(cboProveedor);

            valido = false;
        }
    }
    return valido;
}

function limpiaCombo(cboProveedor) {
    //document.getElementById(cboProveedor).innerHTML = "";
    asignarValorSelect2(cboProveedor, "");
}

function asignarValorSelect2(id, valor) {
    //console.log([id],[valor]);

    $("#" + id).select2().select2("val", valor);
    $("#" + id).select2({width: '100%'});
}

function eliminarProveedorDeLista(cboProveedor) {
    var indice = listaComboProveedor.indexOf(cboProveedor);
    if (indice > -1) {
        listaComboProveedor.splice(indice, 1);
        listaProveedorId.splice(indice, 1);
        listaPrioridad.splice(indice, 1);
    }
}

function reiniciarComboProveedores() {
    listaProveedorId = [];
    listaPrioridad = [];
    listaComboProveedor = [];
    banderaComboProveedor = 0;

    limpiaCombo("cboProveedor1");
    limpiaCombo("cboProveedor2");
    limpiaCombo("cboProveedor3");
    limpiaCombo("cboProveedor4");
}

var banderaLlenarProveedores = 0;
function llenarModalProveedores(data) {
    //console.log([data]);
    //listarComboProveedores();  

    if (banderaLlenarProveedores === 0) {
        for (var i = 0; i < data.length; i++) {
//            console.log(data[i]);
            asignarValorSelect2('cboProveedor' + data[i]['prioridad'], data[i]['persona_id']);

            listaProveedorId.push(data[i]['persona_id']);
            listaComboProveedor.push('cboProveedor' + data[i]['prioridad']);
            listaPrioridad.push(parseInt(data[i]['prioridad']));
        }
        banderaLlenarProveedores = 1;
    }
    /*console.log(listaProveedorId);
     console.log(listaComboProveedor);
     console.log(listaPrioridad);*/

}

//cerrar modal
function validarCerrarModal() {
    if (validarModalProveedoresRepetidos()) {
        $('#modalProveedores').modal('hide');
    } else {
        $.Notification.autoHideNotify('warning', 'top right', 'Validación', "Seleccione proveedores en orden de prioridad");
    }

}

function validarModalProveedoresRepetidos() {

    var suma = 0;
    var n = listaPrioridad.length;
    for (var i = 0; i < listaPrioridad.length; i++) {
        suma = suma + listaPrioridad[i];

    }

    var total = n * (n + 1) / 2;

    if (total == suma) {
        return true;
    } else {
        return false;
    }

}

// PESTAÑA PRECIO
var listaPrecioDetalle = [];

var arrayPrecioTipo = [];
var arrayPrecioTipoText = [];
var arrayUnidadMedida = [];
var arrayUnidadMedidaText = [];
var arrayMoneda = [];
var arrayMonedaText = [];
var arrayPrecio = [];
var arrayDescuento = [];
var arrayIncluyeIGV = [];
var arrayBienPrecioId = [];
var arrayCheckIGV = [];

function agregarPrecioDetalle() {
    //alert("Hola");

//    var precioTipo = $('#cboPrecioTipo').val();

    var precioTipo = select2.obtenerValor('cboPrecioTipo');
    var unidadMedida = $('#cboUnidadMedida').val();
    var moneda = $('#cboMoneda').val();
    var precio = $('#txtprecio').val();
    var descuento = $('#txtDescuento').val();
    var idPrecioDetalle = $('#idPrecioDetalle').val();

    var incluyeIGV;
    var checkIGV = 0;
    if (document.getElementById("chkIncluyeIGV").checked) {
        precio = precio / 1.18;
        checkIGV = 1;
    }
    incluyeIGV = precio * 1.18;

    // ids tablas
    var bienPrecioId = null;
    //alert(idPrecioDetalle);

    if (validarFormularioPrecioDetalle(precioTipo, unidadMedida, moneda, precio, descuento)) {
        if (validarPrecioDetalleRepetido(precioTipo, unidadMedida, moneda, precio, incluyeIGV)) {
            var precioTipoText = $('#cboPrecioTipo').find(':selected').text();
            var unidadMedidaText = $('#cboUnidadMedida').find(':selected').text();
            var monedaText = $('#cboMoneda').find(':selected').text();

            //alert("....");

            if (idPrecioDetalle != '') {
                //alert('igual');

                arrayPrecioTipo[idPrecioDetalle] = precioTipo;
                arrayPrecioTipoText[idPrecioDetalle] = precioTipoText;
                arrayUnidadMedida[idPrecioDetalle] = unidadMedida;
                arrayUnidadMedidaText[idPrecioDetalle] = unidadMedidaText;
                arrayMoneda[idPrecioDetalle] = moneda;
                arrayMonedaText[idPrecioDetalle] = monedaText;
                arrayPrecio[idPrecioDetalle] = precio;
                arrayDescuento[idPrecioDetalle] = descuento;
                arrayIncluyeIGV[idPrecioDetalle] = incluyeIGV;
                arrayCheckIGV[idPrecioDetalle] = checkIGV;

                // ids de tablas relacionadas
                bienPrecioId = arrayBienPrecioId[idPrecioDetalle];

                listaPrecioDetalle[idPrecioDetalle] = [precioTipo, precioTipoText, unidadMedida, unidadMedidaText, moneda, monedaText, precio, descuento, bienPrecioId, incluyeIGV, checkIGV];
            } else {
                //alert('diferente');

                arrayPrecioTipo.push(precioTipo);
                arrayPrecioTipoText.push(precioTipoText);
                arrayUnidadMedida.push(unidadMedida);
                arrayUnidadMedidaText.push(unidadMedidaText);
                arrayMoneda.push(moneda);
                arrayMonedaText.push(monedaText);
                arrayPrecio.push(precio);
                arrayDescuento.push(descuento);
                arrayIncluyeIGV.push(incluyeIGV);
                arrayCheckIGV.push(checkIGV);

                listaPrecioDetalle.push([precioTipo, precioTipoText, unidadMedida, unidadMedidaText, moneda, monedaText, precio, descuento, bienPrecioId, incluyeIGV, checkIGV]);
            }

//            console.log(listaPrecioDetalle);
//            console.log(listaBienPrecioEliminado);
            onListarPrecioDetalle(listaPrecioDetalle);
            limpiarCamposPrecioDetalle();
            limpiarMensajesPrecioDetalle();

        }
    }
}

function validarFormularioPrecioDetalle(precioTipo, unidadMedida, moneda, precio, descuento) {
    var bandera = true;
    limpiarMensajesPrecioDetalle();

    if (precioTipo === '' || precioTipo === null) {
        $("#msjPrecioTipo").removeProp(".hidden");
        $("#msjPrecioTipo").text("Tipo de precio es obligatorio").show();
        bandera = false;
    }

    if (unidadMedida === '' || unidadMedida === null) {
        $("#msjUnidadMedida").removeProp(".hidden");
        $("#msjUnidadMedida").text("Unidad de medida es obligatorio").show();
        bandera = false;
    }

    if (moneda === '' || moneda === null) {
        $("#msjMoneda").removeProp(".hidden");
        $("#msjMoneda").text("Moneda es obligatorio").show();
        bandera = false;
    }

    if (precio === '' || precio === null) {
        $("#msjPrecio").removeProp(".hidden");
        $("#msjPrecio").text("Precio es obligatorio").show();
        bandera = false;
    }

    if (precio <= 0) {
        $("#msjPrecio").removeProp(".hidden");
        $("#msjPrecio").text("Precio tiene que ser positivo.").show();
        bandera = false;
    }

    if (descuento === '' || descuento === null) {
        $("#msjDescuento").removeProp(".hidden");
        $("#msjDescuento").text("Descuento es obligatorio").show();
        bandera = false;
    }

    return bandera;
}

function limpiarMensajesPrecioDetalle() {
    $("#msjPrecioTipo").hide();
    $("#msjUnidadMedida").hide();
    $("#msjMoneda").hide();
    $("#msjPrecio").hide();
    $("#msjPrecioDetalle").hide();
    $("#msjDescuento").hide();

}

function validarPrecioDetalleRepetido(precioTipo, unidadMedida, moneda, precio, incluyeIGV) {
    var valido = true;

    var idPrecioDetalle = $('#idPrecioDetalle').val();

    //alert(idPrecioDetalle + ' : '+ indicePrecioTipo);

    if (idPrecioDetalle != '') {
        //alert('igual');
        var indice = buscarPrecioDetalle(precioTipo, unidadMedida, moneda, precio, incluyeIGV);
//        console.log(indice,idPrecioDetalle);
        if (indice != idPrecioDetalle && indice != -1) {
            $.Notification.autoHideNotify('warning', 'top right', 'Validación', "El precio ya ha sido agregado");
            valido = false;
        } else {
            valido = true;
        }
    } else {
        //alert('diferente');
        var indice = buscarPrecioDetalle(precioTipo, unidadMedida, moneda, precio, incluyeIGV);
        if (indice > -1) {
            $.Notification.autoHideNotify('warning', 'top right', 'Validación', "El precio ya ha sido agregado");
            valido = false;
        }
    }

    return valido;
}

function onListarPrecioDetalle(data) {
    $('#dataTablePrecio tbody tr').remove();
    var cuerpo = "";
    var ind = 0;
    if (!isEmpty(data)) {
        data.forEach(function (item) {

            //listaPrecioDetalle.push([ precioTipo, precioTipoText, unidadMedida, unidadMedidaText, moneda, monedaText, precio,descuento,bienPrecioId,incluyeIGV,checkIGV]);


            var eliminar = "<a href='#' onclick = 'eliminarPrecioDetalle(\""
                    + item['0'] + "\", \"" + item['2'] + "\", \"" + item['4'] + "\", \"" + item['6'] + "\", \"" + item['9'] + "\")' >"
                    + "<i id='e" + ind + "' class='fa fa-trash-o' style='color:#cb2a2a;'></i></a>";

            var editar = "<a href='#' onclick = 'editarPrecioDetalle(\""
                    + item['0'] + "\", \"" + item['2'] + "\", \"" + item['4'] + "\", \"" + item['6'] + "\", \"" + ind + "\")' >"
                    + "<i id='e" + ind + "' class='fa fa-edit' style='color:#E8BA2F;'></i></a>&nbsp;&nbsp;&nbsp;";

//            var incluyeIGV='';
//            if (item['9']==1) {
//                incluyeIGV = "Si";
//            } else {
//                incluyeIGV = "No";
//            }
            cuerpo += "<tr>"
                    + "<td style='text-align:left;'>" + item['1'] + "</td>"
                    + "<td style='text-align:left;'>" + item['3'] + "</td>"
                    + "<td style='text-align:left;'>" + item['5'] + "</td>"
                    + "<td style='text-align:right;'>" + (item['6'] * 1).toFixed(2) + "</td>"
                    + "<td style='text-align:right;'>" + (item['9'] * 1).toFixed(2) + "</td>"
                    + "<td style='text-align:right;'>" + (item['7'] * 1).toFixed(2) + "</td>"
                    + "<td style='text-align:center;'>" + editar + eliminar
                    + "</td>"
                    + "</tr>";

            ind++;
        });

        $('#dataTablePrecio tbody').append(cuerpo);
    }
}

function limpiarCamposPrecioDetalle() {
//    asignarValorSelect2('cboPrecioTipo', null);
//    asignarValorSelect2('cboMoneda', 2);
//    asignarValorSelect2('cboUnidadMedida', null);
    $('#txtprecio').val('');
    $('#txtDescuento').val('0.00');
    $('#idPrecioDetalle').val('');
}

function buscarPrecioDetalle(precioTipo, unidadMedida, moneda, precio, incluyeIGV) {
    var tam = arrayPrecioTipo.length;
    var ind = -1;
    for (var i = 0; i < tam; i++) {
        if (arrayPrecioTipo[i] === precioTipo && arrayUnidadMedida[i] === unidadMedida && arrayMoneda[i] === moneda /*&& arrayPrecio[i] === precio && arrayIncluyeIGV[i] === incluyeIGV*/) {
            ind = i;
            break;
        }
    }
    return ind;
}

function editarPrecioDetalle(precioTipo, unidadMedida, moneda, precio, ind) {
    var indice = ind;
    //var indice = buscarObjEspecifico(variable, formula, medicion, puesto, meta, metaValor);

    asignarValorSelect2('cboPrecioTipo', arrayPrecioTipo[indice]);
    asignarValorSelect2('cboUnidadMedida', arrayUnidadMedida[indice]);
    asignarValorSelect2('cboMoneda', arrayMoneda[indice]);

//    $('#txtprecio').val(arrayPrecio[indice]);
    $('#txtDescuento').val(redondearNumero(arrayDescuento[indice]).toFixed(2));
    $('#idPrecioDetalle').val(ind);

    if (arrayCheckIGV[indice] == 1) {
        document.getElementById("chkIncluyeIGV").checked = true;
        $('#txtprecio').val(redondearNumero(arrayIncluyeIGV[indice]).toFixed(2));
    } else {
        document.getElementById("chkIncluyeIGV").checked = false;
        $('#txtprecio').val(redondearNumero(arrayPrecio[indice]).toFixed(2));
    }
}

var listaBienPrecioEliminado = [];

function eliminarPrecioDetalle(precioTipo, unidadMedida, moneda, precio, incluyeIGV) {
    var indice = buscarPrecioDetalle(precioTipo, unidadMedida, moneda, precio, incluyeIGV);
    if (indice > -1) {
        arrayPrecioTipo.splice(indice, 1);
        arrayPrecioTipoText.splice(indice, 1);
        arrayUnidadMedida.splice(indice, 1);
        arrayUnidadMedidaText.splice(indice, 1);
        arrayMoneda.splice(indice, 1);
        arrayMonedaText.splice(indice, 1);
        arrayPrecio.splice(indice, 1);
        arrayDescuento.splice(indice, 1);
        arrayIncluyeIGV.splice(indice, 1);
        arrayCheckIGV.splice(indice, 1);
    }

    if (!isEmpty(arrayBienPrecioId[indice])) {
        var bienPrecioId = arrayBienPrecioId[indice];
        arrayBienPrecioId.splice(indice, 1);
        listaBienPrecioEliminado.push([bienPrecioId]);
    }

    listaPrecioDetalle = [];
    var tam = arrayPrecioTipo.length;
    for (var i = 0; i < tam; i++) {
        listaPrecioDetalle.push([arrayPrecioTipo[i], arrayPrecioTipoText[i], arrayUnidadMedida[i], arrayUnidadMedidaText[i], arrayMoneda[i], arrayMonedaText[i], arrayPrecio[i], arrayDescuento[i], arrayBienPrecioId[i], arrayIncluyeIGV[i], arrayCheckIGV[i]]);
    }

    onListarPrecioDetalle(listaPrecioDetalle);
}

function obtenerDecuento() {
//    var descuento=dataPrecioTipo[document.getElementById('cboPrecioTipo').options.selectedIndex]['descuento'];
//    descuento=redondearNumero(descuento).toFixed(2);
//    $('#txtDescuento').val(descuento);
}

function habilitarDivMarcaTexto() {
    $("#contenedorMarcaDivCombo").hide();
    $("#contenedorMarcaDivTexto").show();
}

function habilitarDivMarcaCombo() {
    $("#contenedorMarcaDivTexto").hide();
    $("#contenedorMarcaDivCombo").show();
}

function habilitarDivMaquinariaTexto() {
    $("#contenedorMaquinariaDivCombo").hide();
    $("#contenedorMaquinariaDivTexto").show();
}

function habilitarDivMaquinariaCombo() {
    $("#contenedorMaquinariaDivTexto").hide();
    $("#contenedorMaquinariaDivCombo").show();
}

function validarToken() {
    if (token == 2) {
        setTimeout("self.close();", 700);
    }
}

function validaNumero(e) {
    var tecla = (document.all) ? e.keyCode : e.which;

    //Tecla de retroceso para borrar, siempre la permite
    if (tecla == 8) {
        return true;
    }

    // Patron de entrada, en este caso solo acepta numeros
    var patron = /[0-9]/;
    var tecla_final = String.fromCharCode(tecla);
    return patron.test(tecla_final);
}




var contadorCentroCosto = 1;
var dataCentroCosto = [];
var dataCentroCostoPersona = [];

function obtenerCentroCostoBien() {
    let arrayCentroCostoBien = [];
    let banderaValidacion = false;
    let totalPorcentaje = 0;
    for (var i = 1; i <= contadorCentroCosto; i++) {
        if ($('#cboCentroCosto_' + i).length > 0) {
            let centro_costo_id = select2.obtenerValor('cboCentroCosto_' + i);
            let porcentaje = $('#txtPorcentaje_' + i).val();

            if (isEmpty(centro_costo_id) || isEmpty(porcentaje)) {
                $.Notification.autoHideNotify('warning', 'top right', 'Validación', "Debe seleccionar los centro de costo o el porcentaje.");
                banderaValidacion = true;
                break;
            } else {
                totalPorcentaje += porcentaje * 1;
                arrayCentroCostoBien.push({centro_costo_id: centro_costo_id, porcentaje: porcentaje});
            }
        }
    }

    if (banderaValidacion) {
        return [];
    }

    if (totalPorcentaje != 100) {
        $.Notification.autoHideNotify('warning', 'top right', 'Validación', "El total de porcentaje debe ser 100%.");
        return [];
    }

    return arrayCentroCostoBien;
}

function cargarCentroCostoBien(data) {
    $('#dataTableCentroCostoBien tbody tr').remove();
    if (!isEmpty(data)) {
        $.each(data, function (index, item) {
            agregarCentroCostoBien(item.centro_costo_id, item.porcentaje);
        });
    } else {
        agregarCentroCostoBien();
    }
}

function agregarCentroCostoBien(centroCosto, porcentaje) {
    let indice = contadorCentroCosto;

    let eliminar = "<a  onclick='eliminarCentroCostoBienDetalle(" + indice + ");'><i class='fa fa-trash-o' style='color:#cb2a2a;'></i></a>";
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
    $('#dataTableCentroCostoBien tbody').append(cuerpo);
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

function eliminarCentroCostoBienDetalle(indice) {
    $('#trCentroCostoPersona_' + indice).remove();
}