<?php
/**
 * Lab Result Data
 */

use Edoceo\Radix\DB\SQL;

// $dt0 = '2019-01-01';
// $dt1 = '2019-12-31 23:59:59';

$d0 = new DateTime();
$d0->sub(new DateInterval('P7M'));

$d1 = clone $d0;
$d1->add(new DateInterval('P6M'));


/*
SELECT "license__via__license_id"."name" AS "name", count(*) AS "count"
FROM "public"."lab_report"
LEFT JOIN "public"."license" "license__via__license_id" ON "public"."lab_report"."license_id" = "license__via__license_id"."id"
WHERE (("public"."lab_report"."id" like 'WAL%')
   AND CAST("public"."lab_report"."created_at" AS date) > CAST('2019-01-01T00:00:00.000Z'::timestamp AS date) AND CAST("public"."lab_report"."created_at" AS date) < CAST('2019-07-01T00:00:00.000Z'::timestamp AS date))
GROUP BY "license__via__license_id"."name"
ORDER BY "license__via__license_id"."name" ASC
*/

$sql = <<<SQL
SELECT license.name AS lab_name
, count(lab_report.id) AS lab_report_count
FROM lab_report
JOIN license ON lab_report.license_id = license.id
JOIN lab_report_lot ON lab_report.id = lab_report_lot.lab_report_id
JOIN lot ON lab_report_lot.lot_id = lot.id
WHERE lab_report.created_at >= :dt0 AND lab_report.created_at <= :dt1
GROUP BY license.name
ORDER BY lab_report_count DESC
SQL;

$dbc = _dbc();

$arg = [
	':dt0' => $d0->format('Y-m-01'),
	':dt1' => $d1->format('Y-m-t'),
];

$res = _select_via_cache($dbc, $sql, $arg);

$cht_data = [];
$cht_data[] = [ 'License Name', 'Count' ];

foreach ($res as $rec) {
	$cht_data[] = [ $rec['lab_name'], $rec['lab_report_count'] ];
}

?>

<div class="mt-4">
	<h2 style="margin:0;">Lab Result Count :: 6 Month Sum</h2>
	<p>Market Share from <?= $d0->format('F 01, Y') ?> to <?= $d1->format('F t, Y') ?>.</p>
	<div class="otd-chart" id="license-lab-result-count-pie"></div>
</div>

<script type="text/javascript">
google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(function() {

	var cht_data = google.visualization.arrayToDataTable(<?= json_encode($cht_data) ?>);

	var cht_opts = {
		axisTitlesPosition: 'in',
		chartArea: {
			left: '2%',
			top: '2%',
			width: '84%',
			height: '92%',
		},
		// title: 'Product Sales',
		isStacked: 'percent',
		hAxis: null,
		vAxis: null,
		bar: { groupWidth: "100%"},
		// legend: { position: "none" },
	};

	var C = new google.visualization.PieChart(document.getElementById('license-lab-result-count-pie'));
	C.draw(cht_data, cht_opts);
});
</script>
