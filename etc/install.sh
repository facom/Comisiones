#!/bin/bash
for file in $(ls *.ini)
do
    filename=$(echo $file | awk -F'.ini' '{print $1}')
    echo "Installing $filename..."
    if [ -e $filename ];then cp $filename $filename.save;fi
    cp $file $filename
done
