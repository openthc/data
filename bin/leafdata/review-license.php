#!/usr/bin/php
<?php
/**
 * Update License
 */

use Edoceo\Radix\DB\SQL;
use Edoceo\Radix\Net\HTTP;

require_once(__DIR__ . '/boot.php');

$dbc = _dbc();
$off = 2128;

$sql = sprintf('SELECT id, code, name, lat, lon, address_meta, company_id FROM license ORDER BY id OFFSET %d', $off);
$res_license = $dbc->fetchAll($sql);
$idx = $off;
$max = $off + count($res_license);

$dir = new OpenTHC\Service\OpenTHC('dir');

foreach ($res_license as $l0) {

	$idx++;

	// Skip these
	switch ($l0['id']) {
	case 'LOST.':
	case 'LOST.J413199':
	case 'LOST.J416763':
	case 'LOST.M421873':
	case 'WAWA1.MM1':
	case 'WAWA1.MM2':
	case 'WAWA1.MM3':
	case 'WAWA1.MM4':
	case 'WAWA1.MM5':
	case 'WAWA1.MM1V5':
		continue 2;
		break;
	}

	if (!empty($l0['lat']) && !empty($l0['lon'])) {
		continue;
	}

	printf('%04d/%04d:%02d%%: %s: %s; ', $idx, $max, $idx / $max * 100, $l0['id'], $l0['name']);

	$res = $dir->get(sprintf('/api/license/%s', $l0['id']));
	if (!empty($res['data']['id'])) {

		$l1 = $res['data'];

		$chk = $dbc->fetchOne('SELECT id FROM company WHERE id = ?', [ $l1['company']['id'] ]);
		if (empty($chk)) {
			echo '+';
			$dbc->insert('company', [
				'id' => $l1['company']['id'],
				'name' => $l1['name'],
			]);
		}

		// Does Up-Stream have a Good Geo?
		if (empty($l1['geo'])) {
			echo 'NO GEO; ';
		}

		$arg = array(
			':id' => $l0['id'],
			':com' => $l1['company']['id'],
			':lat' => $l1['geo']['lat'],
			':lon' => $l1['geo']['lon'],
			':am' => $l1['address_meta'],
		);

		echo '^';

		$sql = 'UPDATE license SET lat = :lat, lon = :lon, address_meta = :am, company_id = :com WHERE id = :id';
		$dbc->query($sql, $arg);

	}

	echo "\n";
}
