<?php
//This file contains all the globally accessible variables and constants.

//Setup - TODO move to config.
$adminList = Array("admin");

//Global variables
$loggedInUser = "";
$loginChecked = false;
$config = Array();
$dictionary = Array();

require "Mustache/Autoloader.php";
Mustache_Autoloader::register();
$mustache = new Mustache_Engine;

?>