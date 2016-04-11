use Comisiones;
/*
alter table Comisiones add cumplido1 varchar(255);
alter table Comisiones add cumplido2 varchar(255);
alter table Comisiones add qcumplido varchar(1);
alter table Comisiones add infocumplido varchar(1000);
alter table Comisiones add confirmacumplido varchar(1000);
alter table Comisiones add destinoscumplido varchar(1000);
alter table Comisiones add fechaini date;
alter table Comisiones add fechafin date;
alter table Profesores add dedicacion varchar(10);

update Profesores set dedicacion='No';

update Comisiones set estado='cumplida',qcumplido=1,cumplido1='empty.pdf',cumplido2='',destinoscumplido='pregradofisica@udea.edu.co;',confirmacumplido='pregradofisica@udea.edu.co::2015-12-18 12:06:56;',infocumplido='Cumplido de comisión otorgada.' where (tipocom='servicios' or tipocom='estudio') and fechafin<now();

update Profesores set pass=md5(pass);
*/
insert ignore into Profesores (tipoid,cedula,nombre,email,tipo,pass,institutoid) values ('cedula','1128268832','JOHNY JARAMILLO','jdex87@gmail.com','Ocasional',md5('1128268832'),'fisica');
