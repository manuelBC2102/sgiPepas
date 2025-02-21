<body>
    <div class="row">
        <div class="col-md-12 ">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="col-md-12 ">
                        <div class="panel-body">
                            <div class="row">
                                <div class="form-group col-md-5">
                                    <label>Tipo Documento *</label>
                                    <div class="input-group">
                                        <select name="cboTipoArchivo" id="cboTipoArchivo" class="select2 ">
                                            <option value="" id="infoCboTipoArchivo">No seleccionado</option>

                                        </select>

                                    </div>
                                    <span id="msjContacto" class="control-label" style="color:red;font-style: normal;"
                                        hidden></span>
                                </div>

                                <div class="form-group col-md-5">
                                    <label>Archivo *</label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input name="file" id="file" type="file" accept="image/*, application/pdf"
                                                class="custom-file-input">
                                        </div>
                                    </div>
                                    <span id="msjContactoTipo" class="control-label" style="color:red;font-style: normal;"
                                        hidden></span>
                                </div>
                                <div class="form-group col-md-5">
                                    <img id="myImg" src="vistas/com/persona/imagen/none.jpg"
                                        onerror="this.src='vistas/com/persona/imagen/none.jpg'" alt=""
                                        class="img-thumbnail profile-img thumb-lg" />
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
                                        }
                                        ;
                                    </script>
                                </div>

                            </div>
                            


                            
                            




                            <div class="row">
                                <div class="form-group col-md-5">
                                    <button type="button" name="btnGuardar" id="btnGuardar"
                                        class="btn btn-info w-sm m-b-5" style="border-radius: 0px;"
                                        onclick="insertDocumentoDetalle()"><i class="fa fa-upload"></i>&nbsp;Agregar
                                        Documento</button>
                                </div>
                            </div>

                            
                           


                            <div class="panel panel-body" id="muestrascroll">
                                <span id="msjContactoDetalle" class="control-label"
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


                            <table class="table table-bordered" id="archivoTable">
                                <thead>
                                    <tr>
                                        <th>Tipo Documento</th>
                                        <th>Archivo</th>
                                        <th>Fecha Creacion</th>
                                        <th>Visualizar</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>

                            <a href="#" class="btn btn-danger m-b-5" id="id" style="border-radius: 0px;"
                                onclick="cargarListarPersonaCancelar()"><i
                                    class="fa fa-close"></i>&ensp;Cancelar</a>&nbsp;&nbsp;&nbsp;




                            <div style="clear:left">
                                <p><b>Leyenda:</b>&nbsp;&nbsp;
                                    <i class="fa fa-trash-o" style="color:#cb2a2a;"></i> Eliminar
                                    Documento&nbsp;&nbsp;&nbsp;
                                    <i class="fa fa-edit" style="color:#E8BA2F;"></i> Editar Documento&nbsp;&nbsp;&nbsp;
                                </p><br>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="vistas/com/persona/persona_subir_archivo.js"></script>

</body>