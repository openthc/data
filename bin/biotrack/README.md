# Loading BioTrack Data

The SOURCE_DSN and TARGET_DSN should be pgsql connection strings.
The LICENSE_CODE must be provided to know which license in the database to copy.
The SECTION_NAME is optional and will put everything into that SECTION; otherewise the import tries to map rooms.

```
./import-sql.php \
	--source="pgsql:SOURCE_DSN"
	--target="pgsql:TARGET_DSN"
	--license="LICENSE_CODE"
	--section="SECTION_NAME"
```


## Legacy From WA-LCB Data Dumps

Fetch the database from the state system, it's stored in ./var/biotrack_*

Then run the scripts in `./bin`
Once that is finished the BioTrack Database will be mostly ready to go.

	export SQL_FILE="/opt/data.openthc.org/var/biotrackthc_20170228.db"
	./bin/load-biotrack-01.sh
	./bin/load-biotrack-02.sh
	./bin/load-biotrack-03.sh

Run `php ./bin/load-biotrack-02.php` to fill out extended data

Now prime the datasets for the web view:

```
bash -x ./bin/data-prime-01.sh
bash -x ./bin/data-prime-02.sh

zgrep 'data.openthc.org' /var/log/apache2/access.log* >> request.raw
cut -d'"' -f2 request.raw > request.log
sed  -i 's/GET //' request.log
sed  -i 's/OPTIONS //' request.log
sed  -i 's/ HTTP\/1.1//' request.log
sed  -i 's/ HTTP\/1.0//' request.log
# sed  -i 'd/^*$/' request.log
sort request.log > x && mv x request.log
uniq -c request.log | sort -nr > x && mv x request.log
uniq -c request.log |sort -nr |awk ' $1 > 4 { print $2 }'
for U in $(uniq -c request.log |sort -nr |awk '{ print $2 }'); do echo "curl -qs \"https://data.openthc.org$U\""; done
for U in $(uniq -c request.log |sort -nr |awk '{ print $2 }'); do echo "curl -qs \"https://data.openthc.org$U\""; done > x.sh
```
