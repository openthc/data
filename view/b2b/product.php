<?php
/**
 *
 */

$dbc = _dbc();

$Vendor = $dbc->fetchRow('SELECT * FROM license WHERE id = ?', [ $_GET['vendor'] ]);
if (empty($Vendor['id'])) {
	_exit_text('Invalid License', 400);
}


// CREATE TABLE report_b2b_license_product_sold AS
// SELECT count(id) AS lot_count
// , date_trunc('month', execute_at) AS execute_at
// , sum(qty_tx) AS qty_tx
// , sum(qty_rx) AS qty_rx
// , sum(sale_item_full_price) AS full_price
// , source_license_id
// , product_type
// , product_name
// FROM b2b_sale_item_full
// WHERE sale_item_full_price > 0
// GROUP BY date_trunc('month', execute_at), source_license_id, product_type, product_name

$sql = <<<SQL
SELECT sum(lot_count) AS lot_count
, execute_at
, sum(full_price) AS full_price
FROM report_b2b_product_sold
WHERE product_name = :p0
GROUP BY execute_at
ORDER BY execute_at
SQL;

$sql = <<<SQL
SELECT execute_at
, lot_count
, qty_tx
, full_price
, full_price / qty_tx AS ppg_ppu
FROM report_b2b_product_sold
WHERE product_name = :p0
SQL;

$sql = <<<SQL
SELECT id AS b2b_sale_id
, execute_at
, source_license_id
, target_license_id
, lot_id_source
, lot_id_target
, qty_tx
, qty_rx
, sale_full_price
, sale_unit_price
, package_size
FROM b2b_sale_item_full
WHERE product_name = :p0 AND source_license_id = :v0
ORDER BY execute_at DESC
SQL;

$arg = [
	':p0' => $_GET['name'],
	':v0' => $Vendor['id']
];

$res = _select_via_cache($dbc, $sql, $arg);
_res_to_table($res);

// -- $sql = <<<SQL
// -- WHERE source_license_id = :l0
// -- GROUP BY execute_at, product_name
// -- ORDER BY execute_at, lot_count
// -- SQL;
// -- $res = _select_via_cache($dbc, $sql, [
// -- 	':l0' => $L['id']
// -- ]);
