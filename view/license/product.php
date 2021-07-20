<?php
/**
 *
 *
 *
 */


$dbc = _dbc();

$L = $dbc->fetchRow('SELECT * FROM license WHERE id = ?', [ $_GET['id'] ]);
if (empty($L['id'])) {
	_exit_text('Invalid License', 400);
}

$_ENV['h1'] = sprintf('License :: <a href="/license/%s">%s</a> :: Products', $L['id'], h($L['name']));
$_ENV['title'] = sprintf('License :: %s :: Products', h($L['name']));

/*
CREATE TABLE report_b2b_product_sold AS
SELECT count(id) AS lot_count
, date_trunc('month', execute_at) AS execute_at
, sum(qty_tx) AS qty_tx
, sum(qty_rx) AS qty_rx
, sum(sale_item_full_price) AS full_price
, source_license_id
, product_name
FROM b2b_sale_item_full
WHERE sale_item_full_price > 0
GROUP BY date_trunc('month', execute_at), source_license_id, product_name
*/


$sql = <<<SQL
SELECT sum(lot_count) AS lot_count
, execute_at
, product_name
, sum(full_price) AS full_price
FROM report_b2b_product_sold
WHERE source_license_id = :l0
GROUP BY execute_at, product_name
ORDER BY execute_at, lot_count
SQL;
$res = _select_via_cache($dbc, $sql, [
	':l0' => $L['id']
]);

// Fold to Chart Data
$res_chart = [];
$product_name = [];
$product_full_price = [];
foreach ($res as $rec) {

	$cts = $rec['execute_at'];

	if (empty($res_chart[$cts])) {
		$res_chart[$cts] = [];
	}

	$res_chart[$cts][ $rec['product_name'] ] = $rec;

	$product_name[ $rec['product_name'] ] = $product_name[ $rec['product_name'] ] + $rec['lot_count'];
	$product_full_price[ $rec['product_name'] ] = $product_full_price[ $rec['product_name'] ] + $rec['full_price'];

}

arsort($product_name);

$product_list_25 = array_slice(array_keys($product_name), 0, 25);

echo '<table class="table table-sm">';
printf('<caption>Top %d Products by Sold Lot Count</caption>', count($product_list_25));
echo '<thead class="thead-dark"><tr><th>Product</th><th class="r">Lots</th><th class="r">Full Price</th></tr></thead>';
foreach ($product_list_25 as $p) {
	echo '<tr>';
	printf('<td><a href="/b2b/product?vendor=%s&amp;name=%s">%s</a></td><td class="r">%d</td>', $L['id'], rawurlencode($p), $p, $product_name[$p]);
	printf('<td class="r">%d</td>', $product_full_price[ $p ]);
	echo '</tr>';
}
echo '</table>';

?>


<hr>


<section>
<div class="chart-wrap">
<table class="charts-css column multiple stacked show-data-on-hover show-heading show-labels">
<caption>Dollars per Product Name per Month</caption>
<thead>
	<tr>
		<th scope="col">Month</th>
		<?php
		foreach ($product_list_25 as $v) {
			printf('<th scope="col">%s</th>', h($v));
		}
		?>
	</tr>
</thead>
<tbody>
<?php
foreach ($res_chart as $cts => $row) {

	$max = array_reduce($row, function($r, $v) {
		return $r + $v['lot_count'];
	}, 0);

	echo '<tr>';
	printf('<th scope="row">%s</th>', _date('m/Y', $cts));
	foreach ($product_list_25 as $k) {
		$v = $row[ $k ]['lot_count'];
		printf('<td style="--size: %0.6f; text-align: right;"><span class="data">%s</span><span class="tooltip">%s</span></td>'
			, $v / $max
			, $v
			, $k
		);
	}
	echo '</tr>';
}
?>
</tbody>
</table>
</div>
</section>
