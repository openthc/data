<?php
/**
 *
 */

$max = 10;
$sql = <<<SQL
SELECT count(b2b_sale.id) AS c
, sum(full_price) AS rev
, license.id AS license_id
, license.name AS license_name
FROM b2b_sale
JOIN license ON b2b_sale.license_id_target = license.id
WHERE b2b_sale.license_id_source = :l0 AND b2b_sale.stat IN ('in-transit', 'ready-for-pickup', 'received')
AND execute_at >= :dt0
AND full_price > 0
GROUP BY license.id, license.name
ORDER BY 2 DESC
LIMIT $max
SQL;

$arg = [
	':l0' => $L['id'],
	':dt0' => '2019-01-01',
];


$res = _select_via_cache($dbc, $sql, $arg);
if (0 == count($res)) {
	return(0);
}

?>

<div>

<div class="d-flex">
	<div style="flex: 1 1 auto;"><h2>Top Clients</h2></div>
	<div style="flex: 1 0 auto; text-align:right;">
		<div class="btn-group btn-group-sm">
			<a class="btn btn-outline-secondary" href="/license/clients?id=<?= $L['id'] ?>"> view more <i class="fas fa-arrow-right"></i></a>
			<a class="btn btn-outline-secondary" href="/license/map?view=clients&amp;id=<?= $L['id'] ?>"><i class="fas fa-map"></i></a>
		</div>
	</div>
</div>

<p>Top <?= $max ?> clients, last ~6 months</p>

<table class="ui table">
<thead>
<tr>
	<th>Client</th>
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
