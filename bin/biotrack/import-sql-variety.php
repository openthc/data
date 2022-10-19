<?php
/**
 * Import Variety from BioTrack SQL to OpenTHC JSON
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

function _import_variety($dbc_source, $dbc_target, $license_source, $license_target)
{

	$dbc_target->query('BEGIN');

	$sql = 'SELECT * FROM strains WHERE (deleted IS NULL OR deleted = 0) AND location = :l0';
	$res = $dbc_source->fetch($sql, [ ':l0' => $license_source['id'] ]);

	foreach ($res as $x) {

		$chk = $dbc_target->fetchOne('SELECT id FROM variety WHERE name = :v0', [
			':v0' => trim($x['strainname'])
		]);

		if (empty($chk)) {
			echo '+';
			$dbc_target->insert('variety', [
				'license_id' => $license_target['id'],
				'guid' => sprintf('V%08x', $x['id']),
				'name' => trim($x['strainname']),
				'meta' => json_encode([
					'type' => trim($x['straintype'])
				])
			]);
		} else {
			echo '.';
		}

	}

	echo "\n";

	$dbc_target->query('COMMIT');

}
