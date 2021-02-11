<?php


function _revenue_nav_tabs()
{
	$path = $_SERVER['REQUEST_URI'];
	// echo '<pre>' . h($path) . '</pre>';
?>
<ul class="nav nav-tabs">
	<li class="nav-item"><a class="nav-link <?= ($path == '/revenue' ? 'active' : null) ?>" href="/revenue">Revenue</a></li>
	<li class="nav-item"><a class="nav-link <?= ($path == '/revenue/company' ? 'active' : null) ?>" href="/revenue/company">Companies</a></li>
	<li class="nav-item"><a class="nav-link <?= ($path == '/revenue/license' ? 'active' : null) ?>" href="/revenue/license">Licensees</a></li>
	<!-- <li class="nav-item"> <a class="item <?= ($path == '/revenue/county' ? 'active' : null) ?>" href="/revenue/county">by County</a></li> -->
	<!-- <li class="nav-item"><a class="item <?= ($path == '/revenue/city' ? 'active' : null) ?>" href="/revenue/city">by City</a></li> -->
</ul>
<?php
}
