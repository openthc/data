#!/usr/bin/php
<?php
/**
 * Import B2B Sale Items
 */

require_once(__DIR__ . '/boot.php');

$dbc = _dbc();

$source_file = sprintf('%s/source-data/b2b-sale-item.tsv', APP_ROOT);
if (!is_file($source_file)) {
	echo "Create the source file at '$source_file'\n";
	exit(1);
}

$fh = _fopen_bom($source_file);
$sep = _fpeek_sep($fh);

// Header Row
$key_list = fgetcsv($fh, 0, $sep);

$idx = 0; // First Data row maps to '1'
$max = 13832109;

while ($rec = fgetcsv($fh, 0, $sep)) {

	$idx++;

	$rec = array_combine($key_list, $rec);

	if (empty($rec['global_id'])) {
		echo sprintf("%d: %s; %s\n", $idx, 'Missing Global ID', json_encode($rec));
		continue;
	}

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
		_append_fail_log(sprintf('%d@%d', $idx, ftell($fh)), 'is_sample/sample_type/product_sample_type', $rec);
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
		_append_fail_log(sprintf('%d@%d', $idx, ftell($fh)), 'Invalid is_for_extraction', $rec);
		die("BAD is_for_extraction = $x\n");
	}
	//sample_type
	//product_sample_type
	$stat = implode('-', $stat);

	$add = array(
		'id' => $rec['global_id'],
		'transfer_id' => $rec['inventory_transfer_id'],
		'lot_id_origin' => $rec['inventory_id'],
		'lot_id_target' => $rec['received_inventory_id'],
		'qom_tx' => floatval($rec['qty']),
		'qom_rx' => floatval($rec['received_qty']),
		'uom' => $rec['uom'],
		'stat' => $stat,
		'full_price' => floatval($rec['price']),
		// 'meta' => json_encode($rec),
	);

	try {
		$dbc->insert('b2b_sale_item', $add);
	}  catch (Exception $e) {
		_append_fail_log(sprintf('%d@%d', $idx, ftell($fh)), $e->getMessage(), $rec);
	}

	_show_progress($idx, $max);

}

_show_progress($max, $max);

