<?php
/**
 * Show Details where License is the Target
 */


$dbc = _dbc();

$max = 10;

$sql = <<<SQL
SELECT count(b2b_sale.id) AS c, sum(full_price) AS rev
, license.id AS license_id
, license.name AS license_name
FROM b2b_sale
JOIN license ON b2b_sale.license_id_source = license.id
WHERE b2b_sale.license_id_target = :l AND b2b_sale.stat IN ('in-transit', 'ready-for-pickup', 'received')
AND execute_at >= now() - '7 months'::interval
GROUP BY license.id, license.name
ORDER BY 2 DESC
LIMIT $max
SQL;
$res = _select_via_cache($dbc, $sql, [ ':l' => $L['id'] ]);
if (count($res)) {
?>

<section>
	<div class="d-flex">
		<div style="flex: 1 1 auto;"><h2>B2B Incoming</h2></div>
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
</section>
<?php
}
