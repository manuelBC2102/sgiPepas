<div class="page-title">
    <h3 id="titulo" class="title"></h3>
</div>
<input type="hidden" id="hddIsDependiente" value="0">
<div class="row">
    <input type="hidden" id="txtTipo" name="txtTipo" class="form-control" value="" readonly="true"/>
    <div class="panel panel-default" style="padding-bottom:  5px;">
    <div class="row">
    <div class="col-lg-12">
        <div class="portlet">
            <div class="row">
                <!-- Columna para el botón -->
                <div class="col-md-2 d-flex align-items-center">
                    <div class="input-group-btn" id="listaPersonaTipo">
                        <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="true" style="width: 100%; padding: 10px;">
                            <i class="fa fa-plus-square-o"></i> Nueva <span class="caret"></span>
                        </button>
                    </div>
                </div>

                <!-- Columna para el formulario -->
                <div class="col-md-10">
                    <div class="portlet-heading bg-purple m-b-0" 
                         onclick="colapsarBuscador()" id="idPopover" title="" 
                         data-toggle="popover" data-placement="top" data-content="" 
                         data-original-title="Criterios de búsqueda"
                         style="padding: 5px; cursor: pointer;">
                        <span><i class="fa fa-filter"></i> Filtrar por</span>
                        <div class="portlet-widgets">
                            <a id="loaderBuscar" onclick="actualizarBusquedaPersona()">
                                <i class="ion-refresh"></i>
                            </a>
                            <span class="divider"></span>
                        </div>
                        <div class="clearfix"></div>
                    </div>

                    <div id="bg-info" class="panel-collapse collapse in">
                        <div class="portlet-body">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Planta</label>
                                    <div class="input-group col-lg-12">
                                        <select name="cboPlantaF" id="cboPlantaF" class="select2"></select>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Zona</label>
                                    <div class="input-group col-lg-12">
                                        <select name="cboZonaF" id="cboZonaF" class="select2"></select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Vehículo</label>
                                    <div class="input-group col-lg-12">
                                        <select name="cboVehiculo" id="cboVehiculo" class="select2"></select>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Transportista</label>
                                    <div class="input-group col-lg-12">
                                        <select name="cboTransportista" id="cboTransportista" class="select2"></select>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                            <button type="button" onclick="limpiarBuscadores()" value="enviar" class="btn btn-green">Limpiar filtros</button>
                                <button type="button" onclick="listarPersona()" value="enviar" class="btn btn-purple">Buscar</button>
                                
                            </div>
                        </div>
                    </div> <!-- End panel-collapse -->
                </div> <!-- End col-md-10 -->
            </div> <!-- End row -->
        </div> <!-- End portlet -->
    </div> <!-- End col-lg-12 -->
</div> <!-- End row -->



        <div class="row">
            <table id="datatable" class="table table-striped table-bordered" >
                <thead>
                    <tr>
                        <th style='text-align:center;'>Id</th>
                        <th style='text-align:center;'>Fecha Entrega</th>
                        <th style='text-align:center;'>Zona</th>
                        <th style='text-align:center;'>Vehiculo</th>
                        <th style='text-align:center;'>Conductor</th>
                        <th style='text-align:center;'>Usuario Solicitud</th>
                        <th style='text-align:center;'>Planta</th>
                        <th style='text-align:center;'>Estado</th>
                        <!--<th style='text-align:center;'>Estado</th>-->
                        <th style='text-align:center;'>Acc.</th>
                    </tr>
                </thead>
            </table>
        </div>
        <br>
        <div style="clear:left">
            <p><b>Leyenda:</b>&nbsp;&nbsp;
                <i class="fa fa-edit" style="color:#E8BA2F;"></i> Editar la informaci&oacute;n &nbsp;&nbsp;&nbsp;
                <i class="fa fa-trash-o" style="color:#cb2a2a;"></i> Eliminar&nbsp;&nbsp;&nbsp;
                <i class="fa fa-file" style="color:blue;"></i> Requerimiento&nbsp;&nbsp;&nbsp;
                <i class="fa fa-file" style="color:green;"></i> Validaciones MTC&nbsp;&nbsp;&nbsp;
<!--                <i class='ion-checkmark-circled' style="color:#5cb85c;"></i> Estado activo &nbsp;&nbsp;&nbsp;
                <i class="ion-flash-off" style="color:#cb2a2a;"></i> Estado inactivo -->
            </p>
        </div>
    </div>
    <div id="modalPersona" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Importar Persona</h4>
                </div>
                <div  class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label id="lb_empresa">Empresa *</label>
                                <select name="cboEmpresa" id="cboEmpresa" class="select2">
                                </select>
                                <span id='msj_empresa' class="control-label"
                                      style='color:red;font-style: normal;' hidden></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label id="lb_empresa">Resultado</label>
                                <div id="resultado" style="overflow-y: auto;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <!--<a href="#" style="border-radius: 0px;" class="btn btn-info w-md" onclick="cargarModal()"><i class=" fa fa-plus-square-o" style="font-size: 18px;"></i>&nbsp;&nbsp;<i> </i><i> </i>Nuevo</a>-->
                        <button type="button" id="btnImportar" class="btn btn-info" onclick="importar()"><i class="fa fa-save" value="" >&nbsp;&nbsp;</i>Importar</button>
                        <button type="button" id="btnSalirModal" class="btn btn-danger" data-dismiss="modal"><i class="fa ion-android-close">&nbsp;&nbsp;</i>Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="vistas/com/solicitudRetiro/solicitud_retiro_listar.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>