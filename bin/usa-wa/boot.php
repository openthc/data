<?php
/**
 * USA/WA/LCB Data Importer bootstrap
 */

require_once(__DIR__ . '/../../boot.php');

function _find_license($lic6)
{
	$dbc = _dbc();
	$sql = 'SELECT * FROM license WHERE code LIKE :l0 ORDER BY id';
	$arg = [
		':l0' => sprintf('_%s', $lic6)
	];
	$chk = $dbc->fetchRow($sql, $arg);
	return $chk;
}


function _revenue_record_insert($L, $date, $mode, $rev_sum, $tax_sum=0)
{
	$dbc = _dbc();

	if (empty($L)) {
		echo '!';
		return(null);
	}

	$add = array(
		'license_id' => $L['id'],
		'month' => $date,
		'source' => $mode,
		'rev_amount' => floatval($rev_sum),
		'tax_amount' => floatval($tax_sum),
	);

	$sql = 'SELECT * FROM license_revenue WHERE license_id = ? AND month = ? AND source = ? AND rev_amount = ?';
	$arg = array($add['license_id'], $add['month'], $add['source'], $add['rev_amount']);
	$chk = $dbc->fetchRow($sql, $arg);
	if (empty($chk)) {
		// echo '+';
		$dbc->insert('license_revenue', $add);
	} else {
		echo '.';
	}
}
