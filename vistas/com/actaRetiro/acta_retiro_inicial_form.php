<body>
    <div class="row">
        <div class="col-md-12 ">
            <div class="panel panel-default">
                <div class="panel-body">
                    <h3><b>Acta de Retiro</b></h3>
                    <div class="col-md-12 ">
                        <div class="panel-body">
                            <div class="row">
                                <div class="form-group col-md-3">

                                </div>

                                <div class="form-group col-md-3">
                                    <label>Placa Vehiculo *</label>
                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <input type="text" id="txtPlacaV" name="txtPlacaV" class="form-control" aria-required="true" value="" maxlength="250" />
                                    </div>
                                    <span id='msjFechaEntrega' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                </div>
                                <!-- <div class="form-group col-md-3">
                                                    <label>Zona *</label>
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <select name="cboTipoArchivo" id="cboTipoArchivo"
                                                            class="select2">

                                                        </select>

                                                    </div>
                                                    <span id="msjContacto" class="control-label"
                                                        style="color:red;font-style: normal;" hidden></span>
                                                </div> -->
                                <div class="form-group col-md-3">
                                    <br>
                                    <button type="button" onclick="setearInputPlaca()" value="buscar" name="env" id="buscar" class="btn btn-info w-sm m-b-5" style="border-radius: 0px;"><i class="fa fa-search"></i>&ensp;Buscar</button>&nbsp;&nbsp;
                                </div>
                            </div>

                        </div>
                        <form id="frmPersonaNatural" class="form">
                            <div class="row">
                                <!-- Contenedor para la tabla de resultados -->
                                <br>
                                <div id="datatable2" id="scroll"></div>
                                <!-- Aquí va el contenido del formulario -->
                                <div class="tab-content">
                                    <!--PESTAÑA GENERAL-->
                                    <div class="tab-pane active" id="tabGeneral">

                                        <div class="row">

                                            <div class="form-group col-md-3">
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <br> <br>
                                                    <div class="fileUpload btn w-lg m-b-5" id="multi" style="border-radius: 0px;background-color: #337Ab7;color: #fff;cursor:default;">
                                                        <div id="edi"><i class="ion-upload m-r-15" style="font-size: 16px;"></i>Subir foto de vehículo</div>
                                                        <input name="file" id="file" type="file" accept="image/*" class="upload" onchange='$("#upload-file-info").html($(this).val().slice(12));'>
                                                    </div>
                                                    &nbsp; &nbsp; <b class='' id="upload-file-info">Ninguna imagen seleccionada</b>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <img id="myImg" src="vistas/com/persona/imagen/none.jpg" onerror="this.src='vistas/com/persona/imagen/none.jpg'" alt="" class="img-thumbnail profile-img thumb-lg" />
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

                                      

                                            <div class="form-group col-md-3">
    <br>
    <label>¿Tiene carreta?</label>
    <div>
        <label>
            <input type="radio" name="carreta" value="si" onclick="toggleCarreta(true)"> Sí
        </label>
        <label>
            <input type="radio" name="carreta" value="no" checked onclick="toggleCarreta(false)"> No
        </label>
    </div>
</div>

<!-- Contenedor para el select2 con las placas de carreta -->
<div id="carretaSelectContainer" style="display: none;" class="form-group col-md-3">
                                                    <label>Carreta </label>
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <select name="cboCarreta" id="cboCarreta"
                                                            class="select2">

                                                        </select>

                                                    </div>
                                                    <span id="msjContacto" class="control-label"
                                                        style="color:red;font-style: normal;" hidden></span>
                                                </div>

                                            <div class="form-group col-md-12">
    

    <!-- Contenedor para los pesajes inicial y final -->
    <div id="pesajeContainer" >
        <div id="pesajeContainerContent">
            <label>Pesaje inicial (vehículo vacío)</label>
            <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <input type="text" id="txtPesaje" name="txtPesaje" class="form-control" aria-required="true" value="" maxlength="250" disabled />
                <div class="input-group-append">
                    <button type="button" class="btn btn-primary" onclick="traerPesaje('inicial')">
                        <i class="fa fa-search"></i> Obtener pesaje inicial
                    </button>
                </div>
            </div>
            <div id="fechaPesajeInicial" class="fecha-pesaje"></div> <!-- Fecha y hora pesaje inicial -->

           
        </div>
    </div>
</div>

<style>
    #pesajeContainer {
        margin-top: 20px;
        background-color: #f9f9f9;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    #pesajeContainerContent {
        margin-bottom: 20px;
    }

    .input-group {
        margin-bottom: 15px;
    }

    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
    }

    .btn-primary:hover {
        background-color: #0056b3;
        border-color: #004085;
    }

    .fecha-pesaje {
        margin-top: 10px;
        font-size: 0.9rem;
        color: #6c757d;
    }

    .fecha-pesaje span {
        font-weight: bold;
    }
</style>
                                        </div>

                                    </div>
                                    <br>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <a href="#" class="btn btn-danger m-b-5" id="id" style="border-radius: 0px;" onclick="cargarListarActaCancelar()"><i class="fa fa-close"></i>&ensp;Cancelar</a>&nbsp;&nbsp;&nbsp;
                                        <button type="button" onclick="guardarSolicitudInicial()" value="enviar" name="env" id="env" class="btn btn-info w-sm m-b-5" style="border-radius: 0px;"><i class="fa fa-send-o"></i>&ensp;Enviar</button>&nbsp;&nbsp;
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
    <script src="vistas/com/actaRetiro/acta_retiro_inicial_form.js"></script>
</body>

<style type="text/css" media="screen">
    @media screen and (max-width: 1000px) {
        #scroll {
            width: 1000px;
        }

        #muestrascroll {
            overflow-x: scroll;
        }
    }


    #datatable td {
        vertical-align: middle;
    }

    .sweet-alert button.cancel {
        background-color: rgba(224, 70, 70, 0.8);
    }

    .sweet-alert button.cancel:hover {
        background-color: #E04646;
    }

    .sweet-alert {

        border-radius: 0px;

    }

    .sweet-alert button {
        -webkit-border-radius: 0px;
        border-radius: 0px;

    }
</style>


