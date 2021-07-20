<?php
/**
 * Stacked Area of Market Share
 */


$dbc = _dbc();

// Lab Market Share
$sql = <<<SQL
SELECT date_trunc('month', CAST("public"."lab_result"."created_at" AS timestamp)) AS "created_at"
, "license__via__license_id"."name" AS "license_name"
, count(*) AS "count"
FROM "public"."lab_result"
LEFT JOIN "public"."license" "license__via__license_id" ON "public"."lab_result"."license_id" = "license__via__license_id"."id"
WHERE ("public"."lab_result"."id" like 'WAL%')
AND lab_result.created_at >= :dt0 AND lab_result.created_at <= :dt1
GROUP BY date_trunc('month', CAST("public"."lab_result"."created_at" AS timestamp)), "license__via__license_id"."name"
ORDER BY date_trunc('month', CAST("public"."lab_result"."created_at" AS timestamp)) ASC, "license__via__license_id"."name" ASC
SQL;

$arg = [
	':dt0' => DATE_ALPHA,
	':dt1' => DATE_OMEGA
];

$res_source = _select_via_cache($dbc, $sql, $arg);

$license_list = [];
$license_rank = [];
$res_output = [];
foreach ($res_source as $src) {
	if (empty($res_output[ $src['created_at'] ])) {
		$res_output[ $src['created_at'] ] = [];
	}
	$res_output[ $src['created_at'] ][ $src['license_name'] ] = $src['count'];
	$license_list[ $src['license_name'] ] = true;
	 // track their most recent counts only
	$license_rank[ $src['license_name'] ] = ($license_rank[ $src['license_name'] ] + $src['count']) / 3;
	// $license_rank[ $src['license_name'] ] = 0; // $src['count'];
}
arsort($license_rank);
$license_list = array_keys($license_rank);

ob_start();
?>

<table>
<caption>Lab Results, By Month, By Lab</caption>
<thead>
	<tr>
		<th scope="col">Month</th>
		<?php
		foreach ($license_rank as $k => $v) {
			printf('<th scope="col">%s</th>', h($k));
		}
		?>
	</tr>
</thead>
<tbody>
<?php
foreach ($res_output as $cts => $row) {

	$max = array_sum($row);

	echo '<tr>';
	printf('<th scope="row">%s</th>', _date('m/Y', $cts));
	foreach ($license_rank as $l => $r) {
		$v = $row[ $l ];
		printf('<td style="--size: %0.6f; text-align: right;"><span class="data">%s</span><span class="tooltip">%s</span></td>', $v / $max, $v, $l);
	}
	echo '</tr>';
}

?>
</tbody>
</table>
<?php
$html = ob_get_clean();

echo '<div style="border: 2px solid #333; height: 420px;">';
// echo '<ul class="charts-css legend legend-square" style="margin: 0; padding: 0;">';
// foreach ($license_rank as $l => $r) {
// 	printf('<li>%s</li>', h($l));
// }
// echo '</ul>';
echo str_replace('<table>', '<table class="charts-css column multiple stacked show-data-on-hover show-heading show-labels">', $html);
echo '</div>';

$html = str_replace('<table>', '<table class="table table-sm table-hover table-striped">', $html);
$html = str_replace('<caption>Lab Results, By Month, By Lab</caption>', '', $html);
// echo '<div class="table-responsive">';
echo $html;
// echo '</div>';/
