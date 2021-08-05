#!/usr/bin/php
<?php
/**
 * Import Version 2 of the License Data
 */

require_once(__DIR__ . '/boot.php');

$url = $argv[1];
if (empty($url)) {
	echo "Call with [URL] of file\n";
	exit(1);
}

$src_file = sprintf('%s/tmp/wa/%s', APP_ROOT, basename($url));
if (!is_file($src_file)) {

	$var_path = sprintf('%s/tmp/wa', APP_ROOT);
	mkdir($var_path, 0755, true);
	chdir($var_path);

	$cmd = sprintf('wget --quiet %s', escapeshellarg($url));
	shell_exec($cmd);

}

$dbc = _dbc();

$xls = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($src_file);
$xls->setReadDataOnly(true);
$xls = $xls->load($src_file);

foreach($xls->getSheetNames() as $wks_name) {

	echo "Worksheet: $wks_name\n";

	$wks = $xls->getSheetByName($wks_name);
	$row_list = $wks->getRowIterator();

	// switch ($wks_name) {
	// case 'Retailers':
	// case 'Producers and Processors':
	// 	break;
	// default:
	// 	throw new \Exception("Invalid Sheet Name: '$n'");
	// }

	foreach ($row_list as $row) {

		$idx = $row->getRowIndex();

		$lic6 = trim($wks->getCell(sprintf('A%d', $idx))->getValue());

		$date = trim($wks->getCell(sprintf('B%d', $idx))->getValue());
		if (strlen($date)) {
			$date = strtotime($date);
			if ($date > 0) {
				$date = date('Y-m-t', $date);
			}
		}

		if (!preg_match('/\d{5,6}/', $lic6) && !preg_match('/^\d{4}/', $date)) {
			echo "ROW:$idx; Skip: $lic6, $date\n";
			continue;
		}

		$rev_sum = trim($wks->getCell(sprintf('C%d', $idx))->getValue());
		$tax_sum = trim($wks->getCell(sprintf('D%d', $idx))->getValue());

		// Info Ouptut
		// echo "ROW: $idx; $lic6, $date, $rev_sum\n";

		// Find License
		$L = _find_license($lic6);
		if (empty($L['id'])) {
			echo "ROW:$idx; No License: $lic6\n";
			continue;
		}

		_revenue_record_insert($L, $date, 'lcb-v2', $rev_sum, $tax_sum);

	}
}
