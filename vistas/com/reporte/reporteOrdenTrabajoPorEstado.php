
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
            .progress-bar {
                font-size: 12px !important;
            }
            .progress {
                height: 18px !important;
                border-radius: 5px !important;
            }
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

                                        <div class="form-group col-md-4 ">
                                            <div class="form-group col-md-2">
                                                <label>Serie</label>
                                            </div>
                                            <div class="form-group col-md-12">
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <input type="text" id="txtSerie" name="txtSerie" class="form-control" value="" maxlength="45">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-4 ">
                                            <div class="form-group col-md-2">
                                                <label>Numero</label>
                                            </div>
                                            <div class="form-group col-md-12">
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <input type="text" id="txtNumero" name="txtNumero" class="form-control" value="" maxlength="45">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-4 ">
                                            <div class="form-group col-md-2">
                                                <label>Progreso</label>
                                            </div>
                                            <div class="form-group col-md-12">
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <select name="cboProgreso" id="cboProgreso" class="select2">
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" onclick="exportarReporteOrdenTrabajo();" value="Exportar" name="env" id="env" class="btn btn-success w-md" style="border-radius: 0px;">&ensp;Exportar reporte</button>
                                        <button type="button" href="#bg-info" onclick="buscarReporteOrdenTrabajoPorEstado(1)" value="enviar" class="btn btn-purple"> Buscar</button>
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
                                        <th style='text-align:center;'>Total</th>
                                        <th style='text-align:center;'>Progreso</th>
                                        <th style='text-align:center;'>Porcentaje</th>
                                        <th style='text-align:center;'>Opc.</th>
                                    </tr>
                                </thead> 

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
<!--modal para el detalle del operacion-->
<div id="modalDetalleDocumento"  class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-full"> 
        <div class="modal-content"> 
            <div class="modal-header"> 
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button> 
                <h4 class="modal-title">Visualización del documento</h4> 
            </div>
            <div class="modal-body"> 
                <div class="row">

                    <div class="col-lg-6">
                        <div class="portlet" style="box-shadow: 0 0px 0px"><!-- /primary heading -->
                            <div class="portlet-heading">
                                <h3 id="nombreDocumentoTipo" class="portlet-title text-dark text-uppercase">

                                </h3>
                                <div class="clearfix"></div>
                            </div>
                            <div id="portlet1" class="panel-collapse collapse in">
                                <div class="portlet-body" >
                                    <form  id="formularioDetalleDocumento"  method="post" class="form" enctype="multipart/form-data;charset=UTF-8" style="min-height: 75px;height: auto;">
                                    </form>
                                </div>
                            </div>
                        </div> <!-- /Portlet -->
                    </div>
                    <div class="col-lg-6 ">
                        <div class="portlet" style="box-shadow: 0 0px 0px"><!-- /primary heading -->
                            <div class="portlet-heading">
                                <div class="clearfix"></div>
                            </div>
                            <div id="portlet2" class="panel-collapse collapse in">
                                <div class="portlet-body">
                                    <br>
                                    <div class="form-group col-lg-12 col-md-12">
                                        <label>COMENTARIO </label>
                                        <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <textarea type="text" id="txtComentario" name="txtComentario" class="form-control" value="" maxlength="500" readonly="true" ></textarea>
                                        </div>
                                    </div>

                                    <div class="form-group col-lg-12 col-md-12">
                                        <br>
                                        <label>Detalle documento </label>
                                        <table id="datatable2" class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th style='text-align:center;'>Cantidad</th>
                                                    <th style='text-align:center;'>Unidad Medida</th>
                                                    <th style='text-align:center;'>Descripcion</th>
                                                    <th style='text-align:center;'>Precio U.</th>
                                                    <th style='text-align:center;'>Importe</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="row">
                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-9"></div>
                    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
                        <div class="input-group m-t-10" style="float: right">
                            <!--<a class="btn btn-success" onclick="enviarCorreoDetalleDocumento()"><i class="ion-email"></i> Enviar correo</a>-->
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
        <script src="vistas/com/reporte/reporteOrdenTrabajoPorEstado.js"></script>
    </body>
</html>