<?php

require_once __DIR__ . '/../../modelo/almacen/Vehiculo.php';
require_once __DIR__ . '/../../modeloNegocio/core/ModeloNegocioBase.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/PersonaNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/commons/TablaNegocio.php';

class VehiculoNegocio extends ModeloNegocioBase {

    /**
     * 
     * @return VehiculoNegocio
     */
    static function create() {
        return parent::create();
    }
    
    public function listarVehiculo(){
        $Vehiculo = Vehiculo::create()->getAllVehiculos();
        return $Vehiculo;
    }

    public function guardarVehiculo($id,$placa,$capacidad,$nro_constancia,$inputFile,$marca,$modelo,$tipo,$usuarioId) {


        if($id === '0'){
            // Decodificar los datos base64
            $imageData = base64_decode($inputFile);

            // Crear un nombre único para la imagen
            $imageName = uniqid() . '.png';

            // Especificar la ruta donde se guardará la imagen
            $imagePath = '../../vistas/com/vehiculo/capturas/' . $imageName;

            // Guardar la imagen en el servidor
            file_put_contents($imagePath, $imageData);
            $respuestaGuardar = Vehiculo::create()->guardarVehiculo( $id,$placa,$capacidad,$nro_constancia,$imageName,$marca,$modelo,$tipo,$usuarioId);

        }else{
            $respuestaGuardar = Vehiculo::create()->guardarVehiculo( $id,$placa,$capacidad,$nro_constancia,$inputFile,$marca,$modelo,$tipo,$usuarioId);

        }
    

        



        return $respuestaGuardar;
    }





    public function actualizarVehiculo($id,$placa,$marca,$modelo,$capacidad,$tipo) {
        return Vehiculo::create()->actualizarVehiculo($id,$placa,$marca,$modelo,$capacidad,$tipo);
    }




    public function actualizarEstadoVehiculo($id,$estado) {
        return Vehiculo::create()->actualizarEstadoVehiculo($id,$estado);
    }





   



    public function obterConfiguracionInicialForm($id) {
       
        $data->dataVehiculo = Vehiculo::create()->listarvehiculosXId($id);
        
        return $data;
    }
 
    public function validarPlacaEndPoint($placa) {
        // $url= 'http://192.141.41.205/mercaderia_transportista_placa/AKHI-796';
        $url= 'http://161.132.56.121:8000/mercaderia_placa/';
        $ch = curl_init();
        $endpointUrl = $url . urlencode($placa);
        curl_setopt($ch, CURLOPT_URL, $endpointUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Límite de tiempo para la solicitud completa
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        $response2 = json_decode(curl_exec($ch));
        $response = json_decode($response2);
        curl_close($ch);
        if ($response !== null ) {
            // Si la respuesta no es null, la solicitud fue exitosa, puedes procesarla y romper el bucle
            return $response;
        }    
        return $response ;
    }

}
