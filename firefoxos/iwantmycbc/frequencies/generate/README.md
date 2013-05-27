Generate GeoJSON of CBC Radio FM Transmitters
====================================

This script generations a [GeoJSON](http://www.geojson.org/geojson-spec.html) representation of CBC Radio FM transmitters in Canada by scraping data from the [Industry Canada Spectrum Direct](http://sd.ic.gc.ca/pls/engdoc_anon/web_search.frequency_range_input) database.

Each transmitter is represented as a "feature":

	{
	  "type": "Feature",
	  "properties": {
	    "call_sign": "CBUF-FM-5",
	    "location": "Kitimat",
	    "frequency": "105.1",
	    "longitude": "1283808",
	    "latitude": "540312"
	  },
	  "geometry": {
	    "type": "Point",
	    "coordinates": [
	      -128.63555555556,
	      54.053333333333
	    ]
	  }
	}
	
Requirements
------------

* PHP with cURL and SimpleXML support

How to Run
----------

	php extract_cbc.php
 
This will dump the GeoJSON file into [../www/data/stations.json](../www/data/stations.json) where it can be viewed with the [Leaflet-based map demonstration](../www/index.html).
