<style type="text/css">
    .colorPP {
        color: #0000ff;
    }

    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .columnAlignCenter {
        text-align: center;
    }
</style>
<!--<div class="wraper container-fluid">-->
<div class="row">
    <div class="form-group col-md-9">
        <h3 id="titulo" class="title"></h3>
    </div>
    <div class="form-group col-md-2 col-md-offset-1">
        <select name="cboAlmacen" id="cboAlmacen" class="select2"></select>
    </div>
</div>

<div class="panel panel-default">
    <div class="row">
        <div class="panel panel-default m-t-20 p-t-0" style="padding-left: 0px;padding-bottom: 1px;padding-right: 0px;">
            <div class="tab-content" style="margin: 0px;padding: 15px">
                <div class="row">
                    <div class="input-group m-t-10">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 " style="padding-left: 0px;">
                            <div id="cabeceraBuscador" name="cabeceraBuscador">
                                <div class="input-group" id="divBuscador">
                                    <span class="input-group-btn" id="spanBuscador">
                                        <a type="button" data-toggle="dropdown" class="dropdown-toggle btn btn-effect-ripple btn-primary col-lg-12 col-md-12 col-sm-12 col-xs-12" href="#">
                                            Buscar<div style="float: right"><i class="caret"></i></div>
                                        </a>
                                        <ul class="dropdown-menu extended fadeInUp animated nicescroll" tabindex="5001" style="overflow: hidden; outline: none;width: 1052px;" id="ulBuscadorDesplegable">
                                            <li>
                                                <div class="form-group col-md-2">
                                                    <label style="color: #141719;">Fecha</label>
                                                </div>
                                                <div class="form-group col-md-10">
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <div class="row">
                                                            <div class="form-group col-md-6">
                                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                                    <input type="text" class="form-control fecha" placeholder="dd/mm/yyyy" id="inicioFechaEmisionAlmacenado">
                                                                    <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                                                                </div>
                                                            </div>
                                                            <div class="form-group col-md-6">
                                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                                    <input type="text" class="form-control fecha" placeholder="dd/mm/yyyy" id="finFechaEmisionAlmacenado">
                                                                    <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li>
                                                <div style="float: right">
                                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <button id="btnBusqueda" type="button" href="#bg-info" onclick="limpiarBuscadores()" class="btn btn-danger"> <i class="fa fa-close"></i>Cancelar</button>
                                                        <button id="btnBusqueda" type="button" href="#bg-info" onclick="actualizarBusqueda()" class="btn btn-purple"> <i class="fa fa-search"></i>Buscar</button>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="input-group-btn" style="padding-left: 0px;padding-right: 0px;">
                            <div class="btn-toolbar" role="toolbar" style="float: right">
                                <div class="input-group-btn">
                                    <a type="button" class="btn btn-success" onclick="actualizarBusqueda()" title="Actualizar resultados de búsqueda"><i class="ion-refresh"></i></a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12" style="padding-top: 20px">
                        <table id="datatableAlmacenado" class="table table-small-font table-striped table-hover" style="width: 1205px;">
                            <thead>
                                <tr>
                                    <th style='text-align:center;'>Producto</th>
                                    <th style='text-align:center;'>Cantidad</th>
                                    <th style='text-align:center;'>Acciones</th>
                                </tr>
                            </thead>
                        </table>
                        <br>
                        <div style="clear:left">
                            <p id="divLeyenda">
                                <br>
                                <b>Leyenda:</b>&nbsp;&nbsp;
                                <i class='fa fa-eye' style='color:green;'></i> Ver detalle &nbsp;&nbsp;&nbsp;
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!--modal para detalle alamcenado -->
<div id="modalDetalleAlmacenado" class="modal fade" role="dialog" aria-labelledby="myModalLabel2" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-full">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title-almacenado">Traking de paquete</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <table id="dtmodalDetallePaquete" class="table table-striped table-bordered" style="width: 100%">
                        <thead>
                            <tr>
                                <th style='text-align:center;'>#</th>
                                <th style='text-align:center;'>Correlativo</th>
                                <th style='text-align:center;'>Organizador</th>
                                <th style='text-align:center;'>Cantidad</th>
                                <th style='text-align:center;'>Usuario C.</th>
                                <th style='text-align:center;'>Fecha C.</th>
                                <th style='text-align:center;'>Acciones</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <br><br>
                <div class="row" id="div_detalleTraking" hidden>
                    <h4>Detalle traking</h4>
                    <table id="dtmodalDetalleTraking" class="table table-striped table-bordered" style="width: 100%">
                        <thead>
                            <tr>
                                <th style='text-align:center;'>#</th>
                                <th style='text-align:center;'>Correlativo</th>
                                <th style='text-align:center;'>Tipo</th>
                                <th style='text-align:center;'>Almacen</th>
                                <th style='text-align:center;'>Organizador actual</th>
                                <th style='text-align:center;'>Alamcen destino</th>
                                <th style='text-align:center;'>Cantidad</th>
                                <th style='text-align:center;'>S|N</th>
                                <th style='text-align:center;'>Tipo Documento</th>
                                <th style='text-align:center;'>Usuario C.</th>
                                <th style='text-align:center;'>Fecha C.</th>
                                <th style='text-align:center;'>Estado</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <div class="modal-footer" style="padding-bottom:  0px;padding-top: 10px;clear:left">
                <div class="form-group">
                    <div class="col-md-6" style="text-align: left;">
                    </div>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-close"></i> Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="vistas/com/almacenes/traking_paquete_listar.js"></script>