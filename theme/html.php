<?php
/**
 * OpenTHC HTML Layout
 */

use Edoceo\Radix;
use Edoceo\Radix\Layout;
use Edoceo\Radix\Session;

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="content-language" content="en">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="viewport" content="initial-scale=1, user-scalable=yes">
<meta name="application-name" content="OpenTHC">
<meta name="apple-mobile-web-app-title" content="OpenTHC">
<meta name="msapplication-TileColor" content="#247420">
<meta name="theme-color" content="#247420">
<meta name="google" content="notranslate">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.3/css/bootstrap.min.css" integrity="sha512-oc9+XSs1H243/FRN9Rw62Fn8EtxjEYWHXRvjS43YtueEewbS6ObfXcJNyohjHqVKFPoXXUxwc+q1K7Dee6vv9g==" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.css" integrity="sha512-/zs32ZEJh+/EO2N1b0PEdoA10JkdC3zJ8L5FTiQu82LR9S/rOQNfQN7U59U9BC12swNeRAz3HSzIL2vpp4fv3w==" crossorigin="anonymous" />
<link rel="stylesheet" href="https://cdn.openthc.com/css/www/0.0.1/www.css">
<style>

footer .foot-copy {
	background: #333;
	margin: 0;
	padding: 1em;
}
footer .foot-copy p {
	color: #f0f0f0;
	margin: 0;
	padding: 0;
	text-align: center;
}

.otd-chart {
	border: 1px solid #333;
	height: 480px;
}
.stat-group-wrap {
	display: flex;
	flex-wrap: wrap;
}
.stat-group-wrap .stat-item {
	flex: 1 1 auto;
}

@media print {
	footer {
		background: #fff;
		font-size: 100%;
		margin: 0;
	}
	footer a {
		color: #333;
		text-decoration: none;
	}
	footer .foot-copy {
		background: #fff;
		padding: 0.25rem 0 0 0;
	}
	footer .foot-copy p {
		color: #333;
	}

}
</style>
<script src="https://cdn.openthc.com/jquery/3.4.1/jquery.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.3/js/bootstrap.min.js" integrity="sha512-8qmis31OQi6hIRgvkht0s6mCOittjMa9GMqtK9hes5iEQBQE/Ca6yGE5FsW36vyipGoWQswBj/QBm2JR086Rkw==" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js" integrity="sha512-d9xgZrVZpmmQlfonhQUvTR7lMPtO7NkZMkA0ABN3PHCbKA5nqylQ/yWlFAyY6hYgdF1Qh6nYiuADWwKB4C2WSw==" crossorigin="anonymous"></script>
<script type="text/javascript" src="/js/chart.js"></script>
<?= Layout::getScript('head'); ?>
<title><?= $_ENV['title'] ?></title>
</head>
<body>

<?= Radix::block('menu-zero'); ?>

<div class="container-fluid">
<?php
if (!empty($_ENV['h1'])) {
	echo '<h1>' . $_ENV['h1'] . '</h1>';
}

// Flash Messages
$x = Session::flash();
if (!empty($x)) {
	echo '<div class="alert radix-flash">';
	echo $x;
	echo '</div>';
}
?>

<?= $this->body ?>

</div>

<?= Radix::block('footer') ?>

<?= Layout::getScript(); ?>

<script>
$(function() {

	// Autocomplete for Vendor Names
	// $('.license-autocomplete').autocomplete({
	// 	source: '//directory.openthc.com/api/autocomplete/license',
	// 	select: function(e, ui) {
	// 		$(e.target).val(ui.item.value);
	// 		return false;
	// 	}
	// });

});
</script>
</body>
</html>
