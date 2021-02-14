<?php
/**
 * Show Rank of B2B Sales
 */

$month_count = 6;
$limit_count = 100;

$dbc = _dbc();

$mon_list = $dbc->fetchAll("SELECT DISTINCT month FROM license_revenue ORDER BY month DESC LIMIT $month_count");
if (empty($mon_list)) {
	echo '<div class="container mt-4">';
	echo '<div class="alert alert-danger">No Revenue Data is currently loaded</div>';
	echo '</div>';
	return(0);
}


$sql = <<<SQL
SELECT ROW_NUMBER() OVER(ORDER BY sum(license_revenue_full.rev_amount_sum) DESC) AS rank
, sum(license_revenue_full.rev_amount_sum) AS rev
, license_id
, license_name
, city AS license_city
, county AS license_county
FROM license_revenue_full
WHERE month = :m AND license_type != :lt
GROUP BY license_id, license_name, city, county
ORDER BY 1
LIMIT $limit_count * 2
SQL;

$sql = <<<SQL
SELECT ROW_NUMBER() OVER(ORDER BY license_revenue.rev_amount DESC) AS rank
, license.id AS license_id
, license.name AS license_name
, license_revenue.month
, license_revenue.rev_amount AS rev
, license.address_meta ->> 'city'::text AS license_city
, license.address_meta ->> 'county'::text AS license_county
FROM license
JOIN license_revenue ON license.id = license_revenue.license_id
WHERE month = :m
ORDER BY license_revenue.rev_amount DESC
LIMIT $limit_count * 2
SQL;


$rev_license = [];
foreach ($mon_list as $mon) {

	// $arg = array(':m' => $mon['month'], ':lt' => '-');
	$arg = array(':m' => $mon['month']);
	$res = _select_via_cache($dbc, $sql, $arg);
	// var_dump($res);
	// exit;

	foreach ($res as $rec) {

		if (empty($rev_license[ $rec['license_id'] ])) {
			$rev_license[ $rec['license_id'] ] = [
				'license_id' => $rec['license_id'],
				'license_name' => $rec['license_name'],
				'license_city' => $rec['license_city'],
				'license_county' => $rec['license_county'],
				'revenue_full' => floatval($rec['rev']),
				'revenue_list' => array(
					$rec['month'] => floatval($rec['rev']),
				),
				'revenue_mean' => 0,
				'rank_list' => [
					$rec['month'] => $rec,
				],
			];
		} else {
			$rev_license[ $rec['license_id'] ]['rank_list'][ $rec['month'] ] = $rec;
			$rev_license[ $rec['license_id'] ]['revenue_list'][ $rec['month'] ] = $rec['rev'];
			$rev_license[ $rec['license_id'] ]['revenue_full'] += $rec['rev'];
		}
	}
}


// Reverse order of Months to get Compare to previous month
$mon_list = array_reverse($mon_list);
foreach ($rev_license as $lid => $L) {

	$mon_prev = null;

	foreach ($mon_list as $mon) {
		$mon = $mon['month'];
		if (!empty($mon_prev)) {

			$v0 = $L['rank_list'][ $mon_prev ]['rev'];
			$v1 = $L['rank_list'][ $mon ]['rev'];

			if ($v0 > 0) {
				$d = ($v1 - $v0) / $v0 * 100;
				if ($d > 10) {
					$L['rank_list'][ $mon ]['rev_delta'] = sprintf(' <span class="text-success" style="font-weight:bold;">%0.1f%%</span>', $d);
				} elseif ($d > 0) {
					$L['rank_list'][ $mon ]['rev_delta'] = sprintf(' <span class="text-success">%0.1f%%</span>', $d);
				} else {
					$L['rank_list'][ $mon ]['rev_delta'] = sprintf(' <span class="text-danger">%0.1f%%</span>', $d);
				}
			} else {
				$L['rank_list'][ $mon ]['rev_delta'] = '-';
			}

			$r0 = $L['rank_list'][ $mon_prev ]['rank'];
			$r1 = $L['rank_list'][ $mon ]['rank'];
			$L['rank_list'][ $mon ]['delta_rank'] = $r0 - $r1;

		}
		$mon_prev = $mon;
	}

	$rev_license[$lid] = $L;

}

array_walk($rev_license, function(&$v, $k) {
	$v['revenue_mean'] = array_sum($v['revenue_list']) / count($v['revenue_list']);
	// $v['revenue_rank_mean'] = array_sum($v['rank_])
});

// uasort($rev_license, function($a, $b) {
// 	return ($a['revenue_mean'] < $b['revenue_mean']);
// });

$rev_license = array_slice($rev_license, 0, $limit_count);


// Put back in the right order
$mon_list = array_reverse($mon_list);
?>

<div class="container-fluid">

<p>Showing Top 50 for most recent month, with history - average per month, and sum of previous <?= $month_count ?> counts</p>

<form>
<div class="form-inline mb-2">
	<select class="form-control mr-2" name="license-type">
		<option value="">- All License -</option>
		<option value="!R">Supply Side</option>
		<option value="R">Retail Side</option>
	</select>
	<input class="form-control mr-2" name="d0" type="date" value="<?= $mon_list[5]['month'] ?>">
	<input class="form-control mr-2" name="d1" type="date" value="<?= $mon_list[0]['month'] ?>">
	<button class="btn btn-outline-secondary"><i class="fas fa-search"></i></button>
</div>
</form>

<table class="ui table">
<thead>
	<tr>
		<th>Name</th>
		<?php
		foreach ($mon_list as $mon) {
			echo '<th class="c">' . _date('F-Y', $mon['month']) . '</th>';
		}
		?>
		<th class="r" title="Monthly Average Revenue"><i style="text-decoration: overline;">x</i>/mo</th>
		<th class="r" title="Revenue Sum">&sum;<?= $month_count ?>mo</th>
	</tr>
</thead>
<tbody>
<?php
foreach ($rev_license as $lic) {
?>
	<tr>
		<td><a href="/license/<?= $lic['license_id'] ?>"><?= $lic['license_name'] ?></a>
			<br><?= $lic['license_city'] ?>
			<br><?= h($lic['license_county']) ?>
		</td>
		<?php
		foreach ($mon_list as $mon) {
			$m = $mon['month'];
			$out = sprintf('$%s<br>#%d (%+d)<br>%s', number_format($lic['rank_list'][$m]['rev']), $lic['rank_list'][$m]['rank'], $lic['rank_list'][$m]['delta_rank'], $lic['rank_list'][$m]['rev_delta']);
			$out = str_replace(' (+0)', null, $out);
			echo '<td class="c">';
			echo $out;
			echo '</td>';
		}
		?>
		<td class="r"><?= number_format($lic['revenue_mean']) ?></td>
		<td class="r"><?= number_format($lic['revenue_full']) ?></td>
	</tr>
<?php
}
?>
</tbody>
</table>

</div>
