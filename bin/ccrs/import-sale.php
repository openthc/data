<?php
/**
 * Imports Sale Header then Sale Detail data
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

$dbc_main = _dbc();
$cmd_main_b2b_insert = $dbc_main->prepare('INSERT INTO b2b_sale (id, source_license_id, target_license_id, execute_at, stat, meta) VALUES (:pk, :sl, :tl, :ct, :s0, :m0) ON CONFLICT DO NOTHING');
$cmd_main_b2c_insert = $dbc_main->prepare('INSERT INTO b2c_sale (id, license_id, created_at, stat, meta) VALUES (:pk, :l0, :ct, :s0, :m0) ON CONFLICT DO NOTHING');


// Import the Header Files
$sale_header_list = glob('SaleHeader_*.tsv');
$dbc_b2temp = new \Edoceo\Radix\DB\SQL('sqlite:sale-header-cache.sqlite');
try {
	$dbc_b2temp->query('CREATE TABLE sale_header (id PRIMARY KEY, type TEXT)');
} catch (\Exception $e) {
	// Nothing
}
$dbc_b2temp_insert = $dbc_b2temp->prepare('INSERT INTO sale_header (id, type) VALUES (:i0, :t0) ON CONFLICT DO NOTHING');

foreach ($sale_header_list as $sale_header_file) {

	echo "Import: {$sale_header_file}\n";

	$idx = 0;
	$max = 1000000;
	$_SERVER['REQUEST_TIME_FLOAT'] = microtime(true); // reset timer base for _show_progress()

	$fh = _fopen_bom($sale_header_file, 'r');
	$csv_head = fgetcsv($fh, null, "\t");
	while ($row = fgetcsv($fh, null, "\t")) {

		$idx++;

		$row = array_combine($csv_head, $row);
		$row['IsDeleted'] = strtoupper($row['IsDeleted']);

		$dbc_b2temp_insert->execute([
			':i0' => $row['SaleHeaderId'],
			':t0' => $row['SaleType'],
		]);

		try {
			switch ($row['SaleType']) {
				case 'RecreationalRetail':  // B2C Table
				case 'RecreationalMedical': // B2C Table
					$ins = [
						':pk' => $row['SaleHeaderId'],
						':l0' => $row['LicenseeId'],
						':ct' => $row['SaleDate'],
						':s0' => ($row['IsDeleted'] == 'TRUE' ? 410 : 200),
						':m0' => json_encode($row)
					];
					$cmd_main_b2c_insert->execute($ins);
					break;
				case 'Wholesale': // B2B Table
					$cmd_main_b2b_insert->execute([
						':pk' => $row['SaleHeaderId'],
						':sl' => $row['LicenseeId'],
						':tl' => $row['SoldToLicenseeId'],
						':ct' => $row['SaleDate'],
						':s0' => ($row['IsDeleted'] == 'TRUE' ? 410 : 200),
						':m0' => json_encode($row)
					]);
					break;
				default:
					print_r($row);
					echo "\nNO TYPE\n";
					exit(1);
			}
		} catch (\Exception $e) {
			print_r($row);
			echo $e->getMessage();
			exit(1);
		}

		_show_progress($idx, $max);

	}

	echo "\nrm $sale_header_file\n";

}

/**
 * Sale Details
 */

$sql = <<<SQL
INSERT INTO b2b_sale_item (id, b2b_sale_id, lot_id_source, stat, unit_count_tx, unit_count_rx, unit_price, meta)
VALUES (:pk, :b2b, :lot, :s0, :uc, :uc, :up, :m0)
ON CONFLICT DO NOTHING
SQL;
$cmd_main_b2b_item_insert = $dbc_main->prepare($sql);

$sql = <<<SQL
INSERT INTO b2c_sale_item (id, b2c_sale_id, lot_id, stat, unit_count, unit_price, meta)
VALUES (:pk, :b2c, :lot, :s0, :uc, :up, :m0)
ON CONFLICT DO NOTHING
SQL;
$cmd_main_b2c_item_insert = $dbc_main->prepare($sql);


$sale_detail_list = glob('SalesDetail_*.tsv');
foreach ($sale_detail_list as $sale_detail_file) {

	echo "Import: {$sale_detail_file}\n";

	$idx = 0;
	$max = 1000000;
	$_SERVER['REQUEST_TIME_FLOAT'] = microtime(true); // reset timer base for _show_progress()

	$csv = new \OpenTHC\Data\CSV_Reader($sale_detail_file);
	$csv_head = $csv->getHeader();

	while ($row = $csv->fetch()) {

		$idx++;

		// Most of the time this fails it's because of an embedded TAB in the ExternalId
		$row = _csv_row_map_check($csv_head, $row, $idx);
		if (empty($row)) {
			continue;
		}

		// Get Sales Detail
		$chk = $dbc_b2temp->fetchRow('SELECT * FROM sale_header WHERE id = :s0', [
			':s0' => $row['SaleHeaderId']
		]);

		switch ($chk['type']) {
			case 'RecreationalRetail':  // B2C Table
			case 'RecreationalMedical': // B2C Table
				// $cmd_main_b2c_item_insert->execute([
				// 	':pk' => $row['SaleDetailId'],
				// 	':b2c' => $row['SaleHeaderId'],
				// 	':lot' => $row['InventoryId'],
				// 	':uc' => $row['Quantity'],
				// 	':up' => $row['UnitPrice'],
				// 	':s0' => ($row['IsDeleted'] == 'TRUE' ? 410 : 200),
				// 	':m0' =>json_encode($row),
				// ]);
				break;
			case 'Wholesale':
				$cmd_main_b2b_item_insert->execute([
					':pk' => $row['SaleDetailId'],
					':b2b' => $row['SaleHeaderId'],
					':lot' => $row['InventoryId'],
					':uc' => $row['Quantity'],
					':up' => $row['UnitPrice'],
					':s0' => ($row['IsDeleted'] == 'TRUE' ? 410 : 200),
					':m0' =>json_encode($row),
				]);
				break;
			default:
				echo "ROW: $idx\n";
				print_r($row);
				throw new \Exception('Invalid Type');
		}

		_show_progress($idx, $max);

	}

	echo "rm $sale_detail_file\n";

}
