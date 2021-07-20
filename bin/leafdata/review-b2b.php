#!/usr/bin/php
<?php
/**
 * Update B2B Records
 */

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
	echo "_create_missing_license()\n";

	$res = $dbc->fetchAll('SELECT DISTINCT source_license_id FROM b2b_sale WHERE source_license_id NOT IN (SELECT id FROM license)');
	foreach ($res as $rec) {
		$dbc->insert('license', array(
			'id' => $rec['source_license_id'],
			'name' => sprintf('-orphan- %s', $rec['source_license_id'])
		));
	}

	// Brute Force Target Licenses into the License Table
	$res = $dbc->fetchAll('SELECT DISTINCT target_license_id FROM b2b_sale WHERE target_license_id NOT IN (SELECT id FROM license)');
	foreach ($res as $rec) {
		$dbc->insert('license', array(
			'id' => $rec['target_license_id'],
			'name' => sprintf('-orphan- %s', $rec['target_license_id'])
		));
	}

	// Link Source and Target Licenses
	// $dbc->query('ALTER TABLE ONLY b2b_sale ADD FOREIGN KEY (source_license_id) REFERENCES license(id)');
	// $dbc->query('ALTER TABLE ONLY b2b_sale ADD FOREIGN KEY (target_license_id) REFERENCES license(id)');

}

/**
 * We do one at a time, not in a transaction on purpose
 * A full SQL solution, big table update, it a lot of load small machines ($5/mo)
 * So, little steps work better there
 * If you have bigger horsepower, you can just run the UPDATE to b2b_sale directly
 */
function _update_b2b_sale_full_price($dbc)
{
	echo "_update_b2b_sale_full_price()\n";

	$res_transfer = $dbc->fetch('SELECT id FROM b2b_sale WHERE (full_price IS NULL) OR (full_price <= 0) ORDER BY id');
	printf("UPDATE: %d B2B_Sale Records\n", $res_transfer->rowCount());

	foreach ($res_transfer as $rec) {
		$sql = 'UPDATE b2b_sale SET full_price = (SELECT sum(full_price) FROM b2b_sale_item WHERE b2b_sale_item.b2b_sale_id = b2b_sale.id) WHERE b2b_sale.id = ?';
		$arg = array($rec['id']);
		$dbc->query($sql, $arg);
	}

}


// Again for Routes
function _update_b2b_path($dbc)
{
	echo "_update_b2b_path()\n";

	$api_key = \OpenTHC\Config::get('google/api_key_map');

	$add = 0;

	$sql = <<<SQL
SELECT DISTINCT
  source_license_id
  , l0.lat AS l0_lat, l0.lon AS l0_lon
  , target_license_id
  , l1.lat AS l1_lat, l1.lon AS l1_lon
FROM b2b_sale
LEFT JOIN license AS l0 ON b2b_sale.source_license_id = l0.id
LEFT JOIN license AS l1 ON b2b_sale.target_license_id = l1.id
WHERE l0.lat IS NOT NULL
  AND l0.lon IS NOT NULL
  AND l1.lat IS NOT NULL
  AND l1.lon IS NOT NULL
SQL;
	$res_transfer = $dbc->fetchAll($sql);
	echo "Routes: " . count($res_transfer) . "\n";

	foreach ($res_transfer as $rec) {

		if (empty($rec['source_license_id']) || empty($rec['target_license_id'])) {
			continue;
		}

		if (empty($rec['l0_lat']) || empty($rec['l0_lon'])) {
			echo "\nNo GEO: {$rec['source_license_id']}\n";
			continue;
		}

		if (empty($rec['l1_lat']) || empty($rec['l1_lon'])) {
			echo "\nNo GEO: {$rec['target_license_id']}\n";
			continue;
		}

		$sql = 'SELECT * FROM b2b_path WHERE source_license_id = :l0 AND target_license_id = :l1';
		$arg = [
			':l0' => $rec['source_license_id'],
			':l1' => $rec['target_license_id'],
		];
		$chk = $dbc->fetchRow($sql, $arg);
		if (empty($chk['meta'])) {

			echo '+';

			$add++;

			$arg = array(
				'key' => $api_key,
				'origin' => sprintf('%0.8f,%0.8f', $rec['l0_lat'], $rec['l0_lon']),
				'destination' => sprintf('%0.8f,%0.8f', $rec['l1_lat'], $rec['l1_lon']),
			);

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

			$dbc->query('INSERT INTO b2b_path (source_license_id, target_license_id, meta) VALUES (:l0, :l1, :m0)', [
				':l0' => $rec['source_license_id'],
				':l1' => $rec['target_license_id'],
				':m0' => json_encode([
					'distance' => [
						'm' => $m,
						// 'cost' => ((($m / 1000) / 1.609344) * $cost_per_mile),
						'nice' => $leg['distance']['text'],
					],
					'duration' => [
						's' => $s,
						// 'cost' => ($s / 3600 * $cost_per_hour),
						'nice' => $leg['duration']['text'],
					]
				])
			]);

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
	echo "_update_b2b_revenue()\n";

	$sql = 'SELECT DISTINCT source_license_id AS id FROM b2b_sale';
	$res_license = $dbc->fetchAll($sql);
	foreach ($res_license as $L) {

		$sql = <<<SQL
SELECT sum(full_price) AS rev, date_trunc('month', execute_at) AS execute_at
FROM b2b_sale
WHERE source_license_id = :l0 AND stat IN ('open', 'ready-for-pickup', 'in-transit', 'received')
GROUP BY 2
ORDER BY 2
SQL;

		$res_revenue = $dbc->fetchAll($sql, [ ':l0' => $L['id'] ]);
		if (!empty($res_revenue)) {
			foreach ($res_revenue as $rev) {
				_revenue_record_insert($L, $rev['execute_at'], 'foia-real', $rev['rev'], 0);
			}
		}

		// Summarize FOIA - VOID
		$sql = <<<SQL
SELECT sum(full_price) AS rev, date_trunc('month', execute_at) AS execute_at
FROM b2b_sale
WHERE source_license_id = :l0 AND stat LIKE 'VOID%'
GROUP BY 2
ORDER BY 2
SQL;

		$res_revenue = $dbc->fetchAll($sql, [ ':l0' => $L['id'] ]);
		if (!empty($res_revenue)) {
			foreach ($res_revenue as $rev) {
				_revenue_record_insert($L, $rev['execute_at'], 'foia-void', $rev['rev'], 0);
			}
		}

		// Summarize FOIA - FULL
		$sql = <<<SQL
SELECT sum(full_price) AS rev, date_trunc('month', execute_at) AS execute_at
FROM b2b_sale
WHERE source_license_id = :l0
GROUP BY 2
ORDER BY 2
SQL;

		$res_revenue = $dbc->fetchAll($sql, [ ':l0' => $L['id'] ]);
		if (!empty($res_revenue)) {
			foreach ($res_revenue as $rev) {
				_revenue_record_insert($L, $rev['execute_at'], 'foia-full', $rev['rev'], 0);
			}
		}

	}
}
