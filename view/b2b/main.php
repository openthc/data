<?php
/**
 * B2B Page
 */

$_ENV['h1'] = $_ENV['title'] = 'B2B Sales';

echo \App\UI::b2b_tabs();

// Pie Chart of Deals
$sql = <<<SQL
SELECT count(distinct(id)) AS c
 , stat
FROM b2b_sale_item_full
GROUP BY stat
ORDER BY 1 DESC
SQL;

$dbc = _dbc();
$res = _select_via_cache($dbc, $sql, null);
$max = array_reduce($res, function($r, $v) {
	return ($r + $v['c']);
}, 0);
?>
<section>
<h2>Dollars per B2B Sale Status</h2>
<div class="chart-wrap" style="height: 32px;">
<table class="charts-css bar multiple stacked">
<tbody>
<tr scope="row">
<?php
foreach ($res as $rec) {
	printf('<td style="--size: %0.6f"><span class="tooltip">%s %s</span></td>'
		, $rec['c'] / $max
		, number_format($rec['c'])
		, $rec['stat']
	);
}
?>
</tr>
</tbody>
</table>
</div>
</section>

<hr>

<?php
// select count(distinct(id)), stat FROM b2b_sale_item_full GROUP BY stat  ORDER BY 1 DESC;
//  count  |         stat
// --------+-----------------------
//  725239 | received
//  111313 | VOID-open
//   50797 | VOID-ready-for-pickup
//   39902 | in-transit
//   39541 | VOID-in-transit
//    9947 | open
//    7511 | ready-for-pickup
//    3679 | VOID-received


// require_once(__DIR__ . '/index-chart.php');
require_once(__DIR__ . '/main-rank.php');
