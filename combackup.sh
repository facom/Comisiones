#!/bin/bash                                                       
echo "Dumping..."
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
echo "Done."
