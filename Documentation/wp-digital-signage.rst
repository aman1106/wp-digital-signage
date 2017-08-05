.. Wp-Digital-Signage documentation master file, created by
   sphinx-quickstart on Fri Aug  4 15:25:29 2017.
   You can adapt this file completely to your liking, but it should at least
   contain the root `toctree` directive.

**Wp-Digital-Signage**
======================

**Description**
---------------
* Site runs on server.
* App is installed on the device on which slider will be displayed.
* The device connect with the server and fetch the necessary information needed for running the slider on the device.
* The details of displays, group displays, events,floormaps and alerts are provided 
* Display table contains the displays of the device on which slider will be running, and the details are display’s id, name, location, mac, status etc.
* Group displays contains the id of one or more displays. Grouping of the displays is done to avoid redundant settings.
* Event manager is another table which contains the names of displays and group displays, it manages the time of the displays and group displays i.e which display will run in what time period and between what days..etc
* Floormaps contains the name of the floormap and the image of the floormaps and the status of the floormap whether they are active or disabled.
* Alerts is the table which contains the ids of displays and group displays and the email address and it uses the crontab to check whether display is working properly or not, and if not then a mail is send to the email address which is linked with the particular display.
* One can add a new display, group, event, floormaps or alert, delete or edit the existing one.

**Working**
-----------
* Devices are registered with their mac address and the corresponding names on the server.
* An app is installed on the device which will automatically run when the device is on.
* The display fetches the corresponding information from the server and according to the slider name it fetches the images and displays the images on the screen between the given time.
* The sliders are decided for every display hence the slider according to the particular display will be displayed on the device screen.
* The device will run the slider and will generate the url which will contain the uid of the display and its uptime as query variables. This url is used to get the uptime of the display i.e the time since display is running. 
* The query variables are used to notify if display is working or not.
* The uid and uptime is fetched and accordingly the upstatus of the display is set to 1 i.e the screen/display is working and last seen is set as per the current timestamp.
* It checks the last seen of the display and calculates the difference between the current time of the server and the last seen of the display and if the difference is above the certain time then a relevant message is sent.
* If device is not working i.e difference is above certain limit then it fetches the email id corresponding to that display to send the mail to the concerned person that particular display is not working.
* The upstatus of the display is again set to 0 as the display stopped working.

**Modules**
-----------
The modules of digital signage are as follows:

Display : The display module contains a list table in which it stored the details of the displays on which slider will run. The display table contains the id of the display which is auto incremented, the name of the display, location the display, mac address of the display to uniquely identify the display, status of the display i.e whether the display is active or disabled, uptime of the display which tells that since when the display is running in seconds, upstatus which tells whether the display is currently running or not, if running then upstatus will be 1, and 0 otherwise. One can also delete the particular display, edit it or create a new one by clicking on the corresponding button and giving the valid details which are needed.

Group Display : The group display module contains the group of the display which are grouped together to run same slider on every display on the group. The table consists of id which is again auto incremented, the group name, location of the displays, the name of the displays which are added in the group, and the status of the group i.e active or disabled. One can also delete the existing group, edit the group details or add a new group.

Event Manager : The Event Manager manages the displays and group displays according to the running time of the event. The Event Manager consists of the event table which has the fields id which contains the id of the Event which is auto incremented, name of the event, slider which tells which slider to run on particular displays and group displays, time from field stores the date and time from which display will be active, time to field stores the date and time till which display will be active, i.e display will be activated between ‘time from’ to ‘time to’, and status of the event which tells whether active or disabled.

Floormaps : Floormap module stores the map of the floor on which the display is to be displayed. The Floormap table contains the id field which contains the id of the floormap which is auto incremented, name field stored the name of the floormap, floormap field actually stores the floormaps and status field contains the current status of the floormap whether they are active or disabled.



.. toctree::
   :maxdepth: 2
   :caption: Contents:



Indices and tables
==================

* :ref:`genindex`
* :ref:`modindex`
* :ref:`search`
