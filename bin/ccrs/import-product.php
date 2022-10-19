<?php
/**
 * Imports Product Data from CCRS
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

$dbc_main = _dbc();
$sql = <<<SQL
INSERT INTO product (id, license_id, name, product_type, stat, package_type, package_size, package_unit, meta)
VALUES (:p0, :l0, :n0, :t0, :s0, :pk_type, :pk_size, :pk_unit, :m0)
ON CONFLICT DO NOTHING
SQL;
$cmd_product_insert = $dbc_main->prepare($sql);

// Import the Header Files
$source_file_list = glob('Product_*.tsv');
foreach ($source_file_list as $source_file) {

	echo "Import: {$source_file}\n";

	$fh = _fopen_bom($source_file, 'r');
	$csv_head = fgetcsv($fh, null, "\t");

	$idx = 1;
	$max = 1000000;
	$_SERVER['REQUEST_TIME_FLOAT'] = microtime(true); // reset timer base for _show_progress()

	while ($row = fgetcsv($fh, null, "\t")) {

		$idx++;

		$row = _csv_row_map_check($csv_head, $row);
		if (empty($row)) {
			continue;
		}

		$row = array_combine($csv_head, $row);
		$row['IsDeleted'] = strtoupper($row['IsDeleted']);

		$cmd_product_insert->execute([
			':p0' => $row['ProductId'],
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
