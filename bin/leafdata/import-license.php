#!/usr/bin/php
<?php
/**
 *
 */

require_once(__DIR__ . '/boot.php');

$dbc = _dbc();

$select_license = $dbc->prepare('SELECT id, name FROM license WHERE id = ?');
//$select_license->execute($arg);
//$select_license = SQL::prepare('SELECT id, name FROM license WHERE id = ?');

$f = sprintf('%s/source-data/license.tsv', APP_ROOT);
if (!is_file($f)) {
	echo "Create the source file at '$f'\n";
	exit(1);
}

$fh = _fopen_bom($f);
$sep = _fpeek_sep($fh);

$map = fgetcsv($fh, 0, $sep);
$map_c = count($map);

$idx = 1;
while ($rec = fgetcsv($fh, 0, $sep)) {

	$idx++;

	if ($map_c != count($rec)) {
		_append_fail_log($idx, 'Field Count', $rec);
		continue;
	}

	$rec = array_combine($map, $rec);

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

echo "\n";
echo "Import: $idx Records\n";
echo "\n";
