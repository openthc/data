#!/usr/bin/php
<?php
/**
 * Update B2B Records
 */

// use Edoceo\Radix\DB\SQL;
use Edoceo\Radix\Net\Curl;

require_once(__DIR__ . '/boot.php');
require_once(APP_ROOT . '/bin/usa-wa/boot.php');

$dbc = _dbc();

// $cost_per_mile = 0.75;
// $cost_per_hour = 20;

_create_missing_license($dbc);
_update_b2b_sale_full_price($dbc);
_update_b2b_revenue($dbc);
_update_b2b_path($dbc);



// Brute Force Origin Licenses into the License Table
function _create_missing_license($dbc)
{
	$res = $dbc->fetchAll('SELECT DISTINCT license_id_source FROM b2b_sale WHERE license_id_source NOT IN (SELECT id FROM license)');
	foreach ($res as $rec) {
		$dbc->insert('license', array(
			'id' => $rec['license_id_source'],
			'name' => sprintf('-unknown- %s', $rec['license_id_source'])
		));
	}

	// Brute Force Target Licenses into the License Table
	$res = $dbc->fetchAll('SELECT DISTINCT license_id_target FROM b2b_sale WHERE license_id_target NOT IN (SELECT id FROM license)');
	foreach ($res as $rec) {
		$dbc->insert('license', array(
			'id' => $rec['license_id_target'],
			'name' => sprintf('-unknown- %s', $rec['license_id_target'])
		));
	}

	// Link Source and Target Licenses
	// $dbc->query('ALTER TABLE ONLY b2b_sale ADD FOREIGN KEY (license_id_source) REFERENCES license(id)');
	// $dbc->query('ALTER TABLE ONLY b2b_sale ADD FOREIGN KEY (license_id_target) REFERENCES license(id)');

}

function _update_b2b_sale_full_price($dbc)
{
	echo "Updating b2b_sale.full_price\n";
	$res_transfer = $dbc->fetchAll('SELECT id FROM b2b_sale WHERE (full_price IS NULL) OR (full_price <= 0) ORDER BY id');
	foreach ($res_transfer as $rec) {
		// echo '.';
		$sql = 'UPDATE b2b_sale SET full_price = (SELECT sum(full_price) FROM b2b_sale_item WHERE b2b_sale_item.transfer_id = b2b_sale.id) WHERE b2b_sale.id = ?';
		$arg = array($rec['id']);
		$dbc->query($sql, $arg);
	}
}


// Again for Routes
function _update_b2b_path($dbc)
{
	echo "Updating b2b_route\n";

	$map_api_key = \OpenTHC\Config::get('google/map_api_key');

	$add = 0;

	$sql = <<<SQL
SELECT DISTINCT
license_id_source
, l0.lat AS l0_lat, l0.lon AS l0_lon
, license_id_target
, l1.lat AS l1_lat, l1.lon AS l1_lon
FROM b2b_sale
LEFT JOIN license AS l0 ON b2b_sale.license_id_source = l0.id
LEFT JOIN license AS l1 ON b2b_sale.license_id_target = l1.id
SQL;
	$res_transfer = $dbc->fetchAll($sql);
	echo "Routes: " . count($res_transfer) . "\n";
	foreach ($res_transfer as $rec) {

		if (empty($rec['license_id_source']) || empty($rec['license_id_target'])) {
			continue;
		}

		if (empty($rec['l0_lat']) && empty($rec['l0_lon'])) {
			echo "No GEO: {$rec['license_id_source']}\n";
			continue;
		}

		if (empty($rec['l1_lat']) && empty($rec['l1_lon'])) {
			echo "No GEO: {$rec['license_id_target']}\n";
			continue;
		}

		$sql = 'SELECT * FROM b2b_path WHERE supply_license_id = :l0 AND demand_license_id = :l1';
		$arg = [
			':l0' => $rec['license_id_source'],
			':l1' => $rec['license_id_target'],
		];
		$chk = $dbc->fetchRow($sql, $arg);
		if (empty($chk['meta'])) {

			echo '+';
			$add++;

			$arg = array(
				'key' => $map_api_key,
				'origin' => sprintf('%0.8f,%0.8f', $rec['l0_lat'], $rec['l0_lon']),
				'destination' => sprintf('%0.8f,%0.8f', $rec['l1_lat'], $rec['l1_lon']),
			);
			// print_r($arg);

			$url = 'https://maps.googleapis.com/maps/api/directions/json?' . http_build_query($arg);
			$req = _curl_init($url);
			$buf = curl_exec($req);
			$buf = json_decode($buf, true);

			$leg = $buf['routes'][0]['legs'][0];
			//var_dump($leg);
			if (empty($leg)) {
				print_r($arg);
				print_r($buf);
				continue;
			}

			$m = $leg['distance']['value']; // meters
			$s = $leg['duration']['value']; // seconds

			$dbc->query('INSERT INTO b2b_path (supply_license_id, demand_license_id, meta) VALUES (:l0, :l1, :m0)', [
				':l0' => $rec['license_id_source'],
				':l1' => $rec['license_id_target'],
				':m0' => json_encode([
					'distance' => [
						'm' => $m,
						'nice' => $leg['distance']['text'],
					],
					'duration' => [
						's' => $s,
						'nice' => $leg['duration']['text'],
					]
				])
			]);

		// 	$route_meta = array(
		// 		'distance' => array(
		// 			'cost' => ((($m / 1000) / 1.609344) * $cost_per_mile),
		// 		),
		// 		'duration' => array(
		// 			'cost' => ($s / 3600 * $cost_per_hour),
		// 		),
		// 	);

		} else {
			echo '.';
		}

	}

	echo "\nadd:$add\n";
}

/*
SELECT DISTINCT stat FROM b2b_sale
open
in-transit
ready-for-pickup
received
VOID-open
VOID-in-transit
VOID-ready-for-pickup
VOID-received
*/
function _update_b2b_revenue($dbc)
{
	$sql = 'SELECT DISTINCT license_id_source AS id FROM b2b_sale';
	$res_license = $dbc->fetchAll($sql);
	foreach ($res_license as $L) {

		$sql = <<<SQL
SELECT sum(full_price) AS rev, date_trunc('month', execute_at) AS dts
FROM b2b_sale
WHERE license_id_source = :l0 AND stat IN ('in-transit', 'open', 'received')
GROUP BY 2
ORDER BY 2
SQL;

		$res_revenue = $dbc->fetchAll($sql, [ ':l0' => $L['id'] ]);
		if (!empty($res_revenue)) {
			foreach ($res_revenue as $rev) {
				_revenue_record_insert($L, $rev['dts'], 'foia-real', $rev['rev'], 0);
			}
		}

		// Summarize FOIA - VOID
		$sql = <<<SQL
SELECT sum(full_price) AS rev, date_trunc('month', execute_at) AS dts
FROM b2b_sale
WHERE license_id_source = :l0 AND stat LIKE 'VOID%'
GROUP BY 2
ORDER BY 2
SQL;

		$res_revenue = $dbc->fetchAll($sql, [ ':l0' => $L['id'] ]);
		if (!empty($res_revenue)) {
			foreach ($res_revenue as $rev) {
				_revenue_record_insert($L, $rev['dts'], 'foia-void', $rev['rev'], 0);
			}
		}

		// Summarize FOIA - FULL
		$sql = <<<SQL
SELECT sum(full_price) AS rev, date_trunc('month', execute_at) AS dts
FROM b2b_sale
WHERE license_id_source = :l0
GROUP BY 2
ORDER BY 2
SQL;

		$res_revenue = $dbc->fetchAll($sql, [ ':l0' => $L['id'] ]);
		if (!empty($res_revenue)) {
			foreach ($res_revenue as $rev) {
				_revenue_record_insert($L, $rev['dts'], 'foia-full', $rev['rev'], 0);
			}
		}

	}
}
