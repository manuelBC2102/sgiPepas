<style type="text/css">
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .columnAlignCenter {
        text-align: center;
    }
</style>
<!DOCTYPE html>
<html lang="es">

<body>
    <div class="row">
        <input type="hidden" id="tipoInterfaz" value="<?php echo $_GET['tipoInterfaz']; ?>" />
        <input type="hidden" id="hddIsDependiente" value="1">
        <input type="hidden" id="almacenId" value="<?php echo $_GET['almacenId']; ?>" />
        <div class="col-lg-12">
            <div class="portlet">
                <div class="portlet-heading">
                    <div class="row">
                        <div class="col-md-10" style="margin-top: -12px; margin-left: -32px;">
                            <div class="col-md-8">
                                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12" style="margin-top: -10px;">
                                    <h3 class="text-dark text-uppercase">
                                        <select name="cboDocumentoTipo" id="cboDocumentoTipo" class="select2"></select>
                                    </h3>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
                                    <div id="contenedorSerieDiv" hidden="true">
                                        <h4 id="contenedorSerie"></h4>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
                                    <div id="contenedorNumeroDiv" hidden="true">
                                        <h4 id="contenedorNumero"></h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div id="divContenedorOrganizador" class="col-lg-12 col-md-12 col-sm-6 col-xs-6" hidden="true">
                                    <h4>
                                        <select id="cboOrganizador" name="cboOrganizador" class="select2" disabled>
                                        </select>
                                    </h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2" style="margin-left: 32px;">
                            <div class="col-lg-10 col-md-10 col-sm-6 col-xs-6" style="margin-top: -12px;">
                                <h4>
                                    <select id="cboPeriodo" name="cboPeriodo" class="select2" onchange="onChangePeriodo()" style="width: 100%" disabled>
                                    </select>
                                </h4>
                            </div>
                        </div>
                        <label class='' id="nombreArchivo" style="color: black" hidden="true"></label>
                    </div>
                </div>
                <div class="modal-footer" style="margin-top: -10px;"></div>
                <div id="portlet1" class="panel-collapse collapse in" style="margin-top: -20px;">
                    <div class="portlet-body">
                        <!--PARTE DINAMICA-->
                        <div id="contenedorDocumentoTipo">
                            <form id="formularioDocumentoTipo" method="post" class="form" enctype="multipart/form-data;charset=UTF-8">
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <a href="#" class="btn btn-danger m-b-5" id="id" style="border-radius: 0px;" onclick="cargarDiv('#window', 'vistas/com/unidad/unidad_listar.php')">
                                            <i class="fa fa-close"></i>&ensp;Cancelar</a>&nbsp;&nbsp;&nbsp;
                                        <button type="button" onclick="save('<?php echo $tipo; ?>')" value="enviar" name="env" id="env" class="btn btn-info w-sm m-b-5" style="border-radius: 0px;">
                                            <i class="fa fa-send-o"></i>&ensp;Enviar</button>&nbsp;&nbsp;
                                    </div>
                                </div>
                            </form>
                        </div>
                        <!--FIN PARTE DINAMICA-->
                        <div id="divDocumentoRelacion" style="min-height: 0px;height: auto;" hidden="true">
                            <div id="contenedorLinkDocumentoACopiar" class="form-group">
                                <div class="col-md-12" style="text-align: left;">
                                    <div id="divChkDocumentoRelacion">
                                        <label class="cr-styled" style="text-align: left;">
                                            <input type="checkbox" id="chkDocumentoRelacion" checked>
                                            <i class="fa"></i>
                                            Relacionar documento
                                            <br>
                                        </label>
                                    </div>
                                    <div id="linkDocumentoACopiar" style="min-height: 0px;height: auto;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="contenedorDetalle" style="min-height: 300px;height: auto;">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="form-group">
                                        <div class="col-md-2"></div>
                                        <div class="col-md-4">
                                        </div>
                                        <div class="col-md-2"></div>
                                        <div class="col-md-4">
                                        </div>
                                    </div>
                                </div>
                                <br>
                                <!--Incluir tab-->
                                <div id="tabDistribucion">
                                    <ul id="tabsDistribucionMostrar" class="nav nav-tabs nav-justified">
                                        <li class="active">
                                            <a href="#detalle" data-toggle="tab" aria-expanded="true" id="tabDetalle" title="Detalle">
                                                <span class="hidden-xs">Ingreso del detalle</span>
                                            </a>
                                        </li>
                                    </ul>
                                    <div id="div_contenido_tab" class="tab-content">
                                        <div class="tab-pane active" id="detalle">
                                            <div class="row" style="height: auto;">
                                                <table id="datatable" class="table table-striped table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th style='text-align:center;' id='th_Nro'>#</th> 
                                                            <th style='text-align:center;'>Producto</th>
                                                            <th style='text-align:center;'>Cantidad</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="dgDetalle">
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <div style="height: auto; float: right; margin-top: 0px;" id="divAgregarFila">
                                                        <a onclick="agregarFila();">
                                                            <i class="fa fa-plus-square" style="color:#E8BA2F;" title="Agregar item"></i>
                                                        </a>
                                                    </div>
                                                    <div style="height: auto; float: right; margin-top: 0px;" id="divTodasFilas">
                                                        <a href="#verMasFilas" onclick="verTodasFilas()">
                                                            <b style="color: #797979">[<i class="ion-chevron-down"></i>&nbsp; Ver todas las filas]&nbsp;&nbsp;</b>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                                    <div class="widget-panel widget-style-1 bg-info" style="padding: 1px 60px 1px 1px;color: black;">
                                        <i class="fa fa-comments-o"></i>
                                        <div>
                                            <textarea type="text" id="txtComentario" name="txtComentario" value="" maxlength="500" rows="2" placeholder="Comentario" style="height: auto;width: 100%;display: block;padding: 6px 12px;transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                                    <div style="float: right;padding-top: 25px;" id="divAccionesEnvio"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Portlet -->
        </div>
        <!-- end col -->
    </div>
    <!-- End row -->


    <div id="datosImpresion" hidden="true"></div>
    <script src="vistas/com/almacenes/despacho_lima_form_tablas.js"></script>
</body>

</html>