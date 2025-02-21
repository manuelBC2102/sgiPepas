<body>
    <div class="row">
        <div class="col-md-12 ">
            <div class="panel panel-default">
                <div class="panel-body">
                    <h3><b>Retenciones</b></h3>
                    <div class="col-md-12 ">
                        <div class="panel-body">
                            <div class="row">
                                <div class="form-group col-md-3">
                                <label>Persona Encargada *</label>
                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <input type="text" id="txtPersonaE" name="txtPersonaE" class="form-control" aria-required="true" readonly value="" maxlength="250" />
                                    </div>
                                    <span id='msjFechaEntrega' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                </div>

                                <div class="form-group col-md-3">
                                    <label>Tipo Cambio *</label>
                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <input type="text" id="txtTipoC" name="txtTipoC" class="form-control" aria-required="true"  value="" maxlength="250" />
                                    </div>
                                    <span id='msjFechaEntrega' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Fecha  *</label>
                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <input type="text" id="txtFecha" name="txtFecha" class="form-control" aria-required="true" readonly value="" maxlength="250" />
                                    </div>
                                    <span id='msjFechaEntrega' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                </div>
                                <!-- <div class="form-group col-md-3">
                                                    <label>Zona *</label>
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <select name="cboTipoArchivo" id="cboTipoArchivo"
                                                            class="select2">

                                                        </select>

                                                    </div>
                                                    <span id="msjContacto" class="control-label"
                                                        style="color:red;font-style: normal;" hidden></span>
                                                </div> -->
                            
                            </div>
                            <div class="row">
                                <br>
                            <div class="form-group col-md-3">
                                  
                                </div>
                                <div class="form-group col-md-3">
                                <label>RUC Proveedor  *</label>
                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <input type="text" id="txtRUC" name="txtRUC" class="form-control" aria-required="true"   value="" maxlength="250" />
                                    </div>
                                </div>
                            <div class="form-group col-md-3">
                                    <br>
                                    <button type="button" onclick="setearInputPlaca()" value="buscar" name="env" id="buscar" class="btn btn-info w-sm m-b-5" style="border-radius: 0px;"><i class="fa fa-search"></i>&ensp;Buscar</button>&nbsp;&nbsp;
                                </div>
                            </div>

                            <div class="row">
                            <h4><b>DATOS PROVEEDOR</b></h4><br>
                                <div class="form-group col-md-3">
                                <label>Razon Social * </label>
                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <input type="text" id="txtRazonSocial" name="txtRazonSocial" class="form-control" readonly aria-required="true"  value="" maxlength="250" />
                                    </div>
                                    <span id='msjFechaEntrega' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                </div>

                                <div class="form-group col-md-3">
                                    <label>Ubigeo *</label>
                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <input type="text" id="txtUbigeo" name="txtUbigeo" class="form-control" aria-required="true"   value="" maxlength="250" />
                                    </div>
                                    <span id='msjFechaEntrega' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Departamento  *</label>
                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <input type="text" id="txtDepartamento" name="txtDepartamento" class="form-control"  aria-required="true"  value="" maxlength="250" />
                                    </div>
                                    <span id='msjFechaEntrega' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Provincia  *</label>
                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <input type="text" id="txtProvincia" name="txtProvincia" class="form-control"  aria-required="true"  value="" maxlength="250" />
                                    </div>
                                    <span id='msjFechaEntrega' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                </div>
                                </div>
                                <div class="row">
                                <div class="form-group col-md-3">
                                    <label>Distrito  *</label>
                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <input type="text" id="txtDistrito" name="txtDistrito" class="form-control"  aria-required="true"  value="" maxlength="250" />
                                    </div>
                                    <span id='msjFechaEntrega' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                </div>
                              

                                <div class="form-group col-md-6">
                                    <label>Dirección  *</label>
                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <input type="text" id="txtDireccion" name="txtDireccion" class="form-control"   aria-required="true"  value="" maxlength="250" />
                                    </div>
                                    <span id='msjFechaEntrega' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                </div>
                              
                            
                            </div>

                            <div class="row">
                            <h4><b>DATOS FACTURA</b></h4><br>
                                <div class="form-group col-md-3">
                                    <label>Serie-Numero Factura  *</label>
                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <input type="text" id="txtFactura" name="txtFactura" class="form-control"  aria-required="true"  value="" maxlength="250" />
                                    </div>
                                    <span id='msjFechaEntrega' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                </div>

                                <div class="form-group col-md-3">
                                    <label>Fecha Factura  *</label>
                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <input type="date" id="txtFechaFactura" name="txtFechaFactura" class="form-control"  aria-required="true"  value="" maxlength="250" />
                                    </div>
                                    <span id='msjFechaEntrega' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                </div>
                              

                                <div class="form-group col-md-3">
                                    <label>Monto Factura  *</label>
                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <input type="text" id="txtMontoFactura" name="txtMontoFactura" class="form-control"   aria-required="true"  value="" maxlength="250" />
                                    </div>
                                    <span id='msjFechaEntrega' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                </div>
                                <div class="form-group col-md-3">
                            <label>Moneda *</label>
                            <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <!-- Select de Moneda con opciones -->
                                <select name="cboMoneda" id="cboMoneda" class="select2">
                                    <option value="soles">Soles</option>
                                    <option value="dolares">Dólares</option>
                                </select>
                            </div>
                            <span id="msjContacto" class="control-label" style="color:red;font-style: normal;" hidden></span>
                        </div>
                            </div>
                           <div class="row">
                            <h4><b>DATOS ADICIONALES</b></h4><br>
                           <div class="form-group col-md-3">
                                    <label>Porcentaje Retención (3%)  *</label>
                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <input type="text" id="txtRetencion" name="txtRetencion" class="form-control"   aria-required="true" readonly value="3" maxlength="250" />
                                    </div>
                                    <span id='msjFechaEntrega' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                </div>

                                <div class="form-group col-md-3">
                                    <label>Fecha Pago  *</label>
                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <input type="date" id="txtFechaPago" name="txtFechaPago" class="form-control"   aria-required="true"  value="" maxlength="250" />
                                    </div>
                                    <span id='msjFechaEntrega' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                </div>
                           </di>
                        </div>
                        <form id="frmPersonaNatural" class="form">
                            <div class="row">
                               <br>
                               
                               
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <a href="#" class="btn btn-danger m-b-5" id="id" style="border-radius: 0px;" onclick="cargarListarActaCancelar()"><i class="fa fa-close"></i>&ensp;Cancelar</a>&nbsp;&nbsp;&nbsp;
                                        <button type="button" onclick="guardarSolicitud()" value="enviar" name="env" id="env" class="btn btn-info w-sm m-b-5" style="border-radius: 0px;"><i class="fa fa-send-o"></i>&ensp;Enviar</button>&nbsp;&nbsp;
                                    </div>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!--        <script src="vistas/libs/imagina/assets/select2/select2.min.js"></script>-->
    <script src="vistas/com/retenciones/retenciones_form.js"></script>
</body>

<style type="text/css" media="screen">
    @media screen and (max-width: 1000px) {
        #scroll {
            width: 1000px;
        }

        #muestrascroll {
            overflow-x: scroll;
        }
    }


    #datatable td {
        vertical-align: middle;
    }

    .sweet-alert button.cancel {
        background-color: rgba(224, 70, 70, 0.8);
    }

    .sweet-alert button.cancel:hover {
        background-color: #E04646;
    }

    .sweet-alert {

        border-radius: 0px;

    }

    .sweet-alert button {
        -webkit-border-radius: 0px;
        border-radius: 0px;

    }
</style>


