#-*-coding:utf-8-*-
from comisiones import *
comisiones,connection=loadDatabase()
db=connection.cursor()

# ############################################################
# LOAD SQL FILE INSTITUTOS
# ############################################################
print "Updating institutes information..."
system("mysql -u %s --password='%s' < institutos.sql"%(USER,PASSWORD))


# ############################################################
# LOAD CSV FILE WITH PROFESORES
# ############################################################
filecsv=argv[1]
csvfile=open("%s"%filecsv,"rU")
content=csv.DictReader(csvfile,dialect="excel",delimiter=";")
profesores=dict()
for row in content:
    cedula=row['cedula']
    if cedula=='':continue
    profesores['fields']=row.keys()
    profesores[cedula]=dict()
    row["pass"]=row["cedula"]
    profesores[cedula].update(row)
profesores['fields']+=["pass"]
csvfile.close()

# ############################################################
# DATABASE COMMAND
# ############################################################
print "Updating Profesores information..."
fieldstxt="("
fieldsup=""
for field in profesores["fields"]:
    fieldstxt+="%s,"%field
    if field!="pass":
        fieldsup+="%s=VALUES(%s),"%(field,field)
fieldstxt+="extra1,extra2,"
fieldsup+="extra1=VALUES(extra1),extra2=VALUES(extra2),"
fieldstxt=fieldstxt.strip(",")
fieldsup=fieldsup.strip(",")
fieldstxt+=")"

year=str(date.today()).split("-")[0]
for cedula in profesores.keys():
    sql=""
    if cedula=='fields':continue
    sql+="insert into Profesores %s"%(fieldstxt)
    profesor=profesores[cedula]
    sql+=" values ("
    for field in profesores["fields"]:
        sql+="'%s',"%profesor[field]
    sql+="'6','%s',"%year
    sql=sql.strip(",")
    sql+=") on duplicate key update %s;\n"%fieldsup
    db.execute(sql)

connection.commit()
