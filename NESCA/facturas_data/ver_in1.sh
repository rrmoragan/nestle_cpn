#!/bin/bash

if [ "$1" = "" ]; then

	echo "==> forma de uso"
	echo "==> ./ver_in1.sh archivo.in1"

else

	echo "====================================================="
	echo ""
	grep 'YourReference=' $1
	grep 'FormaPago=' $1
	grep 'UsoCFDI=' $1
	grep 'Rfc=' $1
	grep 'Nombre=' $1
	echo ""

	grep 'Subtotal=' $1
	grep 'Descuento=' $1
	grep 'Total=' $1
	echo ""
	
	grep 'ValorUnitario=' $1
	grep 'Cantidad=' $1
	grep 'Importe=' $1
	echo ""
	
	grep 'TotalImpuestosTrasladados=' $1

	echo ""
	echo "====================================================="
fi
