<?php
/**
 *
 */

$Lab_Result = $data['Lab_Result'];
$Lab_Result['meta'] = json_decode($Lab_Result['meta'], true);
// _ksort_r($Lab_Result);

echo '<h2>Lab Result Detail</h2>';

echo '<pre>';
echo json_encode($Lab_Result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
echo '</pre>';

echo '<hr>';
echo '<section>';
echo '<h2>Linked Lots</h2>';
echo '<p><strong>todo</strong></p>';
echo '</section>';

$dbc = _dbc();
$sql = <<<SQL
SELECT *
FROM lot_lab_result_retail_cache
WHERE lab_result_id = :lr0
ORDER BY created_at DESC, lab_result_id DESC
LIMIT 100
SQL;
$res = $dbc->fetchAll($sql, [ ':lr0' => $Lab_Result['id'] ]);
echo '<table>';
foreach ($res as $rec) {
	printf('<tr><td>%s</td><td>%s</td><td>%s</td></tr>', $rec['id'], $rec['created_at'], $rec['license_retail']);
}
echo '</table>';
