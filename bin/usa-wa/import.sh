#!/bin/bash
#
# Import from LeafData from Specified Source Path
#

set -o errexit
set -o nounset

f=$(readlink -f "$0")
d=$(dirname "$f")

cd "$d"

#./import-visits.php 'https://lcb.wa.gov/sites/default/files/publications/Marijuana/Enforcement_Visits_Dataset_2021.xlsx'
#./import-violations.php 'https://lcb.wa.gov/sites/default/files/publications/Marijuana/Violations_Dataset_2021.xlsx'
#./import-compliance-checks.php 'https://lcb.wa.gov/sites/default/files/publications/Marijuana/Compliance_Checks_Dataset_2021.xlsx'

time ./import-sales-v1.php \
	'https://lcb.wa.gov/sites/default/files/publications/Marijuana/sales_activity/By-License-Number-MJ-Tax-Obligation-by-Licensee-thru-10_31_17.xlsx' \
	2>&1 \
	| tee OUTPUT-import-sales-v1.txt >/dev/null

time ./import-sales-v2.php \
	'https://lcb.wa.gov/sites/default/files/publications/Marijuana/traceability/2021-08-05-MJ-Sales-Activity-by-License-Number-Traceability-Contingency-Reporting-Retail.xlsx' \
	2>&1 \
	| tee OUTPUT-import-sales-v2.txt >/dev/null
