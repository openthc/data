<?php
/**
 * Lab Result Stats
 */

$_ENV['h1'] = $_ENV['title'] = 'Lab Results :: Attested vs Proper';

echo \OpenTHC\Data\UI::lab_tabs();

$dbc = _dbc();

$arg = null;
$sql = null;

$res_data = [];

// Find AT4s by Month
$sql = <<<SQL
SELECT count(id) AS c
, date_trunc('month', created_at) AS mon
FROM lot_lab_result_retail_cache
WHERE lab_result_id LIKE 'WAATTEST%'
 AND length(lab_result_id) = 16
GROUP BY 2
ORDER BY 2
SQL;
$res = _select_via_cache($dbc, $sql, $arg);
foreach ($res as $rec) {
	$res_data[ $rec['mon'] ]['at4'] = $rec['c'];
}

// Find AT5s by Month
$sql = <<<SQL
SELECT count(id) AS c
, date_trunc('month', created_at) AS mon
FROM lot_lab_result_retail_cache
WHERE meta->>'lab_result_id' LIKE 'WAATTEST%'
 AND length(meta->>'lab_result_id') = 17
GROUP BY 2
ORDER BY 2
SQL;
$res = _select_via_cache($dbc, $sql, $arg);
foreach ($res as $rec) {
	$res_data[ $rec['mon'] ]['at5'] = $rec['c'];
}

$sql = <<<SQL
SELECT count(id) AS c
, date_trunc('month', created_at) AS mon
FROM lot_lab_result_retail_cache
WHERE meta->>'lab_result_id' NOT LIKE 'WAATTEST%'
GROUP BY 2
ORDER BY 2
SQL;
$res = _select_via_cache($dbc, $sql, $arg);
foreach ($res as $rec) {
	$res_data[ $rec['mon'] ]['lr1'] = $rec['c'];
}

$out_chart = [];
$out_table = [];

foreach ($res_data as $dts => $rec) {

	$out_chart[] = _chart_row($dts, $rec);
	$out_table[] = sprintf('<tr><td>%s</td><td class="r">%d</td><td class="r">%d</td><td class="r">%d</td></tr>'
		, _date('m/Y', $dts)
		, $rec['lr1']
		, $rec['at4']
		, $rec['at5']
	);

}

echo '<div class="chart-wrap" style="height: 360px;">';
echo '<table class="charts-css column multiple stacked data-spacing-2 show-data-on-hover show-labels">';
echo '<thead><tr><th>Date</th><th>Lab</th><th>AT4</th><th>AT5</th></tr></thead>';
echo '<tbody>';
echo implode("\n", $out_chart);
echo '</tbody>';
echo '</table>';
echo '</div>';

echo '<table class="table table-sm">';
echo '<thead class="thead-dark"><tr><th>Month</th><th class="r">Proper</th><th class="r">AT4</th><th class="r">AT5</th></tr></thead>';
echo '<tbody>';
echo implode("\n", $out_table);
echo '</tbody>';
echo '</table>';

// _exit_text($res_data);

function _chart_row($dts, $rec)
{

	$max = array_sum($rec);

	$ret = [];
	$ret[] = '<tr>';
	$ret[] = sprintf('<th scope="row">%s</th>', _date('m/Y', $dts));
	$ret[] = sprintf('<td style="--size: %0.6f"><span class="data">%d</span><span class="tooltip">Proper Lab Result</span></td>', $rec['lr1'] / $max, $rec['lr1']);
	$ret[] = sprintf('<td style="--size: %0.6f"><span class="data">%d</span><span class="tooltip">Attested-4</span></td>', $rec['at4'] / $max, $rec['at4']);
	$ret[] = sprintf('<td style="--size: %0.6f"><span class="data">%d</span><span class="tooltip">Attested-5</span></td>', $rec['at5'] / $max, $rec['at5']);
	$ret[] = '</tr>';

	return implode('', $ret);

}
