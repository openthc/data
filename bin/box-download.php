#!/usr/bin/php
<?php
/**
 * @see https://community.box.com/t5/Platform-and-Development-Forum/Automatic-files-download-from-a-Folder/td-p/81514
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

$d = __DIR__;
$d = dirname($d);
require_once("$d/boot.php");

$url_origin = strtok($argv[1], '?');

// Get "all" pages
$page_max = 1;
for ($page_idx=1; $page_idx<=$page_max; $page_idx++) {

	$url = sprintf('%s?page=%d&sortColumn=name&sortDirection=ASC', $url_origin, $page_idx);
	echo "# peek: $url\n";

	$box_info = _box_download_from_page($url);

	$page_max = $box_info['/app-api/enduserapp/shared-folder']['pageCount'];

}


/**
 * Fetch & Parse the Page
 */
function _box_download_from_page($url_origin)
{
	$req = _curl_init($url_origin);
	$res_body = curl_exec($req);
	$res_info = curl_getinfo($req);

	switch ($res_info['http_code']) {
		case 200:
			// Cool
			break;
		case 302:
			echo "Try: {$res_info['redirect_url']}\n";
			exit(1);
		default:
			var_dump($res_info);
			echo "Invalid Response from Box\n";
			exit(1);
	}

	$dom = new DOMDocument('1.0', 'utf-8');
	$dom->loadHTML($res_body);

	$ret = _box_download_page_script($dom);

	return $ret;

}


/**
 * Parse the Script
 */
function _box_download_page_script($dom)
{
	$node_list = $dom->getElementsByTagName('script');
	foreach ($node_list as $node) {

		$x = trim($node->textContent);
		if (preg_match('/^Box\.postStreamData/', $x)) {
			$json_text = str_replace('Box.postStreamData = ', '', $x);
			$json_text = trim($json_text, ';');
			$json_data = json_decode($json_text, true);
			_box_download_file_list($json_data);
		}

	}

	return $json_data;
}


/**
 * Emit Shell code to download all the things
 */
function _box_download_file_list($json_data)
{
	$base_data = $json_data['/app-api/enduserapp/shared-item'];
	$item_data = $json_data['/app-api/enduserapp/shared-folder']['items'];

	foreach ($item_data as $file) {

		echo "# Download {$file['name']}\n";

		$url = sprintf('https://lcb.app.box.com/index.php?rm=box_download_shared_file&shared_name=%s&file_id=f_%s', $base_data['sharedName'], $file['id']);

		$req = _curl_init($url);
		$res_body = curl_exec($req);
		$res_info = curl_getinfo($req);

		if ('302' == $res_info['http_code']) {
			$url = $res_info['redirect_url'];
			$cmd = sprintf('curl %s --remote-name --remote-header-name --silent >/dev/null 2>&1 &', escapeshellarg($url));
			echo "$cmd\n";
			echo "sleep 1\n";
			// shell_exec($cmd);
			// sleep(1);
		} else {
			echo $url;
			echo ' == ';
			echo $res_info['http_code'];
		}

		echo "\n";

	}

}
