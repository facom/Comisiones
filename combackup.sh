#!/bin/bash                                                       
echo "Dumping..."
filename="comisiones"
mysqldump -u root -p Comisiones > etc/data/comisiones.sql
echo "Compressing..."
tar cf etc/data/comisiones.tar etc/data/comisiones.sql comisiones
p7zip etc/data/comisiones.tar
echo "Splitting..."
cd etc/data/dump
rm $filename.tar.7z-*
split -b 1024k ../$filename.tar.7z $filename.tar.7z-
cd - &> /dev/null
echo "Git adding..."
git add --all -f etc/data/dump/*
echo "Done."
