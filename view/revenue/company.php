<?php
/**
 * @todo Breakout between Supply Revenue and Retail Revenue
 */

$_ENV['h1'] = $_ENV['title'] = "Revenue :: by Company :: $month_count Months";

$month_count = 6;
$limit_count = intval($_GET['l']);
if (empty($limit_count)) {
	$limit_count = 100;
}

$dbc = _dbc();

$sql = 'SELECT min(month) AS date_alpha, max(month) AS date_omega FROM license_revenue';
$res = $dbc->fetchRow($sql);
// $res = _select_via_cache($dbc, $sql);


$mon0 = $res['date_alpha'];
$mon1 = $res['date_omega'];
$mon_list = [];

$sql = <<<EOS
SELECT company.id
, company.name
, license_revenue.month
, sum(license_revenue.rev_amount) AS rev
FROM company
JOIN license ON company.id = license.company_id
JOIN license_revenue ON license.id = license_revenue.license_id
WHERE license_revenue.month > '$mon1'::date - '$month_count months'::interval
 AND license_revenue.source IN ('lcb-v1', 'lcb-v2')
GROUP BY company.id, company.name, license_revenue.month
ORDER BY license_revenue.month DESC
EOS;

$rev_company = array();

// $res = $dbc->fetchAll($sql);
$res = _select_via_cache($dbc, $sql, []);
foreach ($res as $rec) {

	if (empty($rev_company[ $rec['id'] ])) {
		$rev_company[ $rec['id'] ] = array(
			'id' => $rec['id'],
			'name' => $rec['name'],
			'revenue_full' => floatval($rec['rev']),
			'revenue_list' => array(
				$rec['month'] => floatval($rec['rev']),
			),
		);
	} else {
		$rev_company[ $rec['id'] ]['revenue_list'][ $rec['month'] ] = floatval($rec['rev']);
		$rev_company[ $rec['id'] ]['revenue_full'] += floatval($rec['rev']);
	}

	$mon_list[] = $rec['month'];
}

// Minimum 3 months of reporting
$rev_company = array_filter($rev_company, function($v) {
	return (count($v['revenue_list']) >= 3);
});

// Full Count
$max_company = count($rev_company);

// Sort
array_walk($rev_company, function(&$v, $k) {
	// Average for Sorting
	$v['revenue_mean'] = array_sum($v['revenue_list']) / count($v['revenue_list']);
});

uasort($rev_company, function($a, $b) {
	return ($a['revenue_mean'] < $b['revenue_mean']);
});

// $rev_company = array_slice($rev_company, 0, $limit_count);
// var_dump($rev_company); exit;

$mon_list = array_unique($mon_list);
rsort($mon_list);
$mon_list = array_slice($mon_list, 0, 6);

echo App\UI::revenue_nav_tabs();

?>

<!-- <p>Showing 1 - 50 of <?= $max_company ?></p> -->

<section>
<h2>Shows Recent Month Revenue, Order by 6mo Average, Top 50</h2>
<div class="chart-wrap">
	<canvas id="revenue-company-chart0"></canvas>
</div>
</section>


<div class="table-responsive">
<table class="table table-sm table-striped table-hover" id="company-revenue-list">
<thead class="thead-dark">
	<tr>
	<th>Company</th>
	<?php
	foreach ($mon_list as $mon) {
		echo '<th class="r">' . strftime('%m/%y', strtotime($mon)) . '</th>';
	}
	?>
	<th class="r"><i style="text-decoration: overline;">x</i>/mo</th>
	<!-- <th class="r">&lt;12mo</th> -->
	</tr>
</thead>
<tbody>
<?php

foreach ($rev_company as $rev) {
?>
	<tr>
		<td><a href="/company?id=<?= $rev['id'] ?>"><?= h($rev['name']) ?></a></td>
		<?php
		foreach ($mon_list as $mon) {
			echo '<td class="r">' . number_format($rev['revenue_list'][$mon]) . '</td>';
		}
		?>
		<td class="r"><?= number_format($rev['revenue_mean']) ?></td>
		<!-- <td class="r"><?= number_format($rev['revenue_sort']) ?></td> -->
		<!-- <td class="r"><?= number_format($rev['revenue_full']) ?></td> -->
	</tr>

<?php
}
?>
</tbody>
</table>
</div>

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
$rev_company = array_slice($rev_company, 0, 50);
foreach ($rev_company as $rec) {
	$Chart0_Config['data']['labels'][] = $rec['name'];
	$Chart0_Config['data']['datasets'][0]['data'][] = intval($rec['revenue_list'][$mon]);
	// $Chart0_Config['data']['datasets'][1]['data'][] = intval($rec['r']);
}
?>
<script>
var Chart0 = new Chart(document.getElementById('revenue-company-chart0'), <?= json_encode($Chart0_Config, JSON_HEX_AMP | JSON_HEX_APOS| JSON_HEX_QUOT| JSON_HEX_TAG | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?>);
</script>
