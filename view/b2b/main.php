<?php
/**
 * B2B Page
 */

$_ENV['h1'] = $_ENV['title'] = 'B2B Sales';

// require_once(__DIR__ . '/index-chart.php');

echo \App\UI::b2b_tabs();

require_once(__DIR__ . '/index-rank.php');
