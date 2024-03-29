<?php
/**
 * Show Incoming Supplier List
 */

$dbc = _dbc();

$L = $dbc->fetchRow('SELECT * FROM license WHERE id = ?', [ $_GET['id'] ]);
if (empty($L['id'])) {
	_exit_text('Invalid License', 400);
}

$_ENV['h1'] = sprintf('License :: %s :: Vendors', h($L['name']));
$_ENV['title'] = $_ENV['h1'];

echo \OpenTHC\Data\UI::license_tabs($L);

// Chart
require_once(__DIR__ . '/vendors-chart-stacked-column.php');
?>


<hr>


<?php


$max = 100;

$sql = <<<SQL
SELECT count(b2b_sale.id) AS c, sum(full_price) AS rev
, license.id AS license_id
, license.code AS license_code
, license.name AS license_name
FROM b2b_sale
JOIN license ON b2b_sale.source_license_id = license.id
WHERE b2b_sale.target_license_id = ? AND b2b_sale.stat IN ('open', 'ready-for-pickup', 'in-transit', 'received')
AND execute_at >= now() - '12 months'::interval
GROUP BY license.id, license.name
ORDER BY 2 DESC
LIMIT $max
SQL;
$arg = [ $L['id'] ];
$res = _select_via_cache($dbc, $sql, $arg);

?>


<section>
<h2>Top Vendors</h2>
<p>Top <?= $max ?> vendors, last 12 months</p>

<table class="table table-sm table-hover">
<thead class="thead-dark">
<tr>
	<th>Vendor</th>
	<th class="r">Purchases</th>
	<th class="r">Expense</th>
	<th></th>
</tr>
</thead>
<?php
foreach ($res as $rec) {
?>
	<tr>
		<td><a href="/license/<?= $rec['license_id'] ?>"><?= $rec['license_name'] ?></a> <small><?= $rec['license_code'] ?></small></td>
		<td class="r"><?= $rec['c'] ?></td>
		<td class="r"><?= sprintf('%0.2f', $rec['rev']) ?></td>
		<td class="r"><a href="/b2b/transfer?client=<?= $L['id'] ?>&amp;vendor=<?= $rec['license_id'] ?>"><i class="fas fa-file-invoice-dollar"></i></a></td>
	</tr>
<?php
}
?>
</table>
</section>
