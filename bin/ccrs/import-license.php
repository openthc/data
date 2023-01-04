<?php
/**
 * Imports Product Data from CCRS
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

$dbc_main = _dbc();
$sql = <<<SQL
INSERT INTO license (id, code, name, meta)
VALUES (:i0, :c0, :n0, :m0)
ON CONFLICT (id)
DO UPDATE SET code = :c0, name = :n0, meta = :m0
SQL;
$cmd_license_insert = $dbc_main->prepare($sql);

// Import the Header Files
$source_file_list = glob('Licensee_*.tsv');
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

		$cmd_license_insert->execute([
			':i0' => $row['LicenseeId'],
			':c0' => $row['LicenseNumber'],
			':n0' => $row['Name'],
			':m0' => json_encode($row)
		]);

		_show_progress($idx, $max);

	}

}
