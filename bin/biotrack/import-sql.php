#!/usr/bin/php
<?php
/**
 * Import from BioTrack SQL to OpenTHC JSON
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

$d = __DIR__;
$d = dirname($d);
$d = dirname($d);
require_once("$d/boot.php");

require_once(__DIR__ . '/import-sql-section.php');
require_once(__DIR__ . '/import-sql-variety.php');
require_once(__DIR__ . '/import-sql-product.php');
require_once(__DIR__ . '/import-sql-inventory.php');
require_once(__DIR__ . '/import-sql-crop.php');
require_once(__DIR__ . '/import-sql-b2b-incoming.php');
require_once(__DIR__ . '/import-sql-b2b-outgoing.php');
require_once(__DIR__ . '/import-sql-b2c.php');

// Options
$opt = getopt('', [
	'source:',
	'target:',
	'license:',
	'section:',
]);


// Database Connections
$dbc_source = new \Edoceo\Radix\DB\SQL($opt['source']);
$dbc_target = new \Edoceo\Radix\DB\SQL($opt['target']);

// Source License
$license_source = $dbc_source->fetchRow('SELECT * FROM locations WHERE licensenum = :l0', [ ':l0' => $opt['license'] ]);
if (empty($license_source['id'])) {
	echo "Cannot find Source License\n";
	exit(1);
}
$license_target = $dbc_target->fetchRow('SELECT * FROM license WHERE code = :l0', [ ':l0' => $opt['license'] ]);
if (empty($license_target['id'])) {
	echo "Cannot find Target License\n";
	exit(1);
}

_import_section($dbc_source, $dbc_target, $license_source, $license_target);
_import_variety($dbc_source, $dbc_target, $license_source, $license_target);
_import_product($dbc_source, $dbc_target, $license_source, $license_target);
_import_inventory($dbc_source, $dbc_target, $license_source, $license_target);
_import_crop($dbc_source, $dbc_target, $license_source, $license_target);
_import_b2b_incoming($dbc_source, $dbc_target, $license_source, $license_target);
_import_b2b_outgoing($dbc_source, $dbc_target, $license_source, $license_target);
_import_b2c($dbc_source, $dbc_target, $license_source, $license_target);
