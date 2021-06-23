<?php
/**
 * Show Details where License is the Target
 */


$dbc = _dbc();

$sql = 'SELECT count(id) AS c FROM b2b_sale WHERE source_license_id = :l';
$res = _select_via_cache($dbc, $sql, [ ':l' => $L['id'] ]);
if (empty($res)) {
	return(null);
}
if (empty($res[0]['c'])) {
	return(null);
}

$full_count = $res[0]['c'];

$res = [];

$sql = <<<SQL
SELECT count(id) AS c
, date_trunc('month', execute_at) AS mon
, sum(full_price) AS rev
FROM b2b_sale
WHERE source_license_id = ? AND stat IN ('in-transit', 'ready-for-pickup', 'received')
 AND b2b_sale.execute_at >= now() - '6 months'::interval
GROUP BY date_trunc('month', execute_at)
ORDER BY 2 DESC
LIMIT 6
SQL;
$res = _select_via_cache($dbc, $sql, [ $L['id'] ]);
// var_dump($res);
?>

<section>
<div class="d-flex">
	<div style="flex: 1 1 auto;"><h2>B2B Outgoing</h2></div>
	<div style="flex: 1 0 auto; text-align:right;">
		<div class="btn-group btn-group-sm">
			<a class="btn btn-outline-secondary" href="/license/clients?id=<?= $L['id'] ?>"> view more <i class="fas fa-arrow-right"></i></a>
			<a class="btn btn-outline-secondary" href="/license/map?view=clients&amp;id=<?= $L['id'] ?>"><i class="fas fa-map"></i></a>
		</div>
	</div>
</div>

<p>Recent clients transfer amounts and counts (<small><?= $full_count ?> all-time</small>)

<table class="table table-sm">
<thead class="thead-dark">
<tr>
	<th>Month</th>
	<th class="r">Transfers</th>
	<th class="r">Revenues</th>
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
</section>

<?php
// require_once(__DIR__ . '/transfer-outgoing-top.php');
