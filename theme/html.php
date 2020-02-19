<?php
/**
	OpenTHC HTML Layout
*/

use Edoceo\Radix;
use Edoceo\Radix\Layout;
use Edoceo\Radix\Session;

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="viewport" content="initial-scale=1, user-scalable=yes">
<meta name="application-name" content="OpenTHC">
<meta name="apple-mobile-web-app-title" content="OpenTHC">
<meta name="msapplication-TileColor" content="#247420">
<meta name="theme-color" content="#247420">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.openthc.com/jqueryui/1.12.1/jqueryui.css" integrity="sha256-rByPlHULObEjJ6XQxW/flG2r+22R5dKiAoef+aXWfik=" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.openthc.com/bootstrap/4.4.1/bootstrap.css" integrity="sha256-L/W5Wfqfa0sdBNIKN9cG6QA5F2qx4qICmU2VgLruv9Y=" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/css/dataTables.bootstrap4.min.css" integrity="sha256-F+DaKAClQut87heMIC6oThARMuWne8+WzxIDT7jXuPA=" crossorigin="anonymous">
<style>
/**
	Footer Stuff
*/
footer {
	background: #000700;
	border-top: 6px solid #192;
	font-size: 130%;
	margin-top: 8em;
	padding: 1em 0 0 0;
}
footer a {
  color: #fff;
}
footer ul {
  list-style-type: none;
  margin: 1em;
}
footer ul li {
  padding: 0.25em;
}

div.main-foot,
footer.home-mini  /* @deprecated name */
{

	background: #000700;
	border-top: 2px solid #070;
	line-height: 2.5em;
	margin: 2vh 0 0 0;
	padding: 0;
	text-align: center;
}

div.main-foot footer {
	background: none;
	border: none;
	font-size: 100%;
	padding: 1em 0 0 0;
}

td.r, th.r {
	text-align: right;
}

.otd-chart {
	border: 1px solid #333;
	height: 480px;
}
</style>
<script src="https://cdn.openthc.com/jquery/3.4.1/jquery.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
<script src="https://cdn.openthc.com/jqueryui/1.12.1/jqueryui.js" integrity="sha256-KM512VNnjElC30ehFwehXjx1YCHPiQkOPmqnrWtpccM=" crossorigin="anonymous"></script>
<script src="https://cdn.openthc.com/bootstrap/4.4.1/bootstrap.js" integrity="sha256-OUFW7hFO0/r5aEGTQOz9F/aXQOt+TwqI1Z4fbVvww04=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/jquery.dataTables.min.js" integrity="sha256-t5ZQTZsbQi8NxszC10CseKjJ5QeMw5NINtOXQrESGSU=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/dataTables.bootstrap4.min.js" integrity="sha256-hJ44ymhBmRPJKIaKRf3DSX5uiFEZ9xB/qx8cNbJvIMU=" crossorigin="anonymous"></script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<?= Layout::getScript('head'); ?>
<title><?= $_ENV['title'] ?></title>
</head>
<body>

<?= Radix::block('menu-zero.php'); ?>

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
</div>

<?= $this->body ?>

<?= Radix::block('footer') ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/plotly.js/1.42.5/plotly.min.js" integrity="sha256-yICXHX3uUb4vhDwkz3XGtdQIjf/Hfm3IhPk0Snldjwk=" crossorigin="anonymous"></script>
<?= Layout::getScript(); ?>
<script>
$(function() {

	// Autocomplete for Vendor Names
	$('.license-autocomplete').autocomplete({
		source: '//directory.openthc.com/api/autocomplete/license',
		select: function(e, ui) {
			$(e.target).val(ui.item.value);
			return false;
		}
	});

});
</script>
</body>
</html>
