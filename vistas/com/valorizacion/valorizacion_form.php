<html lang="es">
    <head>
        
        <style type="text/css" media="screen">


/*            @media  (min-width: 1271px) and (max-width:1824px) {
                #scroll{
                    width: 1200px;
                }
                #muestrascroll{
                    overflow-x:scroll;
                }
            }
            @media screen and (max-width: 1270px) {
                #scroll{
                    width: 1100px;

                }
                #muestrascroll{
                    overflow-x:scroll;
                }

            }*/

           @media screen and (max-width: 1000px) {
                #scroll{
                    width: 1000px;               
                }
                #muestrascroll{
                    overflow-x:scroll;
                }
            }


            #datatable td{
                vertical-align: middle;
            }
            .sweet-alert button.cancel {
                background-color: rgba(224, 70, 70, 0.8);
            }
            .sweet-alert button.cancel:hover {
                background-color:#E04646;
            }
            .sweet-alert {

                border-radius: 0px; 

            }
            .sweet-alert button {
                -webkit-border-radius: 0px; 
                border-radius: 0px; 

            }
        </style>
        <title>Almacen</title>
    </head>
    <body >
        <div class="page-title">
            <h3 class="title">Valorización</h3>
        </div>
        <div class="row">
            <!--<div class="col-md-12 col-md-12 col-xs-12">-->
            <div class="panel panel-default">
                <br><br>
                <div class="panel panel-body" id="muestrascroll">
                <h3 ><b>Lotes aprobados pendientes valorización</b></h3>
                    <div class="col-md-12 col-sm-12 col-xs-12" id="scroll">
                        <div class="table">
                            <div id="dataList" >

                            </div>
                        </div>
                    </div>
                </div>
                <button id="btnAbrirModal" class="btn btn-primary" style="display: none;" onclick="abrirModalSeleccionados()">Registrar Valorización</button>
                <div style="clear:left">
                    
                </div>
            </div>
            <!-- Modal de factura -->
            <div class="modal fade" id="modalFactura" tabindex="-1" role="dialog" aria-labelledby="modalFacturaLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalFacturaLabel">Confirmación de Factura</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Minero</th>
                            <th>Lote</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody id="detalleLotes"></tbody>
                </table>
                <div class="form-group">
                    <label for="serieFactura">Serie</label>
                    <input type="text" class="form-control" id="serieFactura" placeholder="Serie de la factura">
                </div>
                <div class="form-group">
                    <label for="correlativoFactura">Correlativo</label>
                    <input type="text" class="form-control" id="correlativoFactura" placeholder="Correlativo de la factura">
                </div>
                <div class="form-group">
                    <label for="subtotal">Minero</label>
                    <input type="text" class="form-control" id="minero" readonly>
                </div>
                <div class="form-group">
                    <label for="subtotal">Subtotal</label>
                    <input type="text" class="form-control" id="subtotal" readonly>
                </div>
                <div class="form-group">
                    <label for="igv">IGV</label>
                    <input type="text" class="form-control" id="igv" readonly>
                </div>
                <div class="form-group">
                    <label for="totalFactura">Total Factura</label>
                    <input type="text" class="form-control" id="totalFactura" readonly>
                </div>
                <div class="form-group">
                    <label for="detraccion">Detracción</label>
                    <input type="text" class="form-control" id="detraccion" readonly>
                </div>
                <div class="form-group">
                    <label for="netoPago">Neto Pago</label>
                    <input type="text" class="form-control" id="netoPago" readonly>
                </div>
              
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="guardarFactura()">Guardar Factura</button>
            </div>
        </div>
    </div>
</div>


            <div class="modal fade" id="registroModal" tabindex="-1" role="dialog" aria-labelledby="registroModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="registroModalLabel">Registro de resultados finales</h4>
                </div>
                <div class="modal-body">
                    <!-- Contenido del modal -->
                    <form>
                    <div class="form-group col-md-6">
    <label>Ley Final </label>
    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <input 
            type="text" 
            id="txtLey" 
            name="txtLey" 
            class="form-control" 
            aria-required="true" 
            value="" 
            maxlength="250"
            pattern="^\d+(\.\d{1,2})?$" 
            title="Por favor, ingrese solo números, incluidos decimales con hasta 2 dígitos."
            inputmode="decimal" 
            oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" 
        />
    </div>
</div>
<input type="hidden" id="txtaprobacion" >
<input type="hidden" id="txtloteId" >

                        <div class="form-group col-md-6">
                            <label>Documento resultado (.pdf)</label>&nbsp;
                          
                                
                                    <br> 
                                    <div class="fileUpload btn w-lg m-b-2" style="background-color:#321d48; color:white; min-width: 40px;" id="multi" style="border-radius: 0px;background-color: #337Ab7;color: #fff;cursor:default;">
                                        <div id="edi"><i class="ion-upload m-r-15" style="font-size: 10px;"></i>Subir resultado </div>
                                        <input name="file" id="file" type="file" accept="application/pdf" class="upload" onchange='$("#upload-file-info").html($(this).val().slice(10));'>
                                    </div>
                                    <b class='' style="font-size: 10px;" id="upload-file-info">Ningún resultado seleccionado</b>
                                
                                <input type="hidden" id="secretImg" value="" />
                                <script>
                                    $(function() {
                                        $(":file").change(function() {
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
                                    };
                                </script>
                            
                        </div>


                        <br><br>
                        <!-- Agrega más campos según sea necesario -->
                    </form>
                </div>
                <div class="modal-footer">
                    
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarAprobador()" >Guardar</button>
                </div>
            </div>
        </div>
    </div>
        </div>
        <script src="vistas/libs/imagina/assets/datatables/jquery.dataTables.min.js"></script>
        <script src="vistas/libs/imagina/assets/datatables/dataTables.bootstrap.js"></script>  
        <script src="vistas/libs/imagina/assets/sweet-alert/sweet-alert.min.js" type="text/javascript"></script>
        <script src="vistas/com/valorizacion/valorizacion_form.js"></script> 
    </body>
</html>


