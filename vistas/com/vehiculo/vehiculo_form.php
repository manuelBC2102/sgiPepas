<?php
$id = $_GET['id'];
$tipo = $_GET['tipo'];

$titulo = "Nuevo Vehiculo";
if ($tipo == 1) {
    $titulo = "Editar Vehiculo";
}
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <title></title>
        <link href="vistas/libs/imagina/css/bootstrap-combobox.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="vistas/libs/imagina/assets/jquery-multi-select/multi-select.css" />
        <link rel="stylesheet" type="text/css" href="vistas/libs/imagina/assets/select2/select2.css" />
        <script type="text/javascript" src="vistas/libs/imagina/assets/jquery-multi-select/jquery.multi-select.js"></script>
        <script src="vistas/libs/imagina/assets/select2/select2.min.js" type="text/javascript"></script>       
    </head>
    <body>

<!--        <section class="content">-->

        <div class="row">
            <div class="col-md-12 ">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <h4><b><?php echo $titulo; ?>:</b></h4>
                        <div class="col-md-12 ">
                            <div class="panel-body">
                                <form  id="frmAgencia"  method="post" class="form" enctype="multipart/form-data;charset=UTF-8">
                                    <input type="hidden" name="id" id="id" value="<?php echo ($id == NULL ? 0 : $id) ?>"/>
                                    <div class="row">

                                        <div class="form-group col-md-6">
                                            <label>Placa *</label>
                                            <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <input type="text" id="txt_placa" name="txt_placa" class="form-control" value="" maxlength="25"/>
                                                <span class="input-group-btn">
                                                    <button type="button" class="btn btn-effect-ripple btn-primary" onclick="buscarConsultaPlaca();"><i class="fa fa-search"></i>  Buscar</button>
                                                </span>
                                            </div>
                                            <span id='msj_placa' class="control-label"
                                                  style='color:red;font-style: normal;' hidden></span>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label>Capacidad *</label>
                                            <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <input type="number" id="txt_capacidad" name="txt_capacidad" class="form-control" value="" maxlength="25"/>
                                            </div>
                                            <span id='msj_capacidad' class="control-label"
                                                  style='color:red;font-style: normal;' hidden></span>
                                        </div>
                                        
                                        

                                       
                                    </div>



                                    <div class="row">


                                        <div class="form-group col-md-6" id="constancia_contenedor" >
                                            <label>N° Constancia *</label>
                                            <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <input type="text" id="txt_nro_constancia" name="txt_nro_constancia" class="form-control" value="" maxlength="25"/>
                                            </div>
                                            <span id='msj_nro_constancia' class="control-label"
                                                  style='color:red;font-style: normal;' hidden></span>
                                        </div>
                                      
                                        
                                        
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-4">
                                            <label for="cbo_tipo">Tipo *</label>
                                            <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <select name="cbo_tipo" id="cbo_tipo" class="select2">
                                                    <option value="1"><i class="fa fa-car"></i> Vehículo</option>
                                                    <option value="2"><i class="fa fa-truck"></i> Carreta</option>
                                                    <!-- <option value="3"><i class="fa fa-cogs"></i> Maquinaria</option> -->
                                                </select>
                                            </div>
                                            <span id="msj_tipo" class="control-label" style="color:red;font-style:normal;display:none;"></span>
                                        </div>

                                         <!-- marca -->
                                        <div class="form-group col-md-4">
                                            <label>Marca *</label>
                                            <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <input type="text" id="txt_marca" name="txt_marca" class="form-control" required="" aria-required="true" value="" maxlength="250"/>
                                            </div>
                                            <span id='msj_marca' class="control-label"
                                                  style='color:red;font-style: normal;' hidden></span>
                                        </div>
                                        <!-- Modelo -->
                                        <div class="form-group col-md-4">
                                            <label>Modelo *</label>
                                            <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <input type="text" id="txt_modelo" name="txt_modelo" class="form-control" required="" aria-required="true" value="" maxlength="250"/>
                                            </div>
                                            <span id='msj_modelo' class="control-label"
                                                  style='color:red;font-style: normal;' hidden></span>
                                        </div>
                                    </div>
                                    <div class="row">
                                    <div class="form-group col-md-6" id="img_contenedor" >
                                            <label>Imagen *</label>
                                            <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <a id="img_link" target="_blank">
                                                    <a id="download_img" style="display:none;" download="captura_de_placa.png">Descargar imagen</a>

                                                    <img id="img_captura" alt="Captura de Placa" style="display:none; width: 500px; height:600px;">
                                                    <input id="img_64" alt="Captura de Placa" style="display:none; width: 500px; height:600px;" hidden>
                                                </a>
                                            </div>
                                           
                                        </div>

                                    </div>

                                    
                                    <br>
                                    <div class="row alignRight">
                                        <div class="form-group col-md-12">
                                            <a href="#" class="btn btn-danger m-b-5" id="id" style="border-radius: 0px;" onclick="cargarDivIndex('#window', 'vistas/com/vehiculo/vehiculo_listar.php', 355, '')" ><i class="fa fa-close"></i>&ensp;Cancelar</a>&nbsp;&nbsp;&nbsp;
                                            <button type="button" onclick="guardarVehiculo()" value="guardar" name="env" id="env" class="btn btn-info w-sm m-b-5" style="border-radius: 0px;"><i class="fa fa-send-o"></i>&ensp;Enviar</button>&nbsp;&nbsp;
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="vistas/libs/imagina/js/bootstrap-combobox.js"></script>      
        <script src="vistas/com/vehiculo/vehiculo_form.js"></script> 
    </body>
</html>
