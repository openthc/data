#!/bin/bash
#
#
#

set -o errexit

#TMP_DOWNLOAD_SCRIPT="OUTPUT-download.sh"

# mkdir -p source-data/
# cd source-data/

# Fetch the Applicant List?
#../bin/box-download.php 'https://lcb.app.box.com/s/3baoo2t38hr2uaizxmn0pci6872b91x3?page=1&sortColumn=name&sortDirection=ASC' > $TMP_DOWNLOAD_SCRIPT

#bash $TMP_DOWNLOAD_SCRIPT

# Extract those ZIPs
for f in *.zip
do
	unzip -j "$f"
	rm "$f"
done

# Convert that Bullshit
for f in *.csv
do
	b=$(basename "$f" ".csv")
	iconv -f UTF-16LE -t UTF-8 "$f" -o "$b.tsv"
	rm "$f"
done
