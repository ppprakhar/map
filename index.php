<?php 
function youtube_id_from_url($url) { 
    $pattern = 
        '%^# Match any youtube URL
        (?:https?://)?  # Optional scheme. Either http or https
        (?:www\.)?      # Optional www subdomain
        (?:             # Group host alternatives
          youtu\.be/    # Either youtu.be,
        | youtube\.com  # or youtube.com
          (?:           # Group path alternatives
            /embed/     # Either /embed/
          | /v/         # or /v/
          | /watch\?v=  # or /watch\?v=
          )             # End path alternatives.
        )               # End host alternatives.
        ([\w-]{10,12})  # Allow 10-12 for 11 char youtube id.
        $%x'
        ;
    $result = preg_match($pattern, $url, $matches); 
    if ($result) { 
        return $matches[1]; 
    } 
    return false;
} 

?> 
<!DOCTYPE html>
<html>
	<head>
		<meta charset='utf-8' /> 
		<title>Map Box</title> 
		<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">

		<link rel='stylesheet' id='font-icon-css'  href='https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' type='text/css' media='all' />
		<link href='https://api.mapbox.com/mapbox-gl-js/v0.38.0/mapbox-gl.css' rel='stylesheet' /> 
		<link href='css/style.css' rel='stylesheet' type="text/css" />

		<script src='https://api.mapbox.com/mapbox-gl-js/v0.38.0/mapbox-gl.js'></script> 
		<script src="http://code.jquery.com/jquery-3.2.1.slim.min.js" ></script> 

		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/3.0.0/css/swiper.min.css">
		<script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/3.0.0/js/swiper.min.js"></script>

	</head> 

	<body> 

		<div class="layerSlider">

			<!-- Slider main container -->
			<div class="swiper-container">
			    <!-- Additional required wrapper -->
			    <div class="swiper-wrapper">
			        <!-- Slides -->
			        <?php 
						$geoData = stripslashes( file_get_contents('data.json') ); 
						$geoArr = json_decode($geoData); 
						// print_r($geoArr); 
						for ($i=0; $i < 2; $i++) { 
						foreach ($geoArr->features as $value) { ?>
							<div class="swiper-slide" data-cords="[<?php echo implode(',', $value->geometry->coordinates); ?>]" data-video="<?php echo youtube_id_from_url($value->properties->youtube); ?>" data-title="<?php echo $value->properties->title; ?>" data-desc="<?php echo $value->properties->description; ?>">
								<div>
									<img src="img/<?php echo $value->properties->img; ?>" />
									<p class="title"><?php echo $value->properties->title; ?> </p>
								</div> 
							</div> 
							
					<?php } } ?>
			    </div>

			    <!-- If we need pagination -->
			    <!-- <div class="swiper-pagination"></div> -->
			    
			    <!-- If we need navigation buttons -->
			    <div class="swiper-button-prev"></div>
			    <div class="swiper-button-next"></div>
			    
			    <!-- If we need scrollbar -->
			    <!-- <div class="swiper-scrollbar"></div> -->
			</div>
		</div> 


		<div class="layerVideo">
			<div class="text_content">
				<iframe width="100%" height="200" src="" frameborder="0" allowfullscreen></iframe>

				<div class="" >
					<h2 id="title"></h2>
					<p id="desc"></p>
					<div id="social_icons"></div>

				</div>
			</div>

			<div class="layerBtn">
				<button class="home-icon"><i class="fa fa-home prevent-rtl"></i></button>

				<button class="act-icon">
					<i class="fa fa-forward" ></i>
					<i class="fa fa-pause" style="display: none;" ></i>
				</button>

				<button class="left-icon"><i class="fa fa-chevron-left"></i></button>
				<button class="right-icon"><i class="fa fa-chevron-right"></i></button>
			</div>
		</div>

		<div id='map' ></div> 

		<script> 
			mapboxgl.accessToken = 'pk.eyJ1IjoicHJha2hhcnNudiIsImEiOiJjajUwd3V1bHkxb3U0MndxbnNnYXB5cWtwIn0.gUuFL7ZzzogfCFdiK8qEoQ';
			
			var map = new mapboxgl.Map({ 
			    container: 'map',
			    style: 'mapbox://styles/mapbox/streets-v9',
			    center: [37.6230752, 55.7523376],
			    zoom: 17,
			    pitch: 65, // for Y-axix 
			    bearing: -65, // for x-axis
			}); 

			// Add zoom and rotation controls to the map.
			map.addControl(new mapboxgl.NavigationControl()); 

			map.on('load', function () { 
			    // Add a layer showing the places.
			    map.addLayer({
			        "id": "3d-buildings",
			        "type": "fill-extrusion",
			        'source-layer': 'building',
			        'filter': ['==', 'extrude', 'true'],
			        'source': 'composite', 
			        // "layout": {
			        //     "icon-image": "{icon}-15",
			        //     "icon-allow-overlap": true
			        // },
			        'minzoom': 15,
			        'paint': {
			            // See the Mapbox Style Spec for details on property functions
			            // https://www.mapbox.com/mapbox-gl-style-spec/#types-function
			            // 'fill-extrusion-color': {
			            //     // Get the fill-extrusion-color from the source 'color' property.
			            //     'property': 'color',
			            //     'type': 'identity'
			            // },
			            'fill-extrusion-color': '#ccc', 
			            'fill-extrusion-height': {
			                // Get fill-extrusion-height from the source 'height' property.
			                'property': 'height',
			                'type': 'identity'
			            },
			            'fill-extrusion-base': {
			                // Get fill-extrusion-base from the source 'base_height' property.
			                'property': 'min_height',
			                'type': 'identity'
			            },
			            // Make extrusions slightly opaque for see through indoor walls.
			            'fill-extrusion-opacity': 0.8
			        }
			    }); 

			    // When a click event occurs on a feature in the places layer, open a popup at the
			    // location of the feature, with description HTML from its properties.
			    map.on('click', '3d-buildings', function (e) {
			        new mapboxgl.Popup()
			            .setLngLat(e.features[0].geometry.coordinates)
			            .setHTML(e.features[0].properties.description)
			            .addTo(map);
			    });

			    // Change the cursor to a pointer when the mouse is over the places layer.
			    map.on('mouseenter', '3d-buildings', function () {
			        map.getCanvas().style.cursor = 'pointer';
			    });

			    // Change it back to a pointer when it leaves.
			    map.on('mouseleave', '3d-buildings', function () {
			        map.getCanvas().style.cursor = '';
			    });
			}); 



			var geoData = <?php echo $geoData ?>; 

			geoData.features.forEach(function(marker){ 
				console.log(marker.properties.iconSize.join('/')); 
				 // create a DOM element for the marker
			    var el = document.createElement('div'); 
			    el.className = 'marker'; 
			    el.style.backgroundImage = 'url(https://placekitten.com/g/' + marker.properties.iconSize.join('/') + '/)';
			    el.style.width = marker.properties.iconSize[0] + 'px'; 
			    el.style.height = marker.properties.iconSize[1] + 'px'; 

			    el.addEventListener('click', function() { 
			        window.alert(marker.properties.description); 
			    }); 

			    // add marker to map
			    new mapboxgl.Marker(el)
			        .setLngLat(marker.geometry.coordinates)
			        .addTo(map); 

			    var popup = new mapboxgl.Popup({closeOnClick: false}) 
				    .setLngLat(marker.geometry.coordinates) 
				    .setHTML(marker.properties.title) 
				    .addTo(map); 
			}); 

			jQuery('.swiper-slide').click(function(e) { 
				var cords = jQuery(this).data('cords'); 

				map.flyTo({ 
					center: cords,
					pitch: 65, // for Y-axix 
				    bearing: -65, // for x-axis
				    zoom: 17
			    });
			}); 


			jQuery('.home-icon').click(function(){ 
		    	map.flyTo({ 
		    		zoom: 2, 
		    		pitch: 0, // for Y-axix 
			    	bearing: 0, // for x-axis 
			    }); 
		    }); 


			// 
			var swiper = new Swiper('.swiper-container', { 
				autoplay: 2000, 
		        pagination: '.swiper-pagination', 
		        slidesPerView: 4, 
		        centeredSlides: true, 
		        paginationClickable: true, 
		        spaceBetween: 30, 
		        nextButton: '.swiper-button-next', 
		        prevButton: '.swiper-button-prev', 
		        setWrapperSize: true, 
		        onInit: function(arg){ 
		        	alert('hihi'); 
		        	console.log(arg); 
		        }, 
		        onSlideChangeEnd: function (swiper) { 
					var i = swiper.activeIndex; 
				    var srcV = jQuery('.swiper-slide:eq('+i+')').data('video'); 
				    jQuery('.text_content iframe').attr('src', 'https://www.youtube-nocookie.com/embed/'+srcV); 
				    jQuery('#title').html(jQuery('.swiper-slide:eq('+i+')').data('title')); 
		    		jQuery('#desc').html(jQuery('.swiper-slide:eq('+i+')').data('desc')); 
				} 

		    }); 

		    var i = swiper.activeIndex; 
		    var srcV = jQuery('.swiper-slide:eq('+i+')').data('video'); 
		    jQuery('.text_content iframe').attr('src', 'https://www.youtube-nocookie.com/embed/'+srcV); 
		    jQuery('#title').html(jQuery('.swiper-slide:eq('+i+')').data('title')); 
		    jQuery('#desc').html(jQuery('.swiper-slide:eq('+i+')').data('desc')); 


			autoPlay = false; 
			swiper.stopAutoplay(); 
			jQuery('.act-icon').click(function(){ 
				jQuery('.fa-forward').toggle(); 
				jQuery('.fa-pause').toggle(); 

				if(autoPlay==false) { 
		    		swiper.startAutoplay(); autoPlay=true; 
				} else { 
		    		swiper.stopAutoplay(); autoPlay=false; 
		    	} 

		    }); 

		    jQuery('.right-icon').click(function(){ 
		    	swiper.slideNext(); 
		    }); 

		    jQuery('.left-icon').click(function(){ 
		    	swiper.slidePrev(); 
		    }); 
		</script>

	</body>
</html>