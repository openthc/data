<?php
/**
 * Imports Inventory Data from CCRS
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

$dbc_main = _dbc();
$sql = <<<SQL
INSERT INTO inventory (
pk
,id
,license_id
,product_id
,variety_id
,section_id
,created_at
,updated_at
,deleted_at
,stat
,flag
,qty
,meta
) VALUES (:pk, :i0, :l0, :p0, :v0, :s0, :ct, :ut, :dt, :s1, :f1, :q0, :m0)
ON CONFLICT DO NOTHING
SQL;
$cmd_inventory_insert = $dbc_main->prepare($sql);

// Import the Header Files
$source_file_list = glob('Inventory_*.tsv');
foreach ($source_file_list as $source_file) {

	echo "Import: {$source_file}\n";

	$csv = new \OpenTHC\Data\CSV_Reader($source_file);
	$csv_head = $csv->getHeader();
	// fix a case-typo on LCB column name
	if ($csv_head[15] == 'updatedDate') {
		$csv_head[15] = 'UpdatedDate';
	}

	$idx = 1;
	$max = 1000000;
	$_SERVER['REQUEST_TIME_FLOAT'] = microtime(true); // reset timer base for _show_progress()

	while ($row = $csv->fetch()) {

		$idx++;

		// Fail when Name or Description have TAB
		$row = _csv_row_map_check($csv_head, $row, $idx);
		if (empty($row)) {
			continue;
		}

		// @todo should be InventoryId so it can link to sales detail?
		$ins = [
			':pk' => $row['InventoryId'],
			':i0' => $row['ExternalIdentifier'],
			':l0' => $row['LicenseeId'],
			':p0' => $row['ProductId'],
			':v0' => $row['StrainId'],
			':s0' => $row['AreaId'],
			':ct' => $row['CreatedDate'],
			':ut' => $row['UpdatedDate'] ?: null,
			':dt' => null,
			':s1' => ($row['IsDeleted'] == 'TRUE' ? 410 : 200),
			':f1' => ($row['IsMedical'] == 'TRUE' ? 0x02 : 0),
			':q0' => $row['QuantityOnHand'],
			':m0' => json_encode($row)
		];
		// print_r($ins);
		$cmd_inventory_insert->execute($ins);

		_show_progress($idx, $max);

	}

}
