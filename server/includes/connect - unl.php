<?php
$dbname="juc2013";
$server="localhost";
$username="u-juc2013";
$password="JorUniv77";
$db = mysql_connect($server,$username,$password);
mysql_query("use $dbname");
mysql_query("SET NAMES utf8");
?>
