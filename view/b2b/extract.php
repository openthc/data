<?php
/**
 * Extracts
 */

$_ENV['title'] = 'B2B :: Wholesale :: Extract';
$_ENV['h1'] = $_ENV['title'];

echo \OpenTHC\Data\UI::b2b_tabs();


$dbc = _dbc();


// Stat Sum of All Dollars for All Deals
$sql = <<<SQL
SELECT sum(sale_item_full_price) AS c, product_type
FROM b2b_sale_item_full
WHERE
 product_type IN ('infused_cooking_medium', 'food_grade_solvent_concentrate', 'co2_concentrate', 'non-solvent_based_concentrate', 'hydrocarbon_concentrate', 'ethanol_concentrate')
GROUP BY 2
ORDER BY 1 DESC
SQL;
$res = _select_via_cache($dbc, $sql, null);
$max = array_reduce($res, function($r, $v) {
	return ($r + $v['c']);
}, 0);
?>
<section>
<h2>Total Dollars Per Category</h2>
<div class="chart-wrap" style="height: 64px;">
<table class="charts-css bar multiple stacked">
<tbody>
<tr>
<?php
foreach ($res as $rec) {
	printf('<td style="--size: %0.6f"><span class="tooltip">%s %s</span></td>'
		, $rec['c'] / $max
		, number_format($rec['c'])
		, $rec['product_type']
	);
}
?>
</tr>
</tbody>
</table>
</div>
</section>


<?php
/**
 * Data
 */
$sql = <<<SQL
SELECT count(id) AS lot_count
, date_trunc('month', execute_at) AS execute_at
, sum(qty_tx) AS qty_tx_sum
, sum(qty_rx) AS qty_rx_sum
, sum(sale_item_full_price) AS sale_item_full_price_sum
, product_type
FROM b2b_sale_item_full
WHERE
 product_type IN ('infused_cooking_medium', 'food_grade_solvent_concentrate', 'co2_concentrate', 'non-solvent_based_concentrate', 'hydrocarbon_concentrate', 'ethanol_concentrate')
GROUP BY 2, 6
ORDER BY 2
SQL;

$res = _select_via_cache($dbc, $sql, null);

$product_type_rank = [];
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

	$product_type_rank[$t] = intval($product_type_rank[$t]) + $rec['lot_count'];

}
// var_dump($product_type_rank);
arsort($product_type_rank);
// var_dump($product_type_rank);

$product_type_list = array_keys($product_type_rank);
// var_dump($product_type_list);


// foreach ($res_middle as $dts => $rec_middle) {

// 	// $rec = $rec_middle['flower'];

// 	$t = strtotime($dts);
// 	$d = sprintf("Date(%d)", $t * 1000); // Format for JS

// 	$row = [];
// 	$row_rev = [];
// 	$row_vol = [];

// 	$row[] = $d;
// 	$row_rev[] = $d;
// 	$row_vol[] = $d;

// 	foreach ($col_list as $k => $v) {
// 		$row[] = floatval($rec_middle[$k]['lot_count']);
// 		$row_rev[] = floatval($rec_middle[$k]['sale_item_full_price_sum']);
// 		$row_vol[] = floatval($rec_middle[$k]['qty_rx_sum']);
// 	}

// 	$cht_data[] = $row;
// 	$cht_data_rev[] = $row_rev;
// 	$cht_data_vol[] = $row_vol;

// }

?>

<hr>
<section>
<h2>Extract :: Lot Counts Sold per Month</h2>
<div class="chart-wrap">
<table class="charts-css column multiple stacked show-labels show-data-on-hover">
<thead>
<tr>
	<th>Date</th>
	<?php
	foreach ($product_type_list as $x) {
		printf('<th scope="col">%s</th>', h($x));
	}
	?>
</tr>
</thead>
<tbody>
<?php
foreach ($res_middle as $cts => $row) {

	$max = array_reduce($row, function($p, $v) {
		return $p + $v['lot_count'];
	}, 0);

	echo '<tr>';
	printf('<th scope="row">%s</th>', _date('m/y', $cts));
	foreach ($product_type_list as $x) {
		$v = $row[ $x ]['lot_count'] / $max;
		printf('<td style="--size: %0.6f;"><span class="data">%s</span><span class="tooltip">%s</span></td>'
			, $v
			, $row[ $x ]['lot_count']
			, $x
		);
	}
	echo '</tr>';
}
?>
</tbody>
</table>
</div>
</section>


<hr>
<section>
<h2>Extract :: Dollars Per Month</h2>
<div class="chart-wrap">
<table class="charts-css column multiple stacked show-labels">
<thead>
<tr>
	<th>Date</th>
	<?php
	foreach ($product_type_list as $x) {
		printf('<th scope="col">%s</th>', h($x));
	}
	?>
</tr>
</thead>
<tbody>
<?php
foreach ($res_middle as $cts => $row) {

	$max = array_reduce($row, function($p, $v) {
		return $p + $v['sale_item_full_price_sum'];
	}, 0);

	echo '<tr>';
	printf('<th scope="row">%s</th>', _date('m/y', $cts));
	foreach ($product_type_list as $x) {
		$v = $row[ $x ]['sale_item_full_price_sum'] / $max;
		printf('<td style="--size: %0.6f;"><span class="tooltip">$%s %s</span></td>'
			, $v
			, number_format($row[ $x ]['sale_item_full_price_sum'])
			, $x
		);
	}
	echo '</tr>';
}
?>
</tbody>
</table>
</div>
</section>

<hr>
<section>
<h2>Extract :: Dollars per Gram per Month</h2>
<div class="chart-wrap">
<table class="charts-css column multiple stacked show-labels">
<thead>
<tr>
	<th>Date</th>
	<?php
	foreach ($product_type_list as $x) {
		printf('<th scope="col">%s</th>', h($x));
	}
	?>
</tr>
</thead>
<tbody>
<?php
$row_prev = [];
foreach ($res_middle as $cts => $row) {

	$max = array_reduce($row, function($p, $v) {
		return $p + ($v['sale_item_full_price_sum'] / $v['qty_tx_sum']);
	}, 0);

	echo '<tr>';
	printf('<th scope="row">%s</th>', _date('m/y', $cts));
	foreach ($product_type_list as $x) {

		$row[$x]['qty'] = floatval($row[ $x ]['qty_tx_sum']);
		$row[$x]['ppg'] = 0;
		if ($row[$x]['qty']) {
			$row[$x]['ppg'] = $row[ $x ]['sale_item_full_price_sum'] / $row[$x]['qty'];
		}

		$row[$x]['size'] = $row[$x]['ppg'] / $max;
		printf('<td style="--start: %0.4f; --size: %0.6f;"><span class="tooltip">$%s %s</span></td>'
			, $row_prev[$x]['size']
			, $row[$x]['size']
			, number_format($row[$x]['ppg'], 2)
			, $x
		);
	}
	echo '</tr>';

	$row_prev = $row;
}
?>
</tbody>
</table>
</div>
</section>

<!-- <hr>
<div class="chart-wrap" id="chart-extract-lot-count"></div> -->
<!-- <hr>

<h2>Extract :: Weight / Volume</h2>
<div class="chart-wrap" id="chart-extract-vol"></div> -->

</div>
