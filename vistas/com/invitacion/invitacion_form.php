<body>
    <div class="row">
        <div class="col-md-12 ">
            <div class="panel panel-default">
                <div class="panel-body">
                    <h3><b>Invitación a REINFO</b></h3>
                    <div class="col-md-12 ">
                        <div class="panel-body">
                            <div class="row">
                                <div class="form-group col-md-2">
                                    <style>
                                        .linea-separadora {
                                            border: none;
                                            height: 1px;
                                            background-color: #000;
                                            /* Cambia el color de la línea aquí */
                                            margin: 10px 0;
                                            /* Ajusta el espacio alrededor de la línea */
                                        }
                                    </style>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>RUC *</label>
                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <input type="text" id="txtReinfo" name="txtReinfo" class="form-control" aria-required="true" value="" maxlength="250" />
                                    </div>
                                    <span id='msjFechaEntrega' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Código derecho *</label>
                                    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <input type="text" id="txtCodigo" name="txtCodigo" class="form-control" aria-required="true" value="" maxlength="250" />
                                    </div>
                                    <span id='msjFechaEntrega' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                </div>
                                <div class="form-group col-md-3">
                                    <br>
                                    <button type="button" onclick="setearInputREINFO()" value="buscar" name="env" id="buscar" class="btn btn-info w-sm m-b-5" style="border-radius: 0px;"><i class="fa fa-search"></i>&ensp;Buscar</button>&nbsp;&nbsp;
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
                                                <label>Código único</label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <input type="text" id="txtCodigoUnico" name="txtCodigoUnico" class="form-control" aria-required="true" value="" maxlength="250" />
                                                </div>
                                                <span id='msjFechaEntrega' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                            </div>

                                            <div class="form-group col-md-4">
                                                <label>Minero</label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <input type="text" id="txtNombre" name="txtNombre" class="form-control" aria-required="true" value="" maxlength="250" />
                                                </div>
                                                <span id='msjFechaEntrega' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                            </div>

                                            <div class="form-group col-md-4">
                                                <label>Concesión minera</label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <input type="text" id="txtSector" name="txtSector" class="form-control" aria-required="true" value="" maxlength="250" />
                                                </div>
                                                <span id='msjFechaEntrega' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                            </div>
                                        </div>

                                        <div class="row">

                                            <div class="form-group col-md-2">
                                                <label>Estado</label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <input type="text" id="txtEstado" name="txtEstado" class="form-control" aria-required="true" value="" maxlength="250" />
                                                </div>
                                                <span id='msjFechaEntrega' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                            </div>



                                            <div class="form-group col-md-2">
                                                <label>Departamento</label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <input type="text" id="txtDepartamento" name="txtDepartamento" class="form-control" aria-required="true" value="" maxlength="250" />
                                                </div>
                                                <span id='msjFechaEntrega' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label>Provincia</label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <input type="text" id="txtProvincia" name="txtProvincia" class="form-control" aria-required="true" value="" maxlength="250" />
                                                </div>
                                                <span id='msjFechaEntrega' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label>Distrito</label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <input type="text" id="txtDistrito" name="txtDistrito" class="form-control" aria-required="true" value="" maxlength="250" />
                                                </div>
                                                <span id='msjFechaEntrega' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label id="labelUbigeo">Ubigeo *</label>
                                                <select name="cboUbigeo" id="cboUbigeo" class="select2 ">
                                                </select>
                                                <span id="msjUbigeo" class="control-label"
                                                    style="color:red;font-style: normal;" hidden></span>
                                            </div>
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
                                                <label>Zona *</label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <select name="cboTipoArchivo" id="cboTipoArchivo"
                                                        class="select2">

                                                    </select>

                                                </div>
                                                <span id="msjTipoArchivo" class="control-label"
                                                    style="color:red;font-style: normal;" hidden></span>
                                            </div>

                                            <div class="form-group col-md-3">
                                                <label>Sector *</label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <input type="text" id="txtOrganizacion" name="txtOrganizacion" class="form-control" aria-required="true" readonly value="Comunidad Campesina La Victoria" maxlength="250" />
                                                </div>
                                                <span id='msjCorreo' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                            </div>
                                        </div>
                                        <!-- #region -->
                                        <div class="row">
                                            <br>
                                        <div class="form-group col-md-3">
                                        <div id="imagenesDNI2" style="display: none; margin-top: 10px;">
                                        <img id="imgFoto" src="" alt="Imagen Foto" style="width: 30%; border: 1px solid #ccc;" />
                                        <textarea id="base64Foto" rows="4" cols="50" style="display: block;" readonly></textarea></div>
    </div>
    <div class="form-group col-md-3">
        <label>DNI RESPONSABLE LEGAL *</label>
        <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <input type="text" id="txtDNI" name="txtDNI" class="form-control" aria-required="true" value="" maxlength="250" />
        </div>
        <span id="msjtxtDNI" class="control-label" style="color:red;font-style: normal;" hidden></span>
    </div>
    <div class="form-group col-md-3">
        <label id="labelUbigeo">Tipo de Consulta *</label>
        <select name="cboTipoDNI" id="cboTipoDNI" class="select2">
            <option value="" selected disabled>Seleccionar tipo de consulta</option>
            <option value="niveln">DNI VIRTUAL ELECTRONICO</option>
            <option value="nivam">DNI VIRTUAL AMARILLO</option>
            <option value="nivaz">DNI VIRTUAL AZUL</option>
        </select>
        <span id="msjcboTipoDNI" class="control-label" style="color:red;font-style: normal;" hidden></span>
    </div>
    <div class="form-group col-md-3">
        <br>
        <button type="button" onclick="buscarDNI()" value="buscar" name="env" id="buscar" class="btn btn-info w-sm m-b-5" style="border-radius: 0px;">
            <i class="fa fa-search"></i>&ensp;Buscar
        </button>&nbsp;&nbsp;
    </div>
    <hr class="linea-separadora">
</div>

<!-- Sección para mostrar las imágenes cuando se reciban -->
<div id="imagenesDNI" style="display: none; margin-top: 20px;">
    <div class="row">
        <div class="col-md-6">
            <h4>Anverso:</h4>
            <img id="imgAdverso" src="" alt="Imagen Adverso" style="width: 100%; border: 1px solid #ccc;" />
            <textarea id="base64Adverso" rows="4" cols="50" style="display: block;" readonly></textarea>
            <textarea id="base64Reverso" rows="4" cols="50" style="display: block;" readonly></textarea>
        </div>
        <div class="col-md-6">
            <h4>Reverso:</h4>
            <img id="imgReverso" src="" alt="Imagen Reverso" style="width: 100%; border: 1px solid #ccc;" />
        </div>
    </div>
</div>
<div class="row">

                                            <div class="form-group col-md-3">
                                                <label>Código Secreto *</label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <input type="text" id="txtCodigoS" name="txtCodigoS" class="form-control" aria-required="true" value="" maxlength="250" />
                                                </div>
                                                <span id='msjTelefono' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                            </div>



                                            <div class="form-group col-md-3">
                                                <label>Nombre *</label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <input type="text" id="txtResponsableNombre" name="txtResponsableNombre" class="form-control" aria-required="true" value="" maxlength="250" />
                                                </div>
                                                <span id='msjCorreo' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                            </div>

                                            <div class="form-group col-md-3">
                                                <label>Lugar Nacimiento *</label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <input type="text" id="txtLugarN" name="txtLugarN" class="form-control" aria-required="true" value="" maxlength="250" />
                                                </div>
                                                <span id='msjCorreo' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                            </div>

                                            <div class="form-group col-md-3">
                                                <label>Fecha Nacimiento *</label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <input type="text" id="txtfechaN" name="txtfechaN" class="form-control" aria-required="true" value="" maxlength="250" />
                                                </div>
                                                <span id='msjCorreo' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                            </div>


                                        </div>

                                        <div class="row">

<div class="form-group col-md-3">
    <label>Direccion *</label>
    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <input type="text" id="txtDireccionA" name="txtDireccionA" class="form-control" aria-required="true" value="" maxlength="250" />
    </div>
    <span id='msjTelefono' class="control-label" style='color:red;font-style: normal;' hidden></span>
</div>



<div class="form-group col-md-3">
    <label>Estado civil *</label>
    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <input type="text" id="txtEstadoC" name="txtEstadoC" class="form-control" aria-required="true" value="" maxlength="250" />
    </div>
    <span id='msjCorreo' class="control-label" style='color:red;font-style: normal;' hidden></span>
</div>

<div class="form-group col-md-3">
    <label>Hijos *</label>
    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <input type="text" id="txtHijos" name="txtHijos" class="form-control" aria-required="true" value="" maxlength="250" />
    </div>
    <span id='msjCorreo' class="control-label" style='color:red;font-style: normal;' hidden></span>
</div>

<div class="form-group col-md-3">
    <label>Estatura *</label>
    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <input type="text" id="txtEstatura" name="txtEstatura" class="form-control" aria-required="true" value="" maxlength="250" />
    </div>
    <span id='msjCorreo' class="control-label" style='color:red;font-style: normal;' hidden></span>
</div>


</div>

<div class="row">

<div class="form-group col-md-3">
    <label>Madre *</label>
    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <input type="text" id="txtMadre" name="txtMadre" class="form-control" aria-required="true" value="" maxlength="250" />
    </div>
    <span id='msjTelefono' class="control-label" style='color:red;font-style: normal;' hidden></span>
</div>



<div class="form-group col-md-3">
    <label>Padre *</label>
    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <input type="text" id="txtPadre" name="txtPadre" class="form-control" aria-required="true" value="" maxlength="250" />
    </div>
    <span id='msjCorreo' class="control-label" style='color:red;font-style: normal;' hidden></span>
</div>

<div class="form-group col-md-3">
    <label>Restriccion *</label>
    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <input type="text" id="txtRestriccion" name="txtRestriccion" class="form-control" aria-required="true" value="" maxlength="250" />
    </div>
    <span id='msjCorreo' class="control-label" style='color:red;font-style: normal;' hidden></span>
</div>

<div class="form-group col-md-3">
    <label>Genero *</label>
    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <input type="text" id="txtSexo" name="txtSexo" class="form-control" aria-required="true" value="" maxlength="250" />
    </div>
    <span id='msjCorreo' class="control-label" style='color:red;font-style: normal;' hidden></span>
</div>


</div>
                                        <div class="row">

                                            <div class="form-group col-md-3">
                                                <label>Telefono *</label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <input type="text" id="txtTelefono" name="txtTelefono" class="form-control" aria-required="true" value="" maxlength="250" />
                                                </div>
                                                <span id='msjTelefono' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                            </div>



                                            <div class="form-group col-md-3">
                                                <label>Correo *</label>
                                                <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <input type="text" id="txtCorreo" name="txtCorreo" class="form-control" aria-required="true" value="" maxlength="250" />
                                                </div>
                                                <span id='msjCorreo' class="control-label" style='color:red;font-style: normal;' hidden></span>
                                            </div>


                                        </div>

                                        <!DOCTYPE html>
                                        <html lang="en">

                                        <head>
                                            <meta charset="UTF-8">
                                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                                            <title>Firma Digital</title>
                                            <style>
                                                .signature-container {
                                                    text-align: center;
                                                    margin-top: 30px;
                                                }

                                                canvas {
                                                    border: 1px solid #000;
                                                    cursor: crosshair;
                                                }

                                                .buttons {
                                                    margin-top: 20px;
                                                }
                                            </style>
                                        </head>

                                        <body>

                                            <div class="signature-container">
                                                <h2>Por favor, firme aquí:</h2>

                                                <!-- Canvas para la firma -->
                                                <div class="col-md-12">
                                                    <canvas id="signatureCanvas"></canvas>
                                                </div>

                                                <div class="buttons">
                                                    <!-- Botones para limpiar y guardar -->
                                                    <button type="button" id="saveSignatureButton" class="btn btn-info" onclick="clearSignature()">Limpiar</button>
                                                    <button type="button" id="saveSignatureButton" class="btn btn-info" onclick="saveSignature()">Guardar Firma</button>
                                                </div>

                                                <div>

                                                    <textarea id="signatureData" rows="4" cols="50" hidden></textarea>
                                                </div>
                                            </div>

                                            <script>
                                                // Obtener el canvas y el contexto
                                                const canvas = document.getElementById('signatureCanvas');
                                                const ctx = canvas.getContext('2d');

                                                let drawing = false;

                                                // Función para iniciar el dibujo
                                                function startDrawing(e) {
                                                    drawing = true;
                                                    draw(e);
                                                }

                                                // Función para dejar de dibujar
                                                function stopDrawing() {
                                                    drawing = false;
                                                    ctx.beginPath();
                                                }

                                                // Función para dibujar en el canvas
                                                function draw(e) {
                                                    if (!drawing) return;

                                                    // Asegurarse de que las coordenadas son correctas según el tamaño del canvas
                                                    const rect = canvas.getBoundingClientRect();
                                                    const x = e.clientX - rect.left;
                                                    const y = e.clientY - rect.top;

                                                    ctx.lineWidth = 3; // grosor de la línea
                                                    ctx.lineCap = 'round'; // para hacer la firma más suave
                                                    ctx.strokeStyle = '#000'; // color de la firma

                                                    ctx.lineTo(x, y);
                                                    ctx.stroke();
                                                    ctx.beginPath();
                                                    ctx.moveTo(x, y);
                                                }

                                                // Función para limpiar el canvas
                                                function clearSignature() {
                                                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                                                    document.getElementById('signatureData').value = ''; // Limpiar textarea
                                                    enableCanvas();
                                                }

                                                // Función para guardar la firma (en formato base64)
                                                function saveSignature() {
                                                    const signatureData = canvas.toDataURL(); // Obtiene la firma en formato base64
                                                    document.getElementById('signatureData').value = signatureData;
                                                    disableCanvas();
                                                }

                                                // Habilitar los eventos de dibujo (cuando se limpia el canvas)
                                                function enableCanvas() {
                                                    canvas.classList.remove('locked'); // Quitar el estilo de "bloqueado"
                                                    canvas.addEventListener('mousedown', startDrawing);
                                                    canvas.addEventListener('mouseup', stopDrawing);
                                                    canvas.addEventListener('mousemove', draw);

                                                    // Para dispositivos táctiles
                                                    canvas.addEventListener('touchstart', (e) => {
                                                        e.preventDefault();
                                                        startDrawing(e.changedTouches[0]);
                                                    });
                                                    canvas.addEventListener('touchend', stopDrawing);
                                                    canvas.addEventListener('touchmove', (e) => {
                                                        e.preventDefault();
                                                        draw(e.changedTouches[0]);
                                                    });
                                                }

                                                // Deshabilitar los eventos de dibujo (bloquear la firma)
                                                function disableCanvas() {
                                                    canvas.classList.add('locked'); // Añadir estilo de "bloqueado"
                                                    canvas.removeEventListener('mousedown', startDrawing);
                                                    canvas.removeEventListener('mouseup', stopDrawing);
                                                    canvas.removeEventListener('mousemove', draw);

                                                    // Para dispositivos táctiles
                                                    canvas.removeEventListener('touchstart', (e) => {
                                                        e.preventDefault();
                                                        startDrawing(e.changedTouches[0]);
                                                    });
                                                    canvas.removeEventListener('touchend', stopDrawing);
                                                    canvas.removeEventListener('touchmove', (e) => {
                                                        e.preventDefault();
                                                        draw(e.changedTouches[0]);
                                                    });
                                                }



                                                // Agregar eventos de mouse y touch para dibujar en el canvas
                                                canvas.addEventListener('mousedown', startDrawing);
                                                canvas.addEventListener('mouseup', stopDrawing);
                                                canvas.addEventListener('mousemove', draw);

                                                // Soporte para pantallas táctiles
                                                canvas.addEventListener('touchstart', (e) => {
                                                    e.preventDefault();
                                                    startDrawing(e.changedTouches[0]);
                                                });
                                                canvas.addEventListener('touchend', stopDrawing);
                                                canvas.addEventListener('touchmove', (e) => {
                                                    e.preventDefault();
                                                    draw(e.changedTouches[0]);
                                                });
                                            </script>

                                        </body>

                                        </html>

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
    <div id="overlay" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 9999; text-align: center;">
    <!-- Spinner y cuenta regresiva -->
    <div id="loader" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white;">
        <div class="spinner-border" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Cargando...</span>
        </div>
        <div id="contador" style="font-size: 20px; margin-top: 20px;">Esperando...</div>
    </div>
</div>

<style>
    /* Agregar estilo para bloquear la pantalla y mostrar el spinner */
#overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5); /* Fondo oscuro */
    z-index: 9999; /* Hacer que el overlay esté encima de todo */
    display: none; /* Ocultar por defecto */
    justify-content: center;
    align-items: center;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

#contador {
    margin-top: 20px;
    font-size: 20px;
    font-weight: bold;
}

</style>

    <script src="vistas/libs/imagina/assets/select2/select2.min.js"></script>
    <script src="vistas/com/invitacion/invitacion_form.js"></script>

</body>