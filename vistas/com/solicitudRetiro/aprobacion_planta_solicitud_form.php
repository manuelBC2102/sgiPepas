<body>
    <div class="row">
        <div class="col-md-12 ">
            <div class="panel panel-default">
                <div class="panel-body">
                    <h3><b>Aprobación de solicitud</b></h3>
                    <div class="col-md-12 ">
                        <div class="panel-body">
                            <form id="frmPersonaNatural" class="form">
                                <div class="row">
                                <ul class="nav nav-tabs nav-justified">

| <li class="active">
    <a href="#tabGeneral" data-toggle="tab" aria-expanded="true">
        <span class="visible-xs"><i class="fa fa-home"></i></span>
        <span class="hidden-xs">General</span>
    </a>
</li>

<li class="" id="liPersonaDocumentos">
                                            <a href="#tabDocumentos" data-toggle="tab" aria-expanded="false">
                                                <span class="visible-xs"><i class="ion-ios7-bookmarks"></i></span>
                                                <span class="hidden-xs">Documentos</span>
                                            </a>
                                        </li>


</ul>
                                    </ul>
                                    <div class="tab-content">

                                        <!--PESTAÑA GENERAL-->
                                        <div class="tab-pane active" id="tabGeneral">
                                            <div class="row">




                                                <div class="row">
                                                    <div class="form-group col-md-6">
                                                        <label>Fecha Entrega *</label>
                                                        <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                            <input disabled type="date" id="txtFechaEntrega" name="txtFechaEntrega" class="form-control" aria-required="true" value="" maxlength="250" />
                                                        </div>
                                                        <span id='msjFechaEntrega' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                                    </div>


                                                </div>


                                                <div class="row">
                                                    <div class="form-group col-md-6">
                                                        <br>
                                                        <label>Transportista *</label>
                                                        <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                            <select disabled name="cboTransportista" id="cboTransportista" class="select2" onchange="setearComboConvenioSunat()">
                                                            </select>
                                                        </div>
                                                        <span id='msjTransportista' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <br>
                                                        <label>Conductor *</label>
                                                        <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                            <select disabled name="cboConductor" id="cboConductor" class="select2" onchange="setearComboConvenioSunat()">
                                                            </select>
                                                        </div>
                                                        <span id='msjConductor' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="form-group col-md-6">
                                                        <br>
                                                        <label>Vehiculo *</label>
                                                        <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                            <select disabled name="cboVehiculo" id="cboVehiculo" class="select2" onchange="setearComboConvenioSunat()">
                                                            </select>
                                                        </div>
                                                        <span id='msjVehiculo' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                                    </div>
                                                    <div class="form-group col-md-3">
                                                        <br>
                                                        <label>Capacidad Kilos</label>
                                                        <div>
                                                            <input disabled type="text" id="txtCapacidad" name="txtCapacidad" class="form-control" required="" aria-required="true" value="" maxlength="200" />
                                                        </div>
                                                        <span id='msjCapacidad' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                                    </div>

                                                    <div class="form-group col-md-3">
                                                        <br>
                                                        <label>Constancia</label>
                                                        <div>
                                                            <input disabled type="text" id="txtConstancia" name="txtConstancia" class="form-control" required="" aria-required="true" value="" maxlength="200" />
                                                        </div>
                                                        <span id='msjConstancia' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="form-group col-md-6">
                                                        <br>
                                                        <label>Zona *</label>
                                                        <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                            <select disabled name="cboZona" id="cboZona" class="select2" onchange="setearComboConvenioSunat()">
                                                            </select>
                                                        </div>
                                                        <span id='msjZona' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <br>
                                                        <label>Planta *</label>
                                                        <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                            <select disabled name="cboPlanta" id="cboPlanta" class="select2" onchange="setearComboConvenioSunat()">
                                                            </select>
                                                        </div>
                                                        <span id='msjPlanta' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <br>

                                        </div>
                                        <!--FIN PESTAÑA GENERAL-->
                                        <div class="tab-pane" id="tabDocumentos">
                                            <input type="hidden" id="idDocumentoDetalle" value="" />

                                            <div class="row">
                                                <div class="form-group col-md-6">
                                                    <label>Tipo Documento *</label>
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <select name="cboTipoArchivo" id="cboTipoArchivo"
                                                            class="select2">

                                                        </select>

                                                    </div>
                                                    <span id="msjContacto" class="control-label"
                                                        style="color:red;font-style: normal;" hidden></span>
                                                </div>


                                                <div class="form-group col-md-6">
                                                    <label>Archivo *</label>
                                                    <div class="input-group">
                                                        <div class="custom-file">
                                                            <input name="file2" id="file2" type="file"
                                                                accept="image/*, application/pdf"
                                                                class="custom-file-input" />
                                                        </div>
                                                    </div>
                                                    <span id="msjPersonaArchivoTipo" class="control-label"
                                                        style="color:red;font-style: normal;" hidden></span>
                                                </div>
                                                <div class="form-group col-md-5">
                                                    <img id="myImg2" src="vistas/com/persona/imagen/none.jpg"
                                                        onerror="this.src='vistas/com/persona/imagen/none.jpg'" alt=""
                                                        class="img-thumbnail profile-img thumb-lg" />
                                                    <input type="hidden" id="secretImg2" value="" />
                                                    <script>
                                                        $(function () {
                                                            $(":file").change(function () {
                                                                if (this.files && this.files[0]) {
                                                                    var reader = new FileReader();
                                                                    reader.onload = imageIsLoaded2;
                                                                    reader.readAsDataURL(this.files[0]);
                                                                }
                                                            });
                                                        });
                                                        function imageIsLoaded2(e) {
                                                            $('#secretImg2').attr('value', e.target.result);
                                                            $('#myImg2').attr('src', e.target.result);
                                                            $('#myImg2').attr('width', '128px');
                                                            $('#myImg2').attr('height', '128px');

                                                        }
                                                        ;
                                                    </script>
                                                </div>

                                            </div>

                                            <div class="row ">
                                                <div class="form-group col-md-5">
                                                    <button type="button" name="btnGuardar" id="btnGuardar"
                                                        class="btn btn-info w-sm m-b-5" style="border-radius: 0px;"
                                                        onclick="insertDocumentoDetalle()"><i
                                                            class="fa fa-upload"></i>&nbsp;Agregar
                                                        Documento</button>
                                                </div>
                                            </div>

                                            <table class="table table-bordered" id="archivoTable">
                                                <thead>
                                                    <tr>
                                                        <th>Tipo Documento</th>
                                                        <th>Archivo</th>
                                                        <th>Fecha Creación</th>
                                                        <th style="text-align:center;">Estado </th>
                                                        <th style="text-align:center;">Acción</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>


                                            <div class="panel panel-body" id="muestrascroll">
                                                <span id="msjDocumentoDetalle" class="control-label"
                                                    style="color:red;font-style: normal;" hidden></span>
                                                <div class="row" id="scroll">
                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <div class="table">
                                                            <div id="dataList">
                                                                <table id="dataList">

                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div style="clear:left">
                                                <p><b>Leyenda:</b>&nbsp;&nbsp;
                                                    <i class="fa fa-trash-o" style="color:#cb2a2a;"></i> Eliminar
                                                    Documento&nbsp;&nbsp;&nbsp;
                                                    <!-- <i style='color:green;'  class='fa fa-eye'>     </i> Ver 
                                                            Documento&nbsp;&nbsp;&nbsp; -->
                                                </p><br>
                                            </div>
                                        </div>
                                        <!--PESTAÑA CONTACTOS-->

                                        <!--FIN PESTAÑA CONTACTOS-->

                                        <!--PESTAÑA DIRECCIONES-->

                                        <!--FIN PESTAÑA DIRECCIONES-->
                                    </div>

                                              <div class="tab-content">

                                        <!--PESTAÑA GENERAL-->

                                        <!--FIN PESTAÑA GENERAL-->

                                        <!--PESTAÑA CONTACTOS-->

                                        <!--FIN PESTAÑA CONTACTOS-->

                                        <!--PESTAÑA DIRECCIONES-->

                                        <!--FIN PESTAÑA DIRECCIONES-->
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <a href="#" class="btn btn-warning m-b-5" id="id" style="border-radius: 0px;" onclick="cargarListarPersonaCancelar()"><i class="fa fa-close"></i>&ensp;Cancelar</a>&nbsp;&nbsp;&nbsp;
                                        <!--                                        <button type="button" onclick="guardarPersona()" value="enviar" name="env" id="env" class="btn btn-info w-sm m-b-5" style="border-radius: 0px;"><i class="fa fa-send-o"></i>&ensp;Enviar</button>&nbsp;&nbsp;-->
                                        <button type="button" onclick="aprobarSolicitud()" value="enviar" name="env" id="env" class="btn btn-info w-sm m-b-5" style="border-radius: 0px;"><i class="fa fa-send-o"></i>&ensp;Aprobar</button>&nbsp;&nbsp;
                                        <button type="button" onclick="rechazarSolicitud()" value="enviar" name="env" id="env" class="btn btn-danger w-sm m-b-5" style="border-radius: 0px;"><i class="fa fa-remove-o"></i>&ensp;Rechazar</button>
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
    <script src="vistas/com/solicitudRetiro/aprobacion_planta_solicitud_form.js"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->
    <script src="vistas/libs/imagina/assets/select2/select2.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">

<!-- SweetAlert JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
  
</body>