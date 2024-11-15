<?php
/**
 * Convert from XLS to OpenTHC JSON
 *
 * SPDX-License-Identifier: GPL-3.0-only
 *
 * Every sheet is a different source type
 * Extract each sheet, identify which source type it is
 * Execute `APP_ROOT/bin/cli.php convert --source-file $source_file --source-type {$source_type}-csv --output $output_dir`
 */

namespace OpenTHC\Data\Convert;

class XLS
{
	protected $file;

	protected $output_path;

	/**
	 *
	 */
	function __construct(string $source_file, string $output_path)
	{
		$this->file = $source_file;
		// Get a basename and trim extension
		$this->base = basename($this->file);
		$this->base = preg_replace('/\.xl\w+$/i', '', $this->base);
		$this->output_path = $output_path;
	}

	/**
	 *
	 */
	function convert()
	{
		if ( ! is_dir($this->output_path)) {
			throw new \Exception("Cannot Output to '{$this->output_path}' [DCC-029]");
		}

		if ( ! is_writable($this->output_path)) {
			throw new \Exception("Cannot Output to '{$this->output_path}' [DCC-033]");
		}

		$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($this->file);
		foreach($spreadsheet->getSheetNames() as $idx => $wks_name) {

			$csv_file = sprintf('%s/%s-%s.csv', $this->output_path, $this->base, $wks_name);

			echo "OUTPUT {$csv_file}\n";

			$writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
			$writer->setSheetIndex( $idx );
			$writer->save($csv_file);
		}

	}
}
