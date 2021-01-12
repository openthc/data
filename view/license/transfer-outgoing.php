<?php
/**
 * Show Details where License is the Target
 */


$dbc = _dbc();

$sql = 'SELECT count(id) AS c FROM b2b_sale WHERE license_id_source = ?';
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
WHERE license_id_source = ? AND stat IN ('in-transit', 'ready-for-pickup', 'received')
 AND b2b_sale.execute_at >= now() - '6 months'::interval
GROUP BY date_trunc('month', execute_at)
ORDER BY 2 DESC
LIMIT 6
SQL;
// $res = _select_via_cache($dbc, $sql, [ $L['id'] ]);
// var_dump($res);
?>

<div>

<!--
<h2>Clients</h2>
<p>Recent clients transfer amounts and counts (<small><?= $max ?> all-time</small>)

<table class="ui table">
<thead>
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
-->
</div>

<?php
require_once(__DIR__ . '/transfer-outgoing-top.php');
