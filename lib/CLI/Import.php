<?php
/**
 * Import Command Line
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Data\CLI;

class Import
{
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
			cre-ccrs [options] --source=<FILE> --source-type=<TYPE> --object=<TYPE> --output=<FILE>

		Options:
			--source=<FILE>
			--source-type=<TYPE>
			--object=<LIST>
			--output=<FILE>
		TXT;

		$res = \Docopt::handle($doc, [
			'argv' => $cli_args,
			'help' => true,
			'optionsFirst' => true,
		]);
		$cli_args = $res->args;

		$this->source_file = $cli_args['--source-file'];
		$this->source_type = strtoupper($cli_args['--source-type']);

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

			$importer = new  \OpenTHC\Data\Import\Cultivera\HAR([
				'source' => [
					'file' => $cli_args['--source'],
				],
				'output' => $cli_args['--output']
			]);
			$importer->output();

			break;
		case 'CULTIVERA-XLSX':

			$importer = new OpenTHC\Data\Import\Cultivera([
				'object' => $cli_args['--object'],
				'output' => $cli_args['--output'],
				'source' => [
					'file' => $cli_args['--source'],
					'type' => 'xlsx',
				]
			]);

			$importer->import();

			break;

		case 'GROWFLOW-CSV':
			$importer = new OpenTHC\Data\Import\GrowFlow([
				'object' => $cli_args['--object'],
				'source' => [
					'type' => 'csv',
					'file' => $cli_args['--source'],
				],
			]);
			// $exporter = new \OpenTHC\Data\Export\JSON([
			// 	'type' => 'json',
			// 	'ouptut' => $cli_args['--output'],
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
			$importer = new OpenTHC\Data\Convert\CSV([
				'object' => $cli_args['--object'],
				'source' => [
					'type' => 'csv',
					'file' => $cli_args['--source'],
				],
			]);
			break;
		default:
			echo "Unexpected Source Type: '{$cli_args['--source-type']}'\n";
			exit(1);
		}

	}

}
