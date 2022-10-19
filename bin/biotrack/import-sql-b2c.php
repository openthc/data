<?php
/**
 * Import B2C from BioTrack SQL to OpenTHC JSON
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

function _import_b2c($dbc_source, $dbc_target, $license_source, $license_target)
{

	$dbc_target->query('BEGIN');

	$sql = <<<SQL
	SELECT *
	FROM sales
	WHERE location = :l0
	AND (deleted IS NULL OR deleted = 0)
	SQL;
	$arg = [ ':l0' => $license_source['id'] ];
	$res = $dbc_source->fetch($sql, $arg);

	foreach ($res as $x) {


	}

	echo "\n";

	$dbc_target->query('COMMIT');

}
