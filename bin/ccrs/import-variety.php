<?php
/**
 * Imports Variety from CCRS
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

$dbc_main = _dbc();
$sql = <<<SQL
INSERT INTO variety (id, license_id, name, type, stat, meta)
VALUES (:p0, '018NY6XC00L1CENSE000000000', :n0, :t0, :s0, :m0)
ON CONFLICT DO NOTHING
SQL;
$cmd_variety_insert = $dbc_main->prepare($sql);


// Import the Header Files
$source_file_list = glob('Strains_*.tsv');
foreach ($source_file_list as $source_file) {

	echo "Import: {$source_file}\n";

	$csv = new \OpenTHC\Data\CSV_Reader($source_file);
	$csv_head = fgetcsv($fh, null, "\t");

	$idx = 1;
	$max = 1000000;
	$_SERVER['REQUEST_TIME_FLOAT'] = microtime(true); // reset timer base for _show_progress()

	while ($row = $csv->fetch()) {

		$idx++;

		$row = _csv_row_map_check($csv_head, $row, $idx);
		if (empty($row)) {
			continue;
		}

		$cmd_variety_insert->execute([
			':p0' => $row['StrainId'],
			':n0' => $row['Name'],
			':t0' => $row['StrainType'],
			':s0' => ($row['ISDELETED'] == 'TRUE' ? 410 : 200),
			':m0' => json_encode($row)
		]);

		_show_progress($idx, $max);

	}

}
