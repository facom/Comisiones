#-*-coding:utf-8-*-
from sys import exit
from os import system
import commands
System=commands.getoutput

dirs=System("ls -d ?????").split("\n")
comfields=System("cat fields.txt").split("\n")

#COMISIONES
sql="use Comisiones;\n"
for direct in dirs:
    print "Untrashing %s..."%direct
    fl=open("%s/comision-%s.txt"%(direct,direct),"r")
    fields=""
    values=""
    afields=[]
    for line in fl:
        line=line.strip("\r\n")
        parts=line.split(" = ")
        if 'extra' in parts[0]:continue
        if (parts[0] in comfields) and (parts[0] not in afields):
            afields+=[parts[0]]
            fields+="%s,"%parts[0]
            values+="'%s',"%parts[1]
    fl.close()
    fields+="qtrash"
    values+="'1'"
    sql+="insert into Comisiones (%s) values (%s);\n"%(fields,values)
    system("cp -rf %s ../comisiones/"%direct)

fm=open("untrash.sql","w")
fm.write("%s"%sql)
fm.close()
