<?php
/**
 * License Detail
 */

$dbc = _dbc();

$L = $dbc->fetchRow('SELECT * FROM license WHERE id = :l', [ ':l' => $_GET['id'] ]);
if (empty($L['id'])) {
	_exit_text('Invalid License', 400);
}

$_ENV['h1'] = sprintf('License :: %s - %s', $L['name'], $L['code']);
$_ENV['title'] = $_ENV['h1'];

echo \App\UI::license_tabs($L);

// Revenue Chart
if ('*' == $_GET['stat']) {

	$sql = <<<SQL
SELECT date_trunc('month', execute_at) AS execute_at
, CASE
	 WHEN stat IN ('open', 'ready-for-pickup', 'in-transit', 'received') THEN 'Live'
	 ELSE 'VOID'
END AS stat
, sum(full_price) AS full_price
FROM b2b_sale
WHERE source_license_id = :l0
AND full_price > 0
GROUP BY 1, 2
ORDER BY 1, 2
SQL;

	$arg = [ ':l0' => $L['id'] ];
	// $res_source = $dbc->fetchAll($sql, $arg);
	$res_source = _select_via_cache($dbc, $sql, $arg);

	// Determine max value and add padding
	$max = array_reduce($res_source, function($prev, $item) {
		return max($item['full_price'], $prev);
	}, 0);
	$max = ($max * 1.20);

	// Collapse the various types of STAT values into the LIVE or VOID
	$res_output = [];
	$stat_list = [];
	foreach ($res_source as $rec) {

		if (empty($res_output[$rec['execute_at']])) {
			$res_output[$rec['execute_at']]	 = [];
		}

		$res_output[$rec['execute_at']][ $rec['stat'] ] = $rec['full_price'];

		$stat_list[ $rec['stat'] ] = true;

	}

	echo '<div style="border: 2px solid #333; height: 320px;">';
	echo '<table class="charts-css column multiple stacked show-data-on-hover show-heading show-labels">';
	echo '<caption>Monthly Revenue (All) <a href="?stat=">show-live-only</a></caption>';
	echo '<thead><tr><th scope="col">Date</th><th scope="col">VOID</th><th scope="col">Live</th></tr></thead>';
	echo '<tbody>';
	foreach ($res_output as $dts => $rec) {

		$live_v1 = $rec['Live'] / $max;
		$void_v1 = $rec['VOID'] / $max;

		echo '<tr>';
		printf('<th scope="row">%s</th>', _date('m/Y', $dts));
		printf('<td style="--start: %0.6f; --size: %0.6f"><span class="data" style="font-weight:700; z-index: 30">%s</span><span class="tooltip">Dollars in Live</span></td>'
			, $live_v0
			, $live_v1
			, number_format($rec['Live'], 2)
		);
		printf('<td style="--start: %0.6f; --size: %0.6f"><span class="data" style="font-weight:700; z-index: 30">%s</span><span class="tooltip">Dollars in VOID</span></td>'
			, $void_v0
			, $void_v1
			, number_format($rec['VOID'], 2)
		);
		echo '</tr>';

		$live_v0 = $live_v1;
		$void_v0 = $void_v1;

	}
	echo '</tbody>';
	echo '</table>';
	echo '</div>';

} else {

	$sql = <<<SQL
SELECT date_trunc('month', execute_at) AS execute_at
, sum(full_price) AS full_price
FROM b2b_sale
WHERE source_license_id = :l0
AND full_price > 0 AND stat IN ('open', 'ready-for-pickup', 'in-transit', 'received')
GROUP BY 1
ORDER BY 1
SQL;

	$arg = [ ':l0' => $L['id'] ];
	$res = _select_via_cache($dbc, $sql, $arg);

	$max = array_reduce($res, function($prev, $item) {
		return max($item['full_price'], $prev);
	}, 0);
	$max = ($max * 1.20);

	echo '<div class="chart-wrap">';
	echo '<table class="charts-css column multiple show-data-on-hover show-heading show-labels">';
	echo '<caption>Monthly Revenue (in-transit, received) <a href="?stat=*">show-all</a></caption>';

	$v0 = 0;
	if ($max) {
		$v0 = $res[0]['full_price'] / $max;
	}

	foreach ($res as $rec) {
		// <td style="--start: 0.0; --size: 0.4"> <span class="data"> $ 40K </span> </td>
		$v1 = $rec['full_price'] / $max;
		printf('<tr><th scope="row">%s</th><td style="--start: %0.6f; --size: %0.6f"><span class="data" style="font-weight:700; z-index: 30">%s</span></td></tr>', _date('m/Y', $rec['execute_at']), $v0, $v1, number_format($rec['full_price'], 2));
		$v0 = $v1;
	}
	echo '</table>';
	echo '</div>';
}


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
WHERE target_license_id = :l0 AND stat IN ('open', 'ready-for-pickup', 'in-transit', 'received')
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


<!-- <div class="container-fluid">
	<h2 style="margin:0;padding:0;">Expense &amp; Revenue</h2>
	<div id="chart-revenue" style="border 1px solid #333; height:240px; width:100%;"></div>
</div> -->

<hr>


<div class="row">
<div class="col-md-6">
<?php
require_once(__DIR__ . '/single-b2b-incoming.php');
?>
</div>
<div class="col-md-6">
<?php
require_once(__DIR__ . '/single-b2b-outgoing.php');
?>
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
