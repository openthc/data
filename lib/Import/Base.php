<?php
/**
 * Import Base Class
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Data\Import;

class Base
{
	protected $_object;

	protected $_object_list;

	protected $_output_path;

	protected $_source_type;

	protected $_source_file;

	/**
	 *
	 */
	function __construct(array $cfg)
	{
		$this->_source_type = $cfg['source']['type'];
		$this->_source_file = $cfg['source']['file'];
		if ( ! is_file($this->_source_file)) {
			throw new \Exception('Invalid Source File [LIB-039]');
		}

		$this->_output_path = $cfg['output'];
		if ( ! is_dir($this->_output_path)) {
			throw new \Exception('Invalid Output Path [LIB-044]');
		}


		$this->_object = strtoupper($cfg['object']);
		if ( ! empty($this->_object)) {
			$obj_list = [
				'SECTION', 'VARIETY', 'PRODUCT',
				'CROP', 'PLANT',
				'INVENTORY', 'INVENTORY-ADJUST',
				'B2B-INCOMING', 'B2B-OUTGOING',
			];
			if ( ! in_array($this->_object, $obj_list)) {
				throw new \Exception('Invalid Object [LIB-033]');
			}
		}

	}

}
