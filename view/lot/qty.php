<?php
/**
 * Negative Quantity Lots
 */

/*
-- Build Tall Table from Source
CREATE TABLE wip_lot_qty_temp (license_id varchar(26), type varchar(4), qty int);

INSERT INTO wip_lot_qty_temp (license_id, type, qty)
  SELECT lot.license_id, 'n', count(lot.id) FROM lot WHERE created_at >= '2019-01-01' AND lot.qty < 0 GROUP BY license_id;

INSERT INTO wip_lot_qty_temp (license_id, type, qty)
  SELECT lot.license_id, 'p', count(lot.id) FROM lot WHERE created_at >= '2019-01-01' AND lot.qty > 0 GROUP BY license_id;

INSERT INTO wip_lot_qty_temp (license_id, type, qty)
  SELECT lot.license_id, 'z', count(lot.id) FROM lot WHERE created_at >= '2019-01-01' AND lot.qty = 0 GROUP BY license_id;

-- Build Wide Table from Tall Table
CREATE TABLE wip_lot_qty_full (license_id varchar(26),
  f int, p int, n int, z int
  f_pct numeric(5,2),
  n_pct numeric(5,2),
  z_pct numeric(5,2)
);

INSERT INTO wip_lot_qty_full (license_id) SELECT DISTINCT license_id FROM wip_lot_qty_temp;

UPDATE wip_lot_qty_full SET p = (SELECT qty FROM wip_lot_qty_temp WHERE type = 'p' AND license_id = wip_lot_qty_full.license_id);

UPDATE wip_lot_qty_full SET n = (SELECT qty FROM wip_lot_qty_temp WHERE type = 'n' AND license_id = wip_lot_qty_full.license_id);

UPDATE wip_lot_qty_full SET z = (SELECT qty FROM wip_lot_qty_temp WHERE type = 'z' AND license_id = wip_lot_qty_full.license_id);

UPDATE wip_lot_qty_full SET p = 0 WHERE p IS NULL;
UPDATE wip_lot_qty_full SET n = 0 WHERE n IS NULL;
UPDATE wip_lot_qty_full SET z = 0 WHERE z IS NULL;
UPDATE wip_lot_qty_full_2019_2020 set f = n + p + z;

UPDATE wip_lot_qty_full_2019_2020 SET p_pct = p::numeric / f::numeric * 100;
UPDATE wip_lot_qty_full_2019_2020 SET n_pct = n::numeric / f::numeric * 100;
UPDATE wip_lot_qty_full_2019_2020 SET z_pct = z::numeric / f::numeric * 100;

*/


$_ENV['h1'] = $_ENV['title'] = 'Lots :: Counts by Quantity Group';

session_write_close();

$dbc = _dbc();

$tab = 'wip_lot_qty_full_2018_2019_2020';
$tab = 'wip_lot_qty_full_2019_2020';

$top = 100;
if (('csv' == $_GET['f']) && ('ALL' == $_GET['top'])) {
	$top = null;
}

// $sql = <<<SQL
// SELECT count(lot.id) AS c
// , license.id
// , license.name
// FROM lot
// JOIN license ON lot.license_id = license.id
// WHERE lot.qty < 0
// GROUP BY license.id, license.name
// ORDER BY 1 DESC
// SQL;
$sql_order = null;
$sql_order = " ORDER BY n_pct DESC, f DESC";

$sql_limit = null;
if (!empty($top)) {
	$sql_limit = " LIMIT $top";
}


$sql = <<<SQL
SELECT
 license.code AS license_code
, license.name AS license_name
, wip_lot_qty_full.p + wip_lot_qty_full.n + wip_lot_qty_full.z AS c_full
, wip_lot_qty_full.p AS c_pos, p_pct AS c_pos_pct
, wip_lot_qty_full.n AS c_neg, n_pct AS c_neg_pct
, wip_lot_qty_full.z AS c_zero, z_pct AS c_zero_pct
FROM $tab AS wip_lot_qty_full
JOIN license ON wip_lot_qty_full.license_id = license.id
WHERE n > 0 AND z > 0 AND p > 0 AND 3 = 3
$sql_order
$sql_limit
SQL;

$res = _select_via_cache($dbc, $sql, null);

$cht_data = [
	'type' => 'bar',
	'data' => [
		'labels' => [], // License
		'datasets' => [
			[
				'label' => 'Zero Values',
				'backgroundColor' => '#111111',
				'borderColor' => '#11111155',
				'data' => [], // Values
			],
			[
				'label' => 'Positive Values',
				'backgroundColor' => '#00cc00',
				'borderColor' => '#00cc0055',
				'data' => [], // Values
			],
			[
				'label' => 'Negative Values',
				'backgroundColor' => '#cc0000',
				'borderColor' => '#cc000055',
				'data' => [], // Values
			]
		]
	],
	'options' => [
		'responsiveAnimationDuration' => 0,
		'animation' => [
			'duration' => 0,
		],
		'hover' => [
			'animationDuration' => 0,
		],
		'scales' => [
			'xAxes' => [
				[ 'stacked' => true ]
			],
			'yAxes' => [
				[ 'stacked' => true ]
			]
		]
	],
];

// Update Record
foreach ($res as $idx => $rec) {

	$cht_data['data']['labels'][] = $rec['license_code'];
	$cht_data['data']['datasets'][0]['data'][] = $rec['c_zero'];
	$cht_data['data']['datasets'][1]['data'][] = $rec['c_pos'];
	$cht_data['data']['datasets'][2]['data'][] = $rec['c_neg'];

	// Update Record
	// $res[$idx] = $rec;

}

if ('csv' == $_GET['f']) {
	$csv_spec = [
		'license_id' => 'License ID',
		'license_code' => 'License Code',
		'license_name' => 'License Name',
		'c_full' => 'Count Full',
		'c_pos' => 'Count POS',
		'c_pos_pct' => 'Count POS %',
		'c_neg' => 'Count NEG',
		'c_neg_pct' => 'Count NEG %',
		'c_zero' => 'Count Zero',
		'c_zero_pct' => 'Count Zero %',
	];
	$csv_name = 'lot_qty_report.csv';
	_res_to_csv($res, $csv_spec, $csv_name, $csv_char=',');
}

$col_func = [];
$col_func['c_full'] = function($v) { return sprintf('<td class="r">%d</td>', $v); };
$col_func['c_pos'] = function($v) { return sprintf('<td class="r" style="color:#00cc00;">%d</td>', $v); };
$col_func['c_pos_pct'] = function($v) { return sprintf('<td class="r" style="color:#00cc00;">%0.1f%%</td>', $v); };
$col_func['c_neg'] = function($v) { return sprintf('<td class="r" style="color:#cc0000;">%d</td>', $v); };
$col_func['c_neg_pct'] = function($v) { return sprintf('<td class="r" style="color:#cc0000;">%0.1f%%</td>', $v); };
$col_func['c_zero'] = function($v) { return sprintf('<td class="r">%d</td>', $v); };
$col_func['c_zero_pct'] = function($v) { return sprintf('<td class="r">%0.1f%%</td>', $v); };

?>

<style>
.chart-wrap {
	background: #cccccccc;
	margin: 0 0 0.5rem 0;
	padding: 0;
	position: relative;
	width: 100%;
}
.chart-wrap canvas {
	height: 100%;
	width: 100%;
}
</style>

<p>
Counts of Lots with Zero, Positive and Negative quantity values.
Sample data from 2019-01-01 forward, top 100, by count of negative quantity record.
You can <a href="?f=csv&amp;top=ALL"><i class="fas fa-download"></i> download as csv</a>.
</p>

<div class="chart-wrap" style="height: 360px;">
	<canvas id="chart-qty"></canvas>
</div>

<div class="container">
<?= _res_to_table($res, $col_func) ?>
</div>

<script>
function fitToContainer(canvas){
  // Make it visually fill the positioned parent
  canvas.style.width ='100%';
  canvas.style.height='100%';
  // ...then set the internal size to match
  canvas.width  = canvas.offsetWidth;
  canvas.height = canvas.offsetHeight;
}

$(function() {

	var cht_node = document.getElementById('chart-qty');
	fitToContainer(cht_node);

	var cht_data = <?= json_encode($cht_data) ?>;

	// @todo Assign Colors
	var idx = 0;
	var max = cht_data.data.datasets.length;
	for (idx = 0; idx < max; idx++) {
		var ds0 = cht_data.data.datasets[idx];
	}

	var C0 = new Chart(cht_node.getContext('2d'), cht_data);

	$('#res-table-11').DataTable({
		info: false,
		order: [],
		paging: false,
		processing: true,
		searching: false,
	});

})
</script>
