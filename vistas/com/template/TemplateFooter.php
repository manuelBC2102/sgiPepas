<?php
include_once __DIR__.'/TemplateBase.php';

/**
 * @author 
 * @todo Clase que se debe agregar en todas las páginas e inserta el contenido del head del HTML
 */

class TemplateFooter extends TemplateBase {
    
    /**
     * 
     * @return TemplateFooter
     */
    public function create() {
        return parent::create();
    }
    
    public function inicia(){
        echo '2015 © Minapp.';
    }
    
    
}
