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
	@cat data/sql/dump/Quakes.sql.7z-* > data/sql/Quakes.sql.7z
	@p7zip -d data/sql/Quakes.sql.7z
	@echo "Enter root mysql password..."
	@mysql -u root -p tQuakes < data/sql/Quakes.sql
	@p7zip data/sql/Quakes.sql
