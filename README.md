# [SprintCity](http://www.deltametropool.nl/nl/sprintcity_english)

> The SprintCity computer-based simulation game simulates station area development, job growth, ridership and the change in train frequency for several stations along a rail corridor until the year 2030. The game is played by 6 teams consisting of 2 people: each whom have a different role in the process and who are actual stakeholders (urban planners, the Ministry of Transport, municipalities, provinces, housing corporations, rail infrastructure managers etc.). The tool has been used by over 300 participants, in over 30 simulation sessions.

## Installing

1. Install/Use an apache http server, add a PHP module and a MySQL service. Other webservers may work but are untested. In the current build linux servers may be causing some issues because MySQL is only case sensitive in terms of table/column names but this isn't taken into account (yet) in code. During development [XAMPP](https://www.apachefriends.org/index.html) was used for both which contains all you need in a nice installer package.
2. Make sure the apache and mysql services are running.
3. Clone either the master(dutch) or the english branch in the htdocs folder of the webserver.
4. Import the database, you can use phpmyadmin for convenience.
  * In case of the master(dutch) branch: Import the /database/create.sql and /database/insert.sql on your MySQL server. This should create a database called SprintCity. If another name is required either create another database and import said .sql files in there or change the database name on top of the create.sql file.
  * In case of teh english branch: Import the /database/english/sprintstad.sql. This should create a database called SprintCity. If another name is required either create another database and import said .sql file in there or change the database name on top of the sprintstad.sql file.
5. Make sure the /includes/class.config.php file is setup properly.
  * On top of the file the host name for different sever configurations can be found (productionServers, stagingServers, localServers). This links what is typed in the address bar in the browser to a specific configuration specified later in the config file. Right now only 'localhost' is defined as localServer. If for example the server also needs to be accessed using '127.0.0.1' the following change would be needed: 
'''php
private $localServers = array('localhost', '127\.0\.0\.1');
'''
  * The actual configuration can be found in functions below. $localServers addresses point to the local() function for example.
  * Make sure the database host, name, username and password is setup correctly here. The config there is default for a xampp installation.
6. Go to http://localhost/SprintCity or any other domain or folder you put the files from this repository in. Click admin.
7. The default username is 'admin', the default password is 'David'.
8. Add a new time table by going to the Time Tables option on the admin panel and import "2012Sprintstad versie corridorknips 3.xls" for all public transport data needed.

The system is now ready for creating new scenarios, stations, teams and games.

## License

SprintCity is licensed under the XXX License, Version XX. [View the license file](LICENSE)

## Acknowledgements

* [simple-php-framework](https://github.com/tylerhall/simple-php-framework)
* [PHP-ExcelReader](http://sourceforge.net/projects/phpexcelreader/)
* [jQuery](https://github.com/jquery/jquery)
* [JSColor](http://jscolor.com/)
* [Paper.js](http://paperjs.org/)