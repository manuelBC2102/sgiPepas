<?php
require_once __DIR__ . '/../../../util/Configuraciones.php';
?>
<div class="panel-group panel-group-joined" id="accordion-test">
    <!--<div class="panel panel-default">-->
    <div class="panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <!--<a data-toggle="collapse" data-parent="#accordion-test" href="#collapseOne">-->
                I. Datos Generales
                <!--</a>-->
                <input type="hidden" id="secretPersona" value="" />
                <input type="hidden" id="secretInvitacion" value="" />
                <input type="hidden" id="secretNivel" value="" />
            </h4>
        </div>
        <div id="collapseOne" class="panel-collapse collapse in">
            <div class="panel-body">


                <div class="row" style="margin-left: -2px; padding-top: 25px;padding-bottom: 20px;">
                    <div class="form-group">
                        <label class="col-md-2 control-label" style="text-align: left"><span id="spRUC">RUC</span> <span id="spRuc"></span></label>
                        <div class="col-md-10">
                            <input  id="txtCodigoIdentificacion" name="txtCodigoIdentificacion" class="form-control col-md-4 col-lg-4 col-sm-4 col-xs-12" value="" style="width: 35%" type="text" onKeyPress="return soloNumeros(event);" maxlength="11" disabled="">
                            <!-- <button id="btnBuscar" type="button" class="btn btn-effect-ripple btn-primary" onclick="buscarConsultaRUC()"><i class="fa fa-search"></i> Validar SUNAT</button> -->
                            <div>
                                <span id="msjCodigoIdentificacion" class="control-label" style="color: red; font-style: normal; display: none;" hidden=""></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" hidden="" id="idNombreCompleto" style="margin-left: -1px;">
                    <div class="form-group col-md-6">
                        <label>Nombre Completo</label>
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <input  disabled="" id="txtNombreCompleto" name="txtNombreCompleto" class="form-control" aria-required="true" value="" maxlength="200" type="text" disabled="">
                        </div>
                    </div>
                </div>

                <div class="row" style="margin-left: 8px;">
                    <table id="idJuridica" class="table table-striped table-bordered dataTable no-footer" style="width: 100%">
                        <tbody>
                            <tr>
                                <td id="tipoPersona" colspan="4" class="fondoHeaderTable">
                                    Persona Jurídica
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 12%; vertical-align: middle">
                                    <label>Razón social <span id="spRazonSocial"><span style="color: red; font-weight: bold;">*</span></span></label>
                                </td>
                                <td colspan="3" style="width: 88%">
                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <input disabled="" id="txtRazonSocial" name="txtRazonSocial" class="form-control" aria-required="true" value="" type="text">
                                    </div>
                                    <span id="msjRazonSocial" class="control-label" style="color:red;font-style: normal;" hidden=""></span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                </div>

                <br>
                <div class="row" style="margin-left: 8px; overflow-x: auto;">
                    <table class="table table-striped table-bordered dataTable no-footer" style="width: 100%">
                        <tbody>
                           <tr>
                                <td style="vertical-align: middle; width: 17%">
                                    <label>Código derecho <span id="spNombreCom"></span></label>
                                </td>
                                <td colspan="5">
                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <input disabled="" id="txtCodigo" name="txtCodigo" class="form-control" aria-required="true" value="" type="text">
                                    </div>
                                    <span id="msjNombreCom" class="control-label" style="color:red;font-style: normal;" hidden=""></span>
                                </td>
                            </tr>
                            <tr>
                                <td style="vertical-align: middle; width: 17%">
                                    <label>Nombre derecho <span id="spNombreCom"></span></label>
                                </td>
                                <td colspan="5">
                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <input disabled="" id="txtSector" name="txtSector" class="form-control" aria-required="true" value="" type="text">
                                    </div>
                                    <span id="msjNombreCom" class="control-label" style="color:red;font-style: normal;" hidden=""></span>
                                </td>
                            </tr>

                            <tr>
                                <td style="vertical-align: middle; width: 17%">
                                    <label>Ubicación <span id="spNombreCom"></span></label>
                                </td>
                                <td colspan="5">
                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <input disabled="" id="txtUbicacion" name="txtUbicacion" class="form-control" aria-required="true" value="" type="text">
                                    </div>
                                    <span id="msjNombreCom" class="control-label" style="color:red;font-style: normal;" hidden=""></span>
                                </td>
                            </tr>
                           
                            <tr>
                                <td style="vertical-align: middle;">
                                    <label>Teléfono  <span id="spTelef1"></span></label>
                                </td>
                                <td id="tdTelefono1" style="width: 18%" colspan="1">
                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <input disabled="" id="txtTelef1" name="txtTelef1" class="form-control" aria-required="true" maxlength="15" value="" type="text" onKeyPress="return soloNumeros(event);">
                                    </div>
                                    <span id="msjTelef1" class="control-label" style="color:red;font-style: normal;" hidden=""></span>
                                </td>
                                <td style="vertical-align: middle; width: 10%;">
                                    <label>Correo </label>
                                </td>
                                <td colspan="1" style="width: 18%">
                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <input disabled="" id="txtCorreo" name="txtCorreo" class="form-control" aria-required="true" maxlength="200" value="" type="text" onKeyPress="return soloNumeros(event);">
                                    </div>
                                    <span id="msjTelef2" class="control-label" style="color:red;font-style: normal;" hidden=""></span>
                                </td>
                                <!-- <td style="vertical-align: middle; width: 10%;" class="tdFormaFacturacion">
                                    <label>Forma de facturación <span style="color: red; font-weight: bold;">*</span></label>
                                </td>
                                <td colspan="1" style="width: 15%; vertical-align: middle;" class="tdFormaFacturacion">
                                    <label class="cr-styled" for="rbtFormaFacturacionFactura">Factura &nbsp;
                                        <input type="radio" name="rbtFormaFacturacionSiNo" id="rbtFormaFacturacionFactura" value="1"><i class="fa"></i>
                                    </label>&nbsp;&nbsp;
                                    <label class="cr-styled" for="rbtFormaFacturacionRH">Recibo por honoarios &nbsp;
                                        <input type="radio" name="rbtFormaFacturacionSiNo" id="rbtFormaFacturacionRH" value="3"><i class="fa"></i>
                                    </label>
                                    <div>
                                        <span id="msjFormaFacturacion" class="control-label" style="color:red;font-style: normal;" hidden></span>
                                    </div>
                                </td> -->
                            </tr>

                  

                        </tbody>
                    </table>
                </div>


                <br>
            </div>
        </div>
    </div>
<div id="administracion">
    <div class="row" style="margin-left: 8px;">
       
       <span id="spDocumentosAdjuntos"></span>
       <div class="panel-heading">
           <h4 class="panel-title">
               <!--<a data-toggle="collapse" data-parent="#accordion-test" href="#collapseOne">-->
               II. Documentación Administración
               <!--</a>-->
               <input type="hidden" id="secretPersona" value="" />
               <input type="hidden" id="secretInvitacion" value="" />
           </h4>
       </div>
   </div>


   <br>
   <!-- <button type="button" id="btnAgregarDocumento" name="btnAgregarDocumento" class="btn btn-info w-sm m-b-5" onclick="nuevoDocumento()" style="border-radius: 0px; margin-left: 8px">
       <i class="fa fa-plus"></i>&ensp;Agregar documento
   </button> -->

   <div id="dataList2" >

   </div>
   </div>
   <br><br>
   <div id="planta">
    <div class="row" style="margin-left: 8px;">
       
        <span id="spDocumentosAdjuntos"></span>
        <div class="panel-heading">
            <h4 class="panel-title">
                <!--<a data-toggle="collapse" data-parent="#accordion-test" href="#collapseOne">-->
                III. Documentación Planta
                <!--</a>-->
                <input type="hidden" id="secretPersona" value="" />
                <input type="hidden" id="secretInvitacion" value="" />
            </h4>
        </div>
    </div>
    <div class="form-group col-md-12">
                            <label>Plantas *</label>
                            <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <select name="cboPlantas" style="width: 100%;"  id="cboPlantas" class="select2" placeholder="Seleccione tipo de documento" onchange="onChangeDocumentoTipoAdjunto(this.value);">
                                </select>
                            </div>
                            <span id="msjTipoDocumento" class="control-label" style="color:red;font-style: normal;" hidden=""></span>
                        </div>
    <br>
    <!-- <button type="button" id="btnAgregarDocumento" name="btnAgregarDocumento" class="btn btn-info w-sm m-b-5" onclick="nuevoDocumento()" style="border-radius: 0px; margin-left: 8px">
        <i class="fa fa-plus"></i>&ensp;Agregar documento
    </button> -->

    <div id="dataList" >

    </div>
    </div>
    <br><br>
    
    <div class="row" style="margin-left: 8px;">
       
        <span id="spDocumentosAdjuntos"></span>
        <div class="panel-heading">
            <h4 class="panel-title">
                <!--<a data-toggle="collapse" data-parent="#accordion-test" href="#collapseOne">-->
                IV. PUNTOS IGAFOM
                <!--</a>-->
              
            </h4>
        </div>
    </div>
    <br>
    <div id="dataList3" >

</div>
    <br><br>
    <div class="row" style="margin-left: 8px">
        <center>
            <div class="row">
                <div class="col-md-1"></div>
                <div class="form-group col-lg-3 col-md-3 col-sm-3" style="padding-top: 68px;">
                    <!-- <span id="PiePagNombreSolicitante"></span>
                    <hr style="border-color: #244061;">
                    <label id="txtnombresolicitante">Nombre de persona solicitante</label> -->
                </div>

                <div class="form-group col-lg-4 col-md-4 col-sm-4">
                    <span id="PiePagFirma">
                        <img src='<?php echo Configuraciones::url_base(); ?>vistas/images/nofirma.jpg' class='img-responsive' style="width: 170px; height: 68px">
                    </span>
                    <hr style="border-color: #244061;">
                    <label id="txtnombresolicitante">Nombre de persona solicitante</label>&nbsp;
                    <!-- <span class="fileUpload btn btn-purple btn-xs m-b-5">
                        <span><i class="ion-upload m-r-5"></i>Adjuntar</span>
                        <input type="file" id="fileFirma" name="fileFirma" class="upload" accept="image/jpg, image/jpeg, image/png" />
                    </span> -->
                    <input type="hidden" id="firmaAnterior" value="" />
                    <input type="hidden" id="secretFileFirma" value="" />
                    <input type="hidden" id="secretNameFirma" value="" />
                    <div>
                        <span id="msjfileFirma" class="control-label" style="color:red;font-style: normal;" hidden=""></span>
                    </div>
                </div>

                <!-- <div class="form-group col-lg-2 col-md-2 col-sm-2" style="padding-top: 34px;">
                    <div class="form-group">
                        <div class="input-group date col-lg-12 col-md-12 col-sm-12 col-xs-12" id="dpFecha">
                            <input type="text" class="form-control" id="PiePagFecha" style=" border-width: 0; text-align: right; background-color: white" placeholder="" readonly="">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                        </div>
                    </div>
                    <hr style="border-color: #244061;">
                    <label>Fecha</label>
                    <div>
                        <span id="msjFechaPiePag" class="control-label" style="color:red;font-style: normal;" hidden=""></span>
                    </div>
                </div> -->

                <div class="col-md-2"></div>
            </div>
        </center>
    </div>

    <div id="divMensajeDeclaracion" class="row" style="margin-left: 30px;margin-right: 8px;border-right-width: 10px;">
        <div class="row">
            <p style="text-align: justify;">“<b>Declaración Jurada de Veracidad:</b> Yo, en mi condición de Representante Legal (Cuyos datos consignó en el cuadro siguiente), de la empresa que se me presenta como proveedor, declaro bajo juramento que: (i) la información brindada en la presenta Ficha de Datos de Proveedor, es veraz, cierta y vigente; y (ii) cuento con la autorización necesaria para suscribir al mismo”
            </p>
        </div>
    </div>
    <div class="row" id="idComentario">
        <div class="form-group col-md-12">
            <label>Comentario</label>
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <textarea id="txtComentario" name="txtComentario" class="form-control" aria-required="true" placeholder=""></textarea>
            </div>
            <span id='msjComentario' class="control-label" style='color:red;font-style: normal;' hidden></span>
        </div>
    </div>

    <div class="modal-footer">
    <button type="button" onclick="confirmAprobar();" class="btn btn-info"><i class="fa fa-check"></i> Aprobar</button>
    <button type="button" onclick="confirmRechazar();" class="btn btn-danger"><i class="fa fa-times"></i> Rechazar</button>
</div>

    <!--fin adicional-->
</div>




<div id="modalDocumento" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Agregar nuevo documento</h4>
            </div>

            <form id="create_documentos_form" action="vistas/com/rprov/servicio_subir_archivos.php" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                    <div id="datatable2" id="scroll"></div>
                    </div>


                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa ion-android-close"></i> Cancelar</button>
                    <button type="button" onclick="guardarInvitacion();" class="btn btn-info" data-toggle="reload"><i class="fa fa-send-o"></i> Enviar</button>
                </div>
            </form>
        </div>
    </div>
</div>