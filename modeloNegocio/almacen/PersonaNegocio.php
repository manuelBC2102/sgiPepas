<?php
session_start();

require_once __DIR__ . '/../../modelo/almacen/Persona.php';
require_once __DIR__ . '/../../modelo/almacen/ConsultaWs.php';
require_once __DIR__ . '/../../modeloNegocio/core/ModeloNegocioBase.php';
require_once __DIR__ . '/../../modeloNegocio/commons/ConstantesNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/contabilidad/SunatTablaNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/contabilidad/CentroCostoNegocio.php';

class PersonaNegocio extends ModeloNegocioBase
{

  /**
   *
   * @return PersonaNegocio
   */
  static function create()
  {
    return parent::create();
  }

  public function getAllPersonaClase()
  {
    /** @var Countable|array */
    $data = Persona::create()->getAllPersonaClase();
    $tamanio = count($data);
    for ($i = 0; $i < $tamanio; $i++) {
      if ($data[$i]['persona_clase_estado'] == 1) {
        $data[$i]['icono'] = "ion-checkmark-circled";
        $data[$i]['color'] = "#5cb85c";
      } else {
        $data[$i]['icono'] = "ion-flash-off";
        $data[$i]['color'] = "#cb2a2a";
      }
    }
    return $data;
  }

  public function getAllPersonaTipo()
  {
    /** @var Countable|array */
    $data = Persona::create()->getAllPersonaTipo();
    $tamanio = count($data);
    for ($i = 0; $i < $tamanio; $i++) {
      if ($data[$i]['id'] == 2) {
        $data[$i]['ruta'] = "vistas/com/persona/persona_natural_form.php";
      } else {
        $data[$i]['ruta'] = "vistas/com/persona/persona_juridica_form.php";
      }
    }
    return $data;
  }

  public function ExportarPersonaExcel($usuarioId)
  {
    $estiloTituloReporte = array(
      'font' => array(
        'name' => 'Arial',
        'bold' => true,
        'italic' => false,
        'strike' => false,
        'size' => 10
      ),
      'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        'rotation' => 0,
        'wrap' => TRUE
      )
    );

    $estiloTituloColumnas = array(
      'font' => array(
        'name' => 'Arial',
        'bold' => true,
        'size' => 10
      ),
      'borders' => array(
        'allborders' => array(
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array(
            'rgb' => '000000'
          )
        )
      ),
      'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        'wrap' => FALSE
      )
    );

    $estiloTxtInformacion = array(
      'font' => array(
        'name' => 'Arial',
        'size' => 9
      ),
      'borders' => array(
        'allborders' => array(
          'style' => PHPExcel_Style_Border::BORDER_HAIR,
          'color' => array(
            'rgb' => '000000'
          )
        )
      ),
      'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        'wrap' => FALSE
      )
    );

    $estiloNumInformacion = array(
      'font' => array(
        'name' => 'Arial',
        'size' => 8
      ),
      'borders' => array(
        'allborders' => array(
          'style' => PHPExcel_Style_Border::BORDER_HAIR,
          'color' => array(
            'rgb' => '000000'
          )
        )
      ),
      'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        'wrap' => FALSE
      )
    );

    $objPHPExcel = new PHPExcel();

    $i = 1;
    $j = 2;
    $objPHPExcel->setActiveSheetIndex(0)
      ->mergeCells('B' . $i . ':N' . $i);

    //        $response
    // Agregar Informacion
    $objPHPExcel->setActiveSheetIndex()
      ->setCellValue('B' . $i, 'Lista de Personas');

    $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':N' . $i)->applyFromArray($estiloTituloReporte);
    //        $objPHPExcel->getActiveSheet()->setSharedStyle($estiloTituloReporte, "A" . $i . ":C" . $i);
    $i += 2;
    //$j++;
    $j += 2;

    //Código	Descripción	Tipo Unidad	Control	Precio sugerido compra	Precio sugerido venta	Estado	Opciones
    $response = Persona::create()->getDataPersona($usuarioId);

    $objPHPExcel->setActiveSheetIndex()
      ->setCellValue('A' . $i, '      ');
    $objPHPExcel->setActiveSheetIndex()
      ->setCellValue('B' . $i, 'CodId');
    $objPHPExcel->setActiveSheetIndex()
      ->setCellValue('C' . $i, 'Tipo');
    $objPHPExcel->setActiveSheetIndex()
      ->setCellValue('D' . $i, 'Clase');
    $objPHPExcel->setActiveSheetIndex()
      ->setCellValue('E' . $i, 'Nombre');
    $objPHPExcel->setActiveSheetIndex()
      ->setCellValue('F' . $i, 'Apellido Paterno');
    $objPHPExcel->setActiveSheetIndex()
      ->setCellValue('G' . $i, 'Apellido Materno');
    $objPHPExcel->setActiveSheetIndex()
      ->setCellValue('H' . $i, 'Telefono');
    $objPHPExcel->setActiveSheetIndex()
      ->setCellValue('I' . $i, 'Celular');
    $objPHPExcel->setActiveSheetIndex()
      ->setCellValue('J' . $i, 'Email');
    $objPHPExcel->setActiveSheetIndex()
      ->setCellValue('K' . $i, 'Direccion1');
    $objPHPExcel->setActiveSheetIndex()
      ->setCellValue('L' . $i, 'Direccion2');
    $objPHPExcel->setActiveSheetIndex()
      ->setCellValue('M' . $i, 'Direccion3');
    $objPHPExcel->setActiveSheetIndex()
      ->setCellValue('N' . $i, 'Direccion4');
    $objPHPExcel->getActiveSheet()->getStyle('B' . $i . ':N' . $i)->applyFromArray($estiloTituloColumnas);

    //
    foreach ($response as $campo) {
      $objPHPExcel->setActiveSheetIndex()
        //                ->setCellValue('A' . $i, 'Lista de Bienes')
        ->setCellValue('B' . $j, $campo['codid'])
        ->setCellValue('C' . $j, $campo['tipo'])
        ->setCellValue('D' . $j, $campo['clase'])
        ->setCellValue('E' . $j, $campo['nombre'])
        ->setCellValue('F' . $j, $campo['apellidopaterno'])
        ->setCellValue('G' . $j, $campo['apellidomaterno'])
        ->setCellValue('H' . $j, $campo['telefono'])
        ->setCellValue('I' . $j, $campo['celular'])
        ->setCellValue('J' . $j, $campo['email'])
        ->setCellValue('K' . $j, $campo['direccion_1'])
        ->setCellValue('L' . $j, $campo['direccion_2'])
        ->setCellValue('M' . $j, $campo['direccion_3'])
        ->setCellValue('N' . $j, $campo['direccion_4']);
      //            $objPHPExcel->getActiveSheet()->getStyle('B' . $j)->applyFromArray($estiloTituloColumnas);
      $i += 1;
      $j++;
      //        $objPHPExcel->setActiveSheetIndex()
      //                ->setCellValue('A' . $i, 'No Respondieron')
      //                ->setCellValue('B' . $i, 'dato2');
      //        $i +=1;
      //        $objPHPExcel->getActiveSheet()->getStyle('A' . ($i - 2) . ':A' . $i)->applyFromArray($estiloTituloColumnas);
      $objPHPExcel->getActiveSheet()->getStyle('B' . $i . ':N' . $i)->applyFromArray($estiloTxtInformacion);
      //        $objPHPExcel->getActiveSheet()->getStyle('I' . $i . ':L' . $i)->applyFromArray($estiloNumInformacion);
      //        $objPHPExcel->getActiveSheet()->getStyle('J' . $i . ':J' . $i)->applyFromArray($estiloTxtInformacion);
      //        $i +=1;
      //        $i +=2;
    }


    for ($i = 'A'; $i <= 'N'; $i++) {
      $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
    }
    // Renombrar Hoja
    $objPHPExcel->getActiveSheet()->setTitle('Personas');

    // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
    $objPHPExcel->setActiveSheetIndex(0);

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save(__DIR__ . '/../../util/formatos/lista_de_personas.xlsx');
    return 1;
  }

  public function insertPersonaClase($descripcion, $tipo, $estado, $usuarioCreacion)
  {
    $responsePersonaClase = Persona::create()->insertPersonaClase($descripcion, $estado, $usuarioCreacion);
    if ($responsePersonaClase[0]['vout_exito'] == 1) {
      $personaClaseId = $responsePersonaClase[0]['id'];
      $this->savePersonaClaseTipo($tipo, $personaClaseId, $usuarioCreacion);
    }
    return $responsePersonaClase;
  }

  public function cambiarEstadoPersonaClase($id, $estado)
  {
    return Persona::create()->cambiarEstadoPersonaClase($id, $estado);
  }

  public function updatePersonaClase($id, $descripcion, $tipo, $estado)
  {
    $responsePersonaClase = Persona::create()->updatePersonaClase($id, $descripcion, $estado);
    if ($responsePersonaClase[0]['vout_exito'] == 1) {
      $this->savePersonaClaseTipo($tipo, $id, $usuarioCreacion = 0);
    }
    return $responsePersonaClase;
  }

  function savePersonaClaseTipo($tipo, $personaClaseId, $usuarioCreacion)
  {
    Persona::create()->deletePersonaClaseTipo($personaClaseId);
    foreach ($tipo as $tipoId) {
      Persona::create()->savePersonaClaseTipo($tipoId, $personaClaseId, $usuarioCreacion);
    }
  }

  public function importaPersonaXML($xml, $usuarioCreacion, $empresaId)
  {
    return Persona::create()->importaPersonaXML($xml, $usuarioCreacion, $empresaId);
  }

  // para la tabla persona
  //    public function getAllPersona() {
  //        return Persona::create()->getAllPersona();
  //    }

  public function getAllPersona($nombres, $codigo, $tipoPersona, $clasePersona, $elemntosFiltrados, $columns, $order, $start, $usuarioId = 1)
  {
    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
    $clasePersona = Util::convertirArrayXCadena($clasePersona);
    //        $fechaVencimientoDesde = DateUtil::formatearCadenaACadenaBD($valor['inicio']);;

    return Persona::create()->getAllPersona($nombres, $codigo, $tipoPersona, $clasePersona, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start, $usuarioId);
  }

  public function getCantidadAllPersona($nombres, $codigo, $tipoPersona, $clasePersona, $elemntosFiltrados, $columns, $order, $start, $usuarioId = 1)
  {

    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
    $clasePersona = Util::convertirArrayXCadena($clasePersona);
    return Persona::create()->getCantidadAllPersona($nombres, $codigo, $tipoPersona, $clasePersona, $columnaOrdenar, $formaOrdenar, $usuarioId);
  }

  public function insertPersona($PersonaTipoIdo, $codigoIdentificacion, $nombre, $apellidoPaterno, $apellidoMaterno, $telefono, $celular, $email, $file, $estado, $usuarioCreacion, $empresa, $clase, $listaContactoDetalle, $listaDireccionDetalle, $codigoSunatId, $codigoSunatId2, $codigoSunatId3, $nombreBCP = null, $numero_cuenta_bcp = null, $cci = null, $listaCentroCostoPersona = null, $planContableId = null, $licenciaAuto = null, $licenciaMoto = null)
  {

    //direcciones antes
    $direccion = '';
    $direccionReferencia = '';

    $decode = Util::base64ToImage($file);
    if ($file == null || $file == '') {
      $imagen = null;
    } else {
      $imagen = $codigoIdentificacion . '.jpg';
      file_put_contents(__DIR__ . '/../../vistas/com/persona/imagen/' . $imagen, $decode);
    }

    $responsePersona = Persona::create()->insertPersona($PersonaTipoIdo, $codigoIdentificacion, $nombre, $apellidoPaterno, $apellidoMaterno, $telefono, $celular, $email, $direccion, $direccionReferencia, $imagen, $estado, $usuarioCreacion, $codigoSunatId, $codigoSunatId2, $codigoSunatId3, $nombreBCP, $numero_cuenta_bcp, $cci, $planContableId, $licenciaAuto, $licenciaMoto);

    if ($responsePersona[0]["vout_exito"] == 1) {
      $personaId = $responsePersona[0]['id'];
      $this->savePersonaEmpresa($empresa, $personaId, $usuarioCreacion);
      $this->savePersonaClasePersona($clase, $personaId, $usuarioCreacion);

      //            //direccion 1
      //            $res=Persona::create()->guardarPersonaDireccion($personaId,1,$direccion, $usuarioCreacion);
      //
      //            // direccion 2 -> campo de referencia
      //            $res2=Persona::create()->guardarPersonaDireccion($personaId,2,$direccionReferencia, $usuarioCreacion);
      //
      //            // direccion 3
      //            Persona::create()->guardarPersonaDireccion($personaId,3,$direccion3, $usuarioCreacion);
      //
      //            // direccion 4
      //            Persona::create()->guardarPersonaDireccion($personaId,4,$direccion4, $usuarioCreacion);
      //guardar persona direccion
      if (!ObjectUtil::isEmpty($listaDireccionDetalle)) {
        foreach ($listaDireccionDetalle as $indice => $item) {
          //listaDireccionDetalle.push([direccionTipo,direccionTipoText,ubigeo,ubigeoText,direccionText,personaDireccionId]);

          $personaDireccionId = $item[5];
          $direccionTipo = $item[1];
          $ubigeoId = $item[2];
          $direccionTexto = $item[4];

          //si existe direccion tipo obtengo el id de lo contrario inserto direccion tipo.
          $direccionTipo = trim($direccionTipo);
          $resDireccionTipo = Persona::create()->obtenerDireccionTipoXDescripcion($direccionTipo);

          if (ObjectUtil::isEmpty($resDireccionTipo)) {
            $resDireccionTipo = Persona::create()->insertarDireccionTipo($direccionTipo, $usuarioCreacion);
          }

          $direccionTipoId = $resDireccionTipo[0]['id'];
          // fin direccion tipo

          $res = Persona::create()->guardarPersonaDireccion($personaId, ($indice + 1), $direccionTexto, $usuarioCreacion, $personaDireccionId, $direccionTipoId, $ubigeoId);

          if ($res[0]['vout_exito'] == 0) {
            throw new WarningException("Error al guardar la dirección. " . $res[0]['vout_mensaje']);
          }
        }
      }
      if (!ObjectUtil::isEmpty($listaCentroCostoPersona)) {
        foreach ($listaCentroCostoPersona as $indice => $item) {
          $respuestaGuardarPersonaCentroCosto = Persona::create()->guardarPersonaCentroCosto($personaId, $item['centro_costo_id'], $item['porcentaje'], $usuarioCreacion);
          if ($respuestaGuardarPersonaCentroCosto[0]['vout_exito'] == 0) {
            throw new WarningException("Error al guardar persona centro costo. " . $respuestaGuardarPersonaCentroCosto[0]['vout_mensaje']);
          }
        }
      }

      //guardar contacto persona
      if (!ObjectUtil::isEmpty($listaContactoDetalle)) {
        foreach ($listaContactoDetalle as $indice => $item) {
          $personaContactoId = $item[4];
          $contactoId = $item[2];
          $contactoTipo = $item[1];

          //si existe contacto tipo obtengo el id de lo contrario inserto contacto tipo.
          $contactoTipo = trim($contactoTipo);
          $resContactoTipo = Persona::create()->obtenerContactoTipoXDescripcion($contactoTipo);

          if (ObjectUtil::isEmpty($resContactoTipo)) {
            $resContactoTipo = Persona::create()->insertarContactoTipo($contactoTipo, $usuarioCreacion);
          }

          $contactoTipoId = $resContactoTipo[0]['id'];
          // fin contacto tipo

          $res = Persona::create()->guardarPersonaContacto($personaId, $personaContactoId, $contactoId, $contactoTipoId, $usuarioCreacion);

          if ($res[0]['vout_exito'] == 0) {
            throw new WarningException("Error al guardar el contacto. " . $res[0]['vout_mensaje']);
          } else {
            // insertamos clase contacto a la persona
            $resultado = Persona::create()->savePersonaClasePersona(-3, $contactoId, $usuarioCreacion);
          }
        }
      }
    } else {
      throw new WarningException($responsePersona[0]['vout_mensaje']);
    }
    return $responsePersona;
  }

  public function updatePersona($id, $codigoIdentificacion, $nombre, $apellidoPaterno, $apellidoMaterno, $telefono, $celular, $email, $file, $estado, $empresa, $clase, $usuarioSesion, $listaContactoDetalle, $listaPersonaContactoEliminado, $listaDireccionDetalle, $listaPersonaDireccionEliminado, $codigoSunatId, $codigoSunatId2, $codigoSunatId3, $nombreBCP, $numero_cuenta_bcp, $cci, $listaCentroCostoPersona, $planContableId, $licenciaAuto = null, $licenciaMoto = null)
  {

    //direcciones antes
    $direccion = '';
    $direccionReferencia = '';

    $decode = Util::base64ToImage($file);
    if ($file == null || $file == '') {
      $imagen = null;
    } else {
      $imagen = $codigoIdentificacion . '.jpg';
      file_put_contents(__DIR__ . '/../../vistas/com/persona/imagen/' . $imagen, $decode);
    }

    $responsePersona = Persona::create()->updatePersona($id, $codigoIdentificacion, $nombre, $apellidoPaterno, $apellidoMaterno, $telefono, $celular, $email, $direccion, $direccionReferencia, $imagen, $estado, $usuarioSesion, $codigoSunatId, $codigoSunatId2, $codigoSunatId3, $nombreBCP, $numero_cuenta_bcp, $cci, $planContableId, $licenciaAuto, $licenciaMoto);

    if ($responsePersona[0]["vout_exito"] == 1) {
      if (!ObjectUtil::isEmpty($empresa)) {
        $this->savePersonaEmpresa($empresa, $id, 0);
      }
      if (!ObjectUtil::isEmpty($clase)) {
        $this->savePersonaClasePersona($clase, $id, 0);
      }

      $respuestaEliminarPersonaCentroCosto = Persona::create()->eliminarPersonaCentroCostoXPersonaId($id);
      if ($respuestaEliminarPersonaCentroCosto[0]['vout_exito'] != 1) {
        throw new WarningException("Error al eliminar persona centro costo. " . $respuestaEliminarPersonaCentroCosto[0]['vout_mensaje']);
      }

      if (!ObjectUtil::isEmpty($listaCentroCostoPersona)) {
        foreach ($listaCentroCostoPersona as $indice => $item) {
          $respuestaGuardarPersonaCentroCosto = Persona::create()->guardarPersonaCentroCosto($id, $item['centro_costo_id'], $item['porcentaje'], $usuarioSesion);
          if ($respuestaGuardarPersonaCentroCosto[0]['vout_exito'] != 1) {
            throw new WarningException("Error al guardar persona centro costo. " . $respuestaGuardarPersonaCentroCosto[0]['vout_mensaje']);
          }
        }
      }

      //            //direccion 1
      //            $res=Persona::create()->guardarPersonaDireccion($id,1,$direccion, $usuarioSesion);
      //
      //            // direccion 2 -> campo de referencia
      //            $res2=Persona::create()->guardarPersonaDireccion($id,2,$direccionReferencia, $usuarioSesion);
      //
      //            // direccion 3
      //            Persona::create()->guardarPersonaDireccion($id,3,$direccion3, $usuarioSesion);
      //
      //            // direccion 4
      //            Persona::create()->guardarPersonaDireccion($id,4,$direccion4, $usuarioSesion);
      //guardar persona direccion
      if (!ObjectUtil::isEmpty($listaDireccionDetalle)) {
        foreach ($listaDireccionDetalle as $indice => $item) {
          //listaDireccionDetalle.push([direccionTipo,direccionTipoText,ubigeo,ubigeoText,direccionText,personaDireccionId]);

          $personaDireccionId = $item[5];
          $direccionTipo = $item[1];
          $ubigeoId = $item[2];
          $direccionTexto = $item[4];

          //si existe direccion tipo obtengo el id de lo contrario inserto direccion tipo.
          $direccionTipo = trim($direccionTipo);
          $resDireccionTipo = Persona::create()->obtenerDireccionTipoXDescripcion($direccionTipo);

          if (ObjectUtil::isEmpty($resDireccionTipo)) {
            $resDireccionTipo = Persona::create()->insertarDireccionTipo($direccionTipo, $usuarioSesion);
          }

          $direccionTipoId = $resDireccionTipo[0]['id'];
          // fin direccion tipo

          $res = Persona::create()->guardarPersonaDireccion($id, ($indice + 1), $direccionTexto, $usuarioSesion, $personaDireccionId, $direccionTipoId, $ubigeoId);

          if ($res[0]['vout_exito'] == 0) {
            throw new WarningException("Error al guardar la dirección. " . $res[0]['vout_mensaje']);
          }
        }
      }

      if (!ObjectUtil::isEmpty($listaPersonaDireccionEliminado)) {
        foreach ($listaPersonaDireccionEliminado as $indice => $item) {
          $personaDireccionId = $item[0];

          $res2 = Persona::create()->eliminarPersonaDireccion($personaDireccionId);
        }
      }

      //guardar contacto persona
      if (!ObjectUtil::isEmpty($listaContactoDetalle)) {
        foreach ($listaContactoDetalle as $indice => $item) {
          $personaContactoId = $item[4];
          $contactoId = $item[2];
          $contactoTipo = $item[1];

          //si existe contacto tipo obtengo el id de lo contrario inserto contacto tipo.
          $contactoTipo = trim($contactoTipo);
          $resContactoTipo = Persona::create()->obtenerContactoTipoXDescripcion($contactoTipo);

          if (ObjectUtil::isEmpty($resContactoTipo)) {
            $resContactoTipo = Persona::create()->insertarContactoTipo($contactoTipo, $usuarioSesion);
          }

          $contactoTipoId = $resContactoTipo[0]['id'];
          // fin contacto tipo

          $res = Persona::create()->guardarPersonaContacto($id, $personaContactoId, $contactoId, $contactoTipoId, $usuarioSesion);

          if ($res[0]['vout_exito'] == 0) {
            throw new WarningException("Error al guardar el contacto. " . $res[0]['vout_mensaje']);
          } else {
            // insertamos clase contacto a la persona
            $resultado = Persona::create()->savePersonaClasePersona(-3, $contactoId, $usuarioSesion);
          }
        }
      }

      if (!ObjectUtil::isEmpty($listaPersonaContactoEliminado)) {
        foreach ($listaPersonaContactoEliminado as $indice => $item) {
          $personaContactoId = $item[0];

          $res2 = Persona::create()->eliminarPersonaContacto($personaContactoId);
        }
      }
    } else {
      throw new WarningException($responsePersona[0]["vout_mensaje"]);
    }
    return $responsePersona;
  }

  public function cambiarEstadoPersona($id, $usuarioSesion, $estado)
  {
    //        $responsePersona = $this->obtenerPersonaXId($usuarioSesion);
    $responsePersona = Usuario::create()->getUsuario($usuarioSesion);
    $personaIdSesion = $responsePersona[0]['persona_id'];
    $response = Persona::create()->verificarPersona($id, $personaIdSesion);
    if ($response[0]['vout_exito'] == 0) {
      throw new WarningException($response[0]['vout_mensaje']);
    } else {
      if ($estado == 2) {
        Persona::create()->deletePersonaClasePersona($id);
      }
      return Persona::create()->cambiarEstadoPersona($id, $usuarioSesion, $estado);
    }
  }

  function savePersonaEmpresa($empresa, $personaId, $usuarioCreacion)
  {

    if (!ObjectUtil::isEmpty($empresa) && !ObjectUtil::isEmpty($personaId)) {
      Persona::create()->deletePersonaEmpresa($personaId);

      if (is_array($empresa)) {
        foreach ($empresa as $empresaId) {
          Persona::create()->savePersonaEmpresa($empresaId, $personaId, $usuarioCreacion);
        }
      } else {
        Persona::create()->savePersonaEmpresa($empresa, $personaId, $usuarioCreacion);
      }
    }
  }

  function obtenerPersonaXId($id)
  {
    return Persona::create()->obtenerPersonaGetById($id);
  }

  function savePersonaClasePersona($clase, $personaId, $usuarioCreacion)
  {

    if (!ObjectUtil::isEmpty($clase) && !ObjectUtil::isEmpty($personaId)) {
      Persona::create()->deletePersonaClasePersona($personaId);
      if (is_array($clase)) {
        foreach ($clase as $claseId) {
          Persona::create()->savePersonaClasePersona($claseId, $personaId, $usuarioCreacion);
        }
      } else {
        Persona::create()->savePersonaClasePersona($clase, $personaId, $usuarioCreacion);
      }
    }
  }

  public function obtenerComboPersonaClase()
  {
    return Persona::create()->obtenerComboPersonaClase();
  }

  public function obtenerConfiguracionesPersona($personaId, $personaTipoId, $usuarioId, $empresaId)
  {
    $respuesta = new ObjectUtil();
    //$respuesta->empresa = EmpresaNegocio::create()->getAllEmpresaByUsuarioId($usuarioId);
    $respuesta->empresa = EmpresaNegocio::create()->getEmpresaActivas();
    //        $respuesta->persona_clase = Persona::create()->getAllPersonaClaseByTipo($personaTipoId);
    $respuesta->persona_clase = Persona::create()->obtenerPersonaClaseXpersonaTipoIdXUsuarioId($personaTipoId, $usuarioId);
    $respuesta->persona = ($personaId > 0) ? $this->obtenerPersonaXId($personaId) : null;

    //contactos
    $respuesta->personaNatural = Persona::create()->obtenerPersonasXPersonaTipo(2); // 2-> natural
    $respuesta->contactoTipo = Persona::create()->obtenerContactoTipoActivos();
    $respuesta->personaContacto = ($personaId > 0) ? $this->obtenerPersonaContactoXPersonaId($personaId) : null;

    //direcciones
    $respuesta->direccionTipo = Persona::create()->obtenerDireccionTipoActivos();
    $respuesta->dataUbigeo = Persona::create()->obtenerUbigeoActivos();
    $respuesta->personaDireccion = ($personaId > 0) ? $this->obtenerPersonaDireccionXPersonaId($personaId) : null;

    //persona clase asociada al usuario
    $respuesta->personaClaseXUsuario = Persona::create()->obtenerPersonaClaseXUsuarioId($usuarioId);

    //tablas sunat
    $respuesta->dataSunatDetalle = SunatTablaNegocio::create()->obtenerDetalleXSunatTablaId(35);
    $respuesta->dataSunatDetalle2 = SunatTablaNegocio::create()->obtenerDetalleXSunatTablaId(27);
    $respuesta->dataSunatDetalle3 = SunatTablaNegocio::create()->obtenerDetalleXSunatTablaId(25);
    $respuesta->dataCentroCosto = CentroCostoNegocio::create()->listarCentroCosto(2);
    $respuesta->dataCentroCostoPersona = Persona::create()->obtenerPersonaCentroCostoXPersonaId($personaId);
    $respuesta->cuentaContable = PlanContableNegocio::create()->obtenerXEmpresaId($empresaId);
    return $respuesta;
  }

  // otras funciones de sus tablas pivote

  public function getAllPersonaClaseByTipo($idTipo)
  {
    return Persona::create()->getAllPersonaClaseByTipo($idTipo);
  }

  public function obtenerActivas()
  {
    return Persona::create()->obtenerActivas();
  }

  public function obtenerPersonaClaseActivas()
  {
    return Persona::create()->obtenerPersonaClaseActivas();
  }

  public function obtenerPersonaClaseXUsuarioId($usuarioId)
  {
    return Persona::create()->obtenerPersonaClaseXUsuarioId($usuarioId);
  }

  //Nueva funcionalidad
  public function configuracionesInicialesPersonaListar($usuarioId)
  {
    $respuesta = new stdClass();
    //$respuesta->persona_clase = $this->obtenerPersonaClaseActivas();
    $respuesta->persona_clase = $this->obtenerPersonaClaseXUsuarioId($usuarioId);
    $respuesta->persona_tipo = $this->getAllPersonaTipo();
    return $respuesta;
  }

  public function obtenerComboPersonaXPersonaClaseId($personaClaseId, $parse = false)
  {
    $personas = Persona::create()->obtenerComboPersonaXPersonaClaseId($personaClaseId);
    if ($parse) {
      $data["-1"] = "Ninguno";
      foreach ($personas as $persona) {
        $data[$persona["persona_nombre"]] = trim($persona["persona_nombre"]);
      }
      return $data;
    }
    return $personas;
  }

  //obtener datos desde consulta ruc sunat

  public function getDatosProveedor($ruc)
  {
    $data = ConsultaWs::create()->obtenerConsultaRucSunat($ruc);
    if (!ObjectUtil::isEmpty($data)) {
      $data = $data[0];
      $data['NúmerodeRUC']['razon_social'] = $data['razonSocial'];
      //            $data['razonSocial'] = @trim(substr($data['RUC'], strpos($data['RUC'], "-") + 2));
      $data['departamento'] = $data['dpto'];
      $data['provincia'] = $data['prov'];
      $data['distrito'] = $data['dist'];
      $data['ubigeo'] = "";
    }
    return $data;
  }

  public function getDatosProveedorOld($ruc)
  {
    try {
      $res = $this->ComprobarRUC($ruc);
      $data = array();
      foreach ($res as $par) {
        $name = trim(str_replace(" ", "", str_replace(":", "", $par["name"])));
        $value = trim($par["value"]);
        if (strlen($value) > 3 && strpos($value, "-") !== false) {
          $value = explode("-", $value);
          $value = trim($value[0]) . " - " . trim($value[1]);
        }
        $data[$name] = $value;
      }
    } catch (Exception $ex) {
      $this->setMensajeEmergente($ex->getMessage(), '', Configuraciones::MENSAJE_ERROR);
      //            throw $ex;
    }

    $data['CondicióndelContribuyente'] = @trim($data['Condición']);
    $data['DireccióndelDomicilioFiscal'] = @trim($data['DomicilioFiscal']);
    $data['EstadodelContribuyente'] = @trim($data['Estado']);
    $data['razonSocial'] = @trim(substr($data['RUC'], strpos($data['RUC'], "-") + 2));
    return $data;
  }

  public function ComprobarRUC($ruc)
  {
    try {
      if (strlen($ruc) != 11) {
        throw new Exception("El número de ruc sólo puede contener once digitos.");
      }
      $cookie_jar = tempnam('/tmp', 'cookie.txt');
      $referer = "http://www.sunat.gob.pe/descarga/AgentRet/AgenRet1.html";
      $useragent = "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.111 Safari/537.36";
      $rand = $this->getRandomRazonSocial($cookie_jar, $referer, $useragent);

      $response = $this->getHTML($ruc, $rand, $cookie_jar, $referer, $useragent);
      $dom = new DOMDocument();
      @$dom->loadHTML($response["html"]);
      $dom->preserveWhiteSpace = false;
      $tables = $dom->getElementsByTagName('table');

      if ($response["status"] != 200) {
        throw new Exception("Hubo un error al tratar de conectar al servidor de la SUNAT.");
      }
      $rows = $tables->item(3)->getElementsByTagName('tr');
      foreach ($rows as $row) {
        $cols = $row->getElementsByTagName('td');
        if (!is_object($cols->item(1))) {
          throw new Exception("RUC no válido");
        }
        $name = utf8_encode(trim(mb_convert_encoding($cols->item(0)->nodeValue, 'ISO-8859-1', 'utf-8')));
        $valu = utf8_encode(trim(mb_convert_encoding($cols->item(1)->nodeValue, 'ISO-8859-1', 'utf-8')));
        $res[] = array(
          "name" => $name,
          "value" => $valu
        );
      }
      unlink($cookie_jar);
      return $res;
    } catch (Exception $ex) {
      throw $ex;
    }
  }

  public function getRandomRazonSocial($cookie_jar, $referer, $useragent)
  {
    $razonSocial = "SUPERINTENDENCIA NACIONAL DE ADUANAS Y DE ADMINISTRACION TRIBUTARIA - SUNAT";
    $razonSocial = str_replace(" ", "%20", $razonSocial);

    $url = "https://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias?accion=consPorRazonSoc&razSoc=$razonSocial";
    $ch = curl_init();
    $timeout = 8;
    //        $fp = fopen($urlCaptcha, 'w+');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // enable if you want
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_LOW_SPEED_LIMIT, 2);
    curl_setopt($ch, CURLOPT_LOW_SPEED_TIME, $timeout);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);
    curl_setopt($ch, CURLOPT_REFERER, $referer);
    curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
    $html = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_status == 200) {
      $dom = new DOMDocument();
      @$dom->loadHTML($html);
      $dom->preserveWhiteSpace = false;
      $xp = new DOMXpath($dom);
      $nodes = $xp->query('//input[@name="numRnd"]');
      if ($nodes->length > 0) {
        $rnd = $nodes->item(0)->getAttribute('value');
      }
    }
    return $rnd;
  }

  public function getRandom($cookie_jar, $referer, $useragent)
  {
    # Get captcha with POST method
    //        $url = "http://www.sunat.gob.pe/cl-ti-itmrconsruc/captcha";
    $url = "https://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/captcha?accion=random";
    $ch = curl_init();
    $timeout = 8;
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_LOW_SPEED_LIMIT, 2);
    curl_setopt($ch, CURLOPT_LOW_SPEED_TIME, $timeout);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);
    //        curl_setopt($ch, CURLOPT_POST, 1);
    //        curl_setopt($ch, CURLOPT_POSTFIELDS, "accion=random");
    curl_setopt($ch, CURLOPT_REFERER, $referer);
    curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
    $rnd = curl_exec($ch);
    curl_close($ch);
    return $rnd;
  }

  public function getHTML($ruc_nro, $rnd, $cookie_jar, $referer, $useragent)
  {
    //        $url = "http://www.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias";
    $url = "https://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias";
    $ch = curl_init();
    $timeout = 8;
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_LOW_SPEED_LIMIT, 2); // bytes per second
    curl_setopt($ch, CURLOPT_LOW_SPEED_TIME, $timeout); // seconds
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "accion=consPorRuc&nroRuc=$ruc_nro&actReturn=1&numRnd=$rnd");
    curl_setopt($ch, CURLOPT_REFERER, $referer);
    curl_setopt($ch, CURLOPT_USERAGENT, $useragent); // simulate a real browser
    $html = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return array("html" => $html, "status" => $http_status);
  }

  public function getRandom2($cookie_jar, $referer, $useragent)
  {
    # Get captcha with POST method
    $url = "http://www.sunat.gob.pe/cl-ti-itmrconsruc/captcha";
    $ch = curl_init();
    $timeout = 8;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_LOW_SPEED_LIMIT, 2);
    curl_setopt($ch, CURLOPT_LOW_SPEED_TIME, $timeout);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "accion=random");
    curl_setopt($ch, CURLOPT_REFERER, $referer);
    curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
    $rnd = curl_exec($ch);
    curl_close($ch);
    return $rnd;
  }

  public function getHTML2($ruc_nro, $rnd, $cookie_jar, $referer, $useragent)
  {
    $url = "http://www.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias";
    $ch = curl_init();
    $timeout = 8;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_LOW_SPEED_LIMIT, 2); // bytes per second
    curl_setopt($ch, CURLOPT_LOW_SPEED_TIME, $timeout); // seconds
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "accion=consPorRuc&nroRuc=$ruc_nro&actReturn=1&numRnd=$rnd");
    curl_setopt($ch, CURLOPT_REFERER, $referer);
    curl_setopt($ch, CURLOPT_USERAGENT, $useragent); // simulate a real browser
    $html = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return array("html" => $html, "status" => $http_status);
  }

  public function obtenerPersonaDireccionXPersonaId($personaId)
  {
    return Persona::create()->obtenerPersonaDireccionXPersonaId($personaId);
  }

  public function obtenerPersonaPerfilVendedor()
  {
    return Persona::create()->obtenerPersonaPerfilVendedor();
  }

  public function buscarPersonaXNombreXDocumento($opcionId, $busqueda)
  {
    return Persona::create()->buscarPersonaXNombreXDocumento($opcionId, $busqueda);
  }

  public function obtenerPersonasMayorMovimiento($opcionId)
  {
    return Persona::create()->obtenerPersonasMayorMovimiento($opcionId);
  }

  public function obtenerPersonasMayorOperacion($opcionId)
  {
    return Persona::create()->obtenerPersonasMayorOperacion($opcionId);
  }

  public function buscarPersonaOperacionXNombreXDocumento($opcionId, $busqueda)
  {
    return Persona::create()->buscarPersonaOperacionXNombreXDocumento($opcionId, $busqueda);
  }

  public function obtenerActivasXDocumentoTipoId($documentoTipoId)
  {
    return Persona::create()->obtenerActivasXDocumentoTipoId($documentoTipoId);
  }

  public function buscarPersonasXDocumentoTipoXValor($documentoTipoArray, $valor)
  {
    return Persona::create()->buscarPersonasXDocumentoTipoXValor(Util::fromArraytoString($documentoTipoArray), $valor);
  }

  public function buscarPersonasXDocumentoPagar($empresaId, $tipo, $tipoProvisionPago, $busqueda)
  {
    return Persona::create()->buscarPersonasXDocumentoPagar($empresaId, $tipo, $tipoProvisionPago, $busqueda);
  }

  public function buscarPersonasXDocumentoPago($empresaId, $tipo, $tipoProvisionPago, $busqueda)
  {
    return Persona::create()->buscarPersonasXDocumentoPago($empresaId, $tipo, $tipoProvisionPago, $busqueda);
  }

  public function buscarPersonasXDocumentoPagado($empresaId, $tipo, $tipoProvisionPago, $busqueda)
  {
    return Persona::create()->buscarPersonasXDocumentoPagado($empresaId, $tipo, $tipoProvisionPago, $busqueda);
  }

  public function buscarPersonaListarXNombreXDocumento($busqueda, $usuarioId = 1)
  {
    return Persona::create()->buscarPersonaListarXNombreXDocumento($busqueda, $usuarioId);
  }

  public function buscarPersonaClaseXDescripcion($busqueda, $usuarioId = 1)
  {
    return Persona::create()->buscarPersonaClaseXDescripcion($busqueda, $usuarioId);
  }

  public function obtenerPersonasXPersonaTipo($personaTipoId)
  {
    return Persona::create()->obtenerPersonasXPersonaTipo($personaTipoId);
  }

  public function obtenerPersonaContactoXPersonaId($personaId)
  {
    return Persona::create()->obtenerPersonaContactoXPersonaId($personaId);
  }

  public function obtenerPersonaClaseXpersonaTipoIdXUsuarioId($personaTipoId, $usuarioId)
  {
    return Persona::create()->obtenerPersonaClaseXpersonaTipoIdXUsuarioId($personaTipoId, $usuarioId);
  }

  public function obtenerActivasXDocumentoTipoIdXUsuarioId($documentoTipoId, $usuarioId)
  {
    return Persona::create()->obtenerActivasXDocumentoTipoIdXUsuarioId($documentoTipoId, $usuarioId);
  }

  public function obtenerSunatTablaDetalleRelacionXTipoXSunatTablaDetalleId($tipo, $sunatTablaDetalleId)
  {
    return Persona::create()->obtenerSunatTablaDetalleRelacionXTipoXSunatTablaDetalleId($tipo, $sunatTablaDetalleId);
  }

  public function obtenerComboPersonaProveedores()
  {
    return Persona::create()->obtenerComboPersonaProveedores();
  }

  public function validarSimilitud($id, $nombre, $apellidoPaterno)
  {
    return Persona::create()->validarSimilitud($id, $nombre, $apellidoPaterno);
  }

  //busqueda de personas  en modal de relacionar

  public function buscarPersonasXDocumentoOperacion($documentoTipoArray, $valor)
  {
    return Persona::create()->buscarPersonasXDocumentoOperacion(Util::fromArraytoString($documentoTipoArray), $valor);
  }

  public function obtenerPersonaXOpcionMovimiento($opcionId)
  {
    return Persona::create()->obtenerPersonaXOpcionMovimiento($opcionId);
  }

  public function obtenerPersonasMayorDocumentosPPagoXTipos($tipos)
  {
    return Persona::create()->obtenerPersonasMayorDocumentosPPagoXTipos($tipos);
  }

  public function obtenerPersonaXCodigoIdentificacion($codigoIdentificacion)
  {
    return Persona::create()->obtenerPersonaXCodigoIdentificacion($codigoIdentificacion);
  }

  public function buscarPersonaDocumentoEarXNombreXDocumento($opcionId, $busqueda)
  {
    return Persona::create()->buscarPersonaDocumentoEarXNombreXDocumento($opcionId, $busqueda);
  }

  public function obtenerUbigeoXId($ubigeoId)
  {
    return Persona::create()->obtenerUbigeoXId($ubigeoId);
  }

  public function obtenerCorreosEFACT()
  {
    return Persona::create()->obtenerCorreosEFACT();
  }

  public function obtenerCuentaContableXPersonaId($personaId)
  {
    return Persona::create()->obtenerCuentaContableXPersonaId($personaId);
  }

  public function obtenerPersonaCentroCostoXCodigoIdentificacion($codigoIdentificacion)
  {
    return Persona::create()->obtenerPersonaCentroCostoXCodigoIdentificacion($codigoIdentificacion);
  }
  public function obtenerPersonaConductor()
  {
    return Persona::create()->obtenerPersonaConductor();
  }
  function obtenerPersonaXLicenciaConducir($licenciaConducir)
  {
    return Persona::create()->obtenerPersonaGetByLicenciaConducir($licenciaConducir);
  }



  function obtenerTipoDocumento()
  {
    $datos= Persona::create()->obtenerTipoDocumento();
    return $datos;
  }

  public function insertArchivo($usuarioId,$personaId,$personaTipoArchivo,$inputFile,$fileName ) {


    list($type, $imageData) = explode(';', $inputFile);
    list(, $imageData) = explode(',', $imageData);
    list(, $tipo) = explode('/', $type);
    $extension=$tipo;
    // Decodificar los datos base64
    $imageData = base64_decode($imageData);

    // Crear un nombre único para la imagen
    $imageName = uniqid() . '.'.$extension;

    // Especificar la ruta donde se guardará la imagen
    $imagePath = '../../vistas/com/persona/documentos/' . $imageName;

    // Guardar la imagen en el servidor
    file_put_contents($imagePath, $imageData);


    $Archivo=Persona::create()->insertArchivo($usuarioId,$personaId,$personaTipoArchivo,$imageName,$fileName );
    return $Archivo;
  }

  public function obtenerArchivos($usuarioId,$personaId){


        $dataArchivos=Persona::create()->obtenerArchivos($personaId);
      return $dataArchivos;

  }

  public function eliminarArchivos($usuarioId ,$id ,$archivo ){
    if($archivo==null){
    }
    else {
      $ruta='../../vistas/com/persona/documentos/'.$archivo;
     
    }
    $dataArchivos= Persona::create()->eliminarArchivos($id);
    return $dataArchivos;
  }



  // planta obtenerArchivosPlanta

  public function obtenerArchivosPlanta($usuarioId,$personaId){


    $dataArchivosPlanta=Persona::create()->obtenerArchivosPlanta($personaId);
    return $dataArchivosPlanta;

  }

  public function insertTipoDocumentoPlanta($usuarioId,$nombreDocumento)
  {
    $responseTipoDocumento = Persona::create()->insertTipoDocumentoPlanta($usuarioId,$nombreDocumento);
    
    return $responseTipoDocumento;
  }
  public function insertTipoDocumentoPLantaXPersona($usuarioId,$tipoDocumentoPlanta,$personaId,$inputFile,$fileName){
    
    
    list($type, $imageData) = explode(';', $inputFile);
    list(, $imageData) = explode(',', $imageData);
    list(, $tipo) = explode('/', $type);
    $extension = pathinfo($fileName, PATHINFO_EXTENSION);
    // Decodificar los datos base64
    $imageData = base64_decode($imageData);

    // Crear un nombre único para la imagen
    $imageName = uniqid() . '.'.$extension;

    // Especificar la ruta donde se guardará la imagen
    $imagePath = '../../vistas/com/persona/documentosPlanta/' . $imageName;

    // Guardar la imagen en el servidor
    file_put_contents($imagePath, $imageData);

    
    $responseDataDocumentoPlanta = Persona::create()->insertTipoDocumentoPLantaXPersona($usuarioId,$tipoDocumentoPlanta,$personaId,$imageName,$fileName);
    if ($responseDataDocumentoPlanta[0]['vout_exito'] == 0) {
      unlink($imagePath);
      throw new WarningException("Error al guardar la dirección. " . $responseDataDocumentoPlanta[0]['vout_mensaje']);
    }
    
    return $responseDataDocumentoPlanta;
  }

  public function eliminarTipoDocumentoPLantaXPersona($usuarioId ,$id  ){
   
    $dataArchivos= Persona::create()->eliminarTipoDocumentoPLantaXPersona($id);
    return $dataArchivos;
  }
  public function obtenerPersonaActivoXStringBusqueda($stringPersona, $personaId = null)
  {
      return Persona::create()->obtenerPersonaActivoXStringBusqueda($stringPersona, $personaId);
  }



  
}
