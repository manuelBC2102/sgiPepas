<?php
include_once __DIR__ . '/util/Util.php';
include_once __DIR__ . '/vistas/com/template/TemplateWorkflow.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema gestión de reportes. Desarrollado por Minapp https://minapp.pe/site/">
    <meta name="author" content="ImaginaTecPeru">
    <meta http-Equiv="Cache-Control" Content="no-cache">
    <meta http-Equiv="Pragma" Content="no-cache">
    <meta http-Equiv="Expires" Content="0">
    <link rel="icon" type="image/png" href="images/logoTransparente.png" />
    <title>ImaginaTecPeru</title>

    <!-- Google-Fonts -->
    <link href='https://fonts.googleapis.com/css?family=Source+Sans+Pro:100,300,400,600,700,900,400italic' rel='stylesheet'>

    <!-- Bootstrap core CSS -->
    <link href="<?php echo $url_libs_imagina; ?>css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $url_libs_imagina; ?>css/bootstrap-reset.css" rel="stylesheet">

    <!-- Animation CSS -->
    <link href="<?php echo $url_libs_imagina; ?>css/animate.css" rel="stylesheet">

    <!-- Icon-fonts CSS -->
    <link href="<?php echo $url_libs_imagina; ?>assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="<?php echo $url_libs_imagina; ?>assets/ionicon/css/ionicons.min.css" rel="stylesheet" />

    <link href="<?php echo $url_libs_imagina; ?>assets/form-wizard/jquery.steps.css" rel="stylesheet" />
    <!-- sweet alerts -->
    <link href="<?php echo $url_libs_imagina; ?>assets/sweet-alert/sweet-alert.min.css" rel="stylesheet">

    <!-- dataTables -->
    <link href="<?php echo $url_libs_imagina; ?>assets/datatables2/dataTables.bootstrap.min.css" rel="stylesheet" />

    <!-- Select 2-->
    <link href="<?php echo $url_libs_imagina; ?>assets/select2/select2.min.css" rel="stylesheet" />

    <!-- Datepicker-->
    <link href="<?php echo $url_libs_imagina; ?>assets/timepicker/bootstrap-datepicker3.min.css" rel="stylesheet" />

    <!-- Dropzone css -->
    <!--<link href="<?php echo $url_libs_imagina; ?>assets/dropzone/dropzone.css" rel="stylesheet" type="text/css" />-->

    <!-- Calendar css -->
    <!--<link href="<?php echo $url_libs_imagina; ?>assets/fullcalendar/fullcalendar.css" rel="stylesheet" />-->
    <!--<link href="<?php echo $url_libs_imagina; ?>css/bootstrap-reset.css" rel="stylesheet">-->

    <!-- Custom styles for this template -->
    <link href="<?php echo $url_libs_imagina; ?>css/style.css" rel="stylesheet">
    <link href="<?php echo $url_libs_imagina; ?>css/helper.css" rel="stylesheet">
    <link href="<?php echo $url_libs_imagina; ?>css/style-responsive.css" rel="stylesheet" />

    <!-- Adicionales -->
    <link href="<?php echo $url_libs_imagina; ?>assets/notifications/notification.css" rel="stylesheet" />
    <link href="<?php echo $url_base; ?>vistas/css/estilos.css" rel="stylesheet" />

    <link href="<?php echo $url_libs_imagina; ?>assets/select2/select2.css" rel="stylesheet" />
    <link href="<?php echo $url_libs_imagina; ?>assets/sweetalert2/sweetalert.css" rel="stylesheet" />
    <link href="<?php echo $url_libs_imagina; ?>assets/timepicker/bootstrap-datepicker3.min.css" rel="stylesheet" />

    <link href="<?php echo $url_libs_imagina; ?>assets/bootstrap-wysihtml5/bootstrap-wysihtml5.css">
    <link href="<?php echo $url_libs_imagina; ?>assets/summernote/summernote.css" rel="stylesheet">

</head>

<body>

    <div class="page-title">
        <div class="col-md-1 col-md-1 col-xs-1"></div>
        <h3 class="title"><span id="tituloRegistroProveedores"></span></h3>
    </div>


    <div class="row">
        <div class="col-md-1 col-md-1 col-xs-1"></div>
        <div class="col-md-10 col-md-10 col-xs-10">
            <div class="panel panel-body">
                <?php include "vistas/com/registro_proveedor/formProveedorAsociativa.php"; ?>
                <input type="hidden" id="codigoSAP" value="<?php echo $_GET['codigoSAP']; ?>" />
                <input type="hidden" id="tipoPersona" value="<?php echo $_GET['tipoPersona']; ?>" />
                <input type="hidden" id="codigoIdentificacion" value="<?php echo $_GET['codigoIdentificacion']; ?>" />
                <input type="hidden" id="empresaId" value="" />
                <input type="hidden" id="accionGeneral" value="" />
                <input type="hidden" id="solicitudId" value="" />
                <div class="form-group col-md-12">
                    <div class="text-right">
                        <span id="spButton"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- JS placed at the end of the document so the pages load faster -->
    <script src="<?php echo $url_libs_imagina; ?>js/jquery.js"></script>
    <script src="<?php echo $url_libs_imagina; ?>js/bootstrap.min.js"></script>
    <script src="<?php echo $url_libs_imagina; ?>js/pace.min.js"></script>
    <script src="<?php echo $url_libs_imagina; ?>js/wow.min.js"></script>
    <script src="<?php echo $url_libs_imagina; ?>js/jquery.nicescroll.js" type="text/javascript"></script>

    <script src="<?php echo $url_libs_imagina; ?>js/jquery.app.js"></script>
    <script src="<?php echo $url_libs_imagina; ?>js/inputmask2.js"></script>

    <script src="<?php echo $url_libs_imagina; ?>assets/notifications/notify.min.js"></script>
    <script src="<?php echo $url_libs_imagina; ?>assets/notifications/notify-metro.js"></script>
    <script src="<?php echo $url_libs_imagina; ?>assets/notifications/notifications.js"></script>

    <script type="text/javascript">
        var URL_BASE = "<?php echo $url_base; ?>";
    </script>
    <script type="text/javascript">
          ;
        var parametrosUrl = <?php echo json_encode($_POST["parametro"]); ?>;
    </script>

    <script src="<?php echo $url_base; ?>vistas/VistaConfiguraciones.js"></script>
    <script src="<?php echo $url_base; ?>vistas/com/util/Global.js"></script>
    <script src="<?php echo $url_base; ?>vistas/com/util/Enums.js"></script>
    <script src="<?php echo $url_base; ?>vistas/com/util/Include.js"></script>
    <script src="<?php echo $url_base; ?>vistas/com/util/EventManager.js"></script>
    <script src="<?php echo $url_base; ?>vistas/com/util/Utils.js"></script>
    <script src="<?php echo $url_base; ?>vistas/com/util/String.js"></script>
    <script src="<?php echo $url_base; ?>vistas/com/util/validatorResponse/ValidatorResponse.js"></script>
    <script src="<?php echo $url_base; ?>vistas/com/util/Ajaxp.js"></script>
    <script src="<?php echo $url_base; ?>vistas/com/util/Mensajes.js"></script>
    <script src="<?php echo $url_libs_imagina; ?>assets/select2/select2.min.js"></script>
    <script src="<?php echo $url_libs_imagina; ?>assets/select2/lodash.min.js"></script>
    <script src="<?php echo $url_libs_imagina; ?>assets/sweetalert2/sweetalert.min.js"></script>

    <!--<script src="vistas/libs/imagina/assets/select2/select2.min.js"></script>-->
    <script src="<?php echo $url_libs_imagina; ?>assets/select2/locales/select2_locale_es.js"></script>
    <script src="<?php echo $url_libs_imagina; ?>assets/timepicker/bootstrap-datepicker.min.js"></script>
    <script src="<?php echo $url_libs_imagina; ?>assets/timepicker/locales/bootstrap-datepicker.es.js"></script>
    <script src="<?php echo $url_libs_imagina; ?>assets/sweetalert2/sweetalert.min.js"></script>

    <script src="<?php echo $url_libs_imagina; ?>assets/summernote/summernote.min.js"></script>


    <script src="<?php echo $url_libs_imagina; ?>assets/form-wizard/bootstrap-validator.min.js" type="text/javascript"></script>

    <!--Form Wizard-->
    <script src="<?php echo $url_libs_imagina; ?>assets/form-wizard/jquery.steps.min.js" type="text/javascript"></script>
    <script src="<?php echo $url_libs_imagina; ?>assets/jquery.validate/jquery.validate.min.js"></script>

    <script src="<?php echo $url_base; ?>vistas/com/registro_proveedor/inscripcion7.js?<?php echo date("YmdHms"); ?>"></script>
    <script src="<?php echo $url_base; ?>vistas/libs/imagina/assets/datatables2/jquery.dataTables.min.js"></script>
    <script src="<?php echo $url_base; ?>vistas/libs/imagina/assets/datatables2/dataTables.bootstrap.min.js"></script>
    <script src="<?php echo $url_base; ?>vistas/libs/imagina/assets/datatables2/moment-with-locales.min.js"></script>
    <script src="<?php echo $url_base; ?>vistas/libs/imagina/assets/datatables2/datetime-moment.js"></script>
<!-- 
    <script src="vistas/com/index2.js"></script> -->
    <!-- <script src='vistas/com/registro_proveedor/registro_proveedor_form.js?<?php echo date("YmdHms"); ?>'></script> -->
    <!-- <script src="vistas/com/utils.js"></script> -->
</body>

</html>