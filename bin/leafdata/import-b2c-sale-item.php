#!/usr/bin/php
<?php
/**
 * Import B2C Sale Items
 * 11276127
 *
 * Convert icon
 * Import 2019.244-0
 * * time unzip Sale_Item_0.zip - 6m12.613s
 * * time iconv -f UTF-16LE -t UTF-8 SaleItems_0.csv -o SaleItems_0.tsv - 12m26.127s
 * * time wc -l SaleItems_0.tsv  - 5m26.123s
 *
 * Import 2019.244-1 34215187 records in 295m57.322s ~1926/s
 * Import 2019.244-0-a 51400000 records in ??? ~1985/s
 * Import 2019.244-0-b 90000000-51400000 records in 604m24.470s
 */

require_once(__DIR__ . '/boot.php');

$dbc = _dbc();

$source_file = sprintf('%s/source-data/b2c-sale-item.tsv', APP_ROOT);
if (!is_file($source_file)) {
	echo "Create the source file at '$source_file'\n";
	exit(1);
}

$fh = _fopen_bom($source_file);
$sep = _fpeek_sep($fh);

$key_list = fgetcsv($fh, 0, $sep);
$key_size = count($key_list);

$idx = 1;
$max = 90000001; // from wc -l
$max = 47918396;
while ($rec = fgetcsv($fh, 0, $sep)) {

	$idx++;

	if ($key_size != count($rec)) {
		_append_fail_log($idx, 'Field Count', $rec);
		continue;
	}

	$rec = array_combine($key_list, $rec);
	unset($rec['user_id']);
	unset($rec['batch_id']);
	unset($rec['external_id']);
	unset($rec['use_by_date']);

	foreach ($key_list as $x) {
		if (empty($rec[$x])) {
			unset($rec[$x]);
		}
	}

	// print_r($rec); exit;

	if (empty($rec['global_id'])) {
		_append_fail_log($idx, 'Missing Global ID', $rec);
		continue;
	}

	$add = [
		'id' => $rec['global_id'],
		'b2c_sale_id' => $rec['sale_id'],
		'lot_id' => $rec['inventory_id'],
		'qty' => floatval($rec['qty']),
		'unit_price' => floatval($rec['unit_price']),
		'created_at' => $rec['created_at'],
		'updated_at' => $rec['updated_at'],
		'hash' => '-',
		'meta' => json_encode($rec)
	];

	try {
		$dbc->insert('b2c_sale_item', $add);
	} catch (Exception $e) {
		_append_fail_log($idx, $e->getMessage(), $rec);
	}

	_show_progress($idx, $max);

}

echo "done\n";
echo _show_progress($max, $max);
echo "done done\n";

