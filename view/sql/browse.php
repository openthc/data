<?php
/*
 *
 */

session_write_close();

$_ENV['title'] = 'Data :: Browse';

$dbc = _dbc();

$idx = intval($_GET['p']);
$lim = 25;
$off = $idx * $lim;

$tab_list = [
	'company',
	'license',
	// 'license_revenue',
	// 'section',
	// 'variety',
	// 'plant',
	// 'plant_collect',
	// 'plant_collect_plant',
	'product',
	'lot',
	// 'lot_family',
	'lab_result',
	'b2b_sale',
	'b2b_sale_item',
	'b2b_sale_carrier',
	'b2c_sale',
	'b2c_sale_item',
];

// b2b_path
// contact
// lab_result_ext
// lab_result_lot
// license_contact
// license_history
//
// product_license_name


$tab_view = $_GET['t'];
if (!in_array($tab_view, $tab_list)) {
	$tab_view = null;
}

$obj_view = $_GET['id'];

?>
<style>
table {
	border-collapse: collapse;
	margin: 0;
	padding: 0;
	/* width: 100%; */
}
table thead {
	background: #999;
}
button {
	font-family: monospace;
	font-size: 12pt;
	margin: 0;
	padding: 0 0.5em;
}
select {
	font-family: monospace;
	font-size: 12pt;
	margin: 0;
	padding: 0;
}
#browse-wrap {
	font-family: monospace;
	margin: 0.5rem;
}
</style>

<div id="browse-wrap">
<form>
<select name="t">
<?php
foreach ($tab_list as $tab) {
	$sel = ($tab_view == $tab ? ' selected' : null);
	echo sprintf('<option%s value="%s">%s</option>', $sel, $tab, $tab);
}
?>
</select>
<button>Go</button>
<button name="p" value="<?= max($idx - 1, 0) ?>">&laquo;&laquo;</button>
<button name="p" value="<?= $idx + 1 ?>">&raquo;&raquo;</button>
</form>

<?php
if (!empty($tab_view) && !empty($obj_view)) {
	_echo_obj_detail($dbc, $tab_view, $obj_view);
} elseif (!empty($tab_view)) {
	$sql = sprintf('SELECT * FROM "%s" OFFSET %d LIMIT %d', $tab_view, $off, $lim);
	// $sql = sprintf('SELECT * FROM "%s" ORDER BY created_at DESC OFFSET %d LIMIT %d', $tab_view, $off, $lim);
	$res = $dbc->fetchAll($sql);
	echo sprintf('<div><code>%s</code></div>', $sql);
	_echo_res_table($res, $tab_view);
} else {
	echo '<p>Do Somethign!@</p>';
}
?>
</div>
</body>
</html>
<?php

return(null);

function _echo_obj_detail($dbc, $tab_view, $obj_view)
{
	$obj = $dbc->fetchRow(sprintf('SELECT * FROM "%s" WHERE id = ?', $tab_view), [ $obj_view ]);
	if (empty($obj['id'])) {
		echo '<p>No OBJ Found</p>';
		return(null);
	}

	echo sprintf('<h1>%s #%s</h1>', $tab_view, $obj_view);
	// var_dump($obj);
	_echo_res_table([ $obj ], $tab_view);

	switch ($tab_view) {
	case 'b2c_sale':
		echo '<h2>b2c_sale &raquo; b2c_sale_item</h2>';
		$res = $dbc->fetchAll('SELECT * FROM b2c_sale_item WHERE b2c_sale_id = ? ORDER BY id DESC', [ $obj_view ]);
		_echo_res_table($res, 'license');
		break;
	case 'company':
		echo '<h2>company &raquo; license</h2>';
		$res = $dbc->fetchAll('SELECT * FROM license WHERE company_id = ? ORDER BY id DESC', [ $obj_view ]);
		_echo_res_table($res, 'license');
		break;
	case 'license':
		echo '<h2>Show Contacts Maybe?</h2>';
		break;
	case 'lot':
		// echo '<h2>Show Source</h2>';
		// echo '<h2>Show Product</h2>';
		// echo '<h2>Show Variety</h2>';
		// echo '<h2>Show Family</h2>';

		echo '<h2>lot &raquo; b2b_sale_item</h2>';
		$res = $dbc->fetchAll('SELECT * FROM b2b_sale_item WHERE lot_id_origin = ?', [ $obj_view ]);
		_echo_res_table($res, 'b2b_sale_item');

		$res = $dbc->fetchAll('SELECT * FROM b2b_sale_item WHERE lot_id_target = ?', [ $obj_view ]);
		_echo_res_table($res, 'b2b_sale_item');

		echo '<h2>lot &raquo; b2c_sale_item</h2>';
		$res = $dbc->fetchAll('SELECT * FROM b2c_sale_item WHERE lot_id = ?', [ $obj_view ]);
		_echo_res_table($res, 'b2c_sale_item');

		break;

	case 'plant':
		echo '<h2>Plant Collect Plant Events</h2>';
		$res = $dbc->fetchAll('SELECT * FROM plant_collect_plant WHERE plant_id = ? ORDER BY created_at DESC', [ $obj_view ]);
		// _echo_res_table($res);
		break;
	case 'plant_collect':

		echo '<h2>plant_collect_plant</h2>';
		$res = $dbc->fetchAll('SELECT * FROM plant_collect_plant WHERE plant_collect_id = ? ORDER BY created_at DESC', [ $obj_view ]);
		// _echo_res_table($res, '');

		echo '<h2>lot_family</h2>';
		$sql = 'SELECT * FROM lot_family WHERE plant_collect_id = ?';
		$arg = [ $obj_view ];
		$res = $dbc->fetchAll($sql, $arg);
		_echo_res_table($res, 'lot_family');

		// Show Other Neat Stuff?
		break;
	case 'plant_collect_plant':
		echo '<h2>Plant Collect Parent</h2>';
		echo '<h2>Plant</h2>';
		break;
	case 'product':
		echo '<h2>product &raquo; lot</h2>';
		$res = $dbc->fetchAll('SELECT * FROM lot WHERE product_id = ? ORDER BY created_at DESC', [ $obj_view ]);
		_echo_res_table($res, 'lot');
		break;
	case 'variety':
		echo '<h2>Crops with this Variety</h2>';
		echo '<h2>Lots of This Variety</h2>';
		break;
	case 'section':
		echo '<h2>Crops in This Section</h2>';
		echo '<h2>Lots in This Section</h2>';
		break;
	default:
		echo '<p>No Obj View for Type: ' . $tab_view . '</p>';
	}

	// Lot Audit?

	// Lot Delta?

}


// $res = $dbc->fetchAll('SELECT * FROM log_audit ORDER BY created_at DESC LIMIT 20');
// foreach ($res as $rec) {
// 	var_dump($rec);
// }
// $res = $dbc->fetchAll('SELECT * FROM log_delta ORDER BY created_at DESC LIMIT 20');
// foreach ($res as $rec) {
// 	var_dump($rec);
// }

function _echo_res_table($res, $obj_link)
{
	if (empty($res)) {
		echo '<div><p>No Data in Result Set</p></div>';
		return(null);
	}

	$key_list = array_keys($res[0]);

	echo '<table class="ui celled fixed selectable very compact table">';

	echo '<thead>';
	echo '<tr>';
	foreach ($key_list as $key) {
		echo sprintf('<td>%s</td>', $key);
	}
	echo '</tr>';
	echo '</thead>';

	echo '<tbody>';
	foreach ($res as $rec) {
		_echo_row($rec, $obj_link);
	}
	echo '</tbody>';

	echo '</table>';
}


function _echo_row($rec, $obj_link)
{
	$key_list = array_keys($rec);

	echo '<tr>';
	foreach ($key_list as $key) {
		switch ($key) {
		case 'b2c_sale_id':
			echo sprintf('<td><a href="?t=b2c_sale&amp;id=%s">%s</a></td>', $rec[$key], $rec[$key]);
			break;
		case 'company_id':
			echo sprintf('<td><a href="?t=company&amp;id=%s">%s</a></td>', $rec[$key], $rec[$key]);
			break;
		case 'hash':
			echo '<td>' . h(substr($rec[$key], 0, 8)) . '</td>';
			break;
		case 'id':
			echo sprintf('<td><a href="?t=%s&amp;id=%s">%s</a></td>', $obj_link, $rec[$key], $rec[$key]);
			break;
		case 'license_id':
			echo sprintf('<td><a href="?t=license&amp;id=%s">%s</a></td>', $rec[$key], $rec[$key]);
			break;
		case 'lot_id':
			echo sprintf('<td><a href="?t=lot&amp;id=%s">%s</a></td>', $rec[$key], $rec[$key]);
			break;
		case 'meta':
			echo '<td>-META-</td>';
			break;
		case 'plant_collect_id':
			echo sprintf('<td><a href="?t=plant_collect&amp;id=%s">%s</a></td>', $rec[$key], $rec[$key]);
			break;
		case 'plant_id':
			echo sprintf('<td><a href="?t=plant&amp;id=%s">%s</a></td>', $rec[$key], $rec[$key]);
			break;
		case 'product_id':
			echo sprintf('<td><a href="?t=product&amp;id=%s">%s</a></td>', $rec[$key], $rec[$key]);
			break;
		case 'variety_id':
			echo sprintf('<td><a href="?t=variety&amp;id=%s">%s</a></td>', $rec[$key], $rec[$key]);
			break;
		case 'section_id':
			echo sprintf('<td><a href="?t=section&amp;id=%s">%s</a></td>', $rec[$key], $rec[$key]);
			break;
		default:
			echo '<td>' . h($rec[$key]) . '</td>';
			break;
		}
	}

	echo '</tr>';

}
