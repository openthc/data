#!/usr/bin/php
<?php
/**
 * Imports v1
 * @deprecated
 * This report is no longer updated, so we've deprecated this one
 */

use Edoceo\Radix\Net\HTTP;

require_once(__DIR__ . '/boot.php');

$xls_file = sprintf('%s/source-data/usa-wa-license-revenue-v1.xlsx', APP_ROOT);
if (!is_file($xls_file)) {

	$xls_link = 'https://lcb.wa.gov/sites/default/files/publications/Marijuana/sales_activity/By-License-Number-MJ-Tax-Obligation-by-Licensee-thru-10_31_17.xlsx';

	$res = HTTP::get($xls_link);
	if ($res['info']['http_code'] != 200) {
		die("No Data\n");
	}

	file_put_contents($xls_file, $res['body']);

}

$dbc = _dbc();

$xls = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($xls_file);
$xls->setReadDataOnly(true);
$xls = $xls->load($xls_file);

$lic_skip = [];

foreach($xls->getSheetNames() as $n) {

	$wks = $xls->getSheetByName($n);
	$row_list = $wks->getRowIterator();
	$row_max = $wks->getHighestRow();

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

			continue;
		}

		if (!empty($lic_skip[$lic6])) {
			continue;
		}

		$date = trim($wks->getCell(sprintf('B%d', $idx))->getValue());
		$date = preg_match('/^([\d\/]+) /', $date, $m) ? $m[1] : null;
		if (empty($date)) {
			echo "Lic: $lic6 - $date\n";
			die("Failed to Parse Date, Row: $idx\n");
		}

		// Skip Old Stuff
		$date = strftime('%Y-%m-%d', strtotime($date));

		$rev_sum = trim($wks->getCell(sprintf('C%d', $idx))->getValue());
		$tax_sum = trim($wks->getCell(sprintf('D%d', $idx))->getValue());

		$L = _find_license($lic6);
		if (empty($L['id'])) {
			echo "No License: $lic6\n";
			$lic_skip[$lic6] = $lic6;
			continue;
		}

		_revenue_record_insert($L, $date, 'lcb-v1', $rev_sum, $tax_sum);

	}
}
