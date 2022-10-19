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
// require_once(APP_ROOT . '/lib/export.php');

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
			$text = [];
			$text[] = 'Database Connection Error [ERR-036]';
			$text[] = $e->getMessage();
			_exit_text(implode("\n", $text), 503);
		}
	}

	return $dbc;

}


/**
 *
 */
function _fopen_bom($f)
{
	$fh = fopen($f, 'r');

	$bom = fread($fh, 3);
	$bom = bin2hex($bom);
	if ('efbbbf' == $bom) {
		// UTF-8
		return $fh;
	}

	var_dump($bom);
	exit;

	$bom2 = substr($bom3, 0, 4);
	if ('ffee' == $bom2) {
		echo "Adding UTF-16 to UTF-8 Filter\n";
		stream_filter_append($fh, 'convert.iconv.UTF-16/UTF-8');
		return $fh;
	}

	// Hopfully it's ASCII
	// echo "rewind ('$bom')\n";
	// fseek($fh, 0, SEEK_SET);

	return $fh;

}

/**
 * Show a Progress Indicator
 */
function _show_progress($idx, $max, $msg=null)
{
	if ((0 == ($idx % 100000)) || ($idx == $max)) {

		$pct = floor($idx / $max * 100);

		$t1 = microtime(true);
		$sec = $t1 - $_SERVER['REQUEST_TIME_FLOAT'];
		$rps = floor($idx / $sec);

		$dts = date(DateTime::RFC3339);

		$out = trim("$dts: $idx $pct% $rps/s $msg");
		echo "$out\n";

	}
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


function _select_via_cache($dbc, $sql, $arg=null)
{
	$file = sprintf('%s/var/query/%s/%s.json'
		, APP_ROOT
		, md5($sql)
		, md5(json_encode($arg))
	);

	// Use Cache?
	if (is_file($file)) {
		$age = $_SERVER['REQUEST_TIME'] - filemtime($file);
		if ($age < 1209600) { // 2 Weeks
			$res = file_get_contents($file);
			$res = json_decode($res, true);
			$res = $res['res'];
			return $res;
		}
	}

	$res = $dbc->fetchAll($sql, $arg);
	if (!empty($res)) {

		$path = dirname($file);
		if (!is_dir($path)) {
			mkdir($path, 0755, true);
		}

		file_put_contents($file, json_encode([
			'sql' => $sql,
			'arg' => $arg,
			'res' => $res,
		]));

	}


	return $res;

}
