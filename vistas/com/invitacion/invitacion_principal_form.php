<body>
    <div class="row">
        <div class="col-md-12 ">
            <div class="panel panel-default">
                <div class="panel-body">
                    <h3><b>Invitación a usuario principal formal</b></h3>
                    <div class="col-md-12 ">
                        <div class="panel-body">
                            <div class="row">
                                <div class="form-group col-md-3">
                                <style>
        .linea-separadora {
            border: none;
            height: 1px;
            background-color: #000; /* Cambia el color de la línea aquí */
            margin: 10px 0; /* Ajusta el espacio alrededor de la línea */
        }
    </style>
                                </div>
                         
                              
                           
                            </div>
                            <hr class="linea-separadora">
                        </div>
                        <form id="frmPersonaNatural" class="form">
                            <div class="row">
                            <br>
                                <div class="tab-content">
                                    <!--PESTAÑA GENERAL-->
                                    <div class="tab-pane active" id="tabGeneral">

                                        <div class="row">
                                        <div class="form-group col-md-4">
                                    <label>RUC *</label>
                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <input type="text" id="txtReinfo" name="txtReinfo" class="form-control" aria-required="true" value="" maxlength="11" minlength="11" />
                                    </div>
                                    <span id='msjRUC' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                </div>   
                                        <div class="form-group col-md-4">
                                    <label>Razón social *</label>
                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <input type="text" id="txtrazon" name="txtrazon" class="form-control" aria-required="true" value="" maxlength="250" />
                                    </div>
                                    <span id='msjRazon' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                </div>


                                <div class="form-group col-md-4">
                                    <label>Dirección *</label>
                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <input type="text" id="txtDireccion" name="txtDireccion" class="form-control" aria-required="true" value="" maxlength="250" />
                                    </div>
                                    <span id='msjDireccion' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                </div>
                                            </div>

                                                                                    <div class="row">
                                            

                                <div class="form-group col-md-4">
                                    <label>Ubigeo *</label>
                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <input type="text" id="txtUbigeo" name="txtUbigeo" class="form-control" aria-required="true" value="" maxlength="250" />
                                    </div>
                                    <span id='msjUbigeo' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                </div>
                              

                        
                                            
                                            <div class="form-group col-md-4">
                                        <label>Telefono *</label>
                                        <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <input type="text" id="txtTelefono" name="txtTelefono" class="form-control" aria-required="true" value="" maxlength="250" />
                                        </div>
                                        <span id='msjTelefono' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                    </div>
    
                                  
    
                                    <div class="form-group col-md-4">
                                        <label>Correo *</label>
                                        <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <input type="text" id="txtCorreo" name="txtCorreo" class="form-control" aria-required="true" value="" maxlength="250" />
                                        </div>
                                        <span id='msjCorreo' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                    </div>


                                            
                                                </div>
                                        </div>

                                    </div>
                                    <br>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <a href="#" class="btn btn-danger m-b-5" id="id" style="border-radius: 0px;" onclick="cargarListarActaCancelar()"><i class="fa fa-close"></i>&ensp;Cancelar</a>&nbsp;&nbsp;&nbsp;
                                        <button type="button" onclick="guardarSolicitud()" value="enviar" name="env" id="env" class="btn btn-info w-sm m-b-5" style="border-radius: 0px;"><i class="fa fa-send-o"></i>&ensp;Enviar</button>&nbsp;&nbsp;
                                    </div>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

         <script src="vistas/libs/imagina/assets/select2/select2.min.js"></script>
    <script src="vistas/com/invitacion/invitacion_principal_form.js"></script>
   
</body>