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

	$idx++;

	if ($csv->key_size != count($rec)) {
		_append_fail_log($idx, 'Field Count', $rec);
		continue;
	}

	$rec = array_combine($csv->key_list, $rec);
	unset($rec['user_id']);
	unset($rec['batch_id']);
	unset($rec['external_id']);
	unset($rec['use_by_date']);

	foreach ($csv->key_list as $x) {
		if (empty($rec[$x])) {
			unset($rec[$x]);
		}
	}

	if (empty($rec['global_id'])) {
		_append_fail_log($idx, 'Missing Global ID', $rec);
		continue;
	}

	$d0 = new DateTime($rec['created_at']);
	if ($d0 < $min_date) {
		continue;
	}
	$y0 = intval($d0->format('Y'));


	$rec['unit_price'] = floatval($rec['unit_price']);
	$rec['full_price'] = floatval($rec['full_price']);

	// Price Fixer
	if (!empty($rec['qty'])) {
		if (empty($rec['unit_price']) && !empty($rec['full_price'])) {
			$rec['unit_price'] = $rec['full_price'] / $rec['qty'];
		}
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
		switch ($y0) {
			case 2018:
				$dbc->insert('b2c_sale_item_2018', $add);
			break;
			case 2019:
				$dbc->insert('b2c_sale_item_2019', $add);
			break;
			case 2020:
				$dbc->insert('b2c_sale_item', $add);
			break;
			default:
				die("\n$idx Has Bad Year\n");
		}
	} catch (Exception $e) {
		_append_fail_log($idx, $e->getMessage(), $rec);
	}

	_show_progress($idx, $max);

}

_show_progress($idx, $idx);
