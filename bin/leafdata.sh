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
LabResults_0
Strains_0
InventoryTransfers_0
InventoryTransferItems_0
InventoryTypes_0
Inventories_0
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

# ln -s "$RAW_SOURCE_DIR/Licensees_0.tsv" ./source-data/license.tsv
#time ./bin/leafdata/import-license.php 2>&1 | tee ./output-data/import-license.out
# 0m2.157s

# ln -s "$RAW_SOURCE_DIR/Users_0.tsv" ./source-data/contact.tsv
#time ./bin/leafdata/import-contact.php 2>&1 | tee ./output-data/import-contact.out
# 0m9.901s

#time ./bin/leafdata/review-license.php 2>&1 | tee ./output-data/review-license.out
#

#ln -s "$RAW_SOURCE_DIR/InventoryTransfers_0.tsv" ./source-data/b2b-sale.tsv
#time ./bin/leafdata/import-b2b-sale.php 2>&1 | tee output-data/import-b2b-sale.out
# 16m29.337s


#ln -s "$RAW_SOURCE_DIR/InventoryTransferItems_0.tsv" ./source-data/b2b-sale-item.tsv
#time ./bin/leafdata/import-b2b-sale-item.php 2>&1 | tee output-data/import-b2b-sale-item.out

#
# time ./bin/leafdata/review-b2b.php


#ln -s "$RAW_SOURCE_DIR/InventoryTypes_0.tsv" ./source-data/product.tsv
#time ./bin/leafdata/import-product.php 2>&1 | tee output-data/import-product.out
#./bin/leafdata/review-product.php
#rm "$RAW_SOURCE_DIR/InventoryTypes_0.tsv"
# 336m0.375s

#ln -s "$RAW_SOURCE_DIR/Inventories_0.tsv" ./source-data/lot.tsv
#time ./bin/leafdata/import-lot.php 2>&1 | tee output-data/import-lot.out
# 740m43.605s


#ln -s "$RAW_SOURCE_DIR/Sales_0.tsv" ./source-data/b2c-sale.tsv
#time ./bin/leafdata/import-b2c-sale.php 2>&1 |tee output-data/import-b2c-sale.out
# rm "$RAW_SOURCE_DIR/Sales_0.tsv"
# #0 = 1258/s in 1324m7.276s
# #1 = 1088/s in 712m29.780s


#ln -s "$RAW_SOURCE_DIR/SaleItems_0.tsv" ./source-data/b2c-sale-item.tsv
#time ./bin/leafdata/import-b2c-sale-item.php 2>&1 | tee -a output-data/import-b2c-sale-item.out
#rm "$RAW_SOURCE_DIR/SaleItems_0.tsv"

#ln -s "$RAW_SOURCE_DIR/SaleItems_1.tsv" ./source-data/b2c-sale-item.tsv
#time ./bin/leafdata/import-b2c-sale-item.php 2>&1 | tee -a output-data/import-b2c-sale-item.out
#rm "$RAW_SOURCE_DIR/SaleItems_1.tsv"

# Not refined yet

# ./bin/leafdata/import-lab-result.php

# ./bin/leafdata/review-strain.php

# ./bin/leafdata/review-b2c.php
