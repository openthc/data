<?php
/**
 * OpenTHC Data Site Bootstrap
 */

define('APP_ROOT', dirname(__FILE__));
define('APP_SALT', sha1(__FILE__ . 'change-this-value'));
define('APP_BUILD', '420.20.120');

openlog('openthc-data', LOG_ODELAY|LOG_PID, LOG_LOCAL0);

error_reporting(E_ALL & ~ E_NOTICE);

// Objective is to show 5 quarters of data
define('DATE_ALPHA', '2019-06-01 00:00:00');
define('DATE_OMEGA', '2021-06-01 00:00:00');

require_once(APP_ROOT . '/vendor/autoload.php');
require_once(APP_ROOT . '/lib/data.php');
require_once(APP_ROOT . '/lib/export.php');

\OpenTHC\Config::init(APP_ROOT);

/**
 * Database Connection
 */
function _dbc()
{
	static $dbc;

	if (empty($dbc)) {
		try {
			$cfg = \OpenTHC\Config::get('database');
			$dbc = new \Edoceo\Radix\DB\SQL(sprintf('pgsql:host=%s;dbname=%s', $cfg['hostname'], $cfg['database']), $cfg['username'], $cfg['password']);
		} catch (Exception $e) {
			_exit_text(sprintf('Database Connection Error: "%s"', $e->getMessage()), 500);
		}
	}

	return $dbc;

}

/**
 * El-Cheapo Render Helper
 * @param $c Container
 * @param $RES REsponse Object
 * @param $ARG
 * @param $f View Script
 */
function render_view($c, $RES, $f)
{
	$v = new class($c) extends \OpenTHC\Controller\Base {};
	$d = [];
	return $RES->write( $v->render($f, $d) );
}


function _select_via_cache($dbc, $sql, $arg)
{
	$hash = sprintf('%s-%s', md5($sql), md5(json_encode($arg)));
	$file = sprintf('%s/var/cache/sql/%s', APP_ROOT, $hash);

	// Use Cache?
	if (is_file($file)) {
		$age = $_SERVER['REQUEST_TIME'] - filemtime($file);
		if ($age < 1209600) { // 2 Weeks
			$res = file_get_contents($file);
			$res = json_decode($res, true);
			return $res;
		}
	}

	$res = $dbc->fetchAll($sql, $arg);
	if (!empty($res)) {
		file_put_contents($file, json_encode($res));
	}

	// file_put_contents($file, json_encode(array(
	// 	'sql' => $sql,
	// 	'arg' => $arg,
	// 	'err' => SQL::lastError(),
	// 	'res' => $res,
	// )));

	return $res;

}
