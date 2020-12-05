<?php
/**
 * Lab API as JSON
 */

$path = $_SERVER['REQUEST_URI'];
$path = strtok($path, '?');
$path = trim($path, '/');
$path_list = explode('/', $path);
if ('api' == $path_list[0]) {
	array_shift($path_list);
}
if ('lab' == $path_list[0]) {
	array_shift($path_list);
}

switch (count($path_list)) {
	case 0:
		// Index
		$dbc = _dbc();
		$sql = sprintf("SELECT id, updated_at FROM lab_result WHERE id NOT LIKE 'WAATTESTE%%' ORDER BY id OFFSET %d LIMIT 1000", $_GET['o']);
		$res = $dbc->fetchAll($sql);
		_exit_json($res);
	break;
	case 1:
		// Single
		$dbc = _dbc();
		$sql = 'SELECT * FROM lab_result WHERE id = :pk';
		$rec = $dbc->fetchRow($sql, [ ':pk' => $path_list[0] ]);
		$rec['cre_data'] = json_decode($rec['meta'], true);
		unset($rec['meta']);
		_exit_json([
			'data' => $rec,
			'meta' => [],
		]);
	break;
}

_exit_json([
	'data' => null,
	'meta' => [ 'detail' => 'Invalid Request [CAL-028]', 'path' => $path_list ]
], 400);
