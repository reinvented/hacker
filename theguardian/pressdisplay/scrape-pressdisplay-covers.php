<?php
/**
  * scrape-pressdisplay-covers.php
  *
  * A PHP script to retrieve newspapers cover thumbnails from the
  * Pressdisplay.com website.
  *
  * Requires cURL.
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
  * @version 0.1, January 17, 2013
  * @link https://github.com/hacker/theguardian/pressdisplay
  * @author Peter Rukavina <peter@rukavina.net>
  * @copyright Copyright &copy; 2012, Reinvented Inc.
  * @license http://www.fsf.org/licensing/licenses/gpl.txt GNU Public License
  */
  
// Initialize cURL object and set up cURL options.  
$ch = curl_init();
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_USERAGENT, 'PHP script');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

// This is the date we're going to start scraping from.
$startdate = strtotime("2012-01-01");

// We're going to look for 365 days worth of covers maximum.
for ($i = 0 ; $i <= 365 ; $i++) {

	$urldate = strftime("%Y%m%d",$startdate);
	
	// Print the current date to the command line, for debugging and information.
	print $urldate . " -- ";

	// Make a Pressdisplay.com URL.
	curl_setopt($ch, CURLOPT_URL,"http://cache2-thumb1.pressdisplay.com/pressdisplay/docserver/getimage.aspx?file=1909" . $urldate . "00000000001001&page=1&scale=65&ver=3");
	curl_setopt($ch, CURLOPT_HTTPGET, 1);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	
	ob_end_clean();  //resume printout output to screen
	
	$ret = new stdClass;
	$ret->response = curl_exec($ch); // execute and get response
	$ret->error    = curl_error($ch);
	$ret->info     = curl_getinfo($ch);

	// Print the HTTP response code for debugging (200 = good, 500 = cover not found).
	print $ret->info['http_code'] . "\n";
	
	// If the HTTP response code was 200, then dump the response (the JPG of the cover) into a file.
	if ($ret->info['http_code'] == 200) {
		$fp = fopen("2012/$urldate.jpg","w");
		fwrite($fp,$ret->response);
		fclose($fp);
	}
	
	// Add 86400 seconds (1 day) to the date, thus advancing to the next date.
	$startdate += 86400;
	
}

curl_close ($ch);
unset($ch);

