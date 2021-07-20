<?php
/**
 * Stacked Area of Vendor Share
 */

$dbc = _dbc();

$sql = <<<SQL
SELECT count(b2b_sale.id) AS c
, date_trunc('month', execute_at) AS created_at
, sum(full_price) AS full_price_sum
, license.id AS license_id
, license.name AS license_name
FROM b2b_sale
JOIN license ON b2b_sale.source_license_id = license.id
WHERE b2b_sale.target_license_id = ? AND b2b_sale.stat IN ('in-transit', 'ready-for-pickup', 'received')
AND full_price > 0
GROUP BY date_trunc('month', execute_at), license.id, license.name
ORDER BY 2, 3
SQL;
$arg = [ $L['id'] ];
// var_dump($arg);
$res = _select_via_cache($dbc, $sql, $arg);
// var_dump($res);
// _res_to_table($res);
$cht_data = [];
$license_list = [];
$license_rank = [];
foreach ($res as $rec) {
	$license_rank[ $rec['license_name'] ] = $license_rank[ $rec['license_name'] ] + $rec['full_price_sum'];
	$cht_data[ $rec['created_at'] ][ $rec['license_name'] ] = $rec['full_price_sum'];
}
arsort($license_rank);
$license_list = array_keys($license_rank);

?>

<section>
<h2>Dollars per Vendor per Month</h2>
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
	</tr>
</thead>
<tbody>
<?php
foreach ($cht_data as $cts => $rec) {

	$max = array_sum($rec);

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
	}
	echo '</tr>';
}
?>
</tbody>
</table>
</div>
</section>
