#!/usr/bin/php
<?php
/**
 * Extract from ZIP, Convert to UTF-8 and name it "tsv"
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

$zip_file_list = glob('*.zip');
foreach ($zip_file_list as $zip_file) {

	echo "zip:$zip_file\n";

	$zip = new ZipArchive();
	$zip->open($zip_file);
	$zip_stat = $zip->statIndex(0);
	// print_r($zip_stat);

	$output_csv = sprintf('%s.csv', basename($zip_file, '.zip'));
	$output_tsv = sprintf('%s.tsv', basename($zip_file, '.zip'));

	// Try to make smarter names
	if (preg_match('/(\w+_)(\d+)\.csv$/', $zip_stat['name'], $m)) {
		$output_csv = sprintf('%s%03d.csv', $m[1], $m[2]);
		$output_tsv = sprintf('%s%03d.tsv', $m[1], $m[2]);
	}

	$source_res = $zip->getStream($zip_stat['name']); // @note PHP 7.x
	// $source_res = $zip->getStreamIndex(0); // @note only in PHP 8.0+
	$target_res = fopen($output_csv, 'w');
	if ( ! stream_copy_to_stream($source_res, $target_res) ) {
		echo "Failed to Extract $output_csv\n";
		exit(1);
	}
	unlink($zip_file);

	echo "csv:$output_csv\n";

	$cmd = [];
	$cmd[] = '/usr/bin/iconv';
	$cmd[] = '--from-code=UTF-16LE';
	$cmd[] = '--to-code=UTF-8//TRANSLIT';
	$cmd[] = sprintf('--output=%s', escapeshellarg($output_tsv));
	$cmd[] = escapeshellarg($output_csv);
	$cmd[] = '2>&1';
	$cmd = implode(' ', $cmd);
	shell_exec($cmd);

	unlink($output_csv);

	echo "tsv:$output_tsv\n";

	$output_md5 = md5_file($output_tsv);
	file_put_contents("{$output_tsv}.md5", $output_md5);

}
