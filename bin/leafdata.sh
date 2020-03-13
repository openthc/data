#!/bin/bash -x
#
# Import from LeafData
#

set -o errexit

# Set this
RAW_SOURCE_DIR="/mnt/leafdata"

f=$(readlink -f "$0")
d=$(dirname "$f")

cd "$d"
cd ..

#
# Extract the ZIP Files from LeafData
#
file_list="
Licensees_0
Users_0
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

# Import Each

./bin/leafdata/import-license.php "$RAW_SOURCE_DIR/Licensees_0.tsv" 2>&1 | tee -a ./output-data/import-license.out
# 0m2.157s

./bin/leafdata/import-contact.php "$RAW_SOURCE_DIR/Users_0.tsv" 2>&1 | tee -a ./output-data/import-contact.out
# 0m9.901s

# ./bin/leafdata/review-license.php 2>&1 | tee ./output-data/review-license.out

./bin/leafdata/import-b2b-sale.php "$RAW_SOURCE_DIR/InventoryTransfers_0.tsv" 2>&1 | tee -a ./output-data/import-b2b-sale.out
# 16m29.337s

./bin/leafdata/import-b2b-sale-item.php "$RAW_SOURCE_DIR/InventoryTransferItems_0.tsv" 2>&1 | tee -a ./output-data/import-b2b-sale-item.out

# ./bin/leafdata/review-b2b.php

./bin/leafdata/import-product.php "$RAW_SOURCE_DIR/InventoryTypes_0.tsv" 2>&1 | tee -a ./output-data/import-product.out
# 336m0.375s

./bin/leafdata/import-lot.php "$RAW_SOURCE_DIR/Inventories_0.tsv" 2>&1 | tee -a ./output-data/import-lot.out
# ~900 minutes

./bin/leafdata/import-lab-result.php "$RAW_SOURCE_DIR/LabResults_0.tsv" 2>&1 | tee -a ./output-data/import-lab-result.out

./bin/leafdata/import-b2c-sale.php "$RAW_SOURCE_DIR/Sales_0.tsv" 2>&1 |tee -a ./output-data/import-b2c-sale.out
# ~1324m7.276s
./bin/leafdata/import-b2c-sale.php "$RAW_SOURCE_DIR/Sales_1.tsv" 2>&1 |tee -a ./output-data/import-b2c-sale.out
# ~1088/s in 712m29.780s


./bin/leafdata/import-b2c-sale-item.php "$RAW_SOURCE_DIR/SaleItems_0.tsv" 2>&1 | tee -a output-data/import-b2c-sale-item.out
# #0 = 1020m39.460s
./bin/leafdata/import-b2c-sale-item.php "$RAW_SOURCE_DIR/SaleItems_1.tsv" 2>&1 | tee -a output-data/import-b2c-sale-item.out
# #1 =  864m58.789s

# Not refined yet

# ./bin/leafdata/review-b2c-sale-item.php

#

# ./bin/leafdata/review-strain.php
