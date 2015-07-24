use Comisiones;

insert into Institutos (institutoid,instituto,cedulajefe,emailinst)
       values ('fisica','Instituto de Física','98523088',
       'pregradofisica@udea.edu.co') on duplicate key update cedulajefe=VALUES(cedulajefe),emailinst=VALUES(emailinst);

insert into Institutos (institutoid,instituto,cedulajefe,emailinst)
       values ('quimica','Instituto de Química','00000000',
       'pregradofisica@udea.edu.co') on duplicate key update cedulajefe=VALUES(cedulajefe),emailinst=VALUES(emailinst);

insert into Institutos (institutoid,instituto,cedulajefe,emailinst)
       values ('biologia','Instituto de Biología','0000000',
       'pregradofisica@udea.edu.co') on duplicate key update cedulajefe=VALUES(cedulajefe),emailinst=VALUES(emailinst);

insert into Institutos (institutoid,instituto,cedulajefe,emailinst)
       values ('matematicas','Instituto de Matemáticas','0000000',
       'pregradofisica@udea.edu.co') on duplicate key update cedulajefe=VALUES(cedulajefe),emailinst=VALUES(emailinst);

insert into Institutos (institutoid,instituto,cedulajefe,emailinst)
       values ('decanatura','Decanatura','66812679',
       'decaexactas@udea.edu.co') on duplicate key update cedulajefe=VALUES(cedulajefe),emailinst=VALUES(emailinst);
