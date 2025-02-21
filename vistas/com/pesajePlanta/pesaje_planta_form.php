<body>
    <div class="row">
        <div class="col-md-12 ">
            <div class="panel panel-default">
                <div class="panel-body">
                    <h3><b>Pesaje planta</b></h3>
                    <div class="col-md-12 ">
                        <div class="panel-body">
                      
                        <div class="modal fade" id="loteModal" tabindex="-1" role="dialog" aria-labelledby="loteModalLabel" aria-hidden="true">
        <div class="modal-dialog-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loteModalLabel">Agregar/Editar Lote</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Loader -->
                    <div id="loader" class="loader-overlay" style="display: none;">
                        <div class="spinner"></div>
                    </div>
                    
                    <form id="loteForm">
                        <div class="col-md-4">
                            <label for="ticket1">Nombre Lote </label>
                            <input type="text" class="form-control" id="nombre_lote" name="nombre_lote">
                        </div>
                        <div class="col-md-4">
                            <label for="ticket1">Ticket </label>
                            <input type="text" class="form-control" id="ticket1" name="ticket1">
                        </div>
                        <div class="col-md-4">
                            <label for="peso_bruto">Peso Bruto</label>
                            <input type="text" class="form-control" id="peso_bruto" name="peso_bruto" oninput="calculatePesoNeto()">
                        </div>
                        <div class="col-md-4">
                            <label for="peso_tara">Peso Tara</label>
                            <input type="text" class="form-control" id="peso_tara" name="peso_tara" oninput="calculatePesoNeto()">
                        </div>
                        <div class="col-md-4">
                            <label for="peso_neto">Peso Neto</label>
                            <input type="text" class="form-control" id="peso_neto" disabled name="peso_neto">
                        </div>
                        <div class="col-md-6">
                            <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <br> <br>
                                <div class="fileUpload btn w-lg m-b-5" id="multi" style="border-radius: 0px;background-color: #337Ab7;color: #fff;cursor:default;">
                                    <div id="edi" ><i class="ion-upload m-r-15" style="font-size: 16px;"></i>Subir imagen</div>
                                    <input name="file" id="file"  type="file" accept="image/*, application/pdf" class="upload" onchange='$("#upload-file-info").html($(this).val().slice(12));' >
                                </div>
                                &nbsp; &nbsp; <b class='' id="upload-file-info">Ninguna archivo seleccionada</b>
                            </div>
                            <img id="myImg" src="vistas/com/persona/imagen/none.jpg" onerror="this.src='vistas/com/persona/imagen/none.jpg'" alt="" class="img-thumbnail profile-img thumb-lg" />
                            <input type="hidden" id="secretImg" value="" />                                       
                            <script>
                                $(function () {
                                    $(":file").change(function () {
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
                        <div class="col-md-6">
                            <br><br><br>
                            <button type="button" class="btn btn-primary" id="saveLote">Guardar Lote</button>
                        </div>
                    </form>
                    <hr>
                    <table class="table table-bordered" id="loteTable">
                        <thead>
                            <tr>
                                <th>Nombre Lote</th>
                                <th>Ticket</th>
                                <th>Peso Bruto</th>
                                <th>Peso Tara</th>
                                <th>Peso Neto</th>
                                <th>Archivo</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
                        </div>
                        
                            <div class="row">
                                <!-- Contenedor para la tabla de resultados -->
                                <br>
                                <div id="datatable2" id="scroll"></div>
                                <!-- Aquí va el contenido del formulario -->
                                <div class="tab-content">
                                    <!--PESTAÑA GENERAL-->
                                    <div class="tab-pane active" id="tabGeneral">



                                    </div>
                                    <br>
                                </div>
                                <form id="frmPersonaNatural" class="form">
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

    <!--        <script src="vistas/libs/imagina/assets/select2/select2.min.js"></script>-->
    <script src="vistas/com/pesajePlanta/pesaje_planta_form.js"></script>
    
</body>

<style>
        .loader-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border-left-color: #007bff;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>