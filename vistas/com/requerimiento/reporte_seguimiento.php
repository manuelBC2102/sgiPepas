<html lang="es">

<head>
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

        .popover {
            max-width: 100%;
        }

        th {
            white-space: nowrap;
        }

        .alignRight {
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="page-title">
        <h3 id="titulo" class="title"></h3>
    </div>
    <div class="row">
        <div class="panel panel-default">
            <div class="row">
                <div class="col-lg-12">
                    <div class="portlet">
                        <div class="row">
                            <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <div class="portlet-heading bg-purple m-b-0"
                                    onclick="colapsarBuscador()"
                                    id="idPopover" title="" data-toggle="popover"
                                    data-placement="top" data-content=""
                                    data-original-title="Criterios de búsqueda"
                                    style="padding-top: 13px;padding-bottom: 13px;cursor: pointer; cursor: hand;">
                                    <div class="portlet-widgets">

                                        <!-- <a onclick="exportarReporteKardexExcel()" title="">
                                                <i class="fa fa-file-excel-o"></i>
                                            </a>&nbsp; -->
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
                                    <div class="form-group col-md-4 ">
                                        <label>Tipo requerimiento</label>
                                        <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <select name="cboTipoRequerimiento" id="cboTipoRequerimiento" class="select2">
                                                <option value="1">Compra</option>
                                                <option value="2">Servicio</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-4 ">
                                        <label>Producto / Servicio</label>
                                        <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <input name="cboBien" id="cboBien" class="select2" />
                                        </div>
                                    </div>
                                    <div class="form-group col-md-4 ">
                                        <label>Grupo de producto</label>
                                        <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <select name="cboTipoBien" id="cboTipoBien" class="select2" multiple>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Fecha</label>
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
                                    <div class="form-group col-md-6">
                                        <label>Serie Número RQ</label>
                                        <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="row">
                                                <div class="form-group col-md-6">
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <input type="text" class="form-control" placeholder="Serie" id="serie">
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <input type="text" class="form-control" placeholder="Número" id="numero">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" onclick="exportarReporteSeguimiento();" value="Exportar" name="env" id="env" class="btn btn-success w-md" style="border-radius: 0px;">&ensp;Exportar reporte</button>&nbsp;&nbsp;
                                    <button type="button" href="#bg-info" onclick="buscarSeguimiento(1)" value="enviar" class="btn btn-purple"> Buscar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row"></div>
            <div class="panel panel-body">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <button class="btn btn-primary" id="btn_mostrarColumnas">Mosttar Todas las columnas</button><br><br>
                    <select id="miSelect" class="select2" multiple></select>
                    <table id="datatableSeguimiento" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th style='text-align:center;' rowspan="2">Item</th>
                                <th style='text-align:center;' rowspan="2">Cod. Producto</th>
                                <th style='text-align:center;' rowspan="2">Producto</th>
                                <th style='text-align:center;' rowspan="2">Unidad Medida</th>
                                <th style='text-align:center;' rowspan="2">Cantidad</th>
                                <th style='text-align:center;' rowspan="2">Grupo de producto</th>
                                <th style='text-align:center;' rowspan="2">Generador</th>
                                <th style='text-align:center;' rowspan="2">N° de RQ</th>
                                <th style='text-align:center;' rowspan="2">Tipo de RQ</th>
                                <th style='text-align:center;' rowspan="2">F. Creación</th>
                                <th style='text-align:center;' rowspan="2">Área</th>
                                <th style='text-align:center;' colspan="2">Condicion 1 Aprobacion de RQ</th>
                                <th style='text-align:center;' colspan="3">Condicion 2 Generacion de Cotizacion</th>
                                <th style='text-align:center;' colspan="3">Condicion 3 Generacion de OC</th>
                            </tr>
                            <tr>
                                <th style='text-align:center;'>Estado</th>
                                <th style='text-align:center;'>Fecha</th>
                                <th style='text-align:center;'>Usuario Generador</th>
                                <th style='text-align:center;'>Estado</th>
                                <th style='text-align:center;'>Fecha</th>
                                <th style='text-align:center;'>N° OC / OS</th>
                                <th style='text-align:center;'>Estado</th>
                                <th style='text-align:center;'>Fecha</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <div style="clear:left">
                <p><b>Leyenda:</b>&nbsp;&nbsp;
                    <i class="fa fa-eye"></i> Ver detalle
                </p>
            </div>
        </div>
    </div>

    <div id="modal-detalle-kardex" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title">Verificación de stock</h4>
                </div>
                <div class="modal-body">
                    <div class="table">
                        <div id="dataList">
                            <table id="datatableStock" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th style='text-align:center;'>Unidad de medida</th>
                                        <th style='text-align:center;'>Stock</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger m-b-5" id="id" style="border-radius: 0px;" data-dismiss="modal"><i class="fa fa-close"></i>&ensp;Cerrar</button>
                </div>
            </div>
        </div>
    </div><!--
        <!--</div>-->
    <script src="vistas/com/requerimiento/reporte_seguimiento.js"></script>
</body>

</html>