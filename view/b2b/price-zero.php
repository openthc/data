<?php
/**
 * Lots that were sold to Retail for $0
 */

$sql = <<<SQL
SELECT count(id) AS count
FROM b2b_sale_item
WHERE target_lot_id IS NOT NULL
 AND unit_price = 0
 AND target_lot_id LIKE 'WAR%';
SQL;

$res = _select_via_cache($dbc, $sql, $arg);
