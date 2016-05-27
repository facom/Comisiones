#!/bin/bash                                                       
echo "Dumping..."
<<<<<<< HEAD
filename="comisiones"
#mysqldump -u root -p Comisiones > etc/data/comisiones.sql
#echo "Compressing..."
#tar cf etc/data/comisiones.tar etc/data/comisiones.sql comisiones
#p7zip etc/data/comisiones.tar
echo "Splitting..."
cd etc/data/dump
rm $filename.tar.7z-*
split -b 1024k ../$filename.tar.7z $filename.tar.7z-
cd - &> /dev/null
echo "Git adding..."
git add --all -f etc/data/dump/*
=======
mysqldump -u root -p Comisiones > etc/data/comisiones.sql
echo "Compressing..."
tar cf etc/data/comisiones.tar etc/data/comisiones.sql comisiones
p7zip etc/data/comisiones.tar
# echo "Splitting..."
# cd data/sql/dump
# rm $filename.sql.7z-*
# split -b 1024k ../$filename.sql.7z $filename.sql.7z-
#cd - &> /dev/null
echo "Git adding..."
git add --all -f etc/data/*.tar.*
>>>>>>> 8645732910a4ad8367f0c35b4c45228ad9b2e86f
echo "Done."
