<html lang="es">
    <head>
        
        <style type="text/css" media="screen">


/*            @media  (min-width: 1271px) and (max-width:1824px) {
                #scroll{
                    width: 1200px;
                }
                #muestrascroll{
                    overflow-x:scroll;
                }
            }
            @media screen and (max-width: 1270px) {
                #scroll{
                    width: 1100px;

                }
                #muestrascroll{
                    overflow-x:scroll;
                }

            }*/

           @media screen and (max-width: 1000px) {
                #scroll{
                    width: 1000px;               
                }
                #muestrascroll{
                    overflow-x:scroll;
                }
            }


            #datatable td{
                vertical-align: middle;
            }
            .sweet-alert button.cancel {
                background-color: rgba(224, 70, 70, 0.8);
            }
            .sweet-alert button.cancel:hover {
                background-color:#E04646;
            }
            .sweet-alert {

                border-radius: 0px; 

            }
            .sweet-alert button {
                -webkit-border-radius: 0px; 
                border-radius: 0px; 

            }
        </style>
        <title>Almacen</title>
    </head>
    <body >
        <div class="page-title">
            <h3 class="title">Negociar</h3>
        </div>
        <div class="row">
            <!--<div class="col-md-12 col-md-12 col-xs-12">-->
            <div class="panel panel-default">
                <br><br>
                <div class="panel panel-body" id="muestrascroll">
                <h3 ><b>Lotes enviados a negociación</b></h3>
                    <div class="col-md-12 col-sm-12 col-xs-12" id="scroll">
                        <div class="table">
                            <div id="dataList" >

                            </div>
                        </div>
                    </div>
                </div>
                <div style="clear:left">
                    <p><b>Leyenda:</b>&nbsp;&nbsp;
                        <!--<i class="fa fa-file-text" style="color:#088A68;"></i> Detalle de  la informaci&oacute;n &nbsp;&nbsp;&nbsp;-->
                        <i class="fa fa-check-square-o" style="color:blue;"></i> Cargar a pagar &nbsp;&nbsp;&nbsp;
                        

                    </p>
                </div>
            </div>
            <div class="modal fade" id="registroModal" tabindex="-1" role="dialog" aria-labelledby="registroModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="registroModalLabel">Registro de resultados finales</h4>
                </div>
                <div class="modal-body">
                    <!-- Contenido del modal -->
                    <form>
                    <div class="form-group col-md-6">
    <label>Ley a pagar </label>
    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <input 
            type="text" 
            id="txtLey" 
            name="txtLey" 
            class="form-control" 
            aria-required="true" 
            value="" 
            maxlength="250"
            pattern="^\d+(\.\d{1,2})?$" 
            title="Por favor, ingrese solo números, incluidos decimales con hasta 2 dígitos."
            inputmode="decimal" 
            oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" 
        />
    </div>
</div>
<div class="form-group col-md-6">
    <label>Monto Valorización </label>
    <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <input 
            type="text" 
            id="txtMonto" 
            name="txtMonto" 
            class="form-control" 
            aria-required="true" 
            value="" 
            maxlength="250"
            pattern="^\d+(\.\d{1,2})?$" 
            title="Por favor, ingrese solo números, incluidos decimales con hasta 2 dígitos."
            inputmode="decimal" 
            oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" 
        />
    </div>
</div>
<input type="hidden" id="txtaprobacion" >
<input type="hidden" id="txtloteId" >

                        <!-- <div class="form-group col-md-6">
                            <label>Documento resultado (.pdf)</label>&nbsp;
                          
                                
                                    <br> 
                                    <div class="fileUpload btn w-lg m-b-2" style="background-color:#321d48; color:white; min-width: 40px;" id="multi" style="border-radius: 0px;background-color: #337Ab7;color: #fff;cursor:default;">
                                        <div id="edi"><i class="ion-upload m-r-15" style="font-size: 10px;"></i>Subir resultado </div>
                                        <input name="file" id="file" type="file" accept="application/pdf" class="upload" onchange='$("#upload-file-info").html($(this).val().slice(10));'>
                                    </div>
                                    <b class='' style="font-size: 10px;" id="upload-file-info">Ningún resultado seleccionado</b>
                                
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
                            
                        </div> -->


                        <br><br>
                        <!-- Agrega más campos según sea necesario -->
                    </form>
                </div>
                <div class="modal-footer">
                    
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarAprobador()" >Guardar</button>
                </div>
            </div>
        </div>
    </div>
        </div>
        <script src="vistas/libs/imagina/assets/datatables/jquery.dataTables.min.js"></script>
        <script src="vistas/libs/imagina/assets/datatables/dataTables.bootstrap.js"></script>  
        <script src="vistas/libs/imagina/assets/sweet-alert/sweet-alert.min.js" type="text/javascript"></script>
        <script src="vistas/com/dirimencia/negociar_listar.js"></script> 
    </body>
</html>


