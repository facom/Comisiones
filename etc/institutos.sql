use Comisiones;

insert into Institutos (institutoid,instituto,cedulajefe,emailinst)
       values ('fisica','Instituto de Física','71755170',
       'pregradofisica@udea.edu.co') on duplicate key update cedulajefe=VALUES(cedulajefe),emailinst=VALUES(emailinst);

insert into Institutos (institutoid,instituto,cedulajefe,emailinst)
       values ('quimica','Instituto de Química','71755175',
       'pregradofisica@udea.edu.co') on duplicate key update cedulajefe=VALUES(cedulajefe),emailinst=VALUES(emailinst);

insert into Institutos (institutoid,instituto,cedulajefe,emailinst)
       values ('biologia','Instituto de Biología','71755176',
       'pregradofisica@udea.edu.co') on duplicate key update cedulajefe=VALUES(cedulajefe),emailinst=VALUES(emailinst);

insert into Institutos (institutoid,instituto,cedulajefe,emailinst)
       values ('matematicas','Instituto de Matemáticas','71755177',
       'pregradofisica@udea.edu.co') on duplicate key update cedulajefe=VALUES(cedulajefe),emailinst=VALUES(emailinst);

insert into Institutos (institutoid,instituto,cedulajefe,emailinst)
       values ('decanatura','Decanatura','71755179',
       'pregradofisica@udea.edu.co') on duplicate key update cedulajefe=VALUES(cedulajefe),emailinst=VALUES(emailinst);
