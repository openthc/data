#!/usr/bin/php
<?php
/**
 * Review B2C Data
 *
 * SPDX-License-Identifier: GPL-3.0-only
 *
 * Works in chunks of 1000000 records at a time.
 * So, at least some data can get committed before any errors show up
 * You'll have to run it a few times to complete.
 */

$d = __DIR__;
$d = dirname($d);
$d = dirname($d);
require_once("$d/boot.php");

$dbc = _dbc();

$dbc->query('BEGIN');
$sql_select = 'DECLARE _b2c_review_cursor CURSOR FOR SELECT id FROM b2c_sale WHERE full_price IS NULL LIMIT 1000000';
$cur_select = $dbc->prepare($sql_select);
$cur_select->execute();

$res_select = $dbc->prepare('FETCH 1000 FROM _b2c_review_cursor');

$res_select->execute();
while ($res_select->rowCount() > 0) {

	foreach ($res_select as $b2c_sale) {

		// item_count, unit_count

		$sql = <<<SQL
		SELECT sum(coalesce(unit_count, 0) * coalesce(unit_price, 0)) AS full_price
		  , sum(unit_count) AS full_count
		FROM b2c_sale_item
		WHERE b2c_sale_id = :b0
		SQL;
		$arg = [
			':b0' => $b2c_sale['id']
		];

		$sum = $dbc->fetchRow($sql, $arg);

		$dbc->query('UPDATE b2c_sale SET full_price = :fp1, unit_count = :uc1 WHERE id = :b0', [
			':b0' => $b2c_sale['id'],
			':fp1' => floatval($sum['full_price']),
			':uc1' => floatval($sum['unit_count']),
		]);

	}

	$res_select->execute();

	echo '.';

}
$dbc->query('COMMIT');
