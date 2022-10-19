<?php
/**
 * Import B2B Incoming from BioTrack SQL to OpenTHC JSON
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

function _import_b2b_incoming($dbc_source, $dbc_target, $license_source, $license_target)
{

	$dbc_target->query('BEGIN');

	$sql = <<<SQL
	SELECT *
	FROM inventorytransfers
	WHERE location = :l0
	AND (deleted IS NULL OR deleted = 0)
	SQL;
	$arg = [ ':l0' => $license_source['id'] ];
	$res = $dbc_source->fetch($sql, $arg);

	$dbc_target->query('COMMIT');

}
