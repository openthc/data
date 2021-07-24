#!/usr/bin/php
<?php
/**
 * Import Lab Results
 */

require_once(__DIR__ . '/boot.php');

$f = $argv[1];
if (!is_file($f)) {
	echo "Create the source file at '$f'\n";
	exit(1);
}

$csv = new CSV_Reader($f);

$dbc = _dbc();

$idx = 1;
$max = _find_max($f, $csv);

while ($rec = $csv->fetch()) {

	$idx++;

	if ($csv->key_size != count($rec)) {
		_append_fail_log($idx, 'Field Count Issue', $rec);
		continue;
	}

	$rec = array_combine($csv->key_list, $rec);

	if (empty($rec['global_id'])) {
		// _append_fail_log($idx, 'Missing Global ID', $rec);
		continue;
	}

	if ('waste' == $rec['type']) {
		_append_fail_log($idx, 'Is Waste', $rec);
		continue;
	}

	if (empty($rec['type'])) {
		$rec['type'] = 'marijuana';
	}
	if (empty($rec['intermediate_type'])) {
		$rec['intermediate_type'] = 'usable_marijuana';
	}

	// Clean and Drop Empty Fields
	foreach ($csv->key_list as $k) {
		$rec[$k] = trim($rec[$k]);
		if (empty($rec[$k])) {
			unset($rec[$k]);
		}
	}

	// Parse LabID from Inventory IDs?
	// $lab_want = strtok($rec['global_id'], '.');
	// $lab_want = preg_replace('/^WA/', null, $lab_want);
	//
	// $own_want = strtok($rec['global_for_inventory_id'], '.');
	// $own_want = preg_replace('/^WA/', null, $own_want);

	// echo "Lab: $lab_want; Own: $own_want\n";

	// // Lookup from Static list of labs and barf on error?
	// $res = $select_license->execute(array($lab_want));
	// $L_Lab = $select_license->fetch(PDO::FETCH_ASSOC);
	// // var_dump($L_Lab);
	// if (empty($L_Lab)) {
	// 	echo "FAIL: $idx; Cannot Find Laboratory; ";
	// 	echo implode(',', $rec);
	// 	echo "\n";
	// 	continue;
	// }
	//
	// $select_license->execute(array($own_want));
	// $L_Own = $select_license->fetch(PDO::FETCH_ASSOC);
	// // var_dump($L_Own);
	// if (empty($L_Own)) {
	// 	echo "WARN: $idx; Cannot Find Owner; ";
	// 	//echo implode(',', $rec);
	// 	//echo "\n";
	// 	// continue;
	// 	$L_Own = array();
	// 	$L_Own['id'] = $dbc->insert('license', array(
	// 		'id' => 'LOST.' . $own_want,
	// 		'code' => $own_want,
	// 		'name' => '-Phantom License-',
	// 	));
	// }

	$cbd = 0;
	$thc = 0;

	$type = sprintf('%s/%s', $rec['type'], $rec['intermediate_type']);

	// We're not doing the factor on purpose?
	switch ($type) {
	case 'end_product/usable_marijuana':
	case 'end_product/infused_mix':
	case 'end_product/packaged_marijuana_mix':
	case 'end_product/sample_jar':
	case 'harvest_materials/flower':
	case 'harvest_materials/flower_lots':
	case 'harvest_materials/usable_marijuana':
	case 'immature_plant/plant_tissue':
	case 'intermediate_product/co2_concentrate':
	case 'intermediate_product/flower':
	case 'intermediate_product/marijuana_mix':
	case 'intermediate_product/usable_marijuana':
	case 'marijuana/':
	case 'marijuana/flower':
	case 'marijuana/usable_marijuana':
	case 'mature_plant/mature_plant':
	case 'mature_plant/non_mandatory_plant_sample':
		$cbd = floatval($rec['cannabinoid_cbd_percent'])    + (floatval($rec['cannabinoid_cbda_percent'])    * 0.877);
		$thc = floatval($rec['cannabinoid_d9_thc_percent']) + (floatval($rec['cannabinoid_d9_thca_percent']) * 0.877);
		break;
	case 'end_product/capsules':
	case 'end_product/concentrate_for_inhalation':
	case 'end_product/liquid_edible':
	case 'end_product/solid_edible':
	case 'end_product/tinctures':
	case 'end_product/topical':
	case 'end_product/transdermal_patches':
	case 'intermediate_product/ethanol_concentrate':
	case 'intermediate_product/food_grade_solvent_concentrate':
	case 'intermediate_product/hydrocarbon_concentrate':
	case 'intermediate_product/infused_cooking_medium':
	case 'intermediate_product/non-solvent_based_concentrate':
		// MG
		$cbd = floatval($rec['cannabinoid_cbd_mg_g'])    + (floatval($rec['cannabinoid_cbda_mg_g'])    * 0.877);
		$thc = floatval($rec['cannabinoid_d9_thc_mg_g']) + (floatval($rec['cannabinoid_d9_thca_mg_g']) * 0.877);
		break;
	default:
		die("\n$idx: Bad Type: '$type'\n");
		break;
	}

	try {
		$add = array(
			'id' => $rec['global_id'],
			'license_id' => $rec['mme_id'],
			'created_at' => $rec['created_at'],
			'updated_at' => $rec['updated_at'],
			'deleted_at' => $rec['deleted_at'],
			'type' => $type,
			// 'stat' => //
			'cbd' => $cbd,
			'thc' => $thc,
			// 'name' => trim($rec['name']),
			'meta' => json_encode($rec),
		);
		if (empty($add['created_at'])) {
			unset($add['created_at']);
		}
		if (empty($add['updated_at'])) {
			unset($add['updated_at']);
		}
		if (empty($add['deleted_at'])) {
			unset($add['deleted_at']);
		}

		$dbc->insert('lab_result', $add);

	} catch (Exception $e) {
		 _append_fail_log($idx, $e->getMessage(), $rec);
	}

	// Sample is the Inventory Item Sent to the Lab
	// Owned by the Supply-Side License
	// Make sure these are looking like Supply Side IDs (not Lab IDs?)
	// $dbc->insert('lab_sample', array(
	// 	/*
	// 		'id' => from CSV
	// 		'license_id' => Owner of the Lab Sample, not the Lab
	// 		'meta' => ??
	// 	*/
	// ));

	// Link Result to Sample_Lot+=Parent_Lot
	if (!empty($rec['global_for_inventory_id'])) {
		try {
			$dbc->query('INSERT INTO lab_result_lot (lab_result_id, lot_id) VALUES (:lr0, :il1)', array(
				':lr0' => $rec['global_id'],
				':il1' => $rec['global_for_inventory_id'],
			));
		} catch (Exception $e) {
			_append_fail_log($idx, $e->getMessage(), $rec);
		}
	}

	_show_progress($idx, $max);

}

_show_progress($idx, $idx);

// Patch Into Four Types
$map_type = [
	'end_product/capsules' => 'Other',
	'end_product/concentrate_for_inhalation' => 'Extract',
	'end_product/infused_mix' => 'Mixed',
	'end_product/liquid_edible' => 'Edible',
	'end_product/packaged_marijuana_mix' => 'Mixed',
	'end_product/sample_jar' => 'Other',
	'end_product/solid_edible' => 'Edible',
	'end_product/tinctures' => 'Other',
	'end_product/topical' => 'Other',
	'end_product/transdermal_patches' => 'Other',
	'end_product/usable_marijuana' => 'Flower',
	'harvest_materials/flower' => 'Flower',
	'harvest_materials/flower_lots' => 'Flower',
	'harvest_materials/usable_marijuana' => 'Flower',
	'immature_plant/plant_tissue' => 'Other',
	'intermediate_product/co2_concentrate' => 'Extract',
	'intermediate_product/ethanol_concentrate' => 'Extract',
	'intermediate_product/flower' => 'Flower',
	'intermediate_product/food_grade_solvent_concentrate' => 'Edible',
	'intermediate_product/hydrocarbon_concentrate' => 'Extract',
	'intermediate_product/infused_cooking_medium' => 'Edible',
	'intermediate_product/marijuana_mix' => 'Mixed',
	'intermediate_product/non-solvent_based_concentrate' => 'Extract',
	'intermediate_product/usable_marijuana' => 'Flower',
	'marijuana/flower' => 'Flower',
	'marijuana/usable_marijuana' => 'Flower',
	'mature_plant/mature_plant' => 'Other',
	'mature_plant/non_mandatory_plant_sample' => 'Other',
];

foreach ($map_type as $t0 => $t1) {

	if (empty($t1)) {
			echo "Skip: $t0\n";
			continue;
	}

	$sql = 'UPDATE lab_result SET type = :t1 WHERE type = :t0';
	$arg = [
			':t0' => $t0,
			':t1' => $t1,
	];

	// $dbc->query($sql, $arg);

}


// Update the License ID Source on Lab Result
// $sql = <<<SQL
// UPDATE lab_result
//  SET source_license_id = substr(meta->>'global_for_inventory_id', 3, 7)
// WHERE source_license_id IS NULL
// SQL;

// $sql = <<<SQL
// UPDATE lab_result SET source_license_id = (SELECT id FROM license WHERE license.code = lab_result.source_license_id)
