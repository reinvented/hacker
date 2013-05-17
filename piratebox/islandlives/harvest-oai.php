#!/usr/bin/php
<?php
/**
  * harvest-fedora-pids.php
  *
  * A PHP script to harvest a list of Fedora pids via OAI.
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
  * @version 0.1, May 17, 2013
  * @author Peter Rukavina <peter@rukavina.net>
  * @copyright Copyright &copy; 2013, Reinvented Inc.
  * @license http://www.fsf.org/licensing/licenses/gpl.txt GNU Public License
  */

/**
  * The base OAI URL.
  */
$oai_base_url = "http://www.islandlives.ca/oai2";

/**
  * The OAI set to query against.
  */
$oai_set = "ilives_Collection";

/**
  * What to call the directory that will be copied to PirateBox; this will be created if it doesn't exist.
  */
$piratebox_directory = "IslandLives";

/**
  * If the PirateBox directory doesn't exist, then create it, and create subdirectories for
  * payload and thumbnails, as well as copying the CSS file template.
  */
if (!file_exists($piratebox_directory)) {
	system("mkdir $piratebox_directory");
	system("mkdir $piratebox_directory/payload");
	system("mkdir $piratebox_directory/thumbnails");
	system("cp template.css " . $piratebox_directory . "/" . $piratebox_directory . ".css");
}

/**
  * Start writing an index.html file with some introductory messaging.
  */
$fp = fopen($piratebox_directory . "/index.html","w");
fwrite($fp,"<head>\n");
fwrite($fp,"<title>Island Lives Books</title>\n");
fwrite($fp,"<link type=\"text/css\" rel=\"stylesheet\" media=\"all\" href=\"" . $piratebox_directory . ".css\">");
fwrite($fp,"<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n");
fwrite($fp,"</head>\n");
fwrite($fp,"<body>\n");
fwrite($fp,"<h1>IslandLives Books from Robertson Library</h1>\n");
fwrite($fp,"<p>Made possible through a generous private donation, IslandLives contains community and church histories and it builds on the University of Prince Edward Island Robertson Library's mission to preserve and share unique material relating to Prince Edward Island and demonstrates UPEI's ongoing commitment to making PEI's cultural and published heritage available to all.</p>\n");
fwrite($fp,"<p>The canonical source for these books is <a href=\"http://islandlives.ca/\">http://islandlives.ca/</a>; you may review the copyright terms for each work there.</p>");
fwrite($fp,"<b>Click on any book cover or title to download the complete PDF of the book.</p>\n");
fclose($fp);

/**
  * OAI returns data with a "resumptionToken" element until there are no
  * more records to send. So we keep passing the resumptionToken until
  * we don't get one returned. When that happens we set "completed" to
  * true and stop processing.
  *
  * TODO: IslandLives contains a lot of records that don't have a digitized
  * PDF attached, and these appear to be returned later in the list by OAI.
  * So what happens now is that the script will harvest the 199 existing
  * PDF files, but then keep cycling through all the without-a-PDF records
  * until it finishes. It would be nice to prevent this, but it's harmless.
  */
$completed = FALSE;
while (!$completed) {
	$url = $oai_base_url . "?verb=ListIdentifiers&metadataPrefix=oai_dc&set=" . $oai_set;
	if ($resumption_token <> "") {
		$url = $oai_base_url . "?verb=ListIdentifiers&resumptionToken=" . $resumption_token;
	}
	$xml = simplexml_load_file($url);
	if ($xml) {
		foreach($xml->ListIdentifiers->header as $key => $value) {
			$pid = (string)$value->identifier;
			print "Processing PID $pid\n";
			lookForThumbnail($pid);
			if (lookForPayload($pid)) {
				$title_url = $oai_base_url . "?verb=GetRecord&metadataPrefix=oai_dc&identifier=" . $pid;
				$title_xml = simplexml_load_file($title_url);
				$title = (string)$title_xml->GetRecord->record->metadata->children('oai_dc',true)->children('dc',true)->title;
				$author = (string)$title_xml->GetRecord->record->metadata->children('oai_dc',true)->children('dc',true)->creator;
				if (strlen($title) > 50) {
					$title = substr($title,0,50) . "...";
				}
				if (strlen($author) > 35) {
					$author = substr($author,0,35) . "...";
				}
				makeEntry($pid,$title,$author);
			}
		}
	}
	if ($xml->ListIdentifiers->resumptionToken) {
		$resumption_token = (string)$xml->ListIdentifiers->resumptionToken;
	}
	else {
		$resumption_token = FALSE;
	}
}	

/**
  * Finish up the index.html.
  */
$fp = fopen($piratebox_directory . "/index.html","a");
fwrite($fp,"</body>\n");
fclose($fp);

/**
  * Given a PID, title and author, write an entry into the index.html
  */
function makeEntry($pid,$title,$author) {
	global $piratebox_directory;

	$fp = fopen($piratebox_directory . "/index.html","a");
	fwrite($fp,"<div class='record'>");
	$pid_pattern = "oai:islandlives.ca:";	
	$pid = str_replace($pid_pattern,"",$pid);
	fwrite($fp,"<a href=\"payload/" . urlencode($pid) . ".pdf\">");
	fwrite($fp,"<img src=\"thumbnails/" . urlencode($pid) . ".jpg\">");
	fwrite($fp,"</a>");	
	fwrite($fp,"<a href=\"payload/" . urlencode($pid) . ".pdf\">");
	fwrite($fp,"<h2>" . $title . "</h2>");
	if ($author <> "") {
		fwrite($fp,"<h3>" . $author . "</h3>");
	}
	fwrite($fp,"</a>");	
	fwrite($fp,"</div>");
	fclose($fp);
}	

/**
  * Look for a thumbnail JPEG for the given PID. Ideally this would
  * be returned by some sort of API, and maybe it can be. For the time
  * being we just look to see if it's there in Islandora and if it's not
  * then we use the default thumbnail, copied into place.
  */
function lookForThumbnail($pid) {

	global $piratebox_directory;

	$thumbnailpattern = "http://www.islandlives.ca/fedora/repository/[PID]/TN";
	$extension = ".jpg";
	$pid_pattern = "oai:islandlives.ca:";	
	$pid = str_replace($pid_pattern,"",$pid);
	$searchpid = str_replace("_",":",$pid);

	$thumbnail = str_replace("[PID]",urlencode($searchpid),$thumbnailpattern);
	system("wget -q $thumbnail -O $piratebox_directory/thumbnails/" . $pid . $extension);

	if (filesize("$piratebox_directory/thumbnails/" . $pid . $extension) > 0) {
		return TRUE;
	}
	else {
		unlink("$piratebox_directory/thumbnails/" . $pid . $extension);
		system("cp default-thumbnail.jpg $piratebox_directory/thumbnails/" . $pid . $extension);
		return FALSE;
	}
}

/**
  * Look for the PDF file associated with a PID. Again, this would be nice
  * to be able to harvest via an API, and maybe it can be. But for now
  * we look to see if it's there in Islandora and if retrieving it returns
  * a zero-length file the we assume it wasn't there and return FALSE.
  */
function lookForPayload($pid) {

	global $piratebox_directory;

	$payloadpattern = "http://www.islandlives.ca/fedora/repository/[PID]/PDF/[PID]/Full%20Text.pdf";
	$extension = ".pdf";
	$pid_pattern = "oai:islandlives.ca:";	

	$pid = str_replace($pid_pattern,"",$pid);
	$searchpid = str_replace("_",":",$pid);

	$payload = str_replace("[PID]",urlencode($searchpid),$payloadpattern);
	if (!file_exists("$piratebox_directory/payload/" . $pid . $extension)) {
		system("wget -q $payload -O $piratebox_directory/payload/" . $pid . $extension);
	}

	if (filesize("$piratebox_directory/payload/" . $pid . $extension) > 0) {
		return TRUE;
	}
	else {
		unlink("$piratebox_directory/payload/" . $pid . $extension);
		return FALSE;
	}	
}