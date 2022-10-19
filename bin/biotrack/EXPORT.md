# BioTrack Integration

How to import from BioTrack system; details on how their systems work; etc.


## Migrate/Export

To Migrate from BioTrack remote access the BioTrack server.
Then use pgAdmin to export the details.
Connection should use `127.0.0.1:5432`

* Add Server to PGADmin
* File -> Add Server
  * Host: 127.0.0.1 (NOT "localhost")
  * Then Double Click "Server Groups" to make it re-load that stuff.

Choose the database and do Backup

* Filename: In the Users Directory (not desktop cause it makes a bunch of noise on Windows UI)
* Format: Plain
* Encoding: UTF-8
* Dump Options 1:
  * Data: Check
  * Blobs: Check
  * Schema: Check
* Dump Options 2:
  * I forget :(
* Objects:
  * Uncheck ALL the Accounting
  * Uncheck ALL "*.log" and most of the other stuff, cause don't need it all.

## Migrate/Import

Use pg_restore to turn the custom pg_dump into an SQL file (if it's from Custom)
A Plain file would use only `psql` to import.
Then add the tables you want to create them in the database.
You could do the whole database, but we really ony need these few tables.

```
pg_restore \
  --table=strains \
  --table=rooms \
  --table=bio_manifest_stop_data \
  --table=bio_manifest_stop_items \
  --table=bio_manifests           \
  --table=inventory               \
  --table=inventorytransfers      \
  --table=plants                  \
  --table=products \
  bt-2022-138.backup \
  -f- > \
  bt-2022-138.import-lite.sql
```


## Import to Client Database

Create SSH tunnel to database server, then run the import script

```
user@host: ssh -f -L 42095:10.3.3.1:5432 -N openthc.sql1
user@host: ./bin/import-biotrack-sql.php $DSN_SOURCE $DSN_TARGET
```


## See Also

`helium:/home/atom/Desktop/OpenTHC/Client/`

There are scripts to migrate the data there from the BioTrack Table
Somewhere we have directions on how to pgAdmin export as well.
