<div class="row">
    <div class="col-md-12 ">
        <div class="panel panel-default">
            <div class="panel-body">
                <h4><b id="titulo" ></b></h4>
                <div class="col-md-12 ">
                    <div class="panel-body">
                        <form  id="frm_bien"  method="post" class="form" enctype="multipart/form-data;charset=UTF-8">
                            <input type="hidden" name="usuario" id="usuario" value="<?php echo $_SESSION['id_usuario']; ?>"/>
                            <input type="hidden" name="id" id="id" value="<?php echo $id ?>"/>
                            <div class="row">
                                <ul class="nav nav-tabs nav-justified">
                                    <li class="active">
                                        <a href="#tabGeneral" data-toggle="tab" aria-expanded="true">
                                            <span class="visible-xs"><i class="fa fa-home"></i></span>
                                            <span class="hidden-xs">General</span>
                                        </a>
                                    </li>
                                    <!-- <li class="">
                                        <a href="#tabProveedores" data-toggle="tab" aria-expanded="false">
                                            <span class="visible-xs"><i class="ion-person-stalker"></i></span>
                                            <span class="hidden-xs">Proveedores</span>
                                        </a>
                                    </li> -->
                                    <li class="">
                                        <a href="#tabPrecios" data-toggle="tab" aria-expanded="false">
                                            <span class="visible-xs"><i class="ion-pricetags"></i></span>
                                            <span class="hidden-xs">Precios</span>
                                        </a>
                                    </li>

                                    <li class="">
                                        <a href="#tabActivoFijo" data-toggle="tab" aria-expanded="false">
                                            <span class="visible-xs"><i class="ion-pricetags"></i></span>
                                            <span class="hidden-xs">Activo Fijo</span>
                                        </a>
                                    </li>

                                </ul>
                                <div class="tab-content">
                                    <!--PESTAÑA GENERAL-->
                                    <div class="tab-pane active" id="tabGeneral">
                                        <div class="row">
                                            <div class="form-group col-md-6 ">
                                                <label id="codigoProductoText">C&oacute;digo de producto *</label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <input  type="text" id="txt_codigo" name="txt_codigo" class="form-control" value="" maxlength="45"/>
                                                </div>
                                                <span id='msj_codigo' class="control-label"
                                                      style='color:red;font-style: normal;' hidden></span>
                                            </div>

                                            <div class="form-group col-md-6" id="contenedorModelo">
                                                <label>Modelo </label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <input type="text" id="txt_modelo" name="txt_modelo" class="form-control" required="" aria-required="true" value="" maxlength="500"/>
                                                </div>
                                                <span id='msj_modelo' class="control-label"
                                                      style='color:red;font-style: normal;' hidden></span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                <label>Descripci&oacute;n *</label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <input type="text" id="txt_descripcion" name="txt_descripcion" class="form-control" required="" aria-required="true" value="" maxlength="500"/>
                                                </div>
                                                <span id='msj_descripcion' class="control-label"
                                                      style='color:red;font-style: normal;' hidden></span>
                                            </div>
                                            <div class="form-group col-md-6 " id="contenedorSeriNumero">
                                                <label>Serie y número </label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <input  type="text" id="txt_serie_numero" name="txt_serie_numero" class="form-control" value="" maxlength="45"/>
                                                </div>
                                                <span id='msj_serie_numero' class="control-label"
                                                      style='color:red;font-style: normal;' hidden></span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col-md-6" id="contenedorMarca" hidden="true">
                                                <label>Marca</label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">                                                    
                                                    <div id="contenedorMarcaDivCombo">
                                                        <div class="input-group">
                                                            <select name="cboMarca" id="cboMarca" class="select2 ">
                                                            </select>
                                                            <span class="input-group-btn">
                                                                <button type="button" class="btn btn-effect-ripple btn-primary" onclick="habilitarDivMarcaTexto()"><i class="ion-plus"></i></button>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div id="contenedorMarcaDivTexto" hidden="true" >
                                                        <div class="input-group">
                                                            <input  type="text" id="txtMarca" name="txtMarca" class="form-control" value="" maxlength="200"/>                                                                                                                
                                                            <span class="input-group-btn">
                                                                <button type="button" class="btn btn-effect-ripple btn-primary" onclick="habilitarDivMarcaCombo()"><i class="ion-close-round"></i></button>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>                                                
                                                <span id='msjMarca' class="control-label"
                                                      style='color:red;font-style: normal;' hidden></span>
                                            </div>
                                            <div class="form-group col-md-6" id="contenedorBienTipo" hidden="true">
                                                <label>Grupo de producto *</label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <select name="cboBienTipo" id="cboBienTipo" class="select2" onchange="onchangeTipoBien(this.value);">
                                                    </select>
                                                    <i id='msj_tipo'
                                                       style='color:red;font-style: normal;' hidden></i>
                                                </div>

                                            </div>
                                            <!-- <div class="form-group col-md-6" id="contenedorMaquinaria" hidden="true">
                                                <label>Maquinaria</label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">                                                    
                                                    <div id="contenedorMaquinariaDivCombo">
                                                        <div class="input-group">
                                                            <select name="cboMaquinaria" id="cboMaquinaria" class="select2 ">
                                                            </select>
                                                            <span class="input-group-btn">
                                                                <button type="button" class="btn btn-effect-ripple btn-primary" onclick="habilitarDivMaquinariaTexto()"><i class="ion-plus"></i></button>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div id="contenedorMaquinariaDivTexto" hidden="true" >
                                                        <div class="input-group">
                                                            <input  type="text" id="txtMaquinaria" name="txtMaquinaria" class="form-control" value="" maxlength="100"/>                                                                                                                
                                                            <span class="input-group-btn">
                                                                <button type="button" class="btn btn-effect-ripple btn-primary" onclick="habilitarDivMaquinariaCombo()"><i class="ion-close-round"></i></button>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>                                                
                                                <span id='msjMaquinaria' class="control-label"
                                                      style='color:red;font-style: normal;' hidden></span>
                                            </div> -->
                                        </div>
                                        <!-- <div class="row">                                
                                            <div class="form-group col-md-6" id="contenedorCodigoBarras" hidden="true">
                                                <label>Código de barras</label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <input  type="number" id="txtCodigoBarras" name="txtCodigoBarras" class="form-control" value=""  onkeyup="if (this.value.length > 45) {
                                                                this.value = this.value.substring(0, 45)
                                                            }"/>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-6" id="contenedorCodigoFabricante" hidden="true">
                                                <label>Código de fabricante</label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <input  type="text" id="txtCodigoFabricante" name="txtCodigoFabricante" class="form-control" value=""  maxlength="45"/>
                                                </div>
                                                <span id='msjCodigoFabricante' class="control-label"
                                                      style='color:red;font-style: normal;' hidden></span>
                                            </div>
                                        </div> -->
                                        <div class="row">
                                            <div class="form-group col-md-6" id="contenedorUnidadTipo" hidden="true">
                                                <label id="lb_empresa">Tipo de unidades *</label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <select name="cboUnidadTipo" id="cboUnidadTipo" class="select2" multiple onchange="onchangeUnidadTipo();">
                                                    </select>
                                                    <span id='msj_UnidadTipo' class="control-label"
                                                          style='color:red;font-style: normal;' hidden></span>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-6" id="contenedorUnidadControl" hidden="true">
                                                <label>Unidad Control *</label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <select name="cboUnidadControl" id="cboUnidadControl" class="select2" onchange="">
                                                    </select>
                                                    <i id='msj_unidad_control'
                                                       style='color:red;font-style: normal;' hidden></i>
                                                </div>

                                            </div>
                                        </div>

                                        <div class="row">
                                            <!-- <div class="form-group col-md-6" id="contenedorCantidadMinima" hidden="true">
                                                <label>Cantidad Minima </label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <input type="number" id="txt_cant_minima" name="txt_cant_minima" class="form-control" required="" aria-required="true" value="0"   onkeyup="if (this.value.length > 19) {
                                                                this.value = this.value.substring(0, 19)
                                                            }"/>
                                                </div>
                                                <span id='msj_cant_minima' class="control-label"
                                                      style='color:red;font-style: normal;' hidden></span>
                                            </div> -->



                                        </div>

                                        <div class="row">

                                            <div class="form-group col-md-6">
                                                <label>Estado *</label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <!--<span class="input-group-addon white-bg " data-toggle="tooltip" data-placement="bottom"  title="" data-html='true' data-original-title="<?php echo $alerta; ?>"><i  class="ion-alert"></i></span>-->
                                                    <select name="cboEstado" id="cboEstado" class="select2">
                                                        <option value="1" selected>Activo</option>
                                                        <option value="0">Inactivo</option>
                                                    </select>
                                                    <span id='msj_estado' class="control-label"
                                                          style='color:red;font-style: normal;' hidden></span>
                                                </div>
                                            </div> 
                                            <div class="form-group col-md-6">        
                                                <label id="lb_empresa">Empresas *</label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <select name="cboEmpresa" id="cboEmpresa" class="select2" onchange="onchangeEmpresa();" multiple>
                                                    </select>
                                                    <span id='msj_empresa' class="control-label"
                                                          style='color:red;font-style: normal;' hidden></span>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="row">                                            
                                            <div class="form-group col-md-6" id="contenedorCodigoSunat" hidden="true">
                                                <label>Tipo existencia Sunat</label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <select name="cboCodigoSunat" id="cboCodigoSunat" class="select2">
                                                    </select>
                                                    <i id='msjCodigoSunat' style='color:red;font-style: normal;' hidden></i>
                                                </div>
                                            </div>                                        
                                            <!-- <div class="form-group col-md-6" id="contenedorCuentaContable" hidden="true">   
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <div class="row">
                                                        <div class="form-group col-md-6">
                                                            <label>Cuenta contable</label>
                                                            <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12" id="divCuentaContable">
                                                                <select name="cboCuentaContable" id="cboCuentaContable" class="select2">
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Código contable</label>
                                                            <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                                <input type="text" id="txtCodigoCuenta" name="txtCodigoCuenta" class="form-control" value="" maxlength="6"  onkeypress="return validaNumero(event)"/>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div> -->
                                        </div>

                                        <div class="row">
                                            <div class="form-group col-md-6" id="contenedorCostoInicial" hidden="true">
                                                <label>Costo inicial </label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <input type="number" id="txtCostoInicial" name="txtCostoInicial" class="form-control" required="" aria-required="true" value=""   onkeyup="if (this.value.length > 20) {
                                                                this.value = this.value.substring(0, 20)
                                                            }"/>
                                                </div>
                                            </div>

                                            <!-- <div class="form-group col-md-6" >
                                                <label>Código internacional </label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <input type="text" id="txtCodigoInternacional" name="txtCodigoInternacional" class="form-control" required="" aria-required="true" value="" maxlength="45"  />
                                                </div>
                                            </div> -->

                                        </div>
                                        <br>
                                        <div class="row">                                

                                            <div class="form-group col-md-3">
                                                <!--<label>Imagen</label>-->
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <div class="fileUpload btn w-lg m-b-5" id="multi" style="border-radius: 0px;background-color: #337Ab7;color: #fff;cursor:default;">
                                                        <div id="edi" ><i class="ion-upload m-r-15" style="font-size: 16px;"></i>Subir imagen</div>
                                                        <input name="file" id="file"  type="file" accept="image/*" class="upload" onchange='$("#upload-file-info").html($(this).val().slice(12));' >
                                                    </div>
                                                    &nbsp; &nbsp; <b class='' id="upload-file-info">Ninguna imagen seleccionada</b>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <img id="myImg" src="vistas/com/bien/imagen/bienNone.jpg" onerror="this.src='vistas/com/bien/imagen/bienNone.jpg'" alt="" class="img-thumbnail profile-img thumb-lg" />
                                                <input type="hidden" id="secretImg" value="" />                                       
                                                <script>
                                                    $(function () {
                                                        $(":file").change(function () {
                                                            if (this.files && this.files[0]) {
                                                                var reader = new FileReader();
                                                                reader.onload = imageIsLoaded;
                                                                reader.readAsDataURL(this.files[0]);
                                                            }
                                                        });
                                                    });
                                                    function imageIsLoaded(e) {
                                                        $('#secretImg').attr('value', e.target.result);
                                                        $('#myImg').attr('src', e.target.result);
                                                        $('#myImg').attr('width', '128px');
                                                        $('#myImg').attr('height', '128px');
                                                    }
                                                    ;
                                                </script>
                                            </div>
                                            <div id="bcTarget"></div>


                                            <div class="form-group col-md-6">
                                                <label>Comentario </label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <textarea type="text" id="txt_comentario" name="txt_comentario" class="form-control" value="" maxlength="500"></textarea>
                                                </div>
                                            </div>
                                        </div>                                      
                                    </div>
                                    <!--FIN PESTAÑA GENERAL-->

                                    <!--PESTAÑA PROVEEDORES-->
                                    <div class="tab-pane" id="tabProveedores">


                                        <!--<div class="panel panel-body">-->
                                        <div class="row">
                                            <div class="col-md-2 col-sm-2 col-xs-2"></div>                                                                
                                            <div class="col-md-8 col-sm-8 col-xs-8">
                                                <table id="datatable" class="table" style="width: 100%">
                                                    <thead>
                                                        <tr>
                                                            <th style='text-align:center;' WIDTH="30%" >Prioridad</th>
                                                            <th style='text-align:center;'>Proveedor</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td  style='text-align:center;' WIDTH="30%">1</td>
                                                            <td><select id="cboProveedor1" name="cboProveedor1" class="select2" onchange="agregarProveedor('cboProveedor1', 1)"></select></td>
                                                        </tr>
                                                        <tr>
                                                            <td  style='text-align:center;' WIDTH="30%">2</td>
                                                            <td><select id="cboProveedor2" name="cboProveedor2" class="select2" onchange="agregarProveedor('cboProveedor2', 2)"></select></td>
                                                        </tr>
                                                        <tr>
                                                            <td  style='text-align:center;' WIDTH="30%">3</td>
                                                            <td><select id="cboProveedor3" name="cboProveedor3" class="select2" onchange="agregarProveedor('cboProveedor3', 3)"></select></td>
                                                        </tr>
                                                        <tr>
                                                            <td  style='text-align:center;' WIDTH="30%">4</td>
                                                            <td><select id="cboProveedor4" name="cboProveedor4" class="select2" onchange="agregarProveedor('cboProveedor4', 4)"></select></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="col-md-2 col-sm-2 col-xs-2"></div>
                                        </div>
                                        <!--</div>-->
                                        <div class="row">
                                            <div class="col-md-2 col-sm-2 col-xs-2"></div>
                                            <div class="col-md-8 col-sm-8 col-xs-8">

                                                <button type="button" class="btn btn-primary  w-md" data-dismiss="modal" onclick="reiniciarComboProveedores()">
                                                    <i class="fa fa-remove"></i>&ensp;Limpiar
                                                </button>                                                                

                                            </div>
                                            <div class="col-md-2 col-sm-2 col-xs-2"></div>
                                        </div>
                                        <!--                                                        <button type="button" class="btn btn-danger w-md m-b-5" onclick="validarCerrarModal()">
                                                                                                    <i class="fa fa-remove"></i>&ensp;Cerrar
                                                                                                </button>-->

                                    </div>
                                    <!--FIN PESTAÑA PROVEEDORES-->

                                    <!--PESTAÑA PRECIOS-->
                                    <div class="tab-pane" id="tabPrecios">
                                        <!--                                        <div class="row">
                                                                                    <div class="form-group col-md-6">
                                                                                        <label>Precio de  compra *</label>
                                                                                        <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                                                            <input type="number" id="txt_precio_compra" name="txt_precio_compra" class="form-control" required="" aria-required="true" value=""/>
                                                                                        </div>
                                                                                        <span id='msj_precio_compra' class="control-label"
                                                                                              style='color:red;font-style: normal;' hidden></span>
                                                                                    </div>
                                        
                                                                                    <div class="form-group col-md-6">
                                                                                        <label>Precio de  venta *</label>
                                                                                        <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                                                            <input type="number" id="txt_precio_venta" name="txt_precio_venta" class="form-control" required="" aria-required="true" value=""/>
                                                                                        </div>
                                                                                        <span id='msj_precio_venta' class="control-label"
                                                                                              style='color:red;font-style: normal;' hidden></span>
                                                                                    </div>  
                                                                                </div>-->

                                        <input type="hidden" id="idPrecioDetalle" value="" />
                                        <div class="row">
                                            <div class="form-group col-md-4">
                                                <label>Tipo precio *</label>
                                                <div class="form-group">
                                                    <select name="cboPrecioTipo" id="cboPrecioTipo" class="select2" onchange="obtenerDecuento()"></select>
                                                </div>
                                                <span id="msjPrecioTipo" class="control-label" style="color:red;font-style: normal;" hidden></span>
                                            </div>
                                            <div class="form-group col-md-5">
                                                <label>Unidad medida *</label>
                                                <div class="form-group">
                                                    <select name="cboUnidadMedida" id="cboUnidadMedida" class="select2"></select>
                                                </div>
                                                <span id="msjUnidadMedida" class="control-label" style="color:red;font-style: normal;" hidden></span>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Descuento de utilidad *</label>
                                                <div class="input-group col-md-12">                                                
                                                    <input type="number" name="txtDescuento" id="txtDescuento"  class="form-control" style="text-align: right" onkeyup="if (this.value.length > 19) {
                                                                this.value = this.value.substring(0, 19)
                                                            }">
                                                </div>
                                                <span id="msjDescuento" class="control-label" style="color:red;font-style: normal;" hidden></span>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="form-group col-md-4">
                                                <label>Moneda *</label>
                                                <div class="form-group">
                                                    <select name="cboMoneda" id="cboMoneda" class="select2"></select>
                                                </div>
                                                <span id="msjMoneda" class="control-label" style="color:red;font-style: normal;" hidden></span>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Precio *</label>
                                                <div class="input-group col-md-12">                                                
                                                    <input type="number" name="txtprecio" id="txtprecio"  class="form-control"  style="text-align: right" onkeyup="if (this.value.length > 19) {
                                                                this.value = this.value.substring(0, 19)
                                                            }">
                                                </div>
                                                <span id="msjPrecio" class="control-label" style="color:red;font-style: normal;" hidden></span>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label>&nbsp;</label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <div class="checkbox">
                                                        <label class="cr-styled">
                                                            <input type="checkbox" name="chkIncluyeIGV" id="chkIncluyeIGV" checked="true" >
                                                            <i class="fa"></i> 
                                                            Incluye IGV
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>&nbsp;</label>
                                                <div class="input-group col-md-12">
                                                    <button type="button" name="btnGuardar" id="btnGuardar" class="btn btn-info w-sm m-b-5" style="border-radius: 0px;"  onclick="agregarPrecioDetalle()"><i class="fa fa-plus-square-o"></i>&nbsp;Agregar precio</button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="panel panel-body" id="muestrascroll">
                                            <span id="msjPrecioDetalle" class="control-label" style="color:red;font-style: normal;" hidden></span>
                                            <div class="row" id="scroll">
                                                <div class="col-md-12 col-sm-12 col-xs-12" >
                                                    <div class="table">
                                                        <div id="dataList">
                                                            <table id="dataTablePrecio" class="table table-striped table-bordered">
                                                                <thead>
                                                                    <tr>
                                                                        <th style="text-align:center">Tipo precio</th>
                                                                        <th style="text-align:center">Unidad medida</th>
                                                                        <th style="text-align:center">Moneda</th>
                                                                        <th style="text-align:center">P. sin IGV</th>
                                                                        <th style="text-align:center">P. con IGV</th>
                                                                        <th style="text-align:center">Descuento (%)</th>
                                                                        <th style="text-align:center">Acciones</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div style="clear:left">
                                            <p><b>Leyenda:</b>&nbsp;&nbsp;
                                                <i class="fa fa-trash-o" style="color:#cb2a2a;"></i> Eliminar precio&nbsp;&nbsp;&nbsp;
                                                <i class="fa fa-edit" style="color:#E8BA2F;"></i> Editar precio&nbsp;&nbsp;&nbsp;
                                            </p><br>
                                        </div>

                                    </div>
                                    <!--FIN PESTAÑA PRECIOS-->

                                    <!--PESTAÑA ACTIVO FIJO-->
                                    <div class="tab-pane" id="tabActivoFijo">
                                        <div class="row">
                                            <div class="form-group col-md-3">
                                                <label>Método de depreciación</label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <select name="cboMetodoDepreciacion" id="cboMetodoDepreciacion" class="select2" ></select>
                                                    <!--<input type="text" id="txtMetodoDepreciacion" name="txtMetodoDepreciacion" class="form-control" value="" maxlength="45" />-->
                                                </div>
                                                <span id="msjMetodoDepreciacion" class="control-label" style="color:red;font-style: normal;" hidden></span>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Porcentaje(%) *</label>
                                                <!--<div class="input-group col-md-12">-->
                                                <select name="cboDepreciacion" id="cboDepreciacion" class="select2"></select>
<!--                                                    <input type="number" name="txtPorcentajeDepreciacion" id="txtPorcentajeDepreciacion"  class="form-control" style="text-align: right" onkeyup="if (this.value.length > 19) {
                                                            this.value = this.value.substring(0, 19);
                                                        }">-->
                                                <!--</div>-->
                                                <span id="msjPorcentajeDepreciacion" class="control-label" style="color:red;font-style: normal;" hidden></span>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Fecha de adquisición *</label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">            
                                                    <input type="text" class="form-control fecha" placeholder="dd/mm/yyyy" id="txtFechaAdquicion">    
                                                    <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>          
                                                </div>
                                                <span id="msjFechaAdquicion" class="control-label" style="color:red;font-style: normal;" hidden></span>
                                            </div> 
                                            <div class="form-group col-md-3">
                                                <label>Fecha de inicio de uso *</label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">            
                                                    <input type="text" class="form-control fecha" placeholder="dd/mm/yyyy" id="txtFechaInicioUso">    
                                                    <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>          
                                                </div>
                                                <span id="msjFechaInicioUso" class="control-label" style="color:red;font-style: normal;" hidden></span>
                                            </div>
                                        </div>
                                        <br>
                                        <div class="row">
                                            <div class="form-group col-md-3">
                                                <label>Cuenta contable gasto*</label>
                                                <select name="cboCuentaContableGasto" id="cboCuentaContableGasto" class="select2"></select>
                                                <span id="msjCuentaContableGasto" class="control-label" style="color:red;font-style: normal;" hidden></span>

                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Cuenta contable depreciación*</label>
                                                <select name="cboCuentaContableDepreciacion" id="cboCuentaContableDepreciacion" class="select2"></select>
                                                <span id="msjCuentaContableDepreciacion" class="control-label" style="color:red;font-style: normal;" hidden></span>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Cuenta contable venta*</label>
                                                <select name="cboCuentaContableVenta" id="cboCuentaContableVenta" class="select2"></select>
                                                <span id="msjCuentaContableVenta" class="control-label" style="color:red;font-style: normal;" hidden></span>
                                            </div>
                                        </div>
                                        <br>
                                        <div class="row">
                                            <!--<div class="col-md-2"></div>-->
                                            <div class="col-md-10 col-sm-10 col-xs-10">
                                                <label>Distribución contable</label>
                                                <div class="table">
                                                    <table id="dataTableCentroCostoBien" class="table table-striped table-bordered"  width= "100%">
                                                        <thead>
                                                            <tr>                                                                               
                                                                <th style="text-align:center"  width= "60%">Centro Costo  <a onclick="agregarCentroCostoBien();">
                                                                        <i class="fa fa-plus-square" style="color:#E8BA2F;" title="Agregar fila"></i>
                                                                    </a></th>
                                                                <th style="text-align:center"  width= "30%">Porcentaje(%)</th>
                                                                <th style="text-align:center"  width= "10%">Acciones</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div style="clear:left">
                                                    <p><b>Leyenda:</b>&nbsp;&nbsp;
                                                        <i class="fa fa-trash-o" style="color:#cb2a2a;"></i> Eliminar&nbsp;&nbsp;&nbsp;
                                                        <i class="fa fa-plus-square" style="color:#E8BA2F;" title="Agregar fila"></i> Agregar fila
                                                    </p><br>
                                                </div>
                                            </div>
                                            <div class="col-md-2"></div>
                                        </div>
                                    </div>
                                    <!--FIN PESTAÑA ACTIVO FIJO-->
                                </div>       
                            </div> 


                            <br>
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <a href="#" class="btn btn-danger m-b-5" id="id" style="border-radius: 0px;" onclick="cargarPantallaListar()" ><i class="fa fa-close"></i>&ensp;Cancelar</a>&nbsp;&nbsp;&nbsp;
                                    <button type="button" onclick="guardarBien('<?php echo $tipo; ?>')" value="enviar" name="env" id="env" class="btn btn-info w-sm m-b-5" style="border-radius: 0px;"><i class="fa fa-send-o"></i>&ensp;Enviar</button>&nbsp;&nbsp;

                                    <!--                                    <a href="#" class="btn btn-success m-b-5" id="btnProveedor" onclick="abrirModalProveedor()" 
                                                                           style="border-radius: 0px;">
                                                                            <i class="ion-android-add"></i>&ensp;Proveedor
                                                                        </a>                                    -->
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!---------Modal proveedores------->
            <!--            <div id="modalProveedores" class="modal fade" tabindex="-1" role="dialog" 
                             aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                        <h4 class="modal-title">Modal Proveedores</h4>
                                    </div>
                                    <div class="modal-body">
                                        <div class="panel panel-body">
                                            <div class="col-md-12 col-sm-12 col-xs-12">
                                                <table id="datatable" class="table" style="width: 100%">
                                                    <thead>
                                                        <tr>
                                                            <th style='text-align:center;' WIDTH="30%" >Prioridad</th>
                                                            <th style='text-align:center;'>Proveedor</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td  style='text-align:center;' WIDTH="30%">1</td>
                                                            <td><select id="cboProveedor1" name="cboProveedor1" class="select2" onchange="agregarProveedor('cboProveedor1',1)"></select></td>
                                                        </tr>
                                                        <tr>
                                                            <td  style='text-align:center;' WIDTH="30%">2</td>
                                                            <td><select id="cboProveedor2" name="cboProveedor2" class="select2" onchange="agregarProveedor('cboProveedor2',2)"></select></td>
                                                        </tr>
                                                        <tr>
                                                            <td  style='text-align:center;' WIDTH="30%">3</td>
                                                            <td><select id="cboProveedor3" name="cboProveedor3" class="select2" onchange="agregarProveedor('cboProveedor3',3)"></select></td>
                                                        </tr>
                                                        <tr>
                                                            <td  style='text-align:center;' WIDTH="30%">4</td>
                                                            <td><select id="cboProveedor4" name="cboProveedor4" class="select2" onchange="agregarProveedor('cboProveedor4',4)"></select></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <span id="msjProveedor" class="control-label" style="color:red;font-style: normal;" hidden></span>                            
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-primary  w-md" data-dismiss="modal" onclick="reiniciarComboProveedores()">
                                            <i class="fa fa-remove"></i>&ensp;Limpiar
                                        </button>
                                        <button type="button" class="btn btn-danger w-md m-b-5" onclick="validarCerrarModal()">
                                            <i class="fa fa-remove"></i>&ensp;Cerrar
                                        </button>
                                    </div> 
                                </div> 
                            </div>
                        </div>-->
        </div>
    </div>
</div>
<script src="vistas/com/bien/bien_form.js"></script>