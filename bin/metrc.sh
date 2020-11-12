#!/bin/bash -x
#
# Import from METRC
#

PROGRAM_KEY=""
LICENSE_KEY=""

set -o errexit

RAW_SOURCE_DIR="/mnt/leafdata"

f=$(readlink -f "$0")
d=$(dirname "$f")

cd "$d"
cd ..


./metrc/import-section.php
./metrc/import-variety.php
./metrc/import-product.php
./metrc/import-lot.php
./metrc/import-plant.php
./metrc/import-plant-batch.php
