<?php
/**
 * https://www.google.com/search?q=php+nlp+automatic+summarization
 */

use Edoceo\Radix;
use Edoceo\Radix\Session;
use Edoceo\Radix\DB\SQL;

set_time_limit(360);

$_ENV['h1'] = $_ENV['title'] = 'Data :: Direct SQL';

if (!empty($_GET['q'])) {
	$sql = $_GET['q'];
	$sql = trim($sql);
	//if (!preg_match('/^SELECT /i', $sql)) {
	//	unset($_GET['q']);
	//	_extract_sql();
	//}
} else {
	$t = strtolower(trim($_GET['t']));
	$l = intval($_GET['l']);
	$l = 500;
}

$Page = array(
	'max' => 0,
	'size' => 500,
);

// Load and Show a Table
$Table = array(
	'pk' => 'id',
	'name' => $t,
	'sort' => 'id',
);

if (!empty($_GET['s'])) {
	if (preg_match('/^\w+$/', $_GET['s'])) {
		$Table['sort'] = $_GET['s'];
	}
}

// Special case PK Identifiers
// @note handled with database patch
//switch ($Table['name']) {
//case 'inventorylog':
//	$Table['pk'] = 'logid';
//	break;
//case 'organizations':
//case 'organizations_labs':
//	$Table['pk'] = 'orgid';
//	break;
//}

//if (!empty($Table['name'])) {
//
//	// A Table Was Selected, Lets Start Browsing
//	$sql = sprintf('SELECT count(%s) FROM %s', $Table['pk'], $Table['name']);
//	$Table['row_max'] =  SQL::fetch_one($sql);
//	if (empty($Table['row_max'])) {
//		// echo '<h2>No Records Found</h2>';
//		// return(0);
//	}
//	// echo "sql:$sql";
//	// echo "err:" . SQL::lastError();
//}

// SQL Query Form
echo '<form>';
echo '<div>';
echo '<textarea name="q" style="height: 12em; width: 100%;">' . _format_sql($_GET['q']) . '</textarea>';
echo '</div>';
echo '<button class="btn btn-primary">Query</button>';
echo '<button class="btn btn-secondary" name="a" type="submit" value="save">Save</button>';
echo '</form>';

// Paging Information?
$Page['max'] = ceil($Table['row_max'] / $Page['size']);
$Page['cur'] = max(1, min($Page['max'], $_GET['p']));
$Page['off'] = 0;
if ($Page['cur'] > 1) {
	$Page['off'] = ($Page['cur'] - 1) * $Page['size'];
}

$res = [];

// Load Rows
if (!empty($_GET['k']) && !empty($_GET['v'])) {

	$sql = 'SELECT * FROM %s WHERE %s = :v';
	$sql = sprintf($sql, $Table['name'], $_GET['k']);

	$t0 = microtime(true);
	$dbc = _dbc();
	$res = $dbc->fetchAll($sql, [ ':v' => $_GET['v'] ]);
	$t1 = microtime(true);

	$_GET['q'] = $sql;

} elseif (!empty($_GET['r'])) {
	$sql = 'SELECT * FROM %s WHERE %s = %d';
	$sql = sprintf($sql, $Table['name'], $Table['pk'], $_GET['r']);
	$t0 = microtime(true);
	$dbc = _dbc();
	$res = $dbc->fetchAll($sql);
	$t1 = microtime(true);
	$_GET['q'] = $sql;

} elseif (!empty($_GET['q'])) {

	$sql = $_GET['q'];
	unset($Table['pk']);
	echo "<pre>$sql</pre>";

	$t0 = microtime(true);
	$dbc = _dbc();
	$res = $dbc->fetchAll($sql);
	$t1 = microtime(true);

} elseif (!empty($_GET['t'])) {

	$sql = 'SELECT * FROM %s ORDER BY %s DESC LIMIT %d OFFSET %d';
	$sql = sprintf($sql, $Table['name'], $Table['sort'], $Page['size'], $Page['off']);
	$_GET['q'] = $sql;

	$t0 = microtime(true);
	$dbc = _dbc();
	$res = $dbc->fetchAll($sql);
	$t1 = microtime(true);

	$_ENV['h1'] = $_ENV['title'] = 'Data Dumps :: ' . count($res) . ' Items';

	// _echo_data_dump();

}

if (empty($res)) {
	echo '<div class="alert alert-info">No records found matching your query</div>';
	_draw_table_list();
	return(0);
}


// echo '<h2>Table: ' . $t . ' Rows ' . max(1, $Page['off']+1) . ' - ' . ($Page['off'] + count($res)) . ' of ' . $Table['row_max'] . '</h2>';

// Page List
$page_list = _draw_page_list($Table, $Page);
echo $page_list;

$col_skip_list = array(
	'address1', 'address2', 'zip',
	'loclatitude', 'loclongitude',
	'mailaddress1', 'mailaddress2', 'mailzip'
);

ob_start();

echo '<table class="table table-sm">';
echo '<thead class="thead-dark">';
echo '<tr>';
// if (!empty($Table['pk'])) {
// 	echo '<th style="max-width:40em;">' . $Table['pk'] . '</th>';
// }
// unset($rec[ $Table['pk'] ]);
$rec = $res[0];
foreach ($rec as $k => $v) {

	//if (in_array($k, $skip_list)) {
	//	continue;
	//}

	$q = http_build_query(array(
		't' => $Table['name'],
		's' => $k,
	));
	echo '<th><a href="?' . $q . '">' . $k . '</th>';
}
echo '</tr>';
echo '</thead>';

echo '<tbody>';
foreach ($res as $rec) {

	// $pk = null;
	// foreach (array('id', 'orgid', 'logid') as $x) {
	// 	if (!empty($rec[$x])) {
	// 		$pk = $x;
	// 		break;
	// 	}
	// }

	echo '<tr>';
	// if (!empty($pk)) {
	// 	echo '<td>' . $rec[$pk] . '</td>';
	// }
	// unset($rec[$pk]);

	foreach ($rec as $k=>$v) {

		//if (in_array($k, $skip_list)) {
		//	continue;
		//}

		_draw_data_link($Table['name'], $k, $v);

	}

	echo '</tr>';
}
echo '</tbody>';
echo '</table>';

$html_table = ob_get_clean();

if ('save' == $_GET['a']) {
	$hash = sha1($sql);
	$file = sprintf('%s/var/report-%s.html', APP_ROOT, $hash);
	$data = json_encode(array(
		'title' => $_ENV['h1'],
		'query' => $sql,
		'data' => $res,
		'body' => $html_table,
	));
	file_put_contents($file, $data);
	echo '<p>Share Link: <a href="/data/report?h=' . $hash . '">https://data.openthc.org/data/report?h=' . $hash . '</a></p>';
}

echo $html_table;

echo $page_list;

echo '<p>Query took: ' . sprintf('%0.2fs', $t1 - $t0) . '</p>';

function _draw_data_link($tab, $k, $v)
{
	switch ($k) {
	case 'id':
		$q = http_build_query([
			't' => $tab,
			'k' => $k,
			'v' => $v,
		]);
		printf('<td><a href="?%s">%s</a></td>', $q, $v);
		break;
	case 'orgid':
		echo '<td><a href="?t=organizations&r=' . $v . '">' . $v . '</a></td>';
		break;
	case 'childid':
	case 'inventoryarray':
	case 'inventoryid':
	case 'inventoryparentid':
	case 'newid':
	case 'oldid':
	case 'parentid':
		$pid_list = explode(',', $v);
		sort($pid_list);
		$out_list = array();
		foreach ($pid_list as $x) {
			$out_list[] = ('<a href="?t=inventory&r=' . $x . '">' . $x . '</a>');
		}
		echo '<td>' . implode(', ', $out_list) . '</td>';
		break;
	case 'lab_license':
		$q = http_build_query(array(
			't' => 'locations_labs',
			'k' => 'licensenum',
			'v' => $v,
		));
		echo '<td><a href="?' . $q . '">' . $v . '</a></td>';
		break;
	case 'location':
		echo '<td><a href="?t=locations&r=' . $v . '">' . $v . '</a></td>';
		break;
	case 'inbound_license':
	case 'outbound_license':
		$q = http_build_query(array(
			't' => 'locations',
			'k' => 'licensenum',
			'v' => $v,
		));
		echo '<td><a href="?' . $q . '">' . $v . '</a></td>';
		break;
	case 'plantarray':
	case 'plantid':
		$pid_list = explode(',', $v);
		sort($pid_list);
		$out_list = array();
		foreach ($pid_list as $x) {
			$out_list[] = ('<a href="?t=plants&r=' . $x . '">' . $x . '</a>');
		}
		echo '<td>' . implode(', ', $out_list) . '</td>';
		break;
	case 'manifestid':
		echo '<td><a href="?t=inventory_transfers&k=manifestid&amp;v=' . $v . '">' . $v . '</a></td>';
		break;
	case 'room':
	case 'currentroom':
	case 'droom':
		// Depending on Table
		switch ($Table['name']) {
		case 'inventory':
			echo '<td><a href="?t=inventoryrooms&amp;r=' . $v . '">' . $v . '</a></td>';
			break;
		case 'plantderivatives':
		case 'plants':
			echo '<td><a href="?t=growrooms&amp;r=' . $v . '">' . $v . '</a></td>';
			break;
		default:
			echo '<td>' . $v . '</td>';
			break;
		}
		break;
	case 'sampleid':
	case 'sample_id':
		echo '<td><a href="?t=labresults_samples&k=inventoryid&v=' . $v . '">' . $v . '</a></td>';
		break;
	case 'inventorystatustime':
	case 'removescheduletime':
	case 'sessiontime':
	case 'sessiontime_min':
	case 'sessiontime_max':
	case 'max_inventorystatustime':
	case 'min_inventorystatustime':
		echo '<td>' . strftime('%Y-%m-%d %H:%M', $v) . '</td>';
		break;
	case 'source_id':
		echo '<td><a href="?t=plants&r=' . $v . '">' . $v . '</a></td>';;
		break;
	case 'inventorytype':
		echo '<td>' . _format_inventory_type($v) . '</td>';
		break;
	case 'vendor_id':
		$q = http_build_query(array(
			't' => 'locations',
			'k' => 'licensenum',
			'v' => $v,
		));
		echo '<td><a href="?' . $q . '">' . $v . '</a></td>';
		break;
	default:
		echo '<td>' . $v . '</td>';
	}
}

function _draw_page_list($Table, $Page)
{
	if ($Page['max'] > 1) {

		$buf = array();

		$min = max(1, $Page['cur'] - 10); // At least 10 back
		$max = min($Page['max'], $min + 30); // At most 30 past the current one
		if ($max == $Page['max']) {
			$min = min($min, $Page['max'] - 30); // At least 30 back from the end
			$min = max(1, $min);
		}

		if ($min > 1) {
			$arg = http_build_query(array(
				't' => $Table['name'],
				's' => $Table['sort'],
				'p' => 1,
			));
			$buf[] = sprintf('<a href="?%s">%d</a>', $arg, 1);
		}

		for ($idx=$min; $idx<=$max; $idx++) {
			$arg = http_build_query(array(
				't' => $Table['name'],
				's' => $Table['sort'],
				'p' => $idx,
			));
			$buf[] = sprintf('<a href="?%s">%d</a>', $arg, $idx);
		}

		if ($max < $Page['max']) {
			$arg = http_build_query(array(
				't' => $Table['name'],
				's' => $Table['sort'],
				'p' => $Page['max'],
			));
			$buf[] = sprintf('<a href="?%s">%d</a>', $arg, $Page['max']);
		}

		return '<div id="page-list">' . implode(' | ', $buf) . '</div>';
	}
}

function _draw_table_list()
{
	// @todo implement table list query against postgresql

	$res = array();
	$res[] = array('name' => 'company');
	$res[] = array('name' => 'license');
	$res[] = array('name' => 'lab_result');
	$res[] = array('name' => 'b2b_sale');
	$res[] = array('name' => 'b2b_sale_item');
	$res[] = array('name' => 'b2c_sale');
	$res[] = array('name' => 'b2c_sale_item');

	echo '<div style="max-width: 640px;">';
	echo '<section>';
	echo '<h3>Browse Tables</h3>';
	echo '<table class="table table-sm table-bordered table-hover">';
	foreach ($res as $rec) {

		echo '<tr>';
		printf('<td><a href="/sql?t=%s" style="display:block;">%s</a></td>', $rec['name'], $rec['name']);
		echo '</tr>';

		//$sql = preg_replace('/\s+/ms', ' ', $rec['sql']);
		//$sql = preg_match('/\(([^\)]+)\)/', $sql, $m) ? $m[1] : $sql;
		//if (preg_match_all('/ (\w+) (\w+)([^,]*),/', $sql, $m)) {
		//	$c = $m[1];
		//	echo '<p>Columns: ' . implode(', ', $c) . '</p>';
		//	//Radix::dump($m);
		//} else {
		//	echo '<pre>' . $sql . '</pre>';
		//}
		// echo '<pre>' . $rec['sql'] . '</pre>';

	}
	echo '</table>';
	echo '</section>';
	echo '</div>';
}


function _extract_sql()
{
	if (preg_match('/^SELECT .+? FROM (\w+) /', $sql, $m)) {
		$_GET['t'] = $m[1];
	}

}

function _format_sql($sql)
{
	$parse = $sql;
	$query = null;
	$where = null;
	$order = null;
	$group = null;

	if (preg_match('/^(SELECT .+)(FROM.+)/ims', $parse, $m)) {
		$query = trim($m[1]);
		$parse = $m[2];
	}
	if (preg_match('/FROM (.+)(\s+(WHERE|GROUP|HAVING|ORDER|LIMIT|OFFSET))?/ims', $parse, $m)) {
		$table = trim($m[1]);
		$parse = trim($m[2]);
	}
	if (preg_match('/WHERE (.+)/ims', $parse, $m)) {
		$where = trim($m[1]);
		$parse = trim($m[2]);
	}

	//echo '<pre>';
	//echo "sql:$sql\n";
	//echo "parse:$parse\n";
	//echo "query:$query\n";
	//echo "table:$table\n";
	//echo "where:$where\n";
	//echo '</pre>';

	$sql = preg_replace('/\s+FROM /ims', "\nFROM ", $sql);
	$sql = preg_replace('/\s+WHERE /ims', "\nWHERE ", $sql);
	$sql = preg_replace('/\s+GROUP /ims', "\nGROUP ", $sql);
	$sql = preg_replace('/\s+ORDER /ims', "\nORDER ", $sql);
	$sql = preg_replace('/\s+LIMIT /ims', "\nLIMIT ", $sql);
	$sql = preg_replace('/\s+OFFSET /ims', "\nOFFSET ", $sql);

	return $sql;
}
