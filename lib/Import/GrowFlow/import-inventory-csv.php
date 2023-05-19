<?php
/**
 * Import Inventory Data from CSV
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

// syslog(LOG_DEBUG, sprintf('Import Started: %s %s', _date('%Y-%m-%d %H:%M:%S'), implode(' ', $argv)));

$csv = new \OpenTHC\Data\CSV_Reader($this->_source_file);
$csv_head = $csv->getHeader();
// var_dump($csv_head);
$csv_head_have = implode(',', $csv_head);
$csv_head_want = '#,STRAIN,ID,EXT. ID,BATCH ID,TYPE,WEIGHT,ROOM,BIRTHDATE';
if ($csv_head_have != $csv_head_want) {
	throw new \Exception('Invalid CSV Format [IGI-016]');
}

// Spin
while ($src= $csv->fetch('array')) {

	$out = [];
	$out['id'] = $src['ID'];
	$out['created_at'] = $src['BIRTHDATE'];
	$out['name'] = $src['STRAIN'];
	$out['qty'] = $src['WEIGHT'];
	$out['section'] = [
		'id' => '',
		'name' => $src['ROOM'],
	];
	$out['variety'] = [
		'id' => '',
		'name' => $src['STRAIN'],
	];
	$out['product'] = [
		'id' => '',
		'type' => $src['TYPE'],
	];

	$obj_hash = \OpenTHC\CRE\Base::objHash($out);
	$obj_data = json_encode($out, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	$obj_file = sprintf('%s/output-data/inventory-%s.json', APP_ROOT, $obj_hash);
	file_put_contents($obj_file, $obj_data);

	// foreach ($import_list as $inventory) {
	// 	$Lot = Inventory::findByGUID($inventory['ID']);

	// 	if (empty($Lot['id'])) {
	// 		$S = Section::findByName($inventory['Room']);
	// 		$V = Variety::findByName($inventory['Strain']);
	// 		$P = Product::findByName($inventory['Product'], $License['id']);
	// 		$Lot = new Inventory([
	// 			'stat' => Inventory::STAT_LIVE,
	// 			'license_id' => $License['id'],
	// 			'section_id' => $S['id'],
	// 			'variety_id' => $V['id'],
	// 			'product_id' => $P['id'],
	// 			'qty' => $inventory['Available'],
	// 			'qty_initial' => $inventory['Available'],
	// 			'guid' => $inventory['ID'],
	// 		]);
	// 		$Lot->save('Lot/Create by Import');
	// 	}
	// 	$lot_list[ $Lot['id'] ] = $Lot->toArray();
	// }

}
