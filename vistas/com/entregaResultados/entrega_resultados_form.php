<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lotes y Minerales</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .panel {
            border: 1px solid #ddd;
            border-radius: 8px;
            margin: 20px;
            padding: 20px;
            background-color: #f9f9f9;
        }

        .panel-hdr {
            padding: 10px;
            border-bottom: 2px solid #ddd;
            margin-bottom: 20px;
            background-color: #0366b0;
            color: #fff;
            border-radius: 5px;
        }

        .panel-hdr h2 {
            margin: 0;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }

        /* .select2-container--default .select2-selection--single {
            border: 1px solid #ddd;
            border-radius: 5px;
        } */
        .row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
        }

        .col-md-4 {
            flex: 1;
            min-width: 30%;
        }

        .btn-success,
        .btn-danger {
            color: #fff;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            border: none;
        }

        .btn-success {
            background-color: #28a745;
        }

        .btn-danger {
            background-color: #dc3545;
        }

        .add-mineral {
    font-size: 16px;
    padding: 8px 16px;
    border-radius: 4px;
    transition: background-color 0.3s, transform 0.3s;
}

.add-mineral:hover {
    background-color: #28a745; /* Verde más intenso al pasar el ratón */
    transform: scale(1.1);
}

.add-mineral:active {
    transform: scale(1.05); /* Efecto de presión en el botón */
}

/* Estilo para el botón de Eliminar Mineral */
.remove-mineral {
    font-size: 16px;
    padding: 8px 16px;
    border-radius: 4px;
    transition: background-color 0.3s, transform 0.3s;
}

.remove-mineral:hover {
    background-color: #dc3545; /* Rojo más intenso al pasar el ratón */
    transform: scale(1.1);
}

.remove-mineral:active {
    transform: scale(1.05); /* Efecto de presión en el botón */
}

/* Para ocultar el botón de eliminar en el primer mineral */
.remove-mineral[style="display: none;"] {
    display: none !important;  }
    </style>
</head>

<body>
    <div class="container">

        <h1>Resultados de lotes</h1>
       
       
        <div id="dataList" class="form-container">
            <!-- Los formularios generados se insertarán aquí -->
        </div>
        <!-- <button id="guardarDatos" onclick="guardarLotes()" class="btn btn-primary">Guardar Datos</button> -->
        
        <a href="#" class="btn btn-danger m-b-5" id="id" style="border-radius: 0px;" onclick="cargarListarActaCancelar()"><i class="fa fa-close"></i>&ensp;Cancelar</a>&nbsp;&nbsp;&nbsp;
        <button type="button" onclick="guardarLotes()" value="enviar" name="env" id="guardarDatos" class="btn btn-info w-sm m-b-5" style="border-radius: 0px;"><i class="fa fa-send-o"></i>&ensp;Enviar</button>&nbsp;&nbsp;
    </div>
</body>

</html>
<script src="vistas/com/entregaResultados/entrega_resultados_form.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/css/select2.min.css" rel="stylesheet" />
<!-- JS de Select2 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>