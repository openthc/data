<?php
/**
 * OpenTHC HTML Layout v2
 */

use Edoceo\Radix;
use Edoceo\Radix\Layout;
use Edoceo\Radix\Session;

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

header('content-type: text/html; charset=utf-8', true);

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="initial-scale=1, user-scalable=yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="theme-color" content="#212121">
<link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.13.0/css/all.css" integrity="sha384-IIED/eyOkM6ihtOiQsX2zizxFBphgnv1zbe1bKA+njdFzkr6cDNy16jfIKWu4FNH" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" integrity="sha256-rByPlHULObEjJ6XQxW/flG2r+22R5dKiAoef+aXWfik=" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.openthc.com/bootstrap/4.4.1/bootstrap.css" integrity="sha256-L/W5Wfqfa0sdBNIKN9cG6QA5F2qx4qICmU2VgLruv9Y=" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css" integrity="sha256-yMjaV542P+q1RnH6XByCPDfUFhmOafWbeLPmqKh11zo=" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/css/dataTables.bootstrap4.min.css" integrity="sha256-F+DaKAClQut87heMIC6oThARMuWne8+WzxIDT7jXuPA=" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.openthc.com/css/www/0.0.1/www.css" crossorigin="anonymous">
<title><?= h(strip_tags($_ENV['title'])) ?></title>
<head>
<style>
:root {
	--menu0-h: 64px;
	--menu1-open-w: 240px;
	--menu1-mini-w: 48px;
	--menu2-w: 48px;
}
* {
	box-sizing: border-box;
}
body {
	height: 100vh;
	margin: 0;
	overflow: hidden;
	padding: 0;
	width: 100%;
}
/* Body */
div.body-wrap {
	position: relative;
	min-height: 100%;
}
/* Body Header */
header.body-head {
	height: var(--menu0-h);
	padding: 8px;
}
header.body-head > nav {
	display: flex;
	justify-content: space-between;
	position: relative;
}
header.body-head nav div.item {
	height: 48px;
	vertical-align: middle;
	white-space: nowrap;
}
/* Body Header Logo */
header.body-head nav div.logo {
	display: flex; flex: 1 0 auto;
	min-width: 120px;
}
header.body-head nav div.find {
	display: flex;
	flex: 1 1 100%;
	justify-content: flex-start;
}
/* Body Header Tool */
header.body-head nav div.tool {
	display: flex;
	flex: 0 0 auto;
	justify-content: flex-end;
}

div.main-wrap {
	display: flex;
	width: 100%;
}

/* Left Menu */
div.main-wrap nav.menu-l {
	border-right: 2px solid #333;
	display: flex;
	flex-direction: column;
	flex-flow: column nowrap;
	flex-shrink: 0;
	height: calc(100vh - var(--menu0-h));
	overflow-x: hidden;
	overflow-y: auto;
	width: var(--menu1-open-w);
	position: relative;
}
div.main-wrap nav.menu-l a {
	color: #000000;
	font-weight: bold;
}
div.main-wrap nav.menu-l button {
	display: block;
	width: 100%;
}
/* Menu Two(Left) Item */
div.main-wrap nav.menu-l div.item {
	display: block;
	margin: 0;
	/* padding: 4px; */
	width: 100%;
}
div.main-wrap nav.menu-l a.nav-link {
	align-items: center;
	display: flex;
	padding: 0.50rem;
}
div.main-wrap nav.menu-l a.nav-link div.nav-link-icon {
	flex: 0 0 2rem;
	margin: 0 auto;
	max-width: 2rem;
	text-align: center;
}
div.main-wrap nav.menu-l a.nav-link div.nav-link-text {
	flex: 1 1 auto;
}
div.main-wrap nav.menu-l div.item a:hover {
	background: #069420;
}
div.main-wrap nav.menu-l div.item a.active {
	background: #06942099;
}

/* Menu Two - open, mini, shut */
div.main-wrap #menu-left.mini {
	max-width:  var(--menu1-mini-w);
}
/* Hide Text in Mini Mode */
div.main-wrap nav.menu-l.mini a.nav-link div.nav-link-text {
	display: none;
}
div.main-wrap #menu-left.open {
	width: var(--menu1-open-w);
}
div.main-wrap #menu-left.shut {
	overflow: hidden;
	width: 0;
}

/* Main Wrap */
div.main-wrap div.main-body {
	overflow: hidden;
	width: 100%;
}
/* Main Head */
div.main-wrap div.main-body header.main-head {
	background: #666;
	display: flex;
	flex-direction: row;
	height: 48px;
	justify-content: space-between;
	padding: 2px;
}
/* Main Data */
div.main-wrap div.main-body div.main-data {
	height: calc(100vh - var(--menu0-h) - 48px);
	overflow-y: auto;
}
/* Menu Right */
div.main-wrap nav.menu-r {
	align-items: center;
	border-left: 2px solid #333;
	/* display: none; */
	/* display: flex; */
	/* flex-direction: column; */
	width: var(--menu2-w);
	/* overflow: hidden; */
}
/* Menu Right Fly Out */
div.main-wrap nav.menu-r div.item {
	padding: 0.25em;
	position: relative;
}
/* div.main-wrap nav.menu-r div.item h5 a {
	display: block;
} */
div.main-wrap nav.menu-r div.item div.drawer-knob {
	text-align: center;
}
div.main-wrap nav.menu-r div.item div.drawer {
	background: #069420;
	border-left: 4px solid #202020;
	padding: 0.25em;
	display: none;
	width: 15em;
}
div.main-wrap nav.menu-r div.item:hover div.drawer {
	position: absolute;
	display: block;
	top: 0;
	right: 0;
	/* right: calc(15rem - var(--menu2-w)); */
}
div.main-wrap nav.menu-r div.item:hover div.drawer a {
	display: block;
	text-align: left;
	width: 100%;
}
div.main-wrap nav.menu-r div.item:hover div.drawer a:active {
	color: #fff;
}
</style>
</head>
<body>
<div class="body-wrap">
<header class="body-head bg-dark">
	<nav>
		<div class="item logo">
			<div style="font-size: 32px;">
				<a class="btn btn-sm" href="/dashboard"><img alt="OpenTHC Icon" src="https://cdn.openthc.com/img/icon/icon-w-32.png"></a>
			</div>
			<div>
				<button class="btn" id="menu-left-mode" data-mode="<?= $_SESSION['_ui']['menu'] ?>" style="height:48px;">
					<svg width="16px" height="16px" viewBox="0 0 16 16" version="1.1" role="img" title="Menu Icon"><g stroke="none" stroke-width="1" fill="inherit" fill-rule="evenodd"><g transform="translate(-188.000000, -38.000000)" fill-rule="nonzero" fill="inherit"><g><g><g transform="translate(188.000000, 38.000000)"><path d="M15.5,0 C15.776,0 16,0.224 16,0.5 L16,1.5 C16,1.776 15.776,2 15.5,2 L0.5,2 C0.224,2 0,1.776 0,1.5 L0,0.5 C0,0.224 0.224,0 0.5,0 L15.5,0 Z M15.5,4 C15.776,4 16,4.224 16,4.5 L16,5.5 C16,5.776 15.776,6 15.5,6 L0.5,6 C0.224,6 0,5.776 0,5.5 L0,4.5 C0,4.224 0.224,4 0.5,4 L15.5,4 Z M15.5,8 C15.776,8 16,8.224 16,8.5 L16,9.5 C16,9.776 15.776,10 15.5,10 L0.5,10 C0.224,10 0,9.776 0,9.5 L0,8.5 C0,8.224 0.224,8 0.5,8 L15.5,8 Z"></path></g></g></g></g></g></svg>
				</button>
			</div>
		</div>
		<div class="item find">
			<form action="/search" class="form-inline" id="search-form">
				<div class="input-group">
					<input class="form-control" id="search-q" name="q" placeholder="Search" title="Search (use '/' to focus)" type="text">
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
	// $menu_list = [];
	// $menu_list[] = [ 'name' => '', 'link' => '' ];
	// $menu_list[] = [ 'name' => '', 'link' => '' ];
	// $menu_list[] = [ 'name' => '', 'link' => '' ];
	// $menu_list[] = [ 'name' => '', 'link' => '' ];
	// $menu_list[] = [ 'name' => '', 'link' => '' ];
	// $menu_list[] = [ 'name' => '', 'link' => '' ];
	// $menu_list[] = [ 'name' => '', 'link' => '' ];

	foreach ($menu_list as $menu) {

		if (empty($menu['id'])) {
			$menu['id'] = 'menu-' . trim(preg_replace('/[^\w]+/', '-', $menu['link']), '-');
		}

		$menu['pick'] = ($menu['link'] == substr(Radix::$path, 0, strlen($menu['link']))) ? ' active' : '';

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
</div>
</div>

<?php
// Flash Messages
// $x = Session::flash();
// if (!empty($x)) {

// 	$x = str_replace('class="info"', 'class="alert alert-info"', $x);
// 	$x = str_replace('class="warn"', 'class="alert alert-warning"', $x);
// 	$x = str_replace('class="fail"', 'class="alert alert-danger"', $x);

// 	echo '<div class="radix-flash" style="background: #fff; border 1px solid #333; border-radius: 0.5rem; left: 2vw; margin:0; padding: 1vh 1vw; position: absolute; right: -2vw; top: 0; z-index: 1000;">';
// 		echo '<div>';
// 			echo $x;
// 		echo '</div>';
// 		echo '<div>';
// 			echo '<button>X</button>';
// 		echo '</div>';
// 	echo '</div>';

// }

echo Radix::block('app-modal');

?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.15/lodash.min.js" integrity="sha256-VeNaFBVDhoX3H+gJ37DpT/nTuZTdjYro9yBruHjVmoQ=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js" integrity="sha256-KM512VNnjElC30ehFwehXjx1YCHPiQkOPmqnrWtpccM=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js" integrity="sha256-fzFFyH01cBVPYzl16KT40wqjhgPtq6FFUB6ckN2+GGw=" crossorigin="anonymous"></script>
<!-- @deprecated moment, can use browser built-ins now? -->
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.27.0/moment.min.js" integrity="sha512-rmZcZsyhe0/MAjquhTgiUcb4d9knaFc7b5xAfju483gbEXTkeJRUMIPk6s3ySZMYUHEcjKbjLjyddGWMrNEvZg==" crossorigin="anonymous"></script> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/jquery.dataTables.min.js" integrity="sha256-t5ZQTZsbQi8NxszC10CseKjJ5QeMw5NINtOXQrESGSU=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/dataTables.bootstrap4.min.js" integrity="sha256-hJ44ymhBmRPJKIaKRf3DSX5uiFEZ9xB/qx8cNbJvIMU=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.6/clipboard.min.js" integrity="sha512-hDWGyh+Iy4Mr9AHOzUP2+Y0iVPn/BwxxaoSleEjH/i1o4EVTF/sh0/A1Syii8PWOae+uPr+T/KHwynoebSuAhw==" crossorigin="anonymous"></script>
<script src="https://www.gstatic.com/charts/loader.js"></script>
<script>
var Clippy;
$(function() {

	// Menu Toggler
	$('#menu-left-mode').on('click', function() {

		var mode0 = this.dataset.mode || 'open';
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

		$('#menu-left').removeClass('open mini shut');
		$('#menu-left').addClass(mode1);
		this.dataset.mode = mode1;

		$.post('/dashboard/ajax', {
			a: 'menu-mode',
			mode: mode1
		});

	});

	// Time View
	$('.time-from-now').each(function(i, n) {
		var t0 = n.textContent;
		var t1 = moment(t0).fromNow();
		n.textContent = t1;
	});

	$('.time-to-now').each(function(i, n) {
		var t0 = n.textContent;
		var t1 = moment(t0).toNow();
		n.textContent = t1;
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
// JS
if (is_file(APP_ROOT . '/webroot/js/app.js')) {
	echo '<script src="/js/app.js?v=' . APP_VER . '"></script>';
} else {
	// What JS goes Here?
}

$buf = Layout::getScript();
if (!empty($buf)) {
	echo "\n$buf\n";
}

//require_once(__DIR__ . '/dump.php');

?>
</body>
</html>
