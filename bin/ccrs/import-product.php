<?php
/**
 * Imports Product Data from CCRS
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

$dbc_main = _dbc();
$sql = <<<SQL
INSERT INTO product (pk, id, license_id, name, product_type, stat, package_type, package_size, package_unit, meta)
VALUES (:pk, :p0, :l0, :n0, :t0, :s0, :pk_type, :pk_size, :pk_unit, :m0)
ON CONFLICT DO NOTHING
SQL;
$cmd_product_insert = $dbc_main->prepare($sql);

// Import the Header Files
$source_file_list = glob('Product_*.tsv');
foreach ($source_file_list as $source_file) {

	echo "Import: {$source_file}\n";

	$csv = new \OpenTHC\Data\CSV_Reader($source_file);
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

		$cmd_product_insert->execute([
			':pk' => $row['ProductId'],
			':p0' => $row['ExternalIdentifier'],
			':l0' => $row['LicenseeId'],
			':n0' => $row['Name'],
			':t0' => $row['InventoryType'],
			':s0' => ($row['IsDeleted'] == 'TRUE' ? 410 : 200),
			':pk_type' => '-NOTSET-',
			':pk_size' => floatval($row['UnitWeightGrams']),
			':pk_unit' => 'g',
			':m0' => json_encode($row)
		]);

		_show_progress($idx, $max);

	}

}
