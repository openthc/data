<?php
/**
 * Import Crop Data from CSV
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

// syslog(LOG_DEBUG, sprintf('Import Started: %s %s', _date('%Y-%m-%d %H:%M:%S'), implode(' ', $argv)));

$csv = new \OpenTHC\Data\CSV_Reader($this->_source_file);
$csv_head = $csv->getHeader();

$csv_type = 0;
$csv_head_have = implode(',', $csv_head);

// Don't branch to much on these (yet), just using ?: below
$csv_head_want1 = 'STRAIN,EXT ID#,ID#,MOTHER PLANT,STATUS,ROOM,TABLE,DAYS IN ROOM,WET WEIGHT,DRY WEIGHT,AGE,HARVEST,BIRTH DATE,LAST MODIFIED';
$csv_head_want2 = '#,STRAIN,ID,EXT. ID,BATCH ID,AGE (DAYS),ROOM,PARENT INV. ID#,TABLE,STAGE,BIRTHDATE';
if ($csv_head_have == $csv_head_want1) {
	$csv_type = 1;
} elseif ($csv_head_have == $csv_head_want2) {
	$csv_type = 2;
} else {
	throw new \Exception('Unexpected CSV Type');
}

// Spin
while ($src= $csv->fetch('array')) {

	$out = [];
	$out['id'] = $src['ID'] ?: $src['ID#'];
	$out['created_at'] = $src['BIRTHDATE'] ?: $src['BIRTH DATE'];
	$out['updated_at'] = $src['LAST MODIFIED'];
	$out['name'] = $src['STRAIN'];
	$out['raw_weight'] = $src['WET WEIGHT'];
	$out['net_weight'] = $src['DRY WEIGHT'];
	$out['phase'] = $src['STAGE'] ?: $src['STATUS'];
	$out['section'] = [
		'id' => '',
		'name' => $src['ROOM'],
	];
	$out['variety'] = [
		'id' => '',
		'name' => $src['STRAIN'],
	];

	$obj_hash = \OpenTHC\CRE\Base::objHash($out);
	$obj_data = json_encode($out, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	$obj_file = sprintf('%s/output-data/crop-%s.json', APP_ROOT, $obj_hash);
	file_put_contents($obj_file, $obj_data);

}
