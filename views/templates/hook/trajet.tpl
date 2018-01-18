<!-- <iframe src="https://www.google.com/maps/d/embed?mid=1-uZoJ1Jy6-Dcpp2WDRCaoBZUbDw" width="1220" height="540"></iframe> -->

<div id="wholeMap" class="container">
	<div>
		<div id="containerMap" class="col-lg-8 col-xs-12">
			<div id="map" style="height: 540px;"></div>
			<div id="infoWindow" class="lightBorderBottom">
				<div id="infoImage">
					<img style="width: 350px; height: 200px;" src="https://media-cdn.tripadvisor.com/media/photo-s/01/f5/9f/17/contis-plage.jpg"/>
				</div>
				<div id="infoText">
					<div id="infoTitle" class="hardBorderBottom">
						<div id="toggleArrow">
							<img src="{$smarty.const.__PS_BASE_URI__}modules/reservation/views/templates/hook/suivi/images/leftArrow.png">
						</div>
						<p id="namePlace"></p>
					</div>
					<p id="infoDescription" style="overflow-y: scroll;"></p>
				</div>
			</div>
		</div>

		<div id="sidebarMap" class="col-lg-4 col-xs-12 lightBorderBottom">
			<div>
				<p class='categorieMap hardBorderBottom'>Trajets</p>
				<form action="#" style="margin-left: 1em">
					<form action="#" style="margin-left: 1em">
						<fieldset>
							{foreach from=$trajet_fieldset item=fieldset}
							{$fieldset}
							{/foreach}
						</fieldset>
					</form>
				</div>
				<div>
					<p class='categorieMap hardBorderBottom'>Points d'intéret</p>
					<div class="listePartenaires">
						{foreach from=$right_sidebar item=sidebar}
						{$sidebar}
						{/foreach}
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
{literal}
<script>
	function initMap() {

		var defaultZoom = 10
		var defaultCenter = {lat: 44.04407, lng: -1.21973}
		/*var tiledLayer = new google.maps.ImageMapType({
			getTileUrl: function(tileCoord, zoom) {
				var url = "https://wxs.ign.fr/tiq960iwqhkiomx0isn30r8i/geoportail/wmts?" +
				"&REQUEST=GetTile&SERVICE=WMTS&VERSION=1.0.0" +
				"&STYLE=normal" +
				"&TILEMATRIXSET=PM" +
				"&FORMAT=image/jpeg"+
				"&LAYER=ORTHOIMAGERY.ORTHOPHOTOS"+
				"&TILEMATRIX=" + zoom +
				"&TILEROW=" + tileCoord.y +
				"&TILECOL=" + tileCoord.x ;
				return url
			},
			tileSize: new google.maps.Size(256,256),
			name: "IGNapi",
			maxZoom: 18
		});*/

		var map = new google.maps.Map(document.getElementById('map'), {
			zoom: defaultZoom,
			center: defaultCenter,
			mapTypeId: 'terrain'//,
		/*	mapTypeControlOptions: {
				mapTypeIds: ['IGNLayer']
			}*/
		})
		/*map.mapTypes.set('IGNLayer', tiledLayer);
		map.setMapTypeId('IGNLayer');
		var roadsLayer = [
		{
			featureType: 'all',
			stylers: [
			{ visibility: 'off' }
			]
		},
		{
			featureType: 'road',
			stylers: [
			{ visibility: 'on' }
			]
		}
		];

		var roadsType = new google.maps.StyledMapType(roadsLayer, { name: 'roads' });
		map.overlayMapTypes.push(roadsType);
		*/
		var keepMarker = null
		var keepInterval = null
		var keepBool = false

		var keepDisplay = new Array()
		var directionsDisplay = new google.maps.DirectionsRenderer({
			polylineOptions: {
				strokeColor: '#1C72A8',
				strokeWeight: 4.5
			},
			suppressMarkers: true,
			preserveViewport: true,
			map: map
		})
		var setPos = function() { google.maps.event.trigger(map, 'tilesloaded') }

		{/literal}
		{$variables_list}
		{literal}

		function geolocRoute(begin, end) {
			var request = {
				origin: new google.maps.LatLng(begin.lat, begin.lng),
				destination: new google.maps.LatLng(end.lat, end.lng),
				travelMode: google.maps.TravelMode.DRIVING
			};
			var keeper = directionsDisplay
			directionsDisplay = new google.maps.DirectionsRenderer({
				polylineOptions: {
					strokeColor: '#FFE400',
					strokeWeight: 3.5
				},
				suppressMarkers: true,
				preserveViewport: true,
				map: map
			})
			keepDisplay[keepDisplay.length] = directionsDisplay

			var directionsService = new google.maps.DirectionsService()

			directionsService.route(request, function(response, status) {
				var legs = response.routes[0].legs
				var totalDistance = 0
				var totalDuration = 0
				for (var i = 0; i < legs.length; i++) {
					totalDistance += Math.floor(legs[i].distance.value / 1000)
					totalDuration += legs[i].duration.value / 60
				}
				if (status == google.maps.DirectionsStatus.OK)
					directionsDisplay.setDirections(response);
				else
					console.log("Directions Request from " + start.toUrlValue(6) + " to " + end.toUrlValue(6) + " failed: " + status);
			})
		}

		//transfert les positions des objets locations en google.maps.LatLng
		function transferLatLng(element) {
			location = new google.maps.LatLng(element.lat, element.lng)
			return element
		}

		google.maps.event.addDomListener(map, 'tilesloaded', function() {
			if ($('#geolocationGM').length == 0) {
				$('div.gmnoprint').last().append('<div id="geolocationGM">Geolocalisation</div>');
				$('div.gmnoprint').css({
					opacity: 1
				});
				$('#geolocationGM').click(function activateGeoloc() {
					document.getElementById('infoWindow').style.width = 0
					if (navigator.geolocation) {
						if (keepMarker == null) {
							navigator.geolocation.getCurrentPosition(function sucessPosition(position) {
								var pos = {
									lat: position.coords.latitude,
									lng: position.coords.longitude
								}
								keepMarker = new google.maps.Marker({
									map: map,
									position: pos,
									animation: google.maps.Animation.no,
									icon: window.baseDir + 'modules/reservation/views/templates/hook/suivi/images/iconvelo.png'
								})

								keepInterval = setInterval(function() {
									if (keepBool != 0)
										return
									keepBool = 1
									navigator.geolocation.getCurrentPosition(function sucessPosition(position) {
										var pos = {
											lat: position.coords.latitude,
											lng: position.coords.longitude
										}
									//map.setCenter(pos)
									keepMarker.setPosition(new google.maps.LatLng(pos.lat, pos.lng));
									map.panTo(new google.maps.LatLng(pos.lat, pos.lng));
	    							//map.setZoom(6)
									//geolocRoute(pos, defaultCenter)
									keepBool = 0
									console.log('updating')
								}, function () {
									keepBool = 0
									console.error('Error: The Geolocation service fail at finding user position')
								})}, 2500)
							})
						}
						else if (keepMarker != null) {
							map.panTo(new google.maps.LatLng(defaultCenter.lat, defaultCenter.lng));
							clearInterval(keepInterval)
							map.setZoom(defaultZoom)
							keepMarker.setMap(null)
							keepMarker = null
						}
					}
					else {
						console.error(browserHasGeolocation ? 'Error: The Geolocation service failed.' : 'Error: Your browser doesn\'t support geolocation.');
					}
				})
			}
		})

		window.setTimeout(setPos, 10000)

		trajets.map(function traceRoute(trajet, i) {
			var path = trajet.trachet
			var request = {
				origin: new google.maps.LatLng(path[0][0], path[0][1]),
				destination: new google.maps.LatLng(path[path.length - 1][0], path[path.length - 1][1]),
				travelMode: google.maps.TravelMode.BICYCLING
			};

			for (var j = 1; j + 1 < path.length; j++) {
				if (!request.waypoints) request.waypoints = [];
				request.waypoints.push({
					location: new google.maps.LatLng(path[j][0], path[j][1]),
					stopover: true
				})
			}

			var directionsDisplay = new google.maps.DirectionsRenderer({
				polylineOptions: {
					strokeColor: trajet.color,
					strokeWeight: trajet.strokeweight
				},
				suppressMarkers: true,
				preserveViewport: true,
				map: map
			})

			keepDisplay[keepDisplay.length] = directionsDisplay

			var directionsService = new google.maps.DirectionsService();

			directionsService.route(request, function(response, status) {
				var legs = response.routes[0].legs
				var totalDistance = 0
				var totalDuration = 0
				for (var i = 0; i < legs.length; i++) {
					totalDistance += Math.floor(legs[i].distance.value / 1000)
					totalDuration += legs[i].duration.value / 60
				}
				var trajetHtml = document.getElementById('trajet' + trajet.id + '_label')
				trajetHtml.innerHTML = trajetHtml.innerHTML + ' (' + Math.floor(totalDuration / 60) + 'h' + Math.floor(totalDuration % 60) + ', ' + totalDistance + 'km) :'
				if (status == google.maps.DirectionsStatus.OK)
					directionsDisplay.setDirections(response);
				else
					console.log("Directions Request from " + start.toUrlValue(6) + " to " + end.toUrlValue(6) + " failed: " + status);
			})
		})

		locations.map(function putMarker(location, i) {
			location.icon = window.baseDir + '/modules/reservation/views/templates/hook/suivi/images/' + location.icon
			var icon = {
				url: location.icon,
				scaledSize: new google.maps.Size(location.sizeIcon, location.sizeIcon),
				anchor: new google.maps.Point(location.sizeIcon / 2, location.sizeIcon / 2)
			}
			var newMarker = new google.maps.Marker({
				map: map,
				position: location,
				animation: google.maps.Animation.no,
				icon: icon
			})

			function setPanelContent(infoWindow, location) {
				document.getElementById('infoImage').childNodes[1].src = location.infoImage
				document.getElementById('namePlace').innerHTML = location.name
				document.getElementById('infoDescription').innerHTML = location.infoDescription

				var lat = newMarker.getPosition().lat()
				var lng = newMarker.getPosition().lng()
				var location = new google.maps.LatLng(lat, lng);
				map.panTo(location);
				//map.setZoom(14)

				infoWindow.style.width = '250px';
			}

			//ouvre la panel de gauche et zoom sur le marker ciblé
			function openSidebarMap(newMarker, location) {
				var infoWindow = document.getElementById('infoWindow')

				if (infoWindow.style.width == '250px') {
					if (location.name === document.getElementById('namePlace').innerHTML) {
						infoWindow.style.width = 0
					}
					else {
						setPanelContent(infoWindow, location)
					}
				}
				else {
					setPanelContent(infoWindow, location)
				}
			}

			newMarker.addListener('click', function (){ openSidebarMap(newMarker, location) });
			document.getElementById('toggleArrow').addEventListener('click', function (){ 
				document.getElementById('infoWindow').style.width = 0
			});
			i = i + 1
			var listener = document.getElementById("listePartenaires" + i);
			if (listener != null) {
				listener.addEventListener("click", function (){ openSidebarMap(newMarker, location) }, false);
			}
		})

		function changeTrajet(id) {
			infoWindow.style.width = 0
			map.panTo(defaultCenter)
			map.setZoom(defaultZoom)
			for (var i = 0; i < keepDisplay.length; i++) {
				if (i == id) {
					keepDisplay[i].setMap(map);
				} else {
					keepDisplay[i].setMap(null);
				}
			}
		}

		trajets.map(function (trajet, i) {
			document.getElementById('trajet' + trajet.id).addEventListener("click", function () { changeTrajet(trajet.id - 1) })
		})

		changeTrajet(0)
	}
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD_6na7dS7qNvOfm-2L451QpTqH76Vfahk&callback=initMap" type="text/javascript"></script>
{/literal}
