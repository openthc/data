#!/usr/bin/php
<?php
/**
 * Import B2C Sales to OpenTHC
 *
 * Import 2019.243: 127157262 records in 1208m45.305ss ~1753/s
 */

require_once(__DIR__ . '/boot.php');

$f = $argv[1];
if (!is_file($f)) {
	echo "Create the source file at '$f'\n";
	exit(1);
}

$csv = new CSV_Reader($f);

$idx = 1;
$max = _find_max($f, $csv);


// Connect DB
$dbc = _dbc();
$sql = <<<SQL
INSERT INTO b2c_sale (id, license_id, created_at, updated_at, deleted_at, stat, flag, hash, full_price)
VALUES (:id, :license_id, :created_at, :updated_at, :deleted_at, :stat, :flag, :hash, :full_price)
SQL;
$dbc_insert = $dbc->prepare($sql);

while ($rec = $csv->fetch()) {

	$idx++;
	_show_progress($idx, $max);

	if ($csv->key_size != count($rec)) {
		_append_fail_log($idx, 'Field Count', $rec);
		continue;
	}

	$flag = 0;
	$stat = 500;

	$rec = array_combine($csv->key_list, $rec);

	if (empty($rec['global_id'])) {
		// _append_fail_log($idx, 'Missing Global ID', $rec);
		continue;
	}

	// Skip Bogus Prices?
	// $rec['price_total'] = floatval($rec['price_total']);
	// if ($rec['price_total'] <= 0) {
	// 	continue;
	// }

	// Cleanup Dates
	$rec = de_fuck_date_format($rec);

	if (!empty($rec['deleted_at'])) {
		$flag = $flag | 0x08000000;
		continue;
	}

	// Handle These?
	switch ($rec['type']) {
	case 'retail_recreational':
		// OK
		$flag = $flag | 0x0001;
		break;
	case 'retail_medical':
		$flag = $flag | 0x0002;
		break;
	case 'wholesale':
		// $flag = $flag | 0x0004;
		// Ignore
		continue 2;
	case '':
		_append_fail_log($idx, 'Empty Type', $rec);
		continue 2;
	default:
		die("type = '{$rec['type']}'\n");
	}

	switch ($rec['status']) {
	case 'return':
		$stat = 307;
		break;
	case 'sale':
		$stat = 200;
		break;
	case 'void': // First Seen in  WAR414871.SA1HHUTZ; Created: 2018-11-07 19:06:47
		$flag = $flag | 0x01000000;
		$stat = 410;
		continue 2;
		break;
	default:
		_append_fail_log($idx, 'Unknown Status', $rec);
		continue 2;
	}

	// Strip Noise
	foreach ($csv->key_list as $x) {
		if (empty($rec[$x])) {
			unset($rec[$x]);
		}
	}
	unset($rec['area_id']);
	unset($rec['user_id']);
	unset($rec['external_id']);
	unset($rec['sold_by_user_id']);

	// INSERT
	try {
		$dbc_insert->execute([
			':id' => $rec['global_id'],
			':license_id' => $rec['mme_id'],
			':created_at' => $rec['created_at'],
			':updated_at' => $rec['updated_at'],
			':deleted_at' => $rec['deleted_at'],
			':stat' => $stat,
			':flag' => $flag,
			':hash' => '-',
			':full_price' => 0,
		]);
	} catch (Exception $e) {
		_append_fail_log($idx, $e->getMessage(), $rec);
	}

}

_show_progress($idx, $idx);
