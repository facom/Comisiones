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
print "Updating Profesores information..."
filecsv=argv[1]
csvfile=open("%s"%filecsv,"rU")
content=csv.DictReader(csvfile,dialect="excel",delimiter=";")
profesores=dict()
for row in content:
    cedula=row['cedula']
    if cedula=="":continue
    profesores['fields']=row.keys()
    profesores[cedula]=dict()
    """
    for key in row.keys():
        print type(row[key])
        print key
        print row[key]
        row[key]=row[key].decode('utf-8')
        print row[key]
        """
    row["pass"]=row["cedula"]
    profesores[cedula].update(row)
profesores['fields']+=["pass"]
csvfile.close()

# ############################################################
# DATABASE COMMAND
# ############################################################
fieldstxt="("
for field in profesores["fields"]:
    fieldstxt+="%s,"%field
fieldstxt=fieldstxt.strip(",")
fieldstxt+=")"

for cedula in profesores.keys():
    sql=""
    if cedula=='fields':continue
    sql+="insert into Profesores %s"%(fieldstxt)
    profesor=profesores[cedula]
    sql+=" values ("
    for field in profesores["fields"]:
        sql+="'%s',"%profesor[field]
    sql=sql.strip(",")
    sql+=") on duplicate key update cedula='%s';\n"%cedula
    db.execute(sql)

connection.commit()
