<?php
/**
 * Laboratory Details
 */

$_ENV['h1'] = $_ENV['title'] = 'Lab Results';

echo \App\UI::lab_tabs();

echo '<div class="mt-4">';
require_once(__DIR__ . '/main-chart-result-count-column-stacked.php');
echo '</div>';

// echo '<div class="row">';

// echo '<div class="col-md-6">';
// require_once(__DIR__ . '/chart-result-count-6mo-pie.php');
// echo '</div>';

// echo '<div class="col-md-6">';
// require_once(__DIR__ . '/chart-result-count-3mo-pie.php');
// echo '</div>';

// echo '</div>';

// echo '<hr>';

// require_once(__DIR__ . '/table-at4-at5-lrx.php');

return(0);

?>

<!-- <div class="container-fluid mt-2">
<iframe
    allowtransparency
    src="https://meta.openthc.com/public/question/ad1b938a-7c5f-48ab-99dc-a28676d04647#theme=night&bordered=false&titled=false"
    frameborder="0"
    height="600"
	style="width:100%;"
></iframe>
</div> -->

<?php



$sql = <<<SQL
SELECT type AS result_type, count(id) AS result_count from lab_result group by type order by 2 DESC;
SQL;
//
//  count  |                        type
// --------+----------------------------------------------------
//  214846 | marijuana/
//   76499 | intermediate_product/flower
//    7518 | harvest_materials/flower_lots
//    1628 | end_product/usable_marijuana
//     770 | end_product/concentrate_for_inhalation
//     448 | end_product/solid_edible
//     287 | intermediate_product/marijuana_mix
//     259 | intermediate_product/non-solvent_based_concentrate
//     159 | end_product/infused_mix
//      93 | end_product/packaged_marijuana_mix
//      65 | end_product/topical
//      45 | end_product/sample_jar
//      41 | intermediate_product/infused_cooking_medium
//      17 | end_product/capsules
//      11 | harvest_materials/flower
//       6 | marijuana/flower
//       4 | end_product/liquid_edible
//       3 | intermediate_product/co2_concentrate
//       2 | end_product/tinctures


//$res = $dbc->fetch_all($sql);
$res = _select_via_cache($dbc, $sql, null);

echo '<div class="container mt-2">';
echo '<h2>Samples Count by Type</h2>';
echo _res_to_table($res);
echo '</div>';


$sql = <<<SQL
select license_id AS lab_global_id
, license.code AS lab_license_code
, license.name AS lab_name
, count(lab_result.id) AS lab_result_count
FROM lab_result
JOIN license ON lab_result.license_id = license.id
WHERE license.code LIKE 'L%'
GROUP BY license_id, license.code, license.name
ORDER BY lab_result_count DESC
SQL;

//$res = $dbc->fetch_all($sql);
$res = _select_via_cache($dbc, $sql, null);

// var_dump($res);
echo '<div class="container mt-2">';
echo '<h2>Samples Count by Lab</h2>';
_res_to_table($res);
echo '</div>';
