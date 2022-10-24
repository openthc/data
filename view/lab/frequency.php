<?php
/**
 * Number of Tests Per Month
 */

$_ENV['h1'] = $_ENV['title'] = 'Lab Result :: Frequency';

echo \OpenTHC\Data\UI::lab_tabs();

$dbc = _dbc();

?>
<section>
<h2>Result Count, Per Month <small><a href="?d=lrpm&amp;f=csv"><i class="fas fa-download"></i></a></small></h2>
<?php
$sql = <<<SQL
SELECT count(id) AS c,
  date_trunc('month', created_at) AS created_at
FROM lab_result
WHERE created_at >= :dt0
  AND id NOT LIKE 'WAATTESTE%'
GROUP BY 2
ORDER BY 2
SQL;

$arg = [
	':dt0' => DATE_ALPHA
];

$res = _select_via_cache($dbc, $sql, $arg);
if (('lrpm' == $_GET['d']) && ('csv' == $_GET['f'])) {
	$csv_spec = [
		'created_at' => 'Month',
		'c' => 'Count',
	];
	_res_to_csv($res, $csv_spec, 'lab-frequency.csv');
}
$max = array_reduce($res, function($prev, $item) {
	return max($prev, $item['c']);
}, 0);
$max = ($max * 1.10);
?>
<div class="chart-wrap" style="height: 360px;">
	<table class="charts-css column multiple data-spacing-2 show-data-on-hover show-labels">
	<?php
	$v0 = 0;
	$v1 = 0;
	foreach ($res as $rec) {
		$v1 = $rec['c'] / $max;

		echo '<tr>';
		printf('<th scope="row">%s</th>', _date('m/Y', $rec['created_at']));
		printf('<td style="--start: %0.6f; --size: %0.6f"><span class="data">%d</span></td>', $v0, $v1, $rec['c']);
		echo '</tr>';

		$v0 = $v1;
	}
	?>
	</table>
</div>
</section>

<!-- TODO: <p>Bar Chart of Top LIcense By -->
<!-- TODO: <p>Count of Tests, Per License, Per Month</p> -->

<hr>
<section>
<h2>Result Count, By Product Type, By Month <small><a href="?d=lrpmt&amp;f=csv"><i class="fas fa-download"></i></a></small></h2>
<?php
$sql = <<<SQL
SELECT count(id) AS c
	, date_trunc('month', created_at) AS created_at
	, type AS product_type
FROM lab_result
WHERE created_at >= :dt0
  AND id NOT LIKE 'WAATTESTE%'
GROUP BY 2, 3
ORDER BY 2, 3
SQL;

$arg = [
	':dt0' => DATE_ALPHA
];

$res_source = _select_via_cache($dbc, $sql, $arg);
if (('lrpmt' == $_GET['d']) && ('csv' == $_GET['f'])) {
	$csv_spec = [
		'created_at' => 'Month',
		'product_type' => 'Product Type',
		'c' => 'Count',
	];
	_res_to_csv($res_source, $csv_spec, 'lab-frequency-by-type.csv');
}
// $max = array_reduce($res, function($prev, $item) {
// 	return max($prev, $item['c']);
// }, 0);
$product_type_list = [];
$res_output = [];
foreach ($res_source as $src) {
	if (empty($res_output[ $src['created_at'] ])) {
		$res_output[ $src['created_at'] ] = [];
	}
	$res_output[ $src['created_at'] ][ $src['product_type'] ] = $src['c'];
	// $license_list[ $src['product_type'] ] = true;
	 // track their most recent counts only
	// $license_rank[ $src['product_type'] ] = ($license_rank[ $src['product_type'] ] + $src['c']) / 3;
	$product_type_list[ $src['product_type'] ] = ($product_type_list[ $src['product_type'] ] + $src['c']) / 3;
	// $license_rank[ $src['product_type'] ] = 0; // $src['count'];
}
arsort($product_type_list);
// $max = ($max * 1.10);

// Data for Plotly
// $cht_data = [];
// $tmp_data = [];
// $product_type_list = [];
// foreach ($res as $rec) {
// 	$d = $rec['m'];
// 	$t = $rec['t'];
// 	$product_type_list[] = $t;
// 	$tmp_data[$d][$t]  = $rec['c'];
// }
// sort($product_type_list);
// $product_type_list = array_unique($product_type_list);
// $product_type_list = array_values($product_type_list);
// foreach ($product_type_list as $i => $t) {
// 	$cht_data[$i] = [
// 		'name' => $t,
// 		'type' => 'bar',
// 		'x' => [],
// 		'y' => [],
// 	];
// }
// // _exit_json($cht_data);

// foreach ($tmp_data as $di => $dm) {
// 	$x = _date('Y-m-d', $di);
// 	foreach ($product_type_list as $pi => $pt) {
// 		$cht_data[$pi]['x'][] = $x;
// 		$cht_data[$pi]['y'][] = intval($tmp_data[$di][$pt]);
// 	}
// }
// _exit_json($cht_data);
?>
<div class="chart-wrap" style="height: 360px;">
	<table class="charts-css column multiple stacked show-data-on-hover show-labels">
	<caption>Lab Results, By Type, By Month</caption>
	<thead>
		<tr>
			<th scope="col">Month</th>
			<?php
			$idx = 0;
			foreach ($product_type_list as $t => $x) {
				$idx++;
				printf('<th scope="col">%s</th>', h(basename($t)));
			}
			?>
		</tr>
	</thead>
	<tbody>
	<?php
	foreach ($res_output as $cts => $rec) {

		$max = array_sum($rec);

		echo '<tr>';
		printf('<th scope="row">%s</th>', _date('m/Y', $cts));
		foreach ($product_type_list as $t => $x) {
			$v = $rec[$t];
			printf('<td style="--size: %0.6f; text-align: right;"><span class="data">%s</span><span class="tooltip">%s</span></td>'
				, $v / $max, $v, basename($t)
			);
		}

	}
	?>
	</tbody>
	</table>
</div>
</section>

<!-- <hr> -->
<?php
// require_once(__DIR__ . '/frequency-by-lab.php');
?>
