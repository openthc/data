<?php
/**
 * Stacked Area of Client Share
 */

$dbc = _dbc();

$sql = <<<SQL
SELECT count(b2b_sale.id) AS c
, date_trunc('month', execute_at) AS created_at
, sum(full_price) AS full_price_sum
, license.id AS license_id
, license.name AS license_name
FROM b2b_sale
JOIN license ON b2b_sale.target_license_id = license.id
WHERE b2b_sale.source_license_id = ? AND b2b_sale.stat IN ('open', 'ready-for-pickup', 'in-transit', 'received')
-- AND execute_at >= now() - '12 months'::interval
AND full_price > 0
GROUP BY date_trunc('month', execute_at), license.id, license.name
ORDER BY 2, 3
SQL;
$arg = [ $L['id'] ];
$res = _select_via_cache($dbc, $sql, $arg);
if (empty($res)) {
	return(0);
}

$cht_data = [];
$license_list = [];
$license_rank = [];
foreach ($res as $rec) {
	$license_rank[ $rec['license_name'] ] = $license_rank[ $rec['license_name'] ] + $rec['full_price_sum'];
	$cht_data[ $rec['created_at'] ][ $rec['license_name'] ] = $rec['full_price_sum'];
}
arsort($license_rank);
$license_list = array_keys($license_rank);
$license_list = array_slice($license_list, 0, floor(count($license_list) * 0.50));
// $license_list[] = 'Other';

?>

<section>
<h2>Dollars per Client per Month</h2>
<p>Only shows the top 25 clients, then groups the rest as <em>Other</em>.</p>
<div class="chart-wrap">
<table class="charts-css column multiple stacked show-labels hide-data">
<thead>
	<tr>
		<th>Date</th>
		<?php
		foreach ($license_list as $l) {
			printf('<th>%s</th>', $l);
		}
		?>
		<th>Other</th>
	</tr>
</thead>
<tbody>
<?php
foreach ($cht_data as $cts => $rec) {

	$max = array_sum($rec);
	$used_pct = 0;
	$used_sum = 0;

	echo '<tr>';
	printf('<th>%s</th>', _date('m/y', $cts));
	foreach ($license_list as $l) {
		$pct = $rec[$l] / $max;
		printf('<td style="--size:%0.8f;"><span class="tooltip">$%s (%d%%) with %s</span></td>'
			, $pct
			, number_format($rec[$l])
			, $pct * 100
			, $l
		);

		$used_pct += $pct;
		$used_sum += $rec[$l];
	}

	printf('<td style="--size:%0.8f;"><span class="tooltip">$%s (%d%%) with %s</span></td>'
		, 1 - $used_pct
		, $max - $used_sum
		, (1 - $used_pct) * 100
		, 'Other'
	);

	echo '</tr>';
}
?>
</tbody>
</table>
</div>
</section>
