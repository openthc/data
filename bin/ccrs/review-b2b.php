#!/usr/bin/php
<?php
/**
 * Review B2B Data
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

$d = __DIR__;
$d = dirname($d);
$d = dirname($d);
require_once("$d/boot.php");

$dbc = _dbc();

// Too big
// $res_b2b_sale = $dbc->fetch('SELECT id FROM b2b_sale WHERE full_price IS NULL');

// Use this little cursor to work in faster groups
$dbc->query('BEGIN');
$sql_select = 'DECLARE _b2b_review_cursor CURSOR FOR SELECT id FROM b2b_sale WHERE full_price IS NULL';
$cur_select = $dbc->prepare($sql_select);
$cur_select->execute();

$res_select = $dbc->prepare('FETCH 1000 FROM _b2b_review_cursor');

$res_select->execute();
while ($res_select->rowCount() > 0) {

	foreach ($res_select as $b2b_sale) {

		// item_count, unit_count

		$sql = 'SELECT sum(coalesce(unit_count_tx, 0) * coalesce(unit_price, 0)) FROM b2b_sale_item WHERE b2b_sale_id = :b0';
		$arg = [
			':b0' => $b2b_sale['id']
		];
		$sum = $dbc->fetchOne($sql, $arg);
		$dbc->query('UPDATE b2b_sale SET full_price = :fp1 WHERE id = :b0', [
			':b0' => $b2b_sale['id'],
			':fp1' => $sum,
		]);

	}

	$res_select->execute();

	echo '.';

}
$dbc->query('COMMIT');
