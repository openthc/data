<?php
/**
 * Imports Inventory Data from CCRS
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

$dbc_main = _dbc();
$sql = <<<SQL
INSERT INTO inventory (
id
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
) VALUES (:i0, :l0, :p0, :v0, :s0, :ct, :ut, :dt, :s1, :f1, :q0, :m0)
ON CONFLICT DO NOTHING
SQL;
$cmd_inventory_insert = $dbc_main->prepare($sql);

// Import the Header Files
$source_file_list = array_slice($argv, 2);
foreach ($source_file_list as $source_file) {

	echo "Import: {$source_file}\n";

	$csv = new \OpenTHC\Data\CSV_Reader($source_file);
	// LicenseeId	InventoryId	StrainId	AreaId	ProductId	InventoryIdentifier	InitialQuantity	QuantityOnHand	TotalCost	IsMedical	ExternalIdentifier	IsDeleted	CreatedBy	CreatedDate	UpdatedBy	updatedDate
	$csv_head = $csv->getHeader();

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

		$ins = [
			':i0' => $row['EXTERNALIDENTIFIER'],
			':l0' => $row['LICENSEEID'],
			':p0' => $row['PRODUCTID'],
			':v0' => $row['STRAINID'],
			':s0' => $row['AREAID'],
			':ct' => $row['CREATEDDATE'],
			':ut' => $row['UPDATEDDATE'] ?: null,
			':dt' => null,
			':s1' => ($row['ISDELETED'] == 'TRUE' ? 410 : 200),
			':f1' => ($row['ISMEDICAL'] == 'TRUE' ? 0x02 : 0),
			':q0' => $row['QUANTITYONHAND'],
			':m0' => json_encode($row)
		];
		// print_r($ins);
		$cmd_inventory_insert->execute($ins);

		_show_progress($idx, $max);

	}

}
