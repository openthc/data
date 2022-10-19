<?php
/**
 * Import Section from BioTrack SQL to OpenTHC JSON
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

function _import_section($dbc_source, $dbc_target, $license_source, $license_target)
{

	$dbc_target->query('BEGIN');

	$sql = <<<SQL
	SELECT id, roomname AS name, 'Plant' as type, location
	FROM rooms
	WHERE location = :l0
	UNION ALL
	SELECT id, roomname AS name, 'Inventory' as type, location
	FROM inventoryrooms
	WHERE location = :l0
	SQL;
	$res = $dbc_source->fetch($sql, [ ':l0' => $license_source['id'] ]);

	foreach ($res as $x) {

		$x['name'] = trim($x['name']);

		$chk = $dbc_target->fetchOne('SELECT id FROM section WHERE license_id = :l0 AND name = :n0', [
			':l0' => $license_target['id'],
			':n0' => $x['name'],
		]);

		if (empty($chk)) {
			echo '+';
			$dbc_target->insert('section', [
				'license_id' => $license_target['id'],
				'guid' => sprintf('%s%04x', substr($x['type'], 0, 1), $x['id']),
				'name' => $x['name'],
				'type' => $x['type'],
			]);
		} else {
			echo '.';
		}

	}

	echo "\n";

	$dbc_target->query('COMMIT');
}
