<?php
/**
 * Convert from CSV to OpenTHC JSON
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Data\Convert;

class CSV
{
	protected $file;

	protected $type;

	protected $output_path;

	/**
	 *
	 */
	function __construct(string $source_file, string $source_type)
	{
		$this->file = $source_file;
		$this->type = $source_type;
		$this->type = preg_replace('/\-CSV$/', '', $this->type);
	}


	/**
	 *
	 */
	function convert($output_path)
	{
		if ( ! is_dir($output_path)) {
			throw new \Exception("Cannot Output to '{$output_path}' [DCC-029]");
		}

		if ( ! is_writable($output_path)) {
			throw new \Exception("Cannot Output to '{$output_path}' [DCC-033]");
		}

		$this->output_path = $output_path;

		switch ($this->type) {
		case 'CROP':
		case 'PLANT':
			return $this->convertCrop();
			break;
		case 'INVENTORY':
			return $this->convertInventory();
			break;
		case 'PRODUCT':
			return $this->convertProduct();
			break;
		case 'SECTION':
			return $this->convertSection();
			break;
		case 'VARIETY':
			return $this->convertVariety();
			break;
		default:
			throw new \Exception("Source Type '{$this->type}' Not Handled [DCC-032]");
		}

	}

	/**
	 * Convert Crop into OpenTHC Object Type
	 */
	function convertCrop()
	{
		$source_data = new \OpenTHC\Data\CSV\Reader($this->file);
		$source_head = $source_data->getHeader();

		$obj_list = [];
		while ($row = $source_data->fetch('array')) {

			$x = [];
			$x['id'] = $row['CROP_GUID'] ?: $row['PLANT_GUID'];
			$x['qty'] = $row['UNIT_COUNT'];  // UNIT_COUNT_INITITAL _CURRENT
			$x['section'] = [
				'id' => $row['SECTION_GUID'],
				'name' => $row['SECTION_NAME']
			];
			$x['variety'] = [
				'id' => $row['VARIETY_GUID'],
				'name' => $row['VARIETY_NAME']
			];

			$this->writeObject('crop', $x);

		}

	}

	/**
	 * Convert Inventory into OpenTHC Object Type
	 */
	function convertInventory()
	{
		$source_data = new \OpenTHC\Data\CSV\Reader($this->file);
		$source_head = $source_data->getHeader();

		// List of Headers that MUST be present
		$chk_list = [];
		$chk_list['INVENTORY_GUID'];
		$chk_list['VARIETY_NAME'];
		$chk_list['PRODUCT_NAME'];
		$chk_list['UNIT_COUNT'];
		foreach ($chk_list as $chk) {
			if ( ! in_array($chk, $source_head)) {
				throw new \Exception("Cannot Convert; Header '{$chk}' Missing [DCC-062]");
			}
		}

		$obj_list = [];
		while ($row = $source_data->fetch('array')) {

			$x = [];
			$x['id'] = $row['INVENTORY_GUID'];
			$x['qty'] = $row['UNIT_COUNT'];  // UNIT_COUNT_INITITAL _CURRENT
			$x['product'] = [
				'id' => $row['PRODUCT_GUID'],
				'name' => $row['PRODUCT_NAME'],
				'type' => [
					'id' => '',
					'name' => $row['PRODUCT_TYPE'],
				],
				'unit_weight' => $row['PRODUCT_WEIGHT'],
			];
			$x['section'] = [
				'id' => $row['SECTION_GUID'],
				'name' => $row['SECTION_NAME']
			];
			$x['variety'] = [
				'id' => $row['VARIETY_GUID'],
				'name' => $row['VARIETY_NAME']
			];
			// Lab Result?
			$x['lab'] = [];
			$x['lab']['result'] = [];
			$x['lab']['result'][0] = [
				'id' => $row['LAB_RESULT_GUID'],
				'link' => $row['LAB_RESULT_LINK_DATA'], // QA_Results_URL
				'coa_link' => $row['LAB_RESULT_LINK_COA'], // QA_COA_URL
			];

			// By TYPE Do Something?
			// $x['qty_initial'] =

			$this->writeObject('inventory', $x);

		}

	}


	/**
	 *
	 */
	function convertProduct()
	{
		$source_data = new \OpenTHC\Data\CSV\Reader($this->file);
		$source_head = $source_data->getHeader();

		if ( ! in_array('PRODUCT_NAME', $source_head)) {
			throw new \Exception("Cannot Convert w/o Product Name [DCC-062]");
		}

		$product_list = [];
		while ($row = $source_data->fetch('array')) {

			$x = [];
			$x['id'] = $row['PRODUCT_GUID'];
			$x['name'] = $row['PRODUCT_NAME'];
			$x['type'] = $row['PRODUCT_TYPE'];
			$x['unit_weight'] = $row['PRODUCT_WEIGHT'];

			$k = implode('.', array_values($x));
			$product_list[$k] = $x;

		}

		foreach ($product_list as $p) {
			$this->writeObject('product', $p);
		}

	}


	/**
	 *
	 */
	function convertSection()
	{
		$source_data = new \OpenTHC\Data\CSV\Reader($this->file);
		$source_head = $source_data->getHeader();

		if ( ! in_array('SECTION_NAME', $source_head)) {
			throw new \Exception("Cannot Convert w/o Section Name [DCC-046]");
		}

		$obj_list = [];
		while ($row = $source_data->fetch('array')) {

			$s = [];
			$s['id'] = $row['SECTION_GUID'];
			$s['name'] = $row['SECTION_NAME'];
			$s['type'] = $row['SECTION_TYPE'] ?: 'INVENTORY';

			$k = implode('.', array_values($s));
			$obj_list[$k] = $s;

		}

		// var_dump($section_list);
		foreach ($obj_list as $o) {
			$this->writeObject('section', $o);
		}

	}


	/**
	 *
	 */
	function convertVariety()
	{
		$source_data = new \OpenTHC\Data\CSV\Reader($this->file);
		$source_head = $source_data->getHeader();

		if ( ! in_array('VARIETY_NAME', $source_head)) {
			throw new \Exception("Cannot Convert w/o Variety Name [DCC-141]");
		}

		// Collect Objects
		$obj_list = [];
		while ($row = $source_data->fetch('array')) {

			$s = [];
			$s['id'] = $row['VARIETY_GUID'];
			$s['name'] = $row['VARIETY_NAME'];
			$s['type'] = $row['VARIETY_TYPE'] ?: 'HYBRID';

			$k = implode('.', array_values($s));
			$obj_list[$k] = $s;

		}

		// Write Objects
		foreach ($obj_list as $k => $o) {
			$this->writeObject('variety', $o);
		}

	}


	/**
	 * Create a Special, Predictable "ULID"
	 */
	protected function createId(string $k) : string
	{
		// Get 128 Bits
		$x = md5($k, true);
		// Reset the First Two Bits
		$x[0] = "\x00";
		$x[1] = "\xFE";
		// Get a UUID Type String (can set Version?)
		$x = \Ramsey\Uuid\Uuid::fromBytes($x);
		// Convert to ULID for us to use
		$x = \Mpyw\UuidUlidConverter\Converter::uuidToUlid($x->toString());

		return $x;

	}


	/**
	 * Dump File
	 */
	protected function writeObject($obj_type, $obj_data)
	{
		$k = implode('.', array_values($obj_data));
		if (empty($obj_data['id'])) {
			$obj_data['id'] = $this->createId($k);
		}

		$output_file = sprintf('%s/%s-%s.json', $this->output_path, $obj_type, $obj_data['id']);
		$output_data = json_encode($obj_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

		return file_put_contents($output_file, $output_data);

	}
}
