<div class="page-title">
    <h3 id="titulo" class="title">Registro Invitación Usuario Formal</h3>
</div>
<input type="hidden" id="hddIsDependiente" value="0">
<div class="row">
    <input type="hidden" id="txtTipo" name="txtTipo" class="form-control" value="" readonly="true"/>
    <div class="panel panel-default" style="padding-bottom:  5px;">
        <div class="row">
            <div class="col-lg-12">
                <div  class="portlet" >
                    <div class="row">                        
                        <div class="col-md-12">
                            <div class="input-group m-t-10">
                                
                        <div class="input-group-btn" id="listaPersonaTipo">
<!--                            <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="true"  style="width: 100%; padding-top: 10px;padding-bottom: 10px;"><i class=" fa fa-plus-square-o"></i> Nueva <span class="caret"></span></button>
                            <ul id="listaPersonaTipo" class="dropdown-menu" role="menu">
                            </ul>-->
                        </div>
                                
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">                 
                            <!--<div class="col-md-10" style="padding-left: 0px;padding-right: 0px;">-->
                                <div class="input-group" id="divBuscador">                                
                                    <span class="input-group-btn">
                                        <a type="button" data-toggle="dropdown" class="dropdown-toggle btn btn-effect-ripple btn-primary" href="#">
                                            <i class="caret"></i>
                                        </a>
                                        <!-- <ul class="dropdown-menu extended fadeInUp animated nicescroll" tabindex="5001" style="overflow: hidden; outline: none;width: 1052px;" id="ulBuscadorDesplegable">
                                            <div class="portlet-body">
                                                <div class="row">
                                                    <div class="form-group col-md-2">
                                                        <label>Transportista</label>
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                        <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                            <input type="text" id="txtCodigoBusqueda" name="txtCodigoBusqueda" class="form-control" value="" maxlength="500"/>
                                                        </div>
                                                    </div>

                                                    <div class="form-group col-md-2">
                                                        <label>Vehiculo</label>
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                        <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                            <input type="text" id="txtNombresBusqueda" name="txtNombresBusqueda" class="form-control" value="" maxlength="500"/>
                                                        </div>
                                                    </div>                                
                                                </div>
                                                <div class="row">
                                                    <div class="form-group col-md-2">
                                                        <label>Reinfo</label>
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                            <input type="text" id="txtNombresBusqueda" name="txtNombresBusqueda" class="form-control" value="" maxlength="500"/>
                                                        </div>
                                                    </div>

                                                    <div class="form-group col-md-2">
                                                        <label>Fecha Registro</label>
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                        <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                            <input type="date" id="txtNombresBusqueda" name="txtNombresBusqueda" class="form-control" value="" maxlength="500"/>
                                                        </div>
                                                    </div>   
                                                </div>
                                                <div style="float: right">
                                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" >
                                                        <button id="btnBusqueda" type="button" href="#bg-info" onclick="limpiarBuscadores()" class="btn btn-danger"> <i class="fa fa-close"></i> Cancelar</button>
                                                        <button id="btnBusqueda" type="button" href="#bg-info" onclick="buscarPersona(1)" class="btn btn-purple"> <i class="fa fa-search"></i> Buscar</button>                                        
                                                    </div>
                                                </div>
                                            </div>
                                        </ul> -->
                                    </span>
                                    <input type="text" id="txtBuscar"   name="txtBuscar" data-toggle="dropdown" class="dropdown-toggle form-control" placeholder="Buscar" onkeyup="buscarCriteriosBusquedaSolicitud()">                                
                                    <ul class="dropdown-menu extended fadeInUp animated nicescroll" tabindex="5001" style="overflow: hidden; outline: none;width: 1052px;" id="ulBuscadorDesplegable2">

                                    </ul>

                                </div>
                            <!--</div>-->
                        </div>
                            <div class="input-group-btn" style="padding-left: 0px;padding-right: 0px;">
                                <div class="btn-toolbar" role="toolbar"  style="float: right" >
                                    <div class="input-group-btn">
                                        <a type="button" class="btn btn-success" onclick="actualizarBusquedaPersona()" title="Actualizar resultados de búsqueda"><i class="ion-refresh"></i></a>
                                    </div>

                               
                                </div>
                            </div>

                    </div>
                </div>
                </div>
                </div>
            </div>
        </div>
        <div class="row">
            <table id="datatable" class="table table-striped table-bordered" >
                <thead>
                    <tr>
                        <th style='text-align:center;'>Fecha registro</th>
                        <th style='text-align:center;'>RUC</th>
                        <th style='text-align:center;'>Razón Social</th>
                        <th style='text-align:center;'>Ubigeo</th>
                        <th style='text-align:center;'>Dirección</th>
                        <th style='text-align:center;'>Estado</th>
                        <th style='text-align:center;'>Usuario</th>
                        <!--<th style='text-align:center;'>Estado</th>-->
                        <th style='text-align:center;'>Acc.</th>
                    </tr>
                </thead>
            </table>
        </div>
        <br>
        <div style="clear:left">
            <p><b>Leyenda:</b>&nbsp;&nbsp;
                <i class="fa fa-edit" style="color:#088a08;"></i> Editar invitación &nbsp;&nbsp;&nbsp;
                <i class="fa fa-trash-o" style="color:#cb2a2a;"></i> Eliminar&nbsp;&nbsp;&nbsp;
                <i class="fa fa-file-pdf-o" style="color:#0366b0;"></i> Invitación&nbsp;&nbsp;&nbsp;
                <i class="fa fa-link" style="color:#337ab7;"></i> Link Invitación&nbsp;&nbsp;&nbsp;
<!--                <i class='ion-checkmark-circled' style="color:#5cb85c;"></i> Estado activo &nbsp;&nbsp;&nbsp;
                <i class="ion-flash-off" style="color:#cb2a2a;"></i> Estado inactivo -->
            </p>
        </div>
    </div>



    <!-- Modal -->
<div class="modal fade" id="linkModal" tabindex="-1" role="dialog" aria-labelledby="linkModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="linkModalLabel">Link Invitación</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="generatedLink"></p>
            </div>
            
            <div class="modal-footer">
            <button class="btn btn-info" onclick="copiarHTML()">Copiar </button>
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
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
<script src="vistas/com/invitacion/invitacion_principal_listar.js"></script>