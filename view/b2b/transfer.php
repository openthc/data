<?php
/**
 * Detail
 */

$_ENV['h1'] = 'B2B :: Details';

$show_void = intval($_GET['void']);

$dbc = _dbc();

$L_Vendor = $dbc->fetchRow('SELECT * FROM license WHERE id = ?', [ $_GET['vendor'] ]);
if (empty($L_Vendor['id'])) {
	_exit_text('Invalid Vendor License', 400);
}

$L_Target = $dbc->fetchRow('SELECT * FROM license WHERE id = ?', [ $_GET['client'] ]);
if (empty($L_Target['id'])) {
	_exit_text('Invalid Client License', 400);
}

$_ENV['h1'] = 'B2B :: ' . $L_Vendor['name'] . ' to ' . $L_Target['name'];
$_ENV['title'] = $_ENV['h1'];

?>

<div class="row">
	<div class="col-md-6">
		<h2>Vendor: <a href="/license/<?= $L_Vendor['id'] ?>"><?= h($L_Vendor['name']) ?></a>  <small><?= h($L_Vendor['code']) ?></small></h2>
	</div>
	<div class="col-md-6">
		<h2>Client: <a href="/license/<?= $L_Target['id'] ?>"><?= h($L_Target['name']) ?></a>  <small><?= h($L_Target['code']) ?></small></h2>
	</div>
</div>
<div>
<?= App\UI::b2b_transfer_tabs() ?>
</div>


<?php

$stat_filter = "AND stat IN ('open', 'in-transit', 'ready-for-pickup', 'received')";
if ($show_void) {
	$stat_filter = null;
}

// Summary
$sql = <<<SQL
SELECT count(id) AS c
, sum(qty_tx) AS qty_tx
, sum(qty_rx) AS qty_rx
, sum(sale_item_full_price) AS full_price
, product_name
FROM b2b_sale_item_full
WHERE source_license_id = :l0 AND target_license_id = :l1
 $stat_filter
 AND execute_at >= :dt0
 AND sale_item_full_price > 0
GROUP BY product_name
ORDER BY full_price DESC
LIMIT 500
SQL;
$res_b2b = _select_via_cache($dbc, $sql, [
	':dt0' => DATE_ALPHA,
	':l0' => $L_Vendor['id'],
	':l1' => $L_Target['id'],
]);


// Fetch the Retail Information
$res_b2c = [];
if ('R' == $L_Target['type']) {

	$sql = <<<SQL
SELECT sum(b2c_sale_item.qty) AS qty
, sum(b2c_sale_item.unit_price) AS unit_price
, min(b2c_sale_item.unit_price) AS unit_price_min
, max(b2c_sale_item.unit_price) AS unit_price_max
, meta->>'name' AS name
FROM b2c_sale_item WHERE lot_id IN (
	SELECT lot_id_target FROM b2b_sale_item_full
	WHERE source_license_id = :l0 AND target_license_id = :l1
	$stat_filter
	AND execute_at >= :dt0
)
 AND b2c_sale_item.stat = 200
GROUP BY meta->>'name'
SQL;

	$res = _select_via_cache($dbc, $sql, [
		':dt0' => DATE_ALPHA,
		':l0' => $L_Vendor['id'],
		':l1' => $L_Target['id'],
	]);
	// var_dump($res);
	foreach ($res as $rec) {
		$res_b2c[ $rec['name'] ] = $rec;
	}
}
?>


<?php
if ($show_void) {
?>
	<p>Transfers since <?= _date('F, Y', DATE_ALPHA) ?>, <strong class="text-danger">including VOID</strong> transactions.</p>
<?php
} else {
?>
	<p>Transfers since <?= _date('F, Y', DATE_ALPHA) ?>, <strong>excluding VOID</strong> transactions.</p>
<?php
}
?>

<table class="table table-sm">
<caption>Products Sold to this License and, if client side is Retail then the B2C sales of this product are included.</caption>
<thead class="thead-dark">
<tr>
	<th>Product</th>
	<th class="r">Sent</th>
	<th class="r">Received</th>
	<th class="r">Vendor &sum;$</th>
	<th class="r">Vendor $/U</th>
	<th class="r">Client $/#</th>
	<th class="r">Client $/U</th>
</thead>
<tbody>
<?php
foreach ($res_b2b as $rec) {

	if ($rec['qty_rx'] > 0) {
		$rec['unit_price'] = sprintf('%0.2f', $rec['full_price'] / $rec['qty_rx']);
	} else {
		$rec['unit_price'] = '-';
	}

?>
	<tr>
		<td><?= h($rec['product_name']) ?></td>
		<td class="r"><?= $rec['qty_tx'] ?></td>
		<td class="r"><?= $rec['qty_rx'] ?></td>
		<td class="r"><?= $rec['full_price'] ?></td>
		<td class="r"><?= $rec['unit_price'] ?></td>
		<?php
		if (!empty($res_b2c[ $rec['product_name'] ])) {
			$b2c = $res_b2c[ $rec['product_name'] ];
			echo sprintf('<td class="r">%0.2f / %d</td>', $b2c['unit_price'], $b2c['qty']);
			echo sprintf('<td class="r">%0.2f</td>', $b2c['unit_price'] / $b2c['qty']);
			unset($res_b2c[ $rec['product_name'] ]);
		}
		?>
	</tr>
<?php
}
?>
</tbody>
</table>
