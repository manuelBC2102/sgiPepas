<div class="page-title">
    <div class="row">
        <span style="font-size: 26px;" id="titulo" class="title">&nbsp;&nbsp;&nbsp;<b> Registro Invitación</b> </span>
        &nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;<button type="button" class="btn btn-primary" id="btnRegistro" onclick="abrirModalMatriz();">
            <i class="fa fa-plus"></i> Registrar
        </button>
    </div>
</div>
<input type="hidden" id="hddIsDependiente" value="0">
<input type="hidden" id="txtaprobacion" >
<div class="row">
    <input type="hidden" id="txtTipo" name="txtTipo" class="form-control" value="" readonly="true" />
    <div class="panel panel-default" style="padding-bottom:  5px;">
        <div class="row">

            <div class="portlet">

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
                                         <span> <i class="fa fa-filter"></i> Filtrar por</span>
                                        <div class="portlet-widgets">
                                            <!-- <a onclick="exportarReporteReporteCompras()" title="">
                                                
                                                <i class="fa fa-file-excel-o"></i> -->
                                            </a>&nbsp;                                            
                                            <a id="loaderBuscar" onclick="actualizarBusquedaPersona()">
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
                                                <label>Planta</label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <select name="cboPlantaF" id="cboPlantaF" class="select2" >
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-6 ">
                                                <label>Zona</label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <select name="cboZonaF" id="cboZonaF" class="select2" >
                                                    </select>
                                                </div>
                                            </div>
                                            <!-- <div class="form-group col-md-6 ">
                                            <label>Producto</label>
                                            <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <input name="cboBien" id="cboBien" class="select2" />
                                            </div>
                                        </div> -->
                                        </div>

                                        <div class="row">
                                            <div class="form-group col-md-6 ">
                                                <label>Tipo documento</label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <select name="cboTipoDocumentoF" id="cboTipoDocumentoF" class="select2" >
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group col-md-6 ">
                                                <label>Usuario Aprobador</label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <select name="cboUsuarioF" id="cboUsuarioF" class="select2" >
                                                    </select>
                                                </div>
                                            </div>
                                            
                                        </div>

                                    <div class="modal-footer">
                                      
                                    <button type="button" href="#bg-info" onclick="listarMatriz()" value="enviar" class="btn btn-purple"> Buscar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">









                    </div>
                </div>
            </div>

        </div>
        <div class="row">
            <table id="datatable" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th style='text-align:center;'>Planta</th>
                        <th style='text-align:center;'>Zona</th>
                        <th style='text-align:center;'>Tipo Documento</th>
                        <th style='text-align:center;'>Nivel</th>
                        <th style='text-align:center;'>Usuario Aprobador</th>
                        <th style='text-align:center;'>Fecha creación</th>
                        <th style='text-align:center;'>Usuario creador</th>
                        <th style='text-align:center;'>Estado</th>

                        <!--<th style='text-align:center;'>Estado</th>-->
                        <th style='text-align:center;'>Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
        <br>
        <div style="clear:left">
            <p><b>Leyenda:</b>&nbsp;&nbsp;
                <i class="fa fa-edit" style="color:#088a08;"></i> Editar aprobador &nbsp;&nbsp;&nbsp;
                <i class="fa fa-trash-o" style="color:#cb2a2a;"></i> Eliminar&nbsp;&nbsp;&nbsp;
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
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="registroModal" tabindex="-1" role="dialog" aria-labelledby="registroModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="registroModalLabel">Registro de aprobación</h4>
                </div>
                <div class="modal-body">
                    <!-- Contenido del modal -->
                    <form>
                        <div class="form-group col-md-6 ">
                            <label>Plantas</label>
                            <select name="cboPlanta" id="cboPlanta" class="select2" placeholder="Seleccione planta"></select>

                        </div>
                        <div class="form-group col-md-6 ">
                            <label>Zonas</label>
                            <select name="cboZona" id="cboZona" class="select2" placeholder="Seleccione zona"></select>

                        </div>

                        <div class="form-group col-md-6 ">
                            <label>Tipo Documento</label>
                            <select name="cboTipoDocumento" id="cboTipoDocumento" class="select2" placeholder="Seleccione documento"></select>

                        </div>
                        <div class="form-group col-md-6">
                            <label>Nivel</label>
                            <!-- Select con Select2 -->
                            <select name="cboNivel" id="cboNivel" class="select2" placeholder="Seleccione nivel">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6 ">
                            <label>Usuario Aprobador</label>
                            <select name="cboUsuario" id="cboUsuario" class="select2" placeholder="Seleccione usuario"></select>

                        </div>


                        <div class="form-group col-md-6">
                            <label>Firma</label>&nbsp;
                          
                                
                                    <br> 
                                    <div class="fileUpload btn w-lg m-b-2" style="background-color:#321d48; color:white; min-width: 40px;" id="multi" style="border-radius: 0px;background-color: #337Ab7;color: #fff;cursor:default;">
                                        <div id="edi"><i class="ion-upload m-r-15" style="font-size: 10px;"></i>Firma</div>
                                        <input name="file" id="file" type="file" accept="image/*" class="upload" onchange='$("#upload-file-info").html($(this).val().slice(10));'>
                                    </div>
                                    <b class='' style="font-size: 10px;" id="upload-file-info">Ninguna imagen seleccionada</b>
                                
                                <input type="hidden" id="secretImg" value="" />
                                <script>
                                    $(function() {
                                        $(":file").change(function() {
                                            if (this.files && this.files[0]) {
                                                var reader = new FileReader();
                                                reader.onload = imageIsLoaded;
                                                reader.readAsDataURL(this.files[0]);
                                            }
                                        });
                                    });

                                    function imageIsLoaded(e) {
                                        $('#secretImg').attr('value', e.target.result);
                                        $('#myImg').attr('src', e.target.result);
                                        $('#myImg').attr('width', '128px');
                                        $('#myImg').attr('height', '128px');
                                    };
                                </script>
                            
                        </div>

                        <div class="form-group col-md-12">
                                                
                                                <label>Comentario </label>

                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <input type="text" id="txtComentario" name="txtComentario" class="form-control" aria-required="true" value="" maxlength="250" />
                                    </div>
                                            </div>
                        <br><br>
                        <!-- Agrega más campos según sea necesario -->
                    </form>
                </div>
                <div class="modal-footer">
                    
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button  id="btnEnviar" type="button" class="btn btn-primary" onclick="guardarAprobador()" >Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="vistas/com/matriz/matriz_listar.js"></script>