<?php
/**
 * CSV Reader
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Data\Import;

class GrowFlow extends Base
{
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
