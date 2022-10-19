#!/bin/bash
#
# SPDX-License-Identifier: GPL-3.0-only
#

set -o errexit

# Extract those ZIPs
for f in *.zip
do
	# their files are not valid
	# we have to ignore extract warnings about '\' characters
	unzip -j "$f" || true
	rm "$f"
done

# Convert that Bullshit
for f in *.csv
do
	b=$(basename "$f" ".csv")
	iconv --from-code UTF-16LE --to-code UTF-8 "$f" -o "$b.tsv"
	rm "$f"
done
