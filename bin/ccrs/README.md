# Data Import for CCRS

The WA-LCB use their home-brew system called CCRS.
To get access to the data, one must make a FOIA request.

Then run these scripts from that downloaded ZIP files.


## Process

First fetch all the files from the box.com address.
Then inflate them.
Then import each of the objects.

```shell
cd ../../source-data/
../bin/box-download.php '$BOX_URL' > x.sh
bash x.sh
../bin/inflate.sh
../bin/ccrs/import-$OBJECT.php
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
