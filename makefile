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
	@git commit -am "Commit"
	@git push origin master

pull:
	@echo "Pulling from repository..."
	@git reset --hard HEAD	
	@git pull
	@chown -R www-data.www-data .
