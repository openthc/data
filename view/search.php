<?php
/**
 * Search
 */

use Edoceo\Radix;
use Edoceo\Radix\Session;


$_ENV['h1'] = $_ENV['title'] = 'Search';

?>


<!--
<form action="/search" class="form-inline mr-auto" id="search-form">
	<div class="input-group input-group-lg">
		<div class="input-group-prepend">
			<button class="btn btn-outline-secondary" type="button" id="scanner-search-ready" style="color: rgb(119, 119, 119);"><i class="fas fa-barcode"></i></button>
		</div>
		<input class="form-control" id="search-q" name="q" placeholder="Search" title="Search (use '/' to focus)" type="text">
		<div class="input-group-append">
			<button class="btn btn-outline-success"><i class="fas fa-search"></i></button>
		</div>
	</div>
</form>
 -->


<?php

if (empty($_GET['q'])) {
	echo '<div class="alert alert-note">No Query submitted</div>';
	return(0);
}


$col = array();
$col[] = 'company.name';
// $col[] = 'company.guid';
// $col[] = 'company.phone';
// $col[] = 'company.email';
$col[] = 'license.code';
$col[] = 'license.name';
// $col[] = 'license.guid';
//$col[] = 'contact_meta';

$sql_where = array();
foreach ($col as $c) {
	$sql_where[] = sprintf('%s ILIKE ?', $c);
	$arg[] = sprintf('%%%s%%', $_GET['q']);
}

//$sql = "SELECT * FROM company WHERE cre = 'WA/BioTrack' AND (name ILIKE ? OR licid LIKE ? OR ubi9 LIKE ? OR address ILIKE ?)";
$sql = <<<SQL
SELECT license.id AS license_id
, license.code AS license_code
, license.name AS license_name
, company.id AS company_id
, company.name AS company_name
FROM license
LEFT JOIN company ON license.company_id = company.id
WHERE {WHERE}
ORDER BY license_name, license_code
LIMIT 25
SQL;

$sql = str_replace('{WHERE}', implode(' OR ', $sql_where), $sql);

$dbc = _dbc();
$res = $dbc->fetchAll($sql, $arg);

switch (count($res)) {
case 0:
	if (empty($_GET['q'])) {
		echo '<div class="alert alert-warn">No matching search results</div>';
		return(0);
	}
	break;
case 1:
	$rec = $res[0];
	Radix::redirect(sprintf('/license/%s', $rec['license_id']));
	break;
default:

	echo '<section>';
	foreach ($res as $rec) {
		printf('<h2><a href="/license/%s">%s</a> <code>%s</code></h2>', $rec['license_id'], h($rec['license_name']), $rec['license_code']);
	}
	echo '</section>';

}
