<?php
/**
 * License Detail
 */

$_ENV['h1'] = null;
$_ENV['title'] = 'License';

$dbc = _dbc();

$L = $dbc->fetchRow('SELECT * FROM license WHERE id = :l', [ ':l' => $_GET['id'] ]);
if (empty($L['id'])) {
	_exit_text('Invalid License', 400);
}

$_ENV['title'] = sprintf('License :: %s - %s', $L['code'], $L['name']);

/*
select distinct stat from b2b_sale;

open
VOID-in-transit
VOID-ready-for-pickup
in-transit
received
VOID-received
VOID-open
ready-for-pickup
*/

// Expense History
$sql = <<<SQL
SELECT date_trunc('month', execute_at) AS mon
, sum(full_price) AS exp
, 0 AS tax
FROM b2b_sale
WHERE license_id_target = :l0 AND stat IN ('received')
AND execute_at >= :dt0
GROUP BY date_trunc('month', execute_at)
ORDER BY 1 DESC
SQL;

$arg = [
	':l0' => $L['id'],
	':dt0' => DATE_ALPHA,
];

$res_expense = _select_via_cache($dbc, $sql, $arg);
// var_dump($res_expense);

// Revenue History
$sql = <<<SQL
SELECT date_trunc('month', month) AS mon
, sum(rev_amount) AS rev
, sum(tax_amount) AS tax
, source
FROM license_revenue
WHERE license_id = :l0
AND month >= :dt0
GROUP BY date_trunc('month', month), source
ORDER BY 1 DESC
SQL;

$arg = [
	':l0' => $L['id'],
	':dt0' => DATE_ALPHA,
];

$res_revenue = _select_via_cache($dbc, $sql, $arg);

// Now do the same query from the b2b_sale table
$cht_data = [];
$cht_data[] = [
	[ 'label' => 'Date', 'type' => 'date' ],
	'Revenue LCB',
	'Revenue FOIA',
	'FOIA VOID',
	// 'Expense',
];

// $res_exp_rev = [];
// foreach ($res_expense as $exp) {
// 	$res_exp_rev[ $exp['mon'] ] = [
// 		'mon' => $exp['mon'],
// 		'expense' => $exp['exp'],
// 		'revenue' => 0,
// 	];
// }
$res_middle = [];
foreach ($res_revenue as $rev) {

	if (empty($res_middle[ $rev['mon'] ])) {
		$res_middle[ $rev['mon'] ] = [
			'mon' => $rev['mon'],
			'lcb1' => 0,
			'lcb2' => 0,
			'foia' => 0,
			'void' => 0,
			// 4 => 0,
			// 5 => 0,
		];
	}

	switch ($rev['source']) {
	case 'lcb-v1':
		$res_middle[ $rev['mon'] ]['lcb1'] += $rev['rev'];
		break;
	case 'lcb-v2':
		$res_middle[ $rev['mon'] ]['lcb2'] += $rev['rev'];
		break;
	case 'foia-real':
		$res_middle[ $rev['mon'] ]['foia'] += $rev['rev'];
		break;
	case 'foia-void':
		$res_middle[ $rev['mon'] ]['void'] += $rev['rev'];
		break;
	}

	// if (empty($res_exp_rev[ $rev['mon'] ])) {
	// 	$res_exp_rev[ $rev['mon'] ] = [
	// 		'mon' => $rev['mon'],
	// 		'expense' => 0,
	// 		'revenue' => 0,
	// 	];
	// }

	// $res_exp_rev[ $rev['mon'] ]['revenue'] = $rev['rev'];

}

foreach ($res_middle as $rec) {

	$t = strtotime($rec['mon']);
	$d = sprintf("Date(%d)", $t * 1000); // Format for JS
	$cht_data[] = [
		$d,
		$rec['lcb2'],
		$rec['foia'],
		$rec['void'],
		// $rec['expense']
	];
}

?>

<div class="container-fluid mt-2">
<div class="row">
	<div class="col-md-6">
		<?= \App\UI::license_info($L) ?>
	</div>
</div>
<?= \App\UI::license_tabs($L) ?>
</div>

<!-- <div class="container-fluid">
	<h2 style="margin:0;padding:0;">Expense &amp; Revenue</h2>
	<div id="chart-revenue" style="border 1px solid #333; height:240px; width:100%;"></div>
</div> -->

<div class="container-fluid">
<div class="row">
<div class="col-md-6">
<?php
require_once(__DIR__ . '/b2b-incoming.php');
?>
</div>
<div class="col-md-6">
<?php
require_once(__DIR__ . '/b2b-outgoing.php');
?>
</div>
</div>
</div>

<!--
<div class="container-fluid">
<div class="row">
<div class="col-md-6">
	<?php
	// require_once(__DIR__ . '/index-product-incoming.php');
	?>
</div>
<div class="col-md-6">
	<?php
	// require_once(__DIR__ . '/index-product-outgoing.php');
	?>
</div>
</div>
</div>
-->


<script>
$(function() {
	google.charts.load("current", {packages:[ 'corechart', 'line' ]});
	google.charts.setOnLoadCallback(function() {
		var data = google.visualization.arrayToDataTable(<?= json_encode($cht_data, JSON_NUMERIC_CHECK) ?>);
		var div = document.getElementById('chart-revenue');
		var cht_opts = {
			axisTitlesPosition: 'none',
			// chartArea: {
			// 	top: 8,
			// 	right: 8,
			// 	bottom: 32,
			// 	left: 8
			// },
			fontName: 'sans-serif',
			fontSize: '22px',
			// legend: {
			// 	position: 'none',
			// },
			lineWidth: 4,
			vAxis: {
				textPosition: 'in',
			}
		};
		var C = new google.visualization.LineChart(div);
		// cht_opts = google.charts.Line.convertOptions(cht_opts)
		// var C = new google.charts.Line(div);
		C.draw(data, cht_opts);
	});
});
</script>
