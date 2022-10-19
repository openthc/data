<?php
/**
 * Import Crop from BioTrack SQL to OpenTHC JSON
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

function _import_crop($dbc_source, $dbc_target, $license_source, $license_target)
{

	$dbc_target->query('BEGIN');

	$sql = <<<SQL
	SELECT *
	FROM plants
	WHERE location = :l0
	AND (deleted IS NULL OR deleted = 0)

	SQL;
	$arg = [ ':l0' => $license_source['id'] ];
	$res = $dbc_source->fetch($sql, $arg);

	foreach ($res as $x) {

		// Only accept state '0'
		if ( ! empty($x['state'])) {
			continue;
		}

		$x['strain'] = trim($x['strain']);

		// $chk = $dbc_target->fetchOne('SELECT id FROM plant WHERE guid IN (:pk0, :pk1)', [
		// 	':pk0' => $x['custom_data'],
		// 	':pk1' => $x['id']
		// ]);
		$chk = $dbc_target->fetchOne('SELECT id FROM plant WHERE guid IN (:pk0)', [
			':pk0' => $x['id']
		]);

		if (empty($chk)) {
			echo '+';
			// custom_data
			$p1 = [
				'id' => _ulid(),
				'guid' => $x['custom_data'] ?: $x['id'],
				'license_id' => $license_target['id'],
				'variety_id' => $dbc_target->fetchOne('SELECT id FROM variety WHERE name = :v0', [ ':v0' => $x['strain'] ]) ?: '018NY6XC00VAR1ETY000000000',
				'section_id' => $dbc_target->fetchOne('SELECT id FROM section WHERE guid = :r0', [ ':r0' => sprintf('P%04x', $x['room']) ]) ?: '018NY6XC00SECT10N000000000',
				'stat' => 200,
				'qty' => 1,
				'created_at' => $x['created_on'],
				'meta' => json_encode([
					'@origin' => 'BIOTRACK_IMPORT',
					'@source' => $x,
				])
			];

			$dbc_target->insert('plant', $p1);
		} else {
			echo '.';
		}

	}

	echo "\n";

	$dbc_target->query('COMMIT');

}
