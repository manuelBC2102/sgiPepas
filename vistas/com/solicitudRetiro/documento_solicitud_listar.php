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
            <h3 class="title">Trámite Documentario</h3>
        </div>
        <div class="row">
            <!--<div class="col-md-12 col-md-12 col-xs-12">-->
            <div class="panel panel-default">
                <br><br>
                <div class="panel panel-body" id="muestrascroll">
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
                        <i class="fa fa-eye" style="color:#5cb85c;"></i> Visualizar Documento &nbsp;&nbsp;&nbsp;
                        <i class="fa fa-trash-o" style="color:#cb2a2a;"></i> Eliminar&nbsp;&nbsp;&nbsp;

                    </p>
                </div>
            </div>
        </div>
        <script src="vistas/libs/imagina/assets/datatables/jquery.dataTables.min.js"></script>
        <script src="vistas/libs/imagina/assets/datatables/dataTables.bootstrap.js"></script>  
        <script src="vistas/libs/imagina/assets/sweet-alert/sweet-alert.min.js" type="text/javascript"></script>
        <script src="vistas/com/solicitudRetiro/documento_solicitud_listar.js"></script> 
    </body>
</html>


