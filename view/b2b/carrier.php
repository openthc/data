<?php
/**
 * Carrier Report
 */

// in-transit
// received

$dbc = _dbc();

if ('rebuild' == $_GET['a']) {

	// $res = $dbc->query('TRUNCATE TABLE b2b_sale_carrier');

	$sql = <<<SQL
INSERT INTO b2b_sale_carrier
 SELECT id, source_license_id, target_license_id, execute_at, meta->>'transporting_mme_id' AS carrier_id
 FROM b2b_sale
 WHERE stat IN ('open', 'ready-for-pickup', 'in-transit', 'received')
SQL;
	$res = $dbc->query($sql);

	$sql = <<<SQL
DELETE FROM b2b_sale_carrier WHERE carrier_id = ''
SQL;
	$dbc->query($sql);

	_exit_text('done');

}


$sql = <<<SQL
SELECT count(b2b_sale_carrier.id) AS b2b_sale_count
, date_trunc('month', b2b_sale_carrier.execute_at) AS execute_at
, b2b_sale_carrier.carrier_id
, COALESCE(license.name, 'C#' || b2b_sale_carrier.carrier_id) AS carrier_name
FROM b2b_sale_carrier
LEFT JOIN license ON b2b_sale_carrier.carrier_id = license.id
-- WHERE license.type = 'Z'
GROUP BY date_trunc('month', execute_at), carrier_id, carrier_name
HAVING count(b2b_sale_carrier.id) > 50
SQL;

$arg = [];
// $res_source = $dbc->fetchAll($sql);
$res_source = _select_via_cache($dbc, $sql, $arg);

//
$carrier_list = [];
$carrier_rank = [];
$res_output = [];

foreach ($res_source as $src) {

	if (empty($res_output[ $src['execute_at'] ])) {
		$res_output[ $src['execute_at'] ] = [];
	}

	$res_output[ $src['execute_at'] ][ $src['carrier_name'] ] = $src['b2b_sale_count'];
	$carrier_rank[ $src['carrier_name'] ] = ($license_rank[ $src['carrier_name'] ] + $src['b2b_sale_count']);

}
arsort($carrier_rank);
$carrier_list = array_keys($carrier_rank);

?>


<section>
<h2>Carrier Market Share</h2>
<div class="chart-wrap">
<table class="charts-css column multiple stacked show-data-on-hover show-heading show-labels">
<thead>
	<tr>
		<th scope="col">Month</th>
		<?php
		foreach ($carrier_list as $x) {
			printf('<th scope="col">%s</th>', h($x));
		}
		?>
	</tr>
</thead>
<tbody>
<?php
foreach ($res_output as $cts => $rec) {

	$max = array_sum($rec);

?>
	<tr>
		<th scope="row"><?= _date('m/Y', $cts) ?></th>
		<?php
		foreach ($carrier_list as $x) {
			$v = $rec[$x];
			$s = ($max ? $v / $max : 0);
			printf('<td style="--size: %0.8f"><span class="tooltip">%d %s</span></td>', $s, $v, $x);
		}
		?>
	</tr>
<?php
}
?>
</tbody>
</div>
</section>
