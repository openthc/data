<?php
/**
 * Chart of B2C Revenues
 */

$dbc = _dbc();

// FOIA Data Sales
$sql = <<<SQL
SELECT count(id) AS c
, date_trunc('week', created_at) AS w
, sum(full_price) AS rev
FROM b2c_sale
WHERE stat = 200
GROUP BY date_trunc('week', created_at)
ORDER BY w ASC
SQL;

$res = _select_via_cache($dbc, $sql, $arg);

$cht_data = [];
$cht_data[] = [
	[ 'label' => 'Date', 'type' => 'date' ],
	'Sales',
	'Revenue',
];

foreach ($res as $rec) {
	$t = strtotime($rec['w']);
	$d = sprintf("Date(%d)", $t * 1000); // Format for JS
	$cht_data[] = [ $d, floatval($rec['c']), floatval($rec['rev']) ];
}

// Append XLS Data Somehow?
// XLS Data Sales
// $sql = <<<SQL
// SELECT
// , license.id AS license_id
// , license.name AS license_name
// , license.code AS license_code
// , license.type AS license_type
// , license_revenue.month
// , sum(license_revenue.rev_amount) AS rev
// FROM license
// JOIN license_revenue ON license.id = license_revenue.license_id
// WHERE month = :m AND license.type = :lt
//  AND license_revenue.source IN ('lcb-v1', 'lcb-v2')
// GROUP BY license.id, license.name, license_revenue.month
// ORDER BY 1, license.code
// SQL;


?>

<section>
<div class="otd-chart" id="b2c-weekly-chart"></div>
<p>This chart is built from the LeafData information, so it may not match the LCB spreadsheet</p>
</section>

<script>
google.charts.load("current", {packages:["line"]});
google.charts.setOnLoadCallback(function() {

	var cht_data = google.visualization.arrayToDataTable(<?= json_encode($cht_data, JSON_NUMERIC_CHECK) ?>);
	var cht_opts = {
		chartArea:{
			left:0,
			top:0,
			width:'100%',
			height:'100%'
		},
		series: {
			0: { axis: 'Sales' },
			1: { axis: 'Revenue' }
		}
	};

	var chart = new google.charts.Line(document.getElementById('b2c-weekly-chart'));
	chart.draw(cht_data, cht_opts);

});
</script>
