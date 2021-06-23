<?php
/**
 * Stacked Area of Vendor Share
 */

$dbc = _dbc();

$sql = <<<SQL
SELECT count(b2b_sale.id) AS c
, date_trunc('month', execute_at) AS created_at
, sum(full_price) AS full_price_sum
, license.id AS license_id
, license.name AS license_name
FROM b2b_sale
JOIN license ON b2b_sale.source_license_id = license.id
WHERE b2b_sale.target_license_id = ? AND b2b_sale.stat IN ('in-transit', 'ready-for-pickup', 'received')
AND full_price > 0
GROUP BY date_trunc('month', execute_at), license.id, license.name
ORDER BY 2, 3
SQL;
$arg = [ $L['id'] ];
// var_dump($arg);
$res = _select_via_cache($dbc, $sql, $arg);
// var_dump($res);
// _res_to_table($res);
$cht_data = _vlc_fold_to_cht_data($res);
// var_dump($cht_data);
// exit;

?>

<div class="container-fluid mt-2">
	<h2>Revenue, by Month, by Vendor</h2>
	<div>
		<div class="otd-chart" id="vendor-share-by-month"></div>
	</div>
</div>


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
		// title: 'Product Sales',
		isStacked: 'percent',
		hAxis: null,
		vAxis: null,
		bar: { groupWidth: "100%"},
		// legend: { position: "none" },
	};

	var C = new google.visualization.ColumnChart(document.getElementById('vendor-share-by-month'));
	C.draw(cht_data, cht_opts);
});
</script>

<?php
function _vlc_fold_to_cht_data($res)
{
	$col_dt = 'created_at';
	$col_key = 'license_name';
	$col_val = 'full_price_sum';

	$col_list = [];
	$tmp_data = [];
	$lic_rank = [];

	foreach ($res as $rec) {

		$t = strtotime($rec['created_at']);
		$d = sprintf("Date(%d)", $t * 1000); // Format for JS

		$tmp_data[$d][ $rec[$col_key] ] = $rec;

		if (empty($lic_rank[$rec[$col_key]])) {
			$lic_rank[$rec[$col_key]] = 0;
		}

		$lic_rank[$rec[$col_key]] += $rec[$col_val];

	}

	// Sort so the biggest value is at the bottom right of the grid
	arsort($lic_rank);
	// var_dump($lic_rank); exit;

	$col_list = array_keys($lic_rank);
	$col_list = array_slice($col_list, 0, 50);

	$cht_data = [];
	$cht_data[0] = [
		[ 'label' => 'Date', 'type' => 'date' ],
	];
	foreach ($col_list as $x) {
		$cht_data[0][] = $x;
	}

	foreach ($tmp_data as $d => $row_data) {
		$row = [];
		$row[] = $d;
		foreach ($col_list as $c) {
			$row[] = floatval($row_data[$c][$col_val]);
		}
		$cht_data[] = $row;
	}
	$cht_data = array_slice($cht_data, 0, 25);
	return $cht_data;
}
