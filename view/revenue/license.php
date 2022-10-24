<?php
/**
 * License
 */

use Edoceo\Radix\DB\SQL;

$_ENV['h1'] = $_ENV['title'] = 'Revenue :: by License';

$dbc = _dbc();

$month_count = 6;
$limit_count = intval($_GET['l']);
if (empty($limit_count)) {
	$limit_count = 100;
}

$res = $dbc->fetchRow('SELECT min(month) AS date_alpha, max(month) AS date_omega FROM license_revenue');
$mon0 = $res['date_alpha'];
$mon1 = $res['date_omega'];
$mon_list = [];

$license_filter = null;
switch ($_GET['t']) {
case 'R':
	$license_filter = " AND license.type IN ('R') ";
	break;
case 'S':
	$license_filter = " AND license.type IN ('G', 'J', 'M') ";
	break;
}

//$type_list = SQL::fetch_all('SELECT DISTINCT license_type FROM license_revenue_full');
//var_dump($type_list);

// $sql_where = null;
//
// $sql = <<<SQL
// CREATE TEMPORARY TABLE license_revenue_calc (
// 	license_id varchar(26),
// 	revenue numeric(16,3)
// )
// SQL;
// $dbc->query($sql);
//
// $sql = <<<SQL
// INSERT INTO license_revenue_calc
// SELECT license_id, sum(rev_amount_sum)
// FROM license_revenue_full
// WHERE month > '$mon0'::date - '$month_count months'::interval $sql_where
// GROUP BY license_id
// ORDER BY 2 DESC
// LIMIT 50
// SQL;
// $dbc->query($sql);
//
// // Top 50 over last N months
// $sql = <<<SQL
// SELECT * FROM license_revenue_full
// WHERE license_id IN (SELECT DISTINCT license_id FROM license_revenue_calc)
//  AND month > '$mon0'::date - '$month_count months'::interval $sql_where
// SQL;
//
//echo "<p>$sql</p>";

$sql = <<<EOS
SELECT license.id
, license.name
, license.code AS license_code
, license.type AS license_type
, license_revenue.month
, license_revenue.rev_amount AS rev
FROM license
JOIN license_revenue ON license.id = license_revenue.license_id
WHERE license_revenue.month > '$mon1'::date - '$month_count months'::interval $license_filter
 AND license_revenue.source IN ('lcb-v1', 'lcb-v2')
ORDER BY license_revenue.month DESC
EOS;

$rev_license = array();

// $res = $dbc->fetchAll($sql);
$res = _select_via_cache($dbc, $sql, []);
foreach ($res as $rec) {

	if (empty($rev_license[ $rec['id'] ])) {
		$rev_license[ $rec['id'] ] = array(
			'id' => $rec['id'],
			'name' => $rec['name'],
			'license_type' => $rec['license_type'],
			'license_code' => $rec['license_code'],
			// 'county' => $rec['county'],
			// 'city' => $rec['city'],
			'revenue_full' => floatval($rec['rev']),
			'revenue_list' => array(
				$rec['month'] => floatval($rec['rev']),
			),
		);
	} else {
		$rev_license[ $rec['id'] ]['revenue_list'][ $rec['month'] ] = $rec['rev'];
		$rev_license[ $rec['id'] ]['revenue_full'] += $rec['rev'];
	}

	$mon_list[] = $rec['month'];
}
// var_dump($rev_license);

// Minimum 3 months of reporting
//$rev_license = array_filter($rev_license, function($v) {
// 	return (count($v['revenue_list']) >= 3);
// });

// Full Count
$max_license = count($rev_license);

// Sort
array_walk($rev_license, function(&$v, $k) {
	$v['revenue_mean'] = array_sum($v['revenue_list']) / count($v['revenue_list']);
});

// switch ($_GET['sort']) {
// case '0-9':
// 	uasort($rev_license, function($a, $b) {
// 		return ($a['revenue_sort'] < $b['revenue_sort']);
// 	});
// 	break;
// case '9-0':
// default:
	uasort($rev_license, function($a, $b) {
		return ($a['revenue_mean'] < $b['revenue_mean']);
	});
// 	break;
// }

$rev_license = array_slice($rev_license, 0, $limit_count);

$mon_list = array_unique($mon_list);
rsort($mon_list);
$mon_list = array_slice($mon_list, 0, 6);

echo \OpenTHC\Data\UI::revenue_nav_tabs();

?>

<?php
$Chart0_Config = [
	'type' => 'bar',
	'data' => [
		'labels' => [],
		'datasets' => [
			0 => [
				'label' => 'Revenue',
				'backgroundColor' => 'green',
				'borderColor' => 'green',
				'yAxisID' => 'y1',
				'data' => [],
			],
		],
	],
	'options' => [
		'animations' => false,
		'maintainAspectRatio' => false,
		'plugins' => [
			'legend' => false,
		],
		'scales' => [
			'x' => [
				'display' => false,
			],
			'y' => [
				'position' => 'right',
			]
		]
	]
];

$mon = $mon_list[0];
$cht_license = array_slice($rev_license, 0, 50);
foreach ($cht_license as $rec) {
	$Chart0_Config['data']['labels'][] = $rec['name'];
	$Chart0_Config['data']['datasets'][0]['data'][] = intval($rec['revenue_list'][$mon]);
	// $Chart0_Config['data']['datasets'][1]['data'][] = intval($rec['r']);
}
?>
<section>
<h2>Shows Recent Month Revenue, Order by 6mo Average, Top 50</h2>
<div class="chart-wrap">
	<canvas id="revenue-license-chart0"></canvas>
</div>
</section>
<script>
var Chart0 = new Chart(document.getElementById('revenue-license-chart0'), <?= json_encode($Chart0_Config, JSON_HEX_AMP | JSON_HEX_APOS| JSON_HEX_QUOT| JSON_HEX_TAG | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?>);
</script>

<style>
.dataTables_filter, .dataTables_info { display: none; }
</style>


<p>Showing 1 - <?= $limit_count ?> of <?= $max_license ?> </p>

<div class="table-responsive">
<table class="table table-sm table-striped table-hover" id="license-revenue-list">
<thead class="thead-dark">
	<tr>
	<th><input class="form-control form-control-sm table-filter" id="filter-license" placeholder="License"></th>
	<th><input class="form-control form-control-sm table-filter" id="filter-license-type" placeholder="License Type"></th>
	<!--
	<th><input class="form-control form-control-sm table-filter" id="filter-county" placeholder="County"></th>
	<th><input class="form-control form-control-sm table-filter" id="filter-city" placeholder="City"></th> -->
	<?php
	foreach ($mon_list as $mon) {
		echo '<th class="r">' . strftime('%m/%y', strtotime($mon)) . '</th>';
	}
	?>
	<th class="r"><i style="text-decoration: overline;">x</i>/mo</th>
	<th class="r">&sum;<?= $month_count ?>mo</th>
	</tr>
</thead>
<tbody>
<?php

foreach ($rev_license as $rev) {
?>
	<tr>
		<td><a href="/license?id=<?= $rev['id'] ?>"><?= h($rev['name']) ?></a>
			<small>[<?= h($rev['license_code']) ?>]</small>
		</td>
		<td><?= h($rev['license_type']) ?></td>
		<!--
		<td><?= h($rev['county']) ?></td>
		<td><?= h($rev['city']) ?></td> -->
		<?php
		foreach ($mon_list as $mon) {
			echo '<td class="r">' . number_format($rev['revenue_list'][$mon]) . '</td>';
		}
		?>
		<td class="r"><?= number_format($rev['revenue_mean']) ?></td>
		<td class="r"><?= number_format($rev['revenue_full']) ?></td>
	</tr>

<?php
}
?>
</tbody>
</table>
</div>

<script>
$.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {

	//search only in column 1 and 2
	var f0 = $('#filter-license').val().toLowerCase();
	var f1 = ''; // $('#filter-license-type').val().toLowerCase();
	var f2 = ''; // $('#filter-county').val().toLowerCase();
	var f3 = ''; // $('#filter-city').val().toLowerCase();

	var have = 0;
	var want = 0;

	if ((f0 === f1) && (f1 === f2) && (f2 === f3)) {
		return true;
	}

	if (f0) {
		want++;
		if (~data[0].toLowerCase().indexOf(f0)) {
			have++;
		}
	}

	if (f1) {
		want++;
		if (~data[1].toLowerCase().indexOf(f1)) {
			have++;
		}
	}

	if (f2) {
		want++;
		if (~data[2].toLowerCase().indexOf(f2)) {
			have++;
		}
	}

	if (f3) {
		want++;
		if (~data[3].toLowerCase().indexOf(f3)) {
			have++;
		}
	}

	if ((want) && (have === want)) {
		return true;
	}

	return false;
});

$(function() {

	var LicenseTable = $('#license-revenue-list').DataTable({
		info: false,
		order: [],
		paging: false,
		processing: true,
		//searching: false,
	});

	$('.table-filter').on('keyup', function() {
		LicenseTable.draw();
	});

});
</script>
