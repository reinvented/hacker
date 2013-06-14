#!/usr/bin/php
<?php
/**
  * extract_from_industry_canada.php
  *
  * Extracts mobile network tower data from Industry Canada's "Spectrum Direct" 
  * search (http://sd.ic.gc.ca/pls/engdoc_anon/web_search.frequency_range_input)
  * via cURL and creates a GeoJSON file of towers.
	*
  * This program is free software; you can redistribute it and/or modify
  * it under the terms of the GNU General Public License as published by
  * the Free Software Foundation; either version 2 of the License, or (at
  * your option) any later version.
  *
  * This program is distributed in the hope that it will be useful, but
  * WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
  * General Public License for more details.
  * 
  * You should have received a copy of the GNU General Public License
  * along with this program; if not, write to the Free Software
  * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
  * USA
  *
  * @version 0.1, June 14, 2013
  * @author Peter Rukavina <peter@rukavina.net>
  * @copyright Copyright &copy; 2013, Reinvented Inc.
  * @license http://www.fsf.org/licensing/licenses/gpl.txt GNU Public License
  */
  
/* Grab an XML representation of all transmitters in Canada with a
   transmitting frequency between 824 and 894 MHz. */
$ch = curl_init();

/* This is a list of parameters that we POST to the Industry Canada Spectrum
   Direct geographic search. You can change any of these to change the search.
   For example, change p_centre_latitude and p_centre_longitude (which are
   in DDMMSS form) to change the centre of the search area. */
$postfields = array(
								"p_centre_latitude" => "462211",
								"p_centre_longitude" => "632512",
								"radius" => "125",
								"frequency_1" => "824",
								"frequency_2" => "2000",
								"txrx" => "TXRX",
								"station_type" => "land, mobile",
								"extra_ascii" => "None",
								"extra_xml" => "None",
								"output_format" => "5",
								"selected_column_group" => "NONE",
								"selected_columns" => "",
								"selected_columns" => "CALL_SIGN",
								"selected_columns" => "LOCATION",
								"selected_columns" => "LATITUDE",
								"selected_columns" => "LONGITUDE",
								"selected_columns" => "SITE_ELEV",
								"selected_columns" => "STRUCT_HGT",
								"selected_columns" => "COMPANY_NAME",
								"col_in_fmt" => "ARRAY_list");

/* Take these parameters and turn them into a string suitable for passing to cURL */
$poststring = http_build_query($postfields);

curl_setopt($ch, CURLOPT_URL,"http://sd.ic.gc.ca/pls/engdoc_anon/web_search.geographical_results");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS,$poststring);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$frequency_xml = curl_exec ($ch);
curl_close ($ch);

/* Parse the resulting XML file and pull out those transmitters where the 
   the "licensee" is one of Bell, Telus or Rogers, the companies with 
   mobile phone towers on Prince Edward Island. */
$xml = simplexml_load_string($frequency_xml);
$list = array();
foreach($xml->account as $key => $account) {
	switch ((string)$account->licensee_name) {
		case "Bell Mobility Inc":
		case "BELL ALIANT COMMUNICATIONS RÉGIONAL":
		case "BELL ALIANT REGIONAL COMM. INC.":
		case "BELL MOBILITY INC.":
		case "Bell AWS LTE Atlantic":
		case "Bell Canada":
		case "Bell Mobility Inc.":
		case "Bell Mobility Inc. Atlantic CDMA":
		case "Bell Mobility Inc. Atlantic HSPA":
		case "BELL ALIANT REGIONAL COMM. INC.":
				$list = array_merge($list,buildFeature($account,"Bell","#1E7BC0"));
				break;
		case "TELUS Communications Company":
				$list = array_merge($list,buildFeature($account,"Telus","#66CB00"));
				break;
		case "Rogers Communications Partnership":
				$list = array_merge($list,buildFeature($account,"Rogers","#DD2735"));
				break;
	}
}

/* Convert the resulting array into JSON */
$json = json_encode(array("type" => "FeatureCollection", "features" => $list));

/* Dump the JSON file of the results. */
$fp = fopen("../www/data/towers.json","w");
fwrite($fp,$json);
fclose($fp);

/* Convert latitude and longitude in format 
   DDMMSS or DDDMMSS to decimal degrees */
function DMStoDEC($ddmmss) {
	if (strlen($ddmmss) == 7) {
		$deg = substr($ddmmss,0,3);
		$min = substr($ddmmss,3,2);
		$sec = substr($ddmmss,5,2);
	}
	else {
		$deg = substr($ddmmss,0,2);
		$min = substr($ddmmss,2,2);
		$sec = substr($ddmmss,4,2);
	}
  return $deg+((($min*60)+($sec))/3600);
}    

/* Build a GeoJSON feature from the information we pulled from Industry Canada as
   XML. We add a colour and a "short name" so that we can style the feature
   on a Leaflet map. */
function buildFeature($account,$company,$colour) {
	$list = array();  
	foreach($account->licence as $key1 => $license) {
		$s['type'] = 'Feature';
		$s['properties']['shortcompany'] = $company;
		$s['properties']['company'] = (string)$account->licensee_name;
		$s['properties']['call_sign'] = (string)$license->call_sign;
		$s['properties']['location'] = (string)$license->location;
		$s['properties']['longitude'] = (string)$license->longitude;
		$s['properties']['latitude'] = (string)$license->latitude;
		$s['properties']['site_elevation'] = (string)$license->site_elevation;
		$s['properties']['structure_height'] = (string)$license->structure_height;
		$s['properties']['colour'] = $colour;
		$s['geometry']['type'] = "Point";
		$s['geometry']['coordinates'] = array(0-(DMStoDEC((string)$license->longitude)),(DMStoDEC((string)$license->latitude)));
		$list[] = $s;
	}
	return $list;
}