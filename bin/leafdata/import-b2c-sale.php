#!/usr/bin/php
<?php
/**
 * Import B2C Sales to OpenTHC
 *
 * Import 2019.243: 127157262 records in 1208m45.305ss ~1753/s
 */

require_once(__DIR__ . '/boot.php');

$dbc = _dbc();

$source_file = sprintf('%s/source-data/b2c-sale.tsv', APP_ROOT);
if (!is_file($source_file)) {
	echo "Create the source file at '$source_file'\n";
	exit(1);
}

$fh = _fopen_bom($source_file);
$sep = _fpeek_sep($fh);

$key_list = fgetcsv($fh, 0, $sep);
$key_size = count($key_list);

$idx = 0;
// $max = 100000000;  // Sales_0
$max = 37861239; // Sales_1

$min_date = new DateTime('2018-06-01');

while ($rec = fgetcsv($fh, 0, $sep)) {

	$idx++;
	_show_progress($idx, $max);

	if ($key_size != count($rec)) {
		_append_fail_log($idx, 'Field Count', $rec);
		continue;
	}

	$flag = 0;
	$stat = 500;

	$rec = array_combine($key_list, $rec);

	if (empty($rec['global_id'])) {
		_append_fail_log($idx, 'Missing Global ID', $rec);
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
	$d0 = new DateTime($rec['created_at']);
	if ($d0 < $min_date) {
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
	foreach ($key_list as $x) {
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
		$dbc->insert('b2c_sale', [
			'id' => $rec['global_id'],
			'license_id' => $rec['mme_id'],
			'created_at' => $rec['created_at'],
			'updated_at' => $rec['updated_at'],
			'deleted_at' => $rec['deleted_at'],
			'stat' => $stat,
			'flag' => $flag,
			'hash' => '-',
			'full_price' => $rec[''],
			// 'meta' => json_encode($rec)
		]);
	} catch (Exception $e) {
		_append_fail_log($idx, $e->getMessage(), $rec);
	}

}

echo "done\n";
_show_progress($idx, $max);
echo "done done\n";
