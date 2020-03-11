<?php
/**
 * B2C Page
 */

$_ENV['h1'] = $_ENV['title'] = 'B2C';

session_write_close();

_b2c_tabs();

require_once(__DIR__ . '/index-chart.php');

require_once(__DIR__ . '/index-rank.php');
