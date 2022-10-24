<?php
/**
 * B2B Sales for Flower Things
 */

$_ENV['h1'] = $_ENV['title'] = 'B2B :: Wholesale :: Flower';

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
--  AND stat NOT IN
GROUP BY 2, 6
ORDER BY 2
SQL;

$res = _select_via_cache($dbc, $sql, null);


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
?>

<?= \OpenTHC\Data\UI::b2b_tabs(); ?>

<h2>Grade A :: Wholesale <small>flower / flower_lots</small></h2>
<p>Lot Counts, Sent and Received Quantities, Dollars per Month</p>
<div class="chart-wrap">
<table class="charts-css column multiple show-labels show-data-axes hide-data">
<thead>
<tr>
	<th>Date</th>
	<th scope="col">Lot</th>
	<th scope="col">TX</th>
	<th scope="col">RX</th>
	<th scope="col">$$</th>
</tr>
</thead>
<tbody>
<?php
$rec_prev = [];
$prev_0 = 0;
$prev_1 = 0;
$prev_2 = 0;
$prev_3 = 0;
foreach ($res_middle as $rec_middle) {

	// var_dump($rec_middle); exit;
	$curr_0 = $rec_middle['flower']['lot_count'] / 100000;
	$curr_1 = $rec_middle['flower']['qty_tx_sum'] / 1000000000;
	$curr_2 = $rec_middle['flower']['qty_rx_sum'] / 1000000000;
	$curr_3 = $rec_middle['flower']['sale_item_full_price_sum'] / 100000000;

	$tool0 = sprintf('%0.1f k Lots', $rec_middle['flower']['lot_count'] / 1000);
	$tool1 = sprintf('%0d kg Outgoing', $rec_middle['flower']['qty_tx_sum'] / 1000);
	$tool2 = sprintf('%0d kg Incoming', $rec_middle['flower']['qty_rx_sum'] / 1000);
	$tool3 = sprintf('%s USD', number_format($rec_middle['flower']['sale_item_full_price_sum']));

	echo '<tr>';
	printf('<th>%s</th>', _date('m/Y', $rec_middle['flower']['execute_at']));
	printf('<td style="--start:%0.8f; --size:%0.8f;"><span class="data">%d</span><span class="tooltip">%s</span></td>', $prev_0, $curr_0, $rec_middle['flower']['lot_count'], $tool0);
	printf('<td style="--start:%0.8f; --size:%0.8f;"><span class="data">%d</span><span class="tooltip">%s</span></td>', $prev_1, $curr_1, $rec_middle['flower']['qty_tx_sum'], $tool1);
	printf('<td style="--start:%0.8f; --size:%0.8f;"><span class="data">%d</span><span class="tooltip">%s</span></td>', $prev_2, $curr_2, $rec_middle['flower']['qty_rx_sum'], $tool2);
	printf('<td style="--start:%0.8f; --size:%0.8f;"><span class="data">%d</span><span class="tooltip">%s</span></td>', $prev_3, $curr_3, $rec_middle['flower']['sale_item_full_price_sum'], $tool3);
	echo '</tr>';

	$prev_0 = $curr_0;
	$prev_1 = $curr_1;
	$prev_2 = $curr_2;
	$prev_3 = $curr_3;

}
?>
</tbody>
</table>
</div>

<h2>Grade A :: Wholesale Averages</h2>
<div class="chart-wrap">
<table class="charts-css column multiple show-labels show-data-axes hide-data">
<thead>
	<tr>
		<th>Date</th>
		<th>Grams/Lot</th>
		<th>Dollars/Gram</th>
</tr>
</thead>
<tbody>
<?php
$prev0 = 0;
$prev1 = 0;
foreach ($res_middle as $rec_middle) {

	$rec = $rec_middle['flower'];

	$data0 = $rec['qty_rx_sum'] / $rec['lot_count'];
	$data1 = $rec['sale_item_full_price_sum'] / $rec['qty_rx_sum'];

	$size0 = $data0 / 10000;
	$size1 = $data1 / 10;

	$tool0 = sprintf('%0.2f Grams per Lot', $data0);
	$tool1 = sprintf('%0.3f Dollars per Gram', $data1);

	echo '<tr>';
	printf('<th>%s</th>', _date('m/y', $rec_middle['flower']['execute_at']));
	printf('<td style="--start:%0.8f; --size:%0.8f;"><span class="data">%f</span><span class="tooltip">%s</span></td>', $prev0, $size0, $data0, $tool0);
	printf('<td style="--start:%0.8f; --size:%0.8f;"><span class="data">%f</span><span class="tooltip">%s</span></td>', $prev1, $size1, $data1, $tool1);
	echo '</tr>';

	$prev0 = $size0;
	$prev1 = $size1;

}
?>
</tbody>
</table>
</div>


<hr>


<h2>Grade B :: Wholesale <small>other_material / other_material_lots / marijuana_mix</h2>
<div class="chart-wrap">
<table class="charts-css column multiple show-labels show-data-axes hide-data">
<thead>
	<tr>
		<th>Date</th>
		<th>Grams/Lot</th>
		<th>Dollars/Gram</th>
</tr>
</thead>
<tbody>
<?php
$prev_0 = 0;
$prev_1 = 0;
$prev_2 = 0;
$prev_3 = 0;
foreach ($res_middle as $rec_middle) {

	$rec = $rec_middle['other_material'];

	// var_dump($rec_middle); exit;
	$curr_0 = $rec['lot_count'] / 100000 * 2;
	$curr_1 = $rec['qty_tx_sum'] / 1000000000 * 2;
	$curr_2 = $rec['qty_rx_sum'] / 1000000000 * 2;
	$curr_3 = $rec['sale_item_full_price_sum'] / 100000000 * 2;

	$tool0 = sprintf('%0.1f k Lots', $rec['lot_count'] / 1000);
	$tool1 = sprintf('%0.1f Mg Outgoing', $rec['qty_tx_sum'] / 1000000);
	$tool2 = sprintf('%0.1f Mg Incoming', $rec['qty_rx_sum'] / 1000000);
	$tool3 = sprintf('%s USD', number_format($rec['sale_item_full_price_sum']));

	echo '<tr>';
	printf('<th>%s</th>', _date('m/y', $rec['execute_at']));
	printf('<td style="--start:%0.8f; --size:%0.8f;"><span class="data">%d</span><span class="tooltip">%s</span></td>', $prev_0, $curr_0, $rec['lot_count'], $tool0);
	printf('<td style="--start:%0.8f; --size:%0.8f;"><span class="data">%d</span><span class="tooltip">%s</span></td>', $prev_1, $curr_1, $rec['qty_tx_sum'], $tool1);
	printf('<td style="--start:%0.8f; --size:%0.8f;"><span class="data">%d</span><span class="tooltip">%s</span></td>', $prev_2, $curr_2, $rec['qty_rx_sum'], $tool2);
	printf('<td style="--start:%0.8f; --size:%0.8f;"><span class="data">%d</span><span class="tooltip">%s</span></td>', $prev_3, $curr_3, $rec['sale_item_full_price_sum'], $tool3);
	echo '</tr>';

	$prev_0 = $curr_0;
	$prev_1 = $curr_1;
	$prev_2 = $curr_2;
	$prev_3 = $curr_3;

}

?>
</tbody>
</table>
</div>

<hr>

<section>
<?php

$max = array_reduce($res_middle, function($r, $v) {
	$v = $v['other_material'];
	if ($v['lot_count']) {
		$r['gpl'] = max($r['gpl'], $v['qty_tx_sum'] / $v['lot_count']);
	}
	if ($v['qty_tx_sum']) {
		$r['ppg'] = max($r['ppg'], $v['sale_item_full_price_sum'] / $v['qty_tx_sum']);
	}
	return $r;
}, [ 'gpl' => 0, 'ppg' => 0 ]);
var_dump($max);

$rec_prev = [];
?>
<h2>Grade B :: Wholesale Averages</h2>
<div class="chart-wrap">
<table class="charts-css column multiple show-labels show-data-axes hide-data">
<thead>
	<tr>
		<th>Date</th>
		<th>Grams/Lot</th>
		<th>Dollars/Gram</th>
</tr>
</thead>
<tbody>
<?php
foreach ($res_middle as $rec_middle) {

	$rec = $rec_middle['other_material'];

	$data0 = $rec['qty_tx_sum'] / $rec['lot_count'];
	$data1 = $rec['sale_item_full_price_sum'] / $rec['qty_tx_sum'];

	$size0 = $data0 / $max['gpl'];
	$size1 = $data1 / $max['ppg'];

	$tool0 = sprintf('%0.2f Grams per Lot', $data0);
	$tool1 = sprintf('%0.3f Dollars per Gram', $data1);

	echo '<tr>';
	printf('<th>%s</th>', _date('m/y', $rec_middle['flower']['execute_at']));
	printf('<td style="--start:%0.8f; --size:%0.8f;"><span class="data">%f</span><span class="tooltip">%s</span></td>', $prev0, $size0, $data0, $tool0);
	printf('<td style="--start:%0.8f; --size:%0.8f;"><span class="data">%f</span><span class="tooltip">%s</span></td>', $prev1, $size1, $data1, $tool1);
	echo '</tr>';

	$prev0 = $size0;
	$prev1 = $size1;

}
?>
</tbody>
</table>
</div>
</section>
