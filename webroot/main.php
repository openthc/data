<?php
/**
 * Data Site Front Controller
 */

use Edoceo\Radix;

require_once(dirname(dirname(__FILE__)) . '/boot.php');
require_once(APP_ROOT . '/lib/App_Menu.php');

$app = new \OpenTHC\App();
$con = $app->getContainer();
unset($con['errorHandler']);
unset($con['phpErrorHandler']);
// unset($con['notFoundHandler']);

$app->get('/b2b', function($REQ, $RES) {

	$c = $this; // Container
	$v = new class($c) extends \OpenTHC\Controller\Base {};
	$f = 'b2b/main.php';
	$d = [];
	return $RES->write( $v->render($f, $d) );

});

$app->get('/b2b/flower', function($REQ, $RES) {

	$c = $this; // Container
	$v = new class($c) extends \OpenTHC\Controller\Base {};
	$f = 'b2b/flower.php';
	$d = [];
	return $RES->write( $v->render($f, $d) );

});

$app->get('/b2b/extract', function($REQ, $RES) {

	$c = $this; // Container
	$v = new class($c) extends \OpenTHC\Controller\Base {};
	$f = 'b2b/extract.php';
	$d = [];
	return $RES->write( $v->render($f, $d) );

});

$app->get('/b2b/edible', function($REQ, $RES) {

	$c = $this; // Container
	$v = new class($c) extends \OpenTHC\Controller\Base {};
	$f = 'b2b/edible.php';
	$d = [];
	return $RES->write( $v->render($f, $d) );

});


$app->get('/b2b/transfer', function($REQ, $RES) {

	$c = $this;
	$v = new class($c) extends \OpenTHC\Controller\Base {};
	$f = 'b2b/transfer.php';
	$d = [];
	return $RES->write( $v->render($f, $d) );

});

$app->get('/b2b/transfer-detail', function($REQ, $RES) {

	$c = $this;
	$v = new class($c) extends \OpenTHC\Controller\Base {};
	$f = 'b2b/transfer-detail.php';
	$d = [];
	return $RES->write( $v->render($f, $d) );

});


$app->get('/b2c', function($REQ, $RES) {

	$c = $this; // Container
	$v = new class($c) extends \OpenTHC\Controller\Base {};
	$f = 'b2c/main.php';
	$d = [];
	return $RES->write( $v->render($f, $d) );

});

$app->get('/lab', function($REQ, $RES) {

	$c = $this; // Container
	$v = new class($c) extends \OpenTHC\Controller\Base {};
	$f = 'lab/main.php';
	$d = [];
	return $RES->write( $v->render($f, $d) );

});

$app->get('/lab/frequency', function($REQ, $RES) {

	$c = $this; // Container
	$v = new class($c) extends \OpenTHC\Controller\Base {};
	$f = 'lab/frequency.php';
	$d = [];
	return $RES->write( $v->render($f, $d) );

});

$app->get('/lab/potency', function($REQ, $RES) {

	$c = $this; // Container
	$v = new class($c) extends \OpenTHC\Controller\Base {};
	$f = 'lab/potency.php';
	$d = [];
	return $RES->write( $v->render($f, $d) );

});

$app->get('/lab/usage', function($REQ, $RES) {

	$c = $this; // Container
	$v = new class($c) extends \OpenTHC\Controller\Base {};
	$f = 'lab/usage.php';
	$d = [];
	return $RES->write( $v->render($f, $d) );

});

$app->get('/lab/attested', function($REQ, $RES) {

	$c = $this; // Container
	$v = new class($c) extends \OpenTHC\Controller\Base {};
	$f = 'lab/attested.php';
	$d = [];
	return $RES->write( $v->render($f, $d) );

});

$app->get('/license/{id}', function($REQ, $RES, $ARG) {

	$_GET['id'] = $ARG['id'];

	$c = $this; // Container
	$v = new class($c) extends \OpenTHC\Controller\Base {};
	$f = 'license/single.php';
	$d = [];

	return $RES->write( $v->render($f, $d) );

} );

$app->get('/license/{id}/clients', function($REQ, $RES, $ARG) {

	$_GET['id'] = $ARG['id'];

	$c = $this; // Container
	$v = new class($c) extends \OpenTHC\Controller\Base {};
	$f = 'license/clients.php';
	$d = [];

	return $RES->write( $v->render($f, $d) );

} );

$app->get('/license/{id}/map', function($REQ, $RES, $ARG) {

	$_GET['id'] = $ARG['id'];

	$c = $this; // Container
	$v = new class($c) extends \OpenTHC\Controller\Base {};
	$f = 'license/map.php';
	$d = [];

	return $RES->write( $v->render($f, $d) );

} );

$app->get('/license/{id}/b2b', function($REQ, $RES, $ARG) {

	$_GET['id'] = $ARG['id'];

	$c = $this; // Container
	$v = new class($c) extends \OpenTHC\Controller\Base {};
	$f = 'license/b2b-sale.php';
	$d = [];

	return $RES->write( $v->render($f, $d) );

} );

$app->get('/license/{id}/b2b-detail', function($REQ, $RES, $ARG) {

	$_GET['id'] = $ARG['id'];

	$c = $this; // Container
	$v = new class($c) extends \OpenTHC\Controller\Base {};
	$f = 'license/b2b-sale-detail.php';
	$d = [];

	return $RES->write( $v->render($f, $d) );

} );


$app->get('/license/{id}/vendors', function($REQ, $RES, $ARG) {

	$_GET['id'] = $ARG['id'];

	$c = $this; // Container
	$v = new class($c) extends \OpenTHC\Controller\Base {};
	$f = 'license/vendors.php';
	$d = [];

	return $RES->write( $v->render($f, $d) );

} );

$app->get('/revenue', function($REQ, $RES) {

	$c = $this; // Container
	$v = new class($c) extends \OpenTHC\Controller\Base {};
	$f = 'revenue/main.php';
	$d = [];

	return $RES->write( $v->render($f, $d) );

} );

$app->get('/revenue/company', function($REQ, $RES) {

	$c = $this; // Container
	$v = new class($c) extends \OpenTHC\Controller\Base {};
	$f = 'revenue/company.php';
	$d = [];

	return $RES->write( $v->render($f, $d) );

} );

$app->get('/revenue/license', function($REQ, $RES) {

	$c = $this; // Container
	$v = new class($c) extends \OpenTHC\Controller\Base {};
	$f = 'revenue/license.php';
	$d = [];

	return $RES->write( $v->render($f, $d) );

} );

$app->get('/search', function($REQ, $RES) {

	$c = $this; // Container
	$v = new class($c) extends \OpenTHC\Controller\Base {};
	$f = 'search.php';
	$d = [];
	return $RES->write( $v->render($f, $d) );

});

$app->get('/sql', function($REQ, $RES) {
	return render_view($this, $RES, 'sql/main.php');
});

$app->get('/sql/info', function($REQ, $RES) {
	return render_view($this, $RES, 'sql/info.php');
});

App_Menu::addMenuItem('main', '/revenue', '<i class="fas fa-funnel-dollar"></i> Revenue');
App_Menu::addMenuItem('main', '/b2b', '<i class="fas fa-truck-loading"></i> B2B');
App_Menu::addMenuItem('main', '/lab', '<i class="fas fa-flask"></i> Lab');
App_Menu::addMenuItem('main', '/b2c', '<i class="fas fa-users"></i> B2C');

$res = $app->run();
if (404 == $res->getStatusCode()) {
	$html = file_get_contents(sprintf('%s/webroot/e/404.html', APP_ROOT));
	_exit_html($html, 404);
}
