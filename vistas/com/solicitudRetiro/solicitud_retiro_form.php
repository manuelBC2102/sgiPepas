
<body> 
    <div class="row">
        <div class="col-md-12 ">
            <div class="panel panel-default">
                <div class="panel-body">
                <h3 ><b>Solicitud de Retiro</b></h3>
                    <div class="col-md-12 ">
                        <div class="panel-body">
                            <form  id="frmPersonaNatural"  class="form">
                                <div class="row">                                    

                               
                                              
                                        

                                    </ul>
                                    <div class="tab-content">

                                        <!--PESTAÑA GENERAL-->
                                        <div class="tab-pane active" id="tabGeneral">
                                            <div class="row">
   

         
                                           
                                            <div class="row">
                                            <div class="form-group col-md-6">
                                                    <label >Fecha Entrega *</label>
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <input type="date" id="txtFechaEntrega" name="txtFechaEntrega" class="form-control" aria-required="true" value="" maxlength="250"/>
                                                    </div>
                                                    <span id='msjFechaEntrega' class="control-label"
                                                          style='color:red;font-style: normal;' hidden></span>
                                                </div>

                                                <div class="form-group col-md-6">
                                               
                                                    <label>Usuario Solicitud *</label>
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <select name="cboReinfo" id="cboReinfo" class="select2" onchange="setearComboConvenioSunat()" >
                                                        </select>
                                                    </div>
                                                    <span id='msjReinfo' class="control-label"
                                                          style='color:red;font-style: normal;' hidden></span>
                                                </div>

                                            
                                            </div>


                                            <div class="row">                                            
                                                <div class="form-group col-md-6">
                                                    <br>
                                                    <label>Transportista *</label>
                                                    <i id="iconoTransportista" class="fas" style="display: none; margin-left: 10px;"></i>
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <select name="cboTransportista" id="cboTransportista" class="select2" >
                                                        </select>
                                                    </div>
                                                    <span id='msjTransportista' class="control-label"
                                                          style='color:red;font-style: normal;' hidden></span>
                                                </div>                        
                                                <div class="form-group col-md-6">
                                                <br>
                                                    <label>Conductor *</label>
                                                    <i id="iconoConductor" class="fas" style="display: none; margin-left: 10px;"></i>
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <select name="cboConductor" id="cboConductor" class="select2" >
                                                        </select>
                                                    </div>
                                                    <span id='msjConductor' class="control-label"
                                                          style='color:red;font-style: normal;' hidden></span>
                                                </div>  
                                            </div>
                                            <div class="row">                                            
                                                <div class="form-group col-md-6">
                                                <br>
                                                    <label>Vehiculo *</label>
                                                    <i id="iconoVehiculo" class="fas" style="display: none; margin-left: 10px;"></i>
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <select name="cboVehiculo" id="cboVehiculo" class="select2"  >
                                                        </select>
                                                    </div>
                                                    <span id='msjVehiculo' class="control-label"
                                                          style='color:red;font-style: normal;' hidden></span>
                                                </div>
                                                <div class="form-group col-md-2">
                                                <br>
                                                    <label>Capacidad Kilos</label>
                                                    <div >
                                                        <input type="text" id="txtCapacidad" name="txtCapacidad" class="form-control"  required="" aria-required="true" value="" maxlength="200"/>
                                                    </div>
                                                    <span id='msjCapacidad' class="control-label"
                                                          style='color:red;font-style: normal;' hidden></span>
                                                </div>
                                                
                                                <div class="form-group col-md-2">
                                                <br>
                                                    <label>Constancia</label>
                                                    <div >
                                                        <input type="text" id="txtConstancia" name="txtConstancia" class="form-control" required="" aria-required="true" value="" maxlength="200"/>
                                                    </div>
                                                    <span id='msjConstancia' class="control-label"
                                                          style='color:red;font-style: normal;' hidden></span>
                                                </div>

                                                <div class="form-group col-md-2">
                                                <br>
                                                    <label>Cantidad lotes</label>
                                                    <div >
                                                        <input type="text" id="txtLotes" name="txtLotes" class="form-control" required="" aria-required="true" value="" maxlength="200"/>
                                                    </div>
                                                    <span id='msjLotes' class="control-label"
                                                          style='color:red;font-style: normal;' hidden></span>
                                                </div>
                                                </div> 
                                           
                                            <div class="row">
                                            <div class="form-group col-md-6">
                                            <br>
                                                    <label>Zona *</label>
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <select name="cboZona" id="cboZona" class="select2"  >
                                                        </select>
                                                    </div>
                                                    <span id='msjZona' class="control-label"
                                                          style='color:red;font-style: normal;' hidden></span>
                                                </div>
                                                <div class="form-group col-md-6">
                                                <br>
                                                    <label>Planta *</label>
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <select name="cboPlanta" id="cboPlanta" class="select2"  >
                                                        </select>
                                                    </div>
                                                    <span id='msjPlanta' class="control-label"
                                                          style='color:red;font-style: normal;' hidden></span>
                                                </div>
                                            </div>   
                                            </div>     
                                            <br>
                               
                                        </div>
                                        <!--FIN PESTAÑA GENERAL-->

                                        <!--PESTAÑA CONTACTOS-->
                                 
                                        <!--FIN PESTAÑA CONTACTOS-->

                                        <!--PESTAÑA DIRECCIONES-->
                                    
                                        <!--FIN PESTAÑA DIRECCIONES-->
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <a href="#" class="btn btn-danger m-b-5" id="id" style="border-radius: 0px;" onclick="cargarListarPersonaCancelar()" ><i class="fa fa-close"></i>&ensp;Cancelar</a>&nbsp;&nbsp;&nbsp;
<!--                                        <button type="button" onclick="guardarPersona()" value="enviar" name="env" id="env" class="btn btn-info w-sm m-b-5" style="border-radius: 0px;"><i class="fa fa-send-o"></i>&ensp;Enviar</button>&nbsp;&nbsp;-->
                                        <button type="button" onclick="guardarSolicitud()" value="enviar" name="env" id="env" class="btn btn-info w-sm m-b-5" style="border-radius: 0px;"><i class="fa fa-send-o"></i>&ensp;Enviar</button>&nbsp;&nbsp;
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!--        <script src="vistas/libs/imagina/assets/select2/select2.min.js"></script>-->
    <script src="vistas/com/solicitudRetiro/solicitud_retiro_form.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    
</body>
