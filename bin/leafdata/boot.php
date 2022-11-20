<?php
/**
 * LeafData Bootstrap
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/boot.php');

function _find_max($f, $csv)
{
	$max = 0;
	$max_file = preg_match('/\.tsv$/', '.max', $f);

	if (is_file($max_file)) {
		$max = intval(file_get_contents($max_file));
	}

	if (0 == $max) {
		$max = $csv->rowEstimate();
	}

	return $max;

}

function de_fuck_date_format($rec)
{
	$date_field_list = array(
		'batch_created_at',
		'created_at',
		'deleted_at',
		'disposal_at',
		'harvested_at',
		'hold_ends_at',
		'hold_starts_at',
		'inventory_created_at',
		'inventory_expires_at',
		'inventory_packaged_at',
		'lab_results_date',
		'packaged_completed_at',
		'plant_created_at',
		'plant_harvested_at',
		'planted_at',
		'transferred_at',
		'updated_at',
		'sold_at',
	);

	foreach ($date_field_list as $f) {

		$d = trim($rec[$f]);

		if (empty($d)) {
			continue;
		}

		// MySQL
		if ('00/00/0000' == $d) {
			$rec[$f] = null;
			continue;
		}

		if ('0000-00-00 00:00:00' == $d) {
			$rec[$f] = null;
			continue;
		}

		if ('1900-01-01 00:00:00' == $d) {
			$rec[$f] = null;
			continue;
		}

		$d = strtotime($d);
		if ($d > 0) {
			$rec[$f] = strftime('%Y-%m-%d %H:%M:%S', $d);
		} else {
			// Handle Stupid Shit
			if (preg_match('/^(.+ )(\d+):(\d+)(am|pm)$/i', $rec[$f], $m)) {
				$d = $m[1];
				$hh = intval($m[2]);
				$mm = intval($m[3]);
				if ($hh >= 13) {
					$d.= sprintf('%02d:%02d', $hh, $mm);
				} elseif ($m[4] == 'pm') {
					$d.= sprintf('%02d:%02d', $hh + 12, $mm);
				} else {
					$d.= sprintf('%02d:%02d', $hh, $mm);
				}
				$d = strtotime($d);
				if ($d > 0) {
					$rec[$f] = strftime('%Y-%m-%d %H:%M:%S', $d);
				} else {
					throw new Exception('Really Bad Date');
				}
			}
		}
	}

	return $rec;

}


function _append_fail_log($idx, $why, $rec)
{
	if (preg_match('/Key.+id.+already exists/', $why)) {
		return(0);
	}

	$rec = json_encode($rec);

	$msg = "Record: $idx; $why; $rec;\n";

	// $fh = fopen('/opt/data.openthc.org/output-data.out', 'a');
	// fwrite($fh, $msg);
	// fclose($fh);

	echo "$msg\n";

}

function _product_inflate($rec)
{
	$p = array(
		':id' => $rec['global_id'],
		':license_id' => $rec['mme_id'],
		':product_type' => $rec['intermediate_type'],
		':package_type' => null,
		':package_unit' => $rec['uom'],
		':package_size' => null,
		':name' => trim($rec['name']),
	);

	switch ($p[':product_type']) {
		case 'co2_concentrate':
		case 'daily_plant_waste': // WTF?
		case 'ethanol_concentrate':
		case 'flower':
		case 'flower_lots':
		case 'food_grade_solvent_concentrate':
		case 'hydrocarbon_concentrate':
		case 'infused_cooking_medium':
		case 'marijuana_mix':
		case 'non-solvent_based_concentrate':
		case 'other_material':
		case 'other_material_lots':
		case 'waste':
		case '':
			$p[':package_type'] = 'bulk';
			$p[':package_unit'] = 'g';
			break;
		case 'clone': // Typo in LeafData
		case 'clones':
		case 'mature_plant':
		case 'non_mandatory_plant_sample':
		case 'plant': // Not even defined by their system, how did this happen?
		case 'plant_tissue':
		case 'tissue': // Another bogus
		case 'seed':
		case 'seeds':
			$p[':package_type'] = 'each';
			$p[':package_unit'] = 'ea';
			break;
		case 'capsules':
		case 'concentrate_for_inhalation':
		case 'infused_mix':
		case 'liquid_edible':
		case 'packaged_marijuana_mix':
		case 'sample_jar':
		case 'solid_edible':
		case 'tinctures':
		case 'topical':
		case 'transdermal_patches':
		case 'suppository':
		case 'usable_marijuana':
			$p[':package_type'] = 'each';
			if (preg_match('/^(.+) \- ([\d\.]+)\s*(ea|g|gm|gr|gram|grams|mg|ml|oz)\b/i', $p[':name'], $m)) {
				$p[':package_size'] = floatval($m[2]);
				$p[':package_unit'] = $m[3];
			} elseif (preg_match('/^(.+) ([\d\.]+)\s*(ea|g|gm|gr|gram|grams|mg|ml|oz)\b/i', $p[':name'], $m)) {
				$p[':package_size'] = floatval($m[2]);
				$p[':package_unit'] = $m[3];
			} elseif (preg_match('/\b([\d\.]+)\s*(ea|g|gm|gr|gram|grams|mg|ml|oz)\b/i', $p[':name'], $m)) {
				$p[':package_size'] = floatval($m[1]);
				$p[':package_unit'] = $m[2];
			} else {
				// echo "No Match: '{$p['name']}'\n";
			}
			break;
		default:
			die("Unknown Product Type: '{$p[':product_type']}'");
	}

	return $p;

}
