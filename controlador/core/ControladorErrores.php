<?php

/**
 * Description of ControladorErrores
 *
 * @author 
 */

//require_once __DIR__ . '/../../modelo/sbssys/IdiomaContenido.php';
require_once __DIR__ . '/../../util/ObjectUtil.php';

class ControladorErrores
{
  const CURRENT_CULTURE = 'es_pe';

  public $has_error_php = FALSE;
  public $has_error = FALSE;
  private $error_tipo;
  private $last_error;
  private $titulo;
  private $modal = true;

  public function getTitulo()
  {
    return $this->titulo;
  }

  public function getModal()
  {
    return $this->modal;
  }

  public function getError()
  {
    return $this->last_error;
  }

  public function getErrorTipo()
  {
    return $this->error_tipo;
  }

  // <editor-fold defaultstate="collapsed" desc="Metodos principales">
  public function __construct()
  {
    // Inicializo las variables de errores
    $this->has_error = FALSE;
    $this->last_error = "";
  }


  /**
   * Metodo encargado de preparar el mensaje de error hacia el Usuario
   *
   * @param string|integer $value Cadena de error o clave del error
   * @param IdiomaContenidoTipo $type Es el tipo de Error definido en el sistema
   *
   * @author 
   *
   */
  public function responseError($error_object, $type)
  {
    switch (TRUE) {
      case method_exists($error_object, "getTitulo"):
        $titulo =  $error_object->getTitulo();

        break;
      case method_exists($error_object, "getTitle"):
        $titulo =  $error_object->getTitle();

        break;
      default:
        $titulo = "";
    }

    $modal = (method_exists($error_object, "getModal") ? $error_object->getModal() : "");

    $this->has_error = TRUE;
    $this->error_tipo = $type;
    $this->titulo = $titulo;
    $this->last_error = $error_object->getMessage();
    $this->modal = $modal;
  }
  // </editor-fold>

  // <editor-fold defaultstate="collapsed" desc="Métodos de apoyo">
  // </editor-fold>
}
