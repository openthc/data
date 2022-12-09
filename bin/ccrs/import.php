#!/usr/bin/php
<?php
/**
 * Imports X from CCRS
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

$d = __DIR__;
$d = dirname($d);
$d = dirname($d);
require_once("$d/boot.php");

switch ($argv[1]) {
	case 'b2b':
	case 'b2c':
	case 'sale':
		// Same File
		require_once(__DIR__ . '/import-sale.php');
		break;
	case 'inventory':
	case 'lot':
		require_once(__DIR__ . '/import-inventory.php');
		break;
	case 'license':
		require_once(__DIR__ . '/import-license.php');
		break;
	case 'product':
		require_once(__DIR__ . '/import-product.php');
		break;
	case 'variety':
		require_once(__DIR__ . '/import-variety.php');
		break;
	default:
		echo "Use this script to execute one of the import-*.php scripts in here\n";
		$file_list = glob(sprintf('%s/import-*.php', __DIR__));
		print_r($file_list);
		exit(1);
}



/**
 * Check the row is importing properly
 */
function _csv_row_map_check($csv_head, $row, $idx) : ?array
{
	if (count($csv_head) != count($row)) {

		$c0 = count($csv_head);
		$c1 = count($row);

		echo "FAIL@{$idx}[$c0/$c1]:";
		echo implode("\t", $row);
		echo "\n";

		return null;
	}

	$row = array_combine($csv_head, $row);
	if (isset($row['ISDELETED'])) {
		$row['ISDELETED'] = strtoupper($row['ISDELETED']);
	}
	if (isset($row['ISMEDICAL'])) {
		$row['ISMEDICAL'] = strtoupper($row['ISMEDICAL']);
	}


	return $row;

}
