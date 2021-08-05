#!/usr/bin/php
<?php
/**
 * Violations from Excel File
 */

require_once(__DIR__ . '/boot.php');


$url = $argv[1];
if (empty($url)) {
	echo "Call with [URL] of file\n";
	exit(1);
}

$src_file = sprintf('%s/tmp/wa/%s', APP_ROOT, basename($url));
if (!is_file($src_file)) {

	$var_path = sprintf('%s/tmp/wa', APP_ROOT);
	mkdir($var_path, 0755, true);
	chdir($var_path);

	$cmd = sprintf('wget --quiet %s', escapeshellarg($url));
	shell_exec($cmd);

}

$dbc = _dbc();

$xls = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($src_file);
$xls->setReadDataOnly(true);
$xls = $xls->load($src_file);
foreach($xls->getSheetNames() as $wks_name) {

	echo "Worksheet: $wks_name\n";

	$wks = $xls->getSheetByName($wks_name);
	$row_list = $wks->getRowIterator();

	foreach ($row_list as $row) {

		$idx = $row->getRowIndex();

		$lic6 = $wks->getCell(sprintf('A%d', $idx))->getValue();

		$x = $wks->getCell(sprintf('B%d', $idx))->getValue();
		$x = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($x);
		$date = $x->format('Y-m-d');

		$type = $wks->getCell(sprintf('C%d', $idx))->getValue();
		$case = $wks->getCell(sprintf('D%d', $idx))->getValue();
		$cite = $wks->getCell(sprintf('E%d', $idx))->getValue();

		$name = $wks->getCell(sprintf('F%d', $idx))->getValue();
		$name = preg_replace('/^\(MJ\)/', '', $name);
		$name = trim($name);

		$hash = md5("$lic6/$date/$case/$type/$name");

		// Info Output
		// echo "License: $lic6 on $date : $name\n";

		// Find License
		// $license_id = $dbc->fetchOne('SELECT id FROM license WHERE code LIKE :c0 OR code6 = :c6', [
		// 	':c0' => sprintf('_%s', $lic6),
		// 	':c6' => sprintf('%06d', $lic6),
		// ]);
		// if (empty($license_id)) {
		// 	echo "ROW:$idx; Skip License: $lic6\n";
		// 	continue;
		// }

		// Check or Insert
		$L = _find_license($lic6);
		if (empty($L['id'])) {
			echo "ROW:$idx; No License: $lic6\n";
			continue;
		}

		$sql = 'SELECT id FROM license_note WHERE license_id = :l0 AND hash = :h0';
		$arg = [
			':l0' => $L['id'],
			':h0' => $hash
		];
		$chk = $dbc->fetchRow($sql, $arg);
		if (empty($chk)) {
			$ins = array(
				'id' => _ulid(),
				'license_id' => $L['id'],
				'created_at' => $date,
				'hash' => $hash,
				'type' => $type,
				'name' => $name,
				'meta' => json_encode([
					'case' => $case,
					'cite' => $cite,
				])
			);
			$dbc->insert('license_note', $ins);
		}

	}
}

// $sql = 'UPDATE company SET stat_violation = (SELECT count(id) FROM company_note WHERE company_id = company.id AND flag = 1)';
// SQL::query($sql);
