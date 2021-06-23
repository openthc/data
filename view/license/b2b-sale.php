<?php
/**
 * B2B Sale Report
 */

$dbc = _dbc();

$License = $dbc->fetchRow('SELECT * FROM license WHERE id = :l', [ ':l' => $_GET['id'] ]);
if (empty($License['id'])) {
	_exit_text('Invalid License', 400);
}

$_ENV['title'] = sprintf('License :: %s - %s :: B2B', $License['code'], $License['name']);
$_ENV['h1'] = $_ENV['title'];


$sql = <<<SQL
SELECT b2b_sale.*
, license_source.name AS license_source_name
, license_target.name AS license_target_name
FROM b2b_sale
JOIN license AS license_source ON b2b_sale.source_license_id = license_source.id
JOIN license AS license_target ON b2b_sale.target_license_id = license_target.id
WHERE (source_license_id = :l0 OR target_license_id = :l0) AND b2b_sale.full_price > 0
ORDER BY execute_at
SQL;

$arg = [ ':l0' => $License['id'] ];
// $res = $dbc->fetchAll($sql, $arg);
$res = _select_via_cache($dbc, $sql, $arg);

?>

<table class="table table-sm">
<thead class="thead-dark">
<tr>
	<th>Date</th>
	<th>Mode</th>
	<th>Source</th>
	<th>Target</th>
	<th style="text-align: right;">Expense</th>
	<th style="text-align: right;">Revenue</th>
</tr>
</thead>
<tbody>
<?php
$expense = 0;
$revenue = 0;
foreach ($res as $rec) {

	echo '<tr>';

	printf('<td>%s</td><td>%s</td>', _date('m/d/y', $rec['execute_at']), $rec['stat']);

	if ($License['id'] == $rec['source_license_id']) {
		// Supply Side
		printf('<td>%s</td><td style="font-weight:700;"><a href="/license/%s">%s</a></td>'
			, $rec['license_source_name']
			, $rec['target_license_id']
			, $rec['license_target_name']
		);
		printf('<td></td><td style="font-weight:700; text-align: right;">%s</td>', number_format($rec['full_price'], 2));
		$revenue += $rec['full_price'];
	} elseif ($License['id'] == $rec['target_license_id']) {
		// Demand Side
		printf('<td style="font-weight:700;"><a href="/license/%s">%s</a></td><td>%s</td>'
			, $rec['source_license_id']
			, $rec['license_source_name']
			, $rec['license_target_name']
		);
		printf('<td style="font-weight:700; text-align: right;">%s</td><td></td>', number_format($rec['full_price'], 2));
		$expense += $rec['full_price'];
	}

	echo '</tr>';

}
?>
</tbody>
<tfoot>
	<tr>
		<th></th>
		<th></th>
		<th></th>
		<th style="text-align:right;"><?= number_format($expense, 2); ?></th>
		<th style="text-align:right;"><?= number_format($revenue, 2); ?></th>
	</tr>
</tfoot>
</table>
