<?php

$servername = "localhost";
$serverusername = "crisnil_db";
$serverpassword = "crisnil123";
$databasename = "crisnil_db";
$GOOGLE_MAPS_API_KEY = "AIzaSyB0yZHj2xllQIw4A1IgnsEedEiKnSly640";
//F!jO5MbN6
$databaseconn = new mysqli($servername, $serverusername, $serverpassword, $databasename);

if (!$databaseconn){
    echo "<h1>EXCEPTION OCCURRED!<h1>";
    echo "<br>";
    echo "<p>Database Connection Failure detected at " . date("d/m/y H:i:s") . " .</p>";
    echo "<br>";
    echo "<p>Contact support for assistance</p>";
    echo "<br>";
    echo "<br>";
}

?>