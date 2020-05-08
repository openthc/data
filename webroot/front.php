<?php
/**
 * Data Site Front Controller
 */

use Edoceo\Radix;
use Edoceo\Radix\Session;

require_once(dirname(dirname(__FILE__)) . '/boot.php');
require_once(APP_ROOT . '/lib/App_Menu.php');

Radix::init();

// Session
session_start();

if (empty($_SESSION['acl_subject'])) {
	$_SESSION['acl_subject'] = 'free';
}
//$_SESSION['acl_subject'] = 'paid';

if (_acl($_SESSION['acl_subject'], 'sql', 'direct-query')) {
	 // App_Menu::addMenuItem('main', '/sql', '<i class="fas fa-database"></i> SQL', 9000);
}

Radix::exec();

App_Menu::addMenuItem('main', '/revenue', '<i class="fas fa-funnel-dollar"></i> Revenue');
App_Menu::addMenuItem('main', '/b2b', '<i class="fas fa-truck-loading"></i> B2B');
App_Menu::addMenuItem('main', '/lab', '<i class="fas fa-flask"></i> Lab');
App_Menu::addMenuItem('main', '/b2c', '<i class="fas fa-users"></i> B2C');

Radix::view();

Radix::send();

exit(0);
