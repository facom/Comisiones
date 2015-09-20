#!/bin/bash
backfile="scratch/backup-comisiones.tar"

if [ -e $backfile.gz ];then rm $backfile.gz;fi

echo "Backuping database..."
mysqldump -u root --password=diplomaastro Comisiones > scratch/comisiones.sql

echo "Backuping data..."
tar cf $backfile comisiones 

echo "Compressing..."
tar rf $backfile scratch/comisiones.sql 
gzip $backfile

echo "Done."
