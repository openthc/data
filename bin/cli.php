#!/usr/bin/php
<?php
/**
 * Command Line Bootstrapper
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Data\CLI;

require_once(dirname(dirname(__FILE__)) . '/boot.php');

error_reporting(E_ALL & ~ E_NOTICE);

$doc = <<<DOC
OpenTHC OPS Command Line

Usage:
	cli <command> [<command-options>...]

Options:
	Options are specific to the sub-command chosen.
	Get a list by passing an unknown command (eg: list-all)

DOC;
$res = \Docopt::handle($doc, [
	'help' => true,
	'optionsFirst' => true,
]);
$cli_args = $res->args;

// $cli = new \OpenTHC\CLI();
// $cli->setAction($action);

$action = $cli_args['<command>'];
switch ($action) {
case 'convert':
	$cli = new \OpenTHC\Data\CLI\Convert($cli_args['<command-options>']);
	$cli->execute();
	break;
case 'import':
	$cli = new \OpenTHC\Data\CLI\Import($cli_args['<command-options>']);
	$cli->execute();
	break;
case 'list':
	// How to Iterate all the CLI Objects?
	break;
default:
	// Error?
	break;
}
// $action_path = sprintf('%s/lib/CLI', APP_ROOT);
// $action_file = sprintf('%s/%s.php', $action_path , $action);
