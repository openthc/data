<?php
/**
 * OpenTHC HTML Layout
 */

use Edoceo\Radix;
use Edoceo\Radix\Layout;
use Edoceo\Radix\Session;

header('content-type: text/html; charset=utf-8', true);

$body_class_list = [];
$m1_mode = preg_match('/^(open|mini|shut)$/', $_COOKIE['m1'], $m) ? $m[1] : 'open';
$body_class_list[] = sprintf('m1-%s', $m1_mode);

$path = strtok($_SERVER['REQUEST_URI'], '?');


$tool_menu_item = function($head, $link)
{
	$icon = preg_match('/(<i.+i>)/', $head, $m) ? $m[1] : $head;

?>
	<div class="item">
		<div class="drawer-knob">
			<a class="btn" href="<?= $link ?>" target="_blank"><?= $icon ?></a>
		</div>
		<div class="drawer">
			<a class="btn" href="<?= $link ?>" target="_blank"><?= $head ?></a>
		</div>
	</div>
<?php
};

$echo_session_flash = function()
{
	$x = Session::flash();
	if (!empty($x)) {

		$x = str_replace('<div class="good">', '<div class="alert alert-success alert-dismissible" role="alert">', $x);
		$x = str_replace('<div class="info">', '<div class="alert alert-info alert-dismissible" role="alert">', $x);
		$x = str_replace('<div class="warn">', '<div class="alert alert-warning alert-dismissible" role="alert">', $x);
		$x = str_replace('<div class="fail">', '<div class="alert alert-danger alert-dismissible" role="alert">', $x);

		// Add Close Button before Closing DIV
		$x = str_replace('</div>', '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>', $x);

		echo '<div class="radix-flash">';
			// echo '<div>';
			echo $x;
			// echo '</div>';
			// echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>';
		echo '</div>';
	}

};

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="initial-scale=1, user-scalable=yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="theme-color" content="#069420">
<link rel="stylesheet" crossorigin="anonymous" href="/vendor/fontaweomse/css/all.min.css">
<link rel="stylesheet" crossorigin="anonymous" href="/vendor/bootstrap/bootstrap.min.css">
<link rel="stylesheet" crossorigin="anonymous" href="https://cdn.jsdelivr.net/npm/charts.css@0.9.0/dist/charts.min.css">
<!-- <link rel="stylesheet" crossorigin="anonymous" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/css/dataTables.bootstrap4.min.css" integrity="sha256-F+DaKAClQut87heMIC6oThARMuWne8+WzxIDT7jXuPA="> -->
<link rel="stylesheet" href="https://cdn.openthc.com/css/www/0.0.2/main.css" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.openthc.com/css/www/0.0.2/menu-tlr.css" crossorigin="anonymous">
<title><?= h(strip_tags($_ENV['title'])) ?></title>
<style>
h1, h2, h3, h4, h5, h6 {
	margin-top: 0;
	margin-bottom: 0.50rem;
}
.chart-wrap {
	background: #e0e0e0;
	border: 1px solid #333;
	border-radius: 0.25rem;
	height: 420px;
	width:100%;
}
</style>
</head>
<body class="<?= implode(' ', $body_class_list) ?>" data-menu-left-mode="<?= $m1_mode ?>">
<div class="body-wrap">
<header class="body-head bg-dark">
	<nav>
		<div class="item logo">
			<div style="font-size: 32px;">
				<a class="btn btn-sm" href="/"><img alt="OpenTHC Icon" src="https://cdn.openthc.com/img/icon/icon-w-32.png"></a>
			</div>
		</div>
		<div class="item find">
			<form action="/search" autocomplete="off" class="form-inline" id="search-form">
				<div class="input-group">
					<input autocomplete="off" class="form-control" id="search-q" name="q" placeholder="Search" title="Search (use '/' to focus)" type="text">
					<div class="input-group-append">
						<button class="btn btn-outline-success"><i class="fas fa-search"></i></button>
					</div>
				</div>
			</form>
		</div>
	</nav>
</header>
<div class="main-wrap">
	<nav class="menu-l <?= $_SESSION['_ui']['menu'] ?>" id="menu-left">
	<?php
	$menu_list = App_Menu::getMenu('main');
	foreach ($menu_list as $menu) {

		if (empty($menu['id'])) {
			$menu['id'] = 'menu-' . trim(preg_replace('/[^\w]+/', '-', $menu['link']), '-');
		}

		$menu['pick'] = ($menu['link'] == substr($path, 0, strlen($menu['link']))) ? ' active' : '';

		if (preg_match('/^(<i.+i>)(.+)$/', $menu['name'], $m)) {
			$menu['icon'] = $m[1];
			$menu['text'] = $m[2];
		}

		echo '<div class="item">';
		printf('<a class="nav-link%s" href="%s" id="%s">', $menu['pick'], $menu['link'], $menu['id']);
		if (!empty($menu['icon']) && !empty($menu['text'])) {
			printf('<div class="nav-link-icon">%s</div>', $menu['icon']);
			printf('<div class="nav-link-text">%s</div>', $menu['text']);
		} else {
			echo $menu['name'];
		}
		echo '</a>';
		echo '</div>';

	}
	?>
	</nav>
	<div class="main-body">
		<header class="main-head">
			<div>
				<h1><?= $_ENV['h1'] ?></h1>
			</div>
		</header>
		<div class="main-data">
			<?= $echo_session_flash(); ?>
			<div class="container-fluid">
			<?= $this->body; ?>
			</div>
		</div>
	</div>
	<!-- <nav class="menu2 menu-r"></nav> -->
</div>
</div>

<div style="bottom: 0.25rem; left: 0; position: absolute; text-align:center; width: var(--menu1-mini-w);">
	<button class="btn menu-left-mode"><i class="fas fa-bars"></i></button>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.15/lodash.min.js" integrity="sha256-VeNaFBVDhoX3H+gJ37DpT/nTuZTdjYro9yBruHjVmoQ=" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="/vendor/jquery/jquery.min.js"></script>
<script src="/vendor/bootstrap/bootstrap.bundle.min.js"></script>
<!-- @deprecated moment, can use browser built-ins now? -->
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.27.0/moment.min.js" integrity="sha512-rmZcZsyhe0/MAjquhTgiUcb4d9knaFc7b5xAfju483gbEXTkeJRUMIPk6s3ySZMYUHEcjKbjLjyddGWMrNEvZg==" crossorigin="anonymous"></script> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/jquery.dataTables.min.js" integrity="sha256-t5ZQTZsbQi8NxszC10CseKjJ5QeMw5NINtOXQrESGSU=" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/dataTables.bootstrap4.min.js" integrity="sha256-hJ44ymhBmRPJKIaKRf3DSX5uiFEZ9xB/qx8cNbJvIMU=" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.6/clipboard.min.js" integrity="sha512-hDWGyh+Iy4Mr9AHOzUP2+Y0iVPn/BwxxaoSleEjH/i1o4EVTF/sh0/A1Syii8PWOae+uPr+T/KHwynoebSuAhw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.4.1/chart.min.js" integrity="sha512-5vwN8yor2fFT9pgPS9p9R7AszYaNn0LkQElTXIsZFCL7ucT8zDCAqlQXDdaqgA1mZP47hdvztBMsIoFxq/FyyQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="/js/app.js"></script>
<script>
var Clippy;
$(function() {

	// Menu Toggler
	$('.menu-left-mode').on('click', function() {

		var mode0 = document.body.dataset.menuLeftMode || 'open';

		var mode1 = '';
		switch (mode0) {
			case 'mini':
				mode1 = 'shut';
				break;
			case 'open':
				mode1 = 'mini';
				break;
			case 'shut':
				mode1 = 'open';
		}


		var c = document.body.getAttribute('class');
		var a = c.split(/\s+/);
		var i = 0;
		var m = a.length;
		for (i=0; i<m; i++) {
			c = c.replace(a[i], '');
		}
		c += (' ' + `m1-${mode1}`);
		document.body.setAttribute('class', c.trim());
		document.body.dataset.menuLeftMode = mode1;

		document.cookie = `m1=${mode1};path=/;samesite=strict;secure`;

	});

	Clippy = new ClipboardJS('.click2copy');
	// Hightlight then clear
	Clippy.on('success', function(e) {
		var $x = $(e.trigger);
		$x.css('background', '#ffff00');
		setTimeout(function() {
			$x.css('background', '');
		}, 3000);

	});
})

</script>
<?php
$buf = Layout::getScript();
if (!empty($buf)) {
	echo "\n$buf\n";
}
?>
</body>
</html>
