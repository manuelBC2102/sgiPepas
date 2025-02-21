<html lang="es">
    <head>
        <style type="text/css" media="screen">

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
            .popover{
                max-width: 100%; 
            }
            th { white-space: nowrap; }
            .alignRight { text-align: right; }
        </style>
    </head>
    <body >
        <div class="page-title">
            <h3 id="titulo" class="title"></h3>
        </div>
        <div class="row">
            <div class="panel panel-default">
                <div class="row">
                    <div class="col-lg-12">
                        <div  class="portlet" >
                            <div class="row">
                                <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <div class="portlet-heading bg-purple m-b-0" 
                                         onclick="colapsarBuscador()"
                                         id="idPopover" title="" data-toggle="popover" 
                                         data-placement="top" data-content="" 
                                         data-original-title="Criterios de búsqueda"
                                         style="padding-top: 13px;padding-bottom: 13px;cursor: pointer; cursor: hand;">
                                        <div class="portlet-widgets">

                                            <!--                                            <a onclick="exportarReporteBalanceExcel()">
                                                                                            <i class="fa fa-file-excel-o"></i>
                                                                                        </a>&nbsp;-->

                                            <a id="loaderBuscar" onclick="loaderBuscar()">
                                                <i class="ion-refresh"></i>
                                            </a>
                                            <span class="divider"></span>
                                            <!--<a data-toggle="collapse" data-parent="#accordion1" href="#bg-info" onclick="cerrarPopover()">
                                                <i class="ion-minus-round"></i>
                                            </a>-->


                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                </div>

                            </div>

                            <div id="bg-info" class="panel-collapse collapse in">
                                <div class="portlet-body">                                   

                                    <div class="row">
                                        <div class="form-group col-md-6 ">
                                            <label>Persona</label>
                                            <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <select name="cboPersona" id="cboPersona" class="select2">
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Fecha emision</label>
                                            <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <div class="row">
                                                    <div class="form-group col-md-6">
                                                        <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                            <input type="text" class="form-control fecha" placeholder="dd/mm/yyyy" id="inicioFechaEmision">
                                                            <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                            <input type="text" class="form-control fecha" placeholder="dd/mm/yyyy" id="finFechaEmision">
                                                            <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>  

                                    </div>
                                    <div class="row">

                                        <div class="form-group col-md-6 ">
                                            <div class="form-group col-md-2">
                                                <label>Serie</label>
                                            </div>
                                            <div class="form-group col-md-12">
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <input type="text" id="txtSerie" name="txtSerie" class="form-control" value="" maxlength="45">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-6 ">
                                            <div class="form-group col-md-2">
                                                <label>Numero</label>
                                            </div>
                                            <div class="form-group col-md-12">
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <input type="text" id="txtNumero" name="txtNumero" class="form-control" value="" maxlength="45">
                                                </div>
                                            </div>
                                        </div>




                                    </div>


                                    <div class="modal-footer">
                                        <button type="button" onclick="exportarReporteBalanceExcel();" value="Exportar" class="btn btn-success">&ensp;Exportar reporte</button>&nbsp;&nbsp;
                                        <!--<button id="btnBuscar" type="button" data-toggle="collapse" data-parent="#accordion1" href="#bg-info" onclick="buscarBalanceConsolidado()" value="enviar" class="btn btn-purple"><i></i>&ensp;Buscar</button>&nbsp;&nbsp;-->
                                        <button type="button" href="#bg-info" onclick="buscarReporteOrdenTrabajo(1)" value="enviar" class="btn btn-purple"> Buscar</button>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row"></div>
                <div class="panel panel-body">
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <!--<div class="table-responsive">-->
                        <div id="dataList">
                            <table id="datatableReporteOrdenTrabajo" class="table table-striped table-bordered">
                                <thead>
                                    <tr>                                            
                                        <th style='text-align:center;'>F. Emisión</th>                                                                                                                                                                                  
                                        <th style='text-align:center;'>Persona</th>                                                                                                                                                                                  
                                        <th style='text-align:center;'>S/N</th>
                                        <th style='text-align:center;'>Moneda</th>
                                        <th style='text-align:center;'>Precio Venta</th>
                                        <th style='text-align:center;'>EAR Rendido</th>
                                        <th style='text-align:center;'>Costos Adicionales</th>                                        
                                        <th style='text-align:center;'>Utilidad Bruta</th>
                                        <th style='text-align:center;'>Cumplimiento EAR</th>
                                        <th style='text-align:center;'>Opc.</th>
                                    </tr>
                                </thead> 
<!--                                    <tfoot>
                                    <tr>
                                        <th colspan="6" style="text-align:right">Totales:</th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th id="totalReporte">S/. 0</th>
                                    </tr>
                                </tfoot>-->
                            </table>
                            <br>
                            <div style="clear:left">
                                <p id="divLeyenda">
                                    <br>
                                    <b>Leyenda:</b>&nbsp;&nbsp;                                                                              
                                    <i class='fa fa-eye' style='color:black;'></i> Ver detalle
                                    &nbsp;&nbsp; * Los montos que se visualizan no incluye IGV
                                </p>
                            </div>
                        </div>
                        <!--</div>-->
                    </div>
                </div>
            </div>
        </div>
        <!--modal detalle-->
        <div id="modalDetalleOrdenTrabajo"  class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
            <div class="modal-dialog" style="width:80%;"> 
                <div class="modal-content"> 
                    <div class="modal-header"> 
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button> 
                        <h4 class="modal-title"></h4> 
                    </div>
                    <div class="modal-body"> 
                        <div class="row">
                            <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <b>Resumen:</b> 
                            </div> 
                        </div>

                        <div class="row" >
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                 <table id="tablaResumen" class="table"></table>                                
                             </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <b>Facturado:</b> 
                            </div> 
                        </div>
                        <div class="row">
                            <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <table id="datatableFacturacionOrdenTrabajo" class="table table-striped table-bordered">
                                    <thead>
                                        <tr style="color:white;" class="portlet-heading bg-primary">
                                            <th style='text-align:center;'>Documento</th>
                                            <th style='text-align:center;'>F. Emisión</th>                                           
                                            <th style='text-align:center;'>S|N</th>   
                                            <th style='text-align:center;'>Estado</th>
                                            <th style='text-align:center;'>Sub total</th>
                                            <th style='text-align:center;'>IGV</th>
                                            <th style='text-align:center;'>Total</th>                                                                                      
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th colspan="4" style="text-align:right">Totales:</th>
                                            <th> </th>
                                            <th> </th>
                                            <th> </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div> 
                        </div>
                        <div class="row">
                            <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <b>Solicitado:</b> 
                            </div> 
                        </div>
                        <div class="row">
                            <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <table id="datatableSolicitadoOrdenTrabajo" class="table table-striped table-bordered">
                                    <thead>
                                        <tr style="color:white;" class="portlet-heading bg-primary">
                                            <th style='text-align:center;'>F. Emisión</th>                                           
                                            <th style='text-align:center;'>N° EAR</th>
                                            <th style='text-align:center;'>Estado</th>
                                            <th style='text-align:center;'>Descripcion</th>  
                                            <th style='text-align:center;'>Monto</th>                                                                                      
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th colspan="4" style="text-align:right">Totales:</th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div> 
                        </div>
                        <div class="row">
                            <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <b>Rendido EAR:</b> 
                            </div> 
                        </div>
                        <div class="row">
                            <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <table id="datatableEAROrdenTrabajo" class="table table-striped table-bordered" >
                                    <thead>
                                        <tr style="color:white;" class="portlet-heading bg-primary">
                                            <th style='text-align:center;'>Documento</th>
                                            <th style='text-align:center;'>F. Emisión</th>                                           
                                            <th style='text-align:center;'>Persona</th>                                             
                                            <th style='text-align:center;'>S|N</th>                                             
                                            <th style='text-align:center;'>N° EAR</th>                                             
                                            <th style='text-align:center;'>Sub total</th>
                                            <th style='text-align:center;'>IGV</th>
                                            <th style='text-align:center;'>Total</th>                                                                                      
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th colspan="5" style="text-align:right">Totales</th>
                                            <th> </th>
                                            <th> </th>
                                            <th> </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div> 
                        </div> 

                        <div class="row">
                            <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <b>Costos adicionales:</b> 
                            </div> 
                        </div>
                        <div class="row">
                            <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <table id="datatableCostosAdicionalesOrdenTrabajo" class="table table-striped table-bordered" >
                                    <thead>
                                        <tr style="color:white;" class="portlet-heading bg-primary">
                                            <th style='text-align:center;'>Documento</th>
                                            <th style='text-align:center;'>F. Emisión</th>                                           
                                            <th style='text-align:center;'>Persona</th>                                             
                                            <th style='text-align:center;'>S|N</th>                                             
                                            <!--<th style='text-align:center;'>N° EAR</th>-->                                             
                                            <th style='text-align:center;'>Sub total</th>
                                            <th style='text-align:center;'>IGV</th>
                                            <th style='text-align:center;'>Total</th>                                                                                      
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th colspan="4" style="text-align:right">Totales</th>
                                            <th> </th>
                                            <th> </th>
                                            <th> </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div> 
                        </div>
                        <div class="row">
                            <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <i class="ion-android-information" style="color:#0366b0"></i>&nbsp;&nbsp;&nbsp;<b>Recibo por honorarios (Adicionales + EAR)</b> 
                            </div> 
                        </div>
                        <div class="row">
                            <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <table id="datatableRH" class="table table-striped table-bordered" >
                                    <thead>
                                        <tr style="color:white;" class="portlet-heading bg-primary">
                                            <th style='text-align:center;'>Documento</th>
                                            <th style='text-align:center;'>F. Emisión</th>                                           
                                            <th style='text-align:center;'>Persona</th>                                             
                                            <th style='text-align:center;'>S|N</th>                                             
                                            <th style='text-align:center;'>N° EAR</th>                                             
                                            <th style='text-align:center;'>Sub total</th>
                                            <th style='text-align:center;'>IGV</th>
                                            <th style='text-align:center;'>Total</th>                                                                                      
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th colspan="5" style="text-align:right">Totales</th>
                                            <th> </th>
                                            <th> </th>
                                            <th> </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div> 
                        </div>

                    </div> 
                    <div class="modal-footer">                       
                        <div class="row">
                            <div class="col-lg-10 col-md-10 col-sm-10 col-xs-10"></div>
                            <!--<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3"></div>-->
                            <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
                                <div class="input-group m-t-10" style="float: right">
                                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-close"></i> Cerrar</button>  
                                </div>
                            </div>
                        </div>
                    </div>
                </div> 
            </div>
        </div><!-- /.modal --> 
        <!--</div>-->
        <script src="vistas/com/reporte/reporte.js"></script>
        <script src="vistas/com/reporte/reporteOrdenTrabajo.js"></script>
    </body>
</html>


