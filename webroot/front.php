<?php
/**
    @see https://code.google.com/apis/console/#project:461993707506:access
    http://stackoverflow.com/questions/3199489/meaning-of-ffmpeg-output-tbc-tbn-tbr
*/

use Edoceo\Radix;
use Edoceo\Radix\Session;

require_once(dirname(dirname(__FILE__)) . '/boot.php');
require_once(APP_ROOT . '/lib/App_Menu.php');

Radix::init();

// Session
// Session::init(array('name' => 'openthc'));
session_start();

if (empty($_SESSION['geoip'])) {
	$_SESSION['geoip'] = geoip_record_by_name($_SERVER['REMOTE_ADDR']);
}
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
// App_Menu::addMenuItem('main', '/company', 'Companies', 100);
// App_Menu::addMenuItem('main', '/license', 'Licenses', 110);
// App_Menu::addMenuItem('main', '/counties', 'Counties', 200);
// App_Menu::addMenuItem('main', '/cities', 'Cities', 300);
//App_Menu::addMenuItem('main', '/extract', 'Extracts');
////App_Menu::addMenuItem('main', '/producers', 'Producers');
////App_Menu::addMenuItem('main', '/processors', 'Processors');
////App_Menu::addMenuItem('main', '/inventory', 'Inventory', 70);
//App_Menu::addMenuItem('main', '/qa', 'QA');
////App_Menu::addMenuItem('main', '/retail', 'Retail', 60);
////App_Menu::addMenuItem('main', '/wholesale', 'Wholesale', 50);
////App_Menu::addMenuItem('main', '/retail', 'Retail', 60);
////

//App_Menu::addMenuItem('main', '/edibles', 'Edibles');
// Cartridge 1/2g, Cartridge 1g, Wax/Shatter, Kief, Edibile/Liquid, Edible/Solid, Pre-Roll at 0.5, 0.75 and 1g

Radix::view();

Radix::send();

exit(0);

/**
	Draw a List of Companies
	Then the rev columns and percent up/down
	@param $res RecordSet
	@return nothing, but does echo
*/
function _draw_table_n63p($res)
{

	echo '<table class="table">';
	echo '<thead><tr><th>Licensee</th><th>Prev 3</th><th>Recent 3</th><th>Change</th></tr></thead>';
	echo '<tbody>';
	foreach ($res as $rec) {

		echo '<tr>';
		echo '<td><a href="/profile?v=' . $rec['lic6'] . '">' . h($rec['name']) . '</a></td>';
		echo '<td class="r">$' . number_format($rec['rev6']) . '</td>';
		echo '<td class="r">$' . number_format($rec['rev3']) . '</td>';
		if ($rec['rev6'] > 0) {
			$pct = ($rec['rev3'] - $rec['rev6']) / $rec['rev6'] * 100;
			if ($pct > 0) {
				echo sprintf('<td class="r"><i class="fa fa-arrow-up" style="color:#2ECC40;"></i> %0.1f%%</td>', $pct);
			} else {
				echo sprintf('<td class="r"><i class="fa fa-arrow-down" style="color:#FF4136;"></i> %0.1f%%</td>', $pct);
			}
		} else {
			echo '<td class="r">-.--%</td>';
		}
		echo '</tr>';
	}
	echo '</tbody>';
	echo '</table>';

}
