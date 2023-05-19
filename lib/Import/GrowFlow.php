<?php
/**
 * CSV Reader
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Data\Import;

class GrowFlow
{
	/**
	 *
	 */
	function __construct(array $cfg)
	{
		$this->_object = strtoupper($cfg['object']);
		$obj_list = [
			'SECTION', 'VARIETY', 'PRODUCT',
			'CROP', 'PLANT',
			'INVENTORY', 'INVENTORY-ADJUST',
			'B2B-INCOMING', 'B2B-OUTGOING',
		];
		if ( ! in_array($this->_object, $obj_list)) {
			throw new \Exception('Invalid Object [LIG-025]');
		}

		$this->_source_type = $cfg['source']['type'];
		$this->_source_file = $cfg['source']['file'];
		if ( ! is_file($this->_source_file)) {
			throw new \Exception('Invalid Source File [LIG-020]');
		}

	}

	/**
	 *
	 */
	function import()
	{
		switch ($this->_object) {
			case 'CROP':
			case 'PLANT':
				require_once(__DIR__ . '/GrowFlow/import-crop-csv.php');
				break;
			case 'INVENTORY':
				require_once(__DIR__ . '/GrowFlow/import-inventory-csv.php');
				break;
			case 'PRODUCT':
				require_once(__DIR__ . '/GrowFlow/import-product-csv.php');
				break;
			case 'SECTION':
				require_once(__DIR__ . '/GrowFlow/import-section-csv.php');
				break;
			case 'VARIETY':
				require_once(__DIR__ . '/GrowFlow/import-variety-csv.php');
				break;
			default:
				throw new \Exception('Invalid Import');
		}
	}

	/**
	 *
	 */
	function setExporter()
	{

	}

}
