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
    <div class="row">
        <div class="panel panel-default m-t-20 p-t-0" style="padding-left: 0px;padding-bottom: 1px;padding-right: 0px;">
            <div class="tab-content" style="margin: 0px;padding: 15px">
                <!--PESTAÑA DOCUMENTOS-->
                <div class="row">
                    <div class="input-group m-t-10">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 " style="padding-left: 0px;">
                            <div id="cabeceraBuscador" name="cabeceraBuscador">
                                <div class="col-md-2">
                                    <button class="btn btn-info" onclick="abrirModalDocumentos()" style="width: 100%;">
                                        <i class=" fa fa-plus-square-o"></i>
                                        Nuevo
                                    </button>
                                </div>
                                <div class="input-group" id="divBuscador">
                                    <span class="input-group-btn" id="spanBuscador">
                                        <a type="button" data-toggle="dropdown" class="dropdown-toggle btn btn-effect-ripple btn-primary col-lg-12 col-md-12 col-sm-12 col-xs-12" href="#">
                                            Buscar<div style="float: right"><i class="caret"></i></div>
                                        </a>
                                        <ul class="dropdown-menu extended fadeInUp animated nicescroll" tabindex="5001" style="overflow: hidden; outline: none;width: 1052px;" id="ulBuscadorDesplegable">
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
                                            <li>
                                                <div class="form-group col-md-2">
                                                    <label style="color: #141719;">Tipo Operacion</label>
                                                </div>
                                                <div class="form-group col-md-10">
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <div class="row">
                                                            <div class="form-group col-md-6">
                                                                <select name="cboTipo_operacionPP" id="cboTipo_operacionPP" class="select2" style="width: 200px">
                                                                    <option value="0" selected>Todos</option>
                                                                    <option value="1">Abono a cuenta</option>
                                                                    <option value="2">Pago Detraccion</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group col-md-6">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </li>
                                            <li>
                                                <div class="form-group col-md-2">
                                                    <label style="color: #141719;">Moneda</label>
                                                </div>
                                                <div class="form-group col-md-5">
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <select name="cboMoneda" id="cboMoneda" class="select2">
                                                        </select>
                                                    </div>
                                                </div>
                                                <div style="float: right">
                                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <button id="btnBusqueda" type="button" href="#bg-info" onclick="limpiarBuscadores()" class="btn btn-danger"> <i class="fa fa-close"></i>Cancelar</button>
                                                        <button id="btnBusqueda" type="button" href="#bg-info" onclick="buscarPorCriterios()" class="btn btn-purple"> <i class="fa fa-search"></i>Buscar</button>
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
                        <table id="datatable" class="table table-small-font table-striped table-hover" style="width: 1205px;">
                            <thead>
                                <tr>
                                    <th style='text-align:center;'>Tipo Operación</th>
                                    <th style='text-align:center;'>F.Programación</th>
                                    <th style='text-align:center;'>Moneda</th>
                                    <th style='text-align:center;'>Monto Total</th>
                                    <th style='text-align:center;'>F.Creación</th>
                                    <th style='text-align:center;'>Estado</th>
                                    <th style='text-align:center;'>Usuario</th>
                                    <th style='text-align:center;'>Acciones</th>
                                </tr>
                            </thead>
                        </table>
                        <br>
                        <div style="clear:left">
                            <p id="divLeyenda">
                                <br>
                                <b>Leyenda:</b>&nbsp;&nbsp;
                                <i class='fa fa-file-text-o' style='color:green;'></i> Generar TXT &nbsp;&nbsp;&nbsp;
                                <i class='fa fa-ban' style='color:red;'></i> Anular programación &nbsp;&nbsp;&nbsp;
                                <i class='fa fa-eye' style='color:blue;'></i> Ver detalle &nbsp;&nbsp;&nbsp;
                            </p>
                        </div>
                    </div>
                </div>
                <!--FIN DOCUMENTOS-->
            </div>
        </div>
    </div>
</div>

<!--</div>-->
<!--modal para documentos-->
<div id="modalDocumentos" class="modal fade" role="dialog" aria-labelledby="myModalLabel1" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-full">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">Lista de Facturas</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="form-group col-md-6 ">
                        <label>Tipo de Operación *</label>
                        <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <select name="cboTipo_operacion" id="cboTipo_operacion" class="select2">
                                <option value="1" selected>Abono a cuenta</option>
                                <option value="2">Pago Detraccion</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group col-md-6 ">
                        <label>Persona *</label>
                        <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <select name="cboPersonaM" id="cboPersonaM" class="select2">
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-6 " id="div_cboMoneda2">
                        <label>Moneda *</label>
                        <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <select name="cboMoneda2" id="cboMoneda2" class="select2">
                            </select>
                        </div>
                    </div>
                    <div class="form-group col-md-6 ">
                        <label>Fecha Pago *</label>
                        <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <input type="text" class="form-control fecha" placeholder="dd/mm/yyyy" id="fecha_programacion">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <table id="dtDocumentos" class="table table-striped table-bordered" style="width: 100%">
                            <thead>
                                <tr>
                                    <th style='text-align:center;'><input type="checkbox" id="selectAll"></th>
                                    <th style='text-align:center;'>F. creación</th>
                                    <th style='text-align:center;'>Persona</th>
                                    <th style='text-align:center;'>S/N Doc.</th>
                                    <th style='text-align:center;'>Moneda</th>
                                    <th style='text-align:center;'>Monto Pago</th>
                                    <th style='text-align:center;'>Usuario</th>
                                    <th style='text-align:center;' hidden>PersonaId</th>
                                    <th style='text-align:center;' hidden>facturacionProveedorId</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="padding-bottom:  0px;padding-top: 10px;clear:left">
                <div class="form-group">
                    <div class="col-md-6" style="text-align: left;">
                        <!-- <p><b>Leyenda:</b>&nbsp;&nbsp;
                            <i class="fa fa-download" style="color:#04B404;"></i> Agregar documento a copiar&nbsp;&nbsp;
                            <i class="fa fa-arrow-down" style="color:#1ca8dd;"></i> Agregar documento a relacionar
                        </p> -->
                    </div>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-primary" id="btn_agregar"><i class="fa fa-level-down"></i> Generar Programación</button>
                        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-close"></i> Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!--modal para detalle programación-->
<div id="modalProgramacionPagos" class="modal fade" role="dialog" aria-labelledby="myModalLabel2" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-full">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">Lista de Programación de pagos</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <table id="dtProgramacionPagos" class="table table-striped table-bordered" style="width: 100%">
                        <thead>
                            <tr>
                                <th style='text-align:center;'>Persona</th>
                                <th style='text-align:center;'>S/N Doc.</th>
                                <th style='text-align:center;'>Moneda</th>
                                <th style='text-align:center;'>Total</th>
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
<div id="modalAdjunto" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <input type="hidden" id="indiceImagenAdjuntaBien" value="0">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">Adjuntar Archivo (Max. 3MB)</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div id="divContenedorAdjunto" class="form-group col-md-2">
                        &nbsp;<a href='#' onclick="$('#fileInputAdjunto').click();" class="fileUpload btn btn-purple" style="border-radius: 0px;"><i class="fa fa-cloud-upload" title="Adjuntar cotización"></i> Cargar archivo</a>
                        <input type="file" id="fileInputAdjunto" style="display:none;">
                        <br><br>
                        &nbsp;<a id="text_archivoAdjunto" onclick="verImagenPdf()"></a>
                        &nbsp;<input type='hidden' id="nombrearchivoAdjunto" />
                        &nbsp;<input type='hidden' id="base64archivoAdjunto" />
                    </div>
                    <div class="col-sm-12" id="divImagenAdjuntaBien" style="display: flex; justify-content: center; align-items: center;">
                        <div id="error" style="color: red; display: none;">El archivo no es válido, tiene que ser una imagen o pdf</div>
                    </div>
                </div> <!--!--End row-->
            </div>

            <div class="modal-footer">
                <a class="btn btn-danger" id="id" data-dismiss="modal"><i class="fa fa-close"></i>&ensp;Cancelar</a>
                <a class="btn btn-success" onclick="registrarImagenPdfBien()"><i class="fa fa-send-o"></i> Guardar</a>
            </div>
        </div>
    </div>
</div>

<script src="vistas/com/programacionPagos/programacion_pago_listar.js"></script>