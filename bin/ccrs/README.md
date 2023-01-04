# Data Import for CCRS

The WA-LCB use their home-brew system called CCRS.
To get access to the data, one must make a FOIA request.

Then run these scripts from that downloaded ZIP files.


## Process

First fetch all the files from the box.com address.
Then inflate them.
Then import each of the objects.

```shell
cd ./source-data/
../bin/box-download.php '$BOX_URL' > box-download.sh
bash box-download.sh
../bin/ccrs/inflate.php
../bin/ccrs/import.php $OBJECT 2>&1 \
	| tee OUTPUT-$OBJECT-import.txt
```

## Line Fixing

Sometimes the lines are b0rked up because of embedded TAB characters in fields.
The script will barf and needs manual intervention.
Something like this:

```shell
head -n1 $PROBLEM_FILE > x.tsv
tail -n +$LINE_NUM $PROBLEM_FILE >> x.tsv
nano x.tsv
mv x.tsv $PROBLEM_FILE
```

## Comparing Hashes

The scripts create a file with a `.md5` extension.
It's the MD5 of the downloaded and processes TSV.
Compare these files from a prevous download to a current download to see which files can be skipped or removed.

```shell
diff -qrs \
	./source-data-old-2022-11-22/ \
	./source-data-new-2022-12-22/ \
	| awk '/ are identical/ { print $4 }' \
	| sed 's/\.md5//g' \
	| xargs -l echo rm
```
