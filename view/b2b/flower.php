<?php
/**
 *
 */

$_ENV['title'] = 'B2B :: Wholesale :: Flower';

session_write_close();

$dbc = _dbc();

$sql = <<<SQL
SELECT count(id) AS lot_count
, date_trunc('month', execute_at) AS execute_at
, sum(qty_tx) AS qty_tx_sum
, sum(qty_rx) AS qty_rx_sum
, sum(sale_item_full_price) AS sale_item_full_price_sum
, product_type
FROM b2b_sale_item_full
WHERE product_type IN ('flower', 'flower_lots', 'other_material', 'other_material_lots', 'marijuana_mix')
GROUP BY 2, 6
ORDER BY 2
SQL;

$res = _select_via_cache($dbc, $sql, null);
// _res_to_table($res);
// var_dump($res);

$res_middle = [];
foreach ($res as $rec) {

	$d = $rec['execute_at'];
	$t = $rec['product_type'];
	$t = str_replace('_lots', '', $t);
	$t = str_replace('marijuana_mix', 'other_material', $t);

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

}
// var_dump($res_middle);
// _res_to_table($res_middle);


$cht_data_buds = [];
$cht_data_buds[] = [
	[ 'label' => 'Date', 'type' => 'date' ],
	'Lots',
	'Grams TX',
	'Grams RX',
	'Revenue',
];

$cht_data_buds_avg = [];
$cht_data_buds_avg[] = [
	[ 'label' => 'Date', 'type' => 'date' ],
	'Grams / Lot',
	'Dollars / Gram',
];

$cht_data_grade_b_raw = [];
$cht_data_grade_b_raw[] = [
	[ 'label' => 'Date', 'type' => 'date' ],
	'Lots',
	'Grams TX',
	'Grams RX',
	'Revenue',
];

$cht_data_grade_b_avg = [];
$cht_data_grade_b_avg[] = [
	[ 'label' => 'Date', 'type' => 'date' ],
	'Grams / Lot',
	'Dollars / Gram',
];


foreach ($res_middle as $rec_middle) {

	$rec = $rec_middle['flower'];

	$t = strtotime($rec['execute_at']);
	$d = sprintf("Date(%d)", $t * 1000); // Format for JS

	$cht_data_buds[] = [
		$d,
		$rec['lot_count'],
		floatval($rec['qty_tx_sum']),
		floatval($rec['qty_rx_sum']),
		floatval($rec['sale_item_full_price_sum']),
	];

	$cht_data_buds_avg[] = [
		$d,
		floatval($rec['qty_rx_sum'] / $rec['lot_count']),
		floatval($rec['sale_item_full_price_sum'] / $rec['qty_rx_sum'])
	];


	$rec = $rec_middle['other_material'];

	// $t = strtotime($rec['execute_at']);
	// $d = sprintf("Date(%d)", $t * 1000); // Format for JS
	$cht_data_grade_b_raw[] = [
		$d,
		$rec['lot_count'],
		floatval($rec['qty_tx_sum']),
		floatval($rec['qty_rx_sum']),
		floatval($rec['sale_item_full_price_sum']),
	];

	if ($rec['lot_count'] > 0) {
		$cht_data_grade_b_avg[] = [
			$d,
			floatval($rec['qty_rx_sum'] / $rec['lot_count']),
			floatval($rec['sale_item_full_price_sum'] / $rec['qty_rx_sum'])
		];
	} else {
		$cht_data_grade_b_avg[] = [ $d, 0, 0 ];
	}

}

?>

<div class="container-fluid">
<h1><?= $_ENV['title'] ?></h1>
<?php
_b2b_tabs();
?>

<h2>Grade A :: Wholesale <small>flower / flower_lots</small></h2>
<div id="chart-flower" style="height:360px; width:100%;"></div>

<hr>

<h2>Grade A :: Wholesale Averages</h2>
<div id="chart-flower-avg" style="height:360px; width:100%;"></div>

<hr>

<h2>Grade B :: Wholesale <small>other_material / other_material_lots / marijuana_mix</h2>
<div id="chart-grade-b-raw" style="height:360px; width:100%;"></div>

<hr>

<h2>Grade B :: Wholesale Averages</h2>
<div id="chart-grade-b-avg" style="height:360px; width:100%;"></div>

</div>

<script>
$(function() {
google.charts.load("current", {packages:[ 'corechart', 'line' ]});
google.charts.setOnLoadCallback(function() {

	var cht_opts = {
		axisTitlesPosition: 'none',
		chartArea: {
			top: 16,
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
		}
	};

	var data = google.visualization.arrayToDataTable(<?= json_encode($cht_data_buds, JSON_NUMERIC_CHECK) ?>);
	var div = document.getElementById('chart-flower');
	var C = new google.visualization.LineChart(div);
	// cht_opts = google.charts.Line.convertOptions(cht_opts)
	// var C = new google.charts.Line(div);
	C.draw(data, cht_opts);

	// Second Chart
	var avg_node = document.getElementById('chart-flower-avg');
	var avg_data = google.visualization.arrayToDataTable(<?= json_encode($cht_data_buds_avg, JSON_NUMERIC_CHECK) ?>);
	cht_opts.series = {
		0: {
			targetAxisIndex: 1
		}
	};
	var C1 = new google.visualization.LineChart(avg_node);
	C1.draw(avg_data, cht_opts);

	// Third Chart
	var grade_b_raw_node = document.getElementById('chart-grade-b-raw');
	var grade_b_raw_data = google.visualization.arrayToDataTable(<?= json_encode($cht_data_grade_b_raw, JSON_NUMERIC_CHECK) ?>);
	var C2 = new google.visualization.LineChart(grade_b_raw_node);
	C2.draw(grade_b_raw_data, cht_opts);

	var grade_b_avg_node = document.getElementById('chart-grade-b-avg');
	var grade_b_avg_data = google.visualization.arrayToDataTable(<?= json_encode($cht_data_grade_b_avg, JSON_NUMERIC_CHECK) ?>);
	var C3 = new google.visualization.LineChart(grade_b_avg_node);
	C3.draw(grade_b_avg_data, cht_opts);

});
});
</script>
