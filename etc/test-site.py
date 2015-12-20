#-*-coding:utf-8-*-
"""
Cambia las cédulas y el correo electrónico de todos
"""
from comisiones import *
import numpy
comisiones,connection=loadDatabase()
db=connection.cursor()

numpy.random.seed(1)

docids=comisiones["Profesores"]["rows"].keys()
i=1
for docid in docids:
    profesor=comisiones["Profesores"]["rows"][docid]
    cedula=profesor["cedula"]
    ncedula=cedula+"%d"%(10*numpy.random.rand())
    
    # print "Cambiando cedula %s por %s..."%(cedula,ncedula)

    sql="update Comisiones set cedula='%s' where cedula like '%s%%';"%(ncedula,cedula)
    # print sql
    db.execute(sql)
    connection.commit()

    sql="update Profesores set cedula='%s',pass=md5('%s') where cedula='%s';"%(ncedula,ncedula,cedula)
    # print sql
    db.execute(sql)
    connection.commit()
    
    if cedula=='42778064':cedulasecre=ncedula
    if cedula=='43623917':cedulafisica=ncedula
    if cedula=='98523088':cedulajefe=ncedula
    if cedula=='66812679':ceduladecana=ncedula
    if cedula=='71755174':cedulamain=ncedula
    if cedula=='98554575':cedulaprofe=ncedula
    # print 

    i+=1

# CAMBIA EL CORREO ELECTRONICO DE TODOS
fixemail1="astronomia.udea@gmail.com" # Deana
fixemail2="sofia.zuluaga.penagos@gmail.com" # Secre Decanatura
fixemail3="jorge.zuluaga@udea.edu.co" # Jefe Instituto
fixemail4="pregradofisica@udea.edu.co" # Secre instituto
fixemail5="zuluagajorge@gmail.com" # Profesor

# ALL PROFESORES
sql="update Profesores set email='%s'"%(fixemail5)
db.execute(sql)
connection.commit()

# DECANA
sql="update Profesores set email='%s' where cedula='%s'"%(fixemail1,ceduladecana)
db.execute(sql)
connection.commit()

# SECRE DECANATO
sql="update Profesores set email='%s' where cedula='%s'"%(fixemail2,cedulasecre)
db.execute(sql)
connection.commit()

# JEFE
sql="update Profesores set email='%s' where cedula='%s'"%(fixemail3,cedulajefe)
db.execute(sql)
connection.commit()

# SECRE INSTITUTO
sql="update Profesores set email='%s' where cedula='%s'"%(fixemail4,cedulafisica)
db.execute(sql)
connection.commit()

# SECRETARIA DECANATO
sql="update Institutos set cedulajefe='%s',emailinst='%s' where institutoid='fisica'"%(cedulajefe,fixemail2)
db.execute(sql)
connection.commit()

# SECRETARIA INSTITUTO
sql="update Institutos set cedulajefe='%s',emailinst='%s' where institutoid='decanatura'"%(ceduladecana,fixemail4)
db.execute(sql)
connection.commit()

print "Cedula decana: %s (email: %s)"%(ceduladecana,fixemail1)
print "Cedula secre. decana: %s (email: %s)"%(cedulasecre,fixemail2)
print "Cedula jefe fisica: %s (email: %s)"%(cedulajefe,fixemail3)
print "Cedula secre. fisica: %s (email: %s)"%(cedulafisica,fixemail4)
print "Cedula maintainance: %s (email: %s)"%(cedulamain,fixemail5)
print "Cedula profesor: %s (email: %s)"%(cedulaprofe,fixemail5)
