Get Cell ID
===========

This is an open web app, targetted at Firefox OS and tested initially on the Geeksphone Peak, that grabs the current cellular network ID (the MCC, MNC, LAC and Cell ID) and displays them.

The app will also, optionally, upload this information to the [OpenCellID.org](http://opencellid.org) project via its API.

TODO
----

* Handle failure, especially of the OpenCellID.org update, more gracefully (or, indeed, at all), and perhaps check the OpenCellID.org key for validity when it's saved on the "Settings" page.
* Make the Cell ID "clickable", generating a query to the OpenCellID.org API that accepts a Cell ID and returns its guess of geolocation.

Uses
----

* [Zepto.js](http://zeptojs.com/)
* [Moment.js](http://momentjs.com/)


Screenshot
----------

![image](screenshots/getcellid-screenshot.png)