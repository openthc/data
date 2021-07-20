<?php
/**
 * Shows Line Item Details between Source and Target
 */

$_ENV['title'] = 'B2B Detail :: Product';

$dbc = _dbc();

$L_Vendor = $dbc->fetchRow('SELECT * FROM license WHERE id = ?', [ $_GET['vendor'] ]);
if (empty($L_Vendor['id'])) {
	_exit_text('Invalid License', 400);
}

$L_Client = $dbc->fetchRow('SELECT * FROM license WHERE id = ?', [ $_GET['client'] ]);
if (empty($L_Client['id'])) {
	_exit_text('Invalid License', 400);
}

$_ENV['h1'] = 'B2B :: ' . $L_Vendor['name'] . ' to ' . $L_Client['name'] . ' :: Details';
$_ENV['title'] = $_ENV['h1'];


// Chart Data
$sql = <<<SQL
SELECT date_trunc('month', execute_at) AS mon
, sum(qty_tx) AS qty
, sum(sale_item_full_price) AS full_price
, product_name
FROM b2b_sale_item_full
WHERE execute_at >= :dt0
 AND source_license_id = :l0 AND target_license_id = :l1
 AND stat NOT LIKE 'VOID%'
 AND qty_rx > 0
GROUP BY date_trunc('month', execute_at), product_name
SQL;

$arg = [
	':dt0' => DATE_ALPHA,
	':l0' => $L_Vendor['id'],
	':l1' => $L_Client['id'],
];

// $res = $dbc->fetchAll($sql, $arg);
// $res = _select_via_cache($dbc, $sql, $arg);

// $cols = [];
// $data_tmp = [];

// foreach ($res as $rec) {

// 	// Columns are Product Names
// 	if (!in_array($rec['product_name'], $cols)) {
// 		$cols[] = $rec['product_name'];
// 	}

// 	$t = strtotime($rec['mon']);
// 	$d = sprintf("Date(%d)", $t * 1000); // Format for JS

// 	$data_tmp[$d][ $rec['product_name'] ] = $rec;

// }

// var_dump($cols);
// var_dump($data_tmp);

// var_dump($cht_data);
// foreach ($data_tmp as $d => $p_list) {
// 	$row = [];
// 	$row[] = $d;
// 	foreach ($cols as $c) {
// 		$row[] = floatval($p_list[$c]['qty']);
// 	}
// 	$cht_data[] = $row;
// }

// var_dump($cht_data);


?>

<div class="row">
	<div class="col-md-6">
		<h2>Vendor: <a href="/license/<?= $L_Vendor['id'] ?>"><?= h($L_Vendor['name']) ?></a>  <small><?= h($L_Vendor['code']) ?></small></h2>
	</div>
	<div class="col-md-6">
		<h2>Client: <a href="/license/<?= $L_Client['id'] ?>"><?= h($L_Client['name']) ?></a>  <small><?= h($L_Client['code']) ?></small></h2>
	</div>
</div>
<div>
<?= App\UI::b2b_transfer_tabs() ?>
</div>


<?php
$sql = <<<SQL
SELECT *
FROM b2b_sale_item_full
WHERE source_license_id = :l0 AND target_license_id = :l1
--  AND qty_rx > 0
 AND execute_at >= :dt0
ORDER BY execute_at DESC, id
LIMIT 10000
SQL;
$res = _select_via_cache($dbc, $sql, [
	':dt0' => DATE_ALPHA,
	':l0' => $L_Vendor['id'],
	':l1' => $L_Client['id'],
]);

if ('text/csv' == $_GET['t']) {
	$csv_spec = [
		'execute_at' => 'Date',
		'id' => 'B2B Sale ID',
		'stat' => 'Status',
		'product_name' => 'Product',
		'qty_tx' => 'Shipped Qty',
		'qty_rx' => 'Received Qty',
		// 'unit_price' => 'Unit Price',
		'sale_item_full_price' => 'Full Price',
	];
	_res_to_csv($res, $csv_spec, 'b2b-sale-item.csv');
}

?>

<a href="?<?= http_build_query(array_merge($_GET, [ 't' => 'text/csv' ])) ?>"><i class="fas fa-download"></i></a>

<table class="table table-sm">
<thead class="thead-dark">
<tr>
	<th>Date</th>
	<th>Transfer</th>
	<th>Lot / Product</th>
	<th class="r">Shipped</th>
	<th class="r">Received</th>
	<th class="r">$/Unit</th>
	<th class="r">$ Full</th>
</tr>
</thead>
<tbody>
<?php
foreach ($res as $rec) {

	$guid = preg_replace('/^WA\w+\./', null, $rec['id']);
	if (preg_match('/VOID/', $rec['stat'])) {
		$guid = sprintf('<strong class="text-danger" title="VOID Transaction">%s</strong>', $guid);
	}

	$u_price = 0;
	if ($rec['qty_rx'] > 0) {
		$u_price = number_format($rec['sale_item_full_price'] / $rec['qty_rx'], 2);
	} elseif ($rec['qty_tx'] > 0) {
		$u_price = number_format($rec['sale_item_full_price'] / $rec['qty_tx'], 2);
		$u_price = sprintf('<span class="text-secondary">%s</span>', $u_price);
	}
	$f_price = number_format($rec['sale_item_full_price'], 2);
?>
	<tr>
		<td title="<?= h($rec['execute_at']) ?>"><?= _date('Y-m-d', $rec['execute_at']) ?></td>
		<td><?= $guid ?></td>
		<td>
			<!-- <?= preg_replace('/^WA\w+\./', null, $rec['lot_id_source']) ?> -->
			<a href="/b2b/product?vendor=<?= $L_Vendor['id'] ?>&amp;name=<?= rawurlencode($rec['product_name']) ?>"><?= h($rec['product_name']) ?></a>
		</td>
		<td class="r"><?= $rec['qty_tx'] ?></td>
		<td class="r"><?= $rec['qty_rx'] ?></td>
		<td class="r"><?= $u_price ?></td>
		<td class="r"><?= $f_price ?></td>
	</tr>
<?php
}
?>
</tbody>
</table>

</div>
