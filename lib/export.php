<?php
/**
 * Routines for Exporting Data
 */


function _res_to_csv($res, $csv_spec, $csv_name, $csv_char=',')
{
	// Purge output buffers
	while (ob_get_level() > 0) {
		ob_end_clean();
	}

	header('content-description: Data Download');
	header(sprintf('content-disposition: attachment; filename="%s"', $csv_name));
	header('content-type: text/plain');

	$fh = fopen('php://output', 'w');

	if (!empty($csv_spec)) {
		fputcsv($fh, array_values($csv_spec), $csv_char);
	}

	foreach ($res as $rec) {

		$out = [];
		foreach ($csv_spec as $k0 => $k1) {
			$out[] = $rec[$k0];
		}

		fputcsv($fh, $out, $csv_char);

	}

	exit(0);
}
