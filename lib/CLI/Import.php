<?php
/**
 * Import Command Line
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Data\CLI;

class Import
{
	protected $cli_args;

	protected $source_file;

	protected $source_type;

	/**
	 *
	 */
	function __construct($cli_args)
	{

		$doc = <<<TXT
		OpenTHC Data Import Tool

		Usage:
			cre-ccrs [options] --source-file=<FILE> --source-type=<TYPE> --object=<TYPE> --output=<FILE>

		Options:
			--source-file=<FILE>
			--source-type=<TYPE>
			--object=<LIST>
			--output=<FILE>
		TXT;

		$res = \Docopt::handle($doc, [
			'argv' => $cli_args,
			'help' => true,
			'optionsFirst' => true,
		]);
		$this->cli_args = $res->args;

		$this->source_file = $this->cli_args['--source-file'];

		$this->source_type = strtoupper($this->cli_args['--source-type']);

	}

	function execute()
	{
		$source_type_list = [
			'BioTrack-API',
			'BioTrack-SQL',
			'CCRS-TSV',
			'Cultivera-XLSX',
			'GrowFlow-CSV',
			'GrowFlow-HAR',
			'Metrc-API',
			'TraceWeed-CSV',
		];

		switch ($this->source_type) {
		case 'BAMBOO-XLSX':

			$importer = new \OpenTHC\Data\Import\Bamboo([
				'object' => $this->cli_args['--object'],
				'output' => $this->cli_args['--output'],
				'source' => [
					'file' => $this->cli_args['--source-file'],
					'type' => 'xlsx',
				]
			]);

			$importer->import();

			break;

		case 'BIOTRACK-API':
			// require_once(APP_ROOT . '/lib/Import/Metrc.php');
			break;
		case 'BIOTRACK-SQL':
			// require_once(APP_ROOT . '/lib/Import/Metrc.php');
			break;
		case 'CCRS-TSV':
			// require_once(APP_ROOT . '/lib/Import/Metrc.php');
			break;
		case 'CULTIVERA-HAR':

			$importer = new \OpenTHC\Data\Import\Cultivera\HAR([
				'source' => [
					'file' => $this->cli_args['--source'],
				],
				'output' => $this->cli_args['--output']
			]);
			$importer->output();

			break;
		case 'CULTIVERA-XLSX':

			$importer = new \OpenTHC\Data\Import\Cultivera([
				'object' => $this->cli_args['--object'],
				'output' => $this->cli_args['--output'],
				'source' => [
					'file' => $this->cli_args['--source-file'],
					'type' => 'xlsx',
				]
			]);

			$importer->import();

			break;

		case 'GROWFLOW-CSV':
			$importer = new \OpenTHC\Data\Import\GrowFlow([
				'object' => $this->cli_args['--object'],
				'source' => [
					'type' => 'csv',
					'file' => $this->cli_args['--source-file'],
				],
			]);
			// $exporter = new \OpenTHC\Data\Export\JSON([
			// 	'type' => 'json',
			// 	'ouptut' => $this->cli_args['--output'],
			// ]);
			// $importer->setExporter($exporter);
			$importer->import();
			break;
		case 'GROWFLOW-HAR':
			// require_once(APP_ROOT . '/lib/Import/Metrc.php');
			break;
		case 'LIST':
			echo "Source Types:\n";
			echo implode(', ', $source_type_list);
			echo "\n";
			exit(0);
		case 'METRC-API':
			require_once(APP_ROOT . '/lib/Import/Metrc.php');
			break;
		case 'CSV':
			$importer = new \OpenTHC\Data\Convert\CSV([
				'object' => $this->cli_args['--object'],
				'source' => [
					'type' => 'csv',
					'file' => $this->cli_args['--source-file'],
				],
			]);
			break;
		default:
			echo "Unexpected Source Type: '{$this->cli_args['--source-type']}'\n";
			exit(1);
		}

	}

}
