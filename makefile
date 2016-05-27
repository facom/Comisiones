clean:
	touch delete.pyc delete~
	rm -r *.pyc
	find . -name "*~" -exec rm {} \;

cleandb:clean
	mysql -u comisiones --password="123" < initialize.sql
	python insert.py data/profesores-fcen.csv
	touch comisiones/delete
	rm -r comisiones/*
	touch scratch/delete
	rm -r scratch/*

commit:
	@echo "Commiting changes..."
	@-git commit -am "Commit"
	@git push origin master

pull:
	@echo "Pulling from repository..."
	@git reset --hard HEAD	
	@git pull
	@chown -R www-data.www-data .

backup:
	@echo "Backuping comisiones..."
	@bash combackup.sh Quakes

restore:
	@echo "Restoring table Quakes..."
	@-cat etc/data/dump/comisiones* > etc/data/comisiones.tar.7z
	@-p7zip -d etc/data/comisiones.tar.7z
	@-tar xf etc/data/comisiones.tar
	@echo "Enter root mysql password..."
	@mysql -u root -p Comisiones < etc/data/comisiones.sql
	@p7zip etc/data/comisiones.tar

permissions:
	@echo "Setting web permissions..."
	@chown -R www-data.www-data .
