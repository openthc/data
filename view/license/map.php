<?php
/**
 * Show a Map of A and B
 */

$_ENV['title'] = 'License :: Relationship Map';

$map_api_key = \OpenTHC\Config::get('google/map_api_key');

if (!_acl($_SESSION['acl_subject'], 'license', 'view-map')) {
	_exit_html('Please <a href="/auth/open">sign-in</a> to view more details', 403);
}

$dbc = _dbc();

$L = $dbc->fetchRow('SELECT * FROM license WHERE id = ?', [ $_GET['id'] ]);
if (empty($L['id'])) {
	_exit_text('Invalid License', 400);
}

$do_client = false;
$do_vendor = false;

switch ($_GET['view']) {
case 'clients':
	$do_client = true;
	break;
case 'vendors':
	$do_vendor = true;
	break;
}

// $Clients
$res_client_list = [];
if ($do_client) {

	$sql = <<<SQL
SELECT DISTINCT license.id AS license_id, license.name AS license_name, license.lat, license.lon
FROM b2b_sale
JOIN license ON b2b_sale.license_id_target = license.id
WHERE b2b_sale.license_id_source = :l0 AND b2b_sale.stat IN ('in-transit', 'ready-for-pickup', 'received')
AND execute_at >= now() - '12 months'::interval
AND full_price > 0
LIMIT 100
SQL;

	$arg = [
		':l0' => $L['id'],
	];

	$res_client_list = _select_via_cache($dbc, $sql, $arg);

}

// Vendors
$res_vendor_list = [];
if ($do_vendor) {

	$sql = <<<SQL
SELECT DISTINCT license.id AS license_id, license.name AS license_name, license.lat, license.lon
FROM b2b_sale
JOIN license ON b2b_sale.license_id_source = license.id
WHERE b2b_sale.license_id_target = :l0 AND b2b_sale.stat IN ('in-transit', 'ready-for-pickup', 'received')
AND execute_at >= now() - '12 months'::interval
AND full_price > 0
LIMIT 100
SQL;

	$arg = [
		':l0' => $L['id'],
	];

	$res_vendor_list = _select_via_cache($dbc, $sql, $arg);

}

?>

<div class="container-fluid mt-2">
<?= _license_info($L) ?>
<?= _menu_license_tabs($L) ?>

<div id="google-map" style="background: #999; border: 1px solid #333; height: 85vh; width: 100%;"></div>
</div>

<div class="container-fluid mt-2">
<div class="row">

<div class="col-md-6">
<table class="table table-sm">
<?php
$idx = 0;
// foreach ($res_vendor_list as $c) {
foreach ([] as $c) {
	$idx++;
?>
	<tr>
		<td><?= $idx ?></td>
		<td><a href="/license?id=<?= $c['license_id'] ?>"><?= h($c['license_name']) ?></a></td>
		<td>
	</tr>
<?php
}
?>
</table>
</div>

<div class="col-md-6">
<table class="table table-sm">
<?php
$idx = 0;
// foreach ($res_client_list as $c) {
foreach ([] as $c) {
	$idx++;
?>
	<tr>
		<td><?= $idx ?></td>
		<td><a href="/license?id=<?= $c['license_id'] ?>"><?= h($c['license_name']) ?></a></td>
		<td>
	</tr>
<?php
}
?>
</table>
</div>

</div>
</div>


<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?= $map_api_key ?>&amp;libraries=places"></script>
<script>
var head = document.getElementsByTagName('head')[0];

// Save the original method
var insertBefore = head.insertBefore;

// Replace it!
head.insertBefore = function (newElement, referenceElement) {

	if (newElement.href && newElement.href.indexOf('//fonts.googleapis.com/css?family=Roboto') > -1) {
		console.info('Prevented Roboto from loading!');
		return;
	}

	insertBefore.call(head, newElement, referenceElement);
};
</script>
<script type="text/javascript" src="https://directory.openthc.com/js/map-marker.js"></script>
<script>

var L0 = <?= json_encode($L) ?>;


var G_Map = null;
var G_Inf = new google.maps.InfoWindow({
	content: '<h2>Marker</h2>'
});

var Map_Line_List = [];

function draw_line(p0, p1)
{
	var o = {
		clickable: false,
		geodesic: true,
		strokeColor:'#247420',
		strokeOpacity: 0.9,
		strokeWeight: 2
	};

	o.path = [p0, p1];
	var l = new google.maps.Polyline(o);

	Map_Line_List.push(l);

	return l;

}

$(function() {

	var div = document.getElementById('google-map');
	var opt = {
		// draggable: false,
		keyboardShortcuts: false,
		// mapTypeControl: false,
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		navigationControl: false,
		// overviewMapControl: false,
		// panControl: false,
		rotateControl: false,
		// scaleControl: false,
		// scrollwheel: false,
		streetViewControl: false,
		styles: [
			{
				featureType: "poi",
				elementType: "labels",
				stylers: [
					{
						visibility: "off"
					}
				]
			}
		],
		zoom: 7,
		zoomControlOptions:{
			style: google.maps.ZoomControlStyle.SMALL
		}
	};

	var pt0 = new google.maps.LatLng(<?= $L['lat'] ?>, <?= $L['lon'] ?>);

	G_Map = new google.maps.Map(div, opt);
	G_Map.setCenter(pt0);

	var mk0 = marker_create({
		license_type: 'X',
		marker: {
			mark: 'https://maps.google.com/mapfiles/ms/micons/blue.png',
		},
		name: L0.name,
		geo_lat: L0.lat,
		geo_lon: L0.lon
	});
	mk0.setMap(G_Map);

	var client_list = <?= json_encode($res_client_list) ?>;
	var idx = 0;
	var max = client_list.length;

	for (idx=0; idx<max; idx++) {

		var l1 = client_list[idx];
		var pt1 = new google.maps.LatLng(l1.lat, l1.lon);

		var mk1 = new google.maps.Marker({
			//animation: google.maps.Animation.BOUNCE,
			//animation: google.maps.Animation.DROP,
			draggable:false,
			dragCrossMove:false,
			label: 'C',
			icon: {
				url: 'https://maps.google.com/mapfiles/ms/micons/green.png',
				labelOrigin: new google.maps.Point(16, 10)
			},
			position: pt1,
			license: {
				id: l1.license_id,
				name: l1.license_name
			}
		});
		mk1.addListener('click', function() {
			// this == the marker
			var html = '';
			html += '<div>';
			html += '<h2>';
			html += '<a href="/license?id=' + this.license.id + '">';
			html += this.license.name;
			html += '</a>';
			html += '</h2>';
			// html += '<h3>Company: #' + mark._otd.company_guid + '</h3>';
			// html += '<h3>License: #' + mark._otd.license_code + '</h3>';
			html += '</div>';

			G_Inf.open(G_Map, this);
			G_Inf.setPosition( this.getPosition() );
			G_Inf.setContent(html);

		});

		mk1.setMap(G_Map);

		var pl0 = draw_line(pt0, pt1);
		pl0.setMap(G_Map);
	}

	var vendor_list = <?= json_encode($res_vendor_list) ?>;
	var idx = 0;
	var max = vendor_list.length;

	for (idx=0; idx<max; idx++) {

		var l1 = vendor_list[idx];
		var pt1 = new google.maps.LatLng(l1.lat, l1.lon);

		var mk1 = new google.maps.Marker({
			//animation: google.maps.Animation.BOUNCE,
			//animation: google.maps.Animation.DROP,
			draggable:false,
			dragCrossMove:false,
			label: 'V',
			icon: {
				url: 'https://maps.google.com/mapfiles/ms/micons/orange.png',
				labelOrigin: new google.maps.Point(16, 10)
			},
			position: pt1,
			license: {
				id: l1.license_id,
				name: l1.license_name
			}
		});
		mk1.addListener('click', function() {
			// this == the marker
			var html = '';
			html += '<div>';
			html += '<h2>';
			html += '<a href="/license?id=' + this.license.id + '">';
			html += this.license.name;
			html += '</a>';
			html += '</h2>';
			// html += '<h3>Company: #' + mark._otd.company_guid + '</h3>';
			// html += '<h3>License: #' + mark._otd.license_code + '</h3>';
			html += '</div>';

			G_Inf.open(G_Map, this);
			G_Inf.setPosition( this.getPosition() );
			G_Inf.setContent(html);

		});

		mk1.setMap(G_Map);

		var pl0 = draw_line(pt0, pt1);
		pl0.setMap(G_Map);
	}

});
</script>
