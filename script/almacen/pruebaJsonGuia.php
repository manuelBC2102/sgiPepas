<?php

// Plantilla JSON como un string (recuerda que debe estar bien formateado)
$templateJson = '{
    "_D": "urn:oasis:names:specification:ubl:schema:xsd:DespatchAdvice-2",
    "_S": "urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2",
    "_B": "urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2",
    "_E": "urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2",
    "DespatchAdvice": [
        {
            "UBLVersionID": [
                {
                    "IdentifierContent": "2.1"
                }
            ],
            "CustomizationID": [
                {
                    "IdentifierContent": "2.0"
                }
            ],
            "ID": [
                {
                    "IdentifierContent": "<ID>"
                }
            ],
            "IssueDate": [
                {
                    "DateContent": "<IssueDate>"
                }
            ],
            "IssueTime": [
                {
                    "DateTimeContent": "<IssueTime>"
                }
            ],
            "DespatchAdviceTypeCode": [
                {
                    "IdentifierContent": "09"
                }
            ],
            "Note": [
                {
                    "TextContent": "<observacion>"
                }
            ],
            "LineCountNumeric": [
                {
                    "TextContent": "2"
                }
            ],
            "AdditionalDocumentReference": [
                {
                    "ID": [
                        {
                            "IdentifierContent": "<documentoRelacionado>"
                        }
                    ],
                    "DocumentTypeCode": [
                        {
                            "CodeContent": "01"
                        }
                    ],
                    "DocumentType": [
                        {
                            "TextContent": "<descripcionDocumentoRelacionado>"
                        }
                    ],
                    "IssuerParty": [
                        {
                            "PartyIdentification": [
                                {
                                    "ID": [
                                        {
                                            "IdentifierContent": "<rucEmisorDocumentoRelacionado>",
                                            "IdentificationSchemeIdentifier": "6"
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ],
            "Signature": [
                {
                    "ID": [
                        {
                            "IdentifierContent": "IDSignature"
                        }
                    ],
                    "SignatoryParty": [
                        {
                            "PartyIdentification": [
                                {
                                    "ID": [
                                        {
                                            "IdentifierContent": "<rucEmisor>"
                                        }
                                    ]
                                }
                            ],
                            "PartyName": [
                                {
                                    "Name": [
                                        {
                                            "TextContent": "<razonSocialEmisor>"
                                        }
                                    ]
                                }
                            ]
                        }
                    ],
                    "DigitalSignatureAttachment": [
                        {
                            "ExternalReference": [
                                {
                                    "URI": [
                                        {
                                            "TextContent": "IDSignature"
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ],
            "DespatchSupplierParty": [
                {
                    "Party": [
                        {
                            "PartyIdentification": [
                                {
                                    "ID": [
                                        {
                                            "IdentifierContent": "<rucEmisor>",
                                            "IdentificationSchemeIdentifier": "6"
                                        }
                                    ]
                                }
                            ],
                            "PostalAddress": [
                                {
                                    "ID": [
                                        {
                                            "IdentifierContent": "<ubigeo>"
                                        }
                                    ],
                                    "StreetName": [
                                        {
                                            "TextContent": "<direccionEmisor>"
                                        }
                                    ],
                                    "CitySubdivisionName": [
                                        {
                                            "TextContent": "<urbanizacion>"
                                        }
                                    ],
                                    "CityName": [
                                        {
                                            "TextContent": "<provincia>"
                                        }
                                    ],
                                    "CountrySubentity": [
                                        {
                                            "TextContent": "<departamento>"
                                        }
                                    ],
                                    "District": [
                                        {
                                            "TextContent": "<distrito>"
                                        }
                                    ],
                                    "Country": [
                                        {
                                            "IdentificationCode": [
                                                {
                                                    "IdentifierContent": "PE"
                                                }
                                            ]
                                        }
                                    ]
                                }
                            ],
                            "PartyLegalEntity": [
                                {
                                    "RegistrationName": [
                                        {
                                            "TextContent": "<razonSocialEmisor>"
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ],
            "DeliveryCustomerParty": [
                {
                    "Party": [
                        {
                            "PartyIdentification": [
                                {
                                    "ID": [
                                        {
                                            "IdentifierContent": "<rucReceptor>",
                                            "IdentificationSchemeIdentifier": "6"
                                        }
                                    ]
                                }
                            ],
                            "PostalAddress": [
                                {
                                    "ID": [
                                        {
                                            "IdentifierContent": "<ubigeoReceptor>"
                                        }
                                    ],
                                    "StreetName": [
                                        {
                                            "TextContent": "<direccionReceptor>"
                                        }
                                    ],
                                    "CitySubdivisionName": [
                                        {
                                            "TextContent": "<urbanizacionReceptor>"
                                        }
                                    ],
                                    "CityName": [
                                        {
                                            "TextContent": "<provinciaReceptor>"
                                        }
                                    ],
                                    "CountrySubentity": [
                                        {
                                            "TextContent": "<departamentoReceptor>"
                                        }
                                    ],
                                    "District": [
                                        {
                                            "TextContent": "<distritoReceptor>"
                                        }
                                    ],
                                    "Country": [
                                        {
                                            "IdentificationCode": [
                                                {
                                                    "IdentifierContent": "PE"
                                                }
                                            ]
                                        }
                                    ]
                                }
                            ],
                            "PartyLegalEntity": [
                                {
                                    "RegistrationName": [
                                        {
                                            "TextContent": "<razonSocialReceptor>"
                                        }
                                    ]
                                }
                            ],
                            "Contact": [
                                {
                                    "ElectronicMail": [
                                        {
                                            "TextContent": "<electronicMail>"
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ],
            "BuyerCustomerParty": [
                {
                    "Party": [
                        {
                            "PartyIdentification": [
                                {
                                    "ID": [
                                        {
                                            "IdentifierContent": "<rucComprador>",
                                            "IdentificationSchemeIdentifier": "6"
                                        }
                                    ]
                                }
                            ],
                            "PartyLegalEntity": [
                                {
                                    "RegistrationName": [
                                        {
                                            "TextContent": "<razonSocialComprador>"
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ],
            "Shipment": [
                {
                    "ID": [
                        {
                            "IdentifierContent": "SUNAT_Envio"
                        }
                    ],
                    "HandlingCode": [
                        {
                            "IdentifierContent": "03"
                        }
                    ],
                    "HandlingInstructions": [
                        {
                            "TextContent": "<motivoTraslado>"
                        }
                    ],
                    "GrossWeightMeasure": [
                        {
                            "MeasureContent": "<peso>",
                            "MeasureUnitCode": "KGM"
                        }
                    ],
                    "ShipmentStage": [
                        {
                            "TransportModeCode": [
                                {
                                    "IdentifierContent": "02"
                                }
                            ],
                            "TransitPeriod": [
                                {
                                    "StartDate": [
                                        {
                                            "DateContent": "<fechaTraslado>"
                                        }
                                    ]
                                }
                            ],
                            "DriverPerson": [
                                {
                                    "ID": [
                                        {
                                            "IdentifierContent": "<dniConductor>",
                                            "IdentificationSchemeIdentifier": "1"
                                        }
                                    ],
                                    "FirstName": [
                                        {
                                            "TextContent": "<nombreConductor>"
                                        }
                                    ],
                                    "FamilyName": [
                                        {
                                            "TextContent": "<apellidoConductor>"
                                        }
                                    ],
                                    "JobTitle": [
                                        {
                                            "TextContent": "Principal"
                                        }
                                    ],
                                    "IdentityDocumentReference": [
                                        {
                                            "ID": [
                                                {
                                                    "IdentifierContent": "<licenciaConductor>"
                                                }
                                            ]
                                        }
                                    ]
                                }
                            ]
                        }
                    ],
                    "Delivery": [
                        {
                            "DeliveryAddress": [
                                {
                                    "ID": [
                                        {
                                            "IdentifierContent": "<ubigeoLlegada>"
                                        }
                                    ],
                                    "CitySubdivisionName": [
                                        {
                                            "TextContent": "<urbanizacionLlegada>"
                                        }
                                    ],
                                    "CityName": [
                                        {
                                            "TextContent": "<provinciaLlegada>"
                                        }
                                    ],
                                    "CountrySubentity": [
                                        {
                                            "TextContent": "<departamentoLlegada>"
                                        }
                                    ],
                                    "District": [
                                        {
                                            "TextContent": "<distritoLlegada>"
                                        }
                                    ],
                                    "AddressLine": [
                                        {
                                            "Line": [
                                                {
                                                    "TextContent": "<direccionLlegada>"
                                                }
                                            ]
                                        }
                                    ],
                                    "Country": [
                                        {
                                            "IdentificationCode": [
                                                {
                                                    "IdentifierContent": "PE"
                                                }
                                            ]
                                        }
                                    ]
                                }
                            ],
                            "Despatch": [
                                {
                                    "DespatchAddress": [
                                        {
                                            "ID": [
                                                {
                                                    "IdentifierContent": "<ubigeoPartida>"
                                                }
                                            ],
                                            "CitySubdivisionName": [
                                                {
                                                    "TextContent": "<urbanizacionPartida>"
                                                }
                                            ],
                                            "CityName": [
                                                {
                                                    "TextContent": "<provinciaPartida>"
                                                }
                                            ],
                                            "CountrySubentity": [
                                                {
                                                    "TextContent": "<departamentoPartida>"
                                                }
                                            ],
                                            "District": [
                                                {
                                                    "TextContent": "<distritoPartida>"
                                                }
                                            ],
                                            "AddressLine": [
                                                {
                                                    "Line": [
                                                        {
                                                            "TextContent": "<direccionPartida>"
                                                        }
                                                    ]
                                                }
                                            ],
                                            "Country": [
                                                {
                                                    "IdentificationCode": [
                                                        {
                                                            "IdentifierContent": "PE"
                                                        }
                                                    ]
                                                }
                                            ]
                                        }
                                    ],
                                    "DespatchParty": [
                                        {
                                            "AgentParty": [
                                                {
                                                    "PartyLegalEntity": [
                                                        {
                                                            "CompanyID": [
                                                                {
                                                                    "IdentifierContent": "<rucEmisor>",
                                                                    "IdentificationSchemeIdentifier": "06"
                                                                }
                                                            ]
                                                        }
                                                    ]
                                                }
                                            ]
                                        }
                                    ]
                                }
                            ]
                        }
                    ],
                    "TransportHandlingUnit": [
                        {
                            "TransportEquipment": [
                                {
                                    "ID": [
                                        {
                                            "IdentifierContent": "<placa>"
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ],
            "DespatchLine": [
                {
                    "ID": [
                        {
                            "IdentifierContent": 1
                        }
                    ],
                    "Note": [
                        {
                            "TextContent": "UNIDAD"
                        }
                    ],
                    "DeliveredQuantity": [
                        {
                            "QuantityContent": "<cantidadItem>",
                            "QuantityUnitCode": "ZZ"
                        }
                    ],
                    "OrderLineReference": [
                        {
                            "LineID": [
                                {
                                    "IdentifierContent": 1
                                }
                            ]
                        }
                    ],
                    "Item": [
                        {
                            "Description": [
                                {
                                    "TextContent": "<descripcionItem>"
                                }
                            ],
                            "SellersItemIdentification": [
                                {
                                    "ID": [
                                        {
                                            "IdentifierContent": "<codigo>"
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
             
            ]
        }
    ]
}

';

// Datos de la venta
$data = [
    '<ID>' => 'TF01-000055',
    '<IssueDate>' => '2024-11-08',
    '<IssueTime>' => '12:00:00',
    '<observacion>' =>' ',
    '<documentoRelacionado>'=>'0001-000061',
    '<descripcionDocumentoRelacionado>' => ' ',
    '<rucEmisorDocumentoRelacionado>' => '20600739256',
    
    '<rucEmisor>' => '20600739256',
    '<razonSocialEmisor>' => 'GRUPO EMPRESARIAL SOLUCIONES MINERAS S.A.C.',
   '<ubigeo>'=>'130501',
   '<departamento>' => 'LIMA',
   '<provincia>' => 'LIMA' ,
   '<urbanizacion>' => '-' ,
   '<distrito>' => 'ANCON' ,
   '<direccionEmisor>' => 'AV. AMERICA OESTE 10 TORRE UPAO NRO. 706 DPTO. 706 URB. NATASHA ALTA LA LIBERTAD -TRUJILLO - TRUJILLO', 
   '<rucReceptor>' => '20409383891',
   '<razonSocialReceptor>' => 'GRUPO TEXTIL VALLES S.A.C.',
  '<ubigeoReceptor>'=>'240302',
  '<departamentoReceptor>' => 'LIMA',
   '<provinciaReceptor>' => 'LIMA' ,
   '<distritoReceptor>' => 'LIMA' ,
   '<urbanizacionReceptor>' => '-' ,
   '<direccionReceptor>' =>'PJ. TACNA NRO. 111 (A MEDIA CDRA DE TERRACARGO) TUMBES - ZARUMILLA - AGUAS VERDES',
   '<electronicMail>' => 'administracion@solucionesmineras.pe',
   '<rucComprador>'  => '20496108664',
   '<razonSocialComprador>'  => 'SOLUCIONES AMBIENTALES PERU E.I.R.L',
   '<peso>'  => '500',
   '<fechaTraslado>'  => '2024-11-08',
   '<dniConductor>'  => '70370538',
   '<nombreConductor>'  => 'Carlos',
   '<apellidoConductor>'  => 'silva mendoza',
   '<licenciaConductor>'  => 'D70370538',
   '<ubigeoLlegada>'=>'240302',
  '<departamentoLlegada>' => 'LIMA',
   '<provinciaLlegada>' => 'LIMA' ,
   '<distritoLlegada>' => 'LIMA' ,
   '<urbanizacionLlegada>' => '-' ,
   '<direccionLlegada>' =>'PJ. TACNA NRO. 111 (A MEDIA CDRA DE TERRACARGO) TUMBES - ZARUMILLA - AGUAS VERDES',
   '<ubigeoPartida>'=>'130501',
   '<departamentoPartida>' => 'LIMA',
   '<provinciaPartida>' => 'LIMA' ,
   '<urbanizacionPartida>' => '-' ,
   '<distritoPartida>' => 'ANCON' ,
   '<direccionPartida>' => 'AV. AMERICA OESTE 10 TORRE UPAO NRO. 706 DPTO. 706 URB. NATASHA ALTA LA LIBERTAD -TRUJILLO - TRUJILLO', 
   '<placa>' => 'ATL917' ,
   '<cantidadItem>' => '1' ,
   '<descripcionItem>' => 'PRUEBA' ,
   '<codigo>' => '01A'
];

// Reemplazar los datos en la plantilla
$json = str_replace(array_keys($data), array_values($data), $templateJson);

// Guardar el JSON en un archivo
$fileName = $data['<rucEmisor>'].'-09-' . $data['<ID>'] . '.json';
file_put_contents($fileName, $json);

echo "Guia generada y guardada como $fileName";


