<?php
/**
 *
 */

namespace App;

class UI
{
	/**
	 *
	 */
	static function b2b_tabs()
	{
		ob_start();
?>
<ul class="nav nav-tabs">
	<li class="nav-item">
	<a class="nav-link" href="/b2b">B2B</a>
	</li>
	<li class="nav-item">
	<a class="nav-link" href="/b2b/flower">Flower</a>
	</li>
	<li class="nav-item">
	<a class="nav-link" href="/b2b/extract">Extract</a>
	</li>
	<li class="nav-item">
	<a class="nav-link" href="/b2b/edible">Edible</a>
	</li>
	<!-- <li class="nav-item">
	<a class="nav-link<?= ('supply' == $lic_type ? ' active' : null) ?>" href="/b2b/average-revenue">Averages</a>
	</li> -->
	<!-- <li class="nav-item">
	<a class="nav-link<?= ('supply' == $lic_type ? ' active' : null) ?>" href="/b2b/supply-to-retail">S2R</a>
	</li> -->
</ul>
<?php
		return ob_get_clean();
	}

	/**
	 *
	 */
	static function b2b_transfer_tabs()
	{
		ob_start();
?>
<ul class="nav nav-tabs">
	<li class="nav-item">
	<a class="nav-link" href="/b2b/transfer?<?= http_build_query($_GET) ?>">Overview</a>
	</li>
	<li class="nav-item">
	<a class="nav-link" href="/b2b/transfer-detail?<?= http_build_query($_GET) ?>"> Transfer Details</a>
	</li>
</ul>
<?php
		return ob_get_clean();
	}

	/**
	 *
	 */
	static function b2c_tabs()
	{
		ob_start();
	?>
<ul class="nav nav-tabs">
	<li class="nav-item">
	<a class="nav-link" href="/b2c">B2C</a>
	</li>
	<li class="nav-item">
	<a class="nav-link" href="/b2c/flower">Flower</a>
	</li>
	<li class="nav-item">
	<a class="nav-link" href="/b2c/extract">Extract</a>
	</li>
	<li class="nav-item">
	<a class="nav-link" href="/b2c/edible">Edible</a>
	</li>
</ul>
	<?php
		return ob_get_clean();
	}


	/**
	 *
	 */
	static function lab_tabs()
	{
		ob_start();
	?>
<ul class="nav nav-tabs">
	<li class="nav-item">
	<a class="nav-link" href="/lab">Lab</a>
	</li>
	<li class="nav-item">
	<a class="nav-link" href="/lab/frequency">Frequency</a>
	</li>
	<li class="nav-item">
	<a class="nav-link" href="/lab/potency">Potency</a>
	</li>
	<li class="nav-item">
	<a class="nav-link" href="/lab/usage">Usage</a>
	</li>
	<li class="nav-item">
	<a class="nav-link" href="/lab/attested">Attested</a>
	</li>
</ul>
	<?php
		return ob_get_clean();
	}

	/**
	 *
	 */
	static function license_info($L)
	{
		$m = $L['address_meta'];
		if (is_string($m)) {
			$m = json_decode($m, true);
		}

		ob_start();
	?>
<div class="license-info">
<h2><a href="/license/<?= $L['id'] ?>"><?= h($L['name']) ?></a> - <?= h($L['code']) ?></h2>
<!--
<p><?= $m['full'] ?> <a href="https://directory.openthc.com/map?<?= http_build_query([ 'q' => $m['full'] ]) ?>" target="_blank"><i class="fas fa-map"></i></a></p>
-->
<!-- <pre>
	<?php
	var_dump($L);
	?>
</pre> -->
</div>
<?php
		return ob_get_clean();
	}



	static function license_tabs($L)
	{
		$active = 'single';

		$p = strtok($_SERVER['REQUEST_URI'], '?');
		$b = basename($p);
		switch ($b) {
			case 'clients':
			case 'vendors':
			case 'map':
				$active = $b;
		}

		ob_start();
	?>
<ul class="nav nav-tabs">
	<li class="nav-item">
	<a class="nav-link<?= ('single' == $active ? ' active' : null) ?>" href="/license/<?= $L['id'] ?>">Overview</a>
	</li>
	<li class="nav-item">
	<a class="nav-link<?= ('vendors' == $active ? ' active' : null) ?>" href="/license/<?= $L['id'] ?>/vendors">Vendors</a>
	</li>
	<li class="nav-item">
	<a class="nav-link<?= ('clients' == $active ? ' active' : null) ?>" href="/license/<?= $L['id'] ?>/clients">Clients</a>
	</li>
	<li class="nav-item">
	<a class="nav-link<?= ('map' == $active ? ' active' : null) ?>" href="/license/<?= $L['id'] ?>/map">Map</a>
	</li>
</ul>
	<?php
		return ob_get_clean();
	}


	/**
	 *
	 */
	static function revenue_nav_tabs()
	{
		ob_start();

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
		return ob_get_clean();
	}

}
