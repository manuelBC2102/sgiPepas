<?php
$id = $_GET['id'];
$tipo = $_GET['tipo'];

$titulo = "Nueva agencia";
if ($tipo == 1) {
    $titulo = "Editar agencia";
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
                                        <div class="form-group col-md-4 ">
                                            <label>Centro costo *</label>
                                            <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <input type="text" id="txt_codigo" name="txt_codigo" class="form-control" value="" maxlength="25"/>
                                            </div>
                                            <span id='msj_codigo' class="control-label"
                                                  style='color:red;font-style: normal;' hidden></span>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Descripci&oacute;n *</label>
                                            <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <input type="text" id="txt_descripcion" name="txt_descripcion" class="form-control" required="" aria-required="true" value="" maxlength="250"/>
                                            </div>
                                            <span id='msj_descripcion' class="control-label"
                                                  style='color:red;font-style: normal;' hidden></span>
                                        </div>

                                        <div class="form-group col-md-4">
                                            <label>Estado *</label>
                                            <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <select name="cbo_estado" id="cbo_estado" class="select2">
                                                    <option value="1" selected>Activo</option>
                                                    <option value="0">Inactivo</option>
                                                </select>
                                                <span id='msj_estado' class="control-label"
                                                      style='color:red;font-style: normal;' hidden></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">

                                        <div class="form-group col-md-4">
                                            <label id="lb_division">División *</label>
                                            <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <select name="cbo_division" id="cbo_division" class="select2" >  </select>
                                                <span id='msj_division' class="control-label"
                                                      style='color:red;font-style: normal;' hidden></span>
                                            </div>
                                        </div>

                                        <div class="form-group col-md-4">
                                            <label id="lb_modelo_local">Modelo de local</label>
                                            <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <select name="cbo_modelo_local" id="cbo_modelo_local" class="select2">                                                     
                                                </select>
                                                <span id='msj_modelo_local' class="control-label"
                                                      style='color:red;font-style: normal;' hidden></span>
                                            </div>
                                        </div>

                                        <div class="form-group col-md-4">
                                            <label id="lb_ubicacion_geografica">Ubicación Geográfica *</label>
                                            <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <select name="cbo_ubicacion_geografica" id="cbo_ubicacion_geografica" class="select2">                                                     
                                                </select>
                                                <span id='msj_ubicacion_geografica' class="control-label"
                                                      style='color:red;font-style: normal;' hidden></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label id="lb_ubigeo">Ubigeo</label>
                                            <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <select name="cbo_ubigeo" id="cbo_ubigeo" class="select2">                                                     
                                                </select>
                                                <span id='msj_ubigeo' class="control-label"
                                                      style='color:red;font-style: normal;' hidden></span>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Dirección *</label>
                                            <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <textarea type="text" id="txt_direccion" name="txt_direccion" class="form-control" value="" maxlength="500"></textarea>
                                                <span id='msj_direccion' class="control-label"
                                                      style='color:red;font-style: normal;' hidden></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row"></div>
                                    <div class="row">
                                        <div class="form-group col-md-12">
                                            <a href="#" class="btn btn-danger m-b-5" id="id" style="border-radius: 0px;" onclick="cargarDivIndex('#window', 'vistas/com/agencia/agencia_listar.php', 324, '')" ><i class="fa fa-close"></i>&ensp;Cancelar</a>&nbsp;&nbsp;&nbsp;
                                            <button type="button" onclick="guardarAgencia()" value="guardar" name="env" id="env" class="btn btn-info w-sm m-b-5" style="border-radius: 0px;"><i class="fa fa-send-o"></i>&ensp;Enviar</button>&nbsp;&nbsp;
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
        <script src="vistas/com/agencia/agencia_form.js"></script>
    </body>
</html>
