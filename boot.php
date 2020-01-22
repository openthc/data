<?php
/**
 * OpenTHC Data Site Bootstrap
 */

define('APP_ROOT', dirname(__FILE__));
define('APP_SALT', sha1(__FILE__ . 'change-this-value'));
define('APP_BUILD', '420.19.300');

openlog('openthc-data', LOG_ODELAY|LOG_PID, LOG_LOCAL0);

error_reporting(E_ALL & ~ E_NOTICE);

define('DATE_ALPHA_6MO', '2019-06-01 00:00:00');

require_once(APP_ROOT . '/vendor/autoload.php');
require_once(APP_ROOT . '/lib/data.php');

function _dbc()
{
	static $dbc;

	if (empty($dbc)) {
		try {
			$cfg = \OpenTHC\Config::get('database');
			$dbc = new \Edoceo\Radix\DB\SQL(sprintf('pgsql:host=%s;dbname=%s', $cfg['hostname'], $cfg['database']), $cfg['username'], $cfg['password']);
		} catch (Exception $e) {
			_exit_text('Database Connection Error', 500);
		}
	}

	return $dbc;

}


/**
 * Check ACL
 * @param string $sub [description]
 * @param string $obj [description]
 * @param string $act [description]
 * @return bool [description]
 */
function _acl($sub, $obj, $act)
{
	return true;

	static $cbe; // Casbin Enforcer

	// We would have to implement a Model to cache it or make it faster
	// We would have to implement an Adapter to cache it or make it faster

	if (empty($cbe)) {
		$cmf = sprintf('%s/etc/casbin/model.conf', APP_ROOT); // Model
		$cpf = sprintf('%s/etc/casbin/policy.csv', APP_ROOT); // Adapter
		$cbe = new \Casbin\Enforcer($cmf, $cpf);
	}

	return $cbe->enforce($sub, $obj, $act);
}

function _acl_exit($s, $o, $a)
{
	if (!_acl($s, $o, $a)) {
		\Edoceo\Radix\Session::flash('fail', 'Access Denied [APP#169]');
		\Edoceo\Radix::redirect('/auth/open?r=' . rawurlencode($_SERVER['REQUEST_URI']));
		exit(0);
	}
}



function _select_via_cache($dbc, $sql, $arg)
{
	$hash = sprintf('%s-%s', md5($sql), md5(json_encode($arg)));
	$file = sprintf('%s/var/cache/sql/%s', APP_ROOT, $hash);

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

	return $res;

}
