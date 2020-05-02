#!/usr/bin/php
<?php
/**
 * Import Product
 */

require_once(__DIR__ . '/boot.php');

$f = $argv[1];
if (!is_file($f)) {
	echo "Create the source file at '$f'\n";
	exit(1);
}

$csv = new CSV_Reader($f);

$idx = 1;
$off = 500000;
$max = 24068254;

// Seek to Work
while ($idx < $off) {
	$idx++;
	$rec = $csv->fetch();
}

// Connect DB
$dbc = _dbc();
$pdo = $dbc->_pdo;
$sql = <<<SQL
INSERT INTO product (id, license_id, product_type, package_type, package_size, package_unit, name)
VALUES (:id, :license_id, :product_type, :package_type, :package_size, :package_unit, :name)
SQL;
$dbc_insert = $pdo->prepare($sql);

// Read the Data
$idx = 1;
while ($rec = $csv->fetch()) {

	$idx++;
	if ($idx < $off) {
		continue;
	}

	$rec = array_combine($csv->key_list, $rec);

	// Skip These?
	if (empty($rec['global_id'])) {
		echo sprintf("%d: %s; %s\n", $idx, 'Missing Global ID', json_encode($rec));
		continue;
	}

	if ('waste' == $rec['intermediate_type']) {
		continue;
	}

	$rec = de_fuck_date_format($rec);
	$rec['name'] = trim($rec['name']);
	$rec['name'] = stripslashes($rec['name']);
	$rec['name'] = str_replace('\\t', null, $rec['name']); // Replace literal \\t ?

	$P = _product_inflate($rec);

	try {
		$dbc_insert->execute($P);
	} catch (Exception $e) {
		_append_fail_log($idx, $e->getMessage(), $rec);
	}

	_show_progress($idx, $max);

}

_show_progress($idx, $idx);

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
