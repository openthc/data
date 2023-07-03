<?php
/**
 * Cultivera Crop Import
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Data\Import\Cultivera;

class Crop
{
	const HEADER_ROW = "Plant#	Strain	Phase / Stage	Room	Source Type	Plant Date	Age	Last Harvested";

	protected $_output_path;

	protected $_source_file;

	/**
	 *
	 */
	function __construct(array $cfg)
	{
		$this->_output_path = $cfg['output-path'];
		$this->_source_file = $cfg['source-file'];
	}

	/**
	 *
	 */
	function import()
	{
		$xls = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($this->_source_file);
		$xls->setReadDataOnly(true);
		$xls = $xls->load($this->_source_file);

		$wks_list = $xls->getSheetNames();
		if (count($wks_list) != 1) {
			throw new \Exception('Invalid Workbook [ICP-031]');
		}

		$wks_name = array_shift($wks_list);
		$wks = $xls->getSheetByName($wks_name);

		return $this->import_worksheet($wks);

	}

	/**
	 *
	 */
	function import_worksheet($wks)
	{
		$wks_data = $wks->toArray();

		$wks_head = $wks_data[0];
		$wks_head_text = implode("\t", $wks_head);

		if ($wks_head_text != self::HEADER_ROW) {
			throw new \Exception('Unexpected File Layout [ICP-060]');
		}
		array_shift($wks_data);

		foreach ($wks_data as $row) {

			$row = array_combine($wks_head, $row);

			$obj = [];
			$obj['id'] = $row['Plant#'];
			$obj['qty'] = 1;
			$obj['created_at'] = $row['Plant Date'];
			$obj['section'] = [
				'id' => null,
				'name' => $row['Room'],
			];
			$obj['variety'] = [
				'id' => null,
				'name' => $row['Strain'],
			];
			$obj['meta'] = [
				'@version' => 'cultivera/2017',
				'@source' => $row,
			];

			$obj_hash = \OpenTHC\CRE\Base::objHash($obj);
			$obj_data = json_encode($obj, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
			$obj_file = sprintf('%s/crop-%s.json', $this->_output_path, $obj_hash);

			file_put_contents($obj_file, $obj_data);

		}

	}

}
