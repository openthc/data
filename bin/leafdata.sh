#!/bin/bash -x
#
# Import from LeafData
#

set -o errexit

RAW_SOURCE_DIR="/mnt/leafdata"

f=$(readlink -f "$0")
d=$(dirname "$f")

cd "$d"
cd ..

#
# Extract the ZIP Files from LeafData
#
file_list="
SaleItems_0
SaleItems_1
Sales_0
Sales_1
Inventories_0
InventoryTransferItems_0
InventoryTypes_0
LabResults_0
InventoryTransfers_0
Strains_0
Users_0
Licensees_0
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


# Link the Files to our Source Directory
cd ./source-data/
# ln -s "$RAW_SOURCE_DIR/Licensees_0.tsv" ./license.tsv
# ln -s "$RAW_SOURCE_DIR/Users_0.tsv" ./contact.tsv
# ln -s "$RAW_SOURCE_DIR/InventoryTransfers_0.tsv" ./b2b-sale.tsv
# ln -s "$RAW_SOURCE_DIR/InventoryTransferItems_0.tsv" ./b2b-sale-item.tsv
cd ../

# Import Each
#time ./bin/leafdata/import-license.php 2>&1 | tee ./output-data/import-license.out
# 0m2.157s

#time ./bin/leafdata/import-contact.php 2>&1 | tee ./output-data/import-contact.out
# 0m9.901s

time ./bin/leafdata/review-license.php 2>&1 | tee ./output-data/review-license.out
#

#time ./bin/leafdata/import-b2b-sale.php 2>&1 | tee output-data/import-b2b-sale.out
# 16m29.337s

time ./bin/leafdata/import-b2b-sale-item.php 2>&1 | tee output-data/import-b2b-sale-item.out


time ./bin/leafdata/review-b2b.php
#

time ./bin/leafdata/import-product.php 2>&1 | tee output-data/import-product.out
#

# ./bin/leafdata/review-product.php

time ./bin/leafdata/import-lot.php 2>&1 | tee output-data/import-lot.out
#

# time ./bin/leafdata/import-b2c-sale.php 2>&1 |tee output-data/import-b2c-sale.out
## 1189m33.752s

# time ./bin/leafdata/import-b2c-sale.php 2>&1 |tee -a output-data/import-b2c-sale.out

# ln -s /mnt/leafdata/SaleItems_0.tsv ./source-data/b2c-sale-item.tsv
# time ./bin/leafdata/import-b2c-sale-item.php 2>&1 | tee -a output-data/import-b2c-sale-item.out
# rm /mnt/leafdata/b2c-sale-item.tsv

# b2c-sale-item-1
# ln -s /mnt/leafdata/b2c-sale-item.tsv ./source-data/b2c-sale-item.tsv
# time ./leafdata/import-b2c-sale-item.php 2>&1 | tee -a output-data/import-b2c-sale-item.out
# rm /mnt/leafdata/b2c-sale-item.tsv

# ./bin/leafdata/import-lab-result.php

# ./bin/leafdata/review-strain.php
# ./bin/leafdata/review-b2c.php
