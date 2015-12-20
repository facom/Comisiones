#!/bin/bash

lista=$(grep "tipocom" comisiones/*/*.txt | grep -v "noremunerada" | cut -f 1 -d ":")

for comision in $lista
do
    comisionid=$(echo $comision | cut -f 2 -d '/')
    if ! mysql -u root --password='pum' Comisiones -e "select comisionid,fechafin from Comisiones where comisionid='$comisionid' and fechafin>now()" | grep "-" &> /dev/null
    then
	echo "Copying empty file to $comisionid..."
	# cedula=$(grep "cedula =" comisiones/$comisionid/comision-$comisionid.txt | head -n 1 | awk -F" = " '{print $2}')
	cedula=$(mysql -u root --password='pum' Comisiones -e "select cedula from Comisiones where comisionid='$comisionid'" | grep "[0-9]")
	cp images/empty.pdf comisiones/$comisionid/Cumplido1_${cedula}_${comisionid}_empty.pdf
    fi
done
