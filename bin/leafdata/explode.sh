#!/bin/bash
#
# Split TSV Files to Yearly Options
#

set -o errexit
set -o nounset

f=$(readlink -f "$0")
d=$(dirname "$f")

RAW_SOURCE_DIR=$(readlink -f "$1")

cd "$RAW_SOURCE_DIR"


function _split_file_once()
{
	base="$1"

	# sed into four file, four passes arross the base file
	# head -n1 "$base.tsv" > "$base-2018.tsv"
	# sed -En '/^WA\w+\.\w+\s+2018/p' "$base.tsv" | tee -a "$base-2018.tsv" | wc -l > "$base-2018.max"

	# head -n1 "$base.tsv" > "$base-2019.tsv"
	# sed -En '/^WA\w+\.\w+\s+2019/p' "$base.tsv" | tee -a "$base-2019.tsv" | wc -l > "$base-2019.max"

	# head -n1 "$base.tsv" > "$base-2020.tsv"
	# sed -En '/^WA\w+\.\w+\s+2020/p' "$base.tsv" | tee -a "$base-2020.tsv" | wc -l > "$base-2020.max"

	head -n1 "$base.tsv" > "$base-2021.tsv"
	sed -En '/^WA\w+\.\w+\s+2021/p' "$base.tsv" | tee -a "$base-2021.tsv" | wc -l > "$base-2021.max"

}


function _split_file_many()
{

	# build a map file, then split in four ways at once
	head -n1 "$base.tsv" > "$base.map"
	sed -En \
		-e "/^WA\w+\.\w+\s+2018/w $base-2018.tsv" \
		-e "/^WA\w+\.\w+\s+2019/w $base-2019.tsv" \
		-e "/^WA\w+\.\w+\s+2020/w $base-2020.tsv" \
		-e "/^WA\w+\.\w+\s+2021/w $base-2021.tsv" \
		"$base.tsv"

	# but now we have to count them all to get the MAX value
	wc -l "$base-2018.tsv" > "$base-2018.max"
	wc -l "$base-2019.tsv" > "$base-2019.max"
	wc -l "$base-2020.tsv" > "$base-2020.max"
	wc -l "$base-2021.tsv" > "$base-2021.max"

}


# _split_file "Inventories_0"
# _split_file "InventoryTypes_0"
# _split_file "LabResults_0"
# _split_file "InventoryTransfers_0"
# _split_file "InventoryTransferItems_0"
# sed -En -e '/^WA\w+\.\w+\s+2020/w x2020.tsv' -e '/^WA\w+\.\w+\s+2021/w x2021.tsv' "$base.tsv" | tee -a "$base-2021.tsv" | wc -l > "$base-2021.max"


_split_file_once "InventoryTypes_0"
_split_file_once "Inventories_0"
_split_file_once "LabResults_0"
_split_file_once "InventoryTransfers_0"
_split_file_once "InventoryTransferItems_0"
# _split_file_once ""

