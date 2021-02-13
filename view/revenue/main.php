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
		$sql_type = "WHERE license_type = 'R'";
		break;
	case 'supply':
		$lic_type = 'supply';
		$sql_type = "WHERE license_type NOT IN ('R', 'X', 'Z') ";
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
, sum(tax_amount_sum) AS t
FROM license_revenue_full
$sql_type
GROUP BY month
ORDER BY month ASC
EOS;

//echo "<p>$sql</p>";

$cht_data = array();
$cht_data_ext = array();
$cht_data[] = array('Month', $cht_type, 'Total Revenue');
$cht_data_ext[] = array(
	array('label' => 'Month', 'type' => 'date'),
	array('label' => $cht_type, 'type' => 'number'),
	array('label' => 'title1', 'type' => 'string'),
	array('label' => 'text1', 'type' => 'string'),
	array('label' => 'Total Revenue', 'type' => 'number'),
	array('label' => 'title2', 'type' => 'string'),
	array('label' => 'text2', 'type' => 'string'),
);
$cht_data_ext['2014-06-01'] = array('2014-06-01', 0, null, null, 0, null, null);

$res = $dbc->fetchAll($sql);
foreach ($res as $rec) {
	$rec['c'] = floatval($rec['c']);
	$rec['r'] = floatval($rec['r']);
	$rec['t'] = floatval($rec['t']);
	//$cht_data[] = array($rec['m'], $rec['c'], $rec['r'], $rec['t'], $rec['r'] / $rec['c']);
	$cht_data[$rec['m']] = array($rec['m'], $rec['c'], $rec['r']); //, null);
	$cht_data_ext[$rec['m']] = array($rec['m'], $rec['c'], null, null, $rec['r'] / 1000000, null, null);
}
//var_dump($cht_data);

$cht_data_ext['2014-06-01'][2] = 'I502 Cannabis Sales Begins';
$cht_data_ext['2015-06-01'][2] = 'First Year of Retail Operations';
$cht_data_ext['2016-06-01'][2] = 'Second Year of Retail Operations';
$cht_data_ext['2017-06-01'][2] = 'Third Year of Retail Operations';
$cht_data_ext['2017-10-01'][2] = 'Most Companies/Licenses Reporting';
$cht_data_ext['2017-10-01'][3] = 'October was the final month of BioTrack reporting';
$cht_data_ext['2017-11-01'][2] = 'Spreadsheet Reporting Begins';
$cht_data_ext['2017-11-01'][3] = '143 Companies/147 Licenses Disappeared';
$cht_data_ext['2018-06-01'][2] = 'Fourth Year of Retail Operations';
$cht_data_ext['2018-01-01'][2] = 'Valley of Contingency';
$cht_data_ext['2018-01-01'][3] = 'Off by 384 Companies/394 Licenses, about 30% loss in three months, the same level as June 2016!';
$cht_data_ext['2018-02-01'][2] = 'LeafData Launched';
$cht_data_ext['2018-04-01'][5] = 'Data Anomaly'; // 5,6 are for the Red-Line
$cht_data_ext['2018-04-01'][6] = 'Data Reporting from LCB has some very wild values in it';
$cht_data_ext['2019-06-01'][2] = 'Fourth Year of Retail Operations';
// $cht_data_ext['2019-07-15'][2] = 'LeafData v1.37.5';


$cht_json = json_encode(array_values($cht_data));
$cht_json_ext = json_encode(array_values($cht_data_ext));

echo App\UI::revenue_nav_tabs();

?>

<!--
<div class="container">
<div class="card">
	<div class="card-header"><h2>Active Companes and Monthly Revenue</h2></div>
	<div class="card-body">
		<div id="active-monthly-chart" style="height:480px;"></div>
	</div>
</div>
</div>
-->

<div class="ui segment">
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

		<div>
			<div class="otd-chart" id="active-monthly-chart-annotated"></div>
		</div>

		<p><?= $obj_name ?> count shown in blue, in absolute count. Revenue shown in red, factored in millions of dollars, second Y-axis.</p>
	</div>
</div>
</div>

<script>
$(function() {
	// Draw the Chart
	google.charts.load('current', {'packages':['corechart']});
	google.charts.setOnLoadCallback(function() {

		return(null);

		var data = google.visualization.arrayToDataTable(<?= $cht_json ?>);

		var options = {
			title: 'Company Performance',
			legend: { position: 'none' },
			lineWidth: 4,
			hAxis: {
				logScale: true,
			},
			vAxes: {
				0: {title: 'Active Companies'},
				1: {title: 'Total Revenue'}
			},
			series: {
				0: { targetAxisIndex: 0},
				1: { targetAxisIndex: 1}
			},
		};

		var chart = new google.visualization.LineChart(document.getElementById('active-monthly-chart'));

		chart.draw(data, options);

	});

});
</script>


<script>
$(function() {
	// Draw the Chart
	google.charts.load('current', {'packages':['annotationchart']});
	google.charts.setOnLoadCallback(function() {

		var src_data = <?= $cht_json_ext ?>;

		// Convert to JS Date
		var idx = 1; // Yes, skip first row
		var max = src_data.length;
		var d = null;
		for (idx; idx < max; idx++) {
			d = src_data[idx][0];
			//console.log(d);
			d = new Date(d + 'T00:00:00-08:00');
			//console.log(d);
			//d.setDate(2);
			//d.setHours(23);
			src_data[idx][0] = d;
		}

		var data = google.visualization.arrayToDataTable(src_data);
		var options = {
			title: 'Companies Reporting',
			legend: { position: 'none' },
			annotationsWidth: 15,
			displayZoomButtons: false,
			fill: 20,
			scaleColumns: [0, 1],
			scaleType: 'allmaximized',
			thickness: 4,
		};

		var chart = new google.visualization.AnnotationChart(document.getElementById('active-monthly-chart-annotated'));

		chart.draw(data, options);

		$('#active-monthly-chart-annotated').removeClass('container');

	});

});
</script>
