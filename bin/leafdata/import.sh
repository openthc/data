#!/bin/bash
#
# Import from LeafData from Specified Source Path
#

set -o errexit
set -o nounset

f=$(readlink -f "$0")
d=$(dirname "$f")

cd "$d"
cd ../../

RAW_SOURCE_DIR=$(readlink -f "$1")
if [ -z "$RAW_SOURCE_DIR" ]
then
	echo "Provide a Source Path of Files"
	exit 1
fi

#
# Import Each
#
./bin/leafdata/import-license.php "$RAW_SOURCE_DIR/Licensees_0.tsv" 2>&1 | tee -a ./output-data/import-license.out
./bin/leafdata/import-contact.php "$RAW_SOURCE_DIR/Users_0.tsv" 2>&1 | tee -a ./output-data/import-contact.out
./bin/leafdata/import-contact.php "$RAW_SOURCE_DIR/MmeUser_0.tsv" 2>&1 | tee -a ./output-data/import-contact.out

./bin/leafdata/import-b2b-sale.php "$RAW_SOURCE_DIR/InventoryTransfers_0.tsv" 2>&1 | tee -a ./output-data/import-b2b-sale.out
./bin/leafdata/import-b2b-sale-item.php "$RAW_SOURCE_DIR/InventoryTransferItems_0.tsv" 2>&1 | tee -a ./output-data/import-b2b-sale-item.out
# ./bin/leafdata/import-b2b-sale-item.php "$RAW_SOURCE_DIR/InventoryTransferItems_1.tsv" 2>&1 | tee -a ./output-data/import-b2b-sale-item.out

./bin/leafdata/import-product.php "$RAW_SOURCE_DIR/InventoryTypes_0.tsv" 2>&1 | tee -a ./output-data/import-product.out
./bin/leafdata/import-lot.php "$RAW_SOURCE_DIR/Inventories_0.tsv" 2>&1 | tee -a ./output-data/import-lot.out
./bin/leafdata/import-lab-result.php "$RAW_SOURCE_DIR/LabResults_0.tsv" 2>&1 | tee -a ./output-data/import-lab-result.out

./bin/leafdata/import-b2c-sale.php "$RAW_SOURCE_DIR/Sales_0.tsv" 2>&1 |tee -a ./output-data/import-b2c-sale.out
./bin/leafdata/import-b2c-sale.php "$RAW_SOURCE_DIR/Sales_1.tsv" 2>&1 |tee -a ./output-data/import-b2c-sale.out

./bin/leafdata/import-b2c-sale-item.php "$RAW_SOURCE_DIR/SaleItems_0.tsv" 2>&1 | tee -a output-data/import-b2c-sale-item.out
./bin/leafdata/import-b2c-sale-item.php "$RAW_SOURCE_DIR/SaleItems_1.tsv" 2>&1 | tee -a output-data/import-b2c-sale-item.out
./bin/leafdata/import-b2c-sale-item.php "$RAW_SOURCE_DIR/SaleItems_2.tsv" 2>&1 | tee -a output-data/import-b2c-sale-item.out
./bin/leafdata/import-b2c-sale-item.php "$RAW_SOURCE_DIR/SaleItems_3.tsv" 2>&1 | tee -a output-data/import-b2c-sale-item.out


#
# Review and repair the data
# Not refined yet
#

# ./bin/leafdata/review-license.php 2>&1 | tee ./output-data/review-license.out
# ./bin/leafdata/review-variety.php
# ./bin/leafdata/review-product.php
# ./bin/leafdata/review-lab.php

./bin/leafdata/review-b2b-sale-item.php 2>&1 | tee ./output-data/review-b2b-sale-item.out
./bin/leafdata/review-b2b.php 2>&1 | tee ./output-data/review-b2b.out

./bin/leafdata/review-b2c-sale-item.php 2>&1 | tee ./output-data/review-b2c-sale-item.out
./bin/leafdata/review-b2c.php 2>&1 | tee ./output-data/review-b2c.out
