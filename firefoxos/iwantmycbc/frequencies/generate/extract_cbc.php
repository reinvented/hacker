#!/usr/bin/php
<?php
/**
  * extract_cbc.php
  *
  * Extracts CBC Radio FM frequencies from a previously-downloaded XML file
  * from the Industry Canada "Spectrum Direct" search.  To generate this file
  * go to:
  *
  * http://sd.ic.gc.ca/pls/engdoc_anon/web_search.frequency_range_input
  *
  * and enter the following search parameters:
  *
  * 1. Frequency Range (MHz): 87.5 to 108
  * 2. Frequency Type to Search: Tx
  * 3. Station Type: All of the above station types
  * 4. Region(s): Canada Wide
  * 5. Output Format: XML (None)
  *
  * Click the "Find" button and save the resulting file to the same
  * directory as this script with filename of "frequency_range.xml".
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
  
$xml = simplexml_load_file('frequency_range.xml');
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

$json = json_encode(array("type" => "FeatureCollection", "features" => $list));

/* Dump a JSON file of the results. */
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