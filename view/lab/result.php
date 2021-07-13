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
