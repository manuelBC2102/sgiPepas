<?php
include_once __DIR__.'/BaseException.php';
/**
 * Excepción Advertencia del Sistema
 *
 * @author 
 */
class WarningException extends BaseException { 
    public function __construct($message, $modal = true){
        parent::__construct($message, $modal);
        $this->tipo = 3;
        $this->titulo = "Validación";
    }
}
?>
