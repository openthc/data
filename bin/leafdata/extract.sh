#!/bin/bash -x
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
# Explicit list, in preferred order
file_list="
Licensees_0
Users_0
MmeUser_0
Strains_0
Areas_0
InventoryTransfers_0
InventoryTransferItems_0
InventoryTypes_0
Inventories_0
InventoryAdjustments_0
InventoryAdjustments_1
InventoryAdjustments_2
LabResults_0
Sales_0
Sales_1
SaleItems_0
SaleItems_1
SaleItems_2
SaleItems_3
"

for f in $file_list;
do
	echo "FILE: $f"

	if [ ! -f "$RAW_SOURCE_DIR/$f.zip" ]
	then
		echo "ZIP NOT FOUND"
		continue
	fi

	chk=$(zipinfo $RAW_SOURCE_DIR/$f.zip | grep '1 file')
	if [ -z "$chk" ]
	then
		echo "Invalid Zip File for: $f"
		continue
	fi

	unzip "$RAW_SOURCE_DIR/$f.zip" -d "$RAW_SOURCE_DIR"
	if [ ! -f "$RAW_SOURCE_DIR/$f.csv" ]
	then
		echo "CSV NOT FOUND"
		continue
	fi


	echo "CONV"
	touch "$RAW_SOURCE_DIR/$f.csv"
	iconv -f UTF-16LE -t ASCII//TRANSLIT "$RAW_SOURCE_DIR/$f.csv" | tee "$RAW_SOURCE_DIR/$f.tsv" | wc -l | tee "$RAW_SOURCE_DIR/$f.max"

	echo "DONE"
	rm "$RAW_SOURCE_DIR/$f.zip"
	rm "$RAW_SOURCE_DIR/$f.csv"

done
