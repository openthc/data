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

$t0 = microtime(true);

$dbc = _dbc();

$source_file = sprintf('%s/source-data/b2b-sale.tsv', APP_ROOT);
if (!is_file($source_file)) {
	echo "Create the source file at '$source_file'\n";
	exit(1);
}

$idx = 1;
$max = 924580;


$fh = _fopen_bom($source_file);
$sep = _fpeek_sep($fh);

// Header Row
$key_list = fgetcsv($fh, 0, $sep);

while ($rec = fgetcsv($fh, 0, $sep)) {

	$idx++;

	$rec = array_combine($key_list, $rec);

	unset($rec['user_id']);
	unset($rec['from_user_id']);
	unset($rec['to_user_id']);
	unset($rec['hold_ends_at']);
	unset($rec['hold_starts_at']);

	$rec = de_fuck_date_format($rec);

	$date = $rec['transferred_at'];
	if (empty($date)) {
		$date = $rec['updated_at'];
	}
	if (empty($date)) {
		$date = $rec['created_at'];
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
		_append_fail_log(sprintf('%d@%d', $idx, ftell($fh)), $e->getMessage(), $rec);
		die("\nODD VOID \n");
	}

	try {
		$add = array(
			'id' => $rec['global_id'],
			'license_id_origin' => $rec['from_mme_id'],
			'license_id_target' => $rec['to_mme_id'],
			'execute_at' => $date,
			'stat' => $stat,
			'meta' => json_encode($rec),
		);
		$dbc->insert('b2b_sale', $add);
	} catch (Exception $e) {
		_append_fail_log(sprintf('%d@%d', $idx, ftell($fh)), $e->getMessage(), $rec);
	}

	_show_progress($idx, $max);

}

_show_progress($max, $max);
