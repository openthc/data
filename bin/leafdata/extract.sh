#!/bin/bash
#
# Extract the ZIP to "CVS" to ".tsv" from the given directory
#

set -o errexit
set -o nounset

f=$(readlink -f "$0")
d=$(dirname "$f")

RAW_SOURCE_DIR=$(readlink -f "$1")

cd "$RAW_SOURCE_DIR"

#
# Extract the ZIP Files from LeafData
for f in $RAW_SOURCE_DIR/*.zip;
do
	n=$(basename "$f" ".zip")

	echo "FILE: $f / $n"

	# Check Zip
	chk=$(zipinfo "$f" | grep '1 file')
	if [ -z "$chk" ]
	then
		echo "Invalid Zip File for: $f"
		continue
	fi

	# Extract Zip
	unzip "$f" -d "$RAW_SOURCE_DIR"
	if [ -f "$RAW_SOURCE_DIR/$n.csv" ]
	then
		rm "$f"
	else
		echo "CSV NOT FOUND: $n.csv"
	fi

done

#
# spin CSV files
for f in $RAW_SOURCE_DIR/*.csv;
do
	n=$(basename "$f" ".csv")

	iconv -f UTF-16LE -t ASCII//TRANSLIT "$f" | tee "$RAW_SOURCE_DIR/$n.tsv" | wc -l | tee "$RAW_SOURCE_DIR/$n.max"

	rm "$f"

done
