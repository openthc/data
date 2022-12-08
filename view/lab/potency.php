<?php
/**
 * Laboratory Potency Details by Month
 */

session_write_close();

$_ENV['h1'] = $_ENV['title'] = 'Lab Results :: Potency';

$dbc = _dbc();

$sql = <<<SQL
SELECT license_id
, date_trunc('month', created_at) AS Month
, count(id) AS c
, avg(thc) AS thc_avg
, avg(cbd) AS cbd_avg
FROM lab_report
WHERE created_at >= '2019-01-01'
 AND id LIKE 'WAL%'
GROUP BY 1, 2
ORDER BY 1, 2
SQL;

$res = _select_via_cache($dbc, $sql, null);

if ('csv' == $_GET['o']) {
	$csv_spec = [
		'license_id' => 'License ID',
		'month' => 'Month',
		'c' => 'Result Count',
		'thc_avg' => 'THC Average',
		'cbd_avg' => 'CBD Average',
	];
	_res_to_csv($res, $csv_spec, 'Lab_Report_Potency.csv');
}

echo \OpenTHC\Data\UI::lab_tabs();
echo '<p><a href="?o=csv">Download CSV</p>';

_res_to_table($res);
