<?php
/**
 * Show B2B Incoming for a License
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

?>

<h2>Product Purchased</h2>
<?php

// Stuff They Are Selling
$sql = <<<SQL
SELECT count(unit_count_tx) AS c, sum(full_price) AS r
FROM b2b_sale_item WHERE b2b_sale_id IN (SELECT id FROM b2b_sale WHERE target_license_id = :l0)
LIMIT 100
SQL;

$arg = [
	':l0' => $L['id'],
];

$res_incoming = _select_via_cache($dbc, $sql, $arg);
