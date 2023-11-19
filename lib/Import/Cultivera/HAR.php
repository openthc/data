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

			$vp = sprintf('%s%s', $e['request']['method'], parse_url($e['request']['url'], PHP_URL_PATH));
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
			echo "REQ: $vp = {$e['response']['content']['mimeType']} " . strlen($e['response']['content']['text']) . "\n";

			switch ($vp) {
				case 'GET/api/v1/facility/all-inventory-rooms':
				case 'GET/api/v1/facility/all-plant-rooms?name=':
				case 'GET/api/v1/facility/all-rooms/':
				case 'GET/api/v1/facility/get-roomsummary':
				case 'GET/api/v1/facility/get-roomtypes':
				case 'GET/api/v1/facility/get-subroom-types/':
				case 'GET/api/v1/facility/producer-inventory-rooms':

					if ($e['has_json']) {
						$res = json_decode($e['response']['content']['text'], true);
						$this->output_section($res);
					}

					break;

				case 'GET/api/v1/product/all-strains':

					if ($e['has_json']) {
						$res = json_decode($e['response']['content']['text'], true);
						$this->output_variety($res);
					}

					break;

				case 'GET/api/v1/product/get-product-batch-detail':

					// Should Inflate the Product or Inventory Record?
					if ($e['has_json']) {
						$res = json_decode($e['response']['content']['text'], true);
						$this->output_inventory_update($res);
					}

					break;

				case 'POST/api/v1/inventory/get-qa-test-results':

					if ($e['has_json']) {
						$res = json_decode($e['response']['content']['text'], true);
						$this->output_lab_result($res);
					}

					break;

				case 'POST/api/v1/product/get-product-batches/':

					if ($e['has_json']) {
						$res = json_decode($e['response']['content']['text'], true);
						$this->output_inventory($res['Data']);
					}

					break;

				case 'POST/api/v1/product/products':

					if ($e['has_json']) {
						$res = json_decode($e['response']['content']['text'], true);
						$this->output_product($res['Data']);
					}

					break;
			}

			// case 'api/v1/grow/get-grow-cycles':
			// case 'api/v1/plants/plants':

		}

	}

	/**
	 *
	 */
	function output_inventory($res)
	{
		foreach ($res as $src) {

			// if ('20046037838758729' == $src['Barcode']) {
			// 	var_dump($src);
			// 	exit;
			// }


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

			$out['variety'] = [
				'id' => '',
				'name' => '-orphan-',
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


			// Variety is In the Product Name
			// Sometimes between "-" and "-" chars (eg: "Product - Variety - Other")
			// Sometimes just after the "-" eg: "Product - Variety"
			// Sometimes not at all (so we don't do magic on this case)
			// Spacing around the "-" is not consistent.
			if (preg_match('/^(\w.+?)\-(.+?)\-(.+)$/', $source_data->product->name, $m)) {
				$out['variety']['name'] = trim($m[2]);
				$out['product']['name'] = sprintf('%s - %s', trim($m[1]), trim($m[3]));
			} elseif (preg_match('/^(\w.+?)\-(.+?)$/', $source_data->product->name, $m)) {
				// $source_data->product->name = trim($m[1]);
				$out['variety']['name'] = trim($m[2]);
				$out['product']['name'] = trim($m[1]);
			}
			// if (preg_match('/^(\w.+?)\-(.+?)(\-|$)/', $src['ProductName'], $m)) {
			// 	$out['variety']['name'] = trim($m[2]);
			// 	$out['product']['name'] = trim($m[1]);
			// }

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

	function output_inventory_update($res)
	{
		$out = [];

		$f = sprintf('%s/inventory-%s.json', $this->_output_path, $res['BatchSummary']['Barcode']);
		if (is_file($f)) {
			$out = [];
			$out = file_get_contents($f);
			$out = json_decode($out, true);
		}

		// BatchQaParentList
		// QaSampleList
		if ( ! empty($res['QaSampleList'])) {
			if (empty($out['lab_sample'])) {
				$out['lab_sample'] = [];
			}
			foreach ($res['QaSampleList'] as $qas) {
				$out['lab_sample'][] = [
					'id' => $qas['TSID'],
					'created_at' => $qas['DateCreated'],
					'license_id_lab' => $qas['LabName'],
				];
			}
		}

		// BatchDirectParentList
		// BatchChildrenList
		if ( ! empty($res['BatchChildrenList'])) {
			// Child Lots?
		}

		$out['meta'] = [];
		$out['meta']['@source'] = $res;

		// AdjustmentList
		$d = json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

		file_put_contents($f, $d);

	}

	function output_lab_result($res)
	{
		foreach ($res['Data'] as $src) {

			$f = sprintf('%s/lab_result-%09d.json', $this->_output_path, $src['Id']);
			$d = json_encode($src, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

			$out = [];
			$out['id'] = $src['LabTestTSID'];
			$out['coa'] = $src['COAPath'];
			$out['stat'] = $src['Status'];
			$out['sample'] = [
				'id' => $src['SampleId'],
				'source' => [
					'id' => $src['QaParentId']
				]
			];
			$out['variety'] = [
				'name' => $src['Strain']
			];
			$out['meta'] = [
				'@source' => $src,
			];
			$out['lab_metric_list'] = [];
			$out['lab_metric_list']['018NY6XC00LM49CV7QP9KM9QH9'] = $src['THC'];
			$out['lab_metric_list']['018NY6XC00LMB0JPRM2SF8F9F2'] = $src['THCA'];
			$out['lab_metric_list']['018NY6XC00LMK7KHD3HPW0Y90N'] = $src['CBD'];
			$out['lab_metric_list']['018NY6XC00LMENDHEH2Y32X903'] = $src['CBDA'];
			$out['lab_metric_list']['018NY6XC00V7ACCY94MHYWNWRN'] = $src['Total'];

			$d = json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

			file_put_contents($f, $d);
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
