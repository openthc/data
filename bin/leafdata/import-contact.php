#!/usr/bin/php
<?php
/**
 * Import Contacts
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
while ($rec = $csv->fetch()) {

	$idx++;
	$rec = array_combine($csv->key_list, $rec);

	try {
		$dbc->insert('contact', array(
			'id' => $rec['global_id'],
			'name' => trim($rec['first_name'] . ' ' . $rec['last_name']),
			'email' => strtolower(trim($rec['email']))
		));
	} catch (Exception $e) {
		_append_fail_log($idx, $e->getMessage(), $rec);
	}

}

exit(0);

//$f1 = $argv[2]; // sprintf('%s/source-data/contact-license.tsv', APP_ROOT);
//if (!is_file($f1)) {
//	echo "Create the source file at '$f1'\n";
//	exit(1);
//}


// License to Contact Linkage
$fh = _fopen_bom($f1);
$sep = _fpeek_sep($fh);

$map = fgetcsv($fh, 0, $sep);
$map_c = count($map);

$idx = 1;
while ($rec = fgetcsv($fh, 0, $sep)) {

	$rec = array_combine($map, $rec);

	try {
		$dbc->query('INSERT INTO license_contact (license_id, contact_id) VALUES (:l0, :c0)', array(
			':l0' => $rec['mme_id'],
			':c0' => $rec['user_id'],
		));
	} catch (Exception $e) {
		_append_fail_log($idx, $e->getMessage(), $rec);
	}

}
