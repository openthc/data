#!/usr/bin/php
<?php
/**
 * Imports v1
 * @deprecated
 * This report is no longer updated, so we've deprecated this one
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

	foreach ($row_list as $row) {

		$idx = $row->getRowIndex();

		$lic6 = trim($wks->getCell(sprintf('A%d', $idx))->getValue());
		if (!preg_match('/\d{5,6}/', $lic6)) {

			switch ($lic6) {
			case '':
			case 'License Number':
			case 'PRODUCERS':
			case 'PROCESSORS':
			case 'RETAILERS':
			case 'PRODUCERS PERIOD TOTAL':
			case 'PROCESSORS PERIOD TOTAL':
			case 'RETAILERS PERIOD TOTAL':
				// OK & Expected
				break;
			default:
				die("NOT HANDLED: $lic6\n");
			}

			// Detect Role for Some Reason?
			// switch ($lic6) {
			// case 'PRODUCERS':
			// 	$role = 'G';
			// 	break;
			// case 'PROCESSORS':
			// 	$role = 'M';
			// 	break;
			// case 'RETAILERS':
			// 	$role = 'R';
			// 	break;
			// }

			continue;
		}

		$x = trim($wks->getCell(sprintf('B%d', $idx))->getValue());
		$date = preg_match('/([\d\/]+)$/', $x, $m) ? $m[1] : null;
		if (empty($date)) {
			echo "ROW:$idx; Failed to Parse Date: $lic6, $x\n";
		}

		$date = date('Y-m-t', strtotime($date));

		$rev_sum = trim($wks->getCell(sprintf('C%d', $idx))->getValue());
		$tax_sum = trim($wks->getCell(sprintf('D%d', $idx))->getValue());

		// Info Ouptut
		// echo "ROW: $idx; $lic6, $date, $rev_sum\n";

		$L = _find_license($lic6);
		if (empty($L['id'])) {
			echo "ROW:$idx; No License: $lic6\n";
			continue;
		}

		_revenue_record_insert($L, $date, 'lcb-v1', $rev_sum, $tax_sum);

	}
}
