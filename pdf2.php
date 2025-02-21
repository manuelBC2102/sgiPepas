<html>
    <head>
        <title><?php echo $_GET['nombre_pdf']; ?></title>
    </head>
    <body onload="history.replaceState(null, '', 'pdf')" style="overflow: hidden;"> 
        <iframe src="<?php echo $_GET['url_pdf']; ?>" 
                width="100%" height="100%" id="PDFtoPrint">

        </iframe>
    </body>

    <script type="text/javascript" language="javascript" src="vistas/libs/imagina/js/jquery.js"></script>
    
    <script>
//        $(document).ready(function () {
//            document.getElementById('PDFtoPrint').focus();
//            document.getElementById('PDFtoPrint').contentWindow.print();
//        });
    </script>
    
</html>
