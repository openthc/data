<?php
/**
 * Import Inventory from BioTrack SQL to OpenTHC JSON
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

function _import_inventory($dbc_source, $dbc_target, $license_source, $license_target)
{

	$dbc_target->query('BEGIN');

	$sql = <<<SQL
	SELECT *
	FROM inventory
	WHERE location = :l0
	AND (deleted IS NULL OR deleted = 0)
	AND quantity IS NOT NULL
	AND quantity >= 0
	ORDER BY id
	SQL;
	$arg = [ ':l0' => $license_source['id'] ];
	$res = $dbc_source->fetch($sql, $arg);

	foreach ($res as $x) {

		$x['strain'] = trim($x['strain']);

		$chk = $dbc_target->fetchOne('SELECT id FROM inventory WHERE guid = :pk', [
			':pk' => $x['id']
		]);
		if (empty($chk)) {
			echo '+';
			$i1 = [
				'id' => _ulid(),
				'guid' => $x['id'],
				'license_id' => $license_target['id'],
				'product_id' => '018NY6XC00PR00000000000001',
				'variety_id' => $dbc_target->fetchOne('SELECT id FROM variety WHERE name = :v0', [ ':v0' => $x['strain'] ]) ?: '018NY6XC00VAR1ETY000000000',
				'section_id' => $dbc_target->fetchOne('SELECT id FROM section WHERE guid = :r0', [ ':r0' => sprintf('I%04x', $x['currentroom']) ]) ?: '018NY6XC00SECT10N000000000',
				'stat' => 200,
				'qty' => $x['quantity'],
				'created_at' => $x['created'] ?: $x['created_on'], // Two?
				'meta' => json_encode([
					'@origin' => 'BIOTRACK_IMPORT',
					'@source' => $x,
				])
			];
			$dbc_target->insert('inventory', $i1);
		} else {
			echo '.';
			// echo sprintf('BT%08d', $x['productid']);
			// echo "\n";
			$p0 = $dbc_target->fetchOne('SELECT id FROM product WHERE guid = :g0', [
				':g0' => sprintf('BT%08d', $x['productid'])
			]);
			if ($p0) {
				$dbc_target->query('UPDATE inventory SET product_id = :p0 WHERE id = :i0', [
					':i0' => $chk,
					':p0' => $p0,
				]);
			}
		}

	}

	echo "\n";

	$dbc_target->query('COMMIT');

}
