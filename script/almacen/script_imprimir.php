<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

SERVICIO DE ATENCIÓN DE EMERGENCIAS REDES LAMBAYEQUE<br>INCLUYE:<br><ul><li>PERSONAL CAPACITADO EN ATENCION POR EMERGENCIA EN REDES&nbsp;(FUSIONISTA, AYUDANTE DE FUSIONISTA, 2 MOTORIZADOS IG1)</li><li>UN VEHÍCULO CON ARRESTA-LLAMAS PARA EL TRASLADO
        DEL PERSONAL, EQUIPO Y HERRAMIENTAS<br></li><li><span>DOS VEHÍCULO MOTORIZADO PARA EL DESPLAZAMIENTO
            DE LOS TÉCNICOS IG1<br></span></li><li>04 JUEGOS DE ENTIBADOS PARA EXCAVACIONES
        PROFUNDAS, SEGÚN ESPECIFICACIONES DELMANUAL DE HSE PARA CONTRATISTAS DE GDP</li><li>UNA ESCALERA EXTENSIBLE QUE NO SUPERE LOS 3
        METROS DE LONGITUD Y QUE CUMPLA CON LAS ESPECIFICACIONES DEL MANUAL DE HSE PARA
        CONTRATISTAS DE GDP</li><li>CUATRO JUEGOS DE ARNESES DE SEGURIDAD CON LÍNEA
        DE VIDA Y CUATRO BARBIQUEJOS</li><li><span>DOS ROLLOS CINTA DE ADVERTENCIA PLÁSTICA CON
            LOGOTIPO (FONDO AMARILLO CON LETRAS NEGRAS) SERÁ DE 4* DE ANCHO E INDIQUE QUE
            SE ESTÁ EJECUTANDO TRABAJOS, SEGÚN LAS ESPECIFICACIONES DEL MANUAL DE HSE PARA
            CONTRATISTAS DE GDP&nbsp;<br></span></li><li><span>10 PORTA CINTAS Y 10 CACHACOS<br></span></li><li><span>ROLLOS DE MALLA NARANJA DE SEÑALIZACIÓN<br></span></li><li><span>TRES PARES DE PRENSAS
            MECÁNICAS PARA DIÁMETROS 20MM, 32MM Y 63MM.<br></span></li><li><span>DOS PRENSAS HIDRÁULICAS
            PARA DIÁMETROS 110MM, 160MM Y 200MM<br></span></li><li><span>TRAJE IGNÍFUGO<br></span></li><li><span>DETECTOR DE GASES PORTÁTIL Y ATMOSFERA EXPLOSIVA<br></span></li><li><span>TRES LINTERNAS
            INTRÍNSECAS<br></span></li><li><span>DOS MANERALES
            PORTÁTILES <br></span></li><li><span>TRES MANERALES PARA APERTURA Y CIERRE DE
            POLIVÁLVULA<br></span></li><li><span>TRES LLAVE DE APERTURA
            Y CIERRE DE REGISTRO DE VÁLVULA<br></span></li><li><span>TRES LLAVE PARA BLOQUEO
            DE VÁLVULA DE SERVICIO DE CLIENTE RESIDENCIAL<br></span></li><li><span>TRES LLAVES DE APERTURA
            DE GABINETE<br></span></li><li><span>DOS PALAS DE BRONCE<br></span></li><li><span>DOS PICOS DE BRONCE.<br></span></li><li><span>01 LUMINARIAS CON
            PEDESTAL<br></span></li><li><span>UNA PANTALLA DE SEÑALIZACIÓN Y LUMINARIAS<br></span></li><li><span>UN GENERADOR ELÉCTRICO<br></span></li><li><span>DOS CONTENEDORES CON 25 LITROS DE AGUA CADA
            UNO<br></span></li><li>UN EQUIPOS DE COMUNICACIÓN CELULAR
        OPERATIVOS, CON SALIDA A LLAMADAS A TELÉFONO FIJO Y CELULAR CON USUARIO TOA
        ACTIVADO</li><li>EQUIPO MÓVIL DE COMUNICACIÓN INTRÍNSECO</li><li><span>MAQUINA
            ELECTROFUSIÓN GF, 01 PLANCHA DE TERMOFUSIÓN RITMO, 01 SOCKT 32MM, 01 CALIBRADOR
            DE 20MM, 32MM, 1
            BISELADOR DE 20MM, 32MM, RASPADOR,
            02 CORTATUBO DE 32MM, DE 63MM A 110MM, DE 160MM A 200MM,&nbsp;</span></li><li><span>02 ALINEADOR DE 63MM, DE
            110MM A 200MM, 01 ANILLO FRIO DE 20MM, 02 ANILLO FRIO DE 32MM, 02 EXTENSIÓN
            ELÉCTRICA DE 15MTS, 01 MARTILLO DE GOMA, 01 PALA DE FIERRO, 01 ALCOHOL
            ISOPROPÍLICO, 01 PAÑO WYPALL, 01 MARCADOR PLOMO</span><br></li><li><span><span>20 CINTA DE AISLANTE, 05 CINTA DUCK TAPE, 05 CINTA
                VULCANIZANTE, 01 AMOLADORA 9*, 01 MARTILLO DEMOLEDOR, 01 COMBA, 01
                CINCEL PUNTA PLANA, 01 CINCEL PUN

                <?php
                include_once __DIR__ . '/../../modelo/almacen/Persona.php';
                include_once __DIR__ . '/../../modeloNegocio/almacen/MovimientoNegocio.php';
                include_once __DIR__ . '/../../modeloNegocio/almacen/DocumentoTipoDatoNegocio.php';
                try {
                    MovimientoNegocio::create()->imprimirExportarPDFDocumento(23, 36405, 1);
                } catch (Exception $ex) {
                    echo $ex->getMessage();
                }