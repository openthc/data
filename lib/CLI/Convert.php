<?php
/**
 * Convert Command Line
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Data\CLI;

class Convert
{
	/**
	 *
	 */
	function __construct($cli_args)
	{
		$doc = <<<DOC
		OpenTHC OPS Command Line

		Usage:
			convert --source-file=<FILE> --source-type=<TYPE> [--output-path=<PATH>]

		Options:
			Options are specific to the sub-command chosen.
			Get a list by passing an unknown command (eg: list-all)

		DOC;
		$res = \Docopt::handle($doc, [
			'argv' => $cli_args,
			'help' => true,
			'optionsFirst' => true,
		]);
		$cli_args = $res->args;
		// var_dump($cli_args);

		$this->source_file = $cli_args['--source-file'];
		$this->source_type = strtoupper($cli_args['--source-type']);
		$this->output_path = $cli_args['--output-path'];
		if (empty($this->output_path)) {
			$this->output_path = sprintf('%s/output-data/convert-%s', APP_ROOT, _ulid());
		}
		if ( ! is_dir($this->output_path)) {
			mkdir($this->output_path, 0755, true);
		}

	}

	/**
	 *
	 */
	function execute()
	{
		switch ($this->source_type) {
		case 'CROP-CSV':
		case 'PLANT-CSV':
		case 'INVENTORY-CSV':
		case 'PRODUCT-CSV':
		case 'SECTION-CSV':
		case 'VARIETY-CSV':
			$converter = new \OpenTHC\Data\Convert\CSV($this->source_file, $this->source_type);
			return $converter->convert($this->output_path);
			break;
		case 'XLS':
		case 'PROFILE-XLS':
			$converter = new \OpenTHC\Data\Convert\XLS($this->source_file, $this->output_path);
			return $converter->convert();
			break;
		default:
			throw new \Exception("Invalid Source Type '{$this->source_type}' [DCC-059]");
		}
	}
}
