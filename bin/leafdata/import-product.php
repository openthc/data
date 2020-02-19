#!/usr/bin/php
<?php
/**
 * Import Product
 * 2019-14 Import - V-CPU: 4 Core, 2GHz; 8G RAM
 ** File Size: 5,643,261,018 bytes, 19,089,982 records

 * 2019.241 Import - V-CPU: 4 Core, 2GHz; 8G RAM
 ** File Size: 5,879,862,984 bytes
 ** Time to Read File & Insert: 19954511 in 210m21.184s (~1581/rps)

 */

require_once(__DIR__ . '/boot.php');

$t0 = microtime(true);

$dbc = _dbc();

$source_file = sprintf('%s/source-data/product.tsv', APP_ROOT);
if (!is_file($source_file)) {
	echo "Create the source file at '$source_file'\n";
	exit(1);
}

$fh = _fopen_bom($source_file);
$sep = _fpeek_sep($fh);

// Header Row
$map = fgetcsv($fh, 0, $sep);

$idx = 1;
$max = 21976516; // Get from `wc -l`
$max = 22126659;

while ($rec = fgetcsv($fh, 0, $sep)) {

	$idx++;

	$rec = array_combine($map, $rec);

	// Skip These?
	if ('waste' == $rec['intermediate_type']) {
		continue;
	}

	if (empty($rec['global_id'])) {
		echo sprintf("%d: %s; %s\n", $idx, 'Missing Global ID', json_encode($rec));
		continue;
	}
	if ('waste' == $rec['intermediate_type']) {
		continue;
	}

	$rec = de_fuck_date_format($rec);
	$rec['name'] = trim($rec['name']);

	// Record: 1225527
	// Record: 1248289
	// Record: 1283084
	$rec['name'] = str_replace('\\t', null, $rec['name']); // Replace literal \\t ?

	try {
		$add = array(
			'id' => $rec['global_id'],
			'license_id' => $rec['mme_id'],
			'product_type' => $rec['intermediate_type'],
			'package_type' => $rec['uom'],
			'package_size' => $size,
			'package_unit' => $unit,
			'name' => trim($rec['name']),
		);
		$dbc->insert('product', $add);
	} catch (Exception $e) {
		_append_fail_log($idx, $e->getMessage(), $rec);
	}

	_show_progress($idx, $max);

}

_show_progress($max, $max);

/*
  count  |          product_type
---------+--------------------------------
 4011578 | concentrate_for_inhalation
   21267 | co2_concentrate
   40449 | packaged_marijuana_mix
  112953 | ethanol_concentrate
  169182 | topical
    3820 | plant_tissue
    3470 | transdermal_patches
    9152 | tinctures
   28194 | seeds
 2636147 | mature_plant
 1902735 | flower_lots
   22070 | non-solvent_based_concentrate
    9536 | daily_plant_waste
  519339 | sample_jar
    8773 | non_mandatory_plant_sample
  525437 | other_material_lots
    5888 |
   49125 | marijuana_mix
  218651 | flower
   58733 | other_material
   68106 | hydrocarbon_concentrate
    2820 | infused_cooking_medium
   18434 | plant
    1352 | suppository
   19281 | food_grade_solvent_concentrate
   22228 | clone
 1135905 | infused_mix
  248434 | liquid_edible
   94193 | capsules
     190 | seed
 1139958 | solid_edible
  176289 | clones
 7907520 | usable_marijuana
*/
