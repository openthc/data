<?php
/**
 * Show Outgoing Supplier List
 */

$_ENV['title'] = 'License :: Top Clients';

session_write_close();

if (!_acl($_SESSION['acl_subject'], 'license', 'view-clients')) {
	_exit_html('Please <a href="/auth/open">sign-in</a> to view more details', 403);
}

$show_void = intval($_GET['void']);

$dbc = _dbc();

$L = $dbc->fetchRow('SELECT * FROM license WHERE id = ?', [ $_GET['id'] ]);
if (empty($L['id'])) {
	_exit_text('Invalid License', 400);
}

?>

<div class="container-fluid">
<div class="row">
	<div class="col-md-6">
		<?= _license_info($L) ?>
	</div>
	<div class="col-md-6 r">
		<a class="btn btn-outline-secondary" href="/license/map?id=<?= $L['id'] ?>&amp;license=vendor"><i class="fas fa-map"></i></a>
	</div>
</div>
<?= _menu_license_tabs($L) ?>
</div>

<div class="container-fluid">
<?php
require_once(__DIR__ . '/clients-chart-stacked-column.php');
?>
</div>

<?php

$max = 100;

$stat_filter = "AND b2b_sale.stat IN ('in-transit', 'ready-for-pickup', 'received')";
if ($show_void) {
	$stat_filter = null;
}

$sql = <<<SQL
SELECT count(b2b_sale.id) AS c, sum(full_price) AS rev
, license.id AS license_id
, license.code AS license_code
, license.name AS license_name
FROM b2b_sale
JOIN license ON b2b_sale.license_id_target = license.id
WHERE b2b_sale.license_id_source = ?
 $stat_filter
AND execute_at >= now() - '12 months'::interval
AND full_price > 0
GROUP BY license.id, license.name
ORDER BY 2 DESC
LIMIT $max
SQL;
$arg = [ $L['id'] ];
$res = _select_via_cache($dbc, $sql, $arg);
?>

<div class="container">
<?php
if ($show_void) {
?>
	<p>Top <?= $max ?> clients, last 12 months -- including DONE and VOID type transactions</p>
<?php
} else {
?>
	<p>Top <?= $max ?> clients, last 12 months -- DONE transactions only <a href="?<?= http_build_query(array_merge($_GET, ['all' => true ])) ?>">show all</a></p>
<?php
}
?>

<table class="table table-sm">
<thead class="thead-dark">
<tr>
	<th>Client</th>
	<th class="r">Purchases</th>
	<th class="r">Revenue</th>
	<th></th>
</tr>
</thead>
<?php
foreach ($res as $rec) {
?>
	<tr>
		<td><a href="/license?id=<?= $rec['license_id'] ?>"><?= $rec['license_name'] ?></a> <small><?= $rec['license_code'] ?></small></td>
		<td class="r"><?= $rec['c'] ?></td>
		<td class="r"><?= $rec['rev'] ?></td>
		<td class="r"><a href="/b2b/transfer?client=<?= $rec['license_id'] ?>&amp;vendor=<?= $L['id'] ?>"><i class="fas fa-exchange-alt"></i></a></td>
	</tr>
<?php
}
?>
</table>
</div>
