<?php
$servername = "srv1416.hstgr.io";
$serverusername = "u232602010_crisnil_db_v2";
$serverpassword = "F!jO5MbN6";
$databasename = "u232602010_crisnil_db_v2";
$GOOGLE_MAPS_API_KEY = "AIzaSyB0yZHj2xllQIw4A1IgnsEedEiKnSly640";

$databaseconn = new mysqli($servername, $serverusername, $serverpassword, $databasename);

if ($databaseconn->connect_error) {
    die("Database connection failed: " . $databaseconn->connect_error);
}
?>
