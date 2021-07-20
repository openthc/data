<?php
/**
 * Show License Activity Per Quarter
 */

$_ENV['title'] = 'License :: Activity';

$res_activity = [];
$l_list = [];
$q_list = [];

$dbc = _dbc();
$res_license = $dbc->fetchAll('SELECT * FROM license');
// var_dump($res_license);
foreach ($res_license as $l) {

	$l['api'] = [];
	$l['xls'] = [];

	unset($l['address_meta']);
	unset($l['company_id']);
	unset($l['tsp']);
	unset($l['lat']);
	unset($l['lon']);

	$res_activity[ $l['id'] ] = $l;

}
// _exit_text($res_activity);


// Sales Activity, By Quarter, from FOIA data
$sql = <<<SQL
SELECT source_license_id AS l
, count(id) AS c
, sum(full_price) AS r
, date_trunc('quarter', execute_at) AS q
FROM b2b_sale
WHERE execute_at > '2018-01-01'
GROUP BY l, q
SQL;

$res_b2b_sale = $dbc->fetchAll($sql);
foreach ($res_b2b_sale as $x) {

	$x['c'] = intval($x['c']);
	$x['r'] = floatval($x['r']);
	$x['q'] = _date('Y-m-d', $x['q']);

	// _exit_text($x);
	$l = $x['l'];
	$q = $x['q'];

	if (empty($res_activity[$l])) {
		$res_activity[$l] = [
			'id' => $l,
			'code' => '-',
			'name' => '-api-unknown-',
			'type' => '-',
			'api' => [],
			'xls' => [],
		];
	}

	$res_activity[$l]['api'][$q] = $x;

	$q_list[$q] = true;

}
// _exit_text($res_activity);


// XLS Based Revenue (suspect data)
$res_revenue = [];
$sql = <<<SQL
SELECT license_id AS l
, count(id) AS c
, sum(rev_amount) AS r
, date_trunc('quarter', month) AS q
FROM license_revenue
WHERE month > '2018-01-01'
GROUP BY l, q
SQL;
// $res_revenue = $dbc->fetchAll($sql);
foreach ($res_revenue as $x) {

	$x['c'] = intval($x['c']);
	$x['r'] = floatval($x['r']);
	$x['q'] = _date('Y-m-d', $x['q']);

	// _exit_text($x);
	$l = $x['l'];
	$q = $x['q'];

	if (empty($res_activity[$l])) {
		$res_activity[$l] = [
			'id' => $l,
			'code' => '-',
			'name' => '-xls-unknown-',
			'type' => '-',
			'api' => [],
			'xls' => [],
		];
	}

	$res_activity[$l]['xls'][$q] = $x;

	$q_list[$q] = true;

}

$l_list = array_keys($res_activity);
sort($l_list);
$q_list = array_keys($q_list);
sort($q_list);
// var_dump($q_list);
// echo strlen(json_encode($res_activity));

if ('csv' == $_GET['o']) {

	// Have to Re-Fold this Data First

	// Head
	$csv_spec = [
		'code' => 'Code',
		'name' => 'Name',
	];
	foreach ($q_list as $q) {
		$csv_spec["c$q"] = "Count $q";
		$csv_spec["r$q"] = "Revnue $q";
	}

	$csv_data = [];
	foreach ($res_activity as $l) {

		$row = [];
		$row['code'] = $l['code'];
		$row['name'] = $l['name'];
		foreach ($q_list as $q) {
			$row["c$q"] = floatval($l['api'][$q]['c']);
			$row["r$q"] = floatval($l['api'][$q]['r']);
		}

		$csv_data[] = $row;
	}

	_res_to_csv($csv_data, $csv_spec, 'License_Activity.csv');

}


// Chart
?>

<h2>Active Licenses, By Type, By Quarter <small><a href="?o=csv">[csv]</a></small></h2>
<div>
	<div class="otd-chart" id="license-active-type-quarter-chart"></div>
</div>


<table class="table table-sm">
<thead class="thead-dark">
<tr>
	<th colspan="2">License</th>
	<?php
	foreach ($q_list as $q) {
		echo sprintf('<th>%s</th>', $q);
	}
	?>
	</tr>
</thead>
<tbody>
<?php
foreach ($res_activity as $l) {

	echo '<tr>';
	echo sprintf('<td><a href="/license?id=%s">%s</a></td>', $l['id'], $l['code']);
	echo sprintf('<td>%s</td>', $l['name']);

	foreach ($q_list as $q) {
		echo '<td class="r">';
		echo sprintf('%d for $%0.2f', $l['api'][$q]['c'], $l['api'][$q]['r']);
		// if ($l['xls'][$q]['r'] > 0) {
		// 	echo sprintf('<br><span style="color:#f00;">%d for %0.2f</span>', $l['xls'][$q]['c'], $l['xls'][$q]['r']);
		// }
		// echo sprintf('<br>%d for %0.2f', $l['xls'][$q]['c'], $l['xls'][$q]['r']);
		echo '</td>';
	}

	echo '</tr>';

}
?>
</tbody>
</table>


<?php
// Fold to Chart Data
$tmp_data0 = [];
foreach ($res_activity as $l) {

	$t = substr($l['type'], 0, 1);
	if (empty($tmp_data0[$t])) {
		$tmp_data0[$t] = [];
	}

	foreach ($q_list as $q) {

		if (empty($tmp_data0[$t][$q])) {
			$tmp_data0[$t][$q] = 0;
		}

		$tmp_data0[$t][$q] += $l['api'][$q]['c'];
	}
}
// _exit_text($tmp_data0);
$tmp_data1 = [];
foreach ($tmp_data0 as $t => $s_list) {
	$sum = array_sum($s_list);
	if ($sum > 0) {
		$tmp_data1[$t] = $s_list;
	}
}
// _exit_text($tmp_data1);

$cht_data = [];
$cht_data[0] = [
	[ 'label' => 'Date', 'type' => 'date' ],
];
foreach ($tmp_data1 as $t => $s_list) {
	$cht_data[0][] = $t;
}
foreach ($q_list as $q) {

	$t = strtotime($q);
	$d = sprintf("Date(%d)", $t * 1000); // Format for JS

	$row = [];
	$row[] = $d;

	foreach ($tmp_data1 as $t => $x) {
		$row[] = $tmp_data1[$t][$q];
	}

	$cht_data[] = $row;

}
?>

<script type="text/javascript">
google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(function() {

	var cht_data = google.visualization.arrayToDataTable(<?= json_encode($cht_data, JSON_NUMERIC_CHECK|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) ?>);

	var cht_opts = {
		axisTitlesPosition: 'in',
		chartArea: {
			left: '2%',
			top: '2%',
			width: '84%',
			height: '92%',
		},
		isStacked: 'percent',
		hAxis: null,
		vAxis: null,
		bar: { groupWidth: "100%"},
		// legend: { position: "none" },
	};

	var C = new google.visualization.ColumnChart(document.getElementById('license-active-type-quarter-chart'));
	C.draw(cht_data, cht_opts);
});
</script>
