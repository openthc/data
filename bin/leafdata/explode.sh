#!/bin/bash
#
# Split TSV Files to Yearly Options
#
# SPDX-License-Identifier: GPL-3.0-only
#

set -o errexit
set -o nounset

f=$(readlink -f "$0")
d=$(dirname "$f")

RAW_SOURCE_DIR=$(readlink -f "$1")

cd "$RAW_SOURCE_DIR"

mkdir -p 2018 2019 2020 2021

function _split_file_once()
{
	base="$1"

	# sed into four file, four passes arross the base file
	# head -n1 "$base.tsv" > "$base-2018.tsv"
	# sed -En '/^WA\w+\.\w+\s+2018/p' "$base.tsv" | tee -a "$base-2018.tsv" | wc -l > "$base-2018.max"

	# head -n1 "$base.tsv" > "$base-2019.tsv"
	# sed -En '/^WA\w+\.\w+\s+2019/p' "$base.tsv" | tee -a "$base-2019.tsv" | wc -l > "$base-2019.max"

	head -n1 "$base.tsv" > "2020/$base.tsv"
	sed -En '/^WA\w+\.\w+\s+2020/p' "$base.tsv" | tee -a "2020/$base.tsv" | wc -l > "2020/$base-2021.max"

	head -n1 "$base.tsv" > "2021/$base.tsv"
	sed -En '/^WA\w+\.\w+\s+2021/p' "$base.tsv" | tee -a "2021/$base.tsv" | wc -l > "2021/$base-2021.max"

}


function _split_file_many()
{

	# build a map file, then split in four ways at once
	head -n1 "$base.tsv" > "$base.map"
	sed -En \
		-e "/^WA\w+\.\w+\s+2018/w 2018/$base.tsv" \
		-e "/^WA\w+\.\w+\s+2019/w 2019/$base.tsv" \
		-e "/^WA\w+\.\w+\s+2020/w 2020/$base.tsv" \
		-e "/^WA\w+\.\w+\s+2021/w 2021/$base.tsv" \
		"$base.tsv"

	# but now we have to count them all to get the MAX value
	wc -l "2018/$base.tsv" > "2018/$base.max"
	wc -l "2019/$base.tsv" > "2019/$base.max"
	wc -l "2020/$base.tsv" > "2020/$base.max"
	wc -l "2021/$base.tsv" > "2021/$base.max"

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
