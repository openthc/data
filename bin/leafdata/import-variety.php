#!/usr/bin/php
<?php
/**
 * Import Variety Data
 * File Size: 186774238 bytes, 925986 records
 * Time to Read File: 13 seconds
 * Time to Read File & Insert:
 */

require_once(__DIR__ . '/boot.php');

$f = $argv[1];
if (!is_file($f)) {
	echo "Create the source file at '$f'\n";
	exit(1);
}

$csv = new CSV_Reader($f);

$dbc = _dbc();

$fh = _fopen_bom($f);
$sep = _fpeek_sep($fh);
// echo "Sep: '$sep'\n";

$idx = 1;
while ($rec = fgetcsv($fh, 0, $sep)) {

	$idx++;
	// echo "\r$idx";

	$rec = array_combine($csv->key_list, $rec);

	if (empty($rec['global_id'])) {
		// _append_fail_log($idx, 'Missing Global ID', $rec);
		continue;
	}

	$rec['name'] = trim($rec['name']);
	if (empty($rec['name'])) {
		$rec['name'] = '-empty-field-on-import-';
	}

	unset($rec['external_id']);
	unset($rec['user_id']);

	$add = [
		'id' => $rec['global_id'],
		'license_id' => $rec['mme_id'],
		'created_at' => $rec['created_at'],
		'updated_at' => $rec['updated_at'],
		'updated_at' => $rec['deleted_at'],
		'name' => trim($rec['name']),
		'stat' => 200,
		'flag' => 0,
		'hash' => '-',
		'meta' => json_encode($rec)
	];
	if (empty($add['created_at'])) {
		unset($add['created_at']);
	}
	if (empty($add['updated_at'])) {
		unset($add['updated_at']);
	}
	if (empty($add['deleted_at'])) {
		unset($add['deleted_at']);
	}

	try {
		$dbc->insert('variety', $add);
	} catch (Exception $e) {
		echo "$idx : ";
		echo $e->getMessage();
		echo "\n";
	}

}

_show_progress($idx, $idx);
