<?php
/**
 * Edible
 */

$_ENV['title'] = 'B2B :: Wholesale :: Edible';

$dbc = _dbc();

// Stat
$sql = <<<SQL
SELECT sum(sale_item_full_price) AS c, product_type
FROM b2b_sale_item_full
WHERE product_type IN ('solid_edible', 'liquid_edible')
GROUP BY 2
ORDER BY 1 DESC
SQL;
$res = _select_via_cache($dbc, $sql, null);
// var_dump($res);


// Data
$sql = <<<SQL
SELECT count(id) AS lot_count
, date_trunc('month', execute_at) AS execute_at
, sum(qty_tx) AS qty_tx_sum
, sum(qty_rx) AS qty_rx_sum
, sum(sale_item_full_price) AS sale_item_full_price_sum
, product_type
FROM b2b_sale_item_full
WHERE product_type IN ('solid_edible', 'liquid_edible')
GROUP BY 2, 6
ORDER BY 2
SQL;

$res = _select_via_cache($dbc, $sql, null);
// _res_to_table($res);
// exit;

$res_middle = [];
foreach ($res as $rec) {

	$d = $rec['execute_at'];
	$t = $rec['product_type'];

	if (empty($res_middle[$d])) {
		$res_middle[$d] = [];
	}

	if (empty($res_middle[$d][$t])) {
		$res_middle[$d][$t] = $rec;
	} else {
		$res_middle[$d][$t]['lot_count'] += $rec['lot_count'];
		$res_middle[$d][$t]['qty_tx_sum'] += $rec['qty_tx_sum'];
		$res_middle[$d][$t]['qty_rx_sum'] += $rec['qty_rx_sum'];
		$res_middle[$d][$t]['sale_item_full_price_sum'] += $rec['sale_item_full_price_sum'];
	}

	$col_list[$t] = $t;

}
// var_dump($res_middle);
// _res_to_table($res_middle);
// exit;


$cht_data = [];
$cht_data[] = [
	[ 'label' => 'Date', 'type' => 'date' ],
];

$cht_data_rev = [];
$cht_data_rev[] = [
	[ 'label' => 'Date', 'type' => 'date' ],
];

$cht_data_vol = [];
$cht_data_vol[] = [
	[ 'label' => 'Date', 'type' => 'date' ],
];


foreach ($col_list as $k => $v) {
	$cht_data[0][] = $k;
	$cht_data_rev[0][] = $k;
	$cht_data_vol[0][] = $k;
}

foreach ($res_middle as $dts => $rec_middle) {

	// $rec = $rec_middle['flower'];

	$t = strtotime($dts);
	$d = sprintf("Date(%d)", $t * 1000); // Format for JS

	$row = [];
	$row_rev = [];
	$row_vol = [];

	$row[] = $d;
	$row_rev[] = $d;
	$row_vol[] = $d;

	foreach ($col_list as $k => $v) {
		$row[] = floatval($rec_middle[$k]['lot_count']);
		$row_rev[] = floatval($rec_middle[$k]['sale_item_full_price_sum']);
		$row_vol[] = floatval($rec_middle[$k]['qty_rx_sum']);
	}

	$cht_data[] = $row;
	$cht_data_rev[] = $row_rev;
	$cht_data_vol[] = $row_vol;

}

?>

<style>
.chart-page h2 {
	background: #777;
	color: #fff;
	margin: 0;
	padding: 0.125em 0.25em;
}
.chart-fill {
	background: #eee;
	border: 1px solid #333;
}
</style>

<div class="container-fluid chart-page">

<h1><?= $_ENV['title'] ?></h1>

<?= \App\UI::b2b_tabs(); ?>

<h2>Extract :: Dollars</h2>
<div class="chart-fill" id="chart-extract-rev" style="height:360px; width:100%;"></div>

<hr>

<h2>Extract :: Lot Counts</h2>
<div class="chart-fill" id="chart-extract-lot-count" style="height:360px; width:100%;"></div>

<hr>

<h2>Extract :: Weight / Volume</h2>
<div class="chart-fill" id="chart-extract-vol" style="height:360px; width:100%;"></div>

</div>

<script>
$(function() {
google.charts.load("current", {packages:[ 'corechart', 'line' ]});
google.charts.setOnLoadCallback(function() {

	var cht_opts = {
		axisTitlesPosition: 'none',
		chartArea: {
			top: 8,
			right: 8,
			bottom: 32,
			left: 8
		},
		// fontName: 'sans-serif',
		fontSize: '22px',
		legend: {
			position: 'none',
		},
		lineWidth: 4,
		series: {
			0: {
				axis: 'Lots',
				targetAxisIndex: 1,
			},
			// 1: {
			// 	axis: 'Lots',
			// 	targetAxisIndex: 0,
			// },
		},
		vAxis: {
			textPosition: 'in',
		},
		// bar: { groupWidth: "100%"},
		isStacked: 'percent',
	};

	var node_lot = document.getElementById('chart-extract-lot-count');
	var data_lot = google.visualization.arrayToDataTable(<?= json_encode($cht_data, JSON_NUMERIC_CHECK) ?>);
	var C = new google.visualization.ColumnChart(node_lot);
	C.draw(data_lot, cht_opts);


	// Second Chart
	var avg_node = document.getElementById('chart-extract-rev');
	var avg_data = google.visualization.arrayToDataTable(<?= json_encode($cht_data_rev, JSON_NUMERIC_CHECK) ?>);
	cht_opts.series = {
		0: {
			targetAxisIndex: 1
		}
	};
	var C1 = new google.visualization.LineChart(avg_node);
	C1.draw(avg_data, cht_opts);

	// Second Chart
	var node_vol = document.getElementById('chart-extract-vol');
	var data_vol = google.visualization.arrayToDataTable(<?= json_encode($cht_data_vol, JSON_NUMERIC_CHECK) ?>);
	cht_opts.series = {
		0: {
			targetAxisIndex: 1
		}
	};
	var C2 = new google.visualization.LineChart(node_vol);
	C2.draw(data_vol, cht_opts);

});
});
</script>
