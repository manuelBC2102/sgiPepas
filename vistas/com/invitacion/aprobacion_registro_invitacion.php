<html lang="es">
    <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
         <style type="text/css" media="screen">

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
            <h3 class="title">Aprobación de registro de transportistas</h3>
        </div>
        <div class="row">
            <!--<div class="col-md-12 col-md-12 col-xs-12">-->
            <div class="panel panel-default">
               
                <div class="panel panel-body" >
                    <div class="col-md-12 col-sm-12 col-xs-12">
                  
                    <div id="dataList" >

</div>

                    </div>
                </div>
                <div style="clear:left">
                    <p><b>Leyenda:</b>&nbsp;&nbsp;
                        <!--<i class="fa fa-file-text" style="color:#088A68;"></i> Detalle de  la informaci&oacute;n &nbsp;&nbsp;&nbsp;-->
                        <i class="fa fa-file-pdf-o" style="color:#0366b0;"></i> Invitación&nbsp;&nbsp;&nbsp;

                    </p>
                </div>
            </div>
        </div>
        <script src="vistas/libs/imagina/assets/datatables/jquery.dataTables.min.js"></script>
        <script src="vistas/libs/imagina/assets/datatables/dataTables.bootstrap.js"></script>  
        <script src="vistas/com/invitacion/aprobacion_registro_invitacion.js"></script> 
    </body>
</html>


