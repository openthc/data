#!/usr/bin/php
<?php
/**
 * Import B2B Sale Items
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

require_once(__DIR__ . '/boot.php');

$dbc = _dbc();

$f = $argv[1];
if (!is_file($f)) {
	echo "Create the source file at '$f'\n";
	exit(1);
}

$stat_ignore = 0;
$stat_insert = 0;

$csv = new CSV_Reader($f);

$idx = 1; // First Data row maps to '1'
$max = _find_max($f, $csv);

while ($rec = $csv->fetch()) {

	$idx++;
	$rec = array_combine($csv->key_list, $rec);

	if (empty($rec['global_id'])) {
		_append_fail_log($idx, 'Missing Global ID', $rec);
		continue;
	}

	$rec['qty'] = floatval($rec['qty']);
	$rec['received_qty'] = floatval($rec['received_qty']);
	$rec['price'] = floatval($rec['price']);

	$stat = array();

	$x = trim(sprintf('%s/%s/%s', $rec['is_sample'], $rec['sample_type'], $rec['product_sample_type'])); // $rec['retest']; ?
	switch ($x) {
	case 'False//':
		// Ignore
		break;
	case 'True//':
		$stat[] = 'Sample/!!';
		break;
	case 'True/lab_sample/':
		$stat[] = 'Sample/Lab';
		break;
	case 'True/non_mandatory_sample/':
		$stat[] = 'Sample/Opt';
		break;
	case 'True/non_mandatory_sample/budtender_sample':
		$stat[] = 'Sample/Opt!Budtender'; // Shouldn't Exist
		break;
	case 'True/non_mandatory_sample/vendor_sample':
		$stat[] = 'Sample/Opt!Client'; // Shouldn't Exist
		break;
	case 'True/product_sample/':
		$stat[] = 'Sample/?';
		break;
	case 'True/product_sample/vendor_sample':
		$stat[] = 'Sample/Client';
		break;
	case 'True/product_sample/budtender_sample':
		$stat[] = 'Sample/Budtender';
		break;
	default:
		_append_fail_log($idx, 'is_sample/sample_type/product_sample_type', $rec);
		die("BAD is_sample/sample_type/product_sample_type = $x\n");
	}

	$x = $rec['is_for_extraction'];
	switch ($x) {
	case '':
	case 'False':
		// Ignore
		break;
	case 'True':
		$stat[] = 'For Extraction';
		break;
	default:
		_append_fail_log($idx, 'Invalid is_for_extraction', $rec);
		die("BAD is_for_extraction = $x\n");
	}
	//sample_type
	//product_sample_type
	$stat = implode('-', $stat);

	$qty = $rec['received_qty'];
	if (empty($qty)) {
		$qty = $rec['qty'];
	}

	$add = array(
		'id' => $rec['global_id'],
		'b2b_sale_id' => $rec['inventory_transfer_id'],
		'lot_id_source' => $rec['inventory_id'],
		'lot_id_target' => $rec['received_inventory_id'],
		'unit_count_tx' => $rec['qty'],
		'unit_count_rx' => $rec['received_qty'],
		'uom' => $rec['uom'],
		'stat' => $stat,
		'full_price' => $rec['price'],
		'unit_price' => ($qty ? ($rec['price'] / $qty) : 0)
		// 'meta' => json_encode($rec),
	);

	try {
		$dbc->insert('b2b_sale_item', $add);
		$stat_insert++;
	} catch (Exception $e) {
		$stat_ignore++;
		_append_fail_log($idx, $e->getMessage(), $rec);
	}

	_show_progress($idx, $max);

}

_show_progress($idx, $idx);

echo "INSERT: $stat_insert\n";
echo "IGNORE: $stat_ignore\n";
