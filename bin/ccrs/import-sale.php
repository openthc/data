<?php
/**
 * Imports Sale Header then Sale Detail data
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

$dbc_main = _dbc();
$cmd_main_b2b_insert = $dbc_main->prepare('INSERT INTO b2b_sale (id, id1, source_license_id, target_license_id, execute_at, stat, meta) VALUES (:id0, :id1, :sl, :tl, :ct, :s0, :m0) ON CONFLICT DO NOTHING');
$cmd_main_b2c_insert = $dbc_main->prepare('INSERT INTO b2c_sale (id, id1, license_id, created_at, stat, meta) VALUES (:id0, :id1, :l0, :ct, :s0, :m0) ON CONFLICT DO NOTHING');
$cmd_main_b2x_insert = $dbc_main->prepare('INSERT INTO b2x_sale (id, id1, type) VALUES (:id0, :id1, :t0) ON CONFLICT DO NOTHING');

// Import the Header Files
$sale_header_list = [];
$sale_header_list = glob('SaleHeader_*.tsv');
foreach ($sale_header_list as $sale_header_file) {

	echo "Import: {$sale_header_file}\n";

	$idx = 0;
	$max = 1000000;
	$_SERVER['REQUEST_TIME_FLOAT'] = microtime(true); // reset timer base for _show_progress()

	$csv = new \OpenTHC\Data\CSV_Reader($sale_header_file);
	$csv_head = $csv->getHeader();

	while ($row = $csv->fetch()) {

		$idx++;

		$row = _csv_row_map_check($csv_head, $row, $idx);
		if (empty($row)) {
			continue;
		}

		$cmd_main_b2x_insert->execute([
			':id0' => $row['SaleHeaderId'],
			':id1' => $row['ExternalIdentifier'],
			':t0' => $row['SaleType'],
		]);

		try {
			switch ($row['SaleType']) {
				case 'RecreationalRetail':  // B2C Table
				case 'RecreationalMedical': // B2C Table
					$rec = [
						':id0' => $row['SaleHeaderId'],
						':id1' => $row['ExternalIdentifier'],
						':l0' => $row['LicenseeId'],
						':ct' => $row['SaleDate'],
						':s0' => ($row['IsDeleted'] == 'TRUE' ? 410 : 200),
						':m0' => json_encode($row)
					];
					$cmd_main_b2c_insert->execute($rec);
					break;
				case 'Wholesale': // B2B Table
					if (empty($row['SoldToLicenseeId'])) {
						// throw new \Exception('WholeSale Missing Target');
						echo "ROW:$idx; Sale:{$row['SaleHeaderId']}; WholeSale Missing Target;\n";
						var_dump($row);
						continue 2;
					}
					$rec = [
						':id0' => $row['SaleHeaderId'],
						':id1' => $row['ExternalIdentifier'],
						':sl' => $row['LicenseeId'],
						':tl' => $row['SoldToLicenseeId'],
						':ct' => $row['SaleDate'],
						':s0' => ($row['IsDeleted'] == 'TRUE' ? 410 : 200),
						':m0' => json_encode($row)
					];
					$cmd_main_b2b_insert->execute($rec);
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
VALUES (:pk, :si0, :b2b, :lot, :s0, :uc, :uc, :up, :m0)
ON CONFLICT DO NOTHING
SQL;
$cmd_main_b2b_item_insert = $dbc_main->prepare($sql);

$sql = <<<SQL
INSERT INTO b2c_sale_item (id, b2c_sale_id, lot_id, stat, unit_count, unit_price, meta)
VALUES (:pk, :si0, :b2c, :lot, :s0, :uc, :up, :m0)
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
		$chk = $dbc_main->fetchRow('SELECT * FROM b2x_sale WHERE id = :s0', [
			':s0' => $row['SaleHeaderId']
		]);

		switch ($chk['type']) {
			case 'RecreationalRetail':  // B2C Table
			case 'RecreationalMedical': // B2C Table
				$cmd_main_b2c_item_insert->execute([
					':pk' => $row['SaleDetailId'],
					':b2c' => $row['SaleHeaderId'],
					':lot' => $row['InventoryId'],
					':uc' => $row['Quantity'],
					':up' => $row['UnitPrice'],
					':s0' => ($row['IsDeleted'] == 'TRUE' ? 410 : 200),
					':m0' => json_encode($row),
				]);
				break;
			case 'Wholesale':
				$cmd_main_b2b_item_insert->execute([
					':pk' => $row['SaleDetailId'],
					':b2b' => $row['SaleHeaderId'],
					':lot' => $row['InventoryId'],
					':uc' => $row['Quantity'],
					':up' => $row['UnitPrice'],
					':s0' => ($row['IsDeleted'] == 'TRUE' ? 410 : 200),
					':m0' => json_encode($row),
				]);
				break;
			default:
				echo "ROW: $idx; Sale:{$row['SaleHeaderId']}; Item:{$row['SaleDetailId']}\n";
				var_dump($row);
				// var_dump($chk);
				// throw new \Exception('Invalid Type');
		}

		_show_progress($idx, $max);

	}

	echo "rm $sale_detail_file\n";

}
