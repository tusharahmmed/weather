<?php

if ( isset( $_POST ) && !empty( $_POST['place'] ) ) {
    $city = $_POST['place'];
    $apiKey = '4e0a9b1693744d5c6db3982e795e7be4';

    // call api for latitude & longitude & current data
    $api_url_1 = 'https://api.openweathermap.org/data/2.5/weather?q=' . $city . '&appid=' . $apiKey;

    // cUrl init
    function curl_get_contents( $url ) {
        $ch = curl_init( $url );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
        $data = curl_exec( $ch );
        curl_close( $ch );
        return $data;
    }

    $url = $api_url_1;
    $data = json_decode( curl_get_contents( $url ), true );

}
// set code
$code = ( !empty( $data ) ) ? $data['cod'] : '';

// Get Place Coordiante for second call to get daily data
if ( '200' == $code ) {
    $coordinate = $data['coord'];
}

// call api for daily data

if ( isset( $coordinate ) && $coordinate ) {

    $latitude = $coordinate['lat'];
    $longitude = $coordinate['lon'];

    $api_url_2 = 'https://api.openweathermap.org/data/2.5/onecall?lat=' . $latitude . '&lon=' . $longitude . '&appid=' . $apiKey;

    $json = file_get_contents( $api_url_2 );
    // daily weather
    $raw = json_decode( $json, true );

    // set search place time zone
    $zoneName = $raw['timezone'];
    date_default_timezone_set( $zoneName );

}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1.0,maximum-scale=1">

		<title>Weather Forecast Web Application by Tushar</title>

		<!-- Loading favicon -->
		<link rel="icon" href="images/logo.png" type="image/png">
		<!-- Loading third party fonts -->
		<link href="http://fonts.googleapis.com/css?family=Roboto:300,400,700|" rel="stylesheet" type="text/css">
		<link href="fonts/font-awesome.min.css" rel="stylesheet" type="text/css">

		<!-- Loading main css file -->
		<link rel="stylesheet" href="style.css">

		<!--[if lt IE 9]>
		<script src="js/ie-support/html5.js"></script>
		<script src="js/ie-support/respond.js"></script>
		<![endif]-->

	</head>

	<body>
		<div class="site-content">
			<div class="site-header">
				<div class="container">
					<a href="./" class="branding">
						<img src="images/logo.png" alt="" class="logo">
						<div class="logo-type">
							<h1 class="site-title">Weather Forecast</h1>
							<small class="site-description">Web Application with PHP</small>
						</div>
					</a>
				</div>
			</div> <!-- .site-header -->

			<div class="hero" data-bg-image="https://img-prod-cms-rt-microsoft-com.akamaized.net/cms/api/am/imageFileData/RE4wEag?ver=1497">
				<div class="container">
					<form action="" class="find-location" method="post">

						<?php
// set placeholder
if ( '200' == $code ) {
    $placeholder = $data['name'];
} elseif ( '404' == $code ) {
    $placeholder = $data['message'];
} else {
    $placeholder = 'Find your location...';
}

?>
						<input name="place" type="text" placeholder="<?php echo ucfirst( $placeholder ); ?>">
						<input type="submit" value="Find">
					</form>

				</div>
			</div>

			<?php
// if have data then code = 200
if ( '200' == $code ) {

    ?>
			<!-- Weather Table-->
			<div class="forecast-table">
				<div class="container">
					<div class="forecast-container">

					<?php

    // current weather

    $unixLocalTime = $data['dt'];

    $day = date( 'l', $unixLocalTime );
    $month = date( 'j M', $unixLocalTime );
    $state = $data['name'];
    $currentTemp = round( $data['main']['temp'] - 273.15 );
    $icon = $data['weather']['0']['icon'];
    $iconUrl = 'http://openweathermap.org/img/w/' . $icon . '.png';
    $cloud = $data['clouds']['all'];
    $windSpeed = $data['wind']['speed'];
    $windSpeed = number_format( $windSpeed, 1, '.', '' );
    $windDegree = $data['wind']['deg'];

    // degree to direction

    $brng = $windDegree;
    $bearings = array( "NorthEast", "East", "SouthEast", "South", "SouthWest", "West", "NorthWest", "North" );

    $index = $brng - 22.5;
    if ( $index < 0 ) {
        $index += 360;
    }

    $index = (int) ( $index / 45 );
    $direction = $bearings[$index];

    ?>
						<div class="today forecast">
							<div class="forecast-header">
								<div class="day"><?php echo $day; ?></div>
								<div class="date"><?php echo $month; ?></div>
							</div> <!-- .forecast-header -->
							<div class="forecast-content">
								<div class="location"><?php echo $state; ?></div>
								<div class="degree">
									<div class="num"><?php echo $currentTemp; ?><sup>o</sup>C</div>
									<div class="forecast-icon">
										<img src="<?php echo $iconUrl; ?>" alt="" width=90>
									</div>
								</div>
								<span><img src="images/icon-umberella.png" alt=""><?php echo $cloud; ?>%</span>
								<span><img src="images/icon-wind.png" alt=""><?php echo $windSpeed; ?>km/h</span>
								<span><img src="images/icon-compass.png" alt=""><?php echo $direction; ?></span>
							</div>
						</div>
						<?php

    // forecast of this week

    if ( !empty( $raw ) ) {

        $countDay = count( $raw['daily'] );

        $i = 1;
        // Start While Loop
        while ( $i < $countDay - 1 ) {

            $dailyData = $raw['daily'][$i];

            $unixLocalTime = $dailyData['dt'];
            $date = date( 'l', $unixLocalTime );
            $maxTemp = round( $dailyData['temp']['max'] - 273.15 );
            $minTemp = round( $dailyData['temp']['min'] - 273.15 );
            $icon = $dailyData['weather']['0']['icon'];
            // default url
            //$iconUrl = 'images/icons/icon-3.svg';

            $iconUrl = 'http://openweathermap.org/img/w/' . $icon . '.png';

            ?>

								<div class="forecast">
							<div class="forecast-header">
								<div class="day"><?php echo $date; ?></div>
							</div> <!-- .forecast-header -->
							<div class="forecast-content">
								<div class="forecast-icon">
									<img src="<?php echo $iconUrl; ?>" alt="" width=48>
								</div>
								<div class="degree"><?php echo $maxTemp; ?><sup>o</sup>C</div>
								<small><?php echo $minTemp; ?><sup>o</sup></small>
							</div>
						</div>

						<?php
$i++;
        } // end while
        ?>

						<?php } // end if ?>



					</div>
				</div>
			</div>
			<?php }?>
			<!-- End Weather Table-->
	</div>

			<footer class="site-footer">
				<div class="container">
					<p class="colophon">Copyright 2021 <a href="//www.spfatech.com" target="_blank">SPFA TECH</a>. All rights reserved</p>
				</div>
			</footer> <!-- .site-footer -->
		</div>

		<script src="js/jquery-1.11.1.min.js"></script>
		<script src="js/plugins.js"></script>
		<script src="js/app.js"></script>

	</body>

</html>