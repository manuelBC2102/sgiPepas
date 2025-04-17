<style type="text/css">
    .colorPP {
        color: #0000ff;
    }
</style>
<!--<div class="wraper container-fluid">-->
<div class="page-title">
    <h3 id="titulo" class="title"></h3>
</div>

<div class="panel panel-default">
    <input type="hidden" id="documento_tipo" value="<?php echo $_GET['documento_tipo']; ?>" />
    <div class="row">
        <div class="panel panel-default m-t-20 p-t-0" style="padding-left: 0px;padding-bottom: 1px;padding-right: 0px;">
            <div class="tab-content" style="margin: 0px;padding: 15px">
                <!--PESTAÑA DOCUMENTOS-->
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
                                            <br>
                                            <li>
                                                <div class="form-group col-md-2">
                                                    <label style="color: #141719;">Fecha programación</label>
                                                </div>
                                                <div class="form-group col-md-10">
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
                                            </li>
                                            <li id="liTipo" hidden>
                                                <div class="form-group col-md-2">
                                                    <label style="color: #141719;">Tipo</label>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <div class="row">
                                                            <div class="form-group col-md-12">
                                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                                    <select name="cboTipo" id="cboTipo" class="select2">
                                                                        <option value="0">seleccionar</option>
                                                                        <option value="Compra">Orden de Compra</option>
                                                                        <option value="Servicio">Orden de Servicio</option>    
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li id="liarea_tipo">
                                                <div class="form-group col-md-2">
                                                    <label style="color: #141719;">Área</label>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <div class="row">
                                                            <div class="form-group col-md-12">
                                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                                    <select name="cboArea" id="cboArea" class="select2">
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-2">
                                                    <label style="color: #141719;">Tipo Requerimiento</label>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <div class="row">
                                                            <div class="form-group col-md-12">
                                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                                    <select name="cboTipoRequerimiento" id="cboTipoRequerimiento" class="select2">
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>                                                
                                            </li>
                                            <li id="liEstado">
                                                <div class="form-group col-md-2">
                                                    <label style="color: #141719;">Estado</label>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <div class="row">
                                                            <div class="form-group col-md-12">
                                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                                    <select name="cboEstado" id="cboEstado" class="select2">
                                                                        <option value="0">Todos</option>
                                                                        <option value="Por Aprobar" selected>Por Aprobar</option>    
                                                                        <option value="Aprobado">Aprobado</option>   
                                                                        <option value="Rechazado">Rechazado</option>    
                                                                    </select>
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
                        <table id="datatableRequermiento" class="table table-small-font table-striped table-hover" style="width: 1205px;">
                            <thead>
                                <tr>
                                    <th style='text-align:center;'>S/N</th>
                                    <th style='text-align:center;'>Tipo</th>
                                    <th style='text-align:center;'>Área</th>
                                    <th style='text-align:center;' id="th_persona">Solicitante</th>
                                    <th style='text-align:center;' id="th_total">Total</th>
                                    <th style='text-align:center;'>F.Creación</th>
                                    <th style='text-align:center;'>U.Creación</th>
                                    <th style='text-align:center;'>Estado</th>
                                    <th style='text-align:center;'>Progreso</th>
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
                                <i class='fa fa-check' style='color:blue;'></i> Aprobar  &nbsp;&nbsp;&nbsp;
                                <i class='fa fa-eye' style='color:blue;'></i> Ver detalle cuadro comparativo&nbsp;&nbsp;&nbsp;
                                <i class='fa fa-print' style='color:black;'></i> Ver pdf de cuadro comparativo&nbsp;&nbsp;&nbsp;
                                <i class='fa fa-eye' style='color:black;'></i> Ver detalle distribución pagos&nbsp;&nbsp;&nbsp;
                                <!-- <i class='fa fa-times' style='color:red;'></i> Rechazar  &nbsp;&nbsp;&nbsp; -->
                            </p>
                        </div>
                    </div>
                </div>
                <!--FIN DOCUMENTOS-->
            </div>
        </div>
    </div>
</div>

<!--modal para detalle -->
<div id="modalDetalle" class="modal fade" role="dialog" aria-labelledby="myModalLabel2" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-full">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">Detalle</h4>
            </div>
            <div class="modal-body">
                <div id="portlet1" class="panel-collapse collapse in">
                    <div class="portlet-body">
                        <form id="formularioDetalleDocumento" method="post" class="form" enctype="multipart/form-data;charset=UTF-8" style="min-height: 75px;height: auto;">
                        </form>
                    </div>
                </div>
                <div class="row" id="tableRequerimiento" name="tableRequerimiento" hidden>
                    <table id="dtmodalDetalleRequerimiento" class="table table-striped table-bordered" style="width: 100%">
                        <thead>
                            <tr>
                                <th style='text-align:center;'>#</th>
                                <th style='text-align:center;'>Producto</th>
                                <th style='text-align:center;'>Cantidad</th>
                                <th style='text-align:center;'>U. Medida</th>
                                <th style='text-align:center;'>CeCo</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div class="row" id="tableRequerimientoArea" name="tableRequerimientoArea" hidden>
                    <table id="dtmodalDetalleRequerimientoArea" class="table table-striped table-bordered" style="width: 100%">
                        <thead>
                            <tr>
                                <th style='text-align:center;'>#</th>
                                <th style='text-align:center;'>Producto</th>
                                <th style='text-align:center;'>Cantidad compras</th>
                                <th style='text-align:center;'>Cantidad en reserva</th>
                                <th style='text-align:center;'>Cantidad solicitada</th>
                                <th style='text-align:center;'>U. Medida</th>
                                <th style='text-align:center;'>Compras</th>
                            </tr>
                        </thead>
                    </table>
                </div>                
                <div class="row" id="tableConsolidado" name="tableConsolidado" hidden>
                    <h3>Cuadro comparativo</h3>
                    <table id="dtmodalDetalleConsolidado" class="table table-striped table-bordered" style="width: 100%">
                        <thead id="theadConsolidado">

                        </thead>
                        <tbody id="tbodyDetalle">

                        </tbody>
                        <tfoot id="tfootDetalle">

                        </tfoot>
                    </table>
                </div>
                <br>
                <div class="row" id="tableOrdenCompraServicio" name="tableOrdenCompraServicio" hidden>
                    <h3 id="tituloOrden"></h3>
                    <table id="dtmodalDetalletableOrdenCompraServicio" class="table table-striped table-bordered" style="width: 100%">
                        <thead>
                            <tr>
                                <th style='text-align:center;'>#</th>
                                <th style='text-align:center;'>Producto</th>
                                <th style='text-align:center;'>U. Medida</th>
                                <th style='text-align:center;'>Cantidad</th>
                                <th style='text-align:center;'>Precio Unt.</th>
                                <th style='text-align:center;'>Sub Total</th>
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
                        <div id="div_btn_aprobar" class="col-md-8"></div>
                        <input type="hidden" id="visualizarDocumentoId" name="visualizarDocumentoId" />
                        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-close"></i> Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!--inicio modal anulacion-->     
<div id="modalAnulacion"  class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">       
    <div class="modal-dialog">            
        <div class="modal-content">               
            <div class="modal-header">                      
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true" onclick="limpiarFormularioRechazo()">×</button>         
                <h4 class="modal-title text-dark text-uppercase" id="tituloModalAnulacion"></h4> 
            </div>                     
            <div class="modal-body">                 
                <div class="row">
                    <div class="form-group col-md-12">
                        <label>Motivo de anulación *</label>
                        <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <textarea type="text" id="txtMotivoRechazo" name="txtMotivoRechazo" class="form-control" value="" maxlength="500"></textarea>
                        </div>
                    </div>
                </div>
            </div>                   
            <div class="modal-footer">   
                <div class="form-group col-md-12">
                    <a href="#" class="btn btn-danger w-sm m-b-5" id="id" style="border-radius: 0px;margin-bottom: 0px;" data-dismiss="modal" onclick="limpiarFormularioRechazo()"><i class="fa fa-close"></i>&ensp;Cancelar</a>&nbsp;&nbsp;&nbsp;
                    <a type="button" onclick="rechazarComentario()"  class="btn btn-info w-sm m-b-5" style="border-radius: 0px;"><i class="fa fa-send-o"></i>&ensp;Rechazar</a>&nbsp;&nbsp;
                </div>
            </div>         
        </div>         
    </div>     
</div>

<!--modal para archivos adjuntos -->
<div id="modalDetalleArchivos" class="modal fade" role="dialog" aria-labelledby="myModalLabel2" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-full">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">Detalle de OC / OS</h4>
            </div>
            <div class="modal-body">
                <!-- <div class="row">
                    <div class="form-group col-md-1">
                        <label style="color: #141719;">Tipo Archivo</label>
                    </div>
                    <div class="form-group col-md-2">
                        <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <select name="cboTipoArchivo" id="cboTipoArchivo" class="select2">
                                <option value="3">Pdf factura</option>
                                <option value="4">Xml factura</option>
                                <option value="5">Cdr factura</option>
                                <option value="6">Otros</option>
                            </select>
                        </div>
                    </div>
                    <div id="divContenedorAdjuntoMulti" class="form-group col-md-2">
                        <div class="fileUpload btn btn-purple" style="border-radius: 0px;"
                            id="idPopoverMulti"
                            title=""
                            data-toggle="popover"
                            data-placement="top"
                            data-content="">
                            <i class="ion-upload" style="font-size: 16px;"></i>
                            Cargar documento
                            <input name="archivoAdjuntoMulti" id="archivoAdjuntoMulti" type="file" accept="*" class="upload">
                            <input type="hidden" id="dataArchivoMulti" value="" />
                        </div>
                    </div>
                    <div class="form-group col-md-2">
                        <button id="btnAgregarDoc" name="btnAgregarDoc" type="button" class="btn btn-info w-sm m-b-5" style="border-radius: 0px;">
                            <i class="fa fa-plus-circle"></i>&nbsp;&nbsp;Agregar archivo
                        </button>
                    </div>
                </div> -->
                <span id="msjDocumento" style="color:#cb2a2a;font-style: normal;"></span>
                <br>
                <div class="row" id="scroll">
                    <div class="form-group col-md-12">
                        <div class="table">
                            <div id="dataList2">

                            </div>
                        </div>
                    </div>
                </div>
                <div id="divLeyenda">
                    <b>Leyenda:</b>&nbsp;&nbsp;
                    <i class="fa fa-cloud-download" style="color:#1ca8dd;"></i>&nbsp;Descargar &nbsp;&nbsp;&nbsp;
                    <i class="fa fa-trash-o" style="color:#cb2a2a;"></i>&nbsp;Eliminar &nbsp;&nbsp;&nbsp;
                    <!-- <i class='fa fa-times' style='color:red;'></i> Rechazar Documento &nbsp;&nbsp;&nbsp; -->
                </div>
            </div>
            <div class="modal-footer" style="padding-bottom:  0px;padding-top: 10px;clear:left">
                <div class="form-group">
                    <div class="col-md-6" style="text-align: left;">
                    </div>
                    <div class="col-md-6">
                        <!-- <button type="button" class="btn btn-primary" onclick="AgregarActualizar()" id="btn_agregarActualizar" name="btn_agregarActualizar"><i class="fa fa-check"></i> Subir archivos</button> -->
                        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-close"></i> Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modalDetalleArchivosDistribucionPagos" class="modal fade" role="dialog" aria-labelledby="myModalLabel2" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-full">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">Documentos de distribución de Pagos  <span id="tituloDistribucionPagos"></span></h4>
            </div>
            <div class="modal-body">
                <span id="msjDocumentoDistribucionPagos" style="color:#cb2a2a;font-style: normal;"></span>
                <br>
                <div class="row" id="scroll">
                    <div class="form-group col-md-12">
                        <div class="table">
                            <div id="dataList2DistribucionPagos">

                            </div>
                        </div>
                    </div>
                </div>
                <div id="divLeyenda">
                    <b>Leyenda:</b>&nbsp;&nbsp;
                    <i class="fa fa-cloud-download" style="color:#1ca8dd;"></i>&nbsp;Descargar &nbsp;&nbsp;&nbsp;
                    <i class="fa fa-trash-o" style="color:#cb2a2a;"></i>&nbsp;Eliminar &nbsp;&nbsp;&nbsp;
                    <!-- <i class='fa fa-times' style='color:red;'></i> Rechazar Documento &nbsp;&nbsp;&nbsp; -->
                </div>
            </div>
            <div class="modal-footer" style="padding-bottom:  0px;padding-top: 10px;clear:left">
                <div class="form-group">
                    <div class="col-md-6" style="text-align: left;">
                    </div>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-danger" onclick="ocultarModalDistribucionPagos()"><i class="fa fa-close"></i> Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="vistas/com/requerimiento/aprobar_requerimiento_listar.js"></script>