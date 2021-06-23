<?php
/**
 * Show Incoming Supplier List
 */

$_ENV['title'] = 'License :: Top Vendors';

$dbc = _dbc();

$L = $dbc->fetchRow('SELECT * FROM license WHERE id = ?', [ $_GET['id'] ]);
if (empty($L['id'])) {
	_exit_text('Invalid License', 400);
}

?>

<div class="container-fluid mt-2">
<?= App\UI::license_info($L) ?>
<?= App\UI::license_tabs($L) ?>
</div>

<div class="container-fluid">
<?php
require_once(__DIR__ . '/vendors-chart-stacked-column.php');
?>
</div>

<?php

$max = 100;

$sql = <<<SQL
SELECT count(b2b_sale.id) AS c, sum(full_price) AS rev
, license.id AS license_id
, license.code AS license_code
, license.name AS license_name
FROM b2b_sale
JOIN license ON b2b_sale.source_license_id = license.id
WHERE b2b_sale.target_license_id = ? AND b2b_sale.stat IN ('in-transit', 'ready-for-pickup', 'received')
AND execute_at >= now() - '12 months'::interval
AND full_price > 0
GROUP BY license.id, license.name
ORDER BY 2 DESC
LIMIT $max
SQL;
$arg = [ $L['id'] ];
// $res = $dbc->fetchAll($sql, $arg);
$res = _select_via_cache($dbc, $sql, $arg);
// var_dump($res); exit;

?>

<div class="container">

<div class="d-flex">
	<div style="flex: 1 1 auto;"><h2>Top Vendors</h2></div>
</div>

<p>Top <?= $max ?> vendors, last 12 months</p>

<table class="table table-sm">
<thead class="thead-dark">
<tr>
	<th>Vendor</th>
	<th class="r">Purchases</th>
	<th class="r">Expense</th>
	<th></th>
</tr>
</thead>
<?php
foreach ($res as $rec) {
?>
	<tr>
		<td><a href="/license/<?= $rec['license_id'] ?>"><?= $rec['license_name'] ?></a> <small><?= $rec['license_code'] ?></small></td>
		<td class="r"><?= $rec['c'] ?></td>
		<td class="r"><?= $rec['rev'] ?></td>
		<td class="r"><a href="/b2b/transfer?client=<?= $L['id'] ?>&amp;vendor=<?= $rec['license_id'] ?>"><i class="fas fa-retweet"></i></a></td>
	</tr>
<?php
}
?>
</table>
</div>
