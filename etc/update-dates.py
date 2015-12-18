#-*-coding:utf-8-*-
from comisiones import *
comisiones,connection=loadDatabase()
db=connection.cursor()

comids=comisiones["Comisiones"]["rows"].keys()
ncomids=len(comids)

i=0
for comisionid in comids:
    i+=1
    comision=comisiones["Comisiones"]["rows"][comisionid]
    print "Comision %d/%d: "%(i,ncomids),comisionid
    print "\tFecha: ",comision["fecha"]

    if comision["fechaini"] is not None:
        print "\tFechas ya fijadas: ",comision["fechaini"],"-",comision["fechafin"]
        ans=raw_input("\t¿Corregir?...")
        if ans=="":continue

    fecha=comision["fecha"]
    print "\t",;fechaini=raw_input("Fecha ini (Mes-Dia): ")
    if '2016' not in fechaini:
        fechaini="2015-%s"%fechaini
    print "\t",;fechafin=raw_input("Fecha end [%s]: "%fechaini)
    if fechafin=="":
        fechafin=fechaini
    else:
        if '2016' not in fechafin:
            fechafin="2015-%s"%fechafin
    comision["fechaini"]=fechaini
    comision["fechafin"]=fechafin
    print "\tFechas guardadas: %s - %s"%(fechaini,fechafin)
    updateDatabase(comisiones,connection)
    
