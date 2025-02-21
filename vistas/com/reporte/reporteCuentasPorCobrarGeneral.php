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
                                            <a id="loaderBuscarDeuda" onclick="loaderBuscarDeudaGeneral()">
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
                                        <div class="form-group col-md-6">
                                            <label>Cliente</label>
                                            <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <select name="cboPersona" id="cboPersona" class="select2">
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-6 ">
                                            <label>Empresa</label>
                                            <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <select name="cboEmpresa" id="cboEmpresa" class="select2" multiple>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label>Fecha</label>
                                            <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <div class="row">
<!--                                                    <div class="form-group col-md-6">
                                                        <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">-->
                                                            <input type="hidden" class="form-control fecha" placeholder="dd/mm/yyyy" id="inicioFechaVencimiento">
<!--                                                            <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                                                        </div>-->
                                                    <!--</div>-->
                                                    <div class="form-group col-md-12">
                                                        <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                            <input type="text" class="form-control fecha" placeholder="dd/mm/yyyy" id="datpickerFecha">
                                                            <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <div class="checkbox">
                                            <label class="cr-styled">
                                                <input type="checkbox" name="chk_mostrar" id="chk_mostrar">
                                                <i class="fa"></i> 
                                                Mostrar pagados
                                            </label>
                                        </div>
                                        </div>
                                        
                                        
                                    </div>
<!--                                    <div class="row">
                                        <div class="checkbox">
                                            <label class="cr-styled">
                                                <input type="checkbox" name="chk_mostrar" id="chk_mostrar">
                                                <i class="fa"></i> 
                                                Mostrar pagados
                                            </label>
                                        </div>
                                    </div>-->

                                    <div class="modal-footer">
                                        <!--<button id="btnBuscar" type="button" data-toggle="collapse" data-parent="#accordion1" href="#bg-info" onclick="buscarDeudaGeneral()" value="enviar" class="btn btn-purple"><i></i>&ensp;Buscar</button>&nbsp;&nbsp;-->
                                        <button type="button" href="#bg-info" onclick="buscarDeudaGeneral(1)" value="enviar" class="btn btn-purple"> Buscar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row"></div>
                <div class="panel panel-body">
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <div class="table">
                            <!--<div id="dataList" class="table-responsive">-->
                                <table id="dataTableDeudaGeneral" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>                                            
                                            <th style='text-align:center; vertical-align: middle;' rowspan="2"></th>
                                            <th style='text-align:center; vertical-align: middle;' rowspan="2">F. Emisión</th>
                                            <th style='text-align:center; vertical-align: middle;' rowspan="2">F. Venc.</th>
                                            <th style='text-align:center; vertical-align: middle;' rowspan="2">Tipo documento</th> 
                                            <th style='text-align:center; vertical-align: middle;' rowspan="2">Empresa</th>
                                            <th style='text-align:center; vertical-align: middle;' rowspan="2">Persona</th>
                                            <th style='text-align:center; vertical-align: middle;' rowspan="2">Serie</th>
                                            <th style='text-align:center; vertical-align: middle;' rowspan="2">Número</th>
                                            <th style='text-align:center; vertical-align: middle;' rowspan="2">Descripción</th>
                                            <th style='text-align:center; vertical-align: middle;' rowspan="2">Moneda</th>
                                            <th style='text-align:center; vertical-align: middle;' colspan="2">Soles</th>
                                            <th style='text-align:center; vertical-align: middle;' colspan="2">Dólares</th>
                                            <th style='text-align:center; vertical-align: middle;' rowspan="2">Total</th>
                                            <th style='text-align:center; vertical-align: middle;' rowspan="2">Acciones</th>
                                        </tr>
                                        <tr>
                                            <th style='text-align:center;'>Imp. Pag.</th>
                                            <th style='text-align:center;'>Deuda</th>
                                            <th style='text-align:center;'>Imp. Pag.</th>
                                            <th style='text-align:center;'>Deuda</th>
                                        </tr>
                                    </thead>
                                    
                                    <tfoot>
                                        <tr>
                                            <th colspan="10" style="text-align:right">Totales:</th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            <!--</div>-->
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="modal_detalle_cobros_general"  class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
                <div class="modal-dialog" style="width:80%;"> 
                    <div class="modal-content"> 
                        <div class="modal-header"> 
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button> 
                            <h4 class="modal-title"></h4> 
                        </div> 
                        <div class="modal-body"> 
                            <div class="table">
                                <div id="dataList">
                                    <table id="datatableDetalleCobroGeneral" class="table table-striped table-bordered" >
                                        <thead>
                                            <tr>
                                                <th style='text-align:center;'>Fecha de pago</th>
                                                <th style='text-align:center;'>Documento tipo/Efectivo</th>
                                                <th style='text-align:center;'>Número</th>
                                                <!--<th style='text-align:center;'>Fecha Vencimiento</th>-->
                                                <th style='text-align:center;'>Moneda</th>
                                                <th style='text-align:center;'>Monto</th>
                                                <!--<th style='text-align:center;'>Discrepancia</th>-->
                                            </tr>
                                        </thead>
<!--                                        <tfoot>
                                            <tr>
                                                <th colspan="3" style="text-align:right">Total:</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>-->
                                    </table>
                                </div>
                            </div>
                        </div> 
                        <div class="modal-footer"> 
                            <button type="button" class="btn btn-danger m-b-5" id="id" style="border-radius: 0px;" data-dismiss="modal"><i class="fa fa-close"></i>&ensp;Cerrar</button> 
                        </div> 
                    </div> 
                </div>
            </div>
            
            
        </div>
        <!--</div>-->
        <script src="vistas/com/reporte/reporteCuentasPorCobrarGeneral.js"></script>
    </body>
</html>


