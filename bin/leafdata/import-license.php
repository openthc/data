#!/usr/bin/php
<?php
/**
 *
 */

require_once(__DIR__ . '/boot.php');

$f = $argv[1];
if (!is_file($f)) {
	echo "Create the source file at '$f'\n";
	exit(1);
}

$csv = new CSV_Reader($f);

$dbc = _dbc();

$select_license = $dbc->prepare('SELECT id, name FROM license WHERE id = ?');
//$select_license->execute($arg);
//$select_license = SQL::prepare('SELECT id, name FROM license WHERE id = ?');

$idx = 1;
while ($rec = $csv->fetch()) {

	$idx++;

//	if ($map_c != count($rec)) {
//		_append_fail_log($idx, 'Field Count', $rec);
//		continue;
//	}
//
//	$rec = array_combine($map, $rec);
	$rec = array_combine($csv->key_list, $rec);

	unset($rec['exernal_id']);

	$rec['name'] = trim($rec['name']);
	$rec['address1'] = trim($rec['address1']);
	$rec['address2'] = trim($rec['address2']);
	$rec['certificate_number'] = trim($rec['certificate_number']);

	// $chk = $select_license
	try {
		$dbc->insert('license', array(
			'id' => $rec['global_id'],
			'type' => substr($rec['code'], 0, 1),
			'code' => trim($rec['code']),
			'name' => trim($rec['name'])
		));
	} catch (Exception $e) {
		_append_fail_log($idx, $e->getMessage(), $rec);
	}

}

_show_progress($idx, $idx);
