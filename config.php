<?php
/*
This is the configuration file of the decision database interface.
*/

// Path to the SQLite3 database that contains all decisions.
$db_path = "./db.sqlite3";

// Folder where new decisions (in YAML form) can be found.
// Note that the web server must be able to read and delete files in that folder.
$import_path = "./imports";
?>
