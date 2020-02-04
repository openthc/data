<?php
/**
 *
 */

// App-Boot
require_once(dirname(dirname(dirname(__FILE__))) . '/boot.php');


function _fopen_bom($f)
{
	$fh = fopen($f, 'r');

	$bom = fread($fh, 3);

	$bom3 = bin2hex($bom);
	if ('efbbbf' == $bom3) {
		// UTF-8
		return $fh;
	}

	$bom2 = substr($bom3, 0, 2);
	if ('ffee' == $bom2) {
		echo "Adding UTF-16 to UTF-8 Filter\n";
		stream_filter_append($fh, 'convert.iconv.UTF-16/UTF-8');
		return $fh;
	}

	// Hopfully it's ASCII

	// echo "rewind ('$bom')\n";
	fseek($fh, 0, SEEK_SET);

	return $fh;

}


function _fpeek_sep($fh)
{
	$off = ftell($fh);
	$buf = fread($fh, 1000);
	fseek($fh, $off, SEEK_SET);

	$c_c = substr_count($buf, ',');
	$c_t = substr_count($buf, "\t");

	if (($c_t != 0) && ($c_c != 0)) {
		// It's way more tabs than commas
		if ($c_t > ($c_c * 2)) {
			return "\t";
		}
		die("_fpeek_sep() Cannot TelL Separator Type!! $c_t and $c_c\n");
	} elseif (($c_t > 0) && ($c_c == 0)) {
		return "\t";
	} else {
		return ',';
	}

}

function de_fuck_date_format($rec)
{
	$date_field_list = array(
		'batch_created_at',
		'created_at',
		'deleted_at',
		'disposal_at',
		'harvested_at',
		'hold_ends_at',
		'hold_starts_at',
		'inventory_created_at',
		'inventory_expires_at',
		'inventory_packaged_at',
		'lab_results_date',
		'packaged_completed_at',
		'plant_created_at',
		'plant_harvested_at',
		'planted_at',
		'transferred_at',
		'updated_at',
		'sold_at',
	);

	foreach ($date_field_list as $f) {

		$d = trim($rec[$f]);

		if (empty($d)) {
			continue;
		}

		// MySQL
		if ('00/00/0000' == $d) {
			$rec[$f] = null;
			continue;
		}

		if ('0000-00-00 00:00:00' == $d) {
			$rec[$f] = null;
			continue;
		}

		if ('1900-01-01 00:00:00' == $d) {
			$rec[$f] = null;
			continue;
		}

		$d = strtotime($d);
		if ($d > 0) {
			$rec[$f] = strftime('%Y-%m-%d %H:%M:%S', $d);
		} else {
			// Handle Stupid Shit
			if (preg_match('/^(.+ )(\d+):(\d+)(am|pm)$/i', $rec[$f], $m)) {
				$d = $m[1];
				$hh = intval($m[2]);
				$mm = intval($m[3]);
				if ($hh >= 13) {
					$d.= sprintf('%02d:%02d', $hh, $mm);
				} elseif ($m[4] == 'pm') {
					$d.= sprintf('%02d:%02d', $hh + 12, $mm);
				} else {
					$d.= sprintf('%02d:%02d', $hh, $mm);
				}
				$d = strtotime($d);
				if ($d > 0) {
					$rec[$f] = strftime('%Y-%m-%d %H:%M:%S', $d);
				} else {
					throw new Exception('Really Bad Date');
				}
			}
		}
	}

	return $rec;

}


function _append_fail_log($idx, $why, $rec)
{
	if (preg_match('/Key.+id.+already exists/', $why)) {
		return(0);
	}

	$rec = json_encode($rec);

	$msg = "Record: $idx; $why; $rec;\n";

	// $fh = fopen('/opt/data.openthc.org/output-data.out', 'a');
	// fwrite($fh, $msg);
	// fclose($fh);

	echo "$msg\n";

}

function _show_progress($idx, $max)
{
	if ((0 == ($idx % 100000)) || ($idx == $max)) {

		$pct = floor($idx / $max * 100);

		$t1 = microtime(true);
		$sec = $t1 - $_SERVER['REQUEST_TIME_FLOAT'];
		$rps = floor($idx / $sec);

		$dts = date(DateTime::RFC3339);

		echo "$dts: $idx $pct% $rps/s\n";

	}
}
