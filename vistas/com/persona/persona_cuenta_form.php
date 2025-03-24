<?php
session_start();
$id = null;
$tipo = null;
extract($_REQUEST, EXTR_PREFIX_ALL, "f");
if (isset($f_id)) {
    $id = (int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT);
}
if (isset($f_tipo)) {
    //si el tipo es 1 se va a editar
    $tipo = (int) filter_var($f_tipo, FILTER_SANITIZE_NUMBER_INT);
}
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <script>
            altura();
        </script> 
    </head>
    <body>
        <div class="row">
            <div class="col-md-12 ">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <h4 ><b id="titulo"></b></h4>
                        <div class="col-md-12 ">
                            <div class="panel-body">
                                <form  id="frm_colaborador"  method="post" class="form" enctype="multipart/form-data;charset=UTF-8">
                                    <input type="hidden" name="usuario" id="usuario" value="<?php echo $_SESSION['id_usuario']; ?>"/>
                                    <input type="hidden" name="id" id="id" value="<?php echo $id ?>"/>

                                    <div class="row">
                                        <div class="form-group col-md-6 ">
                                            <label>Número *</label>
                                            <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <input type="text" id="txtNumero" name="txtNumero" class="form-control" value="" maxlength="500"/>
                                            </div>
                                            <span id='msjNumero' class="control-label"
                                                  style='color:red;font-style: normal;' hidden></span>
                                        </div>
                                        <div class="form-group col-md-6 ">
                                            <label id="lblCci">Cci *</label>
                                            <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <input type="text" id="txtCci" name="txtCci" class="form-control" value="" maxlength="500"/>
                                            </div>
                                            <span id='msjCci' class="control-label"
                                                  style='color:red;font-style: normal;' hidden></span>
                                        </div>                                        
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label>Tipo *</label>
                                            <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <select name="cboTipo" id="cboTipo" class="select2" onchange="seleccionarBanco()">
                                                    <option value="0" selected>Seleccionar</option>
                                                    <option value="1">Principal</option>
                                                    <option value="2">Detracción</option>
                                                </select>

                                                </select>
                                                <span id='msjTipo' class="control-label"
                                                      style='color:red;font-style: normal;' hidden></span>
                                            </div>
                                        </div>                                        
                                        <div class="form-group col-md-6">
                                            <label>Banco * </label>
                                            <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <select name="cboBanco" id="cboBanco" class="select2" onchange="cambiarTextoTipoCuenta()">
                                                </select>
                                                <i id='msjBanco'
                                                   style='color:red;font-style: normal;' hidden></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label id="lblTipoCuenta">Tipo Cuenta </label>
                                            <div class="input-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <select name="cboTipoCuenta" id="cboTipoCuenta" class="select2">
                                                    <option value="0" selected>Seleccionar</option>
                                                    <option value="001">Cuenta corriente</option>
                                                    <option value="002">Cuenta de ahorros</option>
                                                </select>

                                                </select>
                                                <span id='msjTipoCuenta' class="control-label"
                                                      style='color:red;font-style: normal;' hidden></span>
                                            </div>
                                        </div>
                                    </div>                                    
                                    <br>
                                    <div class="row">
                                        <div class="form-group col-md-12">
                                            <a href="#" class="btn btn-danger m-b-5" id="id" style="border-radius: 0px;" onclick="cargarListarPersonaCuenta()" ><i class="fa fa-close"></i>&ensp;Cancelar</a>&nbsp;&nbsp;&nbsp;
                                            <button type="button" onclick="guardarPersonaCuenta()" value="enviar" name="env" id="env" class="btn btn-info w-sm m-b-5" style="border-radius: 0px;"><i class="fa fa-send-o"></i>&ensp;Enviar</button>&nbsp;&nbsp;
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<!--<script src="vistas/libs/imagina/assets/select2/select2.min.js"></script>-->
        <script src="vistas/com/persona/persona_cuenta.js"></script>
        <script type="text/javascript">
                                                loaderShow(null);
                                                cargarSelect2();
        </script>

    </body>
</html>
