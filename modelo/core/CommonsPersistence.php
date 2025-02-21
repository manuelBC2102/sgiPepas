<?php
require_once __DIR__.'/Connection.php';
require_once __DIR__.'/../../util/Configuraciones.php';
require_once __DIR__.'/../../util/ObjectUtil.php';
/**
 * Description of CommonsPersistence
 *
 * @author 
 */
class CommonsPersistence {
     private $base_mensaje;
    
    
    public $c;
    private static $hInstance;
    
    /**
     * @var Connection 
     */
    function __construct() {
        $this->c = new Connection();
        $this->base_mensaje = null;
    }
    
    public static function create() {
        if (!(self::$hInstance instanceof self)) {
            self::$hInstance = new self();
        }
        return self::$hInstance;
    }
    
    /**
     * Seteo de mensaje personalizado en caso todo haya ocurrido correctamente.
     * 
     * @param int $clave Clave de tipo  MENSAJE EMERGENTE | TOOLTIP (12)
     * 
     * @author 
     */
    public function setMensajeEmergente($mensaje, $concatenacion, $tipo){
        $titulo = "";
        $tipo="";
        switch ($tipo){
            case Configuraciones::MENSAJE_OK:
                $titulo = "Confirmación";
                if (ObjectUtil::isEmpty($mensaje)) $mensaje = 'Ok';
                break;
            default :
                $titulo = "Validación";
                break;
        }
        $this->base_mensaje = array('titulo' => $titulo, 'mensaje' => $mensaje, 'tipo' => $tipo);
    }
    
    public function validateResponse($response){
        $id = 0; // en caso se retorne un id desde la bd se devuelve en esta funcion
        if (is_array($response) && !ObjectUtil::isEmpty($response)){
            $respuesta = $response[0];
            if (array_key_exists("vout_estado", $respuesta)){
                if (array_key_exists("vout_id", $respuesta)){
                    $id = $respuesta["vout_id"];
                }
                if ($respuesta["vout_estado"] == 1) {
                    $this->setMensajeEmergente($respuesta["vout_mensaje"], "",0);
                }else{
                    throw new WarningException($respuesta["vout_mensaje"]);
                }
            }
        }
        return $id;
    }
    /**
     * Obteniendo el mensaje personalizado en caso todo haya ocurrido correctamente.
     * 
     * @param int $clave Clave de tipo  MENSAJE EMERGENTE | TOOLTIP (12) de la tabla Idioma contenido 
     * 
     * @author 
     */
    public function getMensajeEmergente(){
        return $this->base_mensaje;
    }
}
