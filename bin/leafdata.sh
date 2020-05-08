#!/bin/bash -x
#
# Import from LeafData
#

set -o errexit
set -o nounset

f=$(readlink -f "$0")
d=$(dirname "$f")

RAW_SOURCE_DIR=$(readlink -f "$1")

cd "$d"
cd ..


#
# Extract the ZIP Files from LeafData
#
file_list="
Licensees_0
Users_0
MmeUser_0
Strains_0
InventoryTransfers_0
InventoryTransferItems_0
InventoryTypes_0
Inventories_0
InventoryAdjustments_0
LabResults_0
Sales_0
Sales_1
SaleItems_0
SaleItems_1
SaleItems_2
"

for f in $file_list;
do
	if [ ! -f "$RAW_SOURCE_DIR/$f.zip" ]
	then
		continue
	fi

	chk=$(zipinfo $RAW_SOURCE_DIR/$f.zip | grep '1 file')
	if [ -z "$chk" ]
	then
		echo "Invalid Zip File for: $f"
		continue
	fi

	unzip "$RAW_SOURCE_DIR/$f.zip" -d "$RAW_SOURCE_DIR"
	if [ -f "$RAW_SOURCE_DIR/$f.csv" ]
	then
		rm "$RAW_SOURCE_DIR/$f.zip"
		touch "$RAW_SOURCE_DIR/$f.csv"
		iconv -f UTF-16LE -t ASCII//TRANSLIT "$RAW_SOURCE_DIR/$f.csv" > "$RAW_SOURCE_DIR/$f.tsv"
		rm "$RAW_SOURCE_DIR/$f.csv"
	fi

done


#
# Import Each
#
./bin/leafdata/import-license.php "$RAW_SOURCE_DIR/Licensees_0.tsv" 2>&1 | tee -a ./output-data/import-license.out

./bin/leafdata/import-contact.php "$RAW_SOURCE_DIR/Users_0.tsv" 2>&1 | tee -a ./output-data/import-contact.out

./bin/leafdata/import-b2b-sale.php "$RAW_SOURCE_DIR/InventoryTransfers_0.tsv" 2>&1 | tee -a ./output-data/import-b2b-sale.out

./bin/leafdata/import-b2b-sale-item.php "$RAW_SOURCE_DIR/InventoryTransferItems_0.tsv" 2>&1 | tee -a ./output-data/import-b2b-sale-item.out

./bin/leafdata/import-product.php "$RAW_SOURCE_DIR/InventoryTypes_0.tsv" 2>&1 | tee -a ./output-data/import-product.out

./bin/leafdata/import-lot.php "$RAW_SOURCE_DIR/Inventories_0.tsv" 2>&1 | tee -a ./output-data/import-lot.out

./bin/leafdata/import-lab-result.php "$RAW_SOURCE_DIR/LabResults_0.tsv" 2>&1 | tee -a ./output-data/import-lab-result.out

./bin/leafdata/import-b2c-sale.php "$RAW_SOURCE_DIR/Sales_0.tsv" 2>&1 |tee -a ./output-data/import-b2c-sale.out

./bin/leafdata/import-b2c-sale.php "$RAW_SOURCE_DIR/Sales_1.tsv" 2>&1 |tee -a ./output-data/import-b2c-sale.out

./bin/leafdata/import-b2c-sale-item.php "$RAW_SOURCE_DIR/SaleItems_0.tsv" 2>&1 | tee -a output-data/import-b2c-sale-item.out

./bin/leafdata/import-b2c-sale-item.php "$RAW_SOURCE_DIR/SaleItems_1.tsv" 2>&1 | tee -a output-data/import-b2c-sale-item.out

./bin/leafdata/import-b2c-sale-item.php "$RAW_SOURCE_DIR/SaleItems_2.tsv" 2>&1 | tee -a output-data/import-b2c-sale-item.out

#
# Review and repair the data
# Not refined yet
#

# ./bin/leafdata/review-license.php 2>&1 | tee ./output-data/review-license.out
# ./bin/leafdata/review-b2b.php
# ./bin/leafdata/review-b2c.php
# ./bin/leafdata/review-b2c-sale-item.php

# ./bin/leafdata/review-strain.php
