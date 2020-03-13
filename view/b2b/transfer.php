<?php
/**
 * Detail
 */

$_ENV['title'] = 'B2B :: Details';

session_write_close();

if (!_acl($_SESSION['acl_subject'], 'transfer', 'view-full')) {
	_exit_html('Please <a href="/auth/open">sign-in</a> to view more details', 403);
}

$dbc = _dbc();

$L_Vendor = $dbc->fetchRow('SELECT * FROM license WHERE id = ?', [ $_GET['vendor'] ]);
if (empty($L_Vendor['id'])) {
	_exit_text('Invalid Vendor License', 400);
}

$L_Target = $dbc->fetchRow('SELECT * FROM license WHERE id = ?', [ $_GET['client'] ]);
if (empty($L_Target['id'])) {
	_exit_text('Invalid Client License', 400);
}

$_ENV['title'] = 'B2B :: Details :: ' . $L_Vendor['name'] . ' :: ' . $L_Target['name'];

// Summary
$sql = <<<SQL
SELECT count(id) AS c
, sum(qty_tx) AS qty
, sum(sale_item_full_price) AS full_price
, product_name
FROM b2b_sale_item_full
WHERE license_id_origin = :l0 AND license_id_target = :l1
 AND stat IN ('in-transit', 'received')
 AND execute_at >= now() - '12 months'::interval
 AND sale_item_full_price > 0
GROUP BY product_name
ORDER BY full_price DESC
LIMIT 500
SQL;
$res_b2b = _select_via_cache($dbc, $sql, [
	':l0' => $L_Vendor['id'],
	':l1' => $L_Target['id'],
]);
// var_dump($res);



// Fetch the Retail Information
$res_b2c = [];
$sql = <<<SQL
SELECT sum(b2c_sale_item.qty) AS qty
, sum(b2c_sale_item.unit_price) AS unit_price
, min(b2c_sale_item.unit_price) AS unit_price_min
, max(b2c_sale_item.unit_price) AS unit_price_max
, meta->>'name' AS name
FROM b2c_sale_item WHERE lot_id IN (
	SELECT lot_id_target FROM b2b_sale_item_full
	WHERE license_id_origin = :l0 AND license_id_target = :l1
	AND stat IN ('in-transit', 'received')
	AND execute_at >= now() - '12 months'::interval
)
 AND b2c_sale_item.stat = 200
GROUP BY meta->>'name'
SQL;

$res = _select_via_cache($dbc, $sql, [
	':l0' => $L_Vendor['id'],
	':l1' => $L_Target['id'],
]);
// var_dump($res);
foreach ($res as $rec) {
	$res_b2c[ $rec['name'] ] = $rec;
}

?>

<div class="container-fluid mt-2">
<div class="row">
	<div class="col-md-6">
		<h2>Vendor: <a href="/license?id=<?= $L_Vendor['id'] ?>"><?= h($L_Vendor['name']) ?></a>  <small><?= h($L_Vendor['code']) ?></small></h2>
	</div>
	<div class="col-md-6">
		<h2>Client: <a href="/license?id=<?= $L_Target['id'] ?>"><?= h($L_Target['name']) ?></a>  <small><?= h($L_Target['code']) ?></small></h2>
	</div>
</div>
<div class="row">
<div class="col-12">
<?= _b2b_transfer_tabs() ?>
</div>
</div>

</div>

<div class="container-fluid">
<div class="table-responsive">
<table class="table table-sm">
<thead class="thead-dark">
<tr>
	<th>Product</th>
	<th>Units</th>
	<th class="r">Revenue</th>
	<th class="r">B2B $/U</th>
	<th class="r">B2C $/#</th>
	<th class="r">B2C $/U</th>
</thead>
<tbody>
<?php
foreach ($res_b2b as $rec) {
	$rec['unit_price'] = $rec['full_price'] / $rec['qty'];
?>
	<tr>
		<td><?= h($rec['product_name']) ?></td>
		<td class="r"><?= $rec['qty'] ?></td>
		<td class="r"><?= $rec['full_price'] ?></td>
		<td class="r"><?= sprintf('%0.2f', $rec['unit_price']) ?></td>
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

</div>
</div>