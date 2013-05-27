#!/usr/bin/php
<?php
/**
  * extract_cbc.php
  *
  * Extracts FM transmitter data from Industry Canada's "Spectrum Direct" 
  * search (http://sd.ic.gc.ca/pls/engdoc_anon/web_search.frequency_range_input)
  * via cURL and creates a GeoJSON of CBC Radio transmitters by filtering
  * for those transmitters where the licensee name is "CBC/ RADIO-CANADA".
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
  * @version 0.1, May 26, 2013
  * @author Peter Rukavina <peter@rukavina.net>
  * @copyright Copyright &copy; 2013, Reinvented Inc.
  * @license http://www.fsf.org/licensing/licenses/gpl.txt GNU Public License
  */
  
/* Grab an XML representation of all transmitters in Canada with a
   transmitting frequency between 87.5 and 108 MHz. */
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,"http://sd.ic.gc.ca/pls/engdoc_anon/web_search.frequency_range_results");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS,"frequency_1=87.5&frequency_2=108&txrx=TX&station_type=land%2C+mobile&region_list=CANADA&extra_ascii=None&output_format=5&extra_xml=None&selected_columns=&selected_columns=FREQ_STAT&selected_columns=TX_FREQ&selected_columns=RX_FREQ&selected_column_group=NONE&selected_columns=LOCATION&selected_columns=COMPANY_NAME&col_in_fmt=ARRAY_list");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$freqency_xml = curl_exec ($ch);
curl_close ($ch);

/* Parse the resulting XML file and pull out those transmitters where
   the "licensee" is "CBC/ RADIO-CANADA" */  
$xml = simplexml_load_string($freqency_xml);
$list = array();
foreach($xml->account as $key => $station) {
	if ((string)$station->licensee_name == "CBC/ RADIO-CANADA") {
		$s['type'] = 'Feature';
		$s['properties']['call_sign'] = (string)$station->licence->call_sign;
		$s['properties']['location'] = (string)$station->licence->location;
		$s['properties']['frequency'] = (string)$station->licence->frequency->tx_frequency;
		$s['properties']['longitude'] = (string)$station->licence->longitude;
		$s['properties']['latitude'] = (string)$station->licence->latitude;
		$s['geometry']['type'] = "Point";
		$s['geometry']['coordinates'] = array(0-(DMStoDEC((string)$station->licence->longitude)),(DMStoDEC((string)$station->licence->latitude)));
		$list[] = $s;
	}
}

/* Convert the resulting array into JSON */
$json = json_encode(array("type" => "FeatureCollection", "features" => $list));

/* Dump the JSON file of the results. */
$fp = fopen("../www/data/stations.json","w");
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