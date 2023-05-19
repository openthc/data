<?php
/**
 * CSV Reader
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Data;

class CSV_Reader
{
	private $fh;
	private $sep;

	public $csv_head;

	public $key_list;
	public $key_size = 0;

	/**
	 *
	 */
	function __construct($csv_file, $arg=[])
	{
		$this->csv_file = $csv_file;

		$this->fh = _fopen_bom($this->csv_file, 'r'); // fopen($f, 'r');
		$this->sep = $this->_fpeek_sep($this->fh);

		// $map_file = preg_replace('/\.\w+$/', '.map', $f);
		// if (is_file($map_file)) {
		// 	die("read Header from MAP!\n");
		// }

		// // Header Row
		// $this->key_list = fgetcsv($this->fh, 0, $this->sep);
		// if ('global_id' != $this->key_list[0]) {
		// 	die("\nInvalid First header Row in ($f)\n");
		// }

		// $this->key_size = count($this->key_list);
	}

	/**
	 * Fetch Next Record
	 */
	function fetch($as='plain')
	{
		$ret = fgetcsv($this->fh, 0, $this->sep);
		if ( ! empty($ret)) {
			switch ($as) {
				case 'array':
					$ret = array_combine($this->csv_head, $ret);
					break;
				case 'object':
					// Object?
			}
		}

		return $ret;

	}

	/**
	 *
	 */
	function getHeader()
	{
		// Seek to Start?
		$row = $this->fetch();
		array_walk($row, function(&$v) {
			$v = strtoupper($v);
		});

		$this->csv_head = $row;

		return $row;

	}

	/**
	 * Get an Estimate of Rows
	 */
	function rowEstimate()
	{
		$cur = ftell($this->fh);

		$idx = 0;
		$max = 1000;

		while ($idx < $max) {
			$idx++;
			$row = fgets($this->fh);
			$len += strlen($row);
		}

		$avg = $len / $idx;

		$inf = fstat($this->fh);

		$est = $inf['size'] / $avg;

		fseek($this->fh, $cur);

		return $est;

	}

	/**
	 * Try to Guess what the separator is
	 */
	private function _fpeek_sep($fh) : string
	{
		$off = ftell($fh);
		$buf = fread($fh, 1000);
		fseek($fh, $off, SEEK_SET);

		$c_c = substr_count($buf, ',');
		$c_t = substr_count($buf, "\t");

		if (($c_t != 0) && ($c_c != 0)) {
			// It's way more tabs than commas
			if ($c_t > ($c_c * 2)) {
				return "\t";
			}
			die("_fpeek_sep() Cannot TelL Separator Type!! $c_t and $c_c\n");
		} elseif (($c_t > 0) && ($c_c == 0)) {
			return "\t";
		}

		return ',';
	}

}
