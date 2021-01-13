<?php
/**
 * Show Details where License is the Target
 */


$dbc = _dbc();

$sql = 'SELECT count(id) AS c FROM b2b_sale WHERE license_id_target = ?';
$res = _select_via_cache($dbc, $sql, [ $L['id'] ]);
if (empty($res)) {
	return(null);
}
if (empty($res[0]['c'])) {
	return(null);
}

$max = $res[0]['c'];

$res = [];
// var_dump($res);
$sql = <<<SQL
SELECT count(id) AS c
, date_trunc('month', execute_at) AS mon
, sum(full_price) AS rev
FROM b2b_sale
WHERE license_id_target = ? AND stat IN ('received')
 AND b2b_sale.execute_at >= now() - '6 months'::interval
GROUP BY date_trunc('month', execute_at)
ORDER BY 2 DESC
LIMIT 6
SQL;
// $res = _select_via_cache($dbc, $sql, [ $L['id'] ])
// var_dump($res);
?>

<div>

<!--
<h2>Vendors</h2>
<p>Recent incoming transfer amounts and counts (<small><?= $max ?> all-time</small>)</p>

<table class="ui table">
<thead>
<tr>
	<th>Month</th>
	<th class="r">Transfers</th>
	<th class="r">Expense</th>
</tr>
</thead>
<?php
foreach ($res as $rec) {
?>
	<tr>
		<td><?= _date('m/Y', $rec['mon']) ?></td>
		<td class="r"><?= $rec['c'] ?></td>
		<td class="r"><?= $rec['rev'] ?></td>
	</tr>
<?php
}
?>
</table>
-->
</div>


<?php
$max = 10;
$sql = <<<SQL
SELECT count(b2b_sale.id) AS c, sum(full_price) AS rev
, license.id AS license_id
, license.name AS license_name
FROM b2b_sale
JOIN license ON b2b_sale.license_id_source = license.id
WHERE b2b_sale.license_id_target = ? AND b2b_sale.stat IN ('in-transit', 'ready-for-pickup', 'received')
AND execute_at >= now() - '7 months'::interval
GROUP BY license.id, license.name
ORDER BY 2 DESC
LIMIT $max
SQL;
$res = _select_via_cache($dbc, $sql, [ $L['id'] ]);
if (count($res)) {
?>

	<div>
	<div class="d-flex">
		<div style="flex: 1 1 auto;"><h2>Top Vendors</h2></div>
		<div style="flex: 1 0 auto; text-align:right;">
			<div class="btn-group btn-group-sm">
				<a class="btn btn-outline-secondary" href="/license/vendors?id=<?= $L['id'] ?>"> view more <i class="fas fa-arrow-right"></i></a>
				<a class="btn btn-outline-secondary" href="/license/map?view=vendors&amp;id=<?= $L['id'] ?>"><i class="fas fa-map"></i></a>
			</div>
		</div>
	</div>
	<p>Top <?= $max ?> suppliers, last ~6 months</p>

	<table class="ui table">
	<thead>
	<tr>
		<th>Vendor</th>
		<th class="r">Purchases</th>
		<th class="r">Expense</th>
	</tr>
	</thead>
	<?php
	foreach ($res as $rec) {
	?>
		<tr>
			<td><a href="/license?id=<?= $rec['license_id'] ?>"><?= $rec['license_name'] ?></a></td>
			<td class="r"><?= $rec['c'] ?></td>
			<td class="r"><?= $rec['rev'] ?></td>
		</tr>
	<?php
	}
	?>
	</table>
	</div>

<?php
}
