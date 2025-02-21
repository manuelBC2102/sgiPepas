<div class="page-title">
    <h3 id="titulo" class="title">Entrega resultados</h3>
</div>
<input type="hidden" id="hddIsDependiente" value="0">
<div class="row">
    <input type="hidden" id="txtTipo" name="txtTipo" class="form-control" value="" readonly="true" />
    <div class="panel panel-default" style="padding-bottom:  5px;">
        <div class="row">
            <div class="col-lg-12">
                <div class="portlet">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="input-group m-t-10">

                                <div class="input-group-btn" id="listaPersonaTipo">
                                    <!--                            <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="true"  style="width: 100%; padding-top: 10px;padding-bottom: 10px;"><i class=" fa fa-plus-square-o"></i> Nueva <span class="caret"></span></button>
                            <ul id="listaPersonaTipo" class="dropdown-menu" role="menu">
                            </ul>-->
                                </div>

                           
                                <!-- <div class="input-group-btn" style="padding-left: 0px;padding-right: 0px;">
                                    <div class="btn-toolbar" role="toolbar" style="float: right">
                                        <div class="input-group-btn">
                                            <a type="button" class="btn btn-success" onclick="actualizarBusquedaPersona()" title="Actualizar resultados de búsqueda"><i class="ion-refresh"></i></a>
                                        </div>


                                    </div>
                                </div> -->

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
        <div class="table">
                            <div id="dataList" >

                            </div>
                        </div>
        </div>
        <br>
        <div style="clear:left">
            <p><b>Leyenda:</b>&nbsp;&nbsp;
                <i class="fa fa-list" style="color:blue;"></i> Registrar resultados &nbsp;&nbsp;&nbsp;

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
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label id="lb_empresa">Empresa *</label>
                                <select name="cboEmpresa" id="cboEmpresa" class="select2">
                                </select>
                                <span id='msj_empresa' class="control-label" style='color:red;font-style: normal;' hidden></span>
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
                        <button type="button" id="btnImportar" class="btn btn-info" onclick="importar()"><i class="fa fa-save" value="">&nbsp;&nbsp;</i>Importar</button>
                        <button type="button" id="btnSalirModal" class="btn btn-danger" data-dismiss="modal"><i class="fa ion-android-close">&nbsp;&nbsp;</i>Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="modalActualizarPesajes" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Actualizar Pesajes</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="selectSolicitud">Seleccionar Solicitud</label>
                        
                            <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <select name="cboTipoArchivo" id="cboTipoArchivo" class="select2">

                                </select>

                            </div>
                            <span id="msjContacto" class="control-label" style="color:red;font-style: normal;" hidden></span>
                        
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="hiddenRowId" value="">
                    <button type="button" class="btn btn-primary" onclick="actualizarPesajes()">Confirmar</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

</div>
<script src="vistas/com/entregaResultados/entrega_resultados_listar.js"></script>