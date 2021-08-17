<?php
/**
 * @see https://502data.com/counties
 * @todo Breakout between Supply Revenue and Retail Revenue
*/

$_ENV['h1'] = $_ENV['title'] = 'Revenue Overview';

$dbc = _dbc();

$cht_type = 'Active Companies';
$obj_type = 'company_id';
$lic_type = null;

switch ($_GET['ot']) {
case 'license':
	$cht_type = 'Active Licensees';
	$obj_name = 'Licensees';
	$obj_type = 'license_id';
	switch ($_GET['lt']) {
	case 'retail':
		$lic_type = 'retail';
		$sql_type = "AND license_type = 'R'";
		break;
	case 'supply':
		$lic_type = 'supply';
		$sql_type = "AND license_type NOT IN ('R', 'X', 'Z') ";
		break;
	}
	break;
case 'company':
default:
	$obj_type = 'company_id';
	$obj_name = 'Companies';
	$lic_type = null;
	break;
}


$sql = <<<EOS
SELECT month AS m
, count(DISTINCT $obj_type) AS c
, sum(rev_amount_sum) AS r
FROM license_revenue_full
WHERE source IN ('lcb-v1', 'lcb-v2')
$sql_type
GROUP BY month
ORDER BY month ASC
EOS;

// echo "<p>$sql</p>";

// $cht_data = array();
// $cht_data_ext = array();
// $cht_data[] = array('Month', $cht_type, 'Total Revenue');
// $cht_data_ext[] = array(
// 	array('label' => 'Month', 'type' => 'date'),
// 	array('label' => $cht_type, 'type' => 'number'),
// 	array('label' => 'title1', 'type' => 'string'),
// 	array('label' => 'text1', 'type' => 'string'),
// 	array('label' => 'Total Revenue', 'type' => 'number'),
// 	array('label' => 'title2', 'type' => 'string'),
// 	array('label' => 'text2', 'type' => 'string'),
// );
// $cht_data_ext['2014-06-01'] = array('2014-06-01', 0, null, null, 0, null, null);

// $res = $dbc->fetchAll($sql);
$res = _select_via_cache($dbc, $sql);
// foreach ($res as $rec) {
// 	$rec['c'] = floatval($rec['c']);
// 	$rec['r'] = floatval($rec['r']);
// 	$rec['t'] = floatval($rec['t']);
// 	//$cht_data[] = array($rec['m'], $rec['c'], $rec['r'], $rec['t'], $rec['r'] / $rec['c']);
// 	$cht_data[$rec['m']] = array($rec['m'], $rec['c'], $rec['r']); //, null);
// 	$cht_data_ext[$rec['m']] = array($rec['m'], $rec['c'], null, null, $rec['r'] / 1000000, null, null);
// }
//var_dump($cht_data);

// $cht_data_ext['2014-06-01'][2] = 'I502 Cannabis Sales Begins';
// $cht_data_ext['2015-06-01'][2] = 'First Year of Retail Operations';
// $cht_data_ext['2016-06-01'][2] = 'Second Year of Retail Operations';
// $cht_data_ext['2017-06-01'][2] = 'Third Year of Retail Operations';
// $cht_data_ext['2017-10-01'][2] = 'Most Companies/Licenses Reporting';
// $cht_data_ext['2017-10-01'][3] = 'October was the final month of BioTrack reporting';
// $cht_data_ext['2017-11-01'][2] = 'Spreadsheet Reporting Begins';
// $cht_data_ext['2017-11-01'][3] = '143 Companies/147 Licenses Disappeared';
// $cht_data_ext['2018-06-01'][2] = 'Fourth Year of Retail Operations';
// $cht_data_ext['2018-01-01'][2] = 'Valley of Contingency';
// $cht_data_ext['2018-01-01'][3] = 'Off by 384 Companies/394 Licenses, about 30% loss in three months, the same level as June 2016!';
// $cht_data_ext['2018-02-01'][2] = 'LeafData Launched';
// $cht_data_ext['2018-04-01'][5] = 'Data Anomaly'; // 5,6 are for the Red-Line
// $cht_data_ext['2018-04-01'][6] = 'Data Reporting from LCB has some very wild values in it';
// $cht_data_ext['2019-06-01'][2] = 'Fourth Year of Retail Operations';
// $cht_data_ext['2019-07-15'][2] = 'LeafData v1.37.5';


// $cht_json = json_encode(array_values($cht_data));
// $cht_json_ext = json_encode(array_values($cht_data_ext));

// $sql = <<<EOS
// SELECT month AS m
// , count(DISTINCT $obj_type) AS c
// , sum(rev_amount_sum) AS r
// FROM license_revenue_full
// WHERE source IN ('lcb-v1', 'lcb-v2', 'foia-live')
// $sql_type
// GROUP BY month
// ORDER BY month ASC
// EOS;


echo App\UI::revenue_nav_tabs();

?>

<section>
<div class="card">
	<div class="card-header">
		<h2>Timeline of <?= $obj_name ?> filing Revenue Reports</h2>
	</div>
	<div class="card-body">
		<ul class="nav nav-tabs">
		  <li class="nav-item">
		    <a class="nav-link<?= ('company_id' == $obj_type ? ' active' : null) ?>" href="?">by Company</a>
		  </li>
		  <li class="nav-item">
		    <a class="nav-link<?= (('license_id' == $obj_type && null == $lic_type) ? ' active' : null) ?>" href="?ot=license">by License</a>
		  </li>
		  <li class="nav-item">
		    <a class="nav-link<?= ('supply' == $lic_type ? ' active' : null) ?>" href="?ot=license&amp;lt=supply">by Supply License</a>
		  </li>
		  <li class="nav-item">
		    <a class="nav-link<?= ('retail' == $lic_type ? ' active' : null) ?>" href="?ot=license&amp;lt=retail">by Retail License</a>
		  </li>
		</ul>

		<div class="chart-wrap">
			<canvas id="revenue-chart0"></canvas>
		</div>

		<p><?= $obj_name ?> count shown in red, in absolute count.
		 Revenue shown in green, factored in millions of dollars, second Y-axis.
		</p>
	</div>
</div>
</section>

<?php
$Chart0_Config = [
	'type' => 'line',
	'data' => [
		'labels' => [],
		'datasets' => [
			0 => [
				'label' => 'Object Count',
				'backgroundColor' => 'red',
				'borderColor' => 'red',
				'yAxisID' => 'y1',
				'data' => [],
			],
			1 => [
				'label' => 'Revenue',
				'backgroundColor' => 'green',
				'borderColor' => 'green',
				'yAxisID' => 'y2',
				'data' => [],
			]
		],
	],
	'options' => [
		'animations' => false,
		'maintainAspectRatio' => false,
	]
];

foreach ($res as $rec) {
	$Chart0_Config['data']['labels'][] = _date('m/y', $rec['m']);
	$Chart0_Config['data']['datasets'][0]['data'][] = intval($rec['c']);
	$Chart0_Config['data']['datasets'][1]['data'][] = intval($rec['r']);
}
?>
<script>
var Chart0 = new Chart(document.getElementById('revenue-chart0'), <?= json_encode($Chart0_Config, JSON_HEX_AMP | JSON_HEX_APOS| JSON_HEX_QUOT| JSON_HEX_TAG | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?>);
</script>
