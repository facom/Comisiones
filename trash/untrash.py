#-*-coding:utf-8-*-
from sys import exit
from os import system
import commands
System=commands.getoutput

out=System("ls -d ?????")

dirs=out.split("\n")

sql=""
for direct in dirs:
    fl=open("%s/comision-%s.txt"%(direct,direct),"r")
    fields=""
    values=""
    for line in fl:
        line=line.strip("\r\n")
        parts=line.split(" = ")
        fields+="%s,"%parts[0]
        values+="'%s',"%parts[1]
    fields+="qtrash"
    values+="'1'"
    sql+="insert into Comisiones (%s) values (%s)\n"%(fields,values)
    
    print sql
    break

