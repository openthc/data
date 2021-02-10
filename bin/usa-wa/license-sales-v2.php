#!/usr/bin/php
<?php
/**
 * Import Version 2 of the License Data
 */

use Edoceo\Radix\Net\HTTP;

require_once(__DIR__ . '/boot.php');

$xls_file = sprintf('%s/source-data/usa-wa-license-revenue-v2.xlsx', APP_ROOT);
if (!is_file($xls_file)) {

	$page = HTTP::get('https://lcb.wa.gov/records/frequently-requested-lists');
	$body = $page['body'];
	$body = preg_replace('/\s+/ms', ' ', $body);
	/*
	<a href="https://lcb.wa.gov/sites/default/files/publications/Marijuana/sales_activity/2019-08-12-MJ-Sales-Activity-by-License-Number-Traceability-Contingency-Reporting.xlsx">Marijuana Sales Activity by License Number – Traceability Contingency Reporting</a>
	*/
	if (!preg_match('/ <a href="(http[^ ]+Sales\-Activity[^ ]+\.xlsx)">.+?Sales Activity.+?Traceability Contingency Reporting</', $body, $m)) {
		die("\nNo File!\n");
	}

	$xls_link = 'https://lcb.wa.gov/sites/default/files/publications/Marijuana/sales_activity/2020-02-05-MJ-Sales-Activity-by-License-Number-Traceability-Contingency-Reporting-Retail.xlsx';
	$res = HTTP::get($xls_link);
	if ($res['info']['http_code'] != 200) {
		print_r($res);
		echo "\nFailed to Source Visits\n\n";
		return(0);
	}

	file_put_contents($xls_file, $res['body']);
}

$dbc = _dbc();

$xls = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($xls_file);
$xls->setReadDataOnly(true);
$xls = $xls->load($xls_file);

$idx = 0;
foreach($xls->getSheetNames() as $n) {

	echo "Open Sheet: $n\n";

	switch ($n) {
	case 'Retailers':
	case 'Producers and Processors':
		break;
	default:
		throw new \Exception("Invalid Sheet Name: '$n'");
	}

	$wks = $xls->getSheetByName($n);
	$row_list = $wks->getRowIterator();
	$row_max = $wks->getHighestRow();
	$date_x = null;

	foreach ($row_list as $row) {

		$idx = $row->getRowIndex();

		$lic6 = trim($wks->getCell(sprintf('A%d', $idx))->getValue());

		$date = trim($wks->getCell(sprintf('B%d', $idx))->getValue());
		if (strlen($date)) {
			$date = strtotime($date);
			if ($date > 0) {
				$date = strftime('%Y-%m-%d', $date);
			}
		}

		if ($date != $date_x) {
			echo "Date: $date\n";
		}

		if (!preg_match('/\d{5,6}/', $lic6) && !preg_match('/^\d{4}/', $date)) {
			echo "Skip: $idx - $lic6\n";
			continue;
		}

		if (!empty($lic_skip[$lic6])) {
			continue;
		}

		$rev_sum = trim($wks->getCell(sprintf('C%d', $idx))->getValue());

		// Find Vendor
		$L = _find_license($lic6);
		if (empty($L['id'])) {
			echo "No License: $lic6\n";
			$lic_skip[$lic6] = $lic6;
			continue;
		}

		_revenue_record_insert($L, $date, 'lcb-v2', $rev_sum);

		$date_x = $date;

	}
}
