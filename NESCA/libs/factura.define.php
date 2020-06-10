<?php

	/* pruebas */
	define('PRODUCCION',0);

	if( PRODUCCION==1 ){
		define('SERIE','NESCA');
		define('FOLIO_INI',1);
	}else{
		define('SERIE','NESCX');
		define('USER_RFC','XAXX010101000');
		define('USER_CORP','Ventas público general');
		define('FOLIO_INI',33);
	}

	define('FVER','3.3');
	define('MEX','MXN'); 			// moneda local
	define('TCAMBIO','1.0'); 		// tipo de cambio correspondiente a la moneda seleccionada
	define('COMPROBANTE','I');		// I = ingreso
	define('MPAGO','PUE'); 			// METODO DE PAGO
	define('LEXPEDITION','11700');	// codigo postal del lugar de expedicion
	define('ACSTATUS','71');		// AfterCreateStatus
	define('DESCIP','Ventas café para mi negocio');

	// Emisor
	define('ERFC','MLG100224TC1');	// rfc empresa
	define('ENOM','Master Loyalty Group, S.A. de C.V.');	// nombre empresa
	define('ERFI','601');	// regimen fiscal
	define('ECAL','Bosque de Duraznos 65 1002 A y B');	// direccion calle y numero
	define('ECOL','Bosque de las Lomas');	// direccion colonia
	define('EMUN','Miguel Hidalgo');	// direccion municipio
	define('EEST','Ciudad de México');	// direccion estado
	define('EPAI','México');			// direccion pais
	define('ECPT','11700');				// direccion codigo postal

	define('ITRANSLADO','002'); 		// impuesto translado
	define('ITIPOFACTOR','Tasa');		// impuesto tipo factor
	define('ITASA','0.160000'); 		// inpuesto tasa o cuota

	define('SCLAVE','81141601');		// clave para el servicio
	define('SCLAVEUNIT','SX');			// clave unidad para el servicio
	define('SUNIT','Envio');			// unidad para el servicio
	define('SDESCIP','Costo de envío');
	
	define('Aux_BU','7i5hg9lv27');
	define('Aux_PJ','6dktzt7frg');
	define('Aux_CT','*rfc*');
	/* productos con impuestos */
	define('Aux_AC','ACT(40230)');
	define('Aux_IT','1Mg_40230');
	/* productos sin impuestos */
	define('Aux_AC3','ACT(40240)');
	define('Aux_IT3','1Mg_40240');
	/* para envios */
	define('Aux_AC2','ACT(40290)');
	define('Aux_IT2','1Mg_40290');

	define('LEMAIL','frodriguez@mlg.com.mx;rmorales@mlg.com.mx;');

	/* para ver sumatotrias  */
	define('VER_SUMATORIAS','0');

	/* file debug */
	define('LOG','process_in1.log');
	define('LOG_DIR','log/');

/*	TODOS LOS VALORES MONETARIOS SE COLOCAN CON DOS DECIMALES
	comprobante ==> subtotal == suma de los importes de todos los conceptos
	comprobante ==> descuento == suma de los descuentos de todos los conceptos
	impuestos==> TotalImpuestosTrasladados == suma de los ImpuestosTraslado1Importe de los concepros
	comprobante ==> subtotal * 0.16 == impuestos==> TotalImpuestosTrasladados


	[Comprobante]

		idUnico 			=SERIE + consecutivo()
		Version 			=FVER
		Serie 				=SERIE
		Folio 				=consecutivo()
		Fecha 				=fecha formato yyyy-mm-dd + T + hh:mm:ss
		FormaPago 			=factura_catalogo_forma_de_pago(); // 03
		CondicionesDePago 	=''
		Subtotal 			=suma_productos_sin_impuestos(); // sin impuestos ni agregados
		Descuento 			=suma_descuentos();
		Moneda 				=MEX
		TipoCambio			=TCAMBIO
		Total 				=suma_productos_con_impuestos(); // suma de todos los productos con impuestos sin descuentos ni agregados
		TipoDeComprobante 	=COMPROBANTE 
		MetodoPago 			=MPAGO
		LugarExpedicion 	=LEXPEDITION
		Confirmacion 		=''
		Correo 				=factura_list_email_notify();
		FormatoCfdi			=''
		Status 				=''

	[Extras]
		AfterCreateStatus 	=ACSTATUS
		ExtrasTexto01 		=customer_razon_social()
		Description 		=DESCIP
		YourReference 		=num_orden(); // numero de pedido
		ExtrasNotas 		=''

	[Emisor]
		Rfc 			=ERFC
		Nombre 			=ENOM
		RegimenFiscal 	=ERFI
		Calle 			=ECAL
		NoExterior 		=''
		NoInterior 		=''
		Colonia 		=ECOL
		Localidad 		=''
		Referencia 		=''
		Municipio 		=EMUN
		Estado 			=EEST
		Pais 			=EPAI
		CodigoPostal 	=ECPT

	[Receptor]
		Rfc 				=customer_rfc();
		Nombre 				=customer_razon_social();
		ResidenciaFiscal 	=''
		NumRegIdTrib 		=''
		UsoCFDI 			=customer_cfdi(); // G03, agregar al campo de facturacion en cafeparaminegocio.com.mx formulario pagar

	LISTA DE PRODUCTOS ADQUIRIDOS

		consecutivo_concepto=1;

			[Concepto + (consecutivo_concepto++) ] 											// para los productos comprados
				ClaveProdServ 						=prod_sat_clave();						// sat_clave del producto
				NoIdentificacion 					=''
				Cantidad 							=prod_qty();
				ClaveUnidad 						=prod_sat_clave_unidad();
				Unidad 								=prod_sat_unidad();
				Descripcion 						=prod_subtitle(); 						// descripcion del producto (subtitulo revisar)
				ValorUnitario 						=prod_price_list()+prod_ieps_monto(); 	// precio del producto por unidad + IEP (en caso de tener), sin IVA y sin descuento y sin agregados
				Importe 							=prod_qty()*ValorUnitario;				// resultado de cantidad * valor_unitario
				ImpuestosTraslado1Base 				=Importe
				ImpuestosTraslado1Impuestos 		=ITRANSLADO
				ImpuestosTraslado1TipoFactor 		=ITIPOFACTOR
				ImpuestosTraslado1TasaOCuota 		=ITASA
				ImpuestosTraslado1Importe 			=Importe * ITASA 						// Importe * TasaOCuota (a dos decimales)

			[Concepto + (consecutivo_concepto++) ]											// para el envio y agregados
				ClaveProdServ 							=SCLAVE
				NoIdentificacion 						=''
				Cantidad 								=1
				ClaveUnidad 							=SCLAVEUNIT
				Unidad 									=SUNIT
				Descripcion 							=SDESCIP
				ValorUnitario 							=prod_precio_envio() * prod_qty(); 	// suma de costos de envio
				Importe 								=Cantidad * ValorUnitario			// 
				resultado de cantidad * valor_unitario
														// ?????? valor_unitario con o sin impuesto
				ImpuestosTraslado1Base 					=Importe
				ImpuestosTraslado1Impuestos 			=ITRANSLADO
				ImpuestosTraslado1TipoFactor 			=ITIPOFACTOR
				ImpuestosTraslado1TasaOCuota 			=ITASA
				ImpuestosTraslado1Importe 				=Importe * ITASA; 	// Importe * TasaOCuota (a dos decimales)

		InformacionAduaneraNumeroPedimento				=''
		CuentaPredialNumero								=''
		ConceptoTexto01									=''
		ConceptoTexto02									=''
		ConceptoTexto03									=''
		ConceptoTexto04									=''
		ConceptoTexto05									=''
		ConceptoTexto06									=''
		ConceptoTexto07									=''
		ConceptoTexto08									=''
		ConceptoTexto09									=''
		ConceptoTexto10									=''
		Aux_BU											=Aux_BU
		Aux_PJ											=Aux_PJ
		Aux_CT											=Aux_CT
		Aux_AC											=Aux_AC
		Aux_IT											=Aux_IT

	[Impuestos]
		TotalImpuestosRetenidos 						='' 								// (para impuestos retenidos)
		TotalImpuestosTrasladados 						=suma_impuestos_traslado_importe();
		ImpuestosTraslado1Impuesto 						=ITRANSLADO
		ImpuestosTraslado1TipoFactor					=ITIPOFACTOR
		ImpuestosTraslado1TasaOCuota 					=ITASA
		ImpuestosTraslado1Importe 						=suma_impuestos_traslado_importe();
*/

?>