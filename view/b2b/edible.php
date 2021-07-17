<?php
/**
 * Edible
 */

$_ENV['title'] = 'B2B :: Wholesale :: Edible';
$_ENV['h1'] = $_ENV['title'];

echo \App\UI::b2b_tabs();

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
$max = array_reduce($res, function($r, $v) {
	return ($r + $v['c']);
}, 0);
?>
<section>
<h2>Dollars Per Category</h2>
<div class="chart-wrap" style="height: 64px;">
<table class="charts-css bar multiple stacked">
<tbody>
<tr>
<?php
foreach ($res as $rec) {
	printf('<td style="--size: %0.6f"><span class="tooltip">%s %s</span></td>', $rec['c'] / $max, number_format($rec['c']), $rec['product_type']);
}
?>
</tr>
</tbody>
</table>
</div>
</section>

<hr>

<section>
<h2>Dollars and Volume Per Type, Per Month</h2>
<?php
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

<h2>Extract :: Dollars</h2>
<div class="chart-wrap">
<?php

?>
</div>

<hr>

<h2>Extract :: Lot Counts</h2>
<div class="chart-wrap"></div>

<hr>

<h2>Extract :: Weight / Volume</h2>
<div class="chart-wrap"></div>
