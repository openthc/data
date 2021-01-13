#!/usr/bin/php
<?php
/**
 * Update License
 */

use Edoceo\Radix\DB\SQL;
use Edoceo\Radix\Net\HTTP;

require_once(__DIR__ . '/boot.php');

$dbc = _dbc();

$dir = new OpenTHC\Service('dir');

$idx = 0;
$res_license = $dbc->fetchAll('SELECT id, code, lat, lon, address_meta, company_id FROM license');
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

	echo '.';

	if (!empty($l0['lat']) && !empty($l0['lon'])) {
		continue;
	}

	// Special Key to Get Revenue
	$res = $dir->get(sprintf('/api/license/%s', $l0['id']));
	switch ($res->getStatusCode()) {
	case 200:

		$res = $res->getBody()->getContents();
		$l1 = json_decode($res, true);
		// var_dump($l1);

		$chk = $dbc->fetchOne('SELECT id FROM company WHERE id = ?', [ $l1['company']['id'] ]);
		if (empty($chk)) {
			$dbc->insert('company', [
				'id' => $l1['company']['id'],
				'name' => $l1['name'],
			]);
		}

		// Does Up-Stream have a Good Geo?
		if (empty($l1['geo'])) {
			echo "$idx: {$l0['id']} No Source GEO\n";
		}

		$arg = array(
			':id' => $l0['id'],
			':com' => $l1['company']['id'],
			':lat' => $l1['geo']['lat'],
			':lon' => $l1['geo']['lon'],
			':am' => $l1['address_meta'],
		);

		$sql = 'UPDATE license SET lat = :lat, lon = :lon, address_meta = :am, company_id = :com WHERE id = :id';
		$dbc->query($sql, $arg);

		echo '^';

	}
}
