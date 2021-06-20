#!/usr/bin/php
<?php
/**
 * Import B2B
 * 2019-08 Import
 ** File Size: 266818836 bytes, 739827 records
 ** Time to Read File: 26 seconds
 ** Time to Read File & Insert: 6m39.232s
 *
 ** Import 2019.242 - 780214 records, 8m37.471s
 ** 2019-10-26: 129939468 ~1166/s - 1856m34.619s
 */

require_once(__DIR__ . '/boot.php');

$dbc = _dbc();

$f = $argv[1];
if (!is_file($f)) {
	echo "Create the source file at '$f'\n";
	exit(1);
}

$csv = new CSV_Reader($f);

$idx = 1;
$max = _find_max($f, $csv);
$min_date = new DateTime(DATE_ALPHA);

while ($rec = $csv->fetch()) {

	// Clean
	$rec = array_combine($csv->key_list, $rec);

	// Some records are missing this critical field
	// if (empty($rec['to_mme_id'])) {
	// 	_append_fail_log()
	// }

	$rec = de_fuck_date_format($rec);

	unset($rec['user_id']);
	unset($rec['from_user_id']);
	unset($rec['to_user_id']);
	unset($rec['hold_ends_at']);
	unset($rec['hold_starts_at']);

	$date = $rec['transferred_at'];
	if (empty($date)) {
		$date = $rec['updated_at'];
	}
	if (empty($date)) {
		$date = $rec['created_at'];
	}

	// Skip Old
	$d0 = new DateTime($date);
	if ($d0 < $min_date) {
		continue;
	}

	$stat = $rec['status'];
	switch ($rec['void']) {
	case 'False':
		// Ignore
		break;
	case 'True':
		$stat = "VOID-$stat";
		if (!empty($rec['deleted_at'])) {
			$date = $rec['deleted_at'];
		}
		break;
	default:
		_append_fail_log($idx, $e->getMessage(), $rec);
		print($rec);
		die("\nODD VOID \n");
	}

	try {
		$add = array(
			'id' => $rec['global_id'],
			'license_id_source' => $rec['from_mme_id'],
			'license_id_target' => $rec['to_mme_id'],
			'created_at' => $rec['created_at'],
			'execute_at' => $date,
			'stat' => $stat,
			'meta' => json_encode($rec),
		);
		$dbc->insert('b2b_sale', $add);
	} catch (Exception $e) {
		_append_fail_log($idx, $e->getMessage(), $rec);
	}

	$idx++;
	_show_progress($idx, $max);

}

_show_progress($idx, $idx);
