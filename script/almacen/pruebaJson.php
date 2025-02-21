<?php

// Plantilla JSON como un string (recuerda que debe estar bien formateado)
$templateJson = '{
        "_D" : "urn:oasis:names:specification:ubl:schema:xsd:Invoice-2",
        "_S" : "urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2",
        "_B" : "urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2",
        "_E" : "urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2",
        "Invoice" : [ 
            {
                "UBLVersionID" : [ 
                    {
                        "IdentifierContent" : "2.1"
                    }
                ],
                "CustomizationID" : [ 
                    {
                        "IdentifierContent" : "2.0"
                    }
                ],
                "ID" : [ 
                    {
                        "IdentifierContent" : "<ID>"
                    }
                ],
                "IssueDate" : [ 
                    {
                        "DateContent" : "<IssueDate>"
                    }
                ],
		"IssueTime" : [ 
                    {
                        "DateTimeContent" : "<IssueTime>"
                    }
                ],
                "InvoiceTypeCode" : [ 
                    {
                        "CodeContent" : "01",
                        "CodeListNameText" : "Tipo de Documento",
                        "CodeListSchemeUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo51",
                        "CodeListIdentifier" : "0101",
                        "CodeNameText" : "Tipo de Operacion",
                        "CodeListUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo01",
                        "CodeListAgencyNameText" : "PE:SUNAT"
                    }
                ],
                "Note" : [ 
                    {
                        "TextContent" : "<montoLetras>",
                        "LanguageLocaleIdentifier" : "1000"
                    }
                ],
                "DocumentCurrencyCode" : [ 
                    {
                        "CodeContent" : "<moneda>",
                        "CodeListIdentifier" : "ISO 4217 Alpha",
                        "CodeListNameText" : "Currency",
                        "CodeListAgencyNameText" : "United Nations Economic Commission for Europe"
                    }
                ],
                "LineCountNumeric" : [ 
                    {
                        "NumericContent" : 2
                    }
                ],
                "Signature" : [ 
                    {
                        "ID" : [ 
                            {
                                "IdentifierContent" : "IDSignature"
                            }
                        ],
                        "SignatoryParty" : [ 
                            {
                                "PartyIdentification" : [ 
                                    {
                                        "ID" : [ 
                                            {
                                                "TextContent" : "<rucEmisor>"
                                            }
                                        ]
                                    }
                                ],
                                "PartyName" : [ 
                                    {
                                        "Name" : [ 
                                            {
                                                "TextContent" : "<razonEmisor>"
                                            }
                                        ]
                                    }
                                ]
                            }
                        ],
                        "DigitalSignatureAttachment" : [ 
                            {
                                "ExternalReference" : [ 
                                    {
                                        "URI" : [ 
                                            {
                                                "TextContent" : "IDSignature"
                                            }
                                        ]
                                    }
                                ]
                            }
                        ]
                    }
                ],
                "AccountingSupplierParty" : [ 
                    {
                        "Party" : [ 
                            {
                                "PartyIdentification" : [ 
                                    {
                                        "ID" : [ 
                                            {
                                                "IdentifierContent" : "<rucEmisor>",
                                                "IdentificationSchemeIdentifier" : "6",
                                                "IdentificationSchemeNameText" : "Documento de Identidad",
                                                "IdentificationSchemeAgencyNameText" : "PE:SUNAT",
                                                "IdentificationSchemeUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06"
                                            }
                                        ]
                                    }
                                ],
                                "PartyName" : [ 
                                    {
                                        "Name" : [ 
                                            {
                                                "TextContent" : "<razonEmisor>"
                                            }
                                        ]
                                    }
                                ],
                                "PartyLegalEntity" : [ 
                                    {
                                        "RegistrationName" : [ 
                                            {
                                                "TextContent" : "<razonEmisor>"
                                            }
                                        ],
                                        "RegistrationAddress" : [ 
                                            {
                                                "ID" : [ 
                                                    {
                                                        "IdentifierContent" : "<ubigeo>",
                                                        "IdentificationSchemeAgencyNameText" : "PE:INEI",
                                                        "IdentificationSchemeNameText" : "Ubigeos"
                                                    }
                                                ],
                                                "AddressTypeCode" : [ 
                                                    {
                                                        "CodeContent" : "0000",
                                                        "CodeListAgencyNameText" : "PE:SUNAT",
                                                        "CodeListNameText" : "Establecimientos anexos"
                                                    }
                                                ],
                                                "CityName" : [ 
                                                    {
                                                        "TextContent" : "<departamento>"
                                                    }
                                                ],
                                                "CountrySubentity" : [ 
                                                    {
                                                        "TextContent" : "<ciudad>"
                                                    }
                                                ],
                                                "District" : [ 
                                                    {
                                                        "TextContent" : "<distrito>"
                                                    }
                                                ],
                                                "AddressLine" : [ 
                                                    {
                                                        "Line" : [ 
                                                            {
                                                                "TextContent" : "<direccionEmisor>"
                                                            }
                                                        ]
                                                    }
                                                ],
                                                "Country" : [ 
                                                    {
                                                        "IdentificationCode" : [ 
                                                            {
                                                                "CodeContent" : "PE",
                                                                "CodeListIdentifier" : "ISO 3166-1",
                                                                "CodeListAgencyNameText" : "United Nations Economic Commission for Europe",
                                                                "CodeListNameText" : "Country"
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
                "AccountingCustomerParty" : [ 
                    {
                        "Party" : [ 
                            {
                                "PartyIdentification" : [ 
                                    {
                                        "ID" : [ 
                                            {
                                                "IdentifierContent" : "<rucReceptor>",
                                                "IdentificationSchemeIdentifier" : "6",
                                                "IdentificationSchemeNameText" : "Documento de Identidad",
                                                "IdentificationSchemeAgencyNameText" : "PE:SUNAT",
                                                "IdentificationSchemeUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06"
                                            }
                                        ]
                                    }
                                ],
				"PartyName" : [ 
                                    {
                                        "Name" : [ 
                                            {
                                                "TextContent" : "<razonReceptor>"
                                            }
                                        ]
                                    }
                                ],
                                "PartyLegalEntity" : [ 
                                    {
                                        "RegistrationName" : [ 
                                            {
                                                "TextContent" : "<razonReceptor>"
                                            }
                                        ],
                                        "RegistrationAddress" : [ 
                                            {
                                                "ID" : [ 
                                                    {
                                                        "IdentifierContent" : "<ubigeoReceptor>",
                                                        "IdentificationSchemeAgencyNameText" : "PE:INEI",
                                                        "IdentificationSchemeNameText" : "Ubigeos"
                                                    }
                                                ],
                                                "CityName" : [ 
                                                    {
                                                        "TextContent" : "<departamentoReceptor>"
                                                    }
                                                ],
                                                "CountrySubentity" : [ 
                                                    {
                                                        "TextContent" : "<ciudadReceptor>"
                                                    }
                                                ],
                                                "District" : [ 
                                                    {
                                                        "TextContent" : "<distritoReceptor>"
                                                    }
                                                ],
                                                "AddressLine" : [ 
                                                    {
                                                        "Line" : [ 
                                                            {
                                                                "TextContent" : "<direccionReceptor>"
                                                            }
                                                        ]
                                                    }
                                                ],
                                                "Country" : [ 
                                                    {
                                                        "IdentificationCode" : [ 
                                                            {
                                                                "CodeContent" : "PE",
                                                                "CodeListIdentifier" : "ISO 3166-1",
                                                                "CodeListAgencyNameText" : "United Nations Economic Commission for Europe",
                                                                "CodeListNameText" : "Country"
                                                            }
                                                        ]
                                                    }
                                                ]
                                            }
                                        ]
                                    }
                                ],
                                "Contact" : [ 
                                    {
                                        "ElectronicMail" : [ 
                                            {
                                                "TextContent" : "<electronicMail>"
                                            }
                                        ]
                                    }
                                ]
                            }
                        ]
                    }
                ],
                "TaxTotal" : [ 
                    {
                        "TaxAmount" : [ 
                            {
                                "AmountContent" : "<montoIGV>",
                                "AmountCurrencyIdentifier" : "<moneda>"
                            }
                        ],
                        "TaxSubtotal" : [ 
                            {
                                "TaxableAmount" : [ 
                                    {
                                        "AmountContent" : "<subTotal>",
                                        "AmountCurrencyIdentifier" : "<moneda>"
                                    }
                                ],
                                "TaxAmount" : [ 
                                    {
                                        "AmountContent" : "<montoIGV>",
                                        "AmountCurrencyIdentifier" : "<moneda>"
                                    }
                                ],
                                "TaxCategory" : [ 
                                    {
                                        "TaxScheme" : [ 
                                            {
                                                "ID" : [ 
                                                    {
                                                        "IdentifierContent" : "1000",
                                                        "IdentificationSchemeNameText" : "Codigo de tributos",
                                                        "IdentificationSchemeUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo05",
                                                        "IdentificationSchemeAgencyNameText" : "PE:SUNAT"
                                                    }
                                                ],
                                                "Name" : [ 
                                                    {
                                                        "TextContent" : "IGV"
                                                    }
                                                ],
                                                "TaxTypeCode" : [ 
                                                    {
                                                        "CodeContent" : "VAT"
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
                "LegalMonetaryTotal" : [ 
                    {
                        "LineExtensionAmount" : [ 
                            {
                                "AmountContent" : "<subTotal>",
                                "AmountCurrencyIdentifier" : "<moneda>"
                            }
                        ],
                        "TaxInclusiveAmount" : [ 
                            {
                                "AmountContent" : "<total>",
                                "AmountCurrencyIdentifier" : "<moneda>"
                            }
                        ],
                        "PayableAmount" : [ 
                            {
                                "AmountContent" : "<total>",
                                "AmountCurrencyIdentifier" : "<moneda>"
                            }
                        ]
                    }
                ],
                "InvoiceLine" : [ 
                    {
                        "ID" : [ 
                            {
                                "IdentifierContent" : "1"
                            }
                        ],
                        "Note" : [ 
                            {
                                "TextContent" : "UNIDAD"
                            }
                        ],
                        "InvoicedQuantity" : [ 
                            {
                                "QuantityContent" : "<cantidadItems>",
                                "QuantityUnitCode" : "ZZ",
                                "QuantityUnitCodeListIdentifier" : "UN/ECE rec 20",
                                "QuantityUnitCodeListAgencyNameText" : "United Nations Economic Commission for Europe"
                            }
                        ],
                        "LineExtensionAmount" : [ 
                            {
                                "AmountContent" : "<subTotal>",
                                "AmountCurrencyIdentifier" : "<moneda>"
                            }
                        ],
                        "PricingReference" : [ 
                            {
                                "AlternativeConditionPrice" : [ 
                                    {
                                        "PriceAmount" : [ 
                                            {
                                                "AmountContent" : "<total>",
                                                "AmountCurrencyIdentifier" : "<moneda>"
                                            }
                                        ],
                                        "PriceTypeCode" : [ 
                                            {
                                                "CodeContent" : "01",
                                                "CodeListNameText" : "Tipo de Precio",
                                                "CodeListAgencyNameText" : "PE:SUNAT",
                                                "CodeListUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo16"
                                            }
                                        ]
                                    }
                                ]
                            }
                        ],
                        "TaxTotal" : [ 
                            {
                                "TaxAmount" : [ 
                                    {
                                        "AmountContent" : "<montoIGV>",
                                        "AmountCurrencyIdentifier" : "<moneda>"
                                    }
                                ],
                                "TaxSubtotal" : [ 
                                    {
                                        "TaxableAmount" : [ 
                                            {
                                                "AmountContent" : "<subTotal>",
                                                "AmountCurrencyIdentifier" : "<moneda>"
                                            }
                                        ],
                                        "TaxAmount" : [ 
                                            {
                                                "AmountContent" : "<montoIGV>",
                                                "AmountCurrencyIdentifier" : "<moneda>"
                                            }
                                        ],
                                        "TaxCategory" : [ 
                                            {
                                                "Percent" : [ 
                                                    {
                                                        "NumericContent" : 18.00
                                                    }
                                                ],
                                                "TaxExemptionReasonCode" : [ 
                                                    {
                                                        "CodeContent" : "10",
                                                        "CodeListAgencyNameText" : "PE:SUNAT",
                                                        "CodeListNameText" : "Afectacion del IGV",
                                                        "CodeListUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo07"
                                                    }
                                                ],
                                                "TaxScheme" : [ 
                                                    {
                                                        "ID" : [ 
                                                            {
                                                                "IdentifierContent" : "1000",
                                                                "IdentificationSchemeNameText" : "Codigo de tributos",
                                                                "IdentificationSchemeUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo05",
                                                                "IdentificationSchemeAgencyNameText" : "PE:SUNAT"
                                                            }
                                                        ],
                                                        "Name" : [ 
                                                            {
                                                                "TextContent" : "IGV"
                                                            }
                                                        ],
                                                        "TaxTypeCode" : [ 
                                                            {
                                                                "CodeContent" : "VAT"
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
                        "Item" : [ 
                            {
                                "Description" : [ 
                                    {
                                        "TextContent" : "<descripcionItemFactura>"
                                    }
                                ],
                                "SellersItemIdentification" : [ 
                                    {
                                        "ID" : [ 
                                            {
                                                "IdentifierContent" : "<codigoFactura>"
                                            }
                                        ]
                                    }
                                ]
                            }
                        ],
                        "Price" : [ 
                            {
                                "PriceAmount" : [ 
                                    {
                                        "AmountContent" : "<subTotal>",
                                        "AmountCurrencyIdentifier" : "<moneda>"
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
    '<ID>' => 'FF01-000036',
    '<IssueDate>' => '2024-11-05',
    '<IssueTime>' => '12:00:00',
    '<moneda>' => "PEN",
    '<rucEmisor>' => '20600739256',
    '<razonEmisor>' => 'GRUPO EMPRESARIAL SOLUCIONES MINERAS S.A.C.',
   '<ubigeo>'=>'130501',
   '<departamento>' => 'LIMA',
   '<ciudad>' => 'LIMA' ,
   '<distrito>' => 'ANCON' ,
   '<direccionEmisor>' => 'AV. AMERICA OESTE 10 TORRE UPAO NRO. 706 DPTO. 706 URB. NATASHA ALTA LA LIBERTAD -TRUJILLO - TRUJILLO', 
   '<rucReceptor>' => '20409383891',
   '<razonReceptor>' => 'GRUPO TEXTIL VALLES S.A.C.',
  '<ubigeoReceptor>'=>'>140124',
  '<departamentoReceptor>' => 'LIMA',
   '<ciudadReceptor>' => 'LIMA' ,
   '<distritoReceptor>' => 'LIMA' ,
   '<direccionReceptor>' =>'PJ. TACNA NRO. 111 (A MEDIA CDRA DE TERRACARGO) TUMBES - ZARUMILLA - AGUAS VERDES',
   '<electronicMail>' => 'administracion@solucionesmineras.pe',
   '<montoIGV>'  => '90',
   '<subTotal>'  => '500',
   '<total>'  => '590',
   '<cantidadItems>'  => '1',
   '<descripcionItemFactura>'  => 'PRURBA',
   '<codigoFactura>' => '01'
];

// Reemplazar los datos en la plantilla
$json = str_replace(array_keys($data), array_values($data), $templateJson);

// Guardar el JSON en un archivo
$fileName = 'factura_' . $data['<ID>'] . '.json';
file_put_contents($fileName, $json);

echo "Factura generada y guardada como $fileName";


