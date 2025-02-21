<style type="text/css" media="screen">
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
                            <div class="portlet-heading bg-purple m-b-0" onclick="colapsarBuscador()" id="idPopover" title="" data-toggle="popover" data-placement="top" data-content="" data-original-title="Criterios de búsqueda" style="padding-top: 13px;padding-bottom: 13px;cursor: pointer; cursor: hand;">
                                <div class="portlet-widgets">
                                    <a onclick="exportarExcel();" title="">
                                        <i class="fa fa-file-excel-o"></i>
                                    </a>&nbsp;
                                    <a id="loaderBuscarVentas" onclick="loaderBuscarVentas()">
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
                                    <label>Año</label>
                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <select name="cboPeriodo" id="cboPeriodo" class="select2"> </select>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" onclick="exportarExcel();" value="Exportar" name="env" class="btn btn-info w-md" style="border-radius: 0px;"><i class="fa fa-file-excel-o"></i>&ensp;Exportar excel</button>&nbsp;&nbsp;
                                <button type="button" href="#bg-info" onclick="buscarPorCriterios(1);" value="enviar" class="btn btn-purple"><i class="fa fa-search"></i>&ensp;Buscar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row"></div>
        <div class="panel panel-body">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div id="dataList" class="table">
                    <table id="datatable" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th style='text-align:center;' colspan="2">Cuenta y subcuenta contable</th>
                                <th style='text-align:center;' colspan="2">Saldos iniciales</th>
                                <th style='text-align:center;' colspan="2">Movimiento</th>
                                <th style='text-align:center;' colspan="2">Saldos Finales</th>
                            </tr>
                            <tr>
                                <th style='text-align:center;'>Cuenta</th>
                                <th style='text-align:center;'>Nombre</th>
                                <th style='text-align:center;'>Deudo</th>
                                <th style='text-align:center;'>Acreedor</th>
                                <th style='text-align:center;'>Deudo</th>
                                <th style='text-align:center;'>Acreedor</th>
                                <th style='text-align:center;'>Deudo</th>
                                <th style='text-align:center;'>Acreedor</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyDataTable"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="vistas/com/reporteContabilidad/balance_comprobacion.js?<?php echo date("YmdHms"); ?>"></script>