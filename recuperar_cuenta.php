<?php
include_once __DIR__ . '/util/Util.php';
include_once __DIR__ . '/controlador/core/ControladorParametros.php';
include_once __DIR__ . '/controlador/almacen/AlmacenIndexControlador.php';
include_once __DIR__ . '/controlador/almacen/UsuarioControlador.php';
if (is_null($_POST['usu_email'])==false) {
    $usu_email = $_POST['usu_email'];
    $usu = new UsuarioControlador();
    //echo "email: ".$usu_email;
    $response = $usu->recuperarContrasena($usu_email);
    // var_dump($response);
   // exit();
    if($response[0]['email']=='' || $response[0]['email']==null)
    {
        header("location:recuperar_cuenta.php?error=si"); 
    }  else {
        header("location:recuperar_cuenta.php?error=no");
    }
}
?>
 <html lang="en">

        <!-- Mirrored from coderthemes.com/velonic/admin/login.html by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 14 May 2015 23:17:26 GMT -->
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="description" content="">
            <meta name="author" content="">

            <link rel="shortcut icon" href="admin/img/favicon_1.ico">

            <title>Sistema de Almacen</title>

            <!-- Google-Fonts -->
            <!-- Bootstrap core CSS -->
            <link href="vistas/libs/imagina/css/bootstrap.min.css" rel="stylesheet">
            <!--<link href="admin/css/bootstrap.min.css" rel="stylesheet">-->
            <link href="vistas/libs/imagina/css/bootstrap-reset.css" rel="stylesheet">

            <!--Animation css-->
            <link href="vistas/libs/imagina/css/animate.css" rel="stylesheet">

            <!--Icon-fonts css-->
            <link href="vistas/libs/imagina/assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
            <link href="vistas/libs/imagina/assets/ionicon/css/ionicons.min.css" rel="stylesheet" />

            <!--Morris Chart CSS -->
            <!--<link rel="stylesheet" href="admin/assets/morris/morris.css">-->


            <!-- Custom styles for this template -->
            <link href="vistas/libs/imagina/css/style.css" rel="stylesheet">
            <link href="vistas/libs/imagina/css/helper.css" rel="stylesheet">
            <link href="vistas/libs/imagina/css/style-responsive.css" rel="stylesheet" />

        </head>


        <body ng-app="almacenLoginApp"
          style="background: url('vistas/images/mina.jpg') no-repeat center center fixed;
          -webkit-background-size: cover;
          -moz-background-size: cover;
          -o-background-size: cover;
          background-size: cover;">

        <div class="wrapper-page animated fadeInDown">
            <div class="panel panel-color panel-primary" style="border-top: 1px solid;">
                <div class="panel-heading" style="padding: 10px"> 
                    <h3 class="text-center m-t-10"> Recuperar contrase&ntilde;a</h3>
                </div> 

                <form class="form-horizontal m-t-40" action="recuperar_cuenta.php" method="POST">
                    <div class="form-group ">
                        <div class="col-xs-12">
                            <input class="form-control" type="text" id="usu_email" name="usu_email" placeholder="Ingrese usuario o email" value="">
                        </div>
                    </div>
                    <div class="form-group ">
                        <div class="col-xs-12">

                        </div>
                    </div>
                    <div class="form-group text-right">
                        <div class="col-xs-12">
                            <a href="<?php echo Configuraciones::url_base(); ?>login.php">Regresar&nbsp;&nbsp;&nbsp;</a>
                            <button class="btn btn-primary w-md" id="btn_recuperar" name="btn_recuperar" type="submit"><i class="fa fa-send-o"></i> Enviar</button>
                        </div>
                    </div>
                </form>
                <?php
                if (isset($_GET['error'])) {
                    echo("<br />\n");
                    if ($_GET['error'] == "si") {
                        echo("<font color='red'>Error: Usuario o email no registrado</font>\n");
                    } elseif ($_GET['error'] == "no") {
                        echo("<font color='blue'>Datos enviados a email</font>\n");
                    }
                }
                ?>
            </div>
        </div>

        <?php
        include_once __DIR__ . '/vistas/com/template/partBodyMainContentEnds.php';
        ?>
            <!--<script src="<?php echo Configuraciones::url_base(); ?>vistas/recuperarCuenta.js"></script>-->
    </body>

    </html>
