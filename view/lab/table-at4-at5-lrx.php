<?php
/**
 * Lab Result Stats
 */

$dbc = _dbc();

$arg = null;
$sql = null;

// Find AT4s by Month
$sql = <<<SQL
SELECT count(id) AS c
, date_trunc('month', created_at) AS mon
FROM cache_lot_retail
WHERE meta->>'lab_result_id' LIKE 'WAATTEST%'
 AND length(meta->>'lab_result_id') = 16
GROUP BY 2
ORDER BY 2
SQL;
$res0 = _select_via_cache($dbc, $sql, $arg);

// Find AT5s by Month
$sql = <<<SQL
SELECT count(id) AS c
, date_trunc('month', created_at) AS mon
FROM cache_lot_retail
WHERE meta->>'lab_result_id' LIKE 'WAATTEST%'
 AND length(meta->>'lab_result_id') = 17
GROUP BY 2
ORDER BY 2
SQL;
$res1 = _select_via_cache($dbc, $sql, $arg);

$sql = <<<SQL
SELECT count(id) AS c
, date_trunc('month', created_at) AS mon
FROM cache_lot_retail
WHERE meta->>'lab_result_id' NOT LIKE 'WAATTEST%'
GROUP BY 2
ORDER BY 2
SQL;
$res2 = _select_via_cache($dbc, $sql, $arg);

echo '<div class="row">';
echo '<div class="col-md-4"><p>AT4 Record Counts Per Month</p>';
echo _res_to_table($res0);
echo '</div>';
echo '<div class="col-md-4"><p>AT5 Record Counts Per Month</p>';
echo _res_to_table($res1);
echo '</div>';
echo '<div class="col-md-4"><p>PROPER Record Counts Per Month</p>';
echo _res_to_table($res2);
echo '</div>';
echo '</div>';
