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

$res_license = $dbc->fetchAll('SELECT id, code FROM license');
foreach ($res_license as $license) {

	$arg = [
		':l0' => $license['id'],
		':c0' => $license['code'],
	];

	$dbc->query('UPDATE product SET license_id = :c1 WHERE license_id = :l0', $arg);
	$dbc->query('UPDATE inventory SET license_id = :c1 WHERE license_id = :l0', $arg);

	$dbc->query('UPDATE b2b_sale SET source_license_id = :c1 WHERE source_license_id = :l0', $arg);
	$dbc->query('UPDATE b2b_sale SET target_license_id = :c1 WHERE target_license_id = :l0', $arg);

	$dbc->query('UPDATE b2c_sale SET license_id = :c1 WHERE license_id = :l0', $arg);

}
