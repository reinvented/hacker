Scraping Pressdisplay.com Thumbnails
====================================

Following on from [a project I undertook to make a montage of covers of The Charlottetown Guardian from 1912](http://ruk.ca/content/year-guardian) to mark the newspaper's 125th anniversary, I decided to find a way of preparing a similar montage for the covers of the 2012 volume.

As these newspapers haven't been scanned into the [IslandNewspapers.ca](IslandNewspapers.ca) project yet, I couldn't use the same code I'd used for the 1912 project.

Fortunately, [the system that The Guardian uses to sell its digital edition online](http://theguardian.newspaperdirect.com/epaper/viewer.aspx) provides a free cache of newspaper cover thumbnails, and these are easily scraped from the cache with the code provided here.

Cached Thumbnails
-----------------

The thumbnails for covers of The Guardian live at the following URL:

	http://cache2-thumb1.pressdisplay.com/pressdisplay/docserver/getimage.aspx?file=1909" . $urldate . "00000000001001&page=1&scale=65&ver=3

where $urldate is the date of the issue in question, YYYYMMDD.

So, for example, [the cover of April 5, 2012 is here](http://cache2-thumb1.pressdisplay.com/pressdisplay/docserver/getimage.aspx?file=19092012040500000000001001&page=1&scale=65&ver=3):

	http://cache2-thumb1.pressdisplay.com/pressdisplay/docserver/getimage.aspx?file=19092012040500000000001001&page=1&scale=65&ver=3
	
Scraping Thumbnails
-------------------

The script scrape-pressdisplay-covers.php simply iterates over a year's worth of dates and looks for a thumbnail cover; in situations where there's no edition for a given date -- Sundays, for example -- it simply detects an HTTP response of 500 and continues merrily along.

Making an Montage
-----------------

Once a collection of cover images is scraped, they can be turned into a montage using [ImageMagick](http://www.imagemagick.org/), and, specifically, its **montage** command:

For example, with a collection of TIFF covers in a directory **2012** a montage can be made with:

	montage 2012/*.jpg -tile 19x16 -geometry +20+20 ./2012montage.tiff
	
In this case I've choosen to tile the montage in a 19 across by 16 down matrix, leaving 20 pixels of space between each tile both vertically and horizontally.