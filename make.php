#!/usr/bin/php
<?php
/**
 * Make Helper
 */

use OpenTHC\Make;

if ( ! is_file(__DIR__ . '/vendor/autoload.php')) {
	$cmd = [];
	$cmd[] = 'composer';
	$cmd[] = 'install';
	$cmd[] = '--classmap-authoritative';
	$cmd[] = '2>&1';
	echo "Composer:\n";
	passthru(implode(' ', $cmd), $ret);
	var_dump($ret);
}

require_once(__DIR__ . '/boot.php');

Make::composer();

Make::npm();

Make::install_bootstrap();

Make::install_fontawesome();

Make::install_jquery();
