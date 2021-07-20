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
	return render_view($this, $RES, 'b2b/main.php');
});

$app->get('/b2b/flower', function($REQ, $RES) {
	return render_view($this, $RES, 'b2b/flower.php');
});

$app->get('/b2b/extract', function($REQ, $RES) {
	return render_view($this, $RES, 'b2b/extract.php');
});

$app->get('/b2b/edible', function($REQ, $RES) {
	return render_view($this, $RES, 'b2b/edible.php');
});

$app->get('/b2b/product', function($REQ, $RES, $ARG) {
	return render_view($this, $RES, 'b2b/product.php');
});

$app->get('/b2b/transfer', function($REQ, $RES) {
	return render_view($this, $RES, 'b2b/transfer.php');
});

$app->get('/b2b/transfer-detail', function($REQ, $RES) {
	return render_view($this, $RES, 'b2b/transfer-detail.php');
});

$app->get('/b2c', function($REQ, $RES) {
	return render_view($this, $RES, 'b2c/main.php');
});

$app->get('/lab', function($REQ, $RES) {
	return render_view($this, $RES, 'lab/main.php');
});

$app->get('/lab/frequency', function($REQ, $RES) {
	return render_view($this, $RES, 'lab/frequency.php');
});

$app->get('/lab/potency', function($REQ, $RES) {
	return render_view($this, $RES, 'lab/potency.php');
});

$app->get('/lab/result/{id}', function($REQ, $RES, $ARG) {

	$c = $this; // Container
	$v = new class($c) extends \OpenTHC\Controller\Base {};

	$_ENV['h1'] = $_ENV['title'] = sprintf('Lab Result :: Detail :: %s', $ARG['id']);

	$dbc = _dbc();

	$d = [
		'Page' => [ 'title' => $_ENV['title'] ],
		'Lab_Result' => $dbc->fetchRow('SELECT * FROM lab_result WHERE id = :lr0', [ ':lr0' => $ARG['id'] ] )
	];

	return $RES->write( $v->render('lab/result.php', $d) );

});

$app->get('/lab/usage', function($REQ, $RES) {
	return render_view($this, $RES, 'lab/usage.php');
});

$app->get('/lab/attested', function($REQ, $RES) {
	return render_view($this, $RES, 'lab/attested.php');
});

$app->get('/license/{id}', function($REQ, $RES, $ARG) {
	$_GET['id'] = $ARG['id'];
	return render_view($this, $RES, 'license/single.php');
});

$app->get('/license/{id}/clients', function($REQ, $RES, $ARG) {
	$_GET['id'] = $ARG['id'];
	return render_view($this, $RES, 'license/clients.php');
});

$app->get('/license/{id}/product', function($REQ, $RES, $ARG) {
	$_GET['id'] = $ARG['id'];
	return render_view($this, $RES, 'license/product.php');
});

$app->get('/license/{id}/map', function($REQ, $RES, $ARG) {
	$_GET['id'] = $ARG['id'];
	return render_view($this, $RES, 'license/map.php');
});

$app->get('/license/{id}/b2b', function($REQ, $RES, $ARG) {
	$_GET['id'] = $ARG['id'];
	return render_view($this, $RES, 'license/b2b-sale.php');
});

$app->get('/license/{id}/b2b-detail', function($REQ, $RES, $ARG) {
	$_GET['id'] = $ARG['id'];
	return render_view($this, $RES, 'license/b2b-sale-detail.php');
});

$app->get('/license/{id}/vendors', function($REQ, $RES, $ARG) {
	$_GET['id'] = $ARG['id'];
	return render_view($this, $RES, 'license/vendors.php');
});

$app->get('/product', function($REQ, $RES, $ARG) {
	return render_view($this, $RES, 'product/main.php');
});

$app->get('/revenue', function($REQ, $RES) {
	return render_view($this, $RES, 'revenue/main.php');
});

$app->get('/revenue/company', function($REQ, $RES) {
	return render_view($this, $RES, 'revenue/company.php');
});

$app->get('/revenue/license', function($REQ, $RES) {
	return render_view($this, $RES, 'revenue/license.php');
});

$app->get('/search', function($REQ, $RES) {
	return render_view($this, $RES, 'search.php');
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
