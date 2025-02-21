<?php

require_once __DIR__ . "/../core/ModeloBase.php";
require_once __DIR__ . "/../enumeraciones/EstadoGenerico.php";

/**
 * Description of Persona
 *
 * @author CHL
 */
class Persona extends ModeloBase {

    /**
     * 
     * @return Persona
     */
    static function create() {
        return parent::create();
    }

    public function getAllPersonaClase() {
        $this->commandPrepare("sp_persona_clase_getAll");
        return $this->commandGetData();
    }

    public function getAllPersonaTipo() {
        $this->commandPrepare("sp_persona_tipo_getAll");
        return $this->commandGetData();
    }

    public function getDataPersona($usuarioId = 1) {
        $this->commandPrepare("sp_persona_excel_getAll");
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        return $this->commandGetData();
    }

    public function insertPersonaClase($descripcion, $estado, $usuarioCreacion) {
        $this->commandPrepare("sp_persona_clase_insert");
        $this->commandAddParameter(":vin_descripcion", $descripcion);
        $this->commandAddParameter(":vin_estado", $estado);
        $this->commandAddParameter(":vin_usuario_creacion", $usuarioCreacion);
        return $this->commandGetData();
    }

    public function importaPersonaXML($xml, $usuarioCreacion, $empresaId) {
        $this->commandPrepare("sp_persona_insert_xml");
        $this->commandAddParameter(":vin_XML", $xml);
        $this->commandAddParameter(":vin_usu_creacion", $usuarioCreacion);
        $this->commandAddParameter(":vin_empresa", $empresaId);
        return $this->commandGetData();
    }

    public function updatePersonaClase($id, $descripcion, $estado) {
        $this->commandPrepare("sp_persona_clase_update");
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_descripcion", $descripcion);
        $this->commandAddParameter(":vin_estado", $estado);
        return $this->commandGetData();
    }

    public function cambiarEstadoPersonaClase($id, $estado = 0) {
        $this->commandPrepare("sp_persona_clase_cambiarEstado");
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_estado", $estado);
        return $this->commandGetData();
    }

    public function verificarPersona($id, $usuarioId) {
        $this->commandPrepare("sp_persona_verificar");
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        return $this->commandGetData();
    }

    public function savePersonaClaseTipo($tipoId, $personaClaseId, $usuarioCreacion) {
        $this->commandPrepare("sp_persona_clase_tipo_save");
        $this->commandAddParameter(":vin_tipo_id", $tipoId);
        $this->commandAddParameter(":vin_persona_clase_id", $personaClaseId);
        $this->commandAddParameter(":vin_usuario_creacion", $usuarioCreacion);
        return $this->commandGetData();
    }

    public function deletePersonaClaseTipo($personaClaseId) {
        $this->commandPrepare("sp_persona_clase_tipo_delete");
        $this->commandAddParameter(":vin_persona_clase_id", $personaClaseId);
        return $this->commandGetData();
    }

    // para la tabla persona
    public function getAllPersona($nombres, $codigo, $tipoPersona, $clasePersona, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start, $usuarioId = 1) {
        $this->commandPrepare("sp_persona_obtenerXCriterios");
        $this->commandAddParameter(":vin_nombres", $nombres);
        $this->commandAddParameter(":vin_codigo", $codigo);
        $this->commandAddParameter(":vin_tipo_persona", $tipoPersona);
        $this->commandAddParameter(":vin_clase_persona", $clasePersona);
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_limite", $elemntosFiltrados);
        $this->commandAddParameter(":vin_tamanio", $start);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        return $this->commandGetData();
    }



    public function getCantidadAllPersona($nombres, $codigo, $tipoPersona, $clasePersona, $columnaOrdenar, $formaOrdenar, $usuarioId) {
        $this->commandPrepare("sp_persona_contador_consulta");
        $this->commandAddParameter(":vin_nombres", $nombres);
        $this->commandAddParameter(":vin_codigo", $codigo);
        $this->commandAddParameter(":vin_tipo_persona", $tipoPersona);
        $this->commandAddParameter(":vin_clase_persona", $clasePersona);
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        return $this->commandGetData();
    }
    

    public function insertPersona($tipo, $codigoIdentificacion, $nombre, $apellidoPaterno, $apellidoMaterno, $telefono, $celular, $email, $direccion, $direccionReferencia, $file, $estado, $usuarioCreacion, $codigoSunatId=null, $codigoSunatId2=null, $codigoSunatId3=null, $nombreBCP=null, $numero_cuenta_bcp=null, $cci=null, $planContableId=null, $licenciaAuto=null, $licenciaMoto=null,$firma=null,$zona=null) {
        $this->commandPrepare("sp_persona_insert");
        $this->commandAddParameter(":vin_tipo", $tipo);
        $this->commandAddParameter(":vin_codigo_identificacion", $codigoIdentificacion);
        $this->commandAddParameter(":vin_nombre", $nombre);
        $this->commandAddParameter(":vin_paterno", $apellidoPaterno);
        $this->commandAddParameter(":vin_materno", $apellidoMaterno);
        $this->commandAddParameter(":vin_telefono", $telefono);
        $this->commandAddParameter(":vin_celular", $celular);
        $this->commandAddParameter(":vin_email", $email);
        $this->commandAddParameter(":vin_direccion", $direccion);
        $this->commandAddParameter(":vin_direccion_referencia", $direccionReferencia);
        $this->commandAddParameter(":vin_usuario_creacion", $usuarioCreacion);
        $this->commandAddParameter(":vin_estado", $estado);
        $this->commandAddParameter(":vin_file", $file);
        $this->commandAddParameter(":vin_sunat_tabla_detalle_id", $codigoSunatId);
        $this->commandAddParameter(":vin_sunat_tabla_detalle_id2", $codigoSunatId2);
        $this->commandAddParameter(":vin_sunat_tabla_detalle_id3", $codigoSunatId3);
        $this->commandAddParameter(":nombre_bcp", $nombreBCP);
        $this->commandAddParameter(":vin_numero_cuenta_bcp", $numero_cuenta_bcp);
        $this->commandAddParameter(":vin_cci", $cci);
        $this->commandAddParameter(":vin_plan_contable_id", $planContableId);
        $this->commandAddParameter(":vin_num_licencia_conducir_auto", $licenciaAuto);
        $this->commandAddParameter(":vin_num_licencia_conducir_moto", $licenciaMoto);
        $this->commandAddParameter(":vin_firma", $firma);
        $this->commandAddParameter(":vin_zona", $zona);
        return $this->commandGetData();
    }

    public function insertPersonaMinero($tipo, $codigoIdentificacion, $nombre, $apellidoPaterno, $apellidoMaterno, 
    $telefono, $celular, $email, $direccion, $direccionReferencia, $file, $estado, $usuarioCreacion, $codigoSunatId=null,
     $codigoSunatId2=null, $codigoSunatId3=null, $nombreBCP=null, $numero_cuenta_bcp=null, $cci=null,
      $planContableId=null, $licenciaAuto=null, $licenciaMoto=null,$firma=null,$zona=null,
      $lugarNacimiento=null,$fechaNacimiento=null,$estadoCivil=null,$hijo=null,$estatura=null,$madre=null,$padre=null,
      $restriccion=null,$sexo=null,$codigo=null,$personaPadre=null,$tipoDNI=null
      ) {
        $this->commandPrepare("sp_persona_minero_insert");
        $this->commandAddParameter(":vin_tipo", $tipo);
        $this->commandAddParameter(":vin_codigo_identificacion", $codigoIdentificacion);
        $this->commandAddParameter(":vin_nombre", $nombre);
        $this->commandAddParameter(":vin_paterno", $apellidoPaterno);
        $this->commandAddParameter(":vin_materno", $apellidoMaterno);
        $this->commandAddParameter(":vin_telefono", $telefono);
        $this->commandAddParameter(":vin_celular", $celular);
        $this->commandAddParameter(":vin_email", $email);
        $this->commandAddParameter(":vin_direccion", $direccion);
        $this->commandAddParameter(":vin_direccion_referencia", $direccionReferencia);
        $this->commandAddParameter(":vin_usuario_creacion", $usuarioCreacion);
        $this->commandAddParameter(":vin_estado", $estado);
        $this->commandAddParameter(":vin_file", $file);
        $this->commandAddParameter(":vin_sunat_tabla_detalle_id", $codigoSunatId);
        $this->commandAddParameter(":vin_sunat_tabla_detalle_id2", $codigoSunatId2);
        $this->commandAddParameter(":vin_sunat_tabla_detalle_id3", $codigoSunatId3);
        $this->commandAddParameter(":nombre_bcp", $nombreBCP);
        $this->commandAddParameter(":vin_numero_cuenta_bcp", $numero_cuenta_bcp);
        $this->commandAddParameter(":vin_cci", $cci);
        $this->commandAddParameter(":vin_plan_contable_id", $planContableId);
        $this->commandAddParameter(":vin_num_licencia_conducir_auto", $licenciaAuto);
        $this->commandAddParameter(":vin_num_licencia_conducir_moto", $licenciaMoto);
        $this->commandAddParameter(":vin_firma", $firma);
        $this->commandAddParameter(":vin_zona", $zona);
        $this->commandAddParameter(":vin_lugarNacimiento", $lugarNacimiento);
        $this->commandAddParameter(":vin_fechaNacimiento", $fechaNacimiento);
        $this->commandAddParameter(":vin_estadoCivil", $estadoCivil);
        $this->commandAddParameter(":vin_hijo", $hijo);
        $this->commandAddParameter(":vin_estatura", $estatura);
        $this->commandAddParameter(":vin_madre", $madre);
        $this->commandAddParameter(":vin_padre", $padre);
        $this->commandAddParameter(":vin_restriccion", $restriccion);
        $this->commandAddParameter(":vin_sexo", $sexo);
        $this->commandAddParameter(":vin_codigo", $codigo);
        $this->commandAddParameter(":vin_persona_padre", $personaPadre);
        $this->commandAddParameter(":vin_tipo_dni", $tipoDNI);
        return $this->commandGetData();
    }


    public function guardarPersonaDireccion($personaId, $prioridad, $direccion, $usuarioCreacion, $personaDireccionId = null, $direccionTipoId = null, $ubigeoId = null) {

        $this->commandPrepare("sp_persona_direccion_guardar");
        $this->commandAddParameter(":vin_persona_id", $personaId);
        $this->commandAddParameter(":vin_prioridad", $prioridad);
        $this->commandAddParameter(":vin_direccion", $direccion);
        $this->commandAddParameter(":vin_usuario_creacion", $usuarioCreacion);
        $this->commandAddParameter(":vin_persona_direccion_id", $personaDireccionId);
        $this->commandAddParameter(":vin_direccion_tipo_id", $direccionTipoId);
        $this->commandAddParameter(":vin_ubigeo_id", $ubigeoId);
        return $this->commandGetData();
    }

    public function guardarPersonaDireccionMinapp($personaId, $prioridad, $direccion, $usuarioCreacion, $direccionTipoId = null, $ubigeoId = null,$departamento,$provincia,
    $distrito,$ubigeo) {

        $this->commandPrepare("sp_persona_direccion_reinfo_guardar");
        $this->commandAddParameter(":vin_persona_id", $personaId);
        $this->commandAddParameter(":vin_prioridad", $prioridad);
        $this->commandAddParameter(":vin_direccion", $direccion);
        $this->commandAddParameter(":vin_usuario_creacion", $usuarioCreacion);
        $this->commandAddParameter(":vin_direccion_tipo_id", $direccionTipoId);
        $this->commandAddParameter(":vin_ubigeo_id", $ubigeoId);
        $this->commandAddParameter(":vin_departamento", $departamento);
        $this->commandAddParameter(":vin_provincia", $provincia);
        $this->commandAddParameter(":vin_distrito", $distrito);
        $this->commandAddParameter(":vin_ubigeo", $ubigeo);
        return $this->commandGetData();
    }

    

    public function updatePersona($id, $codigoIdentificacion, $nombre, $apellidoPaterno, $apellidoMaterno, $telefono, $celular, $email, $direccion, $direccionReferencia, $file, $estado, $usuarioSesion, $codigoSunatId, $codigoSunatId2, $codigoSunatId3, $nombreBCP, $numero_cuenta_bcp, $cci,$planContableId, $licenciaAuto, $licenciaMoto,$firma=null) {

        $this->commandPrepare("sp_persona_update");
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_codigo_identificacion", $codigoIdentificacion);
        $this->commandAddParameter(":vin_nombre", $nombre);
        $this->commandAddParameter(":vin_paterno", $apellidoPaterno);
        $this->commandAddParameter(":vin_materno", $apellidoMaterno);
        $this->commandAddParameter(":vin_telefono", $telefono);
        $this->commandAddParameter(":vin_celular", $celular);
        $this->commandAddParameter(":vin_email", $email);
        $this->commandAddParameter(":vin_direccion", $direccion);
        $this->commandAddParameter(":vin_ref_direccion", $direccionReferencia);
        $this->commandAddParameter(":vin_estado", $estado);
        $this->commandAddParameter(":vin_file", $file);
        $this->commandAddParameter(":vin_usuario_sesion", $usuarioSesion);
        $this->commandAddParameter(":vin_sunat_tabla_detalle_id", $codigoSunatId);
        $this->commandAddParameter(":vin_sunat_tabla_detalle_id2", $codigoSunatId2);
        $this->commandAddParameter(":vin_sunat_tabla_detalle_id3", $codigoSunatId3);
        $this->commandAddParameter(":vin_nombre_bcp", $nombreBCP);
        $this->commandAddParameter(":vin_numero_cuenta_bcp", $numero_cuenta_bcp);
        $this->commandAddParameter(":vin_cci", $cci);
        $this->commandAddParameter(":vin_plan_contable_id", $planContableId);
        $this->commandAddParameter(":vin_num_licencia_conducir_auto", $licenciaAuto);
        $this->commandAddParameter(":vin_num_licencia_conducir_moto", $licenciaMoto);
        $this->commandAddParameter(":vin_firma", $firma);
        return $this->commandGetData();
    }

    public function cambiarEstadoPersona($id, $usuarioSesion, $estado = 0) {
        $this->commandPrepare("sp_persona_cambiarEstado");
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_estado", $estado);
        $this->commandAddParameter(":vin_usuario_sesion", $usuarioSesion);
        return $this->commandGetData();
    }

    public function obtenerPersonaXId($id = 0) {
        $this->commandPrepare("sp_persona_obtenerXId");
        $this->commandAddParameter(":vin_id", $id);
        return $this->commandGetData();
    }

    public function obtenerPersonaGetById($id) {
        $this->commandPrepare("sp_persona_getById");
        $this->commandAddParameter(":vin_id", $id);
        return $this->commandGetData();
    }

    public function obtenerPersonaGetByIdAll($id) {
        $this->commandPrepare("sp_persona_obtenerXIdAll");
        $this->commandAddParameter(":vin_id", $id);
        return $this->commandGetData();
    }

    
    // persona - empresa

    public function deletePersonaEmpresa($personaId) {
        $this->commandPrepare("sp_persona_empresa_delete");
        $this->commandAddParameter(":vin_persona_id", $personaId);
        return $this->commandGetData();
    }

    public function savePersonaEmpresa($empresaId, $personaId, $usuarioCreacion) {
        $this->commandPrepare("sp_persona_empresa_save");
        $this->commandAddParameter(":vin_empresa_id", $empresaId);
        $this->commandAddParameter(":vin_persona_id", $personaId);
        $this->commandAddParameter(":vin_usuario_creacion", $usuarioCreacion);
        return $this->commandGetData();
    }

    // persona clase persna
    public function deletePersonaClasePersona($personaId) {
        $this->commandPrepare("sp_persona_clase_persona_delete");
        $this->commandAddParameter(":vin_persona_id", $personaId);
        return $this->commandGetData();
    }

    public function savePersonaClasePersona($claseId, $personaId, $usuarioCreacion) {
        $this->commandPrepare("sp_persona_clase_persona_save");
        $this->commandAddParameter(":vin_clase_id", $claseId);
        $this->commandAddParameter(":vin_persona_id", $personaId);
        $this->commandAddParameter(":vin_usuario_creacion", $usuarioCreacion);
        return $this->commandGetData();
    }

    public function obtenerComboPersonaClase() {
        $this->commandPrepare("sp_persona_clase_combo");
        return $this->commandGetData();
    }

    // dfunciones tablas pivote
    public function getAllPersonaClaseByTipo($idTipo) {
        $this->commandPrepare("sp_persona_clase_getAllByTipo");
        $this->commandAddParameter(":vin_id_tipo", $idTipo);
        return $this->commandGetData();
    }

    public function obtenerActivas() {
        $this->commandPrepare("sp_persona_obtenerActivas");
        return $this->commandGetData();
    }

    public function obtenerPersonaClaseActivas() {
        $this->commandPrepare("sp_persona_clase_obtenerActivas");
        return $this->commandGetData();
    }

    public function obtenerComboPersonaXPersonaClaseId($personaClaseId) {
        $this->commandPrepare("sp_persona_obtener_XPersonaClaseId");
        $this->commandAddParameter(":vin_persona_clase_id", $personaClaseId);
        return $this->commandGetData();
    }

    public function obtenerPersonaDireccionXPersonaId($personaId) {
        $this->commandPrepare("sp_persona_direccion_obtenerXPersonaId");
        $this->commandAddParameter(":vin_persona_id", $personaId);
        return $this->commandGetData();
    }

    public function obtenerPersonaPerfilVendedor() {
        $this->commandPrepare("sp_persona_obtenerPerfilVendedor");
        return $this->commandGetData();
    }

    public function buscarPersonaXNombreXDocumento($opcionId, $busqueda) {
        $this->commandPrepare("sp_persona_buscarXNombreXDocumento");
        $this->commandAddParameter(":vin_opcion_id", $opcionId);
        $this->commandAddParameter(":vin_busqueda", $busqueda);
        return $this->commandGetData();
    }

    public function obtenerPersonasMayorMovimiento($opcionId) {
        $this->commandPrepare("sp_persona_obtenerXMayorMovimiento");
        $this->commandAddParameter(":vin_opcion_id", $opcionId);
        return $this->commandGetData();
    }

    public function obtenerPersonasMayorOperacion($opcionId) {
        $this->commandPrepare("sp_persona_obtenerXMayorOperacion");
        $this->commandAddParameter(":vin_opcion_id", $opcionId);
        return $this->commandGetData();
    }

    public function buscarPersonaOperacionXNombreXDocumento($opcionId, $busqueda) {
        $this->commandPrepare("sp_personaOperacion_buscarXNombreXDocumento");
        $this->commandAddParameter(":vin_opcion_id", $opcionId);
        $this->commandAddParameter(":vin_busqueda", $busqueda);
        return $this->commandGetData();
    }

    public function obtenerActivasXDocumentoTipoId($documentoTipoId) {
        $this->commandPrepare("sp_persona_obtenerActivasXDocumentoTipoId");
        $this->commandAddParameter(":vin_documento_tipo_id", $documentoTipoId);
        return $this->commandGetData();
    }

    public function buscarPersonasXDocumentoTipoXValor($documentoTipoIdStringArray, $valor) {
        $this->commandPrepare("sp_persona_buscarXDocumentoTipoXNombre");
        $this->commandAddParameter(":vin_tipo_documento_set", $documentoTipoIdStringArray);
        $this->commandAddParameter(":vin_valor", $valor);
        return $this->commandGetData();
    }

    public function buscarPersonasXDocumentoPagar($empresaId, $tipo, $tipoProvisionPago, $busqueda) {
        $this->commandPrepare("sp_persona_buscarXDocumentoPagar");
        $this->commandAddParameter(":vin_empresa_id", $empresaId);
        $this->commandAddParameter(":vin_tipo", $tipo);
        $this->commandAddParameter(":vin_tipoProvisionPago", $tipoProvisionPago);
        $this->commandAddParameter(":vin_busqueda", $busqueda);
        return $this->commandGetData();
    }

    public function buscarPersonasXDocumentoPago($empresaId, $tipo, $tipoProvisionPago, $busqueda) {
        $this->commandPrepare("sp_persona_buscarXDocumentoPago");
        $this->commandAddParameter(":vin_empresa_id", $empresaId);
        $this->commandAddParameter(":vin_tipo", $tipo);
        $this->commandAddParameter(":vin_tipoProvisionPago", $tipoProvisionPago);
        $this->commandAddParameter(":vin_busqueda", $busqueda);
        return $this->commandGetData();
    }

    public function buscarPersonasXDocumentoPagado($empresaId, $tipo, $tipoProvisionPago, $busqueda) {
        $this->commandPrepare("sp_persona_buscarXDocumentoPagado");
        $this->commandAddParameter(":vin_empresa_id", $empresaId);
        $this->commandAddParameter(":vin_tipo", $tipo);
        $this->commandAddParameter(":vin_tipoProvisionPago", $tipoProvisionPago);
        $this->commandAddParameter(":vin_busqueda", $busqueda);
        return $this->commandGetData();
    }

    public function buscarPersonaListarXNombreXDocumento($busqueda, $usuarioId = 1) {
        $this->commandPrepare("sp_persona_listar_buscarXNombreXDocumento");
        $this->commandAddParameter(":vin_busqueda", $busqueda);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        return $this->commandGetData();
    }

    public function buscarPersonaClaseXDescripcion($busqueda, $usuarioId = 1) {
        $this->commandPrepare("sp_persona_listar_buscarPersonaClaseXDescripcion");
        $this->commandAddParameter(":vin_busqueda", $busqueda);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        return $this->commandGetData();
    }

    public function obtenerPersonasXPersonaTipo($personaTipoId) {
        $this->commandPrepare("sp_persona_obtenerXPersonaTipoId");
        $this->commandAddParameter(":vin_persona_tipo_id", $personaTipoId);
        return $this->commandGetData();
    }

    public function obtenerContactoTipoActivos() {
        $this->commandPrepare("sp_contacto_tipo_obtenerActivos");
        return $this->commandGetData();
    }

    public function obtenerContactoTipoXDescripcion($contactoTipo) {
        $this->commandPrepare("sp_contacto_tipo_obtenerXDescripcion");
        $this->commandAddParameter(":vin_descripcion", $contactoTipo);
        return $this->commandGetData();
    }

    public function insertarContactoTipo($descripcion, $usuarioId) {
        $this->commandPrepare("sp_contacto_tipo_insertar");
        $this->commandAddParameter(":vin_descripcion", $descripcion);
        $this->commandAddParameter(":vin_usuario_creacion", $usuarioId);
        return $this->commandGetData();
    }

    public function guardarPersonaContacto($personaId, $personaContactoId, $contactoId, $contactoTipoId, $usuarioCreacion) {
        $this->commandPrepare("sp_persona_contacto_insertar");
        $this->commandAddParameter(":vin_persona_empresa_id", $personaId);
        $this->commandAddParameter(":vin_contacto_persona_id", $personaContactoId);
        $this->commandAddParameter(":vin_contacto_id", $contactoId);
        $this->commandAddParameter(":vin_contacto_tipo_id", $contactoTipoId);
        $this->commandAddParameter(":vin_usuario_creacion", $usuarioCreacion);
        return $this->commandGetData();
    }

    public function obtenerPersonaContactoXPersonaId($personaId) {
        $this->commandPrepare("sp_persona_contacto_obtenerXPersonaId");
        $this->commandAddParameter(":vin_persona_id", $personaId);
        return $this->commandGetData();
    }

    public function eliminarPersonaContacto($personaContactoId) {
        $this->commandPrepare("sp_persona_contacto_eliminarXId");
        $this->commandAddParameter(":vin_persona_contacto_id", $personaContactoId);
        return $this->commandGetData();
    }

    public function obtenerDireccionTipoActivos() {
        $this->commandPrepare("sp_direccion_tipo_obtenerActivos");
        return $this->commandGetData();
    }

    public function obtenerUbigeoActivos() {
        $this->commandPrepare("sp_ubigeo_obtenerActivos");
        return $this->commandGetData();
    }

    public function obtenerDireccionTipoXDescripcion($direccionTipo) {
        $this->commandPrepare("sp_direccion_tipo_obtenerXDescripcion");
        $this->commandAddParameter(":vin_descripcion", $direccionTipo);
        return $this->commandGetData();
    }

    public function insertarDireccionTipo($descripcion, $usuarioId) {
        $this->commandPrepare("sp_direccion_tipo_insertar");
        $this->commandAddParameter(":vin_descripcion", $descripcion);
        $this->commandAddParameter(":vin_usuario_creacion", $usuarioId);
        return $this->commandGetData();
    }

    public function eliminarPersonaDireccion($personaDireccionId) {
        $this->commandPrepare("sp_persona_direccion_eliminarXId");
        $this->commandAddParameter(":vin_persona_direccion_id", $personaDireccionId);
        return $this->commandGetData();
    }

    public function obtenerPersonaClaseXUsuarioId($usuarioId) {
        $this->commandPrepare("sp_persona_clase_obtenerXusuarioId");
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        return $this->commandGetData();
    }

    public function obtenerPersonaClaseXUsuarioId2($usuarioId) {
        $this->commandPrepare("sp_persona_clase_obtenerXusuarioId2");
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        return $this->commandGetData();
    }

    public function obtenerPersonaClaseXpersonaTipoIdXUsuarioId($personaTipoId, $usuarioId) {
        $this->commandPrepare("sp_persona_clase_obtenerXpersonaTipoIdXusuarioId");
        $this->commandAddParameter(":vin_persona_tipo_id", $personaTipoId);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        return $this->commandGetData();
    }

    public function obtenerActivasXDocumentoTipoIdXUsuarioId($documentoTipoId, $usuarioId) {
        $this->commandPrepare("sp_persona_obtenerActivasXDocumentoTipoIdXUsuarioId");
        $this->commandAddParameter(":vin_documento_tipo_id", $documentoTipoId);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        return $this->commandGetData();
    }

    public function obtenerSunatTablaDetalleRelacionXTipoXSunatTablaDetalleId($tipo, $sunatTablaDetalleId) {
        $this->commandPrepare("sp_sunat_tabla_detalle_relacion_obtenerXTipoXSunatTablaDetalleId");
        $this->commandAddParameter(":vin_tipo_id", $tipo);
        $this->commandAddParameter(":vin_sunat_tabla_detalle_id", $sunatTablaDetalleId);
        return $this->commandGetData();
    }

    public function obtenerComboPersonaProveedores() {
        $this->commandPrepare("sp_persona_obtenerProveedores");
        return $this->commandGetData();
    }

    public function validarSimilitud($id, $nombre, $apellidoPaterno) {
        $this->commandPrepare("sp_persona_VerificarNombreSimilitud");
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_nombre", $nombre);
        $this->commandAddParameter(":vin_apellido_paterno", $apellidoPaterno);

        return $this->commandGetData();
    }

    //busqueda de personas modal de copia en operaciones
    public function buscarPersonasXDocumentoOperacion($documentoTipoIdStringArray, $valor) {
        $this->commandPrepare("sp_persona_buscarXDocumentoOperacionXNombre");
        $this->commandAddParameter(":vin_tipo_documento_set", $documentoTipoIdStringArray);
        $this->commandAddParameter(":vin_valor", $valor);
        return $this->commandGetData();
    }

    public function obtenerPersonaXOpcionMovimiento($opcionId) {
        $this->commandPrepare("sp_persona_obtenerXOpcionMovimiento");
        $this->commandAddParameter(":vin_opcion_id", $opcionId);
        return $this->commandGetData();
    }

    public function obtenerPersonasMayorDocumentosPPagoXTipos($tipos) {
        $this->commandPrepare("sp_persona_obtenerDocumentosPPagoXTipos");
        $this->commandAddParameter(":vin_tipos", $tipos);
        return $this->commandGetData();
    }

    public function obtenerPersonaXCodigoIdentificacion($codigoIdentificacion) {
        $this->commandPrepare("sp_persona_obtenerXCodigoIdentificacion");
        $this->commandAddParameter(":vin_codigo_identificacion", $codigoIdentificacion);
        return $this->commandGetData();
    }

    public function buscarPersonaDocumentoEarXNombreXDocumento($opcionId, $busqueda) {
        $this->commandPrepare("sp_persona_buscarDocumentoEarXNombreXDocumento");
        $this->commandAddParameter(":vin_opcion_id", $opcionId);
        $this->commandAddParameter(":vin_busqueda", $busqueda);
        return $this->commandGetData();
    }

    public function obtenerUbigeoXId($ubigeoId) {
        $this->commandPrepare("sp_ubigeo_obtenerXId");
        $this->commandAddParameter(":vin_ubigeo_id", $ubigeoId);
        return $this->commandGetData();
    }

    public function obtenerCorreosEFACT() {
        $this->commandPrepare("sp_efact_obtenerCorreos");
        return $this->commandGetData();
    }

    public function obtenerCuentaContableXPersonaId($personaId) {
        $this->commandPrepare("sp_persona_obtenerCuentaContableXPersonaId");
        $this->commandAddParameter(":vin_persona_id", $personaId);
        return $this->commandGetData();
    }

    public function guardarPersonaCentroCosto($personaId, $centroCostoId, $porcentaje, $usarioCreacionId) {
        $this->commandPrepare("sp_persona_centro_costo_guardar");
        $this->commandAddParameter(":vin_persona_id", $personaId);
        $this->commandAddParameter(":vin_centro_costo_id", $centroCostoId);
        $this->commandAddParameter(":vin_porcentaje", $porcentaje);
        $this->commandAddParameter(":vin_usuario_creacion", $usarioCreacionId);

        return $this->commandGetData();
    }

    public function eliminarPersonaCentroCostoXPersonaId($personaId) {
        $this->commandPrepare("sp_persona_centro_costo_eliminarxPersonaId");
        $this->commandAddParameter(":vin_persona_id", $personaId);
        return $this->commandGetData();
    }

    public function obtenerPersonaCentroCostoXPersonaId($personaId) {
        $this->commandPrepare("sp_persona_centro_costo_obtenerxPersonaId");
        $this->commandAddParameter(":vin_persona_id", $personaId);
        return $this->commandGetData();
    }

    public function obtenerPersonaCentroCostoXCodigoIdentificacion($codigoIdentificacion) {
        $this->commandPrepare("sp_persona_centro_costo_obtenerXCodigoIdentificacion");
        $this->commandAddParameter(":vin_codigo_identificacion", $codigoIdentificacion);
        return $this->commandGetData();
    }
    public function obtenerPersonaConductor()
    {
        $this->commandPrepare("sp_persona_obtenerConductor");
        return $this->commandGetData();
    }
    public function obtenerPersonaGetByLicenciaConducir($licenciaConducir)
    {
        $this->commandPrepare("sp_persona_getByLicenciaConducir");
        $this->commandAddParameter(":vin_licencia_conducir", $licenciaConducir);
        return $this->commandGetData();
    }

    public function obtenerPersonasXClase($clase)
    {
        $this->commandPrepare("sp_persona_obtenerPersonasXClase");
        $this->commandAddParameter(":vin_clase", $clase);
        return $this->commandGetData();
    }


 
    public function obtenerPersonasXClasexUsuario($usuario)
    {
        $this->commandPrepare("sp_persona_obtenerPersonasXClaseXUsuario");
        $this->commandAddParameter(":vin_usuario", $usuario);
        return $this->commandGetData();
    }
    
    
    public function obtenerPersonaXUsuarioId($usuarioId)
    {
        $this->commandPrepare("sp_persona_obtenerPersonaXUsuarioId");
        $this->commandAddParameter(":vin_id", $usuarioId);
        return $this->commandGetData();
    }

    public function obtenerTipoDocumento()
    {
        $this->commandPrepare("sp_persona_archivo_obtenerTipo");
        return $this->commandGetData();
    }
    

    public function listarSolicitudesDocumentario($usuarioId) {
        $this->commandPrepare("sp_solicitud_retiro_obtenerSolicitudesPendientesDocumentos");
        $this->commandAddParameter(":vin_usuario", $usuarioId);
        return $this->commandGetData();
    }



    
    public function insertArchivo($usuarioId,$personaId,$personaTipoArchivo,$imageName, $fileName ) {
        $this->commandPrepare("sp_persona_insertDocumentosPersonaReinfo");
        $this->commandAddParameter(":vin_personaId", $personaId);
        $this->commandAddParameter(":vin_personaTipoArchivo", $personaTipoArchivo);
        $this->commandAddParameter(":vin_imageName", $imageName);
        $this->commandAddParameter(":vin_fileName", $fileName);
        $this->commandAddParameter(":vin_usuarioId", $usuarioId);
        return $this->commandGetData();
    }
    public function obtenerArchivos( $personaId ) {
        $this->commandPrepare("sp_persona_ObtenerDocumentosXPersona");
        $this->commandAddParameter(":vin_personaId", $personaId);

        return $this->commandGetData();
    }

    public function obtenerArchivosA( $personaId ) {
        $this->commandPrepare("sp_persona_ObtenerDocumentosXPersona2");
        $this->commandAddParameter(":vin_personaId", $personaId);

        return $this->commandGetData();
    }

    public function eliminarArchivos( $id) {
        $this->commandPrepare("sp_persona_eliminarDocumento");
        $this->commandAddParameter(":vin_id", $id);
        return $this->commandGetData();
    }
    // planta tipos documentos
    
    public function obtenerArchivosPlanta( $personaId ) {
        $this->commandPrepare("sp_persona_ObtenerDocumentosXPersonaPlanta");
        $this->commandAddParameter(":vin_personaId", $personaId);

        return $this->commandGetData();
    }
    public function obtenerArchivosPlantaA( $personaId ) {
        $this->commandPrepare("sp_persona_ObtenerDocumentosXPersonaPlanta2");
        $this->commandAddParameter(":vin_personaId", $personaId);

        return $this->commandGetData();
    }

    public function obtenerArchivosAdministracion(  ) {
        $this->commandPrepare("sp_persona_ObtenerDocumentosXAdministracion");

        return $this->commandGetData();
    }

    public function obtenerArchivosAdministracionT(  ) {
        $this->commandPrepare("sp_persona_ObtenerDocumentosXAdministracionT");

        return $this->commandGetData();
    }

    public function obtenerArchivosAdministracionP(  ) {
        $this->commandPrepare("sp_persona_ObtenerDocumentosXAdministracionP");

        return $this->commandGetData();
    }

    public function obtenerArchivosAdministracionAsociativa(  ) {
        $this->commandPrepare("sp_persona_ObtenerDocumentosXAdministracionAsociativa");

        return $this->commandGetData();
    }
    public function insertTipoDocumentoPlanta( $usuarioId,$nombreDocumento ) {
        $this->commandPrepare("sp_persona_insertDocumentoPlanta");
        $this->commandAddParameter(":vin_usuarioId", $usuarioId);
        $this->commandAddParameter(":vin_nombreDocumento", $nombreDocumento);

        return $this->commandGetData();
    }
    
    public function insertTipoDocumentoPLantaXPersona( $usuarioId,$tipoDocumentoPlanta,$personaId,$inputFile,$fileName ) {
        $this->commandPrepare("sp_persona_insertDocumentoPersonaPlanta");
        $this->commandAddParameter(":vin_usuarioId", $usuarioId);
        $this->commandAddParameter(":vin_tipoDocumentoPlanta", $tipoDocumentoPlanta);
        $this->commandAddParameter(":vin_personaId", $personaId);
        $this->commandAddParameter(":vin_inputFile", $inputFile);
        $this->commandAddParameter(":vin_formato", $fileName);

        return $this->commandGetData();
    }

    public function eliminarTipoDocumentoPLantaXPersona( $id) {
        $this->commandPrepare("sp_persona_eliminarDocumentoPLantaXPersona");
        $this->commandAddParameter(":vin_id", $id);
        return $this->commandGetData();
    }

    public function actualizarFirmaDigital( $persona,$imageName) {
        $this->commandPrepare("sp_persona_actualizarFirmaDigital");
        $this->commandAddParameter(":vin_persona", $persona);
        $this->commandAddParameter(":vin_name", $imageName);
        return $this->commandGetData();
    }

    public function relacionarPlantaXPersona( $persona,$planta,$invitacionId) {
        $this->commandPrepare("sp_persona_registrarPersonaXPlanta");
        $this->commandAddParameter(":vin_persona", $persona);
        $this->commandAddParameter(":vin_planta", $planta);
        $this->commandAddParameter(":vin_invitacion", $invitacionId);
        return $this->commandGetData();
    }

    
    public function obtenerPersonaActivoXStringBusqueda($textoBusqueda, $personaId = null) {
        $this->commandPrepare("sp_persona_obtenerActivoXTextoBusqueda");
        $this->commandAddParameter(":vin_texto_busqueda", $textoBusqueda);
        $this->commandAddParameter(":vin_persona_id", $personaId);
        return $this->commandGetData();
    }
    public function registrarCoordenadasXPersona($persona, $latitud ,$longitud,$orden,$usuarioId) {
        $this->commandPrepare("sp_registrarCoordenadasXPersona");
        $this->commandAddParameter(":vin_persona", $persona);
        $this->commandAddParameter(":vin_latitud", $latitud);
        $this->commandAddParameter(":vin_longitud", $longitud);
        $this->commandAddParameter(":vin_orden", $orden);
        $this->commandAddParameter(":vin_usuario", $usuarioId);
        return $this->commandGetData();
    }

    public function obtenerDocumentoIdentidadPadron($dni) {
        $this->commandPrepare("sp_persona_obtenerDocumentoIdentidadXPadron");
        $this->commandAddParameter(":vin_dni", $dni);
       
        return $this->commandGetData();
    }

    public function guardarCuentasPersona($personaId,$cuenta,$numeroCuenta,$cci,$usuarioId) {
        $this->commandPrepare("sp_persona_guardarCuentaXpersona");
        $this->commandAddParameter(":vin_persona_id", $personaId);
        $this->commandAddParameter(":vin_cuenta", $cuenta);
        $this->commandAddParameter(":vin_numero", $numeroCuenta);
        $this->commandAddParameter(":vin_cci", $cci);
        $this->commandAddParameter(":vin_usuario", $usuarioId);
       
        return $this->commandGetData();
    }
    
    public function guardarCapturasDNI($personaId,$usuarioId) {
        $this->commandPrepare("sp_persona_guardarCapturasDNI");
        $this->commandAddParameter(":vin_persona_id", $personaId);
        $this->commandAddParameter(":vin_usuario", $usuarioId);
        return $this->commandGetData();
    }

    public function obtenerDireccionXPersonaId($personaId,$tipo=null) {
        $this->commandPrepare("sp_persona_obtenerDireccionXPersonaId");
        $this->commandAddParameter(":vin_persona_id", $personaId);
        $this->commandAddParameter(":vin_tipo",  $tipo);
        return $this->commandGetData();
    }

    public function obtenerUbigeoPersona($departamento,$provincia,$distrito){

        $this->commandPrepare("sp_persona_obtenerDatosUbigeo");
        $this->commandAddParameter(":vin_departamento", $departamento);
        $this->commandAddParameter(":vin_provincia", $provincia);
        $this->commandAddParameter(":vin_distrito", $distrito);

        return $this->commandGetData();
    }
    
    
}




    

