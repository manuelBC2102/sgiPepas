<body>
    <style>
        .inactivo {
            background-color: #f0f0f0;
            /* Color de fondo para la fila inactiva */
            color: #aaa;
            /* Color del texto para la fila inactiva */
            opacity: 0.6;
            /* Opacidad para que parezca deshabilitado */
        }

        .inactivo a {
            color: #00f;
            /* Color para los enlaces en filas inactivas */
            opacity: 1;
            /* Asegurarse de que los enlaces no tengan opacidad */
            pointer-events: auto;
            /* Permitir interacción con los enlaces */
            text-decoration: underline;
            /* Opción para diferenciar enlaces */
        }
        .inactivo2 {
            background-color: #f0f0f0;
            /* Color de fondo para la fila inactiva */
            color: #aaa;
            /* Color del texto para la fila inactiva */
            opacity: 0.6;
            /* Opacidad para que parezca deshabilitado */
        }

        .inactivo2 a {
            color: #00f;
            /* Color para los enlaces en filas inactivas */
            opacity: 1;
            /* Asegurarse de que los enlaces no tengan opacidad */
            pointer-events: auto;
            /* Permitir interacción con los enlaces */
            text-decoration: underline;
            /* Opción para diferenciar enlaces */
        }
    </style>
    <div class="row">
        <div class="col-md-12 ">
            <div class="panel panel-default">
                <div class="panel-body">
                    <h4><b id="titulo"></b></h4>
                    <div class="col-md-12 ">
                        <div class="panel-body">
                            <form id="frmPersonaNatural" class="form">
                                <div class="row">
                                    <ul class="nav nav-tabs nav-justified">

                                         <li class="active">
                                            <a href="#tabGeneral" data-toggle="tab" aria-expanded="true">
                                                <span class="visible-xs"><i class="fa fa-home"></i></span>
                                                <span class="hidden-xs">General</span>
                                            </a>
                                        </li>
                                        <li class="" id="liPersonaDireccion">
                                            <a href="#tabDireccion" data-toggle="tab" aria-expanded="false">
                                                <span class="visible-xs"><i class="ion-arrow-graph-up-right"></i></span>
                                                <span class="hidden-xs">Dirección</span>
                                            </a>
                                        </li>
                                        <li class="" id="liPersonaContactos">
                                            <a href="#tabContactos" data-toggle="tab" aria-expanded="false">
                                                <span class="visible-xs"><i class="ion-ios7-bookmarks"></i></span>
                                                <span class="hidden-xs">Contactos</span>
                                            </a>
                                        </li>
                                        <li class="" id="liPersonaDocumentos">
                                            <a href="#tabDocumentos" data-toggle="tab" aria-expanded="false">
                                                <span class="visible-xs"><i class="ion-ios7-bookmarks"></i></span>
                                                <span class="hidden-xs">Documentos</span>
                                            </a>
                                        </li>

                                        <li class="" id="litPersonaPlantaDocumento">
                                            <a href="#tabPlantaDocumento" data-toggle="tab" aria-expanded="false">
                                                <span class="visible-xs"><i class="ion-ios7-bookmarks"></i></span>
                                                <span class="hidden-xs">PlantaDocumento</span>
                                            </a>
                                        </li>


                                    </ul>

                                    <div class="tab-content">

                                        <!--PESTAÑA GENERAL-->
                                        <div class="tab-pane active" id="tabGeneral">
                                            <div class="row">
                                                <div class="form-group col-md-6">
                                                    <!--<label>Tipo *</label>-->
                                                    <label>Clase * </label>
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <select name="cboClasePersona" id="cboClasePersona"
                                                            class="select2 claseprueba"
                                                            onchange="mostrarMensajeError('ClasePersona'); modificarFormularioProveedorInternacional();mostrarLicenciaConducir();controlarVisibilidadDocumentos();"
                                                            multiple>
                                                        </select>
                                                        <i id='msjClasePersona' style='color:red;font-style: normal;'
                                                            hidden></i>
                                                    </div>
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <input type="hidden" id="txtTipo" name="txtTipo"
                                                            class="form-control" value="" readonly="true" />
                                                    </div>
                                                </div>

                                                <div class="form-group col-md-6 ">
                                                    <label id="lblCodigoIdentificacion">DNI *</label>
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <input type="text" id="txtCodigoIdentificacion"
                                                            name="txtCodigoIdentificacion" class="form-control" value=""
                                                            maxlength="15" />

                                                        <span class="input-group-btn">
                                                            <div id="contenedorBuscarRUC" hidden="true">
                                                                <button type="button"
                                                                    class="btn btn-effect-ripple btn-primary"
                                                                    onclick="buscarConsultaRUC()"><i
                                                                        class="fa fa-search"></i> Buscar</button>
                                                            </div>
                                                        </span>
                                                    </div>
                                                    <span id='msjCodigoIdentificacion' class="control-label"
                                                        style='color:red;font-style: normal;' hidden></span>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="form-group col-md-6" id="contenedorNombres" hidden="true">
                                                    <label id="lblNombre">Nombres *</label>
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <input type="text" id="txtNombre" name="txtNombre"
                                                            class="form-control" aria-required="true" value=""
                                                            maxlength="250" />
                                                    </div>
                                                    <span id='msjNombre' class="control-label"
                                                        style='color:red;font-style: normal;' hidden></span>
                                                </div>

                                                <div class="form-group col-md-6" id="contenedorApellidoPaterno"
                                                    hidden="true">
                                                    <label>Apellido Paterno * </label>
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <input type="text" id="txtApellidoPaterno"
                                                            name="txtApellidoPaterno" class="form-control"
                                                            aria-required="true" value="" maxlength="45" />
                                                    </div>
                                                    <span id='msjApellidoPaterno' class="control-label"
                                                        style='color:red;font-style: normal;' hidden></span>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="form-group col-md-6" id="contenedorApellidoMaterno"
                                                    hidden="true">
                                                    <label>Apellido Materno</label>
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <input type="text" id="txtApellidoMaterno"
                                                            name="txtApellidoMaterno" class="form-control"
                                                            aria-required="true" value="" maxlength="45" />
                                                    </div>
                                                    <span id='msjApellidoMaterno' class="control-label"
                                                        style='color:red;font-style: normal;' hidden></span>
                                                </div>

                                                <div class="form-group col-md-6" id="contenedorRazonSocial"
                                                    hidden="true">
                                                    <label>Razon social *</label>
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <input type="text" id="txtRazonSocial" name="txtRazonSocial"
                                                            class="form-control" aria-required="true" value=""
                                                            maxlength="200" />
                                                    </div>
                                                    <span id='msjRazonSocial' class="control-label"
                                                        style='color:red;font-style: normal;' hidden></span>
                                                </div>

                                                <div class="form-group col-md-6">
                                                    <label>Teléfono</label>
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <input type="text" id="txtTelefono" name="txtTelefono"
                                                            class="form-control" required="" aria-required="true"
                                                            value="" maxlength="45" />
                                                    </div>
                                                    <span id='msjTelefono' class="control-label"
                                                        style='color:red;font-style: normal;' hidden></span>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="form-group col-md-6">
                                                    <label>Celular</label>
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <input type="text" id="txtCelular" name="txtCelular"
                                                            class="form-control" aria-required="true" value=""
                                                            maxlength="45" />
                                                    </div>
                                                    <span id='msjCelular' class="control-label"
                                                        style='color:red;font-style: normal;' hidden></span>
                                                </div>

                                                <div class="form-group col-md-6">
                                                    <label>Email </label>
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <input type="email" id="txtEmail" name="txtEmail"
                                                            class="form-control" required="" aria-required="true"
                                                            value="" maxlength="100" />
                                                        <i id='msjEmail' style='color:red;font-style: normal;'
                                                            hidden></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row" id="contenedorLicenciaConducir" hidden="true">
                                                <div class="form-group col-md-6">
                                                    <label>N° Licencia conducir auto</label>
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <input type="text" id="txtLicenciaAuto" name="txtLicenciaAuto"
                                                            class="form-control" aria-required="true" value=""
                                                            maxlength="200" />
                                                    </div>
                                                </div>

                                                <div class="form-group col-md-6">
                                                    <label>N° Licencia conducir moto</label>
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <input type="text" id="txtLicenciaMoto" name="txtLicenciaMoto"
                                                            class="form-control" aria-required="true" value=""
                                                            maxlength="200" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="form-group col-md-6">
                                                    <label>Estado *</label>
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <select name="cboEstado" id="cboEstado" class="select2">
                                                            <option value="1" selected>Activo</option>
                                                            <option value="0">Inactivo</option>
                                                        </select>

                                                        </select>
                                                        <span id='msjEstado' class="control-label"
                                                            style='color:red;font-style: normal;' hidden></span>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label>Empresas * </label>
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <select name="cboEmpresa" id="cboEmpresa" class="select2"
                                                            onchange="mostrarMensajeError('Empresa');" multiple>
                                                        </select>
                                                        <i id='msjEmpresa' style='color:red;font-style: normal;'
                                                            hidden></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="form-group col-md-6">
                                                    <label>País Sunat</label>
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <select name="cboCodigoSunat" id="cboCodigoSunat"
                                                            class="select2" onchange="setearComboConvenioSunat()">
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label>Vinculación económica Sunat</label>
                                                    <div id="divCboCodigoSunat2"
                                                        class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <select name="cboCodigoSunat2" id="cboCodigoSunat2"
                                                            class="select2">
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="form-group col-md-6">
                                                    <label>Convenio para evitar la doble tributación Sunat</label>
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <select name="cboCodigoSunat3" id="cboCodigoSunat3"
                                                            class="select2" disabled="true">
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-6" id="contenedorNombreBCP">
                                                    <label>Nombre BCP</label>
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <input type="text" id="txtNombreBCP" name="txtNombreBCP"
                                                            class="form-control" aria-required="true" value=""
                                                            maxlength="200" />
                                                    </div>
                                                    <span id='msjNombreBCP' class="control-label"
                                                        style='color:red;font-style: normal;' hidden></span>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="form-group col-md-6">
                                                    <label>Número Cuenta</label>
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <input type="text" id="txtCuentaBCP" name="txtCuentaBCP"
                                                            class="form-control" required="" aria-required="true"
                                                            value="" maxlength="200" />
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label>CCI</label>
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <input type="text" id="txtCCI" name="txtCCI"
                                                            class="form-control" required="" aria-required="true"
                                                            value="" maxlength="45" />
                                                    </div>
                                                </div>
                                            </div>
                                            <br>
                                            <div class="row">
                                                <div class="form-group col-md-3">
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <div class="fileUpload btn w-lg m-b-5" id="multi"
                                                            style="border-radius: 0px;background-color: #337Ab7;color: #fff;cursor:default;">
                                                            <div id="edi"><i class="ion-upload m-r-15"
                                                                    style="font-size: 16px;"></i>Subir imagen</div>
                                                            <input name="file" id="file" type="file" accept="image/*"
                                                                class="upload"
                                                                onchange='$("#upload-file-info").html($(this).val().slice(12));'>
                                                        </div>
                                                        &nbsp; &nbsp; <b class='' id="upload-file-info">Ninguna imagen
                                                            seleccionada</b>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-3">
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
                                                <div id="divCentroCostoPersona" class="form-group col-md-6">
                                                    <div class="row">
                                                        <div class="col-md-12 col-sm-12 col-xs-12">
                                                            <label>Cuenta contable</label>
                                                            <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12"
                                                                id="divCuentaContable">
                                                                <select name="cboCuentaContable" id="cboCuentaContable"
                                                                    class="select2">
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <br>
                                                    <div class="row">
                                                        <div class="col-md-12 col-sm-12 col-xs-12">
                                                            <div class="table">
                                                                <div id="dataListCentroCostoPersona">
                                                                    <table id="dataTableCentroCostoPersona"
                                                                        class="table table-striped table-bordered"
                                                                        width="100%">
                                                                        <thead>
                                                                            <tr>
                                                                                <th style="text-align:center"
                                                                                    width="60%">Centro Costo <a
                                                                                        onclick="agregarCentroCostoPersona();">
                                                                                        <i class="fa fa-plus-square"
                                                                                            style="color:#E8BA2F;"
                                                                                            title="Agregar fila"></i>
                                                                                    </a></th>
                                                                                <th style="text-align:center"
                                                                                    width="30%">Porcentaje(%)</th>
                                                                                <th style="text-align:center"
                                                                                    width="10%">Acciones</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div style="clear:left">
                                                        <p><b>Leyenda:</b>&nbsp;&nbsp;
                                                            <i class="fa fa-trash-o" style="color:#cb2a2a;"></i>
                                                            Eliminar&nbsp;&nbsp;&nbsp;
                                                            <i class="fa fa-plus-square" style="color:#E8BA2F;"
                                                                title="Agregar fila"></i> Agregar fila
                                                        </p><br>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!--FIN PESTAÑA GENERAL-->

                                        <!--PESTAÑA CONTACTOS-->
                                        <div class="tab-pane" id="tabContactos">
                                            <input type="hidden" id="idContactoDetalle" value="" />
                                            <div class="row">
                                                <div class="form-group col-md-4">
                                                    <label>Contacto *</label>
                                                    <div class="input-group">
                                                        <select name="cboContacto" id="cboContacto" class="select2 ">
                                                        </select>
                                                        <span class="input-group-btn">
                                                            <button type="button"
                                                                class="btn btn-effect-ripple btn-primary"
                                                                onclick="nuevoContactoPersona()"><i
                                                                    class="ion-plus"></i></button>
                                                        </span>
                                                    </div>
                                                    <span id="msjContacto" class="control-label"
                                                        style="color:red;font-style: normal;" hidden></span>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label>Tipo contacto *</label>
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <div id="contenedorContactoTipoDivCombo">
                                                            <div class="input-group">
                                                                <select name="cboContactoTipo" id="cboContactoTipo"
                                                                    class="select2 ">
                                                                </select>
                                                                <span class="input-group-btn">
                                                                    <button type="button"
                                                                        class="btn btn-effect-ripple btn-primary"
                                                                        onclick="habilitarDivContactoTipoTexto()"><i
                                                                            class="ion-plus"></i></button>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div id="contenedorContactoTipoDivTexto" hidden="true">
                                                            <div class="input-group">
                                                                <input type="text" id="txtContactoTipo"
                                                                    name="txtContactoTipo" class="form-control" value=""
                                                                    maxlength="100" />
                                                                <span class="input-group-btn">
                                                                    <button type="button"
                                                                        class="btn btn-effect-ripple btn-primary"
                                                                        onclick="habilitarDivContactoTipoCombo()"><i
                                                                            class="ion-close-round"></i></button>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span id="msjContactoTipo" class="control-label"
                                                        style="color:red;font-style: normal;" hidden></span>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label>&nbsp;</label>
                                                    <div class="input-group col-md-12">
                                                        <button type="button" name="btnGuardar" id="btnGuardar"
                                                            class="btn btn-info w-sm m-b-5" style="border-radius: 0px;"
                                                            onclick="agregarContactoDetalle()"><i
                                                                class="fa fa-plus-square-o"></i>&nbsp;Agregar
                                                            contacto</button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="panel panel-body" id="muestrascroll">
                                                <span id="msjContactoDetalle" class="control-label"
                                                    style="color:red;font-style: normal;" hidden></span>
                                                <div class="row" id="scroll">
                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <div class="table">
                                                            <div id="dataList1">
                                                                <table id="dataTableContacto"
                                                                    class="table table-striped table-bordered">
                                                                    <thead>
                                                                        <tr>
                                                                            <th style="text-align:center">Contacto</th>
                                                                            <th style="text-align:center">Tipo contacto
                                                                            </th>
                                                                            <th style="text-align:center">Teléfono</th>
                                                                            <th style="text-align:center">E-mail</th>
                                                                            <th style="text-align:center">Acciones</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div style="clear:left">
                                                <p><b>Leyenda:</b>&nbsp;&nbsp;
                                                    <i class="fa fa-trash-o" style="color:#cb2a2a;"></i> Eliminar
                                                    contacto&nbsp;&nbsp;&nbsp;
                                                    <i class="fa fa-edit" style="color:#E8BA2F;"></i> Editar
                                                    contacto&nbsp;&nbsp;&nbsp;
                                                </p><br>
                                            </div>

                                        </div>
                                        <!--FIN PESTAÑA CONTACTOS-->

                                        <!--PESTAÑA DIRECCIONES-->
                                        <div class="tab-pane" id="tabDireccion">
                                            <input type="hidden" id="idDireccionDetalle" value="" />
                                            <div class="row">
                                                <!--<div class="row">-->
                                                <div class="form-group col-md-4">
                                                    <label>Tipo dirección *</label>
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <div id="contenedorDireccionTipoDivCombo">
                                                            <div class="input-group">
                                                                <select name="cboDireccionTipo" id="cboDireccionTipo"
                                                                    class="select2 ">
                                                                </select>
                                                                <span class="input-group-btn">
                                                                    <button type="button"
                                                                        class="btn btn-effect-ripple btn-primary"
                                                                        onclick="habilitarDivDireccionTipoTexto()"><i
                                                                            class="ion-plus"></i></button>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div id="contenedorDireccionTipoDivTexto" hidden="true">
                                                            <div class="input-group">
                                                                <input type="text" id="txtDireccionTipo"
                                                                    name="txtDireccionTipo" class="form-control"
                                                                    value="" maxlength="100" />
                                                                <span class="input-group-btn">
                                                                    <button type="button"
                                                                        class="btn btn-effect-ripple btn-primary"
                                                                        onclick="habilitarDivDireccionTipoCombo()"><i
                                                                            class="ion-close-round"></i></button>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <span id="msjDireccionTipo" class="control-label"
                                                            style="color:red;font-style: normal;" hidden></span>
                                                    </div>

                                                    <!--                                                </div>
                                                                                                        <div class="form-group col-md-4">-->
                                                    <label id="labelUbigeo">Ubigeo *</label>
                                                    <select name="cboUbigeo" id="cboUbigeo" class="select2 ">
                                                    </select>
                                                    <span id="msjUbigeo" class="control-label"
                                                        style="color:red;font-style: normal;" hidden></span>
                                                </div>
                                                <!--                                                </div>
                                                                                                <div class="row">-->
                                                <div class="form-group col-md-5">
                                                    <label>Direcci&oacute;n *</label>
                                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <textarea type="text" id="txtDireccion" name="txtDireccion"
                                                            class="form-control" value="" maxlength="500"></textarea>
                                                    </div>
                                                    <span id='msjDireccion' class="control-label"
                                                        style='color:red;font-style: normal;' hidden></span>
                                                </div>


                                                <div class="form-group col-md-3">
                                                    <label>&nbsp;</label>
                                                    <div class="input-group col-md-12">
                                                        <button type="button" name="btnGuardar" id="btnGuardar"
                                                            class="btn btn-info w-sm m-b-5" style="border-radius: 0px;"
                                                            onclick="agregarDireccionDetalle()"><i
                                                                class="fa fa-plus-square-o"></i>&nbsp;Agregar
                                                            dirección</button>
                                                    </div>
                                                </div>
                                                <!--</div>-->
                                            </div>

                                            <div class="panel panel-body" id="muestrascroll">
                                                <span id="msjDireccionDetalle" class="control-label"
                                                    style="color:red;font-style: normal;" hidden></span>
                                                <div class="row" id="scroll">
                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <div class="table">
                                                            <div id="dataList2">
                                                                <table id="dataTableDireccion"
                                                                    class="table table-striped table-bordered">
                                                                    <thead>
                                                                        <tr>
                                                                            <th style="text-align:center">Tipo dirección
                                                                            </th>
                                                                            <th style="text-align:center">Ubigeo</th>
                                                                            <th style="text-align:center">Dirección</th>
                                                                            <th style="text-align:center">Acciones</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div style="clear:left">
                                                <p><b>Leyenda:</b>&nbsp;&nbsp;
                                                    <i class="fa fa-trash-o" style="color:#cb2a2a;"></i> Eliminar
                                                    dirección&nbsp;&nbsp;&nbsp;
                                                    <i class="fa fa-edit" style="color:#E8BA2F;"></i> Editar
                                                    dirección&nbsp;&nbsp;&nbsp;
                                                </p><br>
                                            </div>

                                        </div>
                                        <!--FIN PESTAÑA DIRECCIONES-->

                                        <!--PESTAÑA DOCUMENTOS REINFO-->
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
                                        <!--FIN PESTAÑA DOCUMENTOS REINFO-->



                                        <!--PESTAÑA DOCUMENTOS PLANTA-->
                                        <div class="tab-pane" id="tabPlantaDocumento">
                                            <input type="hidden" id="idDocumentoDetalle" value="" />


                                            <div class="row">

                                                <div class="form-group col-md-6">
                                                    <label>Tipo Documento *</label>
                                                    <div class="input-group">
                                                        <select name="cboTipoArchivo2" id="cboTipoArchivo2"
                                                            class="select2 ">
                                                            <option value="">No seleccionado ---</option>
                                                        </select>
                                                        <span class="input-group-btn">
                                                            <button type="button"
                                                                class="btn btn-effect-ripple btn-primary"
                                                                onclick="MostrarGuardarDocumento();"><i
                                                                    class="ion-plus"></i></button>
                                                        </span>
                                                    </div>
                                                    
                                                </div>

                                                <div id="contenedorGuardarNuevoDocumento" class="form-group col-md-6">
                                                    <label>Nuevo Documento </label>
                                                    <div class="input-group">
                                                        <input type="text" id="txtNombreDocumento"
                                                            name="txtNombreDocumento" class="form-control" value=""
                                                            maxlength="100" />
                                                        <span class="input-group-btn">
                                                            <button type="button"
                                                                class="btn btn-effect-ripple btn-primary"
                                                                onclick="validarYGuardar();"><i
                                                                    class="fa fa-save"></i></button>
                                                        </span>
                                                    </div>
                                                    <span id="msjPlanta" class="control-label"
                                                        style="color:red;font-style: normal;" hidden></span>
                                                </div>




                                            </div>
                                            <br>
                                            <br>

                                            <div class="row">
                                                <div class="form-group col-md-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="checkSubida"
                                                            onchange="mostrarFormato();">
                                                        <label class="form-check-label" for="checkSubida">
                                                            Cuenta con un formato?
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="form-group col-md-6" id="uploadField"
                                                    style="display: none;">
                                                    <div class="form-group col-md-6">
                                                        <label>Formato *</label>
                                                        <div class="input-group">
                                                            <div class="custom-file">
                                                                <input name="file3" id="file3" type="file"
                                                                    accept="image/*, application/pdf"
                                                                    class="custom-file-input" />
                                                            </div>
                                                        </div>
                                                        
                                                    </div>


                                                    <div class="form-group col-md-5">
                                                        <img id="myImg3" src="vistas/com/persona/imagen/none.jpg"
                                                            onerror="this.src='vistas/com/persona/imagen/none.jpg'"
                                                            alt="" class="img-thumbnail profile-img thumb-lg" />
                                                        <input type="hidden" id="secretImg3" value="" />
                                                        <script>
                                                            $(function () {
                                                                $(":file").change(function () {
                                                                    if (this.files && this.files[0]) {
                                                                        var reader = new FileReader();
                                                                        reader.onload = imageIsLoaded3;
                                                                        reader.readAsDataURL(this.files[0]);
                                                                    }
                                                                });
                                                            });
                                                            function imageIsLoaded3(e) {
                                                                $('#secretImg3').attr('value', e.target.result);
                                                                $('#myImg3').attr('src', e.target.result);
                                                                $('#myImg3').attr('width', '128px');
                                                                $('#myImg3').attr('height', '128px');

                                                            }
                                                            ;
                                                        </script>
                                                    </div>




                                                </div>
                                            </div>


                                            <br>
                                            <br>
                                            <div class="row ">
                                                <div class="form-group col-md-5">
                                                    <button type="button" name="btnGuardar" id="btnGuardar"
                                                        class="btn btn-info w-sm m-b-5" style="border-radius: 0px;"
                                                        onclick="insertDocumentoPersonaPlanta()"><i
                                                            class="fa fa-upload"></i>&nbsp;Agregar
                                                        Tipo Documento</button>
                                                </div>
                                            </div>
                                            <br>
                                            <br>

                                            <table class="table table-bordered" id="archivoPlantaTable">
                                                <thead>
                                                    <tr>
                                                        <th>Tipo Documento</th>
                                                        <th>Estado</th>
                                                        <th>Nombre</th>
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
                                        <!--FIN PESTAÑA DOCUMENTOS PLANTA-->
                                    </div>
                                </div>




                                <div class="row alignRight">
                                    <div class="form-group col-md-12">
                                        <a href="#" class="btn btn-danger m-b-5" id="id" style="border-radius: 0px;"
                                            onclick="cargarListarPersonaCancelar()"><i
                                                class="fa fa-close"></i>&ensp;Cancelar</a>&nbsp;&nbsp;&nbsp;
                                        <!--                                        <button type="button" onclick="guardarPersona()" value="enviar" name="env" id="env" class="btn btn-info w-sm m-b-5" style="border-radius: 0px;"><i class="fa fa-send-o"></i>&ensp;Enviar</button>&nbsp;&nbsp;-->
                                        <button type="button" onclick="validarSimilitud()" value="enviar" name="env"
                                            id="env" class="btn btn-info w-sm m-b-5" style="border-radius: 0px;"><i
                                                class="fa fa-send-o"></i>&ensp;Enviar</button>&nbsp;&nbsp;
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
    <script src="vistas/com/persona/persona_form.js"></script>

</body>