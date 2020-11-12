#!/usr/bin/php
<?php
/**
 * Import Lot Data
 **
 ** File Size: 10031913776 bytes
 ** Records: 17156647 Records
 * Took 268m1.050s to IMport 17156594 records
 *
 ** Import 2019.243 : 21877568 rows in 288m24.485s
 */

require_once(__DIR__ . '/boot.php');

$f = $argv[1];
if (!is_file($f)) {
	echo "Create the source file at '$f'\n";
	exit(1);
}

$csv = new CSV_Reader($f);
// $est = $csv->rowEstimate();
// With my samle I counted 32503064 when sampling 100 rows.
// And 28297439 when sampling 1000 rows
// wc -l counts 27726031

$idx = 1;
$off = 0;
$max = 27726031;

// Seek to Work
while ($idx < $off) {
	$idx++;
	$rec = $csv->fetch();
}

// Connect DB
$dbc = _dbc();
$pdo = $dbc->_pdo;
$sql = <<<SQL
INSERT INTO lot (id, license_id, product_id, variety_id, qty, created_at, meta)
VALUES (:id, :license_id, :product_id, :variety_id, :qty, :created_at, :meta)
SQL;
$dbc_insert = $pdo->prepare($sql);

// Read the Data
$idx = 1;
while ($rec = $csv->fetch()) {

	$idx++;
/*
Array
(
    [0] => global_id
    [1] => created_at
    [2] => updated_at
    [3] => mme_id
    [4] => user_id
    [5] => external_id
    [6] => area_id
    [7] => batch_id
    [8] => lab_result_id
    [9] => lab_retest_id
    [10] => is_initial_inventory
    [11] => inventory_created_at
    [12] => inventory_packaged_at
    [13] => created_by_mme_id
    [14] => qty
    [15] => uom
    [16] => strain_id
    [17] => inventory_type_id
    [18] => additives
    [19] => serving_num
    [20] => sent_for_testing
    [21] => deleted_at
    [22] => medically_compliant
    [23] => legacy_id
    [24] => lab_results_attested
    [25] => lab_results_date
    [26] => global_original_id
)
*/

	if ($csv->key_size != count($rec)) {
		_append_fail_log($idx, 'Field Count Issue', $rec);
		continue;
	}

	$rec = array_combine($csv->key_list, $rec);

	if (empty($rec['global_id'])) {
		_append_fail_log($idx, 'Missing Global ID', $rec);
		continue;
	}

	$rec = de_fuck_date_format($rec);

	// Clean and Drop Empty Fields
	foreach ($csv->key_list as $k) {
		$rec[$k] = trim($rec[$k]);
		if (empty($rec[$k])) {
			unset($rec[$k]);
		}
	}

	// Make sure we have the product
	// $chk = $dbc->fetchOne('SELECT id FROM product WHERE id = ?', [ $rec['inventory_type_id'] ]);
	// if (empty($chk)) {
	// 	continue;
	// }

	try {
		$dbc_insert->execute([
			':id' => $rec['global_id'],
			':license_id' => $rec['mme_id'],
			':product_id' => $rec['inventory_type_id'],
			':variety_id' => $rec['strain_id'],
			':qty' => $rec['qty'],
			':created_at' => $rec['created_at'],
			':meta' => json_encode($rec),
		]);
	} catch (Exception $e) {
		_append_fail_log($idx, $e->getMessage(), $rec);
	}

	// Linkage
	// @todo Move to a review script
	// if (!empty($rec['lab_result_id'])) {
	// 	try {
	// 		$dbc->query('INSERT INTO lab_result_lot (lab_result_id, lot_id, type) VALUES (:lr0, :il1, :t0)', array(
	// 			':lr0' => $rec['lab_result_id'],
	// 			':il1' => $rec['global_id'],
	// 			':t0' => 'Lot Linkage',
	// 		));
	// 	} catch (Exception $e) {
	// 		_append_fail_log($idx, $e->getMessage(), $rec);
	// 	}
	// }

	_show_progress($idx, $max);

}

_show_progress($idx, $idx);
