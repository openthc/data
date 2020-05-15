<?php
/**
 * Negative Quantity Lots
 */

$_ENV['h1'] = $_ENV['title'] = 'Lots :: Negative Quantity';

session_write_close();

$dbc = _dbc();

$sql = <<<SQL
SELECT count(lot.id) AS c
, license.id
, license.name
FROM lot
JOIN license ON lot.license_id = license.id
WHERE lot.qty < 0
GROUP BY license.id, license.name
ORDER BY 1 DESC
SQL;

// $res = $dbc->fetchAll($sql);
$res = _select_via_cache($dbc, $sql, null);

?>

<div class="container">
<p>Count of Lots w/Negative Quantity by License</p>
<?= _res_to_table($res) ?>
</div>
