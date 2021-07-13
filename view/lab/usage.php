<?php
/**
 * Show Most Used Lab Results
 */

$_ENV['h1'] = $_ENV['title'] = 'Lab Results :: Usage';

$dbc = _dbc();

// Create Cache Table
$sql = <<<SQL
CREATE TABLE wip_lot_retail_lab_result AS
  SELECT * FROM lot
  WHERE id LIKE 'WAR%'
   AND created_at >= :dt0
SQL;
// $dbc->query($sql, [ ':dt0' => DATE_ALPHA ]);

// Count of Licenses w/Attested
$sql = <<<SQL
SELECT count(id) AS lot_count, license_retail
FROM wip_lot_retail_lab_result
WHERE lab_result_id LIKE 'WAATTEST%'
GROUP BY license_retail
ORDER BY 1 DESC
LIMIT 25
SQL;
$res0 = _select_via_cache($dbc, $sql, $arg);

// Count of Licenses w/o Attested
$sql = <<<SQL
SELECT count(id) AS lot_count, license_retail
FROM wip_lot_retail_lab_result
WHERE lab_result_id NOT LIKE 'WAATTEST%'
GROUP BY license_retail
ORDER BY 1 DESC
LIMIT 25
SQL;
$res1 = _select_via_cache($dbc, $sql, $arg);

echo App\UI::lab_tabs();

echo '<div class="row">';
echo '<div class="col-md-6"><p>Count lots with WAATTEST records, at Retail, Top 25</p>';
echo _res_to_table($res0);
echo '</div>';
echo '<div class="col-md-6"><p>Count lots with PROPER, at Retail, Top 25</p>';
echo _res_to_table($res1);
echo '</div>';
echo '</div>';
echo '<hr>';

// Top Used WAATTEST and PROPER
$sql = <<<SQL
SELECT count(id) AS lab_result_count
, lab_result_id
FROM wip_lot_retail_lab_result
WHERE lab_result_id LIKE 'WAATTEST%'
GROUP BY lab_result_id
ORDER BY 1 DESC
LIMIT 25
SQL;
$res0 = _select_via_cache($dbc, $sql, $arg);

$sql = <<<SQL
SELECT count(id) AS lab_result_count
, lab_result_id AS lab_result_id
FROM wip_lot_retail_lab_result
WHERE lab_result_id NOT LIKE 'WAATTEST%'
GROUP BY lab_result_id
ORDER BY 1 DESC
LIMIT 25
SQL;
$res1 = _select_via_cache($dbc, $sql, $arg);

echo '<div class="row">';
echo '<div class="col-md-6"><p>Top 25 count WAATTEST results, at Retail; ie: the most used WAATTEST values</p>';
echo _res_to_table($res0);
echo '</div>';
echo '<div class="col-md-6"><p>Top 25 count PROPER results, at Retail; ie: the most used Lab Results</p>';
echo _res_to_table($res1, [
	'lab_result_id' => function($v) {
		return sprintf('<td><a href="/lab/result/%s">%s</a></td>', $v, $v);
	}
]);
echo '</div>';
echo '</div>';
echo '<hr>';

// require_once(__DIR__ . '/table-at4-at5-lrx.php');

// select count(id) from wip_lot_retail_lab_result where length(meta->>'lab_result_id') = 17;
// select count(id) from wip_lot_retail_lab_result where length(meta->>'lab_result_id') = 16;

// SELECT count(id) AS c, meta->>'lab_result_id' AS lab_result_id, min(created_at) AS min_date, max(created_at) AS max_date
// FROM wip_lot_retail_lab_result
// GROUP BY meta->>'lab_result_id'
// ORDER BY 1 DESC;

// SELECT id, license_id, meta->>'external_id' FROM wip_lot_retail_lab_result WHERE meta->>'lab_result_id' = 'WAL21.LR1243A' ORDER BY created_at DESC;

// $res = _select_via_cache($dbc, $sql, $arg);
