<?php
/**
 * Cultivera HAR Reader
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Data\Import\Cultivera;

class HAR extends \OpenTHC\Data\Import\Base
{
	/**
	 * Ouptut the OpenTHC Style Data Files
	*/
	function output()
	{
		$output_idx = 1;

		$res = array();
		$data = file_get_contents($this->_source_file);
		$data = json_decode($data , true);

		// log.version; log.creator; log.pages; log.entries
		// foreach ($data['log']['pages'] as $e) {
		// 	$x = json_encode($e);
		// 	if (preg_match('/20046038218353349/', $x)) {
		// 		// echo "OG";
		// 		// var_dump($e);
		// 		// exit;
		// 	}
		// }

		foreach ($data['log']['entries'] as $e) {

			// $key_list = [ '_initiator', '_priority', '_resourceType', 'cache', 'connection', 'serverIPAddress', 'startedDateTime', 'timings', 'time' ];
			// foreach ($key_list as $k) {
			// 	unset($e[$k]);
			// }

			$e['request']['url'] = strtolower($e['request']['url']);

			// $x = json_encode($e);
			// if (preg_match('/20046038218353349/', $x)) {
			// 	echo "ENT";
			// 	var_dump($e);
			// 	exit;
			// }

			$vp = sprintf('%s/%s', $e['request']['method'], $e['request']['url']);
			echo "REQ: $vp = {$e['response']['content']['mimeType']}\n";
			if ('application/json' == $e['response']['content']['mimeType']) {
				if ( ! empty($e['response']['content']['text'])) {

					$e['has_json'] = true;

					// $path = parse_url($e['request']['url'], PHP_URL_PATH);
					// $path = trim($path, '/');
					// $path = rawurlencode($path);

					// $f = sprintf('%s/%s-%s.json', $this->_output_path, $e['startedDateTime'], $path);
					// file_put_contents($f, $e['response']['content']['text']);

				}
			}

			switch ($vp) {
				case 'GET/https://api-wa.cultiverapro.com/api/v1/facility/all-inventory-rooms':
				case 'GET/https://api-wa.cultiverapro.com/api/v1/facility/all-plant-rooms?name=':
				case 'GET/https://api-wa.cultiverapro.com/api/v1/facility/all-rooms/':
				case 'GET/https://api-wa.cultiverapro.com/api/v1/facility/get-roomsummary':
				case 'GET/https://api-wa.cultiverapro.com/api/v1/facility/get-roomtypes':
				case 'GET/https://api-wa.cultiverapro.com/api/v1/facility/get-subroom-types/':
				case 'GET/https://api-wa.cultiverapro.com/api/v1/facility/producer-inventory-rooms':

					if ($e['has_json']) {
						$res = json_decode($e['response']['content']['text'], true);
						$this->output_section($res);
					}

					break;

				case 'GET/https://api-wa.cultiverapro.com/api/v1/product/all-strains':

					if ($e['has_json']) {
						$res = json_decode($e['response']['content']['text'], true);
						$this->output_variety($res);
					}

					break;

				case 'POST/https://api-wa.cultiverapro.com/api/v1/product/get-product-batches/':

					if ($e['has_json']) {
						$res = json_decode($e['response']['content']['text'], true);
						$this->output_inventory($res['Data']);
					}

					break;

				case 'POST/https://api-wa.cultiverapro.com/api/v1/product/products':

					if ($e['has_json']) {
						$res = json_decode($e['response']['content']['text'], true);
						$this->output_product($res['Data']);
					}

					break;
			}

			// case 'https://api-wa.cultiverapro.com/api/v1/grow/get-grow-cycles':
			// case 'https://api-wa.cultiverapro.com/api/v1/plants/plants':

		}

	}

	/**
	 *
	 */
	function output_inventory($res)
	{
		foreach ($res as $src) {

			// echo "PRODUCT: " . $src['ProductName'] . "\n";
			// continue;
			// var_dump($src); exit;

			$out = [];
			$out['id'] = $src['Barcode'];
			// AltTSID
			$out['license'] = [
				'id' => '',
				'code' => $src['Location'],
			];
			$out['section'] = [
				'id' => $src['RoomId'],
				'name' => $src['Room'],
			];
			// Variety is In the Product Name
			// Sometimes between "-" and "-" chars (eg: "Product - Variety - Other")
			// Sometimes just after the "-" eg: "Product - Variety"
			// Spacing around the "-" is not consistent.
			$out['variety'] = [
				'id' => '',
				'name' => (preg_match('/ \-(.+?)(\-|$)/', $src['ProductName'], $m) ? trim($m[1]) : '-orphan-')
			];
			$out['product'] = [
				'name' => $src['ProductName'],
				'type' => [
					'id' => '',
					'name' => $src['InventoryTypeName'],
					'code' => $src['InventoryTypeCode'], // BioTrack Type Code
				]
			];
			$out['qty'] = $src['RemainingQuantity'];

			// HasQaResult
			// Thc
			// Cbd
			// Thca
			// Total
			// BatchDate

			$f = sprintf('%s/inventory-%s.json', $this->_output_path, $out['id']);
			file_put_contents($f, json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
		}

	}

	/**
	 * Fold to OpenTHC Data and Output
	 */
	function output_product($res)
	{
		// $res_src = array_change_key_case($res_src);
		echo "PRODUCT: " . count($res) . "\n";
		// $d = json_encode($res_src);
		// $f = sprintf('%s/product-dump.json', $this->_output_path);
		// file_put_contents($f, $d);
		foreach ($res as $src) {
			// var1_dump($src);
			// return;
		}

	}

	/**
	 * Fold to OpenTHC Data and Output
	 */
	function output_section($res)
	{
		foreach ($res as $src) {

		}
	}

	/**
	 * Fold to OpenTHC Data and Output
	 */
	function output_variety($res)
	{
		// $res_src = array_change_key_case($res_src);
		// var_dump($res_src);
		echo "VARITETY: " . count($res) . "\n";
		// $d = json_encode($res_src);
		// $f = sprintf('%s/variety-dump.json', $this->_output_path);
		// file_put_contents($f, $d);

	}

}
