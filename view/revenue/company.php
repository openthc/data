<?php
/**
 * @todo Breakout between Supply Revenue and Retail Revenue
 */

session_write_close();

$_ENV['h1'] = $_ENV['title'] = "Revenue :: by Company :: $month_count Months";

_revenue_nav_tabs();

$month_count = 6;
$limit_count = intval($_GET['l']);
if (empty($limit_count)) {
	$limit_count = 100;
}

$dbc = _dbc();

$res = $dbc->fetchRow('SELECT min(month) AS date_alpha, max(month) AS date_omega FROM license_revenue');
// var_dump($res);
// if (empty($res['date_omega'])) {
// 	_exit_text('Revenue Reports not Available', 404);
// }

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

$rev_company = array_slice($rev_company, 0, $limit_count);
// var_dump($rev_company); exit;

$mon_list = array_unique($mon_list);
sort($mon_list);
$mon_list = array_slice($mon_list, -6);

?>

<div class="container-fluid">

<p>Showing 1 - 50 of <?= $max_company ?></p>

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

</div>

<script>
$(function() {
	// Draw the Chart
	google.charts.load('current', {'packages':['corechart']});
	google.charts.setOnLoadCallback(function() {
		var data = google.visualization.arrayToDataTable(<?= $cht_json ?>);

		var options = {
			title: 'Company Performance',
			curveType: 'function',
			legend: { position: 'none' }
		};

		var chart = new google.visualization.LineChart(document.getElementById(''));

		chart.draw(data, options);
	});

	var CompanyTable = $('#company-revenue-list').DataTable({
		info: false,
		order: [],
		paging: false,
		processing: true,
		searching: false,
	});


});
</script>
