<?php
/**
 * Cultivera XLSX Reader
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Data\Import;

class Cultivera extends Base
{
	/**
	 *
	 */
	function import()
	{
		switch ($this->_object) {
			case 'CROP':
			case 'PLANT':

				$reader = new Cultivera\Crop([
					'output-path' => $this->_output_path,
					'source-file' => $this->_source_file
				]);

				return $reader->import();

				break;

			case 'INVENTORY':

				$reader = new Cultivera\Inventory([
					'output-path' => $this->_output_path,
					'source-file' => $this->_source_file
				]);

				return $reader->import();

				break;

			case 'PRODUCT':
				require_once(__DIR__ . '/Cultivera/import-product-csv.php');
				break;
			case 'SECTION':
				require_once(__DIR__ . '/Cultivera/import-section-csv.php');
				break;
			case 'VARIETY':
				require_once(__DIR__ . '/Cultivera/import-variety-csv.php');
				break;
			default:
				throw new \Exception('Invalid Import');
		}
	}

}
