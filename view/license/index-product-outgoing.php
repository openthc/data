<?php
?>

<h2>Product Sold</h2>
<?php
switch ($L['type']) {
case 'R':
	// Retail!
	// Stuff They Are Selling
	$sql = <<<SQL
SELECT sum(qty) AS q, sum(unit_price) AS p
FROM b2c_sale_item WHERE b2c_sale_id IN (SELECT id FROM b2c_sale WHERE license_id = :l0)
LIMIT 100
SQL;

	$res_outgoing = _select_via_cache($dbc, $sql, $arg);

	break;
default:

	// Stuff They Are Selling
	$sql = <<<SQL
SELECT sum(qom_tx) AS q, sum(full_price) AS p
FROM b2b_sale_item WHERE transfer_id IN (SELECT id FROM b2b_sale WHERE license_id_source = :l0)
LIMIT 100
SQL;

	$res_outgoing = _select_via_cache($dbc, $sql, $arg);

}
