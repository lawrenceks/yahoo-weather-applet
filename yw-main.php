<?php
/*!
@header			api/yw/yw-main.php
@abstract		Yahoo! Weather applet
@version		1.0.8
@updated		2014-03-14
@author			Chris Stringer (cstringer\@lawrenceks.org)
@discussion

Uses the Yahoo! Weather REST API to display weather forecast by ZIP code <br>
 <br>

Accepts GET parameter: <br>
 <br>
 <b> zip </b> = ZIP code to display (defaults to 66044) <br>

*/

/*=== Definitions ===*/

/* Base URL to Yahoo! Weather REST endpoint */
define ("YW_URL_BASE",		'http://query.yahooapis.com/v1/public/yql');
/* Paramaters (query string) to pass to REST endpoint */
define ("YW_URL_QUERY", 	'?q=select%20*%20from%20weather.forecast%20where%20location%3D%22[[ZIP]]%22&format=json');
/* Default ZIP code */
define ("YW_DEF_ZIP", 		'66044');
/* URL base for condition images */
define ("YW_IMG_BASE",		'http://l.yimg.com/a/i/us/we/52/');
/* Filename extension for condition images */
define ("YW_IMG_EXT",			'.gif');

/*=== Globals ===*/

/*! 5-digit ZIP code (string) */
$Zip = YW_DEF_ZIP;				
/*! code (number indicating weather type, used for graphic) */
$YwCode = "";	
/*! description of current conditions */
$YwDesc = "";							
/*! current temperature (deg. F) */
$YwTemp = "";							
/*! date/time data published/updated */
$YwPubDate = "";					
/*! URL to condition graphic */
$YwImgUrl = "";						

/*=== Main ===*/

// get ZIP code
if (isset ($_GET['zip']) && preg_match ('/^[\d]{5}$/', $_GET['zip']))
	{
	// if we got 5 digits, use 'em
	$Zip = $_GET['zip'];
	}

// parse query string with ZIP code
$url_query = str_replace ('[[ZIP]]', $Zip, YW_URL_QUERY);

// build URL to REST endpoint
$yw_url = YW_URL_BASE . $url_query;

// get JSON feed as string
$yw_json = file_get_contents ($yw_url);
if (strlen ($yw_json) > 0)
	{
	// convert JSON to PHP obj
	$yw_data = json_decode ($yw_json);
	if ($yw_data != NULL && isset ($yw_data->{'query'}->{'results'}->{'channel'}->{'item'}->{'condition'}))
		{
		// copy data fields to global vars
		$YwCode = $yw_data->{'query'}->{'results'}->{'channel'}->{'item'}->{'condition'}->{'code'};
		$YwDesc = $yw_data->{'query'}->{'results'}->{'channel'}->{'item'}->{'condition'}->{'text'};
		$YwTemp = $yw_data->{'query'}->{'results'}->{'channel'}->{'item'}->{'condition'}->{'temp'};
		$YwPubDate = $yw_data->{'query'}->{'results'}->{'channel'}->{'item'}->{'pubDate'};

		// build URL to weather graphic
		$YwImgUrl = YW_IMG_BASE . $YwCode . YW_IMG_EXT;
		}
	else
		{
		error_log ("Error decoding JSON: " . json_last_error());
		}
	}

// render HTML...
?>
<div id="yw-output">
 <img src="<?php echo $YwImgUrl; ?>" alt="Powered by Yahoo! Weather" />
 <b>Current Conditions</b><br />
 <?php echo $YwDesc; ?>, <?php echo $YwTemp; ?>&deg;F<br />
 <span class="w-date"><?php echo $YwPubDate; ?></span>
</div>
