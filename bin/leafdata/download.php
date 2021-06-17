#!/usr/bin/php
<?php
/**
 * @see https://community.box.com/t5/Platform-and-Development-Forum/Automatic-files-download-from-a-Folder/td-p/81514
 */

$d = __DIR__;
$d = dirname($d);
$d = dirname($d);
require_once("$d/boot.php");

$url_origin = $argv[1];

// Get "all" pages
$page_max = 2;
for ($page_idx=1; $page_idx<=$page_max; $page_idx++) {
	_box_download_from_page(sprintf('%s?page=%d', $url_origin, $page_idx));
}


/**
 * Fetch & Parse the Page
 */
function _box_download_from_page($url_origin)
{
	$req = _curl_init($url_origin);
	$res_body = curl_exec($req);
	$res_info = curl_getinfo($req);
	// print_r($res_info);
	// file_put_contents('box.html', $res_body);

	if (200 != $res_info['http_code']) {
		echo "Invalid Response from Box\n";
		exit(1);
	}

	$dom = new DOMDocument('1.0', 'utf-8');
	$dom->loadHTML($res_body);

	_box_download_page_script($dom);

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
			// echo ">>>>$x####\n";
			$json_text = str_replace('Box.postStreamData = ', '', $x);
			$json_text = trim($json_text, ';');
			// echo ">>>>$json_text####\n";
			$json_data = json_decode($json_text, true);
			_box_download_file_list($json_data);
		}

	}
}

/**
 * Emit Shell code to download all the things
 */
function _box_download_file_list($json_data)
{
	$base_data = $json_data['/app-api/enduserapp/shared-item'];
	// print_r($base_data);
	// print_r(array_keys($json_data['/app-api/enduserapp/shared-folder']['items']));
	$item_data = $json_data['/app-api/enduserapp/shared-folder']['items'];
	// print_r($json_data['/app-api/enduserapp/shared-folder']['items']);

	foreach ($item_data as $file) {

		$url = sprintf('https://lcb.app.box.com/index.php?rm=box_download_shared_file&shared_name=%s&file_id=f_%s', $base_data['sharedName'], $file['id']);
		// echo "Download: $url\n";

		$req = _curl_init($url);
		$res_body = curl_exec($req);
		$res_info = curl_getinfo($req);
		// print_r($res_info); exit;

		if ('302' == $res_info['http_code']) {
			$url = $res_info['redirect_url'];
			$cmd = sprintf('curl %s --remote-name --remote-header-name --silent &', escapeshellarg($url));
			echo "$cmd\n";
		}
	}

}
