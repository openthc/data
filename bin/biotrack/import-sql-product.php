<?php
/**
 * Import Product from BioTrack SQL to OpenTHC JSON
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

function _import_product($dbc_source, $dbc_target, $license_source, $license_target)
{

	$dbc_target->query('BEGIN');

	// Product Type Map
	$product_type_map = \OpenTHC\CRE\BioTrack::kindList();

	$sql = <<<SQL
	SELECT *
	FROM products
	WHERE location = :l0
	AND (deleted IS NULL OR deleted = 0)
	SQL;
	$arg = [ ':l0' => $license_source['id'] ];
	$res = $dbc_source->fetch($sql, $arg);

	foreach ($res as $x) {

		$x['name'] = trim($x['name']);

		$chk = $dbc_target->fetchOne('SELECT id FROM product WHERE guid = :g0', [
			':g0' => sprintf('BT%08d', $x['id'])
		]);
		if (empty($chk)) {
			echo '+';
			$p1 = [
				'id' => _ulid(),
				'license_id' => $license_target['id'],
				'product_type_id' => '018NY6XC00PR0DUCTTYPE00000', // \OpenTHC\CRE\BioTrack::typeMap($x['productcategory']),
				'guid' => sprintf('BT%08d', $x['id']),
				'name' => $x['name'],
				'stub' => substr(_text_stub($x['name']),0, 128),
				'created_at' => $x['created'] ?: $x['created_on'], // Two?
				'meta' => json_encode([
					'@origin' => 'BIOTRACK_IMPORT',
					'@source' => $x,
				])
			];
			$dbc_target->insert('product', $p1);
		} else {
			echo '.';
		}

	}

	echo "\n";

	$dbc_target->query('COMMIT');

}
