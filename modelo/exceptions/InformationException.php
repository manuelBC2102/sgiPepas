<?php
include_once __DIR__.'/BaseException.php';
/**
 * Excepción Informativa del sistema
 *
 * @author 
 */
class InformationException  extends BaseException {
    public function __construct($message, $modal = true){
        parent::__construct($message, $modal);
        $this->tipo = 4;
        $this->titulo = "Información";
    }
}
?>
