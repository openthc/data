<?php
/**
 * Show Details about the Data Size
 */

$_ENV['title'] = 'Database Info';

$dbc = _dbc();

// Temp tables make life easy
$sql = 'CREATE TEMP TABLE data_size (table_name varchar(64), row_count bigint, total_size bigint, index_size bigint, toast_size bigint)';
$dbc->query($sql);

// Maybe join pg_stat_user_tables?
/*
$sql = <<<SQL
SELECT schemaname,relname,n_live_tup
FROM pg_stat_user_tables
ORDER BY n_live_tup DESC
SQL;
*/

// Load Data
$sql = <<<SQL
INSERT INTO data_size
SELECT relname AS table_name
  , c.reltuples::int8 AS row_count
  , pg_total_relation_size(c.oid) AS total_size
  , pg_indexes_size(c.oid) AS index_size
  , COALESCE(pg_total_relation_size(reltoastrelid), 0) AS toast_size
FROM pg_class c
LEFT JOIN pg_namespace n ON n.oid = c.relnamespace
WHERE relkind = 'r' AND nspname = 'public'
SQL;
$dbc->query($sql);

// Fetch Data
$sql = <<<SQL
SELECT table_name, row_count, total_size, index_size, toast_size, total_size - index_size - toast_size AS table_size
 , replace(pg_size_pretty(total_size), ' bytes', ' B') AS total_nice
 , replace(pg_size_pretty(index_size), ' bytes', ' B') AS index_nice
 , replace(pg_size_pretty(toast_size), ' bytes', ' B') AS toast_nice
 , replace(pg_size_pretty(total_size - index_size - toast_size), ' bytes', ' B') AS table_nice
FROM data_size
ORDER BY table_name
SQL;

$res = $dbc->fetchAll($sql);

$sum_stat = [
	'table_size' => 0,
	'index_size' => 0,
	'toast_size' => 0,
	'total_size' => 0,
];

?>

<div class="container mt-2">

<h1><?= $_ENV['title'] ?></h1>

<table class="table table-sm">
<thead class="thead-dark">
	<tr>
		<th>Table</th>
		<th class="r">Rows</th>
		<th class="r">Table</th>
		<th class="r">Index</th>
		<th class="r">Toast</th>
		<th class="r">Total</th>
	</tr>
</thead>
<tbody>
<?php
foreach ($res as $rec) {
?>
	<tr>
		<td><?= h($rec['table_name']) ?></td>
		<td class="r"><?= number_format($rec['row_count']) ?></td>
		<td class="r"><?= h($rec['table_nice']) ?></td>
		<td class="r"><?= h($rec['index_nice']) ?></td>
		<td class="r"><?= h($rec['toast_nice']) ?></td>
		<td class="r"><?= h($rec['total_nice']) ?></td>
	</tr>
<?php

	$sum_stat['rows'] += $rec['row_count'];
	$sum_stat['table_size'] += $rec['table_size'];
	$sum_stat['index_size'] += $rec['index_size'];
	$sum_stat['toast_size'] += $rec['toast_size'];
	$sum_stat['total_size'] += $rec['total_size'];

}
?>
</tbody>
<tfoot class="thead-dark">
	<tr>
		<th></th>
		<th class="r"><?= number_format($sum_stat['rows']) ?></th>
		<th class="r"><?= ceil($sum_stat['table_size'] / 1024 / 1024 / 1024) ?> GiB</th>
		<th class="r"><?= ceil($sum_stat['index_size'] / 1024 / 1024 / 1024) ?> GiB</th>
		<th class="r"><?= ceil($sum_stat['toast_size'] / 1024 / 1024 / 1024) ?> GiB</th>
		<th class="r"><?= ceil($sum_stat['total_size'] / 1024 / 1024 / 1024) ?> GiB</th>
	</tr>
</tfoot>
</table>

</div>
